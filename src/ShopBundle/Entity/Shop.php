<?php

namespace ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Shop
 *
 * @ORM\Table(name="shops")
 * @ORM\Entity(repositoryClass="ShopBundle\Repository\ShopRepository")
 */
class Shop
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=75)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;


    /**
     * @ORM\OneToMany(targetEntity="\UserBundle\Entity\User", mappedBy="shop")
     */
    private $users;
    
    public function __construct() {
        $this->users = new ArrayCollection();
    }
    
    public function __call(string $methodName, array $params) {
        $attributeName = substr($methodName, 3, strlen($methodName));
        
        $firstChar = strtolower(substr($attributeName, 0, 1));
        
        $attributeName = $firstChar . substr($attributeName, 1, strlen($attributeName));
        
        $content = $this->getContent();
        
        if (property_exists($content, $attributeName)) {
            return $content->{$attributeName};
        }
        
    }
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Shop
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Shop
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->content !== null) {
            $json = new JsonDecode();
            return $json->decode($this->content, JsonEncoder::FORMAT);
        }
        
        return null;
    }
    
    /**
     * Retourne la liste des utilisateurs du groupe
     * @return ArrayCollection
     */
    public function getUsers() {
        return $this->users;
    }
    
    /**
     * Ajoute un utilisateur au site à gérer
     * @param \UserBundle\Entity\User $user
     * @return \Shop
     */
    public function addUser(\UserBundle\Entity\User $user): Shop {
        $this->users[] = $user;
        return $this;
    }
}

