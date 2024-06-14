<?php

namespace ComCompany\YousignBundle\Controller;

use App\Exception\ApplicationException;
use App\Exception\JsonRequestException;
use App\Manager\Webhook\Yousign;
use ComCompany\YousignBundle\Service\YousignV3\WebhookManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            throw new \Exception($e->getMessage(), $e->getCode(), $e, $e->getErrors());
        }

        return new Response();
    }
}
