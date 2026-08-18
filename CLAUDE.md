# Projet : Site web B2B AFSAC (centre de formation OACI)

## Contexte

Site commercial B2B multilingue (FR / EN, + contenus AR en RTL) pour vendre
des formations aviation civile (TRAINAIR PLUS = 11 areas OACI ~490 cours,
AVSEC FR/ENG/ARAB) et des services (conseil, audit, sur-mesure).
Objectif : génération de leads qualifiés, SEO international, réassurance.

## Stack & décisions d'architecture

- WordPress CLASSIQUE (PAS headless).
- Logique métier dans un PLUGIN custom : `afsac-core`
  (CPT, taxonomies, intégrations). Jamais dans le thème.
- Présentation dans un thème custom.
- ACF Pro pour les champs structurés.
- Polylang Pro pour le multilingue + hreflang.
- Rank Math pour le SEO. Fluent Forms pour les formulaires.

## Modèle de contenu

- CPT `formation` (taxonomies : area, famille, langue, modalite)
- CPT `session` (liée à une formation, avec date/lieu/langue)
- CPT `temoignage`, CPT `reference`
- Pages éditoriales en blocs Gutenberg natifs.

## Conventions de code

- Respecter les WordPress Coding Standards (PHP).
- Préfixer toutes les fonctions/hooks/CPT par `afsac_`.
- Échapper toutes les sorties (esc_html, esc_url, esc_attr) et
  sécuriser les entrées (sanitize_*, nonces sur les formulaires).
- Internationaliser toutes les chaînes : __('...', 'afsac').
- Prévoir le RTL pour l'arabe dans le CSS.

## À NE PAS faire

- Ne pas hardcoder de texte non traduisible.
- Ne pas mettre de CPT/taxonomie dans le thème.
- Ne pas modifier le core WordPress.

## Commandes utiles (WP-CLI dispo via Local site shell)

- wp plugin install <slug> --activate
- wp post list --post_type=formation
- wp rewrite flush
