<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
class RolesTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		//Запускается в деплое.
		//Проверяем что еще нет данных
		$role = Role::first();
		if ($role) {
			return;
		}
		
		Role::insert([
			[
				'name'        => 'admin',
				'description' => 'Администратор'
			],
			[
				'name'        => 'activated',
				'description' => 'Активированный пользователь'
			],
			[
				'name'        => 'user',
				'description' => 'Обычный пользователь'
			],
		]);
	}
}
