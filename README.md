# UserBundle
userbundle de fainéant pour les projets a la con (dont use it)

=> Rédaction du README en cours

## Installation:
#### Ajouter le bundle dans le composer.json

    "require": {
        "rudak/user-bundle": "dev-master",
      },
#### Déclaration dans le kernel:

    new Rudak\UserBundle\RudakUserBundle(),
    
#### Configuration du security.yml

    security:
        encoders:
            Rudak\UserBundle\Entity\User:
                algorithm: bcrypt
                cost:      15
        providers:
            administrators:
                entity: { class: RudakUserBundle:User, property: username }
    
        role_hierarchy:
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPER_ADMIN: ROLE_ADMIN
    
        firewalls:
            dev:
                pattern: ^/(_(profiler|wdt|error)|css|images|js)/
                security: false
            secured_area:
                pattern:    ^/
                anonymous: ~
                form_login:
                    login_path:  /login
                    check_path:  /login_check
                logout:
                    path:   /logout
                    target: /
                remember_me:
                    key:       "%secret%"
                    lifetime: 31536000
                    path: /
                    domain: ~
            default:
                anonymous: ~
        access_control:
            - { path: ^/user, roles: ROLE_USER }
            - { path: ^/admin, roles: ROLE_ADMIN }
            - { path: ^/.*, role: IS_AUTHENTICATED_ANONYMOUSLY }
            
#### Options:            
    
    rudak_user:
        autologin_before_reinit: false
        from: admin@youporn.com
        websiteName: youporn.com
        homepage_route: homepage
        
## Lignes de commandes:
####creer un utilisateur

rudakuser:create
    
Exemple :
    php app/console rudakuser:create --admin --blocked francky
Cette commande creera un utilisateur nommé franky, il sera admin, mais pas de bol, il sera bloqué dès la naissance !
####Donner des droits a un utilisateur
    rudakuser:promote
####Retirer les droits a un utilisateur
    rudakuser:demote
####Verifier les hashs de securité expirés
    rudakuser:securitycheck
## Options:

Exemple d'options se trouvant dans le fichier app/config/config.yml

    rudak_user:
        autologin_before_reinit: false
        from: admin@youporn.com
        websiteName: youporn.com
        homepage_route: homepage

### Détails
* ```autologin_before_reinit:``` : **False** par défaut.    
Cet argument enclenche l'authentification automatique après la réinitialisation du mot de passe, dans le cas contraire, l'utilisateur doit se connecter avec son pseudo et son mot de passe fraichement modifié.
* ```from:``` : **Obligatoire**, aucune valeur par défaut.   
Cette option sert a configurer l'expéditeur mentionné lors des envois d'emails (inscription, réinitialisations password etc...)
* ```websiteName:``` : **Obligatoire**, aucune valeur par défaut.   
C'est le nom du site, qui sera lui aussi mentionné dans les emails de correspondance.
* ```homepage_route:``` : **Obligatoire**, aucune valeur par défaut.   
C'est la route par défaut utilisée pour retourner à la page d'accueil de votre site.