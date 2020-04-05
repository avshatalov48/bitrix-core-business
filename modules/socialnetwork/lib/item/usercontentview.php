<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserContentViewTable;
use Bitrix\Main\DB\SqlQueryException;

class UserContentView
{
	public static function getAvailability()
	{
		static $result = null;
		if ($result !== null)
		{
			return $result;
		}

		$result = true;
/*

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $result;
		}

		if (Loader::includeModule('bitrix24'))
		{
			$result = (
				!in_array(\CBitrix24::getLicenseType(), array('project'), true)
				|| \CBitrix24::isNfrLicense()
				|| \CBitrix24::isDemoLicense()
			);
		}
*/
		return $result;

	}

	public static function getViewData($params = array())
	{
		if (!is_array($params))
		{
			return false;
		}

		$contentId = (isset($params['contentId']) ? $params['contentId'] : false);
		if (empty($contentId))
		{
			return false;
		}

		$result = array();

		if (!is_array($contentId))
		{
			$contentId = array($contentId);
		}

		$res = UserContentViewTable::getList(array(
			'filter' => array(
				'@CONTENT_ID' => $contentId
			),
			'select' => array('CNT', 'CONTENT_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID'),
			'runtime' => array(
				new ExpressionField('CNT', 'COUNT(*)')
			),
			'group' => array('CONTENT_ID')
		));

		while ($content = $res->fetch())
		{
			$result[$content['CONTENT_ID']] = $content;
		}

		return $result;
	}

	public static function getUserList($params = array())
	{
		global $USER;

		$result = array(
			'items' => array(),
			'hiddenCount' => 0
		);

		$contentId = (!empty($params['contentId']) ? $params['contentId'] : false);
		$pageNum = (!empty($params['page']) ? intval($params['page']) : 1);
		$pathToUserProfile = (!empty($params['pathToUserProfile']) ? $params['pathToUserProfile'] : '');
		$pageSize = 7;

		if (
			!$contentId
			&& $pageNum <= 0
		)
		{
			return $result;
		}

		$select = array(
			'USER_ID', 'DATE_VIEW', 'USER.ID', 'USER.NAME', 'USER.LAST_NAME', 'USER.SECOND_NAME', 'USER.LOGIN', 'USER.PERSONAL_PHOTO'
		);

		$extranetInstalled = $mailInstalled = false;
		$extranetIdList = array();

		if (ModuleManager::isModuleInstalled('extranet'))
		{
			$extranetInstalled = true;
			$select[] = "USER.UF_DEPARTMENT";
		}

		if (IsModuleInstalled('mail'))
		{
			$mailInstalled = true;
			$select[] = "USER.EXTERNAL_AUTH_ID";
		}

		$queryParams = array(
			'order' => array(
				'DATE_VIEW' => 'DESC'
			),
			'filter' => array(
				'=CONTENT_ID' => $contentId
			),
			'select' => $select
		);

		if (!$extranetInstalled)
		{
			$queryParams['limit'] = $pageSize;
			$queryParams['offset'] = ($pageNum - 1) * $pageSize;
		}

		$userList = array();
		$timeZoneOffset = \CTimeZone::getOffset();

		$res = \Bitrix\Socialnetwork\UserContentViewTable::getList($queryParams);

		while ($fields = $res->fetch())
		{
			$photoSrc = '';
			if (
				!empty($fields['SOCIALNETWORK_USER_CONTENT_VIEW_USER_PERSONAL_PHOTO'])
				&& intval($fields['SOCIALNETWORK_USER_CONTENT_VIEW_USER_PERSONAL_PHOTO']) > 0
			)
			{
				$file = \CFile::resizeImageGet(
					$fields["SOCIALNETWORK_USER_CONTENT_VIEW_USER_PERSONAL_PHOTO"],
					array('width' => 58, 'height' => 58),
					BX_RESIZE_IMAGE_EXACT,
					false
				);
				$photoSrc = $file["src"];
			}

			$userFields = array(
				'NAME' => $fields['SOCIALNETWORK_USER_CONTENT_VIEW_USER_NAME'],
				'LAST_NAME' => $fields['SOCIALNETWORK_USER_CONTENT_VIEW_USER_LAST_NAME'],
				'SECOND_NAME' => $fields['SOCIALNETWORK_USER_CONTENT_VIEW_USER_SECOND_NAME'],
				'LOGIN' => $fields['SOCIALNETWORK_USER_CONTENT_VIEW_USER_LOGIN'],
			);

			$userType = '';
			if (
				$mailInstalled
				&& $fields["SOCIALNETWORK_USER_CONTENT_VIEW_USER_EXTERNAL_AUTH_ID"] == "email"
			)
			{
				$userType = "mail";
			}
			elseif (
				$extranetInstalled
				&& (
					empty($fields["SOCIALNETWORK_USER_CONTENT_VIEW_USER_UF_DEPARTMENT"])
					|| intval($fields["SOCIALNETWORK_USER_CONTENT_VIEW_USER_UF_DEPARTMENT"][0]) <= 0
				)
			)
			{
				$userType = "extranet";
				$extranetIdList[] = $fields["USER_ID"];
			}

			$dateView = ($fields['DATE_VIEW'] instanceof \Bitrix\Main\Type\DateTime ? $fields['DATE_VIEW']->toString() : '');

			$userList[$fields['USER_ID']] = array(
				'ID' => $fields['USER_ID'],
				'TYPE' => $userType,
				'URL' => \CUtil::jSEscape(\CComponentEngine::makePathFromTemplate($pathToUserProfile, array(
					"UID" => $fields["USER_ID"],
					"user_id" => $fields["USER_ID"],
					"USER_ID" => $fields["USER_ID"]
				))),
				'PHOTO_SRC' => $photoSrc,
				'FULL_NAME' => \CUser::formatName(\CSite::getNameFormat(), $userFields, true, true),
				'DATE_VIEW' => $dateView,
				'DATE_VIEW_FORMATTED' => (!empty($dateView) ? \CComponentUtil::getDateTimeFormatted(MakeTimeStamp($dateView), "FULL", $timeZoneOffset) : '')
			);
		}

		$userIdToCheckList = array();

		if (Loader::includeModule('extranet'))
		{
			$userIdToCheckList = (
				\CExtranet::isIntranetUser(SITE_ID, $USER->getId())
					? $extranetIdList
					: array_keys($userList)
			);
		}

		if (!empty($userIdToCheckList))
		{
			$myGroupsUserList = \CExtranet::getMyGroupsUsersSimple(\CExtranet::getExtranetSiteID());
			foreach ($userIdToCheckList as $userIdToCheck)
			{
				if (
					!in_array($userIdToCheck, $myGroupsUserList)
					&& $userIdToCheck != $USER->getId()
				)
				{
					unset($userList[$userIdToCheck]);
					$result['hiddenCount']++;
				}
			}
		}

		if (!$extranetInstalled)
		{
			$result['items'] = $userList;
		}
		else
		{
			if ($pageNum <= ((count($userList) / $pageSize) + 1))
			{
				$res = new \CDBResult();
				$res->initFromArray($userList);
				$res->navStart($pageSize, false, $pageNum);

				while($user = $res->fetch())
				{
					$result['items'][] = $user;
				}
			}
		}

		$result['items'] = array_values($userList);

		return $result;
	}

	public static function deleteNoDemand($userId = 0)
	{
		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$result = true;

		try
		{
			\Bitrix\Main\Application::getConnection()->queryExecute("DELETE FROM ".UserContentViewTable::getTableName()." WHERE USER_ID = ".$userId);
		}
		catch (SqlQueryException $exception)
		{
			$result = false;
		}

		return $result;
	}
}
