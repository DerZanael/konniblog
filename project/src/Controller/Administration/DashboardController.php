<?php

namespace App\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\ModAction;
use App\Entity\Article;
use App\Entity\Status;

/**
 * @Route("/administration")
 */
class DashboardController extends AbstractController
{
    /**
	 * @Route("/", name="administration")
     * @Route("/dashboard", name="admin_dashboard")
     */
    public function dashboard(Request $request)
    {
		$doctrine = $this->getDoctrine();
		//Get comments
		$commentsToApprove = ($this->isGranted("ROLE_MODERATOR") && $this->getParameter("blog_allowcomments") && $this->getParameter("blog_validatecomments")) ? $doctrine->getRepository(Comment::class)->findBy(["reviewed"=>false], ["date"=>"desc"], 5) : [];
		$commentsLatest = ($this->getParameter("blog_allowcomments")) ? $doctrine->getRepository(Comment::class)->findBy([], ["date"=>"desc"], 10) : []; //All latest comments

		//Get new users
		$usersToApprove = ($this->getParameter("blog_allowusers") && $this->getParameter("blog_validateusers") && $this->isGranted("ROLE_ADMIN")) ? $doctrine->getRepository(User::class)->findBy(["valid"=>false], ["registrationDate"=>"desc"], 5) : [];

		//Latest mod actions
		$modActionsLatest = ($this->isGranted("ROLE_ADMIN")) ? $doctrine->getRepository(ModAction::class)->findBy([], ["date"=>"desc"], 5) : [];

		//Latest articles
		$articlesLatest = ($this->isGranted("ROLE_ADMIN")) ? $doctrine->getRepository(Article::class)->findBy([], ["creationDate"=>"desc"], 5) : [];
		$draftLatest = ($this->isGranted(["ROLE_ADMIN"])) ? $doctrine->getRepository(Article::class)->findBy(["status"=>$doctrine->getRepository(Status::class)->findOneByCode(Status::CODE_PUBLISHED)], ["editionDate"=>"desc"], 5) : [];

        return $this->render('administration/dashboard/index.html.twig', [
            "commentsToApprove"=>$commentsToApprove,
			"commentsLatest"=>$commentsLatest,
			"usersToApprove"=>$usersToApprove,
			"modActionsLatest"=>$modActionsLatest,
			"articlesLatest"=>$articlesLatest,
			"draftLatest"=>$draftLatest,
        ]);
    }
}
