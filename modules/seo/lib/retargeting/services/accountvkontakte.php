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

	const MAX_ADS_EDIT = 20;

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		// https://vk.com/dev/ads.getAccounts

		$result =  $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'ads.getAccounts',
		]);
		if ($result->isSuccess())
		{
			$list = [];
			while ($item = $result->fetch())
			{
				if ($item['ACCOUNT_TYPE'] === 'general')
				{
					$list[] = $item;
				}
			}
			$result->setData($list);
		}

		return $result;
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