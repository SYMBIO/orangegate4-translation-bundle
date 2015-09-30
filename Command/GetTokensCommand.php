<?php

namespace Symbio\OrangeGate\TranslationBundle\Command;

use Proxies\__CG__\Agrofert\Bundle\AgrofertBundle\Entity\CHCompany;
use Proxies\__CG__\Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\FileLocator;

class GetTokensCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orangegate:translation:get-tokens')
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
        $tokens = array();

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
                    if (strpos($tokenName, '|trans') !== false) {
                        $tokenName = substr($tokenName, 0, strlen($tokenName) - (strlen($tokenName) - strpos($tokenName, '|trans')));
                    }
                    if (strpos($tokenName, '%') === false) {
                        if (!$em->getRepository('SymbioOrangeGateTranslationBundle:LanguageToken')->findOneBy(array('token' => $tokenName))) {
                            $tokens[] = $tokenName;
                            $token = new \Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken();
                            $token->setToken($tokenName);
                            $token->setSite($tokenSite);
                            $em->persist($token);
                        }
                    }
                }
            }
        }

        //find tokens in cms bundles
        $cmsFinder->files()->in($this->getContainer()->get('kernel')->getRootDir().'/../vendor/symbio');
        foreach ($cmsFinder as $file) {
            $fileContent = file_get_contents($file->getRealPath());
            if (preg_match('/\'orangegate\.(.*)\'/', $fileContent, $matches) || preg_match('/\"orangegate\.(.*)\"/', $fileContent, $matches)) {
                $tokenName = str_replace("\"", '', str_replace('\'', '', $matches[0]));
                if (!in_array($tokenName, $services) && !in_array($tokenName, $tokens)) {
                    if (strpos($tokenName, '|trans') !== false) {
                        $tokenName = substr($tokenName, 0, strlen($tokenName) - (strlen($tokenName) - strpos($tokenName, '|trans')));
                    }
                    if (strpos($tokenName, '%') === false) {
                        if (!$em->getRepository('SymbioOrangeGateTranslationBundle:LanguageToken')->findOneBy(array('token' => $tokenName))) {
                            $tokens[] = $tokenName;
                            $token = new \Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken();
                            $token->setToken($tokenName);
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