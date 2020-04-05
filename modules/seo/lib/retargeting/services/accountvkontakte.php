<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use \Bitrix\Seo\Retargeting\Account;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;

class AccountVkontakte extends Account
{
	const TYPE_CODE = 'vkontakte';

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'retargeting.account.list',
			'parameters' => array()
		));
		$data = $response->getData();
		$data = array_values(array_filter($data, function ($item) {
			return ($item['account_type'] == 'general'); // only "general" is supported
		}));
		$response->setData($data);
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
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
				'LINK' => 'https://vk.com/' . $data['SCREEN_NAME'],
				'PICTURE' => $data['PHOTO_50'],
			);
		}

		return null;
	}
}