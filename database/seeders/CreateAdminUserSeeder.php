<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Seeder;
use App\Models\User;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

use App\Models\Role;
use App\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminUser = User::create([
            'name' => 'Admin user', 
            'username' => 'siteAdmin', 
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456')
        ]);
    
        $role = Role::create(['guard_name' => 'web','name' => 'Admin']);
        $permissions = Permission::pluck('id','id')->all();
        $role->syncPermissions($permissions);
        $adminUser->assignRole($role);

        $roles=[
            'guardian',
            'student',
            'instructor',
            'facilitator'
        ];
        for ($i=0; $i < count($roles) ; $i++) { 
            
            $role2 = Role::create(['guard_name' => 'api','name' => $roles[$i]]);
            $permissions2 = Permission::pluck('id','id')->all();
            $role2->syncPermissions($permissions2);
            // if ($role) {

            // }
            // $permissions = Permission::find([5,7,8,10,11,12]);
            // $role->syncPermissions($permissions);
        }
    }
}