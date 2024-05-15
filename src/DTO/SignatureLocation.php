<?php

namespace ComCompany\YousignBundle\DTO;

use ComCompany\SignatureContract\DTO\Location;

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
}
