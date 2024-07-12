<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class Document
{
    private string $id;
    private string $name;
    private string $path;
    private string $mimeType;

    public function __construct(string $id, string $name, string $path, string $mimeType)
    {
        $this->id = $id;
        $this->name = $name;
        $this->path = $path;
        $this->mimeType = $mimeType;
    }

    public function getId(): string
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

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /** @return array<string, mixed> */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
