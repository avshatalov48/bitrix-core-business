<?php
namespace Bitrix\Landing\Source;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Landing\Hook,
	Bitrix\Landing\Landing,
	Bitrix\Landing\Manager,
	Bitrix\Landing\Node;

class Selector
{
	/* default path to open the selector in the slider */
	const SOURCE_PATH = '/bitrix/tools/landing/source.php';

	/* default event id for get sources */
	const EVENT_BUILD_SOURCE_LIST = 'OnBuildSourceList';

	/* site mode for sources */
	const SITE_MODE_UNKNOWN = 'UNKNOWN';
	const SITE_MODE_SYSTEM = 'SYSTEM';
	const SITE_MODE_PAGE = 'PAGE';
	const SITE_MODE_STORE = 'STORE';
	const SITE_MODE_KNOWLEDGE = 'KNOWLEDGE';

	/* selector type */
	const SOURCE_TYPE_COMPONENT = 'C'; // component selector
	const SOURCE_TYPE_PRESET = 'P'; // without selector - fixed preset

	/* transfer type source filter after selecting items to display */
	const ACTION_TYPE_EVENT = 'event';	// use javascript event directly
	const ACTION_TYPE_SLIDER = 'slider'; // use standart slider method BX.SidePanel.Instance.postMessageTop

	const ACTION_NAME = 'save'; // default event name for Selector::ACTION_TYPE_SLIDER

	/* config for current landing */
	protected $config = [
		'SOURCE_EVENT_ID' => self::EVENT_BUILD_SOURCE_LIST, // Event ID for a list of sources
		'SOURCE_PATH' => self::SOURCE_PATH, // path for open source filter (component, include page, etc)
		'RESULT_ACTION_TYPE' => self::ACTION_TYPE_SLIDER, //
		'RESULT_ACTION_NAME' => self::ACTION_NAME
	];

	/** @var string */
	protected $siteMode = self::SITE_MODE_UNKNOWN;

	/** @var array */
	protected $restrictions = null;

	/** @var array */
	protected $sourceList = null;

	/**
	 * Selector constructor.
	 *
	 * @param array $config Initialization parameters.
	 * The array must contain all or part of the elements of the \Bitrix\Landing\Source\Selector::$config.
	 *
	 * @return void
	 */
	public function __construct(array $config = [])
	{
		$this->setConfig($config);
		$this->initSiteMode();
		$this->initRestrictions();
	}

	/**
	 * Getting a list of sources.
	 * Calls the event handlers and processes their results (checks the accuracy of the data returned by the handlers).
	 *
	 * @return void
	 */
	protected function initSourceList()
	{
		if ($this->sourceList !== null)
		{
			return;
		}
		$this->sourceList = [];

		$event = new Main\Event(
			'landing',
			$this->config['SOURCE_EVENT_ID'],
			[
				'SELECTOR' => $this,
				'RESTRICTIONS' => $this->restrictions // TODO: remove this key after stable socialnetwork update
			]
		);
		$event->send();
		$resultList = $event->getResults();
		if (!empty($resultList))
		{
			foreach ($resultList as $eventResult)
			{
				if ($eventResult->getType() != Main\EventResult::SUCCESS)
				{
					continue;
				}
				$module = (string)$eventResult->getModuleId();
				$list = $eventResult->getParameters();
				if (empty($list) || !is_array($list))
				{
					continue;
				}
				foreach ($list as $row)
				{
					$source = $this->prepareSourceParameters(
						$module,
						$row
					);
					if (empty($source))
					{
						continue;
					}
					$this->sourceList[$source['INDEX']] = $source;
				}
			}
			unset($source, $row, $list, $module);
			unset($eventResult, $resultList);

			if (!empty($this->sourceList))
			{
				Main\Type\Collection::sortByColumn(
					$this->sourceList,
					['TYPE' => SORT_ASC, 'TITLE' => SORT_ASC],
					'',
					null,
					true
				);
			}
		}
		unset($event);
	}

	/**
	 * Returns base uri for source filter form. Should be used to build page navigation when selecting items, filter operation.
	 *
	 * @param string $module
	 * @param string $sourceId
	 * @return Main\Web\Uri
	 */
	public function getSourceFilterBaseUri($module, $sourceId)
	{
		$uri = new Main\Web\Uri($this->config['SOURCE_PATH']);
		$uri->addParams($this->getBaseUrlParams(static::getSourceIndex($module, $sourceId)));
		return $uri;
	}

	/**
	 * Returns a full description of the sources: the URL to open the filter, a list of possible fields of elements, etc
	 *
	 * @return array
	 */
	public function getSourcesDescription()
	{
		$result = [];
		$this->initSourceList();
		if (empty($this->sourceList))
		{
			return $result;
		}
		$uri = new Main\Web\Uri($this->config['SOURCE_PATH']);
		foreach ($this->sourceList as $source)
		{
			$systemSettings = [
				'detailPage' => $source['SYSTEM_SETTINGS']['DETAIL_PAGE']
			];

			$uri->addParams($this->getBaseUrlParams($source['INDEX']));
			$row = [
				'id' => $source['INDEX'],
				'name' => $source['TITLE'],
				'sort' => $source['DATA_SETTINGS']['ORDER'],
				'references' => $source['DATA_SETTINGS']['FIELDS'],
				'settings' => $systemSettings
			];
			switch ($source['TYPE'])
			{
				case self::SOURCE_TYPE_COMPONENT:
					$row['url'] = [
						'filter' => $uri->getUri()
					];
					break;
				case self::SOURCE_TYPE_PRESET:
					$row['filter'] = $source['SETTINGS']['FILTER'];
					break;
			}
			$result[$source['INDEX']] = $row;
		}
		unset($row, $source);
		unset($uri);

		return $result;
	}

	/**
	 * Searches for source by index.
	 * @see \Bitrix\Landing\Source\Selector::getSourceIndex
	 *
	 * @param string $index
	 * @return array|null
	 */
	public function findSource($index)
	{
		if (!is_string($index) || $index === '')
		{
			return null;
		}
		$this->initSourceList();
		return isset($this->sourceList[$index]) ? $this->sourceList[$index] : null;
	}

	/**
	 * Displays a source filter for selecting which items are shown.
	 *
	 * @param string $index
	 * @return void
	 */
	public function showSourceFilter($index)
	{
		global $APPLICATION;

		$source = $this->findSource($index);
		if (empty($source))
		{
			return;
		}

		switch ($source['TYPE'])
		{
			case self::SOURCE_TYPE_COMPONENT:
				$APPLICATION->IncludeComponent(
					'bitrix:ui.sidepanel.wrapper',
					'.default',
					[
						'POPUP_COMPONENT_NAME' => $source['SETTINGS']['COMPONENT_NAME'],
						'POPUP_COMPONENT_TEMPLATE_NAME' => $source['SETTINGS']['COMPONENT_TEMPLATE_NAME'],
						'POPUP_COMPONENT_PARAMS' => $source['SETTINGS']['COMPONENT_PARAMS'],
					] + $source['SETTINGS']['WRAPPER']
				);
				break;
		}
	}

	/**
	 * Internal method for show a filter of source from request id.
	 *
	 * @return void
	 */
	public function showSourceFilterByRequest()
	{
		$request = Main\Context::getCurrent()->getRequest();
		$sourceIndex = (string)$request->get('SOURCE_ID');
		unset($request);
		if ($sourceIndex === '')
		{
			return;
		}
		$this->showSourceFilter($sourceIndex);
	}

	/**
	 * @param string $index
	 * @param array $parameters
	 * @param array $options
	 * @return DataLoader|null
	 */
	public function getDataLoader($index, array $parameters, array $options = [])
	{
		$source = $this->findSource($index);
		if (empty($source))
		{
			return null;
		}

		$parameters['internal_filter'] = $source['SOURCE_FILTER'];

		/** @var DataLoader $result */
		$result = new $source['DATA_LOADER'];
		$result->setConfig($parameters);
		$result->setOptions($options);

		return $result;
	}

	/**
	 * Returns a full description of the sources: the URL to open the filter, a list of possible fields of elements, etc
	 *
	 * @param array $config
	 * @return array
	 */
	public static function getSources(array $config = [])
	{
		$selector = new static($config);
		return $selector->getSourcesDescription();
	}

	/**
	 * Returns a short description of the sources: id and name
	 *
	 * @param array $config
	 * @return array
	 */
	public static function getSourceNames(array $config = [])
	{
		$result = [];
		$selector = new static($config);
		$selector->initSourceList();
		foreach ($selector->sourceList as $source)
		{
			$result[$source['INDEX']] = $source['TITLE'];
		}
		unset($source);

		return $result;
	}

	/**
	 * Returns current site mode for sources.
	 *
	 * @return string
	 */
	public function getSiteMode()
	{
		return $this->siteMode;
	}

	/**
	 * @param array $modeList
	 * @return bool
	 */
	public function checkSiteMode(array $modeList)
	{
		return (
			$this->siteMode === self::SITE_MODE_SYSTEM
			|| in_array($this->siteMode, $modeList, true)
		);
	}

	/**
	 * Returns a list of constraints that module sources must satisfy (for example, information block ID).
	 *
	 * @param string $module
	 * @return array|null
	 */
	public function getModuleRestrictions($module)
	{
		$module = (string)$module;
		if ($module === '')
		{
			return null;
		}
		if (isset($this->restrictions[$module]))
		{
			return $this->restrictions[$module];
		}
		if (isset($this->restrictions['all']))
		{
			return $this->restrictions['all'];
		}
		return null;
	}

	/* Public tools */

	/**
	 * @param string $module
	 * @param string $sourceId
	 * @return string
	 */
	public static function getSourceIndex($module, $sourceId)
	{
		return (string)$module.':'.(string)$sourceId;
	}

	/**
	 * @return array
	 */
	public function getResultAction()
	{
		return [
			'TYPE' => $this->config['RESULT_ACTION_TYPE'],
			'NAME' => $this->config['RESULT_ACTION_NAME']
		];
	}

	/**
	 * @return array
	 */
	public function getDefaultLinkActions()
	{
		return [
			[
				'type' => 'detail',
				'name' =>  Loc::getMessage('LANDING_SOURCE_ACTION_TITLE_DETAIL')
			],
			[
				'type' =>  'link',
				'name' =>  Loc::getMessage('LANDING_SOURCE_ACTION_TITLE_LINK')
			]
		];
	}

	/* Public tools end */

	/**
	 * Set/update selector config.
	 *
	 * @param array $config
	 * @return void
	 */
	protected function setConfig(array $config)
	{
		$config = $this->prepareConfig($config);
		if (!empty($config))
		{
			$this->config = array_merge($this->config, $config);
		}
	}

	/**
	 * Checks selector config.
	 *
	 * @param array $config
	 * @return array
	 */
	protected function prepareConfig(array $config)
	{
		$result = [];
		$config = array_filter($config, [__CLASS__, 'clearFilter']);
		$config = array_intersect_key($config, $this->config);
		if (!empty($config))
		{
			foreach ($config as $field => $value)
			{
				$checked = true;
				switch ($field)
				{
					case 'ID':
						if (is_string($value))
						{
							$value = preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', trim($value));
						}
						$checked = (is_string($value) && $value !== '');
						break;
					case 'SOURCE_EVENT_ID':
						if (is_string($value))
						{
							$value = preg_replace('/[^a-zA-Z0-9]/', '', trim($value));
						}
						$checked = (is_string($value) && $value !== '');
						break;
					case 'RESULT_ACTION_TYPE':
					case 'RESULT_ACTION_NAME':
						if (is_string($value))
						{
							$value = preg_replace('/[^a-zA-Z0-9_]/', '', trim($value));
						}
						$checked = (is_string($value) && $value !== '');
						break;
					case 'SOURCE_PATH':
						if (is_string($value))
						{
							$value = trim($value);
						}
						//TODO: add check relative real path
						$checked = (is_string($value) && $value !== '');
						break;
				}
				if ($checked)
				{
					$result[$field] = $value;
				}
			}
		}

		if (!isset($result['ID']))
		{
			$result['ID'] = preg_replace('/[^a-zA-Z0-9_:\\[\\]]/', '', get_called_class());
		}

		return $result;
	}

	/**
	 * Filter for array_filter - remove items with NULL value.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	protected function clearFilter($value)
	{
		return ($value !== null);
	}

	/**
	 * Load current landing mode
	 *
	 * @return void
	 */
	protected function initSiteMode()
	{
		$siteType = Landing::getSiteType();
		if ($siteType === '')
		{
			$this->siteMode = self::SITE_MODE_SYSTEM;
		}
		else
		{
			switch ($siteType)
			{
				case 'PAGE':
					$this->siteMode = self::SITE_MODE_PAGE;
					break;
				case 'STORE':
					$this->siteMode = self::SITE_MODE_STORE;
					break;
				case 'KNOWLEDGE':
					$this->siteMode = self::SITE_MODE_KNOWLEDGE;
					break;
			}
		}
		unset($siteType);
	}

	/**
	 * Load constraints that sources must satisfy (for example, information block ID).
	 *
	 * @return void
	 */
	protected function initRestrictions()
	{
		$this->restrictions = [];
		/* all */
		$siteId = (defined('SITE_ID') ? SITE_ID : null);
		if (!empty($siteId))
		{
			$this->restrictions['all'] = ['SITE_ID' => $siteId];
			$this->restrictions['iblock'] = $this->restrictions['all'];
			$this->restrictions['socialnetwork'] = $this->restrictions['all'];
		}
		/* iblock */
		$iblockId = (string)Manager::getOption('source_iblocks');
		if ($iblockId !== '')
		{
			$iblockId = explode(',', $iblockId);
		}
		else
		{
			$iblockId = [
				Hook\Page\Settings::getDataForSite()['IBLOCK_ID']
			];
		}
		$this->restrictions['iblock']['IBLOCK_ID'] = $iblockId;
	}

	/**
	 * Check source description.
	 *
	 * @param string $module
	 * @param array $parameters
	 * @return array|null
	 */
	protected function prepareSourceParameters($module, $parameters)
	{
		if (empty($parameters) || !is_array($parameters))
		{
			return null;
		}

		if (!isset($parameters['SOURCE_ID']))
		{
			return null;
		}
		$parameters['SOURCE_ID'] = (string)$parameters['SOURCE_ID'];
		if ($parameters['SOURCE_ID'] === '')
		{
			return null;
		}

		if (!isset($parameters['TITLE']))
		{
			return null;
		}
		$parameters['TITLE'] = trim((string)$parameters['TITLE']);
		if ($parameters['TITLE'] === '')
		{
			return null;
		}

		$index = self::getSourceIndex($module, $parameters['SOURCE_ID']);
		$prepared = [];
		if (!preg_match('/^[a-z][a-z.]+:[A-Za-z][A-Za-z0-9]*$/', $index, $prepared))
		{
			return null;
		}

		$result = [
			'INDEX' => $index,
			'MODULE' => $module,
			'SOURCE_ID' => $parameters['SOURCE_ID'],
			'TITLE' => $parameters['TITLE']
		];

		if (!isset($parameters['TYPE']))
		{
			$parameters['TYPE'] = self::SOURCE_TYPE_COMPONENT;
		}
		$parameters['TYPE'] = (string)$parameters['TYPE'];
		if (
			$parameters['TYPE'] !== self::SOURCE_TYPE_COMPONENT
			&& $parameters['TYPE'] !== self::SOURCE_TYPE_PRESET
		)
		{
			return null;
		}

		$result['TYPE'] = $parameters['TYPE'];

		if (empty($parameters['SETTINGS']) || !is_array($parameters['SETTINGS']))
		{
			return null;
		}

		$result['SYSTEM_SETTINGS'] = $this->checkSystemSettings($parameters['SETTINGS']);

		$result['SETTINGS'] = [];

		$settings = null;
		switch ($parameters['TYPE'])
		{
			case self::SOURCE_TYPE_COMPONENT:
				$settings = $this->checkComponentSettings($parameters['SETTINGS']);
				break;
			case self::SOURCE_TYPE_PRESET:
				$settings = $this->checkPresetSettings($parameters['SETTINGS']);
				break;
		}
		if ($settings === null)
		{
			return null;
		}
		$result['SETTINGS'] = $settings;
		unset($settings);

		if (empty($parameters['DATA_SETTINGS']) || !is_array($parameters['DATA_SETTINGS']))
		{
			return null;
		}
		$settings = $this->checkDataSettings($parameters['DATA_SETTINGS']);
		if ($settings === null)
		{
			return null;
		}
		$result['DATA_SETTINGS'] = $settings;
		unset($settings);

		if (empty($parameters['DATA_LOADER']))
		{
			return null;
		}
		if (!is_string($parameters['DATA_LOADER'])
			|| !is_subclass_of($parameters['DATA_LOADER'], '\Bitrix\Landing\Source\DataLoader'))
		{
			return null;
		}
		$result['DATA_LOADER'] = $parameters['DATA_LOADER'];

		$result['SOURCE_FILTER'] = [];
		if (!empty($parameters['SOURCE_FILTER']) && is_array($parameters['SOURCE_FILTER']))
		{
			$result['SOURCE_FILTER'] = $parameters['SOURCE_FILTER'];
		}

		return $result;
	}

	/**
	 * Check common settings.
	 *
	 * @param array $settings
	 * @return array
	 */
	protected function checkSystemSettings(array $settings)
	{
		$result = [
			'DETAIL_PAGE' => true
		];

		if (isset($settings['DETAIL_PAGE']) && is_bool($settings['DETAIL_PAGE']))
		{
			$result['DETAIL_PAGE'] = $settings['DETAIL_PAGE'];
		}

		return $result;
	}

	/**
	 * Check settings for component filter.
	 *
	 * @param array $settings
	 * @return array|null
	 */
	protected function checkComponentSettings(array $settings)
	{
		if (!isset($settings['COMPONENT_NAME']))
		{
			return null;
		}
		$settings['COMPONENT_NAME'] = (string)$settings['COMPONENT_NAME'];
		if ($settings['COMPONENT_NAME'] === '')
		{
			return null;
		}

		if (!isset($settings['COMPONENT_TEMPLATE_NAME']))
		{
			return null;
		}
		$settings['COMPONENT_TEMPLATE_NAME'] = (string)$settings['COMPONENT_TEMPLATE_NAME'];

		if (!isset($settings['COMPONENT_PARAMS']) || !is_array($settings['COMPONENT_PARAMS']))
		{
			return null;
		}

		$settings['WRAPPER'] = $this->checkWrapperSettings($settings);

		return array_intersect_key(
			$settings,
			[
				'COMPONENT_NAME' => true,
				'COMPONENT_TEMPLATE_NAME' => true,
				'COMPONENT_PARAMS' => true,
				'WRAPPER' => true
			]
		);
	}

	/**
	 * Check fixed block filter.
	 *
	 * @param array $settings
	 * @return array|null
	 */
	protected function checkPresetSettings(array $settings)
	{
		if (empty($settings['FILTER']) || !is_array($settings['FILTER']))
		{
			return null;
		}

		$preset = array_filter($settings['FILTER'], ['\Bitrix\Landing\Source\BlockFilter', 'checkPreparedRow']);
		if (empty($preset))
		{
			return null;
		}

		return [
			'FILTER' => $preset
		];
	}

	/**
	 * Check additional settings for component bitrix:ui.sidepanel.wrapper
	 *
	 * @param array $settings
	 * @return array
	 */
	protected function checkWrapperSettings(array $settings)
	{
		$result = [
			'USE_PADDING' => false,
			'PLAIN_VIEW' => false,
			'USE_UI_TOOLBAR' => 'N'
		];

		if (isset($settings['WRAPPER']) && is_array($settings['WRAPPER']))
		{
			$rawData = $settings['WRAPPER'];
			if (isset($rawData['USE_PADDING']) && is_bool($rawData['USE_PADDING']))
			{
				$result['USE_PADDING'] = $rawData['USE_PADDING'];
			}
			if (isset($rawData['PLAIN_VIEW']) && is_bool($rawData['PLAIN_VIEW']))
			{
				$result['PLAIN_VIEW'] = $rawData['PLAIN_VIEW'];
			}
			if (
				isset($rawData['USE_UI_TOOLBAR'])
				&& ($rawData['USE_UI_TOOLBAR'] === 'Y' || $rawData['USE_UI_TOOLBAR'] === 'N')
			)
			{
				$result['USE_UI_TOOLBAR'] = $rawData['USE_UI_TOOLBAR'];
			}
			unset($rawData);
		}
		else
		{
			// TODO: remove this branch after stable update socialnetwork
			if (
				isset($settings['USE_UI_TOOLBAR'])
				&& ($settings['USE_UI_TOOLBAR'] === 'Y' || $settings['USE_UI_TOOLBAR'] === 'N')
			)
			{
				$result['USE_UI_TOOLBAR'] = $settings['USE_UI_TOOLBAR'];
			}
		}

		return $result;
	}

	/**
	 * Checks the validity of the description of the source fields available.
	 *
	 * @param array $settings
	 * @return array|null
	 */
	protected function checkDataSettings(array $settings)
	{
		if (empty($settings))
		{
			return null;
		}

		$result = [];

		if (!is_array($settings['ORDER']))
		{
			return null;
		}
		$settings['ORDER'] = array_filter($settings['ORDER'], [__CLASS__, 'isNotEmptyField']);
		if (empty($settings['ORDER']))
		{
			return null;
		}
		$list = [];
		foreach ($settings['ORDER'] as $row)
		{
			$row = $this->prepareOrderField($row);
			if (empty($row))
			{
				continue;
			}
			$list[] = $row;
		}
		unset($row);
		if (empty($list))
		{
			return null;
		}
		$result['ORDER'] = $list;
		unset($list);

		if (!is_array($settings['FIELDS']))
		{
			return null;
		}
		$settings['FIELDS'] = array_filter($settings['FIELDS'], [__CLASS__, 'isNotEmptyField']);
		if (empty($settings['FIELDS']))
		{
			return null;
		}
		$list = [];
		foreach ($settings['FIELDS'] as $row)
		{
			$row = Node::prepareFieldDefinition($row);
			if (empty($row))
			{
				continue;
			}

			$list[] = $row;
		}
		if (empty($list))
		{
			return null;
		}
		$result['FIELDS'] = $list;
		unset($list);

		return $result;
	}

	/**
	 * Returns required parameters for base url.
	 *
	 * @param string $index
	 * @return array
	 */
	protected function getBaseUrlParams($index)
	{
		return array_merge(
			[
				'SOURCE_ID' => $index
			],
			$this->getUrlSystemParams()
		);
	}

	/**
	 * Returns system parameters (language, site, template) for urls.
	 *
	 * @return array
	 */
	protected function getUrlSystemParams()
	{
		$result = [
			'lang' => LANGUAGE_ID
		];
		if (defined('SITE_ID'))
		{
			$result['site'] = SITE_ID;
		}
		if (defined('SITE_TEMPLATE_ID'))
		{
			$result['template'] = SITE_TEMPLATE_ID;
		}
		$request = Main\Context::getCurrent()->getRequest();
		if ($request->isAdminSection())
		{
			$result['admin_section'] = 'Y';
		}
		unset($request);
		return $result;
	}

	/**
	 * @param mixed $item
	 * @return bool
	 */
	protected static function isNotEmptyField($item)
	{
		return (!empty($item) && is_array($item));
	}

	/**
	 * @param array $field
	 * @return array|null
	 */
	protected function prepareOrderField(array $field)
	{
		$field = array_change_key_case($field, CASE_LOWER);

		$field['id'] = $this->prepareStringValue($field, 'id');
		$field['name'] = $this->prepareStringValue($field, 'name');
		if (empty($field['id']) || empty($field['name']))
		{
			return null;
		}

		return [
			'id' => $field['id'],
			'name' => $field['name']
		];
	}

	/**
	 * @param array $row
	 * @param string $name
	 * @return string|null
	 */
	protected function prepareStringValue(array $row, $name)
	{
		if (empty($row[$name]) || !is_string($row[$name]))
		{
			return null;
		}
		$row[$name] = trim($row[$name]);
		if ($row[$name] === '')
		{
			return null;
		}
		return $row[$name];
	}
}