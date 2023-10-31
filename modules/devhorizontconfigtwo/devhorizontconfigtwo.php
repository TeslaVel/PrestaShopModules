<?php
/**
 *
 * Este es un modulo admin que contiene el boton de configuracion
 * sirve para setear valores ya sea en una db o en una env.
 * Tiene una vista 'plantilla.tpl' con un formulario html
 * que ejecuta un post request el cual es manejado en la funcion getContent
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

class DevHorizontConfigTwo extends Module
{
  public function __construct()
  {
    $this->name = 'devhorizontconfigtwo';
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

    $this->displayName = $this->l('DevHz config module two');
    $this->description = $this->l('Modulo con boton configuracion y plantilla.tpl, puede recibir post del formulario de configuracion');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('DEVHORIZONTCONFIGTWOENV')) {
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
      Configuration::updateValue('DEVHORIZONTCONFIGTWOENV', 'algunvaloraqui')
    );
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      Configuration::deleteByName('DEVHORIZONTCONFIGTWOENV')
    );
  }

/**
 *
 * Funcion que renderiza una vista al presionar el boton configure
 * que aparece en al lado del dropdown menu para instalar el modulo
 * Tambien maneja los request hacia este modulo
 *
 * @author JOSE GARCES
 */
  public function getContent()
  {
    $content = 'Contenido original';

    # Aqui recibimos el post
    # verficamos si se presiono el button submit
    if (Tools::isSubmit('btnSubmitForm')) {
      $content = Tools::getValue('PS_NEW_CONTENT_1');
    }

    $this->context->smarty->assign([
      'title' => 'Este es el titulo',
      'content' => $content
    ]);

    return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
  }
}

