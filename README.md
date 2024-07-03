# Yousign V3 Signature Interface
Component Symfony pour signature Yousign V3

Auteur : Andrei DACIN (adacin@com-company.fr); Valentin DO ESPIRITO SANTO (vdoespiritosanto@com-company.fr)
## installation avec composer
Les projets doivent avoir une dépendance de 
```bash
    "require": {
      "com-company/symfony-contract-signature": "*",
      "com-company/yousign-bundle": "*",
    },
     "repositories": [{
        "type": "vcs",
        "url": "https://github.com/Com-Company/yousign-bundle.git"
    }, {
        "type": "vcs",
        "url": "https://github.com/Com-Company/symfony-contract-signature.git"
    }],
```

Exécutez ensuite
```bash
$ composer require com-company/symfony-signature-interface
```
## Configuration au sein de votre projet
### 1 . Déclaration des varaible d'environnement


    ###> yousign ###
    #dev URI = 'https://staging-api.yousign.com' 
    #prod URI = 'https://api.yousign.com'
    YOUSIGN_V2_URI=''
    YOUSIGN_V2_APP_URI=''
    YOUSIGN_V2_TOKEN=''
    YOUSIGN_V2_ACCESS_KEY='' #que si vous gérez les webhook yousign
    ###< yousign ###
    ###> yousign V3 ###
    #dev URI = 'https://api-sandbox.yousign.app/v3/'
    #prod URI = 'https://api.yousign.app/v3/'
    YOUSIGN_V3_URI=''
    YOUSIGN_V3_TOKEN=''
    YOUSIGN_V3_ACCESS_KEY='' #que si vous gérez les webhook yousign
    ###< yousign V3###


### 2. Si votre application va gerer lew webhooks 

#### 1. Créer un fichier yousign.yaml dans le dossier config/routes avec le contenu suivant:
```yaml
yousign:
    resource: '@YousignBundle/Resources/config/routes.yaml'
    prefix: /api/subscription/yousign
```

- Vous pouvez éditer les prefix à votre convenance
- La routes des webhooks sera : {prefix} /webhook/{version} où version est la version de l'api yousign (v2 ou v3)

#### 2. Déclarer les events listeners:
Pour chaque event que vous souhaitez écouter, créez une class implémentant EventHandlerInterface
```php
<?php
    interface EventHandlerInterface
    {
    public function handle(WebhookPayload $payload): void;
    
        public function onError(YousignException $e): void;
    }
```
Créer un fichier yousign.yaml dans le dossier config/packages avec le contenu suivant:
 
```yaml
    yousign:
    eventHandlers:
    default: 'App\Service\Signature\WebhookProcess' 
    bindings: 
        - {event: 'yousign.webhook.signature.completed', service: 'App\Service\Signature\WebhookProcess'}
```
Où event est le nom de l'event yousign à écouter et service est la méthode à appeler lors de la réception de l'event

la class déclarée avec default (default: 'App\Service\Signature\WebhookProcess') intercepte tous les events qui ne sont pas bindés 