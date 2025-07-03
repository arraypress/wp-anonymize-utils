<?php
/**
 * Personal Data Anonymization Utilities
 *
 * This class provides utility methods for anonymizing personal information
 * such as names, addresses, phone numbers, and dates while preserving
 * structure and format for analytics purposes.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class Personal {

	/**
	 * Anonymize a name while preserving its structure.
	 *
	 * @param string $name The name to anonymize.
	 *
	 * @return string The anonymized name.
	 * @example Output: "J**n S***h"
	 *
	 * @example Input:  "John Smith"
	 */
	public static function name( string $name ): string {
		if ( empty( $name ) ) {
			return '';
		}

		$words = explode( ' ', trim( $name ) );

		return implode( ' ', array_map( function ( $word ) {
			$len = mb_strlen( $word );
			if ( $len <= 2 ) {
				return str_repeat( '*', $len );
			}

			return mb_substr( $word, 0, 1 ) . str_repeat( '*', $len - 2 ) . mb_substr( $word, - 1 );
		}, $words ) );
	}

	/**
	 * Anonymize a phone number by keeping only the last N digits.
	 *
	 * @param string $phone     The phone number to anonymize.
	 * @param int    $keep_last Number of digits to keep at the end.
	 *
	 * @return string The anonymized phone number.
	 * @example Output: "*****4567"
	 *
	 * @example Input:  "555-123-4567"
	 */
	public static function phone( string $phone, int $keep_last = 4 ): string {
		if ( empty( $phone ) ) {
			return '';
		}

		// Remove all non-digit characters
		$digits = preg_replace( '/[^0-9]/', '', $phone );
		$length = strlen( $digits );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize an address while preserving its structure.
	 *
	 * @param string $address The address to anonymize.
	 *
	 * @return string The anonymized address.
	 * @example Output: "*** **** ******"
	 * @example Input:  "456 François-Xavier Blvd"
	 * @example Output: "*** ********-****** ****"
	 *
	 * @example Input:  "123 Main Street"
	 */
	public static function address( string $address ): string {
		if ( empty( $address ) ) {
			return '';
		}

		// Split address into lines
		$lines = explode( "\n", $address );

		return implode( "\n", array_map( function ( $line ) {
			// Anonymize numbers but keep their length
			$line = preg_replace_callback( '/\d+/', function ( $matches ) {
				return str_repeat( '*', strlen( $matches[0] ) );
			}, $line );

			// Replace letters (including Unicode) with asterisks but keep spaces and punctuation
			return preg_replace( '/\p{L}/u', '*', $line );
		}, $lines ) );
	}

	/**
	 * Anonymize a date while preserving the month and year.
	 *
	 * @param string $date The date to anonymize (any standard format).
	 *
	 * @return string|null The anonymized date (YYYY-MM-**) or null on failure.
	 * @example Output: "1990-05-**"
	 *
	 * @example Input:  "1990-05-15"
	 */
	public static function date( string $date ): ?string {
		if ( empty( $date ) ) {
			return null;
		}

		$timestamp = strtotime( $date );
		if ( $timestamp === false ) {
			return null;
		}

		return date( 'Y-m-**', $timestamp );
	}

	/**
	 * Anonymize a zip or postal code.
	 *
	 * @param string $zipcode   The zip or postal code to anonymize.
	 * @param int    $keep_last Number of characters to keep at the end.
	 *
	 * @return string The anonymized zip code.
	 * @example Output: "**210"
	 *
	 * @example Input:  "90210"
	 */
	public static function zipcode( string $zipcode, int $keep_last = 3 ): string {
		if ( empty( $zipcode ) ) {
			return '';
		}

		$zipcode = preg_replace( '/[^a-zA-Z0-9]/', '', $zipcode );
		$length  = strlen( $zipcode );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $zipcode, - $keep_last );
	}

	/**
	 * Anonymize arbitrary text by replacing characters with asterisks while preserving length and structure.
	 *
	 * @param string $text           The text to anonymize.
	 * @param array  $preserve_chars Optional. Characters to preserve.
	 *
	 * @return string The anonymized text.
	 * @example Output: "**** ********* ****"
	 *
	 * @example Input:  "Some sensitive text"
	 */
	public static function text( string $text, array $preserve_chars = [ ' ', '.', '@', '-' ] ): string {
		if ( empty( $text ) ) {
			return '';
		}

		return preg_replace_callback( '/[^' . preg_quote( implode( '', $preserve_chars ), '/' ) . ']/', function ( $matches ) {
			return '*';
		}, $text );
	}

	/**
	 * Anonymize multiple personal data fields.
	 *
	 * @param array $data Array of personal data to anonymize.
	 *
	 * @return array Array of anonymized data.
	 * @example Output: ["name" => "J**n S***h", "phone" => "****1234"]
	 *
	 * @example Input:  ["name" => "John Smith", "phone" => "555-1234"]
	 */
	public static function anonymize_multiple( array $data ): array {
		$anonymized = [];

		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				$anonymized[ $key ] = $value;
				continue;
			}

			switch ( strtolower( $key ) ) {
				case 'name':
				case 'first_name':
				case 'last_name':
				case 'display_name':
					$anonymized[ $key ] = self::name( $value );
					break;
				case 'phone':
				case 'telephone':
				case 'mobile':
					$anonymized[ $key ] = self::phone( $value );
					break;
				case 'address':
				case 'billing_address':
				case 'shipping_address':
					$anonymized[ $key ] = self::address( $value );
					break;
				case 'zipcode':
				case 'postal_code':
				case 'zip':
					$anonymized[ $key ] = self::zipcode( $value );
					break;
				case 'birth_date':
				case 'birthday':
				case 'date_of_birth':
					$anonymized[ $key ] = self::date( $value );
					break;
				default:
					$anonymized[ $key ] = self::text( $value );
					break;
			}
		}

		return $anonymized;
	}

}