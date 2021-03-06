<?php

namespace Symbio\OrangeGate\TranslationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class LanguageTokenAdminController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request = NULL)
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $sitePool = $this->get('orangegate.site.pool');
        $sites = $sitePool->getSites();
        $currentSite = $sitePool->getCurrentSite($request);

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('site', null, $currentSite->getId());
        $formView = $datagrid->getForm()->createView();

        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render('SymbioOrangeGateTranslationBundle:LanguageTokenAdmin:list.html.twig', array(
            'action' => 'list',
            'sites' => $sites,
            'datagrid' => $datagrid,
            'currentSite' => $currentSite,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ));
    }
}
