<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ActionRoadmapEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionTypeEnum;
use App\Enums\DepartmentEnum;
use App\Enums\RoleEnum;
use App\Models\Action;
use App\Models\Odd;
use App\Models\OperationalObjective;
use App\Models\Partner;
use App\Models\Role;
use App\Models\Service;
use App\Models\StrategicObjective;
use App\Models\User;
use Carbon\Carbon;
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

    protected string $dir = __DIR__.'/../../../data/';

    private int $lastSo = 0;

    private int $lastOo = 0;

    private ImportDto $actionDto;

    private string $department;

    private array $os = [
        "Être un CPAS favorisant l'autonomie et l'inclusion dans la société des personnes fragilisées ou exclues socialement pour qu'elles puissent mener une vie conforme à la dignité humaine et renforcer leur résilience",
        'Être un CPAS qui intègre le vieillissement de la population au centre de ses préocupations, par une offre adaptée, digne et diversifiée.',
        'Être un CPAS performant et impliqué dirigé par un management proactif et empathique',
    ];

    private array $oos = [
        [
            'os' => 1,
            'name' => "Développer l'accès au logement décent et salubre et accompagner les bénéficiaires dans leurs démarches logement (recherche - maintien - déménagement). Diversifier l'offre de logement.",
        ],
        [
            'os' => 1,
            'name' => 'Lutter contre les violences conjugales ou intrafamiliales, faites essentiellement aux femmes et à leurs enfants',
        ],
        [
            'os' => 1,
            'name' => 'Développer des actions spécifiques en soutien aux familles et plus particulièrement aux mamans solos',
        ],
        [
            'os' => 1,
            'name' => "Développer des actions d'accompagnements spécifiques aux 18-25 ans et plus particulièrement aux NEET's tout en encourageant une participation active dans leur parcours social.",
        ],
        [
            'os' => 1,
            'name' => "Veiller à l'inclusion des personnes étrangères lors de leur passage ou de leur installation sur Marche-en-Famenne et maintenir l'offre d'hébergement en initiatives locales d'Accueil.",
        ],
        [
            'os' => 1,
            'name' => "Intensifier l'accompagnement socioprofessionnel pour une recherche ou reprise d'autonomie visant un emploi durable, vecteur d'émancipation, plus spécifiquement dans le cadre de la réforme régionale sur le dispositif d'insertion socioprofessionnelle des articles 60-61",
        ],
        [
            'os' => 1,
            'name' => "Mettre en oeuvre l'accompagnement social dans le cadre de la réforme sur le chômage de longue durée qui induit de nouveaux enjeux sociaux et très probablement, de nouveaux profils de bénéficiaires (classe moyenne)",
        ],
        ['os' => 2, 'name' => 'Favoriser le maintien à domicile'],
        ['os' => 2, 'name' => 'Maintenir et développer des projets intergénérationnels sur le site du Quartier Libert'],
        [
            'os' => 2,
            'name' => 'Moderniser et développer les services du Quartier Libert afin de garantir un cadre de vie agréable pour ses résidents',
        ],
        ['os' => 2, 'name' => 'Développer le caractère inclusif du Quartier Libert'],
        [
            'os' => 3,
            'name' => "Proximité : Lutter contre la stigmatisation de l'aide sociale et la fracture numérique, et être proche du citoyen, usager ou pas, par une communication moderne, adpatée et diversifée en vue d'optimiser leur accueil",
        ],
        [
            'os' => 3,
            'name' => "Répondre aux objectifs de développement durables définis par l'ONU et être responsable en termes de transition énergétique et numérique",
        ],
        [
            'os' => 3,
            'name' => "Réduire l'empreinte carbone, en lien notamment avec les objectifs de la ville dans le cadre de la Convention des Maires (rénovation et amélioration du patrimoine bâti)",
        ],
        ['os' => 3, 'name' => "Synergie : transversalité optimale entre l'administration communale et le CPAS"],
        ['os' => 3, 'name' => 'Continuer les démarches de bonne gouvernance entreprise depuis plusieurs années'],
        [
            'os' => 3,
            'name' => "Veiller au bien-être au travail des agents via la valorisation du travail, leur évolution de carrière et l'amélioration de la communication",
        ],
    ];

    public function handle(): int
    {
        $csvFile = $this->dir.'PSTCPAS.csv';
        $this->department = DepartmentEnum::CPAS->value;
        // $this->importO();
        $this->importCsv($csvFile);
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

            if (!$strategicObjectiveId) {
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
        while ($row = fgetcsv($file_handle, null, $delimiter)) {
            if ($row[0] === "Numéro d'action ") {
                continue;
            }
            $so = StrategicObjective::where('name', $row[0])->first();
            if ($so) {
                $this->lastSo = $so->id;

                continue;
            }
            $oo = OperationalObjective::where('name', $row[0])->first();
            if ($oo) {
                $this->lastOo = $oo->id;

                continue;
            }
            $actionNum = (int)$row[0];
            $actionName = $row[1];
            $ooEmpty = $row[2];
            $badNa = $row[3];
            $odds = explode(',', $row[4]);
            $oddObjects = $this->findOdds($odds);
            $rhEmpty = $row[5];
            if ($row[6] === 'Permanent') {
                $actionType = ActionTypeEnum::PERENNIAL;
                $actionState = ActionStateEnum::PENDING;
            } else {
                $actionType = ActionTypeEnum::PST;
                $actionState = ActionStateEnum::from($row[6]);
            }
            $evolutionPercentage = (int)$row[7];
            $dueDate = Carbon::createFromFormat('d/m/Y', $row[8]);
            if (!$dueDate) {
                dump('no due date '.$actionName);
            }
            $responsable =
                match ($row[9]) {
                    'CD' => User::where('last_name', 'Dermience')->first(),
                    'GS' => User::where('last_name', 'Santer')->first(),
                    'MH' => User::where('last_name', 'Heinen')->first(),
                    'NW' => User::where('last_name', 'Dermience')->first(),
                    'MDu' => User::where('last_name', 'Dermience')->first(),
                    'VB' => User::where('last_name', 'Dermience')->first(),
                    'FD' => User::where('last_name', 'Dermience')->first(),
                    'CL' => User::where('last_name', 'Dermience')->first(),
                    'ILI' => User::where('last_name', 'Dermience')->first(),
                    'BM' => User::where('last_name', 'Dermience')->first(),
                    default => null,
                };
            $serviceSociopro = null;
            if ($row[9] === 'insertion sociopro.' or $row[9] === "service d'insertion socio-professionnelle") {
                $serviceSociopro = Service::where('name', 'insertion socioprofessionnelle')->first();
            }
            $agentPilote = match ($row[10]) {
                'CD' => User::where('last_name', 'Dermience')->first(),
                'GS' => User::where('last_name', 'Santer')->first(),
                'MH' => User::where('last_name', 'Heinen')->first(),
                'NW' => User::where('last_name', 'Dermience')->first(),
                'MDu' => User::where('last_name', 'Dermience')->first(),
                'VB' => User::where('last_name', 'Dermience')->first(),
                'FD' => User::where('last_name', 'Dermience')->first(),
                'CL' => User::where('last_name', 'Dermience')->first(),
                'ILI' => User::where('last_name', 'Dermience')->first(),
                'BM' => User::where('last_name', 'Dermience')->first(),
                default => null,
            };
            $servicesAndPartners = $this->findServicesOrPartners($row[11]);
            $notes = mb_trim($row[12]);
            $roadMap = match ($row[13]) {
                'Oui' => ActionRoadmapEnum::YES,
                'Non' => ActionRoadmapEnum::NO,
                default => null,
            };
            if ($serviceSociopro instanceof Service) {
                $servicesAndPartners['services'][] = $serviceSociopro;
            }
            $this->addAction(
                $actionName,
                $actionState,
                $actionType,
                $actionNum,
                $evolutionPercentage,
                $notes,
                $roadMap,
                $oddObjects,
                $servicesAndPartners,
                $agentPilote,
                $responsable
            );
            dd('end odd');
        }
    }

    /**
     * @param array<int,Odd> $odds
     */
    public function addExtraData(
        Action $action,
        array $odds,
        array $servicesAndPartners,
        ?User $agentPilote,
        ?User $responsable
    ): void {
        $action->odds()->sync($odds);
        $services = $servicesAndPartners['services'] ?? [];
        $partners = $servicesAndPartners['partners'] ?? [];
        $action->odds()->sync($odds);
        $action->leaderServices()->sync($services);
        $action->partners()->sync($partners);
        $responsable?->addRole(Role::where('name', RoleEnum::RESPONSIBLE->value)->first());
        if ($agentPilote) {
            $action->users()->attach($agentPilote);
        }
    }

    private function addAction(
        string $name,
        ActionStateEnum $actionStateEnum,
        ActionTypeEnum $actionTypeEnum,
        int $actionNum,
        string $notes,
        int $evolutionPercentage,
        ActionRoadmapEnum $actionRoadmapEnum,
        array $oddObjects,
        array $servicesAndPartners,
        ?User $agentPilote,
        ?User $responsable
    ): void {
        $this->info('---- Action '.$name);

        $action = Action::create([
            'name' => $name,
            'department' => DepartmentEnum::CPAS->value,
            'state' => $actionStateEnum->value,
            'type' => $actionTypeEnum->value,
            'state_percentage' => $evolutionPercentage,
            'user_add' => 'import',
            'note' => $notes,
            'position' => $actionNum,
            'roadmap' => $actionRoadmapEnum->value,
            'operational_objective_id' => $this->lastOo,
        ]);

        $this->addExtraData($action, $oddObjects, $servicesAndPartners, $agentPilote, $responsable);
    }

    private function findOdds(array $odds): array
    {
        $oddObjects = [];
        foreach ($odds as $odd) {
            $oddName = mb_trim(mb_substr($odd, mb_strpos($odd, '.') + 1));
            // $odd = Odd::where('name', 'LIKE', $odd)->first();
            $odd = Odd::whereRaw('LOWER(name) = ?', [mb_strtolower($oddName)])->first();
            if (!$odd) {
                dump('not found odd '.$oddName);
            }
            $oddObjects[] = $odd;
        }

        return $oddObjects;
    }

    private function findServicesOrPartners(string $name): array
    {
        $data['services'] = [];
        $data['partners'] = [];
        $services = explode(',', $name);
        foreach ($services as $name) {
            $service = Service::where('name', $name)->orWhere('initials', $name)->first();
            if ($service) {
                $data['services'][] = $service;

                continue;
            }
            $partner = Partner::where('name', $name)->orWhere('initials', $name)->first();
            if ($partner) {
                $data['partners'][] = $partner;
            }
        }

        return $data;
    }
}
