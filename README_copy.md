# Test Drupal

## Description de l'existant
Le site est déjà installé (profile standard), la db est à la racine du projet.
Un **type de contenu** `Événement` a été créé et des contenus générés avec Devel. Il y a également une **taxonomie** `Type d'événement` avec des termes.

La version du core est la 10.0.9 et le composer lock a été généré sur PHP 8.1.

Les files sont versionnées sous forme d'une archive compressée. Vous êtes invité à créer un fichier `settings.local.php` pour renseigner vos accès à la DB. Le fichier `settings.php` est lui versionné.

## Consignes

### 1. Faire un bloc custom (plugin annoté)
* s'affichant sur la page de détail d'un événement ;
* et affichant 3 autres événements du même type (taxonomie) que l'événement courant, ordonnés par date de début (asc), et dont la date de fin n'est pas dépassée ;
* S'il y a moins de 3 événements du même type, compléter avec un ou plusieurs événements d'autres types, ordonnés par date de début (asc), et dont la date de fin n'est pas dépassée.

Pour réaliser ce test, un module custom, intitulé test_manage_content a été créé. Dans ce module :
- J'ai créé un nouveau Block custom intitulé (Same Event Type Block) que j'ai ajouté via la Mise en page des blocs dans la partie Content.
    - le titre du bloc a volontairement été masqué
    - l'affichage des 3 contenus de la même Catégorie portée par la Taxonomie est effectuée dans la fonction build via une requête avec l'EntityTypeManager, qui porte les conditions demandées, en vérifiant également que le contenu est publié, chaque contenu est alors rendu via le view_builder avec le mode 'teaser' (accroche) puis le tout est rendu sous forme de liste via le thème 'item_list'
    - s'il y a moins de 3 résultats pour la même catégorie, on complète ces résultats (avec le parti pris de limiter à 3) avec les autres catégories. La même requête est utilisée sans la condition qui filtre sur la catégorie, on exclue d'ailleurs volontairement la catégorie initiale pour éviter de retrouver les mêmes résultats.

### 2. Faire une tache cron
qui dépublie, **de la manière la plus optimale,** les événements dont la date de fin est dépassée à l'aide d'un **QueueWorker**.

- Une classe qui étend QueueWokerBase a été créée et contient la logique de dépublication de contenu dans la méthode processItem().
Dans le hook_cron du fichier test_manage_content.module on récupère l'ensemble des contenus de type Evènements dont la date de fin est dépassée, ces contenus sont donc à déplublier, et ils sont ajouté à la queue via leur ID Drupal et en appelant la méthode createItem()


## Rendu attendu
**Vous devez cloner ce repo, MERCI DE NE PAS LE FORKER,** et nous envoyer soit un lien vers votre propre repo, soit un package avec :

* votre configuration exportée ;
* **et/ou** un dump de base de données ;
* **et pourquoi pas** des readme, des commentaires etc. :)

**Le temps que vous avez passé** : par mail ou dans un readme par exemple.
