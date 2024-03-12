
<?php

if($_GET['letter']) {
	$search_term = sanitize_text_field($_GET['letter']); // Use proper sanitization
	$args = array(
		'post_type'      => 'custom_item', // Replace with your custom post type if needed
		'posts_per_page' => -1,     // Show all posts that match the criteria
		'orderby'        => 'title', // Order by title, you can change this to another field
		'order'          => 'ASC',   // Order in ascending order, change to 'DESC' for descending
		's'              => $search_term, // Set the search term
	);

	// Execute the query
	$query = new WP_Query($args);

	// Check if posts were found
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();

			// Access post information
			$post_id = get_the_ID();
			$post_title = get_the_title();
			 $featured_image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
			echo $image_tag = '<img width="150" height="117" src="' . esc_url($featured_image_url) . '" alt="Featured Image">';
		
	  }
	}
}
?>
