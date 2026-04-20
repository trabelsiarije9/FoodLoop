<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\PlatformRepository;
use App\Support\ActorContext;
use App\Support\PlatformClock;
use App\Support\UmlModelCatalog;
use App\Views\View;

final class HomeController
{
    public function index(): string
    {
        $repository = new PlatformRepository();
        $actorRole = ActorContext::resolve($_GET);
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'category' => $this->normalizeInteger($_GET['category'] ?? null),
            'zone' => $this->normalizeInteger($_GET['zone'] ?? null),
        ];

        $announcements = array_map(
            static fn (array $announcement): array => $announcement + [
                'action' => ActorContext::announcementAction($actorRole, $announcement['statut']),
            ],
            $repository->searchAnnouncements($filters, $actorRole)
        );

        return View::render('home', [
            'platform' => $repository->getPlatformOverview($actorRole),
            'roles' => ActorContext::catalogue(),
            'currentRole' => ActorContext::details($actorRole),
            'currentActor' => $repository->getCurrentActorSnapshot($actorRole),
            'filters' => $filters,
            'categories' => $repository->getCategories(),
            'zones' => $repository->getZones(),
            'announcements' => $announcements,
            'preferences' => $repository->getPreferenceProfile(),
            'consultations' => $repository->getConsultationHistory(),
            'reservationFlow' => $repository->getReservationFlow(),
            'notifications' => $repository->getNotificationsForRole($actorRole),
            'suggestions' => $repository->getSuggestions($actorRole),
            'reports' => $repository->getReports(),
            'databaseCoverage' => $repository->getDatabaseCoverage(),
            'umlClasses' => UmlModelCatalog::classes(),
            'umlNotes' => UmlModelCatalog::integrationNotes(),
            'referenceNow' => PlatformClock::displayReferenceNow(),
        ]);
    }

    private function normalizeInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_INT);

        return $normalized === false ? null : (int) $normalized;
    }
}
