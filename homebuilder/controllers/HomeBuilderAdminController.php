<?php

namespace HomeBuilder\Controllers;

use Core\Controllers\AdminController;
use Core\Responses\AdminResponse;
use HomeBuilder\Entities\Block;
use HomeBuilder\Entities\BlockManager;
use Utils\Show;
use Utils\Util;

defined('ROOT') or exit('Access denied!');

class HomeBuilderAdminController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $BlockManager = new BlockManager();
        $blocks = $BlockManager->getAllBlocks();
        $blockMeta = $BlockManager->getBlockTypeMeta();
        $blockTypes = $BlockManager->getAvailableBlockTypes();
        $blocksData = [];
        
        $activeCount = 0;
        foreach ($blocks as $block) {
            $type = $block->getType();
            $meta = $blockMeta[$type] ?? [
                'label' => $blockTypes[$type] ?? $type,
                'icon' => 'fa-solid fa-cube',
                'color' => '#64748b',
                'category' => 'Autre',
                'desc' => ''
            ];
            $isActive = $block->isActive();
            if ($isActive) {
                $activeCount++;
            }

            // Aperçu visuel intelligent selon le type
            $preview = '';
            $options = $block->getOptions();
            if ($type === 'hero') {
                $sub = !empty($options['subtitle']) ? $options['subtitle'] : $block->getContent();
                $preview = $sub ? mb_substr(strip_tags((string)$sub), 0, 110) . '…' : 'Bannière d\'accroche principale';
            } elseif ($type === 'latest_articles') {
                $count = $options['count'] ?? 3;
                $preview = "Flux dynamique des {$count} derniers articles publiés";
            } elseif ($type === 'searchmaps_map') {
                $preview = 'Carte interactive et mise en valeur des commerces locaux';
            } elseif ($type === 'prordv_cta') {
                $preview = 'Module de réservation en direct connecté à ProRdv';
            } elseif ($type === 'button') {
                $btnText = $options['button_text'] ?? 'Bouton d\'action';
                $preview = "Bouton CTA : \"{$btnText}\"";
            } elseif ($type === 'guestbook_cta') {
                $btnText = $options['button_text'] ?? 'Signer le livre d\'or';
                $preview = "Appel à signature : \"{$btnText}\"";
            } elseif ($type === 'latest_sondages') {
                $count = $options['count'] ?? 2;
                $preview = "Affiche les {$count} sondages actifs";
            } elseif ($type === 'columns') {
                $colsCount = is_array($options['columns'] ?? null) ? count($options['columns']) : 0;
                $preview = "Grille de {$colsCount} colonnes d'avantages";
            } else {
                $clean = trim(strip_tags((string)$block->getContent()));
                $preview = $clean ? mb_substr($clean, 0, 110) . (mb_strlen($clean) > 110 ? '…' : '') : 'Section sans description textuelle';
            }

            $blocksData[] = [
                'id' => $block->getId(),
                'title' => $block->getTitle() ?: $meta['label'],
                'type' => $type,
                'typeLabel' => $meta['label'],
                'typeIcon' => $meta['icon'],
                'typeColor' => $meta['color'],
                'typeCategory' => $meta['category'],
                'content' => $block->getContent(),
                'order' => $block->getOrder(),
                'active' => $isActive,
                'isActive' => $isActive,
                'contentPreview' => $preview,
                'editUrl' => $this->router->generate('admin-homebuilder-edit', ['id' => $block->getId()]),
                'stylesUrl' => $this->router->generate('admin-homebuilder-styles', ['id' => $block->getId()]),
                'deleteUrl' => $this->router->generate('admin-homebuilder-delete', ['id' => $block->getId()]),
                'toggleUrl' => $this->router->generate('admin-homebuilder-toggle', ['id' => $block->getId()])
            ];
        }
        
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('homebuilder', 'admin/index');
        
        $tpl->set('blocks', $blocksData);
        $tpl->set('blockTypes', $blockTypes);
        $tpl->set('blockMeta', $blockMeta);
        $tpl->set('hasBlocks', count($blocksData) > 0);
        $tpl->set('totalBlocks', count($blocksData));
        $tpl->set('activeCount', $activeCount);
        $tpl->set('siteHomeUrl', $this->core->getConfigVal('siteUrl') ?: '/');
        
        $response->addTemplate($tpl);
        $response->setTitle('Constructeur de page d\'accueil');
        
        return $response;
    }

    public function add()
    {
        $manager = new BlockManager();
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('homebuilder', 'admin/edit');

        $tpl->set('edit_send_link', $this->router->generate('admin-homebuilder-add-send'));
        $tpl->set('blockTypes', $manager->getAvailableBlockTypes());
        $tpl->set('colors', $manager->getAvailableColors());
        $tpl->set('sizes', $manager->getAvailableSizes());
        $tpl->set('title', 'Nouveau bloc');
        $tpl->set('type', 'text');
        $tpl->set('content', '');
        $tpl->set('active', true);
        $tpl->set('button_url', '');
        $tpl->set('button_text', '');
        $tpl->set('button_color', 'primary');
        $tpl->set('button_size', 'medium');
        $tpl->set('table_headers', '');
        $tpl->set('table_rows', '');
        $tpl->set('form_action', '');
        $tpl->set('form_method', 'POST');
        $tpl->set('is_post', true);
        $tpl->set('is_get', false);
        $tpl->set('form_fields', '');
        $tpl->set('image_src', '');
        $tpl->set('image_alt', '');
        $tpl->set('image_width', '');
        $tpl->set('image_height', '');
        $tpl->set('latest_articles_count', 3);
        $tpl->set('latest_sondages_count', 2);
        $tpl->set('guestbook_button_text', "Signer le livre d'or");
        $tpl->set('hero_subtitle', '');
        $tpl->set('hero_btn1_text', '');
        $tpl->set('hero_btn1_url', '');
        $tpl->set('hero_btn2_text', '');
        $tpl->set('hero_btn2_url', '');
        $tpl->set('hero_image', '');
        for ($i = 1; $i <= 3; $i++) {
            $tpl->set("col_{$i}_icon", 'fa-solid fa-star');
            $tpl->set("col_{$i}_title", '');
            $tpl->set("col_{$i}_text", '');
        }
        $tpl->set('searchmaps_height', '400px');
        $tpl->set('prordv_button_text', 'Prendre rendez-vous en ligne');
        $tpl->set('prordv_url', '/rendez-vous');

        $response->addTemplate($tpl);
        $response->setTitle('Ajouter un bloc');
        return $response;
    }

    public function addSend()
    {
        $manager = new BlockManager();

        $blockData = [
            'id' => $manager->generateBlockId(),
            'type' => $_POST['type'] ?? 'text',
            'title' => trim($_POST['title'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'order' => count($manager->getAllBlocks()) + 1,
            'active' => isset($_POST['active']),
            'options' => $this->processOptions($_POST),
            'parentId' => $_POST['parentId'] ?? null,
            'children' => [],
            'styles' => (new Block(['id' => 'default']))->getStyles()
        ];

        $errors = $manager->validateBlockData($blockData);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                Show::msg($error, 'error');
            }
            header('location:' . $this->router->generate('admin-homebuilder-add'));
            die();
        }

        if ($manager->addBlock($blockData)) {
            Show::msg('Bloc créé avec succès', 'success');
        } else {
            Show::msg('Erreur lors de la création du bloc', 'error');
        }

        header('location:' . $this->router->generate('admin-homebuilder'));
        die();
    }

    public function edit($id)
    {
        $manager = new BlockManager();
        $block = $manager->getBlock($id);

        if (!$block) {
            Show::msg('Bloc non trouvé', 'error');
            header('location:' . $this->router->generate('admin-homebuilder'));
            die();
        }

        $options = $block->getOptions() ?? [];

        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('homebuilder', 'admin/edit');

        $tpl->set('edit_send_link', $this->router->generate('admin-homebuilder-edit-send', ['id' => $block->getId()]));
        $tpl->set('blockTypes', $manager->getAvailableBlockTypes());
        $tpl->set('colors', $manager->getAvailableColors());
        $tpl->set('sizes', $manager->getAvailableSizes());
        $tpl->set('title', $block->getTitle());
        $tpl->set('type', $block->getType());
        $tpl->set('content', $block->getContent());
        $tpl->set('active', $block->isActive());
        $tpl->set('button_url', $options['url'] ?? '');
        $tpl->set('button_text', $options['button_text'] ?? '');
        $tpl->set('button_color', $options['color'] ?? 'primary');
        $tpl->set('button_size', $options['size'] ?? 'medium');
        $tpl->set('table_headers', isset($options['headers']) ? implode(',', $options['headers']) : '');
        $tpl->set('table_rows', isset($options['rows']) ? json_encode($options['rows']) : '');
        $tpl->set('form_action', $options['action'] ?? '');
        $tpl->set('form_method', strtoupper($options['method'] ?? 'POST'));
        $tpl->set('is_post', strtoupper($options['method'] ?? 'POST') === 'POST');
        $tpl->set('is_get', strtoupper($options['method'] ?? 'POST') === 'GET');
        $tpl->set('form_fields', isset($options['fields']) ? json_encode($options['fields']) : '');
        $tpl->set('image_src', $options['src'] ?? '');
        $tpl->set('image_alt', $options['alt'] ?? '');
        $tpl->set('image_width', $options['width'] ?? '');
        $tpl->set('image_height', $options['height'] ?? '');
        $tpl->set('latest_articles_count', $block->getType() === 'latest_articles' ? ($options['count'] ?? 3) : 3);
        $tpl->set('latest_sondages_count', $block->getType() === 'latest_sondages' ? ($options['count'] ?? 2) : 2);
        $tpl->set('guestbook_button_text', $block->getType() === 'guestbook_cta' ? ($options['button_text'] ?? "Signer le livre d'or") : "Signer le livre d'or");
        $tpl->set('hero_subtitle', $options['subtitle'] ?? '');
        $tpl->set('hero_btn1_text', $options['btn1_text'] ?? '');
        $tpl->set('hero_btn1_url', $options['btn1_url'] ?? '');
        $tpl->set('hero_btn2_text', $options['btn2_text'] ?? '');
        $tpl->set('hero_btn2_url', $options['btn2_url'] ?? '');
        $tpl->set('hero_image', $options['image'] ?? '');
        $cols = $options['columns'] ?? [];
        for ($i = 1; $i <= 3; $i++) {
            $idx = $i - 1;
            $tpl->set("col_{$i}_icon", $cols[$idx]['icon'] ?? 'fa-solid fa-star');
            $tpl->set("col_{$i}_title", $cols[$idx]['title'] ?? '');
            $tpl->set("col_{$i}_text", $cols[$idx]['text'] ?? '');
        }
        $tpl->set('searchmaps_height', $options['height'] ?? '400px');
        $tpl->set('prordv_button_text', $options['button_text'] ?? 'Prendre rendez-vous en ligne');
        $tpl->set('prordv_url', $options['url'] ?? '/rendez-vous');

        $response->addTemplate($tpl);
        $response->setTitle('Modifier le bloc');
        return $response;
    }

    public function editSend($id)
    {
        $BlockManager = new BlockManager();
        $block = $BlockManager->getBlock($id);
        
        if (!$block) {
            Show::msg('Bloc non trouvé', 'error');
            header('location:' . $this->router->generate('admin-homebuilder'));
            die();
        }
        
        $blockData = [
            'id' => $id,
            'type' => $_POST['type'] ?? 'text',
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'order' => $block->getOrder(),
            'active' => isset($_POST['active']),
            'options' => $this->processOptions($_POST),
            'parentId' => $_POST['parentId'] ?? $block->getParentId(),
            'children' => $block->getChildren(),
            'styles' => $block->getStyles()
        ];
        
        $errors = $BlockManager->validateBlockData($blockData);
        
        if (empty($errors)) {
            if ($BlockManager->updateBlock($id, $blockData)) {
                Show::msg('Bloc modifié avec succès', 'success');
                header('location:' . $this->router->generate('admin-homebuilder'));
                die();
            } else {
                Show::msg('Erreur lors de la modification du bloc', 'error');
            }
        } else {
            foreach ($errors as $error) {
                Show::msg($error, 'error');
            }
        }
        
        // En cas d'erreur, retour au formulaire d'édition
        header('location:' . $this->router->generate('admin-homebuilder-edit', ['id' => $id]));
        die();
    }

    public function delete($id)
    {
        $BlockManager = new BlockManager();
        
        if ($BlockManager->deleteBlock($id)) {
            Show::msg('Bloc supprimé avec succès', 'success');
        } else {
            Show::msg('Erreur lors de la suppression du bloc', 'error');
        }
        
        header('location:' . $this->router->generate('admin-homebuilder'));
        die();
    }

    public function reorder()
    {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        if (isset($_POST['blocks'])) {
            $data = ['blocks' => json_decode($_POST['blocks'], true)];
        }

        if (!isset($data['blocks']) || !is_array($data['blocks'])) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            die();
        }

        $blockManager = new BlockManager();
        $orderedIds = [];
        foreach ($data['blocks'] as $item) {
            if (is_array($item) && isset($item['id'])) {
                $orderedIds[] = $item['id'];
            } elseif (is_string($item)) {
                $orderedIds[] = $item;
            }
        }

        if (empty($orderedIds)) {
            echo json_encode(['success' => false, 'error' => 'Aucun bloc fourni']);
            die();
        }

        if ($blockManager->reorderBlocks($orderedIds)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors du réordonnancement']);
        }
        die();
    }

    public function toggle($id)
    {
        $manager = new BlockManager();
        $block = $manager->getBlock($id);
        if (!$block) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Bloc introuvable']);
            exit;
        }

        $newActive = !$block->isActive();
        $blockData = [
            'id' => $block->getId(),
            'type' => $block->getType(),
            'title' => $block->getTitle(),
            'content' => $block->getContent(),
            'order' => $block->getOrder(),
            'active' => $newActive,
            'options' => $block->getOptions(),
            'parentId' => $block->getParentId(),
            'children' => $block->getChildren(),
            'styles' => $block->getStyles()
        ];

        $success = $manager->updateBlock($id, $blockData);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'active' => $newActive,
            'id' => $id
        ]);
        exit;
    }

    public function styles($id)
    {
        $BlockManager = new BlockManager();
        $block = $BlockManager->getBlock($id);
        
        if (!$block) {
            Show::msg('Bloc non trouvé', 'error');
            header('location:' . $this->router->generate('admin-homebuilder'));
            die();
        }
        
        // Récupérer les styles existants
        $styles = $block->getStyles();
        
        // Debug: Afficher les styles récupérés
        
        $defaultStyles = [
            'container' => [
                'backgroundColor' => '#ffffff',
                'backgroundType' => 'solid',
                'gradientStart' => '#ffffff',
                'gradientEnd' => '#f0f0f0',
                'gradientDirection' => 'to bottom',
                'padding' => '20px',
                'margin' => '10px 0',
                'borderRadius' => '8px',
                'border' => '1px solid #e0e0e0',
                'boxShadow' => '0 2px 4px rgba(0,0,0,0.1)',
                'width' => '100%',
                'maxWidth' => 'none'
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
        
        // Fusionner les styles existants avec les valeurs par défaut
        foreach ($defaultStyles as $section => $defaults) {
            if (!isset($styles[$section])) {
                $styles[$section] = [];
            }
            foreach ($defaults as $property => $defaultValue) {
                // Ne pas écraser les valeurs existantes, même si elles sont vides
                if (!isset($styles[$section][$property])) {
                    $styles[$section][$property] = $defaultValue;
                }
            }
        }
        
        // Préparer les variables booléennes pour les sélections
        $templateData = [
            'block' => $block,
            'block_id' => $block->getId(),
            'block_title' => $block->getTitle(),
            'block_type' => $block->getType(),
            'block_html' => $block->render(),
            'styles' => $styles,
            'edit_link' => $this->router->generate('admin-homebuilder-styles-send', ['id' => $block->getId()]),
            'back_link' => $this->router->generate('admin-homebuilder')
        ];

        // Variables booléennes pour le type de fond
        $bgType = $styles['container']['backgroundType'] ?? 'solid';
        $templateData['isSolid'] = ($bgType === 'solid');
        $templateData['isGradient'] = ($bgType === 'gradient');
        $gradDir = $styles['container']['gradientDirection'] ?? 'to bottom';
        $templateData['gradToBottom'] = ($gradDir === 'to bottom');
        $templateData['gradToTop'] = ($gradDir === 'to top');
        $templateData['gradToRight'] = ($gradDir === 'to right');
        $templateData['gradToLeft'] = ($gradDir === 'to left');
        $templateData['grad45deg'] = ($gradDir === '45deg');
        $templateData['grad135deg'] = ($gradDir === '135deg');
        
        // Variables booléennes pour les sélections du titre
        $templateData['titleFontWeightNormal'] = ($styles['title']['fontWeight'] ?? 'bold') === 'normal';
        $templateData['titleFontWeightBold'] = ($styles['title']['fontWeight'] ?? 'bold') === 'bold';
        $templateData['titleFontWeightLighter'] = ($styles['title']['fontWeight'] ?? 'bold') === 'lighter';
        $templateData['titleTextAlignLeft'] = ($styles['title']['textAlign'] ?? 'left') === 'left';
        $templateData['titleTextAlignCenter'] = ($styles['title']['textAlign'] ?? 'left') === 'center';
        $templateData['titleTextAlignRight'] = ($styles['title']['textAlign'] ?? 'left') === 'right';
        
        // Variables booléennes pour les sélections des liens
        $templateData['linksTextDecorationNone'] = ($styles['links']['textDecoration'] ?? 'underline') === 'none';
        $templateData['linksTextDecorationUnderline'] = ($styles['links']['textDecoration'] ?? 'underline') === 'underline';
        $templateData['linksTextDecorationOverline'] = ($styles['links']['textDecoration'] ?? 'underline') === 'overline';
        $templateData['linksTextDecorationLineThrough'] = ($styles['links']['textDecoration'] ?? 'underline') === 'line-through';
        
        // Variables booléennes pour les sélections des boutons
        $templateData['buttonCursorPointer'] = ($styles['button']['cursor'] ?? 'pointer') === 'pointer';
        $templateData['buttonCursorDefault'] = ($styles['button']['cursor'] ?? 'pointer') === 'default';
        $templateData['buttonCursorNotAllowed'] = ($styles['button']['cursor'] ?? 'pointer') === 'not-allowed';
        
        $response = new AdminResponse();
        $tpl = $response->createPluginTemplate('homebuilder', 'admin/styles');
        
        // Passer toutes les données au template
        foreach ($templateData as $key => $value) {
            $tpl->set($key, $value);
        }
        
        $response->addTemplate($tpl);
        $response->setTitle('Styles du bloc : ' . $block->getTitle());
        
        return $response;
    }

    public function stylesSend($id)
    {
        $BlockManager = new BlockManager();
        $block = $BlockManager->getBlock($id);
        
        if (!$block) {
            Show::msg('Bloc non trouvé', 'error');
            header('location:' . $this->router->generate('admin-homebuilder'));
            die();
        }
        
        $styles = $this->processStyles($_POST);
        
        // Debug: Afficher les styles traités
        
        $blockData = $block->toArray();
        $blockData['styles'] = $styles;
        
        if ($BlockManager->updateBlock($id, $blockData)) {
            Show::msg('Styles appliqués avec succès', 'success');
        } else {
            Show::msg('Erreur lors de l\'application des styles', 'error');
        }
        
        header('location:' . $this->router->generate('admin-homebuilder'));
        die();
    }

    private function processOptions($postData)
    {
        $options = [];
        
        switch ($postData['type']) {
            case 'button':
                $options['url'] = $postData['button_url'] ?? '#';
                $options['button_text'] = $postData['button_text'] ?? 'Cliquer ici';
                $options['color'] = $postData['button_color'] ?? 'primary';
                $options['size'] = $postData['button_size'] ?? 'medium';
                break;
                
            case 'table':
                $options['headers'] = isset($postData['table_headers']) ? explode(',', $postData['table_headers']) : [];
                $options['rows'] = isset($postData['table_rows']) ? json_decode($postData['table_rows'], true) : [];
                break;
                
            case 'form':
                $options['action'] = $postData['form_action'] ?? '';
                $options['method'] = $postData['form_method'] ?? 'POST';
                $options['fields'] = isset($postData['form_fields']) ? json_decode($postData['form_fields'], true) : [];
                break;
                
            case 'image':
                $options['src'] = $postData['image_src'] ?? '';
                $options['alt'] = $postData['image_alt'] ?? '';
                $options['width'] = $postData['image_width'] ?? '';
                $options['height'] = $postData['image_height'] ?? '';
                break;
            case 'hero':
                $options['subtitle'] = $postData['hero_subtitle'] ?? '';
                $options['btn1_text'] = $postData['hero_btn1_text'] ?? '';
                $options['btn1_url'] = $postData['hero_btn1_url'] ?? '';
                $options['btn2_text'] = $postData['hero_btn2_text'] ?? '';
                $options['btn2_url'] = $postData['hero_btn2_url'] ?? '';
                $options['image'] = $postData['hero_image'] ?? '';
                break;
            case 'columns':
                $cols = [];
                for ($i = 1; $i <= 3; $i++) {
                    if (!empty($postData["col_{$i}_title"]) || !empty($postData["col_{$i}_text"])) {
                        $cols[] = [
                            'icon' => $postData["col_{$i}_icon"] ?? 'fa-solid fa-check',
                            'title' => $postData["col_{$i}_title"] ?? '',
                            'text' => $postData["col_{$i}_text"] ?? ''
                        ];
                    }
                }
                $options['columns'] = $cols;
                break;
            case 'searchmaps_map':
                $options['height'] = $postData['searchmaps_height'] ?? '400px';
                break;
            case 'prordv_cta':
                $options['button_text'] = $postData['prordv_button_text'] ?? 'Prendre rendez-vous en ligne';
                $options['url'] = $postData['prordv_url'] ?? '/rendez-vous';
                break;
            case 'latest_articles':
                $options['count'] = max(1, (int)($postData['latest_articles_count'] ?? 3));
                break;
            case 'latest_sondages':
                $options['count'] = max(1, (int)($postData['latest_sondages_count'] ?? 2));
                break;
            case 'guestbook_cta':
                $options['button_text'] = $postData['guestbook_button_text'] ?? 'Signer le livre d\'or';
                break;
        }
        
        return $options;
    }

    private function processStyles($postData)
    {
        // Debug: Afficher toutes les données POST reçues
        
        $styles = [
            'container' => [],
            'title' => [],
            'content' => [],
            'links' => [],
            'button' => []
        ];
        
        // Traiter les styles du conteneur
        $containerProps = ['backgroundColor', 'backgroundType', 'gradientStart', 'gradientEnd', 'gradientDirection', 'padding', 'margin', 'borderRadius', 'border', 'boxShadow', 'width', 'maxWidth'];
        foreach ($containerProps as $prop) {
            $key = 'container_' . $prop;
            if (isset($postData[$key])) {
                $value = trim($postData[$key]);
                $styles['container'][$prop] = $value; // Sauvegarder même si vide
            }
        }
        
        // Traiter les styles du titre
        $titleProps = ['color', 'fontSize', 'fontWeight', 'marginBottom', 'textAlign'];
        foreach ($titleProps as $prop) {
            $key = 'title_' . $prop;
            if (isset($postData[$key])) {
                $value = trim($postData[$key]);
                $styles['title'][$prop] = $value; // Sauvegarder même si vide
            }
        }
        
        // Traiter les styles du contenu
        $contentProps = ['color', 'fontSize', 'lineHeight', 'marginBottom'];
        foreach ($contentProps as $prop) {
            $key = 'content_' . $prop;
            if (isset($postData[$key])) {
                $value = trim($postData[$key]);
                $styles['content'][$prop] = $value; // Sauvegarder même si vide
            }
        }
        
        // Traiter les styles des liens
        $linkProps = ['color', 'textDecoration'];
        foreach ($linkProps as $prop) {
            $key = 'links_' . $prop;
            if (isset($postData[$key])) {
                $value = trim($postData[$key]);
                $styles['links'][$prop] = $value; // Sauvegarder même si vide
            }
        }
        
        // Traiter les styles des boutons
        $buttonProps = ['backgroundColor', 'color', 'padding', 'borderRadius', 'border', 'fontSize', 'cursor'];
        foreach ($buttonProps as $prop) {
            $key = 'button_' . $prop;
            if (isset($postData[$key])) {
                $value = trim($postData[$key]);
                $styles['button'][$prop] = $value; // Sauvegarder même si vide
            }
        }
        
        // Debug: Afficher les styles traités
        
        return $styles;
    }
} 
