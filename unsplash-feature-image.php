<?php
/*
Plugin Name: Unsplash Image Fetcher
Plugin URI: https://www.techbreeze.in/plugins/unsplash-image-fetcher
Description: Fetches images from Unsplash based on post titles, converts them to PNG, and sets them as featured images automatically.
Version: 1.6
Author: Techbreeze IT Solutions
Author URI: https://www.techbreeze.in
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: unsplash-image-fetcher
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Unsplash_Image_Fetcher {

    private $api_key;
    private $is_fetching = false;

    public function __construct() {
        // Hook to create settings page
        add_action( 'admin_menu', array( $this, 'create_settings_page' ) );
        // Hook to setup sections in the settings page
        add_action( 'admin_init', array( $this, 'setup_sections' ) );
        // Hook to setup fields in the settings page
        add_action( 'admin_init', array( $this, 'setup_fields' ) );
        // Hook to fetch image when a post is saved
        add_action( 'save_post', array( $this, 'fetch_image' ), 10, 2 );
    }

    // Create the settings page in WordPress admin
    public function create_settings_page() {
        add_options_page(
            'Unsplash Image Fetcher Settings',
            'Unsplash Image Fetcher',
            'manage_options',
            'unsplash_image_fetcher',
            array( $this, 'settings_page_content' )
        );
    }

    // Content for the settings page
    public function settings_page_content() { ?>
        <div class="wrap">
            <h1>Unsplash Image Fetcher Settings</h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'unsplash_image_fetcher' );
                    do_settings_sections( 'unsplash_image_fetcher' );
                    submit_button();
                ?>
            </form>
        </div> <?php
    }

    // Setup sections for settings page
    public function setup_sections() {
        add_settings_section( 'unsplash_image_fetcher_section', '', array(), 'unsplash_image_fetcher' );
    }

    // Setup fields for settings page
    public function setup_fields() {
        add_settings_field( 'unsplash_api_key', 'Unsplash API Key', array( $this, 'field_callback' ), 'unsplash_image_fetcher', 'unsplash_image_fetcher_section' );
        register_setting( 'unsplash_image_fetcher', 'unsplash_api_key' );
    }

    // Callback for settings field
    public function field_callback() {
        $value = get_option( 'unsplash_api_key', '' );
        echo '<input type="text" name="unsplash_api_key" value="' . esc_attr( $value ) . '" />';
    }

    // Fetch image and set as featured image when post is saved
    public function fetch_image( $post_id, $post ) {
        if ( wp_is_post_revision( $post_id ) || $post->post_type != 'post' || $this->is_fetching || $post->post_status != 'draft' ) {
            return;
        }

        $title = get_the_title( $post_id );
        if ( empty( $title ) ) {
            error_log( 'Post title is empty. Skipping image fetch.' );
            return;
        }

        $this->is_fetching = true;
        $this->api_key = get_option( 'unsplash_api_key' );

        if ( ! $this->api_key ) {
            error_log( 'Unsplash API key is not set.' );
            $this->is_fetching = false;
            return;
        }

        if ( has_post_thumbnail( $post_id ) ) {
            error_log( 'Post already has a featured image.' );
            $this->is_fetching = false;
            return;
        }

        $image_url = $this->get_unsplash_image( $title );

        if ( $image_url ) {
            $png_image_path = $this->convert_image_to_png( $image_url );
            if ( $png_image_path ) {
                $this->set_featured_image( $post_id, $png_image_path );
            } else {
                error_log( 'Failed to convert image to PNG.' );
            }
        } else {
            error_log( 'Failed to retrieve image from Unsplash.' );
        }

        $this->is_fetching = false;
    }

    // Fetch image from Unsplash API
    private function get_unsplash_image( $query ) {
        $response = wp_remote_get( 'https://api.unsplash.com/photos/random?query=' . urlencode( $query ) . '&client_id=' . $this->api_key );

        if ( is_wp_error( $response ) ) {
            error_log( 'Unsplash API request error: ' . $response->get_error_message() );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['urls']['full'] ) ) {
            return $data['urls']['full'];
        }

        error_log( 'Unsplash API response does not contain image URL. Response: ' . print_r( $data, true ) );
        return false;
    }

    // Convert the fetched image to PNG
    private function convert_image_to_png( $image_url ) {
        $response = wp_remote_get( $image_url );
        if ( is_wp_error( $response ) ) {
            error_log( 'Failed to download image from URL: ' . $image_url );
            return false;
        }

        $image_data = wp_remote_retrieve_body( $response );

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($image_data);
        error_log( 'Image MIME type: ' . $mime_type );

        $image = imagecreatefromstring( $image_data );
        if ( $image === false ) {
            error_log( 'Failed to create image from string.' );
            return false;
        }

        $upload_dir = wp_upload_dir();
        $png_image_path = $upload_dir['path'] . '/' . uniqid() . '.png';

        if ( imagepng( $image, $png_image_path ) ) {
            imagedestroy( $image );
            return $png_image_path;
        } else {
            error_log( 'Failed to save PNG image.' );
            imagedestroy( $image );
            return false;
        }
    }

    // Set the PNG image as the featured image of the post
    private function set_featured_image( $post_id, $image_path ) {
        $upload_dir = wp_upload_dir();
        $filename = basename( $image_path );

        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }

        $image_data = $wp_filesystem->get_contents( $image_path ); // Using WP_Filesystem to handle file operations
        if ( $wp_filesystem->put_contents( $file, $image_data, FS_CHMOD_FILE ) ) {
            $wp_filetype = wp_check_filetype( $filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name( $filename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $post_id, $attach_id );

            error_log( 'Featured image set for post ID: ' . $post_id );
        } else {
            error_log( 'Failed to save the image using WP_Filesystem.' );
        }
    }
}

new Unsplash_Image_Fetcher();
?>
