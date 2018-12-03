<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Groupe
 *
 * @ORM\Table(name="groupes")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\GroupeRepository")
 */
class Groupe
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
     * @ORM\Column(name="libelle", type="string", length=75, unique=true)
     */
    private $libelle;

    /**
     * @var int
     *
     * @ORM\Column(name="can_be_deleted", type="integer")
     */
    private $canBeDeleted;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     */
    private $users;
 
    /**
     * @ORM\ManyToMany(targetEntity=MenuBundle\Entity\Menu::class)
     * @ORM\JoinTable(name="groupetomenus")
     */
    protected $menus;
    
    /**
     * Constructeur de l'entité
     */
    public function __construct() {
    	$this->canBeDeleted = true;
    	$this->users = new ArrayCollection();
    	$this->menus = new ArrayCollection();
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Groupe
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set canBeDeleted
     *
     * @param integer $canBeDeleted
     *
     * @return Groupe
     */
    public function setCanBeDeleted($canBeDeleted)
    {
        $this->canBeDeleted = $canBeDeleted;

        return $this;
    }

    /**
     * Get canBeDeleted
     *
     * @return int
     */
    public function getCanBeDeleted()
    {
        return $this->canBeDeleted;
    }
    
    /**
     * Retourne la liste des utilisateurs du groupe
     * @return ArrayCollection
     */
    public function getUsers() {
    	return $this->users;
    }
    
    /**
     * Ajoute un utilisateur au groupe
     * @param unknown UserBundle\Entity\User $user
     * @return \UserBundle\Entity\Groupe
     */
    public function addUser(\UserBundle\Entity\User $user) {
    	$this->users[] = $user;
    	return $this;
    }
    
    public function getMenus() {
    	return $this->menus;
    }
    
    /**
     * Ajoute un menu au groupe courant
     * @param \MenuBundle\Entity\Menu $menu
     * @return \UserBundle\Entity\Groupe
     */
    public function addMenu(\MenuBundle\Entity\Menu $menu): \UserBundle\Entity\Groupe {
    	$this->menus[] = $menu;
    	return $this;
    }
}

