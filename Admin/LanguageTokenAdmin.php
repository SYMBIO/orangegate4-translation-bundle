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

class LanguageTokenAdmin extends BaseAdmin
{
    protected $siteManager;
    protected $sitePool;

    protected function configureFormFields(FormMapper $formMapper)
    {
        $site = $this->getSite();
        if (!$site) {
            $formMapper
                ->add('token', 'text', array('label' => 'Key'));
        } else {
            $formMapper
                ->add('token', 'text', array('label' => 'Key', 'data' => strtolower($site->getSlug()).'.'));
        }
        $formMapper
            ->add('catalogue', 'sonata_type_model_list', array())
            ->add('translations', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position',
                'admin_code' => 'orangegate.admin.translation'
            ));
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('token')
            ->add('catalogue')
            ->add('site', null, array(
                'show_filter' => false,
            ))
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
            ->addIdentifier('site')
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
        $site = $this->sitePool->getCurrentSite($this->getRequest());

        foreach ($object->getTranslations() as $tr) {
            $tr->setLanguageToken($object);
        }
        $object->setSite($site);
        $this->clearCache();
    }

    public function preUpdate($object)
    {
        foreach ($object->getTranslations() as $tr) {
            $tr->setLanguageToken($object);
        }
        $this->clearCache();
    }

    public function postRemove($object)
    {
        $this->clearCache();
    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        if (!$this->hasRequest()) {
            return $instance;
        }

        if ($site = $this->getSite()) {
            $instance->setSite($site);
        }

        return $instance;
    }

    /**
     * @return SiteInterface
     *
     * @throws \RuntimeException
     */
    public function getSite()
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $siteId = null;

        if ($this->getRequest()->getMethod() == 'POST') {
            $values = $this->getRequest()->get($this->getUniqid());
            $siteId = isset($values['site']) ? $values['site'] : null;
        }

        $siteId = (null !== $siteId) ? $siteId : $this->getRequest()->get('siteId');

        if ($siteId) {
            $site = $this->siteManager->findOneBy(array('id' => $siteId));

            if (!$site) {
                throw new \RuntimeException('Unable to find the site with id=' . $this->getRequest()->get('siteId'));
            }

            return $site;
        }

        return false;
    }

    /**
     * @param \Sonata\PageBundle\Model\SiteManagerInterface $siteManager
     */
    public function setSiteManager(SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    public function setSitePool(SitePool $sitePool)
    {
        $this->sitePool = $sitePool;
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

