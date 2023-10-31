<?php
/**
 *
 * Este es un modulo no contiene configuracion, en su lugar registra un hook en el home
 * del cliente y muestra un texto final de productos, la posicion se puede configurar
 * en el dashboard en el menu lateral diseño/posiciones busca los que estan en
 * displayHome y alli aparecera este hook para moverlos de posicion
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

class DevHorizontHookOne extends Module
{
  public function __construct()
  {
    $this->name = 'devhorizonthookone';
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

    $this->displayName = $this->l('DevHz Hook module one');
    $this->description = $this->l('Hook para mostrar un texto en el home.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('DEVHORIZONTHOOKEENV')) {
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
      $this->registerHook('displayHome') &&
      Configuration::updateValue('DEVHORIZONTHOOKEENV', 'valordemiapikey')
    );
  
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      Configuration::deleteByName('DEVHORIZONTHOOKEENV')
    );
  }


  public function HookDisplayHome($params) {
    $this->context->smarty->assign([
      'title' => 'Este es el titlo del hook',
      'content' => 'El contenido del hook'
    ]);

    return $this->display(__FILE__, 'views/templates/hook/devhorizonttopsection.tpl');
  }
}
