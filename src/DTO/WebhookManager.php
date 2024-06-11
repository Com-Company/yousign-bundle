<?php

namespace ComCompany\YousignBundle\DTO;

use ComCompany\YousignBundle\DTO\YousignV3\ProcedureDTO;
// use ComCompany\YousignBundle\DTO\YousignV3\WebhookManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class WebhookManager
{
    /*private ?string $uri = null;

    private ?WebhookManagerInterface $manager = null;

    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }

    public function setManager(?WebhookManagerInterface $manager): void
    {
        $this->manager = $manager;
    }*/

    public function handle(Request $request): void
    {
        /**$procedure = new ProcedureDTO();
        $procedure->id = $request->get('procedure_id');

        $this->manager->handle($procedure);
         * */
    }
}
