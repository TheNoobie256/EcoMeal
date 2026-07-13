<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Consumer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsumerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('preferredCategories', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true, // Checkboxes instead of dropdown
            'required' => false,
            'label' => 'Dietary Preferences (Optional)'
        ]);
            # ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Consumer::class,
        ]);
    }
}
