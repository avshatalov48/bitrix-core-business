<?php


namespace Bitrix\Rest\Marketplace;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web;
use Bitrix\Main\ModuleManager;

class MarketplaceActions
{
	public static function getItems($placement, $userLang)
	{
		$response = [];
		$items = [];
		$params = 'placement='.$placement.'&lang='.$userLang;
		if(ModuleManager::isModuleInstalled('bitrix24'))
		{
			$zone = mb_strtolower(\CBitrix24::getPortalZone());
			$params .= '&zone='.$zone;
		}
		else
		{
			$hash = \Bitrix\Main\Analytics\Counter::getAccountId();
			$params .= '&hash='.$hash;
		}

		$client = new HttpClient();
		$client->query(Web\HttpClient::HTTP_GET, 'https://util.1c-bitrix.ru/b24/buttons.php?'.$params);

		if ($client->getStatus() == 200)
		{
			$resp = $client->getResult();
			try
			{
				$response = Web\Json::decode($resp);
			}
			catch (ArgumentException $e)
			{
			}
		}

		if (is_array($response) && !empty($response))
		{
			foreach ($response as $item)
			{
				$items[$item['id']] = [
					'NAME' => $item['name'],
					'COLOR' => $item['color'],
					'HANDLER' => $item['link'],
					'IMAGE' => $item['image'],
					'SLIDER' => $item['slider'],
				];
			}
		}

		return $items;
	}
}