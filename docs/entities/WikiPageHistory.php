<?php

namespace Docs\Entities;

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Maxime Blanc <maximeblanc@flexcb.fr>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class WikiPageHistory
{
    private $id;
    private $pageId;
    private $version;
    private $name;
    private $content;
    private $intro;
    private $seoDesc;
    private $modifiedBy;
    private $modifiedAt;
    private $changeDescription;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->pageId = $data['pageId'] ?? null;
        $this->version = $data['version'] ?? 1;
        $this->name = $data['name'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->intro = $data['intro'] ?? '';
        $this->seoDesc = $data['seoDesc'] ?? '';
        $this->modifiedBy = $data['modifiedBy'] ?? '';
        $this->modifiedAt = $data['modifiedAt'] ?? date('Y-m-d H:i:s');
        $this->changeDescription = $data['changeDescription'] ?? '';
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getPageId() {
        return $this->pageId;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getName() {
        return $this->name;
    }

    public function getContent() {
        return $this->content;
    }

    public function getIntro() {
        return $this->intro;
    }

    public function getSEODesc() {
        return $this->seoDesc;
    }

    public function getModifiedBy() {
        return $this->modifiedBy;
    }

    public function getModifiedAt() {
        return $this->modifiedAt;
    }

    public function getChangeDescription() {
        return $this->changeDescription;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setPageId($pageId) {
        $this->pageId = $pageId;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function setIntro($intro) {
        $this->intro = $intro;
    }

    public function setSEODesc($seoDesc) {
        $this->seoDesc = $seoDesc;
    }

    public function setModifiedBy($modifiedBy) {
        $this->modifiedBy = $modifiedBy;
    }

    public function setModifiedAt($modifiedAt) {
        $this->modifiedAt = $modifiedAt;
    }

    public function setChangeDescription($changeDescription) {
        $this->changeDescription = $changeDescription;
    }
} 