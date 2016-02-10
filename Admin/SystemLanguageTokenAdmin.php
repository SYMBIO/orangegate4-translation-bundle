<?php

namespace Symbio\OrangeGate\TranslationBundle\Admin;

use Sonata\PageBundle\Model\SiteManagerInterface;
use Symbio\OrangeGate\AdminBundle\Admin\Admin as BaseAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symbio\OrangeGate\PageBundle\Entity\SitePool;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class SystemLanguageTokenAdmin extends BaseAdmin
{
    protected $baseRouteName = 'admin_orangegate_translation_systemlanguagetoken';
    protected $baseRoutePattern = 'translation/system-languagetoken';

    protected function configureFormFields(FormMapper $formMapper)
    {
        $site = $this->getRequest()->get('siteId');

        if (!$site) {
            $formMapper
                ->add('token', 'text', array('label' => 'Key'));
        } else {
            $formMapper
                ->add('token', 'text', array('label' => 'Key', 'data' => $site.'.'));
        }
        $formMapper
            ->add('catalogue', 'sonata_type_model_list', array())
            ->add('translations', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position',
                'admin_code' => 'orangegate.admin.system_translation'
            ));
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('token')
            ->add('catalogue')
        ;
    }


    public function getExportFields()
    {
        $results = $this->getModelManager()->getExportFields($this->getClass());
        $results[] = 'export_translations';
        return $results;
    }


    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('token')
            ->addIdentifier('catalogue')
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
            ->add('token', null, array('label' => 'Key'))
            ->add('catalogue', null, array('label' => 'Catalogue'))
        ;
    }

    public function prePersist($object)
    {
        foreach ($object->getTranslations() as $tr) {
            $tr->setLanguageToken($object);
        }

        $this->clearCache();
    }

    public function preUpdate($object)
    {
        $this->prePersist($object);
    }

    public function postRemove($object)
    {
        $this->clearCache();
    }

    /**
     * {@inheritDoc).
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        // Filter on blocks without page and parents
        $query->andWhere($query->expr()->isNull($query->getRootAlias().'.site'));

        return $query;
    }

    public function clearCache()
    {
        $container = $this->getConfigurationPool()->getContainer();
        $cacheDir = $container->get('kernel')->getCacheDir();
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->in(array($cacheDir . "/../*/translations"))->files();

        foreach($finder as $file){
            unlink($file->getRealpath());
        }

        if (is_dir($cacheDir.'/translations')) {
            rmdir($cacheDir.'/translations');
        }
    }
}