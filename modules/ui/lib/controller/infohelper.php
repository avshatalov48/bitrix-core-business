<?

namespace Bitrix\UI\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class InfoHelper extends Engine\Controller
{
	public function getInitParamsAction()
	{
		return \Bitrix\UI\InfoHelper::getInitParams();
	}

	public function activateDemoLicenseAction()
	{
		$result	= [
			'success' => 'N',
		];
		if (Loader::includeModule('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$queryField = [
				'DEMO' => 'Y',
				'SITE' => BX24_HOST_NAME,
			];

			if (function_exists('bx_sign'))
			{
				$queryField['hash'] = bx_sign(md5(implode('|', $queryField)));
			}

			$httpClient = new HttpClient();
			$res = $httpClient->post('https://www.1c-bitrix.ru/buy_tmp/b24_coupon.php', $queryField);
			if ($res && mb_strpos($res, 'OK') !== false)
			{
				$result['success'] = 'Y';
			}
		}

		return $result;
	}
}
