<?

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Router;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Marketing\Configurator;
use Bitrix\Seo\Marketing\Service;
use Bitrix\Seo\Marketing\Services\AdCampaignFacebook;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('currency'))
{
	ShowError('Module `currency` not installed');
	die();
}

if (!Bitrix\Main\Loader::includeModule('landing'))
{
	ShowError('Module `landing` not installed');
	die();
}

if (!Bitrix\Main\Loader::includeModule('crm'))
{
	ShowError('Module `crm` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SeoAdsBuilderComponent extends CBitrixComponent implements Controllerable
{
	/** @var ErrorCollection $errors */
	protected $errors;

	private const STORE_ID = 'store_v3';

	protected function checkRequiredParams()
	{
		return $this->checkAccess();
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME_PREFIX'] = isset($this->arParams['INPUT_NAME_PREFIX']) ? $this->arParams['INPUT_NAME_PREFIX'] : '';
		$this->arParams['HAS_ACCESS'] = isset($this->arParams['HAS_ACCESS']) ? (bool) $this->arParams['HAS_ACCESS'] : false;
		$this->arParams['TEMPLATE'] = $this->arParams['TEMPLATE']??'';
		$this->arParams['TARGET_URL'] = $this->arParams['TARGET_URL']??'';

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

		$this->arResult['BASE_CURRENCY'] = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
		$this->arResult['IBLOCK_ID'] = \CAllCrmCatalog::GetDefaultID();
		$this->arResult['BASE_PRICE_ID'] = \CAllCrmCatalog::GetCatalogTypeID();
		$this->arResult['STORE_EXISTS'] = boolval($this->getDefaultStore());

		$this->arResult['POST_LIST_URL'] = UrlManager::getInstance()
			->create('getPostListContent', [
			'c' => 'bitrix:seo.ads.builder',
			'mode' => Router::COMPONENT_MODE_AJAX,
		]);

		$this->arResult['AUDIENCE_URL'] = UrlManager::getInstance()
			->create('getAudienceContent', [
			'c' => 'bitrix:seo.ads.builder',
			'mode' => Router::COMPONENT_MODE_AJAX,
		]);

		$this->arResult['CRM_AUDIENCE_URL'] = UrlManager::getInstance()
			->create('getCrmAudienceContent', [
			'c' => 'bitrix:seo.ads.builder',
			'mode' => Router::COMPONENT_MODE_AJAX,
		]);

		$this->arResult['PAGE_CONFIGURATION_URL'] = UrlManager::getInstance()
			->create('getPageConfigurationContent', [
			'c' => 'bitrix:seo.ads.builder',
			'mode' => Router::COMPONENT_MODE_AJAX,
		]);

		$this->arResult['IS_CLOUD'] = Loader::includeModule('bitrix24');
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

		$this->includeComponentTemplate($this->arParams['TEMPLATE']);
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
		$errorTexts = Configurator::getErrors();
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
			$service = Configurator::getService();
			$service->setClientId($clientId);
			$data = Configurator::getAccounts($type);
		}

		return $this->prepareAjaxAnswer($data);
	}


	public function getInstagramAccountsAction($type, $clientId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = Configurator::getService();
			$service->setClientId($clientId);
			$data = Configurator::getInstagramAccounts($type);
		}

		return $this->prepareAjaxAnswer($data);
	}


	public function convertCurrencyAction($sourceCurrency = null, $targetCurrency = null, $amount = null)
	{
		$amount = \CCurrencyRates::ConvertCurrency($amount, $sourceCurrency, $targetCurrency);

		return $this->prepareAjaxAnswer(['amount' => ceil($amount)]);
	}


	public function checkCurrencyExistsAction($currency = null)
	{
		return $this->prepareAjaxAnswer(['exists' => \CCurrency::GetByID($currency)]);
	}


	public function checkStoreExistsAction($currency = null)
	{
		return $this->prepareAjaxAnswer(['exists' => (bool)$this->getDefaultStore()]);
	}

	private function getDefaultStore()
	{
		$siteId = null;
		$res = \Bitrix\Landing\Site::getList(
			[
				'select' => ['ID'],
				'filter' => ['=TPL_CODE' => self::STORE_ID],
				'order'  => ['ID' => 'desc']
			]
		);
		if ($row = $res->fetch())
		{
			$siteId = $row['ID'];
		}

		return $siteId;
	}


	public function addCurrencyAction($newCurrency = null, $amountCnt = null, $course = null)
	{
		$languages = [];
		$languageIterator = LanguageTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=ACTIVE' => 'Y')
		));

		while ($existLanguage = $languageIterator->fetch())
		{
			$languages[$existLanguage['ID']] = mb_strtoupper($existLanguage['ID']);
		}

		$currencyInfo =  \Bitrix\Currency\CurrencyClassifier::getCurrency($newCurrency, $languages);

		$langs = [];
		$whiteList = [
			'FULL_NAME' => true,
			'FORMAT_STRING' => true,
			'DEC_POINT' => true,
			'THOUSANDS_VARIANT' => true,
			'DECIMALS' => true
		];

		foreach ($languages as $languageId => $language)
		{
			$langs[$languageId] = [
					'LID' => $languageId,
					'CURRENCY' => $newCurrency,
					'HIDE_ZERO' => 'Y',
					'THOUSANDS_SEP' => null
				] + array_intersect_key($currencyInfo[$language], $whiteList) ;
		}

		if (!\Bitrix\Currency\CurrencyManager::isCurrencyExist($newCurrency))
		{
			$createdCurrency =  CCurrency::Add(
				[
					'CURRENCY' => $currencyInfo['SYM_CODE'],
					'AMOUNT_CNT' => (int)$amountCnt,
					'AMOUNT' => (float)$course,
					'SORT' => 500,
					'NUMCODE' =>  $currencyInfo['NUM_CODE'],
					'LANG' => $langs
				]
			);
		}

		return $this->prepareAjaxAnswer(['success' => $createdCurrency]);
	}


	public function getPostListAction($type, $clientId = null, $accountId = null, $last = null, $limit = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = Configurator::getService();
			$service->setClientId($clientId);
			$parameters = [
				'accountId' => $accountId
			];

			if($limit)
			{
				$parameters['limit'] = $limit;
			}

			if($last)
			{
				$parameters['last'] = $last;
			}

			$data = Configurator::getPostList($type, $parameters);
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
			$service = Configurator::getService();
			$service->setClientId($logoutClientId);
			Configurator::removeAuth($type);

			$data = static::getAdsProvider($type, $clientId);
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function getAdAccountsAction($type, $clientId = null, $accountId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = Configurator::getService();
			$service->setClientId($clientId);
			$data = Configurator::getAudiences($type, $accountId);
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function addAudienceAction($type, $name = null, $clientId = null, $accountId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = Configurator::getService();
			$service->setClientId($clientId);

			$audienceId = Configurator::addAudience($type, $accountId, $name);
			if ($audienceId)
			{
				$data['id'] = $audienceId;
			}
			else
			{
				$data['error'] = implode(', ', Configurator::getErrors());
			}
		}

		return $this->prepareAjaxAnswer($data);
	}

	public function getRegionsAction($type, $clientId = null)
	{
		$data = [];
		if ($this->checkAccess())
		{
			$service = Configurator::getService();
			$service->setClientId($clientId);
			$data = Configurator::getRegions($type);
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
		$service = Configurator::getService();
		$service->setClientId($clientId);
		if($adsType === Service::TYPE_INSTAGRAM)
		{
			$adsType = Service::TYPE_FACEBOOK;
		}
		$providers = Configurator::getProviders([$adsType]);
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

	public function createCampaignAction(array $parameters)
	{
		$service = Configurator::getService();
		$parameters['budget'] = (($parameters['budget'] ?? 100) * 100) / (int)$parameters['duration'];
		$service->setClientId($parameters['client_id']);
		$parameters['name'] = 'new bitrix campaign';
		$data = Configurator::createCampaign(
			AdCampaignFacebook::TYPE_CODE,
			$parameters
		);

		return [
			'data' => $data
		];
	}

	public function getProductUrlAction($id)
	{
		$data = \Bitrix\Landing\Connector\Iblock::getElementUrl(self::STORE_ID, $id);

		return [
			'data' => $data
		];
	}
}