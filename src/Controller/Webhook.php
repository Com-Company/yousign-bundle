<?php

namespace ComCompany\YousignBundle\Controller;

use App\Exception\ApplicationException;
use App\Exception\JsonRequestException;
use App\Manager\Webhook\Yousign;
use ComCompany\YousignBundle\Service\YousignV3\WebhookManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

final class Webhook
{
    private WebhookManager $manager;

    public function __construct(WebhookManager $manager)
    {
        $this->manager = $manager;
    }
    /**
     * @Route("/api/subscription/yousign/webhook", methods={"POST"}, name="subscription.yousign.webhook")
     */
    public function __invoke(Request $request): Response
    {
        try {
            $this->manager->handle($request);
        } catch (ApplicationException $e) {
            throw new JsonRequestException($e->getMessage(), $e->getCode(), $e, $e->getErrors());
        }

        return Response::create([]);
    }
}
