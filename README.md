# Outil mailing

Application PHP déployée par cPanel dans `/home/dxjnl098/public_html/mailer`.

## Configuration

Le véritable fichier `config.php` contient les identifiants de base de données et les paramètres OAuth Gmail. Il est volontairement exclu de Git.

1. Copier `config.example.php` en `config.php` directement sur cPanel.
2. Renseigner les paramètres réels dans `config.php`.
3. Ne jamais ajouter `config.php` au dépôt.

## Déploiement cPanel

Dans **Git Version Control → Gérer** :

1. **Update from Remote**
2. **Deploy HEAD Commit**

Le déploiement remplace uniquement les fichiers applicatifs listés dans `.cpanel.yml`. Il ne supprime pas le `config.php` déjà présent sur le serveur.
