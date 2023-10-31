<?php

declare(strict_types=1);

use PrestaShop\Module\DevHorizontMembers\Controller\Admin\MemberController;

if (!defined('_PS_VERSION_')) exit;

// Needed for install process
require_once __DIR__ . '/vendor/autoload.php';

class DevHorizontMembers extends Module
{
  public function __construct()
  {
    $this->name = 'devhorizontmembers';
    $this->tab = 'administration';
    $this->author = 'Jose Garces';
    $this->version = '1.0.0';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = ['min' => '1.7.7', 'max' => '8.99.99'];
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('DevHz Members Tab');
    $this->description = $this->l('Manage dev horizont members');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    $tabNames = [];
    foreach (Language::getLanguages(true) as $lang) {
      $tabNames[$lang['locale']] = $this->trans('DevHz Member Tabs', [], 'Modules.DevHorizontMembers.Admin', $lang['locale']);
    }

    $this->tabs = [
      [
        'route_name' => 'ps_dev_horizont_members',
        'class_name' => MemberController::TAB_CLASS_NAME,
        'visible' => true,
        'name' => $tabNames,
        'icon' => 'school',
        'parent_class_name' => 'IMPROVE',
      ],
    ];
  }

  public function install()
  {
    return parent::install() &&
    $this->installDb();
  }

  public function installDb()
  {
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . MemberController::DB_TABLE_NAME . '` (
              `dev_horizont_member_id` INT AUTO_INCREMENT PRIMARY KEY,
              `email` VARCHAR(255) NOT NULL,
              `first_name` VARCHAR(255) NOT NULL,
              `last_name` VARCHAR(255) NOT NULL,
              `age` INT NOT NULL
            ) ENGINE = InnoDB;';

    return Db::getInstance()->execute($sql);
  }

  public function uninstall()
  {
    return parent::uninstall() &&
    $this->uninstallDb();
  }

  public function uninstallDb()
  {
    $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . MemberController::DB_TABLE_NAME . '`';
    return Db::getInstance()->execute($sql);
  }

  public function getContent()
  {
    Tools::redirectAdmin(
        $this->context->link->getAdminLink(MemberController::TAB_CLASS_NAME)
    );
  }
}
