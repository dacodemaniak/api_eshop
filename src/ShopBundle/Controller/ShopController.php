<?php
namespace ShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use UserBundle\Entity\User;
use ShopBundle\Entity\Shop;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Common\Util\ClassUtils;

use UserBundle\Service\TokenService;

class ShopController extends FOSRestController {
    
    /**
     * Service d'authentification de l'utilisateur
     * @var TokenService
     */
    private $tokenService;
    
    public function __construct() {
        
    }
    
    /**
     * @Rest\Get("/shops")
     */
    public function allShopsAction(Request $request) {
        $this->tokenService = $this->get("token_service");
        
        $authGuard = $this->tokenService->authenticate($request);
        
        $results = [];
        
        if ($authGuard["code"] === Response::HTTP_OK) {
            $shops = $this->getDoctrine()
                ->getManager()
                ->getRepository("ShopBundle:Shop")
                ->findAll();
            if ($shops) {
                foreach ($shops as $shop) {
                    $results[] = [
                            "id" => $shop->getId(),
                            "slug" => $shop->getSlug(),
                            "api" => $shop->getApi(),
                            "title" => $shop->getTitle()
                    ];
                }
                return new View($results, Response::HTTP_OK);
            }
            return new View($results, Response::HTTP_NO_CONTENT);
        }
        
        return new View("Token utilisateur expiré", Response::HTTP_FORBIDDEN);
    }
}