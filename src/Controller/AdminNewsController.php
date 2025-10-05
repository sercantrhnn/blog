<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin/news', name: 'admin_news_')]
class AdminNewsController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, NewsRepository $newsRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 10;
        $total = $newsRepository->count([]);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) { $page = $totalPages; }
        $offset = ($page - 1) * $perPage;
        $items = $newsRepository->findBy([], ['id' => 'DESC'], $perPage, $offset);

        return $this->render('admin/news/index.html.twig', [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $originalName);
                $newFilename = $safeName . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/news', $newFilename);
                $news->setFilePath('/news/' . $newFilename);
            }
            $em->persist($news);
            $em->flush();
            $this->addFlash('success', 'Haber eklendi.');
            return $this->redirectToRoute('admin_news_index');
        }
        return $this->render('admin/news/new.html.twig', [ 'form' => $form->createView() ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(News $news, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $originalName);
                $newFilename = $safeName . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/news', $newFilename);
                $news->setFilePath('/news/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Haber güncellendi.');
            return $this->redirectToRoute('admin_news_index');
        }
        return $this->render('admin/news/edit.html.twig', [ 'form' => $form->createView(), 'item' => $news ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(News $news, Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $tokenValue = $request->request->get('_token');
        $token = new CsrfToken('delete_news_' . $news->getId(), $tokenValue);
        if ($csrfTokenManager->isTokenValid($token)) {
            $em->remove($news);
            $em->flush();
            $this->addFlash('success', 'Haber silindi.');
        } else {
            $this->addFlash('error', 'Geçersiz CSRF.');
        }
        return $this->redirectToRoute('admin_news_index');
    }
}


