<?php

namespace Database\Seeders;

use App\Enums\DegreeTrack;
use App\Enums\DocumentType;
use App\Enums\StaffFunction;
use App\Enums\UserRole;
use App\Models\ResearchSubject;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class ExamDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $this->call(EstfDemoSeeder::class);
        $supervisor = User::where('email', 'encadrant@estf.example')->firstOrFail();
        $subjects = ResearchSubject::where('professor_id', $supervisor->id)->get();
        $password = env('GCD_DEMO_PASSWORD');
        if (! is_string($password) || strlen($password) < 12) {
            throw new \RuntimeException('Définissez GCD_DEMO_PASSWORD (12 caractères minimum) avant de préparer les comptes.');
        }

        $supervisor->update(['password' => $password, 'is_active' => true]);
        $this->account('admin', 'Administration de démonstration', UserRole::Admin, $password);
        foreach (StaffFunction::cases() as $function) {
            $account = $this->account($function->value, $function->label().' (démo)', UserRole::Staff, $password);
            foreach ($subjects as $subject) {
                $account->staffAssignments()->firstOrCreate([
                    'research_subject_id' => $subject->id,
                    'function' => $function->value,
                ]);
            }
        }

        $candidate = $this->account('candidat', 'Amine Démonstration', UserRole::Candidate, $password);
        if ($candidate->profile?->isComplete()) {
            return;
        }

        $profiles = app(ProfileService::class);
        $profile = $profiles->updateDetails($candidate, [
            'first_name' => 'Amine',
            'last_name' => 'Démonstration',
            'birth_date' => '2000-01-15',
            'birth_place' => 'Fès',
            'nationality' => 'Marocaine',
            'gender' => 'male',
            'cin_or_passport_number' => 'DEMO0001',
            'address' => 'Adresse fictive, Fès',
            'phone' => '+212 600 000 000',
            'degree_track' => DegreeTrack::Engineer,
            'last_degree' => 'Diplôme d’ingénieur',
            'last_institution' => 'Établissement fictif',
            'graduation_year' => 2025,
            'field_of_study' => 'Génie industriel',
            'bac_institution' => 'Lycée fictif',
            'bac_graduation_year' => 2020,
            'bac_field_of_study' => 'Sciences',
        ]);
        $path = database_path('fixtures/piece-demo.pdf');
        $types = [DocumentType::CinPassport, ...DocumentType::academicRequiredDocuments()];
        $files = [];
        foreach ($types as $type) {
            $files[$type->value] = new UploadedFile($path, $type->value.'.pdf', 'application/pdf', null, true);
        }
        $profiles->uploadDocuments($profile, $files);
    }

    private function account(string $alias, string $name, UserRole $role, string $password): User
    {
        return User::updateOrCreate(['email' => $alias.'@demo.estf.example'], [
            'name' => $name,
            'role' => $role,
            'password' => $password,
            'is_active' => true,
        ]);
    }
}
