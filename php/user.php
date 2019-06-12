<?php

include_once 'seat.php';
class user
{
    private $email;
    private $seats;
    private $cnt;

    function __construct($email)
    {
        $this->email = $email;
        $this->seats = array();
        $this->cnt = 0;
    }

    function addSeat($seat){
        if(is_a($seat, 'seat'))
            throw new InvalidArgumentException("Parameter must be a seat Object");
        $this->seats[$this->cnt] = $seat;
        $this->cnt++;
    }

    public function getEmail()
    {
        return $this->email;
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