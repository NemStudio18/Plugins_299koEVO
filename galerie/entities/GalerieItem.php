<?php

namespace Galerie\Entities;

use Core\Storage\JsonActiveRecord;
use Utils\Util;

defined('ROOT') or exit('Access denied!');

/**
 * Élément de Galerie (Photo)
 */
class GalerieItem extends JsonActiveRecord
{
    protected static string $filePath = DATA_PLUGIN . 'galerie/galerie.json';
    protected static string $tableName = 'galerie_items';
    protected static string $primaryKey = 'id';
    protected static array $casts = [
        'hidden' => 'integer'
    ];

    public function __construct($data = [])
    {
        parent::__construct($data);
        if (empty($this->date)) {
            $this->date = date('Y-m-d');
        }
    }

    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getDate($short = false) {
        if ($short && is_string($this->date)) {
            return substr($this->date, 0, 10);
        }
        return $this->date;
    }
    public function getImg() { return $this->img; }
    public function getCategory() { return $this->category; }
    public function getHidden() { return $this->hidden; }

    public function getUrl(): string {
        return Util::urlBuild(UPLOAD . 'galerie/' . $this->img);
    }

    public function getRelUrl(): string {
        return '/data/upload/galerie/' . ltrim((string)$this->img, '/');
    }

    public function setId($val) { $this->id = $val; }
    public function setTitle($val) { $this->title = $val; }
    public function setContent($val) { $this->content = $val; }
    public function setDate($val) { $this->date = $val; }
    public function setImg($val) { $this->img = $val; }
    public function setCategory($val) { $this->category = $val; }
    public function setHidden($val) { $this->hidden = (int)$val; }
}
