<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactMessageModel;

final class ContactController extends Controller
{
    public function submit(): string
    {
        $this->requireCsrf();

        $data = [
            'fullname' => trim((string) ($_POST['fullname'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'organization' => trim((string) ($_POST['organization'] ?? '')),
            'role' => trim((string) ($_POST['role'] ?? '')),
            'message' => trim((string) ($_POST['message'] ?? '')),
        ];

        store_old_input($data);

        if ($data['fullname'] === '' || $data['email'] === '' || $data['message'] === '') {
            flash('error', 'Nom, email et message sont obligatoires.');
            redirect('contact');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Adresse email invalide.');
            redirect('contact');
        }

        try {
            (new ContactMessageModel())->create($data);
        } catch (\Throwable $exception) {
            flash('error', 'Brief impossible a enregistrer: ' . $exception->getMessage());
            redirect('contact');
        }

        clear_old_input();
        flash('success', 'Brief envoye. Le message a bien ete enregistre.');
        redirect('contact');
    }
}
