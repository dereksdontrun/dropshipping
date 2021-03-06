{*
* 2007-2021 PrestaShop
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
*  @author     PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2021 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="row dropshipping-admin-orders">
  <div class="col-lg-12">
    <div class="panel clearfix">
      <div class="panel-heading">
        <i class="icon-shopping-cart"></i>
        Dropshipping <small>Atención - Estos datos no son reales, han sido obtenidos con una versión test de la api de conexión</small>
      </div>
      <div class="col-lg-3">
        <img class="dropshipping-logo" src="{$dropshipping_img_path|escape:'htmlall':'UTF-8'}dropshipping.png"/>        

        <div class="alert alert-light">
          <p>
            <a href="{$link->getAdminLink('AdminPedidosDropshipping')|escape:'html':'utf-8'}" target="_blank">
              Pulsa aquí para administrar todos los pedidos Dropshipping
            </a>
          </p>
        </div>
        {if $info.direccion.envio_almacen}
          <div class="alert alert-warning">
            <p>
              Envío previo a almacén
            </p>
          </div>
        {else}
          <div class="alert alert-success">            
              Envío directo a cliente<br>
              <small>{$info.direccion.firstname} {$info.direccion.lastname}</small><br>
              <small>{$info.direccion.address1}</small><br>
              <small>{$info.direccion.postcode} - {$info.direccion.city}</small><br>
              <small>{$info.direccion.provincia}</small><br>
          </div>
        {/if}
          <div class="alert alert-info">
            <p>
              <b>Proveedores Dropshipping:</b> <br>{$info.detalles.total_proveedores_dropshipping}<br>
              <b>Productos No Dropshipping:</b> <br>{$info.detalles.productos_no_drop}<br>
              <b>Total Productos:</b> <br>{$info.detalles.total_productos}
            </p>
          </div>
      </div>
      <div class="col-lg-9">
        <div id="contenido_dropshipping">
          {foreach from=$info.proveedores key=id_supplier item=proveedor}    
            {* construimos la url con el id de proveedor encadenando a $plantillas, y enviamos los datos de proveedor. Primero comprobamos que la plantilla existe (todo esto debería venir desde php)  *}
            {if file_exists("$plantillas/supplier_$id_supplier/supplier_$id_supplier.tpl")}
              {include file="$plantillas/supplier_$id_supplier/supplier_$id_supplier.tpl" proveedor=$proveedor } 
            {else}
              <div class="alert alert-danger">               
                  Error - Plantilla de proveedor {$proveedor.supplier_name} no encontrada                
              </div>
            {/if}
          {/foreach}           
        </div>
      </div>
    </div>
  </div>
</div>

{* <pre>{$info|@var_export:true|nl2br}</pre> *}
<pre>{$info|@print_r}</pre>
{* {$info} *}