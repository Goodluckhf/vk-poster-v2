<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Vk\VkApi;
use Log;
use Mail;
use App\Models\GroupSeekJob;
use App\Helpers\Helper;
use App\Exceptions\VkApiException;
use App\Exceptions\Models\GroupSeekFailException;


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
		$jobs = GroupSeekJob::active()->get();
		foreach ($jobs as $job) {
			try {
				$job->seek();
			} catch (VkApiException $e) {
				$jsonErr = $e->toJson(true);
				Log::error('GroupSeekJob vkApi error: ', [$jsonErr]);
				
				$errMessage = "error (group_id: {$job->group_id}): {$e->getStatusCode()}. msg: {$jsonErr}";
				
				Helper::sendSeekMail([
					'title'     => 'Слежка: ошибка VK',
					'postText'  => $errMessage,
					'userEmail' => $job->job->user->email
				]);
				
				$job->job->finish();
			} catch (GroupSeekFailException $e) {
				$jsonErr = $e->toJson(true);
				Log::error('ссылку забанили: ', [$jsonErr]);
				
				Helper::sendSeekMail([
					'title'     => 'Слежка: Ссылку забанили!',
					'postText'  => $jsonErr,
					'userEmail' => $job->job->user->email
				]);
				
				$job->job->finish();
			}
		}
	}
}