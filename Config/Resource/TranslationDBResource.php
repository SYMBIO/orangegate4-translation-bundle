<?php
namespace Symbio\OrangeGate\TranslationBundle\Config\Resource;

use Symbio\OrangeGate\TranslationBundle\Services\DBLoader;
use Symfony\Component\Config\Resource\ResourceInterface;

class TranslationDBResource implements ResourceInterface
{
    /**
     * @var DBLoader
     */
    private $loader;
    /**
     * @param DBLoader $loader
     */
    public function __construct(DBLoader $loader)
    {
        $this->loader = $loader;
    }
    /**
     * {@inheritDoc}
     */
    public function isFresh($timestamp)
    {
        return $this->loader->isFresh($timestamp);
    }
    /**
     * {@inheritDoc}
     */
    public function getResource()
    {
        return $this->loader;
    }
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return 'DBLoader';
    }
}