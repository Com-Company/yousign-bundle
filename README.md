# Yousign V3 Signature Interface
Component Symfony pour signature Yousign V3

Auteur : Andrei DACIN (adacin@com-company.fr)
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
### 1 . Déclaration du client Yousign ;


        app.yousign_client:
        class: 'Symfony\Component\HttpClient\HttpClient'
        factory: [ 'Symfony\Component\HttpClient\HttpClient', 'create' ]
        arguments:
            $defaultOptions:
                headers:
                    content-type: 'application/json'
                    Authorization: 'Bearer %yousign_token_v3%'
                base_uri: '%yousign_uri_v3%'

### 2. Déclarer le service de connexion à Yousign dans votre app : 

        ComCompany\YousignBundle\Service\ClientYousign:
        arguments:
            $httpClient: '@app.yousign_client'
### 3. Utiliser le service dans votre app

        ComCompany\SignatureContract\Service\SignatureContractInterface:
        alias: 'ComCompany\YousignBundle\Service\ClientYousign'

### 4. Déclarer les paramètres de connexion à Yousign dans votre fichier .env

####YOUSIGN_V3_URI='https://api-sandbox.yousign.app/v3/' dev ou 'https://api.yousign.app/v3/' prod 
####YOUSIGN_V3_TOKEN='token_yousign_v3'
####YOUSIGN_V3_WORKSPACE_ID='workspace_id_de_votre_application'
