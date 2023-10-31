<?php
/**
 *
 * Este es un modulo admin que contiene el boton de configuracion
 * Tiene una vista 'plantilla.tpl' con un formulario html que
 * ejecuta un post request el cual es manejado en la funcion getContent
 *
 * Tambien Al momento de instlarse este modulo ejecuta una funcion para
 * crear una tabla en la base de datos.
 *
 * AL desinstalarse elimina la tabla creada
 *
 * @author JOSE GARCES
 */
if (!defined('_PS_VERSION_')) exit;

class DevHorizontSqlOne extends Module
{
  const DB_TABLE_NAME = 'dev_horizont_test_table';

  public function __construct()
  {
    $this->name = 'devhorizontsqlone';
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

    $this->displayName = $this->l('DevHz Sql Form one');
    $this->description = $this->l('Modulo con form template');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('DEVHORIZONTSQLONEENV')) {
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
      $this->installDb() &&
      Configuration::updateValue('DEVHORIZONTSQLONEENV', 'valordemiapikey')
    );
  }

  public function uninstall()
  {
    return (
      parent::uninstall() &&
      $this->uninstallDb() &&
      Configuration::deleteByName('DEVHORIZONTSQLONEENV')
    );
  }

/**
 *
 * funcion que crea una tabla al instalarse el modulo
 * @author JOSE GARCES
 */
  private function installDb()
  {
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'` (
        `dev_horizont_test_table_id` INT AUTO_INCREMENT PRIMARY KEY,
        `content` VARCHAR(255) NOT NULL
    ) ENGINE = InnoDB;';

    return Db::getInstance()->execute($sql);
  }

/**
 *
 * funcion que elimina la tabla creada al instalarse el modulo
 * @author JOSE GARCES
 */
  private function uninstallDb()
  {
    $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'`;';

    return Db::getInstance()->execute($sql);
  }

/**
 *
 * funcion que renderiza una vista el cual tiene un formulario
 * tambien maneja los post request a este modulo.
 * @author JOSE GARCES
 */
  public function getContent()
  {
    $content = 'Contenido original';

    # Aqui recibimos el post
    # verficamos si se presiono el button submit
    if (Tools::isSubmit('btnSubmitForm')) {
      $data = $this->postProcess();
      $content = "data inserted: ".$data;
    }

    $this->context->smarty->assign([
      'title' => 'Este es el titulo',
      'content' => $content
    ]);

    return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
  }

  public function postProcess()
  {
    if ( Tools::isSubmit('PS_NEW_CONTENT_1') ) {
      $content = Tools::getValue('PS_NEW_CONTENT_1');

      // Insertar datos en la base de datos
      $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'` (`content`) VALUES ("'.pSQL($content).'")';

      Db::getInstance()->execute($sql);
  
      $id = (int)Db::getInstance()->Insert_ID();
      return $id;
    }
  }
}

