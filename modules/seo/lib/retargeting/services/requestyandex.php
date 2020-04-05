<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Request;

class RequestYandex extends Request
{
	const TYPE_CODE = 'yandex';

	public function query(array $params = array())
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