<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2019 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\UserTagTable;
use Bitrix\Main\Entity;

class UserTag
{
	public static function getTagData($params)
	{
		$result = [
			'USERS' => []
		];

		if (
			empty($params)
			&& !is_array($params)
			|| empty(trim($params['tag']))
		)
		{
			return $result;
		}

		$tag = trim($params['tag']);

		$currentUserId = (isset($params['currentUserId']) ? intval($params['currentUserId']) : 0);
		$avatarSize = (isset($params['avatarSize']) ? intval($params['avatarSize']) : 100);
		$pageSize = (isset($params['pageSize']) ? intval($params['pageSize']) : 10);
		$pageNum = (!empty($params['page']) ? intval($params['page']) : 1);
		$pathToUser = (!empty($params['pathToUser']) ? $params['pathToUser'] : '');

		$ratingId = \CRatings::getAuthorityRating();
		if (intval($ratingId) <= 0)
		{
			return $result;
		}

		$userIdList = [];

		$queryParams = [
			'order' => (
				\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
					? [
						'SUBORDINATE.VOTES' => 'DESC'
					]
				: []
			),
			'filter' => [
				'NAME' => $tag
			],
			'runtime' => (
				\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
					? [
						new \Bitrix\Main\Entity\ReferenceField(
							'SUBORDINATE',
							'\Bitrix\Intranet\RatingSubordinateTable',
							Entity\Query\Join::on('this.USER_ID', 'ref.ENTITY_ID')->where('ref.RATING_ID', intval($ratingId)),
							["join_type" => "left"]
						)
					]
					: []
			),
			'select' => [ 'USER_ID' ]
		];

		if (isset($params['pageSize']))
		{
			$queryParams['limit'] = $pageSize;
			$queryParams['offset']  = ($pageNum - 1) * $pageSize;
		}

		$res = UserTagTable::getList($queryParams);

		while ($fields = $res->fetch())
		{
			$userIdList[] = $fields['USER_ID'];
		}

		if (!empty($userIdList))
		{
			$userData = \Bitrix\Socialnetwork\Item\UserTag::getUserData([
				'userIdList' => $userIdList,
				'pathToUser' => $pathToUser,
				'avatarSize' => $avatarSize
			]);

			foreach($userIdList as $userId)
			{
				if (isset($userData[$userId]))
				{
					$result['USERS'][] = $userData[$userId];
				}
			}
		}

		$result['CAN_ADD'] = 'N';
		if ($currentUserId > 0)
		{
			$res = \Bitrix\Socialnetwork\UserTagTable::getList([
				'filter' => [
					'USER_ID' => $currentUserId,
					'NAME' => $tag
				]
			]);
			if (!($res->fetch()))
			{
				$result['CAN_ADD'] = 'Y';
			}
		}

		return $result;
	}

	public static function getUserData($params)
	{
		$result = [];

		if (
			empty($params)
			&& !is_array($params)
		)
		{
			return $result;
		}

		$userIdList = (!empty($params['userIdList']) && is_array($params['userIdList']) ? $params['userIdList'] : []);
		$avatarSize = (!empty($params['avatarSize']) && intval($params['avatarSize']) > 0 ? intval($params['avatarSize']) : 100);

		if (empty($userIdList))
		{
			return $result;
		}

		$select = [ 'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'PERSONAL_GENDER' ];

		$getListClassName = '\Bitrix\Main\UserTable';
		if (Loader::includeModule('intranet'))
		{
			$getListClassName = '\Bitrix\Intranet\UserTable';
			$select[] = 'USER_TYPE';
		}
		$getListMethodName = 'getList';

		$res = $getListClassName::$getListMethodName(array(
			'filter' => array(
				'@ID' => $userIdList
			),
			'select' => $select
		));

		while ($userFields = $res->fetch())
		{
			$result[$userFields["ID"]] = array(
				'ID' => $userFields["ID"],
				'NAME_FORMATTED' => \CUser::formatName(
					\CSite::getNameFormat(false),
					$userFields,
					true
				),
				'PERSONAL_PHOTO' => array(
					'ID' => $userFields['PERSONAL_PHOTO'],
					'SRC' => false
				),
				'PERSONAL_GENDER' => $userFields['PERSONAL_GENDER'],
				'URL' => \CComponentEngine::makePathFromTemplate($params['pathToUser'], array('user_id' => $userFields['ID'])),
				'TYPE' => (!empty($userFields['USER_TYPE']) ? $userFields['USER_TYPE'] : '')
			);

			if (intval($userFields['PERSONAL_PHOTO']) > 0)
			{
				$imageFile = \CFile::getFileArray($userFields["PERSONAL_PHOTO"]);
				if ($imageFile !== false)
				{
					$file = \CFile::resizeImageGet(
						$imageFile,
						array("width" => $avatarSize, "height" => $avatarSize),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$result[$userFields["ID"]]['PERSONAL_PHOTO']['SRC'] = $file['src'];
				}
			}
		}

		return $result;
	}
}
