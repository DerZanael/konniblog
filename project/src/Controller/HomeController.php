<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Article;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @Route("/home", name="home")
     */
    public function index(Request $request)
    {
		$doctrine = $this->getDoctrine();
		$featured = $doctrine->getRepository(Article::class)->getFeatured(3);
		$latest = $doctrine->getRepository(Article::class)->getLatest(5);
		$nbArticles = $doctrine->getRepository(Article::class)->numPublished();

        return $this->render('home/index.html.twig', [
            "featuredArticles" => $featured,
			"latestArticles" => $latest,
			"numberArticles" => $nbArticles,
        ]);
    }


	public function menu(Request $request) {

		return $this->render("home/menu.html.twig", [
			"routename"=>$request->get("routename"),
		]);
	}
}
