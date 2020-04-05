<?php

namespace Bitrix\Blog\Integration\Socialnetwork;

class CounterPost
{
	public static function increment($params = array())
	{
		$socnetPerms = (
			is_array($params)
			&& !empty($params['socnetPerms'])
			&& is_array($params['socnetPerms'])
				? $params['socnetPerms']
				: array()
		);

		$logId = (
			is_array($params)
			&& !empty($params['logId'])
			&& intval($params['logId']) > 0
				? intval($params['logId'])
				: 0
		);

		$logEventId = (
			is_array($params)
			&& !empty($params['logEventId'])
				? $params['logEventId']
				: ''
		);

		$sendToAuthor = (
			is_array($params)
			&& !empty($params['sendToAuthor'])
				? $params['sendToAuthor']
				: false
		);

		if (
			$logId <= 0
			|| empty($logEventId)
		)
		{
			return false;
		}

		$userIdList = array();
		$forAll = (
			in_array("AU", $socnetPerms)
			|| in_array("G2", $socnetPerms)
		);

		if (!$forAll)
		{
			foreach($socnetPerms as $code)
			{
				if (preg_match('/^U(\d+)$/', $code, $matches))
				{
					$userIdList[] = $matches[1];
				}
				elseif (!in_array($code, array("SA")))
				{
					$userIdList = array();
					break;
				}
			}
		}

		\CSocNetLog::counterIncrement(array(
			"ENTITY_ID" => $logId,
			"EVENT_ID" => $logEventId,
			"TYPE" => "L",
			"FOR_ALL_ACCESS" =>  $forAll,
			"USERS_TO_PUSH" => (
				$forAll
				|| empty($userIdList)
				|| count($userIdList) > 20
					? array()
					: $userIdList
			),
			"SEND_TO_AUTHOR" => (
				$sendToAuthor
					? "Y"
					: "N"
			)
		));

		return true;
	}
}
