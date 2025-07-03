<?php
/**
 * WordPress Comment Anonymization Utilities
 *
 * This class provides utility methods for anonymizing WordPress comment data
 * including author information, email addresses, and IP addresses while
 * maintaining comment functionality and thread integrity.
 *
 * @package ArrayPress\AnonymizeUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\AnonymizeUtils;

class Comment {

	/**
	 * Anonymize WordPress comment data.
	 *
	 * @param int $comment_id The comment ID to anonymize.
	 *
	 * @return bool True on success, false on failure.
	 * @example Output: Author becomes "J**n D*e", email becomes "deleted@site.invalid", IP becomes "192.168.1.0"
	 *
	 * @example Input:  Comment with author "John Doe", email "john@example.com", IP "192.168.1.100"
	 */
	public static function anonymize_data( int $comment_id ): bool {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		$comment_data = [
			'comment_ID'           => $comment_id,
			'comment_author'       => Personal::name( $comment->comment_author ),
			'comment_author_email' => Email::placeholder( $comment->comment_author_email ),
			'comment_author_url'   => Web::url( $comment->comment_author_url ),
			'comment_author_IP'    => IP::anonymize( $comment->comment_author_IP ),
		];

		$result = wp_update_comment( $comment_data );

		return $result !== false;
	}

	/**
	 * Check if a comment appears to be anonymized.
	 *
	 * @param int $comment_id The comment ID to check.
	 *
	 * @return bool True if the comment appears anonymized, false otherwise.
	 */
	public static function is_anonymized( int $comment_id ): bool {
		return Validate::is_comment_anonymized( $comment_id );
	}

	/**
	 * Anonymize comments by post ID.
	 *
	 * @param int $post_id The post ID whose comments to anonymize.
	 *
	 * @return array Results array with success/failure for each comment.
	 */
	public static function anonymize_by_post( int $post_id ): array {
		$comments = get_comments( [
			'post_id' => $post_id,
			'status'  => 'approve',
		] );

		$results = [];
		foreach ( $comments as $comment ) {
			$results[ $comment->comment_ID ] = self::anonymize_data( absint( $comment->comment_ID ) );
		}

		return $results;
	}

	/**
	 * Get anonymized comment data for export.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return array Anonymized comment data.
	 */
	public static function get_anonymized_export( int $comment_id ): array {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return [];
		}

		return [
			'comment_ID'           => $comment_id,
			'comment_author'       => Personal::name( $comment->comment_author ),
			'comment_author_email' => Email::anonymize( $comment->comment_author_email ),
			'comment_author_url'   => Web::url( $comment->comment_author_url ),
			'comment_author_IP'    => IP::anonymize( $comment->comment_author_IP ),
			'comment_content'      => $comment->comment_content, // Keep content intact
			'comment_date'         => $comment->comment_date,
		];
	}

	/**
	 * Bulk anonymize multiple comments.
	 *
	 * @param array $comment_ids Array of comment IDs to anonymize.
	 *
	 * @return array Results array with success/failure for each comment.
	 * @example Output: [123 => true, 456 => true, 789 => false] (success/failure for each)
	 *
	 * @example Input:  [123, 456, 789] (comment IDs)
	 */
	public static function bulk_anonymize( array $comment_ids ): array {
		$results = [];

		foreach ( $comment_ids as $comment_id ) {
			$results[ $comment_id ] = self::anonymize_data( $comment_id );
		}

		return $results;
	}

}