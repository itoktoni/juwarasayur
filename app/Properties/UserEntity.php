<?php

namespace App\Properties;

trait UserEntity
{
    public static function field_email()
    {
        return 'email';
    }

    public function getFieldEmailAttribute()
    {
        return $this->{static::field_email()};
    }

    public static function field_address()
    {
        return 'address';
    }

    public function getFieldAddressAttribute()
    {
        return $this->{static::field_address()};
    }
}
