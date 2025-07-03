<?php
/**
 * IP Address Anonymization Utilities
 *
 * This class provides utility methods for anonymizing and masking IP addresses.
 * It supports both IPv4 and IPv6 addresses with simple anonymization methods
 * suitable for WordPress analytics and privacy compliance.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class IP {

	/**
	 * Anonymize an IP address by zeroing out the last octet (IPv4) or the last group (IPv6).
	 *
	 * @param string $ip The IP address to anonymize.
	 *
	 * @return string|null The anonymized IP address, or null if invalid.
	 * @example Output: "192.168.1.0"
	 * @example Input:  "2001:db8:85a3::8a2e:370:7334"
	 * @example Output: "2001:db8:85a3::8a2e:370:0"
	 * @example Input:  "::1" (IPv6 localhost)
	 * @example Output: "::0"
	 *
	 * @example Input:  "192.168.1.100"
	 */
	public static function anonymize( string $ip ): ?string {
		if ( ! self::is_valid( $ip ) ) {
			return null;
		}

		if ( self::is_ipv4( $ip ) ) {
			return preg_replace( '/\.\d+$/', '.0', $ip );
		}

		if ( self::is_ipv6( $ip ) ) {
			// Handle special case of all-zeros IPv6
			if ( $ip === '::' ) {
				return '::'; // Already all zeros, no need to change
			}

			// For all other IPv6 addresses, replace the last group with 0
			return preg_replace( '/:[^:]*$/', ':0', $ip );
		}

		return null;
	}

	/**
	 * Mask the last octet of an IPv4 address or last group of IPv6.
	 *
	 * @param string $ip The IP address to mask.
	 *
	 * @return string|null The masked IP address, or null if invalid.
	 * @example Output: "192.168.1.***"
	 * @example Input:  "2001:db8:85a3::8a2e:370:7334"
	 * @example Output: "2001:db8:85a3::8a2e:370:****"
	 * @example Input:  "::" (IPv6 all zeros)
	 * @example Output: "::****"
	 *
	 * @example Input:  "192.168.1.100"
	 */
	public static function mask_last_octet( string $ip ): ?string {
		if ( ! self::is_valid( $ip ) ) {
			return null;
		}

		if ( self::is_ipv4( $ip ) ) {
			return preg_replace( '/\.\d+$/', '.***', $ip );
		}

		if ( self::is_ipv6( $ip ) ) {
			return preg_replace( '/:[^:]*$/', ':****', $ip );
		}

		return null;
	}

	/**
	 * Check if an IP address is already anonymized.
	 *
	 * @param string $ip The IP address to check.
	 *
	 * @return bool True if the IP appears to be anonymized, false otherwise.
	 * @example Input:  "192.168.1.0" or "192.168.1.***"
	 * @example Output: true
	 * @example Input:  "2001:db8:85a3::8a2e:370:0"
	 * @example Output: true
	 */
	public static function is_anonymized( string $ip ): bool {
		return Validate::is_anonymized( $ip );
	}

	/**
	 * Anonymize multiple IP addresses.
	 *
	 * @param array $ips Array of IP addresses to anonymize.
	 *
	 * @return array Array of anonymized IP addresses.
	 * @example Output: ["192.168.1.0", "10.0.0.0"]
	 *
	 * @example Input:  ["192.168.1.100", "10.0.0.50"]
	 */
	public static function anonymize_multiple( array $ips ): array {
		$anonymized = [];
		foreach ( $ips as $ip ) {
			$result = self::anonymize( $ip );
			if ( $result !== null ) {
				$anonymized[] = $result;
			}
		}

		return $anonymized;
	}

	/**
	 * Get current user's anonymized IP.
	 *
	 * @return string|null The anonymized user IP, or null if not available.
	 * @example Output: "2001:db8:85a3::8a2e:370:0" (if user IP was IPv6)
	 *
	 * @example Output: "192.168.1.0" (if user IP was "192.168.1.100")
	 */
	public static function get_user_anonymized(): ?string {
		$user_ip = self::get_user_ip();

		return $user_ip ? self::anonymize( $user_ip ) : null;
	}

	/**
	 * Validate an IP address (IPv4 or IPv6).
	 *
	 * @param string $ip The IP address to validate.
	 *
	 * @return bool True if the IP address is valid.
	 */
	private static function is_valid( string $ip ): bool {
		return filter_var( $ip, FILTER_VALIDATE_IP ) !== false;
	}

	/**
	 * Check if an IP address is IPv4.
	 *
	 * @param string $ip The IP address to check.
	 *
	 * @return bool True if IPv4, false otherwise.
	 */
	private static function is_ipv4( string $ip ): bool {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false;
	}

	/**
	 * Check if an IP address is IPv6.
	 *
	 * @param string $ip The IP address to check.
	 *
	 * @return bool True if IPv6, false otherwise.
	 */
	private static function is_ipv6( string $ip ): bool {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) !== false;
	}

	/**
	 * Get the current user's IP address.
	 *
	 * @return string|null The user's IP address, or null if not found.
	 */
	private static function get_user_ip(): ?string {
		$ip_keys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		];

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );
					if ( self::is_valid( $ip ) ) {
						return $ip;
					}
				}
			}
		}

		return null;
	}

}