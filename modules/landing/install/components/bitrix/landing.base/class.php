<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Crm\Integration\Landing\FormLanding;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Site;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Error;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Service\GeoIp;
use \Bitrix\Main\UI\PageNavigation;

class LandingBaseComponent extends \CBitrixComponent
{
	/**
	 * @deprecated
	 */
	const B24_SERVICE_DETECT_IP = 'https://ip.bitrix24.site/getipforzone/?bx24_zone=';
	const B24_DEFAULT_DNS_IP = '52.59.124.117';

	/**
	 * Manifest path template.
	 */
	const FILE_PATH_SITE_MANIFEST = '/bitrix/components/bitrix/landing.demo/data/site/#code#/.theme.php';

	/**
	 * Http status OK.
	 */
	const ERROR_STATUS_OK = '200 OK';

	/**
	 * Http status Forbidden.
	 */
	const ERROR_STATUS_FORBIDDEN = '403 Forbidden';

	/**
	 * Http status Not Found.
	 */
	const ERROR_STATUS_NOT_FOUND = '404 Not Found';

	/**
	 * Http status Service Unavailable.
	 */
	const ERROR_STATUS_UNAVAILABLE = '503 Service Unavailable';

	/**
	 * Navigation id.
	 */
	const NAVIGATION_ID = 'nav';

	/**
	 * Current user options.
	 * @var array|null
	 */
	protected $userOptions = null;

	/**
	 * Current errors.
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Current template.
	 * @var string
	 */
	protected $template = '';

	/**
	 * Last navigation result.
	 * @var \Bitrix\Main\UI\PageNavigation
	 */
	protected $lastNavigation = null;

	/**
	 * Current request
	 * @var \Bitrix\Main\HttpRequest
	 */
	protected $currentRequest = null;

	/**
	 * Init class' vars, check conditions.
	 * @return bool
	 */
	protected function init()
	{
		static $init = null;

		if ($init !== null)
		{
			return $init;
		}

		$init = true;

		Loc::loadMessages($this->getFile());

		if ($init && !Loader::includeModule('landing'))
		{
			$this->addError('LANDING_CMP_NOT_INSTALLED');
			$init = false;
		}
		$this->initRequest();

		return $init;
	}

	/**
	 * Updates site's and main page's titles.
	 * @param int $siteId Site id.
	 * @param array $update Data array.
	 * @return void
	 */
	protected function updateMainTitles(int $siteId, array $update): void
	{
		$res = Site::update($siteId, $update);
		if ($res->isSuccess())
		{
			$res = Site::getList([
				'select' => [
					'LANDING_ID_INDEX'
				],
				'filter' => [
					'ID' => $siteId
				]
			]);
			$row = $res->fetch();
			if (!empty($row['LANDING_ID_INDEX']))
			{
				Landing::update($row['LANDING_ID_INDEX'], [
					'TITLE' => $update['TITLE']
				]);
			}
		}
	}

	/**
	 * Returns current user GEO data.
	 * @return array
	 */
	public function getUserGeoData(): array
	{
		$countryName = GeoIp\Manager::getCountryName('', 'ru');
		if (!$countryName)
		{
			$countryName = GeoIp\Manager::getCountryName();
		}

		$cityName = GeoIp\Manager::getCityName('', 'ru');
		if (!$cityName)
		{
			$cityName = GeoIp\Manager::getCityName();
		}

		return [
			'country' => $countryName,
			'city' => $cityName
		];
	}

	/**
	 * Returns true if current request is ajax.
	 * @return bool
	 */
	public function isAjax(): bool
	{
		return Application::getInstance()->getContext()->getRequest()->isAjaxRequest();
	}

	/**
	 * Returns feedback parameters.
	 * @param string $id Feedback code.
	 * @param array $presets Additional params.
	 * @return array|null
	 */
	public function getFeedbackParameters(string $id, array $presets = []): ?array
	{
		$id = 'landing-feedback-' . $id;
		$tariffTtl = \Bitrix\Main\Config\Option::get('main', '~controller_group_till');
		$tariffDate = $tariffTtl ? (string)\Bitrix\Main\Type\Date::createFromTimestamp((int)$tariffTtl) : null;
		$partnerId = \Bitrix\Main\Config\Option::get('bitrix24', 'partner_id', 0);
		$b24 = Loader::includeModule('bitrix24');

		$data = [
			'landing-feedback-designblock' => [
				'ID' => 'landing-feedback-designblock',
				'VIEW_TARGET' => null,
				'FORMS' => [
					['zones' => ['br'], 'id' => '317','lang' => 'br', 'sec' => '3uon92'],
					['zones' => ['es'], 'id' => '315','lang' => 'la', 'sec' => 'd3jam4'],
					['zones' => ['de'], 'id' => '319','lang' => 'de', 'sec' => 'pr1z8q'],
					['zones' => ['ua'], 'id' => '321','lang' => 'ua', 'sec' => 'm6etjp'],
					['zones' => ['ru', 'by', 'kz'], 'id' => '311','lang' => 'ru', 'sec' => 'b8sbcz'],
					['zones' => ['en'], 'id' => '313','lang' => 'en', 'sec' => '9hdvqb']
				],
				'PRESETS' => [
					'from_domain' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $_SERVER['SERVER_NAME']
				]
			],
			'landing-feedback-demo' => [
				'ID' => 'landing-feedback-demo',
				'VIEW_TARGET' => null,
				'FORMS' => [
					['zones' => ['br'], 'id' => '279','lang' => 'br', 'sec' => 'wcqdvn'],
					['zones' => ['es'], 'id' => '277','lang' => 'la', 'sec' => 'eytrfo'],
					['zones' => ['de'], 'id' => '281','lang' => 'de', 'sec' => '167ch0'],
					['zones' => ['ua'], 'id' => '283','lang' => 'ua', 'sec' => 'ggoa61'],
					['zones' => ['ru', 'by', 'kz'], 'id' => '273','lang' => 'ru', 'sec' => 'z71z93'],
					['zones' => ['en'], 'id' => '275','lang' => 'en', 'sec' => '5cs6v2']
				],
				'PRESETS' => [
					'from_domain' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $_SERVER['SERVER_NAME']
				]
			],
			'landing-feedback-developer' => [
				'ID' => 'landing-feedback-developer',
				'VIEW_TARGET' => null,
				'FORMS' => [
					['zones' => ['en'], 'id' => '946','lang' => 'en', 'sec' => 'b3isk2'],
					['zones' => ['de'], 'id' => '951','lang' => 'de', 'sec' => '34dwna'],
					['zones' => ['es'], 'id' => '952','lang' => 'la', 'sec' => 'pkalm2'],
					['zones' => ['br'], 'id' => '953','lang' => 'br', 'sec' => 'p9ty5r'],
					['zones' => ['fr'], 'id' => '954','lang' => 'fr', 'sec' => 'udxiup'],
					['zones' => ['pl'], 'id' => '955','lang' => 'pl', 'sec' => 'isnnbz'],
					['zones' => ['it'], 'id' => '956','lang' => 'it', 'sec' => 'wnelcr'],
					['zones' => ['tr'], 'id' => '957','lang' => 'tr', 'sec' => '6utlw2'],
					['zones' => ['sc'], 'id' => '958','lang' => 'sc', 'sec' => '3bbec2'],
					['zones' => ['tc'], 'id' => '959','lang' => 'tc', 'sec' => '4fo52q'],
					['zones' => ['id'], 'id' => '960','lang' => 'id', 'sec' => 'jy3w82'],
					['zones' => ['ms'], 'id' => '961','lang' => 'ms', 'sec' => 'pbmmy8'],
					['zones' => ['th'], 'id' => '962','lang' => 'th', 'sec' => 'e587lw'],
					['zones' => ['ja'], 'id' => '963','lang' => 'ja', 'sec' => 'hh20c2'],
					['zones' => ['vn'], 'id' => '964','lang' => 'vn', 'sec' => '01bk91'],
					['zones' => ['hi'], 'id' => '965','lang' => 'hi', 'sec' => 'io8koq'],
					['zones' => ['ua'], 'id' => '969','lang' => 'ua', 'sec' => 'e5se9x'],
					['zones' => ['ru'], 'id' => '891','lang' => 'ru', 'sec' => 'h208n3'],
					['zones' => ['kz'], 'id' => '968','lang' => 'ru', 'sec' => '1312ws'],
					['zones' => ['by'], 'id' => '971','lang' => 'ru', 'sec' => '023nxk']
				],
				'PRESETS' => [
					'url' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $_SERVER['SERVER_NAME'],
					'tarif' => $b24 ? \CBitrix24::getLicenseType() : '',
					'city' => $b24 ? implode(' / ', $this->getUserGeoData()) : '',
					'partner_id' => $partnerId,
					'date_to' => $tariffDate ?: null
				],
				'PORTAL_URI' => 'https://cp.bitrix.ru'
			],
			'landing-feedback-knowledge' => [
				'ID' => 'landing-feedback-knowledge',
				'VIEW_TARGET' => null,
				'FORMS' => [
					['zones' => ['en'], 'id' => '1399','lang' => 'en', 'sec' => 'fkonbt'],
					['zones' => ['de'], 'id' => '1398','lang' => 'de', 'sec' => 'zvchw9'],
					['zones' => ['es'], 'id' => '1396','lang' => 'la', 'sec' => 'vb62o3'],
					['zones' => ['fr'], 'id' => '1401','lang' => 'fr', 'sec' => 'ungyc0'],
					['zones' => ['pl'], 'id' => '1392','lang' => 'pl', 'sec' => 'ib6p6u'],
					['zones' => ['pt'], 'id' => '1394','lang' => 'pt', 'sec' => 'sfzq02'],
					['zones' => ['ua'], 'id' => '1373','lang' => 'ua', 'sec' => 'p4xpwb'],
					['zones' => ['ru'], 'id' => '1368','lang' => 'ru', 'sec' => '0rb92n'],
					['zones' => ['kz'], 'id' => '1372','lang' => 'ru', 'sec' => 'o32l7z'],
					['zones' => ['by'], 'id' => '1378','lang' => 'ru', 'sec' => 'naegic']
				],
				'PRESETS' => [
					'url' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $_SERVER['SERVER_NAME'],
					'tarif' => $b24 ? \CBitrix24::getLicenseType() : '',
					'city' => $b24 ? implode(' / ', $this->getUserGeoData()) : '',
					'partner_id' => $partnerId,
					'date_to' => $tariffDate ?: null
				],
				'PORTAL_URI' => 'https://cp.bitrix.ru'
			]
		];

		$data = array_key_exists($id, $data) ? $data[$id] : null;
		if ($presets)
		{
			$data['PRESETS'] += $presets;
		}

		return $data;
	}

	/**
	 * Returns IP for DNS record for custom domains.
	 * @return string
	 */
	protected function getIpForDNS()
	{
		$dnsRecords = \Bitrix\Landing\Domain\Register::getDNSRecords();
		return $dnsRecords['INA'];
	}

	/**
	 * Get preview picture from cloud or not
	 * @return bool
	 */
	protected function previewFromCloud()
	{
		$disableCloud = Manager::isCloudDisable();
		return Manager::isB24() && !$disableCloud;
	}

	/**
	 * Http request initialization.
	 *
	 * @return void
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function initRequest()
	{
		if ($this->currentRequest !== null)
		{
			return;
		}
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$this->currentRequest = $context->getRequest();
		if ($this->currentRequest->isAjaxRequest())
		{
			$this->currentRequest->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());
		}
		unset($context);
	}

	/**
	 * Send only first http status.
	 * @param string $code Http status code.
	 * @return void
	 */
	protected function setHttpStatusOnce($code)
	{
		static $wasSend = false;

		if (!$wasSend)
		{
			$wasSend = true;
			\CHTTP::setStatus($code);
		}
	}

	/**
	 * Returns true if it is repo sever.
	 * @return bool
	 */
	protected function isRepo(): bool
	{
		return defined('LANDING_IS_REPO') && LANDING_IS_REPO === true;
	}

	/**
	 * Check var in arParams. If no exists, create with default val.
	 * @param string|int $var Variable.
	 * @param mixed $default Default value.
	 * @return void
	 */
	protected function checkParam($var, $default)
	{
		if (!isset($this->arParams[$var]))
		{
			$this->arParams[$var] = $default;
		}
		if (is_int($default))
		{
			$this->arParams[$var] = (int)$this->arParams[$var];
		}
		if (mb_substr($var, 0, 1) !== '~')
		{
			$this->checkParam('~' . $var, $default);
		}
	}

	/**
	 * Add one more error.
	 * @param string $code Code of error (lang code).
	 * @param string $message Optional message.
	 * @param bool $fatal Is fatal error.
	 * @return void
	 */
	protected function addError($code, $message = '', $fatal = false)
	{
		if ($message == '')
		{
			$message = Loc::getMessage($code);
		}
		$this->errors[$code] = new Error($message != '' ? $message : $code, $code);
		if ($fatal)
		{
			$this->arResult['FATAL'] = true;
		}
	}

	/**
	 * Collect errors from result.
	 * @param Entity\AddResult|Entity\UpdateResult|Entity\DeleteResult $result Result.
	 * @return void
	 */
	protected function addErrorFromResult($result)
	{
		if (
			(
			$result instanceof Entity\AddResult ||
			$result instanceof Entity\UpdateResult ||
			$result instanceof Entity\DeleteResult
			) && !$result->isSuccess()
		)
		{
			foreach ($result->getErrors() as $error)
			{
				$this->addError(
					$error->getCode(),
					$error->getMessage()
				);
			}
		}
	}

	/**
	 * Copy Error from one to this.
	 * @param array|\Bitrix\Main\Error $errors Error or array of errors.
	 * @return void
	 */
	protected function setErrors($errors)
	{
		if (!is_array($errors))
		{
			$errors = array($errors);
		}
		foreach ($errors as $err)
		{
			if ($err instanceof Error)
			{
				$this->errors[$err->getCode()] = $err;
			}
		}
	}

	/**
	 * Get current errors.
	 * @param bool $string Convert Errors to string.
	 * @return array
	 */
	public function getErrors($string = true)
	{
		if ($string)
		{
			$errors = array();
			foreach ($this->errors as $error)
			{
				$errors[$error->getCode()] = $error->getMessage();
			}
			return $errors;
		}
		else
		{
			return $this->errors;
		}
	}

	/**
	 * Get error from current by string code.
	 * @param string $code Error code.
	 * @return false|\Bitrix\Main\Error
	 */
	protected function getErrorByCode($code)
	{
		if (isset($this->errors[$code]))
		{
			return $this->errors[$code];
		}

		return false;
	}

	/**
	 * Get __FILE__.
	 * @return string
	 */
	protected function getFile()
	{
		return __FILE__;
	}

	/**
	 * Refresh current page.
	 * @param array $add New params.
	 * @param array $delete Params to remove.
	 * @return void
	 */
	public function refresh(array $add = [], array $delete = [])
	{
		$uriString = $this->currentRequest->getRequestUri();
		if ($add)
		{
			$uriSave = new \Bitrix\Main\Web\Uri($uriString);
			$uriSave->addParams($add);
			$uriString = $uriSave->getUri();
		}
		if ($delete)
		{
			$uriSave = new \Bitrix\Main\Web\Uri($uriString);
			$uriSave->deleteParams($delete);
			$uriString = $uriSave->getUri();
		}
		\LocalRedirect($uriString);
	}

	/**
	 * Redirects to the url in according with frame mode.
	 * @param string $url Url to redirect.
	 * @return void
	 */
	protected function frameRedirect(string $url): void
	{
		if ($this->request('IFRAME') == 'Y')
		{
			$uri = new \Bitrix\Main\Web\Uri($url);
			$uri->addParams([
				'IFRAME' => 'Y',
				'IFRAME_TYPE' => 'SIDE_SLIDER'
			]);
			$url = $uri->getUri();
		}
		\localRedirect($url);
	}

	/**
	 * Get some var from request.
	 * @param string $var Code of var.
	 * @return mixed
	 */
	public function request($var)
	{
		$result = $this->currentRequest[$var];
		return ($result !== null ? $result : '');
	}

	/**
	 * Return valid class from module.
	 * @param string $class Class name.
	 * @return string|false Full class name or false on failure.
	 */
	protected function getValidClass($class)
	{
		$class = '\\Bitrix\\Landing\\' . $class;
		if (
			class_exists($class) &&
			method_exists($class, 'getMap')
		)
		{
			return $class;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets last navigation object.
	 * @return \Bitrix\Main\UI\PageNavigation
	 */
	public function getLastNavigation(): PageNavigation
	{
		if (!$this->lastNavigation)
		{
			$this->lastNavigation = new PageNavigation('nav');
			$this->lastNavigation
				->allowAllRecords(false)
				->initFromUri();
		}
		return $this->lastNavigation;
	}

	/**
	 * Get items from some table.
	 * @param string $class Class code.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getItems($class, $params = array())
	{
		$items = array();
		$class = $this->getValidClass($class);

		if ($class)
		{
			// make navigation
			if (isset($params['navigation']))
			{
				$this->lastNavigation = new \Bitrix\Main\UI\PageNavigation(
					$this::NAVIGATION_ID
				);
				$this->lastNavigation->allowAllRecords(false)
									->setPageSize($params['navigation'])
									->initFromUri();
				$params['offset'] = $this->lastNavigation->getOffset();
				$params['limit'] = $this->lastNavigation->getLimit();
			}

			/** @var Entity\DataManager $class */
			$res = $class::getList(array(
				'select' => array_merge(array(
					'*'
				), isset($params['select'])
							? $params['select']
							: array()),
				'filter' => isset($params['filter'])
							? $params['filter']
							: array(),
				'order' => isset($params['order'])
							? $params['order']
							: array(
								'ID' => 'asc'
							),
				'limit' => isset($params['limit'])
							? $params['limit']
							: null,
				'offset' => isset($params['offset'])
							? $params['offset']
							: null,
				'runtime' => isset($params['runtime'])
							? $params['runtime']
							: array(),
				'count_total' => isset($params['navigation'])
							? true
							: null
			));
			while ($row = $res->fetch())
			{
				$items[$row['ID']] = $row;
			}

			// make navigation
			if (isset($params['navigation']))
			{
				$this->lastNavigation->setRecordCount(
					$res->getCount()
				);
			}
		}

		return $items;
	}

	/**
	 * Get current sites.
	 * @param array $params Params.
	 * @param bool $skipTypeCheck Skip check for type in filter.
	 * @return array
	 */
	protected function getSites($params = array(), bool $skipTypeCheck = false)
	{
		if (!isset($params['filter']))
		{
			$params['filter'] = array();
		}
		if (
			!$skipTypeCheck &&
			isset($this->arParams['TYPE']) &&
			!isset($params['filter']['=TYPE'])
		)
		{
			if (
				Manager::isExtendedSMN() &&
				$this->arParams['TYPE'] == 'STORE'
			)
			{
				$params['filter']['=TYPE'] = [
					$this->arParams['TYPE'],
					'SMN'
				];
			}
			else
			{
				$params['filter']['=TYPE'] = $this->arParams['TYPE'];
			}
		}
		return $this->getItems('Site', $params);
	}

	/**
	 * Get current domains.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getDomains($params = array())
	{
		\Bitrix\Landing\Domain::createDefault();
		return $this->getItems('Domain', $params);
	}

	/**
	 * Get current templates.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getTemplates($params = array())
	{
		if (!isset($params['filter']))
		{
			$params['filter'] = array();
		}
		if (!isset($params['order']))
		{
			$params['order'] = array();
		}
		$params['filter']['=ACTIVE'] = 'Y';
		$params['order'] = array(
			'SORT' => 'ASC'
		);
		return $this->getItems('Template', $params);
	}

	/**
	 * Get some landings.
	 * @param array $params Params.
	 * @return array
	 */
	protected function getLandings($params = array())
	{
		return $this->getItems('Landing', $params);
	}

	/**
	 * Init script for initialization API keys.
	 * @return void
	 */
	public function initAPIKeys()
	{
		$googleImagesKey = Manager::getOption(
			'google_images_key',
			null
		);
		$googleImagesKey = \CUtil::jsEscape(
			(string) $googleImagesKey
		);
		$allowKeyChange = true;

		Asset::getInstance()->addString("
			<script>
				(function() {
					\"use strict\";
					BX.namespace(\"BX.Landing.Client.Google\");
					BX.Landing.Client.Google.key = \"".$googleImagesKey."\";
					BX.Landing.Client.Google.allowKeyChange = ".json_encode($allowKeyChange).";
				})();
			</script>
		");
	}

	/**
	 * Get loc::getMessage by type of site.
	 * @param string $code Mess code.
	 * @param array $replace Array for replace, e.g. array('#NUM#' => 5).
	 * @return string
	 */
	public function getMessageType($code, $replace = null)
	{
		static $codes = [];

		if (!array_key_exists($code, $codes))
		{
			$mess = Loc::getMessage($code . '_' . $this->arParams['TYPE'], $replace);
			if (!$mess)
			{
				$mess = Loc::getMessage($code, $replace);
			}
			$codes[$code] = $mess;
		}

		return $codes[$code];
	}

	/**
	 * Get actual rest path.
	 * @deprecated since 20.2.100
	 * @return string
	 */
	public function getRestPath(): string
	{
		return Manager::getRestPath();
	}

	/**
	 * Set timestamp for url.
	 * @param string $url Url.
	 * @return string
	 */
	protected function getTimestampUrl($url)
	{
		// temporary disable this function
		if (Manager::isB24())
		{
			return rtrim($url, '/') . '/?ts=' . time();
		}
		else
		{
			return $url;
		}
	}

	/**
	 * Gets instance of URI without some external params.
	 * @return \Bitrix\Main\Web\Uri
	 */
	protected function getUriInstance()
	{
		static $curUri = null;

		if ($curUri === null)
		{
			$curUri = new \Bitrix\Main\Web\Uri(
				$this->currentRequest->getRequestUri()
			);
			$curUri->deleteParams([
				'sessid', 'action', 'param', 'additional', 'code', 'tpl',
				'stepper', 'start', 'IS_AJAX', $this::NAVIGATION_ID
			]);
		}

		return $curUri;
	}

	/**
	 * Get URI within/without some external params.
	 * @param array $add Additional params for adding.
	 * @param array $remove Additional params for deleting.
	 * @return string
	 */
	public function getUri(array $add = [], array $remove = [])
	{
		$curUri = clone $this->getUriInstance();

		if ($add)
		{
			$curUri->addParams($add);
		}
		if ($remove)
		{
			$curUri->deleteParams($remove);
		}

		return $curUri->getUri();
	}

	/**
	 * Adds new params / removes old params from $pageUri.
	 * @param string $pageUri Page uri.
	 * @param array $add Additional params for adding.
	 * @param array $remove Additional params for deleting.
	 * @return string
	 */
	public function getPageParam(string $pageUri, array $add = [], array $remove = []): string
	{
		$curUri = new \Bitrix\Main\Web\Uri($pageUri);

		if ($add)
		{
			$curUri->addParams($add);
		}
		if ($remove)
		{
			$curUri->deleteParams($remove);
		}

		return $curUri->getUri();
	}

	/**
	 * Returns relative path of current component template.
	 * @return string
	 */
	public function getComponentTemplate(): string
	{
		return $this->__template->__folder;
	}

	/**
	 * Get URI path.
	 * @return string
	 */
	protected function getUriPath()
	{
		return $this->getUriInstance()->getPath();
	}

	/**
	 * Gets current file real name.
	 * @return string
	 */
	protected function getRealFile()
	{
		static $scriptName = null;

		if ($scriptName === null)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			$scriptName = $server->get('REAL_FILE_PATH');
			if (!$scriptName)
			{
				$scriptName = $server->getScriptName();
			}
		}

		return $scriptName;
	}

	/**
	 * Gets tasks for access part.
	 * @return array
	 */
	protected function getAccessTasks()
	{
		return \Bitrix\Landing\Rights::getAccessTasks();
	}

	/**
	 * Gets settings link by error code.
	 * @param string $errorCode Error code.
	 * @return string
	 */
	public function getSettingLinkByError($errorCode)
	{
		$params = $this->arParams;
		if (preg_match('/^(PUBLIC_HTML_DISALLOWED)\[([S,L]{1})([\d]+)\]$/i', $errorCode, $matches))
		{
			if (
				$matches[2] == 'S' &&
				isset($params['SEF']['site_edit'])
			)
			{
				$editPage = $params['SEF']['site_edit'];
				$editPage = str_replace(
					'#site_edit#',
					$matches[3],
					$editPage
				);
			}
			else if (
				$matches[2] == 'L' &&
				isset($params['SEF']['landing_edit'])
			)
			{
				if (!isset($params['SITE_ID']))
				{
					$res = Landing::getList([
						'select' => [
							'SITE_ID'
						],
						'filter' => [
							'ID' => $matches[3]
						]
	 				]);
					if ($row = $res->fetch())
					{
						$params['SITE_ID'] = $row['SITE_ID'];
					}
					unset($row, $res);

				}
				$editPage = $params['SEF']['landing_edit'];
				$editPage = str_replace(
					['#site_show#', '#landing_edit#'],
					[$params['SITE_ID'], $matches[3]],
					$editPage
				);
			}
			if (isset($editPage))
			{
				$editPage .= '#'.mb_strtolower($matches[1]);
				unset($params, $matches);
				return '<br/><br/><a href="' . $editPage . '">' . Loc::getMessage('LANDING_GOTO_EDIT') . '</a>';
			}
		}
		unset($params);

		return '';
	}

	/**
	 * Detect, if error occurred on small tarrifs.
	 * @param string $errorCode Error code.
	 * @return bool
	 */
	public function isTariffError($errorCode)
	{
		static $tariffsCodes = [
			'PUBLIC_PAGE_REACHED',
			'PUBLIC_SITE_REACHED',
			'TOTAL_SITE_REACHED',
			'PUBLIC_HTML_DISALLOWED',
			'LANDING_PAYMENT_FAILED'
		];

		foreach ($tariffsCodes as $code)
		{
			if (mb_strpos($errorCode, $code) === 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Proxy rest methods, that we can redefine an answer.
	 * @throws ReflectionException
	 * @throws \Bitrix\Main\ArgumentException
	 * @return void
	 */
	protected function restProxy()
	{
		Manager::getApplication()->restartBuffer();
		header('Content-Type: application/json');
		$ajaxResult = \Bitrix\Landing\PublicAction::ajaxProcessing();

		// redefine errors
		if ($ajaxResult['type'] == 'error')
		{
			$ajaxResult['error_type'] = 'common';
			if (isset($ajaxResult['result']))
			{
				foreach ($ajaxResult['result'] as &$error)
				{
					if ($this->isTariffError($error['error']))
					{
						$ajaxResult['error_type'] = 'payment';
						$error['error_description'] .= $this->getSettingLinkByError(
							$error['error']
						);
					}
				}
				unset($error);
			}
		}

		echo \Bitrix\Main\Web\Json::encode($ajaxResult);
		\CMain::finalActions();
	}

	/**
	 * Initiates user options from storage.
	 * @return void
	 */
	protected function initUserOption(): void
	{
		if ($this->userOptions === null)
		{
			$this->userOptions = \CUserOptions::getOption('landing', 'editor_option');
			if (!is_array($this->userOptions))
			{
				$this->userOptions = [];
			}
		}
	}

	/**
	 * Save some data for current user.
	 * @param string $key Key of value.
	 * @param mixed $value Mixed value.
	 * @return void
	 */
	protected function setUserOption(string $key, $value): void
	{
		$this->initUserOption();
		$this->userOptions[$key] = $value;
		\CUserOptions::setOption('landing', 'editor_option', $this->userOptions);
	}

	/**
	 * Returns some user data by key.
	 * @param string $key Option key.
	 * @return mixed|null
	 */
	protected function getUserOption(string $key)
	{
		$this->initUserOption();
		if (array_key_exists($key, $this->userOptions))
		{
			return $this->userOptions[$key];
		}
		return null;
	}

	/**
	 * Returns site theme manifest.
	 * @param string $tplCode Site template code.
	 * @return array|null
	 */
	protected function getThemeManifest(string $tplCode): ?array
	{
		$path = $this::FILE_PATH_SITE_MANIFEST;
		$path = Manager::getDocRoot() . str_replace('#code#', $tplCode, $path);
		if (file_exists($path))
		{
			$manifest = include $path;
			if (is_array($manifest))
			{
				return $manifest;
			}
		}

		return null;
	}

	/**
	 * Detects site special type and returns it.
	 * @param int $siteId Site id.
	 * @deprecated since 21.700.0
	 * @return string|null
	 */
	protected function getSpecialTypeSite(int $siteId): ?string
	{
		$specialType = null;

		if (Loader::includeModule('crm'))
		{
			$storedSiteId = (int) \Bitrix\Main\Config\Option::get(
				'crm', FormLanding::OPT_CODE_LANDINGS_SITE_ID
			);
			if ($storedSiteId == $siteId)
			{
				$specialType = \Bitrix\Landing\Site\Type::PSEUDO_SCOPE_CODE_FORMS;
			}
		}

		return $specialType;
	}

	/**
	 * Detects site special type and returns it.
	 * @param Landing $landing Landing instance.
	 * @return string|null
	 */
	protected function getSpecialTypeSiteByLanding(Landing $landing): ?string
	{
		$specialType = null;
		$meta = $landing->getMeta();

		if ($meta['SITE_SPECIAL'] === 'Y')
		{
			if (preg_match('#^/' . Site\Type::PSEUDO_SCOPE_CODE_FORMS . '[\d]*/$#', $meta['SITE_CODE']))
			{
				$specialType = \Bitrix\Landing\Site\Type::PSEUDO_SCOPE_CODE_FORMS;
			}
		}

		return $specialType;
	}

	/**
	 * Get users from admin group.
	 * @return array
	 */
	protected function getAdmins(): array
	{
		$users = [];

		$userQuery = new \Bitrix\Main\Entity\Query(
			\Bitrix\Main\UserTable::getEntity()
		);
		// set select
		$userQuery->setSelect([
			'ID', 'LOGIN', 'NAME', 'LAST_NAME',
			'SECOND_NAME', 'PERSONAL_PHOTO'
		]);
		// set runtime for inner group ID=1 (admins)
		$userQuery->registerRuntimeField(
			null,
			new \Bitrix\Main\Entity\ReferenceField(
				'UG',
				\Bitrix\Main\UserGroupTable::getEntity(),
				[
					'=this.ID' => 'ref.USER_ID',
					'=ref.GROUP_ID' => new Bitrix\Main\DB\SqlExpression(1)
				],
				[
					'join_type' => 'INNER'
				]
			)
		);
		// set filter
		$date = new \Bitrix\Main\Type\DateTime;
		$userQuery->setFilter([
			'=ACTIVE' => 'Y',
			'!ID' => Manager::getUserId(),
			[
				'LOGIC' => 'OR',
				'<=UG.DATE_ACTIVE_FROM' => $date,
				'UG.DATE_ACTIVE_FROM' => false
			],
			[
				'LOGIC' => 'OR',
				'>=UG.DATE_ACTIVE_TO' => $date,
				'UG.DATE_ACTIVE_TO' => false
			]
		]);
		$res = $userQuery->exec();
		while ($row = $res->fetch())
		{
			if ($row['PERSONAL_PHOTO'])
			{
				$row['PERSONAL_PHOTO'] = \CFile::ResizeImageGet(
					$row['PERSONAL_PHOTO'],
					['width' => 38, 'height' => 38],
					BX_RESIZE_IMAGE_EXACT
				);
				if ($row['PERSONAL_PHOTO'])
				{
					$row['PERSONAL_PHOTO'] = $row['PERSONAL_PHOTO']['src'];
				}
			}
			$users[$row['ID']] = [
				'id' => $row['ID'],
				'name' => \CUser::formatName(
					\CSite::getNameFormat(false),
					$row, true, false
				),
				'img' => $row['PERSONAL_PHOTO']
			];
		}

		return $users;
	}

	/**
	 * Until updater to new folders is running, this method helps to force update specific site.
	 * @param int $siteId Site id.
	 * @return void
	 */
	protected function forceUpdateNewFolders(int $siteId): void
	{
		// hotfix #147619 + #147868
		/*if (Manager::getOption('landing_new') === 'Y')
		{
			return;
		}*/
		\Bitrix\Landing\Site\Type::setScope(
			$this->arParams['TYPE']
		);

		$result = [];
		$updater = new \Bitrix\Landing\Update\Landing\FolderNew();
		$updater->execute($result, $siteId);
	}

	/**
	 * Checks if form's block exists within landing.
	 * @param Landing $landing Landing instance.
	 * @return void
	 */
	protected function checkFormInLanding(Landing $landing): void
	{
		$formExists = false;

		foreach ($landing->getBlocks() as $block)
		{
			$manifest = $block->getManifest();
			if (($manifest['block']['subtype'] ?? null) === 'form')
			{
				$formExists = true;
				break;
			}
		}

		if (!$formExists && \Bitrix\Main\Loader::includeModule('crm'))
		{
			\Bitrix\Landing\Rights::setGlobalOff();
			$res = \Bitrix\Crm\WebForm\Internals\LandingTable::getList([
				'select' => [
					'FORM_ID'
				],
				'filter' => [
					'LANDING_ID' => $landing->getId()
				]
			]);
			if ($row = $res->fetch())
			{
				$blockId = $landing->addBlock('66.90.form_new_default', [
					'ACCESS' => 'W'
				]);
				if($blockId)
				{
					\Bitrix\Landing\Subtype\Form::setFormIdToBlock($blockId, $row['FORM_ID']);
					if ($landing->isActive())
					{
						$landing->publication();
					}
				}
			}
			\Bitrix\Landing\Rights::setGlobalOn();
		}
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if (!$init)
		{
			return;
		}

		$action = $this->request('action');
		$param = $this->request('param');
		$additional = $this->request('additional');
		$componentName = $this->request('componentName');
		$this->arResult['CUR_URI'] = $this->getUri();

		// some action
		if ($this->request('actionType') == 'rest')
		{
			if (!$componentName || $this->getName() == $componentName)
			{
				$this->restProxy();
			}
		}
		else if (
			$action &&
			check_bitrix_sessid() &&
			$this->request('actionType') == 'json' &&
			is_callable(array($this, 'action' . $action))
		)
		{
			Manager::getApplication()->restartBuffer();
			header('Content-Type: application/json');
			echo \Bitrix\Main\Web\Json::encode(
				$this->{'action' . $action}($param, $additional)
			);
			\CMain::finalActions();
		}
		else if ($action && is_callable(array($this, 'action' . $action)))
		{
			if (!check_bitrix_sessid())
			{
				$this->addError('LANDING_ERROR_SESS_EXPIRED');
			}
			else if ($this->{'action' . $action}($param, $additional))
			{
				\localRedirect($this->arResult['CUR_URI']);
			}
		}

		if (!isset($this->arResult['FATAL']))
		{
			$this->arResult['FATAL'] = !$init;
		}
		$this->arResult['ERRORS'] = $this->getErrors();

		$this->IncludeComponentTemplate($this->template);
	}
}
