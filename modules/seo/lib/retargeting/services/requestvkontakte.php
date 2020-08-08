<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Seo\Retargeting\ProxyRequest;

class RequestVkontakte extends ProxyRequest
{
	const TYPE_CODE = 'vkontakte';
	const REST_METHOD_PREFIX = 'seo.client.ads.vkontakte';

	protected function directQuery(array $params = array())
	{
		$url = 'https://api.vk.com/method/';
		$url .= $params['endpoint'];

		$clientParameters = is_array($params['fields']) ? $params['fields'] : array();
		$clientParameters = $clientParameters + ['v' => '5.107', 'access_token' => $this->adapter->getToken()];


		if (isset($params['method']) && $params['method'] == 'POST')
		{
			return $this->client->post($url, $clientParameters);
		}
		else
		{
			$url .= '?' . http_build_query($clientParameters, "", "&");
			return $this->client->get($url);
		}
	}
}