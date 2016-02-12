<?php

namespace Symbio\OrangeGate\TranslationBundle\Services;

use Symbio\OrangeGate\PageBundle\Entity\SitePool;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Doctrine\ORM\EntityManager;

class DBLoader implements LoaderInterface{
    private $translationRepository;
    private $catalogueRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager){
        $this->translationRepository = $entityManager->getRepository("SymbioOrangeGateTranslationBundle:LanguageTranslation");
        $this->catalogueRepository = $entityManager->getRepository("SymbioOrangeGateTranslationBundle:LanguageCatalogue");
    }

    function load($resource, $locale, $domain = 'messages'){
        $cataloguesDB = $this->catalogueRepository->findAll();
        $catalogue = new MessageCatalogue($locale);
        foreach ($cataloguesDB as $ctlg) {
            $translations = $this->translationRepository->getTranslations($locale, $ctlg->getName());
            foreach($translations as $translation){
                $catalogue->set($translation->getLanguageToken()->getToken(), $translation->getTranslation(), $ctlg->getName());
            }
        }

        $catalogue->addResource(new FileResource($resource));
        return $catalogue;
    }
}