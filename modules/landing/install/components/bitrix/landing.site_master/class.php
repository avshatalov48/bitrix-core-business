<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Url\AdminPage;
use Bitrix\Crm\Product;
use Bitrix\Landing\Site;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Domain;
use Bitrix\Landing\Connector;
use Bitrix\Sale;
use Bitrix\Crm;
use Bitrix\Catalog;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingSiteMasterComponent extends LandingBaseFormComponent implements Controllerable
{
	public const OPTION_SHOP_INSTALL_COUNT = '~shop_install_count_';

	/**
	 * Configures filter for ajax request.
	 * @return array
	 */
	public function configureActions(): array
	{
		return [
			'sendMessage' => [
				'prefilters' => [
					new ActionFilter\Authentication
				],
			],
		];
	}

	/**
	 * Returns site info by site id (or last created).
	 * @param int|null $siteId Site id.
	 * @param string $siteType Site type.
	 * @return array|null
	 */
	protected function getSite(?int $siteId, string $siteType): ?array
	{
		$filter = [
			'=TYPE' => $siteType
		];
		if ($siteId)
		{
			$filter['ID'] = $siteId;
		}
		$row = $this->getSites([
			'select' => [
				'*',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
				'DOMAIN_PROTOCOL' => 'DOMAIN.PROTOCOL'
			],
			'filter' => $filter,
			'order' => [
				'ID' => 'desc'
			],
			'limit' => 1
		]);
		if ($row)
		{
			$row = array_shift($row);
			$row['SITE_URL'] = $row['DOMAIN_PROTOCOL'] . '://' . $row['DOMAIN_NAME'];
		}

		return $row;
	}

	/**
	 * Processing step to save company data and new domain if changed.
	 *
	 * @param int $siteId Site id.
	 * @param array $siteInfo Company and domain data.
	 * @return void
	 */
	protected function processingStep(int $siteId, array $siteInfo): void
	{
		if (($this->request('SAVE_SITE') == 'Y') && check_bitrix_sessid())
		{
			$update = [];

			Connector\Crm::setContacts($siteId, [
				'COMPANY' => $this->request('COMPANY'),
				'PHONE' => $this->request('PHONE')
			]);

			if ($company = $this->request('COMPANY'))
			{
				$update['TITLE'] = $company;
			}

			if ($subDomain = $this->request('SUBDOMAIN'))
			{
				$newDomain = $subDomain . $this->getPostfix();
				if ($siteInfo['DOMAIN_NAME'] != $newDomain)
				{
					$update['DOMAIN_ID'] = $newDomain;
				}
			}

			if ($update)
			{
				$this->updateMainTitles($siteId, $update);
			}
		}
	}

	/**
	 * Creates new shop by code and redirects to it's master.
	 * @param string $shopCode Shop code.
	 * @return bool
	 */
	public function actionCreate(string $shopCode): bool
	{
		$result = \Bitrix\Landing\Site::addByTemplate($shopCode, 'STORE', ['section' => 'fashion']);
		if ($result->getId())
		{
			$optionName = self::getShopInstallCountOptionName($shopCode);
			$installCount = Manager::getOption($optionName, 0);
			Manager::setOption($optionName, ++$installCount);

			\Bitrix\Landing\PublicAction\Site::publication($result->getId());

			if (Main\Loader::includeModule('sale'))
			{
				$this->prepareOrderProperties($result->getId());
			}

			$contacts = Connector\Crm::getContacts($result->getId());
			$this->updateMainTitles($result->getId(), ['TITLE' => $contacts['COMPANY']]);
			$this->setSiteMasterUrl($result->getId());

			if (isset($this->arParams['PAGE_URL_SITE_MASTER']) && is_string($this->arParams['PAGE_URL_SITE_MASTER']))
			{
				$this->frameRedirect($this->arParams['PAGE_URL_SITE_MASTER']);
			}
		}

		return false;
	}

	public static function getShopInstallCountOptionName(string $shopCode): string
	{
		return self::OPTION_SHOP_INSTALL_COUNT . strtolower(str_replace('-', '_', $shopCode));
	}

	/**
	 * Returns postfix for bitrix24 domain.
	 * @return string
	 */
	protected function getPostfix(): string
	{
		return Domain::getBitrix24Postfix(
			'store'
		);
	}

	/**
	 * Returns site's template's manifest.
	 * @param string $tplCode Site template code.
	 * @return array
	 */
	protected function getTemplateManifest(string $tplCode): array
	{
		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = ['TYPE' => 'STORE'];
		return $demoCmp->getDemoSite($tplCode)[$tplCode] ?? [];
	}

	/**
	 * Returns site pages for master's step.
	 * @param int $siteId Site id.
	 * @param array $codes Codes array.
	 * @return array
	 */
	public function getPages(int $siteId, array $codes): array
	{
		$pages = [];

		$landing = Landing::createInstance(0);
		$res = Landing::getList([
			'select' => [
				'ID', 'SITE_ID', 'TITLE', 'DESCRIPTION'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'=TPL_CODE' => $codes
			]
		]);
		while ($row = $res->fetch())
		{
			$row['URL'] = '';
			$pages[$row['ID']] = $row;
		}

		foreach ($landing->getPublicUrl(array_keys($pages)) as $id => $url)
		{
			$pages[$id]['URL'] = $url;
		}

		return $pages;
	}

	/**
	 * Returns product's url for site's product's section.
	 * @param int $siteId Site id.
	 * @return string
	 */
	public function getProductUrl(int $siteId): string
	{
		if (
			Main\Loader::includeModule('iblock')
			&& Main\Loader::includeModule('iblock')
			&& Main\Loader::includeModule('crm')
		)
		{
			$settings = Site::getAdditionalFields($siteId);
			$fieldSectionId = $settings['SETTINGS_SECTION_ID'] ?? null;
			if ($fieldSectionId)
			{
				$useBitrix24 = \Bitrix\Main\Loader::includeModule('bitrix24');
				if ($useBitrix24)
				{
					Main\Config\Option::set('catalog', 'product_card_slider_enabled', 'Y', '');
					$urlBuilder = AdminPage\BuilderManager::getInstance()->getBuilder(
						Product\Url\ProductBuilder::TYPE_ID
					);
				}
				else
				{
					$urlBuilder = AdminPage\BuilderManager::getInstance()->getBuilder(
						Catalog\Url\ShopBuilder::TYPE_ID
					);
				}
				if ($urlBuilder)
				{
					$urlBuilder->setIblockId(\CCrmCatalog::getDefaultID());
					if ($useBitrix24)
					{
						$urlBuilder->setSeparateIblockList();
						CBitrixComponent::includeComponentClass('bitrix:crm.catalog.controller');
						$params = \CrmCatalogControllerComponent::getViewModeParams();
						// to define('PUBLIC_MODE', 1) in main/include/prolog_admin_before.php
						$params['public'] = 'Y';
						// to disable redirect by $arResult['IS_SIDE_PANEL'] in crm.admin.page.include
						$params['disableRedirect'] = 'Y';
					}
					else
					{
						$params = [];
						$urlBuilder->setSliderMode(false);
					}
					$params['by'] = 'ID';
					$params['order'] = 'ASC';
					$urlBuilder->setUrlParams($params);

					return $urlBuilder->getElementListUrl($fieldSectionId->getValue() ?: 0);
				}
			}
		}

		return '';
	}

	/**
	 * Returns true if site has views for any page.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public function siteHasViewsAction(int $siteId): bool
	{
		if (\Bitrix\Main\Loader::includeModule('landing'))
		{
			return Landing::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'SITE_ID' => $siteId,
					'>VIEWS' => 0
				],
				'limit' => 1
			])->fetch() ? true : false;
		}

		return false;
	}

	/**
	 * Returns true if site has created orders.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public function siteHasOrdersAction(int $siteId): bool
	{
		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			return \Bitrix\Crm\Order\TradeBindingCollection::getList([
				'select' => [
					'ORDER_ID'
				],
				'filter' => [
					'=TRADING_PLATFORM.CODE' => 'landing_' . $siteId
				],
				'limit' => 1
			])->fetch() ? true : false;
		}

		return false;
	}

	/**
	 * Replaces site id marker in master page url.
	 * @param int $siteId Site id.
	 * @return void
	 */
	protected function setSiteMasterUrl(int $siteId): void
	{
		if (isset($this->arParams['PAGE_URL_SITE_MASTER']))
		{
			$this->arParams['PAGE_URL_SITE_MASTER'] = str_replace(
				'#site_edit#',
				$siteId,
				$this->arParams['PAGE_URL_SITE_MASTER']
			);
		}
	}

	private function prepareOrderProperties(int $siteId): void
	{
		$createNewOrderPropertiesResult = $this->createOrderProperties();
		if ($createNewOrderPropertiesResult->isSuccess())
		{
			$orderPropertyIdList = $createNewOrderPropertiesResult->getData();
			$this->bindOrderProperties($orderPropertyIdList);
			$this->updateOrderPropertiesRelations($siteId, $orderPropertyIdList);
		}
	}

	private function createOrderProperties(): Main\Result
	{
		$result = new Main\Result();

		$propertyTypeList = [
			'IS_PAYER' => [
				'NAME' => Loc::getMessage('LANDING_ORDER_PROPERTY_NAME'),
				'TYPE' => 'STRING',
				'REQUIRED' => 'Y',
				'DEFAULT_VALUE' => '',
				'SORT' => 100,
				'USER_PROPS' => 'Y',
				'IS_LOCATION' => 'N',
				'DESCRIPTION' => '',
				'IS_PHONE' => 'N',
				'IS_EMAIL' => 'N',
				'IS_PROFILE_NAME' => 'Y',
				'IS_PAYER' => 'Y',
				'IS_LOCATION4TAX' => 'N',
				'CODE' => 'NAME',
				'IS_FILTERED' => 'Y',
			],
			'IS_PHONE' => [
				'NAME' => Loc::getMessage('LANDING_ORDER_PROPERTY_PHONE'),
				'TYPE' => 'STRING',
				'REQUIRED' => 'Y',
				'DEFAULT_VALUE' => '',
				'SORT' => 200,
				'USER_PROPS' => 'Y',
				'IS_LOCATION' => 'N',
				'DESCRIPTION' => '',
				'IS_PHONE' => 'Y',
				'IS_EMAIL' => 'N',
				'IS_PROFILE_NAME' => 'N',
				'IS_PAYER' => 'N',
				'IS_LOCATION4TAX' => 'N',
				'CODE' => 'PHONE',
				'IS_FILTERED' => 'N',
			],
		];

		if (Main\Config\Option::get('main', 'new_user_email_required', 'Y') !== 'N')
		{
			$propertyTypeList['IS_EMAIL'] = [
				'NAME' => 'E-Mail',
				'TYPE' => 'STRING',
				'REQUIRED' => 'Y',
				'DEFAULT_VALUE' => '',
				'SORT' => 300,
				'USER_PROPS' => 'Y',
				'IS_LOCATION' => 'N',
				'DESCRIPTION' => '',
				'IS_PHONE' => 'N',
				'IS_EMAIL' => 'Y',
				'IS_PROFILE_NAME' => 'N',
				'IS_PAYER' => 'N',
				'IS_LOCATION4TAX' => 'N',
				'CODE' => 'EMAIL',
				'IS_FILTERED' => 'Y',
			];
		}

		$personTypeId = $this->getPersonTypeId();
		if (!$personTypeId)
		{
			$createPersonTypeResult = $this->createPersonType();
			if ($createPersonTypeResult->isSuccess())
			{
				$createPersonTypeData = $createPersonTypeResult->getData();
				$personTypeId = $createPersonTypeData['id'];
			}
			else
			{
				$result->addErrors($createPersonTypeResult->getErrors());
				return $result;
			}
		}

		$orderPropertiesGroupId = $this->getOrderPropertiesGroupId($personTypeId);
		if (!$orderPropertiesGroupId)
		{
			$createOrderPropertiesGroupResult = $this->createOrderPropertiesGroup($personTypeId);
			if ($createOrderPropertiesGroupResult->isSuccess())
			{
				$createOrderPropertiesGroupData = $createOrderPropertiesGroupResult->getData();
				$orderPropertiesGroupId = $createOrderPropertiesGroupData['id'];
			}
			else
			{
				$result->addErrors($createOrderPropertiesGroupResult->getErrors());
				return $result;
			}
		}

		$orderPropertyIdList = [];
		foreach ($propertyTypeList as $type => $propertyValue)
		{
			$orderPropId = $this->getExistingOrderPropertyId(
				$propertyValue['CODE'],
				$type,
				$personTypeId,
				$orderPropertiesGroupId
			);
			if ($orderPropId)
			{
				$orderPropertyIdList[] = $orderPropId;
			}
			else
			{
				$propertyValue += [
					'PERSON_TYPE_ID' => $personTypeId,
					'PROPS_GROUP_ID' => $orderPropertiesGroupId,
					'ENTITY_REGISTRY_TYPE' => Sale\Registry::ENTITY_ORDER,
					'ENTITY_TYPE' => Sale\Registry::ENTITY_ORDER,
				];
				$createOrderPropertyResult = $this->createOrderProperty($propertyValue);
				if ($createOrderPropertyResult->isSuccess())
				{
					$orderPropertyIdList[] = $createOrderPropertyResult->getId();
				}
				else
				{
					$result->addErrors($createOrderPropertyResult->getErrors());
				}
			}
		}

		$result->setData($orderPropertyIdList);

		return $result;
	}

	private function getOrderPropertiesGroupId(int $personTypeId): ?int
	{
		$orderPropertiesGroup = Sale\Internals\OrderPropsGroupTable::getList([
			'select'=> ['ID'],
			'filter' => [
				'PERSON_TYPE_ID' => $personTypeId,
				'NAME' => Loc::getMessage('LANDING_ORDER_PROPERTIES_GROUP_NAME'),
			],
			'limit' => 1,
		])->fetch();

		return $orderPropertiesGroup ? (int)$orderPropertiesGroup['ID'] : null;
	}

	private function createOrderPropertiesGroup(int $personTypeId): Main\Result
	{
		$result = new Main\Result();

		$addOrderPropsGroup = Sale\Internals\OrderPropsGroupTable::add([
			'PERSON_TYPE_ID' => $personTypeId,
			'NAME' => Loc::getMessage('LANDING_ORDER_PROPERTIES_GROUP_NAME'),
			'SORT' => 100,
		]);
		if ($addOrderPropsGroup->isSuccess())
		{
			$result->setData(['id' => $addOrderPropsGroup->getId()]);
		}
		else
		{
			$result->addErrors($addOrderPropsGroup->getErrors());
		}

		return $result;
	}

	private function getPersonTypeId(): ?int
	{
		$personType = Sale\Internals\BusinessValuePersonDomainTable::getList([
			'select' => ['PERSON_TYPE_ID'],
			'filter' => [
				'=DOMAIN' => Sale\BusinessValue::INDIVIDUAL_DOMAIN,
				'=PERSON_TYPE_REFERENCE.ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER,
				'=PERSON_TYPE_REFERENCE.LID' => $this->getSiteId(),
				'=PERSON_TYPE_REFERENCE.ACTIVE' => 'Y',
			],
			'order' => [
				'PERSON_TYPE_REFERENCE.SORT' => 'ASC'
			],
			'limit' => 1,
		])->fetch();

		return $personType ? (int)$personType['PERSON_TYPE_ID'] : null;
	}

	private function createPersonType(): Main\Result
	{
		$result = new Main\Result();

		$addPersonTypeResult = Sale\Internals\PersonTypeTable::add([
			'LID' => $this->getSiteId(),
			'NAME' => Loc::getMessage('LANDING_CRM_PERSON_TYPE_CONTACT'),
			'SORT' => 100,
			'ENTITY_REGISTRY_TYPE' => Sale\Registry::ENTITY_ORDER,
			'ACTIVE' => 'Y',
		]);

		if ($addPersonTypeResult->isSuccess())
		{
			$personTypeId = $addPersonTypeResult->getId();

			Sale\Internals\PersonTypeSiteTable::add([
				'PERSON_TYPE_ID' => $personTypeId,
				'SITE_ID' => $this->getSiteId(),
			]);

			Sale\Internals\BusinessValuePersonDomainTable::add([
				'PERSON_TYPE_ID' => $personTypeId,
				'DOMAIN' => Sale\BusinessValue::INDIVIDUAL_DOMAIN,
			]);

			$result->setData(['id' => $personTypeId]);
		}
		else
		{
			$result->addErrors($addPersonTypeResult->getErrors());
		}

		return $result;
	}

	private function getExistingOrderPropertyId(string $propertyCode, string $type, int $personTypeId, int $propertiesGroupId): ?int
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Property $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();
		$propertyData = $propertyClassName::getList([
			'select' => ['ID'],
			'filter' => [
				'=PERSON_TYPE.LID' => $this->getSiteId(),
				'=CODE' => $propertyCode,
				'=PERSON_TYPE_ID' => $personTypeId,
				'=PROPS_GROUP_ID' => $propertiesGroupId,
				'='.$type => 'Y',
			],
			'order' => ['ID' => 'DESC'],
			'limit' => 1,
		])->fetch();

		if ($propertyData)
		{
			$propertyRelationData = Sale\Internals\OrderPropsRelationTable::getList([
				'select' => ['ENTITY_ID'],
				'filter' => [
					'=PROPERTY_ID' => $propertyData['ID'],
					'=ENTITY_TYPE' => 'L',
				],
				'limit' => 1,
			])->fetch();
			if ($propertyRelationData)
			{
				$platform = Sale\TradingPlatform\Manager::getObjectById($propertyRelationData['ENTITY_ID']);
				if (
					$platform instanceof Sale\TradingPlatform\Landing\Landing
					&& $platform->isOfType(Sale\TradingPlatform\Landing\Landing::LANDING_STORE_STORE_V3)
				)
				{
					return (int)$propertyData['ID'];
				}
			}
		}

		return null;
	}

	private function createOrderProperty(array $propertyValue): Main\ORM\Data\AddResult
	{
		return Sale\Internals\OrderPropsTable::add($propertyValue);
	}

	private function bindOrderProperties(array $orderPropertyIdList): void
	{
		if (empty($orderPropertyIdList))
		{
			return;
		}

		$matches = [];

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Property $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();
		$orderPropsIterator = $propertyClassName::getList([
			'select' => ['ID', 'IS_PAYER', 'IS_EMAIL', 'IS_PHONE'],
			'filter' => [
				'=PERSON_TYPE.LID' => $this->getSiteId(),
				'@ID' => $orderPropertyIdList,
			],
		]);
		while ($orderPropsData = $orderPropsIterator->Fetch())
		{
			if ($orderPropsData['IS_PAYER'] === 'Y')
			{
				$matches[$orderPropsData['ID']] = [
					'CRM_ENTITY_TYPE' => \CCrmOwnerType::Contact,
					'CRM_FIELD_TYPE' => Crm\Order\Matcher\ContactMatcher::GENERAL_FIELD_TYPE,
					'CRM_FIELD_CODE' => 'NAME',
					'SETTINGS' => [],
				];
			}
			elseif ($orderPropsData['IS_EMAIL'] === 'Y')
			{
				$matches[$orderPropsData['ID']] = [
					'CRM_ENTITY_TYPE' => \CCrmOwnerType::Contact,
					'CRM_FIELD_TYPE' => Crm\Order\Matcher\ContactMatcher::MULTI_FIELD_TYPE,
					'CRM_FIELD_CODE' => 'EMAIL_WORK',
					'SETTINGS' => [],
				];
			}
			elseif ($orderPropsData['IS_PHONE'] === 'Y')
			{
				$matches[$orderPropsData['ID']] = [
					'CRM_ENTITY_TYPE' => \CCrmOwnerType::Contact,
					'CRM_FIELD_TYPE' => Crm\Order\Matcher\ContactMatcher::MULTI_FIELD_TYPE,
					'CRM_FIELD_CODE' => 'PHONE_WORK',
					'SETTINGS' => [],
				];
			}
		}

		if ($matches)
		{
			$matchedProps = [];
			$existingPropertyIdList = array_keys($matches);

			$orderPropsMatchIterator = Crm\Order\Matcher\Internals\OrderPropsMatchTable::getList([
				'select' => ['SALE_PROP_ID'],
				'filter' => ['@SALE_PROP_ID' => $existingPropertyIdList],
			]);
			while ($orderPropsMatchData = $orderPropsMatchIterator->Fetch())
			{
				$matchedProps[$orderPropsMatchData['SALE_PROP_ID']] = true;
			}

			foreach ($existingPropertyIdList as $existingPropertyId)
			{
				if (empty($matchedProps[$existingPropertyId]) && !empty($matches[$existingPropertyId]))
				{
					$propMatch = $matches[$existingPropertyId];
					Crm\Order\Matcher\Internals\OrderPropsMatchTable::add([
						'SALE_PROP_ID' => $existingPropertyId,
						'CRM_ENTITY_TYPE' => $propMatch['CRM_ENTITY_TYPE'],
						'CRM_FIELD_TYPE' => $propMatch['CRM_FIELD_TYPE'],
						'CRM_FIELD_CODE' => $propMatch['CRM_FIELD_CODE'],
						'SETTINGS' => $propMatch['SETTINGS'],
					]);
				}
			}
		}
	}

	private function updateOrderPropertiesRelations(int $siteId, array $orderPropertyIdList): Main\Result
	{
		$result = new Main\Result();

		$landing = Sale\TradingPlatform\Landing\Landing::getInstanceByCode(
			Sale\TradingPlatform\Landing\Landing::getCodeBySiteId($siteId)
		);

		foreach ($orderPropertyIdList as $orderPropertyId)
		{
			$orderPropsRelationAddResult = Sale\Internals\OrderPropsRelationTable::add([
				'PROPERTY_ID' => $orderPropertyId,
				'ENTITY_ID' => $landing->getId(),
				'ENTITY_TYPE' => 'L',
			]);
			if (!$orderPropsRelationAddResult->isSuccess())
			{
				$result->addErrors($orderPropsRelationAddResult->getErrors());
			}
		}

		return $result;
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
			$this->checkParam('TYPE', '');
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('GET_DATA', 'Y');
			$this->checkParam('PAGE_URL_SITE_MASTER', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');

			if ($this->arParams['GET_DATA'] != 'Y')
			{
				parent::executeComponent();
				return;
			}

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			$this->arResult['SITE'] = $this->getSite(
				$this->arParams['SITE_ID'],
				$this->arParams['TYPE']
			);

			if ($this->arResult['SITE'])
			{
				if ($this->request('redirect_to') === 'products')
				{
					\localRedirect($this->getProductUrl($this->arResult['SITE']['ID']));
				}
				$this->setSiteMasterUrl(
					$this->arResult['SITE']['ID']
				);
				$this->processingStep(
					$this->arResult['SITE']['ID'],
					$this->arResult['SITE']
				);
				$this->arResult['CRM_CONTACTS'] = Connector\Crm::getContacts(
					$this->arResult['SITE']['ID']
				);
				$this->arResult['STEP'] = (int)$this->request('STEP');
				$this->arResult['STEP'] = max(1, $this->arResult['STEP']);
				$this->arResult['SITE']['PUBLIC_URL'] = Site::getPublicUrl(
					$this->arResult['SITE']['ID']
				);
				$this->arResult['SITE']['SUBDOMAIN_NAME'] = Domain::getBitrix24Subdomain(
					$this->arResult['SITE']['DOMAIN_NAME']
				);
				$this->arResult['SITE']['POSTFIX'] = $this->getPostfix();
			}
			else
			{
				$this->addError(
					'SITE_NOT_FOUND',
					Loc::getMessage('LANDING_CMP_ERROR_SITE_NOT_FOUND'),
					true
				);
			}
		}

		parent::executeComponent();
	}
}
