<?php

require_once __DIR__ . "/User.php";

class Admin extends User
{
    public function __construct($row = [])
    {
        parent::__construct($row);
        $this->role_user = "admin";
    }
}
