<?php
// src/OC/PlatformBundle/Form/AdvertEditType.php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class AdvertEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     *  @ORM\OneToOne(targetForm="OC\PlatformBundle\Form\ImageType", cascade={"persist"})
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
     $builder->remove('date');
    }
    /**
 * {@inheritdoc}
 */
    public function getParent()
    {
        return AdvertType::class;
    }

}
