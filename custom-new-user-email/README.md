# Email Nouvel Utilisateur Personnalisé (Plugin WordPress)

Personnalisez l’email qu’un utilisateur reçoit lorsqu’un administrateur crée son compte et que WordPress lui demande de définir un mot de passe.

## Fonctionnalités

- Remplace le contenu de l’email de notification WordPress par défaut pour les nouveaux utilisateurs
- Objet et message personnalisables avec placeholders
- Mode email HTML optionnel (avec balises HTML sûres)
- Nom et email de l’expéditeur optionnels
- Activation/désactivation depuis l’administration WordPress
- Bouton d’email de test en un clic
- Email destinataire de prévisualisation optionnel
- Contenu d’email séparé pour les utilisateurs avec `genre=F`
- Bouton de prévisualisation en direct pour les deux contenus (M/défaut et F)

## Placeholders

Utilisez ces placeholders dans l’objet et le message :

- `{site_name}`
- `{username}`
- `{user_email}`
- `{set_password_url}`
- `{login_url}`
- `{meta:votre_cle_meta}` (pour les meta utilisateur)

Exemples :

- `{meta:parrain}`
- `{meta:first_name}`

## Installation

1. Copiez le dossier `custom-new-user-email` dans `wp-content/plugins/`.
2. Activez **Email Nouvel Utilisateur** dans **Extensions**.
3. Allez dans **Réglages > Email Nouvel Utilisateur**.
4. (Optionnel) Activez **Envoyer en HTML**.
5. Mettez à jour l’objet/le message puis enregistrez.
6. (Optionnel) Renseignez **Email destinataire de prévisualisation**.
7. Utilisez **Envoyer un email de test** pour recevoir un aperçu à cette adresse (ou à l’email admin actuel si vide).

## Contenu selon le genre

- **Message email (M / défaut)** est utilisé pour les utilisateurs avec la meta `genre = M` et comme fallback pour toute autre valeur.
- **Message email pour genre F** est utilisé quand la meta `genre = F`.
- Exemple d’utilisation d’une meta personnalisée dans le message : `{meta:parrain}`.

## Notes

- Le plugin utilise le hook `wp_new_user_notification_email`.
- Il fonctionne quand un administrateur crée un utilisateur et envoie l’email standard de création de compte.
