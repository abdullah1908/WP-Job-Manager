<?php
/**
 * Defines a class with methods for cleaning up plugin data. To be used when
 * the plugin is deleted.
 *
 * @package Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

/**
 * Methods for cleaning up all plugin data.
 *
 * @author Automattic
 * @since 1.31.0
 */
class WP_Job_Manager_Data_Cleaner {

	/**
	 * Custom post types to be deleted.
	 *
	 * @var $custom_post_types
	 */
	private static $custom_post_types = array(
		'job_listing',
	);

	/**
	 * Taxonomies to be deleted.
	 *
	 * @var $taxonomies
	 */
	private static $taxonomies = array(
		'job_listing_category',
		'job_listing_type',
	);

	/**
	 * Role to be removed.
	 *
	 * @var $role
	 */
	private static $role = 'employer';

	/**
	 * Capabilities to be deleted.
	 *
	 * @var $caps
	 */
	private static $caps = array(
		'manage_job_listings',
		'edit_job_listing',
		'read_job_listing',
		'delete_job_listing',
		'edit_job_listings',
		'edit_others_job_listings',
		'publish_job_listings',
		'read_private_job_listings',
		'delete_job_listings',
		'delete_private_job_listings',
		'delete_published_job_listings',
		'delete_others_job_listings',
		'edit_private_job_listings',
		'edit_published_job_listings',
		'manage_job_listing_terms',
		'edit_job_listing_terms',
		'delete_job_listing_terms',
		'assign_job_listing_terms',
	);

	/**
	 * Cleanup all data.
	 *
	 * @access public
	 */
	public static function cleanup_all() {
		self::cleanup_custom_post_types();
		self::cleanup_taxonomies();
		self::cleanup_pages();
		self::cleanup_roles_and_caps();
	}

	/**
	 * Cleanup data for custom post types.
	 *
	 * @access private
	 */
	private static function cleanup_custom_post_types() {
		foreach ( self::$custom_post_types as $post_type ) {
			$items = get_posts( array(
				'post_type'   => $post_type,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
			) );

			foreach ( $items as $item ) {
				wp_trash_post( $item );
			}
		}
	}

	/**
	 * Cleanup data for taxonomies.
	 *
	 * @access private
	 */
	private static function cleanup_taxonomies() {
		global $wpdb;

		foreach ( self::$taxonomies as $taxonomy ) {
			$terms = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT term_id, term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s",
					$taxonomy
				)
			);

			// Delete all data for each term.
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				$wpdb->delete( $wpdb->termmeta, array( 'term_id' => $term->term_id ) );
			}
		}
	}

	/**
	 * Cleanup data for pages.
	 *
	 * @access private
	 */
	private static function cleanup_pages() {
		// Trash the Submit Job page.
		$submit_job_form_page_id = get_option( 'job_manager_submit_job_form_page_id' );
		if ( $submit_job_form_page_id ) {
			wp_trash_post( $submit_job_form_page_id );
		}

		// Trash the Job Dashboard page.
		$job_dashboard_page_id = get_option( 'job_manager_job_dashboard_page_id' );
		if ( $job_dashboard_page_id ) {
			wp_trash_post( $job_dashboard_page_id );
		}

		// Trash the Jobs page.
		$jobs_page_id = get_option( 'job_manager_jobs_page_id' );
		if ( $jobs_page_id ) {
			wp_trash_post( $jobs_page_id );
		}
	}

	/**
	 * Cleanup data for roles and caps.
	 *
	 * @access private
	 */
	private static function cleanup_roles_and_caps() {
		global $wp_roles;

		// Remove caps from roles.
		$role_names = array_keys( $wp_roles->roles );
		foreach ( $role_names as $role_name ) {
			$role = get_role( $role_name );
			self::remove_all_job_manager_caps( $role );
		}

		// Remove caps and role from users.
		$users = get_users( array() );
		foreach ( $users as $user ) {
			self::remove_all_job_manager_caps( $user );
			$user->remove_role( self::$role );
		}

		// Remove role.
		remove_role( self::$role );
	}

	/**
	 * Helper method to remove WPJM caps from a user or role object.
	 *
	 * @param (WP_User|WP_Role) $object the user or role object.
	 */
	private static function remove_all_job_manager_caps( $object ) {
		foreach ( self::$caps as $cap ) {
			$object->remove_cap( $cap );
		}
	}
}
