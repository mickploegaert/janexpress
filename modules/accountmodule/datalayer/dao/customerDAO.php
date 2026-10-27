<?php

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../entities/user.php';

class CustomerDAO
{
    private Database $database;
    private string $endpoint = '/rest/v1/users';

    public function __construct()
    {
        $this->database = Database::getInstance();

        if (!$this->hasSecretKey($this->database->getSupabaseKey())) {
            throw new RuntimeException('SUPABASE_KEY_NOT_SECRET');
        }
    }

    public function findByEmail(string $email): ?User
    {
        $rows = $this->request('GET', '?' . http_build_query([
            'select' => 'id,name,email,phone_number,role,password_hash',
            'email' => 'ilike.' . $this->escapeLike($email),
            'limit' => 1,
        ]));

        if (isset($rows[0])) {
            return $this->createUserFromRow($rows[0]);
        }

        return null;
    }

    public function findById(string $id): ?User
    {
        if (preg_match('/^[0-9a-fA-F-]{36}$/', $id) !== 1) {
            return null;
        }

        $rows = $this->request('GET', '?' . http_build_query([
            'select' => 'id,name,email,phone_number,role,password_hash',
            'id' => 'eq.' . $id,
            'limit' => 1,
        ]));

        if (isset($rows[0])) {
            return $this->createUserFromRow($rows[0]);
        }

        return null;
    }

    public function create(User $user, string $password): User
    {
        $name = trim($user->getFirstName() . ' ' . $user->getLastName());
        $authId = $this->createAuthUser($user->getEmail(), $password, $name);
        $profile = [
            'id' => $authId,
            'name' => $name,
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhone(),
            'role' => $user->getRole(),
            'password_hash' => $user->getPasswordHash(),
        ];

        try {
            $rows = $this->request('POST', '', $profile, true);
        } catch (RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), '23505')) {
                $this->authRequest('DELETE', '/admin/users/' . rawurlencode($authId));
                throw $exception;
            }

            if ($this->findById($authId) === null) {
                $this->authRequest('DELETE', '/admin/users/' . rawurlencode($authId));
                throw new InvalidArgumentException('Dit e-mailadres is al in gebruik.');
            }

            unset($profile['id']);
            $rows = $this->request('PATCH', '?' . http_build_query([
                'id' => 'eq.' . $authId,
            ]), $profile, true);
        }

        if (!isset($rows[0]['id'])) {
            throw new RuntimeException('Supabase HTTP 500');
        }

        return $this->createUserFromRow($rows[0]);
    }

    public function updateProfile(User $current, User $updated): User
    {
        $id = $updated->getId();
        if (!is_string($id) || preg_match('/^[0-9a-fA-F-]{36}$/', $id) !== 1 || $id !== $current->getId()) {
            throw new InvalidArgumentException('Je account is niet gevonden.');
        }

        $name = trim($updated->getFirstName() . ' ' . $updated->getLastName());
        $emailChanged = $updated->getEmail() !== $current->getEmail();
        $authPayload = [
            'user_metadata' => ['name' => $name],
        ];
        if ($emailChanged) {
            $authPayload['email'] = $updated->getEmail();
            $authPayload['email_confirm'] = true;
        }

        try {
            $this->authRequest('PUT', '/admin/users/' . rawurlencode($id), $authPayload);
        } catch (RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), 'HTTP 404')) {
                throw $exception;
            }
        }

        try {
            $rows = $this->request('PATCH', '?' . http_build_query([
                'id' => 'eq.' . $id,
            ]), [
                'name' => $name,
                'email' => $updated->getEmail(),
                'phone_number' => $updated->getPhone(),
            ], true);
        } catch (RuntimeException $exception) {
            if ($emailChanged) {
                try {
                    $this->authRequest('PUT', '/admin/users/' . rawurlencode($id), [
                        'email' => $current->getEmail(),
                        'email_confirm' => true,
                        'user_metadata' => [
                            'name' => trim($current->getFirstName() . ' ' . $current->getLastName()),
                        ],
                    ]);
                } catch (RuntimeException) {
                }
            }

            throw $exception;
        }

        if (!isset($rows[0]['id'])) {
            throw new RuntimeException('Supabase HTTP 500');
        }

        return $this->createUserFromRow($rows[0]);
    }

    private function createAuthUser(string $email, string $password, string $name): string
    {
        $user = $this->authRequest('POST', '/admin/users', [
            'email' => $email,
            'password' => $password,
            'email_confirm' => true,
            'user_metadata' => ['name' => $name],
        ]);

        $id = $user['id'] ?? ($user['user']['id'] ?? null);
        if (!is_string($id) || $id === '') {
            throw new RuntimeException('Supabase HTTP 500');
        }

        return $id;
    }

    private function createUserFromRow(array $row): User
    {
        $phone = $row['phone_number'] ?? null;
        [$firstName, $lastName] = $this->splitName((string) ($row['name'] ?? ''));
        $passwordHash = $row['password_hash'] ?? '';
        $role = $row['role'] ?? 'customer';

        return new User(
            (string) $row['id'],
            $firstName,
            $lastName,
            (string) $row['email'],
            is_string($phone) && $phone !== '' ? $phone : null,
            is_string($passwordHash) ? $passwordHash : '',
            is_string($role) && $role !== '' ? $role : 'customer'
        );
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function hasSecretKey(string $key): bool
    {
        if (str_starts_with($key, 'sb_secret_')) {
            return true;
        }

        if (!str_starts_with($key, 'eyJ')) {
            return false;
        }

        $parts = explode('.', $key);
        if (count($parts) < 2) {
            return false;
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        return is_array($payload) && ($payload['role'] ?? '') === 'service_role';
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }

    private function request(string $method, string $query, ?array $data = null, bool $returnRow = false): array
    {
        $curl = curl_init(
            rtrim($this->database->getSupabaseUrl(), '/') . $this->endpoint . $query
        );

        if ($curl === false) {
            throw new RuntimeException('Supabase-request kon niet worden gestart.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->database->getSupabaseKey(),
                'Authorization: Bearer ' . $this->database->getSupabaseKey(),
                'Content-Type: application/json',
                'Prefer: ' . ($returnRow ? 'return=representation' : 'return=minimal'),
            ],
            CURLOPT_POSTFIELDS => $data === null
                ? null
                : json_encode($data, JSON_THROW_ON_ERROR),
        ]);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('Supabase-request mislukt: ' . $error);
        }

        if ($status < 200 || $status >= 300) {
            $decoded = json_decode($response, true);
            $code = is_array($decoded) ? (string) ($decoded['code'] ?? '') : '';

            throw new RuntimeException('Supabase HTTP ' . $status . ' ' . $code);
        }

        if ($response === '') {
            return [];
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }

    private function authRequest(string $method, string $path, ?array $data = null): array
    {
        $curl = curl_init(rtrim($this->database->getSupabaseUrl(), '/') . '/auth/v1' . $path);

        if ($curl === false) {
            throw new RuntimeException('Supabase-request kon niet worden gestart.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->database->getSupabaseKey(),
                'Authorization: Bearer ' . $this->database->getSupabaseKey(),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $data === null
                ? null
                : json_encode($data, JSON_THROW_ON_ERROR),
        ]);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('Supabase-request mislukt: ' . $error);
        }

        if ($status < 200 || $status >= 300) {
            $decoded = json_decode($response, true);
            $code = is_array($decoded) ? (string) ($decoded['error_code'] ?? $decoded['code'] ?? '') : '';
            $message = is_array($decoded) ? (string) ($decoded['msg'] ?? $decoded['message'] ?? '') : '';

            if ($code === 'email_exists' || str_contains(strtolower($message), 'already')) {
                throw new InvalidArgumentException('Dit e-mailadres is al in gebruik.');
            }

            throw new RuntimeException('Supabase HTTP ' . $status . ' ' . $code);
        }

        if ($response === '') {
            return [];
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
