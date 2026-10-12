<?php

class Database
{
    private static ?Database $instance = null;
    private string $supabaseUrl = 'https://zsubfqsgoaysyczpmxbq.supabase.co';
    private string $supabaseKey;
    private function __construct()
    {
        $this->loadEnvironmentFile();
        $supabaseKey = getenv('SUPABASE_SERVICE_ROLE_KEY');
        if ($supabaseKey === false || $supabaseKey === '') {
            throw new RuntimeException('SUPABASE_SERVICE_ROLE_KEY is niet ingesteld.');
        }

        $this->supabaseKey = $supabaseKey;
    }
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getSupabaseUrl(): string
    {
        return $this->supabaseUrl;
    }
    public function getSupabaseKey(): string
    {
        return $this->supabaseKey;
    }

    private function loadEnvironmentFile(): void
    {
        $environmentFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (!is_readable($environmentFile)) {
            return;
        }

        foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($name !== '') {
                putenv($name . '=' . $value);
            }
        }
    }
}
