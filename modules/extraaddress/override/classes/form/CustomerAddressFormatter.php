<?php

declare(strict_types=1);

use PrestaShop\Module\ExtraAddress\Functions\FieldsDB\District;

class CustomerAddressFormatter extends CustomerAddressFormatterCore
{
    private $id_district = "id_district";

    private $id_department = "id_department";

    public function getFieldsList()
    {
        return [$this->id_department, $this->id_district];
    }

    public function getFormat()
    {
        $format = parent::getFormat();
        foreach ($format as $key => $value) {
            if (in_array($key, $this->getFieldsList())) {
                $format[$key]->setType("select");
            }
            if ($key === $this->id_department) {
                if ($this->getCountry()->contains_states) {
                    // $format[$key]->setName("id_department");
                    $format[$key]->setLabel("Departamento");
                    $states = State::getStatesByIdCountry($this->getCountry()->id, true, 'name', 'asc');
                    foreach ($states as $state) {
                        $format[$key]->addAvailableValue(
                            $state['id_state'],
                            $state["name"]
                        );
                    }
                }
            }

            if ($key === $this->id_district) {
                if ($this->getCountry()->contains_states) {
                    // $format[$key]->setName("id_district");
                    $format[$key]->setLabel("Distrito");
                    // unset($format['district']);
                    $districtObj = new District;
                    $districts = $districtObj->getDistrictsByIdCountry($this->getCountry()->id);
                    foreach ($districts as $district) {
                        $format[$key]->addAvailableValue(
                            $district['id_district'],
                            $district["name"]
                        );
                    }
                }
            }
        }
        return $format;
    }
}
