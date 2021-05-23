<?

namespace Bitrix\Seo\Marketing\Services;

use Bitrix\Seo\Marketing\AdCampaign;

class AdCampaignFacebook extends AdCampaign
{
	const TYPE_CODE = 'facebook';

	/**
	 * @param array $params
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createCampaign(array $params = [])
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.create.campaign',
			'parameters' => $params
		));

		if ($response->isSuccess())
		{
			return $response->getData();
		}

		$errors = [];
		foreach ($response->getErrors() as $error)
		{
			$errors[] = $error->getMessage();
		}
		return [
			'error' => true,
			'errors' => $errors
		];
	}

	public function getAdSetList($accountId)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.adset.list',
			'parameters' => array(
				'accountId' => $accountId
			)
		));

		if ($response->isSuccess())
		{
			$result = [];
			while($data = $response->fetch())
			{
				$result[] = $data;
			}
			return $result;
		}

		return null;
	}

	public function getCampaignList($accountId)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.campaign.list',
			'parameters' => array(
				'accountId' => $accountId
			)
		));
		if ($response->isSuccess())
		{
			$result = [];
			while($data = $response->fetch())
			{
				$result[] = $data;
			}
			return $result;
		}

		return null;
	}

	public function updateAds($adsId)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.ads.update',
			'parameters' => array(
				'adsId' => $adsId
			)
		));

		return $response->isSuccess();
	}

	public function getAds($adsId)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.ads.get',
			'parameters' => array(
				'adsId' => $adsId
			)
		));
		if ($response->isSuccess())
		{
			$result = [];
			while($data = $response->fetch())
			{
				$result[] = $data;
			}
			return $result;
		}
	}

	public function searchTargetingData($params)
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.search.targeting',
			'parameters' => array(
				'query' => $params['q'],
				'type' => $params['type'],
				'locale' => \Bitrix\Main\Application::getInstance()->getContext()->getLanguage(),
			)
		));
		if ($response->isSuccess())
		{
			return $response->getData();
		}
	}
}
