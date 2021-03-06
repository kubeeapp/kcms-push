<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/kubeeapp/kcms-push/
 * @since             3.0.3
 * @package           kcmspush
 *
 * @wordpress-plugin
 * Plugin Name: KCMS Push
 * Plugin URI: https://github.com/kubeeapp/kcms-push
 * Description: Deployment of themes and plugins directly from GitHub.
 * Version: 3.0.3
 * Author: WP Pusher
 * Author URI: https://github.com/kubeeapp/
 * License: GNU GENERAL PUBLIC LICENSE
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kcmspush
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

require __DIR__ . '/autoload.php';

use Pusher\ActionHandlers\ActionHandlerProvider;
use Pusher\Pusher;
use Pusher\PusherServiceProvider;

$pusher = new Pusher;
$pusher->setInstance($pusher);
$pusher->pusherPath = plugin_dir_path(__FILE__);
$pusher->pusherUrl = plugin_dir_url(__FILE__);
$pusher->register(new PusherServiceProvider);
$pusher->register(new ActionHandlerProvider);

do_action('wppusher_register_dependency', $pusher);

register_activation_hook(__FILE__, array($pusher, 'activate'));

require_once('wp-updates-plugin.php');
new WPUpdatesPluginUpdater_957('https://dashboard.wppusher.com/api/releases/latest', plugin_basename(__FILE__));

$pusher->init();

if ( ! function_exists('getHostIcon')) {
    function getHostIcon($host)
    {
        if ($host === 'gh') {
            return 'fa-github';
        } elseif ($host === 'bb') {
            return 'fa-bitbucket';
        } else {
            return 'fa-gitlab';
        }
    }
}

if ( ! function_exists('getHostBaseUrl')) {
    function getHostBaseUrl($host)
    {
        if ($host === 'gh') {
            return 'https://github.com/';
        } elseif ($host === 'bb') {
            return 'https://bitbucket.org/';
        } elseif ($host === 'gl') {
            return trailingslashit(get_option('gl_base_url'));
        } else {
            return null;
        }
    }
}

$hidePluginsFromUpdateChecks = function($args, $url) use ($pusher)
{
    if (0 !== strpos($url, 'https://api.wordpress.org/plugins/update-check')) {
        return $args;
    }

    $plugins = json_decode($args['body']['plugins'], true);

    $repository = $pusher->make('Pusher\Storage\PluginRepository');
    $pluginsToHide = array_keys($repository->allPusherPlugins());
    $pluginsToHide[] = plugin_basename(__FILE__);

    foreach ($pluginsToHide as $plugin) {
        unset($plugins['plugins'][$plugin]);
        unset($plugins['active'][array_search($plugin, $plugins['active'])]);
    }

    $args['body']['plugins'] = json_encode($plugins);

    return $args;
};

$hideThemesFromUpdateChecks = function($args, $url) use ($pusher)
{
    if (0 !== strpos($url, 'https://api.wordpress.org/themes/update-check')) {
        return $args;
    }

    $themes = json_decode($args['body']['themes'], true);

    $repository = $pusher->make('Pusher\Storage\ThemeRepository');
    $themesToHide = array_keys($repository->allPusherThemes());

    foreach ($themesToHide as $theme) {
        unset($themes['themes'][$theme]);

        if (isset($themes['active']) and in_array($themes['active'], $themesToHide)) {
            unset($themes['active']);
        }
    }

    $args['body']['themes'] = json_encode($themes);

    return $args;
};

add_filter('http_request_args', $hidePluginsFromUpdateChecks, 5, 2);
add_filter('http_request_args', $hideThemesFromUpdateChecks, 5, 2);

// Add link to help page
add_action('admin_menu', function () {
    global $submenu;

    if (current_user_can('manage_options')) {
        $submenu['wppusher'][] = array('Get Help', 'manage_options', 'https://wppusher.com/support');
    }
});

// Dismiss welcome hero
if (isset($_GET['wppusher-welcome']) and $_GET['wppusher-welcome'] == '0') {
    update_option('hide-wppusher-welcome', true);
}

if ( ! function_exists('pusherTableName()')) {
    function pusherTableName()
    {
        global $wpdb;
        $dbPrefix = is_multisite() ? $wpdb->base_prefix : $wpdb->prefix;

        return $dbPrefix . 'wppusher_packages';
    }
}

if ( ! function_exists('pusher')) {
    /**
     * @return \Pusher\Pusher
     */
    function pusher() {
        return \Pusher\Pusher::getInstance();
    }
}