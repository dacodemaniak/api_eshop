<?php
/**
 * @name Categorie Entité relative à la table "categorie"
 * @author IDea Factory (dev-team@ideafactory.fr) - Oct. 2018
 * @package MenuBundle\Entity
 * @version 1.0.1
 * 	- Ajout de la méthode addChildren
 */
namespace MenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany as OneToMany;
use Doctrine\ORM\Mapping\ManyToOne as ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn as JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Categorie
 *
 * @ORM\Table(name="categorie")
 * @ORM\Entity(repositoryClass="MenuBundle\Repository\CategorieRepository")
 */
class Categorie
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
     * @ORM\Column(name="slug", type="string", length=50, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255, nullable=true)
     */
    private $route;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * Une catégorie peut contenir n catégories
     * 
     * @OneToMany(targetEntity="Categorie", mappedBy="parent", fetch="EAGER")
     */
    private $children;

    /**
     * Plusieurs Categories peuvent avoir une catéogie parent
     * 
     * @ManyToOne(targetEntity="Categorie", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    
    public function __construct() {
    	$this->children = new ArrayCollection();
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
     * @return Categorie
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
     * Set route
     *
     * @param string $route
     *
     * @return Categorie
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     *
     * @return Categorie
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Categorie
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return JsonDecode
     */
    public function getContent()
    {
    	
    	if ($this->content) {
    		$json = new JsonDecode();
    		return $json->decode($this->content, JsonEncoder::FORMAT);
    	}
    }
    
    
    /**
     * Retourne le contenu de la catégorie sous forme de tableau associatif
     * @return array
     */
    public function getRawContent(): array {
    	$json = new JsonDecode(true);
    	return $json->decode($this->content, JsonEncoder::FORMAT);
    }
    
    /**
     * Retourne la liste des catégories filles
     * @return ArrayCollection
     */
    public function getChildren() {
    	return $this->children;
    }
    
    /**
     * Détermine si la catégorie courante dispose d'enfants ou non
     * @return bool
     */
    public function hasChildren(): bool {
        return count($this->children) > 0 ? true : false;
    }
    
    /**
     * Ajoute une catégorie fille de la catégorie courante
     * @param \MenuBundle\Entity\Categorie $category
     * @return self
     */
    public function addChildren(\MenuBundle\Entity\Categorie $category): self {
    	$this->children->add($category);
    	
    	return $this;
    }

    public function childrenToArray(): array {
		$datas = [];
		
		if (count($this->children)) {
			foreach ($this->children as $child) {
				if ($child->getIsEnabled()) {
					$datas[] = [
						"id" => $child->getId(),
						"slug" => $child->getSlug(),
						"route" => $child->getRoute(),
						"content" => $child->getRawContent(),
						"nodes" => $child->childrenToArray()
					];
				}
			}
		}
		return $datas;
    }
    
    /**
     * Retourne le parent de la catégorie courante
     * @return Categorie
     */
    public function getParent(): self {
    	return $this->parent;
    }
    
    /**
     * Définit l'entité parente de la catégorie courante
     * @param \MenuBundle\Entity\Categorie $categorie
     */
    public function setParent(\MenuBundle\Entity\Categorie $categorie) {
    	$this->parent = $categorie;
    }
    
    /**
     * Détermine si cette catégorie a un parent
     * @return bool
     */
    public function hasParent(): bool {
        return $this->parent === null ? false : true;
    }
    
    /**
     * Retourne l'ancêtre de la catégorie courante
     * @return Categorie
     */
    public function getAncestor() {
        return $this->getAncestors($this->parent);
    }
    
    /**
     * Retourne les ancêtres de la catégorie courante
     * @return array
     */
    public function getAncestorsAsArray(): array {
        
        return $this->hydrateAncestors($this, []);
        
    }
    
    /**
     * Retourne les ancêtres de la catégorie courante
     * @param self $category Catégorie inspectée
     * @param array $ancestors Ancêtres
     * @return \MenuBundle\Entity\Categorie[]
     */
    private function getAncestors(self $category) {
        
        $hasParent = $category->hasParent();
        
        if ($hasParent) {
            return $this->getAncestors($category->getParent());
        }
        
        return $category;
    }
    
    /**
     * Alimente les ancêtres de la catégorie courante
     */
    private function hydrateAncestors(self $currentCategorie, array $ancestors) {
        $hasParent = $currentCategorie->hasParent();
        
        if ($hasParent) {
            $ancestors[] = [
                "parent" => [
                    "id" => $currentCategorie->getParent()->getId(),
                    "slug" => $currentCategorie->getParent()->getSlug(),
                    "content" => $currentCategorie->getParent()->getRawContent()
                ],
                "ancestors" => $this->hydrateAncestors($currentCategorie->getParent(), $ancestors)
            ];
        }
        return $ancestors;
    }
}

