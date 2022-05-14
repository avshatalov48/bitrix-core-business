<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\SystemException;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Role;
use \Bitrix\Landing\Restriction;

Loc::loadMessages(__FILE__);

/**
 * Class SiteTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Site_Query query()
 * @method static EO_Site_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Site_Result getById($id)
 * @method static EO_Site_Result getList(array $parameters = array())
 * @method static EO_Site_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_Site createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_Site_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_Site wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_Site_Collection wakeUpCollection($rows)
 */
class SiteTable extends Entity\DataManager
{
	/**
	 * For save callbacks.
	 */
	const ACTION_TYPE_ADD = 'add';

	/**
	 * For save callbacks.
	 */
	const ACTION_TYPE_UPDATE = 'update';

	/**
	 * Stored fields for save separate.
	 * @var array
	 */
	protected static $additionalFields = array();

	/**
	 * Disable callback.
	 * @var boolean
	 */
	protected static $disableCallback = false;

	/**
	 * In current iteration we change date only.
	 * @var bool
	 */
	protected static $touchMode = false;

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_site';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		$types = \Bitrix\Landing\Site::getTypes();
		$typesKeys = array_keys($types);

		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_CODE'),
				'required' => true
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_ACTIVE'),
				'default_value' => 'Y'
			)),
			'DELETED' => new Entity\StringField('DELETED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_DELETED'),
				'default_value' => 'N'
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_TITLE'),
				'required' => true
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'DESCRIPTION' => new Entity\StringField('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESCRIPTION')
			)),
			'TYPE' => new Entity\EnumField('TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TYPE'),
				'values' => $typesKeys,
				'default_value' => array_shift($typesKeys)
			)),
			'TPL_ID' => new Entity\IntegerField('TPL_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_ID'),
				'default_value' => 0
			)),
			'TPL_CODE' => new Entity\StringField('TPL_CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_CODE')
			)),
			'DOMAIN_ID' => new Entity\IntegerField('DOMAIN_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DOMAIN_ID'),
				//'required' => true
			)),
			'DOMAIN' => new Entity\ReferenceField(
				'DOMAIN',
				'Bitrix\Landing\Internals\DomainTable',
				array('=this.DOMAIN_ID' => 'ref.ID')
			),
			'SMN_SITE_ID' => new Entity\StringField('SMN_SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SMN_SITE_ID')
			)),
			'LANDING_ID_INDEX' => new Entity\IntegerField('LANDING_ID_INDEX', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID_INDEX')
			)),
			'LANDING_ID_404' => new Entity\IntegerField('LANDING_ID_404', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID_404')
			)),
			'LANDING_ID_503' => new Entity\IntegerField('LANDING_ID_503', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ID_503')
			)),
			'LANG' => new Entity\IntegerField('LANG', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANG')
			)),
			'SPECIAL' => new Entity\StringField('SPECIAL', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SPECIAL'),
				'default_value' => 'N'
			)),
			'VERSION' => new Entity\IntegerField('VERSION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_VERSION')
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'CREATED_BY' => new Entity\ReferenceField(
				'CREATED_BY',
				'Bitrix\Main\UserTable',
				array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY' => new Entity\ReferenceField(
				'MODIFIED_BY',
				'Bitrix\Main\UserTable',
				array('=this.MODIFIED_BY_ID' => 'ref.ID')
			),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}

	/**
	 * Return site controller class, or pseudo.
	 * @return string
	 */
	protected static function getSiteController()
	{
		return Manager::getExternalSiteController();
	}

	/**
	 * Check CODE unique in site group.
	 * @param string $code Site code.
	 * @param int $currentId Current site id.
	 * @param int $domainId Domain id.
	 * @return boolean
	 */
	protected static function checkUniqueInDomain($code, $currentId, $domainId)
	{
		$res = self::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'!ID' => $currentId,
				'DOMAIN_ID' => $domainId,
				'=CODE' => $code,
				'=DELETED' => ['Y', 'N'],
				'CHECK_PERMISSIONS' => 'N'
			)
		));
		return $res->fetch() ? false : true;
	}

	/**
	 * Is bitrix24.site subdomain?
	 * @param string $domainName Domain name.
	 * @return boolean
	 */
	protected static function isB24Domain($domainName)
	{
		return Domain::getBitrix24Subdomain($domainName) !== null;
	}

	/**
	 * Customize controller message.
	 * @param SystemException $ex Exception from controller.
	 * @return Entity\EntityError
	 */
	protected static function customizeControllerError(SystemException $ex)
	{
		$code = str_replace(' ', '', $ex->getMessage());
		$code = mb_strtoupper($code);
		$message = Loc::getMessage('LANDING_CONTROLLER_ERROR_' . $code);
		$message = $message ? $message : $ex->getMessage();

		return new Entity\EntityError(
			$message,
			'CONTROLLER_ERROR_' . $code
		);
	}

	/**
	 * Check 'bitrix'-named domain.
	 * @param string $domainName Domain name.
	 * @return boolean
	 */
	public static function checkBitrixUse($domainName)
	{
		$isB24Domain = self::isB24Domain($domainName);
		$disableMask = '/bitrix[^\.]*\.bitrix[^\.]+\.[a-z]+$/';
		if (
			Manager::isB24() &&
			(
				$isB24Domain && preg_match_all($disableMask, $domainName)
				||
				!$isB24Domain && mb_strpos($domainName, 'bitrix') !== false
			)
		)
		{
			return true;
		}
		return false;
	}

	/**
	 * Set additional access filter.
	 * @param mixed $params ORM params.
	 * @return mixed
	 */
	public static function setAccessFilter($params)
	{
		if (
			isset($params['filter']['CHECK_PERMISSIONS']) &&
			$params['filter']['CHECK_PERMISSIONS'] == 'N'
		)
		{
			return $params;
		}

		// build filter
		$allowedSites = Rights::getAllowedSites();
		$buildFilter = Rights::getAccessFilter(
			$allowedSites ? ['ID' => $allowedSites] : []
		);
		if (empty($buildFilter))
		{
			return $params;
		}

		// create runtime/filter keys if no exists
		if (
			!isset($params['filter']) ||
			!is_array($params['filter'])
		)
		{
			$params['filter'] = [];
		}
		if (
			!isset($params['runtime']) ||
			!is_array($params['runtime'])
		)
		{
			$params['runtime'] = [];
		}
		if (
			!isset($params['group']) ||
			!is_array($params['group'])
		)
		{
			$params['group'] = [];
		}

		//$tasks = Rights::getAccessTasksReferences();
		//$readCode = Rights::ACCESS_TYPES['denied'];
		$extendedRights = Rights::isExtendedMode();
		static $expectedRoles = null;
		if ($expectedRoles === null)
		{
			$expectedRoles = Role::getExpectedRoleIds();
		}

		// create runtime fields
		$runtimeParams = [];
		$runtimeParams[] = [
			'LOGIC' => 'OR',
			'=this.ID' => 'ref.ENTITY_ID',
			'=ref.ENTITY_ID' => [0]
		];
		if ($extendedRights)
		{
			$runtimeParams['=ref.ROLE_ID'] = [0];
		}
		else
		{
			$runtimeParams['=ref.ENTITY_TYPE'] = ['?', Rights::ENTITY_TYPE_SITE];
			$runtimeParams['@ref.ROLE_ID'] = [implode(',', $expectedRoles)];
		}
		$params['runtime'][] = new Entity\ReferenceField(
			'RIGHTS',
			'Bitrix\Landing\Internals\RightsTable',
			$runtimeParams,
			['join_type' => 'INNER']
		);

		$params['group'][] = 'ID';

		// build filter
		$params['filter'][] = $buildFilter;

		return $params;
	}

	/**
	 * Returns value from row by key code. If key don't exist, get from DB.
	 * @param int $id Site id.
	 * @param array $fields Site row fields.
	 * @param string $code Key code of row.
	 * @return mixed
	 */
	private static function getValueByCode($id, array $fields, $code)
	{
		static $data = [];

		if (array_key_exists($id, $data))
		{
			return isset($data[$id][$code]) ? $data[$id][$code] : null;
		}

		if (array_key_exists($code, $fields))
		{
			return $fields[$code];
		}

		$res = self::getList([
			'select' => [
				'*',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
				'DOMAIN_PROVIDER' => 'DOMAIN.PROVIDER'
			],
			'filter' => [
				'ID' => $id,
				'CHECK_PERMISSIONS' => 'N',
				'=DELETED' => ['Y', 'N']
			]
		]);
		if ($row = $res->fetch())
		{
			$data[$id] = $row;
		}

		return isset($data[$id][$code]) ? $data[$id][$code] : null;
	}

	/**
	 * Prepare change to save.
	 * @param Entity\Event $event Event instance.
	 * @param string $actionType Action type: add / update.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event, $actionType)
	{
		$result = new Entity\EventResult();
		$fields = $event->getParameter('fields');
		$primary = $event->getParameter('primary');
		$unsetFields = array();
		$modifyFields = array();
		$siteController = self::getSiteController();
		$deleteMode = false;

		self::$touchMode = isset($fields['TOUCH']) && $fields['TOUCH'] == 'Y';

		if ($actionType == self::ACTION_TYPE_ADD)
		{
			//@tmp log
			\Bitrix\Landing\Debug::log(
				$fields['TITLE'] ?? 'Noname',
				print_r([$fields, \Bitrix\Main\Diag\Helper::getBackTrace(15)],  true),
				'LANDING_SITE_CREATE'
			);
		}

		// clear binding cache
		if (
			isset($fields['CODE']) ||
			isset($fields['TITLE']) ||
			isset($fields['DELETED'])
		)
		{
			if ($primary)
			{
				\Bitrix\Landing\Binding\Entity::onSiteChange(
					$primary['ID']
				);
			}
		}

		if (
			isset($fields['DOMAIN_ID']) &&
			$fields['DOMAIN_ID'] === ''
		)
		{
			unset($fields['DOMAIN_ID']);
			$unsetFields[] = 'DOMAIN_ID';
		}

		// if delete, set unpublic always
		if (isset($fields['DELETED']))
		{
			$deleteMode = true;
			$modifyFields['ACTIVE'] = 'N';
			$fields['ACTIVE'] = 'N';
			// user try to restore site, check the limits
			if ($primary && $fields['DELETED'] == 'N')
			{
				$fields['TYPE'] = self::getValueByCode(
					$primary['ID'],
					$fields,
					'TYPE'
				);
				$check = Manager::checkFeature(
					Manager::FEATURE_CREATE_SITE,
					[
						'type' => $fields['TYPE'],
						'filter' => ['!ID' => $primary['ID']]
					]
				);
				if (!$check)
				{
					$result->setErrors([
						new Entity\EntityError(
							Restriction\Manager::getSystemErrorMessage('limit_sites_number'),
							'TOTAL_SITE_REACHED'
						)
					]);
					return $result;
				}
			}
			else if ($primary && $fields['DELETED'] == 'Y')
			{
				$fields['DOMAIN_PROVIDER'] = self::getValueByCode(
					$primary['ID'],
					$fields,
					'DOMAIN_PROVIDER'
				);
				if ($fields['DOMAIN_PROVIDER'] && ModuleManager::isModuleInstalled('bitrix24'))
				{
					$result->setErrors([
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_ACCESS_DENIED_DELETED'),
							'ACCESS_DENIED_DELETED'
						)
					]);
					return $result;
				}
			}
		}

		// check that TYPE is valid and allowed
		if (!$primary && isset($fields['TYPE']))
		{
			$allowedTypes = (array)\Bitrix\Landing\Site\Type::getFilterType();
			if (!in_array($fields['TYPE'], $allowedTypes))
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_ACCESS_DENIED_ADD'),
						'ACCESS_DENIED'
					)
				));
				return $result;
			}
		}

		// if domain id is not specified
		if (!$primary && !array_key_exists('DOMAIN_ID', $fields))
		{
			$fields['DOMAIN_ID'] = \Bitrix\Landing\Site\Type::getDomainId();
			$modifyFields['DOMAIN_ID'] = $fields['DOMAIN_ID'];
		}

		// check rights
		if ($primary['ID'] && Rights::isOn())
		{
			$rights = Rights::getOperationsForSite(
				$primary['ID']
			);
			$freeAccessFields = [
				'CREATED_BY_ID',
				'MODIFIED_BY_ID',
				'DATE_CREATE',
				'DATE_MODIFY',
				'TOUCH'
			];
			if (in_array(Rights::ACCESS_TYPES['sett'], $rights))
			{
				$freeAccessFields = $fields;
				if (isset($freeAccessFields['ACTIVE']))
				{
					unset($freeAccessFields['ACTIVE']);
				}
				if (isset($freeAccessFields['DELETED']))
				{
					unset($freeAccessFields['DELETED']);
				}
				$freeAccessFields = array_keys($freeAccessFields);
			}
			if (in_array(Rights::ACCESS_TYPES['public'], $rights))
			{
				$freeAccessFields[] = 'ACTIVE';
			}
			if (in_array(Rights::ACCESS_TYPES['delete'], $rights))
			{
				$freeAccessFields[] = 'DELETED';
				// allow unpublic in delete case
				if ($deleteMode)
				{
					$freeAccessFields[] = 'ACTIVE';
				}
			}
			foreach ($fields as $key => $val)
			{
				if (!in_array($key, $freeAccessFields))
				{
					$errMessage = Loc::getMessage(
						'LANDING_TABLE_ERROR_ACCESS_DENIED_' . $key
					);
					if (!$errMessage)
					{
						$errMessage = Loc::getMessage(
							'LANDING_TABLE_ERROR_ACCESS_DENIED'
						);
					}
					$result->setErrors(array(
						new Entity\EntityError(
							$errMessage,
							'ACCESS_DENIED'
						)
					));
					return $result;
				}
			}
		}
		else if (Rights::isOn())
		{
			$hasAccess = Rights::hasAdditionalRight(
				Rights::ADDITIONAL_RIGHTS['create']
			);
			if (!$hasAccess)
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_ACCESS_DENIED_ADD'),
						'ACCESS_DENIED'
					)
				));
				return $result;
			}
		}

		// additional fields save after
		if (array_key_exists('ADDITIONAL_FIELDS', $fields))
		{
			self::$additionalFields = $fields['ADDITIONAL_FIELDS'];
			$unsetFields[] = 'ADDITIONAL_FIELDS';
		}
		else
		{
			self::$additionalFields = array();
		}

		// check rights for site domain
		if (
			array_key_exists('DOMAIN_ID', $fields) &&
			!Manager::isB24()
		)
		{
			// for check rights call upper level
			$res = Domain::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'ID' => $fields['DOMAIN_ID']
				)
			));
			if (!$res->fetch())
			{
				$result->unsetFields($unsetFields);
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_NOT_EXIST'),
						'DOMAIN_NOT_FOUND'
					)
				));
				return $result;
			}
		}

		// check active first (limit count)
		if (
			isset($fields['ACTIVE']) &&
			$fields['ACTIVE'] == 'Y'
		)
		{
			if ($primary)
			{
				$fields['TYPE'] = self::getValueByCode(
					$primary['ID'],
					$fields,
					'TYPE'
				);
			}
			else
			{
				$fields['TYPE'] = null;
			}
			$special = self::getValueByCode(
				$primary['ID'],
				$fields,
				'SPECIAL'
			);
			if ($special == 'Y')
			{
				$canPublicSite = true;
			}
			else
			{
				$domainProvider = self::getValueByCode(
					$primary['ID'],
					$fields,
					'DOMAIN_PROVIDER'
				);
				if ($domainProvider)
				{
					if (!Restriction\Manager::isAllowed('limit_free_domen', ['trueOnNotNull' => true]))
					{
						$result->unsetFields($unsetFields);
						$result->setErrors(array(
							new Entity\EntityError(
								Restriction\Manager::getSystemErrorMessage('limit_free_domen'),
								'FREE_DOMAIN_IS_NOT_ALLOWED'
							)
						));
						return $result;
					}
				}
				$canPublicSite = Manager::checkFeature(
					Manager::FEATURE_PUBLICATION_SITE,
					$primary
					? array(
						'filter' => array(
							'!ID' => $primary['ID']
						),
						'type' => $fields['TYPE']
					)
					: array(
						'type' => $fields['TYPE']
					)
				);
			}
			if (!$canPublicSite)
			{
				$errCode = Manager::licenseIsFreeSite($fields['TYPE']) && !Manager::isFreePublicAllowed()
					? 'PUBLIC_SITE_REACHED_FREE'
					: 'PUBLIC_SITE_REACHED';
				$msgCode = Manager::licenseIsFreeSite($fields['TYPE']) && !Manager::isFreePublicAllowed()
					? 'limit_sites_number_free'
					: 'limit_sites_number';
				$result->unsetFields($unsetFields);
				$result->setErrors(array(
					new Entity\EntityError(
						Restriction\Manager::getSystemErrorMessage($msgCode),
						$errCode
					)
				));
				return $result;
			}
		}

		// prepare CODE - base part of URL
		if (array_key_exists('CODE', $fields))
		{
			$fields['CODE'] = trim(trim(trim($fields['CODE']), '/'));
			if (mb_strpos($fields['CODE'], '/') !== false)
			{
				$result->unsetFields($unsetFields);
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_SLASH_IS_NOT_ALLOWED'),
						'SLASH_IS_NOT_ALLOWED'
					)
				));
				return $result;
			}
			// generate CODE from TITLE, if CODE is empty (in create)
			if (!$fields['CODE'])
			{
				$fields['CODE'] = \CUtil::translit(
					(array_key_exists('TITLE', $fields) && trim($fields['TITLE']))
					? $fields['TITLE'] : 'site',
					LANGUAGE_ID
				);
				if (!$fields['CODE'])
				{
					$fields['CODE'] = randString(12);
				}
			}
			// only digits is disallowed
			if (preg_match('/^[\d]+$/', $fields['CODE']))
			{
				$fields['CODE'] = 'site' . $fields['CODE'];
			}
			$fields['CODE'] = mb_substr($fields['CODE'], 0, 253);
			$domainId = null;
			// get domain id if no exists
			if (!array_key_exists('DOMAIN_ID', $fields) && $primary)
			{
				$domainId = self::getValueByCode(
					$primary['ID'],
					$fields,
					'DOMAIN_ID'
				);
			}
			else if (array_key_exists('DOMAIN_ID', $fields))
			{
				$domainId = $fields['DOMAIN_ID'];
			}
			// make CODE unique in one domain
			if ($domainId !== null)
			{
				$checkCount = 1;
				$originalCode = $fields['CODE'];
				do
				{
					$unique = self::checkUniqueInDomain(
						'/' . $fields['CODE'] . '/',
						$primary ? $primary['ID'] : 0,
						$domainId
					);
					if (!$unique)
					{
						$fields['CODE'] = $originalCode . (++$checkCount);
					}
				} while (!$unique);
			}
			$fields['CODE'] = '/' . $fields['CODE'] . '/';
			$modifyFields['CODE'] = $fields['CODE'];
		}

		// create/get domain by name (reg in b24.site if Bitrix24)
		if (
			array_key_exists('DOMAIN_ID', $fields) &&
			$fields['DOMAIN_ID'] !== 0 &&
			(
				preg_replace('/[\d]/', '', trim($fields['DOMAIN_ID'])) != '' ||
				Manager::isB24()
			)
		)
		{
			$domainId = 0;
			$domainName = mb_strtolower(trim($fields['DOMAIN_ID']));
			$domainNameOld = '';

			// fix for full name
			if ($domainName != '')
			{
				$puny = new \CBXPunycode;
				$domainName = $puny->encode($domainName);
				// check correct name
				if (!preg_match('/^[a-z0-9\-\.]+\.[a-z0-9\-]{2,20}$/i', $domainName))
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_IS_INCORRECT2'),
							'DOMAIN_IS_INCORRECT'
						)
					));
					return $result;
				}
			}

			// if add - unset domain_id, else - get current domain of site
			if ($actionType == self::ACTION_TYPE_ADD)
			{
				$modifyFields['DOMAIN_ID'] = 0;
			}
			else
			{
				if ($primary)
				{
					$res = self::getList(array(
						'select' => array(
							'DOMAIN_ID',
							'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
						),
						'filter' => array(
							'ID' => $primary['ID'],
							'CHECK_PERMISSIONS' => 'N'
						)
					));
					if ($row = $res->fetch())
					{
						$domainNameOld = mb_strtolower($row['DOMAIN_NAME']);
						$domainId = $row['DOMAIN_ID'];
					}
				}
				$unsetFields[] = 'DOMAIN_ID';
			}

			// check CODE unique in site group
			if ($domainId && array_key_exists('CODE', $fields))
			{
				$unique = self::checkUniqueInDomain(
					$fields['CODE'],
					$primary ? $primary['ID'] : 0,
					$domainId
				);
				if (!$unique)
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_SITE_CODE_IS_NOT_UNIQUE2'),
							'CODE_IS_NOT_UNIQUE'
						)
					));
					return $result;
				}
			}

			// if domain name now changed
			if (
				$domainName != $domainNameOld ||
				$actionType == self::ACTION_TYPE_ADD
			)
			{
				$domainExist = false;

				// check domain exist
				if ($domainName != '')
				{
					$resDomain = Domain::getList(array(
						'select' => array(
							'ID'
						),
						'filter' => array(
							'=DOMAIN' => $domainName
						)
					));
					if ($rowDomain = $resDomain->fetch())
					{
						$domainExist = true;
						$resSite = Site::getList(array(
							'select' => array(
								'ID'
							),
							'filter' => array(
								'DOMAIN_ID' => $rowDomain['ID'],
								'=DELETED' => 'Y',
								'CHECK_PERMISSIONS' => 'N'
							)
		  				));
						if ($resSite->fetch())
						{
							$result->setErrors(
								array(
									new Entity\EntityError(
										Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_EXIST_TRASH'),
										'DOMAIN_EXIST_TRASH'
									)
								)
							);
							return $result;
						}
					}
					elseif (Manager::isB24())
					{
						try
						{
							$domainExist = $siteController::isDomainExists($domainName);
						}
						catch (SystemException $ex)
						{
							$result->unsetFields($unsetFields);
							$result->setErrors(array(
								self::customizeControllerError($ex)
							));
							return $result;
						}
					}
				}
				if ($domainExist)
				{
					$result->unsetFields($unsetFields);
					if (self::checkBitrixUse($domainName))
					{
						$result->setErrors(
							array(
								new Entity\EntityError(
									Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_BITRIX_DISABLE'),
									'DOMAIN_DISABLE'
								)
							)
						);
					}
					else
					{
						$result->setErrors(
							array(
								new Entity\EntityError(
									Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_EXIST'),
									'DOMAIN_EXIST'
								)
							)
						);
					}

					return $result;
				}

				// check available external service
				try
				{
					$siteController::isDomainExists('repo.bitrix24.site');
				}
				catch (SystemException $ex)
				{
					$result->unsetFields($unsetFields);
					$result->setErrors(array(
						self::customizeControllerError($ex)
					));
					return $result;
				}

				// handler on add / update
				$eventManager = \Bitrix\Main\EventManager::getInstance();
				$eventManager->addEventHandler(
					'landing',
					$actionType == self::ACTION_TYPE_ADD
					? '\\' . __NAMESPACE__ . '\\Site::onAfterAdd'
					: '\\' . __NAMESPACE__ . '\\Site::onAfterUpdate',
					function(Entity\Event $event) use ($domainId, $domainName, $domainNameOld, $result, $unsetFields, $siteController)
					{
						$primary = $event->getParameter('primary');
						$fields = $event->getParameter('fields');

						if ($primary)
						{
							// create domain
							if (!$domainId)
							{
								// action in b24
								if (Manager::isB24())
								{
									$publicUrl = Manager::getPublicationPath(
										$primary['ID']
									);
									try
									{
										$row = self::getList(array(
											'select' => array(
												'TYPE'
											),
											'filter' => array(
												'ID' => $primary['ID']
											)
									 	))->fetch();
										if ($row['TYPE'] == 'STORE')// fix for controller
										{
											$row['TYPE'] = 'shop';
										}
										if ($domainName)
										{
											$siteController::addDomain(
												$domainName,
												$publicUrl,
												'N',
												$row['TYPE']
											);
										}
										else
										{
											$domainName = $siteController::addRandomDomain(
												$publicUrl,
												$row['TYPE'],
												Manager::getZone()
											);
										}
									}
									catch (SystemException $ex)
									{
										$result->unsetFields($unsetFields);
										$result->setErrors(array(
											self::customizeControllerError($ex)
										));
										return $result;
									}
								}
								// add new domain
								if ($domainName)
								{
									$resDomain = Domain::add(array(
										'ACTIVE' => 'Y',
										'DOMAIN' => $domainName
									));
									$domainId = $resDomain->getId();
									if ($domainId)
									{
										SiteTable::$disableCallback = true;
										SiteTable::update($primary['ID'], array(
											'DOMAIN_ID' => $domainId
										));
										SiteTable::$disableCallback = false;
									}
								}
							}
							// update domain
							else
							{
								$res = Domain::update($domainId, array(
									'DOMAIN' => $domainName,
									'FAIL_COUNT' => null,
									'PROVIDER' => null
								));
								if ($res->isSuccess())
								{
									if (Manager::isB24())
									{
										try
										{
											$publicUrl = Manager::getPublicationPath(
												$primary['ID']
											);
											$siteController::updateDomain(
												$domainNameOld,
												$domainName,
												$publicUrl
											);
										}
										catch (SystemException $ex)
										{
											$result->unsetFields($unsetFields);
											$result->setErrors(array(
												self::customizeControllerError($ex)
											));
											return $result;
										}
									}
								}
							}
						}
					}
				);
			}
		}

		$result->unsetFields($unsetFields);
		$result->modifyFields($modifyFields);

		return $result;
	}

	/**
	 * Switch domains between two sites. Returns true on success.
	 * @param int $siteId1 First site id.
	 * @param int $siteId2 Second site id.
	 * @return bool
	 */
	public static function switchDomain(int $siteId1, int $siteId2): bool
	{
		$result = false;

		self::$disableCallback = true;

		$domains = [];
		$res = self::getList([
			'select' => [
				'ID',
				'TYPE',
				'LANG',
				'DOMAIN_ID',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
			],
			'filter' => [
				'ID' => [$siteId1, $siteId2]
			]
		]);
		while ($row = $res->fetch())
		{
			$domains[] = [
				'ID' => $row['ID'],
				'TYPE' => $row['TYPE'],
				'LANG' => $row['LANG'],
				'DOMAIN_ID' => $row['DOMAIN_ID'],
				'DOMAIN_NAME' => $row['DOMAIN_NAME']
			];
		}

		if (count($domains) == 2)
		{
			$res1 = self::update($domains[0]['ID'], [
				'DOMAIN_ID' => $domains[1]['DOMAIN_ID']
			]);
			$res2 = self::update($domains[1]['ID'], [
				'DOMAIN_ID' => $domains[0]['DOMAIN_ID']
			]);
			$result = $res1->isSuccess() && $res2->isSuccess();
		}

		self::$disableCallback = false;

		// switch domain for bitrix24
		if ($result && Manager::isB24())
		{
			$siteController = self::getSiteController();
			try
			{
				for ($i = 0; $i <= 1; $i++)
				{
					$siteController::deleteDomain($domains[$i]['DOMAIN_NAME']);
					$siteController::addDomain(
						$domains[$i]['DOMAIN_NAME'],
						Manager::getPublicationPath($domains[$i == 0 ? 1 : 0]['ID']),
						'Y',
						($domains[$i]['TYPE'] == 'STORE') ? 'shop' : $domains[$i]['TYPE'],
						$domains[$i]['LANG']
					);
				}
			}
			catch (SystemException $ex) {}
		}

		return $result;
	}

	/**
	 * Sets new random domain to site.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function randomizeDomain(int $siteId): bool
	{
		$res = self::getList([
			'select' => [
				'ID',
				'TYPE',
				'DOMAIN_ID',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
			],
			'filter' => [
				'ID' => $siteId
			]
		]);
		if ($row = $res->fetch())
		{
			$siteController = self::getSiteController();
			$publicUrl = Manager::getPublicationPath($row['ID']);
			try
			{
				$siteController::deleteDomain($row['DOMAIN_NAME']);
				$domainName = $siteController::addRandomDomain(
					$publicUrl,
					($row['TYPE'] == 'STORE') ? 'shop' : $row['TYPE'],
					Manager::getZone()
				);
				if ($domainName)
				{
					$res = Domain::update($row['DOMAIN_ID'], [
						'DOMAIN' => $domainName,
						'FAIL_COUNT' => null,
						'PROVIDER' => null
					]);
					return $res->isSuccess();
				}
			}
			catch (SystemException $ex) {}
		}

		return false;
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult();

		if (self::$disableCallback)
		{
			return $result;
		}

		$fields = $event->getParameter('fields');

		// check site limit
		if (
			!Manager::checkFeature(
				Manager::FEATURE_CREATE_SITE,
				array(
					'type' => $fields['TYPE']
				)
			)
		)
		{
			$result->unsetFields(array('ADDITIONAL_FIELDS'));
			$result->setErrors(array(
				new Entity\EntityError(
					Restriction\Manager::getSystemErrorMessage('limit_sites_number'),
					'SITE_LIMIT_REACHED'
				)
			));
			return $result;
		}

		return self::prepareChange($event, self::ACTION_TYPE_ADD);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeUpdate(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return new Entity\EventResult();
		}

		return self::prepareChange($event, self::ACTION_TYPE_UPDATE);
	}

	/**
	 * Save additional fields after add / update.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	protected static function saveAdditionalFields(Entity\Event $event)
	{
		$result = new Entity\EventResult();

		if (!empty(self::$additionalFields))
		{
			$primary = $event->getParameter('primary');
			Site::saveAdditionalFields(
				$primary['ID'],
				self::$additionalFields
			);
		}

		return $result;
	}

	/**
	 * Get entity rows.
	 * @param array $params Params array.
	 * @return \Bitrix\Main\ORM\Query\Result
	 */
	public static function getList(array $params = array())
	{
		if (
			!isset($params['filter']) ||
			!is_array($params['filter'])
		)
		{
			$params['filter'] = array();
		}
		if (
			!isset($params['filter']['DELETED']) &&
			!isset($params['filter']['=DELETED'])
		)
		{
			$params['filter']['=DELETED'] = 'N';
		}
		if (isset($params['filter']['CHECK_PERMISSIONS']))
		{
			unset($params['filter']['CHECK_PERMISSIONS']);
		}

		// strict filter by type
		$type = null;
		if (isset($params['filter']['TYPE']))
		{
			$type = $params['filter']['TYPE'];
			unset($params['filter']['TYPE']);
		}
		if (isset($params['filter']['=TYPE']))
		{
			$type = $params['filter']['=TYPE'];
			unset($params['filter']['=TYPE']);
		}
		$allowedTypes = \Bitrix\Landing\Site\Type::getFilterType();
		$params['filter']['=TYPE'] = (!is_array($type) && in_array($type, (array)$allowedTypes))
									? $type
									: $allowedTypes;

		return parent::getList($params);
	}

	/**
	 * After add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterAdd(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		return self::saveAdditionalFields($event);
	}

	/**
	 * After update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterUpdate(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		// for B24 we must update domain
		if (Manager::isB24() && !self::$touchMode)
		{
			static $domainUpdated = [];

			$primary = $event->getParameter('primary');

			if (!in_array($primary['ID'], $domainUpdated))
			{
				$domainUpdated[] = $primary['ID'];
				$siteController = self::getSiteController();
				$res = self::getList([
					'select' => [
						'ACTIVE', 'DELETED',
						'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
						'LANG'
					],
					'filter' => [
						'ID' => $primary['ID'],
						'=DELETED' => ['Y', 'N'],
						'CHECK_PERMISSIONS' => 'N',
						'>DOMAIN_ID' => 0
					]
				]);
				if ($row = $res->fetch())
				{
					try
					{
						// now external domains always are active
						$siteController::activateDomain(
							$row['DOMAIN_NAME'],
							'Y',
							$row['LANG']
						);
					}
					catch (\Bitrix\Main\SystemException $ex) {}
				}
			}
		}

		return self::saveAdditionalFields($event);
	}

	/**
	 * Before delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeDelete(Entity\Event $event)
	{
		if (self::$disableCallback)
		{
			return true;
		}

		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$siteController = self::getSiteController();

		if ($primary)
		{
			// check delete access
			$hasAccess = Rights::hasAccessForSite(
				$primary['ID'],
				Rights::ACCESS_TYPES['delete'],
				true
			);
			if (!$hasAccess)
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_ACCESS_DENIED_DELETED'),
						'ACCESS_DENIED'
					)
				));
				return $result;
			}
			// check if site is not empty
			$res = LandingTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $primary['ID'],
					'CHECK_PERMISSIONS' => 'N'
				)
			));
			if ($res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_IS_NOT_EMPTY'),
						'SITE_IS_NOT_EMPTY'
					)
				));
				return $result;
			}
			// check lock status
			if (\Bitrix\Landing\Lock::isSiteDeleteLocked($primary['ID']))
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_IS_LOCK'),
						'SITE_IS_LOCK'
					)
				));
				return $result;
			}

			// delete in b24.site
			if (Manager::isB24())
			{
				$res = self::getList(array(
					'select' => array(
						'DOMAIN_ID',
						'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
						'DOMAIN_PROVIDER' => 'DOMAIN.PROVIDER'
					),
					'filter' => array(
						'ID' => $primary['ID'],
						'DELETED' => ['Y', 'N'],
						'CHECK_PERMISSIONS' => 'N'
					)
				));
				if ($row = $res->fetch())
				{
					if ($row['DOMAIN_PROVIDER'] && ModuleManager::isModuleInstalled('bitrix24'))
					{
						$result->setErrors([
							new Entity\EntityError(
								Loc::getMessage('LANDING_TABLE_ERROR_ACCESS_DENIED_DELETED'),
								'ACCESS_DENIED_DELETED'
							)
						]);
						return $result;
					}

					$domainId = $row['DOMAIN_ID'];
					$domainName = $row['DOMAIN_NAME'];
					$eventManager = \Bitrix\Main\EventManager::getInstance();
					$eventManager->addEventHandler(
						'landing',
						'\\' . __NAMESPACE__ . '\\Site::onAfterDelete',
						function(Entity\Event $event) use ($domainId, $domainName, $result, $siteController)
						{
							$res = self::getList(array(
								'select' => array(
									'ID'
								),
								'filter' => array(
									'DOMAIN_ID' => $domainId,
									'DELETED' => ['Y', 'N']
								)
							));
							if (!$res->fetch())
							{
								DomainTable::delete($domainId);
								try
								{
									$siteController::deleteDomain($domainName);
								}
								catch (SystemException $ex)
								{
									$result->setErrors(array(
							   			self::customizeControllerError($ex)
							   		));
									return $result;
								}
							}
						}
					);
				}
			}
		}

		return $result;
	}

	/**
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');

		if (self::$disableCallback)
		{
			return $result;
		}

		// delete all inner landings
		if ($primary)
		{
			Rights::setOff();

			$res = LandingTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $primary['ID']
				)
			));
			while ($row = $res->fetch())
			{
				\Bitrix\Landing\Landing::delete($row['ID'], true);
			}

			\Bitrix\Landing\Syspage::deleteForSite($primary['ID']);
			\Bitrix\Landing\File::deleteFromSite($primary['ID']);
			\Bitrix\Landing\Hook::deleteForSite($primary['ID']);
			\Bitrix\Landing\TemplateRef::setForSite($primary['ID'], []);
			\Bitrix\Landing\UrlRewrite::removeForSite($primary['ID']);
			\Bitrix\Landing\Rights::setOperationsForSite($primary['ID'], []);
			\Bitrix\Landing\Folder::deleteForSite($primary['ID']);
			\Bitrix\Landing\Site\Cookies::removeAgreementsForSite($primary['ID']);
			BindingTable::siteClear($primary['ID']);

			Rights::setOn();
		}

		return $result;
	}
}
