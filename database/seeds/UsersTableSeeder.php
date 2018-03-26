<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'       => 'admin',
            'username'   => 'admin',
            'email'      => 'admin@admin.com',
            'credits'    => 0,
            'role_id'    => 1,
            'password'   => bcrypt('admin'),
            'created_at' => date('Y-m-d h:m:s'),
            'updated_at' => date('Y-m-d h:m:s'),
        ]);

        DB::table('users')->insert([
            'name'       => 'test',
            'username'   => 'test',
            'email'      => 'test@test.com',
            'credits'    => 0,
            'role_id'    => 2,
            'password'   => bcrypt('test'),
            'created_at' => date('Y-m-d h:m:s'),
            'updated_at' => date('Y-m-d h:m:s'),
        ]);
    }
}
