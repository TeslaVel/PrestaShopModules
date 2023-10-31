<?php
/**
 *
 * Este es un modulo admin que contiene el boton de configuracion
 * sirve para setear valores ya sea en una db o en una env.
 * Tiene una vista 'plantilla.tpl' con un formulario geneardo por un
 * helperform que ejecuta un post request el cual es manejado en la funcion getContent
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

class DevHorizontConfigThree extends Module
{
  public function __construct()
  {
    $this->name = 'devhorizontconfigthree';
    $this->tab = 'administration';
    $this->version = '1.0.0';
    $this->author = 'Jose Garces';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = [
      'min' => '1.7.0.0',
      'max' => '8.99.99',
    ];
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('DevHz config module three');
    $this->description = $this->l('Modulo con boton configuracion y plantilla.tpl, puede recibir post del formulario de configuracion');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('DEVHORIZONTCONFIGTHREEENV')) {
      $this->warning = $this->l('No name provided');
    }
  }

  public function install()
  {
    if (Shop::isFeatureActive()) {
      Shop::setContext(Shop::CONTEXT_ALL);
    }

    return (
      parent::install() &&
      Configuration::updateValue('DEVHORIZONTCONFIGTHREEENV', 'valordemiapikey')
    );
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      Configuration::deleteByName('DEVHORIZONTCONFIGTHREEENV')
    );
  }

/**
 *
 * Funcion que renderiza una vista al presionar el boton configure
 * que aparece en al lado del dropdown menu para instalar el modulo.
 * Tambien maneja los request hacia este modulo
 *
 * @author JOSE GARCES
 */
  public function getContent()
  {
    $content = 'Este es el contenido';
    # Aqui recibimos el post
    # verficamos si se presiono el button submit
    if (Tools::isSubmit('btnSubmitHelp') == true) {
      $content = Tools::getValue('PS_TEXT_INPUT_1');
    }

    $this->context->smarty->assign([
      'title' => 'Este es el titulo',
      'content' => $content,
      'footer' => 'Este es el footer'
    ]);

    $output = $this->display(__FILE__, 'views/templates/admin/configure.tpl');

    return $output . $this->renderForm();
  }

/**
 *
 * Funcion que utiliza un HelperForm para genera un formulario
 * y retornarlo
 *
 * @author JOSE GARCES
 */
  protected function renderForm()
  {
    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->identifier = $this->identifier;
    $helper->submit_action = 'btnSubmitHelp';
    $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
    # $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
    $helper->fields_value = $this->formVars();

    return $helper->generateForm([$this->getFormStructure()]);
  }

  /**
   * Esta funcion retorna un array de etiquetas y attributos
   * para ser utilizada pro el HelperForm y daler estructura al formulario
   * @return string HTML code
   */
  protected function getFormStructure()
  {
    return [
      'form' => [
        'legend' => [
            'title' => $this->l('Titulo del form'),
        ],
        'input' => [
          [
            'type' => 'text',
            'label' => $this->l('Label del input'),
            'name' => 'PS_TEXT_INPUT_1',
            'size' => 20,
          ],
        ],
        'submit' => [
          'title' => $this->l('Save'),
          'class' => 'btn btn-default pull-right',
        ],
      ],
    ];
  }

  /**
   * Esta funcion retorna un array de campos con valores por default
   * para ser utilizada en el renderForm dentro del HelperForm
   * @return string HTML code
   */
  protected function formVars()
  {
    return [
      'PS_TEXT_INPUT_1' => ''
    ];
  }
}

