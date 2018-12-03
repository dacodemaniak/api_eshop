<?php
/**
* @name UserController Service REST pour la gestion des utilisateurs
* @author IDea Factory (dev-team@ideafactory.fr)
* @package UserBundle\Controller
* @version 1.0.1
* 	Modification de la route pour l'utilisateur anonyme
*/
namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use UserBundle\Entity\User;
use UserBundle\Entity\Groupe;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Common\Util\ClassUtils;
<<<<<<< HEAD
use UserBundle\Service\TokenService;


class UserController extends FOSRestController {
	
	/**
	 * Instance du repo des utilisateurs
	 * @var \UserBundle\Repository\UserRepository
	 */
	private $_repository;
	
	/**
	 * Instance d'un utilisateur complet
	 * @var \UserBundle\Entity\User
	 */
	private $_wholeUser;
	
	/**
	 * Service de gestion des Token JWT
	 */
	private $tokenService;

	/**
	 * Constructeur du contrôleur User
	 * 
	 * @param TokenService $tokenService
	 */
	public function __construct(TokenService $tokenService) {
		$this->tokenService = $tokenService;
	}
	
	/**
	 * @Rest\Put("/register")
	 */
	public function registerAction(Request $request) {
		$authGuard = $this->tokenService->authenticate($request);

		if ($authGuard["code"] === Response::HTTP_OK) {
			if (!$this->_alreadyExists($request->get("email"))) {
				$this->_wholeUser = new User();
				
				// Génère le sel de renforcement du mot de passe
				$salt = $this->_makeSalt();
				
				$content = [];
				
				$content["lastName"] = $request->get("lastName");
				$content["firstName"] = $request->get("firstName");
				$content["civility"] = $request->get("civility");
				$content["company"] = $request->get("company");
				$content["email"] = $request->get("email");
				$content["phone"] = $request->get("phone");
				
				$this->_wholeUser
					->setLogin($request->get("username"))
					->setSecurityPass($this->_createPassword($request->get("password"), $salt))
					->setSalt($salt)
					->setIsValid(true)
					->setCreatedAt(new \DateTime())
					->setLastLogin(new \DateTime())
					->setValidatedAt(new \DateTime())
					->setContent(json_encode($content))
					->setGroup($this->_getGroup($request->get("group")));
				
				// Fait persister la donnée
				$entityManager = $this->getDoctrine()->getManager();
				$entityManager->persist($this->_wholeUser);
				$entityManager->flush();
				
				return new View($this->_format($this->_wholeUser), Response::HTTP_CREATED);
			}
			
			return new View("Un compte avec cet email existe déjà sur ce site", Response::HTTP_CONFLICT);
		}

		return new View("Token non valide ou expiré", $authGuard["code"]);

	}
	
	/**
	 * @Rest\Post("/signin")
	 * @param Request $request Requête envoyée
	 */
	public function signinAction(Request $request) {
		
		if ($request) {
			if (!$this->_checkLogin($request->get("username"))) {
				return new View("Ce nom d'utilisateur est inconnu ou votre compte a été invalidé", Response::HTTP_FORBIDDEN);
			}
			
			if (!$this->_validPassword($request->get("password"))) {
			    return new View("Votre mot de passe est incorrect, veuillez réessayer s'il vous plaît [" . $request->get("username") . "]" , Response::HTTP_FORBIDDEN);
			}
			
			return new View($this->_format($this->_wholeUser), Response::HTTP_OK);
		}
	}
	
	/**
	 * Détermine si le login saisi n'existe pas déjà
	 * @param string $login
	 * @return bool
	 */
	private function _alreadyExists(string $login): bool {
		$this->_wholeUser = $this->getDoctrine()
			->getManager()
			->getRepository("UserBundle:User")
			->findOneBy(["login" => $login]);
		
		if ($this->_wholeUser) {
			return true;
		}
		
		return false;
	}
	/**
	 * Vérifie l'existence du login et sa validité
	 * @return boolean
	 */
	private function _checkLogin(string $login): bool {
		$this->_wholeUser = $this->getDoctrine()
			->getManager()
			->getRepository("UserBundle:User")
			->findOneBy(["login" => $login]);
		
		if ($this->_wholeUser) {
			if ($this->_wholeUser->getIsValid()) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Vérifie le mot de passe avec la clé de renforcement
	 * @param string $password
	 * @return boolean
	 */
	private function _validPassword(string $password): bool {
		$saltedPassword = $this->_wholeUser->getSalt() . $password . $this->_wholeUser->getSalt();
		
		if (md5($saltedPassword) === $this->_wholeUser->getSecurityPass()) {
			return true;
		}
		
		return false;
	}

	/**
	 * Récupère ou crée le groupe de l'utilisateur identifié
	 * @param int $id
	 * @return \UserBundle\Entity\Groupe
	 */
	private function _getGroup(int $id) {
		if (is_null($id)) {
			$group = $this->getDoctrine()
				->getManager()
				->getRepository("UserBundle:Groupe")
				->findOneBy(["libelle" => "customer"]);
			
			if (!$group) {
				$group = new UserBundle\Entity\Groupe();
				$group
					->setLibelle("customer")
					->setCanBeDeleted(false);
				// Assurer la persistence du groupe
				$entityManager = $this->getDoctrine()->getManager();
				$entityManager->persist($group);
				$entityManager->flush();
			}
		} else {
			// Retourne le groupe de l'utilisateur à partir de son id
			$group = $this->getDoctrine()
				->getManager()
				->getRepository("UserBundle:Groupe")
				->find($id);
		}
		
		return $group;
		
	}
	
	/**
	 * Génère un sel aléatoire
	 * @return string
	 * @todo Créer un service pour gérer ce type de traitement
	 */
	private function _makeSalt(): string {
		$chars = [
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
			"A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
			"0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
			"*", "-", "+", "/", "#", "@", "^", "|"
		];
		
		$saltChars = [];
		
		for ($i = 0; $i < 10; $i++) {
			$random = rand(0, 69);
			$saltChars[$i] = $chars[$random];
		}
		
		return join("",$saltChars);
	}
	
	/**
	 * Retourne le mot de passe renforcé en hash md5
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	private function _createPassword(string $password, string $salt): string {
		return md5($salt.$password.$salt);
	}
	
	/**
	 * Retourne le formatage d'un utilisateur complet
	 * @param Entity $userEntity
	 * @return array
	 */
	private function _format($userEntity) {
		$datas = [];
		
		$datas["id"] = $userEntity->getId();
		
		$datas["userName"] = $userEntity->getLogin();
		$datas["token"] = $this->tokenService->generate($this->_wholeUser);
		
		// Traite le contenu, pour récupérer les données cohérentes
		$jsonContent = $userEntity->getContent();
		
		if ($jsonContent !== null) {
			$datas["firstName"] = $jsonContent->firstname;
			$datas["lastName"] = $jsonContent->lastname;
			$datas["civility"] = $jsonContent->civility;
			$datas["email"] = $jsonContent->email;
			$datas["phone"] = $jsonContent->phone;
		}
		
		// Traite les options de menu
		$group = $userEntity->getGroup();
		
		$datas["group"] = $group->getLibelle();
		
		$menus = [];
		if ($group->getMenus()) {
			foreach($group->getMenus() as $menu) {
				$menus[] = [
					"id" => $menu->getId(),
					"slug" => $menu->getSlug(),
					"region" => $menu->getRegion(),
					"content" => $menu->getContent(),
					"options" => $menu->categoriesToArray()
				];
			}
		}
		
		$datas["menus"] = $menus;
		
		return $datas;
=======
use ReallySimpleJWT\Token;
use ReallySimpleJWT\TokenValidator;


class UserController extends FOSRestController {
	
	/**
	 * Instance du repo des utilisateurs
	 * @var \UserBundle\Repository\UserRepository
	 */
	private $_repository;
	
	/**
	 * Instance d'un utilisateur complet
	 * @var \UserBundle\Entity\User
	 */
	private $_wholeUser;
	
    
	/**
	 * Clé pour la génération du token
	 * @var string
	 */
	private $secret = "K1K@2018!";
	
	/**
	 * Constructeur du contrôleur User
	 */
	public function __construct() {}
	
	/**
	 * @Rest\Put("/register")
	 */
	public function registerAction(Request $request) {
		
		if (!$this->_alreadyExists($request->get("email"))) {
			$this->_wholeUser = new User();
			
			// Génère le sel de renforcement du mot de passe
			$salt = $this->_makeSalt();
			
			$content = [];
			
			$content["lastName"] = $request->get("lastName");
			$content["firstName"] = $request->get("firstName");
			$content["civility"] = $request->get("civility");
			$content["company"] = $request->get("company");
			$content["email"] = $request->get("email");
			$content["phone"] = $request->get("phone");
			
			$this->_wholeUser
				->setLogin($request->get("username"))
				->setSecurityPass($this->_createPassword($request->get("password"), $salt))
				->setSalt($salt)
				->setIsValid(true)
				->setCreatedAt(new \DateTime())
				->setLastLogin(new \DateTime())
				->setValidatedAt(new \DateTime())
				->setContent(json_encode($content))
				->setGroup($this->_getGroup($request->get("group")));
			
			// Fait persister la donnée
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($this->_wholeUser);
			$entityManager->flush();
			
			return new View($this->_format($this->_wholeUser), Response::HTTP_CREATED);
		}
		
		return new View("Un compte avec cet email existe déjà sur ce site", Response::HTTP_CONFLICT);
	}
	
	/**
	 * @Rest\Post("/signin")
	 * @param Request $request Requête envoyée
	 */
	public function signinAction(Request $request) {
		
		if ($request) {
			if (!$this->_checkLogin($request->get("login"))) {
				return new View("Ce nom d'utilisateur est inconnu ou votre compte a été invalidé", Response::HTTP_FORBIDDEN);
			}
			
			if (!$this->_validPassword($request->get("password"))) {
			    return new View("Votre mot de passe est incorrect, veuillez réessayer s'il vous plaît [" . $request->get("login") . "]" , Response::HTTP_FORBIDDEN);
			}
			
			return new View($this->_format($this->_wholeUser), Response::HTTP_OK);
		}
	}
	
	/**
	 * @Rest\Get("user/authentication/{token}")
	 * @param Request $request Requête envoyée
	 */
	public function authentication(Request $request) {
	    return new View($this->_requestAuthentication($request->get('token')), Response::HTTP_OK);
	}
	
	/**
	 * Valide le token utilisateur
	 * @param string $token
	 * @return string|boolean
	 */
	private function _requestAuthentication(string $token) {
	    $isValid = Token::validate($token, $this->secret);
	    
	    if ($isValid) {
	        $validator = new TokenValidator();
	        $validator->splitToken($token)
	           ->validateExpiration()
	           ->validateSignature($this->secret);
	        
	        $payload = $validator->getPayload();
	        
	        $header = $validator->getHeader();
	        
	        return $payload;
	    }
	    
	    return false;
	}
	
	/**
	 * Détermine si le login saisi n'existe pas déjà
	 * @param string $login
	 * @return bool
	 */
	private function _alreadyExists(string $login): bool {
		$this->_wholeUser = $this->getDoctrine()
			->getManager()
			->getRepository("UserBundle:User")
			->findOneBy(["login" => $login]);
		
		if ($this->_wholeUser) {
			return true;
		}
		
		return false;
	}
	/**
	 * Vérifie l'existence du login et sa validité
	 * @return boolean
	 */
	private function _checkLogin(string $login): bool {
		$this->_wholeUser = $this->getDoctrine()
			->getManager()
			->getRepository("UserBundle:User")
			->findOneBy(["login" => $login]);
		
		if ($this->_wholeUser) {
			if ($this->_wholeUser->getIsValid()) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Vérifie le mot de passe avec la clé de renforcement
	 * @param string $password
	 * @return boolean
	 */
	private function _validPassword(string $password): bool {
		$saltedPassword = $this->_wholeUser->getSalt() . $password . $this->_wholeUser->getSalt();
		
		if (md5($saltedPassword) === $this->_wholeUser->getSecurityPass()) {
			return true;
		}
		
		return false;
	}

	/**
	 * Récupère ou crée le groupe de l'utilisateur identifié
	 * @param int $id
	 * @return \UserBundle\Controller\UserBundle\Entity\Groupe
	 */
	private function _getGroup(int $id) {
		if (is_null($id)) {
			$group = $this->getDoctrine()
				->getManager()
				->getRepository("UserBundle:Groupe")
				->findOneBy(["libelle" => "customer"]);
			
			if (!$group) {
				$group = new UserBundle\Entity\Groupe();
				$group
					->setLibelle("customer")
					->setCanBeDeleted(false);
				// Assurer la persistence du groupe
				$entityManager = $this->getDoctrine()->getManager();
				$entityManager->persist($group);
				$entityManager->flush();
			}
		} else {
			// Retourne le groupe de l'utilisateur à partir de son id
			$group = $this->getDoctrine()
				->getManager()
				->getRepository("UserBundle:Groupe")
				->find($id);
		}
		
		return $group;
		
	}
	
	/**
	 * Génère un sel aléatoire
	 * @return string
	 * @todo Créer un service pour gérer ce type de traitement
	 */
	private function _makeSalt(): string {
		$chars = [
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
			"A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
			"0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
			"*", "-", "+", "/", "#", "@", "^", "|"
		];
		
		$saltChars = [];
		
		for ($i = 0; $i < 10; $i++) {
			$random = rand(0, 69);
			$saltChars[$i] = $chars[$random];
		}
		
		return join("",$saltChars);
	}
	
	/**
	 * Retourne le mot de passe renforcé en hash md5
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	private function _createPassword(string $password, string $salt): string {
		return md5($salt.$password.$salt);
	}
	
	/**
	 * Retourne le formatage d'un utilisateur complet
	 * @param Entity $userEntity
	 * @return array
	 */
	private function _format($userEntity) {
		$datas = [];
		
		$datas["id"] = $userEntity->getId();
		
		$datas["userName"] = $userEntity->getLogin();
		$datas["token"] = $this->_generateToken();
		
		// Traite le contenu, pour récupérer les données cohérentes
		$jsonContent = $userEntity->getContent();
		
		if ($jsonContent !== null) {
			$datas["firstName"] = $jsonContent->firstname;
			$datas["lastName"] = $jsonContent->lastname;
			$datas["civility"] = $jsonContent->civility;
			$datas["email"] = $jsonContent->email;
			$datas["phone"] = $jsonContent->phone;
		}
		
		// Traite les options de menu
		$group = $userEntity->getGroup();
		
		$datas["group"] = $group->getLibelle();
		
		$menus = [];
		if ($group->getMenus()) {
			foreach($group->getMenus() as $menu) {
				$menus[] = [
					"id" => $menu->getId(),
					"slug" => $menu->getSlug(),
					"region" => $menu->getRegion(),
					"content" => $menu->getContent(),
					"options" => $menu->categoriesToArray()
				];
			}
		}
		
		$datas["menus"] = $menus;
		
		return $datas;
	}
	
	/**
	 * Génère un Token pour une journée
	 * @return string
	 */
	private function _generateToken(): string {
	    $expirationDate = new \DateTime();
	    $expirationDate->add(new \DateInterval("P1D"));
	    return Token::getToken(
	       $this->_wholeUser->getId() . "_" . $this->_wholeUser->getLogin(),
	       $this->secret,
	       $expirationDate->format('Y-m-d H:i:s'),
	        $this->_wholeUser->getId() . "_" . $this->_wholeUser->getLogin()
	    );
>>>>>>> branch 'master' of https://github.com/dacodemaniak/api_eshop.git
	}
}
