<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Yaml\Yaml;
use Cocur\Slugify\SlugifyInterface;
use App\Form\InstallationType;

use App\Entity\Rank;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Status;

/**
 * @Route("/installation")
 */
class InstallationController extends AbstractController
{
	private $installdist; //path to install file
	private $websiteconfig; //path to config file
	const INSTALLATION_FILE = "installation.dist";
	const CONFIG_FILE = "konniblog.yaml";

	public function __construct(ParameterBagInterface $bag) {
		$this->installdist = $bag->get("public_dir")."/".$this::INSTALLATION_FILE;
		$this->websiteconfig = $bag->get("project_dir")."/config/".$this::CONFIG_FILE;
	}

    /**
     * @Route("/start", name="installation")
     */
    public function installation(Request $request, UserPasswordEncoderInterface $encoder, SessionInterface $session, SlugifyInterface $slugifier)
    {
		if(!file_exists($this->installdist)) //Redirect to home if the file does not exists
			return $this->redirectToRoute("homepage");

		//Installation file
		@chown($this->installdist, "www-data");
		@chmod($this->installdist, 0777);
		$writable_install = (substr(sprintf('%o', fileperms($this->installdist)), -4) == "0777"); //checks if the installation.dist file is deletable

		//Config file
		@chown($this->websiteconfig, "www-data");
		@chmod($this->websiteconfig, 0775);
		$writable_config = (substr(sprintf('%o', fileperms($this->websiteconfig)), -4) == "0775"); //checks if config/konniblog.yaml is writeable

		$form = $this->createForm(InstallationType::class, null);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			//Database setup
			//Tests are made to check if the script has been executed before and avoid data duplication for ranks and status
			$em = $this->getDoctrine()->getManager();

			//Set up user ranks
			$nbranks = $em->getRepository(Rank::class)->createQueryBuilder("rnk")->select("COUNT(rnk.id)")->getQuery()->getSingleScalarResult();
			if($nbranks ==0) {
				$ranks = ["SUPERADMIN", "ADMIN", "MODERATOR", "USER"];
				$userrank = null;
				foreach($ranks as $rank){
					$bddrank = new Rank();
					$bddrank->setCode($rank);
					$em->persist($bddrank);
					if($rank == "SUPERADMIN")
						$userrank = $bddrank;
				}
				$em->flush();
			}

			//Set up article status
			$nbstatus = $em->getRepository(Status::class)->createQueryBuilder("st")->select("COUNT(st.id)")->getQuery()->getSingleScalarResult();
			if($nbstatus == 0) {
				$statuses = ["Published", "Draft", "Archived", "Deleted"];
				foreach($statuses as $status) {
					$statut = new Status();
					$statut->setLabel($status);
					$statut->setCode(strtoupper($status));
					$em->persist($statut);
				}
				$em->flush();
			}

			$slugcat = $slugifier->slugify($data["category"]);
			$category_check = $em->getRepository(Category::class)->findOneBy(["slug"=>$slugcat]);
			if($category_check === null) {
				$category = new Category();
				$category->setLabel($data["category"]);
				$category->setSlug($slugcat);
				$em->persist($category);
				$em->flush();
			}

			$slugtag = $slugifier->slugify($data["tag"]);
			$tag_check = $em->getRepository(Tag::class)->findOneBy(["slug"=>$slugtag]);
			if($tag_check === null) {
				$tag = new Tag();
				$tag->setLabel($data["tag"]);
				$tag->setSlug($slugtag);
				$em->persist($tag);
				$em->flush();
			}

			//Set up user
			$nbusers = $em->getRepository(User::class)->createQueryBuilder("usr")->select("COUNT(usr.id)")->getQuery()->getSingleScalarResult();
			if($nbusers == 0) { //No new superadmin creation :p
				$user = new User();
				$user->setFirstname($data["firstname"]);
				$user->setLastname($data["lastname"]);
				$user->setEmail($data["email"]);
				$user->setUsername($data["username"]);
				$user->setRank($userrank);
				$user->setConfirmed(true);
				$user->setValid(true);
				$user->setBanned(false);
				$user->setRegistrationDate(new \Datetime("now"));
				$user->setLastOnline(new \Datetime("now"));
				$user->setPassword($encoder->encodePassword($user, $data["password"]));
				$em->persist($user);
				$em->flush();
			}

			//Update configuration
			$conf = Yaml::parseFile($this->websiteconfig)["parameters"]; //Get default configuration from yaml file
			foreach($data as $key=>$value) {
				if(\array_key_exists($key, $conf)) { //Key in configuration file
					$conf[$key] = $value;
				}
			}
			$conf["blog_file_edited"] = true; //manual update
			$yaml = Yaml::dump(["parameters"=>$conf]); //regenerate yaml contents

			@\file_put_contents($this->websiteconfig, $yaml); //replace config
			@unlink($this->installdist); //delete installation file

			$session->set("installation_done", true); //confirms that the user comes from the install script
			$session->set("yaml", $yaml); //temp yaml content to display
			$session->set("check_username", $data["username"]); //username to check

			return $this->redirectToRoute("installation_result");
		}

        return $this->render('installation/installation_form.html.twig', [
            "form"=>$form->createView(),
			"writable_install"=>$writable_install,
			"writable_config"=>$writable_config,
        ]);
    }

	/**
	 * @Route("/result", name="installation_result")
	 */
	public function installationResult(Request $request, SessionInterface $session) {
		$installation_done = $session->get("installation_done", false); //checks if user comes from installation()
		if(!$installation_done)
			return $this->redirectToRoute("homepage");

		//Check for errors in the process
		$error_remove_installationdist = file_exists($this->installdist); //installation.dist still exists
		$error_edit_websiteconfig = !$this->getParameter("blog_file_edited"); //konniblog.yaml not edited
		$user = ($session->get("check_username", null) !== null) ? $this->getDoctrine()->getRepository(User::class)->findOneBy(["username"=>$session->get("check_username")]) : null;
		$error_database = ($user === null); //user does not exists
		$yaml_contents = $session->get("yaml", null);

		//remove session junk
		$session->remove("installation_done");
		$session->remove("yaml");
		$session->remove("check_username");

		return $this->render("installation/installation_result.html.twig", [
			"error_remove_installationdist" => $error_remove_installationdist,
			"error_edit_websiteconfig" => $error_edit_websiteconfig,
			"error_database" => $error_database,
			"yaml_contents" => $yaml_contents,
		]);
	}
}
