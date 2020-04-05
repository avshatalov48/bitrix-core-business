<?php
namespace Bitrix\Socialservices;

use Bitrix\Main\Web\Uri;

class ApManager
{
	public static function receive($userId, $connectionString)
	{
		if(static::checkState())
		{
			$connection = static::parseConnectionString($connectionString);

			if($connection)
			{
				$uri = new Uri($connection['endpoint']);

				if($uri->getHost())
				{
					$dbRes = ApTable::getList(array(
						'filter' => array(
							'=USER_ID' => $userId,
							'=DOMAIN' => $uri->getHost()
						),
						'select' => array('ID')
					));
					$existingEntry = $dbRes->fetch();
					if($existingEntry)
					{
						$result = ApTable::update($existingEntry['ID'], array(
							'ENDPOINT' => $uri->getLocator(),
							'LAST_AUTHORIZE' => '',
						));
					}
					else
					{
						$result = ApTable::add(array(
							'USER_ID' => $userId,
							'DOMAIN' => $uri->getHost(),
							'ENDPOINT' => $uri->getLocator(),
						));
					}

					return $result->isSuccess();
				}
			}
		}

		return false;
	}

	protected static function checkState()
	{
		return \CSocServAuthManager::checkUniqueKey();
	}

	protected static function parseConnectionString($connectionString)
	{
		$client = \CBitrix24NetPortalTransport::init();
		if($client)
		{
			$result = $client->call('client.authorize', array('apcode' => $connectionString));

			if($result && $result['result'])
			{
				return array(
					'endpoint' => $result['result']['ENDPOINT'],
				);
			}
		}

		return false;
	}
}