<?php

namespace ComCompany\YousignBundle\Controller;

use ComCompany\SignatureContract\Exception\ClientException;
use ComCompany\YousignBundle\Service\WebhookManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Webhook
{
    private WebhookManager $manager;

    public function __construct(WebhookManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(Request $request): Response
    {
        try {
            $this->manager->handle($request);
        } catch (\Exception $e) {
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response();
    }
}
