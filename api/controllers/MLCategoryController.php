<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 */
class MLCategoryController {
	/* If we didn't get a category ID, but got a category permalink, set the category ID, if we can get it */
	/**
	 * @param $user_permalink
	 * @param $user_category_id
	 *
	 * @return array
	 */
	public function set_taxonomy_by_permalink( MLQuery $ml_query ) {
		$user_permalink = $ml_query->user_permalink;
		$user_category_id        = $ml_query->user_category_id;

		$permalink_is_taxonomy = false;
		$taxonomy            = false;

		$c = get_category_by_path( $user_permalink, false );

		//If getting category fails, trying to get custom taxonomy
		if ( $c == null ) {
			$c = $this->get_taxonomy_by_path( $user_permalink );
		}

		// So is it a category/taxonomy? Let's also tell the permalink parser we already figured it out
		if ( $c ) {
			$user_category_id    = $c->term_id;
			$permalink_is_taxonomy = true;
			$taxonomy            = $c->taxonomy;
		}

		$ml_query->user_category_id    = $user_category_id;
		$ml_query->permalink_is_taxonomy = $permalink_is_taxonomy;
		$ml_query->taxonomy            = $taxonomy;
	}

	/**
	 * @param $by
	 * @param $term_ref
	 * @param $taxonomy_used
	 *
	 * @return array
	 */
	function ml_get_term_by( $by, $term_ref, $taxonomy_used ) {
		$taxes = $this->ml_get_used_taxonomies( $taxonomy_used );

		foreach ( $taxes as $tax ) {
			$term = get_term_by( $by, $term_ref, $tax );
			if ( $term ) {
				//TODO Add after 3.3.3 release. Tags feature
				//return array('term'=>$term, 'tax'=>$term->taxonomy);
				return array( 'term' => $term, 'tax' => $tax );
			}
		}

		return array( 'term' => false, 'tax' => false );
	}

	/**
	 * @param $taxonomy_used
	 *
	 * @return array
	 */
	function ml_get_used_taxonomies( $taxonomy_used ) {
		$taxes      = array( 'category', 'post_tag', $taxonomy_used );
		$menu_terms = get_option( 'ml_menu_terms', array() );
		foreach ( $menu_terms as $term ) {
			$term_data = explode( "=", $term );
			$taxes[]   = $term_data[0];
		}

		return $taxes;
	}

	/**
	 * @param $parent_id
	 * @param string $taxonomy
	 *
	 * @return int
	 */
	function ml_get_category_child_post_count( $parent_id, $taxonomy = 'category' ) {
		$count     = 0;
		$tax_terms = get_terms( $taxonomy, array( 'child_of' => $parent_id ) );
		foreach ( $tax_terms as $tax_term ) {
			$count += $tax_term->count;
		}

		return $count;
	}

	/**
	 * Retrieve taxonomy based on URL containing the taxonomy slug.
	 *
	 * Breaks the $taxonomy_path parameter up to get the taxonomy slug.
	 *
	 * @since 3.2.3
	 *
	 * @param string $taxonomy_path URL containing taxonomy slugs.
	 * @param string $output Optional. Constant OBJECT, ARRAY_A, or ARRAY_N
	 *
	 * @return null|object|array Null on failure. Type is based on $output value.
	 */
	function get_taxonomy_by_path( $taxonomy_path, $output = OBJECT ) {
		$taxonomy_path  = rawurlencode( urldecode( $taxonomy_path ) );
		$taxonomy_path  = str_replace( '%2F', '/', $taxonomy_path );
		$taxonomy_path  = str_replace( '%20', ' ', $taxonomy_path );
		$taxonomy_paths = '/' . trim( $taxonomy_path, '/' );
		$leaf_path      = sanitize_title( basename( $taxonomy_paths ) );
		$taxonomies     = get_taxonomies();

		$taxonomies = get_terms( $taxonomies, array( 'get' => 'all', 'slug' => $leaf_path ) );

		if ( empty( $taxonomies ) ) {
			return null;
		}

		$taxonomy = get_term( reset( $taxonomies )->term_id, reset( $taxonomies )->taxonomy, $output );

		return $taxonomy;
	}

	/**
	 * Set included taxonomies
	 */
	function set_included_tax( MLQuery $ml_query ) {
		$tax_array = array();
		$tax_list = get_option( 'ml_main_screen_tax_list', array());
		foreach ($tax_list as $item) {
			$list = explode(':', $item);
			if (isset($tax_array[$list[0]])) {
				$tax_array[$list[0]][] = $list[1];
			} else {
				$tax_array[$list[0]] = array($list[1]);
			}
		}
		$ml_query->included_tax_ids = $tax_array;
	}

	/**
	 * Set included/excluded categories
	 */
	function set_excluded_cats( MLQuery $ml_query ) {
		$excluded_cats_ids = array();
		$included_cats_ids = array();

		$all_cats = get_categories( 'orderby=name' );

		foreach ( $all_cats as $cat ) {
			$id = $cat->term_id;
			array_push( $included_cats_ids, $cat->term_id );
		}

		if ( ! empty( $all_cats ) ) {
			$excluded_cats = explode( ",", get_option( "ml_article_list_exclude_categories", "" ) );

			foreach ( $excluded_cats as $cat ) {
				$cat = get_term_by( 'name', $cat, 'category' );
				if ( ! empty( $cat ) ) {
					array_push( $excluded_cats_ids, $cat->term_id );
				}
			}
		}

		if (empty( $all_cats ) && !empty( $ml_query->included_tax_ids )) {
			// do not include any category, we'll use included taxonomies only
		} else {
			$included_cats_ids = array_diff( $included_cats_ids, $excluded_cats_ids );
		}

		$ml_query->included_cats_ids = $included_cats_ids;
		$ml_query->excluded_cats_ids = $excluded_cats_ids;
	}


}