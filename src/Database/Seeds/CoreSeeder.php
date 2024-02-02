<?php

namespace Vtlabs\Core\Database\Seeds;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Seeder;

use Vtlabs\Core\Models\User\User;
use Database\traits\TruncateTable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Database\traits\DisableForeignKeys;;

class CoreSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        // create role and permission
        Role::create(['name' => 'admin']);
        Role::create(['name' => '_superadmin']);
        Role::create(['name' => 'customer']);
        
        $this->disableForeignKeys();
        $this->truncate('users');

        $users = [
            [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin'),
                'mobile_number' => '1213141516',
                'mobile_verified' => true,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('admin'),
                'mobile_number' => '1213141517',
                'mobile_verified' => true,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('users')->insert($users);

        $this->enableForeignKeys();

        // assign roles
        User::find(1)->assignRole('admin');

        User::find(2)->assignRole('admin');
        User::find(2)->assignRole('_superadmin');
    }
}
