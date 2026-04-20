<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Area;
use App\Models\Contractor;
use App\Models\Category;
use App\Models\Asset;
use App\Models\Part;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@cmms.local',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Regular user
        User::create([
            'name'     => 'John Technician',
            'email'    => 'tech@cmms.local',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);

        // Locations
        $hq = Location::create([
            'name'    => 'Headquarters',
            'address' => '123 Main Street, City, State 10001',
            'phone'   => '+1 555-0100',
            'email'   => 'hq@company.com',
        ]);

        $warehouse = Location::create([
            'name'    => 'Warehouse A',
            'address' => '456 Industrial Ave, City, State 10002',
            'phone'   => '+1 555-0200',
            'email'   => 'warehouse@company.com',
        ]);

        // Areas
        $floor1 = Area::create(['location_id' => $hq->id, 'name' => 'Floor 1', 'area_code' => 'HQ-F1']);
        $floor2 = Area::create(['location_id' => $hq->id, 'name' => 'Floor 2', 'area_code' => 'HQ-F2']);
        Area::create(['location_id' => $warehouse->id, 'name' => 'Zone A', 'area_code' => 'WH-ZA']);

        // Contractors
        $contractor = Contractor::create([
            'name'             => 'TechServ Solutions',
            'address'          => '789 Contractor Rd',
            'phone'            => '+1 555-0300',
            'email'            => 'service@techserv.com',
            'point_of_contact' => 'Jane Smith',
        ]);

        // Categories
        $hvac  = Category::create(['name' => 'HVAC',        'color' => '#3b82f6']);
        $elec  = Category::create(['name' => 'Electrical',  'color' => '#f59e0b']);
        $mech  = Category::create(['name' => 'Mechanical',  'color' => '#10b981']);
        $plumb = Category::create(['name' => 'Plumbing',    'color' => '#6366f1']);

        // Assets
        Asset::create([
            'name'          => 'HVAC Unit A',
            'description'   => 'Main HVAC unit for Floor 1',
            'serial_number' => 'HVAC-001-2024',
            'manufacturer'  => 'Carrier',
            'status'        => 'active',
            'contractor_id' => $contractor->id,
            'location_id'   => $hq->id,
            'area_id'       => $floor1->id,
            'category_id'   => $hvac->id,
        ]);

        Asset::create([
            'name'          => 'Generator B',
            'description'   => 'Backup generator',
            'serial_number' => 'GEN-002-2024',
            'manufacturer'  => 'Caterpillar',
            'status'        => 'active',
            'location_id'   => $warehouse->id,
            'category_id'   => $mech->id,
        ]);

        // Parts
        Part::create([
            'name'              => 'Air Filter 12x24',
            'part_number'       => 'AF-12x24',
            'unit'              => 'pcs',
            'unit_cost'         => 12.50,
            'quantity_on_hand'  => 20,
            'minimum_quantity'  => 5,
            'category_id'       => $hvac->id,
        ]);

        Part::create([
            'name'             => 'Lubricant Oil 1L',
            'part_number'      => 'LUB-1L',
            'unit'             => 'liters',
            'unit_cost'        => 8.75,
            'quantity_on_hand' => 50,
            'minimum_quantity' => 10,
            'category_id'      => $mech->id,
        ]);
    }
}
