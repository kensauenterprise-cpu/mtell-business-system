<?php
// CredentialValidator.php

class CredentialValidator {
    private $consumerKey;
    private $consumerSecret;
    private $tokenUrl;
    private $tokenFile;

    public function __construct($consumerKey, $consumerSecret, $tokenUrl, $tokenFile = null) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->tokenUrl = $tokenUrl;
        $this->tokenFile = $tokenFile ?? __DIR__ . '/../../config/access_token.json';
    }

    public function validate(): bool {
        $errors = [];

        if (empty($this->consumerKey)) {
            $errors[] = 'Missing Consumer Key';
        }

        if (empty($this->consumerSecret)) {
            $errors[] = 'Missing Consumer Secret';
        }

        if (empty($this->tokenUrl) || !filter_var($this->tokenUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid or missing Token URL';
        }

        if (!empty($errors)) {
            $this->logErrors($errors);
            return false;
        }

        return true;
    }

    public function isTokenFresh(): bool {
        if (!file_exists($this->tokenFile)) {
            // ✅ Auto-create empty token file
            file_put_contents($this->tokenFile, json_encode([
                'access_token' => '',
                'expires_at' => 0
            ]));
            error_log("CredentialValidator: Token file was missing and has been created", 3, __DIR__ . '/../../logs/error_log.txt');
            return false;
        }

        $data = json_decode(file_get_contents($this->tokenFile), true);

        if (!isset($data['access_token'], $data['expires_at'])) {
            error_log("CredentialValidator: Token file missing required fields", 3, __DIR__ . '/../../logs/error_log.txt');
            return false;
        }

        return time() < $data['expires_at'];
    }

    private function logErrors(array $errors): void {
        $logPath = __DIR__ . '/../../logs/error_log.txt';
        foreach ($errors as $error) {
            error_log("CredentialValidator: $error", 3, $logPath);
        }
    }
}
