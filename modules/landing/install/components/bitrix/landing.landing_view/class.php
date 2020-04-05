<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\Hook;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingViewComponent extends LandingBaseComponent
{
	/**
	 * Total this type sites count.
	 * @var int
	 */
	protected $sitesCount;

	/**
	 * Total pages count in current site.
	 * @var int
	 */
	protected $pagesCount;

	/**
	 * Publication landing.
	 * @param int $id Landing id.
	 * @return boolean
	 */
	protected function actionPublication($id)
	{
		$landing = Landing::createInstance($id);
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();
		$agreementExist = isset($this->arParams['AGREEMENT']) &&
						  !empty($this->arParams['AGREEMENT']);

		// agreement already display
		if (
			$agreementExist &&
			$request->get('agreement') == 'Y'
		)
		{
			return false;
		}

		if ($landing->exist())
		{
			// display agreement
			$uriSave = new \Bitrix\Main\Web\Uri(
				$request->getRequestUri()
			);
			$uriSave->deleteParams(array(
				'agreement'
			));
			if (
				isset($this->arParams['AGREEMENT']) &&
				!empty($this->arParams['AGREEMENT'])
			)
			{
				$uriSave->addParams(array(
					'agreement' => 'Y'
				));
				\localRedirect($uriSave->getUri(), true);
			}
			if ($landing->publication())
			{
				// current landing is not area
				$areas = $landing->getAreas();
				if (!in_array($id, $areas))
				{
					foreach ($areas as $aId)
					{
						$landingArea = Landing::createInstance($aId);
						if ($landingArea->exist())
						{
							$landingArea->publication();
						}
					}
				}
				\localRedirect($landing->getPublicUrl(false, true, true), true);
			}
		}

		$this->setErrors(
			$landing->getError()->getErrors()
		);

		return false;
	}

	/**
	 * Gets sites count.
	 * @return int
	 */
	public function getSitesCount()
	{
		if (is_int($this->sitesCount))
		{
			return $this->sitesCount;
		}

		$res = Site::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			),
			'filter' => array(
				'=TYPE' => $this->arParams['TYPE']
			)
		));
		if ($row = $res->fetch())
		{
			$this->sitesCount = $row['CNT'];
		}
		else
		{
			$this->sitesCount = 0;
		}

		return $this->sitesCount;
	}

	/**
	 * Gets pages count of current site.
	 * @return int
	 */
	public function getPagesCount()
	{
		if (is_int($this->pagesCount))
		{
			return $this->pagesCount;
		}

		$res = Landing::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			),
			'filter' => array(
				'=SITE_ID' => $this->arParams['SITE_ID']
			)
		));
		if ($row = $res->fetch())
		{
			$this->pagesCount = (int) $row['CNT'];
		}
		else
		{
			$this->pagesCount = 0;
		}
		
		return $this->pagesCount;
	}

	/**
	 * Handler on view landing.
	 * @return void
	 */
	protected function onLandingView()
	{
		$type = strtolower($this->arParams['TYPE']);
		$params = $this->arParams;
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onLandingView',
			function(\Bitrix\Main\Event $event) use ($type, $params)
			{
				$result = new \Bitrix\Main\Entity\EventResult;
				$options = $event->getParameter('options');
				$options['params'] = $params['PARAMS'];
				$options['params']['type'] = $params['TYPE'];
				$options['sites_count'] = $this->getSitesCount();
				$options['pages_count'] = $this->getPagesCount();
				$options['syspages'] = array();
				$options['promoblocks'] = array();
				$options['placements'] = array(
					'blocks' => array()
				);
				$options['hooks'] = array(
					'YACOUNTER' => array(),
					'GACOUNTER' => array()
				);
				// some hooks
				$hookSite = Hook::getForSite($params['SITE_ID']);
				$hookLanding = Hook::getForLanding($params['LANDING_ID']);
				foreach ($options['hooks'] as $hook => &$hookFields)
				{
					$fields = array();
					if (
						isset($hookLanding[$hook]) &&
						$hookLanding[$hook]->enabled()
					)
					{
						$fields = $hookLanding[$hook]->getFields();
					}
					elseif (
						isset($hookSite[$hook]) &&
						$hookSite[$hook]->enabled()
					)
					{
						$fields = $hookSite[$hook]->getFields();
					}
					foreach ($fields as $fieldCode => $field)
					{
						$hookFields[$fieldCode] = $field->getValue();
					}
				}
				unset($hookFields);
				// get system pages
				foreach (Syspage::get($this->arParams['SITE_ID']) as $code => $page)
				{
					$options['syspages'][$code] = array(
						'landing_id' => $page['LANDING_ID'],
						'name' => $page['TITLE']
					);
				}
				// unset blocks not for this type
				$b24 = \Bitrix\Landing\Manager::isB24();
				foreach ($options['blocks'] as &$section)
				{
					foreach ($section['items'] as $code => $block)
					{
						if (
							!empty($block['type']) &&
							!in_array($type, (array)$block['type']) &&
							($b24 || $block['type'] == 'null')
						)
						{
							unset($section['items'][$code]);
						}
					}
				}
				unset($section);
				// redefine options
				if (\Bitrix\Main\Loader::includeModule('rest'))
				{
					// add promo blocks
					$blocks = \Bitrix\Rest\Marketplace\Client::getByTag(
						array('sites', 'crm'),
						1
					);
					if (isset($blocks['ITEMS']) && !empty($blocks['ITEMS']))
					{
						shuffle($blocks['ITEMS']);
						$blocks = array_shift(array_chunk($blocks['ITEMS'], 5));
						foreach ($blocks as $block)
						{
							$options['promoblocks'][$block['CODE']] = array(
								'name' => $block['NAME'],
								'description' => '',
								'preview' => $block['ICON'],
								'price' => isset($block['PRICE'][1])
											? $block['PRICE'][1]
											: ''
							);
						}
					}
					// add placements
					$res = \Bitrix\Rest\PlacementTable::getList(array(
						'select' => array(
							'ID', 'APP_ID', 'PLACEMENT', 'TITLE',
							'APP_NAME' => 'REST_APP.APP_NAME'
						),
						'filter' => array(
							'PLACEMENT' => 'LANDING_BLOCK_%'
						),
						'order' => array(
							'ID' => 'DESC'
						)
					));
					while ($row = $res->fetch())
					{
						$row['PLACEMENT'] = strtolower(substr($row['PLACEMENT'], 14));
						if (!isset($options['placements']['blocks'][$row['PLACEMENT']]))
						{
							$options['placements']['blocks'][$row['PLACEMENT']] = array();
						}
						$options['placements']['blocks'][$row['PLACEMENT']][$row['ID']] = array(
							'id' => $row['ID'],
							'placement' => $row['PLACEMENT'],
							'app_id' => $row['APP_ID'],
							'title' => trim($row['TITLE'])
										? $row['TITLE']
										: $row['APP_NAME']
						);
					}
				}
				if (\Bitrix\Main\Loader::includeModule('bitrix24'))
				{
					$options['license'] = \CBitrix24::getLicenseType();
				}
				$result->modifyFields(array(
					'options' => $options
				));
				return $result;
			}
		);
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('LANDING_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('PAGE_URL_URL_SITES', '');
			$this->checkParam('PAGE_URL_LANDINGS', '');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('PAGE_URL_SITE_EDIT', '');
			$this->checkParam('PARAMS', array());

			Landing::setEditMode();
			$landing = Landing::createInstance($this->arParams['LANDING_ID']);

			$this->arResult['LANDING'] = $landing;

			if ($landing->exist())
			{
				$this->arResult['SITE'] = $this->getSites(array(
					'filter' => array(
						'ID' => $this->arParams['SITE_ID']
					)
				));
				if ($this->arResult['SITE'])
				{
					$this->arResult['SITE'] = array_pop($this->arResult['SITE']);
				}
				// disable optimisation
				if (\Bitrix\Landing\Manager::isB24())
				{
					$asset = \Bitrix\Main\Page\Asset::getInstance();
					if (
						method_exists($asset, 'disableOptimizeCss') &&
						method_exists($asset, 'disableOptimizeJs')
					)
					{
						$asset->disableOptimizeCss();
						$asset->disableOptimizeJs();
					}
				}
				// get settings placements
				$this->arResult['PLACEMENTS_SETTINGS'] = array();
				if (\Bitrix\Main\Loader::includeModule('rest'))
				{
					$res = \Bitrix\Rest\PlacementTable::getList(array(
						'select' => array(
							'ID', 'APP_ID', 'PLACEMENT', 'TITLE',
							'APP_NAME' => 'REST_APP.APP_NAME'
						),
						'filter' => array(
							'=PLACEMENT' => 'LANDING_SETTINGS'
						),
						'order' => array(
							'ID' => 'DESC'
						)
					));
					while ($row = $res->fetch())
					{
						$this->arResult['PLACEMENTS_SETTINGS'][] = $row;
					}
				}
				$this->onLandingView();
			}

			// some errors?
			$this->setErrors(
				$landing->getError()->getErrors()
			);
		}

		parent::executeComponent();
	}
}