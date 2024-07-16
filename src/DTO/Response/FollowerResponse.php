<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class FollowerResponse
{
    private string $email;

    private string $locale;

    private ?string $followerLink;

    public function __construct(string $email, string $locale, ?string $followerLink)
    {
        $this->email = $email;
        $this->locale = $locale;
        $this->followerLink = $followerLink;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getFollowerLink(): ?string
    {
        return $this->followerLink;
    }
}
