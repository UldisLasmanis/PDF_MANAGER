<?php

namespace App\Entity;

use App\Repository\ThumbnailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThumbnailRepository::class)
 */
class Thumbnail
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
    private $filename;

    /**
     * @ORM\Column(type="smallint")
     */
    private $page_nr;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploaded_at;

    /**
     * @ORM\Column(type="integer")
     */
    private $size_in_bytes;

    /**
     * @ORM\Column(type="integer")
     */
    private $pdf_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getPageNr(): ?int
    {
        return $this->page_nr;
    }

    public function setPageNr(int $page_nr): self
    {
        $this->page_nr = $page_nr;

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

    public function getSizeInBytes(): ?int
    {
        return $this->size_in_bytes;
    }

    public function setSizeInBytes(int $size_in_bytes): self
    {
        $this->size_in_bytes = $size_in_bytes;

        return $this;
    }

    public function getPdfId(): ?int
    {
        return $this->pdf_id;
    }

    public function setPdfId(int $pdf_id): self
    {
        $this->pdf_id = $pdf_id;

        return $this;
    }
}
