<?php

namespace ComCompany\YousignBundle\DTO\Field;

class SignatureField extends Field
{
    public int $x;
    public int $y;
    public int $width;
    public int $height;

    public function __construct(int $x, int $y, int $page, string $type, int $width, int $height)
    {
        parent::__construct($page, $type);
        $this->x = $x;
        $this->y = $y;
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
