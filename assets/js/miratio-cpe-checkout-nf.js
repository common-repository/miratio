//wooweb_cpe_tipo_documento

jQuery(document).ready(function ($) {

  $(".ubigeo").css("display", "none");

  $('form.checkout').on('change', 'select#wooweb_cpe_tipo_documento', function (e) {
    //e.preventDefault();
    //var a = $(this).val();

    $('#wooweb_cpe_registro').val('');
    $('#wooweb_cpe_razonsocial').val('');
    $('#wooweb_cpe_domiciliofiscal').val('');
    $('#wooweb_cpe_ubigeo').val('');

    if ($("select#wooweb_cpe_tipo_documento option[value='6']").attr('selected')) {
      $(".wooweb-company").css("display", "block");
    } else {
      $(".wooweb-company").css("display", "none");
    }

    //nonce = jQuery(this).attr("data-nonce");
    tipo_doc_cliente = $("select#wooweb_cpe_tipo_documento option:selected").val();
    nro_doc_cliente = $('#wooweb_cpe_registro').val();
    //tipo_doc_cliente = '6';
    //nro_doc_cliente = '20600980794';

    //alert(tipo_doc_cliente);
    // Update checkout event
    //$('body').trigger('update_checkout');
  });
  $("#wooweb_cpe_registro").on("keypress", function (event) {
    tipo_doc_cliente = $("select#wooweb_cpe_tipo_documento option:selected").val();
    nro_doc_cliente = $('#wooweb_cpe_registro').val();
    if (tipo_doc_cliente == 1) { //DNI
      if (!(event.which > 47 && event.which < 58) || $(this).val().length == 8) {
        return false;
      }
    } else if (tipo_doc_cliente == 6) { //RUC
      if (!(event.which > 47 && event.which < 58) || $(this).val().length == 11) {
        return false;
      }
    } else if (tipo_doc_cliente == 4) { //CARNE EXTRANJERIA
      if (!((event.charCode >= 48 && event.charCode <= 57) || (event.charCode >= 65 && event.charCode <= 90) || (event.charCode >= 97 && event.charCode <= 122) ) || $(this).val().length == 12) {
         return false;
      }
    } else if (tipo_doc_cliente == 7) { //PASAPORTE
      if (!((event.charCode >= 48 && event.charCode <= 57) || (event.charCode >= 65 && event.charCode <= 90) || (event.charCode >= 97 && event.charCode <= 122) ) || $(this).val().length == 12) {
        return false;
      }
    }
  });
  
  $("#wooweb_cpe_registro").on("input", function () {
    tipo_doc_cliente = $("select#wooweb_cpe_tipo_documento option:selected").val();
    nro_doc_cliente = $('#wooweb_cpe_registro').val();

    nombreDocumento = '';
    if (tipo_doc_cliente == 1) {
      nombreDocumento = 'DNI';
    } else if (tipo_doc_cliente == 6) {
      nombreDocumento = 'RUC';
    }
    //

    if ((tipo_doc_cliente == 1 && nro_doc_cliente.length == 8) || (tipo_doc_cliente == 6 && nro_doc_cliente.length == 11)) {
      //e.preventDefault();
      jQuery.ajax({
        type: "post",
        url: ajax_miratio_cpe_peru.ajax_url,
        beforeSend: function (qXHR, settings) {
          $('#wooweb_cpe_razonsocial').val('Obteniendo datos...');
          $('#wooweb_cpe_domiciliofiscal').val('Obteniendo datos...');
        },
        data: {
          action: "miratio_cpe_peru_getCliente",
          tipo_doc_cliente: tipo_doc_cliente,
          nro_doc_cliente: nro_doc_cliente
        },
        success: function (response) {
          if (response.respuesta == 'ok') {
            if (tipo_doc_cliente == '6') {
              $('#wooweb_cpe_razonsocial').val(response.data.razon_social);
              $('#wooweb_cpe_domiciliofiscal').val(response.data.direccion);
              $('#wooweb_cpe_ubigeo').val(response.data.codigo_ubigeo);
            } else if (tipo_doc_cliente == '1') {
              $('#wooweb_cpe_razonsocial').val(response.data.nombre);
              $('#wooweb_cpe_domiciliofiscal').val('');
              $('#wooweb_cpe_ubigeo').val('');
            }
          } else {
            $('#wooweb_cpe_razonsocial').val('No se encontró el '.nombreDocumento);
            $('#wooweb_cpe_domiciliofiscal').val('No se encontró el '.nombreDocumento);
          }
        }
      });

    }

  });
});