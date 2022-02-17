<?php
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
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Dropshipping extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'dropshipping';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Sergio';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        //colocamos el link al módulo en la pestaña de pedidos        
        $this->admin_tab[] = array('classname' => 'AdminPedidosDropshipping', 'parent' => 'AdminOrders', 'displayname' => 'Pedidos Dropshipping');

        $this->displayName = $this->l('Dropshipping');
        $this->description = $this->l('Módulo para gestionar pedidos a proveedores mediante sus APIs etc, y en caso de hacer dropshipping poder gestionarlo.');

        $this->confirmUninstall = $this->l('¿Me vas a desinstalar?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('DROPSHIPPING_LIVE_MODE', false);

        //añadimos link en pestaña de pedidos llamando a installTab
        foreach ($this->admin_tab as $tab)
            $this->installTab($tab['classname'], $tab['parent'], $this->name, $tab['displayname']);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionOrderStatusPostUpdate');
    }

    public function uninstall()
    {
        Configuration::deleteByName('DROPSHIPPING_LIVE_MODE');

        //desinstalar el link de la pestaña de pedidos llamando a unistallTab
        foreach ($this->admin_tab as $tab)
            $this->unInstallTab($tab['classname']);

        return parent::uninstall();
    }

    /*
     * Crear el link en pestaña de menú
     */    
    protected function installTab($classname = false, $parent = false, $module = false, $displayname = false) {
        if (!$classname)
            return true;

        $tab = new Tab();
        $tab->class_name = $classname;
        if ($parent)
            if (!is_int($parent))
                $tab->id_parent = (int) Tab::getIdFromClassName($parent);
            else
                $tab->id_parent = (int) $parent;
        if (!$module)
            $module = $this->name;
        $tab->module = $module;
        $tab->active = true;
        if (!$displayname)
            $displayname = $this->displayName;
        $tab->name[(int) (Configuration::get('PS_LANG_DEFAULT'))] = $displayname;

        if (!$tab->add())
            return false;

        return true;
    }

    /*
     * Quitar el link en pestaña de menú 
     */
    protected function unInstallTab($classname = false) {
        if (!$classname)
            return true;

        $idTab = Tab::getIdFromClassName($classname);
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
            ;
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitDropshippingModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDropshippingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'DROPSHIPPING_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'DROPSHIPPING_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'DROPSHIPPING_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'DROPSHIPPING_LIVE_MODE' => Configuration::get('DROPSHIPPING_LIVE_MODE', true),
            'DROPSHIPPING_ACCOUNT_EMAIL' => Configuration::get('DROPSHIPPING_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'DROPSHIPPING_ACCOUNT_PASSWORD' => Configuration::get('DROPSHIPPING_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    // public function hookHeader()
    // {
    //     $this->context->controller->addJS($this->_path.'/views/js/front.js');
    //     $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    // }

    //este hook se activa al cambiar de estado un pedido, justo después
    // array(
        // 'newOrderStatus' => (object) OrderState,
        // 'id_order' => (int) Order ID
        // );
    public function hookActionOrderStatusPostUpdate($params)
    {
        //vamos a comprobar cada cambio de estado, pero solo nos interesan los que van a Verificando stock, antiguo Sin Stock Pagado, ya que si entran en estado no pagado no queremos hacer todavía el pedido y si son pago aceptado es que tenemos stock y no hay que pedirlo
        if ($params) {
            $new_order_status = $params['newOrderStatus'];
            if (Validate::isLoadedObject($new_order_status) && $new_order_status->id == Configuration::get(PS_OS_OUTOFSTOCK_PAID)){ 
                // $estado_pedido = $new_order_status->id;
                $id_order = $params['id_order'];

                //chequeamos si ya ha pasado por aquí el pedido
                if ($this->checkPedidoProcesado($id_order)) {
                    return;
                }

                //chequeamos si algún producto corresponde a proveedor dropshipping. Devuelve false si no hay productos dropshipping y un array con los productos y sus proveedores si hay uno o varios
                if (!$info_dropshipping = $this->checkProveedorDropshipping($id_order)) {
                    return;
                }

                //si hay productos sin stock de proveedores dropshipping los tenemos en $info_dropshipping

                    
                


            } //if validate status and status verificando stock

        } // if $params

    }

    //función que comprueba si el pedido ya existe en la tabla de pedidos dropshipping (en caso de ser un cambio de estado manual, porque si se cambia el estado manualmente una vez ha entrado se duplicaría la entrada), también deberá comprobar, si existe, si los productos dropshipping actuales son los mismos y actuar en consecuencia. De momento simplemente, si lo encuentra pasamos del pedido    
    public function checkPedidoProcesado($id_order) 
    {
        //por ahora solo comprobamos si existe - habrá que añadir que compruebe si los productos son los mismos en el futuro
        $sql_existe_pedido = 'SELECT id_dropshipping_pedidos
        FROM lafrips_dropshipping_pedidos                     
        WHERE id_order = '.$id_order;

        if (Db::getInstance()->ExecuteS($sql_existe_pedido)) {
            return true;
        }

        return false;
    }

    //obtenemos los productos del pedido y comprobamos si alguno corresponde a proveedor dropshipping y si por su stock(falta de) hay que pedirlo al proveedor. Devuelve false si no encuentra productos para dropshipping y un array con lso productos y sus respectivos proveedores, etc si los encuentra
    //Si hay productos dropshipping devuelve un array multidimensional. en $info_dropshipping[0] irá la información del pedido (id_customer, id_address_delivery, etc) y luego irá otro subarray por proveedor dropshipping. Si hay de disfrazzes será $info_dropshipping[id_proveedor_disfrazzes], y dentro tantos arrays como productos dropshipping de dicho array con su info. Si luego hubiera un producto dmi, $info_dropshipping[id_proveedor_dmi]
    public function checkProveedorDropshipping($id_order) 
    {
        $proveedores_dropshipping = explode(",", Configuration::get(PROVEEDORES_DROPSHIPPING));
        $num_dropshipping = 0;
        $num_no_dropshipping = 0;
        $info_dropshipping = array();

        $order = new Order($id_order);
        if (Validate::isLoadedObject($order)){ 
            //sacamos los productos del pedido                   
            $order_products = $order->getProducts();      

            //comprobamos si hay algún producto dropshipping en el pedido, y su stock en order_detail 
            if ($order_products) {
                foreach ($order_products as $producto) {
                    if (in_array((int)$producto['id_supplier'], $proveedores_dropshipping)) {                     
                        $producto_dropshipping = array();
                        //si el producto no está en gestión avanzada, pasamos al siguiente
                        if (!StockAvailableCore::dependsOnStock($producto['product_id'])){
                            $num_no_dropshipping++;
                            continue;
                        }      
                        
                        //product_quantity_in_stock indica las unidades disponibles en el momento de la compra sobre las compradas, es decir, si se compran 5 y hay 7, valdrá 5. Si se compran 5 y hay 3 valdrá 3. Salvo para pedidos de worten y amazon que su módulo tiene algo mal y product_quantity_in_stock equivale al stock total en el momento de compra (creo que disponible)
                        //
                        //si la cantidad en stock es mayor o igual que las unidades compradas, no es sin stock, pasamos al siguiente.                         
                        if ($producto['product_quantity_in_stock'] >= $producto['product_quantity']) {
                            $num_no_dropshipping++;
                            continue;
                        }   
                        
                        $num_dropshipping++;
                        
                        //en este punto, se ha vendido un producto sin stock de proveedor dropshipping   
                        $producto_dropshipping['id_order_detail'] = $producto['id_order_detail'];                                                  
                        $producto_dropshipping['id_supplier'] = (int)$producto['id_supplier'];
                        $producto_dropshipping['id_product'] = $producto['product_id']; 
                        $producto_dropshipping['id_product_attribute'] = $producto['product_attribute_id'];
                        $producto_dropshipping['product_name'] = pSQL($producto['product_name']);
                        $producto_dropshipping['product_reference'] = pSQL($producto['product_reference']);
                        $producto_dropshipping['product_supplier_reference'] = pSQL($producto['product_supplier_reference']);
                        $producto_dropshipping['product_quantity'] = $producto['product_quantity']; 

                        $info_dropshipping[(int)$producto['id_supplier']][] = $producto_dropshipping;

                    } else {
                        //este producto no tiene supplier dropshipping
                        $num_no_dropshipping++;
                        continue;
                    }                                 
                }                                
            }
        }

        if ($num_dropshipping) {
            //hay dropshipping, sacamos información sobre el pedido y la metemos en la posición 0 del array info
            //si en info_dropshipping hay más un array es que hay más de un proveedor, contamos antes de meter info de pedido
            $numero_proveedores = count($info_dropshipping);
            $info_dropshipping[0]['id_order'] = $id_order;
            $info_dropshipping[0]['proveedores_dropshipping'] = $numero_proveedores;                      
            $info_dropshipping[0]['otros_productos'] = $num_no_dropshipping; //si hay productos no dropshipping  
            $info_dropshipping[0]['total_productos'] = $num_dropshipping + $num_no_dropshipping;
            $info_dropshipping[0]['id_customer'] = $order->id_customer;  
            $info_dropshipping[0]['id_address_delivery'] = $order->id_address_delivery; 

            return $info_dropshipping;
        }

        return false;

    }
}
