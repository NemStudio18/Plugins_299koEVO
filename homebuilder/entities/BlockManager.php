<?php

namespace HomeBuilder\Entities;

use Utils\Util;

defined('ROOT') or exit('Access denied!');

class BlockManager
{
    private $configFile;
    private $blocks = [];

    public function __construct()
    {
        $this->configFile = DATA_PLUGIN . 'homebuilder/blocks.json';
        if (!file_exists($this->configFile)) {
            $default = PLUGINS . 'homebuilder/param/blocks.default.json';
            if (file_exists($default)) {
                @copy($default, $this->configFile);
            } else {
                Util::writeJsonFile($this->configFile, ['blocks' => []]);
            }
        }
        $this->loadBlocks();
    }

    /**
     * Charge les blocs depuis le fichier de configuration
     */
    private function loadBlocks()
    {
        $config = Util::readJsonFile($this->configFile, true);
        $this->blocks = [];
        
        
        if (isset($config['blocks']) && is_array($config['blocks'])) {
            foreach ($config['blocks'] as $blockData) {
                $this->blocks[] = new Block($blockData);
            }
        }
        
    }

    /**
     * Sauvegarde les blocs dans le fichier de configuration
     */
    private function saveBlocks()
    {
        // Debug: log avant sauvegarde
        foreach ($this->blocks as $block) {
        }
        
        // Créer la structure de base si le fichier n'existe pas
        $config = ['blocks' => []];
        
        // Essayer de lire le fichier existant
        $existingConfig = Util::readJsonFile($this->configFile, true);
        if (is_array($existingConfig)) {
            $config = $existingConfig;
        }
        
        // Mettre à jour les blocs
        $config['blocks'] = [];
        
        foreach ($this->blocks as $block) {
            $config['blocks'][] = $block->toArray();
        }
        
        // Debug: log avant écriture
        
        $result = Util::writeJsonFile($this->configFile, $config);
        
        return $result;
    }

    /**
     * Retourne tous les blocs actifs triés par ordre
     */
    public function getActiveBlocks()
    {
        $activeBlocks = array_filter($this->blocks, function($block) {
            return $block->isActive();
        });
        
        usort($activeBlocks, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });
        
        return $activeBlocks;
    }

    /**
     * Retourne tous les blocs (actifs et inactifs)
     */
    public function getAllBlocks()
    {
        usort($this->blocks, function($a, $b) {
            return $a->getOrder() - $b->getOrder();
        });
        
        return $this->blocks;
    }

    /**
     * Retourne un bloc par son ID
     */
    public function getBlock($id)
    {
        foreach ($this->blocks as $block) {
            if ($block->getId() === $id) {
                return $block;
            }
        }
        return null;
    }

    /**
     * Ajoute un nouveau bloc
     */
    public function addBlock($blockData)
    {
        $block = new Block($blockData);
        $this->blocks[] = $block;
        return $this->saveBlocks();
    }

    /**
     * Met à jour un bloc existant
     */
    public function updateBlock($id, $blockData)
    {
        // Debug: Afficher les données reçues
        
        foreach ($this->blocks as $key => $block) {
            if ($block->getId() === $id) {
                $this->blocks[$key] = new Block($blockData);
                
                // Debug: Vérifier les styles après création
                $newBlock = $this->blocks[$key];
                
                return $this->saveBlocks();
            }
        }
        return false;
    }

    /**
     * Supprime un bloc
     */
    public function deleteBlock($id)
    {
        // Debug: Afficher l'ID à supprimer
        
        foreach ($this->blocks as $key => $block) {
            if ($block->getId() === $id) {
                unset($this->blocks[$key]);
                $this->blocks = array_values($this->blocks); // Réindexer
                return $this->saveBlocks();
            }
        }
        
        return false;
    }

    /**
     * Active/désactive un bloc
     */
    public function toggleBlock($id)
    {
        foreach ($this->blocks as $block) {
            if ($block->getId() === $id) {
                $block->setActive(!$block->isActive());
                return $this->saveBlocks();
            }
        }
        return false;
    }

    /**
     * Change l'ordre des blocs
     */
    public function reorderBlocks($blockIds)
    {
        // Mettre à jour l'ordre des blocs spécifiés
        foreach ($blockIds as $index => $id) {
            $block = $this->getBlock($id);
            if ($block) {
                $block->setOrder($index + 1);
            }
        }
        
        // Préserver tous les blocs existants, pas seulement ceux dans blockIds
        return $this->saveBlocks();
    }

    /**
     * Génère l'ID unique pour un nouveau bloc
     */
    public function generateBlockId()
    {
        $existingIds = [];
        foreach ($this->blocks as $block) {
            $existingIds[] = $block->getId();
        }
        
        $counter = 1;
        $id = 'block_' . $counter;
        
        while (in_array($id, $existingIds)) {
            $counter++;
            $id = 'block_' . $counter;
        }
        
        return $id;
    }

    /**
     * Retourne les blocs parents (sans parent)
     */
    public function getParentBlocks()
    {
        return array_filter($this->blocks, function($block) {
            return $block->getParentId() === null;
        });
    }

    /**
     * Retourne les blocs enfants d'un bloc parent
     */
    public function getChildBlocks($parentId)
    {
        return array_filter($this->blocks, function($block) use ($parentId) {
            return $block->getParentId() === $parentId;
        });
    }

    /**
     * Retourne tous les blocs disponibles comme parents potentiels
     */
    public function getAvailableParents($excludeId = null)
    {
        $parents = [];
        foreach ($this->blocks as $block) {
            if ($block->isContainer() && $block->getId() !== $excludeId) {
                $parents[] = [
                    'id' => $block->getId(),
                    'title' => $block->getTitle(),
                    'type' => $block->getType()
                ];
            }
        }
        return $parents;
    }

    /**
     * Vérifie si un bloc peut être déplacé dans un autre bloc
     */
    public function canMoveBlock($blockId, $newParentId)
    {
        // Un bloc ne peut pas être son propre parent
        if ($blockId === $newParentId) {
            return false;
        }
        
        // Vérifier qu'on ne crée pas de cycle
        $currentParent = $newParentId;
        while ($currentParent) {
            $parentBlock = $this->getBlock($currentParent);
            if (!$parentBlock) {
                break;
            }
            if ($parentBlock->getParentId() === $blockId) {
                return false; // Cycle détecté
            }
            $currentParent = $parentBlock->getParentId();
        }
        
        return true;
    }

    /**
     * Déplace un bloc dans un autre bloc
     */
    public function moveBlock($blockId, $newParentId)
    {
        if (!$this->canMoveBlock($blockId, $newParentId)) {
            return false;
        }
        
        $block = $this->getBlock($blockId);
        if ($block) {
            $block->setParentId($newParentId);
            return $this->saveBlocks();
        }
        
        return false;
    }

    /**
     * Retourne les types de blocs disponibles
     */
    public function getAvailableBlockTypes()
    {
        return [
            'hero' => 'Bannière Hero',
            'text' => 'Texte & Titre',
            'columns' => 'Grille d\'avantages',
            'button' => 'Bouton d\'action (CTA)',
            'latest_articles' => 'Derniers articles (Blog)',
            'searchmaps_map' => 'Carte interactive (Commerces & Lieux)',
            'prordv_cta' => 'Prise de rendez-vous (ProRdv)',
            'latest_sondages' => 'Sondages actifs',
            'guestbook_cta' => 'Livre d\'or & Avis',
            'image' => 'Image / Visuel',
            'form' => 'Formulaire de contact',
            'table' => 'Tableau de données',
            'html' => 'Code HTML personnalisé',
            'container' => 'Conteneur'
        ];
    }

    /**
     * Métadonnées riches pour l'interface visuelle (icône, couleur, catégorie, description)
     */
    public function getBlockTypeMeta(): array
    {
        return [
            'hero' => [
                'label' => 'Bannière Hero',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'color' => '#8b5cf6',
                'category' => 'Présentation',
                'desc' => 'Grand titre percutant, sous-titre, image d\'accroche et boutons d\'action.'
            ],
            'text' => [
                'label' => 'Texte & Titre',
                'icon' => 'fa-solid fa-align-left',
                'color' => '#0ea5e9',
                'category' => 'Contenu',
                'desc' => 'Paragraphes rédigés, sous-titres de section et listes à puces.'
            ],
            'columns' => [
                'label' => 'Grille d\'avantages',
                'icon' => 'fa-solid fa-table-columns',
                'color' => '#3b82f6',
                'category' => 'Présentation',
                'desc' => 'Présentation en 3 ou 4 colonnes avec icônes, titres et descriptions.'
            ],
            'button' => [
                'label' => 'Bouton d\'action (CTA)',
                'icon' => 'fa-solid fa-arrow-pointer',
                'color' => '#ec4899',
                'category' => 'Conversion',
                'desc' => 'Bouton cliquable mis en valeur menant vers une offre ou inscription.'
            ],
            'latest_articles' => [
                'label' => 'Derniers articles (Blog)',
                'icon' => 'fa-solid fa-newspaper',
                'color' => '#0284c7',
                'category' => 'Modules',
                'desc' => 'Affiche automatiquement les publications récentes du module Blog.'
            ],
            'searchmaps_map' => [
                'label' => 'Carte interactive (Lieux)',
                'icon' => 'fa-solid fa-map-location-dot',
                'color' => '#10b981',
                'category' => 'Modules',
                'desc' => 'Aperçu ou intégration de la carte interactive des établissements.'
            ],
            'prordv_cta' => [
                'label' => 'Réservation RDV Pro',
                'icon' => 'fa-solid fa-calendar-check',
                'color' => '#f59e0b',
                'category' => 'Modules',
                'desc' => 'Bannière invitant les visiteurs à réserver un rendez-vous en direct.'
            ],
            'latest_sondages' => [
                'label' => 'Sondage en cours',
                'icon' => 'fa-solid fa-square-poll-vertical',
                'color' => '#6366f1',
                'category' => 'Modules',
                'desc' => 'Affiche les sondages actifs pour recueillir l\'avis des visiteurs.'
            ],
            'guestbook_cta' => [
                'label' => 'Livre d\'or & Avis',
                'icon' => 'fa-solid fa-book-open',
                'color' => '#eab308',
                'category' => 'Modules',
                'desc' => 'Bouton et message incitant les visiteurs à laisser un témoignage.'
            ],
            'image' => [
                'label' => 'Image / Visuel',
                'icon' => 'fa-regular fa-image',
                'color' => '#14b8a6',
                'category' => 'Médias',
                'desc' => 'Photo ou illustration pleine largeur avec légende.'
            ],
            'form' => [
                'label' => 'Formulaire',
                'icon' => 'fa-solid fa-envelope',
                'color' => '#059669',
                'category' => 'Conversion',
                'desc' => 'Formulaire simple pour collecter des messages ou des coordonnées.'
            ],
            'table' => [
                'label' => 'Tableau de données',
                'icon' => 'fa-solid fa-table',
                'color' => '#64748b',
                'category' => 'Contenu',
                'desc' => 'Tableau comparatif ou grille tarifaire structurée.'
            ],
            'html' => [
                'label' => 'Code HTML libre',
                'icon' => 'fa-solid fa-code',
                'color' => '#475569',
                'category' => 'Avancé',
                'desc' => 'Intégration de code HTML brut, widget externe ou iframe.'
            ],
            'container' => [
                'label' => 'Conteneur',
                'icon' => 'fa-solid fa-box',
                'color' => '#6b7280',
                'category' => 'Structure',
                'desc' => 'Section regroupant plusieurs sous-blocs dans un même cadre.'
            ]
        ];
    }

    /**
     * Retourne les couleurs disponibles pour les boutons
     */
    public function getAvailableColors()
    {
        return [
            'primary' => 'Primaire',
            'secondary' => 'Secondaire',
            'success' => 'Succès',
            'danger' => 'Danger',
            'warning' => 'Attention',
            'info' => 'Information',
            'light' => 'Clair',
            'dark' => 'Sombre'
        ];
    }

    /**
     * Retourne les tailles disponibles pour les boutons
     */
    public function getAvailableSizes()
    {
        return [
            'small' => 'Petit',
            'medium' => 'Moyen',
            'large' => 'Grand'
        ];
    }

    /**
     * Valide les données d'un bloc
     */
    public function validateBlockData($data)
    {
        $errors = [];
        
        if (empty($data['id'])) {
            $errors[] = 'ID du bloc requis';
        }
        
        if (empty($data['type'])) {
            $errors[] = 'Type de bloc requis';
        }
        
        if (!in_array($data['type'], array_keys($this->getAvailableBlockTypes()))) {
            $errors[] = 'Type de bloc invalide';
        }
        
        if (empty($data['title'])) {
            $errors[] = 'Titre requis';
        }
        
        return $errors;
    }
} 
