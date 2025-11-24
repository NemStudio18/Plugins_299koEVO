<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class GuestbookEntry
{
    private $id;
    private $name;
    private $email;
    private $message;
    public $approved;
    private $date;
    private $fingerprint; // Hash de (User-Agent + IP) au lieu de l'IP en clair
    private $likes;
    private $adminReply;
    private $adminReplyDate;
    private $adminReplyAuthor;

    public function __construct($val = array())
    {
        if (count($val) > 0) {
            $this->id = $val['id'] ?? null;
            $this->name = $val['name'] ?? '';
            $this->email = $val['email'] ?? '';
            $this->message = $val['message'] ?? '';
            $this->approved = $val['approved'] ?? '0';
            $this->date = $val['date'] ?? date('Y-m-d H:i:s');
            $this->fingerprint = $val['fingerprint'] ?? ($val['ip'] ?? ''); // Migration: ancien système avec 'ip'
            $this->likes = $val['likes'] ?? [];
            $this->adminReply = $val['adminReply'] ?? '';
            $this->adminReplyDate = $val['adminReplyDate'] ?? null;
            $this->adminReplyAuthor = $val['adminReplyAuthor'] ?? '';
        }
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setName($val)
    {
        $this->name = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setEmail($val)
    {
        $this->email = trim(filter_var($val, FILTER_SANITIZE_EMAIL));
    }

    public function setMessage($val)
    {
        $this->message = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setApproved($val)
    {
        $this->approved = trim($val);
    }

    public function setDate($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d H:i:s');
        }
        $this->date = trim($val);
    }

    public function setIp($val)
    {
        // Méthode dépréciée, utilisez setFingerprint() à la place
        $this->fingerprint = trim($val);
    }

    public function setFingerprint($val)
    {
        $this->fingerprint = trim($val);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getApproved()
    {
        return $this->approved;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getIp()
    {
        // Méthode dépréciée pour compatibilité
        return $this->fingerprint;
    }

    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    public function isApproved()
    {
        return $this->approved == '1';
    }

    public function setLikes($val)
    {
        $this->likes = is_array($val) ? $val : [];
    }

    public function getLikes()
    {
        return $this->likes;
    }

    public function getLikesCount()
    {
        return count($this->likes);
    }

    public function hasLiked($fingerprint)
    {
        return in_array($fingerprint, $this->likes);
    }

    public function addLike($fingerprint)
    {
        if (!$this->hasLiked($fingerprint)) {
            $this->likes[] = $fingerprint;
            return true;
        }
        return false;
    }

    public function removeLike($fingerprint)
    {
        $key = array_search($fingerprint, $this->likes);
        if ($key !== false) {
            unset($this->likes[$key]);
            $this->likes = array_values($this->likes);
            return true;
        }
        return false;
    }

    public function setAdminReply($val)
    {
        $this->adminReply = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function getAdminReply()
    {
        return $this->adminReply;
    }

    public function setAdminReplyDate($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d H:i:s');
        }
        $this->adminReplyDate = trim($val);
    }

    public function getAdminReplyDate()
    {
        return $this->adminReplyDate;
    }

    public function setAdminReplyAuthor($val)
    {
        $this->adminReplyAuthor = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function getAdminReplyAuthor()
    {
        return $this->adminReplyAuthor;
    }

    public function hasAdminReply()
    {
        return !empty($this->adminReply);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'approved' => $this->approved,
            'date' => $this->date,
            'fingerprint' => $this->fingerprint,
            'ip' => $this->fingerprint, // Compatibilité avec ancien système
            'likes' => $this->likes,
            'adminReply' => $this->adminReply,
            'adminReplyDate' => $this->adminReplyDate,
            'adminReplyAuthor' => $this->adminReplyAuthor
        ];
    }
}
