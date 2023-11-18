<?php

/**
 *
 * Este es un modulo admin que contiene el boton de configuracion
 * sirve para setear valores ya sea en una db o en una env.
 * Solo es un modulo que renderiza una vista 'plantilla.tpl' vacia
 * de no tener esta vista, al entra en el modulo solo se mostrara un body vacio
 *
 * @author RODER Y JOSE
 */
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PrestaShop\Module\ExtraAddress\Controller\Admin\DevDistrictsController;
use PrestaShop\Module\ExtraAddress\Controller\Admin\DevProvincesController;
use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\Province;

if (!defined('_PS_VERSION_')) exit;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class ExtraAddress extends Module
{
    const DB_TABLE_ADDRESS = 'address';
    const DB_TABLE_ADDRESS_FORMAT = 'address_format';
    const DB_TABLE_DISTRICT = 'district';
    const COUNTRY_ID = Province::COUNTRY_ID;

    public function __construct()
    {
        $this->name = 'extraaddress';
        $this->tab = 'other';
        $this->author = 'Roder y Jose';
        $this->version = '1.0.0';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Extra address fields 🤘');
        $this->description = $this->l('Extra address fields 🤘');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        // $tabNames = [];
        // foreach (Language::getLanguages(true) as $lang) {
        //     $tabNames[$lang['locale']] = $this->trans('Extra Address Fields', [], 'Modules.ExtraAddressFields.Admin', $lang['locale']);
        // }

        // $this->tabs = [
        //     [
        //         'route_name' => 'ps_extra_address_provinces',
        //         'class_name' => AdminFieldsController::TAB_CLASS_NAME,
        //         'visible' => true,
        //         'name' => $tabNames,
        //         'icon' => 'store',
        //         'parent_class_name' => 'IMPROVE',
        //     ],
        // ];
    }

    public function install()
    {
        return (
            parent::install() &&
            $this->addExtraFieldToCustomerAddress() &&
            $this->createTableDistrict() &&
            $this->registerDistrictTab() &&
            $this->registerProvincesTab()
        );
    }

    public function uninstall()
    {
        return (
            parent::uninstall() &&
            $this->removeExtraFieldFromCustomerAddress() &&
            $this->removeTableDistrict() &&
            $this->unregisterDistrictTab() &&
            $this->unregisterProvincesTab()
        );
    }

    protected function addExtraFieldToCustomerAddress()
    {
        try {
            $addressFormat = Db::getInstance()->getRow(
                'SELECT * FROM ' . _DB_PREFIX_ . self::DB_TABLE_ADDRESS_FORMAT . ' WHERE id_country = ' . self::COUNTRY_ID
            );

            if (!$addressFormat) {
                return false;
            }

            $format = $addressFormat['format'];
            $newText = "\n" . 'id_district';
            $updatedFormat = $format . $newText;

            // Update address format
            $formatUpdated = Db::getInstance()->execute(
                'UPDATE ' . _DB_PREFIX_ . self::DB_TABLE_ADDRESS_FORMAT . ' SET format = \'' . pSQL($updatedFormat) .
                    '\' WHERE id_country = ' . self::COUNTRY_ID
            );

            // Modify ps_address table to add new fields
            $alterTableQuery = 'ALTER TABLE ' . _DB_PREFIX_ . self::DB_TABLE_ADDRESS .
                '
                ADD COLUMN id_district INT DEFAULT NULL
                ';

            $addressUpdated = Db::getInstance()->execute($alterTableQuery);

            return $formatUpdated && $addressUpdated;
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    protected function removeExtraFieldFromCustomerAddress()
    {
        try {

            $addressFormat = Db::getInstance()->getRow(
                'SELECT * FROM ' . _DB_PREFIX_ . self::DB_TABLE_ADDRESS_FORMAT . ' WHERE id_country = ' . self::COUNTRY_ID
            );

            if (!$addressFormat) {
                return false;
            }

            $format = $addressFormat['format'];
            $removedText = "\n" . 'id_district';
            $updatedFormat = str_replace($removedText, '', $format);

            // Update address format
            $formatRestored = Db::getInstance()->execute(
                'UPDATE ' . _DB_PREFIX_ . self::DB_TABLE_ADDRESS_FORMAT . ' SET format = \'' . pSQL($updatedFormat) .
                    '\' WHERE id_country = ' . self::COUNTRY_ID
            );

            // Drop columns from ps_address table
            $addressRestored = Db::getInstance()->execute(
                'ALTER TABLE ' . _DB_PREFIX_ . self::DB_TABLE_ADDRESS .
                    '
                DROP COLUMN id_district
                '
            );

            return $formatRestored && $addressRestored;
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    protected function createTableDistrict()
    {
        try {
            $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::DB_TABLE_DISTRICT . '` (
              `id_district` INT AUTO_INCREMENT PRIMARY KEY,
              `id_country` INT(11) NOT NULL,
              `id_state` INT(11) NOT NULL,
              `name` VARCHAR(255) NOT NULL,
              KEY `id_country` (`id_country`)
            ) ENGINE = InnoDB;';

            return Db::getInstance()->execute($sql);
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function removeTableDistrict()
    {   
        try {
            
            $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::DB_TABLE_DISTRICT . '`';
            return Db::getInstance()->execute($sql);
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function registerDistrictTab()
    {
        try {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = DevDistrictsController::TAB_CLASS_NAME;
            $tab->name = array();

            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = '[Distritos]';
            }

            $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentCountries');
            $tab->module = $this->name;

            return $tab->add();
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function unregisterDistrictTab()
    {
        try {
            $id_tab = (int) Tab::getIdFromClassName(DevDistrictsController::TAB_CLASS_NAME);
            $tab = new Tab($id_tab);
            return $tab->delete();
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

     public function registerProvincesTab()
    {
        try {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = DevProvincesController::TAB_CLASS_NAME;
            $tab->name = array();

            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = '[Provincias]';
            }

            $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentCountries');
            $tab->module = $this->name;

            return $tab->add();
        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function unregisterProvincesTab()
    {
        try {
            $id_tab = (int) Tab::getIdFromClassName(DevProvincesController::TAB_CLASS_NAME);
            $tab = new Tab($id_tab);
            return $tab->delete();

        } catch (Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink(DevDistrictsController::TAB_CLASS_NAME)
        );
    }
}
