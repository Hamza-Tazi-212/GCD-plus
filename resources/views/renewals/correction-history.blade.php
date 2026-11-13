@if ($renewal->correction_history)
    <details class="my-2" @if($renewal->status === 'correction') open @endif>
        <summary>Historique des demandes de complément</summary>
        <ul class="mt-2">
            @foreach ($renewal->correction_history as $entry)
                <li>
                    {{ \Illuminate\Support\Carbon::parse($entry['requested_at'])->timezone('Africa/Casablanca')->format('d/m/Y H:i') }} · {{ $entry['reason'] }}
                    @if (isset($entry['resubmitted_at']))
                        <br><small>Dossier corrigé le {{ \Illuminate\Support\Carbon::parse($entry['resubmitted_at'])->timezone('Africa/Casablanca')->format('d/m/Y H:i') }}</small>
                    @endif
                </li>
            @endforeach
        </ul>
    </details>
@endif
