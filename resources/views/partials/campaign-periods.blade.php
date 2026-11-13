<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Campagne</th><th>Étape</th><th>Ouverture</th><th>Clôture</th><th>État</th></tr></thead>
        <tbody>
            @forelse ($periods as $period)
                <tr>
                    <td>{{ $period->academic_year }}</td>
                    <td>{{ \App\Models\CampaignPeriod::PHASES[$period->phase] }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($period->opens_on)->format('d/m/Y') }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($period->closes_on)->format('d/m/Y') }}</td>
                    <td>{{ $period->statusLabel() }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune période publiée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $periods->links() }}
