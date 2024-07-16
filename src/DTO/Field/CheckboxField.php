<?php

namespace ComCompany\YousignBundle\DTO\Field;

class CheckboxField extends Field
{
    public int $x;
    public int $y;
    public int $size;
    public bool $optional;
    public bool $checked;

    public function __construct(int $x, int $y, int $page, string $type, int $size = 16, bool $optional = false, bool $checked = false)
    {
        parent::__construct($page, $type);
        $this->x = $x;
        $this->y = $y;
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
