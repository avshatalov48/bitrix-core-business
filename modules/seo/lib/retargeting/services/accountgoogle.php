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
		$response = $this->request->send(array(
			'methodName' => 'account.list',
			'parameters' => array()
		));

		return $response;
	}

	public function getProfile()
	{
		$response = $this->request->send(array(
			'methodName' => 'retargeting.profile',
			'parameters' => array()
		));

		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['NAME'],
				'LINK' => '',
				'PICTURE' => $data['PICTURE'],
			);
		}

		return null;
	}
}