<?php
namespace Bitrix\Landing;

use Bitrix\Landing\Block\BlockRepo;
use Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class Landing extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Keys for get meta data from landing data
	 */
	protected const META_KEYS = [
		'CREATED_BY_ID', 'MODIFIED_BY_ID', 'DATE_CREATE',
		'DATE_MODIFY', 'DATE_PUBLIC', 'INITIATOR_APP_CODE', 'VIEWS', 'TPL_CODE',
		'ACTIVE', 'PUBLIC', 'SITE_CODE', 'SITE_SPECIAL', 'RULE',
		'SITE_VERSION', 'SITE_LANG', 'SITE_TPL_CODE'
	];

	/**
	 * Meta keys, than can be changed
	 */
	protected const META_KEYS_MODIFIABLE = [
		'DATE_MODIFY', 'DATE_PUBLIC', 'ACTIVE', 'PUBLIC'
	];

	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'LandingTable';

	/**
	 * Enabled updated or not.
	 * @var bool
	 */
	protected static $enabledUpdate = true;

	/**
	 * Enabled checking unique page address or not;
	 * @var bool
	 */
	protected static $checkUniqueAddress = true;

	/**
	 * Check deleted pages or not.
	 * @var bool
	 */
	protected static $checkDelete = true;

	/**
	 * Current mode is edit.
	 * @var boolean
	 */
	protected static $editMode = false;

	/**
	 * Current mode is preview.
	 * @var boolean
	 */
	protected static $previewMode = false;

	/**
	 * External variables of Landing.
	 * @var array
	 */
	protected static $variables = array();

	/**
	 * Dynamic filter id.
	 * @var int
	 */
	protected static $dynamicFilterId = 0;

	/**
	 * Dynamic element id.
	 * @var int
	 */
	protected static $dynamicElementId = 0;

	/**
	 * Landing's site code.
	 * @var string
	 */
	protected static string $siteType = '';

	/**
	 * Set false if landing view as area.
	 * @var boolean
	 */
	protected $mainInstance = true;

	/**
	 * Additional data of current landing.
	 * @var array
	 */
	protected $metaData = array();

	/**
	 * All blocks of current landing.
	 * @var Block[]
	 */
	protected $blocks = array();

	/**
	 * Id of current landing.
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Title of current landing.
	 * @var string
	 */
	protected $title = '';

	/**
	 * Code (part of URL) of current landing.
	 * @var string
	 */
	protected $code = '';

	/**
	 * XMl Id of current landing.
	 * @var string
	 */
	protected $xmlId = '';

	/**
	 * Some fields from landing's site.
	 * @var array
	 */
	protected $siteRow = [];

	/**
	 * Site id of current landing.
	 * @var int
	 */
	protected $siteId = 0;

	/**
	 * Site title of current landing.
	 * @var string
	 */
	protected $siteTitle = '';

	/**
	 * Domain id.
	 * @var int
	 */
	protected $domainId = 0;

	/**
	 * Folder id of current landing.
	 * @var int
	 */
	protected $folderId = 0;

	/**
	 * Current template id.
	 * @var int
	 */
	protected int $tplId = 0;

	/**
	 * Current template type (site or landing).
	 * @var string
	 */
	protected $tplType = 'landing';

	/**
	 * Active or not current landing.
	 * @var boolean
	 */
	protected $active = false;

	/**
	 * Instance of Error.
	 * @var Error
	 */
	protected $error = null;

	/**
	 * Current landing rights.
	 * @var string[]
	 */
	protected $rights = [];

	/**
	 * Checks permissions within current landing.
	 * @var bool
	 */
	protected $checkPermissions = true;

	/**
	 * Current version.
	 * @var int
	 */
	protected $version = 1;

	/**
	 * Disable /preview for link in replace method.
	 * @var bool
	 */
	protected $disableLinkPreview = false;

	/**
	 * Constructor.
	 * @param int $id Landing id.
	 * @param array $params Some params.
	 */
	protected function __construct($id, $params = array())
	{
		$id = intval($id);
		$this->error = new Error;
		$filter = array(
			'ID' => $id,
		);
		if (
			isset($params['force_deleted']) &&
			$params['force_deleted'] === true
		)
		{
			$filter['=DELETED'] = ['Y', 'N'];
			$filter['=SITE.DELETED'] = ['Y', 'N'];
		}
		if (
			isset($params['check_permissions']) &&
			$params['check_permissions'] === false
		)
		{
			$filter['CHECK_PERMISSIONS'] = 'N';
			$this->checkPermissions = false;
		}
		if (
			isset($params['disable_link_preview']) &&
			$params['disable_link_preview'] === true
		)
		{
			$this->disableLinkPreview = true;
		}

		if ($id)
		{
			$landing = self::getList(array(
				'select' => array(
					'*',
					'SITE_TPL_ID' => 'SITE.TPL_ID',
					'SITE_TPL_CODE' => 'SITE.TPL_CODE',
					'SITE_CODE' => 'SITE.CODE',
					'SITE_TYPE' => 'SITE.TYPE',
					'SITE_SPECIAL' => 'SITE.SPECIAL',
					'SITE_TITLE' => 'SITE.TITLE',
					'SITE_VERSION' => 'SITE.VERSION',
					'SITE_LANG' => 'SITE.LANG',
					'DOMAIN_ID' => 'SITE.DOMAIN_ID',
					'SITE_LANDING_ID_INDEX' => 'SITE.LANDING_ID_INDEX',
				),
				'filter' => $filter,
			))->fetch();
		}
		// check landing folder if exists
		if (!empty($landing['FOLDER_ID']))
		{
			$breadCrumbs = Folder::getBreadCrumbs($landing['FOLDER_ID'], $landing['SITE_ID']);
			foreach ($breadCrumbs as $crumb)
			{
				if ($crumb['DELETED'] === 'Y')
				{
					$id = 0;
					break;
				}
			}
		}
		if ($id && isset($landing) && is_array($landing))
		{
			/*
			 * $this->getEditMode()
			 * @todo return if no access
			 */
			// get base data
			self::$siteType = (string)$landing['SITE_TYPE'];
			$this->title = $landing['TITLE'];
			$this->code = $landing['CODE'];
			$this->xmlId = $landing['XML_ID'];
			$this->id = (int)$landing['ID'];
			$this->version = (int)$landing['VERSION'];
			$this->siteId = (int)$landing['SITE_ID'];
			$this->siteTitle = $landing['SITE_TITLE'];
			$this->domainId = (int)$landing['DOMAIN_ID'];
			$this->folderId = (int)$landing['FOLDER_ID'];
			$this->active = $landing['ACTIVE'] == 'Y';
			if ($this->checkPermissions)
			{
				$this->rights = Rights::getOperationsForSite(
					$this->siteId
				);
			}
			$this->siteRow = [
				'TPL_ID' => $landing['SITE_TPL_ID'],
				'LANDING_ID_INDEX' => $landing['SITE_LANDING_ID_INDEX'],
			];
			$siteTplId = max($landing['SITE_TPL_ID'], 0);
			$this->tplId = (int)$landing['TPL_ID'] > 0
				? (int)$landing['TPL_ID']
				: $siteTplId
			;
			if (isset($params['is_area']) && $params['is_area'])
			{
				$this->mainInstance = false;
			}
			if ($landing['SITE_TPL_ID'] > 0 && !$landing['TPL_ID'])
			{
				$this->tplType = 'site';
			}
			// if edit mode - create copy for edit
			if ($this->getEditMode()/* && $landing['PUBLIC'] == 'Y'*/)
			{
				Block::cloneForEdit($this);
			}
			// some update if we need
			$this->updateVersion();
			// get available blocks
			if (
				!isset($params['skip_blocks']) ||
				$params['skip_blocks'] !== true
			)
			{
				Block::fillLanding(
					$this,
					$params['blocks_limit'] ?? null,
					[
						'id' => $params['blocks_id'] ?? 0,
						'deleted' => isset($params['deleted']) && $params['deleted'] === true
					]
				);
			}
			// fill meta data
			foreach (self::META_KEYS as $key)
			{
				$this->metaData[$key] = $landing[$key];
			}
		}
		// landing not found
		else
		{
			$this->error->addError(
				'LANDING_NOT_EXIST',
				Loc::getMessage('LANDING_NOT_FOUND')
			);
			$this->title = Loc::getMessage('LANDING_TITLE_NOT_FOUND');
		}
	}

	/**
	 * Can refresh value of modifiable meta data
	 * @param array $metaData
	 * @return void
	 */
	public function setMetaData(array $metaData): void
	{
		foreach ($metaData as $key => $value)
		{
			if (in_array($key, self::META_KEYS_MODIFIABLE, true))
			{
				$this->metaData[$key] = $value;
			}
		}
	}

	/**
	 * Return true if landing exists and available.
	 * @param int $id Landing id.
	 * @param bool $deleted And from recycle bin.
	 * @return bool
	 */
	public static function ping($id, $deleted = false)
	{
		$returnCheckDelete = false;
		$filter = [
			'ID' => $id
		];

		if ($deleted)
		{
			if (self::$checkDelete)
			{
				$returnCheckDelete = true;
				self::$checkDelete = false;
			}
			$filter['=DELETED'] = ['Y', 'N'];
		}

		$check = self::getList([
			'select' => [
				'ID',
			],
				'filter' => $filter,
		]);

		if ($returnCheckDelete)
		{
			self::$checkDelete = true;
		}

		return (boolean) $check->fetch();
	}

	/**
	 * Set work mode to edit.
	 * @param boolean $mode Edit mode.
	 * @return void
	 */
	public static function setEditMode($mode = true)
	{
		self::$editMode = (boolean) $mode;
	}

	/**
	 * Get state of edit mode.
	 * @return boolean
	 */
	public static function getEditMode()
	{
		return self::$editMode;
	}

	/**
	 * Set work mode to preview.
	 * @param boolean $mode Preview mode.
	 * @return void
	 */
	public static function setPreviewMode($mode = true)
	{
		self::$previewMode = (boolean) $mode;
	}

	/**
	 * Get state of preview mode.
	 * @return boolean
	 */
	public static function getPreviewMode()
	{
		return self::$previewMode;
	}

	/**
	 * Check delete pages or not.
	 * @return bool
	 */
	public static function checkDeleted()
	{
		return self::$checkDelete;
	}

	/**
	 * Disable check delete.
	 * @return void
	 */
	public static function disableCheckDeleted()
	{
		self::$checkDelete = false;
	}

	/**
	 * Enable check delete.
	 * @return void
	 */
	public static function enableCheckDeleted()
	{
		self::$checkDelete = true;
	}

	/**
	 * Disable update.
	 * @return void
	 */
	public static function disableUpdate()
	{
		self::$enabledUpdate = false;
	}

	/**
	 * Enable update.
	 * @return void
	 */
	public static function enableUpdate()
	{
		self::$enabledUpdate = true;
	}

	/**
	 * Checking or not unique address.
	 * @return bool
	 */
	public static function isCheckUniqueAddress(): bool
	{
		return self::$checkUniqueAddress;
	}

	/**
	 * Disable checking unique address.
	 * @return void
	 */
	public static function disableCheckUniqueAddress(): void
	{
		self::$checkUniqueAddress = false;
	}

	/**
	 * Enable checking unique address
	 * @return void
	 */
	public static function enableCheckUniqueAddress(): void
	{
		self::$checkUniqueAddress = true;
	}

	/**
	 * Create current instance.
	 * @param int $id Landing id.
	 * @param array $params Additional params.
	 * @return Landing
	 */
	public static function createInstance($id, array $params = array())
	{
		return new self($id, $params);
	}

	/**
	 * Mark landing as deleted.
	 * @param int $id Landing id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markDelete(int $id): \Bitrix\Main\Result
	{
		if (TemplateRef::landingIsArea($id))
		{
			$result = new \Bitrix\Main\Result;
			$result->addError(
				new \Bitrix\Main\Error(
					Loc::getMessage('LANDING_BLOCK_UNABLE_DEL_INC'),
					'UNABLE_DELETE_INCLUDE'
				)
			);
			return $result;
		}

		$event = new Event('landing', 'onBeforeLandingRecycle', array(
			'id' => $id,
			'delete' => 'Y',
		));
		$event->send();

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() == EventResult::ERROR)
			{
				$return = new \Bitrix\Main\Result;
				foreach ($result->getErrors() as $error)
				{
					$return->addError(
						$error
					);
				}
				return $return;
			}
		}

		if (($currentScope = Site\Type::getCurrentScopeId()))
		{
			Agent::addUniqueAgent('clearRecycleScope', [$currentScope]);
		}

		$landing = self::createInstance($id, ['skip_blocks' => true]);

		$res = parent::update($id, array(
			'DELETED' => 'Y',
		));

		if ($res->isSuccess())
		{
			$landing->clearFolderIndex();
		}

		return $res;
	}


	/**
	 * Mark entity as restored.
	 * @param int $id Entity id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markUnDelete($id)
	{
		$id = intval($id);

		$event = new Event('landing', 'onBeforeLandingRecycle', array(
			'id' => $id,
			'delete' => 'N',
		));
		$event->send();

		foreach ($event->getResults() as $result)
		{
			if ($result->getType() == EventResult::ERROR)
			{
				$return = new \Bitrix\Main\Result;
				foreach ($result->getErrors() as $error)
				{
					$return->addError(
						$error
					);
				}
				return $return;
			}
		}

		return parent::update($id, array(
			'DELETED' => 'N',
		));
	}

	/**
	 * Delete landing by id and its blocks.
	 * @param int $id Landing id.
	 * @param bool $forceDeleted Force delete throw an errors.
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id, bool $forceDeleted = false): \Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Entity\DeleteResult();
		$params = [];

		if ($forceDeleted)
		{
			$params['force_deleted'] = true;
		}

		// first check
		if (Rights::isOn())
		{
			foreach (['draft', 'public'] as $code)
			{
				self::setEditMode($code == 'draft');
				$landing = self::createInstance($id, $params);
				if ($landing->exist())
				{
					foreach ($landing->getBlocks() as $block)
					{
						if ($block->getAccess() < $block::ACCESS_X)
						{
							$result->addError(
								new \Bitrix\Main\Error(
									Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED'),
									'ACCESS_DENIED'
								)
							);
							return $result;
						}
					}
				}
				else
				{
					if (!$landing->getError()->isEmpty())
					{
						$result->addError(
							$landing->getError()->getErrors()[0]
						);
					}
					return $result;
				}
			}
		}

		// delete blocks
		$params['skip_blocks'] = true;
		foreach (array('draft', 'public') as $code)
		{
			self::setEditMode($code == 'draft');
			$landing = self::createInstance($id, $params);
			if ($landing->exist())
			{
				Block::deleteAll($id);
				File::deleteFromLanding($id);
				File::deleteFromAsset($id);
			}
		}

		$res = parent::delete($id);
		if ($res->isSuccess())
		{
			$landing->clearFolderIndex();
		}

		return $res;
	}

	/**
	 * Get hooks of Landing.
	 * @param int $id Landing id.
	 * @return array Array of Hook.
	 */
	public static function getHooks($id)
	{
		if (!Rights::hasAccessForLanding($id, Rights::ACCESS_TYPES['read']))
		{
			return [];
		}

		return Hook::getForLanding($id);
	}

	/**
	 * Get additional fields of Landing.
	 * @param int $id Landing id.
	 * @return array Array of Field.
	 */
	public static function getAdditionalFields($id)
	{
		$fields = array();

		// now we can get additional fields only from hooks
		foreach (self::getHooks($id) as $hook)
		{
			$fields += $hook->getPageFields();
		}

		return $fields;
	}

	/**
	 * Returns additional fields of Landing as array.
	 * @param int $landingId Landing id.
	 * @param bool $skipEmpty Skip emty values.
	 * @return array Array of Field.
	 */
	public static function getAdditionalFieldsAsArray(int $landingId, bool $skipEmpty = true): array
	{
		$hookFiles = Hook::HOOKS_CODES_FILES;
		$fields = self::getAdditionalFields($landingId);

		foreach ($fields as $key => $field)
		{
			$fields[$key] = $field->getValue();

			if ($skipEmpty && !$fields[$key])
			{
				unset($fields[$key]);
				continue;
			}

			if (in_array($key, $hookFiles))
			{
				$fields['~' . $key] = $fields[$key];
				if (intval($fields[$key]) > 0)
				{
					$fields[$key] = File::getFilePath($fields[$key]);
				}
			}
		}

		return $fields;
	}

	/**
	 * Save additional fields for Landing.
	 * @param int $id Landing id.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveAdditionalFields($id, array $data)
	{
		// now we can get additional fields only from hooks
		Hook::saveForLanding($id, $data);
	}

	/**
	 * Set external variables of Landing.
	 * @param array $vars Additional vars.
	 * @return void
	 */
	public static function setVariables(array $vars)
	{
		foreach ($vars as $code => $val)
		{
			self::$variables[$code] = $val;
		}
	}

	/**
	 * Get external variables of Landing.
	 * @return array
	 */
	public static function getVariables()
	{
		return self::$variables;
	}

	/**
	 * Set dynamic params (filter id and dynamic element id).
	 * @param int $filterId Id of filter.
	 * @param int $elementId Id of dynamic element id.
	 * @return void
	 */
	public static function setDynamicParams($filterId, $elementId)
	{
		self::$dynamicFilterId = $filterId;
		self::$dynamicElementId = $elementId;
	}

	/**
	 * Get dynamic filter.
	 * @return array
	 */
	public static function getDynamicFilter()
	{
		static $filter = null;
		if ($filter === null)
		{
			$filter = Source\FilterEntity::getFilter(
				self::$dynamicFilterId
			);
		}
		return $filter;
	}

	/**
	 * Get dynamic element id.
	 * @return int
	 */
	public static function getDynamicElementId()
	{
		return self::$dynamicElementId;
	}

	/**
	 * Return true, if current page is dynamic detail page.
	 * @return bool
	 */
	public static function isDynamicDetailPage()
	{
		return self::$dynamicFilterId && self::$dynamicElementId;
	}

	/**
	 * Get preview picture of the landing.
	 * @param int|null $id Landing id (if null, gets for $this->id).
	 * @param bool $skipCloud Skip getting picture from cloud.
	 * @param string|null $publicUrl Full public url of site (you may set this param for optimisation).
	 * @return string|null
	 */
	public function getPreview(?int $id = null, bool $skipCloud = false, ?string $publicUrl = null): ?string
	{
		if (
			!$skipCloud &&
			Manager::isB24() &&
			!Manager::isCloudDisable()
		)
		{
			if (!$publicUrl)
			{
				$publicUrl = $this->getPublicUrl();
			}
			return rtrim($publicUrl, '/') . '/preview.jpg';
		}

		static $hookPics = null;

		if ($hookPics === null)
		{
			$hookPics = Hook\Page\MetaOg::getAllImages();
		}

		if (!$id)
		{
			$id = $this->id;
		}

		if (isset($hookPics[$id]))
		{
			$pic = $hookPics[$id];
			if (intval($pic) > 0)
			{
				$pic = File::getFilePath($pic);
			}
			return $pic;
		}

		return Manager::getUrlFromFile('/bitrix/images/landing/nopreview.jpg');
	}

	/**
	 * Get full pubic URL for this landing.
	 * @param int|array $id Landing id (id array), optional.
	 * @param boolean $absolute Full url.
	 * @param bool $createPubPath Create pub path (checking and create).
	 * @param array &$fullUrl Returns full url of landings.
	 * @return string|array
	 */
	public function getPublicUrl($id = false, $absolute = true, $createPubPath = false, &$fullUrl = [])
	{
		if ($id === false)
		{
			$id = $this->id;
		}

		$previewMode = self::$previewMode && !$this->disableLinkPreview;
		$siteKeyCode = Site\Type::getKeyCode();
		$hostUrl = Domain::getHostUrl();
		$siteId = Manager::getMainSiteId();
		$bitrix24 = Manager::isB24();
		$bitrix24originalVar = $bitrix24;
		$disableCloud = Manager::isCloudDisable();
		$domainDefault = null;
		$data = array();
		$res = Landing::getList(array(
			'select' => array(
				'ID',
				'CODE',
				'RULE',
				'SITE_ID',
				'FOLDER_ID',
				'SITE_ID_INDEX' => 'SITE.LANDING_ID_INDEX',
				'SITE_PROTOCOL' => 'SITE.DOMAIN.PROTOCOL',
				'SITE_DOMAIN' => 'SITE.DOMAIN.DOMAIN',
				'SITE_CODE' => 'SITE.CODE',
				'SITE_TYPE' => 'SITE.TYPE',
				'SITE_SMN_ID' => 'SITE.SMN_SITE_ID',
			),
			'filter' => array(
				'ID' => $id,
				'=DELETED' => ['Y', 'N'],
				'CHECK_PERMISSIONS' => 'N',
			),
		));
		while ($row = $res->fetch())
		{
			if ($row['SITE_TYPE'] == 'SMN')
			{
				$bitrix24 = false;
			}
			else
			{
				$bitrix24 = $bitrix24originalVar;
			}
			$domainReplace = false;
			$row['SITE_ID_ORIG'] = $row['SITE_ID'];
			// build site domain by default
			if (!$row['SITE_DOMAIN'])
			{
				if (!$domainDefault)
				{
					$domainDefault =  Domain::getList(array(
					  	'filter' => array(
							'ID' => Domain::getCurrentId(),
					  	),
					  ))->fetch();
				}
				if (isset($domainDefault['PROTOCOL']))
				{
					$row['SITE_PROTOCOL'] = $domainDefault['PROTOCOL'];
				}
				if (isset($domainDefault['DOMAIN']))
				{
					$row['SITE_DOMAIN'] = $domainDefault['DOMAIN'];
				}
				$domainReplace = true;
			}
			// force https
			if (Manager::isHttps())
			{
				$row['SITE_PROTOCOL'] = 'https';
			}
			if ($domainReplace || !$bitrix24 || $disableCloud)
			{
				$pubPath = Manager::getPublicationPath(
					null,
					$row['SITE_SMN_ID'] ? $row['SITE_SMN_ID'] : $siteId,
					$createPubPath
				);
				$pubPath = rtrim($pubPath, '/');
			}
			// for create publication path
			if (!ModuleManager::isModuleInstalled('bitrix24'))
			{
				Manager::getPublicationPath(
					null,
					$row['SITE_SMN_ID'] ? $row['SITE_SMN_ID'] : $siteId,
					$createPubPath
				);
			}
			if (isset($row['SITE_ID']))
			{
				if ($siteKeyCode == 'CODE')
				{
					$row['SITE_ID'] = $row['SITE_CODE'];
				}
				else
				{
					$row['SITE_ID'] = '/' . $row['SITE_ID'] . '/';
				}
			}
			$publicHash = '';
			if ($previewMode)
			{
				if ($siteKeyCode == 'CODE')
				{
					$publicHash = Site::getPublicHash(trim($row['SITE_CODE'], '/'), $row['SITE_DOMAIN']);
				}
				else
				{
					$publicHash = Site::getPublicHash($row['SITE_ID_ORIG'], $row['SITE_DOMAIN']);
				}
			}
			if ($row['CODE'])
			{
				$row['CODE'] .= '/';
			}
			if ($disableCloud)
			{
				$lastFolderItem = [];
				$fullUrl[$row['ID']] = ($absolute ? $hostUrl : '') .
									$pubPath .
									($bitrix24 ? $row['SITE_ID'] : '/') .
									($previewMode ? 'preview/' . $publicHash . '/' : '') .
									($row['FOLDER_ID'] ? ltrim(Folder::getFullPath($row['FOLDER_ID'], $row['SITE_ID_ORIG'], $lastFolderItem), '/') : '');
				$folderIndex = $row['ID'] == ($lastFolderItem['INDEX_ID'] ?? 0)
												||
											!($lastFolderItem['INDEX_ID'] ?? 0)
											&& ($row['CODE'] ?? null)
											&& ($lastFolderItem['CODE'] ?? null)
											&& trim($row['CODE'], '/') === $lastFolderItem['CODE']
				;
				$data[$row['ID']] = $fullUrl[$row['ID']] .
									(($row['ID'] == $row['SITE_ID_INDEX'] || $folderIndex || $row['RULE']) ? '' : $row['CODE']);
				if (!$row['RULE'])
				{
					$fullUrl[$row['ID']] .= $row['CODE'];
				}
			}
			else
			{
				$lastFolderItem = [];
				$fullUrl[$row['ID']] = (
									$absolute
										? (
											$row['SITE_PROTOCOL'] . '://' .
											$row['SITE_DOMAIN']
										)
										: ''
									) .
									(($domainReplace || !$bitrix24) ? $pubPath : '') .
									(($previewMode && !$bitrix24) ? '/preview/' . $publicHash : '') .
									(($domainReplace && $bitrix24) ? $row['SITE_ID'] : '/') .
									(($previewMode && $bitrix24) ? 'preview/' . $publicHash . '/' : '') .
									($row['FOLDER_ID'] ? ltrim(Folder::getFullPath($row['FOLDER_ID'], $row['SITE_ID_ORIG'], $lastFolderItem), '/') : '');
				$folderIndex = $row['ID'] == $lastFolderItem['INDEX_ID'] || !$lastFolderItem['INDEX_ID'] && trim($row['CODE'], '/') === $lastFolderItem['CODE'];
				$data[$row['ID']] = $fullUrl[$row['ID']] .
									(($row['ID'] == $row['SITE_ID_INDEX'] || $folderIndex || $row['RULE']) ? '' : $row['CODE']);
				if (!$row['RULE'])
				{
					$fullUrl[$row['ID']] .= $row['CODE'];
				}
			}
		}

		if (is_array($id))
		{
			return $data;
		}
		elseif (!empty($data))
		{
			return array_pop($data);
		}

		return false;
	}

	/**
	 * Returns landing id resolves by landing public url.
	 * @param string $landingUrl Landing public url.
	 * @param int $siteId Landing's site id.
	 * @return int|null
	 */
	public static function resolveIdByPublicUrl(string $landingUrl, int $siteId): ?int
	{
		if (Manager::isB24() && !Manager::isCloudDisable() && Site\Type::isPublicScope())
		{
			$landingUrl = rtrim(Manager::getPublicationPath($siteId), '/') . '/' . ltrim($landingUrl, '/');
		}
		$publicationPath = Manager::getPublicationPath();
		$componentName = 'bitrix:landing.pub';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = [
			'PATH' => mb_substr($landingUrl, mb_strlen($publicationPath)),
			'DRAFT_MODE' => 'Y',
			'SITE_ID' => $siteId,
			'SITE_TYPE' => self::getSiteType(),
			'CHECK_PERMISSIONS' => 'N',
			'NOT_CHECK_DOMAIN' => 'Y',
			'NOT_SEND_HTTP_STATUS' => 'Y',
			'SKIP_404' => 'Y',
		];
		return $demoCmp->detectPage() ?: null;
	}

	/**
	 * View landing in public or edit mode.
	 * @param array $params Some additional params.
	 * @return void
	 */
	public function view(array $params = array())
	{
		$blockEditMode = $this->getEditMode();
		$editMode = $this->mainInstance && $blockEditMode;

		if (!isset($params['parse_link']))
		{
			$params['parse_link'] = true;
		}

		if (!isset($params['apply_template']))
		{
			$params['apply_template'] = true;
		}

		if (!isset($params['check_permissions']))
		{
			$params['check_permissions'] = true;
		}

		if (!$params['check_permissions'])
		{
			Rights::setOff();
		}

		// title
		if ($this->mainInstance)
		{
			Manager::setPageTitle(
				\htmlspecialcharsbx($this->title)
			);
		}

		// add chain item if need
		if ($this->mainInstance)
		{
			if ($this->folderId)
			{
				$chains = Folder::getBreadCrumbs($this->folderId, $this->siteId);
				foreach ($chains as $chain)
				{
					Manager::getApplication()->addChainItem(
						$chain['TITLE'],
						$chain['INDEX_ID'] ? '#landing' . $chain['INDEX_ID'] : '#'
					);
				}
			}
			else if (($this->siteRow['LANDING_ID_INDEX'] ?? 0) != $this->id)
			{
				Manager::getApplication()->addChainItem(
					$this->title,
					'#landing' . $this->id
				);
			}
		}

		// assets
		if ($editMode)
		{
			$options = [
				'site_id' => $this->siteId,
				'server_name' => $_SERVER['SERVER_NAME'],
				'xml_id' => $this->xmlId,
				'blocks' => (new BlockRepo())->getRepository(),
				'style' => Block::getStyle(),
				'attrs' => Block::getAttrs(),
				'mainOptions' => [
					'saveOriginalFileName' => Option::get('main', 'save_original_file_name') === 'Y',
				],
			];
			// event for redefine $options
			$event = new Event('landing', 'onLandingView', [
				'options' => $options,
			]);
			$event->send();
			foreach ($event->getResults() as $result)
			{
				if ($result->getResultType() != EventResult::ERROR)
				{
					if (($modified = $result->getModified()))
					{
						if (isset($modified['options']) && is_array($modified['options']))
						{
							$options = array_merge($options, $modified['options']);
						}
					}
				}
			}
			// output js
			Asset::getInstance()->addString(
				'<script>' .
					'BX.ready(function(){'
						. 'if (typeof BX.Landing.Main !== "undefined")'
						. '{'
							. 'BX.Landing.Env.createInstance(' . \CUtil::phpToJSObject($options, false, false, true) . ');'
							. 'BX.Landing.Main.createInstance(' . $this->id . ');'
						. '}'
					. '});' .
				'</script>'
			);
		}

		$content = '';

		// templates part - first
		if ($params['apply_template'] && $this->mainInstance)
		{
			if (!TemplateRef::landingIsArea($this->id))
			{
				$content = $this->applyTemplate();
			}
			else
			{
				$content = '<div class="landing-main"' .
						   		' data-site="' . $this->siteId . '"' .
						   		' data-landing="' . $this->id . '">' .
								'#CONTENT#' .
							'</div>';
			}
		}

		// then content
		ob_start();
		Landing\Seo::beforeLandingView();
		foreach ($this->blocks as $block)
		{
			$block->view($blockEditMode, $this);
		}
		Landing\Seo::afterLandingView();
		if ($this->mainInstance)
		{
			$this->execHooks();
		}
		$contentMain = ob_get_contents();
		ob_end_clean();

		$replace = [];

		if (!$content)
		{
			$content = $contentMain;
		}

		if (mb_strpos($content, '#CONTENT#') !== false)
		{
			$replace['#CONTENT#'] = '<a id="workarea"></a>' . $contentMain;
		}

		if (mb_strpos($content . $contentMain, '#crm') !== false)
		{
			$replace = array_merge($replace, Connector\Crm::getReplacesForContent($this->siteId, !$blockEditMode));
		}

		if ($replace)
		{
			$content = str_replace(
				array_keys($replace),
				array_values($replace),
				$content
			);
		}

		// breadcrumb (see chain_template.php in tpl) and title
		if (!$blockEditMode && $this->mainInstance)
		{
			ob_start();
			echo Manager::getApplication()->getNavChain(
				false, 0, false, true
			);
			$breadcrumb = ob_get_contents();
			ob_end_clean();
			$content = str_replace(
				array(
					'#breadcrumb#',
					'#title#',
				),
				array(
					$breadcrumb,
					Manager::getApplication()->getTitle(),
				),
				$content
			);
		}

		// parse links between landings
		if ($params['parse_link'] === true && !$blockEditMode)
		{
			echo $this->parseLocalUrl($content);
		}
		else
		{
			echo $content;
		}

		if (!$params['check_permissions'])
		{
			Rights::setOn();
		}
	}

	/**
	 * Get included areas of this page.
	 * @return array
	 */
	public function getAreas()
	{
		if ($this->tplType == 'site')
		{
			return TemplateRef::getForSite($this->siteId);
		}
		else
		{
			return TemplateRef::getForLanding($this->id);
		}
	}

	/**
	 * Apply template for this landing.
	 * @param string $content Landing content.
	 * @return string
	 */
	protected function applyTemplate($content = null)
	{
		if ($this->tplId)
		{
			$template = Template::getList(array(
				'filter' => array(
					'ID' => $this->tplId,
				),
			))->fetch();
			if ($template)
			{
				$editMode = $this->getEditMode();
				if ($template['XML_ID'] == 'empty')
				{
					$template['CONTENT'] = '<div class="landing-main">' .
												$template['CONTENT'] .
											'</div>';
				}
				if ($editMode)
				{
					$replace = array(
						'>#CONTENT#<' => ' data-site="' . $this->siteId .
										'" data-landing="' . $this->id .
										'">#CONTENT#<',
						'#CONTENT#' => $content ? $content : '#CONTENT#',
					);
				}
				else
				{
					$replace = array(
						'#CONTENT#' => $content ? $content : '#CONTENT#',
					);
				}
				// if areas exist, get landings
				if ($template['AREA_COUNT'] > 0)
				{
					foreach ($this->getAreas() as $area => $lid)
					{
						ob_start();
						$landing = self::createInstance($lid, array(
							'is_area' => true,
							'check_permissions' => false,
							'disable_link_preview' => $this->disableLinkPreview,
						));
						if ($landing->exist())
						{
							$landing->view();
						}
						if ($editMode)
						{
							$rights = Rights::getOperationsForSite($landing->getSiteId());
							$replace['>#AREA_' . $area . '#<'] = ' data-site="' . $landing->getSiteId() .
																'" data-landing="' . $lid .
																'" data-rights="' . implode(',', $rights) .
																'">#AREA_' . $area . '#<';
						}
						$replace['#AREA_' . $area . '#'] = ob_get_contents();
						ob_end_clean();
					}
				}
				$content = str_replace(
					array_keys($replace),
					array_values($replace),
					$template['CONTENT']
				);
			}
		}
		else if ($this->getEditMode())
		{
			if (!$content)
			{
				$content = '#CONTENT#';
			}
			$content = '<div class="landing-main" ' .
							'data-site="' . $this->siteId . '" ' .
							'data-landing="' . $this->id . '">' .
								$content .
						'</div>';
		}

		return $content;
	}

	/**
	 * Get current template ID
	 * @return int
	 */
	public function getTplId(): int
	{
		return $this->tplId;
	}

	/**
	 * Parse between-landings url in landing content.
	 * @param string $content Landing content.
	 * @return string
	 */
	protected function parseLocalUrl(string $content): string
	{
		$pattern = '/([",\'\;]{1})(page:|block:|user:)?#(landing|block|dynamic|user)([\d\_]+)\@{0,1}([^\'"]*)([",\'\&]{1})/is';
		$patternWithoutUser = '/([",\'\;]{1})(page:|block:)?#(landing|block|dynamic)([\d\_]+)\@{0,1}([^\'"]*)([",\'\&]{1})/is';
		static $isIframe = null;

		if (!self::$editMode && $content)
		{
			$content = Subtype\Form::prepareFormsToView(
				$content
			);
		}

		if ($isIframe === null)
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$isIframe = $request->get('IFRAME') == 'Y';
		}

		// replace catalog links in preview mode
		if (self::$previewMode)
		{
			$content = preg_replace_callback(
				'/href\="(product:)?#catalog(Element|Section)([\d]+)"/i',
				function($href)
				{
					return 'href="' . PublicAction\Utils::getIblockURL(
							$href[3],
							mb_strtolower($href[2])
						) . '"';
				},
				$content);
		}

		$replace = [];

		// for form in frames we should insert hidden tag
		if ($isIframe)
		{
			$replace['</form>'] = '<input type="hidden" name="IFRAME" value="Y" /></form>';
		}

		// fix breadcrumb navigation
		if ($this->siteRow['LANDING_ID_INDEX'] > 0)
		{
			$replace['#system_mainpage'] = '#landing' . $this->siteRow['LANDING_ID_INDEX'];
		}

		if ($replace)
		{
			$content = str_replace(
				array_keys($replace),
				array_values($replace),
				$content
			);
		}

		// replace in content
		if (preg_match_all($pattern, $content, $matches))
		{
			$urls = array(
				'LANDING' => array(),
				'BLOCK' => array(),
				'USER' => array(),
				'DYNAMIC' => array(),
			);
			for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
			{
				if (mb_strtoupper($matches[3][$i]) == 'LANDING')
				{
					$urls['LANDING'][] = $matches[4][$i];
				}
				else if (mb_strtoupper($matches[3][$i]) == 'DYNAMIC')
				{
					[$dynamicId, ] = explode('_', $matches[4][$i]);
					$urls['DYNAMIC'][] = $dynamicId;
				}
				else if (mb_strtoupper($matches[3][$i]) == 'USER')
				{
					$urls['USER'][] = $matches[4][$i];
				}
				else
				{
					$urls['BLOCK'][] = $matches[4][$i];
				}
			}
			// get parent landings for blocks
			// and public version of blocks too
			$anchorsId = array();
			$anchorsPublicId = array();
			if (!empty($urls['BLOCK']))
			{
				$urls['BLOCK'] = Block::getRowByBlockId(
					$urls['BLOCK'],
					array(
						'ID', 'LID', 'PARENT_ID', 'ANCHOR',
					)
				);
				foreach ($urls['BLOCK'] as $bid => &$bidRow)
				{
					if (
						!self::$previewMode &&
						$bidRow['PARENT_ID']
					)
					{
						$anchorsPublicId[$bid] = $bidRow['PARENT_ID'];
					}
					else
					{
						$anchorsId[$bid] = $bidRow['ANCHOR']
											? \htmlspecialcharsbx($bidRow['ANCHOR'])
											: Block::getAnchor($bidRow['ID']);
					}
					$bidRow = $bidRow['LID'];
				}
				unset($bidRow);
				$urls['LANDING'] = array_unique(array_merge(
					$urls['LANDING'],
					$urls['BLOCK']
				));
			}
			$urls['LANDING'] = array_unique(array_merge(
				$urls['LANDING'],
				$urls['DYNAMIC']
			));
			// get anchors for public version
			if ($anchorsPublicId)
			{
				$anchorsPublicIdTmp = Block::getRowByBlockId(
					$anchorsPublicId,
					array(
						'ID', 'LID', 'PARENT_ID', 'ANCHOR',
					)
				);
				foreach ($anchorsPublicId as $bid => $bidParent)
				{
					if (!isset($anchorsPublicIdTmp[$bidParent]))
					{
						continue;
					}
					$bidParent = $anchorsPublicIdTmp[$bidParent];
					$anchorsPublicId[$bid] = $bidParent['ANCHOR']
											? \htmlspecialcharsbx($bidParent['ANCHOR'])
											: Block::getAnchor($bidParent['ID']);
				}
			}
			$anchorsPublicId += $anchorsId;
			$landingFull = [];
			$lidEncoded = [];
			// get landing and blocks urls
			if (!empty($urls['LANDING']))
			{
				$urls['LANDING'] = $this->getPublicUrl(
					$urls['LANDING'],
					!Connector\Mobile::isMobileHit(),
					false,
					$landingFull
				);
				foreach ($urls['LANDING'] as $lid => &$url)
				{
					$lidEncoded[] = $lid;
					$url = \htmlspecialcharsbx($url);
					if ($isIframe)
					{
						$url .= '?IFRAME=Y';
					}
				}
				unset($url);
			}
			if (!empty($urls['BLOCK']))
			{
				foreach ($urls['BLOCK'] as $bid => $lid)
				{
					if (isset($urls['LANDING'][$lid]))
					{
						if (!in_array($lid, $lidEncoded))
						{
							$urls['LANDING'][$lid] = \htmlspecialcharsbx($urls['LANDING'][$lid]);
						}
						$urls['LANDING'][$lid] .= ($isIframe ? '?IFRAME=Y' : '');
						$urls['BLOCK'][$bid] = $urls['LANDING'][$lid] . '#' . $anchorsPublicId[$bid];
					}
					else
					{
						unset($urls['BLOCK'][$bid]);
					}
				}
			}
			// replace urls
			if (!empty($urls['LANDING']))
			{
				krsort($urls['LANDING']);
				$content = preg_replace_callback(
					$patternWithoutUser,
					function($matches) use($urls, $landingFull, $isIframe)
					{
						$dynamicPart = '';
						$matches[3] = mb_strtoupper($matches[3]);
						if ($matches[3] == 'DYNAMIC')
						{
							$matches[3] = 'LANDING';
							if (($underPos = mb_strpos($matches[4], '_')) !== false)
							{
								$dynamicPart = mb_substr($matches[4], $underPos);
								$matches[4] = mb_substr($matches[4], 0, $underPos);
							}
							[$dynamicId, ] = explode('_', $matches[4]);
							$matches[4] = $dynamicId;
						}
						if (isset($urls[$matches[3]][$matches[4]]))
						{
							if ($dynamicPart)
							{
								$landingUrl = $urls[$matches[3]][$matches[4]];
								if (isset($landingFull[$matches[4]]))
								{
									$landingUrl = $landingFull[$matches[4]];
								}
								$url = mb_substr($landingUrl, 0, mb_strlen($landingUrl) - 1);
								$url .= $dynamicPart . ($isIframe ? '/?IFRAME=Y' : '/');
							}
							else
							{
								$url = $urls[$matches[3]][$matches[4]];
							}
							return $matches[1] .
								   		$url . $matches[5] .
									$matches[6];
						}
						else
						{
							return $matches[1] .
										'#landing' . $matches[4] . $matches[5] . $dynamicPart .
									$matches[6];
						}
					},
					$content
				);
				$landingUrls = array();
				foreach ($urls['LANDING'] as $lid => $url)
				{
					$landingUrls['@#landing' . $lid.'@'] = $url;
				}
			}
			// replace user urls
			if (!empty($urls['USER']))
			{
				$patternForPseudoUrlUser = '/data-pseudo-url="{\S*(user:)?#user([\d\_]+)\S*}"/is';
				$content = preg_replace_callback(
					$patternForPseudoUrlUser,
					function($matches)
					{
						$url = "'" . Domain::getHostUrl() . '/company/personal/user/' . $matches[2] . "/'";
						return 'onClick="BX.SidePanel.Instance.open('. $url . ')" ';
					},
					$content
				);
				$patternForUser = '/(user:)?#(user)([\d\_]+)/is';
				$content = preg_replace_callback(
					$patternForUser,
					function($matches)
					{
						return Domain::getHostUrl() . '/company/personal/user/' . $matches[3] . '/';
					},
					$content
				);
			}
		}

		return $content;
	}

	/**
	 * Exec hooks for landing (site and landing).
	 * @return void
	 */
	protected function execHooks()
	{
		$hooksExec = array();

		foreach (Hook::getForSite($this->siteId) as $hook)
		{
			if ($hook->enabled())
			{
				$hook = $this->prepareHook($hook);
				$hooksExec[$hook->getCode()] = $hook;
			}
		}

		foreach (Hook::getForLanding($this->id) as $hook)
		{
			if ($hook->enabled())
			{
				$hooksExec[$hook->getCode()] = $hook;
			}
		}

		foreach ($hooksExec as $hook)
		{
			if (
				!$this->getEditMode() ||
				$hook->enabledInEditMode()
			)
			{
				$hook->exec();
			}
		}
	}

	protected function prepareHook($hook)
	{
		if ($hook->getCode() === 'GMAP')
		{
			$hook->setSiteId($this->siteId);
		}

		return $hook;
	}

	/**
	 * Exist or not landing in current instance.
	 * @return boolean
	 */
	public function exist()
	{
		return $this->id > 0;
	}

	/**
	 * Active or not the landing.
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->active;
	}

	/**
	 * Get id of current landing.
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get xml id of current landing.
	 * @return int
	 */
	public function getXmlId()
	{
		return $this->xmlId;
	}

	/**
	 * Get title of current landing.
	 * @return int
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns code of current landing.
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * Get metadata of current landing.
	 * @return array
	 */
	public function getMeta()
	{
		return $this->metaData;
	}

	/**
	 * Can current user edit this landing.
	 * @return bool
	 */
	public function canEdit()
	{
		if (!$this->checkPermissions)
		{
			return true;
		}
		return in_array(Rights::ACCESS_TYPES['edit'], $this->rights);
	}

	/**
	 * Can current user publication this landing.
	 * @return bool
	 */
	public function canPublication()
	{
		if (!$this->checkPermissions)
		{
			return true;
		}
		return in_array(Rights::ACCESS_TYPES['public'], $this->rights);
	}

	/**
	 * Can current user delete this landing.
	 * @return bool
	 */
	public function canDelete()
	{
		if (!$this->checkPermissions)
		{
			return true;
		}
		return in_array(Rights::ACCESS_TYPES['delete'], $this->rights);
	}

	/**
	 * Gets folder id of current landing.
	 * @return int
	 */
	public function getFolderId()
	{
		return $this->folderId;
	}

	/**
	 * Get site id of current landing.
	 * @return int
	 */
	public function getSiteId()
	{
		return $this->siteId;
	}

	/**
	 * Get site title of current landing.
	 * @return string
	 */
	public function getSiteTitle()
	{
		return $this->siteTitle;
	}

	/**
	 * Gets domain id of landing site.
	 * @return int
	 */
	public function getDomainId()
	{
		return $this->domainId;
	}

	/**
	 * Get site type of current landing.
	 * @return string
	 */
	public static function getSiteType(): string
	{
		return self::$siteType;
	}

	/**
	 * Get site id of main, if exist.
	 * @return string|null
	 */
	public function getSmnSiteId(): ?string
	{
		if ($this->siteId)
		{
			$res = Site::getList(array(
				'select' => array(
					'SMN_SITE_ID',
				),
				'filter' => array(
					'ID' => $this->siteId,
				),
			));
			if ($row = $res->fetch())
			{
				return $row['SMN_SITE_ID'];
			}
		}

		return null;
	}

	/**
	 * Get all blocks of current landing.
	 * @return Block[]
	 */
	public function getBlocks()
	{
		return $this->blocks;
	}

	/**
	 * Get the block by id of current landing.
	 * @param int $id Block id.
	 * @return Block
	 */
	public function getBlockById($id)
	{
		$id = intval($id);
		return isset($this->blocks[$id])
				? $this->blocks[$id]
				: null;
	}

	/**
	 * Add new Block to the current landing.
	 * @param \Bitrix\Landing\Block $block New block instance.
	 * @return void
	 */
	public function addBlockToCollection(\Bitrix\Landing\Block $block)
	{
		if ($block->exist())
		{
			$this->blocks[$block->getId()] = $block;
		}
	}

	/**
	 * Get error collection
	 * @return \Bitrix\Landing\Error
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Returns Error instance as Main\Error instance.
	 * @return \Bitrix\Main\Error|null
	 */
	public function getErrorMain(): ?\Bitrix\Main\Error
	{
		if ($this->error)
		{
			$error = $this->error->getFirstError();
			return new \Bitrix\Main\Error(
				$error->getMessage(),
				$error->getCode()
			);
		}
		return null;
	}

	/**
	 * Change modified user and date for current landing.
	 * @return void
	 */
	public function touch()
	{
		static $touched = [];

		if (isset($touched[$this->id]))
		{
			return;
		}

		$touched[$this->id] = true;

		if (self::update($this->id, ['PUBLIC' => 'N'])->isSuccess())
		{
			Site::touch($this->siteId);
		}
	}

	/**
	 * If current version is not actual, making update.
	 * @return void
	 */
	public function updateVersion()
	{
		$needUpdate = false;
		// double hooks: public and draft
		if ($this->version <= 1)
		{
			$needUpdate = true;
			$this->version = 2;
			$hookEditMode = Hook::getEditMode();
			if (!$hookEditMode)
			{
				Hook::setEditMode(true);
			}
			Hook::publicationSite($this->siteId);
			Hook::publicationLanding($this->id);
			if (!$hookEditMode)
			{
				Hook::setEditMode(false);
			}
		}
		// block assets
		if ($this->version <= 2)
		{
			$needUpdate = true;
			$this->version = 3;
			Assets\PreProcessing\Icon::processingLanding($this->id);
			Assets\Manager::rebuildWebpackForLanding($this->id);
		}
		if ($this->version <= 3)
		{
			$needUpdate = true;
			$this->version = 4;
			Assets\PreProcessing\Font::processingLanding($this->id);
		}
		if ($this->version <= 4)
		{
			$needUpdate = true;
			$this->version = 5;
			Hook\Page\ThemeFonts::migrateFromTypoThemes($this->id, $this->siteId);
		}
		if ($this->version <= 5)
		{
			// fix 126641
			$needUpdate = true;
			$this->version = 6;
			Assets\PreProcessing\Icon::processingLanding($this->id);
			Assets\Manager::rebuildWebpackForLanding($this->id);
		}
		if ($this->version <= 6)
		{
			$needUpdate = true;
			Update\Block\Buttons::updateLanding($this->id);
			Update\Block\FontWeight::updateLanding($this->id);
			$this->version = 7;
		}
		if ($this->version <= 7)
		{
			$needUpdate = true;
			Assets\PreProcessing\Icon::processingLanding($this->id);
			Assets\Manager::rebuildWebpackForLanding($this->id);
			$this->version = 8;
		}
		if ($this->version <= 9)
		{
			// +1 version for reupdate in hotfix
			$needUpdate = true;
			Subtype\Form::updateLandingToEmbedForms($this->id);
			Assets\Manager::rebuildWebpackForLanding($this->id);
			$this->version = 10;
		}
		if ($this->version <= 10)
		{
			$needUpdate = true;
			Update\Block\DuplicateImages::updateLanding($this->id);

			$this->version = 11;
		}
		if ($needUpdate)
		{
			Rights::setOff();
			self::update($this->id, [
				'VERSION' => $this->version,
				'DATE_MODIFY' => false,
				'MODIFIED_BY_ID' => false,
			]);
			Rights::setOn();
		}
	}

	/**
	 * Publication current landing.
	 * @param int|int[]|null $blockId Publication only this block(s).
	 * @return boolean
	 */
	public function publication($blockId = null): bool
	{
		if ($this->canPublication())
		{
			return Mutator::landingPublication($this, $blockId);
		}
		return false;
	}

	/**
	 * Fake current landing (for testing potential publication and resolves the errors).
	 * @return boolean
	 */
	public function fakePublication(): bool
	{
		if ($this->canPublication())
		{
			return Mutator::landingPublication($this, null, true);
		}
		return false;
	}

	/**
	 * Cancel publication of landing.
	 * @return boolean
	 */
	public function unpublic()
	{
		static $siteUpdated = [];

		$res = parent::update($this->id, array(
			'ACTIVE' => 'N',
			'PUBLIC' => 'N',
			'DATE_MODIFY' => false,
		));
		if ($res->isSuccess())
		{
			if (
				!in_array($this->siteId, $siteUpdated) &&
				Manager::isB24()
			)
			{
				$siteUpdated[] = $this->siteId;
				Site::update($this->siteId, array());
			}
			// send event
			$event = new Event('landing', 'onLandingAfterUnPublication', array(
				'id' => $this->getId(),
			));
			$event->send();
			return true;
		}
		else
		{
			$this->error->addFromResult($res);
			return false;
		}
	}

	/**
	 * Add new block to the landing.
	 * @param string $code Code of block.
	 * @param array $data Data array of block.
	 * @param bool $saveInLastUsed Save this block as last used for current user.
	 * @return int|false Id of new block or false on failure.
	 */
	public function addBlock(string $code, array $data = array(), bool $saveInLastUsed = false)
	{
		if (!$this->canEdit())
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		if (!isset($data['PUBLIC']))
		{
			$data['PUBLIC'] = $this::$editMode ? 'N' : 'Y';
		}

		$block = Block::createFromRepository($this, $code, $data);

		if ($block)
		{
			if ($saveInLastUsed)
			{
				Block::markAsUsed($code);
			}

			$this->touch();
			$this->addBlockToCollection($block);

			if (History::isActive())
			{
				$history = new History($this->id, History::ENTITY_TYPE_LANDING);
				$history->push('ADD_BLOCK', ['block' => $block]);
			}

			return $block->getId();
		}

		return false;
	}

	/**
	 * Delete one block from current landing.
	 * @param int $id Block id.
	 * @return boolean
	 */
	public function deleteBlock($id)
	{
		$id = intval($id);
		if (isset($this->blocks[$id]))
		{
			$result = $this->blocks[$id]->unlink();
			$this->error->copyError(
				$this->blocks[$id]->getError()
			);
			if ($result)
			{
				unset($this->blocks[$id]);
			}
			$this->touch();
			return $result;
		}
		else
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			return false;
		}
	}

	/**
	 * Mark delete or not the block.
	 * @param int $id Block id.
	 * @param boolean $mark Mark.
	 * @return boolean
	 */
	public function markDeletedBlock($id, $mark)
	{
		$id = intval($id);
		if (!isset($this->blocks[$id]))
		{
			$this->blocks[$id] = new Block($id);
		}

		if (
			isset($this->blocks[$id]) &&
			$this->blocks[$id]->exist() &&
			$this->blocks[$id]->getLandingId() == $this->getId()
		)
		{
			if ($this->blocks[$id]->getAccess() >= $this->blocks[$id]::ACCESS_X)
			{
				$this->blocks[$id]->markDeleted($mark);
				if (!$mark)
				{
					Assets\PreProcessing::blockUndeleteProcessing(
						$this->blocks[$id]
					);
				}
				if ($this->blocks[$id]->save())
				{
					if ($mark)
					{
						if (History::isActive())
						{
							$history = new History($this->id, History::ENTITY_TYPE_LANDING);
							$history->push('REMOVE_BLOCK', ['block' => $this->blocks[$id]]);
						}

						unset($this->blocks[$id]);
					}
					else
					{
						$this->addBlockToCollection(
							$this->blocks[$id]
						);
					}
					$this->touch();
					return true;
				}
				else
				{
					$this->error->copyError(
						$this->blocks[$id]->getError()
					);
				}
			}
			else
			{
				$this->error->addError(
					'ACCESS_DENIED',
					Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
				);
				return false;
			}
		}
		else
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			return false;
		}

		return false;
	}

	/**
	 * Transfer one block to another landing.
	 * @param int $id Block id.
	 * @param int $lid Landing id.
	 * @return boolean
	 */
	protected function transferBlock($id, $lid)
	{
		$id = intval($id);
		if (isset($this->blocks[$id]))
		{
			$result = $this->blocks[$id]->changeLanding($lid);
			$this->error->copyError($this->blocks[$id]->getError());
			if ($result)
			{
				unset($this->blocks[$id]);
			}
			return $result;
		}
		else
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			return false;
		}
	}

	/**
	 * Resort current blocks.
	 * @return void
	 */
	public function resortBlocks()
	{
		uasort($this->blocks, function($a, $b)
		{
			if ($a->getSort() == $b->getSort())
			{
				return ($a->getId() < $b->getId()) ? -1 : 1;
			}
			return ($a->getSort() < $b->getSort()) ? -1 : 1;
		});
		$sort = 0;
		foreach ($this->blocks as $id => $block)
		{
			$block->saveSort($sort);
			$sort += 500;
		}
	}

	/**
	 * Sort the block on the landing.
	 * @param int $id Block id.
	 * @param string $action Code: up or down.
	 * @return boolean
	 */
	protected function sortBlock(int $id, string $action): bool
	{
		$id = intval($id);
		if (isset($this->blocks[$id]))
		{
			$blocks = array_keys($this->blocks);
			for ($i = 0, $c = count($blocks); $i < $c; $i++)
			{
				if ($blocks[$i] == $id)
				{
					// change sort between two blocks
					$targetKey = $i + ($action === 'up' ? -1 : 1);
					if (isset($blocks[$targetKey]))
					{
						$thisBlock = $this->blocks[$id];
						$targetBlock = $this->blocks[$blocks[$targetKey]];
						$thisBlockSort = $thisBlock->getSort();
						$targetBlockSort = $targetBlock->getSort();

						$thisBlock->setSort($targetBlockSort);
						$targetBlock->setSort($thisBlockSort);
						$res1 = $thisBlock->save();
						$res2 = $targetBlock->save();

						$this->error->copyError($thisBlock->getError());
						$this->error->copyError($targetBlock->getError());

						if ($res1 || $res2)
						{
							$this->touch();
						}

						return $res1 && $res2;
					}

					$this->error->addError(
						'BLOCK_WRONG_SORT',
						Loc::getMessage('LANDING_BLOCK_WRONG_SORT')
					);

					return false;
				}
			}
		}
		else
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
		}

		return false;
	}

	/**
	 * Sort up the block on the landing.
	 * @param int $id Block id.
	 * @return boolean
	 */
	public function upBlock(int $id): bool
	{
		if ($this->sortBlock($id, 'up'))
		{
			if (History::isActive())
			{
				$history = new History($this->id, History::ENTITY_TYPE_LANDING);
				$history->push('SORT_BLOCK', [
					'block' => $id,
					'lid' => $this->getId(),
					'up' => true,
				]);
			}

			return true;
		}

		return false;
	}

	/**
	 * Sort down the block on the landing.
	 * @param int $id Block id.
	 * @return boolean
	 */
	public function downBlock(int $id): bool
	{
		if ($this->sortBlock($id, 'down'))
		{
			if (History::isActive())
			{
				$history = new History($this->id, History::ENTITY_TYPE_LANDING);
				$history->push('SORT_BLOCK', [
					'block' => $id,
					'lid' => $this->getId(),
					'up' => false,
				]);
			}

			return true;
		}

		return false;
	}

	/**
	 * Show/hide the block on the landing.
	 * @param int $id Block id.
	 * @param string $action Code: up or down.
	 * @return boolean
	 */
	protected function activateBlock($id, $action)
	{
		$id = intval($id);
		if (isset($this->blocks[$id]))
		{
			if ($this->blocks[$id]->setActive($action == 'show'))
			{
				if ($res = $this->blocks[$id]->save())
				{
					$this->touch();
				}
			}
			$this->error->copyError($this->blocks[$id]->getError());
			return $res;
		}
		else
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			return false;
		}
	}

	/**
	 * Activate the block on the landing.
	 * @param int $id Block id.
	 * @return boolean
	 */
	public function showBlock($id)
	{
		return $this->activateBlock($id, 'show');
	}

	/**
	 * Dectivate the block on the landing.
	 * @param int $id Block id.
	 * @return boolean
	 */
	public function hideBlock($id)
	{
		return $this->activateBlock($id, 'hide');
	}

	/**
	 * Saves the block in favorites.
	 * @param int $id Block id.
	 * @param array $meta Meta info.
	 * @return int|null New block id.
	 */
	public function favoriteBlock(int $id, array $meta = []): ?int
	{
		$bewBlockId = $this->copyBlock($id, $id);
		if ((int)$bewBlockId > 0)
		{
			if (
				$this->blocks[$bewBlockId]->changeLanding(0) &&
				$this->blocks[$bewBlockId]->changeFavoriteMeta($meta)
			)
			{
				Block::markAsUsed($this->blocks[$bewBlockId]->getCode() . '@' . $bewBlockId);
				\Bitrix\Landing\Block::clearRepositoryCache();
				if ($meta['preview'] ?? null)
				{
					File::deleteFromBlock($id, $meta['preview']);
				}
			}
			else
			{
				$this->error->copyError($this->blocks[$bewBlockId]->getError());
			}
			return $bewBlockId;
		}

		return null;
	}

	/**
	 * Removes the block from favorites.
	 * @param int $blockId Block id.
	 * @return bool
	 */
	public function unFavoriteBlock(int $blockId): bool
	{
		$block = new Block($blockId);
		if (!$block || empty($block->getMeta()['FAVORITE_META']))
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
			return false;
		}

		if (
			$block->getAccess() < Block::ACCESS_X
			|| (int)$block->getMeta()['CREATED_BY_ID'] !== Manager::getUserId()
		)
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_BLOCK_ACCESS_DENIED')
			);
			return false;
		}

		if (BlockTable::delete($blockId)->isSuccess())
		{
			File::deleteFromBlock($blockId);
			Block::removeAsUsed($block->getCode() . '@' . $block->getId());
			Block::clearRepositoryCache();
			return true;
		}

		return false;
	}

	/**
	 * Copy/move other block to this landing.
	 * @param int $block Block id.
	 * @param array $params Params array.
	 * @return int|null New Block id.
	 */
	protected function changeParentOfBlock(int $block, array $params): ?int
	{
		$block = intval($block);
		$move = isset($params['MOVE']) && $params['MOVE'];
		$afterId = isset($params['AFTER_ID']) ? (int)$params['AFTER_ID'] : 0;
		$fromLandingRow = Block::getRowByBlockId($block, ['ID', 'LID', 'SITE_TYPE' => 'LANDING.SITE.TYPE']);
		$fromLandingId = $fromLandingRow['LID'] ?? null;
		$currentScopeId = Site\Type::getCurrentScopeId();
		$same = $this->id == $fromLandingId;
		$newBlock = null;

		if ($currentScopeId !== $fromLandingRow['SITE_TYPE'])
		{
			Site\Type::setScope($fromLandingRow['SITE_TYPE']);
		}

		if ($same)
		{
			$fromLanding = clone $this;
		}
		else
		{
			$fromLanding = self::createInstance($fromLandingId);
		}

		// if landing exist and available, get it blocks
		if ($this->exist() && $fromLanding->exist())
		{
			$fromLandingBlocks = $fromLanding->getBlocks();
			// if move, just change landing id
			if ($move)
			{
				$res = $fromLanding->transferBlock($block, $this->id);
				$this->error->copyError($fromLanding->getError());
				if ($res)
				{
					$newBlock = $fromLandingBlocks[$block];
				}
			}
			// else create copy
			else if (isset($fromLandingBlocks[$block]))
			{
				$srcBlock = $fromLandingBlocks[$block];
				$newBlock = Block::createFromRepository(
					$this,
					$srcBlock->getCode(),
					array(
						'ACTIVE' => $srcBlock->isActive() ? 'Y' : 'N',
						'DESIGNED' => $srcBlock->isDesigned() ? 'Y' : 'N',
						'ACCESS' => $srcBlock->getAccess(),
						'SORT' => $srcBlock->getSort(),
						'CONTENT' => $srcBlock->getContent(),
						'SOURCE_PARAMS' => $srcBlock->getDynamicParams(),
						'PUBLIC' => 'N',
				));
				if ($newBlock)
				{
					// we should save original content after all callbacks
					$newBlock->saveContent(
						$srcBlock->getContent()
					);
					$newBlock->save();
					// copy files
					if ($newBlock)
					{
						File::copyBlockFiles(
							$srcBlock->getId(),
							$newBlock->getId()
						);
					}
				}
			}
			// add block to collection and resort
			if ($newBlock)
			{
				if ($afterId > 0 && isset($this->blocks[$afterId]))
				{
					$targetBlock = $this->blocks[$afterId];
				}
				else
				{
					$blocksTmp = array_values($this->blocks);
					$targetBlock = array_pop($blocksTmp);
				}
				if ($targetBlock)
				{
					$newBlock->setSort($targetBlock->getSort() + 1);
				}
				$this->addBlockToCollection($newBlock);
				$this->resortBlocks();
				// search index
				$newBlock->save();
			}
			//change dates
			if ($this->error->isEmpty())
			{
				if ($move && !$same)
				{
					$fromLanding->touch();
				}
			}
		}

		$this->error->copyError($fromLanding->getError());

		if ($currentScopeId !== $fromLandingRow['SITE_TYPE'])
		{
			Site\Type::setScope($currentScopeId);
		}

		if ($this->error->isEmpty())
		{
			$this->touch();
		}

		return $newBlock ? $newBlock->getId() : null;
	}

	/**
	 * Copy other block to this landing.
	 * @param int $id Block id (from another landing).
	 * @param int $afterId Put after this block id (in this landing).
	 * @return int|null New Block id.
	 */
	public function copyBlock(int $id, int $afterId): ?int
	{
		$blockId = $this->changeParentOfBlock($id, [
			'MOVE' => false,
			'AFTER_ID' => $afterId,
		]);
		if (!$blockId)
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
		}

		return $blockId;
	}

	/**
	 * Copy all blocks from another landing to this.
	 * @param int $lid Landing id.
	 * @param boolean $replaceLinks Replace links to the old blocks.
	 * @param array &$references Array that will contain references between old and new IDs.
	 * @return void
	 */
	public function copyAllBlocks($lid, $replaceLinks = true, array &$references = [])
	{
		$landing = self::createInstance($lid);

		if ($this->exist() && $landing->exist())
		{
			$oldNew = array();
			// copy blocks
			foreach ($landing->getBlocks() as $block)
			{
				$newBlock = Block::createFromRepository(
					$this,
					$block->getCode(),
					array(
						'ACTIVE' => $block->isActive() ? 'Y' : 'N',
						'DESIGNED' => $block->isDesigned() ? 'Y' : 'N',
						'PUBLIC' => $block->isPublic() ? 'Y' : 'N',
						'ACCESS' => $block->getAccess(),
						'SORT' => $block->getSort(),
						'CONTENT' => $block->getContent(),
						'SOURCE_PARAMS' => $block->getDynamicParams(),
					));
				if ($newBlock)
				{
					$oldNew[$block->getId()] = $newBlock;
					$references[$block->getId()] = $newBlock->getId();
					$this->addBlockToCollection($newBlock);
				}
			}
			// replace old id of blocks to the new one and clone files
			foreach ($this->getBlocks() as $block)
			{
				$content = $block->getContent();
				foreach ($oldNew as $oldId => $newBlock)
				{
					// clone files
					File::addToBlock(
						$newBlock->getId(),
						File::getFilesFromBlockContent($oldId, $content)
					);
					// replace ids
					if ($replaceLinks)
					{
						$content = str_replace(
							'#block' . $oldId,
							'#block' . $newBlock->getId(),
							$content
						);
						$block->saveContent($content);
					}
					$block->save();
				}
			}
			$this->touch();
		}

		$this->error->copyError($this->getError());
		$this->error->copyError($landing->getError());
	}

	/**
	 * If current landing is index page of folder, clear folder's index.
	 * @return void
	 */
	public function clearFolderIndex(): void
	{
		if ($this->folderId)
		{
			$resFolder = Folder::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'ID' => $this->folderId,
					'INDEX_ID' => $this->id,
				],
			]);
			if ($resFolder->fetch())
			{
				Folder::update($this->folderId, [
					'INDEX_ID' => null,
				]);
			}
		}
	}

	/**
	 * System method for checking ability to publication page after copy/move.
	 *
	 * @return bool
	 */
	private function canPublicAfterCopy(): bool
	{
		$siteId = $this->getSiteId();
		$folderId = $this->getFolderId();

		// if permissions enough
		if (!$this->canPublication())
		{
			return false;
		}

		// in root, we can
		if (!$folderId)
		{
			return true;
		}

		// check all folders above the page
		$crumbs = Folder::getBreadCrumbs($folderId, $siteId);
		foreach ($crumbs as $crumb)
		{
			// if folder is active, we don't care about
			if ($crumb['ACTIVE'] === 'Y')
			{
				continue;
			}

			// check active pages in each folder above
			$res = self::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'=ACTIVE' => 'Y',
					'FOLDER_ID' => $crumb['ID'],
				],
				'limit' => 1,
			]);
			if ($res->fetch())
			{
				// if such folder exists we cant public any folder
				return false;
			}

			// check active folders in folders above
			$res = Folder::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'=ACTIVE' => 'Y',
					'PARENT_ID' => $crumb['ID'],
				],
				'limit' => 1,
				]);
			if ($res->fetch())
			{
				// if such folder exists we cant public any folder
				return false;
			}
		}

		// all folders are active or not exists active pages
		return true;
	}

	/**
	 * Move current page to site/folder.
	 * @param int|null $toSiteId Destination site id (if you want copy in another site).
	 * @param int|null $toFolderId Destination folder id.
	 * @return bool
	 */
	public function move(?int $toSiteId = null, ?int $toFolderId = null): bool
	{
		if (!$this->exist())
		{
			return false;
		}

		if (!$toSiteId)
		{
			$toSiteId = $this->getSiteId();
		}

		$rightsSite = Rights::getOperationsForSite($toSiteId);
		if (!in_array(Rights::ACCESS_TYPES['edit'], $rightsSite))
		{
			$this->error->addError(
				'ACCESS_DENIED',
				Loc::getMessage('LANDING_SITE_ACCESS_DENIED')
			);
			return false;
		}

		if (!$this->canDelete())
		{
			$this->error->addError(
				'DELETE_ACCESS_DENIED',
				Loc::getMessage('LANDING_DELETE_ACCESS_DENIED')
			);
			return false;
		}

		$result = self::update($this->id, [
			'ACTIVE' => 'N',
			'PUBLIC' => 'N',
			'CODE' => $this->code,
			'SITE_ID' => $toSiteId,
			'FOLDER_ID' => $toFolderId,
		]);

		if ($result->isSuccess())
		{
			$this->clearFolderIndex();
			if ($this->active && $this->canPublicAfterCopy($toFolderId))
			{
				$this->publication();
			}
		}

		$this->error->addFromResult($result);

		return $result->isSuccess();
	}

	/**
	 * Copy current landing.
	 * @param int|null $toSiteId Site id (if you want copy in another site).
	 * @param int|null $toFolderId Folder id (if you want copy in some folder).
	 * @param bool $withoutBlocks Copy only pages, without blocks.
	 * @param bool $skipSystem If true, don't copy system flag.
	 * @return int|null New landing id.
	 */
	public function copy(?int $toSiteId = null, ?int $toFolderId = null, bool $withoutBlocks = false, bool $skipSystem = false): ?int
	{
		if ($this->exist())
		{
			$landingRow = Landing::getList([
				'filter' => [
					'ID' => $this->id,
				],
			])->fetch();
			$folderId = null;
			if (!$toSiteId)
			{
				$toSiteId = $this->getSiteId();
			}
			if ($toFolderId !== null)
			{
				$folderId = $toFolderId;
			}
			// check if folder in the same site
			if ($folderId)
			{
				$folderRow = Site::getFolder($folderId);
				if (intval($folderRow['SITE_ID'] ?? null) !== $toSiteId)
				{
					$folderId = null;
				}
			}
			// add 'copy' to new page?
			$addCopyMark = !!Landing::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'=TITLE' => $landingRow['TITLE'],
					'=DELETED' => 'N',
					'FOLDER_ID' => $folderId,
					'SITE_ID' => $toSiteId,
				],
			])->fetch();
			// create new page
			$res = Landing::add([
				'CODE' => $landingRow['CODE'],
				'ACTIVE' => 'N',
				'PUBLIC' => 'N',
				'TITLE' => $addCopyMark
							? $landingRow['TITLE'] . ' ' . Loc::getMessage('LANDING_COPY_SUFFIX')
							: $landingRow['TITLE'],
				'SYS' => $skipSystem ? 'N' : $landingRow['SYS'],
				'XML_ID' => $landingRow['XML_ID'],
				'TPL_CODE' => $landingRow['TPL_CODE'],
				'INITIATOR_APP_CODE' => $landingRow['INITIATOR_APP_CODE'],
				'DESCRIPTION' => $landingRow['DESCRIPTION'],
				'TPL_ID' => $landingRow['TPL_ID'],
				'SITE_ID' => $toSiteId,
				'SITEMAP' => $landingRow['SITEMAP'],
				'FOLDER_ID' => $folderId,
				'RULE' => '',
			]);
			// landing allready create, just copy the blocks
			if ($res->isSuccess())
			{
				Landing::setEditMode();
				$landingNew = Landing::createInstance($res->getId());
				if ($landingNew->exist())
				{
					if (!$withoutBlocks)
					{
						$landingNew->copyAllBlocks($this->id);
					}
					// copy hook data
					Hook::copyLanding(
						$this->id,
						$landingNew->getId()
					);
					// copy files
					File::copyLandingFiles(
						$this->id,
						$landingNew->getId()
					);
					// copy template refs
					if (($refs = TemplateRef::getForLanding($this->id)))
					{
						TemplateRef::setForLanding($landingNew->getId(), $refs);
					}
					// publication if needed
					if ($landingRow['ACTIVE'] === 'Y' && $landingNew->canPublicAfterCopy($toFolderId))
					{
						$landingNew->publication();
					}
					return $landingNew->getId();
				}
				$this->error->copyError($landingNew->getError());
			}
			else
			{
				$this->error->addFromResult($res);
			}
		}

		return null;
	}

	/**
	 * Move other block to this landing.
	 * @param int $id Block id (from another landing).
	 * @param int $afterId Put after this block id (in this landing).
	 * @return int|null New Block id.
	 */
	public function moveBlock(int $id, int $afterId): ?int
	{
		$blockId = $this->changeParentOfBlock($id, [
			'MOVE' => true,
			'AFTER_ID' => $afterId,
		]);
		if (!$blockId)
		{
			$this->error->addError(
				'BLOCK_NOT_FOUND',
				Loc::getMessage('LANDING_BLOCK_NOT_FOUND')
			);
		}

		return $blockId;
	}

	/**
	 * Update the landing.
	 * @param int $id Landing id.
	 * @param array $fields Fields.
	 * @return \Bitrix\Main\Result
	 */
	public static function update($id, $fields = array())
	{
		if (self::$enabledUpdate)
		{
			return parent::update($id, $fields);
		}
		else
		{
			return new \Bitrix\Main\Result;
		}
	}

	/**
	 * Creates new page in the site by template.
	 * @param int $siteId Site id.
	 * @param string $code Template code.
	 * @param array $fields Landing fields.
	 * @return \Bitrix\Main\Entity\AddResult
	 */
	public static function addByTemplate(int $siteId, string $code, array $fields = []): \Bitrix\Main\Entity\AddResult
	{
		$result = new \Bitrix\Main\Entity\AddResult;

		// get type by siteId
		$res = Site::getList([
			'select' => [
				'TYPE',
			],
			'filter' => [
				'ID' => $siteId,
			],
		]);
		if (!($site = $res->fetch()))
		{
			$result->addError(new \Bitrix\Main\Error(
				  'SITE_ERROR',
				  Loc::getMessage('LANDING_SITE_ERROR')
			  ));
			return $result;
		}

		// get landing if specified
		if ($fields['ID'] ?? null)
		{
			$res = self::getList([
				'select' => [
					'ID', 'FOLDER_ID', 'FOLDER', 'ACTIVE',
				],
				'filter' => [
					'SITE_ID' => $siteId,
					'ID' => $fields['ID'],
				],
			]);
			if (!($landing = $res->fetch()))
			{
				$result->addError(new \Bitrix\Main\Error(
					'LANDING_ERROR',
					Loc::getMessage('LANDING_NOT_FOUND')
				));
				return $result;
			}
			if ($landing['FOLDER'] === 'Y')
			{
				$landing['FOLDER_ID'] = $landing['ID'];
			}
		}

		// include the component
		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		/** @var \LandingSiteDemoComponent $demoCmp */
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = [
			'TYPE' => $fields['SITE_TYPE'] ?? (($site['TYPE'] == 'STORE' || $site['TYPE'] == 'SMN') ? 'PAGE' : $site['TYPE']),
			'SITE_ID' => $siteId,
			'SITE_WORK_MODE' => 'N',
			'DISABLE_REDIRECT' => 'Y',
			'DONT_LEAVE_FRAME' => 'N',
			'FOLDER_ID' => $landing['FOLDER_ID'] ?? $fields['FOLDER_ID'] ?? 0,
			'META' => $fields,
		];
		if (
			isset($fields['PREPARE_BLOCKS'], $fields['PREPARE_BLOCKS_DATA'])
			&& $fields['PREPARE_BLOCKS'] === true
			&& is_array($fields['PREPARE_BLOCKS_DATA'])
		)
		{
			$demoCmp->arParams['PREPARE_BLOCKS_DATA'] = $fields['PREPARE_BLOCKS_DATA'];
		}

		// ... and create the page by component's method
		$landingId = $demoCmp->createPage($siteId, $code);

		// prepare success or failure
		if ($landingId)
		{
			$result->setId($landingId);
			if (($landing['ACTIVE'] ?? 'N') === 'Y')
			{
				Landing::createInstance($landingId)->publication();
			}
		}
		else
		{
			foreach ($demoCmp->getErrors() as $code => $title)
			{
				$result->addError(new \Bitrix\Main\Error($code, $title));
			}
		}

		return $result;
	}
}
