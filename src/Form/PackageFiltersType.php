<?php

namespace App\Form;

use App\DTO\PackageSearchFilter;
use App\Entity\Category;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', SearchType::class, [
                'required' => false,
                'label' => 'Name'
            ])
            ->add('minPrice', NumberType::class, [
                'required' => false,
                'label' => 'Min Price'
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false,
                'label' => 'Max Price'
            ])
            ->add('category', EntityType::class, [
                'required' => false,
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('isAvailable', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'Available Only' => true,
                    'Sold Out' => false,
                    'All Packages' => null,
                ],
                'label' => 'Status'
            ])
            // ->add('submit', SubmitType::class, ['label' => 'Filter'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PackageSearchFilter::class,
            'method' => 'GET',
        ]);
    }
}
