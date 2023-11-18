<?php

class Address extends AddressCore
{
    /** @var int */
    public static $id_district;

    public function __construct($id_address = null, $id_lang = null)
    {
        self::$definition['fields']['id_district'] = [
            'type' => self::TYPE_INT,
            'validate' => 'isNullOrUnsignedId'
        ];

        parent::__construct($id_address);
    }
}
