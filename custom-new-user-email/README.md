# Custom New User Email (WordPress Plugin)

Customize the email a user receives when an administrator creates the account and WordPress prompts them to set a password.

## Features

- Replaces the default new-user notification email content
- Custom subject and message with placeholders
- Optional HTML email mode (with safe HTML tags)
- Optional custom sender name and sender email
- Enable/disable switch from WordPress admin
- One-click test email button for template preview
- Optional preview recipient email for test sends
- Separate email content for users with `genre=F`
- Live preview button for both email contents (M/default and F)

## Placeholders

Use these placeholders in subject and message:

- `{site_name}`
- `{username}`
- `{user_email}`
- `{set_password_url}`
- `{login_url}`
- `{meta:your_meta_key}` (for user meta)

Examples:

- `{meta:parrain}`
- `{meta:first_name}`

## Install

1. Copy the `custom-new-user-email` folder into `wp-content/plugins/`.
2. Activate **Custom New User Email** in **Plugins**.
3. Go to **Settings > Custom New User Email**.
4. (Optional) Enable **Send as HTML email**.
5. Update subject/message and save.
6. (Optional) Set **Preview recipient email**.
7. Use **Send test email** to receive a preview at that address (or your current admin email if empty).

## Genre-based content

- **Email message (M / default)** is used for users with meta `genre = M` and as fallback for any other value.
- **Email message for genre F** is used when user meta `genre = F`.
- Example custom meta usage in message: `{meta:parrain}`.

## Notes

- The plugin hooks into `wp_new_user_notification_email`.
- It works when the admin creates a user and sends the standard account email.
