<?

namespace Bitrix\Seo\Retargeting\Services;

use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Account;

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
			'method' => 'GET',
			'endpoint' => 'me/adaccounts',
			'fields' => array(
				'fields' => 'account_id,id,name'
			)
		));
	}

	public function getProfile()
	{
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'me/',
			'fields' => array(
				'fields' => 'id,name,picture,link'
			)
		));


		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['NAME'],
				'LINK' => $data['LINK'],
				'PICTURE' => $data['PICTURE'] ? $data['PICTURE']['data']['url'] : null,
			);
		}
		else
		{
			return null;
		}
	}
}
