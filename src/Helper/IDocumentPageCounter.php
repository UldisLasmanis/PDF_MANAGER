<?php


namespace App\Helper;


interface IDocumentPageCounter
{
    public function count(string $filePath): int;
}