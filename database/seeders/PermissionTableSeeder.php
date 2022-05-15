<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Permission;

use App\Models\Permission;
  
class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
           'role-list',
           'role-create',
           'role-edit',
           'role-delete',

           'child-create',
           
           'course-create',
           'course-edit',
           'course-delete',
           'course-progress',
           'course-buy',
           'course-assign',
           'course-enroll',

           'quiz-create',
           'quiz-edit',
           'quiz-delete',
           'quiz-show',
           'quiz-attend',
           'quiz-publish',

           'wishlist-view',
           'wishlist-edit',

           'comment-create',
        ];
     
        foreach ($permissions as $permission) {
             Permission::create(['name' => $permission]);
        }
    }
}