<?php


namespace App\Service;


abstract class Remover implements IFileRemover
{
    protected string $targetDir;
}