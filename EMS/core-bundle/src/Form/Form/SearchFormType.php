<?php

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Entity\Form\Search;
use EMS\CoreBundle\Entity\SearchFieldOption;
use EMS\CoreBundle\Entity\SortOption;
use EMS\CoreBundle\Form\Field\ContentTypePickerType;
use EMS\CoreBundle\Form\Field\EnvironmentPickerType;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use EMS\CoreBundle\Form\Subform\SearchFilterType;
use EMS\CoreBundle\Service\SearchFieldOptionService;
use EMS\CoreBundle\Service\SortOptionService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SearchFormType extends AbstractType
{
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker, private readonly SortOptionService $sortOptionService, private readonly SearchFieldOptionService $searchFieldOptionService)
    {
    }

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isSuper = $this->authorizationChecker->isGranted('ROLE_SUPER');

        $searchFields = [];
        $searchFieldsData = [];

        /** @var SearchFieldOption[] $searchFieldOptions */
        $searchFieldOptions = $this->searchFieldOptionService->getAll();
        foreach ($searchFieldOptions as $searchFieldOption) {
            $searchFieldsData[$searchFieldOption->getName()] = $searchFieldOption->getField();
            $searchFields[$searchFieldOption->getName()] = $searchFieldOption;
        }

        $builder->add('filters', CollectionType::class, [
            'entry_type' => SearchFilterType::class,
            'allow_add' => true,
            'entry_options' => [
                'is_super' => $isSuper,
                'searchFieldsData' => $searchFieldsData,
                'searchFields' => $searchFields,
            ],
        ]);
        if ($options['light']) {
            $builder->add('applyFilters', SubmitEmsType::class, [
                'attr' => [
                        'class' => 'btn btn-primary btn-md',
                ],
                'icon' => 'fa fa-check',
            ]);
        } else {
            /** @var SortOption[] $sortOptions */
            $sortOptions = $this->sortOptionService->getAll();
            if ($isSuper || empty($sortOptions)) {
                $builder->add('sortBy', TextType::class, [
                    'required' => false,
                ]);
            } else {
                $sortFields = [];
                $sortFieldIcons = [];
                foreach ($sortOptions as $sortOption) {
                    $sortFields[$sortOption->getName()] = $sortOption->getField();
                    $sortFieldIcons[$sortOption->getField()] = $sortOption->getIcon();
                }

                $builder->add('sortBy', ChoiceType::class, [
                    'required' => false,
                    'choices' => $sortFields,
                    'choice_attr' => fn ($category, $key, $index) => [
                        'data-content' => '<span class=""><i class="'.($sortFieldIcons[$index] ?: 'fa fa-square').'"></i>&nbsp;&nbsp;'.$key.'</span>',
                    ],
                    'attr' => [
                        'class' => 'selectpicker',
                    ],
                ]);
            }

            $builder->add('sortOrder', ChoiceType::class, [
                'choices' => [
                    'Ascending' => 'asc',
                    'Descending' => 'desc',
                ],
                'choice_attr' => fn ($category, $key, $index) => [
                    'data-content' => '<span class=""><i class="fa fa-sort-'.$index.'"></i>&nbsp;&nbsp;'.$key.'</span>',
                ],
                'attr' => [
                    'class' => 'selectpicker',
                ],
                'required' => false,
            ]);

            $builder->add('minimumShouldMatch', IntegerType::class, [
                'required' => false,
                'empty_data' => '1',
                'attr' => [
                    'min' => 0,
                ],
            ]);

            $builder->add('search', SubmitEmsType::class, [
                    'attr' => [
                            'class' => 'btn btn-primary btn-md',
                    ],
                    'icon' => 'fa fa-search',
            ])->add('exportResults', SubmitEmsType::class, [
                    'attr' => [
                            'class' => 'btn btn-primary btn-sm',
                    ],
                    'icon' => 'glyphicon glyphicon-export',
            ])->add('environments', EnvironmentPickerType::class, [
                'multiple' => true,
                'required' => false,
                'managedOnly' => false,
            ])->add('contentTypes', ContentTypePickerType::class, [
                'multiple' => true,
                'required' => false,
            ]);
            if (!$options['savedSearch']) {
                $builder->add('save', SubmitEmsType::class, [
                        'attr' => [
                                'class' => 'btn btn-primary btn-md',
                        ],
                        'icon' => 'fa fa-save',
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'savedSearch' => false,
            'csrf_protection' => false,
            'light' => false,
            'translation_domain' => EMSCoreBundle::TRANS_DOMAIN,
        ]);
    }

    /**
     * @param FormView<FormView>           $view
     * @param FormInterface<FormInterface> $form
     * @param array<mixed>                 $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
    }
}
