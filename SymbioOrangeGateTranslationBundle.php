<?php

namespace Symbio\OrangeGate\TranslationBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symbio\OrangeGate\TranslationBundle\DependencyInjection\Compiler\TemplatingPass,
    Symbio\OrangeGate\TranslationBundle\DependencyInjection\Compiler\AddResourcePass;

class SymbioOrangeGateTranslationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TemplatingPass());
        $container->addCompilerPass(new AddResourcePass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
