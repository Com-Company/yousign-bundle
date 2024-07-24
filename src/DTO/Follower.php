<?php

namespace ComCompany\YousignBundle\DTO;

class Follower
{
    private string $email;

    private string $locale;

    public function __construct(string $email, string $locale = 'fr')
    {
        $this->email = $email;
        $this->locale = $locale;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
