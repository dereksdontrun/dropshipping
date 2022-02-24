/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

//para mostrar el botón de scroll arriba, aparecerá cuando se haga scroll abajo y desaparecerá al volver arriba
$(window).scroll(function(){    
    if ($(this).scrollTop() > 400) {
      $('#boton_scroll').fadeIn();
    } else {
      $('#boton_scroll').fadeOut();
    }
});

document.addEventListener('DOMContentLoaded', start);

function start() {
    //quitamos cosas del panel header que pone Prestashop por defecto, para que haya más espacio. 
    document.querySelector('h2.page-title').remove(); 
    document.querySelector('div.page-bar.toolbarBox').remove(); 
    document.querySelector('div.page-head').style.height = '36px';  
    
    //el panel que contiene el formulario, etc donde aparecerá el contenido lo hacemos relative y colocamos para que aparezca inicialmente bajo el panel superior, poniendo top -80px ¿?
    const panel_contenidos = document.querySelector('div#content div.row'); 
    panel_contenidos.style.position = 'relative';
    panel_contenidos.style.top = '-80px';    

    // obtenemos token del input hidden que hemos creado con id 'token_admin_modulo_'.$token_admin_modulo, para ello primero buscamos el id de un input cuyo id comienza por token_admin_modulo y al resultado le hacemos substring.  
    const id_hiddeninput = document.querySelector("input[id^='token_admin_modulo']").id;    

    //substring, desde 19 hasta final(si no se pone lenght coge el resto de la cadena)
    const token = id_hiddeninput.substring(19);
    // console.log('token = '+token);

    //vamos a añadir un panel para visualizar los pedidos, llamado panel_pedidos, más tarde es posible que añada sobre este otro como botonera    
    //generamos la tabla "vacia" para los resultados de las consultas
    //utilizamos el mismo formato de prestashop para mostrar los productos, con tabla responsiva etc.
    //div contenedor de la tabla
    const div_tabla = document.createElement('div');
    div_tabla.classList.add('table-responsive-row','clearfix');
    div_tabla.id = 'div_tabla';
    document.querySelector('div.panel-heading').insertAdjacentElement('afterend', div_tabla);

    //generamos tabla
    const tabla = document.createElement('table');
    tabla.classList.add('table');
    tabla.id = 'tabla';
    document.querySelector('#div_tabla').appendChild(tabla);

    //generamos head de tabla
    const thead = document.createElement('thead');
    thead.id = 'thead';
    thead.innerHTML = `
        <tr class="nodrag nodrop" id="tr_campos_tabla">
            <th class="row-selector text-center">
                <input class="noborder" type="checkbox" name="selecciona_todos_pedidos" id="selecciona_todos_pedidos">
            </th>            
            <th class="fixed-width-xs center">
                <span class="title_box active">ID
                    <a id="orden_id_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_id_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>            
            <th class="">
                <span class="title_box">Proveedor
                </span>
            </th>
            <th class="left">
                <span class="title_box">Referencia
                </span>
            </th>
            <th class="fixed-width-md text-right">
                <span class="title_box">EAN                 
                </span>
            </th>            
            <th class="fixed-width-sm text-center">
                <span class="title_box">PVP
                    <a id="orden_pvp_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_pvp_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-sm text-center">
                <span class="title_box"><span id="span_unidades_vendidas">Ventas</span>
                    <a id="orden_ventas_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_ventas_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-sm text-center">
                <span class="title_box"><span id="span_unidades_vendidas_marketplaces">Ventas<br>Marketplaces</span>
                    <a id="orden_ventas_marketplace_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_ventas_marketplace_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-xs text-center">
                <span class="title_box">Disponible<br>Catálogo</span>
            </th>
            <th class="fixed-width-xs text-center">
                <span class="title_box">Permite<br>Pedidos</span>
            </th>
            <th class="fixed-width-sm text-center">
                <span class="title_box">ABC
                    <a id="orden_abc_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_abc_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-xs text-center">
                <span class="title_box"><span id="span_consumo">Consumo</span>
                    <a id="orden_consumo_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_consumo_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-xs text-right">
                <span class="title_box">Stock
                    <a id="orden_stock_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_stock_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-xs text-center">
                <span class="title_box">Días Stock
                    <a id="orden_dias_stock_previsto_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_dias_stock_previsto_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="">
                <span class="title_box">Último Proveedor
                    <a id="orden_proveedor_abajo" class="filtro_orden"><i class="icon-caret-down"></i></a>
                    <a id="orden_proveedor_arriba" class="filtro_orden"><i class="icon-caret-up"></i></a>
                </span>
            </th>
            <th class="fixed-width-sm text-center">
                <span class="title_box">Unidades En Espera<br>Validar (Confirmadas)
                </span>
            </th>
            <th class="fixed-width-sm text-center">
                <span class="title_box">Total Unidades
                </span>
            </th>
            <th class="fixed-width-xs text-center">
                <span class="title_box">Propuesta
                </span>
            </th>
            <th></th>
        </tr>
        <tr class="nodrag nodrop filter row_hover">
            <th class="text-center">--</th>
            <th class="text-center"><input type="text" class="filter" id="filtro_id" value=""></th>
            <th class="text-center">--</th>
            <th class="text-center"><input type="text" class="filter" id="filtro_nombre" value=""></th>
            <th class="text-center"><input type="text" class="filter" id="filtro_referencia" value=""></th>
            <th class="text-center"><input type="text" class="filter" id="filtro_ean" value=""></th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>            
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center">--</th>
            <th class="text-center"></th>
        </tr>
        `; 
    document.querySelector('#tabla').appendChild(thead);

    //generamos el botón para subir hasta arriba haciendo scroll
    const boton_scroll = document.createElement('div');    
    boton_scroll.id = "boton_scroll";
    boton_scroll.innerHTML =  `<i class="icon-arrow-up"></i>`;

    boton_scroll.addEventListener('click', scrollArriba);

    //lo append a la botonera, y con css lo haremos fixed
    panel_botones.appendChild(boton_scroll);

}