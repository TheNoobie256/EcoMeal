<?php

namespace App\Form;

use App\Entity\Consumer;
use App\Entity\Order;
use App\Entity\Package;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('package', EntityType::class, [
                'class' => Package::class,
                'choice_label' => 'name',
            ])
            ->add('consumer', EntityType::class, [
                'class' => Consumer::class,
                // Concatenates first and last name for the dropdown display
                'choice_label' => function (Consumer $consumer) {
                    return $consumer->getFirstName() . ' ' . $consumer->getLastName();
                },
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
