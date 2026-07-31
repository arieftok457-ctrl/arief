<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class Wedding
{
    public static function saveRsvp(string $name, string $attendance, int $guests, ?string $message): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO wedding_rsvp (name, attendance, guests, message)
             VALUES (:name, :attendance, :guests, :message)'
        );
        $stmt->execute([
            ':name' => $name,
            ':attendance' => $attendance === 'tidak_hadir' ? 'tidak_hadir' : 'hadir',
            ':guests' => max(1, min(10, $guests)),
            ':message' => $message,
        ]);
    }

    public static function messages(int $limit = 30): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT name, attendance, guests, message, created_at
             FROM wedding_rsvp
             ORDER BY id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function count(string $attendance): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) AS total FROM wedding_rsvp WHERE attendance = :attendance'
        );
        $stmt->execute([':attendance' => $attendance]);

        return (int) $stmt->fetch()['total'];
    }
}
