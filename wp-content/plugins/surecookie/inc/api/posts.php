<?php
/**
 * Posts API.
 *
 * REST endpoints for searching posts suitable for the Cookie Policy page picker
 * and fetching a single post by ID. Defaults to the `page` post type only;
 * developers can extend the searchable list via the `surecookie_searchable_post_types`
 * filter (see `get_allowed_post_types()` below).
 *
 * @package SureCookie\Inc\API
 * @since 0.0.1-beta.2
 */

namespace SureCookie\Inc\API;

use SureCookie\Inc\Functions\SendJson;
use SureCookie\Inc\Traits\GetInstance;
use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Posts
 *
 * @since 0.0.1-beta.2
 */
class Posts extends Base {
	use GetInstance;

	/**
	 * Register API routes.
	 *
	 * @since 0.0.1-beta.2
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->get_api_namespace(),
			'/posts/search',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'search_posts' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => [
					'search'   => [
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					],
					'per_page' => [
						'type'              => 'integer',
						'default'           => 20,
						'minimum'           => 1,
						'maximum'           => 50,
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
			]
		);

		register_rest_route(
			$this->get_api_namespace(),
			'/posts/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_post_by_id' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'type'              => 'integer',
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					],
				],
			]
		);
	}

	/**
	 * Search published posts suitable for the Cookie Policy page picker.
	 *
	 * Defaults to the `page` post type only; the allowed list is extensible via
	 * the `surecookie_searchable_post_types` filter. Results are ordered
	 * alphabetically when no search term is given, or by relevance otherwise.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @since 0.0.1-beta.2
	 * @return void
	 */
	public function search_posts( WP_REST_Request $request ): void {
		// Values are already sanitized and validated by route arg definitions.
		$search   = $request->get_param( 'search' );
		$per_page = $request->get_param( 'per_page' );

		$allowed_post_types = $this->get_allowed_post_types();

		// Guard: WP_Query treats `'post_type' => []` as `'post'`, which would silently
		// include the default post type even if a filter attempted to remove it.
		if ( empty( $allowed_post_types ) ) {
			SendJson::success( [ 'data' => [] ] );
			return;
		}

		$args = [
			'post_type'      => $allowed_post_types,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true, // Skips COUNT query - pagination not needed here.
		];

		if ( ! empty( $search ) ) {
			$args['s']       = $search;
			$args['orderby'] = 'relevance';
			unset( $args['order'] );
		}

		$query = new \WP_Query( $args );
		$posts = [];

		foreach ( $query->posts as $post ) {
			if ( ! ( $post instanceof \WP_Post ) ) {
				continue;
			}
			$posts[] = [
				'id'    => $post->ID,
				// Use the raw stored title instead of get_the_title() - that function runs the
				// `the_title` filter chain (SEO/translation plugins) which we don't want for an
				// admin JSON picker where the raw title is the source of truth.
				'title' => wp_strip_all_tags( $post->post_title ),
			];
		}

		SendJson::success( [ 'data' => $posts ] );
	}

	/**
	 * Return basic data for a single published post regardless of post type.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @since 0.0.1-beta.2
	 * @return void
	 */
	public function get_post_by_id( WP_REST_Request $request ): void {
		$post_id = $request->get_param( 'id' ); // Already absint by sanitize_callback.
		$post    = get_post( $post_id );

		// Return 404 for missing, non-published, OR disallowed post type so that draft/private/
		// structural IDs (attachments, nav items, blocks) are not confirmed to exist. Using the
		// same 404 for every disallowed case preserves the non-enumeration property.
		// instanceof narrows the type from WP_Post|array|null to WP_Post for PHPStan.
		if (
			! ( $post instanceof \WP_Post )
			|| $post->post_status !== 'publish'
			|| ! in_array( $post->post_type, $this->get_allowed_post_types(), true )
		) {
			SendJson::error( [ 'message' => __( 'Post not found.', 'surecookie' ) ], 404 );
			return;
		}

		// Only return a permalink when it resolves to the same host as the site. A rogue
		// `post_link` filter or custom rewrite could otherwise steer admins toward an
		// off-site URL which then ships to every visitor as the "Cookie Policy" link.
		// Mirrors the host-match pattern in Get::cookie_policy_page_details().
		$permalink = get_permalink( $post->ID );
		$link      = '';

		if ( is_string( $permalink ) && $permalink !== '' ) {
			$parsed_permalink = wp_parse_url( $permalink );
			$parsed_home      = wp_parse_url( home_url() );

			if (
				! empty( $parsed_permalink['host'] )
				&& ! empty( $parsed_home['host'] )
				&& strtolower( $parsed_permalink['host'] ) === strtolower( $parsed_home['host'] )
			) {
				$link = esc_url_raw( $permalink );
			}
		}

		SendJson::success(
			[
				'id'     => $post->ID,
				'title'  => wp_strip_all_tags( $post->post_title ),
				'status' => $post->post_status,
				'link'   => $link,
			]
		);
	}

	/**
	 * Get the allowed post types for cookie policy page selection.
	 *
	 * Defaults to `['page']` only. Developers can extend the searchable list
	 * via the `surecookie_searchable_post_types` filter - the filter result
	 * is the source of truth, including removal of `page` if explicitly chosen.
	 *
	 * @since 0.0.1-beta.2
	 * @return array<int, string> Indexed array of post type slugs.
	 */
	private function get_allowed_post_types(): array {
		$post_types = [ 'page' => 'page' ];

		/**
		 * Filter the post types searchable via the Cookie Policy page picker.
		 *
		 * Defaults to `['page']`. Return a superset to opt in to additional
		 * post types (e.g., a 'policy-pages' CPT from a legal-pages plugin).
		 *
		 * Example (child theme `functions.php`):
		 *
		 *     add_filter( 'surecookie_searchable_post_types', function ( $types ) {
		 *         $types['policy-pages'] = 'policy-pages';
		 *         return $types;
		 *     } );
		 *
		 * This filter does NOT affect the Cookie Scanner "Select Pages" surface,
		 * which is page-locked for MVP and uses WordPress core's `/wp/v2/pages`
		 * endpoint directly.
		 *
		 * @param array<string, string> $post_types Post type slugs keyed by slug.
		 * @since 0.0.1-beta.2
		 */
		return array_values(
			(array) apply_filters( 'surecookie_searchable_post_types', $post_types )
		);
	}
}
