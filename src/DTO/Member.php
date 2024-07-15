<?php

namespace ComCompany\YousignBundle\DTO;

class Member
{
    private ?string $id;

    private string $firstName;

    private string $lastName;

    private string $email;

    private string $phone;

    /** @var array<string, mixed> */
    private array $extraConfig;

    private ?MemberConfig $config;

    /** @var array<int, array<string, mixed>> */
    public array $fields = [];

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, mixed>             $extraConfig
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        array $fields = [],
        array $extraConfig = [],
        ?MemberConfig $config = null,
        ?string $id = null
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->fields = $fields;
        $this->extraConfig = $extraConfig;
        $this->config = $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function formattedForApi(): array
    {
        return [
            'info' => [
                'first_name' => $this->getFirstName(),
                'last_name' => $this->getLastName(),
                'email' => $this->getEmail(),
                'phone_number' => $this->getPhone(),
                'locale' => 'fr',
            ],
            'fields' => $this->fields,
            'signature_level' => $this->getConfig()->signatureLevel ?? null,
            'signature_authentication_mode' => $this->getConfig()->signatureAuthentificationMode ?? null,
        ] + $this->getExtraConfig();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(get_object_vars($this), [
            'config' => $this->getConfig() ? $this->getConfig()->toArray() : [],
        ]);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    /** @return array<string, mixed> */
    public function getExtraConfig(): array
    {
        return $this->extraConfig;
    }

    public function getConfig(): ?MemberConfig
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $field
     */
    public function addField(array $field): void
    {
        $this->fields[] = $field;
    }
}
