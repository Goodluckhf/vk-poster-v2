<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Vk\VkApi;
use Log;
use Mail;
use \App\Job;
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
	
	const URL_PATTERN = "/(?:(?:http|https):\/\/)?[a-z0-9-_.]+\.[a-z]{2,5}(?:\/[a-z0-9-_]+\/?)*/i";
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$jobs = Job::whereType(Job::GROUP_SEEK)
			->whereIsFinish(0)
			->get();
		
		foreach ($jobs as $job) {
			Log::info('start check post job_id: ', [$job->id]);
			$this->seek($job);
		}
	}
	
	private function seek($job) {
		$jobData = json_decode($job->data, true);
		
		$user  = \App\User::find($job->user_id);
		
		if (! $user) {
			Log::info('User has deleted', ['user_id' => $job->user_id]);
			return $job->finish();
		}
		
		$vkApi = new VkApi($user->vk_token);
		$wallRequest = $vkApi->callApi('wall.get', [
			'owner_id' => $jobData['group_id'],
			'count'    => $jobData['count'],
			'offset'   => 1,
			'v'        => 5.40
		]);
		
		if (isset($wallRequest['error'])) {
			$errMessage = 'error (group_id: ' . $jobData['group_id'] . '): ' . $wallRequest['error']['error_code'] . '. msg: ' . $wallRequest['error']['error_msg'];
			Log::error($errMessage);
			
			Helper::sendSeekMail([
				'title'     => 'Слежка: ошибка VK',
				'postText'  => $errMessage,
				'userEmail' => $user->email
			]);
			
			$job->finish();
			return;
		}
		
		$wall = $wallRequest['response'];
		
		for ($i = 0; $i < $jobData['count']; $i++) {
			$vkPost = $wallRequest['response']['items'][$i];
			if(! $this->checkPost($vkPost, $vkApi)) {
				Helper::sendSeekMail([
					'title'     => 'Слежка: Ссылку забанили!',
					'postText'  => $vkPost['text'],
					'userEmail' => $user->email
				]);
				
				$job->finish();
			}
		}
	}
	
	private function checkPost($post, $api) {
		preg_match(self::URL_PATTERN, $post['text'], $link);
		Log::error('Слежка: парс ссылки', [
			'text' => $post['text'],
			'link' => $link,
		]);
		if (! isset($link[0])) {
			Log::error('Слежка: Нет ссылки');
			return true;
		}
		
		$link = $link[0];
		
		$link = Helper::addProtocol($link);
		$vkChekLink = 'https://vk.com/away.php?to=' . urlencode($link) . '&post=' . $post['to_id'] . '_' . $post['id'] . 'cc_key=';
		$curl = curl_init($vkChekLink);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0');
		curl_exec($curl);
		$requestResult = curl_getinfo($curl);
		/*Log::info('result', [
			'link'   => $vkChekLink,
			'result' => $requestResult
		]);*/
		
		if ($requestResult['http_code'] == 200) {
			Log::error('ссылку забанили: ' . $post['id']);
			return false;
		}
		
		return true;
	}
}