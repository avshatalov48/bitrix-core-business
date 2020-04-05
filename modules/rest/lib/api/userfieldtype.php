<?php
namespace Bitrix\Rest\Api;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AuthTypeException;
use Bitrix\Rest\HandlerHelper;
use Bitrix\Rest\OAuth\Auth;
use Bitrix\Rest\PlacementTable;
use Bitrix\Rest\RestException;
use Bitrix\Rest\UserField\Callback;

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

	public static function add($param, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$param = array_change_key_case($param, CASE_UPPER);

		$userTypeId = toLower($param['USER_TYPE_ID']);
		$placementHandler = $param['HANDLER'];

		if(strlen($userTypeId) <= 0)
		{
			throw new ArgumentNullException("USER_TYPE_ID");
		}

		if(strlen($placementHandler) <= 0)
		{
			throw new ArgumentNullException("HANDLER");
		}

		$appInfo = AppTable::getByClientId($server->getClientId());;

		HandlerHelper::checkCallback($placementHandler, $appInfo);

		$placementBind = array(
			'APP_ID' => $appInfo['ID'],
			'PLACEMENT' => static::PLACEMENT_UF_TYPE,
			'PLACEMENT_HANDLER' => $placementHandler,
			'TITLE' => $userTypeId,
			'ADDITIONAL' => $userTypeId,
		);

		if(!empty($param['TITLE']))
		{
			$placementBind['TITLE'] = trim($param['TITLE']);
		}

		if(!empty($param['DESCRIPTION']))
		{
			$placementBind['COMMENT'] = trim($param['DESCRIPTION']);
		}

		$result = PlacementTable::add($placementBind);
		if(!$result->isSuccess())
		{
			$errorMessage = $result->getErrorMessages();
			throw new RestException(
				'Unable to set placement handler: '.implode(', ', $errorMessage),
				RestException::ERROR_CORE
			);
		}
		else
		{
			Callback::bind(array(
				'ID' => $result->getId(),
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

		$userTypeId = toLower($param['USER_TYPE_ID']);

		if(strlen($userTypeId) <= 0)
		{
			throw new ArgumentNullException("USER_TYPE_ID");
		}

		$updateFields = array();
		if(!empty($param['HANDLER']))
		{
			$appInfo = AppTable::getByClientId($server->getClientId());;
			HandlerHelper::checkCallback($param['HANDLER'], $appInfo);

			$updateFields['PLACEMENT_HANDLER'] = $param['HANDLER'];
		}

		if(!empty($param['TITLE']))
		{
			$updateFields['TITLE'] = trim($param['TITLE']);
		}

		if(!empty($param['DESCRIPTION']))
		{
			$updateFields['COMMENT'] = trim($param['DESCRIPTION']);
		}

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
			throw new ArgumentNullException('HANDLER|TITLE|DESCRIPTION');
		}

		return true;
	}

	public static function delete($param, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$param = array_change_key_case($param, CASE_UPPER);

		$userTypeId = toLower($param['USER_TYPE_ID']);

		if(strlen($userTypeId) <= 0)
		{
			throw new ArgumentNullException("USER_TYPE_ID");
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