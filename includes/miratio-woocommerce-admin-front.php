<?php
//-------------------------------------------------------------------------------------------------------
/**ADD FRONTEND SCRIPT AND CSS **/
add_action('admin_enqueue_scripts', 'miratio_styles_admin');
function miratio_styles_admin()
{

  wp_register_style(
    'miratio',
    MIRATIO_PLUGIN_URL . '/assets/css/miratio.css',
    array(),
    '1.1'
  );

  if (CPEP_PLATAFORMA == 'miratio.app') {
    wp_register_script('miratio-js', MIRATIO_PLUGIN_URL . '/assets/js/miratio-cpe-checkout-nf.js', array('jquery'), '1.0', true);
  }
  wp_enqueue_script('miratio-js');

  wp_localize_script('miratio-js', 'ajax_miratio_cpe_peru', array(
    'ajax_url' => admin_url('admin-ajax.php')
  ));
}
//-------------------------------------------------------------------------------------------------------
//View cpe from order list
add_filter('manage_edit-shop_order_columns', 'miratio_new_order_column');
function miratio_new_order_column($columns)
{
  $columns['cpe'] = 'Comprobante';
  return $columns;
}
// Adding custom fields meta data for each new column
add_action('manage_shop_order_posts_custom_column', 'miratio_custom_orders_list_column_content', 20, 2);
function miratio_custom_orders_list_column_content($column, $post_id)
{
  switch ($column) {
    case 'cpe':
      // Get custom post meta data
      miratio_get_cpe_urls($post_id, false);
      break;
  }
}
//-------------------------------------------------------------------------------------------------------
//Permite emitir/ver comprobante desde el detalle de pedido
/**
 * Display field sunat values on the order edit page
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'miratio_checkout_field_display_admin_order_meta', 10, 1);

function miratio_checkout_field_display_admin_order_meta($order)
{

  $cpeTypeId = miratio_get_cpe_type_id($order->get_id());

  if ($cpeTypeId != '') {
    $cpeName = miratio_tipo_doc_cliente($cpeTypeId);

    echo '<div class="address">';
    echo '<h3>Datos CPE</h3>';
    echo '<p><strong>' . __('Tipo de Documento', CPEP_TEXTDOMAIN) . ':</strong> ' . $cpeName . '</p>';

    echo '<p><strong>' . __('N. ' . $cpeName, CPEP_TEXTDOMAIN) . ':</strong> ' . get_post_meta($order->get_id(), 'miratio_registro', true) . '</p>';
    echo '<p><strong>' . __('Nombre', CPEP_TEXTDOMAIN) . ':</strong> ' . get_post_meta($order->get_id(), 'miratio_razonsocial', true) . '</p>';

    if ($cpeTypeId == 6) {
      echo '<p><strong>' . __('Domicilio Fiscal', CPEP_TEXTDOMAIN) . ':</strong> ' . get_post_meta($order->get_id(), 'miratio_domiciliofiscal', true) . '</p>';
      echo '<p><strong>' . __('Ubigeo', CPEP_TEXTDOMAIN) . ':</strong> ' . get_post_meta($order->get_id(), 'miratio_ubigeo', true) . '</p>';
    }

    echo '</div>';
  }

  echo '<div class="edit_address">';

  woocommerce_wp_select(array(
    'id' => 'wooweb_cpe_tipo_documento',
    'label' => 'Tipo de Documento',
    'wrapper_class' => 'form-field',
    'class' => '_billing_address_1_field  sunat_field',
    'options' => array(
      'blank'    => __('Seleccione su documento', CPEP_TEXTDOMAIN),
      '1'  => __('DNI', CPEP_TEXTDOMAIN),
      '6'  => __('RUC', CPEP_TEXTDOMAIN),
      //'A' 	=> __( 'Doc.trib.no.dom.sin.ruc', CPEP_TEXTDOMAIN ),
      '4'   => __('Carnet de Extranjería', CPEP_TEXTDOMAIN),
      '7'   => __('Pasaporte', CPEP_TEXTDOMAIN)
    ),
    'value' => get_post_meta($order->get_id(), 'miratio_tipo_documento', true)
  ));

  woocommerce_wp_text_input(array(
    'id' => 'wooweb_cpe_registro',
    'label' => __('N. ', CPEP_TEXTDOMAIN),
    'wrapper_class' => 'form-field',
    'class' => '_billing_address_2_field  sunat_field',
    'value' => get_post_meta($order->get_id(), 'miratio_registro', true)
  ));

  woocommerce_wp_text_input(array(
    'id' => 'wooweb_cpe_razonsocial',
    'label' => __('Nombre ', CPEP_TEXTDOMAIN),
    'wrapper_class' => 'form-field',
    'class' => '_billing_address_1_field  sunat_field',
    'value' => get_post_meta($order->get_id(), 'miratio_razonsocial', true)
  ));

  woocommerce_wp_text_input(array(
    'id' => 'wooweb_cpe_domiciliofiscal',
    'label' => __('Domicilio Fiscal', CPEP_TEXTDOMAIN),
    'wrapper_class' => 'form-field',
    'class' => '_billing_address_2_field  sunat_field',
    'value' => get_post_meta($order->get_id(), 'miratio_domiciliofiscal', true)
  ));

  woocommerce_wp_text_input(array(
    'id' => 'wooweb_cpe_ubigeo',
    'label' => __('Ubigeo', CPEP_TEXTDOMAIN),
    'wrapper_class' => 'form-field',
    'class' => '_billing_address_2_field  sunat_field',
    'value' => get_post_meta($order->get_id(), 'miratio_ubigeo', true)
  ));

  echo '</div>';
}
//-------------------------------------------------------------------------------------------------------
add_action('woocommerce_process_shop_order_meta', 'miratio_save_shipping_details');
function miratio_save_shipping_details($order_id)
{

  $tipo_doc_cliente = $_POST['wooweb_cpe_tipo_documento'];

  if (!empty($_POST['wooweb_cpe_tipo_documento'])) {
    update_post_meta($order_id, 'miratio_tipo_documento', wc_clean($_POST['wooweb_cpe_tipo_documento']));
  }

  if (!empty($_POST['wooweb_cpe_registro'])) {
    update_post_meta($order_id, 'miratio_registro', wc_clean($_POST['wooweb_cpe_registro']));
  }

  if (!empty($_POST['wooweb_cpe_razonsocial'])) {
    update_post_meta($order_id, 'miratio_razonsocial', wc_clean($_POST['wooweb_cpe_razonsocial']));
  }

  if ($tipo_doc_cliente == 6) {
    if (!empty($_POST['wooweb_cpe_domiciliofiscal'])) {
      update_post_meta($order_id, 'miratio_domiciliofiscal', wc_clean($_POST['wooweb_cpe_domiciliofiscal']));
    }

    if (!empty($_POST['wooweb_cpe_ubigeo'])) {
      update_post_meta($order_id, 'miratio_ubigeo', wc_clean($_POST['wooweb_cpe_ubigeo']));
    }
  }
}
//-------------------------------------------------------------------------------------------------------
add_action('add_meta_boxes', 'miratio_add_meta_boxes_woocommerce');
if (!function_exists('miratio_add_meta_boxes_woocommerce')) {
  function miratio_add_meta_boxes_woocommerce()
  {
    add_meta_box('miratio_box_cpe_peru', __('Mi Comprobante', CPEP_TEXTDOMAIN), 'miratio_box_cpe_peru_markup', 'shop_order', 'side', 'core');
  }
}
//-------------------------------------------------------------------------------------------------------
if (!function_exists('miratio_box_cpe_peru_markup')) {
  function miratio_box_cpe_peru_markup($order)
  {
    //miratio_generar_comprobante($order); 

    $order_id = $order->ID;
    $nroCPE = miratio_get_nro_cpe($order_id);

    $bGenerar = 0;
    if (array_key_exists('gen', $_GET)) {
      $bGenerar = $_GET['gen'];
    }

    //Verificar si se genera la orden o no
    if (($bGenerar == 1) && (!$nroCPE)) {
      $message = miratio_generar_comprobante($order_id);
      if ($message['success'] == true) {
        echo "Generado correctamente<br/><br/>";
      } else {
        echo "Hubo un problema, porfavor revisa lo siguiente, " . $message['message'] . '.<br/>Si desconoces el error, consulta al soporte de tu sistema de facturación electronica<br/>';
      }
    } else if (($bGenerar == 1) && ($nroCPE != '')) {
      echo '<center>Ya Existe un comprobante generado para este pedido</center><br/>';
    }

    //obtener datos de factura
    $nroCPE = miratio_get_nro_cpe($order_id); //volver a obtener el nro

    if (!$nroCPE) {
      echo '<center>No existe comprobante actualmente, <b>¿Desea generarlo?</b></center><br/>
                <a href="' . $_SERVER['REQUEST_URI'] . '&gen=1" type="button" id="generarCPE" data-orderid="' . $order_id . '" class="add_note button center button-primary" style="width: 100%; text-align: center;">Generar</a>
                <br/><br/>';
    } else {
      $CPExternalID = miratio_get_cpe_external_id($order_id);
      $URL = miratio_get_api_url();

      echo "<b style='font-size: 18px;'>N° CPE: " . $nroCPE . '</b><br/><br/>';

      miratio_get_cpe_urls($order_id, false);
    }
  }
}
$options = get_option(MIRATIO_SETTINGS);
//-------------------------------------------------------------------------------------------------------
//Verified order payment complete
if (miratio_check_option_array_clean($options, 'miratio_enabled_status_payment') == 1) {
  add_action('woocommerce_payment_complete', 'miratio_payment_complete');
  function miratio_payment_complete($order_id)
  {

    $nroCPE = miratio_get_nro_cpe($order_id);

    if (!$nroCPE || $nroCPE == '') {
      miratio_generar_comprobante($order_id);
    }
  }
}
//-------------------------------------------------------------------------------------------------------
//Verified order completed
if (miratio_check_option_array_clean($options, 'miratio_enabled_status_completed') == 1) {
  add_action('woocommerce_order_status_completed', 'miratio_order_status_completed', 10, 1);
  function miratio_order_status_completed($order_id)
  {

    $nroCPE = miratio_get_nro_cpe($order_id);

    if (!$nroCPE || $nroCPE == '') {
      miratio_generar_comprobante($order_id);
    }
  }
}
//-------------------------------------------------------------------------------------------------------
//verified order is processing
if (miratio_check_option_array_clean($options, 'miratio_enabled_status_processing') == 1) {
  add_action('woocommerce_order_status_processing', 'miratio_order_status_processing', 10, 1);
  function miratio_order_status_processing($order_id)
  {

    $nroCPE = miratio_get_nro_cpe($order_id);

    if (!$nroCPE || $nroCPE == '') {
      miratio_generar_comprobante($order_id);
    }
  }
}
//-------------------------------------------------------------------------------------------------------
