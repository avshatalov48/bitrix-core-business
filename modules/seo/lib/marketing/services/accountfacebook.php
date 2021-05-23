<?

namespace Bitrix\Seo\Marketing\Services;

use Bitrix\Seo\Marketing\Account;

class AccountFacebook extends Account
{
	const TYPE_CODE = 'facebook';

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		return $this->getRequest()->send(array(
			'methodName' => 'marketing.account.list',
			'parameters' => array()
		));
	}

	public function getProfile()
	{
		$response = $this->getRequest()->send(array(
			'methodName' => 'marketing.profile',
			'parameters' => array()
		));

		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['NAME'],
				'LINK' => $data['LINK'],
				'CURRENCY' => $data['CURRENCY'],
				'PICTURE' => $data['PICTURE'] ? $data['PICTURE']['data']['url'] : null,
			);
		}

		return null;
	}

	public function getInstagramList()
	{
		return $this->getRequest()->send(array(
			'methodName' => 'marketing.account.instagram.list',
			'parameters' => array()
		));
	}
}
