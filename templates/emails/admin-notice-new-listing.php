<?php
/**
 * Email content when notifying admin of a new job listing.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/emails/admin-notice-new-listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @version     1.31.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * @var WP_Post $job
 */
$job = $args['job'];
?>
	<p><?php
		printf( __( 'A new job listing has been submitted titled <em>%s</em>.', 'wp-job-manager' ), esc_html( $job->post_title ) );
		switch ( $job->post_status ) {
			case 'publish':
				printf( ' ' . __( 'It has been published and is now available to the public.', 'wp-job-manager' ) );
				break;
			case 'pending':
				printf( ' ' . __( 'It is awaiting approval by an administrator in <a href="%s">WordPress admin</a>.', 'wp-job-manager' ), esc_url( admin_url( 'edit.php?post_type=job_listing' ) ) );
				break;
		}
		?></p>
<?php

/**
 * Show details about the job listing.
 *
 * @param WP_Post              $job            The job listing to show details for.
 * @param WP_Job_Manager_Email $email          Email object for the notification.
 * @param bool                 $sent_to_admin  True if this is being sent to an administrator.
 * @param bool                 $plain_text     True if the email is being sent as plain text.
 */
do_action( 'job_manager_email_job_details', $job, $email, true, $plain_text );
