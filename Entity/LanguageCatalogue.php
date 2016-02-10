<?php

namespace Symbio\OrangeGate\TranslationBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * LanguageCatalogue
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Symbio\OrangeGate\TranslationBundle\Entity\LanguageCatalogueRepository")
 * @UniqueEntity("name")
 */
class LanguageCatalogue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken", mappedBy="catalogue", fetch="EAGER", cascade={"persist", "remove"})
     */
    private $tokens;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=1000, unique=true)
     */
    private $name;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return LanguageCatalogue
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add token
     *
     * @param \Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken $token
     * @return LanguageCatalogue
     */
    public function addToken(\Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken $token)
    {
        $this->tokens[] = $token;

        return $this;
    }

    /**
     * Remove token
     *
     * @param \Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken $token
     */
    public function removeToken(\Symbio\OrangeGate\TranslationBundle\Entity\LanguageToken $token)
    {
        $this->tokens->removeElement($token);
    }

    /**
     * Get tokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    public function __toString()
    {
        return $this->name;
    }
}
