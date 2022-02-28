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

{* Plantilla para supplier dropshipping id_supplier 161 - Disfrazzes *}


<div class="panel">                        
  <h3>{$proveedor.supplier_name}</h3> 
  <div class="row">
    {if !isset($proveedor.dropshipping)} {* Si no existe array dropshipping dentro del array infoparaese proveedor, es que a pesar de ser dropshipping no lo tenemos funcionando *}
      <div class="alert alert-warning">
        <p>
          Proveedor Dropshipping sin gestión
        </p>
      </div>
    {elseif $proveedor.dropshipping.response_result != 1}
      <div class="col-lg-9">
        <div class="alert alert-danger clearfix">          
          <div class="col-lg-2">                
            <b>Mensaje API:</b><br> <span title="API result: {$proveedor.dropshipping.response_result}">{$proveedor.dropshipping.response_msg}</span>  
          </div>                       
        </div>
      </div>
    {else}
      <div class="col-lg-9"> 
        <div class="alert alert-success clearfix">         
          <div class="col-lg-2">                
            <b>Mensaje API:</b><br> <span title="API result: {$proveedor.dropshipping.response_result}">{$proveedor.dropshipping.response_msg}</span> 
          </div>
          <div class="col-lg-3">                
            <b>Referencia Disfrazzes / ID:</b><br> {$proveedor.dropshipping.disfrazzes_reference} / {$proveedor.dropshipping.disfrazzes_id}
          </div> 
          <div class="col-lg-2">                
            <b>Fecha entrega:</b><br> {$proveedor.dropshipping.response_delivery_date}  
          </div>
          <div class="col-lg-2">                
            <b>Fecha expedición:</b><br> {if $proveedor.dropshipping.date_expedicion == '0000-00-00 00:00:00'} 
                                        Pendiente de envío 
                                      {else $proveedor.dropshipping.date_expedicion} 
                                      {/if}
          </div>
          <div class="col-lg-3">                
            <b>Seguimiento:</b> {if $proveedor.dropshipping.tracking == ''} 
                                  <br>Pendiente de envío 
                                {else}
                                  {$proveedor.dropshipping.tracking }
                                  <br>
                                  {$proveedor.dropshipping.url_tracking }                              
                                {/if}
          </div>          
        </div>
      </div>      
    {/if}
    {if isset($proveedor.dropshipping)}
      <div class="col-lg-3"> 
        Volver a solicitar<br>Actualizar
      </div>
    {/if}
  </div>
  {if isset($proveedor.dropshipping)}
    <div class="row"> <!-- mostramos los productos con sus mensajes, etc -->
      <div class="table-responsive">
        <table class="table" id="productos_dropshipping_{$proveedor.id_supplier}">
          <thead>
            <tr>
              <th></th>
              <th><span class="title_box">Producto</span></th>
              <th>
                <span class="title_box">Referencia</span>
                <small class="text-muted">Prestashop</small>
              </th>
              <th>
                <span class="title_box">Referencia</span>
                <small class="text-muted">Proveedor</small>
              </th>
              <th>
                <span class="title_box">Cantidad<br>Solicitada</span>              
              </th>
              <th>
                <span class="title_box">Cantidad<br>Aceptada</span>              
              </th>
              <th>
                <span class="title_box">API Code</span>              
              </th>
              <th>
                <span class="title_box">Mensaje API</span>              
              </th>		
              <th></th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$proveedor.productos item=producto}  
              <tr>
                <td><img src="https://{$producto.image_path}" alt="" class="imgm img-thumbnail" height="49px" width="45px"></td>
                <td>{$producto.product_name}</td>
                <td>{$producto.product_reference}</td>
                <td>{$producto.product_supplier_reference}</td>
                <td class="text-center">{$producto.product_quantity}</td>
                <td class="text-center">{$producto.variant_quantity_accepted}</td>
                <td class="text-center">{$producto.variant_result}</td>
                <td>{$producto.variant_msg}</td>
                {* <td colspan="2" style="display: none;" class="add_product_fields">&nbsp;</td> *}
                <td class="text-right">
                  <div class="btn-group">
                    <button type="button" class="btn btn-default">
                      <i class="icon-trash"></i>
                        Eliminar
                    </button>                  
                  </div>  
                </td>
              </tr>
            {/foreach}                                                      
          </tbody>
        </table>
      </div>

    </div> <!-- Fin row productos-->
  {/if} <!-- Fin if isset($proveedor.dropshipping)-->
</div>



        