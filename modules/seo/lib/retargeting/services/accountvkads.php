<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use \Bitrix\Seo\Retargeting\Account;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;

class AccountVkads extends Account
{
	const TYPE_CODE = 'vkads';

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
			'methodName' => 'analytics.profile',
			'parameters' => []
		));

		if (
			$response->isSuccess()
			&& ($data = $response->fetch())
		)
		{
			$result = [
				'ID' => $data['ID'],
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
				'LINK' => 'https://ads.vk.com/hq/budget/transactions/',
			];

			$result['PICTURE'] = (Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http')
				. '://'
				.  Context::getCurrent()->getServer()->getHttpHost() . '/bitrix/images/seo/integration/vklogo.svg';

			return $result;
		}

		return null;
	}
}
