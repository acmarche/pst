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

    public function handle(): int
    {
        $csvFile = $this->dir.'PST_Marche2025.csv';
        $csvFile = $this->dir.'PST_Internal.csv';
        $this->department = DepartmentEnum::COMMON->value;
        $this->importCsv($csvFile);
        $this->info('Update');

        return SfCommand::SUCCESS;
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
                    if (!$service) {
                        $this->warn(
                            'ERROR service with , not found '.$name.' original: '.$this->actionDto->servicePorteur
                        );
                    }
                    $action->leaderServices()->attach($service->id);
                }
            } else {
                $service = $this->findService(mb_trim($this->actionDto->servicePorteur));
                if (!$service) {
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
                    if (!$service) {
                        $this->warn(
                            'ERROR service part with , not found '.$name.' original: '.$this->actionDto->servicePartenaire
                        );
                    }
                    $action->partnerServices()->attach($service->id);
                }
            } else {
                $service = $this->findService(mb_trim($this->actionDto->servicePartenaire));
                if (!$service) {
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
            'department' =>  $this->department,
            'position' => $number,
        ]);
        $this->lastOo = $oo->id;
    }

    private function addAction(): void
    {
        if (!$this->actionDto->name) {
            return;
        }

        $this->info('---- Action '.$this->actionDto->actionNum.' '.$this->actionDto->name.' -----');
        $state = null;
        if ($this->actionDto->etat) {
            $state = $this->findState($this->actionDto->etat);
            if (!$state) {
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
            'department' =>  $this->department,
            'state' => $state,
            'type' => $type?->value,
            'user_add' => 'import',
            'note' => $this->actionDto->notes,
            'position' => (int)$position,
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
        if (!$name) {
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
        if (!$name) {
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
                if (!$user) {
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
                    if (!$user) {
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
        if (!$name) {
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
                if (!$odd) {
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
            if (!$odd) {
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
        if (!$partner) {
            $partner = Partner::create(['name' => $name]);
        }

        return $partner;
    }
}
