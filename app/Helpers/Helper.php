<?php

namespace App\Helpers;

class Helper {
	const urlRegExp = '/^((http|https):\/\/)?vk.com\/([0-9a-z_.]+)/';
	
	/**
	 * Для API VK.com
	 * по ссылке на группу выдает обьект с ключем "owner_id" || "domain"
	 */
	public static function groupForVkApiByHref($href) {
		$data        = [];
		$res         = preg_match(self::urlRegExp, $href, $matchResult);
		$group       = '';
		
		if (count($matchResult) === 0) {
			$group = $href;
		} else {
			$group = $matchResult[count($matchResult) - 1];
		}

		$res = preg_match('/^(public|club)([0-9]+)/', $group, $groupMatched);
		
		if (count($groupMatched) !== 0) {
			$owner_id = '-' . $groupMatched[count($groupMatched) - 1];
            $data['owner_id'] = (int) $owner_id;
        } else {
            $data['domain'] = $group;
        }
        
        return $data;
	}
	
	public static function groupIdForLink($id) {
		if ($id > 0) {
			return $id;
		}
		
		return $id * (-1);
	}
	
	/**
	 * По результату метода "groupForVkApiByHref"
	 * Возвращает полную ссылку
	 */
	public static function hrefByGroupObjVk($group) {
		$href = 'https://vk.com/';
		if (isset($group['owner_id'])) {
			return $href . 'club' . self::groupIdForLink($group['owner_id']);
		}
		
		return $href . $group['domain'];
	}
}
