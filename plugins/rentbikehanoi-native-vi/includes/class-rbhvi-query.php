<?php
/**
 * Language separation for lists: archives, search, recent/related posts,
 * widgets, feeds, public REST search and previous/next links.
 * English requests exclude Vietnamese content; Vietnamese requests include
 * only Vietnamese content. Nothing changes until a Vietnamese item exists.
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query filters.
 */
class RBHVI_Query {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 5 );
		foreach ( array( 'previous', 'next' ) as $direction ) {
			add_filter( "get_{$direction}_post_where", array( __CLASS__, 'adjacent_where' ), 10, 5 );
		}
		add_filter( 'get_terms_args', array( __CLASS__, 'terms_args' ), 10, 2 );
	}

	/**
	 * Whether this query should be language-filtered.
	 *
	 * @param WP_Query $query Query.
	 * @return bool
	 */
	private static function applies( $query ) {
		if ( $query->get( 'rbhvi_skip' ) || $query->is_preview() ) {
			return false;
		}
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// Editors need every page/post (e.g. parent pickers); public searches are separated.
			$context = isset( $_GET['context'] ) ? sanitize_key( wp_unslash( $_GET['context'] ) ) : 'view'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'edit' === $context || current_user_can( 'edit_posts' ) ) {
				return false;
			}
		}
		$types = $query->get( 'post_type' );
		if ( empty( $types ) ) {
			return true;
		}
		$types = (array) $types;
		return in_array( 'any', $types, true ) || array_intersect( $types, RBHVI_Language::POST_TYPES );
	}

	/**
	 * Add the language clause.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function pre_get_posts( $query ) {
		if ( ! self::applies( $query ) ) {
			return;
		}
		$lang = 'vi' === $query->get( 'rbhvi_lang' ) ? 'vi' : RBHVI_Language::current();
		$term = RBHVI_Language::term_id();
		if ( ! $term ) {
			if ( 'vi' === $lang && ! $query->is_singular() ) {
				$query->set( 'post__in', array( 0 ) );
			}
			return;
		}
		$clause   = array(
			'taxonomy'         => RBHVI_Language::TAX,
			'field'            => 'term_id',
			'terms'            => array( $term ),
			'operator'         => 'vi' === $lang ? 'IN' : 'NOT IN',
			'include_children' => false,
		);
		$existing = $query->get( 'tax_query' );
		$query->set(
			'tax_query',
			is_array( $existing ) && $existing ? array(
				'relation' => 'AND',
				$existing,
				$clause,
			) : array( $clause )
		); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	/**
	 * Previous/next links stay inside the current post's language.
	 *
	 * @param string       $where          SQL WHERE.
	 * @param bool         $in_same_term   Unused.
	 * @param array|string $excluded_terms Unused.
	 * @param string       $taxonomy       Unused.
	 * @param WP_Post|null $post           Current post.
	 * @return string
	 */
	public static function adjacent_where( $where, $in_same_term = false, $excluded_terms = '', $taxonomy = 'category', $post = null ) {
		$term = RBHVI_Language::term_id();
		$post = $post ? $post : get_post();
		if ( ! $term || ! $post || is_admin() ) {
			return $where;
		}
		global $wpdb;
		$operator = RBHVI_Language::is_vi( $post ) ? 'IN' : 'NOT IN';
		return $where . $wpdb->prepare( " AND p.ID {$operator} ( SELECT tr.object_id FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.term_id = %d )", $term ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- operator is one of two literals.
	}

	/**
	 * Category lists (widgets, hubs) show only categories of the request
	 * language. Inactive until a Vietnamese category exists.
	 *
	 * @param array    $args       Term query args.
	 * @param string[] $taxonomies Taxonomies.
	 * @return array
	 */
	public static function terms_args( $args, $taxonomies ) {
		// Sitemaps (Yoast or core) list every language; their exclusions live in RBHVI_SEO.
		if ( get_query_var( 'sitemap' ) || get_query_var( 'yoast-sitemap-xsl' ) ) {
			return $args;
		}
		if ( ! in_array( 'category', (array) $taxonomies, true ) || ! RBHVI_Language::is_frontend() || ! empty( $args['object_ids'] ) || ! empty( $args['include'] ) || ! empty( $args['slug'] ) || ! empty( $args['name'] ) || ! RBHVI_Language::has_vi_terms() ) {
			return $args;
		}
		$clause   = 'vi' === RBHVI_Language::current()
			? array(
				'key'   => RBHVI_Language::TERM_META,
				'value' => 'vi',
			)
			: array(
				'key'     => RBHVI_Language::TERM_META,
				'compare' => 'NOT EXISTS',
			);
		$existing = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
		// Small termmeta lookup, only once a Vietnamese category exists.
		$args['meta_query'] = $existing ? array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'AND',
			$existing,
			$clause,
		) : array( $clause ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		return $args;
	}
}
