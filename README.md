# Yousign V3 Signature Interface
Component Symfony pour signature Yousign V3

## Installation
```bash
$ composer require com-company/yousign-bundle
```
## Configuration au sein de votre projet
### 1 . Déclaration des variables d'environnement

```sh
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
```

### 2. Si votre application va gerer les webhooks 

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

<ins>Remarque :</ins> La classe WebhookPayload est conçue pour porter les infos des évènements de type signature_request (les plus communs). 
Si vous utilisez des évènements plus spécifiques (exemple : les évènements de vérifications de documents d'identité), le contenu du webhook sera contenu tel quel dans la propriété $rawData de WebhookPayload 

### 3.STATUTS DE SIGNATURE/MEMBRES:

Afin de préprarer la transition vers V3, le bundle ne renvoie que des statuts de Yousign V3, même pour les signatures initiées en V2. Chaque statut de V2 est mappé à un statut V3 correspondant.
