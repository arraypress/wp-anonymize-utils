<?php
/**
 * Email Anonymization Utilities
 *
 * This class provides utility methods for anonymizing and masking email addresses.
 * It includes methods for full anonymization, partial masking, and validation of
 * anonymized email addresses while preserving domain structure where needed.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class Email {

	/**
	 * The standard anonymized email placeholder.
	 */
	const ANONYMIZED_PLACEHOLDER = 'deleted@site.invalid';

	/**
	 * Anonymize an email address by replacing the local part with asterisks.
	 *
	 * @param string $email The email address to anonymize.
	 *
	 * @return string The anonymized email address.
	 * @example Output: "jo*****@ex*****.com"
	 * @example Input:  "user@subdomain.example.co.uk"
	 * @example Output: "us***@su*******.example.co.uk"
	 *
	 * @example Input:  "john.doe@example.com"
	 */
	public static function anonymize( string $email ): string {
		if ( ! is_email( $email ) ) {
			return '';
		}

		[ $name, $domain ] = explode( '@', $email );
		$domain_parts = explode( '.', $domain );

		// Anonymize the local part (username)
		$anonymized_name = substr( $name, 0, 2 ) . str_repeat( '*', max( strlen( $name ) - 2, 3 ) );

		// Only anonymize the first part of the domain, preserve the rest
		if ( count( $domain_parts ) > 1 ) {
			$first_domain_part     = $domain_parts[0];
			$anonymized_first_part = substr( $first_domain_part, 0, 2 ) . str_repeat( '*', max( strlen( $first_domain_part ) - 2, 3 ) );

			// Rebuild domain: anonymized_first_part + all remaining parts
			$remaining_parts   = array_slice( $domain_parts, 1 );
			$anonymized_domain = $anonymized_first_part . '.' . implode( '.', $remaining_parts );
		} else {
			// Single part domain (shouldn't happen with valid emails, but just in case)
			$anonymized_domain = substr( $domain, 0, 2 ) . str_repeat( '*', max( strlen( $domain ) - 2, 3 ) );
		}

		return $anonymized_name . '@' . $anonymized_domain;
	}

	/**
	 * Mask an email address for display while preserving readability.
	 *
	 * @param string $email      The email address to mask.
	 * @param int    $show_first Number of characters to show at the start of the local part.
	 * @param int    $show_last  Number of characters to show at the end of the local part.
	 *
	 * @return string The masked email address.
	 * @example Output: "j**n@example.com"
	 *
	 * @example Input:  "john@example.com" (show_first: 1, show_last: 1)
	 */
	public static function mask( string $email, int $show_first = 1, int $show_last = 1 ): string {
		if ( ! is_email( $email ) ) {
			return '';
		}

		[ $name, $domain ] = explode( '@', $email );
		$name_length = strlen( $name );

		if ( $show_first + $show_last >= $name_length ) {
			return $email; // If showing all characters, return the original email
		}

		$masked_length = $name_length - $show_first - $show_last;
		$masked_name   = substr( $name, 0, $show_first ) . str_repeat( '*', $masked_length ) . substr( $name, - $show_last );

		return $masked_name . '@' . $domain;
	}

	/**
	 * Check if an email address is already anonymized.
	 *
	 * @param string $email The email address to check.
	 *
	 * @return bool True if the email is anonymized, false otherwise.
	 * @example Input:  "deleted@site.invalid" or "jo***@ex****.com"
	 * @example Output: true
	 */
	public static function is_anonymized( string $email ): bool {
		return Validate::is_anonymized( $email );
	}

	/**
	 * Replace an email with the standard anonymized placeholder.
	 * Useful for GDPR compliance and maintaining database integrity.
	 *
	 * @param string $email The email address to replace.
	 *
	 * @return string The anonymized placeholder email.
	 * @example Output: "deleted@site.invalid"
	 *
	 * @example Input:  "user@example.com"
	 */
	public static function placeholder( string $email ): string {
		return is_email( $email ) ? self::ANONYMIZED_PLACEHOLDER : '';
	}

	/**
	 * Anonymize multiple email addresses.
	 *
	 * @param array $emails Array of email addresses to anonymize.
	 *
	 * @return array Array of anonymized email addresses.
	 * @example Output: ["us***@ex*****.com", "us***@te**.org"]
	 *
	 * @example Input:  ["user1@example.com", "user2@test.org"]
	 */
	public static function anonymize_multiple( array $emails ): array {
		return array_map( [ self::class, 'anonymize' ], $emails );
	}

	/**
	 * Get the anonymized placeholder email.
	 *
	 * @return string The standard anonymized email placeholder.
	 */
	public static function get_placeholder(): string {
		return self::ANONYMIZED_PLACEHOLDER;
	}

}