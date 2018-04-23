<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Vk\VkApi;
use Log;
use Mail;
use \App\Models\GroupSeekJob;
use \App\Models\User;
use \App\Helpers\Helper;


class GroupSeek extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'GroupSeek';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Проверка группы на полный бан и бан ссылок';
	
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$jobs = GroupSeekJob::findActive();
		$this->info('herer', [$jobs->toArray()]);
		foreach ($jobs as $job) {
			$job->seek();
		}
	}
}