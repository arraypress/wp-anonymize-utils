<?php
/**
 * Web Data Anonymization Utilities
 *
 * This class provides utility methods for anonymizing web-related data
 * such as URLs and user agents for WordPress analytics and privacy compliance.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class Web {

	/**
	 * Anonymize a URL while preserving the domain structure.
	 *
	 * @param string $url      The URL to anonymize.
	 * @param bool   $validate Optional. Whether to validate the URL first.
	 *
	 * @return string|null The anonymized URL or null if validation fails.
	 */
	public static function url( string $url, bool $validate = true ): ?string {
		if ( $validate && ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return null;
		}

		$parts = parse_url( $url );
		if ( $parts === false ) {
			return null;
		}

		// Anonymize path segments but keep structure
		if ( isset( $parts['path'] ) ) {
			$path_segments       = explode( '/', trim( $parts['path'], '/' ) );
			$anonymized_segments = array_map( function ( $segment ) {
				return preg_replace( '/[a-zA-Z0-9]/', '*', $segment );
			}, $path_segments );
			$parts['path']       = '/' . implode( '/', $anonymized_segments );
		}

		// Anonymize query parameters but keep keys
		if ( isset( $parts['query'] ) ) {
			parse_str( $parts['query'], $query_params );
			array_walk( $query_params, function ( &$value ) {
				$value = str_repeat( '*', strlen( (string) $value ) );
			} );
			$parts['query'] = http_build_query( $query_params );
		}

		// Anonymize fragments but keep structure
		if ( isset( $parts['fragment'] ) ) {
			$parts['fragment'] = str_repeat( '*', strlen( $parts['fragment'] ) );
		}

		// Rebuild URL
		return
			( isset( $parts['scheme'] ) ? "{$parts['scheme']}://" : '' ) .
			( isset( $parts['host'] ) ? "{$parts['host']}" : '' ) .
			( isset( $parts['port'] ) ? ":{$parts['port']}" : '' ) .
			( isset( $parts['path'] ) ? "{$parts['path']}" : '' ) .
			( isset( $parts['query'] ) ? "?{$parts['query']}" : '' ) .
			( isset( $parts['fragment'] ) ? "#{$parts['fragment']}" : '' );
	}

	/**
	 * Anonymize a user agent string while preserving browser and OS information.
	 *
	 * @param string $user_agent The user agent string to anonymize.
	 *
	 * @return string The anonymized user agent.
	 */
	public static function user_agent( string $user_agent ): string {
		if ( empty( $user_agent ) ) {
			return '';
		}

		// Extract basic browser information
		$browser = self::detect_browser( $user_agent );
		$os      = self::detect_os( $user_agent );

		return sprintf( '%s on %s', $browser, $os );
	}

	/**
	 * Anonymize multiple web identifiers.
	 *
	 * @param array $data Array of web data to anonymize.
	 *
	 * @return array Array of anonymized web data.
	 */
	public static function anonymize_multiple( array $data ): array {
		$anonymized = [];

		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				$anonymized[ $key ] = $value;
				continue;
			}

			$key_lower = strtolower( $key );

			switch ( $key_lower ) {
				case 'url':
				case 'request_uri':
				case 'redirect_url':
				case 'referrer':
					$anonymized[ $key ] = self::url( $value );
					break;
				case 'user_agent':
				case 'http_user_agent':
					$anonymized[ $key ] = self::user_agent( $value );
					break;
				default:
					$anonymized[ $key ] = $value;
					break;
			}
		}

		return $anonymized;
	}

	/**
	 * Detect browser from user agent string.
	 *
	 * @param string $user_agent The user agent string.
	 *
	 * @return string The detected browser.
	 */
	private static function detect_browser( string $user_agent ): string {
		if ( str_contains( $user_agent, 'Chrome' ) ) {
			return 'Chrome';
		} elseif ( str_contains( $user_agent, 'Firefox' ) ) {
			return 'Firefox';
		} elseif ( str_contains( $user_agent, 'Safari' ) ) {
			return 'Safari';
		} elseif ( str_contains( $user_agent, 'Edge' ) ) {
			return 'Edge';
		} elseif ( str_contains( $user_agent, 'Opera' ) ) {
			return 'Opera';
		}

		return 'Unknown Browser';
	}

	/**
	 * Detect operating system from user agent string.
	 *
	 * @param string $user_agent The user agent string.
	 *
	 * @return string The detected operating system.
	 */
	private static function detect_os( string $user_agent ): string {
		if ( str_contains( $user_agent, 'Windows' ) ) {
			return 'Windows';
		} elseif ( str_contains( $user_agent, 'Mac OS' ) ) {
			return 'macOS';
		} elseif ( str_contains( $user_agent, 'Linux' ) ) {
			return 'Linux';
		} elseif ( str_contains( $user_agent, 'Android' ) ) {
			return 'Android';
		} elseif ( str_contains( $user_agent, 'iPhone' ) || str_contains( $user_agent, 'iPad' ) ) {
			return 'iOS';
		}

		return 'Unknown OS';
	}

}







