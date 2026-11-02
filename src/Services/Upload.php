<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Envoi sécurisé d'images (photos des menus et des plats).
 *  - taille limitée, vérification du vrai type du fichier (et non de son extension)
 *  - l'image est ré-encodée avec GD : les métadonnées et tout contenu caché sont supprimés
 *  - nom de fichier aléatoire : impossible d'écraser un fichier ou d'exécuter un script
 */
final class Upload
{
    private const TAILLE_MAX = 3 * 1024 * 1024; // 3 Mo
    private const TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const LARGEUR_MAX = 1400;
    public const DOSSIER = 'assets/images/uploads';

    /**
     * Enregistre une image envoyée. Retourne le chemin relatif (ex. assets/images/uploads/ab12.jpg)
     * ou lève une exception avec un message lisible.
     */
    public static function image(array $fichier): string
    {
        if (($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('L\'envoi de l\'image a échoué.');
        }
        if (!is_uploaded_file($fichier['tmp_name'])) {
            throw new \RuntimeException('Fichier invalide.');
        }
        if ($fichier['size'] > self::TAILLE_MAX) {
            throw new \RuntimeException('L\'image dépasse 3 Mo.');
        }

        $type = (new \finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
        if (!in_array($type, self::TYPES, true)) {
            throw new \RuntimeException('Format non accepté : seules les images JPEG, PNG ou WebP sont autorisées.');
        }

        $source = match ($type) {
            'image/jpeg' => @imagecreatefromjpeg($fichier['tmp_name']),
            'image/png'  => @imagecreatefrompng($fichier['tmp_name']),
            'image/webp' => @imagecreatefromwebp($fichier['tmp_name']),
        };
        if (!$source) {
            throw new \RuntimeException('Image illisible ou corrompue.');
        }

        // Redimensionnement si l'image est trop large
        $largeur = imagesx($source);
        $hauteur = imagesy($source);
        if ($largeur > self::LARGEUR_MAX) {
            $nouvelleHauteur = (int) round($hauteur * self::LARGEUR_MAX / $largeur);
            $copie = imagecreatetruecolor(self::LARGEUR_MAX, $nouvelleHauteur);
            imagecopyresampled($copie, $source, 0, 0, 0, 0, self::LARGEUR_MAX, $nouvelleHauteur, $largeur, $hauteur);
            imagedestroy($source);
            $source = $copie;
        }

        $nom = bin2hex(random_bytes(12)) . '.jpg';
        $dossier = ROOT . '/public/' . self::DOSSIER;
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }
        imagejpeg($source, $dossier . '/' . $nom, 85);
        imagedestroy($source);

        return self::DOSSIER . '/' . $nom;
    }

    /** Supprime un fichier envoyé (uniquement dans le dossier des uploads). */
    public static function supprimer(?string $chemin): void
    {
        if ($chemin && str_starts_with($chemin, self::DOSSIER . '/') && !str_contains($chemin, '..')) {
            @unlink(ROOT . '/public/' . $chemin);
        }
    }

    /** Liste les fichiers envoyés via un champ "multiple" sous forme de tableau simple. */
    public static function normaliser(array $champ): array
    {
        $fichiers = [];
        foreach ((array) ($champ['name'] ?? []) as $i => $nom) {
            if (($champ['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $fichiers[] = [
                'name' => $nom, 'type' => $champ['type'][$i], 'tmp_name' => $champ['tmp_name'][$i],
                'error' => $champ['error'][$i], 'size' => $champ['size'][$i],
            ];
        }
        return $fichiers;
    }
}
