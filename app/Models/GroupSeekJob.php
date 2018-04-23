<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Log;
use App\Helpers\Helper;
use App\Models\User;
use App\Vk\VkApi;
use Sunra\PhpSimple\HtmlDomParser;

class GroupSeekJob extends Model {
	protected $table = 'group_seek_jobs';
	
	const URL_PATTERN = "/(?:(?:http|https):\/\/)?[a-z0-9-_.]+\.[a-z]{2,5}(?:\/[a-z0-9-_]+\/?)*/i";
	
	public function job() {
		return $this->morphOne('\App\Models\Job', 'job');
	}
	
	public static function findActive() {
		return self::with('job')->whereHas('job', function($q) {
			$q->active();
		})->get();
	}
	
	
	public function seek() {
		Log::info('start check post job_id: ', [$this->id]);
		$user  = User::find($this->job->user_id);
		
		if (! $user) {
			Log::info('User has deleted', ['user_id' => $this->job->user_id]);
			return $this->job->finish();
		}
		
		$vkApi = new VkApi($user->vk_token);
		$wallRequest = $vkApi->callApi('wall.get', [
			'owner_id' => $this->group_id,
			'count'    => $this->count,
			'v'        => 5.40
		]);
		
		if (isset($wallRequest['error'])) {
			$wallReqErr = $wallRequest['error'];
			$errMessage = "error (group_id: {$this->group_id}): {$wallReqErr['error_code']}. msg: {$wallReqErr['error_msg']}";
			
			Log::error($errMessage);
			
			Helper::sendSeekMail([
				'title'     => 'Слежка: ошибка VK',
				'postText'  => $errMessage,
				'userEmail' => $user->email
			]);
			
			return $this->job->finish();
		}
		
		$wall = $wallRequest['response'];
		
		for ($i = 0; $i < $this->job->count; $i++) {
			$vkPost = $wallRequest['response']['items'][$i];
			if(! $this->checkPost($vkPost)) {
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
		$html = HtmlDomParser::file_get_html($url, false, null, 0);
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
		$link        = Helper::addProtocol($link);
		$httpClient  = App::make('HttpRequest');
		$encodedLink = urlencode($link);
		$vkChekLink  = "https://vk.com/away.php?to={$encodedLink}&post={$post['to_id']}_{$post['id']}cc_key=";
		
		$response = $httpClient->request(
			'GET',
			$vkChekLink, [
				'headers' => [
					'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0'
				]
			]
		);
		
		if ($response->getStatusCode() === 200) {
			Log::error('ссылку забанили: ', [
				'post_id' => $post['id'],
				'group_id' => $post['to_id']
			]);
			return false;
		}
		
		return true;
	}
}