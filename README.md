# Symfony Signature Interface
Interface pour les components de signature Yousign et Docaposte

Auteur : Andrei DACIN (adacin@com-company.fr)
## installation avec composer
les bundle doivent avoir une dépendance de 
```bash
    "repositories": [
            {"type": "git", "url": "https://github.com/com-company/yousign-bundle"},
    ],
```

Exécutez ensuite
```bash
$ composer require com-company/symfony-signature-interface
```


//TODO : Ajouter la documentation pour les services (exemple de configuration)

    yousign_client:
        class: 'Symfony\Component\HttpClient\HttpClient'
        arguments:
            $uri: '%yousign_uri%'
            $appUri: '%yousign_app_uri%'
            $token: '%yousign_token%'
            $accessKey: '%yousign_access_key%'
        
    ComCompany\YousignBundle\Service\ClientYousign:
        arguments:
            $client: '@yousgin_client'   
            $options: ['headers' => ['Content-Type' => 'application/json']]
            
    ComCompany\SignatureContract\Service\SignatureContractInterface:
        alias: 'ComCompany\YousignBundle\Service\ClientYousign'
