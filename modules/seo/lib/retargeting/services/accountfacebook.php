<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Application;
use \Bitrix\Seo\Retargeting\Account;

class AccountFacebook extends Account
{
	const TYPE_CODE = 'facebook';

	const REGIONS_LIST_CACHE_TTL = 60*60*24*30; // 1 month

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		return $this->getRequest()->send(array(
			'methodName' => 'retargeting.account.list',
			'parameters' => array()
		));
	}

	public function getProfile()
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.profile',
			'parameters' => array()
		));

		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['NAME'],
				'LINK' => $data['LINK'],
				'PICTURE' => $data['PICTURE'] ? $data['PICTURE']['data']['url'] : null,
			);
		}

		return null;
	}

	public function getRegionsList()
	{
		$cache = Application::getInstance()->getManagedCache();
		$cacheId = 'seo|facebook|audience|region_list|'.LANGUAGE_ID;
		$data = [];

		if ($cache->read(static::REGIONS_LIST_CACHE_TTL, $cacheId))
		{
			$data = $cache->get($cacheId);
		}
		else
		{
			$result = $this->getRequest()->send(array(
				'methodName' => 'retargeting.audience.regions',
				'parameters' => array()
			));

			if ($result->isSuccess())
			{
				foreach($result->getData() as $region)
				{
					$data[] = [
						'id' => $region['key'],
						'name' => $region['name']
					];
				}
				usort($data, function ($a, $b)
				{
					return strcmp($a['name'], $b['name']);
				});
				$cache->set($cacheId, $data);
			}
		}

		return $data;
	}
}
