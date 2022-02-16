<?php
/**
 * Gestión de pedidos de proveedores Dropshipping 16/02/2022
 *
 *  @author    Sergio™ <sergio@lafrikileria.com>
 *    
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminPedidosDropshippingController extends ModuleAdminController {
    
    public function __construct() {
        require_once (dirname(__FILE__) .'/../../dropshipping.php');

        $this->lang = false;
        $this->bootstrap = true;        
        $this->context = Context::getContext();
        
        parent::__construct();
        
    }
    
    /**
     * AdminController::init() override
     * @see AdminController::init()
     */
    public function init() {
        $this->display = 'add';
        parent::init();
    }
   
    /*
     *
     */
    public function setMedia(){
        parent::setMedia();
        $this->addJs($this->module->getPathUri().'views/js/back_pedidos_dropshipping.js');
        //añadimos la dirección para el css
        $this->addCss($this->module->getPathUri().'views/css/back_pedidos_dropshipping.css');
    }


    /**
     * AdminController::renderForm() override
     * @see AdminController::renderForm()
     */
    public function renderForm() {    

        //generamos el token de AdminPedidosDropshipping ya que lo vamos a usar en el archivo de javascript . Lo almacenaremos en un input hidden para acceder a el desde js
        $token_admin_modulo = Tools::getAdminTokenLite('AdminPedidosDropshipping');

        $this->fields_form = array(
            'legend' => array(
                'title' => 'Pedidos Dropshipping',
                'icon' => 'icon-pencil'
            ),
            'input' => array( 
                //input hidden con el token para usarlo por ajax etc
                array(  
                    'type' => 'hidden',                    
                    'name' => 'token_admin_modulo_'.$token_admin_modulo,
                    'id' => 'token_admin_modulo_'.$token_admin_modulo,
                    'required' => false,                                        
                ),                 
            ),
            
            // 'reset' => array('title' => 'Limpiar', 'icon' => 'process-icon-eraser icon-eraser'),   
            // 'submit' => array('title' => 'Guardar', 'icon' => 'process-icon-save icon-save'),            
        );

        // $this->displayInformation(
        //     'Revisar productos que actualmente se encuentran en la categoría Prepedido, vendidos o no, o revisar productos vendidos sin stock que se encuentran en pedidos en espera, con o sin categoría prepedido'
        // );
        
        return parent::renderForm();
    }

    public function postProcess() {

        parent::postProcess();

        
    }

    



}
