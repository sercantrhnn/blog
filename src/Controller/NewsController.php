<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'public_news_index', methods: ['GET'])]
    public function index(Request $request, NewsRepository $newsRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 12;
        $category = $request->query->get('category');

        $criteria = [];
        if ($category) {
            $criteria['category'] = $category;
        }

        $total = $newsRepository->count($criteria);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) { $page = $totalPages; }
        $offset = ($page - 1) * $perPage;
        $items = $newsRepository->findBy($criteria, ['id' => 'DESC'], $perPage, $offset);

        $categories = [
            'Hammadde & Kimyasallar',
            'Maden & Mineraller',
            'Metal & Alaşımlar',
            'Hizmet & Ofis & Vasıta',
            'Sanayi Makineleri & Üretim Hattı',
            'Tarım & Gıda & Hayvancılık Makineleri',
        ];

        return $this->render('news/index.html.twig', [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    #[Route('/news/{id}', name: 'public_news_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, NewsRepository $newsRepository): Response
    {
        $item = $newsRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Haber bulunamadı');
        }

        $related = $newsRepository->findBy([
            'category' => $item->getCategory(),
        ], ['id' => 'DESC'], 2);

        return $this->render('news/show.html.twig', [
            'item' => $item,
            'related' => $related,
        ]);
    }
}


