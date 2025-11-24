<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class NewsletterSubscriber
{
    private $id;
    private $email;
    private $date;
    private $token;
    private $active;

    public function __construct($val = array())
    {
        if (count($val) > 0) {
            $this->id = $val['id'] ?? null;
            $this->email = $val['email'] ?? '';
            $this->date = $val['date'] ?? date('Y-m-d H:i:s');
            $this->token = $val['token'] ?? $this->generateToken();
            $this->active = $val['active'] ?? '1';
        } else {
            $this->token = $this->generateToken();
            $this->active = '1';
        }
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setEmail($val)
    {
        $this->email = trim(filter_var($val, FILTER_SANITIZE_EMAIL));
    }

    public function setDate($val)
    {
        $this->date = $val;
    }

    public function setToken($val)
    {
        $this->token = $val;
    }

    public function setActive($val)
    {
        $this->active = $val ? '1' : '0';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function isActive()
    {
        return $this->active == '1';
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'date' => $this->date,
            'token' => $this->token,
            'active' => $this->active
        ];
    }
}


