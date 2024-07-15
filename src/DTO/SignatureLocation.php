<?php

namespace ComCompany\YousignBundle\DTO;

class SignatureLocation extends Location
{
    public int $width;
    public int $height;

    public function __construct(int $x, int $y, int $page, string $type, int $width, int $height)
    {
        parent::__construct($x, $y, $page, $type);
        $this->width = $width;
        $this->height = $height;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            get_object_vars($this),
        );
    }
}
