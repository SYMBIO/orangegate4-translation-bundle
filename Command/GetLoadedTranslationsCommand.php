<?php

namespace Symbio\OrangeGate\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetLoadedTranslationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orangegate:translation:get-loaded-translations')
            ->setDescription('Print loaded translations from DB. Use --env=prod to run command in prod environment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $translator = $this->getContainer()->get('translator');

        $catalogue = $translator->getCatalogue($translator->getLocale())->all();
        $cataloguesDB = $em->getRepository("SymbioOrangeGateTranslationBundle:LanguageCatalogue")->findAll();
        $translations = [];

        foreach ($cataloguesDB as $ctlg) {
            if (isset($catalogue[$ctlg->getName()])) {
                $translations[$ctlg->getName()] = $catalogue[$ctlg->getName()];
            }
        }

        $output->writeln("Translations loaded from DB for current locale (" . $translator->getLocale() . ")");

        $loaded = 0;
        foreach ($translations as $ctlg => $tokens) {
            $output->writeln("<info>Catalogue: </info>" . $ctlg);
            foreach ($tokens as $token => $translation) {
                $loaded++;
                $output->write("<comment>Token: </comment>");
                $output->write($token);
                $output->write(", <comment>Translation: </comment>");
                $output->writeln($translation);
            }
        }

        $output->writeln("Tokens loaded: " . $loaded);
        $output->writeln("Operation complete!");
    }
}