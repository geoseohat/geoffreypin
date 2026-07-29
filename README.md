# Portail d’applications Geoffrey Pin

Portail PHP/HTML déployé dans `/home/dxin1098/public_html/mailer`.

## Applications

- `index.php` : accueil du portail
- `mailer.php` : interface du Mailer
- `api.php`, `oauth.php`, `install.php` : services du Mailer
- `calculateur/index.html` : calculateur de saponification

## Configuration sensible

Le véritable fichier `config.php` reste uniquement sur cPanel. Il contient les paramètres de base de données et OAuth Gmail et ne doit jamais être ajouté au dépôt.

## Déploiement

Le déploiement normal est effectué par SSH après mise à jour de GitHub. Le fichier `.cpanel.yml` reste disponible comme solution de repli.
