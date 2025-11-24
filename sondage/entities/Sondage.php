<?php

/**
 * @copyright (C) 2024, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class Sondage
{
    private $id;
    private $title;
    public $options = [];
    private $active;
    private $votes;
    private $date;
    private $closeDate;

    public function __construct($val = array())
    {
        if (count($val) > 0) {
            $this->id = $val['id'] ?? null;
            $this->title = $val['title'] ?? '';
            $this->options = $val['options'] ?? [];
            $this->active = $val['active'] ?? '0';
            $this->votes = $val['votes'] ?? [];
            $this->date = $val['date'] ?? date('Y-m-d H:i:s');
            $this->closeDate = $val['closeDate'] ?? null;
        }
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setTitle($val)
    {
        $this->title = trim($val);
    }

    public function setOptions($val)
    {
        $this->options = is_array($val) ? $val : [];
    }

    public function setActive($val)
    {
        $this->active = trim($val);
    }

    public function setVotes($val)
    {
        $this->votes = is_array($val) ? $val : [];
    }

    public function setDate($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d H:i:s');
        }
        $this->date = trim($val);
    }

    public function setCloseDate($val)
    {
        $this->closeDate = ($val === null || empty($val)) ? null : trim($val);
    }

    public function getCloseDate()
    {
        return $this->closeDate;
    }

    public function isClosed()
    {
        if ($this->closeDate === null || empty($this->closeDate)) {
            return false;
        }
        return strtotime($this->closeDate) < time();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getActive()
    {
        // Si le sondage a une date de clôture et qu'elle est dépassée, il n'est plus actif
        if ($this->isClosed()) {
            return false;
        }
        return $this->active == '1';
    }

    public function getVotes()
    {
        return $this->votes;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTotalVotes()
    {
        return count($this->votes);
    }

    public function getVotesForOption($optionIndex)
    {
        $count = 0;
        foreach ($this->votes as $vote) {
            if (isset($vote['option']) && $vote['option'] == $optionIndex) {
                $count++;
            }
        }
        return $count;
    }

    public function hasUserVoted($userId = null, $fingerprint = null)
    {
        foreach ($this->votes as $vote) {
            if ($userId && isset($vote['userId']) && $vote['userId'] == $userId) {
                return true;
            }
            if ($fingerprint && isset($vote['fingerprint']) && $vote['fingerprint'] === $fingerprint) {
                return true;
            }
        }
        return false;
    }

    public function addVote($optionIndex, $userId = null, $fingerprint = null, $email = null)
    {
        $this->votes[] = [
            'option' => intval($optionIndex),
            'userId' => $userId,
            'fingerprint' => $fingerprint,
            'email' => $email ? filter_var($email, FILTER_SANITIZE_EMAIL) : null,
            'date' => date('Y-m-d H:i:s')
        ];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'options' => $this->options,
            'active' => $this->active,
            'votes' => $this->votes,
            'date' => $this->date,
            'closeDate' => $this->closeDate
        ];
    }
}
