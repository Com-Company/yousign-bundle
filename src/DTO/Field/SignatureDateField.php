<?php

namespace ComCompany\YousignBundle\DTO\Field;

class SignatureDateField extends Field
{
    public int $x;

    public int $y;

    public ?Font $font = null;

    /**
     * @var string|null Date format value between 'dd/MM/yyyy', 'dd-MM-yyyy', 'dd.MM.yyyy', 'yyyy-MM-dd',
     *                  'MM/dd/yyyy', 'dd MMMM yyyy', 'MMMM dd, yyyy' or 'MMM dd, yyyy'
     */
    public ?string $dateFormat = null;

    /** @var string|null Time format value between null, 'HH:mm' or 'hh:mm a' */
    public ?string $timeFormat = null;

    public function __construct(
        int $x,
        int $y,
        int $page,
        ?Font $font = null,
        ?string $dateFormat = 'dd/MM/yyyy',
        ?string $timeFormat = null
    ) {
        parent::__construct($page, 'signature_date');
        $this->x = $x;
        $this->y = $y;
        $this->font = $font;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            get_object_vars($this),
        );
    }
}
