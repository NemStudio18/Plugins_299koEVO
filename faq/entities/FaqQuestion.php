<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class FaqQuestion
{
    private $id;
    private $question;
    private $answer;
    private $category;
    private $order;
    private $votes;
    private $fingerprints; // Hash de (User-Agent + IP) pour le vote
    private $date;
    private $active;
    private $email; // Email de l'auteur de la question (pour contact)

    public function __construct($val = array())
    {
        if (count($val) > 0) {
            $this->id = $val['id'] ?? null;
            $this->question = $val['question'] ?? '';
            $this->answer = $val['answer'] ?? '';
            $this->category = $val['category'] ?? '';
            $this->order = $val['order'] ?? 0;
            $this->votes = $val['votes'] ?? 0;
            $this->fingerprints = $val['fingerprints'] ?? [];
            $this->date = $val['date'] ?? date('Y-m-d H:i:s');
            $this->active = $val['active'] ?? '1';
            $this->email = $val['email'] ?? '';
        } else {
            $this->votes = 0;
            $this->fingerprints = [];
            $this->active = '1';
            $this->order = 0;
            $this->date = date('Y-m-d H:i:s');
            $this->email = '';
        }
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setQuestion($val)
    {
        $this->question = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setAnswer($val)
    {
        $this->answer = trim($val);
    }

    public function setCategory($val)
    {
        $this->category = trim(filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    public function setOrder($val)
    {
        $this->order = intval($val);
    }

    public function setVotes($val)
    {
        $this->votes = intval($val);
    }

    public function setFingerprints($val)
    {
        $this->fingerprints = is_array($val) ? $val : [];
    }

    public function setDate($val)
    {
        $this->date = $val;
    }

    public function setActive($val)
    {
        $this->active = $val ? '1' : '0';
    }

    public function setEmail($val)
    {
        $this->email = filter_var($val, FILTER_SANITIZE_EMAIL);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getVotes()
    {
        return $this->votes;
    }

    public function getFingerprints()
    {
        return $this->fingerprints;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function isActive()
    {
        return $this->active == '1';
    }

    public function hasVoted($fingerprint)
    {
        return in_array($fingerprint, $this->fingerprints);
    }

    public function addVote($fingerprint)
    {
        if (!$this->hasVoted($fingerprint)) {
            $this->votes++;
            $this->fingerprints[] = $fingerprint;
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category,
            'order' => $this->order,
            'votes' => $this->votes,
            'fingerprints' => $this->fingerprints,
            'date' => $this->date,
            'active' => $this->active,
            'email' => $this->email
        ];
    }
}

