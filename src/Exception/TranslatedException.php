<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\Exception;

class TranslatedException extends YousignException
{
    private string $originalMessage;

    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
        array $errors = [],
        ?string $originalMessage = null
    ) {
        parent::__construct($this->getTranslatedMessage($errors, $previous), $code, $previous, $errors);
        $this->originalMessage = $originalMessage ?? $message;
    }

    public function getOriginalMessage(): string
    {
        return $this->originalMessage;
    }

    public function setOriginalMessage(string $originalMessage): void
    {
        $this->originalMessage = $originalMessage;
    }

    private function getTranslatedMessage(array $errors, ?\Throwable $previous = null): string
    {
        $invalidParams = $errors['errors'] ?? [];

        if ($previous && get_class($previous) !== ApiException::class) {
            return 'Une erreur est survenue lors de votre demande.';
        }

        if (!isset($errors['message']) || $errors['message'] !== 'You have some invalid params in your payload.') {
            return 'Une erreur est survenue lors de votre demande.';
        }

        if (empty($invalidParams)) {
            return 'Votre demande contient des paramètres invalides.';
        }

        $first = true;
        $errorMessages = array_map(
            static function (array $param) use (&$first) {
                $prefix = $first ? '' : '- ';
                $first = false;
                return $prefix . self::translateReason($param['reason'] ?? '');
            },
            $invalidParams
        );

        return implode("\n", $errorMessages);
    }

    private static function translateReason(string $reason): string
    {
        $translations = [
            // verification compte bancaires
            '/^.*image width is too small.*?(\d+px).*?(\d+px).*$/i'
            => 'La largeur de l\'image est trop petite ($1). La largeur minimale attendue est de $2.',
            '/^.*image height is too small.*?(\d+px).*?(\d+px).*$/i'
            => 'La hauteur de l\'image est trop petite ($1). La hauteur minimale attendue est de $2.',
            '/^.*must not be blank.*$/i'
            => 'Ce champ ne peut pas être vide.',
            '/^.*is not a valid.*$/i'
            => 'La valeur fournie n\'est pas valide.',
            '/^.*file.*too large.*$/i'
            => 'Le fichier est trop volumineux.',
            '/^.*file.*too small.*$/i'
            => 'Le fichier est trop petit.',
            '/^.*invalid.*format.*$/i'
            => 'Le format du fichier est invalide.',
            '/^.*too many pixels.*?(\d+).*?(\d+).*$/i'
            => 'L\'image contient trop de pixels ($1). Le nombre maximum attendu est de $2.',
            '/^.*Please upload a valid extension.*$/i'
            => 'Format du fichier invalide, veuillez utiliser un des formats suivants: PDF, JPG, JPEG ou PNG.',
        ];

        foreach ($translations as $pattern => $replacement) {
            if (preg_match($pattern, $reason)) {
                return preg_replace($pattern, $replacement, $reason) ?? $reason;
            }
        }

        return $reason;
    }
}
