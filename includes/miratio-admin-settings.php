<?php

//-------------------------------------------------------------------------------------------------------
add_action('admin_menu', 'miratio_cpe_plugin_settings_page');
function miratio_cpe_plugin_settings_page()
{
  if (CPEP_PLATAFORMA == "miratio.app") {
    $page_title = 'Facturación electrónica MIRATIO';
    $menu_title = 'MIRATIO';
  }
  $capability = 'edit_posts';
  $slug = 'miratio_fields';
  $callback = 'miratio_cpe_settings_page_content';
  $icon = 'dashicons-admin-plugins';
  $position = 100;

  //add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
  //Add menu to settings option on wordpress
  add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $slug, $callback);
}
//-------------------------------------------------------------------------------------------------------

function miratio_cpe_settings_page_content()
{ ?>
  <div class="wrap">
    <h2><?php _e('Datos de su sistema de Facturación', CPEP_TEXTDOMAIN); ?></h2>
    <p><?php
        /*
          _e('Para obtener tu token y datos de configuraicón ingresa a: 
              <a href="https://facturalaya.com/sys/login"
                target="_blank">miratio.app</a></p>', CPEP_TEXTDOMAIN) 
        */
        ?>
    <form action='options.php' method='post'>
      <?php
      //echo WOOWEB_SUNAT_CHECK['status_code'].'<br/>';
      settings_fields('miratio_fields');
      do_settings_sections('miratio_fields');
      submit_button();
      ?>
    </form>

  </div> <?php

        }
        //-------------------------------------------------------------------------------------------------------

        add_action('admin_init', 'miratio_setup_sections');
        function miratio_setup_sections()
        {

          register_setting('miratio_fields', MIRATIO_SETTINGS, 'miratio_validate_fields');

          $stProcess = Miratio_CPE_Peru::miratio_get_pluginst();
          if (($stProcess != 's203' && ($stProcess != 'e110') && $stProcess != 'e204' && $stProcess != 'e002' && $stProcess != 'e312' && $stProcess != 'e001')
            || (get_option('miratio_sunnat_con') == 1 && $stProcess == 'e312')
          ) {
          } else {
            update_option('miratio_sunnat_con', 1);
            add_settings_section('section_sunat_connection', 'ACTIVAR LICENCIA', 'section_license_callback', 'miratio_fields');
          }

          add_settings_section('section_configuracion', 'CONFIGURACIÓN', 'section_configuracion_callback', 'miratio_fields');
          add_settings_section('section_connection', 'CONEXIÓN', 'section_connection_callback', 'miratio_fields');
          add_settings_section('section_config_cpe', 'CONFIGURACION CPE', 'section_config_cpe_callback', 'miratio_fields');
          add_settings_section('section_currency', 'OTROS DATOS', 'section_currency_callback', 'miratio_fields');
        }
        //-------------------------------------------------------------------------------------------------------

        function section_configuracion_callback()
        {
          echo __('Configura el comportamiento del plugin', CPEP_TEXTDOMAIN);
        }

        function section_config_cpe_callback()
        {
          echo 'Ingresa la series con las cuales trabajarás, previamente ingresalo en tu sistema de facturación electrónica';
        }

        function section_currency_callback()
        {
          echo 'Ingresa la moneda oficial de tu tienda virtual USD (Dólares americanos), PEN (Soles Peruanos), etc y la unidad de medida global de tus productos ZZ (Servicios) ó NIU (Productos), si desea alguna otra unidad revisar su sistema de facturación y colocar el que encuentre en su catálogo.';
        }

        function section_connection_callback()
        {
          echo 'Debes Ingresar la RUTA de Conexión, y tu TOKEN, estos datos puedes encontrarlos en la opción de "Configuración de Empresa - Integración API Rest"';
        }

        function section_license_callback()
        {
          echo 'Ingresa los datos de tu licencia';
        }

        //-------------------------------------------------------------------------------------------------------

        add_action('admin_init', 'miratio_setup_fields');
        function miratio_setup_fields()
        {

          $stProcess = Miratio_CPE_Peru::miratio_get_pluginst();

          if (($stProcess != 's203' && ($stProcess != 'e110') && $stProcess != 'e204' && $stProcess != 'e002' && $stProcess != 'e312' && $stProcess != 'e001')
            || (get_option('miratio_sunnat_con') == 1 && $stProcess == 'e312')
          ) {

            //configuración fields
            add_settings_field('miratio_enabled_checkout', 'Habilitar', 'miratio_enabled_checkout_render', 'miratio_fields', 'section_configuracion');
            add_settings_field('miratio_enabled_status_processing', 'Estado del pedido', 'miratio_enabled_status_processing_render', 'miratio_fields', 'section_configuracion');

            //series fields
            /*
                add_settings_field( 'miratio_invoice_serie', 'Facturas', 'miratio_invoice_serie_render', 'miratio_fields', 'section_config_cpe' );
              add_settings_field( 'miratio_boleta_serie', 'Boletas', 'miratio_boleta_serie_render', 'miratio_fields', 'section_config_cpe' );
              */
            add_settings_field('miratio_sucursal', 'ID Sucursal', 'miratio_sucursal_render', 'miratio_fields', 'section_config_cpe');
            add_settings_field('miratio_vendedor', 'ID Usuario Ventas', 'miratio_vendedor_render', 'miratio_fields', 'section_config_cpe');


            //moneda - seccion 2
            add_settings_field('miratio_currency', 'Moneda', 'miratio_currency_render', 'miratio_fields', 'section_currency');
            add_settings_field('miratio_cambio', 'Tipo de Cambio', 'miratio_cambio_render', 'miratio_fields', 'section_currency');
            add_settings_field('miratio_und', 'Unidad de Medida', 'miratio_und_render', 'miratio_fields', 'section_currency');
            add_settings_field('miratio_productos', 'Productos', 'miratio_products_sync_render', 'miratio_fields', 'section_currency');
            //Conexión
            add_settings_field('miratio_url', 'URL', 'miratio_url_render', 'miratio_fields', 'section_connection');
            add_settings_field('miratio_tokem', 'TOKEN', 'miratio_tokem_render', 'miratio_fields', 'section_connection');

            if (CPEP_PLATAFORMA == 'miratio.app') {
              //add_settings_field( 'miratio_apidevurl', 'TOKEM CONSULTAS', 'miratio_apidevurl_render', 'miratio_fields', 'section_connection' );
              add_settings_field('miratio_nf_sunatcliente', 'Adicionales', 'miratio_nf_sunatcliente_render', 'miratio_fields', 'section_connection');
            }
          }
          add_settings_field('miratio_sunat_conn', 'N° LICENCIA', 'miratio_sunat_conn_render', 'miratio_fields', 'section_sunat_connection');
        }

        //-------------------------------------------------------------------------------------------------------
        //validated when the options is saving
        function miratio_validate_fields($input)
        {

          $stProcess = Miratio_CPE_Peru::miratio_get_pluginst();

          $options = get_option(MIRATIO_SETTINGS);

          if (!($stProcess == 's101') && !($stProcess == 's100')) {

            $datosSUNAT = Miratio_CPE_Peru::miratio_facturacion_sunat($input['miratio_sunat_connection'], 'activate');

            $status = $datosSUNAT['status'];
            $type = $status;

            if ($status == 'success')  $type = 'updated';

            /*
            add_settings_error(
                      'notified',
                      esc_attr( 'settings_updated' ),
                      "Cambios actualizados y ". $datosSUNAT['status_code'].'/'.$datosSUNAT['message'],
                      $type
                  );
              */
          }

          if ($datosSUNAT['status_code'] == 's100' || $datosSUNAT['status_code'] == 's101') {
            update_option('miratio_sunnat_con', 1);
          }

          return $input;
        }

        //-------------------------------------------------------------------------------------------------------

        function miratio_enabled_checkout_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input type="checkbox" name="miratio_settings[miratio_enabled_checkout]" value="1"' . checked(1, miratio_check_option_array_clean($options, 'miratio_enabled_checkout'), false) . '/> Formulario en el Checkout<br/>';
          echo '<input type="checkbox" name="miratio_settings[miratio_enabled_shipping]" value="1"' . checked(1, miratio_check_option_array_clean($options, 'miratio_enabled_shipping'), false) . '/> Agregar el costo de envío como un item en el detalle de tu CPE';
        }

        function miratio_enabled_status_processing_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo "<b>Generar comprobante según el estado del pedido:</b><br/>";
          echo '<input type="checkbox" name="miratio_settings[miratio_enabled_status_processing]" value="1"' . checked(1, miratio_check_option_array_clean($options, 'miratio_enabled_status_processing'), false) . '/> Procesando<br/>';
          echo '<input type="checkbox" name="miratio_settings[miratio_enabled_status_completed]" value="1"' . checked(1, miratio_check_option_array_clean($options, 'miratio_enabled_status_completed'), false) . '/> Completado<br/>';
          echo '<input type="checkbox" name="miratio_settings[miratio_enabled_status_payment]" value="1"' . checked(1, miratio_check_option_array_clean($options, 'miratio_enabled_status_payment'), false) . '/> Cuando el pago es confirmado';
        }

        /*
function miratio_invoice_serie_render(  ) {
  $options = get_option( miratio_SETTINGS );
  echo '<input name="miratio_settings[miratio_invoice_serie]" placeholder="Ejemplo: F001" type="text" value="' . checkOptionArrayClean($options,'miratio_invoice_serie') . '" />';
  if (CPEP_PLATAFORMA == "facturalaya.com") {
    echo '-<input name="miratio_settings[miratio_invoice_serie_num]" type="text" placeholder="1" value="' . checkOptionArrayClean($options,'miratio_invoice_serie_num') . '" />';
  }
}

function miratio_boleta_serie_render(  ) {
  $options = get_option( miratio_SETTINGS );
  echo '<input name="miratio_settings[miratio_boleta_serie]" placeholder="Ejemplo: B001" type="text" value="' . checkOptionArrayClean($options,'miratio_boleta_serie') . '" />';
  if (CPEP_PLATAFORMA == "facturalaya.com") {
    echo '-<input name="miratio_settings[miratio_boleta_serie_num]" type="text" placeholder="1" value="' . checkOptionArrayClean($options,'miratio_boleta_serie_num') . '" />';
  }
}
*/

        function miratio_sucursal_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_sucursal]" placeholder="Ejemplo: 1, 2, 39" type="text" value="' . miratio_check_option_array_clean($options, 'miratio_sucursal') . '" />';
        }

        function miratio_vendedor_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_vendedor]" placeholder="Ejemplo: 1, 2, 39" type="text" value="' . miratio_check_option_array_clean($options, 'miratio_vendedor') . '" />';
        }

        function miratio_currency_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_currency]" type="text" placeholder="Ejemplo: PEN" value="' . miratio_check_option_array_clean($options, 'miratio_currency') . '" />';
        }

        function miratio_cambio_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_tipo_cambio]" type="text" placeholder="3.35" value="' . miratio_check_option_array_clean($options, 'miratio_tipo_cambio') . '" />';
        }

        function miratio_und_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_und]" type="text" placeholder="Ejemplo: NIU o ZZ" value="' . miratio_check_option_array_clean($options, 'miratio_und') . '" />';
        }

        function miratio_url_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo 'https://miratio.app/sys/api/procesar_venta';
          echo '<input name="miratio_settings[miratio_url]" type="hidden" value="https://miratio.app/sys/api/procesar_venta" style="min-width:350px" />';
        }

        function miratio_tokem_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_tokem]" type="text" value="' . miratio_check_option_array_clean($options, 'miratio_tokem') . '" style="min-width:350px" />';
        }

        function miratio_products_sync_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          // echo '<button >' ."SINCRONIZAR PRODUCTOS". '</button>';
          
          echo '<a href="options-general.php?page=miratio_fields&sync=1" type="button" id="syncProducts"  class="add_note button center button-primary" style="width: 20%; text-align: center;">Sincronizar Stock Productos</a>';
          if (isset($_GET['sync'])) {
            $sync = sanitize_text_field($_GET['sync']);
            if (!empty($sync)) {
              echo "<br>";
              do_action('wp_miratio_update_products');
            }
          }
        }



        function miratio_apidevurl_render()
        {
          /*
          $options = get_option( miratio_SETTINGS );
          echo '<input name="miratio_settings[miratio_apidevurl]" type="text" value="' . checkOptionArrayClean($options,'miratio_apidevurl') . '" style="min-width:250px" /><br/>';
          if (CPEP_PLATAFORMA == "facturalaya.com") {
            echo "NubeFact no provee una forma de consultar RUC y DNI, cobra un fee adicional por realizar dichas consultas, por lo tanto es necesario usar un servicio externo, registrate en https://apiperu.dev, este sistema brinda 1500 consultas gratuitas x mes";
          }
          */
        }

        function miratio_nf_sunatcliente_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          if (CPEP_PLATAFORMA == "miratio.app") {
            //echo '<input type="checkbox" name="miratio_settings[miratio_ambiente_pruebas]" value="1"' . checked( 1, checkOptionArrayClean($options,'miratio_ambiente_pruebas'), false ) . '/> Enviar al Ambiente de PRUEBAS<br/>';
            echo '<input type="checkbox" name="miratio_settings[miratio_nf_clientedirecto]" value="1"' . checked(1, miratio_check_option_array_clean($options, 'miratio_nf_clientedirecto'), false) . '/> Enviar Email automaticamente al cliente';
          }
        }

        function miratio_sunat_conn_render()
        {
          $options = get_option(MIRATIO_SETTINGS);
          echo '<input name="miratio_settings[miratio_sunat_connection]" type="text" value="' . miratio_check_option_array_clean($options, 'miratio_sunat_connection') . '" style="min-width:250px" /><br/>';
          //echo WOOWEB_SUNAT_CHECK['message'].'<br/>';
          echo "Encuentra la clave de tu licencia en el detalle de tu pedido de <a href='https://wooweb.site/mi-cuenta' target='_blank'>https://wooweb.site/mi-cuenta</a>, recuerda que algunas funcionalidades no estan disponibles si no activas tu licencia.";
        }
