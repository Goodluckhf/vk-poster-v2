<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Vk\VkApi;
use Log;

class VkPost extends Job implements ShouldQueue
{
	use InteractsWithQueue, SerializesModels;
	
	protected $post;
	protected $token;
	protected $vkUserId;
	protected $groupId;
	
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($post, $groupId, $token, $vkId)
	{
		$this->post     = $post;
		$this->token    = $token;
		$this->groupId  = $groupId;
		$this->vkUserId = $vkId;
	}
	
	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		 //Log::info('начало создание поста');
		$imgDir = public_path() . '/vk-images/';
		
		$vk = new VkApi($this->token, $this->groupId, $this->vkUserId, $imgDir);
		$vk->setPost($this->post);
		$result = $vk->curlPost();
		
		$resPost = $vk->post(null, $vk->getPhotosByResponse($result));
		//Log::info('ответ на создание поста: '.$resPost);
	}
}