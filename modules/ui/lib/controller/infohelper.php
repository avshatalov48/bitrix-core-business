<?

namespace Bitrix\UI\Controller;

use Bitrix\Bitrix24;
use Bitrix\Bitrix24\License\Market;
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

	public function getBuySubscriptionUrlAction()
	{
		$action = 'blank';
		if (Loader::includeModule('bitrix24'))
		{
			$url = Market::PATH_MARKET_BUY;
		}
		else
		{
			require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/classes/general/update_client.php';
			$lkeySign = md5(\CUpdateClient::GetLicenseKey());
			$url = 'https://www.1c-bitrix.ru/buy_tmp/key_update.php?license_key=' . $lkeySign . '&tobasket=y&action=b24subscr';
		}

		return [
			'url' => $url,
			'action' => $action,
		];
	}

	public function activateTrialFeatureAction(string $featureId)
	{
		$result	= [
			'success' => 'N',
		];

		if (
			Loader::includeModule('bitrix24')
			&& method_exists(Bitrix24\Feature::class, 'trialFeature')
		)
		{
			$trialable = Bitrix24\Feature::isFeatureTrialable($featureId);

			if ($trialable)
			{
				$result['success'] = Bitrix24\Feature::trialFeature($featureId) ? 'Y' : 'N';
			}
			else
			{
				$result['error'] = 'IS_NOT_TRIALABLE';
			}
		}

		return $result;
	}

	public function showLimitSliderAction(): bool
	{
		return true;
	}
}
