<?php


namespace App\Controller;


use App\Formatter\PdfFormatter;
use App\Repository\PDFRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PDFController extends AbstractController
{
    /**
     * @Route ("/documents", name="documents", methods={"GET"})
     * @param Request $request
     * @param PDFRepository $repository
     * @param PdfFormatter $formatter
     * @return Response
     */
    public function getDocuments(Request $request, PDFRepository $repository, PdfFormatter $formatter): Response
    {
        $pageNr = $request->get('page', 0);
        $offset = $pageNr * 20; //limit should be 20

        $response = $repository->findByOffset($offset);
        $list = [];
        if (false === empty($response)) {
            $list = $formatter->format($response);
        }

        return $this->render('index.html.twig', ['items' => $list]);
    }
}