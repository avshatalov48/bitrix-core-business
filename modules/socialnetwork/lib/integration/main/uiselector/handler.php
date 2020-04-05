<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class Handler
{
	const ENTITY_TYPE_GROUPS = 'GROUPS';
	const ENTITY_TYPE_USERS = 'USERS';
	const ENTITY_TYPE_EMAILUSERS = 'EMAILUSERS';
	const ENTITY_TYPE_CRMEMAILUSERS = 'CRMEMAILUSERS';
	const ENTITY_TYPE_SONETGROUPS = 'SONETGROUPS';
	const ENTITY_TYPE_PROJECTS = 'PROJECTS';

	public static function isExtranetUser()
	{
		return (
			Loader::includeModule('extranet')
			&& !\CExtranet::isIntranetUser()
		);
	}

	public static function getNameTemplate($requestFields = array())
	{
		if (!empty($requestFields["nt"]))
		{
			preg_match_all("/(#NAME#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\\s|\\,/", urldecode($requestFields["nt"]), $matches);
			$result = implode("", $matches[0]);
		}
		else
		{
			$result = \CSite::getNameFormat(false);
		}

		return $result;
	}

	public static function OnUISelectorActionProcessAjax(Event $event)
	{
		$result = new EventResult(EventResult::UNDEFINED, null, 'socialnetwork');

		$action = $event->getParameter('action');

		$resultParams = false;

		if ($action == \Bitrix\Main\UI\Selector\Actions::GET_DATA)
		{
			$resultParams = Entities::getData($event->getParameter('options'));
		}
		elseif ($action == \Bitrix\Main\UI\Selector\Actions::SEARCH)
		{
			$resultParams = Search::process($event->getParameter('requestFields'));
		}
		elseif ($action == \Bitrix\Main\UI\Selector\Actions::GET_DEPARTMENT_DATA)
		{
			$resultParams = Entities::getDepartmentData($event->getParameter('requestFields'));
		}

		if ($resultParams)
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'result' => $resultParams
				),
				'socialnetwork'
			);
		}

		return $result;
	}

	public static function OnUISelectorEntitiesGetList(Event $event)
	{
		$itemsSelected = $event->getParameter('itemsSelected');

		if (
			empty($itemsSelected)
			|| !is_array($itemsSelected)
		)
		{
			return new EventResult(EventResult::ERROR, null, 'socialnetwork');
		}

		$entities = Entities::getList(array('itemsSelected' => $itemsSelected));

		return new EventResult(
			EventResult::SUCCESS,
			array(
				'result' => $entities
			),
			'socialnetwork'
		);
	}

	public static function OnUISelectorGetProviderByEntityType(Event $event)
	{
		$result = new EventResult(EventResult::UNDEFINED, null, 'socialnetwork');

		$entityType = $event->getParameter('entityType');

		$provider = false;

		switch($entityType)
		{
			case self::ENTITY_TYPE_GROUPS:
				$provider = new \Bitrix\Socialnetwork\Integration\Main\UISelector\Groups;
				break;
			case self::ENTITY_TYPE_USERS:
				$provider = new \Bitrix\Socialnetwork\Integration\Main\UISelector\Users;
				break;
			case self::ENTITY_TYPE_EMAILUSERS:
				$provider = new \Bitrix\Socialnetwork\Integration\Main\UISelector\EmailUsers;
				break;
			case self::ENTITY_TYPE_CRMEMAILUSERS:
				$provider = new \Bitrix\Socialnetwork\Integration\Main\UISelector\CrmEmailUsers;
				break;
			case self::ENTITY_TYPE_SONETGROUPS:
				$provider = new \Bitrix\Socialnetwork\Integration\Main\UISelector\SonetGroups;
				break;
			case self::ENTITY_TYPE_PROJECTS:
				$provider = new \Bitrix\Socialnetwork\Integration\Main\UISelector\Projects;
				break;
			default:
				$provider = false;
		}

		if ($provider)
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'result' => $provider
				),
				'socialnetwork'
			);
		}

		return $result;
	}
}
