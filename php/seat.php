<?php


class seat
{
    private $id;
    private $state;
    private $user_email;

    public function __construct(string $id ,string $state, string $user_email)
    {
        $this->id = $id;
        $this->state = $state;
        $this->user_email = $user_email;
    }


    public function getId() : string
    {
        return $this->id;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getUserEmail(): string
    {
        return $this->user_email;
    }
}