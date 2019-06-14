<?php

include_once 'seat.php';
class user
{
    private $email;
    private $name;

    function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }


    public function getName(): string
    {
        return $this->name;
    }
}