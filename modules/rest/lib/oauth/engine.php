<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage rest
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Rest\OAuth;


use Bitrix\Main\Config\Option;
use Bitrix\Main;

class Engine
{
	protected $scope = array(
		"rest", "application"
	);

	protected $client = null;

	public function __construct()
	{
	}


	/**
	 * @return \Bitrix\Rest\OAuth\Client
	 */
	public function getClient()
	{
		if(!$this->client)
		{
			$this->client = new Client(
				$this->getClientId(),
				$this->getClientSecret(),
				$this->getLicense()
			);
		}

		return $this->client;
	}

	public function isRegistered()
	{
		return $this->getClientId() !== false;
	}

	public function getClientId()
	{
		return Option::get("rest", "service_client_id", false);
	}

	public function getClientSecret()
	{
		return Option::get("rest", "service_client_secret", false);
	}

	public function setAccess(array $accessParams)
	{
		$connection = Main\Application::getInstance()->getConnection();
		$connection->startTransaction();
		try
		{
			Option::set("rest", "service_client_id", $accessParams["client_id"]);
			Option::set("rest", "service_client_secret", $accessParams["client_secret"]);
			$connection->commitTransaction();
		}
		catch (Main\ArgumentNullException $e)
		{
			$connection->rollbackTransaction();
		}

		$this->client = null;
	}

	public function clearAccess()
	{
		$this->setAccess(array(
			"client_id" => false,
			"client_secret" => false,
		));

		$this->client = null;
	}

	public function getLicense()
	{
		return LICENSE_KEY;
	}
}