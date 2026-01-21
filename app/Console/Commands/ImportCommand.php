<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\ActionTypeEnum;
use App\Enums\DepartmentEnum;
use App\Models\Action;
use App\Models\Odd;
use App\Models\OperationalObjective;
use App\Models\Partner;
use App\Models\Service;
use App\Models\StrategicObjective;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pst:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command';

    protected string $dir = __DIR__.'/../../../old/output/';

    private int $lastOs = 0;

    private int $lastOo = 0;

    private ImportDto $actionDto;

    private string $department;

    private array $os = [
        "Être un CPAS favorisant l'autonomie et l'inclusion dans la société des personnes fragilisées ou exclues socialement pour qu'elles puissent mener une vie conforme à la dignité humaine et renforcer leur résilience",
        'Être un CPAS qui intègre le vieillissement de la population au centre de ses préocupations, par une offre adaptée, digne et diversifiée.',
        'Être un CPAS performant et impliqué dirigé par un management proactif et empathique',
    ];

    private array $oos = [
        ['os' => 1, 'name' => "Développer l'accès au logement décent et salubre et accompagner les bénéficiaires dans leurs démarches logement (recherche - maintien - déménagement). Diversifier l'offre de logement."],
        ['os' => 1, 'name' => 'Lutter contre les violences conjugales ou intrafamiliales, faites essentiellement aux femmes et à leurs enfants'],
        ['os' => 1, 'name' => 'Développer des actions spécifiques en soutien aux familles et plus particulièrement aux mamans solos'],
        ['os' => 1, 'name' => "Développer des actions d'accompagnements spécifiques aux 18-25 ans et plus particulièrement aux NEET's tout en encourageant une participation active dans leur parcours social."],
        ['os' => 1, 'name' => "Veiller à l'inclusion des personnes étrangères lors de leur passage ou de leur installation sur Marche-en-Famenne et maintenir l'offre d'hébergement en initiatives locales d'Accueil."],
        ['os' => 1, 'name' => "Intensifier l'accompagnement socioprofessionnel pour une recherche ou reprise d'autonomie visant un emploi durable, vecteur d'émancipation, plus spécifiquement dans le cadre de la réforme régionale sur le dispositif d'insertion socioprofessionnelle des articles 60-61"],
        ['os' => 1, 'name' => "Mettre en oeuvre l'accompagnement social dans le cadre de la réforme sur le chômage de longue durée qui induit de nouveaux enjeux sociaux et très probablement, de nouveaux profils de bénéficiaires (classe moyenne)"],
        ['os' => 2, 'name' => 'Favoriser le maintien à domicile'],
        ['os' => 2, 'name' => 'Maintenir et développer des projets intergénérationnels sur le site du Quartier Libert'],
        ['os' => 2, 'name' => 'Moderniser et développer les services du Quartier Libert afin de garantir un cadre de vie agréable pour ses résidents'],
        ['os' => 2, 'name' => 'Développer le caractère inclusif du Quartier Libert'],
        ['os' => 3, 'name' => "Proximité : Lutter contre la stigmatisation de l'aide sociale et la fracture numérique, et être proche du citoyen, usager ou pas, par une communication moderne, adpatée et diversifée en vue d'optimiser leur accueil"],
        ['os' => 3, 'name' => "Répondre aux objectifs de développement durables définis par l'ONU et être responsable en termes de transition énergétique et numérique"],
        ['os' => 3, 'name' => "Réduire l'empreinte carbone, en lien notamment avec les objectifs de la ville dans le cadre de la Convention des Maires (rénovation et amélioration du patrimoine bâti)"],
        ['os' => 3, 'name' => "Synergie : transversalité optimale entre l'administration communale et le CPAS"],
        ['os' => 3, 'name' => 'Continuer les démarches de bonne gouvernance entreprise depuis plusieurs années'],
        ['os' => 3, 'name' => "Veiller au bien-être au travail des agents via la valorisation du travail, leur évolution de carrière et l'amélioration de la communication"],
    ];

    public function handle(): int
    {
        $csvFile = $this->dir.'PST_Marche2025.csv';
        $csvFile = $this->dir.'PST_Internal.csv';
        $this->department = DepartmentEnum::CPAS->value;
        $this->importO();
     //   $this->importCsv($csvFile);
        $this->info('Update');

        return SfCommand::SUCCESS;
    }

    public function importO(): void
    {
        $osIds = [];

        // Import Strategic Objectives (OS)
        foreach ($this->os as $position => $name) {
            $name = mb_substr($name, 0, 255);
            $os = StrategicObjective::create([
                'name' => $name,
                'department' => $this->department,
                'position' => $position + 1,
            ]);
            $osIds[$position + 1] = $os->id;
            $this->info("Created OS {$os->id}: {$name}");
        }

        // Import Operational Objectives (OO)
        foreach ($this->oos as $position => $oo) {
            $strategicObjectiveId = $osIds[$oo['os']] ?? null;

            if (! $strategicObjectiveId) {
                $this->warn("OS {$oo['os']} not found for OO: {$oo['name']}");

                continue;
            }

            $name = mb_substr($oo['name'], 0, 255);
            $operationalObjective = OperationalObjective::create([
                'name' => $name,
                'department' => $this->department,
                'strategic_objective_id' => $strategicObjectiveId,
                'position' => $position + 1,
            ]);
            $this->info("Created OO {$operationalObjective->id}: {$name}");
        }
    }

    public function importCsv($csvFile, $delimiter = '|'): void
    {
        $file_handle = fopen($csvFile, 'r');
        $this->lastOs = 6;
        while ($row = fgetcsv($file_handle, null, $delimiter)) {
            $this->actionDto = ImportDto::fromRow($row);
            if ($this->actionDto->actionNum) {
                $this->addAction();

                continue;
            }
            if ($this->actionDto->osNum) {
                $this->addOs();

                continue;
            }
            if ($this->actionDto->ooNum) {
                $this->addOo();
            }
        }
        fclose($file_handle);
    }

    public function addExtraData(Action $action): void
    {
        foreach ($this->findMandatary($this->actionDto->mandataire) as $mandatary) {
            $action->mandataries()->attach($mandatary->id);
        }

        foreach ($this->findMandatary($this->actionDto->agentPilote) as $agent) {
            $action->users()->attach($agent->id);
        }

        foreach ($this->findOdd($this->actionDto->odd) as $odd) {
            $action->odds()->attach($odd->id);
        }

        if ($this->actionDto->servicePorteur) {
            if (str_contains($this->actionDto->servicePorteur, ',')) {
                $services = explode(',', $this->actionDto->servicePorteur);
                foreach ($services as $name) {
                    $service = $this->findService(mb_trim($name));
                    if (! $service) {
                        $this->warn(
                            'ERROR service with , not found '.$name.' original: '.$this->actionDto->servicePorteur
                        );
                    }
                    $action->leaderServices()->attach($service->id);
                }
            } else {
                $service = $this->findService(mb_trim($this->actionDto->servicePorteur));
                if (! $service) {
                    $this->warn('ERROR service not found '.$this->actionDto->servicePorteur);
                }
                $action->leaderServices()->attach($service->id);
            }
        }
        if ($this->actionDto->servicePartenaire) {
            if (str_contains($this->actionDto->servicePartenaire, ',')) {
                $services = explode(',', $this->actionDto->servicePartenaire);
                foreach ($services as $name) {
                    $service = $this->findService(mb_trim($name));
                    if (! $service) {
                        $this->warn(
                            'ERROR service part with , not found '.$name.' original: '.$this->actionDto->servicePartenaire
                        );
                    }
                    $action->partnerServices()->attach($service->id);
                }
            } else {
                $service = $this->findService(mb_trim($this->actionDto->servicePartenaire));
                if (! $service) {
                    $this->warn('ERROR service part not found '.$this->actionDto->servicePartenaire);
                }
                $action->partnerServices()->attach($service->id);
            }
        }
        if ($this->actionDto->partners) {
            if (str_contains($this->actionDto->partners, ',')) {
                $services = explode(',', $this->actionDto->partners);
                foreach ($services as $name) {
                    $partner = $this->findPartner(mb_trim($name));
                    $action->partners()->attach($partner->id);
                }
            } else {
                $partner = $this->findPartner(mb_trim($this->actionDto->partners));
                $action->partners()->attach($partner->id);
            }
        }
    }

    private function addOs(): void
    {
        $number = preg_replace('/\D/', '', $this->actionDto->osNum);
        $name = $this->actionDto->name;

        $os = StrategicObjective::create([
            'name' => $name,
            'department' => $this->department,
            'position' => $number,
        ]);
        $this->lastOs = $os->id;
    }

    private function addOo(): void
    {
        $name = $this->actionDto->name;
        $number = 99;
        $oo = OperationalObjective::create([
            'strategic_objective_id' => $this->lastOs,
            'name' => $name,
            'department' => $this->department,
            'position' => $number,
        ]);
        $this->lastOo = $oo->id;
    }

    private function addAction(): void
    {
        if (! $this->actionDto->name) {
            return;
        }

        $this->info('---- Action '.$this->actionDto->actionNum.' '.$this->actionDto->name.' -----');
        $state = null;
        if ($this->actionDto->etat) {
            $state = $this->findState($this->actionDto->etat);
            if (! $state) {
                $this->warn('state not found'.$this->actionDto->etat);
            }
        }

        $type = null;
        if ($this->actionDto->type) {
            $type = ActionTypeEnum::findByName($this->actionDto->type);
        }

        [$osNum, $ooNum, $position] = explode('.', $this->actionDto->actionNum); // 05.1.01;

        $synergie = $this->findSynergy($this->actionDto->synergie);
        $sheet = $this->findSynergy($this->actionDto->feuilleDeRoute);

        $action = Action::create([
            'name' => $this->actionDto->name,
            'department' => $this->department,
            'state' => $state,
            'type' => $type?->value,
            'user_add' => 'import',
            'note' => $this->actionDto->notes,
            'position' => (int) $position,
            'synergy' => $synergie,
            'roadmap' => $sheet,
            'operational_objective_id' => $this->lastOo,
        ]);

        $this->addExtraData($action);
    }

    private function findState(string $name): ?string
    {
        return match ($name) {
            'Suspendu' => ActionStateEnum::SUSPENDED->value,
            'En cours' => ActionStateEnum::PENDING->value,
            'Terminé' => ActionStateEnum::FINISHED->value,
            'A démarrer' => ActionStateEnum::START->value,
            default => null,
        };
    }

    private function findSynergy(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return match ($name) {
            'Non' => ActionSynergyEnum::NO->value,
            'Oui' => ActionSynergyEnum::YES->value,
            default => null,
        };
    }

    private function findMandatary(string $name): array
    {
        $users = [];
        if (! $name) {
            return [];
        }

        if (str_contains($name, ',')) {
            $items = explode(',', $name);
            foreach ($items as $item) {
                [$prenom, $nom] = explode(' ', $item);
                if ($nom === 'Magali') {
                    $nom = 'COPPE';
                }
                if ($nom === 'Jean-François') {
                    $nom = 'Piérard';
                }
                if ($nom === 'Philippe-Michel') {
                    $nom = 'Panza';
                }
                if ($nom === 'Christian') {
                    $nom = 'Ngongang';
                }
                if ($nom === 'Carine') {
                    $nom = 'Bonjean';
                }
                if ($nom === 'Nicolas') {
                    $nom = 'Gregoire';
                }
                $user = User::where('last_name', mb_trim($nom))->first();
                if (! $user) {
                    $this->warn('ERROR User with , not found: '.$nom.' original: '.$name);
                } else {
                    $users[] = $user;
                }
            }
        } else {
            $items = explode(' ', $name);
            if (isset($items[1])) {
                $nom = $items[1];
                if ($nom === 'Magali') {
                    $nom = 'COPPE';
                }
                if ($nom === 'Jean-François') {
                    $nom = 'Piérard';
                }
                if ($nom === 'Philippe-Michel') {
                    $nom = 'Panza';
                }
                if ($nom === 'Christian') {
                    $nom = 'Ngongang';
                }
                if ($nom === 'Nicolas') {
                    $nom = 'Gregoire';
                }
                if ($nom === 'Carine') {
                    $nom = 'Bonjean';
                }
                if ($nom) {
                    $user = User::where('last_name', mb_trim($nom))->first();
                    if (! $user) {
                        $this->warn('ERROR User not found: '.$nom.' original: '.$name);
                    } else {
                        $users[] = $user;
                    }
                }
            }
        }

        return $users;
    }

    private function findOdd(string $name): array
    {
        $odds = [];
        if (! $name) {
            return [];
        }
        if (str_contains($name, ',')) {
            $items = explode(',', $name);
            foreach ($items as $nom) {
                if (str_contains($nom, 'Paix')) {
                    $odd = Odd::find(16);
                } elseif (str_contains($nom, 'Faim')) {
                    $odd = Odd::find(2);
                } else {
                    $odd = Odd::where('name', mb_trim($nom))->first();
                }
                if (! $odd) {
                    $this->warn('ERROR Odd with , not found: '.$nom.' original: '.$name);
                } else {
                    $odds[] = $odd;
                }
            }
        } else {
            if (str_contains($name, 'Paix')) {
                $odd = Odd::find(16);
            } elseif (str_contains($name, 'Faim')) {
                $odd = Odd::find(2);
            } else {
                $odd = Odd::where('name', mb_trim($name))->first();
            }
            if (! $odd) {
                $this->warn('ERROR Odd not found: '.$name.' original: '.$name);
            } else {
                $odds[] = $odd;
            }
        }

        return $odds;
    }

    private function findService(string $service): ?Service
    {
        return Service::where('name', $service)->orWhere('initials', $service)->first();
    }

    private function findPartner(string $name): Partner
    {
        $partner = Partner::where('name', $name)->orWhere('initials', $name)->first();
        if (! $partner) {
            $partner = Partner::create(['name' => $name]);
        }

        return $partner;
    }
}
