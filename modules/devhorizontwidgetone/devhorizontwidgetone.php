<?php
/**
 *
 * Este es un modulo no contiene configuracion, en su lugar implemente un widget en el home
 * que es algo parecido a un hook pero permite transplantarlo a otros hook en diferentes parte del proyecto
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

if (file_exists(__DIR__.'/vendor/autoload.php')) {
  require_once __DIR__.'/vendor/autoload.php';
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class DevHorizontWidgetOne extends Module implements WidgetInterface
{
  public function __construct()
  {
    $this->name = 'devhorizontwidgetone';
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

    $this->displayName = $this->l('DevHz Widget module one');
    $this->description = $this->l('Widget para mostrar un texto en el home.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

  }

  public function install()
  {
    if (Shop::isFeatureActive()) {
      Shop::setContext(Shop::CONTEXT_ALL);
    }

    return (
      parent::install() &&
      $this->registerHook('displayFooter') &&
      $this->registerHook('displayNavFullWidth') &&
      Configuration::updateValue('VALORPARAWIDGET', 'ESTE ES EL TEXO DE MI WIDGED') &&
      Configuration::updateValue('VALORPARAWIDGETEXECPTION', 'ESTA ES LA EXCEPCIÓN')
    );
  
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      Configuration::deleteByName('VALORPARAWIDGET') &&
      Configuration::deleteByName('VALORPARAWIDGETEXECPTION')
    );
  }

  public function getContent()
  {
    $title = 'Modulo de configuracion';
    $content = 'Texto descriptivo del modulo';

    if (Tools::isSubmit('btnSubmitForm')) {
      $v_widget = Tools::getValue('valor_input_widget');
      $v_widget_ex = Tools::getValue('valor_input_widget_execption');

      if (!empty($v_widget)) Configuration::updateValue('VALORPARAWIDGET', $v_widget);
      if (!empty($v_widget_ex)) Configuration::updateValue('VALORPARAWIDGETEXECPTION', $v_widget_ex);
    }

    $this->context->smarty->assign([
      'title' => $title,
      'content' => $content,
    ]);

    return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
  }

  public function renderWidget($hookname, array $configuration) {

    $this->context->smarty->assign($this->getWidgetVariables($hookname, $configuration));

    if($hookname == 'displayNavFullWidth') {
      return $this->fetch("module:devhorizontwidgetone/views/templates/hook/devhorizonttemplate.tpl");
    }

    return $this->fetch("module:devhorizontwidgetone/views/templates/hook/devhorizontwidget.tpl");
  }

  public function getWidgetVariables($hookname, array $configuration) {

    if($hookname == 'displayNavFullWidth') {
      return [
        'title' => Configuration::get('VALORPARAWIDGETEXECPTION'),
      ];
    }

    return [
      'title' => Configuration::get('VALORPARAWIDGET'),
    ];
  }
}
