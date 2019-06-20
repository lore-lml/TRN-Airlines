<?php

include_once 'seat.php';
class user
{
    private $email;
    private $name;

    function __construct(string $email)
    {
        $this->email = $email;

        $split = preg_split("/@/", $email);
        $this->name = $split[0];
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