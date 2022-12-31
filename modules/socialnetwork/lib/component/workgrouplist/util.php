<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

class Util
{
	public static function filterNumericIdList(array $idList = []): array
	{
		$idList = array_map(static function($value) {
			return (int)$value;
		}, $idList);

		$idList = array_filter($idList, static function($value) {
			return ($value > 0);
		});

		$idList = array_unique($idList);

		return $idList;
	}
}
