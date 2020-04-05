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
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.profile',
			'parameters' => array()
		));

		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['REAL_NAME'] ?: $data['LOGIN'],
				'LINK' => '',
				'PICTURE' => 'https://avatars.mds.yandex.net/get-yapic/0/0-0/islands-50',
			);
		}

		return null;
	}
}