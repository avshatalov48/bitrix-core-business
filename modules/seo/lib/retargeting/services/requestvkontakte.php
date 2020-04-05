<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Request;

class RequestVkontakte extends Request
{
	const TYPE_CODE = 'vkontakte';

	public function query(array $params = array())
	{
		$url = 'https://api.vk.com/method/';
		$url .= $params['endpoint'];

		$clientParameters = is_array($params['fields']) ? $params['fields'] : array();
		$clientParameters = $clientParameters + ['v' => '5.7', 'access_token' => $this->adapter->getToken()];


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