<?php

declare(strict_types=1);

namespace PrestaShop\Module\ExtraAddress\Functions\FieldsDB;
use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\EaUtils;

use Module;
use Db;

class Province
{
  const DB_STATE_TABLE_NAME = 'state';
  const COUNTRY_ID = '169';

  public $fillable_fields = [
    'name',
    'id_country',
    'id_zone'
  ];

  protected $protected_fields = ['id_state'];
  protected $primary_key = 'id_state';

  public $EaUtils;

  public function __construct()
  {
    $this->EaUtils = new EaUtils;
  }

  public function getAllProvinces($id_country = null, $limit = null)
  {
    $fields = implode(', ', $this->protected_fields) .','. implode(', ', $this->fillable_fields);

    $sql = 'SELECT '. $fields .' FROM `' . _DB_PREFIX_ . self::DB_STATE_TABLE_NAME . '`';


    if ($id_country !== null && filter_var($id_country, FILTER_VALIDATE_INT)) {
      $sql .= ' WHERE id_country = ' . (int)$id_country;
    }

    if ($limit !== null && filter_var($limit, FILTER_VALIDATE_INT)) {
      $sql .= ' LIMIT ' . (int)$limit;
    }

    $results = Db::getInstance()->executeS($sql);

    return $results;
  }


  public function insertData($data)
  {
    $composed = $this->EaUtils->preparedData($this->fillable_fields, $data);
    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::DB_STATE_TABLE_NAME . '` (`' . implode('`, `', $keys) . '`) VALUES ("' . implode('", "', $values) . '")';

    Db::getInstance()->execute($sql);
    $id = (int)Db::getInstance()->Insert_ID();
    return $id;
  }

  public function updateData($data, $id_province)
  {
    if (empty($id_province)) return false;

    $composed = $this->EaUtils->preparedData($this->fillable_fields, $data);

    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'UPDATE `' . _DB_PREFIX_ . self::DB_STATE_TABLE_NAME . '` SET ';

    foreach ($keys as $index => $key) {
      if ($key == 'parent_id') {
        $sql .= '`' . $key . '` = "' . (int)$values[$index] . '", ';
      } else {
        $sql .= '`' . $key . '` = "' . pSQL($values[$index]) . '", ';
      }
    }

    $sql = rtrim($sql, ', ');
    $sql .= ' WHERE `'.$this->primary_key.'` = ' . (int)$id_province . ';';

    return Db::getInstance()->execute($sql);
  }

  public function getProvinceById($id_province)
  {
    if (empty($id_province)) return null;

    $fields = implode(', ', $this->protected_fields) . ',' . implode(', ', $this->fillable_fields);

    $sql = 'SELECT ' . $fields . ' FROM `' . _DB_PREFIX_ . self::DB_STATE_TABLE_NAME . '` WHERE `'.$this->primary_key.'` = ' . (int)$id_province;

    $results = Db::getInstance()->executeS($sql);

    if (empty($results)) null;

    return $results[0];
  }

  public function deleteProvince($id_province)
  {
    if (empty($id_province)) return null;

    $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::DB_STATE_TABLE_NAME . '` WHERE `'.$this->primary_key.'` = ' . (int)$id_province . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

  public function getFilteredFields($data)
  {
    $filtered = $this->EaUtils->filteredFields($this->fillable_fields, $data);
    return $filtered;
  }
}
