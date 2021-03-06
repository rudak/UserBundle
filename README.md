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
Ajoutez ces parametres dans le ```app/config/config.yml```

    rudak_user:
        autologin_before_reinit: false
        from: admin@youporn.com
        websiteName: youporn.com
        homepage_route: homepage
        
#### Parametrer le routing
Ajouter ces informations de routing

    rudak_user:
        resource: "@RudakUserBundle/Resources/config/routing.yml"
        prefix:   /
    login:
        path:   /login
        defaults:  { _controller: RudakUserBundle:Security:login }
    login_check:
        path:   /login_check
    logout:
        path:   /logout

#### Traductions
Pour que les traductions fonctionnent il faut parametrer la locale et décommenter cette ligne :

    framework:   
        translator:      { fallbacks: ["%locale%"] }        
        
## Affichage des vues

Les vues étendent par défaut **main_layout**, une variable qui pointe vers la vue principale, dans le config.yml. Ca permet de centraliser un peu la mise en place.

    # Twig Configuration
    twig:
        ...
        globals:
            main_layout: '::base.html.twig'
            
## Importation des blocks

Pour importer la partie membre dans une **template twig** :
    
    {% render(controller('RudakUserBundle:Include:userArea')) %}
    
Pour inclure le formulaire de login :

    {% render(controller('RudakUserBundle:Form:getFormLogin')) %}
## Administration

Toute la partie administration est prefixée par default par ```admin/user```    
*(par exemple : ```http://localhost.com/admin/user```)*    
Il s'agit d'un **crud** traditionnel,création, consultation, mise a jour, et suppression d'utilisateurs.    
On ne peut pas modifier le mot de passe utilisateur depuis l'administration, le processus étant assez simple, personne ne 
devrait avoir de mal à changer ses infos privées de manière ... privée.   
J'ai utilisé une base de Bootstrap pour l'admin, c'est propre sans etre foufou non plus, ca peut s'améliorer mais a la base, 
j'ai pas besoin d'une administration super branchée donc voila...

Les pages du CRUD sont prévues pour étendre la vue nommée ```admin_layout```. Le contenu est envoyé vers le block nommé ```content```. Voici un petit exemple de ce que pourrait contenir cette vue.

    {% extends main_layout %}
    
    {% block body %}
        <div class="row">
            <div class="col-sm-8">
                <h1>Administration</h1>
                {% block content %}{% endblock %}
            </div>
            <div class="col-sm-4">
    
            </div>
        </div>
    {% endblock %}
    
Pour les variables twig, il faut rajouter ca dans votre ```config.yml```.

    twig:
        globals:
            main_layout: '::base.html.twig'
            admin_layout: '::admin-layout.html.twig'

# Lignes de commandes:
###Creer un utilisateur

    rudakuser:create [--admin] [--superadmin] [--blocked] [username]
    
**Exemple :** ``` php app/console rudakuser:create --admin --blocked francky ```    
Cette commande creera un utilisateur nommé franky, il sera admin, mais pas de bol, il sera bloqué dès la naissance !

###Donner des droits a un utilisateur
    
    rudakuser:promote [--moderator] [--admin] [--superadmin] [username]
**Exemple :** ``` php app/console rudakuser:promote --admin francky ```    
Cette commande donera le role **ROLE_ADMIN** à l' utilisateur nommé franky !

###Retirer les droits a un utilisateur

    rudakuser:demote [username]
    
**Exemple :** ``` php app/console rudakuser:demote francky ```    
Cette commande retire tous les roles de l'utilisateur (sauf le role user, qui fait partie des murs de Symfony ^^).

###Verifier les hashs de securité expirés
    
    rudakuser:securitycheck [--delete]    
    
**Exemple :** ``` php app/console rudakuser:securitycheck ```    
Cette commande renvoie tous les utilisateurs qui contiennent des hashs expirés.    
En passant l'option ```--delete``` Les informations qui concernent ces hashs sont supprimées.
*Ces hashs sont créés lors des demandes de réinitialisation de mot de passe et autres. On peut se passer de cette commande, mais elle existe...*

###Verifier la configuration du bundle rapidement
    
    rudakuser:config:dump
**Exemple :** ``` php app/console rudakuser:config:dump ```    
Cette commande renvoie toutes les valeurs de configuration du bundle.
# Options:

Exemple d'options se trouvant dans le fichier ```app/config/config.yml```

    rudak_user:
        autologin_before_reinit: false
        from: admin@youporn.com
        websiteName: youporn.com
        homepage_route: homepage

### Détails des options
* ```autologin_before_reinit:``` : **False** par défaut.    
Cet argument enclenche l'authentification automatique après la réinitialisation du mot de passe, dans le cas contraire, l'utilisateur doit se connecter avec son pseudo et son mot de passe fraichement modifié.
* ```from:``` : **Obligatoire**, aucune valeur par défaut.   
Cette option sert a configurer l'expéditeur mentionné lors des envois d'emails (inscription, réinitialisations password etc...)
* ```websiteName:``` : **Obligatoire**, aucune valeur par défaut.   
C'est le nom du site, qui sera lui aussi mentionné dans les emails de correspondance.
* ```homepage_route:``` : **Obligatoire**, aucune valeur par défaut.   
C'est la route par défaut utilisée pour retourner à la page d'accueil de votre site.