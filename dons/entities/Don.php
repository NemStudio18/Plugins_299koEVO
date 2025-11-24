<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class Don
{
    private $id;
    private $amount;
    private $firstName;
    private $lastName;
    private $email;
    private $message;
    private $gateway; // 'paypal' ou 'stripe'
    private $transactionId;
    private $status; // 'pending', 'completed', 'failed'
    private $date;
    private $anonymous;

    public function __construct($val = array())
    {
        if (count($val) > 0) {
            $this->id = $val['id'] ?? null;
            $this->amount = $val['amount'] ?? 0;
            $this->firstName = $val['firstName'] ?? '';
            $this->lastName = $val['lastName'] ?? '';
            $this->email = $val['email'] ?? '';
            $this->message = $val['message'] ?? '';
            $this->gateway = $val['gateway'] ?? '';
            $this->transactionId = $val['transactionId'] ?? '';
            $this->status = $val['status'] ?? 'pending';
            $this->date = $val['date'] ?? date('Y-m-d H:i:s');
            $this->anonymous = $val['anonymous'] ?? '0';
        } else {
            $this->status = 'pending';
            $this->date = date('Y-m-d H:i:s');
            $this->anonymous = '0';
        }
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setAmount($val)
    {
        $this->amount = floatval($val);
    }

    public function setFirstName($val)
    {
        $this->firstName = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setLastName($val)
    {
        $this->lastName = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setEmail($val)
    {
        $this->email = trim(filter_var($val, FILTER_SANITIZE_EMAIL));
    }

    public function setMessage($val)
    {
        $this->message = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setGateway($val)
    {
        $this->gateway = $val;
    }

    public function setTransactionId($val)
    {
        $this->transactionId = $val;
    }

    public function setStatus($val)
    {
        $this->status = $val;
    }

    public function setDate($val)
    {
        $this->date = $val;
    }

    public function setAnonymous($val)
    {
        $this->anonymous = $val ? '1' : '0';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function isAnonymous()
    {
        return $this->anonymous == '1';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'message' => $this->message,
            'gateway' => $this->gateway,
            'transactionId' => $this->transactionId,
            'status' => $this->status,
            'date' => $this->date,
            'anonymous' => $this->anonymous
        ];
    }
}



