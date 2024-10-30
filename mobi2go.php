<?php

/**
 * Plugin Name: Mobi2Go
 * Plugin URI: https://mobi2go.com
 * Description: Mobi2Go’s online ordering plugin lets your customers order online through your WordPress website.
 * Version: 1.0.0
 * Author: Mobi2Go
 * Author URI: https://mobi2go.com
 */

require_once(dirname(__FILE__) . '/Mobi2GoAdminPage.php');

/*
* Only load the admin page if user has admin permissions
*/
if (is_admin()) {
    $adminPage = new Mobi2GoAdminPage();
}

/**
 * mobi2go_shortcode
 * Adds [mobi2go] tag to wordpress for easy embedding
 *
 * @param array $atts Array of attributes added to the short code
 * @return string mobi2go embedded code or nothing if site name is not set
 */
function mobi2go_shortcode($atts)
{
    $options = get_option('mobi2go-settings');
    $container = empty($options['container']) ? 'mobi2go-ordering' : $options['container'];
    $site = empty($options['site']) ? false : $options['site'];

    if (!$site) {
        return '';
    } else {
        wp_enqueue_script('mobi2go-js', plugin_dir_url(__FILE__) . 'js/mobi2go.js', array(), false, true);
        wp_localize_script('mobi2go-js', 'mobi2go', array(
            'site' => $site,
            'container' => $container,
        ));

        return '<div id="' . $container . '"></div>';
    }
}

// Add the shortcode [mobi2go] to WordPress and give it the function to run
add_shortcode('mobi2go', 'mobi2go_shortcode');
