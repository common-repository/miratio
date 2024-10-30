<?php
add_action('wp_ajax_nopriv_miratio_cpe_peru_getCliente', 'miratio_get_cliente');
add_action('wp_ajax_miratio_cpe_peru_getCliente', 'miratio_get_cliente');

add_action('wp_miratio_update_products', 'miratio_update_products_stock');
add_filter('cron_schedules', 'isa_add_every_three_minutes');
function isa_add_every_three_minutes($schedules)
{
  $schedules['every_three_minutes'] = array(
    'interval'  => 180,
    'display'   => __('Every 3 Minutes', 'textdomain')
  );
  return $schedules;
}
// The activation hook
if (!wp_next_scheduled('wp_miratio_update_products')) {
  wp_schedule_single_event(time() + 86400, 'wp_miratio_update_products');
}


function miratio_get_cliente()
{
  $id_tipo_doc =  intval($_POST['tipo_doc_cliente']);
  $nro_doc_cliente =  $_POST['nro_doc_cliente'];
  $options = get_option(MIRATIO_SETTINGS);

  $TOKEN = $options['miratio_tokem'];
  $URL = $options['miratio_url'];

  $tipo_doc = 'dni';
  if ($id_tipo_doc == 6) {
    $tipo_doc = 'ruc';
  } else if ($id_tipo_doc == 1) {
    $tipo_doc = 'dni';
  } else if ($id_tipo_doc == 4) {
      $tipo_doc = 'carne extranjeria';
    }  else if ($id_tipo_doc == 7) {
        $tipo_doc = 'pasaporte';
  }

  //$data = array('tipo' => $id_tipo_doc, 'nro' => $nroregistro);
  $data['tipo_doc'] = $tipo_doc;
  $data['num_doc'] = $nro_doc_cliente;
  $data['token'] = $TOKEN;

  //////HTTP API////
  $request = wp_remote_post('https://miratio.app/sys/api/buscar_data_cliente', array(
    'headers' => array(
      'Authorization' => 'Bearer ' . $TOKEN,
      'Content-Type' => 'application/json',
      'cache-control: no-cache'
    ),
    'method' => 'POST',
    'sslverify' => false,
    'body' => json_encode($data),
    'httpversion' => '1.1',
    'blocking'    => true,
    'redirection' => 5,
    'timeout' => 45,
  ));

  $response = wp_remote_retrieve_body($request);
  // Check for error
  if (is_wp_error($response)) {
    echo "Error :" . $response->get_error_message();
  } else {
    return wp_send_json(json_decode($response, true));
  }

  die();
}

//-------------------------------------------------------------------------------------------------------
if (CPEP_PLATAFORMA == 'miratio.app') {

  function miratio_generar_comprobante($order_id)
  {
    global $woocommerce;
    $order = wc_get_order($order_id);
    //$order = wc_get_order( $porder->get_id());

    $responsetmp = array('success' => 'false', 'message' => 'La factura tiene un monto cero');
    if ($order->get_total() <= 0) {
      return $responsetmp;
    }

    //opciones de conexión APIREST
    $options = get_option(MIRATIO_SETTINGS);
    $conf_enable_shipping = $options['miratio_enabled_shipping'];
    $URL = $options['miratio_url'];
    $TOKEN = $options['miratio_tokem'];
    $CURRENCY = $options['miratio_currency'];
    $CAMBIO = round($options['miratio_tipo_cambio'], 2);
    if ($CAMBIO == 0) {
      $CAMBIO = 3.35;
    }
    $UND = isset($options['miratio_und']) ? ($options['miratio_und'] == 'NIU' ? 'NIU' : 'ZZ') : 'NIU';
    $IGV = 18;
    $enviarCliente = false;
    $tipo_envio = 'produccion';
    if (miratio_check_option_array_clean($options, 'miratio_nf_clientedirecto') == 1) $enviarCliente = true;
    if (miratio_check_option_array_clean($options, 'miratio_ambiente_pruebas') == 1) $tipo_envio = 'prueba';
    //***************************** */

    $data["contribuyente"] = array(
      "token_contribuyente"   => $options['miratio_tokem'], //Token del contribuyente
      //"id_contribuyente" => 1, //tu ID
      "id_usuario_vendedor"   => $options['miratio_vendedor'], //Debes ingresar el ID de uno de tus vendedores
      "tipo_proceso"       => $tipo_envio, //El ambiente al que se enviará, puede ser: {prueba, produccion}
      "tipo_envio"       => "inmediato" //aquí puedes definir si se enviará de inmediato a sunat
    );

    //datos del cliente
    $tipo_doc_cliente = get_post_meta($order->get_id(), 'miratio_tipo_documento', true);
    if ($tipo_doc_cliente == 7 || $tipo_doc_cliente == 4) {
      $tipo_doc_cliente = 0;
    }
    // print_r("doc ". $tipo_doc_cliente);
    $data["cliente"] = array(
      "tipo_docidentidad"   => $tipo_doc_cliente, //{0: SINDOC, 1: DNI, 6: RUC}
      "numerodocumento"    => get_post_meta($order->get_id(), 'miratio_registro', true), //Es opcional solo cuando tipo_docidentidad es 0, caso contrario se debe ingresar el número de ruc
      "nombre"         => get_post_meta($order->get_id(), 'miratio_razonsocial', true), //Es opcional solo cuando tipo_docidentidad es 1, caso contrario es obligatorio ingresar aquí la razón social
      "email"         => $order->get_billing_email(), //opcional: (si tiene correo se enviará automáticamente el email)
      "direccion"       => get_post_meta($order->get_id(), 'mriratio_domiciliofiscal', true), //opcional: 
      "ubigeo"         => get_post_meta($order->get_id(), 'miratio_ubigeo', true),
      "sexo"           => "", //opcional: masculino
      "fecha_nac"       => "", //opcional: 
      "celular"         => $order->billing_phone //opcional
    );

    //print_r($data["cliente"]);
    $tipo_cpe = '03';
    if ($tipo_doc_cliente == '6') {
      $tipo_cpe = '01';
    }

    //Cabecera del Comprobante
    $data["cabecera_comprobante"] = array(
      "tipo_documento"     => $tipo_cpe,  //{"01": FACTURA, "03": BOLETA}
      "moneda"         => $options['miratio_currency'],  //{"USD", "PEN"}
      "idsucursal"       => $options['miratio_sucursal'],  //{ID DE SUCURSAL}
      "id_condicionpago"     => "",  //condicionpago_comprobante
      "fecha_comprobante"   => date('d/m/Y', current_time('timestamp', 0)),  //fecha_comprobante
      "nro_placa"       => "",  //nro_placa_vehiculo
      "nro_orden"       => "TV_" . $order_id,  //nro_orden
      "guia_remision"     => "",  //guia_remision_manual
      "descuento_porcentaje"   => 0,  //10 = 10%, 45 = 45% (máximo 2 decimales)
      "descuento_monto"     => $order->get_total_discount(), //Woocomerce solo guarda el monto, no el porcentaje.
      "observacion"       => "",  //observacion_documento
    );

    $detalle = array();
    foreach ($order->get_items() as $product_key => $item) {
      $product = $item->get_product();
      $product_unit_price = round(($item->get_total() + $item->get_total_tax()) / $item->get_quantity(), 2); //valor sin descuento

      $detalle[] = array(
        "idproducto"       => 0,  //"idarticulo":"11604",
        "codigo"        => $product->get_sku(),//$item->get_id(),
        "afecto_icbper"     => "no",  //"afecto_icbper":"no",
      
        "id_tipoafectacionigv"   =>  ($product->is_taxable() )? 10:20,  //"id_tipoafectacionigv":"10",
        "descripcion"       => $item->get_name(),  //"descripcion":"Zapatos",
        "idunidadmedida"     => $UND,  //"idunidadmedida":"2", //NIU - ZZ
        "precio_venta"       => $product_unit_price,  //"precio":"1703.5",
        "cantidad"         => $item->get_quantity(),  //"cantidad":"1"
      );
    }

    //datos del envío si esta activado ---------------------------------------------------------
    $order_shipping_total = round(($order->get_shipping_total() + $order->get_shipping_tax()), 2);
    if (!($order_shipping_total > 0)) {
      $conf_enable_shipping = 0; //desactivar la linea de pedido en la factura si este es cero
    }

    //Agregar el envío a los items en caso este incluido como una linea de producto
    if ($conf_enable_shipping == 1) {
      $detalle[] = array(
        "idproducto"       => 0,  //"idarticulo":"11604",
        "codigo"        => 'ENVIO',
        "afecto_icbper"     => "no",  //"afecto_icbper":"no",
        "id_tipoafectacionigv"   => 10,  //"id_tipoafectacionigv":"10",
        "descripcion"       => 'ENVIO - ' . $order->get_shipping_method(),  //"descripcion":"Zapatos",
        "idunidadmedida"     => 'ZZ',  //"idunidadmedida":"2", //NIU - ZZ
        "precio_venta"       => $order_shipping_total,  //"precio":"1703.5",
        "cantidad"         => 1,  //"cantidad":"1"
      );
    }

    $data["detalle"] = $detalle;

    $numero_doc = get_post_meta($order->get_id(), 'miratio_numero_doc', true);
    if (!$numero_doc) {

      $resp_api_ws = miratio_send_sunat_nf($data);

      $resp_api = json_decode($resp_api_ws, true);
      // var_dump($resp_api);
      if (empty($resp_api)) {
        $resp['success'] = false;
        $resp['message'] = $resp_api['mensaje'];
        return $resp;
      }

      if ($resp_api['respuesta'] == 'error') {

        $resp['success'] = false;
        $resp['message'] = $resp_api['mensaje'];
        return $resp;
      }
      //$resp_api['documento'] = {"respuesta":"ok","id_contribuyente":"1","id_tipodoc_electronico":"03","serie_comprobante":"B001","numero_comprobante":405,"tipo_envio_sunat":"prueba"}

      if (!isset($resp_api['documento']) || empty($resp_api['documento'])) {
        $resp['success'] = false;
        $resp['message'] = 'No se encuentran los datos del comprobante';
        return $resp;
      }

      $documento = $resp_api['documento'];
      $id_contribuyente = $documento['id_contribuyente'];
      $tipo_cpe = $documento['id_tipodoc_electronico'];
      $serie = $documento['serie_comprobante'];
      $numero = $documento['numero_comprobante'];
      $ambiente_sistema = $documento['tipo_envio_sunat']; //prueba, produccion

      update_post_meta($order->get_id(), 'miratio_id_contribuyente', sanitize_text_field($id_contribuyente));
      update_post_meta($order->get_id(), 'miratio_tipo_cpe', sanitize_text_field($tipo_cpe));
      update_post_meta($order->get_id(), 'miratio_serie_doc', sanitize_text_field($serie));
      update_post_meta($order->get_id(), 'miratio_numero_doc', sanitize_text_field($numero));
      update_post_meta($order->get_id(), 'miratio_ambiente_sistema', sanitize_text_field($ambiente_sistema));
    }

    $resp['success'] = true;
    return $resp;
  }
}
//-------------------------------------------------------------------------------------------------------
function miratio_send_sunat($data, $rutaApi)
{
  $curl = curl_init();

  $options = get_option(MIRATIO_SETTINGS);
  $URL = $options['miratio_url'];
  $TOKEN = $options['miratio_tokem'];

  //////HTTP API////
  $request = wp_remote_post($URL . '/' . $rutaApi, array(
    'headers' => array(
      'Authorization' => 'Bearer ' . $TOKEN,
      'Content-Type' => 'application/json',
      'cache-control: no-cache'
    ),
    'method' => 'POST',
    'sslverify' => false,
    'body' => json_encode($data),
    'httpversion' => '1.1',
    'blocking'    => true,
    'redirection' => 5,
    'timeout' => 120,
  ));

  $response = wp_remote_retrieve_body($request);
  // Check for error
  if (is_wp_error($response)) {
    echo "Error :" . $response->get_error_message();
  } else {
    $SUNAT_respuesta = json_decode($response, true);
    return $SUNAT_respuesta;
  }
  ////// 
}
//-------------------------------------------------------------------------------------------------------
function miratio_send_sunat_nf($data)
{
  $options = get_option(MIRATIO_SETTINGS);
  $URL = $options['miratio_url'];
  $TOKEN = $options['miratio_tokem'];

  //////HTTP API////
  $request = wp_remote_post($URL, array(
    'headers' => array(
      'Authorization' => 'Bearer ' . $TOKEN,
      'Content-Type' => 'application/json',
      'cache-control: no-cache'
    ),
    'method' => 'POST',
    'sslverify' => false,
    'body' => json_encode($data),
    'httpversion' => '1.1',
    'blocking'    => true,
    'redirection' => 5,
    'timeout' => 120,
  ));

  $response = wp_remote_retrieve_body($request);
  // Check for error
  if (is_wp_error($response)) {
    echo "Error :" . $response->get_error_message();
  } else {
    return $response;
  }
}

//-------------------------------------------------------------------------------------------------------
function miratio_tipo_doc_cliente($idDoc)
{
  $documento = "";
  switch ($idDoc) {
    case '1':
      $documento = 'DNI';
      break;

    case 'A':
      $documento = 'Doc.trib.no.dom.sin.ruc';
      break;
    case '4':
      $documento = 'CE';
      break;
    case '6': //antes 4
      $documento = 'RUC';
      break;
    case '7':
      $documento = 'PASAPORTE';
      break;

    default:
      $documento = 'RUC';
      break;
  }
  return $documento;
}
//-------------------------------------------------------------------------------------------------------
function miratio_calcular_neto($mtotal, $igv)
{
  $total_neto = round($mtotal, 2) / (1 + round('0.' . $igv, 2));
  return $total_neto;
}
//-------------------------------------------------------------------------------------------------------
function miratio_get_cpe_type_id($orderId)
{
  $options = get_option(MIRATIO_SETTINGS);
  $var = get_post_meta($orderId, 'miratio_tipo_documento', true);
  return $var;
}
//-------------------------------------------------------------------------------------------------------
function miratio_get_nro_cpe($orderId)
{
  $options = get_option(MIRATIO_SETTINGS);
  $documento = "";
  $num_doc = intval(get_post_meta($orderId, 'miratio_numero_doc', true));
  if (CPEP_PLATAFORMA == 'miratio.app') {
    $serie_doc = get_post_meta($orderId, 'miratio_serie_doc', true);
    if ($num_doc > 0) {
      $documento = $serie_doc . '-' . $num_doc;
    }
  }
  return $documento;
}
//-------------------------------------------------------------------------------------------------------
function miratio_get_cpe_external_id($orderId)
{
  $options = get_option(MIRATIO_SETTINGS);
  $var = get_post_meta($orderId, 'miratio_doc_externalid', true);
  return $var;
}
//-------------------------------------------------------------------------------------------------------
function miratio_get_cpe_urls($orderId, $nocdr)
{

  if (CPEP_PLATAFORMA == 'miratio.app') {
    $options = get_option(MIRATIO_SETTINGS);
    $URL = $options['miratio_url'];
    $parse = parse_url($URL);
    $dominio = $parse['host'];

    $id_contribuyente = get_post_meta($orderId, 'miratio_id_contribuyente', true);
    $tipo_cpe = get_post_meta($orderId, 'miratio_tipo_cpe', true);
    $serie = get_post_meta($orderId, 'miratio_serie_doc', true);
    $numero = get_post_meta($orderId, 'miratio_numero_doc', true);
    $ambiente_sistema = get_post_meta($orderId, 'miratio_ambiente_sistema', true);

    $url_pdf_a4 = "https://$dominio/sys/download/downloadpdf/$id_contribuyente/$tipo_cpe/$serie/$numero/pdf/a4";
    $url_pdf_ticket = "https://$dominio/sys/download/downloadpdf/$id_contribuyente/$tipo_cpe/$serie/$numero/pdf/ticket";
    $url_xml = "https://$dominio/sys/download/downloadcpe/$id_contribuyente/$tipo_cpe/$serie/$numero/xml_cpe_zip";
    $url_cdr = "https://$dominio/sys/download/downloadcpe/$id_contribuyente/$tipo_cpe/$serie/$numero/xml_cdr_zip";

    if ($numero != '') {
      echo '<a style="text-decoration: none!important;" href="' . $url_cdr . '" target="_blank" id="descargarPDF" data-orderid="' . $orderId . '" >
            <img style="max-width: 30px; cursor: pointer; text-decoration: none!important;" src="https://imgur.com/oxxvw0T.png" /> </a> ';
      echo '<a style="text-decoration: none!important;" href="' . $url_pdf_a4 . '" target="_blank" id="descargarPDF" data-orderid="' . $orderId . '" >
            <img style="max-width: 30px; cursor: pointer; text-decoration: none!important;" src="https://imgur.com/gFZfIJR.png" /> </a> ';
      echo '<a style="text-decoration: none!important;" href="' . $url_xml . '" target="_blank" id="descargarXML" data-orderid="' . $orderId . '"> 
            <img style="max-width: 30px; cursor: pointer; text-decoration: none!important;" src="https://imgur.com/8gYGyyG.png" /> </a> ';

      if ($nocdr) {
        echo '<a style="text-decoration: none!important;" href="' . $url_cdr . '" target="_blank" id="descargarCDR" data-orderid="' . $orderId . '"> <img style="max-width: 30px; cursor: pointer;" src="https://miratio.app/sys/img/svg/xml_cdr.svg" /> </a>';
      }
    } else {
      echo "<b>Sin CPE</b>";
    }
  }
}
//-------------------------------------------------------------------------------------------------------
function miratio_get_api_url()
{
  $options = get_option(MIRATIO_SETTINGS);
  $URL = $options['miratio_url'];
  return $URL;
}

function miratio_check_option_array_clean($array, $key)
{
  if (is_array($array)) {
    $return = (array_key_exists($key, $array)) ? $array[$key] : '';
  } else {
    $return = '';
  }

  return $return;
}
//-------------------------------------------------------------------------------------------------------
function miratio_secure($action = 'woocod', $string = false)
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

function miratio_update_products_stock()
{
  $options = get_option(MIRATIO_SETTINGS);

  // VERIFICANDO CANTIDAD DE PRODUCTOS
  $URL_product = "https://miratio.app/sys_prueba/api/get_num_productos";
  $TOKEN = $options['miratio_tokem'];

  $data = array(
    "token_contribuyente" =>  $TOKEN, //Token del contribuyente
    "idsucursal"       => "7925"  //{ID DE SUCURSAL}
  );

  $request1 = wp_remote_post($URL_product, array(
    'headers' => array(
      'Authorization' => 'Bearer ' . $TOKEN,
      'Content-Type' => 'application/json',
      'cache-control: no-cache'
    ),
    'method' => 'POST',
    'sslverify' => false,
    'body' => json_encode($data),
    'httpversion' => '1.1',
  ));
  $response1 = wp_remote_retrieve_body($request1);
  $cant_products = json_decode($response1, true);
  //////////////////////////////////////////////////////



  // LISTANDO PRODUCTOS
  $start_limit = 0;
  $end_limit = $cant_products['total'];

  $vueltas=1;
  if ($end_limit >= 400 ){
    $vueltas = $end_limit/400;
    $vueltas = intval($vueltas)+1;
  }

    for ($i=1; $i <= $vueltas ; $i++) { 
      $data = array(
        "limite_inferior" => ($i-1)*400  ,
        "limite_superior" => ($i)*400,
      );
    
      $URL = "https://miratio.app/sys_prueba/api/get_productos";
      $request = wp_remote_post($URL, array(
        'headers' => array(
          'Authorization' => 'Bearer ' . $TOKEN,
          'Content-Type' => 'application/json',
          'cache-control: no-cache'
        ),
        'method' => 'POST',
        'sslverify' => false,
        'body' => json_encode($data),
        'httpversion' => '1.1',
      ));
    
      $response = wp_remote_retrieve_body($request);
      $data_productos = json_decode($response, true);
      foreach ($data_productos as $data_producto) {
        $product_reference = $data_producto['sku'];
        $producto_tienda_id = wc_get_product_id_by_sku($product_reference);
    
        if ((int)$producto_tienda_id != 0) {
          $product = wc_get_product($producto_tienda_id);
          if ($product->get_stock_quantity() != $data_producto['stock']) {
            echo  $product->get_name() . " - SKU: " . $product->get_sku(). " - NUEVO STOCK (" . $data_producto['stock'] . ")" . "<br>";
            $product->set_stock_quantity($data_producto['stock']);
            $product->save();
          }
        }
      }
      if ($i< $vueltas) { echo '<b>NO SE ENCONTRARON MÁS PRODUCTOS POR ACTUALIZAR. GRUPO ('.$i.')<b><br>'; }
    }
    
 

  
}
