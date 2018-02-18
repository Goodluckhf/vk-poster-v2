<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Vk\VkApi;
use Log;
use Mail;
use \App\Job;
use \App\Helpers\Helper;
use Sunra\PhpSimple\HtmlDomParser;

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
	
	private function getLinkFromWikiUrl($url) {
		$html = HtmlDomParser::file_get_html($url);
		$wikiBody = $html->find('div.wiki_body', 0);
		if (! $wikiBody) {
			return null;
		}
		
		$firstLink = $wikiBody->find('a.wk_ext_link', 0);
		
		if (! $firstLink) {
			return null;
		}
		
		$href = $firstLink->getAttribute('href');
		return Helper::paramFromUrlStr($href, 'to');
	}
	
	private function getUrlByAttachments($attachments) {
		foreach ($attachments as $attach) {
			if ($attach['type'] === 'page') {
				return $this->getLinkFromWikiUrl($attach['page']['view_url']);
			}
			
			if ($attach['type'] === 'link') {
				return $attach['link']['url'];
			}
		}
		
		return null;
	}
	
	private function checkPost($post) {
		// Достаем ссылку из поста
		// 1) в прикреплениях: ссылка(снипет) | wiki
		// 2) в тексте поста
		$link = null;
		if (isset($post['attachments'])) {
			$link = $this->getUrlByAttachments($post['attachments']);
			Log::info('Ссылка из прикрепления', [
				'link' => $link
			]);
		}
		
		if (! $link) {
			preg_match(self::URL_PATTERN, $post['text'], $link);
			Log::info('Слежка: парс ссылки', [
				'text' => $post['text'],
				'link' => $link,
			]);
			
			if (! isset($link[0])) {
				Log::info('Слежка: Нет ссылки');
				return true;
			}
			
			$link = $link[0];
		}
		
		//Сам процесс проверки
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
			Log::error('ссылку забанили: ', [
				'post_id' => $post['id'],
				'group_id' => $post['to_id']
			]);
			return false;
		}
		
		return true;
	}
}