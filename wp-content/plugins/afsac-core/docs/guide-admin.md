# AFSAC — Guide d'administration du site

Document de passation / support de réunion.
Version du thème : 0.8.0 · Plugin métier : `afsac-core`

---

## Partie 1 — Comment le site a été construit (à expliquer au client)

### 1.1 Le principe : un site que vous pilotez vous-même

Le site n'est pas une brochure figée. C'est une **base de données de votre offre
de formation**, avec un habillage par-dessus. Concrètement : quand vous ajoutez
un cours dans l'administration, il apparaît **automatiquement** au bon endroit —
dans le catalogue, dans la page du domaine OACI concerné, dans le moteur de
recherche interne, dans le calendrier si une session est programmée, dans les
carrousels de la page d'accueil, et dans le plan du site envoyé à Google.
Vous ne touchez jamais au design, jamais au code.

### 1.2 Deux briques séparées (le point important)

| Brique | Rôle | Analogie |
|---|---|---|
| **Plugin `afsac-core`** | Le moteur métier : les cours, les sessions, les domaines OACI, les formulaires, les leads, le SEO technique | Le moteur et le châssis |
| **Thème `afsac`** | La présentation : couleurs, mises en page, typographies | La carrosserie |

**Pourquoi c'est important pour vous :** vos données (les ~490 cours, les
sessions, les leads) vivent dans le moteur, pas dans la carrosserie. Si dans
3 ans vous voulez refaire le design du site, **rien n'est perdu** : on change la
carrosserie, le catalogue reste intact. C'est une garantie d'indépendance :
vous n'êtes prisonnier ni du design, ni du prestataire.

### 1.3 Ce qui a été mis en place

- **WordPress standard** (pas de technologie exotique) : n'importe quel
  intégrateur WordPress peut reprendre la main.
- **Multilingue** français / anglais, avec le socle RTL conservé (sens de
  lecture droite-à-gauche déjà géré dans les feuilles de style). Chaque page a
  sa version par langue, et les balises `hreflang` sont émises automatiquement
  pour que Google serve la bonne langue au bon pays.
- **Modèle de contenu conforme au référentiel OACI** : les 11 *areas* TRAINAIR
  PLUS sont des catégories officielles du site, avec les **couleurs exactes
  relevées sur le portail OACI**.
- **Génération de leads** : les demandes d'inscription et les messages de contact
  ne partent pas seulement par e-mail — ils sont **stockés dans le site**, avec
  horodatage du consentement RGPD, la langue du visiteur et la page d'origine.
  Rien ne se perd si une boîte mail sature.
- **SEO** : Rank Math, fils d'Ariane, données structurées (les fiches de cours
  sont déclarées à Google comme des cours, pas comme de simples pages).
- **Conformité RGPD** : consentement explicite horodaté, données personnelles
  jamais publiques.

### 1.4 Ce qui est déjà en base aujourd'hui

- **19 cours AVSEC** en français **et** en anglais (38 fiches).
- **4 cours TRAINAIR PLUS** + sessions de démonstration.
- Les **22 termes de domaines** (11 areas × 2 langues) aux couleurs OACI.
- Les pages éditoriales : Accueil, Formations & Services, Catalogue / Liste de
  cours, Calendrier des sessions, Qui sommes-nous, Références & témoignages,
  Actualités, Contact, Inscription, Espace participant.

---

## Partie 2 — Le mode d'emploi de wp-admin

### 2.0 Se connecter

Adresse : `https://<votre-domaine>/wp-admin`
Identifiants transmis séparément. Chaque personne doit avoir **son propre
compte** (jamais de compte partagé) : Utilisateurs → Ajouter.

Rôles conseillés :
- **Éditeur** — pour l'équipe formation : peut tout créer et publier en contenu.
- **Administrateur** — réservé à 1 ou 2 personnes : réglages, extensions, comptes.

### 2.1 Le menu de gauche, en un coup d'œil

| Menu | À quoi ça sert |
|---|---|
| **Formations** | Les fiches de cours (le catalogue) |
| **Sessions** | Les dates programmées (le calendrier) |
| **Services** | Les prestations de conseil / audit / sur-mesure |
| **Témoignages** | Les avis clients affichés sur le site |
| **Références** | Les institutions clientes / partenaires |
| **Inscriptions** | Les demandes d'inscription reçues (leads) |
| **Messages** | Les messages du formulaire de contact |
| **Téléchargements** | Qui a demandé la brochure, et combien l'ont téléchargée |
| **Pages** | Les pages éditoriales (Accueil, Qui sommes-nous…) |
| **Médias** | Images et PDF |
| **Apparence → Menus / Personnaliser** | Navigation, logo, coordonnées |
| **AFSAC Réglages** | Destinataires des e-mails de leads |
| **Outils** | Imports en masse |
| **Langues** | Réglages multilingues (Polylang) |

---

### 2.2 AJOUTER UN COURS (le geste le plus fréquent)

**Formations → Ajouter**

#### Étape 1 — Le titre et la description

- **Titre** : l'intitulé exact du cours (ex. « Sûreté de l'aviation civile —
  cours de base »).
- **Zone d'édition principale** : la description commerciale, en blocs. C'est le
  texte de présentation qui accroche le prospect. Un paragraphe suffit ;
  le détail pédagogique va dans les champs structurés ci-dessous.

#### Étape 2 — Le bloc « Détails formation » (sous l'éditeur)

C'est le cœur de la fiche. Tous ces champs alimentent l'affichage automatique :

| Champ | Exemple | Remarque |
|---|---|---|
| Abréviation | `TDC EN` | affichée à côté du titre |
| Référence (complète) | `214001/TDCEN` | code catalogue |
| Objectifs | texte riche | objectifs pédagogiques |
| Public cible | texte riche | version détaillée |
| Public (résumé) | `Cadres & experts` | version courte, colonne de droite |
| Prérequis | texte | |
| Résultats attendus | texte riche | compétences acquises |
| **Structure (modules)** | **une ligne = un module** | le site numérote tout seul : Module 0, Module 1… |
| Développé par | `ICAO · OACI` | pré-rempli |
| Autres langues | `Français, Espagnol` | séparées par des virgules |
| Niveau | liste déroulante | Initiation / Fondamental / … |
| Durée | `5 jours` | format libre |
| Frais (montant) | `1800` | **chiffres seuls, sans devise** |
| Devise | USD / EUR / TND | |
| Certificat délivré | Oui / Non | si « Oui », un champ « Intitulé du certificat » apparaît |

> ⚠️ Le champ **Frais** n'accepte que des chiffres. Ne pas écrire « 1800 USD » :
> la devise est un champ à part.

#### Étape 3 — Le classement (colonne de droite)

C'est ce qui détermine **où le cours apparaît** sur le site. À ne pas négliger.

- **Areas OACI** — obligatoire. Le domaine TRAINAIR PLUS (1 seul en principe).
  C'est lui qui donne la couleur de la fiche et la page « Liste de cours » où
  elle se rangera.
- **Familles** — le regroupement commercial (AVSEC, TRAINAIR PLUS…). C'est ce
  qui alimente les deux grandes portes d'entrée du catalogue.
- **Langues de formation** — la langue dans laquelle le cours est **animé**
  (FR / ENG / ARAB). ⚠️ À ne pas confondre avec la langue de la *page*.
- **Modalités** — présentiel, distanciel, hybride…
- **Types de cours** — Cours OACI / STP / Atelier / Programme (badge + filtre).
- **Classement (liste de cours)** — encadré à droite :
  - *Méthode de dispensation* : pilote les onglets « Avec instructeur » /
    « Auto-rythmé » de la page catalogue.
  - *Tarif réduit (États ACA)* : interrupteur oui/non.

#### Étape 4 — Le visuel

Colonne de droite → **Visuel de la formation** → Définir le visuel.

- Format conseillé : **paysage, minimum 1200 × 800 px**, JPG ou WebP.
- Sans visuel, le site affiche automatiquement une photo d'aviation de secours
  (toujours la même pour un cours donné) : la page reste propre.
- ⚠️ Ne pas mettre en visuel un **scan de fiche descriptive** (page de texte) :
  en vignette c'est illisible. Une affiche, une photo de salle, un visuel
  thématique — pas un document.

#### Étape 5 — Publier, puis traduire

1. **Publier**.
2. Dans la colonne de droite, encadré **Langue** : la fiche est en français.
   En face de « English », cliquer sur le **+**.
3. WordPress ouvre une fiche vierge en anglais, déjà reliée à la version
   française. Saisir le titre et les champs en anglais, choisir les mêmes
   catégories (dans leur version anglaise), publier.

> Le lien entre les deux versions est ce qui fait fonctionner le sélecteur de
> langue du site : sans lui, le visiteur anglophone tombe sur la page d'accueil.

---

### 2.3 MODIFIER UN COURS EXISTANT

**Formations → Toutes les formations** → cliquer sur le titre.

- La liste peut être filtrée par **Area OACI**, **Famille**, **Langue** et par
  langue du site (drapeaux en haut).
- Modifier ce qu'il faut → bouton **Mettre à jour**.
- **Penser à répercuter sur l'autre langue** : modifier la fiche FR ne modifie
  pas la fiche EN. Le raccourci : dans l'encadré « Langue », cliquer sur
  l'icône crayon en face de l'autre langue.
- **Historique** : en cas d'erreur, l'encadré « Révisions » permet de revenir à
  une version précédente. Rien n'est jamais perdu.
- **Retirer un cours du site** sans le supprimer : passer son état en
  « Brouillon » (Publier → Modifier l'état).

---

### 2.4 PROGRAMMER UNE SESSION (alimente le calendrier)

**Sessions → Ajouter**

| Champ | Remarque |
|---|---|
| **Titre** | libre, ex. « AVSEC de base — Tunis, mars 2026 » |
| **Formation liée** ⚠️ obligatoire | commencer à taper le nom du cours et le choisir dans la liste |
| **Date de début** ⚠️ obligatoire | sélecteur de calendrier |
| Date de fin | |
| **Lieu** | ex. « Tunis, Tunisie » — sert aussi à placer le point sur la carte |
| **Langue de la session** ⚠️ obligatoire | FR / ENG / ARAB |
| Places disponibles | optionnel |
| **Statut** ⚠️ obligatoire | **Ouvert** / **Complet** / **Archivé** |
| Institution hôte | ex. « Autorité de l'aviation civile » |
| Contact — nom / e-mail | affichés sur la page d'inscription |
| Latitude / Longitude | optionnel : renseignés seulement si la ville n'est pas reconnue |

**Ce qu'il faut retenir :**

- Une session **passée disparaît toute seule** du calendrier. Aucune maintenance.
- Statut **Archivé** = retirée immédiatement de l'affichage public (elle reste
  en base pour vos statistiques).
- Statut **Complet** = elle reste visible, avec la mention « Complet ».
- La vignette affichée dans le calendrier est **celle du cours lié** : c'est une
  raison de plus de soigner les visuels de cours.
- **Pour qu'une session apparaisse aussi dans le calendrier anglais**, il faut
  la traduire (bouton **+** dans l'encadré Langue), avec les mêmes dates.

---

### 2.5 GÉRER LES CATÉGORIES (domaines, familles, types…)

**Formations → Areas OACI** (et Familles / Langues / Modalités / Types)

- Les 11 domaines OACI sont **déjà créés, dans les deux langues, avec les
  couleurs officielles**. En principe on n'y touche plus.
- Sur la fiche d'un domaine, deux champs vous appartiennent :
  - **Chapô (introduction)** : le texte d'accroche affiché en haut de la page
    « Liste de cours » du domaine.
  - **Couleur d'accent** : la couleur du domaine (déjà réglée sur la charte OACI).
- Si vous créez un **nouveau** terme, pensez à créer aussi sa **traduction**
  (colonne « Langue » de la liste des termes), sinon il n'existera que d'un côté
  du site.

---

### 2.6 LES LEADS (inscriptions, messages, téléchargements)

**Inscriptions** — chaque demande d'inscription reçue depuis le site.
La liste affiche : nom, **statut**, **e-mail**, **cours concerné**, date.
Ouvrir une ligne pour voir le détail : coordonnées, organisme, session visée,
langue du visiteur, page d'origine, horodatage du consentement RGPD.

**Messages** — idem pour le formulaire de contact.

**Téléchargements** — le suivi de la brochure. **Depuis le 8 septembre 2026, la
brochure se télécharge d'un simple clic**, sans e-mail ni formulaire : le bouton
du pied de page sert directement le PDF de la langue de la page consultée (site
en français → brochure française, site en anglais → brochure anglaise). Le
chiffre **« Téléchargements directs »** en haut de l'écran compte ces clics,
édition par édition, sans aucune donnée personnelle.

Les fiches et chiffres ci-dessous datent de la période (août – début septembre
2026) où le visiteur laissait son e-mail ; ils restent consultables et
exportables, mais ne s'alimentent plus.

En haut de la liste, quatre chiffres répondent d'un coup d'œil :

| Chiffre | Ce qu'il compte |
|---|---|
| **Personnes** | le nombre de contacts distincts — **une adresse e-mail = une fiche**, même si la personne revient plusieurs fois |
| **Téléchargements** | le nombre de PDF **réellement servis** |
| **Formulaires envoyés** | le nombre de demandes, ré-envois compris |
| **Ce mois-ci** | les nouvelles fiches du mois, et la part des fiches qui ont bien téléchargé |

⚠️ **« Formulaires envoyés » et « Téléchargements » sont deux choses
différentes** : quelqu'un peut remplir le formulaire puis fermer la fenêtre sans
récupérer le fichier. C'est voulu : confondre les deux gonflerait le chiffre.
La colonne « Demandes / Téléch. » donne le détail ligne par ligne, et une fiche
qui n'a **jamais** téléchargé est signalée en rouge.

Le bouton **« Exporter en CSV »** télécharge toute la liste (coordonnées,
compteurs, dates, pays, édition demandée) pour l'ouvrir dans Excel.

Le PDF n'est jamais servi par un lien public : chaque demande crée un lien
personnel valable **7 jours**, envoyé aussi par e-mail au demandeur. C'est ce qui
empêche l'adresse du fichier de circuler et fausser le comptage.

**En parallèle**, chaque demande déclenche :
1. un **e-mail interne** vers les destinataires configurés (avec « Répondre à »
   pré-rempli sur l'adresse du prospect : on répond en un clic) ;
2. un **e-mail de confirmation** au prospect ;
3. optionnellement, un **envoi automatique vers votre CRM** si vous en avez un.

**Configurer les destinataires : menu « AFSAC Réglages »**

- *E-mail(s) de notification* : les adresses internes qui reçoivent les leads,
  **séparées par des virgules**. ⚠️ Tant que c'est vide, tout part sur l'adresse
  d'administration du site — un bandeau d'avertissement le rappelle.
  Réglé le 09/09/2026 sur **managerit@afsactunisie.com** (inscriptions ET
  messages de contact).
- *Nom / e-mail de l'expéditeur* : l'identité des e-mails envoyés par le site.
  Réglé sur « AFSAC – ICAO ASTC Tunis » / **icao@afsac-training.com** (la boîte
  OVH qui sert à l'envoi, voir ci-dessous). Le nom sert aussi de préfixe aux
  sujets (« [AFSAC – ICAO ASTC Tunis] Nouvelle inscription — … »).
- *Webhook CRM* : à remplir le jour où vous branchez un CRM.

⚠️ Ces réglages vivent dans la base de données : ils sont à reporter sur chaque
copie du site (site de test, production) dans le même écran.

**Savoir si un e-mail est parti : la section « Envoi des e-mails »**

Depuis la version 0.5.3 du plugin, chaque fiche (Inscriptions, Messages) se
termine par une section **« Envoi des e-mails »** : pour la notification interne
et pour la confirmation au demandeur, elle indique **« Remis au serveur
d'envoi »** ou **« Échec »** avec la cause exacte, plus l'expéditeur utilisé et
le canal (WP Mail SMTP ou mail() du serveur).

- *Remis au serveur d'envoi* : le site a fait son travail. Si le message
  n'arrive pas, il est chez le destinataire — courrier indésirable, ou
  quarantaine Microsoft 365 pour managerit@ (security.microsoft.com →
  Quarantaine).
- *Échec* : lire la cause. `535 … authentication failed` = mot de passe SMTP
  faux ; `553 … sender address rejected` = expéditeur non autorisé (voir
  ci-dessous) ; `Could not instantiate mail function` = aucun plugin SMTP actif.

**Pourquoi les e-mails arrivent en spam, et comment y remédier**

Depuis septembre 2026 les e-mails sont mis en forme (bandeau bleu, tableau des
champs, bouton vers la fiche). Mais la présentation ne suffit pas : un e-mail
envoyé « à la sauvage » par le serveur web, avec une adresse d'expéditeur que
personne n'a autorisée, est rejeté ou classé en spam par Gmail et Microsoft.

La solution est de faire partir les e-mails du site par le **serveur SMTP d'OVH,
avec la boîte icao@afsac-training.com**, via le plugin gratuit **WP Mail SMTP**.
Le domaine afsac-training.com autorise OVH à émettre pour lui (SPF
`include:mx.ovh.com`) : les e-mails partent donc « signés » et arrivent en
boîte de réception.

⚠️ Deux règles à respecter, sinon rien ne part :
- l'**adresse d'expéditeur doit être la boîte qui s'authentifie** :
  icao@afsac-training.com. OVH refuse d'envoyer un message « De :
  managerit@afsactunisie.com » (ou no-reply@…) avec le compte icao@ — c'est la
  cause nº 1 des envois qui échouent en silence ;
- ne PAS mettre une adresse @afsactunisie.com en expéditeur : la messagerie de
  ce domaine est chez Microsoft 365 et son SPF n'autorise que Microsoft
  (`include:spf.protection.outlook.com -all`) → spam garanti.

Le **destinataire** des notifications, lui, reste managerit@afsactunisie.com
(réglé dans AFSAC Réglages) : recevoir n'a rien à voir avec envoyer.

**Marche à suivre (WP Mail SMTP + OVH, ~10 minutes)**

1. *Extensions → Ajouter* : chercher **WP Mail SMTP** (éditeur WPForms) →
   Installer → Activer. Fermer l'assistant si besoin et aller dans
   *WP Mail SMTP → Réglages*.
2. Bloc **De (From)** :
   - *From Email* : **icao@afsac-training.com** → cocher **Force From Email** ;
   - *From Name* : **AFSAC – ICAO ASTC Tunis** → cocher **Force From Name**.
3. Mailer : **Other SMTP (Autre SMTP)** :
   - SMTP Host : `ssl0.ovh.net` ; Encryption : **SSL** ; SMTP Port : **465**
     (ou TLS + 587, les deux marchent) ; Auto TLS : ON ;
   - Authentication : ON ; SMTP Username : `icao@afsac-training.com` ;
     SMTP Password : le mot de passe **de la boîte mail** (celui du webmail
     OVH, pas celui du client OVH). En cas de doute, le réinitialiser depuis
     l'espace client OVH → *Web Cloud → E-mails → afsac-training.com →
     icao@ → Modifier le mot de passe*.
   - Enregistrer.
4. **Vérifier** : *WP Mail SMTP → Outils → Test d'e-mail* → envoyer vers une
   adresse Gmail.
   - Cadre **vert** : le message doit arriver en boîte de réception, expéditeur
     « AFSAC – ICAO ASTC Tunis <icao@afsac-training.com> ».
   - Cadre **rouge** : lire le message d'erreur (aussi dans *Outils → Debug
     Events*). `535 … authentication failed` = mot de passe faux ;
     `553 … sender address rejected` / `not owned by user` = l'expéditeur n'est
     pas icao@ (revoir l'étape 2) ; `Connection timed out` = le port est bloqué,
     essayer TLS + 587.
5. Envoyer un vrai message depuis le formulaire de contact du site : la
   notification arrive sur managerit@ (vérifier aussi *Courrier indésirable*
   et la *quarantaine* Microsoft 365 la première fois, puis marquer
   « Pas indésirable » pour apprendre au filtre) et le bouton « Répondre » vise
   bien le visiteur.

⚠️ Ces réglages vivent dans la base du serveur : **chaque nouvel import Duplicator
les efface** (la base locale remplace celle du serveur). Après chaque import,
réactiver WP Mail SMTP et ressaisir le mot de passe — ou demander à l'intégrateur
de poser les réglages en constantes dans wp-config.php, qui survivent aux
imports.

---

### 2.7 LES AUTRES CONTENUS

**Témoignages → Ajouter** : Auteur, Fonction, Organisation, Citation, Langue,
et optionnellement une vidéo (URL + durée). Une photo peut être ajoutée en
image mise en avant.

**Références → Ajouter** : le nom de l'institution en titre, son **logo** en
image mise en avant.

**Services → Ajouter** : titre, description courte, **icône** (liste déroulante),
et un lien « En savoir plus » optionnel. Ces cartes alimentent la page
« Formations & Services ».

---

### 2.8 LES PAGES ÉDITORIALES

**Pages → Toutes les pages** → modifier.

- Le contenu se rédige en **blocs** (comme un traitement de texte).
- Sur les pages à gabarit spécifique (Contact, Actualités, Qui sommes-nous,
  Catalogue…), un encadré **« En-tête de page »** apparaît sous le titre :
  - *Sur-titre* : le petit label au-dessus du titre (ex. « Nous contacter ») ;
  - *Chapô* : la phrase d'introduction dans le bandeau ;
  - *Intro — sur-titre / titre* : la carte d'introduction sous le bandeau.
- **Ne pas changer le « Modèle »** d'une page (colonne de droite) : c'est lui
  qui détermine la mise en page. Le modifier casse l'affichage.

**Le PDF du programme (proposé dans le pied de page de tout le site)** :
Pages → **Accueil** → encadré « Brochure — programme de formation ». Deux
fichiers : *Français* et *English*. Le visiteur ne choisit plus : la page en
français sert la brochure française, la page en anglais la brochure anglaise.
Si l'un des deux manque, c'est le champ *Programme complet — PDF* (fichier
unique) qui est servi, sinon l'autre édition.

Remplacer un fichier suffit : les liens déjà envoyés continuent de fonctionner et
pointent vers la nouvelle version. Si **aucun** PDF n'est chargé, la bande
« Téléchargez la brochure » disparaît du site (jamais de bouton qui ne mène à
rien) et un avertissement s'affiche dans **Téléchargements**.

---

### 2.9 NAVIGATION, LOGO ET COORDONNÉES

**Apparence → Menus** — trois emplacements :
- *Navigation principale* (le menu du site)
- *Barre utilitaire (haut)*
- *Pied de page — Liens utiles*

⚠️ En multilingue, **chaque langue a son propre menu** : créer un menu FR et un
menu EN, et les affecter chacun à l'emplacement voulu.

**Apparence → Personnaliser** :
- *Identité du site* → **Logo**.
- **« Coordonnées AFSAC »** → téléphone(s), e-mails (contact / formation /
  sur-mesure), adresse, réseaux sociaux, liens légaux. Ces valeurs alimentent la
  barre du haut et le pied de page **de tout le site** : on les change à un seul
  endroit. Un réseau social laissé vide n'affiche simplement pas son icône.

---

### 2.10 LE SEO (Rank Math)

Sur chaque cours / page, en bas de l'écran d'édition, l'encadré **Rank Math** :
- *Titre SEO* et *Méta description* : ce que Google affiche dans ses résultats.
  Laisser vide = Rank Math génère automatiquement.
- L'aperçu montre le rendu dans Google.

Le reste (plan du site XML, fils d'Ariane, hreflang, données structurées de
cours) est **déjà automatique**, il n'y a rien à faire.

---

### 2.11 IMPORTS EN MASSE (usage ponctuel, administrateur)

**Outils → Import fiches AVSEC** et **Outils → Import TRAINAIR PLUS**.

Ces deux outils rechargent les catalogues de référence. Ils sont **idempotents** :
les relancer ne crée pas de doublons, ils mettent à jour l'existant.
À n'utiliser qu'en cas de besoin (restauration, mise à jour de masse), de
préférence avec le prestataire.

### 2.12 CHARGER TOUT LE CATALOGUE DEPUIS UN TABLEUR

**Formations → Import / Export (tableur)**. C'est l'outil prévu pour charger les
~500 cours d'un seul coup, à partir d'un classeur Excel rempli par l'AFSAC.

**Le principe en 3 temps :**

1. **Envoyer le modèle au client.** Bouton « Télécharger le classeur Excel ».
   Le classeur contient un mode d'emploi, l'onglet *Formations*, l'onglet
   *Sessions* et la liste des codes autorisés (menus déroulants).
   Une ligne = un cours dans une langue ; **5 colonnes seulement sont
   obligatoires** (`cle`, `langue_fiche`, `titre`, `famille`, `domaine`).
2. **Contrôler avant d'écrire.** Déposer le fichier reçu avec la case
   « Simulation » cochée : rien n'est enregistré, mais chaque anomalie est
   signalée avec son **numéro de ligne** (code inconnu, date illisible, cours
   introuvable…). On corrige le fichier, on refait une simulation.
3. **Importer.** Décocher « Simulation ». Le traitement avance par paquets avec
   une barre de progression : laisser la page ouverte jusqu'à la fin.

**Les 4 règles à connaître :**

- La colonne **`cle`** est l'identifiant du cours. Deux lignes avec la **même
  clé** et des `langue_fiche` différentes (`fr` / `en`) deviennent
  automatiquement **deux traductions liées**.
- **Ré-importer le même fichier ne crée jamais de doublon** : les fiches
  portant une clé déjà connue sont mises à jour.
- **Une cellule vide ne supprime rien.** Ce qui a été saisi dans wp-admin est
  conservé. Pour vider volontairement un champ, écrire un tiret `-`.
- L'import **n'empêche pas** la saisie manuelle : après le chargement, tout se
  modifie et s'ajoute normalement dans wp-admin.

**Un onglet par domaine (recommandé au-delà de 100 cours).** L'onglet
*Formations* peut être **dupliqué autant de fois que nécessaire** — typiquement
un onglet par domaine OACI — pour répartir la saisie entre plusieurs personnes.
Tous ces onglets sont importés **en une seule fois**.

Si l'onglet porte le nom d'un domaine (« AVIATION SECURITY », « Aviation LAW »,
« FLIGHT SAFETY », « Sûreté de l'aviation »…), **la colonne `domaine` peut
rester vide** : elle est déduite du nom de l'onglet. Une valeur saisie dans la
colonne reste prioritaire. Même principe pour un onglet nommé « AVSEC » ou
« TRAINAIR PLUS », qui renseigne la famille.

Deux conditions : conserver **la ligne 1** (les intitulés) dans chaque onglet
dupliqué, et garder un nom d'onglet reconnaissable. Le compte rendu d'import
liste les onglets lus, leur nombre de lignes et la valeur héritée — c'est le
premier endroit à regarder après une simulation. Les onglets *Mode d'emploi*,
*Listes* et ceux de l'autre type sont ignorés automatiquement.

**Export.** Les deux boutons d'export produisent le catalogue actuel **au format
du modèle** : pratique pour faire relire ou compléter l'existant par le client,
puis ré-importer le même fichier.

> Le classeur modèle est aussi versionné dans le plugin
> (`wp-content/plugins/afsac-core/modeles/`) et se régénère avec
> `php modeles/build-modele-xlsx.php` après toute évolution des colonnes.

---

## Partie 3 — Les 8 pièges à éviter

1. **Ne jamais changer le « Modèle » d'une page** dans la colonne de droite.
2. **Toujours renseigner l'Area OACI et la Famille** d'un cours : sans elles, le
   cours n'apparaît nulle part dans le catalogue.
3. **Ne pas confondre** « Langue de formation » (langue d'animation du cours) et
   « Langue » (langue de la page, encadré Polylang).
4. **Une modification en français ne se propage pas en anglais.** Toujours faire
   les deux.
5. **Frais** : chiffres seuls, la devise est un champ à part.
6. **Visuels** : pas de scan de document en image de cours.
7. **Sessions** : la « Formation liée », la date de début, la langue et le statut
   sont obligatoires — sans eux la session ne s'affiche pas correctement.
8. **Ne pas désactiver les extensions** ACF, Polylang, Rank Math ou
   AFSAC Core : le site en dépend.

---

## Partie 4 — Points à valider avec le client

- [ ] **Adresse(s) e-mail** qui doivent recevoir les demandes d'inscription et
      les messages de contact.
- [ ] **Service d'envoi d'e-mails** (SMTP) : indispensable pour que les e-mails
      ne partent pas en spam, sur le site de test comme en production. Marche à
      suivre détaillée en 2.6 (WP Mail SMTP + SMTP OVH, boîte icao@afsac-training.com).
- [ ] **Visuels des cours** : fournir des affiches / photos exploitables pour
      remplacer les scans de fiches.
- [ ] **PDF du programme** consolidé FR · EN.
- [x] **Arabe** : écarté à la demande du client (03/09/2026). Le site n'est publié
      qu'en français et en anglais ; le socle RTL reste en place si la décision change.
- [ ] **CRM** : y a-t-il un outil à brancher ? (le point d'entrée est déjà prévu)
- [ ] **Comptes utilisateurs** : qui doit avoir accès, et avec quel rôle ?
- [ ] **Formation de l'équipe** : prévoir une session de prise en main d'1 h avec
      ce guide en support.
