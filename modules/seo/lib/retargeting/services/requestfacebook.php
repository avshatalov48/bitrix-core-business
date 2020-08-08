<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Web\Json;
use Bitrix\Seo\Retargeting\ProxyRequest;

class RequestFacebook extends ProxyRequest
{
	const TYPE_CODE = 'facebook';
	const REST_METHOD_PREFIX = 'seo.client.ads.facebook';

	protected function directQuery(array $params = array())
	{
		$url = 'https://graph.facebook.com/v5.0/';
		$url .= $params['endpoint'];

		$clientParameters = is_array($params['fields']) ? $params['fields'] : array();
		$clientParameters = $clientParameters + array('access_token' => $this->adapter->getToken());

		$result = '';
		if ($params['method'] == 'GET')
		{
			$url .= '?' . http_build_query($clientParameters, "", "&");
			$result = $this->client->get($url);
		}
		elseif ($params['method'] == 'DELETE')
		{
			$result = $this->client->delete($url, $clientParameters, true);
		}
		else
		{
			$result = $this->client->post($url, $clientParameters, true);
		}
		if (!$params['has_pagination'])
			return $result;

		try
		{
			$partialResult = $result;
			$result = [];
			$page = 1;
			do
			{
				$decodedResult = Json::decode($partialResult);
				$nextPage = ($decodedResult['paging'] && $decodedResult['paging']['next']) ? $decodedResult['paging']['next'] : false;
				unset($decodedResult['paging']);

				$result = array_merge_recursive($result, $decodedResult);

				if ($nextPage)
				{
					$this->client->query($params['method'], $nextPage);
					$partialResult = $this->client->getResult();
					$page++;
				}
				else
				{
					if ($page == 1) // if haven't ['paging']['next'] in original response
					{
						return $partialResult;
					}
					break;
				}
			}
			while($nextPage && $page < 20); // max 500 items

			return Json::encode($result);
		}
		catch (\Exception $e)
		{
			return $result;
		}
	}
}