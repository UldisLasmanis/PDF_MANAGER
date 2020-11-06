<?php

namespace App\Entity;

use App\Repository\AttachmentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttachmentRepository::class)
 * @ORM\Table(name="attachment",indexes={@ORM\Index(name="i_pdf_id", columns={"pdf_id"})})
 */
class Attachment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $filename_original;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $filename_hash;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $size_in_bytes;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $uploaded_at;

    /**
     * @ORM\Column(name="pdf_id", type="integer")
     */
    private ?int $pdf_id;

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

    public function getFilenameHash(): ?string
    {
        return $this->filename_hash;
    }

    public function setFilenameHash(string $filename_hash): self
    {
        $this->filename_hash = $filename_hash;

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

    public function getUploadedAt(): ?DateTimeInterface
    {
        return $this->uploaded_at;
    }

    public function setUploadedAt(DateTimeInterface $uploaded_at): self
    {
        $this->uploaded_at = $uploaded_at;

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
