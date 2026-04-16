<?php

namespace wp_opt;
class funEnhance {
	static function removeCategory() {
		add_action( 'created_category', array( Plugin::class, 'flushRules' ) );
		add_action( 'delete_category', array( Plugin::class, 'flushRules' ) );
		add_action( 'edited_category', array( Plugin::class, 'flushRules' ) );
		add_action( 'init', array( static::class, 'removeCategoryUrlPermastruct' ) );
		add_filter( 'category_rewrite_rules', array( static::class, 'removeCategoryUrlRewriteRules' ) );
		add_filter( 'query_vars', array( static::class, 'removeCategoryUrlQueryVars' ) );
		add_filter( 'request', array( static::class, 'removeCategoryUrlRequest' ) );
	}

	static function unsetRemoveCategory(){
		remove_filter( 'category_rewrite_rules', array( static::class, 'removeCategoryUrlRewriteRules' ) );
	}

	static function removeCategoryUrlRequest( $query_vars ) {
		if ( isset( $query_vars['category_redirect'] ) ) {
			$catlink = trailingslashit( get_option( 'home' ) ) . user_trailingslashit( $query_vars['category_redirect'], 'category' );
			status_header( 301 );
			header( "Location: $catlink" );
			exit;
		}

		return $query_vars;
	}

	static function removeCategoryUrlQueryVars( $public_query_vars ) {
		$public_query_vars[] = 'category_redirect';
		return $public_query_vars;
	}

	static function removeCategoryUrlPermastruct() {
		global $wp_rewrite, $wp_version;
		if ( 3.4 <= $wp_version ) {
			$wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
		} else {
			$wp_rewrite->extra_permastructs['category'][0] = '%category%';
		}
	}

	static function removeCategoryUrlRewriteRules( $category_rewrite ) {
		global $wp_rewrite;

		$category_rewrite = array();

		/* WPML is present: temporary disable terms_clauses filter to get all categories for rewrite */
		if ( class_exists( 'Sitepress' ) ) {
			global $sitepress;

			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10 );
			$categories = get_categories( array( 'hide_empty' => false, '_icl_show_all_langs' => true ) );
			add_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
		} else {
			$categories = get_categories( array( 'hide_empty' => false ) );
		}

		foreach ( $categories as $category ) {
			$category_nicename = $category->slug;
			if ( $category->parent == $category->cat_ID ) {
				$category->parent = 0;
			} elseif ( 0 != $category->parent ) {
				$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;
			}
			$category_rewrite[ '(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
			$category_rewrite[ '(' . $category_nicename . ')/page/?([0-9]{1,})/?$' ]                  = 'index.php?category_name=$matches[1]&paged=$matches[2]';
			$category_rewrite[ '(' . $category_nicename . ')/?$' ]                                    = 'index.php?category_name=$matches[1]';
		}

		// Redirect support from Old Category Base
		$old_category_base                                 = get_option( 'category_base' ) ? get_option( 'category_base' ) : 'category';
		$old_category_base                                 = trim( $old_category_base, '/' );
		$category_rewrite[ $old_category_base . '/(.*)$' ] = 'index.php?category_redirect=$matches[1]';

		return $category_rewrite;
	}

}