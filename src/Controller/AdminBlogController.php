<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin/blog', name: 'admin_blog_')]
class AdminBlogController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, BlogPostRepository $blogPostRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 10;
        $total = $blogPostRepository->count([]);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $blogPosts = $blogPostRepository->findBy([], ['id' => 'DESC'], $perPage, $offset);

        return $this->render('admin/blog/index.html.twig', [
            'blogPosts' => $blogPosts,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $blogPost = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $originalName);
                $newFilename = $safeName . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/blogs', $newFilename);
                $blogPost->setFilePath('/blogs/' . $newFilename);
            }

            $em->persist($blogPost);
            $em->flush();

            $this->addFlash('success', 'Blog yazısı oluşturuldu.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(BlogPost $blogPost, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $originalName);
                $newFilename = $safeName . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/blogs', $newFilename);
                $blogPost->setFilePath('/blogs/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Blog yazısı güncellendi.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/edit.html.twig', [
            'form' => $form->createView(),
            'blogPost' => $blogPost,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(BlogPost $blogPost, Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $submittedToken = $request->request->get('_token');
        $token = new CsrfToken('delete_blog_' . $blogPost->getId(), $submittedToken);

        if ($csrfTokenManager->isTokenValid($token)) {
            $em->remove($blogPost);
            $em->flush();
            $this->addFlash('success', 'Blog yazısı silindi.');
        } else {
            $this->addFlash('error', 'Geçersiz CSRF token.');
        }

        return $this->redirectToRoute('admin_blog_index');
    }
}


