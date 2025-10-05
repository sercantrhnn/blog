<?php

namespace App\Form;

use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Başlık',
                'required' => false,
                'attr' => ['placeholder' => 'Başlık giriniz']
            ])
            ->add('description', TextType::class, [
                'label' => 'Kısa Açıklama',
                'required' => false,
                'attr' => ['placeholder' => 'Kısa açıklama']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Görsel (JPG/PNG)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('post', TextareaType::class, [
                'label' => 'İçerik',
                'required' => false,
                'attr' => ['rows' => 10, 'placeholder' => 'Blog içeriği']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}


