=== MIRATIO - Facturación electrónica Perú ===

Contributors: Nextcoders
Donate link: https://miratio.net/
Tags: WP, facturacion, factura electronica, WooCommerce, CPE, Peru, wordpress, woocommerce
Requires at least: 4.7
Tested up to: 6.0
Stable tag: 4.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ahora puedes emitir comprobantes electrónicos como Boletas y Facturas automáticamente con el plugin de MIRATIO para WooCommerce.

== Description ==

Emite Boletas y Facturas directamente desde tu tienda virtual con WooCommerce.

== Installing ==

Este documento contiene instruciones acerca de como instalar este plugin.

==================================================
REQUISITOS
==================================================
WordPress 5.4 o superior.
WooCommerce 4.0.1 o superior.

==================================================
INSTALACIÓN 
==================================================

1. Ingresar al dashboard de tu sitio web en WordPress.
2. Dirigirse a la opción de PlugIns / Agregar nuevo.
3. Elegir el ZIP del plugin.
4. Instalar.
5. Activar.

==================================================
CONFIGURACIÓN
==================================================
Encuentra la configuración del Plugin en Ajustes - MIRATIO.

Para empezar a configurar MIRATIO en tu tienda virtual debes habilitar el plugin en el checkout.

De manera opcional puedes Agregar el costo de envío como un item en el detalle de tu CPE.

==================================================
ESTADO DEL PEDIDO
==================================================
En esta sección eliges en qué momento se emitirá el comprobante de la venta.

-Procesando el pedido
-Pedido completado
-Cuando el pago es confirmado (Recomendado).

==================================================
CONEXIÓN
==================================================
Aquí conectas tu cuenta en MIRATIO con tu tienda virtual.

URL: https://miratio.app/sys/api/procesar_venta
TOKEN: (MIRATIO.APP -> CONFIGURACIÓN DE EMPRESA -> API_REST -> TOKEN)

También puedes elegir si enviarle la factura al cliente después de su compra por email.

==================================================
CONFIGURACIÓN DE TU CUENTA
==================================================
En esta sección indicas el ID de la surcursal y el ID de tu usuaio. Ambos ID's los puedes encontrar en la sección de Administración.

-ID de usuario (Gestión de usuarios).
-ID de sucursal (Listar Sucursales).

==================================================
OTROS DATOS
==================================================
Moneda: PEN
Tipo de cambio: (Opcional si en moneda indicas USD, es decir, dólares).
Unidad de Medida: NIU para productos o ZZ para servicios


== Frequently Asked Questions ==

= ¿Cómo emitir comprobantes electrónicos? =

Debes tener una cuenta en producción en https://miratio.app.

= ¿Dónde encuentro la información para configurar el plugin? =

Toda la información que necesitas está dentro de tu cuenta en https://miratio.app.

== Screenshots ==

1. Instalación - 1.png 
2. Configuración - 2.png 
3. Configuración 2 - 3.png 
4. Vista en producción (Checkout) - 4.png

== Changelog ==

= 2.6 =
Ahora es posible hacer consulta y sincronización de más de 500 productos. Sincroniza tu stock y consulta en tiempo real la cantidad de unidades disponibles por producto desde miratio.app.

= 2.5 =
Ya procesamos productos gravados y exonerados. Configura los impuestos en tus productos como STANDARD y el plugin procesará la información en el documento.
También ya procesamos ventas con los documentos Pasaporte y Carnet de Extranjería.
Mejoras menores de rendimiento y seguridad.

= 2.4 =
Corrección de bug en sincronización de stock.

= 2.3 =
Optimización de la traducción en la configuración del plugin.

= 2.2 =
Corrección de bug para documentos Pasaporte y Carnét de Extranjería.

= 2.1 =
Corrección de bug en cantidad de caracteres la insertar un número de documento.

= 2.0 =
Nueva versión! Ahora puedes sincronizar tu stock entre Woocommerce y MIRATIO, la sincronización es automática pero pusimos un botón para que la ejecutes cuando tu quieras. 

= 1.5 =
Corrección de errores y bugs. 

= 1.2 =
Corrección de bugs en la página de checkout.

= 1.0 =
Lanzamiento. 



== Upgrade Notice ==
Ahora es posible hacer consulta y sincronización de más de 500 productos. Sincroniza tu stock y consulta en tiempo real la cantidad de unidades disponibles por producto desde miratio.app.
Seguimos trabajando en mejorar nuestra plataforma
Muy pronto lanzaremos la versión 2.7 con más novedades.

`<?php code(); // goes in backticks ?>`