<?php

/**
 * Plugin Name: Findit Post Search and Filter
 * Description: A clean, responsive AJAX-powered search plugin for WordPress posts with keyword highlighting, category/tag filtering, and load more pagination.
 * Version: 1.0
 * Author: Xiangxu
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: findit-post-search-and-filter
 */

if (!defined('ABSPATH')) exit;
require plugin_dir_path(__FILE__) . 'includes/findit-enqueue.php';
require plugin_dir_path(__FILE__) . 'includes/findit-shortcode.php';
require plugin_dir_path(__FILE__) . 'includes/findit-ajax-handler.php';
