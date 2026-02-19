# Custom New User Email (WordPress Plugin)

Customize the email a user receives when an administrator creates the account and WordPress prompts them to set a password.

## Features

- Replaces the default new-user notification email content
- Custom subject and message with placeholders
- Optional HTML email mode (with safe HTML tags)
- Optional custom sender name and sender email
- Enable/disable switch from WordPress admin

## Placeholders

Use these placeholders in subject and message:

- `{site_name}`
- `{username}`
- `{user_email}`
- `{set_password_url}`
- `{login_url}`

## Install

1. Copy the `custom-new-user-email` folder into `wp-content/plugins/`.
2. Activate **Custom New User Email** in **Plugins**.
3. Go to **Settings > Custom New User Email**.
4. (Optional) Enable **Send as HTML email**.
5. Update subject/message and save.

## Notes

- The plugin hooks into `wp_new_user_notification_email`.
- It works when the admin creates a user and sends the standard account email.
