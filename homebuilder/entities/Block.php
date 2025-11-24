<?php

namespace HomeBuilder\Entities;

use Core\Lang;
use Core\Plugin\PluginsManager;
use Core\Router\Router;
use Utils\Util;

defined('ROOT') or exit('Access denied!');

class Block
{
    private $id;
    private $type;
    private $title;
    private $content;
    private $order;
    private $active;
    private $options;
    private $parentId; // Pour les blocs imbriqués
    private $children; // Sous-blocs
    private $styles; // Styles CSS fins

    public function __construct($data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->type = $data['type'] ?? 'text';
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->order = $data['order'] ?? 1;
        $this->active = $data['active'] ?? true;
        $this->options = $data['options'] ?? [];
        $this->parentId = $data['parentId'] ?? null;
        $this->children = $data['children'] ?? [];
        
        // Gestion spéciale des styles
        if (isset($data['styles'])) {
            $this->styles = $data['styles'];
        } else {
            $this->styles = $this->getDefaultStyles();
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getType() { return $this->type; }
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getOrder() { return $this->order; }
    public function isActive() { return $this->active; }
    public function getOptions() { return $this->options; }
    public function getParentId() { return $this->parentId; }
    public function getChildren() { return $this->children; }
    public function getStyles() { return $this->styles; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setType($type) { $this->type = $type; }
    public function setTitle($title) { $this->title = $title; }
    public function setContent($content) { $this->content = $content; }
    public function setOrder($order) { $this->order = $order; }
    public function setActive($active) { $this->active = $active; }
    public function setOptions($options) { $this->options = $options; }
    public function setParentId($parentId) { $this->parentId = $parentId; }
    public function setChildren($children) { $this->children = $children; }
    public function setStyles($styles) { $this->styles = $styles; }

    // Méthodes utilitaires
    public function toArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'order' => $this->order,
            'active' => $this->active,
            'options' => $this->options,
            'parentId' => $this->parentId,
            'children' => $this->children,
            'styles' => $this->styles
        ];
    }

    // Styles par défaut
    private function getDefaultStyles()
    {
        return [
            'container' => [
                'backgroundColor' => '#ffffff',
                'color' => '#333333',
                'padding' => '20px',
                'margin' => '10px 0',
                'borderRadius' => '8px',
                'border' => '1px solid #e0e0e0',
                'boxShadow' => '0 2px 4px rgba(0,0,0,0.1)',
                'width' => '100%',
                'maxWidth' => 'none',
                'textAlign' => 'left'
            ],
            'title' => [
                'color' => '#333333',
                'fontSize' => '24px',
                'fontWeight' => 'bold',
                'marginBottom' => '15px',
                'textAlign' => 'left'
            ],
            'content' => [
                'color' => '#666666',
                'fontSize' => '16px',
                'lineHeight' => '1.6',
                'marginBottom' => '15px'
            ],
            'links' => [
                'color' => '#007bff',
                'textDecoration' => 'underline'
            ],
            'button' => [
                'backgroundColor' => '#007bff',
                'color' => '#ffffff',
                'padding' => '10px 20px',
                'borderRadius' => '5px',
                'border' => 'none',
                'fontSize' => '16px',
                'cursor' => 'pointer'
            ]
        ];
    }

    // Générer le CSS personnalisé
    public function generateCustomCSS()
    {
        $css = '';
        $blockId = 'block-' . $this->id;
        
        // Debug: Afficher les styles disponibles
        
        // Fonction pour convertir camelCase en kebab-case
        $camelToKebab = function($property) {
            return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $property));
        };
        
        // Fonction pour traiter les valeurs
        $processValue = function($value) {
            if ($value === '' || $value === null) {
                return false; // Ne pas inclure cette propriété
            }
            if ($value === 'none') {
                return 'none'; // Inclure 'none' comme valeur valide
            }
            return $value;
        };
        
        // Styles du conteneur
        if (!empty($this->styles['container'])) {
            $css .= "#$blockId {";
            foreach ($this->styles['container'] as $property => $value) {
                $processedValue = $processValue($value);
                if ($processedValue !== false) {
                    $cssProperty = $camelToKebab($property);
                    $css .= " $cssProperty: $processedValue;";
                }
            }
            $css .= " }";
        }
        
        // Styles du titre
        if (!empty($this->styles['title'])) {
            $css .= "#$blockId h2, #$blockId h3 {";
            foreach ($this->styles['title'] as $property => $value) {
                $processedValue = $processValue($value);
                if ($processedValue !== false) {
                    $cssProperty = $camelToKebab($property);
                    $css .= " $cssProperty: $processedValue;";
                }
            }
            $css .= " }";
        }
        
        // Styles du contenu
        if (!empty($this->styles['content'])) {
            $css .= "#$blockId .content {";
            foreach ($this->styles['content'] as $property => $value) {
                $processedValue = $processValue($value);
                if ($processedValue !== false) {
                    $cssProperty = $camelToKebab($property);
                    $css .= " $cssProperty: $processedValue;";
                }
            }
            $css .= " }";
        }
        
        // Styles des liens
        if (!empty($this->styles['links'])) {
            $css .= "#$blockId a {";
            foreach ($this->styles['links'] as $property => $value) {
                $processedValue = $processValue($value);
                if ($processedValue !== false) {
                    $cssProperty = $camelToKebab($property);
                    $css .= " $cssProperty: $processedValue;";
                }
            }
            $css .= " }";
        }
        
        // Styles des boutons
        if (!empty($this->styles['button'])) {
            $css .= "#$blockId .btn {";
            foreach ($this->styles['button'] as $property => $value) {
                $processedValue = $processValue($value);
                if ($processedValue !== false) {
                    $cssProperty = $camelToKebab($property);
                    $css .= " $cssProperty: $processedValue;";
                }
            }
            $css .= " }";
        }
        
        // Debug: Afficher le CSS généré
        
        return $css;
    }

    // Rendu du bloc selon son type
    public function render()
    {
        $blockId = 'block-' . $this->id;
        $customCSS = $this->generateCustomCSS();
        
        $html = '';
        
        // Ajouter le CSS personnalisé
        if (!empty($customCSS)) {
            $html .= "<style>$customCSS</style>";
        }
        
        $html .= '<div id="' . $blockId . '" class="homepage-block homepage-block-' . $this->type . '">';
        
        // Rendu du contenu selon le type
        switch ($this->type) {
            case 'text':
                $html .= $this->renderText();
                break;
            case 'button':
                $html .= $this->renderButton();
                break;
            case 'table':
                $html .= $this->renderTable();
                break;
            case 'form':
                $html .= $this->renderForm();
                break;
            case 'image':
                $html .= $this->renderImage();
                break;
            case 'html':
                $html .= $this->renderHtml();
                break;
            case 'container':
                $html .= $this->renderContainer();
                break;
            case 'latest_articles':
                $html .= $this->renderLatestArticles();
                break;
            case 'latest_sondages':
                $html .= $this->renderLatestSondages();
                break;
            case 'guestbook_cta':
                $html .= $this->renderGuestbookCta();
                break;
            default:
                $html .= $this->renderText();
        }
        
        $html .= '</div>';
        return $html;
    }

    private function renderText()
    {
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        return $html;
    }

    private function renderButton()
    {
        $url = $this->options['url'] ?? '#';
        $buttonText = $this->options['button_text'] ?? 'Cliquer ici';
        
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        $html .= '<a href="' . htmlspecialchars($url) . '" class="btn">' . htmlspecialchars($buttonText) . '</a>';
        return $html;
    }

    private function renderTable()
    {
        $headers = $this->options['headers'] ?? [];
        $rows = $this->options['rows'] ?? [];
        
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        
        if (!empty($headers) || !empty($rows)) {
            $html .= '<table class="table">';
            if (!empty($headers)) {
                $html .= '<thead><tr>';
                foreach ($headers as $header) {
                    $html .= '<th>' . htmlspecialchars($header) . '</th>';
                }
                $html .= '</tr></thead>';
            }
            if (!empty($rows)) {
                $html .= '<tbody>';
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($row as $cell) {
                        $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
            }
            $html .= '</table>';
        }
        return $html;
    }

    private function renderForm()
    {
        $fields = $this->options['fields'] ?? [];
        $action = $this->options['action'] ?? '';
        $method = $this->options['method'] ?? 'POST';
        
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        
        $html .= '<form action="' . htmlspecialchars($action) . '" method="' . $method . '">';
        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';
            $name = $field['name'] ?? '';
            $label = $field['label'] ?? '';
            $required = isset($field['required']) && $field['required'] ? 'required' : '';
            
            $html .= '<div class="form-group">';
            if ($label) {
                $html .= '<label for="' . $name . '">' . htmlspecialchars($label) . '</label>';
            }
            
            if ($type === 'textarea') {
                $html .= '<textarea name="' . $name . '" id="' . $name . '" ' . $required . '></textarea>';
            } else {
                $html .= '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" ' . $required . '>';
            }
            $html .= '</div>';
        }
        $html .= '<button type="submit" class="btn">Envoyer</button>';
        $html .= '</form>';
        return $html;
    }

    private function renderImage()
    {
        $src = $this->options['src'] ?? '';
        $alt = $this->options['alt'] ?? '';
        
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        if ($src) {
            $html .= '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" class="block-image">';
        }
        return $html;
    }

    private function renderHtml()
    {
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . $this->content . '</div>';
        return $html;
    }

    private function renderContainer()
    {
        $html = '';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        
        // Rendu des blocs enfants
        if (!empty($this->children)) {
            $html .= '<div class="block-children">';
            foreach ($this->children as $child) {
                if (is_array($child)) {
                    $childBlock = new Block($child);
                    $html .= $childBlock->render();
                }
            }
            $html .= '</div>';
        }
        
        return $html;
    }

    // Méthodes pour les blocs imbriqués
    public function addChild($childBlock)
    {
        $this->children[] = $childBlock;
    }

    public function removeChild($childId)
    {
        foreach ($this->children as $key => $child) {
            if (is_array($child) && $child['id'] === $childId) {
                unset($this->children[$key]);
                break;
            }
        }
    }

    public function isContainer()
    {
        return $this->type === 'container';
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }

    private function renderLatestArticles(): string
    {
        $pluginsManager = PluginsManager::getInstance();
        if (!$pluginsManager->isActivePlugin('blog') || !class_exists('\\newsManager')) {
            return $this->renderInfoMessage(Lang::get('homebuilder.messages.blog-unavailable', 'Activez le plugin Blog pour afficher des articles.'));
        }

        $manager = new \newsManager();
        $count = max(1, (int)($this->options['count'] ?? 3));
        $articles = [];
        foreach ($manager->getItems() as $article) {
            if (method_exists($article, 'getDraft') && !$article->getDraft()) {
                $articles[] = $article;
            }
        }
        if (empty($articles)) {
            return $this->renderInfoMessage(Lang::get('homebuilder.messages.latest-articles-empty', 'Aucun article disponible pour le moment.'));
        }
        $articles = array_slice($articles, 0, $count);

        $router = Router::getInstance();
        $html = '<div class="latest-articles">';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<ul class="latest-articles-list">';

        foreach ($articles as $article) {
            $slug = Util::strToUrl($article->getName());
            $url = $router->generate('blog-read', ['id' => $article->getId(), 'name' => $slug]);
            $intro = $article->getIntro() ? strip_tags(htmlspecialchars_decode($article->getIntro())) : '';
            $intro = mb_substr($intro, 0, 160) . (mb_strlen($intro) > 160 ? '…' : '');
            $date = Util::FormatDate($article->getDate(), 'en', Lang::getLocale());

            $html .= '<li class="latest-article-item">';
            $html .= '<a href="' . htmlspecialchars($url) . '">';
            $html .= '<span class="article-title">' . htmlspecialchars($article->getName()) . '</span>';
            $html .= '<span class="article-intro">' . htmlspecialchars($intro) . '</span>';
            $html .= '<span class="article-date">' . htmlspecialchars($date) . '</span>';
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul></div>';
        return $html;
    }

    private function renderLatestSondages(): string
    {
        $pluginsManager = PluginsManager::getInstance();
        if (!$pluginsManager->isActivePlugin('sondage') || !class_exists('\\SondageManager')) {
            return $this->renderInfoMessage(Lang::get('homebuilder.messages.sondage-unavailable', 'Activez le plugin Sondage pour afficher les votes.'));
        }

        $manager = new \SondageManager();
        $count = max(1, (int)($this->options['count'] ?? 2));
        $polls = array_slice($manager->getActiveItems(), 0, $count);
        if (empty($polls)) {
            return $this->renderInfoMessage(Lang::get('homebuilder.messages.sondage-empty', 'Aucun sondage actif.'));
        }

        $router = Router::getInstance();
        $html = '<div class="latest-sondages">';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        $html .= '<ul class="latest-sondages-list">';

        foreach ($polls as $poll) {
            $url = $router->generate('sondage-read', ['id' => $poll->getId()]);
            $votes = method_exists($poll, 'getTotalVotes') ? $poll->getTotalVotes() : 0;
            $votesLabel = Lang::get('homebuilder.messages.sondage-votes');
            if ($votesLabel === 'homebuilder.messages.sondage-votes') {
                $votesLabel = '%d vote(s)';
            }
            $votesLabel = sprintf($votesLabel, $votes);
            $html .= '<li class="sondage-item">';
            $html .= '<a href="' . htmlspecialchars($url) . '">';
            $html .= '<span class="sondage-title">' . htmlspecialchars($poll->getTitle()) . '</span>';
            $html .= '<span class="sondage-votes">' . htmlspecialchars($votesLabel) . '</span>';
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul></div>';
        return $html;
    }

    private function renderGuestbookCta(): string
    {
        $pluginsManager = PluginsManager::getInstance();
        if (!$pluginsManager->isActivePlugin('guestbook')) {
            return $this->renderInfoMessage(Lang::get('homebuilder.messages.guestbook-unavailable', 'Activez le plugin Livre d\'or pour afficher ce bloc.'));
        }

        $router = Router::getInstance();
        $ctaLabel = $this->options['button_text'] ?? Lang::get('homebuilder.messages.guestbook-button', 'Signer le livre d\'or');
        $url = $router->generate('guestbook-home');

        $html = '<div class="guestbook-cta">';
        if ($this->title) {
            $html .= '<h2>' . htmlspecialchars($this->title) . '</h2>';
        }
        if ($this->content) {
            $html .= '<div class="content">' . nl2br(htmlspecialchars($this->content)) . '</div>';
        }
        $html .= '<a class="btn" href="' . htmlspecialchars($url) . '">' . htmlspecialchars($ctaLabel) . '</a>';
        $html .= '</div>';
        return $html;
    }

    private function renderInfoMessage(string $message): string
    {
        return '<div class="homebuilder-info">' . htmlspecialchars($message) . '</div>';
    }
} 
