<?php
declare(strict_types=1);

namespace PrestaShop\Module\DevHorizontMembers\Functions\DevHzMembersDbFunctions;

use Module;
use Db;

class DevHzSql
{
  const DB_TABLE_NAME = 'dev_horizont_members';

  public $allowed_fields = [
    'email',
    'first_name',
    'last_name',
    'age'
  ];

  protected $protected_fields = ['id'];

  protected function filter_fields($data) {
    $allowed = $this->allowed_fields;

    return $filtered_data = array_filter($data, function($key) use ($allowed) {
      return in_array($key, $allowed);
    }, ARRAY_FILTER_USE_KEY);

    return $filtered_data;
  }

  protected function prepare_data($data) {
    $new_filtered_data = $this->filter_fields($data);
    $keys = array_keys($new_filtered_data);
    $values = array_map('pSQL', array_values($new_filtered_data));

    return [$keys, $values];
  }

  public function getAllMembers($limit = null)
  {
    $fields = implode(', ', $this->protected_fields) .','. implode(', ', $this->allowed_fields);

    $sql = 'SELECT '. $fields .' FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '`';


    if ($limit !== null && filter_var($limit, FILTER_VALIDATE_INT)) {
      $sql .= ' LIMIT ' . (int)$limit;
    }

    $results = Db::getInstance()->executeS($sql);

    return $results;
  }

  public function insertData($data) {
    $composed = $this->prepare_data($data);
    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` (`' . implode('`, `', $keys) . '`) VALUES ("' . implode('", "', $values) . '")';

    Db::getInstance()->execute($sql);
    $id = (int)Db::getInstance()->Insert_ID();
    return $id;
  }

  public function updateData($data, $member_id) {
    if (empty($id)) return false;

    $composed = $this->prepare_data($data);

    $keys = $composed[0];
    $values = $composed[1];

    $sql = 'UPDATE `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` SET ';

    foreach ($keys as $index => $key) {
      if ($key == 'age') {
        $sql .= '`' . $key . '` = "' . (int)$values[$index] . '", ';
      } else {
        $sql .= '`' . $key . '` = "' . pSQL($values[$index]) . '", ';
      }
    }

    $sql = rtrim($sql, ', ');
    $sql .= ' WHERE `id` = ' . (int)$member_id . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

  public function getMemberById($id)
  {
    if (empty($id)) return null;

    $fields = implode(', ', $this->protected_fields) .','. implode(', ', $this->allowed_fields);

    $sql = 'SELECT ' . $fields . ' FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` WHERE `id` = ' . (int)$id;

    $results = Db::getInstance()->executeS($sql);

    if (empty($results)) null;

    return $results[0];
  }

  public function deleteMember($id)
  {
    $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` WHERE `id` = ' . (int)$id . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

}
