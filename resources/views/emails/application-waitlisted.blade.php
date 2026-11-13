<!doctype html>
<html lang="fr">
<body>
    <p>Bonjour {{ $application->fullName() }},</p>
    <p>Votre candidature au sujet « {{ $application->subject->title }} » est sur liste d’attente. Si une place vous est attribuée, votre dossier sera mis à jour dans GCD+.</p>
    <p><a href="{{ route('candidate.applications.show', $application) }}">Suivre ma candidature</a></p>
</body>
</html>
