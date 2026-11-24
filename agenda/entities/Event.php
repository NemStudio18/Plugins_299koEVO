<?php

namespace Agenda\Entities;

use Core\Storage\JsonActiveRecord;

defined('ROOT') or exit('Access denied!');

/**
 * @property int $offer_id
 */
class Event extends JsonActiveRecord
{
    protected static string $tableName = 'agenda_events';
    protected static string $filePath = DATA_PLUGIN . 'agenda/events.json';
    protected static string $primaryKey = 'id';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function getId()
    {
        return $this->attributes['id'] ?? null;
    }

    public function getTitle(): string
    {
        return $this->attributes['title'] ?? '';
    }

    public function getDescription(): string
    {
        return $this->attributes['description'] ?? '';
    }

    public function getStartDate(): string
    {
        return $this->attributes['start_date'] ?? '';
    }

    public function getEndDate(): string
    {
        return $this->attributes['end_date'] ?? '';
    }

    public function getLocation(): string
    {
        return $this->attributes['location'] ?? '';
    }

    public function getColor(): string
    {
        return $this->attributes['color'] ?? '#3498db';
    }

    public function getSlug(): string
    {
        $title = $this->getTitle() ?? '';
        // Décoder les entités HTML en amont pour éviter les artefacts type "-hellip-"
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remplacer les caractères non-lettres/chiffres par des tirets
        $slug = preg_replace('~[^\pL\d]+~u', '-', $title);
        if (function_exists('iconv')) {
            $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        }
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);
        return empty($slug) ? 'evenement' : $slug;
    }

    public function getUrl(): string
    {
        $slug = $this->getSlug();
        return '/agenda/event/' . $slug . '-' . $this->id;
    }

    public function getStatus(): string
    {
        return $this->attributes['status'] ?? 'approved';
    }

    public function isApproved(): bool
    {
        return $this->getStatus() === 'approved';
    }

    public function getCreatedBy(): ?int
    {
        return isset($this->attributes['created_by']) ? (int)$this->attributes['created_by'] : null;
    }

    public function getPoiId(): ?int
    {
        return isset($this->attributes['poi_id']) ? (int)$this->attributes['poi_id'] : null;
    }

    public function getProofFile(): ?string
    {
        return $this->attributes['proof_file'] ?? null;
    }

    public function getLatitude(): ?float
    {
        return isset($this->attributes['latitude']) ? (float)$this->attributes['latitude'] : null;
    }

    public function getLongitude(): ?float
    {
        return isset($this->attributes['longitude']) ? (float)$this->attributes['longitude'] : null;
    }

    public function getImageUrl(): ?string
    {
        $img = $this->attributes['image_url'] ?? null;
        if (!empty($img)) {
            return $img;
        }
        $proof = $this->attributes['proof_file'] ?? null;
        if (!empty($proof)) {
            if (strpos($proof, 'http') === 0 || strpos($proof, '/') === 0) {
                return $proof;
            }
            return '/data/upload/' . $proof;
        }
        return null;
    }

    public function getExternalUrl(): ?string
    {
        return $this->attributes['external_url'] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getOfferId(): ?int
    {
        return isset($this->attributes['offer_id']) ? (int)$this->attributes['offer_id'] : null;
    }

    public function setOfferId(?int $offerId): self
    {
        $this->attributes['offer_id'] = $offerId;
        return $this;
    }
}

