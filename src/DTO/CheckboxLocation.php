<?php

namespace ComCompany\YousignBundle\DTO;

class CheckboxLocation extends Location
{
    public int $size;
    public bool $optional;
    public bool $checked;

    public function __construct(int $x, int $y, int $page, string $type, int $size = 16, bool $optional = false, bool $checked = false)
    {
        parent::__construct($x, $y, $page, $type);
        $this->size = $size;
        $this->optional = $optional;
        $this->checked = $checked;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            get_object_vars($this),
        );
    }
}
