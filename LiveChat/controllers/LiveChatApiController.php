<?php

namespace LiveChat\Controllers;

use LiveChat\Services\LiveChatService;

defined('ROOT') or exit('Access denied!');

class LiveChatApiController
{
    private LiveChatService $service;

    public function __construct()
    {
        $this->service = new LiveChatService();
    }

    private function jsonResponse(array $data, int $status = 200): \Core\Responses\ApiResponse
    {
        $resp = new \Core\Responses\ApiResponse();
        $resp->httpCode = $status;
        $resp->status = \Core\Responses\ApiResponse::httpStatusLine($status);
        $resp->body = $data;
        return $resp;
    }

    /**
     * Initialise ou récupère la conversation active d'un visiteur
     */
    public function init(): \Core\Responses\ApiResponse
    {
        $token = trim($_POST['session_token'] ?? '');
        if (empty($token) || strlen($token) < 16) {
            $token = bin2hex(random_bytes(16));
        }

        $name = trim($_POST['visitor_name'] ?? '');
        $contact = trim($_POST['visitor_contact'] ?? '');

        try {
            $conv = $this->service->getOrCreateConversation($token, $name ?: null, $contact ?: null);
            $messages = $this->service->getMessages((int)$conv['id'], 0);
            $this->service->markAsRead((int)$conv['id'], 'visitor');

            return $this->jsonResponse([
                'success' => true,
                'session_token' => $token,
                'conversation' => $conv,
                'messages' => $messages,
                'admin_name' => $this->service->getConfig()['admin_name'] ?? 'Équipe Support'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Envoie un message depuis le visiteur
     */
    public function send(): \Core\Responses\ApiResponse
    {
        // Honeypot anti-spam
        if (!empty($_POST['_gotcha'])) {
            return $this->jsonResponse(['success' => true]);
        }

        $token = trim($_POST['session_token'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $name = trim($_POST['visitor_name'] ?? '');
        $contact = trim($_POST['visitor_contact'] ?? '');

        if (empty($token)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Session invalide'], 400);
        }
        if (empty($message)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Veuillez saisir un message'], 400);
        }

        try {
            $conv = $this->service->getOrCreateConversation($token, $name ?: null, $contact ?: null);
            $msg = $this->service->addMessage((int)$conv['id'], 'visitor', $message, $name ?: $conv['visitor_name']);

            return $this->jsonResponse([
                'success' => true,
                'message' => $msg,
                'conversation_id' => $conv['id']
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Polling des nouveaux messages reçus (côté visiteur)
     */
    public function poll(): \Core\Responses\ApiResponse
    {
        $token = trim($_GET['session_token'] ?? '');
        $afterId = max(0, (int)($_GET['after_id'] ?? 0));

        if (empty($token)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Token manquant'], 400);
        }

        $conv = $this->service->getConversationByToken($token);
        if (!$conv) {
            return $this->jsonResponse(['success' => true, 'messages' => []]);
        }

        $messages = $this->service->getMessages((int)$conv['id'], $afterId);
        if (!empty($messages)) {
            $this->service->markAsRead((int)$conv['id'], 'visitor');
        }

        return $this->jsonResponse([
            'success' => true,
            'messages' => $messages,
            'status' => $conv['status']
        ]);
    }
}
