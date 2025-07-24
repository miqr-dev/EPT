<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfessionGroup;

class ProfessionGroupSeeder extends Seeder
{
    public function run(): void
    {
        ProfessionGroup::create(['name' => 'Inhaber(in) und Geschäftsführer(in)
von größeren Unternehmen']);
        ProfessionGroup::create(['name' => 'Freier Beruf']);
        ProfessionGroup::create(['name' => 'Mittlere und kleinere selbstständige
Geschäftsleute']);
        ProfessionGroup::create(['name' => 'Selbstständige Handwerk(in)']);
        ProfessionGroup::create(['name' => 'Leitende(r) Angestellte(r)']);
        ProfessionGroup::create(['name' => 'Nichtleitende(r) Angestellte(r)']);
        ProfessionGroup::create(['name' => 'Beamter(in) des höheren oder
gehobenen Dienst']);
        ProfessionGroup::create(['name' => 'Beamter(in) des mittleren oder
einfachen Dienstes']);
        ProfessionGroup::create(['name' => 'Landwirt(in)']);
        ProfessionGroup::create(['name' => 'Facharbeiter(in) mit abgelegter Prüfung']);
        ProfessionGroup::create(['name' => 'Sonstige(r) Arbeiter(in)']);
        ProfessionGroup::create(['name' => 'Sonstiges']);
       
    }
}
