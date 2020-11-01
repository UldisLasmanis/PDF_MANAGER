<?php


namespace App\Formatter;


interface IFormatter
{
    public function format(array $items): array;
    public function getTargetDir(): string;
}