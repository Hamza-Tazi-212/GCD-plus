@if (in_array($renewal->status, ['favorable', 'confirmed', 'rejected'], true) || $renewal->approvals->isNotEmpty())
    <h4 class="h6 mt-3">Avis et accords institutionnels</h4>
    <ul class="small">
        @foreach (\App\Models\Renewal::APPROVALS as $step)
            @php($approval = $renewal->approvals->firstWhere('function', $step->value))
            <li>
                <strong>{{ $step->label() }}</strong>
                @if ($approval)
                    · {{ $approval->approved ? 'Favorable' : 'Défavorable' }}
                    · {{ $approval->user->name }}
                    · {{ $approval->created_at->timezone('Africa/Casablanca')->format('d/m/Y H:i') }}
                    <div>{{ $approval->comment }}</div>
                @else
                    · {{ in_array($renewal->status, ['confirmed', 'rejected'], true) ? 'Sans avis enregistré' : 'En attente' }}
                @endif
            </li>
        @endforeach
    </ul>
@endif
