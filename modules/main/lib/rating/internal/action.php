<?php

namespace Bitrix\Main\Rating\Internal;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class Action
{
	public static function vote(array $params = []): array
	{
		global $APPLICATION;

		$voteList = [];

		$allowVoteData = \CRatings::checkAllowVote($params);
		if (!$allowVoteData['RESULT'])
		{
			return $voteList;
		}

		$APPLICATION->RestartBuffer();
		$userData = [];
		$remoteAddr = $params['REMOTE_ADDR'];

		if (in_array($params['ACTION'], [ 'plus', 'minus' ]))
		{
			$userData = \CRatings::addRatingVote([
				'ENTITY_TYPE_ID' => $params['ENTITY_TYPE_ID'],
				'ENTITY_ID' => $params['ENTITY_ID'],
				'VALUE' => $params['ACTION'] === 'plus' ? 1 : -1,
				'USER_IP' => $remoteAddr,
				'USER_ID' => $params['CURRENT_USER_ID'],
				'REACTION' => (
					$params['ACTION'] === 'plus'
					&& !empty($params['REACTION'])
						? $params['REACTION']
						: \CAllRatings::REACTION_DEFAULT
				),
			]);

		}
		elseif ($params['ACTION'] === 'change')
		{
			$userData = \CRatings::changeRatingVote([
				'ENTITY_TYPE_ID' => $params['ENTITY_TYPE_ID'],
				'ENTITY_ID' => $params['ENTITY_ID'],
				'USER_IP' => $remoteAddr,
				'USER_ID' => $params['CURRENT_USER_ID'],
				'REACTION' => (
				!empty($params['REACTION'])
					? $params['REACTION']
					: \CAllRatings::REACTION_DEFAULT
				)
			]);
		}
		else if ($params['ACTION'] === 'cancel')
		{
			$userData = \CRatings::cancelRatingVote([
				'ENTITY_TYPE_ID' => $params['ENTITY_TYPE_ID'],
				'ENTITY_ID' => $params['ENTITY_ID'],
				'USER_ID' => $params['CURRENT_USER_ID'],
			]);
		}

		$voteList = \CRatings::getRatingVoteList([
			'ENTITY_TYPE_ID' => $params['ENTITY_TYPE_ID'],
			'ENTITY_ID' => $params['ENTITY_ID'],
			'LIST_LIMIT' => 0,
			'LIST_TYPE' => ($params['ACTION'] === 'minus' ? 'minus' : 'plus'),
		]);

		if ($params['RATING_RESULT'] === 'Y')
		{
			$voteList = array_merge(
				$voteList,
				self::getVoteResult($params['ENTITY_TYPE_ID'], $params['ENTITY_ID'])
			);
		}

		$voteList['action'] = $params['ACTION'];
		$voteList['user_data'] = $userData;

		return $voteList;
	}

	public static function getVoteResult($entityTypeId, $entityId): array
	{
		global $USER;

		$entityId = (int)$entityId;
		$userId = (int)$USER->getId();

		$ratingResult = \CRatings::getRatingVoteResult($entityTypeId, $entityId, $userId);
		if (empty($ratingResult))
		{
			$ratingResult['USER_HAS_VOTED'] = $USER->isAuthorized() ? 'N' : 'Y';
			$ratingResult['USER_VOTE'] = 0;
			$ratingResult['TOTAL_VALUE'] = 0;
			$ratingResult['TOTAL_VOTES'] = 0;
			$ratingResult['TOTAL_POSITIVE_VOTES'] = 0;
			$ratingResult['TOTAL_NEGATIVE_VOTES'] = 0;
		}

		$resultStatus = $ratingResult['TOTAL_VALUE'] < 0 ? 'minus' : 'plus';
		$resultTitle  = sprintf(
			Loc::getMessage('RATING_COMPONENT_DESC'),
			$ratingResult['TOTAL_VOTES'],
			$ratingResult['TOTAL_POSITIVE_VOTES'],
			$ratingResult['TOTAL_NEGATIVE_VOTES']
		);

		return [
			'resultValue' => $ratingResult['TOTAL_VALUE'],
			'resultVotes' => $ratingResult['TOTAL_VOTES'],
			'resultPositiveVotes' => $ratingResult['TOTAL_POSITIVE_VOTES'],
			'resultNegativeVotes' => $ratingResult['TOTAL_NEGATIVE_VOTES'],
			'resultStatus' => $resultStatus,
			'resultTitle' => $resultTitle,
		];
	}

	public static function list(array $params = []): array
	{
		$mailInstalled = ModuleManager::isModuleInstalled('mail');
		$extranetInstalled = ModuleManager::isModuleInstalled('extranet');

		if ($extranetInstalled)
		{
			$params['USER_SELECT'] = [ 'UF_DEPARTMENT' ];
		}

		if ($mailInstalled)
		{
			$params['USER_FIELDS'] = [
				'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN',
				'PERSONAL_PHOTO', 'EXTERNAL_AUTH_ID',
			];
		}

		$result = \CRatings::getRatingVoteList($params);

		$voteList = [
			'items' => [],
			'items_all' => $result['items_all'],
			'items_page' => $result['items_page'],
			'reactions' => (
				isset($result['reactions'])
				&& is_array($result['reactions'])
					? $result['reactions']
					: []
			),
			'list_page' => $result['list_page'],
		];

		foreach ($result['items'] as $key => $value)
		{
			$userVote = [
				'USER_ID' => $value['ID'],
				'VOTE_VALUE' => $value['VOTE_VALUE'],
				'PHOTO' => $value['PHOTO'],
				'PHOTO_SRC' => $value['PHOTO_SRC'],
				'FULL_NAME' => $value['FULL_NAME'],
				'URL' => \CComponentEngine::makePathFromTemplate(
					$params['PATH_TO_USER_PROFILE'],
					[
						'UID' => $value['USER_ID'],
						'user_id' => $value['USER_ID'],
						'USER_ID' => $value['USER_ID'],
					],
				),
			];

			if (
				$mailInstalled
				&& $value['EXTERNAL_AUTH_ID'] === 'email'
			)
			{
				$userVote['USER_TYPE'] = 'mail';
			}
			elseif (
				$extranetInstalled
				&& (
					empty($value['UF_DEPARTMENT'])
					|| (int)$value['UF_DEPARTMENT'][0] <= 0
				)
			)
			{
				$userVote['USER_TYPE'] = 'extranet';
			}

			$voteList['items'][] = $userVote;
		}

		if ($params['CURRENT_USER_ID'] > 0)
		{
			$event = new \Bitrix\Main\Event(
				'main',
				'onRatingListViewed',
				[
					'entityTypeId' => $params['ENTITY_TYPE_ID'],
					'entityId' => $params['ENTITY_ID'],
					'userId' => $params['CURRENT_USER_ID'],
				]
			);
			$event->send();
		}

		return $voteList;
	}
}
