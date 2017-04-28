<?php

namespace Symbio\OrangeGate\TranslationBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symbio\OrangeGate\AdminBundle\Admin\Admin as BaseAdmin;
use Symfony\Component\Translation\TranslatorInterface;

class SystemLanguageTranslationAdmin extends BaseAdmin
{
    protected $translator;
    protected $locales;

    public function __construct($code, $class, $baseControllerName, TranslatorInterface $translator, $locales)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->translator = $translator;
        $this->locales = $locales;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $languages = [];
        foreach($this->locales as $locale) {
            $languages[$locale] = \Locale::getDisplayLanguage(sprintf('%s-Latn-IT-nedis', $locale), $this->translator->getLocale());
        }

        $formMapper
            ->add('language', 'choice', array(
                'label' => $this->translator->trans('Language', [], 'SymbioOrangeGateTranslationBundle'),
                'expanded' => false,
                'choices' => $languages
            ))
            ->add('translation', 'text', array(
                'label' => $this->translator->trans('Translation', [], 'SymbioOrangeGateTranslationBundle')
            ))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('language')
        ;
    }

    public function getExportFields()
    {
        return array(
            'language',
            'translation',
        );
    }


    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('language')
            ->addIdentifier('translation')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }


    // Fields to be shown on revisions
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('language', null, array('label' => 'Language'))
            ->add('translation', null, array('label' => 'Translation'))
        ;
    }
}