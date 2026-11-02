<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envoi des mails via PHPMailer.
 * Si aucun serveur SMTP n'est configuré (développement), le mail est
 * enregistré dans storage/mails pour pouvoir le consulter.
 */
final class Mailer
{
    public static function envoyer(string $destinataire, string $sujet, string $html, ?string $repondreA = null): bool
    {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(env('MAIL_FROM', 'no-reply@vite-gourmand.fr'), env('MAIL_FROM_NAME', 'Vite & Gourmand'));
            $mail->addAddress($destinataire);
            if ($repondreA !== null) {
                $mail->addReplyTo($repondreA);
            }
            $mail->isHTML(true);
            $mail->Subject = $sujet;
            $mail->Body    = self::gabarit($sujet, $html);
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>'], "\n", $html))));

            if (env('SMTP_HOST', '') === '') {
                // Mode développement : écriture du mail dans un fichier .eml
                $mail->preSend();
                $fichier = ROOT . '/storage/mails/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.eml';
                file_put_contents($fichier, $mail->getSentMIMEMessage());
                return true;
            }

            $mail->isSMTP();
            $mail->Host       = env('SMTP_HOST');
            $mail->Port       = (int) env('SMTP_PORT', '587');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('SMTP_USER', '');
            $mail->Password   = env('SMTP_PASSWORD', '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            return $mail->send();
        } catch (Exception $e) {
            error_log('Erreur envoi mail : ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function gabarit(string $titre, string $contenu): string
    {
        return '<!DOCTYPE html><html lang="fr"><body style="margin:0;background:#FBF6EE;font-family:Arial,sans-serif;color:#2A211C">'
            . '<div style="max-width:600px;margin:0 auto;background:#fff">'
            . '<div style="background:#7A1F3D;color:#fff;padding:20px 28px;font-size:22px;font-weight:bold">Vite &amp; Gourmand</div>'
            . '<div style="padding:28px;font-size:15px;line-height:1.6"><h1 style="color:#7A1F3D;font-size:20px;margin-top:0">'
            . htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') . '</h1>' . $contenu . '</div>'
            . '<div style="background:#2A211C;color:#F3ECE2;padding:16px 28px;font-size:12px">Vite &amp; Gourmand — 12 rue Sainte-Catherine, 33000 Bordeaux</div>'
            . '</div></body></html>';
    }
}
