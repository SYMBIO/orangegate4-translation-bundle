<?php

namespace Symbio\OrangeGate\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;

class ClearTranslationCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orangegate:translation:clear-cache')
            ->setDescription('Clear translation cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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