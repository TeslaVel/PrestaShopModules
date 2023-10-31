<?php

/**
 *
 * Este es un modulo admin que contiene el boton de configuracion
 * sirve para setear valores ya sea en una db o en una env.
 * Solo es un modulo que renderiza una vista 'plantilla.tpl' vacia
 * de no tener esta vista, al entra en el modulo solo se mostrara un body vacio
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

class DevHorizontConfigOne extends Module
{
  // Construtor de la clase
  public function __construct()
  {
    $this->name = 'devhorizontconfigone';
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

    $this->displayName = $this->l('DevHz config module one');
    $this->description = $this->l('Modulo con boton configuracion y plantilla.tpl vacia');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('DEVHORIZONCONFIGONEENV')) {
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
      Configuration::updateValue('DEVHORIZONCONFIGONEENV', 'valordemiapikey')
    );
  
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      Configuration::deleteByName('DEVHORIZONCONFIGONEENV')
    );
  }
  
  public function getContent() {
    return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
  }
}
