<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class Document
{
    private string $name;
    private string $path;
    private string $nature;
    private ?string $id;
    private ?string $mimeType;

    public function __construct(string $name, string $path, string $nature = 'signable_document', ?string $mimeType = null, ?string $id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->nature = $nature;
        $this->path = $path;
        $this->mimeType = $mimeType;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getNature(): string
    {
        return $this->nature;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setName(string $name): Document
    {
        $this->name = $name;

        return $this;
    }

    public function setPath(string $path): Document
    {
        $this->path = $path;

        return $this;
    }

    public function setNature(string $nature): Document
    {
        $this->nature = $nature;

        return $this;
    }

    public function setId(?string $id): Document
    {
        $this->id = $id;

        return $this;
    }

    public function setMimeType(?string $mimeType): Document
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
