<?php
declare(strict_types=1);

namespace PrestaShop\Module\DevHorizontMembers\Functions\DevHzMembersDbFunctions;

use Module;
use Db;

class DevHzSql
{
  const DB_TABLE_NAME = 'dev_horizont_members';

  public function getAllMembers()
  {
    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'`';

    $results = Db::getInstance()->executeS($sql);

    return $results;
  }

  public function insertData($data) {
    $email = $data['email'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $age = $data['age'];


    $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::DB_TABLE_NAME.'` (`email`, `first_name`, `last_name`, `age`) VALUES ("'.pSQL($email). '", "' .pSQL($first_name). '", "' .pSQL($last_name). '", ' .(int)$age.')';

    Db::getInstance()->execute($sql);

    $id = (int)Db::getInstance()->Insert_ID();
    return $id;
  }

  public function updateData($data, $member_id) {
    $email = $data['email'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $age = $data['age'];

    $sql = 'UPDATE `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` SET `email` = "' . pSQL($email) . '", `first_name` = "' . pSQL($first_name) . '", `last_name` = "' . pSQL($last_name) . '", `age` = ' . (int)$age . ' WHERE `dev_horizont_member_id` = ' . (int)$member_id . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

  public function getMemberById($id)
  {
    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` WHERE `dev_horizont_member_id` = ' . (int)$id;

    $results = Db::getInstance()->executeS($sql);

    if (!empty($results)) {
      return $results[0];
    }

    return null;
  }

  public function deleteMember($id)
  {
    $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::DB_TABLE_NAME . '` WHERE `dev_horizont_member_id` = ' . (int)$id . ';';

    Db::getInstance()->execute($sql);

    return Db::getInstance()->Affected_Rows() > 0;
  }

}
