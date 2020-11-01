<?php

namespace App\Entity;

use App\Repository\PDFRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PDFRepository::class)
 */
class PDF
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $filename_original;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $filename_MD5;

    /**
     * @ORM\Column(type="integer")
     */
    private $size_in_bytes;

    /**
     * @ORM\Column(type="smallint")
     */
    private $page_cnt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploaded_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilenameOriginal(): ?string
    {
        return $this->filename_original;
    }

    public function setFilenameOriginal(string $filename_original): self
    {
        $this->filename_original = $filename_original;

        return $this;
    }

    public function getFilenameMD5(): ?string
    {
        return $this->filename_MD5;
    }

    public function setFilenameMD5(string $filename_MD5): self
    {
        $this->filename_MD5 = $filename_MD5;

        return $this;
    }

    public function getSizeInBytes(): ?int
    {
        return $this->size_in_bytes;
    }

    public function setSizeInBytes(int $size_in_bytes): self
    {
        $this->size_in_bytes = $size_in_bytes;

        return $this;
    }

    public function getPageCnt(): ?int
    {
        return $this->page_cnt;
    }

    public function setPageCnt(int $page_cnt): self
    {
        $this->page_cnt = $page_cnt;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploaded_at;
    }

    public function setUploadedAt(\DateTimeInterface $uploaded_at): self
    {
        $this->uploaded_at = $uploaded_at;

        return $this;
    }
}
