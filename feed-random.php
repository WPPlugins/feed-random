<?php
/*
Plugin Name: Feed Random
Description: Add a RSS with random posts
Author: Champimouss.net
Version: 1.0.0
Author URI: http://www.champimouss.net/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2011

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
class feed_random {
	function feed_random() {
		global $wp_rewrite;
		add_action('init', array(&$this, 'add_feed_random'));
		add_action('do_feed_random', array(&$this, 'do_feed_random'), 10, 1);
		add_filter('template_include', array(&$this, 'template_random'));
		add_filter('query_vars', array(&$this, 'add_query_vars'));
                add_filter('pre_get_posts', array(&$this, 'randomize'), 1, 1);
                add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 0;' ) );

		$plugin_basename = plugin_basename(__FILE__);
		add_action('activate_' . $plugin_basename, array(&$this, 'add_feed_random_once'));
		add_action('deactivate_' . $plugin_basename, array(&$this, 'remove_feed_random'));
	}

	function add_feed_random_once() {
		global $wp_rewrite;
		$this->add_feed_random();
		$wp_rewrite->flush_rules();
	}

	function remove_feed_random() {
		global $wp_rewrite;
		$feeds = array();
		foreach ( $wp_rewrite->feeds as $feed ) {
			if ( $feed !== 'random' ) {
				$feeds[] = $feed;
			}
		}
		$wp_rewrite->feeds = $feeds;
		$wp_rewrite->flush_rules();
	}

	function add_query_vars($qvars) {
	  $qvars[] = 'callback';
	  $qvars[] = 'limit';
	  return $qvars;
	}

	function add_feed_random() {
		add_feed('random', array(&$this, 'do_feed_random'));
	}

        function add_order_random( $orderby ) {
            return "RAND() ASC";
        }

        function randomize($query) {
            if ($query->is_feed && get_query_var('feed') === 'random') {
                add_filter('posts_orderby', array(&$this, 'add_order_random'));
            }
            return $query;
        }

	function do_feed_random() {
		load_template($this->template_random(dirname(__FILE__) . '/feed-random-template.php'));
	}

	function template_random( $template ) {
		$template_file = false;
		if (get_query_var('feed') === 'random') {
			$template_file = '/feed-random.php';
			if (function_exists('get_stylesheet_directory') && file_exists(get_stylesheet_directory() . $template_file)) {
				$template_file = get_stylesheet_directory() . $template_file;
			} elseif (function_exists('get_template_directory') && file_exists(get_template_directory() . $template_file)) {
				$template_file = get_template_directory() . $template_file;
			} elseif (file_exists(dirname(__FILE__) . '/feed-random-template.php')) {
				$template_file = dirname(__FILE__) . '/feed-random-template.php';
			} else {
				$template_file = false;
			}
		}

		return (
			$template_file !== false
			? $template_file
			: $template
			);
	}
}
new feed_random();
?>