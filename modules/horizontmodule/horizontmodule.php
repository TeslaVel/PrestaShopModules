<?php
/**
 *
 * Este es un modulo con un front controller
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

if (file_exists(__DIR__.'/vendor/autoload.php')) {
  require_once __DIR__.'/vendor/autoload.php';
}


class HorizontModule extends Module
{
  public function __construct()
  {
    $this->name = 'horizontmodule';
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

    $this->displayName = $this->l('DevHz Front Controller one');
    $this->description = $this->l('Front Controller para mostrar una pagina en el home.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

  }

  public function install()
  {
    return (
      parent::install() &&
      $this->registerHook('displayNavFullWidth') &&
      Configuration::updateValue('VALORPARAWIDGET', 'ESTE ES EL TEXO DE MI WIDGED')
    );
  
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      Configuration::deleteByName('VALORPARAWIDGET')
    );
  }

  public function getContent()
  {
    $title = 'Modulo de configuracion';
    $content = 'Texto descriptivo del modulo';

    if (Tools::isSubmit('btnSubmitForm')) {
      $v_widget = Tools::getValue('valor_input_widget');

      if (!empty($v_widget)) Configuration::updateValue('VALORPARAWIDGET', $v_widget);
    }

    $this->context->smarty->assign([
      'title' => $title,
      'content' => $content
    ]);

    return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
  }

  public function HookDisplayNavFullWidth($params) {

    $module_link = $this->context->link->getModuleLink('horizontmodule', 'page');
    $this->context->smarty->assign([
      'title' => 'Horizont Module FC',
      'module_link' => $module_link
    ]);

    return $this->fetch("module:horizontmodule/views/templates/hook/devhorizonttemplate.tpl");
  }
}
