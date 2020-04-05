<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Seo\Retargeting\Account;

class AccountVkontakte extends Account
{
	const TYPE_CODE = 'vkontakte';

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		// https://vk.com/dev/ads.getAccounts

		return $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'ads.getAccounts'
		));
	}

	public function getProfile()
	{
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'users.get',
			'fields' => array(
				//'user_ids' => array(),
				'fields' => 'photo_50,screen_name'
			)
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
		else
		{
			return null;
		}
	}
}