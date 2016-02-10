<?php

namespace Symbio\OrangeGate\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;

class UpdateTranslationDumpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orangegate:translation:dump-update')
            ->setDescription('Update JSON dump of LanguageCatalogue, LanguageToken and LanguageTranslation tables.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $tokens = $em->createQuery("SELECT t.id, c.id as catalogue_id, s.id as site_id, t.token FROM SymbioOrangeGateTranslationBundle:LanguageToken t JOIN t.catalogue c LEFT JOIN t.site s")->execute();
        $cIds = [];

        foreach ($tokens as $token) {
            $cIds[$token['catalogue_id']] = $token['catalogue_id'];
            $tIds[$token['id']] = $token['id'];
        }

        $catalogues = $em->createQuery("SELECT c.id, c.name FROM SymbioOrangeGateTranslationBundle:LanguageCatalogue c WHERE c.id IN(:ids)")->setParameter('ids', $cIds)->execute();
        $translations = $em->createQuery("SELECT t.id, t.translation, t.language, lt.id as languageToken_id FROM SymbioOrangeGateTranslationBundle:LanguageTranslation t JOIN t.languageToken as lt WHERE lt.id IN(:ids)")->setParameter('ids', $tIds)->execute();
        $dump = [$catalogues, $tokens, $translations];

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        try {
            $fs->dumpFile(__DIR__ . "/../Resources/dumps/translation_dump.json", json_encode($dump));
        } catch (IOException $e) {
            throw new \Exception("An error was occured in updateing \"translation_dump.json\" file");
        }

        $output->writeln("Operation complete!");
    }
}