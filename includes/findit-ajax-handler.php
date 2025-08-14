<?php
if (!defined('ABSPATH')) exit;

function findit_post_handle_ajax_search()
{
    $nonce = isset($_POST['findit_search_nonce'])
        ? sanitize_text_field(wp_unslash($_POST['findit_search_nonce']))
        : '';
    if (!wp_verify_nonce($nonce, 'findit_search_action')) {
        wp_send_json_error(['message' => 'Nonce verification failed'], 403);
    }


    $search_text = isset($_POST['search_text']) ? sanitize_text_field(wp_unslash($_POST['search_text'])) : '';
    $selected_category = isset($_POST['selected_category']) ? sanitize_text_field(wp_unslash($_POST['selected_category'])) : '';
    $selected_tag = isset($_POST['selected_tag']) ? sanitize_text_field(wp_unslash($_POST['selected_tag'])) : '';
    $paged = max(1, intval($_POST['page'] ?? 1));
    $posts_per_page = 9;

    $tax_query = [];
    if ($selected_category || $selected_tag) {
        $tax_query['relation'] = 'AND';
        if ($selected_category) {
            $tax_query[] = [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $selected_category,
            ];
        }
        if ($selected_tag) {
            $tax_query[] = [
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => $selected_tag,
            ];
        }
    }

    $query_args = [
        'post_type'      => 'post',
        'posts_per_page' => $posts_per_page,
        'post_status'    => 'publish',
        'paged'          => $paged,
        // 's'              => $search_text,
        'tax_query'      => $tax_query,
    ];

    if (!empty($search_text)) {
        $query_args['s'] = $search_text;
        $query_args['orderby'] = 'relevance';
        $query_args['order'] = 'DESC';
    }

    $query = new WP_Query($query_args);
    ob_start();

    $has_results = false;

    if ($query->have_posts()) {
        echo '<div class="findit-cards-wrapper">';
        while ($query->have_posts()) {
            $query->the_post();
            $post_id     = get_the_ID();
            $title       = get_the_title();
            $excerpt     = get_the_excerpt();
            $content     = wp_strip_all_tags(get_the_content());
            $permalink   = get_permalink();
            $thumbnail   = get_the_post_thumbnail_url($post_id, 'large');
            $categorys   = get_the_terms($post_id, 'category');
            $tags        = get_the_terms($post_id, 'post_tag');
            $category_names = !is_wp_error($categorys) ? wp_list_pluck($categorys, 'name') : [];
            $tag_names      = !is_wp_error($tags) ? wp_list_pluck($tags, 'name') : [];

            $highlight = fn($m) => '<mark>' . $m[0] . '</mark>';
            $matched = false;

            if ($search_text) {
                $words = array_filter(preg_split('/\s+/', $search_text));
                if ($words) {
                    $pattern = '/' . implode('|', array_map('preg_quote', $words)) . '/iu';
                    foreach ($category_names as $kname) {
                        if (preg_match($pattern, $kname)) $matched = true;
                    }
                    foreach ($tag_names as $tname) {
                        if (preg_match($pattern, $tname)) $matched = true;
                    }
                    if (preg_match($pattern, $title) || preg_match($pattern, $excerpt) || preg_match($pattern, $content)) {
                        $matched = true;
                    }
                    $title = preg_replace_callback($pattern, $highlight, $title);
                    $excerpt = preg_replace_callback($pattern, $highlight, $excerpt);
                    $category_names_highlighted = [];
                    $tag_names_highlighted = [];

                    foreach ($category_names as $kname) {
                        $category_names_highlighted[] = preg_replace_callback($pattern, $highlight, $kname);
                    }
                    foreach ($tag_names as $tname) {
                        $tag_names_highlighted[] = preg_replace_callback($pattern, $highlight, $tname);
                    }
                    unset($kname, $tname);
                }
            } else {
                $matched = true;
            }

            if ($matched) {
                $has_results = true;
                echo '<div class="findit-card"><a href="' . esc_url($permalink) . '">';
                if ($thumbnail) echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(wp_strip_all_tags($title)) . '">';
                echo '<div class="findit-card-content">';
                echo '<h3 class="findit-title">' . wp_kses_post($title) . '</h3>';
                $categories_output = !empty($category_names_highlighted)
                    ? implode(', ', $category_names_highlighted)
                    : implode(', ', $category_names);

                $tags_output = !empty($tag_names_highlighted)
                    ? implode(', ', $tag_names_highlighted)
                    : implode(', ', $tag_names);

                if (!empty($categories_output)) {
                    echo '<span class="findit-category"><strong>Category:</strong> ' . wp_kses_post($categories_output) . '</span>';
                }
                if (!empty($tags_output)) {
                    echo '<span class="findit-tag"><strong>Tag:</strong> ' . wp_kses_post($tags_output) . '</span>';
                }



                echo '</div></a></div>';
            }
        }
        echo '</div>';
        wp_reset_postdata();
    }

    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $has_results ? $html : '<div>No matching posts found.</div>',
        'has_more' => $paged < $query->max_num_pages,
        'current_page' => $paged,
        'max_pages' => $query->max_num_pages,
    ]);
}

add_action('wp_ajax_post_search', 'findit_post_handle_ajax_search');
add_action('wp_ajax_nopriv_post_search', 'findit_post_handle_ajax_search');
