<?php

namespace App\Enums;

enum StaffFunction: string
{
    case CoSupervisor = 'co_supervisor';
    case Lab = 'lab';
    case Ced = 'ced';
    case Selection = 'selection';
    case Thesis = 'thesis';
    case Head = 'head';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::CoSupervisor => 'Co-encadrant',
            self::Lab => 'Responsable de la structure de recherche',
            self::Ced => 'Directeur du CED',
            self::Selection => 'Membre de commission de sélection',
            self::Thesis => 'Membre du comité de thèse',
            self::Head => 'Chef d’établissement',
            self::Manager => 'Gestionnaire ESTF',
        };
    }

    public function remit(): string
    {
        return match ($this) {
            self::CoSupervisor => 'Sujets et candidatures associés à vos co-encadrements.',
            self::Lab => 'Sujets et demandes de réinscription relevant de votre structure.',
            self::Ced => 'Sujets et réinscriptions relevant du centre doctoral.',
            self::Selection => 'Candidatures à examiner dans le cadre de la sélection.',
            self::Thesis => 'Bilans annuels des doctorants suivis par votre comité.',
            self::Head => 'Réinscriptions à examiner pour l’accord de l’établissement.',
            self::Manager => 'Dossiers retenus pour l’inscription administrative et demandes de réinscription.',
        };
    }
}
