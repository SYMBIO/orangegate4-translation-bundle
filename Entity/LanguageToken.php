<?php

namespace Symbio\OrangeGate\TranslationBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * LanguageToken
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Symbio\OrangeGate\TranslationBundle\Entity\LanguageTokenRepository")
 */
class LanguageToken
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
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;


    /**
     *
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Symbio\OrangeGate\TranslationBundle\Entity\LanguageCatalogue", inversedBy="tokens", cascade={"persist"})
     * @ORM\JoinColumn(name="catalogue_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $catalogue;

    /**
     * @ORM\ManyToOne(targetEntity="Symbio\OrangeGate\PageBundle\Entity\Site", cascade={"persist"})
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $site;

    /**
     *
     * @ORM\OneToMany(targetEntity="Symbio\OrangeGate\TranslationBundle\Entity\LanguageTranslation", mappedBy="languageToken", fetch="EAGER", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $translations;

    private $export_translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set token
     *
     * @param string $token
     * @return LanguageToken
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Add translations
     *
     * @param \Symbio\OrangeGate\TranslationBundle\Entity\LanguageTranslation $translations
     * @return LanguageToken
     */
    public function addTranslation(\Symbio\OrangeGate\TranslationBundle\Entity\LanguageTranslation $translations)
    {
        $this->translations[] = $translations;

        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Symbio\OrangeGate\TranslationBundle\Entity\LanguageTranslation $translations
     */
    public function removeTranslation(\Symbio\OrangeGate\TranslationBundle\Entity\LanguageTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    public function getExportTranslations()
    {
        $results = array();

        foreach($this->translations as $trans) {
            $results[] = $trans->getTranslation();
        }

        return implode(',', $results);
    }

    public function __toString()
    {
        return $this->token;
    }

    /**
     * Get site
     *
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set site
     *
     * @param mixed $site
     * @return LanguageToken
     */
    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * Get catalogue
     *
     * @return mixed
     */
    public function getCatalogue()
    {
        return $this->catalogue;
    }

    /**
     * Set catalogue
     *
     * @param mixed $catalogue
     * @return LanguageToken
     */
    public function setCatalogue($catalogue)
    {
        $this->catalogue = $catalogue;
        return $this;
    }


}
