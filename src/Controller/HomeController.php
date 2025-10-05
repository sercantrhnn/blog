<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use App\Repository\CompaniesRepository;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index', methods: ['GET'])]
    public function index(NewsRepository $newsRepository, BlogPostRepository $blogPostRepository, CompaniesRepository $companiesRepository): Response
    {
        $latestNews = $newsRepository->findBy(['filePath' => null], ['id' => 'DESC'], 0); // placeholder to satisfy static analysis
        // Fetch latest items (with or without image)
        $latestNews = $newsRepository->findBy([], ['id' => 'DESC'], 4);
        $latestBlogs = $blogPostRepository->findBy([], ['id' => 'DESC'], 4);
        $featuredCompanies = $companiesRepository->findBy([], ['id' => 'DESC'], 8);

        return $this->render('home/index.html.twig', [
            'latestNews' => $latestNews,
            'latestBlogs' => $latestBlogs,
            'featuredCompanies' => $featuredCompanies,
        ]);
    }
}


