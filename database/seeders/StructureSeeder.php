<?php

namespace Database\Seeders;

use App\Models\Structure;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        // Niveau 0: Direction Générale
        $dg = Structure::create([
            'name' => 'Direction Générale',
            'type' => 'dg',
            'code' => 'DG',
            'level' => 0,
            'description' => 'Direction Générale d\'Algérie Télécom',
            'is_active' => true,
        ]);

        // Niveau 1: Pôles
        $poleTech = Structure::create([
            'name' => 'Pôle Technique',
            'type' => 'pole',
            'code' => 'POLE-TECH',
            'parent_id' => $dg->id,
            'level' => 1,
            'description' => 'Pôle Technique',
            'is_active' => true,
        ]);

        $poleCommercial = Structure::create([
            'name' => 'Pôle Commercial',
            'type' => 'pole',
            'code' => 'POLE-COMM',
            'parent_id' => $dg->id,
            'level' => 1,
            'description' => 'Pôle Commercial',
            'is_active' => true,
        ]);

        // Niveau 2: Divisions sous Pôle Technique
        $divisionReseaux = Structure::create([
            'name' => 'Division Réseaux',
            'type' => 'division',
            'code' => 'DIV-RES',
            'parent_id' => $poleTech->id,
            'level' => 2,
            'description' => 'Division des Réseaux',
            'is_active' => true,
        ]);

        // Niveau 3: Directions sous Divisions
        $dsi = Structure::create([
            'name' => 'Direction des Systèmes d\'Information (DSI)',
            'type' => 'direction',
            'code' => 'DSI',
            'parent_id' => $divisionReseaux->id,
            'level' => 3,
            'description' => 'Direction des Systèmes d\'Information',
            'is_active' => true,
        ]);

        $drt = Structure::create([
            'name' => 'Direction des Réseaux Télécoms (DRT)',
            'type' => 'direction',
            'code' => 'DRT',
            'parent_id' => $divisionReseaux->id,
            'level' => 3,
            'description' => 'Direction des Réseaux Télécoms',
            'is_active' => true,
        ]);

        // Autres structures
        Structure::create([
            'name' => 'Direction des Ressources Humaines (DRH)',
            'type' => 'direction',
            'code' => 'DRH',
            'parent_id' => $poleCommercial->id,
            'level' => 3,
            'description' => 'Direction des Ressources Humaines',
            'is_active' => true,
        ]);

        $dot = Structure::create([
            'name' => 'Direction de l\'Organisation du Travail (DOT)',
            'type' => 'direction',
            'code' => 'DOT',
            'parent_id' => $poleTech->id,
            'level' => 3,
            'description' => 'Direction de l\'Organisation du Travail',
            'is_active' => true,
        ]);

        $this->command->info('Structures créées avec succès !');
    }
}