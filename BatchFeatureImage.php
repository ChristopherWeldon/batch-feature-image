<?php

/*
 Plugin Name: Batch Feature Image
 Plugin URI:
 Description: Set the feature image of all post
 Version: 0.1
 Author: Chris Weldon
 License: GPLv2
 */

class BatchFeatureImage {

    function __construct() {
        add_action('init', array($this, 'init'));
    }

    function init() {
        add_action('admin_menu', array($this, 'add_tools_page'));

    }

    /**
     * Add page under "tools" section
     */
    function add_tools_page() {
        add_management_page('Batch Feature Image', 'Batch Feature Image', 'manage_options', 'batch-feature-image', array($this, 'batchfeature_admin_page'));
    }

    /**
     * Admin page
     */
    function batchfeature_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        } else {            
            echo '<div class="wrap">';
            echo '<h2>Feature Image Batch</h2>';

            if (isset($_POST['set-images'])) {
                // Update images
                $output = $this -> set_post_img_batch();

                if (!empty($output) && $output != '<ul></ul>') {
                    //Display output
                    echo $output;
                } else {
                    echo 'No images updated.';
                }
            } elseif (isset($_POST['unset-images'])) {
                //TODO before we can enable this setup database table to track what images were set before the finished running.

            } else {
                // Admin page output!!!
                echo '<form action="' . admin_url('tools.php?page=batch-feature-image') . '" method="post">';
                echo '<fieldset>';
                echo '<input type="submit" name="set-images" value="Set Images" class="button-primary" />';
                //Set feature images
                echo '&nbsp;&nbsp;&nbsp;';
                //echo '<input type="submit" name="unset-images" value="Unset Images" class="button-primary" />';//>Unset feature images
                echo '</fieldset>';
                echo '</form>';
            }
        }
        echo '</div>';
    }

    /**
     * Get the ID of the first attachedment with the type image/jpge associated with a given post ID.. Null if none are found.
     * @param Int, Post ID
     * @return int ID of image
     */
    function get_attachment_id_from_postID($post_ID) {

        global $wpdb;

        //query ID from first attachment with type of image
        $id = $wpdb -> get_var($wpdb -> prepare("SELECT ID FROM {$wpdb->posts} WHERE `post_type` = %s AND `post_mime_type` = %s AND `post_parent` = %d ORDER by ID ASC LIMIT 1", 'attachment', 'image/jpeg ', $post_ID));

        return $id;

    }

    /**
     * Set the thumbnail/feature image for each post
     */
    function set_post_img_batch() {
        $output = '<ul>';
        $all_post = get_posts();
        foreach ($all_post as $post) {
            setup_postdata($post);
            $post_ID = $post -> ID;

            //Skip this post if it already has a feature image
            if (is_numeric($post_ID) && !has_post_thumbnail($post_ID)) {
                $thumbnail_id = $this -> get_attachment_id_from_postID($post_ID);
                // Make sure we have a valid thumbnail ID
                if (is_numeric($thumbnail_id) && $thumbnail_id > 0) {
                    $title = get_the_title($post_ID);
                    $post_link = get_edit_post_link($post_ID);
                    $thumbnail_link = wp_get_attachment_link($thumbnail_id,array(32,32));
                    $output .= '<li>Post: <a href="'.$post_link.'">'
                            .$title.' (ID: ' . $post_ID . ')</a> Feature Image set to: '.$thumbnail_link
                        . '</li>';

                    set_post_thumbnail($post_ID, $thumbnail_id);
                }
            }

        }
        $output .= '</ul>';

        return $output;
    }

}

new BatchFeatureImage();
?>