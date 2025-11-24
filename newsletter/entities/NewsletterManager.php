<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class NewsletterManager
{
    private $dataFile;

    public function __construct()
    {
        $this->dataFile = DATA_PLUGIN . 'newsletter/subscribers.json';
        if (!is_dir(dirname($this->dataFile))) {
            mkdir(dirname($this->dataFile), 0755, true);
        }
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }

    public function getSubscribers()
    {
        $content = file_get_contents($this->dataFile);
        $content = trim($content, "\xEF\xBB\xBF");
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }
        $subscribers = [];
        foreach ($data as $sub) {
            $subscribers[] = new NewsletterSubscriber($sub);
        }
        return $subscribers;
    }

    public function getActiveSubscribers()
    {
        return array_filter($this->getSubscribers(), function($sub) {
            return $sub->isActive();
        });
    }

    public function findByEmail($email)
    {
        $subscribers = $this->getSubscribers();
        foreach ($subscribers as $sub) {
            if (strtolower($sub->getEmail()) === strtolower($email)) {
                return $sub;
            }
        }
        return null;
    }

    public function findByToken($token)
    {
        $subscribers = $this->getSubscribers();
        foreach ($subscribers as $sub) {
            if ($sub->getToken() === $token) {
                return $sub;
            }
        }
        return null;
    }

    public function create($id = null)
    {
        $subscribers = $this->getSubscribers();
        if ($id === null) {
            return new NewsletterSubscriber();
        }
        foreach ($subscribers as $sub) {
            if ($sub->getId() == $id) {
                return $sub;
            }
        }
        return null;
    }

    public function saveSubscriber($subscriber)
    {
        $subscribers = $this->getSubscribers();
        
        if ($subscriber->getId() === null) {
            // Nouvel abonné
            $maxId = 0;
            foreach ($subscribers as $sub) {
                if ($sub->getId() > $maxId) {
                    $maxId = $sub->getId();
                }
            }
            $subscriber->setId($maxId + 1);
            $subscriber->setDate(date('Y-m-d H:i:s'));
            $subscribers[] = $subscriber;
        } else {
            // Modification
            foreach ($subscribers as $key => $sub) {
                if ($sub->getId() == $subscriber->getId()) {
                    $subscribers[$key] = $subscriber;
                    break;
                }
            }
        }

        $data = [];
        foreach ($subscribers as $sub) {
            $data[] = $sub->toArray();
        }

        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    public function deleteSubscriber($id)
    {
        $subscribers = $this->getSubscribers();
        $newSubscribers = [];
        foreach ($subscribers as $sub) {
            if ($sub->getId() != $id) {
                $newSubscribers[] = $sub;
            }
        }

        $data = [];
        foreach ($newSubscribers as $sub) {
            $data[] = $sub->toArray();
        }

        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
}


