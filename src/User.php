<?php
/**
 * WordPress User Anonymization Utilities
 *
 * This class provides utility methods for anonymizing WordPress user data
 * including user accounts, user meta, and bulk operations while maintaining
 * database integrity and WordPress functionality.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class User {

	/**
	 * Anonymize WordPress user data.
	 *
	 * @param int   $user_id The user ID to anonymize.
	 * @param array $fields  Optional. Specific fields to anonymize.
	 *
	 * @return bool True on success, false on failure.
	 * @example Output: Email becomes "deleted@site.invalid", name becomes "J**n S***h"
	 *
	 * @example Input:  User ID 123 with email "john@example.com", name "John Smith"
	 */
	public static function anonymize_data( int $user_id, array $fields = [] ): bool {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		// Default fields to anonymize
		$default_fields = [
			'user_email',
			'user_nicename',
			'user_url',
			'display_name',
			'first_name',
			'last_name'
		];

		$fields_to_anonymize = empty( $fields ) ? $default_fields : $fields;
		$user_data           = [];

		foreach ( $fields_to_anonymize as $field ) {
			switch ( $field ) {
				case 'user_email':
					$user_data[ $field ] = Email::placeholder( $user->user_email );
					break;
				case 'user_nicename':
				case 'display_name':
				case 'first_name':
				case 'last_name':
					$user_data[ $field ] = Personal::name( $user->$field );
					break;
				case 'user_url':
					$user_data[ $field ] = Web::url( $user->user_url );
					break;
			}
		}

		if ( empty( $user_data ) ) {
			return false;
		}

		$user_data['ID'] = $user_id;
		$result          = wp_update_user( $user_data );

		return ! is_wp_error( $result );
	}

	/**
	 * Anonymize WordPress user meta data.
	 *
	 * @param int   $user_id   The user ID.
	 * @param array $meta_keys Optional. Specific meta keys to anonymize.
	 *
	 * @return bool True on success, false on failure.
	 * @example Output: billing_first_name becomes "J**n", billing_phone becomes "*****4567"
	 *
	 * @example Input:  User meta with billing_first_name "John", billing_phone "555-123-4567"
	 */
	public static function anonymize_meta( int $user_id, array $meta_keys = [] ): bool {
		if ( ! get_userdata( $user_id ) ) {
			return false;
		}

		// Default meta keys that commonly contain personal data
		$default_meta_keys = [
			'billing_first_name',
			'billing_last_name',
			'billing_email',
			'billing_phone',
			'billing_address_1',
			'billing_address_2',
			'billing_postcode',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_postcode'
		];

		$keys_to_anonymize = empty( $meta_keys ) ? $default_meta_keys : $meta_keys;

		foreach ( $keys_to_anonymize as $meta_key ) {
			$current_value = get_user_meta( $user_id, $meta_key, true );
			if ( empty( $current_value ) ) {
				continue;
			}

			$anonymized_value = self::anonymize_meta_value( $meta_key, $current_value );
			update_user_meta( $user_id, $meta_key, $anonymized_value );
		}

		return true;
	}

	/**
	 * Check if a user appears to be anonymized.
	 *
	 * @param int $user_id The user ID to check.
	 *
	 * @return bool True if the user appears anonymized, false otherwise.
	 */
	public static function is_anonymized( int $user_id ): bool {
		return Validate::is_user_anonymized( $user_id );
	}

	/**
	 * Bulk anonymize multiple users.
	 *
	 * @param array $user_ids Array of user IDs to anonymize.
	 * @param array $fields   Optional. Specific fields to anonymize.
	 *
	 * @return array Results array with success/failure for each user.
	 * @example Output: [123 => true, 456 => true, 789 => false] (success/failure for each)
	 *
	 * @example Input:  [123, 456, 789] (user IDs)
	 */
	public static function bulk_anonymize( array $user_ids, array $fields = [] ): array {
		$results = [];

		foreach ( $user_ids as $user_id ) {
			$results[ $user_id ] = self::anonymize_data( $user_id, $fields );
		}

		return $results;
	}

	/**
	 * Get anonymized user data for export.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return array Anonymized user data.
	 */
	public static function get_anonymized_export( int $user_id ): array {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return [];
		}

		return [
			'ID'           => $user_id,
			'user_email'   => Email::anonymize( $user->user_email ),
			'display_name' => Personal::name( $user->display_name ),
			'first_name'   => Personal::name( $user->first_name ),
			'last_name'    => Personal::name( $user->last_name ),
			'user_url'     => Web::url( $user->user_url ),
		];
	}

	/**
	 * Anonymize meta value based on meta key patterns.
	 *
	 * @param string $meta_key The meta key.
	 * @param mixed  $value    The meta value.
	 *
	 * @return mixed The anonymized value.
	 */
	private static function anonymize_meta_value( string $meta_key, $value ) {
		$key_lower = strtolower( $meta_key );

		if ( str_contains( $key_lower, 'email' ) ) {
			return Email::placeholder( $value );
		}
		if ( str_contains( $key_lower, 'phone' ) ) {
			return Personal::phone( $value );
		}
		if ( str_contains( $key_lower, 'address' ) ) {
			return Personal::address( $value );
		}
		if ( str_contains( $key_lower, 'postcode' ) || str_contains( $key_lower, 'zip' ) ) {
			return Personal::zipcode( $value );
		}
		if ( str_contains( $key_lower, 'name' ) ) {
			return Personal::name( $value );
		}

		// Default: anonymize as text
		return Personal::text( $value );
	}

}