<?php


namespace Symbio\OrangeGate\TranslationBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseScriptHandler;

class ScriptHandler extends BaseScriptHandler
{
    public static function updateSystemTranslations(CommandEvent $event) {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;

            return;
        }

        static::executeCommand($event, $appDir, 'orangegate:translation:update-system-translations -d', $options['process-timeout']);
    }
}