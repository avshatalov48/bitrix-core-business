<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper\Workgroup;

use Bitrix\Socialnetwork\WorkgroupSubjectTable;

class Subject
{
	public static function getName(int $subjectId = 0): string
	{
		$result = '';
		if ($subjectId <= 0)
		{
			return $result;
		}

		$res = WorkgroupSubjectTable::getList([
			'filter' => [
				'=ID' => $subjectId,
			],
			'select' => [
				'NAME'
			],
		]);

		if ($subjectFields = $res->fetch())
		{
			$result = $subjectFields['NAME'];
		}

		return $result;
	}
}
