<?php

namespace Annonces\Entities;

use Core\Storage\JsonActiveRecord;
use Core\Storage\StorageFactory;
use Core\Router\Router;

defined('ROOT') OR exit('Access denied!');

/**
 * Entité représentant une petite annonce créée par l'administrateur.
 */
class Annonce extends JsonActiveRecord
{
    public const VALID_STATUSES = ['available', 'reserved', 'sold', 'closed', 'draft'];
    public const MODERATION_PENDING = 'pending';
    public const MODERATION_APPROVED = 'approved';
    public const MODERATION_REJECTED = 'rejected';

    protected static string $filePath = DATA_PLUGIN . 'annonces/annonces.json';
    protected static string $tableName = 'annonces';
    protected static string $primaryKey = 'id';
    protected static bool $isBooted = false;

    protected static array $casts = [
        'is_featured' => 'int',
        'images'      => 'array',
        'user_id'     => 'int',
        'show_phone'  => 'int',
    ];

    protected static function boot(): void
    {
        if (static::$isBooted) {
            return;
        }
        parent::boot();

        // Migration douce : assurer la présence des colonnes si stockage SQL
        self::ensureColumns();
    }

    private static function ensureColumns(): void
    {
        try {
            $driver = StorageFactory::getDefaultDriver();
            if ($driver === 'sqlite' || $driver === 'mysql') {
                $conn = StorageFactory::getConnection();
                if ($conn instanceof \PDO) {
                    $existingCols = [];
                    if ($driver === 'sqlite') {
                        $stmt = $conn->query("PRAGMA table_info(annonces)");
                        if ($stmt) {
                            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $col) {
                                $existingCols[] = $col['name'] ?? '';
                            }
                        }
                    } else {
                        $stmt = $conn->query("SHOW COLUMNS FROM annonces");
                        if ($stmt) {
                            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $col) {
                                $existingCols[] = $col['Field'] ?? '';
                            }
                        }
                    }

                    $colsToAdd = [
                        'images'            => 'TEXT',
                        'user_id'           => 'INT NULL',
                        'user_name'         => 'VARCHAR(150) NULL',
                        'user_email'        => 'VARCHAR(180) NULL',
                        'moderation_status' => "VARCHAR(30) DEFAULT 'approved'",
                        'rejection_reason'  => 'TEXT NULL',
                        'city'              => 'VARCHAR(100) NULL',
                        'postal_code'       => 'VARCHAR(20) NULL',
                        'phone'             => 'VARCHAR(50) NULL',
                        'show_phone'        => 'TINYINT DEFAULT 0',
                    ];

                    foreach ($colsToAdd as $colName => $colDef) {
                        if (!in_array($colName, $existingCols, true)) {
                            @$conn->exec("ALTER TABLE annonces ADD COLUMN {$colName} {$colDef}");
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement si la table n'existe pas encore
        }
    }

    protected static function getSchema(): array
    {
        return [
            'title'             => 'string',
            'slug'              => 'string',
            'category'          => 'string',
            'price_label'       => 'string',
            'location'          => 'string',
            'city'              => 'string',
            'postal_code'       => 'string',
            'phone'             => 'string',
            'show_phone'        => 'int',
            'item_condition'    => 'string',
            'image'             => 'string',
            'images'            => 'text',
            'description'       => 'text',
            'contact_info'      => 'string',
            'user_id'           => 'int',
            'user_name'         => 'string',
            'user_email'        => 'string',
            'moderation_status' => 'string',
            'rejection_reason'  => 'string',
            'status'            => 'string',
            'is_featured'       => 'int',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
        ];
    }

    public function getId(): string|int
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Retourne la liste ordonnée de toutes les photos de l'annonce.
     * Rétrocompatible avec le champ unitaire `image`.
     *
     * @return string[]
     */
    public function getImages(): array
    {
        $raw = $this->attributes['images'] ?? [];
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $raw)));
        }
        if (!is_array($raw)) {
            $raw = [];
        }
        if (empty($raw) && !empty($this->attributes['image'])) {
            $raw = [(string) $this->attributes['image']];
        }
        return array_values(array_filter($raw, static fn($v) => is_string($v) && trim($v) !== ''));
    }

    /**
     * Retourne la photo principale de l'annonce.
     */
    public function getMainImage(): string
    {
        $imgs = $this->getImages();
        return $imgs[0] ?? (string) ($this->attributes['image'] ?? '');
    }

    public function isAvailable(): bool
    {
        return ($this->attributes['status'] ?? 'available') === 'available';
    }

    public function isReserved(): bool
    {
        return ($this->attributes['status'] ?? '') === 'reserved';
    }

    public function isSold(): bool
    {
        return ($this->attributes['status'] ?? '') === 'sold';
    }

    public function isApproved(): bool
    {
        $mod = $this->attributes['moderation_status'] ?? self::MODERATION_APPROVED;
        return $mod === self::MODERATION_APPROVED || $mod === '' || $mod === null;
    }

    public function isPendingModeration(): bool
    {
        return ($this->attributes['moderation_status'] ?? '') === self::MODERATION_PENDING;
    }

    public function isRejected(): bool
    {
        return ($this->attributes['moderation_status'] ?? '') === self::MODERATION_REJECTED;
    }

    public function isPublic(): bool
    {
        return $this->isApproved() && in_array($this->attributes['status'] ?? 'available', ['available', 'reserved', 'sold', 'closed'], true);
    }

    public function isAuthor(?\Core\Auth\User $user): bool
    {
        if (!$user) {
            return false;
        }
        $authorId = (int) ($this->attributes['user_id'] ?? 0);
        return $authorId > 0 && $authorId === (int) $user->id;
    }

    public function canEdit(?\Core\Auth\User $user): bool
    {
        if (!$user) {
            return false;
        }
        $evaluator = \Core\Navigation\AccessPolicyEvaluator::getInstance();
        $ctx = new \Core\Navigation\ConditionContext($user);
        if ($evaluator->isAdmin($ctx)) {
            return true;
        }
        return $this->isAuthor($user);
    }

    public function getStatusLabel(): string
    {
        $map = [
            'available' => 'Disponible',
            'reserved'  => 'Réservée',
            'sold'      => 'Vendue',
            'closed'    => 'Clôturée',
            'draft'     => 'Brouillon',
        ];
        $status = $this->attributes['status'] ?? 'available';
        return $map[$status] ?? $status;
    }

    public function getModerationLabel(): string
    {
        $map = [
            self::MODERATION_PENDING  => 'En attente de validation',
            self::MODERATION_APPROVED => 'Validée',
            self::MODERATION_REJECTED => 'Refusée',
        ];
        $mod = $this->attributes['moderation_status'] ?? self::MODERATION_APPROVED;
        return $map[$mod] ?? 'Validée';
    }

    public function getAuthorName(): string
    {
        $name = trim((string) ($this->attributes['user_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }
        return 'Membre';
    }

    public function getAuthorEmail(): string
    {
        return trim((string) ($this->attributes['user_email'] ?? ''));
    }

    public function getCity(): string
    {
        $city = trim((string) ($this->attributes['city'] ?? ''));
        if ($city !== '') {
            return $city;
        }
        return (string) ($this->attributes['location'] ?? '');
    }

    public function getPublicUrl(): string
    {
        $slug = trim((string) ($this->attributes['slug'] ?? ''));
        if ($slug === '') {
            $slug = 'annonce';
        }
        return Router::getInstance()->generate('annonces-view', [
            'id' => $this->getId(),
            'slug' => $slug,
        ]);
    }
}
