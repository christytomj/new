<?php

class HandsOn_User
{
    public $email;
    public $name;
    public $id;

    public function __construct($data = null)
    {
        if (isset($data)) {
            if (!is_array($data)) {
                throw new Exception('Initial data must be an array or object');
            }
    
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}