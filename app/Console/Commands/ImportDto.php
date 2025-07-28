<?php

namespace App\Console\Commands;

class ImportDto
{
    public ?string $osNum = null;
    public ?string $ooNum = null;
    public ?string $actionNum = null;
    public ?string $name = null;
    public ?string $type = null;
    public ?string $mandataire = null;
    public ?string $agentPilote = null;
    public ?string $servicePorteur = null;
    public ?string $servicePartenaire = null;
    public ?string $partners = null;
    public ?string $etat = null;
    public ?string $odd = null;
    public ?string $feuilleDeRoute = null;
    public ?string $synergie = null;
    public ?string $notes = null;

    public function __construct(
        ?string $osNum = null,
        ?string $ooNum = null,
        ?string $actionNum = null,
        ?string $name = null,
        ?string $type = null,
        ?string $mandataire = null,
        ?string $agentPilote = null,
        ?string $servicePorteur = null,
        ?string $servicePartenaire = null,
        ?string $serviceExterne = null,
        ?string $etat = null,
        ?string $odd = null,
        ?string $feuilleDeRoute = null,
        ?string $synergie = null,
        ?string $notes = null
    ) {
        $this->osNum = $osNum;
        $this->ooNum = $ooNum;
        $this->actionNum = $actionNum;
        $this->name = $name;
        $this->type = $type;
        $this->mandataire = $mandataire;
        $this->agentPilote = $agentPilote;
        $this->servicePorteur = $servicePorteur;
        $this->servicePartenaire = $servicePartenaire;
        $this->partners = $serviceExterne;
        $this->etat = $etat;
        $this->odd = $odd;
        $this->feuilleDeRoute = $feuilleDeRoute;
        $this->synergie = $synergie;
        $this->notes = $notes;
    }

    /**
     * Create ActionDto from array row data
     */
    public static function fromRow(array $row): self
    {
        return new self(
            osNum: $row[0] ?? null,           // OS
            ooNum: $row[1] ?? null,           // OO
            actionNum: $row[2] ?? null,       // Position
            name: $row[3] ?? null,            // Name
            type: $row[4] ?? null,            // Type d'action
            mandataire: $row[5] ?? null,      // Mandataire
            agentPilote: $row[6] ?? null,     // Agent pilote
            servicePorteur: $row[7] ?? null,  // Service Porteur
            servicePartenaire: $row[8] ?? null, // Service(s) partenaire(s) internes
            serviceExterne: $row[9] ?? null,  // Service(s) partenaire(s) externes
            etat: $row[10] ?? null,           // Etat d'avancement
            odd: $row[11] ?? null,            // ODD
            feuilleDeRoute: $row[12] ?? null, // Action reprise dans la feuille de route ODD
            synergie: $row[13] ?? null,       // Synergie Ville- CPAS
            notes: $row[14] ?? null           // Notes
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'osNum' => $this->osNum,
            'ooNum' => $this->ooNum,
            'actionNum' => $this->actionNum,
            'name' => $this->name,
            'type' => $this->type,
            'mandataire' => $this->mandataire,
            'agentPilote' => $this->agentPilote,
            'servicePorteur' => $this->servicePorteur,
            'servicePartenaire' => $this->servicePartenaire,
            'serviceExterne' => $this->partners,
            'etat' => $this->etat,
            'odd' => $this->odd,
            'feuilleDeRoute' => $this->feuilleDeRoute,
            'synergie' => $this->synergie,
            'notes' => $this->notes,
        ];
    }
}
