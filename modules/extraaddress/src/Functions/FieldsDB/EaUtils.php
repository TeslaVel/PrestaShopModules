<?php

declare(strict_types=1);

namespace PrestaShop\Module\ExtraAddress\Functions\FieldsDB;

use Module;

class EaUtils
{
  public function filteredFields($fillable_fields, $data)
  {
    if(empty($fillable_fields)) return null;
    if(empty($data)) return null;


    $dataArray = $data;
    $allowed = $fillable_fields;

    if(array_is_list($data)) {
      $dataArray = array_combine(array_values($data), array_keys($data));
    }

    $filtered_data = array_filter($dataArray, function ($key) use ($allowed) {
      return in_array($key, $allowed);
    }, ARRAY_FILTER_USE_KEY);

    return $filtered_data;
  }

  public function preparedData($fillable_fields, $data)
  {
    if(empty($fillable_fields)) return null;
    if(empty($data)) return null;

    $new_filtered_data = $this->filteredFields($fillable_fields, $data);
    $keys = array_keys($new_filtered_data);
    $values = array_map('pSQL', array_values($new_filtered_data));

    return [$keys, $values];
  }

  public function optionIze($id_name, $name, $records)
  {
      if(empty($id_name)) return [];
      if(empty($name)) return [];
      if(empty($records)) return [];
     
      $newArray = array_combine(array_column($records, $name), array_column($records, $id_name));
      return $newArray;
  }
}
