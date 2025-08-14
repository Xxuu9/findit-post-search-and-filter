<?php
if (!defined('ABSPATH')) exit;

function findit_search_form_shortcode()
{
    ob_start();
?>
    <form id="findit-search-form" class="findit-search-form">
        <input type="text" id="findit-search-text" class="findit-input" name="search_text" placeholder="Search ..." />
        <select id="findit-filter-category" name="selected_category" class="findit-select">
            <option value="">All Categories</option>
            <?php foreach (get_terms(['taxonomy' => 'category', 'hide_empty' => true]) as $term) {
                echo '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>';
            } ?>
        </select>
        <select id="findit-filter-tag" name="selected_tag" class="findit-select">
            <option value="">All Tags</option>
            <?php foreach (get_terms(['taxonomy' => 'post_tag', 'hide_empty' => true]) as $term) {
                echo '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>';
            } ?>
        </select>
        <button class="findit-button" type="submit">Search</button>
        <button class="findit-button" type="button" id="reset-search">Reset</button>
    </form>
    <div id="findit-post-search-results" data-cards-per-row="3"></div>
    <button id="findit-load-more" class="findit-button" style="display: none;">Load More</button>
<?php
    return ob_get_clean();
}
add_shortcode('findit_post_filter_search', 'findit_search_form_shortcode');
