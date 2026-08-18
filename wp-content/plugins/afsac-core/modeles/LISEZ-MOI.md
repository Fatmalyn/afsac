# Modèle de chargement du catalogue

## Le fichier à envoyer au client

`AFSAC-modele-catalogue.xlsx` — classeur Excel à 4 onglets :

| Onglet | Rôle |
| --- | --- |
| **Mode d'emploi** | Ce que le client doit lire en premier. |
| **Formations** | Le catalogue des cours. Une ligne = un cours dans une langue. |
| **Sessions** | Les dates planifiées, rattachées aux cours par leur clé. |
| **Listes** | Tous les codes autorisés (FR / EN / AR), repris en menus déroulants. |

Dans les deux onglets de saisie : la **ligne 1** est l'en-tête (figée), les
**lignes 2 à 5** sont de l'aide et des exemples — elles commencent par `#` et
sont ignorées à l'import. La saisie commence **ligne 6**. Les colonnes sur fond
**jaune** sont les 5 seules obligatoires.

**Onglets multiples.** L'onglet *Formations* peut être dupliqué librement (un
onglet par domaine OACI, par exemple) : tous les onglets reprenant les colonnes
du modèle sont importés ensemble. Un onglet nommé d'après un domaine
(« AVIATION SECURITY », « FLIGHT SAFETY »…) ou une famille (« AVSEC ») transmet
cette valeur à ses lignes dont la cellule correspondante est vide — la cellule,
si elle est remplie, reste prioritaire. Voir `afsac_import_sheet_defaults()`.

Le même fichier est téléchargeable depuis wp-admin :
**Formations → Import / Export (tableur)**.

## Import

wp-admin → **Formations → Import / Export (tableur)**, ou en ligne de commande :

```
wp afsac import chemin/du/fichier.xlsx --type=formations --dry-run
wp afsac import chemin/du/fichier.xlsx --type=formations
wp afsac import chemin/du/fichier.xlsx --type=sessions
```

Toujours passer par une **simulation** (`--dry-run` / case cochée) avant
d'écrire : elle liste les anomalies avec leur numéro de ligne sans rien
enregistrer.

L'import est **idempotent** : la colonne `cle` (méta `_afsac_import_key`, la même
que celle des seeds AVSEC / TRAINAIR) identifie la fiche. Relancer met à jour,
ne duplique pas. Une cellule vide ne modifie rien ; un tiret `-` vide le champ.

## Régénérer le classeur

La spécification des colonnes vit dans
`includes/import-catalogue.php` → `afsac_import_spec()`. Après toute
modification (colonne ajoutée, libellé, aide, référentiel) :

```
php modeles/build-modele-xlsx.php
```

(extension PHP `zip` requise ; sous Local :
`php -d extension_dir=<php>/bin/win64/ext -d extension=zip modeles/build-modele-xlsx.php`)

Le script relit la spec et réécrit `AFSAC-modele-catalogue.xlsx` : le modèle
envoyé au client et l'importeur ne peuvent pas diverger.
