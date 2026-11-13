@if ($period)
    <p class="small mb-2">
        <strong>{{ \App\Models\CampaignPeriod::PHASES[$period->phase] }} · {{ $period->statusLabel() }}</strong><br>
        Du {{ \Illuminate\Support\Carbon::parse($period->opens_on)->format('d/m/Y') }}
        au {{ \Illuminate\Support\Carbon::parse($period->closes_on)->format('d/m/Y') }} inclus
    </p>
@endif
