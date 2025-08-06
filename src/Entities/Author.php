<?php
declare(strict_types= 1);

namespace App\Entities;

class Author{
    private int $id;
    private string $first_name;
    private string $last_name;
    private string $username;
    private string $email;
    private string $password;
    private string $orcid;
    private string $afiliation;
    public function __construct
    (int $id, 
    string $first_name, 
    string $last_name, 
    string $username, 
    string $email, 
    string $password, 
    string $orcid, 
    string $afiliation){
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->orcid = $orcid;
        $this->afiliation = $afiliation;
    }

    //Getters
    public function getId(): int{
        return $this->id;
    }
    public function getFirstName(): string{
        return $this->first_name;
    }
    public function getLastName(): string{
        return $this->last_name;
    }
    public function getUsername(): string{
        return $this->username;
    }
    public function getEmail(): string{
        return $this->email;
    }
    public function getPassword(): string{
        return $this->password;
    }
    public function getORcid(): string{
        return $this->orcid;
    }
    public function getAfiliation(): string{
        return $this->afiliation;
    }
    //Setters
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function setFirstName(string $first_name): void{
        $this->first_name = $first_name;
    }
    public function setLastName(string $last_name): void{
        $this->last_name = $last_name;
    }
    public function setUsername(string $username): void{
        $this->username = $username;
    }
    public function setPassword(string $plain): void{
        //$this->password = $password;
        $this->password = password_hash($plain, PASSWORD_BCRYPT);
    }
    public function setORcid(string $orcid): void{
        $this->orcid = $orcid;
    }
    public function setAfiliation(string $afiliation): void{
        $this->afiliation = $afiliation;
    }
}