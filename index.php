<?php
/*
Plugin Name: Custom Plugin
Description: A custom plugin to create a CPT and display items.
Version: 1.0
Author: Your Name
*/

class CustomPlugin
{
    public function __construct()
    {
        add_action("admin_enqueue_scripts", [$this, "enqueue_admin_styles"]);
        add_action("wp_enqueue_scripts", [$this, "enqueue_styles"]);
        add_action("init", [$this, "register_custom_post_type"]);
        add_shortcode("custom_items", [$this, "display_custom_items"]);
        add_shortcode("custom_input_page", [
            $this,
            "custom_input_page_shortcode",
        ]);
        add_filter(
            "posts_where",
            [$this, "custom_item_title_like_filter"],
            10,
            2
        );
        add_action("wp_ajax_handle_custom_input_form_submission", [
            $this,
            "handle_custom_input_form_submission",
        ]);
        add_action("wp_ajax_nopriv_handle_custom_input_form_submission", [
            $this,
            "handle_custom_input_form_submission",
        ]);
        add_shortcode("game_one_letter", [$this, "game_page"]);
        add_shortcode("game_select", [$this, "game_select"]);
        add_shortcode("game_all_letters", [$this, "game_all_letters"]);
    }

    public function enqueue_admin_styles()
    {
        wp_enqueue_style(
            "custom-input-admin-styles",
            plugin_dir_url(__FILE__) . "css/admin-styles.css"
        );
    }

    public function enqueue_styles()
    {
        wp_enqueue_style(
            "custom-input-admin-styles",
            plugin_dir_url(__FILE__) . "css/admin-styles.css?var=" . time()
        );
        wp_enqueue_script(
            "custom-ajax-script",
            plugin_dir_url(__FILE__) . "js/custom-ajax-script.js",
            ["jquery"],
            time(),
            true
        );
        $script_data = [
            "ajax_url" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("custom-ajax-nonce"),
        ];
        wp_localize_script(
            "custom-ajax-script",
            "custom_ajax_object",
            $script_data
        );
    }

    public function register_custom_post_type()
    {
        $labels = [
            "name" => _x(
                "Custom Items",
                "Post Type General Name",
                "text_domain"
            ),
            "singular_name" => _x(
                "Custom Item",
                "Post Type Singular Name",
                "text_domain"
            ),
            "menu_name" => __("Custom Items", "text_domain"),
            "public" => true,
            "has_archive" => true,
            "rewrite" => ["slug" => "custom-items"],
            "supports" => ["title", "editor", "thumbnail"],
        ];
        $args = [
            "labels" => $labels,
            "public" => true,
            "hierarchical" => false,
            "show_ui" => true,
            "show_in_menu" => true,
            "menu_position" => 5,
            "menu_icon" => "dashicons-admin-post",
            "show_in_admin_bar" => true,
            "show_in_nav_menus" => true,
            "has_archive" => true,
            "exclude_from_search" => false,
            "publicly_queryable" => true,
            "rewrite" => ["slug" => "custom-items"],
            "capability_type" => "page",
            "supports" => ["title", "editor", "thumbnail"],
        ];
        register_post_type("custom_item", $args);
    }

    public function display_custom_items()
    {
        $query = new WP_Query(["post_type" => "custom_item"]);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo '<div class="custom-item">';
                echo "<h1>" . get_the_title() . "</h1>";
                echo '<div class="custom-content">' .
                    get_the_content() .
                    "</div>";

                if (has_post_thumbnail()) {
                    echo '<div class="custom-image">' .
                        get_the_post_thumbnail() .
                        "</div>";
                }

                echo "</div>";
            }
            wp_reset_postdata();
        } else {
            echo "No custom items found.";
        }
    }

    public function custom_input_page_shortcode()
    {
        ob_start(); // Start output buffering
        $plugin_dir = plugin_dir_path(__FILE__);
        include $plugin_dir . "/templates/user_form.php";
        $output = ob_get_clean(); // Get the buffer contents and clean it
        return $output;
    }

    public function custom_item_title_like_filter($where, $wp_query)
    {
        global $wpdb;
        if ($post_title_like = $wp_query->get("post_title_like")) {
            $first_letter = esc_sql($wpdb->esc_like($post_title_like)) . "%";
            $where .= " AND $wpdb->posts.post_title LIKE '$first_letter'";
        }

        return $where;
    }

    public function handle_custom_input_form_submission()
{
    global $wpdb;

    $name = sanitize_text_field($_POST["formData"]["name"]);
    $email = sanitize_email($_POST["formData"]["email"]);
    $birthdate = sanitize_text_field($_POST["formData"]["birthdate"]);

    if (!empty($name)) {
        // Check if a record with the same email exists
        $existing_record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}custom_input_data WHERE email = %s",
                $email
            )
        );

        if ($existing_record) {
            // If the record exists, update it with the new name and birthdate
            $update_result = $wpdb->update(
                "{$wpdb->prefix}custom_input_data",
                array(
                    "name" => $name,
                    "birthdate" => $birthdate,
                ),
                array(
                    "email" => $email
                ),
                array(
                    "%s",
                    "%s"
                ),
                array(
                    "%s"
                )
            );

            if ($update_result !== false) {
                // Update successful
                wp_send_json_success([
                    "status" => "success",
                    "message" => "Record updated successfully."
                ]);
            } else {
                // Update failed
                wp_send_json_error([
                    "status" => "error",
                    "message" => "Error updating record."
                ]);
            }
        } else {
            // If the record does not exist, proceed with the existing logic to insert the data

            // Get the first letter of the name
            $first_letter = strtoupper(substr($name, 0, 1));

            $args = [
                "post_type" => "custom_item", // Replace 'custom_item' with your custom post type slug
                "posts_per_page" => -1, // Fetch all posts
                "orderby" => "title",
                "order" => "ASC",
            ];

            $custom_query = new WP_Query($args);

            // Check if custom items were found
            if ($custom_query->have_posts()) {
                $found = false;
                while ($custom_query->have_posts()) {
                    $custom_query->the_post();

                    $post_id = get_the_ID();
                    $post_title = get_the_title();

                    // Check if the first letter of the post title matches
                    $post_first_letter = strtoupper(substr($post_title, 0, 1));

                    if ($post_first_letter === $first_letter) {
                        $found = true;
                        $featured_image_url = get_the_post_thumbnail_url(
                            $post_id,
                            "thumbnail"
                        );

                        if ($featured_image_url) {
                            // Generate image tag
                            $image_tag =
                                '<img width="150" height="117" src="' .
                                esc_url($featured_image_url) .
                                '" alt="Featured Image">';

                            $table_name = $wpdb->prefix . "custom_input_data";

                            $insert = $wpdb->insert($table_name, [
                                "name" => $name,
                                "email" => $email,
                                "birthdate" => $birthdate,
                               // "first_letter" => $post_first_letter,
                            ]);
							
                            if ($insert !== false) {
                                wp_send_json_success([
                                    "status" => "success",
                                    "first_letter" => $first_letter,
                                    "full_name" => $name,
                                    "image_url" => $featured_image_url,
                                    "image_tag" => $image_tag,
                                    "post_id" => $post_id,
                                ]);
                            } else {
                                wp_send_json_error([
                                    "status" => "error",
                                    "message" => "Error inserting new record.",
                                ]);
                            }
                        } else {
                            wp_send_json_error([
                                "Not_found_image" => "Image not found",
                            ]);
                        }
                    }
                }

                wp_reset_postdata();

                // If no matching post is found
                if (!$found) {
                    wp_send_json_success([
                        "status" => "not_found",
                        "message" => "Data not found this letter",
                    ]);
                }
            } else {
                wp_send_json_error([
                    "Not_found_image" => "No custom items found.",
                ]);
            }
        }
    } else {
        wp_send_json_error(["Error" => "Please enter a name."]);
    }
}

    function game_page()
    {
        if (isset($_GET["letter"])) {
            $plugin_dir = plugin_dir_path(__FILE__);
            ob_start(); // Start output buffering
            include $plugin_dir . "/templates/game_one_letter.php";
            $content = ob_get_contents(); // Capture the output
            ob_end_clean(); // End output buffering and clean the buffer
            return $content; // Return the captured output
        }
    }

    function game_select()
    {
            $plugin_dir = plugin_dir_path(__FILE__);
            ob_start(); // Start output buffering
            include $plugin_dir . "/templates/game_select.php";
            $content = ob_get_contents(); // Capture the output
            ob_end_clean(); // End output buffering and clean the buffer
            return $content; // Return the captured output
    }

    function game_all_letters()
    {
        if (isset($_GET["letters"])) {
            $plugin_dir = plugin_dir_path(__FILE__);
            ob_start(); // Start output buffering
            include $plugin_dir . "/templates/game_all_letters.php";
            $content = ob_get_contents(); // Capture the output
            ob_end_clean(); // End output buffering and clean the buffer
            return $content; // Return the captured output
        }
    }
}

$custom_plugin_instance = new CustomPlugin();
