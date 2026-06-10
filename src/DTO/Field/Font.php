<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Field;

class Font
{
    /**
     * @var string Font family value between 'Inconsolata', 'Open Sans', 'Lato', 'Raleway', 'Merriweather',
     *             'EB Garamond', 'Comic Neue', 'Monaco', 'Helvetica', 'Courier' or 'Times Roman'
     */
    public string $family;

    /** @var string Hexadecimal color value */
    public string $color;

    /** @var int Font size value between 7 and 96 */
    public int $size;

    public ?FontVariant $variants;

    public function __construct(
        string $family,
        string $color,
        int $size,
        ?FontVariant $variants = null
    ) {
        $this->family = $family;
        $this->color = $color;
        $this->size = $size;
        $this->variants = $variants ?? new FontVariant();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
