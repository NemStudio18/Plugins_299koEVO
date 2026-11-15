<?php
namespace SocialFeed\Services\Providers;

interface FeedProviderInterface {

    /**
     * Identifiant unique de la source de contenu ('posts', 'blog', 'agenda', 'meteo', 'ecommerce').
     */
    public function getId(): string;

    /**
     * Titre lisible de la source pour les onglets/filtres.
     */
    public function getTitle(): string;

    /**
     * Classe d'icône FontAwesome pour la source.
     */
    public function getIcon(): string;

    /**
     * Détermine si ce fournisseur est disponible (plugin tiers installé et actif).
     */
    public function isAvailable(): bool;

    /**
     * Récupère une liste normalisée d'éléments pour le flux unifié.
     * Chaque élément doit comporter :
     * - 'type': string ('post', 'blog', 'event', 'meteo', 'product')
     * - 'id': string|int
     * - 'title': ?string
     * - 'content': string
     * - 'author_name': string
     * - 'author_avatar': ?string
     * - 'author_url': ?string
     * - 'media_url': ?string
     * - 'permalink': string
     * - 'created_at': string (Y-m-d H:i:s)
     * - 'timestamp': int
     * - 'likes_count': int
     * - 'comments_count': int
     * - 'has_liked': bool
     * - 'meta': array (données spécifiques supplémentaires)
     */
    public function fetchItems(?int $currentUserId, int $page = 1, int $limit = 20, array $options = []): array;
}
