<?php

namespace Game\Entities;

use Core\Storage\JsonActiveRecord;

defined('ROOT') or exit('Access denied!');

class GameScore extends JsonActiveRecord
{
    protected static string $tableName = 'game_scores';
    protected static string $filePath = DATA_PLUGIN . 'game/scores.json';
    protected static string $primaryKey = 'id';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getId(): int
    {
        return (int) ($this->attributes['id'] ?? 0);
    }

    public function getPlayerToken(): string
    {
        return (string) ($this->attributes['player_token'] ?? '');
    }

    public function getPlayerName(): string
    {
        return (string) ($this->attributes['player_name'] ?? 'ANON');
    }

    public function getScore(): int
    {
        return (int) ($this->attributes['score'] ?? 0);
    }

    public function getLines(): int
    {
        return (int) ($this->attributes['lines'] ?? 0);
    }

    public function getLevel(): int
    {
        return (int) ($this->attributes['level'] ?? 0);
    }

    public function getPalette(): string
    {
        return (string) ($this->attributes['palette'] ?? 'gbc_color');
    }

    public function getCreatedAt(): string
    {
        return (string) ($this->attributes['created_at'] ?? date('Y-m-d H:i:s'));
    }

    /**
     * Récupère le Top général (Hall of Fame)
     *
     * @param int $limit
     * @return array
     */
    public static function getTopScores(int $limit = 10): array
    {
        try {
            $all = self::all();
            usort($all, function ($a, $b) {
                return $b->getScore() <=> $a->getScore();
            });
            return array_slice($all, 0, $limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Récupère les meilleurs scores du jour
     *
     * @param int $limit
     * @return array
     */
    public static function getTodayTop(int $limit = 10): array
    {
        try {
            $today = date('Y-m-d');
            $all = self::all();
            $filtered = array_filter($all, function ($item) use ($today) {
                return str_starts_with($item->getCreatedAt(), $today);
            });
            usort($filtered, function ($a, $b) {
                return $b->getScore() <=> $a->getScore();
            });
            return array_slice($filtered, 0, $limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Récupère les meilleurs scores d'un joueur via son token
     *
     * @param string $token
     * @param int $limit
     * @return array
     */
    public static function getPlayerScores(string $token, int $limit = 5): array
    {
        if (empty($token)) {
            return [];
        }
        try {
            $all = self::all();
            $playerScores = array_filter($all, function ($item) use ($token) {
                return $item->getPlayerToken() === $token;
            });
            usort($playerScores, function ($a, $b) {
                return $b->getScore() <=> $a->getScore();
            });
            return array_slice($playerScores, 0, $limit);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
