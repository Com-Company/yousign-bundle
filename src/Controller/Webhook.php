<?php

namespace ComCompany\YousignBundle\Controller;

use App\Exception\ApplicationException;
use App\Exception\JsonRequestException;
use App\Manager\Webhook\Yousign;
use ComCompany\YousignBundle\YousignV3\WebhookManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class Webhook
{
    /*
     * @Route("/api/subscription/yousign/webhook", methods={"POST"}, name="subscription.yousign.webhook")
     */
    /* public function __invoke(WebhookManager $webhook, Request $request): Response
     {
         try {
             $webhook->handle($request);
         } catch (ApplicationException $e) {
             throw new JsonRequestException($e->getMessage(), $e->getCode(), $e, $e->getErrors());
         }

         return Response::create([]);
     }*/
}
