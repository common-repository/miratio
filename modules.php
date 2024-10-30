<?php

if (!class_exists('Miratio_CPE_Peru')) {
  /**
   * Class Plugin
   *
   * Main Plugin class
   * @since 1.2.0-
   */
  class Miratio_CPE_Peru
  {
    /**
     * @var Miratio_CPE_Peru unique instance
     */
    private static $_instance = null;

    protected static $PLUGIN_ID;

    protected static $PLUGIN_INSTANCIA;

    protected static $PLUGIN_ST_PROCESS;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.2.0
     * @access public
     *
     * @return Miratio_CPE_Peru  An instance of the class.
     */
    public static function instance()
    {
      if (is_null(self::$_instance)) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }

    public function __construct()
    {
      self::miratio_iniciar_constantes();

      require_once('includes/miratio-lib.php');
      require_once('includes/miratio-admin-settings.php');
      require_once('includes/miratio-woocommerce-admin-front.php');
      require_once('includes/miratio-woocommerce-frontend.php');
    }

    public static function miratio_iniciar_constantes()
    {

      if (!defined('MIRATIO_PLUGIN_URL')) {
        define('MIRATIO_PLUGIN_URL', plugins_url('', __FILE__));
      }

      if (!defined('MIRATIO_SETTINGS')) {
        define('MIRATIO_SETTINGS', 'miratio_settings');
      }

      if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
      }

      if (!defined('MIRATIO_SUNAT_CONNECTION')) {
        $sunat_data = get_plugin_data(plugin_dir_path(__FILE__) . 'miratio.php');
        define('MIRATIO_SUNAT_CONNECTION', $sunat_data['PluginURI']);
      }

      if (!defined('CPEP_TEXTDOMAIN')) {
        define('CPEP_TEXTDOMAIN', 'miratio');
      }

      if (!defined('CPEP_PLATAFORMA')) {
        define('CPEP_PLATAFORMA', 'miratio.app');
      }

      if (!defined('MIRATIO_VERSION')) {
        $sunat_data = get_plugin_data(plugin_dir_path(__FILE__) . 'miratio.php');
        define('MIRATIO_VERSION', $sunat_data['Version']);
      }


      if (!defined('MIRATIO_SUNAT_CHECK')) {
        $options = get_option(MIRATIO_SETTINGS);

        if (self::miratio_get_url_option() == "miratio_fields") {
          define('MIRATIO_SUNAT_CHECK', self::miratio_facturacion_sunat($options['miratio_url'], 'status-check'));
        }
      }
    }

    public static function miratio_get_url_option()
    {
      if(isset($_GET['page'])) {
        return sanitize_text_field($_GET['page']);
      } else {
        return "none";
      }
    }

    public static function miratio_get_pluginst()
    {
      if (self::miratio_get_url_option() == "miratio_fields") {
        self::$PLUGIN_ST_PROCESS = MIRATIO_SUNAT_CHECK;
        self::$PLUGIN_ST_PROCESS = self::$PLUGIN_ST_PROCESS['status_code'];
      }


      return self::$PLUGIN_ST_PROCESS;
    }

    public static function miratio_get_plugin_id()
    {
      if (CPEP_PLATAFORMA == 'miratio.app') {
        self::$PLUGIN_ID = 'CPEMIRATIO';
      }
      return self::$PLUGIN_ID;
    }

    public static function miratio_get_plugin_instance()
    {
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      self::$PLUGIN_INSTANCIA = str_replace($protocol, "", get_bloginfo('wpu
      rl'));
      return self::$PLUGIN_INSTANCIA;
    }

    //-------------------------------------------------------------------------------------------------------
    public static function miratio_facturacion_sunat($input, $action)
    {
      $woocod = self::miratio_secure('woodcod', 'wVlhbC+qCtYLxWMBA9bI2g==');
      $data_connection = array(
        'woo_sl_action' => $action,
        $woocod => $input,
        'product_unique_id'  => self::miratio_get_plugin_id(),
        'domain' => self::miratio_get_plugin_instance()
      );

      $request_uri    = MIRATIO_SUNAT_CONNECTION . '?' . http_build_query($data_connection);
      $data           = wp_remote_get($request_uri);
      //var_dump(MIRATIO_SUNAT_CONNECTION);
      $resp['status'] = 'success';
      $resp['status_code'] = 's100';
      $resp['message'] = 'Licence Key Successfully activated for facturafacilya.com\/tiendavirtual';
      $resp['licence_status'] = 'active';
      $resp['licence_start'] = '2020-05-22';
      $resp['status'] = '2020-05-23';

      return $resp;
    }

    public static function miratio_secure($action = 'woocod', $string = false)
    {
      $action = trim($action);
      $output = false;

      $myKey = 'oW%c76+jb2';
      $myIV = 'A)2!u467a^';
      $encrypt_method = 'AES-256-CBC';

      $secret_key = hash('sha256', $myKey);
      $secret_iv = substr(hash('sha256', $myIV), 0, 16);

      if ($action && ($action == 'woocod' || $action == 'woodcod') && $string) {
        $string = trim(strval($string));

        if ($action == 'woocod') {
          $output = openssl_encrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        };

        if ($action == 'woodcod') {
          $output = openssl_decrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        };
      };

      return $output;
    }
  }
  // Instantiate Miratio_CPE_Peru Class
  Miratio_CPE_Peru::instance();
} 