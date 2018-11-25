<?php

namespace MenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuToCategories
 *
 * @ORM\Table(name="menutocategories")
 * @ORM\Entity(repositoryClass="MenuBundle\Repository\MenuToCategoriesRepository")
 */
class MenuToCategories
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
     * @var int
     *
     * @ORM\Column(name="place", type="smallint")
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity=Menu::class, inversedBy="categories")
     */
    protected $menu;
    
    /**
     * @ORM\ManyToOne(targetEntity=Categorie::class)
     */
    protected $category;
    

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
     * Set place
     *
     * @param integer $place
     *
     * @return MenuToCategories
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return int
     */
    public function getPlace()
    {
        return $this->place;
    }
    
    /**
     * Définit le menu de l'association
     * @param \MenuBundle\Entity\Menu $menu
     * @return \MenuBundle\Entity\MenuToCategories
     */
    public function setMenu($menu) {
    	$this->menu = $menu;
    	
    	return $this;
    }
    
    /**
     * Retourne le menu de l'association
     * @return \MenuBundle\Entity\Menu
     */
    public function getMenu(): \MenuBundle\Entity\Menu {
    	return $this->menu;
    }
    
    /**
     * Définit la catégorie de l'association
     * @param \MenuBundle\Entity\Categorie $categorie
     * @return \MenuBundle\Entity\MenuToCategories
     */
    public function setCategorie($categorie): \MenuBundle\Entity\MenuToCategories {
    	$this->category = $categorie;
    	return $this;
    }
    
    /**
     * Retourne la catégorie de l'association
     * @return \MenuBundle\Entity\Categorie
     */
    public function getCategorie(): \MenuBundle\Entity\Categorie {
    	return $this->category;
    }
}

