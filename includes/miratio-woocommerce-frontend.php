<?php
//-------------------------------------------------------------------------------------------------------
/**ADD FRONTEND SCRIPT AND CSS **/
add_action( 'wp_enqueue_scripts', 'miratio_styles' );

function miratio_styles() {
  if (is_checkout()) {

    wp_enqueue_style(
      'miratio',
      MIRATIO_PLUGIN_URL . '/assets/css/miratio.css',
      array(),
      '1.1'
    );

    if (CPEP_PLATAFORMA == 'miratio.app') {
      wp_register_script('miratio-js', MIRATIO_PLUGIN_URL.'/assets/js/miratio-cpe-checkout-nf.js', array( 'jquery'), '1.0', true );
    }

    wp_enqueue_script( 'miratio-js' );

    wp_localize_script( 'miratio-js', 'ajax_miratio_cpe_peru', array(
    'ajax_url' => admin_url( 'admin-ajax.php' )
    ));

  }
}
//--- START IF
$options = get_option( MIRATIO_SETTINGS );
if (miratio_check_option_array_clean($options,'miratio_enabled_checkout')==1) {
//-------------------------------------------------------------------------------------------------------
add_action('woocommerce_checkout_process', 'miratio_my_custom_checkout_field_process');
function miratio_my_custom_checkout_field_process() {
    // Check if set, if its not set add an error.

    if ( ! $_POST['wooweb_cpe_tipo_documento'] || $_POST['wooweb_cpe_tipo_documento'] == 'blank' ) {
      wc_add_notice( __( 'Selecciona el <b>tipo de documento</b>', CPEP_TEXTDOMAIN ), 'error' );
    }

    $tipo_doc_cliente = $_POST['wooweb_cpe_tipo_documento'];

    if ( ! $_POST['wooweb_cpe_registro'] ) {
      wc_add_notice( __( 'Ingrese en <b>nro</b> de registro', CPEP_TEXTDOMAIN ), 'error' );
    }

    if ($tipo_doc_cliente == 6)  {
      if ( !preg_match( '/\d{11}/', $_POST['wooweb_cpe_registro'] )  ){
        wc_add_notice( __( 'Número de RUC invalido', CPEP_TEXTDOMAIN ), 'error' );
      }
      if ( ! $_POST['wooweb_cpe_razonsocial'] ) {
        wc_add_notice( __( 'Ingrese la <b>razón social</b>', CPEP_TEXTDOMAIN ), 'error' );
      }

      if ( ! $_POST['wooweb_cpe_domiciliofiscal'] ) {
        wc_add_notice( __( 'Ingrese el <b>domicilio fiscal</b> de la empresa', CPEP_TEXTDOMAIN ), 'error' );
      }
    } else if ($tipo_doc_cliente != 6) {
      if ($tipo_doc_cliente==1){
        if ( !preg_match( '/\d{8}/', $_POST['wooweb_cpe_registro'] )  ){
          wc_add_notice( __( 'Número de DNI invalido', CPEP_TEXTDOMAIN ), 'error' );
        }
      }
      if ($tipo_doc_cliente==7 ){
        if ( !preg_match( '/[a-zA-z0-9]{5,12}/', $_POST['wooweb_cpe_registro'] )  ){
          wc_add_notice( __( 'Número de Pasaporte invalido', CPEP_TEXTDOMAIN ), 'error' );
        }
      }
      if ($tipo_doc_cliente==4 ){
        if ( !preg_match( '/[a-zA-z0-9]{5,12}/', $_POST['wooweb_cpe_registro'] )  ){
          wc_add_notice( __( 'Número de Carnet de Extranjeria invalido', CPEP_TEXTDOMAIN ), 'error' );
        }
      }
      if ($tipo_doc_cliente==0 ){
        if ( !preg_match( '/[a-zA-z0-9]{5,12}/', $_POST['wooweb_cpe_registro'] )  ){
          wc_add_notice( __( 'Número de documento no domiciliado en Perú', CPEP_TEXTDOMAIN ), 'error' );
        }
      }
      if ( ! $_POST['wooweb_cpe_razonsocial'] ) {
        wc_add_notice( __( 'Ingrese la <b>el Nombre</b>', CPEP_TEXTDOMAIN ), 'error' );
      }
    }

}
//-------------------------------------------------------------------------------------------------------
//Agregar campos personalizados en el checkout de WooCommerce
add_action('woocommerce_before_order_notes', 'wps_add_select_checkout_field');
function wps_add_select_checkout_field( $checkout ) {
	echo '<h3>'.__('Comprobante Electrónico', CPEP_TEXTDOMAIN).'</h3>';
	woocommerce_form_field( 'wooweb_cpe_tipo_documento', array(
	    'type'          => 'select',
	    'class'         => array( 'wps-drop form-row form-row-first' ),
	    'label'         => __( 'Documento' ),
	    'options'       => array(
	    	'blank'		=> __( 'Seleccione su documento', CPEP_TEXTDOMAIN ),
	        '1'	=> __( 'DNI', CPEP_TEXTDOMAIN ),
	        '6'	=> __( 'RUC', CPEP_TEXTDOMAIN ),
          '4' 	=> __( 'Carnet de Extranjería', CPEP_TEXTDOMAIN ),
          '7' 	=> __( 'Pasaporte', CPEP_TEXTDOMAIN ),
          '0' 	=> __( 'No domiciliado en Perú', CPEP_TEXTDOMAIN )
	    )
  ), $checkout->get_value( 'wooweb_cpe_tipo_documento' ));

  woocommerce_form_field('wooweb_cpe_registro', array(
    'type' => 'text',
    'class' => array(
      'form-row form-row-last'
    ) ,
    'label' => __('N°') ,
    'placeholder' => __('Ingrese el N°', CPEP_TEXTDOMAIN ) ,
    'required' => true,
  ) , $checkout->get_value('wooweb_cpe_registro'));

  woocommerce_form_field('wooweb_cpe_razonsocial', array(
    'type' => 'text',
    'class' => array(
      'form-row form-row-wide'
    ) ,
    'label' => __('Nombre', CPEP_TEXTDOMAIN ) ,
    'placeholder' => __('Nombre o Razon social', CPEP_TEXTDOMAIN ) ,
    'required' => true,
  ) , $checkout->get_value('wooweb_cpe_razonsocial'));

  woocommerce_form_field('wooweb_cpe_domiciliofiscal', array(
    'type' => 'text',
    'class' => array(
      'form-row form-row-wide wooweb-company'
    ) ,
    'label' => __('Domicilio fiscal', CPEP_TEXTDOMAIN ) ,
    'placeholder' => __('Domicilio fiscal de la empresa', CPEP_TEXTDOMAIN ) ,
    'required' => true,
  ) , $checkout->get_value('wooweb_cpe_domiciliofiscal'));

  woocommerce_form_field('wooweb_cpe_ubigeo', array(
    'type' => 'text',
    'class' => array(
      'form-row form-row ubigeo'
    ) ,
    'label' => __('Ubigeo', CPEP_TEXTDOMAIN ) ,
    'placeholder' => __('Ingrese el ubigeo', CPEP_TEXTDOMAIN ) ,
    'required' => true,
  ) , $checkout->get_value('wooweb_cpe_ubigeo'));

}
//-------------------------------------------------------------------------------------------------------
//agregar campos a pedido
add_action( 'woocommerce_checkout_update_order_meta', 'miratio_checkout_update_order_meta' );
function miratio_checkout_update_order_meta( $order_id ) {

  $tipo_doc_cliente = $_POST['wooweb_cpe_tipo_documento'];

    if ( ! empty( $_POST['wooweb_cpe_tipo_documento'] ) ) {
        update_post_meta( $order_id, 'miratio_tipo_documento', sanitize_text_field( $_POST['wooweb_cpe_tipo_documento'] ) );
    }

    if ( ! empty( $_POST['wooweb_cpe_registro'] ) ) {
        update_post_meta( $order_id, 'miratio_registro', sanitize_text_field( $_POST['wooweb_cpe_registro'] ) );
    }

    if ( ! empty( $_POST['wooweb_cpe_razonsocial'] ) ) {
        update_post_meta( $order_id, 'miratio_razonsocial', sanitize_text_field( $_POST['wooweb_cpe_razonsocial'] ) );
    }

    if ($tipo_doc_cliente == 6)  {
      if ( ! empty( $_POST['wooweb_cpe_domiciliofiscal'] ) ) {
          update_post_meta( $order_id, 'miratio_domiciliofiscal', sanitize_text_field( $_POST['wooweb_cpe_domiciliofiscal'] ) );
      }

      if ( ! empty( $_POST['wooweb_cpe_ubigeo'] ) ) {
          update_post_meta( $order_id, 'miratio_ubigeo', sanitize_text_field( $_POST['wooweb_cpe_ubigeo'] ) );
      }
    }
}
//-------------------------------------------------------------------------------------------------------
} //--- END IF

//-------------------------------------------------------------------------------------------------------
//Display custom fields about the Sunat invoice in the order thank you page
add_filter( 'woocommerce_order_details_after_customer_details', 'miratio_add_delivery_date_to_order_received_page', 10 , 1 );
function miratio_add_delivery_date_to_order_received_page ( $order ) {

	if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
      $order_id = $order->get_id();
      $order_status = $order->get_status();
  } else {
      $order_id = $order->id;
      $order_status = $order->status;
  }

  $cpeTypeId = miratio_get_cpe_type_id($order_id);

  if ($cpeTypeId!='') {

    $cpeName = miratio_tipo_doc_cliente($cpeTypeId);

    $nroCPE = miratio_get_nro_cpe($order_id);

    echo '<h2 class="woocommerce-column__title">'.__('Comprobante SUNAT',CPEP_TEXTDOMAIN).'</h2>';
    echo '<address><strong>'.__('Tipo de Documento',CPEP_TEXTDOMAIN).':</strong> ' . $cpeName . '<br/>';
    echo '<strong>'.__('N. ',CPEP_TEXTDOMAIN).$cpeName.':</strong> ' . get_post_meta( $order_id, 'miratio_registro', true ) . '<br/>';
    echo '<strong>'.__('Nombre',CPEP_TEXTDOMAIN).':</strong> ' . get_post_meta( $order_id, 'miratio_razonsocial', true ) . '<br/>';

    if ($cpeTypeId == 6) { //Invoice
      echo '<strong>'.__('Domicilio Fiscal',CPEP_TEXTDOMAIN).':</strong> ' . get_post_meta( $order_id, 'miratio_domiciliofiscal', true ) . '<br/>';
      echo '<strong>'.__('UBIGEO',CPEP_TEXTDOMAIN).':</strong> ' . get_post_meta( $order_id, 'miratio_ubigeo', true ) . '<br/><br/>';
    }

    if (!$nroCPE || $nroCPE == '') {
        if (!$order->is_paid() || $order_status != 'completed' || $order_status != 'processing') {
          echo __("Su orden aún esta pendiente, se emitirá el comprobante una vez se pague o complete el pedido",CPEP_TEXTDOMAIN);
        }
    } else {
        //imprime los botones para la descarga de comprobantes
        miratio_get_cpe_urls($order_id, false);
    }

    echo "</address>";

  }

}
//-------------------------------------------------------------------------------------------------------
add_filter('woocommerce_email_after_order_table', 'miratio_email_order_custom_fields');
function miratio_email_order_custom_fields ( $order  ) {

  if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
      $order_id = $order->get_id();
      $order_status = $order->get_status();
  } else {
      $order_id = $order->id;
      $order_status = $order->status;
  }

  $cpeTypeId = miratio_get_cpe_type_id($order_id);

  if ($cpeTypeId!='') {

    $cpeName = miratio_tipo_doc_cliente($cpeTypeId);

    //echo $order->is_paid();
    $nroCPE = miratio_get_nro_cpe($order_id);

      //echo '<section class="woocommerce-customer-details">';
    echo '<h2 class="woocommerce-column__title">'.__('Comprobante SUNAT',CPEP_TEXTDOMAIN).'</h2>';
    echo '<address style="padding:12px;color:#636363;border:1px solid #e5e5e5"><strong>'.__('Tipo de Documento',CPEP_TEXTDOMAIN).':</strong> ' . $cpeName . '<br/>';
    echo '<strong>'.__('N. ',CPEP_TEXTDOMAIN).$cpeName.':</strong> ' . get_post_meta( $order_id, 'miratio_registro', true ) . '<br/>';
    echo '<strong>'.__('Nombre',CPEP_TEXTDOMAIN).':</strong> ' . get_post_meta( $order_id, 'miratio_razonsocial', true ) . '<br/>';

    if ($cpeTypeId == 6) {
      echo '<strong>'.__('Domicilio Fiscal',CPEP_TEXTDOMAIN).':</strong> ' . get_post_meta( $order_id, 'miratio_domiciliofiscal', true ) . '<br/>';
      echo '<strong>'.__('UBIGEO',CPEP_TEXTDOMAIN).':</strong> ' . get_post_meta( $order_id, 'miratio_ubigeo', true ) . '<br/><br/>';
    }

    if (!$nroCPE || $nroCPE == '') {
        if (!$order->is_paid() || $order_status != '' || $order_status != 'processing') {
          echo __("Su orden aún esta pendiente, se emitirá el comprobante una vez se pague o complete el pedido",CPEP_TEXTDOMAIN);
        }
    } else {
        //imprime los botones para la descarga de comprobantes
       echo miratio_get_cpe_urls($order_id, false);
    }

    echo "</address><br/>";

  }

}
//-------------------------------------------------------------------------------------------------------

?>
