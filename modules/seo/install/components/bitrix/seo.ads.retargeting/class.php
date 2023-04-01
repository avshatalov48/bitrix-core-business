<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Contract\Controllerable;

use \Bitrix\Seo\Retargeting\AdsAudience;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SeoAdsRetargetingComponent extends CBitrixComponent implements Controllerable
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return $this->checkAccess();
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME_PREFIX'] = isset($this->arParams['INPUT_NAME_PREFIX']) ? $this->arParams['INPUT_NAME_PREFIX'] : '';
		$this->arParams['HAS_ACCESS'] = isset($this->arParams['HAS_ACCESS']) ? (bool) $this->arParams['HAS_ACCESS'] : false;
		$this->arParams['AUDIENCE_LOOKALIKE_MODE'] = (bool)($this->arParams['AUDIENCE_LOOKALIKE_MODE'] ?? false);

		return $this->arParams;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'INPUT_NAME_PREFIX',
			'HAS_ACCESS',
		];
	}

	protected function prepareResult()
	{
		$this->arResult['ERRORS'] = array();

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		if (!$this->errors->isEmpty())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}

	public function onPrepareComponentParams($arParams)
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('seo'))
		{
			$this->errors->setError(new Error('Module `seo` is not installed.'));
			return $arParams;
		}

		$this->arParams = $arParams;
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
		}

		return $this->arParams;
	}

	protected function checkAccess()
	{
		if (!$this->arParams['HAS_ACCESS'])
		{
			$this->errors->setError(new Error('Access denied.'));
			return false;
		}

		return true;
	}

	protected function prepareAjaxAnswer(array $data)
	{
		$errorTexts = AdsAudience::getErrors();
		foreach ($errorTexts as $errorText)
		{
			$this->errors->setError(new Error($errorText));
		}

		/** @var Error $error */
		$error = $this->errors->current();

		return [
			'data' => $data,
			'error' => !$this->errors->isEmpty(),
			'text' => $error ? $error->getMessage() : ''
		];
	}

	public function configureActions()
	{
		return [];
	}

	public function getAccountsAction($type, $clientId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = AdsAudience::getService();
			$service->setClientId($clientId);
			$data = AdsAudience::getAccounts($type);
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function getProviderAction($type, $clientId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$data = static::getAdsProvider($type, $clientId);
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function logoutAction($type, $clientId = null, $logoutClientId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = AdsAudience::getService();
			$service->setClientId($logoutClientId);
			AdsAudience::removeAuth($type);

			$data = static::getAdsProvider($type, $clientId);
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function getAudiencesAction($type, $clientId = null, $accountId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = AdsAudience::getService();
			$service->setClientId($clientId);
			$data = AdsAudience::getAudiences($type, $accountId);
		}

		return $this->prepareAjaxAnswer($data);
	}

	/**
	 * Return audience array with normalized status and status translation
	 * Normalize status example: 'READY', 'PROCESSED', 'OTHER'
	 * @param $type
	 * @param $messageCode
	 * @param $clientId
	 * @param $accountId
	 * @return array
	 */
	public function getAudiencesWithNormalizedStatusAction(
		$type,
		$messageCode = '',
		$clientId = null,
		$accountId = null
	): array
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = AdsAudience::getService();
			$service->setClientId($clientId);
			$data = AdsAudience::getAudienceWithNormalizedStatus($type, $messageCode, $accountId);
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function addAudienceAction($type, $name = null, $clientId = null, $accountId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = AdsAudience::getService();
			$service->setClientId($clientId);

			$audienceId = AdsAudience::addAudience($type, $accountId, $name);
			if ($audienceId)
			{
				$data['id'] = $audienceId;
			}
			else
			{
				$data['error'] = implode(', ', AdsAudience::getErrors());
			}
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function getRegionsAction($type, $clientId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = AdsAudience::getService();
			$service->setClientId($clientId);
			$data = AdsAudience::getRegions($type);
			$langId = mb_strtolower(LANGUAGE_ID);
			$langId = ($langId == 'en' ? 'us' : $langId);
			array_walk($data, function (&$region) use ($langId)
			{
				$region['isDefault'] = (mb_strtolower($region['id']) == $langId);
			});
		}

		return $this->prepareAjaxAnswer($data);
	}

	protected static function getAdsProvider($adsType, $clientId = null)
	{
		$service = AdsAudience::getService();
		$service->setClientId($clientId);
		$providers = AdsAudience::getProviders([$adsType]);
		$isFound = false;
		$provider = array();
		foreach ($providers as $type => $provider)
		{
			if ($type == $adsType)
			{
				$isFound = true;
				break;
			}
		}

		if (!$isFound)
		{
			return null;
		}

		return $provider;
	}
}