<!doctype html>
<html lang="fr">
<body>
    <h1>Convocation à un entretien oral</h1>
    <p>Bonjour {{ $candidateName }},</p>
    <p>Vous êtes convoqué à un entretien pour le sujet « {{ $subjectTitle }} ».</p>
    @if ($examDate)<p><strong>Date et heure de Fès</strong><br>{{ $examDate }}</p>@endif
    @if ($reviewComment ?? null)<p>{{ $reviewComment }}</p>@endif
    <p><a href="{{ $applicationUrl }}">Consulter ma convocation</a></p>
    <p>GCD+ · EST de Fès — USMBA</p>
</body>
</html>
