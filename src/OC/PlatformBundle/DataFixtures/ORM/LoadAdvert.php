<?php
// src/OC/PlatformBundle/DataFixtures/ORM;

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Advert;

class LoadAdvert extends Fixture implements DependentFixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager)
    {
        $advert = new Advert();
        $advert->setTitle('Recherche développeur Symfony');
        $advert->setAuthor('Alexandre');
        $advert->setContent('Nous recherchons un développeur Symfony débutant sur Lyon. Blabla');
        $advert->addCategory($this->getReference('categorie_'.rand(0,4)));
        $manager->persist($advert);

        $advert = new Advert();
        $advert->setTitle('Mission de webmaster');
        $advert->setAuthor('Hugo');
        $advert->setContent('Nous recherchons un webmaster capable de maintenir notre site internet.Blabla');
        $advert->addCategory($this->getReference('categorie_'.rand(0,4)));
        $manager->persist($advert);

        $advert = new Advert();
        $advert->setTitle('Offre de stage webdesigner');
        $advert->setAuthor('Mathieu');
        $advert->setContent('Nous proposons un poste pour un webdesigner. Blabla');
        $advert->addCategory($this->getReference('categorie_'.rand(0,4)));
        $manager->persist($advert);

        // On déclenche l'enregistrement de toutes les catégories
        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            LoadCategory::class
        );
    }
}
