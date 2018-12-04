<?php

namespace ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
        return $this->content;
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

