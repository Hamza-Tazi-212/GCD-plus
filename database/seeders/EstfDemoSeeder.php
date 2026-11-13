<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\ResearchSubject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EstfDemoSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(['code' => 'ST'], ['name' => 'Sciences et techniques']);
        $professor = User::firstOrCreate(['email' => 'encadrant@estf.example'], [
            'name' => 'Encadrant de démonstration',
            'password' => Str::random(40),
            'role' => UserRole::Professor,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        foreach ([
            ['Maintenance prédictive des équipements industriels', 'Étudier les mesures de vibration pour détecter les défauts et anticiper les pannes.', 'Génie industriel'],
            ['Pilotage intelligent de systèmes photovoltaïques', 'Étudier des stratégies de pilotage et de stockage pour une installation photovoltaïque.', 'Énergies renouvelables'],
            ['Optimisation des tournées de livraison en ville', 'Comparer des méthodes d’optimisation de tournées selon les contraintes de mobilité.', 'Logistique'],
            ['Détection de défauts par intelligence artificielle embarquée', 'Évaluer la précision et la consommation de modèles légers sur des capteurs industriels.', 'Intelligence artificielle'],
            ['Jumeau numérique pour le pilotage d’une ligne de production', 'Comparer les performances d’une ligne réelle et de son modèle pour tester des scénarios de production.', 'Génie industriel'],
            ['Diagnostic des moteurs électriques par analyse de courant', 'Détecter les défauts naissants à partir des signatures de courant et de données de banc d’essai.', 'Génie électrique'],
            ['Optimisation énergétique des bâtiments universitaires', 'Mesurer les consommations et évaluer des stratégies de pilotage du chauffage et de la ventilation.', 'Énergie et environnement'],
            ['Gestion de la recharge des véhicules électriques', 'Planifier les sessions de recharge en tenant compte des contraintes du réseau et des usages.', 'Génie électrique'],
            ['Recyclage des déchets plastiques en fabrication additive', 'Caractériser des filaments recyclés et mesurer la résistance mécanique des pièces imprimées.', 'Matériaux'],
            ['Contrôle qualité par vision industrielle', 'Évaluer une chaîne de détection de défauts visuels sur des pièces soumises à différentes conditions d’éclairage.', 'Intelligence artificielle'],
            ['Planification de la maintenance des installations solaires', 'Comparer des stratégies de maintenance à partir de données de production et de vieillissement.', 'Énergies renouvelables'],
            ['Optimisation des stocks hospitaliers', 'Simuler les effets des règles de réapprovisionnement sur les ruptures et le gaspillage de consommables.', 'Logistique'],
            ['Suivi de la qualité de l’air par capteurs connectés', 'Évaluer la fiabilité de capteurs à bas coût et améliorer la qualité de leurs mesures en milieu urbain.', 'Énergie et environnement'],
            ['Navigation de robots mobiles en atelier', 'Comparer des méthodes de localisation et d’évitement des obstacles dans des espaces partagés.', 'Robotique'],
            ['Évaluation du confort thermique avec des matériaux locaux', 'Comparer en laboratoire les performances thermiques et la durabilité de matériaux de construction locaux.', 'Matériaux'],
            ['Pilotage de l’irrigation par mesure de l’humidité du sol', 'Évaluer des stratégies d’arrosage assistées par capteurs pour réduire la consommation d’eau.', 'Énergie et environnement'],
            ['Planification de la collecte des déchets urbains', 'Optimiser des tournées en tenant compte des volumes, des horaires et des contraintes de circulation.', 'Logistique'],
            ['Surveillance de la consommation des machines industrielles', 'Identifier les périodes de fonctionnement inefficace à partir de mesures électriques.', 'Génie industriel'],
            ['Détection automatique des anomalies dans des réseaux de capteurs', 'Comparer des méthodes de détection de mesures incohérentes et de pannes de capteurs.', 'Intelligence artificielle'],
            ['Écoconception de pièces mécaniques imprimées en 3D', 'Mesurer les compromis entre masse, résistance et impact environnemental de plusieurs géométries.', 'Matériaux'],
            ['Aide à la décision pour le choix d’équipements industriels', 'Évaluer une méthode multicritère tenant compte du coût, de la fiabilité et de la consommation énergétique.', 'Génie industriel'],
        ] as [$title, $description, $field]) {
            $laboratory = in_array($field, ['Énergies renouvelables', 'Énergie et environnement'], true)
                ? 'Énergie et environnement (démo)' : 'Technologies et Services Industriels (TSI)';
            $subject = ResearchSubject::firstOrCreate(['professor_id' => $professor->id, 'title' => $title], [
                'department_id' => $department->id,
                'description' => 'Sujet de démonstration. '.$description,
                'is_open' => true,
                'laboratory' => $laboratory,
                'field' => $field,
            ]);
            $subject->update([
                'laboratory' => $subject->laboratory ?? $laboratory,
                'field' => $subject->field ?? $field,
            ]);
        }
    }
}
