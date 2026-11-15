<?php

namespace PellEditor\Controllers;

use Core\Auth\CurrentUser;
use Core\Security\Csrf;
use Utils\Util;
use Core\Core;

class PellEditorController
{
    /**
     * Endpoint d'exportation (PDF via mPDF, DOCX via PHPWord, Markdown, HTML)
     */
    public function export()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit('Method Not Allowed');
        }

        $user = CurrentUser::get();
        if (!$user || !$user->isLogged()) {
            header('HTTP/1.1 403 Forbidden');
            exit('Unauthorized');
        }

        $csrfToken = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        $legacyToken = $_POST['token'] ?? null;
        $hasValidToken = ($csrfToken !== null && $csrfToken !== '' && Csrf::validate((string)$csrfToken, false))
            || (!empty($legacyToken) && isset($user->token) && hash_equals((string)$user->token, (string)$legacyToken));

        if (!$hasValidToken) {
            $secFetchSite = strtolower((string)($_SERVER['HTTP_SEC_FETCH_SITE'] ?? ''));
            $originOrReferer = (string)($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
            $originHost = $originOrReferer !== '' ? strtolower((string)(parse_url($originOrReferer, PHP_URL_HOST) ?? '')) : '';
            $expectedHost = strtolower(explode(':', (string)($_SERVER['HTTP_HOST'] ?? ''))[0]);
            if ($secFetchSite === 'cross-site' || $originHost === '' || $expectedHost === '' || !hash_equals($expectedHost, $originHost)) {
                header('HTTP/1.1 403 Forbidden');
                exit('Invalid CSRF token');
            }
        }

        require_once dirname(__DIR__) . '/libs/export_bootstrap.php';

        $format = $_POST['format'] ?? 'html';
        $rawHtml = (string)($_POST['html'] ?? '');
        $html = \Utils\Sanitizer::cleanHtml($rawHtml);
        // Bloquer les URLs locales/SSRF dans src/href avant passage à mPDF/PHPWord
        $html = (string)preg_replace(
            '/\b(src|href)\s*=\s*(["\']?)\s*(?:file|phar|ftp|gopher|dict|php):[^"\'\s>]*/i',
            '$1=$2#',
            $html
        );
        $html = (string)preg_replace(
            '/\b(src|href)\s*=\s*(["\']?)\s*https?:\/\/(?:127\.|0\.0\.0\.0|localhost|169\.254\.|10\.|192\.168\.|172\.(?:1[6-9]|2\d|3[01])\.|\[::1\])[^"\'\s>]*/i',
            '$1=$2#',
            $html
        );
        $title = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $_POST['title'] ?? 'Document') ?: 'Document';

        switch ($format) {
            case 'pdf':
                $mpdf = \MpdfService\MpdfService::getInstance();
                $mpdf->WriteHTML($html);
                $mpdf->Output($title . '.pdf', 'D');
                break;

            case 'docx':
                $phpWord = \PhpWordService\PhpWordService::getInstance();
                $section = $phpWord->addSection();
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
                $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                header("Content-Description: File Transfer");
                header('Content-Disposition: attachment; filename="' . $title . '.docx"');
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                header('Content-Transfer-Encoding: binary');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Expires: 0');
                $objWriter->save("php://output");
                break;

            case 'md':
                $md = $html;
                $md = preg_replace('/<(h[1-6])>(.*?)<\/\1>/i', "\n\n# $2\n\n", $md);
                $md = str_replace(['<h1>', '<h2>', '<h3>'], ["# ", "## ", "### "], $md);
                $md = str_replace(['</h1>', '</h2>', '</h3>', '</p>', '</div>'], "\n\n", $md);
                $md = str_replace(['<strong>', '</b>', '<b>'], '**', $md);
                $md = str_replace(['<em>', '</i>', '<i>'], '_', $md);
                $md = str_replace(['<br>', '<br/>', '<br />'], "\n", $md);

                header('Content-Type: text/markdown; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $title . '.md"');
                echo trim(strip_tags($md));
                break;

            default:
                header('Content-Type: text/html; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $title . '.html"');
                echo $html;
                break;
        }
        exit;
    }

    /**
     * Endpoint JSON pour l'éditeur Pell
     */
    public function gallery()
    {
        $user = CurrentUser::get();
        if (!$user || !$user->isLogged()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $images = [];
        $baseUrl = rtrim(Core::getInstance()->getConfigVal('siteUrl'), '/') . '/' . str_replace('./', '', UPLOAD) . 'galerie/';

        if (class_exists('\GaleriesManager')) {
            $gm = new \GaleriesManager();
            foreach ($gm->getItems() as $item) {
                $img = method_exists($item, 'getImg') ? $item->getImg() : ($item->attributes['img'] ?? '');
                if ($img !== '') {
                    $images[] = [
                        'id'    => method_exists($item, 'getId') ? $item->getId() : ($item->attributes['id'] ?? ''),
                        'title' => (method_exists($item, 'getTitle') ? $item->getTitle() : ($item->attributes['title'] ?? '')) ?: 'Sans titre',
                        'url'   => $baseUrl . $img
                    ];
                }
            }
        } else {
            $galleryFile = DATA_PLUGIN . 'galerie/galerie.json';
            if (file_exists($galleryFile)) {
                $data = Util::readJsonFile($galleryFile, true);
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (isset($item['img']) && $item['img'] !== '') {
                            $images[] = [
                                'id'    => $item['id'] ?? '',
                                'title' => $item['title'] ?? 'Sans titre',
                                'url'   => $baseUrl . $item['img']
                            ];
                        }
                    }
                }
            }
        }

        $resp = new \Core\Responses\ApiResponse();
        $resp->status = \Core\Responses\ApiResponse::STATUS_SUCCESS;
        $resp->httpCode = 200;
        $resp->body = $images;
        return $resp;
    }
}
