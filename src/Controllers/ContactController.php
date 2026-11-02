<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Mailer;

final class ContactController
{
    public function formulaire(): void
    {
        $erreurs = $_SESSION['erreurs'] ?? [];
        render('contact', ['titrePage' => 'Contact', 'erreurs' => $erreurs]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    public function envoyer(): void
    {
        verifierCsrf();

        // Champ piège invisible : rempli uniquement par les robots
        if (!empty($_POST['site_web'])) {
            redirect('/contact');
        }

        $titre       = trim((string) ($_POST['titre'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $email       = trim((string) ($_POST['email'] ?? ''));

        $erreurs = [];
        if (mb_strlen($titre) < 3 || mb_strlen($titre) > 150) {
            $erreurs['titre'] = 'Le titre doit contenir entre 3 et 150 caractères.';
        }
        if (mb_strlen($description) < 10 || mb_strlen($description) > 3000) {
            $erreurs['description'] = 'Votre message doit contenir entre 10 et 3000 caractères.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Merci de saisir une adresse e-mail valide.';
        }
        // Limitation : un message par minute et par session (anti-spam)
        if (isset($_SESSION['dernier_contact']) && time() - $_SESSION['dernier_contact'] < 60) {
            $erreurs['global'] = 'Vous venez déjà d\'envoyer un message. Merci de patienter une minute.';
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = ['titre' => $titre, 'description' => $description, 'email' => $email];
            redirect('/contact');
        }

        $corps = '<p><strong>Nouvelle demande depuis le formulaire de contact.</strong></p>'
            . '<p><strong>Titre :</strong> ' . e($titre) . '</p>'
            . '<p><strong>E-mail :</strong> ' . e($email) . '</p>'
            . '<p><strong>Message :</strong><br>' . nl2br(e($description)) . '</p>';

        $sujet = 'Contact : ' . str_replace(["\r", "\n"], ' ', $titre);
        $ok = Mailer::envoyer(env('MAIL_ENTREPRISE', 'contact@vite-gourmand.fr'), $sujet, $corps, $email);

        if ($ok) {
            $_SESSION['dernier_contact'] = time();
            flash('success', 'Merci, votre message a bien été envoyé. Nous vous répondrons rapidement par e-mail.');
        } else {
            flash('danger', 'Une erreur est survenue lors de l\'envoi. Merci de réessayer plus tard.');
        }
        redirect('/contact');
    }
}
