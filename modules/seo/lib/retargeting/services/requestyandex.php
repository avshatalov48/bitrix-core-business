<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Seo\Retargeting\ProxyRequest;

class RequestYandex extends ProxyRequest
{
	const TYPE_CODE = 'yandex';
	const REST_METHOD_PREFIX = 'seo.client.ads.yandex';

	protected function directQuery(array $params = array())
	{
		$this->endpoint = $params['endpoint'];

		$url = 'https://api-audience.yandex.ru/v1/management/';
		$url .= $params['endpoint'];

		$clientParameters = is_array($params['fields']) ? $params['fields'] : array();
		$this->client->setHeader('Authorization', 'OAuth ' . $this->adapter->getToken());

		if ($params['method'] == 'GET')
		{
			$url .= '?' . http_build_query($clientParameters, "", "&");
			return $this->client->get($url);
		}
		elseif ($params['method'] == 'DELETE')
		{
			return $this->client->delete($url, $clientParameters, true);
		}
		else
		{
			return $this->client->post($url, $clientParameters, true);
		}
	}
}
