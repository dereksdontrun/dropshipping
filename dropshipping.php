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
                    //si devuelve true,comprobaremos si los productos dropshipping coinciden o se han modificado, en cuyo caso volver a solictar pedido?
                    //para más adelante
                    return;
                }

                //chequeamos si algún producto corresponde a proveedor dropshipping. Devuelve false si no hay productos dropshipping y un array con los productos y sus proveedores si hay uno o varios
                if (!$info_dropshipping = $this->checkProveedorDropshipping($id_order)) {
                    return;
                }

                //si hay productos sin stock de proveedores dropshipping los tenemos en $info_dropshipping
                //necesitamos la información de la dirección de entrega
                if (!$info_address = $this->getAddressInfo($info_dropshipping['order']['id_address_delivery'])) {
                    //no tenemos dirección, marcamos el pedido con error = 1
                    $error = 1;
                    $firstname = '';
                    $lastname = '';
                    $company = '';
                    $email = '';
                    $phone = '';
                    $address1 = '';
                    $postcode = '';
                    $city = '';
                    $provincia = '';
                    $country = '';
                    $other = '';
                    $dni = '';
                } else {
                    $error = 0;
                    $firstname = $info_address[0]['firstname'];
                    $lastname = $info_address[0]['lastname'];
                    $company = $info_address[0]['company'];
                    $email = $info_address[0]['email'];
                    $phone = $info_address[0]['phone'];
                    $address1 = $info_address[0]['address1'];
                    $postcode = $info_address[0]['postcode'];
                    $city = $info_address[0]['city'];
                    $provincia = $info_address[0]['provincia'];
                    $country = $info_address[0]['country'];
                    $other = $info_address[0]['other'];
                    $dni = $info_address[0]['dni'];
                }
                //almacenamos la dirección en lafrips_dropshipping_address y guardamos el id de la inserción
                $sql_insert_lafrips_dropshipping_address = 'INSERT INTO lafrips_dropshipping_address
                 (id_order, id_customer, email, id_address_delivery, firstname, lastname, company, address1, postcode, city, provincia, country, phone, other, dni, error, date_add) 
                 VALUES 
                 ('.$id_order.',
                 '.$info_dropshipping['order']['id_customer'].',
                 "'.$email.'", 
                 '.$info_dropshipping['order']['id_address_delivery'].', 
                 "'.$firstname.'", 
                 "'.$lastname.'", 
                 "'.$company.'", 
                 "'.$address1.'", 
                 "'.$postcode.'", 
                 "'.$city.'", 
                 "'.$provincia.'", 
                 "'.$country.'", 
                 "'.$phone.'", 
                 "'.$other.'", 
                 "'.$dni.'", 
                 '.$error.',
                 NOW())';
                Db::getInstance()->executeS($sql_insert_lafrips_dropshipping_address);

                $id_last_insert_lafrips_dropshipping_address = Db::getInstance()->Insert_ID();


                foreach ($info_dropshipping['dropshipping'] AS $key => $info_productos) {
                    //$key es el id_supplier de dropshipping e info_productos el array que contiene cada producto dropshipping. Hacemos un insert en la tabla de pedidos dropshipping por cada proveedor implicado, y un insert por producto en la tabla de dropshipping de cada proveedor
                    $id_supplier = $key;
                    $supplier_name = Supplier::getNameById($id_supplier);

                    $sql_insert_lafrips_dropshipping = 'INSERT INTO lafrips_dropshipping
                    (id_supplier, supplier_name, id_order, id_customer, id_address_delivery, id_dropshipping_address, total_proveedores_dropshipping, productos_no_drop, total_productos, error, date_add) 
                    VALUES 
                    ('.$id_supplier.',
                    "'.$supplier_name.'", 
                    '.$id_order.',
                    '.$info_dropshipping['order']['id_customer'].',
                    '.$info_dropshipping['order']['id_address_delivery'].', 
                    '.$id_last_insert_lafrips_dropshipping_address.', 
                    '.$info_dropshipping['order']['total_proveedores_dropshipping'].',
                    '.$info_dropshipping['order']['productos_no_drop'].',
                    '.$info_dropshipping['order']['total_productos'].',
                    '.$error.',
                    NOW())';
                    Db::getInstance()->executeS($sql_insert_lafrips_dropshipping);
                    $id_last_insert_lafrips_dropshipping = Db::getInstance()->Insert_ID();

                    //ahora enviamos los productos a la función correspondiente a cada proveedor dropshipping, los distribuimos con un switch que habrá que actualizar cada vez que se añada un proveedor dropshipping
                    //a 18/02/2022 solo trabajamos con Disfrazzes
                    switch ($id_supplier) {
                        case (int)Supplier::getIdByName('Disfrazzes'):
                            if (!$this->productosProveedorDisfrazzes($info_productos, $id_order, $id_last_insert_lafrips_dropshipping)) {
                                //error procesando los productos o haciendo el pedido, marcamos la tabla dropshipping con error = 1
                                $sql_update_lafrips_dropshipping = 'UPDATE lafrips_dropshipping      
                                SET                                
                                error = 1, 
                                date_upd = NOW()
                                WHERE id_dropshipping = '.$id_last_insert_lafrips_dropshipping;

                                Db::getInstance()->Execute($sql_update_lafrips_dropshipping);
                            }
                            break;

                        case (int)Supplier::getIdByName('DMI'):
                            //aviso por email desde apiDmi()
                            $this->apiDmi($info_productos);
                            break;

                        case (int)Supplier::getIdByName('Globomatik'):
                            //aviso por email desde apiDmi()
                            $this->apiGlobomatik($info_productos);
                            break;
    
                        default:
                            //el id_supplier no corresponde a ninguno de los proveedores dropshipping que tenemos contemplados, o aún no he actualizado este módulo para un nuevo proveedor.  Marcamos error en lafrips_dropshipping y envío email aviso?

                                                
                    }


                }

            } //if validate status and status verificando stock

        } // if $params

    }

    //función que comprueba si el pedido ya existe en la tabla de pedidos dropshipping (en caso de ser un cambio de estado manual, porque si se cambia el estado manualmente una vez ha entrado se duplicaría la entrada), también deberá comprobar, si existe, si los productos dropshipping actuales son los mismos y actuar en consecuencia. De momento simplemente, si lo encuentra pasamos del pedido    
    public function checkPedidoProcesado($id_order) 
    {
        //por ahora solo comprobamos si existe - habrá que añadir que compruebe si los productos son los mismos en el futuro
        $sql_existe_pedido = 'SELECT id_dropshipping
        FROM lafrips_dropshipping                     
        WHERE id_order = '.$id_order;

        if (Db::getInstance()->ExecuteS($sql_existe_pedido)) {
            return true;
        }

        return false;
    }

    //obtenemos los productos del pedido y comprobamos si alguno corresponde a proveedor dropshipping y si por su stock(falta de) hay que pedirlo al proveedor. Devuelve false si no encuentra productos para dropshipping y un array con lso productos y sus respectivos proveedores, etc si los encuentra
    //Si hay productos dropshipping devuelve un array multidimensional. en $info_dropshipping['order'] irá la información del pedido (id_customer, id_address_delivery, etc) y luego irá otro subarray $info_dropshipping['dropshipping'] con otro subarray por proveedor dropshipping. Si hay de disfrazzes será $info_dropshipping['dropshipping'][id_proveedor_disfrazzes], y dentro tantos arrays como productos dropshipping de dicho array con su info. Si luego hubiera un producto dmi, $info_dropshipping['dropshipping'][id_proveedor_dmi]
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
                        $producto_dropshipping['id_product'] = $producto['product_id']; 
                        $producto_dropshipping['id_product_attribute'] = $producto['product_attribute_id'];
                        $producto_dropshipping['product_name'] = pSQL($producto['product_name']);
                        $producto_dropshipping['product_reference'] = pSQL($producto['product_reference']);
                        $producto_dropshipping['product_supplier_reference'] = pSQL($producto['product_supplier_reference']);
                        $producto_dropshipping['product_quantity'] = $producto['product_quantity']; 

                        $info_dropshipping['dropshipping'][(int)$producto['id_supplier']][] = $producto_dropshipping;

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
            $numero_proveedores = count($info_dropshipping['dropshipping']);
            $info_dropshipping['order']['id_order'] = $id_order;
            $info_dropshipping['order']['total_proveedores_dropshipping'] = $numero_proveedores;                      
            $info_dropshipping['order']['productos_no_drop'] = $num_no_dropshipping; //si hay productos no dropshipping  
            $info_dropshipping['order']['total_productos'] = $num_dropshipping + $num_no_dropshipping;
            $info_dropshipping['order']['id_customer'] = $order->id_customer;  
            $info_dropshipping['order']['id_address_delivery'] = $order->id_address_delivery; 
            $info_dropshipping['order']['payment'] = $order->payment; 
            $info_dropshipping['order']['date_add'] = $order->date_add; 

            return $info_dropshipping;
        }

        return false;

    }

    //función que recibe id_delivery_address y devuelve los datos necesarios para la entrega
    public function getAddressInfo($id_address) {
        $sql_info_address = 'SELECT adr.firstname AS firstname, adr.lastname AS lastname, adr.company AS company, cus.email AS email, 
        CONCAT(IF(CHAR_LENGTH(adr.phone) < 7,"", adr.phone), IF(CHAR_LENGTH(adr.phone) < 7 OR CHAR_LENGTH(adr.phone_mobile) < 7,"","-"), IF(CHAR_LENGTH(adr.phone_mobile) < 7,"", adr.phone_mobile)) AS phone,
        CONCAT(adr.address1,IF(adr.address2 = "",""," "),IF(adr.address2 = "","",adr.address2)) AS address1, 
        adr.postcode AS postcode, adr.city AS city, sta.name AS provincia, col.name AS country,
        adr.other AS other, adr.dni AS dni
        FROM lafrips_address adr
        JOIN lafrips_customer cus ON cus.id_customer = adr.id_customer
        LEFT JOIN lafrips_state sta ON adr.id_state = sta.id_state
        LEFT JOIN lafrips_country_lang col ON adr.id_country = col.id_country AND col.id_lang = 1
        WHERE adr.id_address = '.$id_address;

        if ($info_address = Db::getInstance()->ExecuteS($sql_info_address)) {
            return $info_address;
        }

        return false;

    }

    //función que recibe los productos de un pedido para Disfrazzes, los mete a la tabla y llama a la función que finalmnente hará la llamada a la aPI de Disfrazzes
    public function productosProveedorDisfrazzes($info_productos, $id_order, $id_last_insert_lafrips_dropshipping) {
        //por cada producto del proveedor dropshipping lo metemos en su tabla y haremos la petición a la API correspondiente para el pedido
        foreach ($info_productos AS $info) {
            //sacamos product_id y variant_id para disfrazzes
            $product_supplier_reference = $info['product_supplier_reference'];
            $referencia_disfrazzes = explode("_", $product_supplier_reference);
            $product_id = $referencia_disfrazzes[0];
            $variant_id = $referencia_disfrazzes[1];

            $sql_insert_lafrips_dropshipping_disfrazzes = 'INSERT INTO lafrips_dropshipping_disfrazzes
            (id_dropshipping, id_order, id_order_detail, id_product, id_product_attribute, product_supplier_reference, product_quantity, product_name, product_reference, product_id, variant_id, date_add) 
            VALUES 
            ('.$id_last_insert_lafrips_dropshipping.',            
            '.$id_order.',
            '.$info['id_order_detail'].',
            '.$info['id_product'].',
            '.$info['id_product_attribute'].',
            "'.$product_supplier_reference.'", 
            '.$info['product_quantity'].',
            "'.$info['product_name'].'", 
            "'.$info['product_reference'].'", 
            '.$product_id.',
            '.$variant_id.',
            NOW())';

            Db::getInstance()->executeS($sql_insert_lafrips_dropshipping_disfrazzes); 
        }     

        //llamamos a la función que hace el pedido a la API
        if (!$this->apiDisfrazzes($id_last_insert_lafrips_dropshipping)) {
            return false;
        }

        return true; 
    }

    //función que llama a la API disfrazzes para hacer el pedido
    public function apiDisfrazzes($id_lafrips_dropshipping) {
        //prueba, enviar email
        //preparamos los parámetros para la llamada, info del pedido y de los productos. Tenemos el id de la tabla dropshipping del pedido
        //el email enviamos tienda@lafrikileria.com en lugar del del cliente

        //sacamos la info del pedido
        $sql_info_order = 'SELECT dro.date_add AS fecha, dro.id_order AS id_order, dra.firstname AS firstname, dra.lastname AS lastname, dra.phone AS phone, dra.company AS company,
        dra.address1 AS address1, dra.postcode AS postcode, dra.city AS city, dra.country AS country
        FROM lafrips_dropshipping dro
        JOIN lafrips_dropshipping_address dra ON dra.id_dropshipping_address = dro.id_dropshipping_address
        WHERE dro.id_dropshipping = '.$id_lafrips_dropshipping;

        $info_order = Db::getInstance()->executeS($sql_info_order); 

        $date_add = $info_order[0]['fecha'];
        $id_order = $info_order[0]['id_order'];
        $firstname = $info_order[0]['firstname'];
        $lastname = $info_order[0]['lastname'];
        $phone = $info_order[0]['phone'];
        $company = $info_order[0]['company'];
        $address1 = $info_order[0]['address1'];
        $postcode = $info_order[0]['postcode'];
        $city = $info_order[0]['city'];

        //sacamos la info de los productos del pedido
        $sql_info_productos = 'SELECT id_order_detail, product_id, variant_id, product_quantity
        FROM lafrips_dropshipping_disfrazzes 
        WHERE id_dropshipping = '.$id_lafrips_dropshipping;

        $info_productos = Db::getInstance()->executeS($sql_info_productos); 
        
        $lines = array();

        foreach ($info_productos AS $info_producto) {
            $producto = array(
                "marketplace_row_id" => $info_producto['id_order_detail'],
                "product_id" => $info_producto['product_id'],
                "variant_id" => $info_producto['variant_id'],
                "quantity" => $info_producto['product_quantity'],
                "expected_price" => ""
            );

            $lines[] = $producto;
        }


        $country_ISO2 = 'ES';

        $parameters = array(
            "date" => $date_add,
            "marketplace_order_id" => $id_order,
            "label_content" => "",
            "address" => array(
                "email" => 'tienda@lafrikileria.com',
                "name" => $firstname,
                "surname" => $lastname,
                "phone" => $phone,
                "company" => $company,
                "address" => $address1,
                "floor" => "",
                "zip_code" => $postcode,
                "city" => $city,
                "country_ISO2" => $country_ISO2
            ),
            "lines" => array($lines[0])
        );

        $array_json_parameters = json_encode($parameters);

        $data = array(
          "login" => "frikileria",
          "pass" => "fk2021ZZ\$DP",
          "parameters" => $array_json_parameters
        );

        $url = http_build_query($data);
    
        $endpoint_test = 'https://zzdevapi.disfrazzes.com/method/register_order';
        $endpoint_produccion = 'https://api.disfrazzes.com/method/register_order';

        $curl = curl_init();
    
        curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint_test,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $url,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
        ));

        $mensaje = '<br>Petición:<br>'.$array_json_parameters.'<br><br>';

        if ($response = curl_exec($curl)) {
            curl_close($curl);
          
            $mensaje .= '<br>Respuesta:<br>'.$response.'<br><br>';
            //pasamos el JSON de respuesta a un objeto PHP
            $response_decode = json_decode($response); 

        } else {
            //no hay respuesta pero cerramos igualmente
            curl_close($curl);
        }
        
        
        $asunto = 'Pedido Disfrazzes para dropshipping '.date("Y-m-d H:i:s");
        $info = [];                
        $info['{firstname}'] = 'Sergio';
        $info['{archivo_expediciones}'] = 'Hora ejecución '.date("Y-m-d H:i:s");
        $info['{errores}'] = $mensaje;
        // print_r($info);
        // $info['{order_name}'] = $order->getUniqReference();
        @Mail::Send(
            1,
            'aviso_error_expedicion_cerda', //plantilla
            Mail::l($asunto, 1),
            $info,
            'sergio@lafrikileria.com',
            'Sergio',
            null,
            null,
            null,
            null,
            _PS_MAIL_DIR_,
            true,
            1
        );

    }

    //función que llama a la API DMI para hacer el pedido
    public function apiDmi($info_productos) {
        //prueba, enviar email
        //preparamos los parámetros para la llamada, info del pedido y de los productos. Tenemos el id de la tabla dropshipping del pedido
        $mensaje = '<pre>'.json_encode($info_productos).'</pre>';

        $asunto = 'Pedido DMI para dropshipping '.date("Y-m-d H:i:s");
        $info = [];                
        $info['{firstname}'] = 'Sergio';
        $info['{archivo_expediciones}'] = 'Hora ejecución '.date("Y-m-d H:i:s");
        $info['{errores}'] = $mensaje;
        // print_r($info);
        // $info['{order_name}'] = $order->getUniqReference();
        @Mail::Send(
            1,
            'aviso_error_expedicion_cerda', //plantilla
            Mail::l($asunto, 1),
            $info,
            'sergio@lafrikileria.com',
            'Sergio',
            null,
            null,
            null,
            null,
            _PS_MAIL_DIR_,
            true,
            1
        );
       

    }

    //función que llama a la API Globomatik para hacer el pedido
    public function apiGlobomatik($info_productos) {
        //prueba, enviar email
        //preparamos los parámetros para la llamada, info del pedido y de los productos. Tenemos el id de la tabla dropshipping del pedido
        $mensaje = '<pre>'.json_encode($info_productos).'</pre>';

        $asunto = 'Pedido Globomatik para dropshipping '.date("Y-m-d H:i:s");
        $info = [];                
        $info['{firstname}'] = 'Sergio';
        $info['{archivo_expediciones}'] = 'Hora ejecución '.date("Y-m-d H:i:s");
        $info['{errores}'] = $mensaje;
        // print_r($info);
        // $info['{order_name}'] = $order->getUniqReference();
        @Mail::Send(
            1,
            'aviso_error_expedicion_cerda', //plantilla
            Mail::l($asunto, 1),
            $info,
            'sergio@lafrikileria.com',
            'Sergio',
            null,
            null,
            null,
            null,
            _PS_MAIL_DIR_,
            true,
            1
        );

    }
}
