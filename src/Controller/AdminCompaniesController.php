<?php

namespace App\Controller;

use App\Entity\Companies;
use App\Form\CompaniesType;
use App\Repository\CompaniesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin/companies', name: 'admin_companies_')]
class AdminCompaniesController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, CompaniesRepository $companiesRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 10;
        $total = $companiesRepository->count([]);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) { $page = $totalPages; }
        $offset = ($page - 1) * $perPage;
        $items = $companiesRepository->findBy([], ['id' => 'DESC'], $perPage, $offset);

        return $this->render('admin/companies/index.html.twig', [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $company = new Companies();
        $form = $this->createForm(CompaniesType::class, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $originalName);
                $newFilename = $safeName . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/companies', $newFilename);
                $company->setFilePath('/companies/' . $newFilename);
            }
            $em->persist($company);
            $em->flush();
            $this->addFlash('success', 'Firma eklendi.');
            return $this->redirectToRoute('admin_companies_index');
        }
        return $this->render('admin/companies/new.html.twig', [ 'form' => $form->createView() ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Companies $company, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CompaniesType::class, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $originalName);
                $newFilename = $safeName . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/companies', $newFilename);
                $company->setFilePath('/companies/' . $newFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Firma güncellendi.');
            return $this->redirectToRoute('admin_companies_index');
        }
        return $this->render('admin/companies/edit.html.twig', [ 'form' => $form->createView(), 'item' => $company ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Companies $company, Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $tokenValue = $request->request->get('_token');
        $token = new CsrfToken('delete_company_' . $company->getId(), $tokenValue);
        if ($csrfTokenManager->isTokenValid($token)) {
            $em->remove($company);
            $em->flush();
            $this->addFlash('success', 'Firma silindi.');
        } else {
            $this->addFlash('error', 'Geçersiz CSRF.');
        }
        return $this->redirectToRoute('admin_companies_index');
    }
}


