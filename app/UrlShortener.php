<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class UrlShortener
{
    private const CODE_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const CODE_LENGTH = 7;

    public static function create(string $url): string
    {
        $pdo = Database::pdo();

        $code = self::generateUniqueCode($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO urls (short_code, original_url) VALUES (:code, :url)'
        );
        $stmt->execute([
            ':code' => $code,
            ':url' => $url,
        ]);

        return $code;
    }

    public static function resolve(string $code): ?string
    {
        $stmt = Database::pdo()->prepare(
            'SELECT original_url, clicks FROM urls WHERE short_code = :code LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        $update = Database::pdo()->prepare(
            'UPDATE urls SET clicks = clicks + 1 WHERE short_code = :code'
        );
        $update->execute([':code' => $code]);

        return $row['original_url'];
    }

    public static function recent(int $limit = 10): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT short_code, original_url, clicks, created_at
             FROM urls
             ORDER BY id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function isValidUrl(string $url): bool
    {
        $value = filter_var($url, FILTER_VALIDATE_URL);
        if ($value === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    private static function generateUniqueCode(PDO $pdo): string
    {
        do {
            $code = '';
            $length = self::CODE_LENGTH;
            $max = strlen(self::CODE_ALPHABET) - 1;

            for ($i = 0; $i < $length; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, $max)];
            }

            $stmt = $pdo->prepare(
                'SELECT 1 FROM urls WHERE short_code = :code LIMIT 1'
            );
            $stmt->execute([':code' => $code]);
        } while ($stmt->fetch() !== false);

        return $code;
    }
}
