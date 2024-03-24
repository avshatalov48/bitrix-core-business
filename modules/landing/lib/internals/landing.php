<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Role;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Landing\Cache;

Loc::loadMessages(__FILE__);

/**
 * Class LandingTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Landing_Query query()
 * @method static EO_Landing_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Landing_Result getById($id)
 * @method static EO_Landing_Result getList(array $parameters = array())
 * @method static EO_Landing_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_Landing createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_Landing_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_Landing wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_Landing_Collection wakeUpCollection($rows)
 */
class LandingTable extends Entity\DataManager
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
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_CODE')
			)),
			'INITIATOR_APP_CODE' => new Entity\StringField('INITIATOR_APP_CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_INITIATOR_APP_CODE')
			)),
			'RULE' => new Entity\StringField('RULE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_RULE')
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ACTIVE'),
				'default_value' => 'Y'
			)),
			'DELETED' => new Entity\StringField('DELETED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_DELETED'),
				'default_value' => 'N'
			)),
			'PUBLIC' => new Entity\StringField('PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_PUBLIC'),
				'default_value' => 'Y'
			)),
			'SYS' => new Entity\StringField('SYS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_SYSTEM'),
				'default_value' => 'N'
			)),
			'VIEWS' => new Entity\IntegerField('VIEWS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_VIEWS'),
				'default_value' => 0
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_TITLE'),
				'required' => true,
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'DESCRIPTION' => new Entity\StringField('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESCRIPTION')
			)),
			'TPL_ID' => new Entity\IntegerField('TPL_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_ID')
			)),
			'TPL_CODE' => new Entity\StringField('TPL_CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_CODE')
			)),
			'SITE_ID' => new Entity\IntegerField('SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_ID'),
				'required' => true
			)),
			'SITE' => new Entity\ReferenceField(
				'SITE',
				'Bitrix\Landing\Internals\SiteTable',
				array('=this.SITE_ID' => 'ref.ID')
			),
			'AREAS' => new Entity\ReferenceField(
				'AREAS',
				'Bitrix\Landing\Internals\TemplateRefTable',
				array('=this.ID' => 'ref.LANDING_ID')
			),
			'SITEMAP' => new Entity\StringField('SITEMAP', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_SITEMAP'),
				'default_value' => 'N'
			)),
			'FOLDER' => new Entity\StringField('FOLDER', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER'),
				'default_value' => 'N'
			)),
			'FOLDER_ID' => new Entity\IntegerField('FOLDER_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_ID')
			)),
			'SEARCH_CONTENT' => new Entity\StringField('SEARCH_CONTENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SEARCH_CONTENT')
			)),
			'VERSION' => new Entity\IntegerField('VERSION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_VERSION'),
				'default_value' => 10
			)),
			'HISTORY_STEP' => new Entity\IntegerField('HISTORY_STEP', array(
				'title' => 'History step',
				'default_value' => 0
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
			)),
			'DATE_PUBLIC' => new Entity\DatetimeField('DATE_PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_PUBLIC')
			))
		);
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
			$allowedSites ? ['SITE_ID' => $allowedSites] : []
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
		//$readCode = Rights::ACCESS_TYPES['read'];
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
			'=this.SITE_ID' => 'ref.ENTITY_ID',
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

		$params['group'][] = 'SITE_ID';

		// build filter
		$params['filter'][] = $buildFilter;

		return $params;
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
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');
		$modifyFields = array();
		$existFields = array();
		$deleteMode = false;

		// get all fields, which we need
		if ($primary)
		{
			$res = self::getList([
				'select' => [
					'CODE',
					'SITE_ID', 'FOLDER_ID',
					'INITIATOR_APP_CODE',
					'ID_INDEX' => 'SITE.LANDING_ID_INDEX'
				],
				'filter' => [
					'ID' => $primary['ID'],
					'=DELETED' => ['Y', 'N']
				]
			]);
			$existFields = $res->fetch();
			unset($res);
		}

		// check that folder within landing's site
		if (($fields['FOLDER_ID'] ?? null) && ($fields['FOLDER_SKIP_CHECK'] ?? 'N') !== 'Y')
		{
			if (empty($existFields['SITE_ID']))
			{
				$existFields['SITE_ID'] = $fields['SITE_ID'];
			}
			$res = FolderTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'SITE_ID' => $existFields['SITE_ID'],
					'ID' => $fields['FOLDER_ID']
				]
			]);
			if (!$res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_FOLDER_NOT_FOUND'),
						'FOLDER_NOT_FOUND'
					)
				));
				return $result;
			}
		}

		// check CODE mask
		if (
			isset($fields['CODE']) &&
			(
				!isset($existFields['CODE']) ||
				$existFields['CODE'] != $fields['CODE']
			)
		)
		{
			if (preg_match('#^([\w]+)\_([\d]+)\_([\d]+)$#', $fields['CODE'], $matches))
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_WRONG_CODE_FORMAT'),
						'WRONG_CODE_FORMAT'
					)
				));
				return $result;
			}
		}

		// if page have't blocks of page's app, we clear this mark
		if (isset($fields['INITIATOR_APP_CODE']))
		{
			$existFields['INITIATOR_APP_CODE'] = $fields['INITIATOR_APP_CODE'];
		}

		// if delete, set unpublic always
		if (isset($fields['DELETED']))
		{
			$deleteMode = true;
			$modifyFields['ACTIVE'] = 'N';
			$fields['ACTIVE'] = 'N';
			// disable main page deleting
			if ($fields['DELETED'] == 'Y' && $primary)
			{
				if ($existFields['ID_INDEX'] == $primary['ID'])
				{
					// disable only for a few pages in the site
					$sitesCheck = self::getList([
						'select' => [
							'ID'
						],
						'filter' => [
							'SITE_ID' => $existFields['SITE_ID'],
							'=DELETED' => 'N'
						],
						'limit' => 2
					])->fetchAll();
					if (count($sitesCheck) > 1)
					{
						$result->setErrors([
							new Entity\EntityError(
								Loc::getMessage('LANDING_TABLE_ERROR_LD_CANT_DELETE_MAIN'),
								'CANT_DELETE_MAIN'
							)
						]);
						return $result;
					}
				}
			}
		}

		// get real site id, for check rights call upper level
		if (array_key_exists('SITE_ID', $fields))
		{
			$res = \Bitrix\Landing\Site::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'ID' => $fields['SITE_ID']
				)
			));
			if (!$res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_NOT_FOUND'),
						'SITE_NOT_FOUND'
					)
				));
				return $result;
			}
		}
		else if ($existFields)
		{
			if ($existFields['SITE_ID'])
			{
				$fields['SITE_ID'] = $existFields['SITE_ID'];
			}
			else
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_NOT_FOUND'),
						'SITE_NOT_FOUND'
					)
				));
				return $result;
			}
		}
		else
		{
			$fields['SITE_ID'] = 0;
		}

		// check rights
		if ($fields['SITE_ID'] && Rights::isOn())
		{
			$rights = Rights::getOperationsForSite(
				$fields['SITE_ID']
			);
			if (!\Bitrix\Landing\Site\Type::isPublicScope())
			{
				$rights[] = Rights::ACCESS_TYPES['public'];
			}
			// can create new landing in site
			if (!$primary)
			{
				if (!in_array(Rights::ACCESS_TYPES['edit'], $rights))
				{
					$errMessage = Loc::getMessage(
						'LANDING_TABLE_ERROR_LD_ACCESS_DENIED_ADD'
					);
					$result->setErrors(array(
						new Entity\EntityError(
							$errMessage,
							'ACCESS_DENIED'
						)
					));
					return $result;
				}
			}
			else
			{
				$freeAccessFields = [
					'CREATED_BY_ID',
					'MODIFIED_BY_ID',
					'DATE_CREATE',
					'DATE_MODIFY',
					'SITE_ID',
					'PUBLIC'
				];
				if (in_array(Rights::ACCESS_TYPES['sett'], $rights))
				{
					$freeAccessFields = $fields;
					$higherAccess = [
						'ACTIVE', 'DATE_PUBLIC', 'DELETED'
					];
					foreach ($higherAccess as $key)
					{
						if (isset($freeAccessFields[$key]))
						{
							unset($freeAccessFields[$key]);
						}
					}
					$freeAccessFields = array_keys($freeAccessFields);
				}
				if (in_array(Rights::ACCESS_TYPES['public'], $rights))
				{
					$freeAccessFields[] = 'ACTIVE';
					$freeAccessFields[] = 'DATE_PUBLIC';
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
							'LANDING_TABLE_ERROR_LD_ACCESS_DENIED_' . $key
						);
						if (!$errMessage)
						{
							$errMessage = Loc::getMessage(
								'LANDING_TABLE_ERROR_LD_ACCESS_DENIED'
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
		}

		// additional fields save after
		if (array_key_exists('ADDITIONAL_FIELDS', $fields))
		{
			self::$additionalFields = $fields['ADDITIONAL_FIELDS'];
			$result->unsetFields(array('ADDITIONAL_FIELDS'));
		}
		else
		{
			self::$additionalFields = array();
		}

		// slash in CODE is not allowed
		if (
			array_key_exists('CODE', $fields) &&
			mb_strpos($fields['CODE'], '/') !== false
		)
		{
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_SLASH_IS_NOT_ALLOWED'),
					'SLASH_IS_NOT_ALLOWED'
				)
			));
			return $result;
		}
		// CODE can't be empty
		elseif (
			array_key_exists('CODE', $fields) &&
			$fields['CODE'] == '' &&
			!isset($fields['FOLDER'])
		)
		{
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_CANT_BE_EMPTY'),
					'CANT_BE_EMPTY'
				)
			));
			return $result;
		}
		elseif (
			$actionType == self::ACTION_TYPE_ADD && array_key_exists('TITLE', $fields) &&
			(!array_key_exists('CODE', $fields) || trim($fields['CODE']) == '')
		)
		{
			$fields['CODE'] = \CUtil::translit(
				trim($fields['TITLE']),
				LANGUAGE_ID,
				array(
					'replace_space' => '',
					'replace_other' => ''
				));
			if (!$fields['CODE'])
			{
				$fields['CODE'] = randString(12);
			}
			$modifyFields['CODE'] = $fields['CODE'];
		}

		$result->modifyFields($modifyFields);

		return $result;
	}

	/**
	 * Get entity rows.
	 * @param array $params Params array.
	 * @return \Bitrix\Main\ORM\Query\Result
	 */
	public static function getList(array $params = array())
	{
		if (Landing::checkDeleted())
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
			if (
				!isset($params['filter']['SITE.DELETED']) &&
				!isset($params['filter']['=SITE.DELETED'])
			)
			{
				$params['filter']['=SITE.DELETED'] = 'N';
			}
		}
		if (isset($params['filter']['CHECK_PERMISSIONS']))
		{
			unset($params['filter']['CHECK_PERMISSIONS']);
		}

		// strict filter by type
		$type = null;
		if (isset($params['filter']['SITE.TYPE']))
		{
			$type = $params['filter']['SITE.TYPE'];
			unset($params['filter']['SITE.TYPE']);
		}
		if (isset($params['filter']['=SITE.TYPE']))
		{
			$type = $params['filter']['=SITE.TYPE'];
			unset($params['filter']['=SITE.TYPE']);
		}
		$allowedTypes = \Bitrix\Landing\Site\Type::getFilterType();
		$params['filter']['=SITE.TYPE'] = in_array($type, (array)$allowedTypes)
										? $type
										: $allowedTypes;

		return parent::getList($params);
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeAdd(Entity\Event $event)
	{
		return self::prepareChange($event, self::ACTION_TYPE_ADD);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeUpdate(Entity\Event $event)
	{
		return self::prepareChange($event, self::ACTION_TYPE_UPDATE);
	}

	/**
	 * Before delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		if ($primary)
		{
			$res = self::getList([
				'select' => [
					'SITE_ID'
				],
				'filter' => [
					'ID' => $primary['ID'],
					'CHECK_PERMISSIONS' => 'N',
					'=SITE.DELETED' => ['Y', 'N'],
					'=DELETED' => ['Y', 'N']
				]
 			]);
			if ($site = $res->fetch())
			{
				// check delete access
				$hasAccess = Rights::hasAccessForSite(
					$site['SITE_ID'],
					Rights::ACCESS_TYPES['delete']
				);
				if (!$hasAccess)
				{
					$result->setErrors(array(
						new Entity\EntityError(
							Loc::getMessage('LANDING_TABLE_ERROR_LD_ACCESS_DENIED_DELETED'),
							'ACCESS_DENIED'
						)
					));
					return $result;
				}
			}
			// check lock status
			if (\Bitrix\Landing\Lock::isLandingDeleteLocked($primary['ID']))
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_LD_IS_LOCK'),
						'LANDING_IS_LOCK'
					)
				));
				return $result;
			}
		}
		return $result;
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
			Landing::saveAdditionalFields(
				$primary['ID'],
				self::$additionalFields
			);
		}

		return $result;
	}

	/**
	 * Reverts non unique code after add/update.
	 * @param Entity\Event $event Event instance.
	 * @return void
	 */
	protected static function revertCode(Entity\Event $event): void
	{
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		if (!Landing::isCheckUniqueAddress())
		{
			return;
		}

		if (isset($primary['ID']) && array_key_exists('CODE', $fields))
		{
			$landingId = (int)$primary['ID'];
			$updateCode = false;

			Landing::disableCheckDeleted();

			$landing = Landing::createInstance($landingId);

			if ($landing->getMeta()['RULE'])
			{
				Landing::enableCheckDeleted();
				return;
			}

			if ($landing->exist())
			{
				if ($fields['FOLDER_ID'] ?? null)
				{
					$res = self::getList([
						'select' => [
							'ID'
						],
						'filter' => [
							'!ID' => $primary['ID'],
							'FOLDER_ID' => $fields['FOLDER_ID'],
							'=CODE' => $fields['CODE']
						]
					]);
					if ($res->fetch())
					{
						$updateCode = true;
					}
				}
				else
				{
					$landingUrl = $landing->getPublicUrl(false, false);
					$resolvedId = Landing::resolveIdByPublicUrl($landingUrl, $landing->getSiteId());
					if ($resolvedId && $landingId !== $resolvedId)
					{
						$updateCode = true;
					}
				}
			}

			Landing::enableCheckDeleted();

			if ($updateCode)
			{
				Landing::disableCheckUniqueAddress();
				$reUpdate = [
					'CODE' => $fields['CODE'] . '_' . Manager::getRandomString(4)
				];
				if (self::$additionalFields)
				{
					$reUpdate['ADDITIONAL_FIELDS'] = self::$additionalFields;
				}
				parent::update($landingId, $reUpdate);
				Landing::enableCheckUniqueAddress();
			}
		}
	}

	/**
	 * After add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterAdd(Entity\Event $event)
	{
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		// set current landing as index, if this is single page
		if ($primary && isset($fields['SITE_ID']))
		{
			$res = self::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $fields['SITE_ID'],
					'CHECK_PERMISSIONS' => 'N'
				),
				'limit' => 2
			));
			if (count($res->fetchAll()) == 1)
			{
				Rights::setOff();
				SiteTable::update($fields['SITE_ID'], array(
					'LANDING_ID_INDEX' => $primary['ID']
				));
				Rights::setOn();
			}
		}

		self::revertCode($event);

		return self::saveAdditionalFields($event);
	}

	/**
	 * After update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterUpdate(Entity\Event $event)
	{
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		if ($primary)
		{
			Cache::clear($primary['ID']);
		}

		// for B24 we must update domain
		if (
			array_key_exists('SITE_ID', $fields) &&
			Manager::isB24()
		)
		{
			\Bitrix\Landing\Site::update(
				$fields['SITE_ID'],
				array()
			);
		}

		self::revertCode($event);

		return self::saveAdditionalFields($event);
	}

	/**
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return void
	 */
	public static function OnAfterDelete(Entity\Event $event)
	{
		$primary = $event->getParameter('primary');

		if (isset($primary['ID']))
		{
			Rights::setOff();

			\Bitrix\Landing\File::deleteFromLanding($primary['ID']);
			\Bitrix\Landing\Syspage::deleteForLanding($primary['ID']);
			\Bitrix\Landing\Hook::deleteForLanding($primary['ID']);
			\Bitrix\Landing\TemplateRef::deleteArea($primary['ID']);
			\Bitrix\Landing\TemplateRef::setForLanding($primary['ID'], array());
			\Bitrix\Landing\UrlRewrite::removeForLanding($primary['ID']);

			// if delete index page, make new page is index
			$res = \Bitrix\Landing\Site::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'LANDING_ID_INDEX' => $primary['ID']
				)
			));
			if ($site = $res->fetch())
			{
				$res = Landing::getList(array(
					'select' => array(
						'ID'
					),
					'filter' => array(
						'SITE_ID' => $site['ID']
					),
					'order' => array(
						'ID' => 'asc'
					)
				));
				while ($page = $res->fetch())
				{
					if (!TemplateRef::landingIsArea($page['ID']))
					{
						\Bitrix\Landing\Site::update(
							$site['ID'],
							array(
								'LANDING_ID_INDEX' => $page['ID']
							)
						);
						break;
					}
				}
			}

			Rights::setOn();
		}
	}
}
