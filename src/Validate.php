<?php

/**
 * Anonymization Validation Utilities
 *
 * This class provides utility methods for validating and checking if data
 * has been anonymized. It centralizes all anonymization detection logic
 * to avoid duplication across other classes.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class Validate {

	/**
	 * Check if any data appears to be anonymized.
	 *
	 * @param mixed $data The data to check.
	 *
	 * @return bool True if the data appears anonymized, false otherwise.
	 */
	public static function is_anonymized( $data ): bool {
		if ( empty( $data ) ) {
			return false;
		}

		$value = (string) $data;

		// Check for common anonymization patterns
		return str_contains( $value, '*' ) ||
		       $value === 'deleted@site.invalid' ||
		       str_ends_with( $value, '.0' ) ||
		       str_ends_with( $value, ':0' ) ||
		       $value === '::' ||
		       preg_match( '/^[A-Za-z\s]+ on [A-Za-z\s]+$/', $value ) === 1;
	}

	/**
	 * Check if a WordPress user appears to be anonymized.
	 *
	 * @param int $user_id The user ID to check.
	 *
	 * @return bool True if the user appears anonymized, false otherwise.
	 */
	public static function is_user_anonymized( int $user_id ): bool {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		return self::is_anonymized( $user->user_email ) ||
		       self::is_anonymized( $user->display_name );
	}

	/**
	 * Check if a WordPress comment appears to be anonymized.
	 *
	 * @param int $comment_id The comment ID to check.
	 *
	 * @return bool True if the comment appears anonymized, false otherwise.
	 */
	public static function is_comment_anonymized( int $comment_id ): bool {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		return self::is_anonymized( $comment->comment_author_email ) ||
		       self::is_anonymized( $comment->comment_author ) ||
		       self::is_anonymized( $comment->comment_author_IP );
	}

}