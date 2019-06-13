<?php

include_once 'seat.php';
class user
{
    private $email;
    private $seats;
    private $name;
    private $cnt;

    function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
        $this->seats = array();
        $this->cnt = 0;
    }

    function addSeat($seat){
        if(is_a($seat, 'seat'))
            throw new InvalidArgumentException("Parameter must be a seat Object");
        $this->seats[$this->cnt] = $seat;
        $this->cnt++;
    }

    public function getEmail(): string
    {
        return $this->email;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getSeats(): array
    {
        return $this->seats;
    }

    function __destruct()
    {
        unset($this->seats);
        unset($this->email);
        unset($this->cnt);
    }
}