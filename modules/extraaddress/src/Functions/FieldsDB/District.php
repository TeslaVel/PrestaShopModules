<?php

declare(strict_types=1);

namespace PrestaShop\Module\ExtraAddress\Functions\FieldsDB;

use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\Province;
use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\EaUtils;

use Module;
use Db;

class District
{
  const DB_DISTRICT_TABLE_NAME = 'district';
  const COUNTRY_ID = Province::COUNTRY_ID;

  protected $fillable_fields = [
    'name',
    'id_state',
    'id_country'
  ];

  protected $protected_fields = ['id_district'];
  protected $primary_key = 'id_district';

  public $EaUtils;

  public function __construct()
  {
    $this->EaUtils = new EaUtils;
  }

  public function getAllDistricts($id_country = null, $limit = null)
  {
    $fields_array = array_merge($this->protected_fields, $this->fillable_fields);
    array_walk($fields_array, function (&$item1, $key, $alias) {
      $item1 = "$alias.$item1";
    }, 'district');

    $fields = implode(', ', $fields_array);

    $table_district = _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME;
    $table_state = _DB_PREFIX_ . 'state';
    $table_country = _DB_PREFIX_ . 'country_lang';

    $select = 'SELECT '. $fields. ', country.name as country, state.name as province';
    $from = ' FROM `' .$table_district. '` as district';
    $inner_state = ' INNER JOIN `'. $table_state .'` as state ON state.id_state = district.id_state';
    $inner_country = ' INNER JOIN `'. $table_country .'` as country ON country.id_country = district.id_country';

    $sql = $select . $from . $inner_state . $inner_country;

    if ($id_country !== null && filter_var($id_country, FILTER_VALIDATE_INT)) {
      $sql .= ' WHERE district.id_country=' . (int)$id_country .';';
    }

    if ($limit !== null && filter_var($limit, FILTER_VALIDATE_INT)) {
      $sql .= ' LIMIT ' . (int)$limit .';';
    }


    $results = Db::getInstance()->executeS($sql);
   
    return $results;
  }


  public function insertData($data)
  {
    $composed = $this->EaUtils->preparedData($this->fillable_fields, $data);
    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` (`' . implode('`, `', $keys) . '`) VALUES ("' . implode('", "', $values) . '")';

    Db::getInstance()->execute($sql);
    $id = (int)Db::getInstance()->Insert_ID();
    return $id;
  }

  public function updateData($data, $id_district)
  {
    if (empty($id_district)) return false;

    $composed = $this->EaUtils->preparedData($this->fillable_fields, $data);
    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'UPDATE `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` SET ';

    foreach ($keys as $index => $key) {
      if ($key == 'parent_id') {
        $sql .= '`' . $key . '` = "' . (int)$values[$index] . '", ';
      } else {
        $sql .= '`' . $key . '` = "' . pSQL($values[$index]) . '", ';
      }
    }

    $sql = rtrim($sql, ', ');
    $sql .= ' WHERE `'.$this->primary_key.'` = ' . (int)$id_district . ';';

    return Db::getInstance()->execute($sql);
  }

  public function getDistrictById($id_district)
  {
    if (empty($id_district)) return null;

    $fields = implode(', ', $this->protected_fields) . ',' . implode(', ', $this->fillable_fields);

    $sql = 'SELECT ' . $fields . ' FROM `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` WHERE `'.$this->primary_key.'` = ' . (int)$id_district;

    $results = Db::getInstance()->executeS($sql);

    if (empty($results)) null;

    return $results[0];
  }

  public function deleteDistrict($id_district)
  {
    if (empty($id_district)) return null;

    $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` WHERE `'.$this->primary_key.'` = ' . (int)$id_district . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

  public function getDistrictsByIdCountry($id_country)
  {
    if (empty($id_country)) return [];

    $fields = implode(', ', $this->protected_fields) . ',' . implode(', ', $this->fillable_fields);

    $sql = 'SELECT ' . $fields . ' FROM `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` WHERE id_country = ' . (int)$id_country;

    return Db::getInstance()->executeS($sql);
  }

  public function getFilteredFields($data)
  {
    $filtered = $this->EaUtils->filteredFields($this->fillable_fields, $data);
    return $filtered;
  }
}
