<?php
// Check if 'letters' parameter is set in the URL
if(isset($_GET['letters'])) {
    // Sanitize the input
    $full_name = sanitize_text_field($_GET['letters']);

    // Create an array to store post data for each letter
    $letter_data = array();

    // Loop through each letter in the input
    for($i = 0; $i < strlen($full_name); $i++) {
        // Get the current letter
        $current_letter = strtoupper(substr($full_name, $i, 1));

        // Set up the query arguments to retrieve custom post type entries for the current letter
        $args = array(
            'post_type'      => 'custom_item',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            's'              => $current_letter,
        );

        // Execute the query
        $query = new WP_Query($args);

        // Check if posts were found for the current letter
        if ($query->have_posts()) {
            // Loop through each post found for the current letter
            while ($query->have_posts()) {
                $query->the_post();

                // Access post information
                $post_id = get_the_ID();
                $post_title = get_the_title();
                $featured_image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                $post_content = get_the_content();

                // Add post data to the array for the current letter
                $letter_data[$current_letter][] = array(
                    'title' => $post_title,
                    'image' => $featured_image_url,
                    'content' => $post_content,
                );
            }

            // Reset post data
            wp_reset_postdata();
        } else {
            // If no posts found for the current letter, add an empty entry to the array
            $letter_data[$current_letter] = array();
        }
    }

    // Output the table
    if (!empty($letter_data)) {
        echo '<table class="letter-table">';
        foreach ($letter_data as $letter => $posts) {
            echo '<tr>';
            echo '<th colspan="3">Letter ' . $letter . '</th>';
            echo '</tr>';
            foreach ($posts as $post) {
                echo '<tr>';
                echo '<td><img src="' . esc_url($post['image']) . '" alt="' . esc_attr($post['title']) . '" width="150" height="117"></td>';
                echo '<td>' . esc_html($post['title']) . '</td>';
                echo '<td>' . esc_html($post['content']) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    } else {
        // No posts found
        echo '<p>No posts found for the given letters.</p>';
    }
}
?>
