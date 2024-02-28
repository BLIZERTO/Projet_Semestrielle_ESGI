<?php

namespace App\Controller;

require '../vendor/autoload.php';

use App\Entity\DrivingSchool;
use App\Entity\Invoice;
use App\Entity\Contract;
use App\Entity\Client;
use App\Form\InvoiceType;
use App\Form\SearchType;
use App\Model\SearchData;
use App\Repository\InvoiceRepository;
use App\Service\PdfService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/', name: 'app_invoice_index', methods: ['GET'])]
    #[Security('is_granted("ROLE_BOSS")')]
    public function index(InvoiceRepository $invoiceRepository, Request $request): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');

        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchData->page = $request->query->getInt('page', 1);
            $invoices = $invoiceRepository->findByInvoiceName($searchData->q);

            return $this->render('invoice/index.html.twig', [
                'form' => $form->createView(),
                'invoices' => $invoices
            ]);
        }

        return $this->render('invoice/index.html.twig', [
            'form' => $form->createView(),
            'invoices' => $invoiceRepository->findByDrivingSchoolId($schoolSelected),
            'drivingSchool' => $schoolSelected
        ]);
    }

    // if ($this->isGranted("ROLE_ADMIN")) {
        //     return $this->render('invoice/index.html.twig', [
        //         'invoices' => $invoiceRepository->findAll(),
        //         'drivingSchool' => $schoolSelected,
        //     ]);
        // } else {
        //     $filtredInvoices = [];
        //     $invoices = $invoiceRepository->findAll();

        //     foreach($invoices as $invoice) {
        //         if ($this->getUser()->getDrivingSchools()->contains($invoice->getDrivingSchool())) {
        //             array_push($filtredInvoices, $invoice);
        //         }
        //     }

        // }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_BOSS")')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');
        $drivingSchool = $entityManager->getRepository(DrivingSchool::class)->findOneById($schoolSelected);

        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice, array('drivingSchool' => $drivingSchool));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setDrivingSchool($drivingSchool);
            $invoice->setDate(new DateTimeImmutable());

            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'drivingSchool' => $schoolSelected,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_invoice_new_id_client', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_BOSS")')]
    public function newClient(Request $request, EntityManagerInterface $entityManager, Client $client): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');
        $drivingSchool = $entityManager->getRepository(DrivingSchool::class)->findOneById($schoolSelected);

        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice, array('drivingSchool' => $drivingSchool));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setDrivingSchool($drivingSchool);
            $invoice->setDate(new DateTimeImmutable());

            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'drivingSchool' => $schoolSelected,
            'form' => $form,
            'idClient' => $client->getId()
        ]);
    }

    #[Route('/convert/{id}/client/{clientId}', name: 'app_invoice_convert', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_BOSS")')]
    public function convert(Contract $contract, int $clientId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');
        
        $drivingSchool = $entityManager->getRepository(DrivingSchool::class)->findOneById($schoolSelected);
        $client = $entityManager->getRepository(Client::class)->findOneById($clientId);
        
        $invoice = new Invoice();
        $invoice->setName($contract->getName());
        $invoice->setDescription($contract->getDescription());
        $invoice->setPrice($contract->getPrice());
        $invoice->setDrivingSchool($drivingSchool);
        $invoice->setClient($client);

        $form = $this->createForm(InvoiceType::class, $invoice, ["drivingSchool" => $drivingSchool]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'drivingSchool' => $schoolSelected,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(Request $request, Invoice $invoice): Response
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');

        return $this->render('invoice/show.html.twig', [
            'drivingSchool' => $schoolSelected,
            'invoice' => $invoice,
        ]);
    }

    #[Route('/pdf/{id}', name: 'app_invoice_pdf_show', methods: ['GET'])]
    public function showPdf(Request $request, Invoice $invoice, PdfService $pdfService)
    {
        $session = $request->getSession();
        $schoolSelected = $session->get('driving-school-selected');

        $html = $this->render('invoice/pdf_invoice.html.twig', [
            'drivingSchool' => $schoolSelected,
            'invoice' => $invoice,
        ]);

        $pdfService->showPdfFile($html);
    }

    #[Route('/{id}', name: 'app_invoice_delete', methods: ['POST'])]
    #[Security('is_granted("ROLE_ADMIN") or (is_granted("ROLE_BOSS") && user.getDrivingSchools().contains(invoice.getDrivingSchool()))')]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager, DrivingSchool $idS): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_index', ["idS" => $idS->getId()], Response::HTTP_SEE_OTHER);
    }
}
