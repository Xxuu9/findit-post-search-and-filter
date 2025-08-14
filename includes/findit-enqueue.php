<?php
if (!defined('ABSPATH')) exit;

function findit_enqueue_assets()
{
    wp_enqueue_script('findit-post-script', plugin_dir_url(__DIR__) . 'findit-post-script.js', [], '1.1', true);
    wp_enqueue_style('findit-post-style', plugin_dir_url(__DIR__) . 'findit-post-style.css', [], '1.0');
    wp_localize_script('findit-post-script', 'findit_ajax_obj', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('findit_search_action'),
    ]);
}
add_action('wp_enqueue_scripts', 'findit_enqueue_assets');
