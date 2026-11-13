<!doctype html>
<html lang="fr">
<body>
    <h1>Votre candidature est retenue</h1>
    <p>Bonjour {{ $candidateName }},</p>
    <p>Votre candidature au sujet « {{ $subjectTitle }} » a été retenue. Consultez votre dossier GCD+ pour connaître les pièces à fournir et les échéances de l’inscription administrative.</p>
    @if ($startDate)<p>Début prévu de la formation le <strong>{{ $startDate }}</strong>.</p>@endif
    <p><a href="{{ $applicationUrl }}">Voir ma candidature</a></p>
    <p>GCD+ · EST de Fès — USMBA</p>
</body>
</html>
