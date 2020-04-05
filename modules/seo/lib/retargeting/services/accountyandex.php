<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Account;
use \Bitrix\Seo\Retargeting\Response;

class AccountYandex extends Account
{
	const TYPE_CODE = 'yandex';

	public function getList()
	{
		// fake

		$response = Response::create(static::TYPE_CODE);
		$response->setData(array(array('ID' => 1)));

		return $response;
	}

	public function getProfile()
	{
		// default_avatar_id
		// 'https://avatars.yandex.net/get-yapic//islands-50/';
		$response = $this->getRequest()->getClient()->get(
			'https://login.yandex.ru/info?format=json&oauth_token=' .
			$this->getRequest()->getAuthAdapter()->getToken()
		);

		if ($response)
		{
			$response = Json::decode($response);
			if (is_array($response))
			{
				return array(
					'ID' => $response['id'],
					'NAME' => $response['login'],
					'LINK' => '',
					'PICTURE' => 'https://avatars.mds.yandex.net/get-yapic/0/0-0/islands-50',
				);
			}
		}


		return null;
	}
}