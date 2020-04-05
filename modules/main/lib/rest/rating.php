<?php
namespace Bitrix\Main\Rest;

use Bitrix\Main;
use Bitrix\Rest;

if(Main\Loader::includeModule("rest")):

class Rating extends \IRestService
{
	const LIST_LIMIT = 20;

	public static function getLikeReactions($query, $nav = 0, \CRestServer $server)
	{
		$query = array_change_key_case($query, CASE_LOWER);

		$entityTypeId = (isset($query['entity_type_id']) ? $query['entity_type_id'] : '');
		$entityId = (isset($query['entity_id']) ? intval($query['entity_id']) : 0);

		if(
			empty($entityTypeId)
			|| $entityId <= 0
		)
		{
			throw new Rest\RestException("Wrong entity data.", Rest\RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$reactionResult = \CRatings::getRatingVoteReaction(array(
			"ENTITY_TYPE_ID" => $entityTypeId,
			"ENTITY_ID" => $entityId,
			"USE_REACTIONS_CACHE" => 'Y'
		));

		return $reactionResult['reactions'];
	}

	public static function getLikeList($query, $nav = 0, \CRestServer $server)
	{
		global $USER;

		$query = array_change_key_case($query, CASE_LOWER);
		$navParams = static::getNavData($nav, true);

		$pathToUserProfile = (isset($query['path_to_user_profile']) ? $query['path_to_user_profile'] : '');
		$entityTypeId = (isset($query['entity_type_id']) ? $query['entity_type_id'] : '');
		$entityId = (isset($query['entity_id']) ? intval($query['entity_id']) : 0);
		$reaction = (isset($query['reaction']) ? $query['reaction'] : false);
		$page = ($navParams['offset'] / $navParams['limit']) + 1;

		if(
			empty($entityTypeId)
			|| $entityId <= 0
		)
		{
			throw new Rest\RestException("Wrong entity data.", Rest\RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$queryParams = array(
			"ENTITY_TYPE_ID" => $entityTypeId,
			"ENTITY_ID" => $entityId,
			"LIST_PAGE" => $page,
			"LIST_LIMIT" => $navParams['limit'],
			"LIST_TYPE" => 'plus',
			"USE_REACTIONS_CACHE" => 'Y'
		);

		$extranetInstalled = $mailInstalled = false;
		if (Main\ModuleManager::isModuleInstalled('extranet'))
		{
			$extranetInstalled = true;
			$queryParams["USER_SELECT"] = array("UF_DEPARTMENT");
		}
		if (Main\ModuleManager::isModuleInstalled('mail'))
		{
			$mailInstalled = true;
			$queryParams["USER_FIELDS"] = array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "EXTERNAL_AUTH_ID");
		}

		if (!empty($reaction))
		{
			$queryParams["REACTION"] = $reaction;
		}

		$res = \CRatings::getRatingVoteList($queryParams);

		$voteList = array(
			'items_all' => $res['items_all'],
			'items_reaction' => ($reaction && isset($res['reactions']) && isset($res['reactions'][$reaction]) ? intval($res['reactions'][$reaction]) : 0),
			'items_page' => $res['items_page'],
			'items' => array()
		);

		foreach($res['items'] as $key => $value)
		{
			$userVote = array(
				'USER_ID' => $value['ID'],
				'VOTE_VALUE' => $value['VOTE_VALUE'],
				'PHOTO' => $value['PHOTO'],
				'PHOTO_SRC' => $value['PHOTO_SRC'],
				'FULL_NAME' => $value['FULL_NAME'],
				'URL' => \CUtil::jSEscape(\CComponentEngine::makePathFromTemplate($pathToUserProfile, array(
					"UID" => $value["USER_ID"],
					"user_id" => $value["USER_ID"],
					"USER_ID" => $value["USER_ID"]
				)))
			);

			if (
				$mailInstalled
				&& $value["EXTERNAL_AUTH_ID"] == "email"
			)
			{
				$userVote["USER_TYPE"] = "mail";
			}
			elseif (
				$extranetInstalled
				&& (
					empty($value["UF_DEPARTMENT"])
					|| intval($value["UF_DEPARTMENT"][0]) <= 0
				)
			)
			{
				$userVote["USER_TYPE"] = "extranet";
			}

			$voteList['items'][] = $userVote;
		}

		if (
			$USER->isAuthorized()
			&& $page == 1
		)
		{
			$event = new Main\Event(
				'main',
				'onRatingListViewed',
				array(
					'entityTypeId' => $entityTypeId,
					'entityId' => $entityId,
					'userId' => $USER->getId()
				)
			);
			$event->send();
		}

		return static::setNavData($voteList['items'], array(
			"count" => ($reaction && $reaction != 'all' ? $voteList['items_reaction'] : $voteList['items_all']),
			"offset" => $navParams['offset']
		));
	}
}

endif;