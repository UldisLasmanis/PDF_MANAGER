<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PDFController extends AbstractController
{
    public function index()
    {
        return $this->render('index.html.twig');
    }
}