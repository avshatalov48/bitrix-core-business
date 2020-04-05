<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Request;


class RequestFacebook extends Request
{
	const TYPE_CODE = 'facebook';

	public function query(array $params = array())
	{
		$url = 'https://graph.facebook.com/v2.10/';
		$url .= $params['endpoint'];

		$clientParameters = is_array($params['fields']) ? $params['fields'] : array();
		$clientParameters = $clientParameters + array('access_token' => $this->adapter->getToken());

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