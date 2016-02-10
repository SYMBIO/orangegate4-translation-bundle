<?php

namespace Symbio\OrangeGate\TranslationBundle\Command;

use Symbio\OrangeGate\TranslationBundle\Entity\LanguageCatalogue;
use Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken;
use Symbio\OrangeGate\TranslationBundle\Entity\LanguageTranslation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\FileLocator;

class UpdateSystemTranslationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orangegate:translation:update-system-translations')
            ->setDescription('Update system translations from dump of LanguageToken and LanguageTranslation tables in json.')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('default_location', 'd', InputOption::VALUE_NONE),
                    new InputArgument('path', InputArgument::OPTIONAL)
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        if (!$input->getArgument('path') && !$input->getOption('default_location')) {
            throw new \Exception("Please provide absolute path to source file (JSON) or run command with [-d] option to load file located in 'dumps' folder.");
        }

        $inPath = $input->getArgument('path') ? $input->getArgument('path') : __DIR__ . "/../Resources/dumps/translation_dump.json";
        $start = strrpos($inPath, '/');

        $fileName = substr($inPath, $start + 1, strlen($inPath) - $start);
        $path = substr($inPath, 0, $start);

        $locator = new FileLocator();
        try {
            $file = $locator->locate($fileName, $path);
        } catch (\Exception $ex) {
            throw new \Exception("File " . $fileName . " doesn't exist in path " . $path);
        }


        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            throw new \Exception("File " . $fileName . "is empty or it's not a valid JSON file.");
        }

        $catalogues = [];
        $insertedC = 0;
        //$data[0] - LanguageCatalogue table
        foreach ($data[0] as $catalogue) {
            $cat = $em->getRepository('SymbioOrangeGateTranslationBundle:LanguageCatalogue')->findOneBy(['name' => $catalogue['name']]);
            if (!$cat) {
                $cat = new LanguageCatalogue();
                $cat->setName($catalogue['name']);
                $em->persist($cat);
                $insertedC++;
            }

            $catalogues[$catalogue['id']]['cat'] = $cat;
        }

        $tokens = [];
        //$data[1] - LanguageToken table
        foreach ($data[1] as $token) {
            if ($token['site_id'] === null) {
                $tokens[$token['id']]['token'] = $token;
            }
        }

        //$data[2] - LanguageTranslation table
        foreach ($data[2] as $trans) {
            if (isset($tokens[$trans['languageToken_id']])) {
                $tokens[$trans['languageToken_id']]['trans'][] = $trans;
            }
        }

        foreach ($catalogues as $key => $cat) {
            foreach ($tokens as $t_key => $token) {
                if ($token['token']['catalogue_id'] == $key) {
                    $catalogues[$key]['tokens'][$t_key] = $token;
                }
            }
        }

        $insertedT = 0;
        foreach ($catalogues as $cat) {
            foreach ($cat['tokens'] as $record) {
                $token = $em->getRepository('SymbioOrangeGateTranslationBundle:LanguageToken')->findOneBy(['catalogue' => $cat['cat'], 'token' => $record['token']['token']]);
                if (!$token) {
                    $token = new LanguageToken();
                    $token->setToken($record['token']['token']);
                    $token->setCatalogue($cat['cat']);
                    $insertedT++;
                    $em->persist($token);
                }

                foreach ($record['trans'] as $tr) {
                    $trans = $em->getRepository('SymbioOrangeGateTranslationBundle:LanguageTranslation')->findOneBy(['languageToken' => $token, 'language' => $tr['language']]);
                    if (!$trans) {
                        $trans = new LanguageTranslation();
                        $trans->setLanguage($tr['language']);
                        $trans->setLanguageToken($token);
                    }

                    $trans->setTranslation($tr['translation']);
                    $em->persist($trans);
                }
            }
        }

        $em->flush();

        $output->writeln("==========CATALOGUES==========");
        $output->writeln("Inserted: " . $insertedC);
        $output->writeln("============TOKENS============");
        $output->writeln("Inserted: " . $insertedT);
        $output->writeln("Clearing the translation cache.");

        //clear translations cache
        $cacheDir = $this->getContainer()->get('kernel')->getCacheDir();
        $finder = new Finder();
        $finder->in([$cacheDir . '/../*/translations'])->files();

        foreach ($finder as $file) {
            unlink($file->getRealPath());
        }

        if (is_dir($cacheDir . '/translations')) {
            rmdir($cacheDir . '/translations');
        }

        $output->writeln("Operation complete!");
    }
}