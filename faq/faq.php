<?php

/**
 * @copyright (C) 2025, 299Ko
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * 
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') OR exit('No direct script access allowed');

require_once PLUGINS . 'faq/entities/FaqQuestion.php';
require_once PLUGINS . 'faq/entities/FaqManager.php';

## Fonction d'installation

function faqInstall() {
    $dataFile = DATA_PLUGIN . 'faq/faq.json';
    $dataDir = dirname($dataFile);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]));
    }
}

## Code relatif au plugin

/**
 * Rendu HTML d'un accordéon de FAQ avec microdonnées Schema.org (JSON-LD FAQPage)
 *
 * @param string $category Filtrer par catégorie (laisser vide pour toutes les questions actives)
 * @return string HTML de l'accordéon
 */
function faqRenderQuestions(string $category = ''): string
{
    if (!class_exists('\Faq\Entities\FaqManager')) {
        return '';
    }

    $manager = new \Faq\Entities\FaqManager();
    $questions = $manager->getQuestionsByCategory($category);

    if (empty($questions)) {
        return '';
    }

    $schemaEntities = [];
    $itemsHtml = '';

    foreach ($questions as $q) {
        $qTitle = htmlspecialchars_decode((string) $q->getQuestion(), ENT_QUOTES);
        $qAnswerRaw = htmlspecialchars_decode((string) $q->getAnswer(), ENT_QUOTES);
        $qAnswerClean = trim(strip_tags($qAnswerRaw));

        $schemaEntities[] = [
            '@type' => 'Question',
            'name' => $qTitle,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $qAnswerClean,
            ],
        ];

        $itemsHtml .= '    <div class="adcom-faq-item faq-item">' . "\n";
        $itemsHtml .= '        <button class="adcom-faq-header faq-question" type="button" aria-expanded="false">' . "\n";
        $itemsHtml .= '            <span>' . htmlspecialchars($qTitle, ENT_QUOTES, 'UTF-8') . '</span>' . "\n";
        $itemsHtml .= '            <i class="fa-solid fa-plus adcom-faq-icon"></i>' . "\n";
        $itemsHtml .= '        </button>' . "\n";
        $itemsHtml .= '        <div class="adcom-faq-body faq-answer">' . "\n";
        $itemsHtml .= '            ' . $qAnswerRaw . "\n";
        $itemsHtml .= '        </div>' . "\n";
        $itemsHtml .= '    </div>' . "\n";
    }

    $schemaJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $schemaEntities,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    $catAttr = $category !== '' ? ' data-category="' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '"' : '';

    $output = '<div class="adcom-faq-wrapper faq-accordion"' . $catAttr . '>' . "\n";
    $output .= $itemsHtml;
    $output .= '</div>' . "\n";
    $output .= '<script type="application/ld+json">' . "\n" . $schemaJson . "\n" . '</script>' . "\n";

    return $output;
}

/**
 * Alias global pour les templates Twig / PHP
 */
function faqRender(string $category = ''): string
{
    return faqRenderQuestions($category);
}

/**
 * Enregistrement du shortcode [faq] pour les pages de contenu
 * Supporte :
 *   [faq category="Contact"]
 *   [faq category="Site Internet" title="Questions fréquentes…"]
 *   [faq]
 */
if (class_exists('\Content\ContentParser')) {
    \Content\ContentParser::addShortcode('faq', function (array $attributes = []) {
        $category = $attributes['category'] ?? ($attributes['cat'] ?? '');
        $title = $attributes['title'] ?? '';

        $accordion = faqRenderQuestions($category);
        if ($accordion === '') {
            return '';
        }

        if ($title !== '') {
            return '<section class="adcom-section evo-section faq-section" style="background-color: var(--adcom-bg-alt, var(--evo-surface-alt, #f8f6f4));">' . "\n"
                . '    <div class="adcom-container evo-container adcom-fade-up">' . "\n"
                . '        <h2 class="adcom-section-title evo-section-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>' . "\n"
                . '        ' . $accordion . "\n"
                . '    </div>' . "\n"
                . '</section>';
        }

        return $accordion;
    });
}
