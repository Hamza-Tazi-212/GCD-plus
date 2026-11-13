<!doctype html>
<html lang="fr">
<body>
    <h1>Décision sur votre candidature</h1>
    <p>Bonjour {{ $candidateName }},</p>
    <p>Après examen de votre dossier, votre candidature au sujet « {{ $subjectTitle }} » n’a pas été retenue.</p>
    @if ($reviewComment)<p><strong>Motif</strong><br>{{ $reviewComment }}</p>@endif
    <p><a href="{{ $applicationUrl }}">Voir ma candidature</a></p>
    <p>GCD+ · EST de Fès — USMBA</p>
</body>
</html>
