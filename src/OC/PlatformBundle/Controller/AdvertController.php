<?php
/**
 * Created by PhpStorm.
 * User: EveMarieThomasse
 * Date: 28/12/2018
 * Time: 14:53
 */

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page"' . $page . '" inexistante');
        }

// Ici je fixe le nombre d'annonces par page à 3
// Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')

        $nbPerPage = 3;

// On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage);

// On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

// Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }

// On donne toutes les informations nécessaires à la vue
        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages' => $nbPages,
            'page' => $page,
        ));
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

// On récupère l'annonce $id
        $advert = $em->getRepository(Advert::class)->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . "n'existe pas.");
        }

// On récupère la liste des candidatures de cette annonce
        $listApplications = $em
            ->getRepository(Application::class)
            ->findBy(array('advert' => $advert));

// On récupère maintenant la liste des AdvertSkill
        $listAdvertSkills = $em
            ->getRepository(AdvertSkill::class)
            ->findBy(array('advert' => $advert));


// Le render ne change pas, on passait avant un tableau, maintenant un objet
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listAdvertSkills
        ));
    }

    public function addAction(Request $request)
    {
        // On crée un objet Advert
        $advert = new Advert();


        // J'ai raccourci cette partie, car c'est plus rapide à écrire !
        $form = $this->get('form.factory')->create(AdvertType::class, $advert);

        // si la requête est en POST
        if ($request->isMethod('POST') &&  $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $advert->getImage()->upload();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    public function editAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository(Advert::class)->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

       // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce cotnre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce à bien été supprimée.");

            return $this->redirectToRoute('oc_platform_home');
        }


        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
            'advert' => $advert,
            'form'   =>$form->createView(),
        ));
    }


    public function testAction()
    {
        $advert = new Advert();

        $advert->setDate(new \DateTime());
        $advert->setTitle("abc");
        $advert->setAuthor('A');

        // On récupère le service validator
        $validator = $this->get('validator');

        // On déclenche la validation sur notre object
        $listErrors = $validator->validate($advert);

        // Si $listErrors n'est pas vide, on affiche les erreurs
        if(count($listErrors) > 0) {
            // $listErrors est un objet, sa méthode __toString permet de lister joliment les erreurs
            return new Response((string) $listErrors);
        } else {
            return new Response("L'annonce est valide !");
        }

//        $em = $this->getDoctrine()->getManager();
//        $em->persist(advert);
//        $em->flush(); // C'est à ce moment qu'est généré le slug
//
//        return new Response('Slug généré : ' . $advert->getSlug());
//        // Affiche " Slug généré : recherche-developpeur "

    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();


        $listAdverts = $em->getRepository(Advert::class)->findBy(
            array(),                    // Pas de critère
            array('date' => 'desc'),    // On trie par date décroissante
            $limit,                     // On sélectionne $limit annonces
            0                           // A partir du premier
        );
//
        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }

}