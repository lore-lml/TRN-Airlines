<?php


class seat
{
    private $id;
    private $state;

    public function __construct($id , $state)
    {
        if(!is_string($id) || !is_string($state)){
            throw new InvalidArgumentException("Parameters must be strings");
        }

        $this->id = $id;
        $this->state = $state;
    }


    public function getId() : string
    {
        return $this->id;
    }


    public function getState(): string
    {
        return $this->state;
    }

    function __destruct()
    {
        unset($this->id);
        unset($this->state);
    }

}