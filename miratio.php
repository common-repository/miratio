<?php
/*
Plugin Name: MIRATIO
Plugin URI: https://miratio.net/woocommerce/
Description: Conexión de Woocommerce con el sistema de facturación electrónica peruana MIRATIO
Author: Nextcoders
Author URI: https://miratio.net
Version: 2.6
Text Domain: MIRATIO

	Copyright: © 9 Nextcoders
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

final class Miratio
{
  /**
   * Plugin Version
   *
   * @since 1.0
   * @var string The plugin version.
   */
  const VERSION = '2.6';

  /**
   * Minimum PHP Version
   *
   * @since 1.2.0
   * @var string Minimum PHP version required to run the plugin.
   */
  const MINIMUM_PHP_VERSION = '7.0';

  public function __construct()
  {
    // Load translation
    add_action('init', array($this, 'i18n'));
    // Init Plugin
    add_action('plugins_loaded', array($this, 'init'));
  }

  /**
   * Load Textdomain
   *
   * Load plugin localization files.
   * Fired by `init` action hook.
   *
   * @since 1.2.0
   * @access public
   */
  public function i18n()
  {
    load_plugin_textdomain('Miratio');
  }

  public function init()
  {
    //validar que este instalado WooCommerce
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      //if ( ! function_exists( 'WC' ) ) {
      add_action('admin_notices', array($this, 'admin_notice_missing_woocommerce_plugin'));
      return;
    }
    // Check for required PHP version
    if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
      add_action('admin_notices', array($this, 'admin_notice_minimum_php_version'));
      return;
    }
    //validar que este instalado Table rates

    //Incluir el plugin
    require_once('modules.php');
  }

  public function admin_notice_missing_woocommerce_plugin()
  {
    $message = sprintf(
      /* translators: 1: Plugin name 2: WooCommerce */
      esc_html__('"%1$s" require que "%2$s" este instalado y activado.', 'miratio'),
      '<strong>' . esc_html__('MIRATIO PERU', 'miratio') . '</strong>',
      '<strong>' . esc_html__('WooCommerce', 'miratio') . '</strong>'
    );
    printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
  }

  /**
   * Admin notice
   *
   * Warning when the site doesn't have a minimum required PHP version.
   *
   * @since 1.0.0
   * @access public
   */
  public function admin_notice_minimum_php_version()
  {
    if (isset($_GET['activate'])) {
      unset($_GET['activate']);
    }
    $message = sprintf(
      /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
      esc_html__('"%1$s" requiere "%2$s" version %3$s o mayor.', 'miratio'),
      '<strong>' . esc_html__('MIRATIO PERU', 'miratio') . '</strong>',
      '<strong>' . esc_html__('PHP', 'miratio') . '</strong>',
      self::MINIMUM_PHP_VERSION
    );
    printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
  }
}

new Miratio();
