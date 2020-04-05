<?php
namespace Bitrix\Rest\Api;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Rest\AccessException;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AuthTypeException;
use Bitrix\Rest\HandlerHelper;
use Bitrix\Rest\OAuth\Auth;
use Bitrix\Rest\PlacementTable;
use Bitrix\Rest\RestException;

class Placement extends \IRestService
{
	const SCOPE_PLACEMENT = 'placement';

	public static function onRestServiceBuildDescription()
	{
		return array(
			static::SCOPE_PLACEMENT => array(
				'placement.list' => array(
					'callback' => array(__CLASS__, 'getList'),
					'options' => array()
				),
				'placement.bind' => array(
					'callback' => array(__CLASS__, 'bind'),
					'options' => array()
				),
				'placement.unbind' => array(
					'callback' => array(__CLASS__, 'unbind'),
					'options' => array()
				),
				'placement.get' => array(
					'callback' => array(__CLASS__, 'get'),
					'options' => array()
				)
			),
		);
	}


	public static function getList($query, $n, \CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException("Application context required");
		}

		$result = array();

		$serviceDescription = $server->getServiceDescription();

		$scopeList = array(\CRestUtil::GLOBAL_SCOPE);

		$query = array_change_key_case($query, CASE_UPPER);

		if(isset($query['SCOPE']))
		{
			if($query['SCOPE'] != '')
			{
				$scopeList = array($query['SCOPE']);
			}
		}
		elseif($query['FULL'] == true)
		{
			$scopeList = array_keys($serviceDescription);
		}
		else
		{
			$scopeList = static::getScope($server);
			$scopeList[] = \CRestUtil::GLOBAL_SCOPE;
		}

		$placementList = static::getPlacementList($server, $scopeList);

		foreach($placementList as $placement => $placementInfo)
		{
			if(!$placementInfo['private'])
			{
				$result[] = $placement;
			}
		}

		return $result;
	}


	public static function bind($params, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$params = array_change_key_case($params, CASE_UPPER);

		$placement = toUpper($params['PLACEMENT']);
		$placementHandler = $params['HANDLER'];

		if(strlen($placement) <= 0)
		{
			throw new ArgumentNullException("PLACEMENT");
		}

		if($placement == PlacementTable::PLACEMENT_DEFAULT)
		{
			throw new ArgumentException("Wrong value", "PLACEMENT");
		}

		if(strlen($placementHandler) <= 0)
		{
			throw new ArgumentNullException("HANDLER");
		}

		$appInfo = static::getApplicationInfo($server);

		HandlerHelper::checkCallback($placementHandler, $appInfo);

		$scopeList = static::getScope($server);
		$scopeList[] = \CRestUtil::GLOBAL_SCOPE;

		$placementList = static::getPlacementList($server, $scopeList);
		$placementInfo = $placementList[$placement];

		if(is_array($placementInfo) && !$placementInfo['private'])
		{
			$placementBind = array(
				'APP_ID' => $appInfo['ID'],
				'PLACEMENT' => $placement,
				'PLACEMENT_HANDLER' => $placementHandler,
			);

			if(!empty($params['TITLE']))
			{
				$placementBind['TITLE'] = trim($params['TITLE']);
			}

			if(!empty($params['DESCRIPTION']))
			{
				$placementBind['COMMENT'] = trim($params['DESCRIPTION']);
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

			return true;
		}

		throw new RestException(
			'Placement not found',
			PlacementTable::ERROR_PLACEMENT_NOT_FOUND
		);
	}


	public static function unbind($params, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$params = array_change_key_case($params, CASE_UPPER);

		$placement = toUpper($params['PLACEMENT']);
		$placementHandler = $params['HANDLER'];

		if(strlen($placement) <= 0)
		{
			throw new ArgumentNullException("PLACEMENT");
		}

		$cnt = 0;

		$placementList = static::getPlacementList($server);

		if(array_key_exists($placement, $placementList) && !$placementList[$placement]['private'])
		{
			$appInfo = static::getApplicationInfo($server);

			$filter = array(
				'=APP_ID' => $appInfo["ID"],
				'=PLACEMENT' => $placement,
			);

			if(strlen($placementHandler) > 0)
			{
				$filter['=PLACEMENT_HANDLER'] = $placementHandler;
			}

			$dbRes = PlacementTable::getList(array(
				'filter' => $filter
			));

			while($placementHandler = $dbRes->fetch())
			{
				$cnt++;
				$result = PlacementTable::delete($placementHandler["ID"]);
				if($result->isSuccess())
				{
					$cnt++;
				}
			}
		}

		return array('count' => $cnt);
	}


	public static function get($params, $n, \CRestServer $server)
	{
		static::checkPermission($server);

		$result = array();

		$appInfo = static::getApplicationInfo($server);

		$dbRes = PlacementTable::getList(array(
			"filter" => array(
				"=APP_ID" => $appInfo["ID"],
			),
			'order' => array(
				"ID" => "ASC",
			)
		));

		$placementList = static::getPlacementList($server);

		while($placement = $dbRes->fetch())
		{
			if(array_key_exists($placement['PLACEMENT'], $placementList) && !$placementList[$placement['PLACEMENT']]['private'])
			{
				$result[] = array(
					"placement" => $placement['PLACEMENT'],
					"handler" => $placement['PLACEMENT_HANDLER'],
					"title" => $placement['TITLE'],
					"description" => $placement['COMMENT'],
				);
			}
		}

		return $result;
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

	protected static function getScope(\CRestServer $server)
	{
		$result = array();

		$authData = $server->getAuthData();

		$scopeList = explode(',', $authData['scope']);

		$serviceDescription = $server->getServiceDescription();
		foreach($scopeList as $scope)
		{
			if(array_key_exists($scope, $serviceDescription))
			{
				$result[] = $scope;
			}
		}

		return $result;
	}

	protected static function getApplicationInfo(\CRestServer $server)
	{
		if($server->getAuthType() !== Auth::AUTH_TYPE)
		{
			throw new AuthTypeException("Application context required");
		}

		return AppTable::getByClientId($server->getClientId());
	}

	protected static function getPlacementList(\CRestServer $server, $scopeList = null)
	{
		$serviceDescription = $server->getServiceDescription();

		if($scopeList === null)
		{
			$scopeList = array_keys($serviceDescription);
		}

		$result = array();

		foreach($scopeList as $scope)
		{
			if(
				isset($serviceDescription[$scope])
				&& is_array($serviceDescription[$scope][\CRestUtil::PLACEMENTS])
			)
			{
				$result = array_merge($result, $serviceDescription[$scope][\CRestUtil::PLACEMENTS]);
			}
		}

		return $result;
	}
}