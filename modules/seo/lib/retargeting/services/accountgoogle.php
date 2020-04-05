<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Account;
use \Bitrix\Seo\Retargeting\Response;

class AccountGoogle extends Account
{
	const TYPE_CODE = 'google';

	public function getList()
	{
		// fake

		$response = Response::create(static::TYPE_CODE);
		$response->setData(array(array('ID' => 1)));

		return $response;
	}

	public function getProfile()
	{
		$response = $this->getRequest()->getClient()->get(
			'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' .
			urlencode($this->getRequest()->getAuthAdapter()->getToken())
		);

		if ($response)
		{
			$response = Json::decode($response);
			if (is_array($response))
			{
				return array(
					'ID' => $response['id'],
					'NAME' => $response['name'],
					'LINK' => '',
					'PICTURE' => $response['picture'],
				);
			}
		}


		return null;
	}
}