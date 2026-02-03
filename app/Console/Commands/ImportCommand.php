<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ActionRoadmapEnum;
use App\Enums\ActionScopeEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
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
use DateTimeInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pst:import {filename : The CSV file to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test command';

    protected string $dir = __DIR__.'/../../../data/';

    private int $lastOo = 0;

    private int $lastSo;

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

    private ActionScopeEnum $scope = ActionScopeEnum::EXTERNAL;

    public function handle(): int
    {
        $csvFile = $this->dir.$this->argument('filename');
        $this->department = DepartmentEnum::CPAS->value;
        if ($csvFile === 'Interne.csv') {
            $this->scope = ActionScopeEnum::INTERNAL;
        }
        $this->importCsv($csvFile);
        $this->info('Update');

        return SfCommand::SUCCESS;
    }

    public function importCsv($csvFile, $delimiter = '|'): void
    {
        $file_handle = fopen($csvFile, 'r');
        $firstLine = true;

        while ($row = fgetcsv($file_handle, null, $delimiter)) {
            if (mb_trim($row[0]) === "Numéro d'action") {
                continue;
            }
            if ($firstLine) {
                $firstLine = false;
                $so = StrategicObjective::where('name', $row[0])->first();
                if ($so) {
                    $this->lastSo = $so->id;

                    continue;
                }
            }

            $actionNum = (int) $row[0];
            $actionName = mb_trim($row[1]);
            if ($actionNum === 0 && $actionName === '') {
                $oo = OperationalObjective::where('name', $row[0])->first();
                if ($oo) {
                    $this->lastOo = $oo->id;

                    continue;
                }
            }
            if (! $actionName) {
                $this->error('no action name '.$actionNum);

                continue;
            }
            $this->info('---- Action '.$actionNum.') '.$actionName);
            $ooEmpty = $row[2];
            $badNa = $row[3];
            $row[4] = str_replace('PAIX, JUSTICE', 'PAIX JUSTICE', $row[4]);
            $row[4] = str_replace('INDUSTRIE, INNOVATION', 'INDUSTRIE INNOVATION', $row[4]);

            $odds = explode(',', $row[4]);
            $oddObjects = $this->findOdds($odds);
            $rhEmpty = $row[5];
            if ($row[6] === 'Permanent') {
                $actionType = ActionTypeEnum::PERENNIAL;
                $actionState = ActionStateEnum::PENDING;
            } else {
                $actionType = ActionTypeEnum::PST;
                $actionState = $this->findState($row[6]);
            }
            $evolutionPercentage = (int) $row[7];

            $dueDate = Carbon::createFromFormat('d/m/Y', $row[8]);
            if (! $dueDate) {
                $this->error('no due date '.$actionName);
            }
            $responsable = null;
            try {
                $responsable = $this->findAgent($row[9]);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            $serviceSociopro = $agentPilote = null;
            if ($row[9] === 'insertion sociopro.' or $row[9] === "service d'insertion socio-professionnelle") {
                $serviceSociopro = Service::where('name', 'insertion socioprofessionnelle')->first();
            }
            try {
                $agentPilote = $this->findAgent($row[10]);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
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
                $dueDate,
                $notes,
                $roadMap,
                $oddObjects,
                $servicesAndPartners,
                $agentPilote,
                $responsable
            );
        }
    }

    /**
     * @param  array<int,Odd>  $odds
     */
    public function addExtraData(
        Action $action,
        array $odds,
        array $servicesAndPartners,
        ?User $agentPilote,
        ?User $responsable
    ): void {
        $action->odds()->sync($odds, false);
        $services = $servicesAndPartners['services'] ?? [];
        $partners = $servicesAndPartners['partners'] ?? [];
        $action->odds()->sync($odds, false);
        $action->leaderServices()->sync($services, false);
        $action->partners()->sync($partners, false);
        if ($responsable && ! $responsable->hasRole(RoleEnum::RESPONSIBLE->value)) {
            $responsable->addRole(Role::where('name', RoleEnum::RESPONSIBLE->value)->first());
        }
        if ($responsable) {
            $this->info($responsable->first_name);
            $action->users()->sync($responsable, false);
        }
        if ($agentPilote) {
            $action->users()->sync($agentPilote, false);
        }
    }

    private function addAction(
        string $name,
        ActionStateEnum $actionStateEnum,
        ActionTypeEnum $actionTypeEnum,
        int $actionNum,
        int $evolutionPercentage,
        DateTimeInterface $dueDate,
        string $notes,
        ActionRoadmapEnum $actionRoadmapEnum,
        array $oddObjects,
        array $servicesAndPartners,
        ?User $agentPilote,
        ?User $responsable
    ): void {
        $name = Str::limit($name, 250, '...');
        try {
            $action = Action::create([
                'name' => $name,
                'department' => DepartmentEnum::CPAS->value,
                'state' => $actionStateEnum->value,
                'type' => $actionTypeEnum->value,
                'state_percentage' => $evolutionPercentage,
                'due_date' => $dueDate,
                'user_add' => 'import',
                'note' => $notes,
                'position' => $actionNum,
                'roadmap' => $actionRoadmapEnum->value,
                'operational_objective_id' => $this->lastOo,
                'validated' => true,
                'scope' => $this->scope->value,
                'synergy' => ActionSynergyEnum::NO->value,
            ]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return;
        }

        $this->addExtraData($action, $oddObjects, $servicesAndPartners, $agentPilote, $responsable);
    }

    private function findState(string $name): ?ActionStateEnum
    {
        return match ($name) {
            'Suspendu' => ActionStateEnum::SUSPENDED,
            'En cours' => ActionStateEnum::PENDING,
            'Terminé' => ActionStateEnum::FINISHED,
            'A démarrer', 'À démarrer' => ActionStateEnum::START,
            default => null,
        };
    }

    private function findOdds(array $odds): array
    {
        $oddObjects = [];
        foreach ($odds as $oddName) {
            $odd = null;
            if (str_contains($oddName, 'PAIX JUSTICE')) {
                $odd = Odd::find(16);
                $oddObjects[] = $odd;

                continue;
            }
            if (str_contains($oddName, 'INDUSTRIE INNOVATION')) {
                $odd = Odd::find(9);
                $oddObjects[] = $odd;

                continue;
            }
            $oddName = mb_trim(mb_substr($oddName, mb_strpos($oddName, '.') + 1));
            $odd = Odd::whereRaw('LOWER(name) = ?', [mb_strtolower($oddName)])->first();

            if (! $odd) {
                $this->error('not found odd '.$oddName);

                continue;
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
            if (! $partner) {
                $partner = Partner::create(['name' => $name]);
            }
            $data['partners'][] = $partner;
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    private function findAgent(?string $name): ?User
    {
        if (! $name) {
            return null;
        }

        return match ($name) {
            'CD' => User::where('last_name', 'LIKE', 'Dermience')->first(),
            'GS' => User::where('last_name', 'Santer')->first(),
            'MH' => User::where('last_name', 'HEINEN')->first(),
            'NW' => User::where('last_name', 'WECHSELER')->first(),
            'MDu' => User::where('last_name', 'DUYVEWAARDT')->first(),
            'VB' => User::where('last_name', 'BARVAUX')->first(),
            'FD' => User::where('last_name', 'DESERT')->first(),
            'CL' => User::where('last_name', 'LAVAL')->first(),
            'BM' => User::where('last_name', 'MATERNE')->first(),
            'FM' => User::where('last_name', 'MARCHAL')->first(),
            'GW' => User::where('last_name', 'WERY')->first(),
            'LD' => User::where('last_name', 'DEVILLERS')->first(),
            'FP' => User::where('last_name', 'PONCELET')->first(),
            'PW' => User::where('last_name', 'WOUTERS')->first(),
            default => throw new Exception('agent not found '.$name),
        };
    }
}
