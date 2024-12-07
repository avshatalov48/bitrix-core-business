<?php
namespace Bitrix\Rest\Api;

use Bitrix\Rest\AccessException;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AuthTypeException;
use Bitrix\Rest\HandlerHelper;
use Bitrix\Rest\OAuth\Auth;
use Bitrix\Rest\PlacementLangTable;
use Bitrix\Rest\PlacementTable;
use Bitrix\Rest\RestException;
use Bitrix\Rest\UserField\Callback;
use Bitrix\Rest\Lang;
use Bitrix\Rest\Exceptions;

class UserFieldType extends \IRestService
{
	const SCOPE_USERFIELDTYPE = 'placement';
	const PLACEMENT_UF_TYPE = 'USERFIELD_TYPE';

	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE_USERFIELDTYPE => array(
				'userfieldtype.list' => array(
					'callback' => array(__CLASS__, 'getList'),
					'options' => array()
				),
				'userfieldtype.add' => array(
					'callback' => array(__CLASS__, 'add'),
					'options' => array()
				),
				'userfieldtype.update' => array(
					'callback' => array(__CLASS__, 'update'),
					'options' => array()
				),
				'userfieldtype.delete' => array(
					'callback' => array(__CLASS__, 'delete'),
					'options' => array()
				),
				\CRestUtil::PLACEMENTS => array(
					static::PLACEMENT_UF_TYPE => array(
						'private' => true
					),
				),
			),
		);
	}

	public static function getList($param, $nav, \CRestServer $server)
	{
		static::checkPermission($server);

		$navParams = static::getNavData($nav, true);

		$dbRes = PlacementTable::getList(array(
			'filter' => array(
				'=PLACEMENT' => static::PLACEMENT_UF_TYPE,
				'=REST_APP.CLIENT_ID' => $server->getClientId(),
			),
			'select' => array(
				'USER_TYPE_ID' => 'ADDITIONAL',
				'HANDLER' => 'PLACEMENT_HANDLER',
				'TITLE' => 'TITLE',
				'DESCRIPTION' => 'COMMENT'
			),

			'limit' => $navParams['limit'],
			'offset' => $navParams['offset'],
			'count_total' => true,
		));

		$result = array();
		while($handler = $dbRes->fetch())
		{
			$result[] = $handler;
		}

		return static::setNavData(
			$result,
			array(
				"count" => $dbRes->getCount(),
				"offset" => $navParams['offset']
			)
		);
	}

	private static function prepareOption($option): array
	{
		$result = [];
		if (is_array($option))
		{
			$option = array_change_key_case($option, CASE_LOWER);
			if ($option['height'])
			{
				$result['height'] = (int)$option['height'];
			}
		}

		return $result;
	}

	public static function add($param, $n, \CRestServer $server): bool
	{
		static::checkPermission($server);

		$param = array_change_key_case($param, CASE_UPPER);

		$userTypeId = mb_strtolower($param['USER_TYPE_ID'] ?? '');
		$placementHandler = $param['HANDLER'] ?? '';

		if ($userTypeId == '')
		{
			throw new Exceptions\ArgumentNullException("USER_TYPE_ID");
		}

		if ($placementHandler == '')
		{
			throw new Exceptions\ArgumentNullException("HANDLER");
		}

		$appInfo = AppTable::getByClientId($server->getClientId());;

		HandlerHelper::checkCallback($placementHandler, $appInfo);

		$placementBind = array(
			'APP_ID' => $appInfo['ID'],
			'PLACEMENT' => static::PLACEMENT_UF_TYPE,
			'PLACEMENT_HANDLER' => $placementHandler,
			'TITLE' => $userTypeId,
			'ADDITIONAL' => $userTypeId,
			'OPTIONS' => static::prepareOption($param['OPTIONS'] ?? null),
		);

		$placementBind = array_merge(
			$placementBind,
			Lang::fillCompatibility(
				$param,
				[
					'TITLE',
					'DESCRIPTION',
				],
				[
					'TITLE' => $placementBind['TITLE'] ?? null
				]
			)
		);
		$langAll = [];
		if ($placementBind['LANG_ALL'])
		{
			$langAll = $placementBind['LANG_ALL'];
		}
		unset($placementBind['LANG_ALL']);

		$result = PlacementTable::add($placementBind);
		if (!$result->isSuccess())
		{
			$errorMessage = $result->getErrorMessages();
			throw new RestException(
				'Unable to set placement handler: '.implode(', ', $errorMessage),
				RestException::ERROR_CORE
			);
		}
		else
		{
			$placementId = $result->getId();
			foreach ($langAll as $lang => $item)
			{
				$item['PLACEMENT_ID'] = $placementId;
				$item['LANGUAGE_ID'] = $lang;
				$res = PlacementLangTable::add($item);
				if (!$res->isSuccess())
				{
					throw new RestException(
						'Error: ' . implode(', ', $res->getErrorMessages()),
						RestException::ERROR_CORE
					);
				}
			}
			Callback::bind(array(
				'ID' => $placementId,
				'APP_ID' => $appInfo['ID'],
				'ADDITIONAL' => $userTypeId,
			));
		}

		return true;
	}

	public static function update($param, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$param = array_change_key_case($param, CASE_UPPER);

		$userTypeId = mb_strtolower($param['USER_TYPE_ID'] ?? '');

		if($userTypeId == '')
		{
			throw new Exceptions\ArgumentNullException("USER_TYPE_ID");
		}

		$updateFields = array();
		if(!empty($param['HANDLER']))
		{
			$appInfo = AppTable::getByClientId($server->getClientId());;
			HandlerHelper::checkCallback($param['HANDLER'], $appInfo);

			$updateFields['PLACEMENT_HANDLER'] = $param['HANDLER'];
		}

		if (array_key_exists('OPTIONS', $param))
		{
			$updateFields['OPTIONS'] = static::prepareOption($param['OPTIONS']);
		}

		$updateFields = array_merge(
			$updateFields,
			Lang::fillCompatibility(
				$param,
				[
					'TITLE',
					'DESCRIPTION',
				],
				[
					'TITLE' => $updateFields['TITLE'] ?? null
				]
			)
		);
		$langAll = [];
		if ($updateFields['LANG_ALL'])
		{
			$langAll = $updateFields['LANG_ALL'];
		}
		unset($updateFields['LANG_ALL']);

		if(count($updateFields) > 0)
		{
			$dbRes = PlacementTable::getList(array(
				'filter' => array(
					'=REST_APP.CLIENT_ID' => $server->getClientId(),
					'=ADDITIONAL' => $userTypeId
				),
				'select' => array('ID', 'APP_ID', 'ADDITIONAL')
			));
			$placementInfo = $dbRes->fetch();
			if($placementInfo)
			{
				$updateResult = PlacementTable::update($placementInfo['ID'], $updateFields);
				if($updateResult->isSuccess())
				{
					PlacementLangTable::deleteByPlacement($placementInfo['ID']);
					foreach ($langAll as $lang => $item)
					{
						$item['PLACEMENT_ID'] = $placementInfo['ID'];
						$item['LANGUAGE_ID'] = $lang;
						$res = PlacementLangTable::add($item);
						if (!$res->isSuccess())
						{
							throw new RestException(
								'Error: ' . implode(', ', $res->getErrorMessages()),
								RestException::ERROR_CORE
							);
						}
					}
					// rebind handler for failover reasons
					Callback::bind($placementInfo);
				}
				else
				{
					$errorMessage = $updateResult->getErrorMessages();
					throw new RestException(
						'Unable to update User Field Type: '.implode(', ', $errorMessage),
						RestException::ERROR_CORE
					);
				}
			}
			else
			{
				throw new RestException('User Field Type not found', RestException::ERROR_NOT_FOUND);
			}
		}
		else
		{
			throw new Exceptions\ArgumentNullException('HANDLER|TITLE|DESCRIPTION');
		}

		return true;
	}

	public static function delete($param, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$param = array_change_key_case($param, CASE_UPPER);

		$userTypeId = mb_strtolower($param['USER_TYPE_ID'] ?? '');

		if($userTypeId == '')
		{
			throw new Exceptions\ArgumentNullException("USER_TYPE_ID");
		}

		$dbRes = PlacementTable::getList(array(
			'filter' => array(
				'=REST_APP.CLIENT_ID' => $server->getClientId(),
				'=ADDITIONAL' => $userTypeId
			),
			'select' => array('ID', 'APP_ID', 'ADDITIONAL')
		));
		$placementInfo = $dbRes->fetch();
		if($placementInfo)
		{
			$deleteResult = PlacementTable::delete($placementInfo['ID']);
			if($deleteResult->isSuccess())
			{
				Callback::unbind($placementInfo);
			}
			else
			{
				$errorMessage = $deleteResult->getErrorMessages();
				throw new RestException(
					'Unable to delete User Field Type: '.implode(', ', $errorMessage),
					RestException::ERROR_CORE
				);
			}
		}
		else
		{
			throw new RestException('User Field Type not found', RestException::ERROR_NOT_FOUND);
		}

		return true;
	}

	protected static function checkPermission(\CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException("Application context required");
		}

		if(!\CRestUtil::isAdmin())
		{
			throw new AccessException();
		}
	}
}