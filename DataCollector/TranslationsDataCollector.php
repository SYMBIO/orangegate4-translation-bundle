<?php

namespace Symbio\OrangeGate\TranslationBundle\DataCollector;

use Symbio\OrangeGate\TranslationBundle\Entity\CatalogueRepository;
use Symbio\OrangeGate\TranslationBundle\Entity\LanguageCatalogueRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class TranslationsDataCollector extends DataCollector
{

    private $translator;
    private $catalogueRepository;

    public function  __construct(Translator $translator, LanguageCatalogueRepository $catalogueRepository)
    {
        $this->translator = $translator;
        $this->catalogueRepository = $catalogueRepository;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $catalogue = $this->translator->getCatalogue($request->getLocale())->all();
        $cataloguesDB  = $this->catalogueRepository->findAll();
        $translations = [];

        foreach ($cataloguesDB as $ctlg) {
            if (isset($catalogue[$ctlg->getName()])) {
                $translations[$ctlg->getName()] = $catalogue[$ctlg->getName()];
            }
        }

        $this->data = ['translations' => $translations];
    }

    public function getTotalTranslations()
    {
        $count = 0;
        foreach ($this->data['translations'] as $catalog) {
            $count += count($catalog);
        }
        return $count;
    }

    public function getTranslations()
    {
        return $this->data['translations'];
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     * @api
     */
    public function getName()
    {
       return 'orangegate_translation.data_collector.translations';
    }
}