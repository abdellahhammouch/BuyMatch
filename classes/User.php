<?php

abstract class User
{
    protected $id_user;
    protected $nom_user;
    protected $prenom_user;
    protected $email_user;
    protected $phone_user;
    protected $photo_user;
    protected $role_user;
    protected $is_active;

    public function __construct($row = [])
    {
        $this->id_user     = $row["id_user"] ?? null;
        $this->nom_user    = $row["nom_user"] ?? null;
        $this->prenom_user = $row["prenom_user"] ?? null;
        $this->email_user  = $row["email_user"] ?? null;
        $this->phone_user  = $row["phone_user"] ?? null;
        $this->photo_user  = $row["photo_user"] ?? null;
        $this->role_user   = $row["role_user"] ?? null;
        $this->is_active   = $row["is_active"] ?? 1;
    }

    public function getId() { return $this->id_user; }
    public function getNom() { return $this->nom_user; }
    public function getPrenom() { return $this->prenom_user; }
    public function getEmail() { return $this->email_user; }
    public function getPhone() { return $this->phone_user; }
    public function getPhoto() { return $this->photo_user; }
    public function getRole() { return $this->role_user; }
    public function isActive() { return $this->is_active ? true : false; }

    public function getFullName()
    {
        return trim(($this->prenom_user ?? "") . " " . ($this->nom_user ?? ""));
    }
}
