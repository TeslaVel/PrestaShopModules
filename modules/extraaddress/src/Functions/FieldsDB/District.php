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
    'name'
  ];

  protected $fillable_fks = [
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

  protected function getFillableFields()
  {
    return array_merge($this->fillable_fks, $this->fillable_fields);
  }

  public function getAllDistricts($id_country = null, $id_state = null, $limit = null)
  {
    $fields_array = array_merge($this->protected_fields, $this->getFillableFields());
    array_walk($fields_array, function (&$field, $key, $alias) {
      $field = "$alias.$field";
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


    if ($id_country !== null || $id_state != null) {
      $sql .= ' WHERE 1';
    }

    if ($id_country !== null && $id_country !== 'all' && filter_var($id_country, FILTER_VALIDATE_INT)) {
      $sql .= ' AND district.id_country=' . (int)$id_country .'';
    }

    if ($id_state !== null && $id_state !== 'all' && filter_var($id_state, FILTER_VALIDATE_INT)) {
      $sql .= ' AND district.id_state=' . (int)$id_state .'';
    }

    if ($limit !== null && filter_var($limit, FILTER_VALIDATE_INT)) {
      $sql .= ' LIMIT ' . (int)$limit .'';
    }

    $sql .= ';';

    $results = Db::getInstance()->executeS($sql);
   
    return $results;
  }


  public function insertData($data)
  {
    $fields_array = $this->getFillableFields();
    $composed = $this->EaUtils->preparedData($fields_array, $data);
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

    $fields_array = $this->getFillableFields();;
    $composed = $this->EaUtils->preparedData($fields_array, $data);
    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'UPDATE `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` SET ';

    foreach ($keys as $index => $key) {
      if (in_array($key, $this->fillable_fks)) {
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

    $fields = implode(', ', $this->protected_fields) . ',' . implode(', ', $this->getFillableFields());

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

    $fields = implode(', ', $this->protected_fields) . ',' . implode(', ', $this->getFillableFields());

    $sql = 'SELECT ' . $fields . ' FROM `' . _DB_PREFIX_ . self::DB_DISTRICT_TABLE_NAME . '` WHERE id_country = ' . (int)$id_country;

    return Db::getInstance()->executeS($sql);
  }

  public function getFilteredFields($data)
  {
    $filtered = $this->EaUtils->filteredFields($this->getFillableFields(), $data);
    return $filtered;
  }
}
