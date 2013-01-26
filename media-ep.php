<?php
/*
Plugin Name: Media EP
Plugin URI: http://ofdesks.com/blog/media-ep
Description: A non-authenticating WordPress Media Endpoint for Twitter Clients
Version: 1.0.1
Author: Wes Koopmans
Author URI: http://ofdesks.com
License: GPL2
*/

class MediaEP {

  /**
   * Initializes the plugin by setting localization, filters, and administration functions.
   */
  function __construct() {

    // Localization
    load_plugin_textdomain('media-ep', false, basename( dirname( __FILE__ ) ) . '/lang' );


    // Activate/Deactivate
    register_activation_hook( __FILE__, array( &$this, 'activate' ) );
    register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

    // End Point
    add_action( 'init', array( &$this, 'rewrite_rules' ) );
    add_filter( 'query_vars', array( &$this, 'query_vars' ) );
    add_action( 'template_redirect', array( &$this, 'upload_endpoint' ) );

    // Settings Page
    add_action( 'admin_init', array( &$this, 'register_settings' ) );
    add_action( 'admin_menu', array( &$this, 'add_options_page' ) );

  } // end constructor


  /**
   * Fired when the plugin is activated.
   *
   * @params  $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
   */
  public function activate( $network_wide ) {

    add_option( 'media_ep_key', 'ep' . strtolower( substr( md5( time() ), 0, 10 ) ) );

    $this->rewrite_rules();
    flush_rewrite_rules();

  } // end activate


  /**
   * Fired when the plugin is deactivated.
   *
   * @params  $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
   */
  public function deactivate( $network_wide ) {

    flush_rewrite_rules();

  } // end deactivate


  /**
   * Update Rewrite urls.
   */
  public function add_options_page() {

    add_options_page('Media EP', 'Media EP', 'manage_options', 'media-ep', array( &$this, 'options_page_output' ) );

  }


  /**
   * Update Rewrite urls.
   */
  function options_page_output() {

    ?>
      <div class="wrap">
        <div id="icon-options-general" class="icon32"><br></div>
        <h2><?php _e( 'Custom Media Endpoint', 'media-ep' ); ?></h2>

        <form method="post" action="options.php">
          <?php settings_fields( 'media_ep' ); ?>

          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><label for="siteurl"><?php _e( 'Current End Point', 'media-ep' ); ?></label></th>
                <td>
                  <p><code><?php bloginfo( 'url' ); ?>/media-ep/<?php echo get_option('media_ep_key'); ?>/</code></p>
                  <p class="description"><?php _e( 'Use this URL as your <em>Custom Media End Point</em> in your favourite Twitter client', 'media-ep' ); ?></p>
                  <p class="description"><?php _e( '<strong>DO NOT SHARE THIS URL</strong> — With it anyone can upload images to your website', 'media-ep' ); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><label for="media_ep_key"><?php _e( 'Secret Key <em>(Make it long!)</em>', 'media-ep' ); ?></label></th>
                <td>
                  <input id="media_ep_key" name="media_ep_key" size="40" type="text" value="<?php echo get_option('media_ep_key'); ?>" class="regular-text ltr">
                  <button type="button" onclick="(function(){ var t='';var p='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';for(var i=0;i<10;i++)t+=p.charAt(Math.floor(Math.random()*p.length));document.getElementById('media_ep_key').value=t;})()">Random</button>
                  <p class="description"><?php _e( 'Accepts alphanumeric characters, make this as long as you want!', 'media-ep' ); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><label for="media_ep_parent_page"><?php _e( 'Attach Uploaded Media to', 'media-ep' ); ?></label></th>
                <td>
                  <?php wp_dropdown_pages( array( 'show_option_none' => '—', 'name' => 'media_ep_parent_page', 'selected' => get_option('media_ep_parent_page') ) ); ?>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><label for="media_ep_image_size"><?php _e( 'Image size to return to client', 'media-ep' ); ?></label></th>
                <td>
                  <select id="media_ep_image_size" name="media_ep_image_size">
                    <option value=""><?php _e( 'Full Size', 'media-ep' ); ?></option>
                    <?php
                      $image_sizes = array_reverse( get_intermediate_image_sizes() );
                      $selected='';
                      foreach ($image_sizes as $size) {
                        if (get_option('media_ep_image_size') === $size) $selected = ' selected';
                        echo '<option value="' . $size . '"' . $selected . '>' . $size . '</option>';
                        $selected='';
                      }
                    ?>
                    <option disabled="disabled">—</option>
                    <option value="media-ep-attachment" <?php if (get_option('media_ep_image_size') === 'media-ep-attachment') echo 'selected="selected"'; ?>><?php _e( 'Attachment Page', 'media-ep' ); ?></option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>

          <?php submit_button(); ?>

        </form>

      </div>
    <?php
  }


  /**
   * Settings Whitelist.
   */
  public function register_settings() {
    register_setting( 'media_ep', 'media_ep_key', array( &$this, 'media_ep_key_sanitize') );
    register_setting( 'media_ep', 'media_ep_parent_page', array( &$this, 'media_ep_parent_page_sanitize') );
    register_setting( 'media_ep', 'media_ep_image_size', array( &$this, 'media_ep_image_size_sanitize') );
  }


  /**
   * Sanitize Key
   */
  public function media_ep_key_sanitize( $input ) {

    return preg_replace( '([^a-zA-Z0-9])', '', $input );

  }


  /**
   * Sanitize Parent Page
   */
  public function media_ep_parent_page_sanitize( $input ) {

    if ( get_page( $input ) ) {
      return (int) $input;
    }

    return '';

  }


  /**
   * Sanitize Parent Page
   */
  public function media_ep_image_size_sanitize( $input ) {

    if ( in_array( $input, get_intermediate_image_sizes() ) || $input === 'media-ep-attachment' ) {
      return $input;
    }

    return '';

  }



  /**
   * Update Rewrite urls.
   */
  public function rewrite_rules() {

    // Upload "API" end point
    add_rewrite_rule( 'media-ep/(.+?)/?$', 'index.php?media_ep_key=$matches[1]', 'top' );

  } // end rewrite_rules



  /**
   * Add Query Var
   */
  public function query_vars( $query_vars ) {

      $query_vars[] = 'media_ep_key';
      return $query_vars;

  } // end query_vars



  /**
   * Process Upload
   */
  public function upload_endpoint( ) {

    if ( get_option( 'media_ep_key') === get_query_var( 'media_ep_key' ) && !empty( $_FILES['media'] ) ) {

      require_once(ABSPATH . 'wp-admin/includes/image.php');
      require_once(ABSPATH . 'wp-admin/includes/file.php');
      require_once(ABSPATH . 'wp-admin/includes/media.php');

      $media = $_FILES['media'];

      // Keep them unique, rather than incremented, incase they are deleted.
      $media['name'] = 'image_' . substr( md5( time() ), 0, 4 ) . substr( $media['name'], strrpos( $media['name'], '.' ) );

      $upload = wp_handle_upload( $media, array('test_form' => false) );

      if(!isset($upload['error']) && isset($upload['file'])) {
        $filetype   = wp_check_filetype(basename($upload['file']), null);
        $message = sanitize_text_field( $_POST['message'] );

        $attachment = array(
          'guid'              => $upload['baseurl'] . _wp_relative_upload_path( $upload['file'] ),
          'post_mime_type'    => $filetype['type'],
          'post_title'        => $message,
          'post_content'      => $message,
          'post_excerpt'      => $message,
          'post_status'       => 'inherit',
          'post_parent'       => get_option('media_ep_parent_page')
        );

        $attachment_id  = wp_insert_attachment( $attachment, $upload['file']);
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
        wp_update_attachment_metadata( $attachment_id,  $attachment_data );

        $attachment_size = get_option('media_ep_image_size');
        if ($attachment_size === 'media-ep-attachment')
          $attachment_url = get_attachment_link( $attachment_id );
        else
          list( $attachment_url ) = wp_get_attachment_image_src( $attachment_id, $attachment_size );

        // header( 'Content-type: application/json' );
        // echo json_encode( array( 'url' => $attachment_url ) );

        // This "XML" is more compatible, Love JSON (Above) but it doesn't work with Twitterrific
        echo "<mediaurl>$attachment_url</mediaurl>";
        die();

      }
    }
  } // end upload_endpoint


} // end class MediaEP

new MediaEP();
