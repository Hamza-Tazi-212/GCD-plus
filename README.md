# GCD+ · Gestion du cycle doctoral

Prototype de l’atelier O&M Développement Logiciel adapté à l’École Supérieure de Technologie de Fès. Le projet couvre le catalogue public, le panier, les candidatures, l’inscription administrative et la réinscription.

- [Déroulement du développement](docs/gcd-plus/developpement.md)
- [Diagrammes de GCD+](docs/gcd-plus/diagrammes/README.md)
- [TP 5 et diagrammes de conception](docs/tp-seance-5/README.md)
- [Tableau comparatif des pistes d’amélioration](docs/tableau-comparatif-gcd.pdf)

## Démarrer en local

Prérequis pour cette version du projet. PHP 8.2 ou supérieur avec PDO SQLite, Composer et Node.js 22.12 ou supérieur sur la branche 22, ou une branche LTS plus récente.

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
npm ci
npm run build
php artisan db:seed
php artisan serve
```

Ouvrir [GCD+ en local](http://127.0.0.1:8000). Le catalogue propose 21 sujets de démonstration.

## Tester les rôles

Dans `.env`, renseigner `GCD_DEMO_PASSWORD` avec un mot de passe local d’au moins 12 caractères. Exécuter ensuite `php artisan db:seed --class=ExamDemoSeeder` en environnement local. Ce jeu d’essai utilise des identités et des pièces fictives. Les comptes partagent le mot de passe choisi dans `.env`.

| Compte de démonstration | Espace |
| --- | --- |
| `candidat@demo.estf.example` | Candidatures, pièces et réinscriptions |
| `encadrant@estf.example` | Sujets et avis de l’encadrant |
| `admin@demo.estf.example` | Administration |
| `co_supervisor@demo.estf.example` | Co-encadrement |
| `lab@demo.estf.example` | Structure de recherche |
| `ced@demo.estf.example` | Centre d’études doctorales |
| `selection@demo.estf.example` | Commission de sélection |
| `thesis@demo.estf.example` | Comité de thèse |
| `head@demo.estf.example` | Chef d’établissement |
| `manager@demo.estf.example` | Gestionnaire ESTF |

Exécuter les tests avec `php artisan test`.

## Provenance

GCD+ adapte le projet CDOC de Yassine Khaldi à partir de la copie de travail fournie pour l’atelier. Avant de redistribuer cette adaptation, vérifier l’autorisation de publication du code de CDOC auprès de son auteur.
