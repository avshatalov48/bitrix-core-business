<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\ModuleManager;
use Bitrix\Rest\Engine\Access;
use Bitrix\Rest\Engine\Access\HoldEntity;
use Bitrix\Main\Loader;
use Bitrix\Rest\Preset\Data\Element;
use Bitrix\Rest\Preset\Provider;
use Bitrix\Main\SystemException;
use Bitrix\Rest\Url\DevOps;
use Bitrix\Rest\Analytic;

Loc::loadMessages(__FILE__);

class RestIntegrationEditComponent extends CBitrixComponent implements Controllerable
{
	private $lengthSecretPassword = 6;
	protected $error = [];

	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 */
	protected function checkRequiredParams()
	{
		if (empty($this->arParams["ELEMENT_CODE"]))
		{
			throw new SystemException('Error: ELEMENT_CODE parameter missing.');
		}

		return true;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'ID',
			'ELEMENT_CODE',
			'SET_TITLE',
			'PATH_TO_IFRAME'
		];
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? intVal($this->arParams['ID']) : 0;
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['ELEMENT_CODE'] = isset($this->arParams['ELEMENT_CODE']) ? $this->arParams['ELEMENT_CODE'] : '';
		$this->arParams['PATH_TO_IFRAME'] = isset($this->arParams['PATH_TO_IFRAME']) ? $this->arParams['PATH_TO_IFRAME'] : '';

		$request = Application::getInstance()->getContext()->getRequest();
		$this->arParams['NEED_GRID_OPEN'] = ($request->getPost("needGridOpen") === 'false')? false:true;
	}

	protected function getSavedData($result)
	{
		if ($result['ID'] > 0)
		{
			$data = Provider::getIntegration($result['ID']);
		}

		if (empty($data))
		{
			$this->error[] = Loc::getMessage('REST_INTEGRATION_EDIT_ERROR_NOT_FOUND');
		}
		else
		{
			$result = array_merge($result, $data);
		}

		return $result;
	}

	/**
	 * @throws SystemException
	 */
	protected function processResultData()
	{
		global $USER;
		$result = [
			'ERROR_MESSAGE' => [],
		];
		$isAdmin = CRestUtil::isAdmin();
		$userId = $USER->GetID();
		$params = $this->arParams;
		$presetData = Element::get($params['ELEMENT_CODE']);

		if (empty($presetData['OPTIONS']))
		{
			throw new SystemException(Loc::getMessage('REST_INTEGRATION_EDIT_ERROR_NOT_FOUND'));
		}

		if (
			!$isAdmin
			&&
			(
				$presetData['ADMIN_ONLY'] === 'Y'
				|| $presetData['OPTIONS']['WIDGET_NEEDED'] !== 'D'
				|| $presetData['OPTIONS']['APPLICATION_NEEDED'] !== 'D'
			)
		)
		{
			throw new SystemException(Loc::getMessage('REST_INTEGRATION_EDIT_ERROR_ACCESS_DENIED'));
		}

		if (isset($presetData['REQUIRED_MODULES']) && $presetData['REQUIRED_MODULES'])
		{
			foreach ($presetData['REQUIRED_MODULES'] as $val)
			{
				if (!ModuleManager::isModuleInstalled($val))
				{
					throw new SystemException(
						Loc::getMessage(
							'REST_INTEGRATION_EDIT_ERROR_REQUIRED_MODULES',
							[
								'#MODULE_CODE#' => $val
							]
						)
					);
				}
			}
		}

		if (!empty($params['ELEMENT_CODE']) && !empty($presetData))
		{
			$result['TITLE'] = $presetData['TITLE'];
			$result['ELEMENT_CODE'] = $presetData['ELEMENT_CODE'];
			$result['DESCRIPTION'] = $presetData['DESCRIPTION'];
			$result['DESCRIPTION_FULL'] = $presetData['DESCRIPTION_FULL'] ?? null;

			if (!isset($presetData['OPTIONS']['QUERY_NEEDED']) || $presetData['OPTIONS']['QUERY_NEEDED'] !== 'D')
			{
				$result['QUERY_NEEDED'] = $presetData['OPTIONS']['QUERY_NEEDED'] ?? null;
				$result['ERROR_MESSAGE'][] = Loc::getMessage(
					'REST_INTEGRATION_EDIT_ATTENTION_USES_WEBHOOK',
					[
						'#URL#' =>
							'<a href="'.\Bitrix\UI\Util::getArticleUrlByCode('12337906').'" >'
							. Loc::getMessage('REST_INTEGRATION_EDIT_ATTENTION_USES_WEBHOOK_URL_MESSAGE')
							. '</a>'
					]
				);
			}
			else
			{
				$result['QUERY_NEEDED'] = 'D';
			}
		}

		$result['ID'] = $params['ID'];
		$result = $this->getSavedData($result);

		if (!$isAdmin && (int)$userId !== (int)$result['USER_ID'])
		{
			throw new SystemException(Loc::getMessage('REST_INTEGRATION_EDIT_ERROR_ACCESS_DENIED'));
		}

		if (
			$isAdmin
			&& $userId !== $result['USER_ID']
			&& $result['QUERY_NEEDED'] !== 'D'
			&& !empty($result['PASSWORD_DATA_PASSWORD'])
		)
		{
			$result['READ_ONLY'] = 'Y';
			$secret = str_repeat('*', $this->lengthSecretPassword);
			$result['PASSWORD_DATA_URL'] = str_replace(
				$result['PASSWORD_DATA_PASSWORD'],
				$secret,
				$result['PASSWORD_DATA_URL']
			);
			$result['PASSWORD_DATA_PASSWORD'] = $secret;
		}

		if (!empty($this->error))
		{
			throw new SystemException(implode(', ', $this->error));
		}

		if (!empty($presetData['OPTIONS']['QUERY']))
		{
			$result['QUERY'] = $this->prepareQuery($result['QUERY'], $presetData['OPTIONS']['QUERY']);
		}

		unset($result['OUTGOING']);

		$blockList = [];
		if ($presetData['OPTIONS']['BOT_NEEDED'] !== 'D')
		{
			if ($result['BOT_ID'] > 0 && isset($result['QUERY']))
			{
				foreach ($result['QUERY'] as &$queryList)
				{
					foreach ($queryList['ITEMS'] as &$query)
					{
						if ($query['title'] === 'BOT_ID')
						{
							$query['value'] = $result['BOT_ID'];
						}
						if ($query['title'] === 'CLIENT_ID')
						{
							$query['value'] = $result['BOT_DATA_APP_ID'];
						}
					}
				}
				unset($queryList, $query);
			}
			$blockList[] = 'BOT';
		}
		if (!isset($presetData['OPTIONS']['QUERY_NEEDED']) || $presetData['OPTIONS']['QUERY_NEEDED'] !== 'D')
		{
			$blockList[] = 'INCOMING';
		}
		if ($presetData['OPTIONS']['OUTGOING_NEEDED'] !== 'D')
		{
			$blockList[] = 'OUTGOING';
		}
		if ($presetData['OPTIONS']['WIDGET_NEEDED'] !== 'D')
		{
			$blockList[] = 'WIDGET';
		}
		if ($presetData['OPTIONS']['APPLICATION_NEEDED'] !== 'D')
		{
			$blockList[] = 'APPLICATION';

			$result['ALLOW_ZIP_APPLICATION'] = \Bitrix\Main\ModuleManager::isModuleInstalled("bitrix24");
			$result['APPLICATION_MODE'] = [
				'SERVER' => Provider::APP_MODE_SERVER,
				'ZIP' => Provider::APP_MODE_ZIP
			];
		}
		$result['BLOCK_LIST'] = $blockList;

		$result['SCOPE_NEEDED'] = (!isset($presetData['OPTIONS']['SCOPE_NEEDED']) || $presetData['OPTIONS']['SCOPE_NEEDED'] !== 'D') ? 'Y' : 'N';

		if ($presetData['OPTIONS']['APPLICATION_NEEDED'] !== 'D' || $presetData['OPTIONS']['WIDGET_NEEDED'] !== 'D')
		{
			$result['LANG_LIST_AVAILABLE'] = [];
			$dbRes = LanguageTable::getList(
				[
					'order' => [
						'DEF' => 'DESC',
						'NAME' => 'ASC',
					],
					'filter' => [
						'=ACTIVE' => 'Y',
					],
					'select' => [
						'LID',
						'NAME',
					],
				]
			);
			while ($lang = $dbRes->fetch())
			{
				$result['LANG_LIST_AVAILABLE'][$lang['LID']] = $lang['NAME'];
			}
		}

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			$GLOBALS['APPLICATION']->SetTitle($result['TITLE']);
		}

		$context = Application::getInstance()->getContext();
		$lang = $context->getLanguage();
		$result['IS_HTTPS'] = $context->getRequest()->isHttps();
		if (!$result['IS_HTTPS'])
		{
			$result['ERROR_MESSAGE'][] = Loc::getMessage('REST_INTEGRATION_EDIT_ERROR_NO_HTTPS');
		}
		$result['IS_NEW_OPEN'] = $this->request->getPost('NEW_OPEN') === 'Y';

		$result['LANG_LIST'] = $this->getLanguageList();
		$result['URI_METHOD_INFO'] = Provider::URI_METHOD_INFO . '?lang=' . $lang . '&method=';
		$result['URI_EXAMPLE_DOWNLOAD'] = Provider::URI_EXAMPLE_DOWNLOAD . '?encode=' . SITE_CHARSET . '&type=';

		if (
				(
					!empty($result['PASSWORD_DATA_PASSWORD'])
					&& HoldEntity::is(HoldEntity::TYPE_WEBHOOK, $result['PASSWORD_DATA_PASSWORD'])
				)
				|| (
					!empty($result['APPLICATION_DATA_CLIENT_ID'])
					&& HoldEntity::is(HoldEntity::TYPE_APP, $result['APPLICATION_DATA_CLIENT_ID'])
				)
		)
		{
			$result['ERROR_MESSAGE'][] = Loc::getMessage('REST_INTEGRATION_EDIT_HOLD_DUE_TO_OVERLOAD');
		}

		$this->arResult = $result;

		return true;
	}

	protected function getLanguageList()
	{
		$result = [
			LANGUAGE_ID
		];
		if (Loader::includeModule('bitrix24'))
		{
			$result[] = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$dbSites = \CSite::getList(
				'sort',
				'asc',
				[
					'DEFAULT' => 'Y',
					'ACTIVE' => 'Y'
				]
			);
			if (($site = $dbSites->fetch()) && isset($site['LANGUAGE_ID']))
			{
				$result[] = $site['LANGUAGE_ID'];
			}
		}

		return array_unique($result);
	}

	protected function prepareQuery($dataQuery, $presetQuery)
	{
		$result = [];
		if (is_array($presetQuery))
		{
			if (!is_null($dataQuery))
			{
				foreach ($presetQuery as $query)
				{
					if (!isset($query['CODE']) || !is_string($query['CODE']))
					{
						continue;
					}
					$query['METHOD'] = (!empty($dataQuery[$query['CODE']]['METHOD']))
						? $dataQuery[$query['CODE']]['METHOD'] : $query['METHOD'];
					$query['ITEMS'] = (!empty($dataQuery[$query['CODE']]['ITEMS']))
						? $dataQuery[$query['CODE']]['ITEMS'] : [];

					$result[$query['CODE']] = $query;
				}
			}
			else
			{
				$result = array_column($presetQuery, null, 'CODE');
			}
		}

		return $result;
	}

	public function executeComponent()
	{
		try
		{
			$this->initParams();
			$this->checkRequiredParams();
			$this->processResultData();
			$this->includeComponentTemplate();
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
	}

	private function getRequestData()
	{
		$result = [];
		$request = Application::getInstance()->getContext()->getRequest();
		$items = $request->getPostList();
		foreach ($items as $code => $value)
		{
			if (is_array($value))
			{
				if ($code == 'QUERY')
				{
					foreach ($value as $section => $data)
					{
						$result[$code][$section] = [
							'METHOD' => $data['METHOD']
						];
						if (!empty($data['ITEMS']['title']))
						{
							foreach ($data['ITEMS']['title'] as $k => $title)
							{
								$result[$code][$section]['ITEMS'][] = [
									'title' => $title,
									'value' => $data['ITEMS']['value'][$k]
								];
							}
						}
					}
				}
				else
				{
					foreach ($value as $k => $val)
					{
						$result[$code][$k] = $val;
					}
				}
			}
			else
			{
				$result[$code] = $value;
			}
		}

		return $result;
	}

	public function saveDataAction()
	{
		$requestData = $this->getRequestData();

		if (
			!Access::isAvailable()
			|| !Access::isAvailableCount(Access::ENTITY_TYPE_INTEGRATION, $requestData['ID'])
		)
		{
			return [
				'helperCode' => Access::getHelperCode(Access::ACTION_INSTALL, Access::ENTITY_TYPE_INTEGRATION, $requestData['ID'])
			];
		}

		return Provider::saveIntegration($requestData, $this->arParams['ELEMENT_CODE'], $this->arParams['ID']);
	}

	public function getNewIntegrationUrlAction()
	{
		$result = [];
		$code = $this->request->getPost('code');

		if (
			!Access::isAvailable()
			|| !Access::isAvailableCount(Access::ENTITY_TYPE_INTEGRATION)
		)
		{
			$result['helperCode'] = Access::getHelperCode(Access::ACTION_INSTALL, Access::ENTITY_TYPE_INTEGRATION);
			return $result;
		}

		if (!empty($code))
		{
			$presetData = Element::get($code);
			if (!empty($presetData['OPTIONS']))
			{
				$saveData = [
					'ELEMENT_CODE' => $presetData['ELEMENT_CODE'],
					'TITLE' => $presetData['TITLE'] ?? null,
					'PASSWORD_ID' => $presetData['WEBHOOK']['ID'] ?? null,
					'SCOPE' => $presetData['OPTIONS']['SCOPE'] ?? null,
					'QUERY' => $this->prepareQuery(null, $presetData['OPTIONS']['QUERY'] ?? null),
					'OUTGOING_EVENTS' => $presetData['OPTIONS']['OUTGOING_EVENTS'] ?? null,
					'OUTGOING_NEEDED' => $presetData['OPTIONS']['OUTGOING_NEEDED'] ?? null,
					'WIDGET_LIST' => $presetData['OPTIONS']['WIDGET_LIST'] ?? null,
					'WIDGET_NEEDED' => $presetData['OPTIONS']['WIDGET_NEEDED'] ?? null,
				];

				$data = Provider::saveIntegration($saveData, $saveData['ELEMENT_CODE']);
				if ($data['ID'] > 0)
				{
					Analytic::logToFile(
						'integrationCreated',
						'integration' . $data['ID'],
						$saveData['ELEMENT_CODE'],
						'code'
					);
					Analytic::logToFile(
						'integrationCreated',
						'integration' . $data['ID'],
						$saveData['TITLE'],
						'title'
					);
					$result['url'] = DevOps::getInstance()->getIntegrationEditUrl($data['ID'], $saveData['ELEMENT_CODE']);
				}
				else
				{
					$result['error'] = is_array($data['errors']) ? implode(', ', $data['errors']) : $data['errors'];
				}
			}
		}

		return $result;
	}

	public function analyticAction()
	{
		return false;
	}

	public function deleteAction()
	{
		return Provider::deleteIntegration((int) $this->arParams['ID']);
	}

	public function configureActions()
	{
		return [
			'saveData' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'getNewIntegrationUrl' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'analytic' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => []
			],
			'delete' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\Csrf(),
				],
				'postfilters' => []
			]
		];
	}
}