<?php

namespace Symbio\OrangeGate\TranslationBundle\Admin;

use Sonata\PageBundle\Model\SiteManagerInterface;
use Symbio\OrangeGate\AdminBundle\Admin\Admin as BaseAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symbio\OrangeGate\PageBundle\Entity\SitePool;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class LanguageTranslationAdmin extends BaseAdmin
{
    protected $sitePool;

    public function __construct($code, $class, $baseControllerName, SitePool $sitePool)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->sitePool = $sitePool;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $currentSite = $this->sitePool->getCurrentSite($this->getRequest());
        $locales = array();

        foreach ($currentSite->getLanguageVersions() as $lv) {
            $locales[$lv->getLocale()] = $lv->getName();
        }

        $formMapper
            ->add('language', 'choice', array('label' => 'Jazyk', 'expanded' => false, 'choices' => $locales))
            ->add('translation', 'text', array('label' => 'Překlad'))
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