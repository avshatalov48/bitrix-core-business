<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Dictionary\Booklet;
use Bitrix\Rest\Marketplace\Url;

class RestMarketplaceBookletComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $countApp = 8;
	protected $countAppBanner = 3;
	protected $iconList = [
		'default' => '/bitrix/images/rest/icon/default.svg',
		'add' => '/bitrix/images/rest/icon/phone-rent.svg',
		'adjust' => '/bitrix/images/rest/icon/phone-rent.svg',
		'phone-rent' => '/bitrix/images/rest/icon/phone-rent.svg',
	];

	protected function checkRequiredParams()
	{
		return true;
	}

	public function onPrepareComponentParams($params)
	{
		$params['SET_TITLE'] = isset($params['SET_TITLE']) ? $params['SET_TITLE'] === 'Y' : true;

		return $params;
	}

	private function get($code)
	{
		$result = [];
		$booklet = (new Booklet())->getValues();
		$key = array_search($code, array_column($booklet, 'code'), true);
		if ($key !== false)
		{
			$result = array_change_key_case($booklet[$key], CASE_UPPER);
			if (!empty($result['OPTION']))
			{
				try
				{
					$result['OPTION'] = Json::decode(base64_decode($result['OPTION']));
				}
				catch (ArgumentException $e)
				{
					$result = [];
				}
			}
		}

		return $result;
	}

	private function getIcon($code)
	{
		return $this->iconList[$code] ?? $this->iconList['default'];
	}

	protected function prepareResult()
	{
		$analyticFrom = 'booklet';
		if (!empty($this->request->get('from')))
		{
			$analyticFrom .= '_' . htmlspecialcharsbx($this->request->get('from'));
		}

		$result = [
			'CONTAINER_ID' => md5(uniqid(mt_rand(), true)),
			'ITEMS' => [],
			'APP_TAG_BANNER' => [],
			'APP_TAG' => []
		];

		if ($this->arParams['CODE'])
		{
			$booklet = $this->get($this->arParams['CODE']);
			$analyticFrom .= '_' . $this->arParams['CODE'];
		}
		if (empty($booklet))
		{
			$this->errors->setError(new Error(Loc::getMessage('REST_MARKETPLACE_BOOKLET_ERROR_NOT_FOUND')));
			return false;
		}

		$result['MP_DETAIL_URL_TPL'] = Url::getApplicationDetailUrl(null, $analyticFrom);
		$result['MP_INDEX_PATH'] = Url::getMarketplaceUrl($analyticFrom);

		if ($this->arParams['SET_TITLE'])
		{
			$result['TITLE'] = !empty($booklet['OPTION']['TITLE']) ? $booklet['OPTION']['TITLE'] : Loc::getMessage('REST_MARKETPLACE_BOOKLET_DEFAULT_TITLE');
		}

		if (!empty($booklet['OPTION']['ACTION_TITLE']))
		{
			$result['ACTION_TITLE'] = $booklet['OPTION']['ACTION_TITLE'];
		}
		else
		{
			$result['ACTION_TITLE'] = Loc::getMessage('REST_MARKETPLACE_BOOKLET_DEFAULT_ACTION_TITLE');
		}

		if (is_array($booklet['OPTION']['ACTION']))
		{
			$isAdmin = \CRestUtil::isAdmin();
			$actionList = [];
			foreach ($booklet['OPTION']['ACTION'] as $action)
			{
				$actionList[] = [
					'title' => $action['title'],
					'url' => $action['url'],
					'onclick' => $action['onclick'],
					'icon' => $action['icon_code'] ? $this->getIcon($action['icon_code']) : $action['icon'],
					'disabled' => $action['admin'] ? !$isAdmin : false
				];
			}
			$result['ITEMS'] = $actionList;
		}

		if (!empty($booklet['OPTION']['APP_BANNER']['TAG']))
		{
			$result['APP_TAG_BANNER'] = $booklet['OPTION']['APP_BANNER']['TAG'];
			$result['APP_BANNER_COUNT'] = (int) $booklet['OPTION']['APP_BANNER']['COUNT'];
			if ($result['APP_BANNER_COUNT'] === 0)
			{
				$result['APP_BANNER_COUNT'] = $this->countAppBanner;
			}
		}

		if (!empty($booklet['OPTION']['APP']['TAG']))
		{
			$result['APP_TAG'] = $booklet['OPTION']['APP']['TAG'];
			$result['APP_COUNT'] = (int) $booklet['OPTION']['APP']['COUNT'];
			if ($result['APP_COUNT'] === 0)
			{
				$result['APP_COUNT'] = $this->countApp;
			}

			$result['MP_TAG_PATH'] = '';
			if (!empty($result['APP_TAG']))
			{
				$uri = new Uri(Url::getMarketplaceUrl($analyticFrom));
				$uri->addParams(['tag' => $result['APP_TAG']]);
				$result['MP_TAG_PATH'] = $uri->getUri();
			}
		}

		if (!empty($booklet['OPTION']['DESCRIPTION_TITLE']) && !empty($booklet['OPTION']['DESCRIPTION']))
		{
			$result['DESCRIPTION_TITLE'] = $booklet['OPTION']['DESCRIPTION_TITLE'];
			$result['DESCRIPTION'] = $booklet['OPTION']['DESCRIPTION'];
		}

		$this->arResult = $result;

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
		$this->errors = new ErrorCollection();

		if (!$this->checkRequiredParams())
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
}