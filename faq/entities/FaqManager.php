<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

class FaqManager
{
    private $dataFile;

    public function __construct()
    {
        $this->dataFile = DATA_PLUGIN . 'faq/questions.json';
        if (!is_dir(dirname($this->dataFile))) {
            mkdir(dirname($this->dataFile), 0755, true);
        }
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }

    public function getQuestions()
    {
        $content = file_get_contents($this->dataFile);
        $content = trim($content, "\xEF\xBB\xBF");
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }
        $questions = [];
        foreach ($data as $q) {
            $questions[] = new FaqQuestion($q);
        }
        return $questions;
    }

    public function getActiveQuestions()
    {
        $questions = $this->getQuestions();
        return array_filter($questions, function($q) {
            return $q->isActive();
        });
    }

    public function getQuestionsByCategory($category = '')
    {
        $questions = $this->getActiveQuestions();
        if (empty($category)) {
            return array_values($questions);
        }
        $filtered = array_filter($questions, function($q) use ($category) {
            return $q->getCategory() === $category;
        });
        // Trier par ordre puis par votes
        usort($filtered, function($a, $b) {
            if ($a->getOrder() != $b->getOrder()) {
                return $a->getOrder() - $b->getOrder();
            }
            return $b->getVotes() - $a->getVotes();
        });
        return array_values($filtered);
    }

    public function getCategories()
    {
        $questions = $this->getActiveQuestions();
        $categories = [];
        foreach ($questions as $q) {
            $cat = $q->getCategory();
            if (!empty($cat) && !in_array($cat, $categories)) {
                $categories[] = $cat;
            }
        }
        sort($categories);
        return $categories;
    }

    public function create($id = null)
    {
        $questions = $this->getQuestions();
        if ($id === null) {
            return new FaqQuestion();
        }
        foreach ($questions as $q) {
            if ($q->getId() == $id) {
                return $q;
            }
        }
        return null;
    }

    public function saveQuestion($question)
    {
        $questions = $this->getQuestions();
        
        if ($question->getId() === null) {
            // Nouvelle question
            $maxId = 0;
            foreach ($questions as $q) {
                if ($q->getId() > $maxId) {
                    $maxId = $q->getId();
                }
            }
            $question->setId($maxId + 1);
            $question->setDate(date('Y-m-d H:i:s'));
            $questions[] = $question;
        } else {
            // Modification
            foreach ($questions as $key => $q) {
                if ($q->getId() == $question->getId()) {
                    // Préserver les votes et fingerprints
                    $question->setVotes($q->getVotes());
                    $question->setFingerprints($q->getFingerprints());
                    $questions[$key] = $question;
                    break;
                }
            }
        }

        $data = [];
        foreach ($questions as $q) {
            $data[] = $q->toArray();
        }

        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    public function deleteQuestion($id)
    {
        $questions = $this->getQuestions();
        $newQuestions = [];
        foreach ($questions as $q) {
            if ($q->getId() != $id) {
                $newQuestions[] = $q;
            }
        }

        $data = [];
        foreach ($newQuestions as $q) {
            $data[] = $q->toArray();
        }

        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
}



