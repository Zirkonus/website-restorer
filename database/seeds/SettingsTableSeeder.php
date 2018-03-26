<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'key'    => 'get_version_year_count',
            'value'  => 5,
            'label'  => 'The number of years for which the points are selected'
        ]);

        DB::table('settings')->insert([
            'key' => 'credits_user_registration',
            'value'  => 3,
            'label'  => 'Number of credits to add on registration'
        ]);

        DB::table('settings')->insert([
            'key' => 'credits_project_create',
            'value'  => 0,
            'label'  => 'Number of credits removed when user create project'
        ]);

        DB::table('settings')->insert([
            'key' => 'credits_version_capture',
            'value'  => -1,
            'label'  => 'Number of credits removed when user capture website'
        ]);

        DB::table('settings')->insert([
            'key' => 'credits_version_upload',
            'value'  => -1,
            'label'  => 'Number of credits removed when generating .zip archive'
        ]);

        DB::table('settings')->insert([
            'key' => 'credits_version_ftp_upload',
            'value'  => -1,
            'label'  => 'Number of credits removed when choice ftp upload'
        ]);

        DB::table('settings')->insert([
            'key' => 'credits_bonus',
            'value'  => 1,
            'label'  => 'Number of credits to be added as bonus'
        ]);
    }
}
