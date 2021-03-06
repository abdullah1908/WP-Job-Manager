<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Abstract class for an email notification.
 *
 * Do not rely on WordPress global variables or functions that rely on global variables such as `wp_get_current_user()`.
 * Email might be generated when no longer in scope. Instead, pass the values on as an argument when initiating the email
 * notification.
 *
 * Additionally, inside of plugins and themes, load email notification files based on this class inside the
 * `job_manager_email_init` hook. This will prevent unnecessary loading and won't include the files if this abstract
 * class isn't available.
 *
 * Example:
 * ```
 * add_action( 'job_manager_email_init', 'custom_plugin_include_emails' );
 * function custom_plugin_include_emails() {
 *     include_once 'emails/custom-plugin-sent-resume.php`;
 * }
 * ```
 *
 * @package wp-job-manager
 *
 * @since 1.31.0
 */

abstract class WP_Job_Manager_Email {
	/**
	 * @var array
	 */
	private $args = array();

	/**
	 * WP_Job_Manager_Email constructor.
	 *
	 * @param array $args Arguments used in forming email notification.
	 */
	final public function __construct( $args ) {
		$this->args = $this->prepare_args( (array) $args );
	}

	/**
	 * Get the unique email notification key.
	 *
	 * @type abstract
	 *
	 * @return string
	 */
	public static function get_key() {
		return false;
	}

	/**
	 * Get the friendly name for this email notification.
	 *
	 * @type abstract
	 * @return string
	 */
	public static function get_name() {
		return false;
	}

	/**
	 * Expand arguments as necessary for the generation of the email.
	 *
	 * @param $args
	 * @return mixed
	 */
	protected function prepare_args( $args ) {
		return $args;
	}

	/**
	 * Get the email subject.
	 *
	 * @return string
	 */
	abstract public function get_subject();

	/**
	 * Get `From:` address header value. Can be simple email or formatted `Firstname Lastname <email@example.com>`.
	 *
	 * @return string|bool Email from value or false to use WordPress' default.
	 */
	abstract public function get_from();

	/**
	 * Get array or comma-separated list of email addresses to send message.
	 *
	 * @return string|array
	 */
	abstract public function get_to();

	/**
	 * Get the rich text version of the email content.
	 *
	 * @return string
	 */
	abstract public function get_rich_content();

	/**
	 * Returns the list of file paths to attach to an email.
	 *
	 * @return array
	 */
	public function get_attachments() {
		return array();
	}

	/**
	 * Checks the arguments and returns whether the email notification is properly set up.
	 *
	 * @return bool
	 */
	abstract public function is_valid();

	/**
	 * Returns the value of the CC header, if needed.
	 *
	 * @return string|null
	 */
	public function get_cc() {
		return null;
	}

	/**
	 * Get the base headers for the email. No need to add CC or From headers. Content-type is added when sending rich-text.
	 *
	 * @return array
	 */
	public function get_headers() {
		return array();
	}

	/**
	 * Get the plaintext version of the email content.
	 *
	 * @return string
	 */
	public function get_plain_content() {
		return strip_tags( $this->get_rich_content() );
	}

	/**
	 * Returns the args that the email notification was sent with.
	 *
	 * @return array
	 */
	final protected function get_args() {
		return $this->args;
	}

}
