<?php

namespace Symbio\OrangeGate\TranslationBundle\Command;

use Symbio\OrangeGate\TranslationBundle\Entity\LanguageCatalogue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\FileLocator;

class ImportTokensCommand extends ContainerAwareCommand
{
    const DEFAULT_CATALOGUE =  "messages";

    protected function configure()
    {
        $this
            ->setName('orangegate:translation:import-tokens')
            ->setDescription('Search for tokens in project/cms and put them into db');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $srcFinder = new Finder();
        $cmsFinder = new Finder();


        $cachedFile = $this->getContainer()->getParameter('debug.container.dump');

        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($cachedFile);

        $services = $container->getServiceIds();

        $sites = $em->getRepository('SymbioOrangeGatePageBundle:Site')->findAll();
        $tokens = [];

        // find tokens in src files
        $srcFinder->files()->in($this->getContainer()->get('kernel')->getRootDir().'/../src')->name('*.php')->name('*.html.twig');
        foreach ($srcFinder as $file) {
            $fileContent = file_get_contents($file->getRealPath());
            foreach ($sites as $site_key => $site) {
                $add = false;
                $tokenSite = null;
                $tokenName = null;
                if (preg_match('/\''.strtolower($site->getSlug()).'\.(.*)\'/', $fileContent, $matches) || preg_match('/\"'.strtolower($site->getSlug()).'\.(.*)\"/', $fileContent, $matches)) {
                    $tokenSite = $site;
                    $tokenName = str_replace("\"", '', str_replace('\'', '', $matches[0]));
                    $add = true;
                } elseif (preg_match('/\'orangegate\.(.*)\'/', $fileContent, $matches) || preg_match('/\"orangegate\.(.*)\"/', $fileContent, $matches)) {
                    $tokenName = str_replace("\"", '', str_replace('\'', '', $matches[0]));
                    $add = true;
                }

                if ($add && !in_array($tokenName, $services) && !in_array($tokenName, $tokens)) {
                    $catalogueName = "";
                    if (strpos($tokenName, '|trans') !== false) {
                        $catalogueName = substr($tokenName, strrpos($tokenName, '(') + 1, strlen($tokenName));
                        if (strpos($catalogueName, ',') !== false) {
                            $catalogueName = substr($catalogueName, strrpos($catalogueName, ',') + 1, strlen($catalogueName));
                        }
                        $tokenName = substr($tokenName, 0, strpos($tokenName, '|trans'));
                    }
                    if (strpos($tokenName, ',') !== false) {
                        $tokenName = str_replace(' ', '', $tokenName);
                        $catalogueName = substr($tokenName, strrpos($tokenName, ',') + 1, strlen($tokenName));
                        $tokenName = substr($tokenName, 0, strpos($tokenName, ','));
                    }

                    $catalogueName = $catalogueName == $tokenName || !$catalogueName ? self::DEFAULT_CATALOGUE : $catalogueName;

                    if (strpos($tokenName, '%') === false) {
                        $catalogue = $em->getRepository("SymbioOrangeGateTranslationBundle:LanguageCatalogue")->findOneByName($catalogueName);
                        if (!$catalogue) {
                            $catalogue = new LanguageCatalogue();
                            $catalogue->setName($catalogueName);
                            $em->persist($catalogue);
                        }

                        if (!$em->getRepository('SymbioOrangeGateTranslationBundle:LanguageToken')->findOneBy(['token' => $tokenName])) {
                            $tokens[] = $tokenName;
                            $token = new \Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken();
                            $token->setToken($tokenName);
                            $token->setCatalogue($catalogue);
                            $token->setSite($tokenSite);
                            $em->persist($token);
                        }
                    }
                }
            }
        }

        //find tokens in cms bundles
        $cmsFinder->files()->in($this->getContainer()->get('kernel')->getRootDir().'/../vendor/symbio')->notName('*.json');
        foreach ($cmsFinder as $file) {
            $fileContent = file_get_contents($file->getRealPath());
            if (preg_match('/\'orangegate\.(.*)\'/', $fileContent, $matches) || preg_match('/\"orangegate\.(.*)\"/', $fileContent, $matches)) {
                $tokenName = str_replace("\"", '', str_replace('\'', '', $matches[0]));
                if (!in_array($tokenName, $services) && !in_array($tokenName, $tokens)) {
                    $catalogueName = "";
                    if (strpos($tokenName, '|trans') !== false) {
                        $tokenName = str_replace(' ', '', $tokenName);
                        $catalogueName = substr($tokenName, strrpos($tokenName, '(') + 1, strlen($tokenName));
                        if (strpos($catalogueName, ',') !== false) {
                            $catalogueName = substr($catalogueName, strrpos($catalogueName, ',') + 1, strlen($catalogueName));
                        }
                        $tokenName = substr($tokenName, 0, strpos($tokenName, '|trans'));
                    }
                    if (strpos($tokenName, ',') !== false) {
                        $tokenName = str_replace(' ', '', $tokenName);
                        $catalogueName = substr($tokenName, strrpos($tokenName, ',') + 1, strlen($tokenName));
                        $tokenName = substr($tokenName, 0, strpos($tokenName, ','));
                    }

                    $catalogueName = $catalogueName == $tokenName || !$catalogueName ? self::DEFAULT_CATALOGUE : $catalogueName;
                    if (strpos($tokenName, '%') === false) {
                        $catalogue = $em->getRepository("SymbioOrangeGateTranslationBundle:LanguageCatalogue")->findOneByName($catalogueName);
                        if (!$catalogue) {
                            $catalogue = new LanguageCatalogue();
                            $catalogue->setName($catalogueName);
                            $em->persist($catalogue);
                        }

                        if (!$em->getRepository('SymbioOrangeGateTranslationBundle:LanguageToken')->findOneBy(['catalogue' => $catalogue, 'token' => $tokenName])) {
                            $tokens[] = $tokenName;
                            $token = new \Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken();
                            $token->setToken($tokenName);
                            $token->setCatalogue($catalogue);
                            $token->setSite(null);
                            $em->persist($token);
                        }
                    }
                }
            }
        }

        $em->flush();

        foreach ($tokens as $token) {
            $output->writeln('---> Token "'.$token.'" successfully inserted.');
        }
        $output->writeln('Operation complete!');
    }
}