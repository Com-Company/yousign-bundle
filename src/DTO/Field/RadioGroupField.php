<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Field;

class RadioGroupField extends Field
{
    public const TYPE = 'radio_group';

    public string $name;
    public bool $optional;
    /** @var list<array{name: string, x: int, y: int, size: int}> */
    public array $radios;

    /**
     * @param list<array{name: string, x: int, y: int, size: int}> $radios
     */
    public function __construct(int $page, string $name, array $radios, bool $optional = false)
    {
        parent::__construct($page, self::TYPE);
        $this->name = $name;
        $this->radios = $radios;
        $this->optional = $optional;
    }
}
