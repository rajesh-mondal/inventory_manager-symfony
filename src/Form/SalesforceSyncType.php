<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SalesforceSyncType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Please enter your first name'])]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Please enter your last name'])]
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Required for Salesforce Account'],
                'constraints' => [new NotBlank(['message' => 'Company name is required for CRM sync'])]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}