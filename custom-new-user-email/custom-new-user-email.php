<?php
/**
 * Plugin Name: Custom New User Email
 * Description: Customize the email sent to users when an administrator creates their account and they need to set a password.
 * Version: 1.3.0
 * Author: Custom
 * License: GPL-2.0-or-later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CNE_Custom_New_User_Email {
	const OPTION_KEY = 'cne_new_user_email_settings';

	public function __construct() {
		add_filter( 'wp_new_user_notification_email', array( $this, 'filter_new_user_email' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_cne_send_test_email', array( $this, 'handle_send_test_email' ) );
	}

	public static function defaults() {
		return array(
			'enabled'    => 1,
			'send_html'  => 0,
			'from_name'  => '',
			'from_email' => '',
			'preview_email' => '',
			'subject'    => 'Welcome to {site_name}',
			'message'    => "Hi {username},\n\nYour account has been created on {site_name}.\n\nSet your password here:\n{set_password_url}\n\nThen log in at:\n{login_url}\n\nIf you did not expect this account, please ignore this email.",
		);
	}

	public function get_settings() {
		$settings = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args( $settings, self::defaults() );
	}

	public function register_settings_page() {
		add_options_page(
			__( 'Custom New User Email', 'custom-new-user-email' ),
			__( 'Custom New User Email', 'custom-new-user-email' ),
			'manage_options',
			'custom-new-user-email',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'cne_new_user_email_group',
			self::OPTION_KEY,
			array( $this, 'sanitize_settings' )
		);
	}

	public function sanitize_settings( $input ) {
		$defaults = self::defaults();
		$send_html = isset( $input['send_html'] ) ? 1 : 0;
		$message   = isset( $input['message'] ) ? wp_unslash( $input['message'] ) : $defaults['message'];

		if ( $send_html ) {
			$message = wp_kses_post( $message );
		} else {
			$message = sanitize_textarea_field( $message );
		}

		return array(
			'enabled'    => isset( $input['enabled'] ) ? 1 : 0,
			'send_html'  => $send_html,
			'from_name'  => isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : $defaults['from_name'],
			'from_email' => isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : $defaults['from_email'],
			'preview_email' => isset( $input['preview_email'] ) ? sanitize_email( $input['preview_email'] ) : $defaults['preview_email'],
			'subject'    => isset( $input['subject'] ) ? sanitize_text_field( $input['subject'] ) : $defaults['subject'],
			'message'    => $message,
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->get_settings();
		$notice   = isset( $_GET['cne_notice'] ) ? sanitize_key( wp_unslash( $_GET['cne_notice'] ) ) : '';
		$meta_example = '{meta:parrain}';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Custom New User Email', 'custom-new-user-email' ); ?></h1>
			<p><?php esc_html_e( 'Customize the account creation email sent when an admin creates a user and WordPress asks them to set a password.', 'custom-new-user-email' ); ?></p>

			<?php if ( 'test_sent' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Test email sent successfully.', 'custom-new-user-email' ); ?></p></div>
			<?php elseif ( 'test_error' === $notice ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Test email could not be sent. Check your mail configuration.', 'custom-new-user-email' ); ?></p></div>
			<?php elseif ( 'test_missing_email' === $notice ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'No email found for the current admin user.', 'custom-new-user-email' ); ?></p></div>
			<?php elseif ( 'test_invalid_email' === $notice ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Preview recipient email is not valid.', 'custom-new-user-email' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'cne_new_user_email_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable custom email', 'custom-new-user-email' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled]" value="1" <?php checked( 1, (int) $settings['enabled'] ); ?> />
								<?php esc_html_e( 'Replace default WordPress email for new users', 'custom-new-user-email' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Email format', 'custom-new-user-email' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[send_html]" value="1" <?php checked( 1, (int) $settings['send_html'] ); ?> />
								<?php esc_html_e( 'Send as HTML email', 'custom-new-user-email' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When enabled, basic HTML tags are allowed in the message template.', 'custom-new-user-email' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="cne_from_name"><?php esc_html_e( 'From name', 'custom-new-user-email' ); ?></label></th>
						<td>
							<input id="cne_from_name" class="regular-text" type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[from_name]" value="<?php echo esc_attr( $settings['from_name'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Optional. Leave empty to use WordPress default sender name.', 'custom-new-user-email' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="cne_from_email"><?php esc_html_e( 'From email', 'custom-new-user-email' ); ?></label></th>
						<td>
							<input id="cne_from_email" class="regular-text" type="email" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[from_email]" value="<?php echo esc_attr( $settings['from_email'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Optional. Leave empty to use WordPress default sender email.', 'custom-new-user-email' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="cne_subject"><?php esc_html_e( 'Email subject', 'custom-new-user-email' ); ?></label></th>
						<td>
							<input id="cne_subject" class="regular-text" type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[subject]" value="<?php echo esc_attr( $settings['subject'] ); ?>" required />
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="cne_preview_email"><?php esc_html_e( 'Preview recipient email', 'custom-new-user-email' ); ?></label></th>
						<td>
							<input id="cne_preview_email" class="regular-text" type="email" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[preview_email]" value="<?php echo esc_attr( $settings['preview_email'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Optional. Used by “Send test email”. If empty, your current admin email is used.', 'custom-new-user-email' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="cne_message"><?php esc_html_e( 'Email message', 'custom-new-user-email' ); ?></label></th>
						<td>
							<textarea id="cne_message" class="large-text code" rows="12" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[message]" required><?php echo esc_textarea( $settings['message'] ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Available placeholders:', 'custom-new-user-email' ); ?>
								<code>{site_name}</code>,
								<code>{username}</code>,
								<code>{user_email}</code>,
								<code>{set_password_url}</code>,
								<code>{login_url}</code>,
								<code><?php echo esc_html( $meta_example ); ?></code>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr />
			<h2><?php esc_html_e( 'Email Preview', 'custom-new-user-email' ); ?></h2>
			<p><?php esc_html_e( 'Send a test email using your saved settings to the preview recipient email (or your current admin email if empty).', 'custom-new-user-email' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="cne_send_test_email" />
				<?php wp_nonce_field( 'cne_send_test_email' ); ?>
				<?php submit_button( __( 'Send test email', 'custom-new-user-email' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	public function handle_send_test_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'custom-new-user-email' ) );
		}

		check_admin_referer( 'cne_send_test_email' );

		$settings     = $this->get_settings();
		$current_user = wp_get_current_user();
		$to_email     = ! empty( $settings['preview_email'] ) ? sanitize_email( $settings['preview_email'] ) : '';

		if ( empty( $to_email ) ) {
			$to_email = isset( $current_user->user_email ) ? sanitize_email( $current_user->user_email ) : '';
		}

		if ( empty( $to_email ) ) {
			$this->redirect_with_notice( 'test_missing_email' );
		}

		if ( ! is_email( $to_email ) ) {
			$this->redirect_with_notice( 'test_invalid_email' );
		}

		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$replacements = array(
			'{site_name}'        => $blogname,
			'{username}'         => $current_user->user_login,
			'{user_email}'       => $to_email,
			'{set_password_url}' => esc_url_raw( wp_lostpassword_url() ),
			'{login_url}'        => wp_login_url(),
		);

		$subject = $this->replace_placeholders( $settings['subject'], $replacements, $current_user );
		$message = $this->replace_placeholders( $settings['message'], $replacements, $current_user );
		$headers = array();

		if ( ! empty( $settings['send_html'] ) ) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}

		if ( ! empty( $settings['from_name'] ) && ! empty( $settings['from_email'] ) ) {
			$headers[] = sprintf( 'From: %s <%s>', $settings['from_name'], $settings['from_email'] );
		} elseif ( ! empty( $settings['from_email'] ) ) {
			$headers[] = sprintf( 'From: <%s>', $settings['from_email'] );
		}

		$sent = wp_mail( $to_email, $subject, $message, $headers );

		$this->redirect_with_notice( $sent ? 'test_sent' : 'test_error' );
	}

	private function redirect_with_notice( $notice ) {
		$url = add_query_arg(
			array(
				'page'       => 'custom-new-user-email',
				'cne_notice' => sanitize_key( $notice ),
			),
			admin_url( 'options-general.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}

	private function replace_placeholders( $content, $replacements, $user ) {
		$content = strtr( (string) $content, (array) $replacements );

		if ( ! ( $user instanceof WP_User ) ) {
			return $content;
		}

		return preg_replace_callback(
			'/\{meta:([^}]+)\}/',
			static function ( $matches ) use ( $user ) {
				$meta_key = sanitize_key( trim( $matches[1] ) );

				if ( '' === $meta_key ) {
					return '';
				}

				$meta_value = get_user_meta( $user->ID, $meta_key, true );

				if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
					return '';
				}

				return (string) $meta_value;
			},
			$content
		);
	}

	public function filter_new_user_email( $wp_new_user_notification_email, $user, $blogname ) {
		$settings = $this->get_settings();

		if ( empty( $settings['enabled'] ) ) {
			return $wp_new_user_notification_email;
		}

		if ( ! ( $user instanceof WP_User ) ) {
			return $wp_new_user_notification_email;
		}

		$set_password_url = '';

		if ( preg_match( '#https?://[^\s]+#', (string) $wp_new_user_notification_email['message'], $matches ) ) {
			$set_password_url = $matches[0];
		}

		if ( empty( $set_password_url ) ) {
			$set_password_url = wp_lostpassword_url();
		}

		$replacements = array(
			'{site_name}'        => wp_specialchars_decode( $blogname, ENT_QUOTES ),
			'{username}'         => $user->user_login,
			'{user_email}'       => $user->user_email,
			'{set_password_url}' => esc_url_raw( $set_password_url ),
			'{login_url}'        => wp_login_url(),
		);

		$subject = $this->replace_placeholders( $settings['subject'], $replacements, $user );
		$message = $this->replace_placeholders( $settings['message'], $replacements, $user );

		$wp_new_user_notification_email['subject'] = $subject;
		$wp_new_user_notification_email['message'] = $message;

		$headers = isset( $wp_new_user_notification_email['headers'] ) ? (array) $wp_new_user_notification_email['headers'] : array();

		if ( ! empty( $settings['send_html'] ) ) {
			$headers = array_filter(
				$headers,
				static function( $header ) {
					return 0 !== stripos( $header, 'Content-Type:' );
				}
			);

			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}

		if ( ! empty( $settings['from_name'] ) && ! empty( $settings['from_email'] ) ) {
			$headers[] = sprintf( 'From: %s <%s>', $settings['from_name'], $settings['from_email'] );
		} elseif ( ! empty( $settings['from_email'] ) ) {
			$headers[] = sprintf( 'From: <%s>', $settings['from_email'] );
		}

		$wp_new_user_notification_email['headers'] = $headers;

		return $wp_new_user_notification_email;
	}
}

new CNE_Custom_New_User_Email();
