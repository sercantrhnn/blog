<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/posts', name: 'public_blog_index', methods: ['GET'])]
    public function index(Request $request, BlogPostRepository $blogPostRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 12;
        $category = $request->query->get('category');

        $criteria = [];
        if ($category) {
            $criteria['category'] = $category;
        }

        $total = $blogPostRepository->count($criteria);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) { $page = $totalPages; }
        $offset = ($page - 1) * $perPage;
        $posts = $blogPostRepository->findBy($criteria, ['id' => 'DESC'], $perPage, $offset);

        $categories = [
            'Hammadde & Kimyasallar',
            'Maden & Mineraller',
            'Metal & Alaşımlar',
            'Hizmet & Ofis & Vasıta',
            'Sanayi Makineleri & Üretim Hattı',
            'Tarım & Gıda & Hayvancılık Makineleri',
        ];

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    #[Route('/posts/{id}', name: 'public_blog_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, BlogPostRepository $blogPostRepository): Response
    {
        $post = $blogPostRepository->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Blog yazısı bulunamadı');
        }

        $related = $blogPostRepository->findBy([
            'category' => $post->getCategory(),
        ], ['id' => 'DESC'], 2);

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}


