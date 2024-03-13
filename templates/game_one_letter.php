<?php
if(isset($_GET['letter'])) {
    $search_term = sanitize_text_field($_GET['letter']);
    $args = array(
        'post_type'      => 'custom_item',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        's'              => $search_term,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="custom-item-list">'; // Opening div for custom item list
        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $post_title = get_the_title();
            $featured_image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
            $post_content = get_the_content();

            // Output post information in a table row
            echo '<div class="custom-item">';
            echo '<img src="' . esc_url($featured_image_url) . '" alt="Featured Image" width="150" height="117">';
            echo '<h2>' . esc_html($post_title) . '</h2>';
            echo '<div class="post-content">' . wpautop($post_content) . '</div>';
            echo '</div>'; // Closing div for custom item
        }
        echo '</div>'; // Closing div for custom item list

        wp_reset_postdata();
    } else {
        echo 'No posts found.';
    }
}
?>
