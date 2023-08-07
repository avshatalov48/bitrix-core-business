<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Context;
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
		$result =  $this->getRequest()->send([
			'methodName' => 'retargeting.account.list',
		]);

		if ($result->isSuccess())
		{
			$list = [];
			while ($item = $result->fetch())
			{
				if (
					(
						isset($item['CLIENT_NAME'])
						|| isset($item['NAME'])
					)
					&& isset($item['ID'])
				)
				{
					$list[] = [
						'NAME' => !empty(trim($item['CLIENT_NAME'])) ? $item['CLIENT_NAME'] : $item['NAME'],
						'ACCOUNT_ID' => $item['ID'],
					];
				}
			}
			$result->setData($list);
		}

		return $result;
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
				'PICTURE' => (Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http')
					. '://'
					.  Context::getCurrent()->getServer()->getHttpHost() . '/bitrix/images/seo/integration/vklogo.svg',
			);
		}

		return null;
	}
}
