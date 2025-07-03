<?php
/**
 * Financial Data Anonymization Utilities
 *
 * This class provides utility methods for anonymizing financial information
 * commonly found in WordPress e-commerce sites such as credit card numbers,
 * bank accounts, and tax IDs while preserving format for validation.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class Financial {

	/**
	 * Anonymize a credit card number.
	 *
	 * @param string $card_number The credit card number to anonymize.
	 * @param int    $keep_last   Number of digits to keep at the end.
	 *
	 * @return string The anonymized credit card number.
	 * @example Output: "************9012"
	 *
	 * @example Input:  "4532-1234-5678-9012"
	 */
	public static function credit_card( string $card_number, int $keep_last = 4 ): string {
		if ( empty( $card_number ) ) {
			return '';
		}

		$digits = preg_replace( '/[^0-9]/', '', $card_number );
		$length = strlen( $digits );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize a bank account number.
	 *
	 * @param string $account_number The bank account number to anonymize.
	 * @param int    $keep_last      Number of digits to keep at the end.
	 *
	 * @return string The anonymized bank account number.
	 * @example Output: "********9012"
	 *
	 * @example Input:  "123456789012"
	 */
	public static function bank_account( string $account_number, int $keep_last = 4 ): string {
		if ( empty( $account_number ) ) {
			return '';
		}

		$digits = preg_replace( '/[^0-9]/', '', $account_number );
		$length = strlen( $digits );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize a Tax ID or EIN number.
	 *
	 * @param string $tax_id    The Tax ID to anonymize.
	 * @param int    $keep_last Number of digits to keep at the end.
	 *
	 * @return string The anonymized Tax ID.
	 * @example Output: "******6789"
	 *
	 * @example Input:  "12-3456789"
	 */
	public static function tax_id( string $tax_id, int $keep_last = 4 ): string {
		if ( empty( $tax_id ) ) {
			return '';
		}

		$digits = preg_replace( '/[^0-9]/', '', $tax_id );
		$length = strlen( $digits );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Check if a financial identifier appears to be anonymized.
	 *
	 * @param string $identifier The identifier to check.
	 *
	 * @return bool True if the identifier appears anonymized, false otherwise.
	 * @example Input:  "************9012"
	 * @example Output: true
	 */
	public static function is_anonymized( string $identifier ): bool {
		return Validate::is_anonymized( $identifier );
	}

	/**
	 * Anonymize multiple financial identifiers.
	 *
	 * @param array $identifiers Array of financial identifiers to anonymize.
	 * @param array $types       Array mapping keys to anonymization types.
	 *
	 * @return array Array of anonymized identifiers.
	 * @example Output: ["card" => "************9012", "account" => "*****6789"]
	 *
	 * @example Input:  ["card" => "4532123456789012", "account" => "123456789"]
	 */
	public static function anonymize_multiple( array $identifiers, array $types = [] ): array {
		$anonymized = [];

		foreach ( $identifiers as $key => $value ) {
			if ( empty( $value ) ) {
				$anonymized[ $key ] = $value;
				continue;
			}

			$type = $types[ $key ] ?? self::detect_type( $key );

			switch ( $type ) {
				case 'credit_card':
					$anonymized[ $key ] = self::credit_card( $value );
					break;
				case 'bank_account':
					$anonymized[ $key ] = self::bank_account( $value );
					break;
				case 'tax_id':
					$anonymized[ $key ] = self::tax_id( $value );
					break;
				default:
					$anonymized[ $key ] = self::credit_card( $value ); // Default fallback
					break;
			}
		}

		return $anonymized;
	}

	/**
	 * Detect financial identifier type based on field name.
	 *
	 * @param string $field_name The field name to analyze.
	 *
	 * @return string The detected type.
	 */
	private static function detect_type( string $field_name ): string {
		$field_name = strtolower( $field_name );

		if ( str_contains( $field_name, 'credit' ) || str_contains( $field_name, 'card' ) ) {
			return 'credit_card';
		}
		if ( str_contains( $field_name, 'bank' ) || str_contains( $field_name, 'account' ) ) {
			return 'bank_account';
		}
		if ( str_contains( $field_name, 'tax' ) || str_contains( $field_name, 'ein' ) ) {
			return 'tax_id';
		}

		return 'credit_card'; // Default fallback
	}

}