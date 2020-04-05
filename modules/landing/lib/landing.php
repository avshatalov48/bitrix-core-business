<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class Landing extends \Bitrix\Landing\Internals\BaseTable
{
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
	 * Folder id of current landing.
	 * @var int
	 */
	protected $folderId = 0;

	/**
	 * Current template id.
	 * @var int
	 */
	protected $tplId = 0;

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
	 * @var \Bitrix\Landing\Error
	 */
	protected $error = null;

	/**
	 * Constructor.
	 * @param int $id Landing id.
	 * @param array $params Some params.
	 */
	protected function __construct($id, $params = array())
	{
		$this->error = new Error;
		$filter = array(
			'ID' => $id
		);
		if (
			isset($params['force_deleted']) &&
			$params['force_deleted'] === true
		)
		{
			$filter['=DELETED'] = ['Y', 'N'];
			$filter['=SITE.DELETED'] = ['Y', 'N'];
		}
		$landing = self::getList(array(
			'select' => array(
				'*',
				'SITE_TPL_ID' => 'SITE.TPL_ID',
				'SITE_LANDING_ID_INDEX' => 'SITE.LANDING_ID_INDEX'
			),
			'filter' => $filter
		))->fetch();

		if ($landing)
		{
			/*
			 * $this->getEditMode()
			 * @todo return if no access
			 */
			// get base data
			$this->title = $landing['TITLE'];
			$this->code = $landing['CODE'];
			$this->xmlId = $landing['XML_ID'];
			$this->id = (int)$landing['ID'];
			$this->siteId = (int)$landing['SITE_ID'];
			$this->folderId = (int)$landing['FOLDER_ID'];
			$this->active = $landing['ACTIVE'] == 'Y';
			$this->siteRow = [
				'TPL_ID' => $landing['SITE_TPL_ID'],
				'LANDING_ID_INDEX' => $landing['SITE_LANDING_ID_INDEX']
			];
			$this->tplId = $landing['TPL_ID'] > 0
							? $landing['TPL_ID']
							: (
								$landing['SITE_TPL_ID'] > 0
								? $landing['SITE_TPL_ID']
								: 0
							);
			if (isset($params['is_area']) && $params['is_area'])
			{
				$this->mainInstance = false;
			}
			if ($landing['SITE_TPL_ID'] > 0 && !$landing['TPL_ID'])
			{
				$this->tplType = 'site';
			}
			// if edit mode - create copy for edit
			if ($this->getEditMode())
			{
				if ($landing['PUBLIC'] == 'Y')
				{
					self::update($id, array(
						'PUBLIC' => 'N',
						'DATE_MODIFY' => false
					));
					Block::cloneForEdit($this);
				}
			}
			// if landing is unactive
			if (
				false &&
				!$this->active
			)
			{
				//add error ?
				//add title ? $this->title = Loc::getMessage('LANDING_TITLE_NOT_FOUND');
			}
			// get available blocks
			else
			{
				Block::fillLanding(
					$this,
					isset($params['blocks_limit']) ? $params['blocks_limit'] : 0,
					array(
						'deleted' => isset($params['deleted']) && $params['deleted'] === true
					)
				);
			}
			// fill meta data
			$keys = ['CREATED_BY_ID', 'MODIFIED_BY_ID', 'DATE_CREATE', 'DATE_MODIFY'];
			foreach ($keys as $key)
			{
				if (isset($landing[$key]))
				{
					$this->metaData[$key] = $landing[$key];
				}
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
	 * Mark entity as deleted.
	 * @param int $id Entity id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markDelete($id)
	{
		if (TemplateRef::landingIsArea($id))
		{
			$result = new \Bitrix\Main\Entity\UpdateResult();
			$result->addError(
				new \Bitrix\Main\Error(
					Loc::getMessage('LANDING_BLOCK_UNABLE_DEL_INC'),
					'UNABLE_DELETE_INCLUDE'
				)
			);
			return $result;
		}
		return parent::update($id, array(
			'DELETED' => 'Y'
		));
	}


	/**
	 * Mark entity as restored.
	 * @param int $id Entity id.
	 * @return \Bitrix\Main\Result
	 */
	public static function markUnDelete($id)
	{
		return parent::update($id, array(
			'DELETED' => 'N'
		));
	}

	/**
	 * Delete landing by id and its blocks.
	 * @param int $id Landing id.
	 * @param bool $forceDeleted Force delete throw an errors.
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id, $forceDeleted = false)
	{
		$result = new \Bitrix\Main\Entity\DeleteResult();
		$params = [];

		if ($forceDeleted)
		{
			$params['force_deleted'] = true;
		}

		// first check
		foreach (array('draft', 'public') as $code)
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
				$result->addError(
					$landing->getError()->getErrors()[0]
				);
				return $result;
			}
		}

		// delete blocks
		foreach (array('draft', 'public') as $code)
		{
			self::setEditMode($code == 'draft');
			$landing = self::createInstance($id, $params);
			if ($landing->exist())
			{
				Block::deleteAll($id);
				File::deleteFromLanding($id);
			}
		}

		return parent::delete($id);
	}

	/**
	 * Get hooks of Landing.
	 * @param int $id Landing id.
	 * @return array Array of Hook.
	 */
	public static function getHooks($id)
	{
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
	 * Get preview picture of the landing.
	 * Is the preview of first block.
	 * @return string
	 */
	public function getPreview()
	{
		if (
			(
				!defined('LANDING_DISABLE_CLOUD')
				||
				LANDING_DISABLE_CLOUD !== true
			)
			&&
			Manager::isB24())
		{
			return $this->getPublicUrl() . 'preview.jpg';
		}

		static $hookPics = null;

		if ($hookPics === null)
		{
			$hookPics = Hook\Page\MetaOg::getAllImages();
		}

		if (isset($hookPics[$this->id]))
		{
			$pic = $hookPics[$this->id];
			if (intval($pic) > 0)
			{
				$pic = File::getFilePath($pic);
			}
			return $pic;
		}

		return '/bitrix/images/landing/nopreview.jpg';
	}

	/**
	 * Get full pubic URL for this landing.
	 * @param int|array $id Landing id (id array), optional.
	 * @param boolean $absolute Full url.
	 * @param bool $createPubPath Create pub path (checking and create).
	 * @return string
	 */
	public function getPublicUrl($id = false, $absolute = true, $createPubPath = false)
	{
		if ($id === false)
		{
			$id = $this->id;
		}

		$siteId = Manager::getMainSiteId();
		$bitrix24 = Manager::isB24();
		$disableCloud = defined('LANDING_DISABLE_CLOUD') &&
						LANDING_DISABLE_CLOUD === true;

		$domainDefault = null;
		$data = array();
		$res = Landing::getList(array(
			'select' => array(
				'ID',
				'CODE',
				'SITE_ID',
				'SITE_ID_INDEX' => 'SITE.LANDING_ID_INDEX',
				'SITE_PROTOCOL' => 'SITE.DOMAIN.PROTOCOL',
				'SITE_DOMAIN' => 'SITE.DOMAIN.DOMAIN',
				'SITE_CODE' => 'SITE.CODE',
				'SITE_SMN_ID' => 'SITE.SMN_SITE_ID',
				'FOLDER_CODE' => 'LF.CODE'
			),
			'filter' => array(
				'ID' => $id,
				'=DELETED' => ['Y', 'N']
			),
			'runtime' => array(
				new \Bitrix\Main\Entity\ReferenceField(
					'LF',
					'\Bitrix\Landing\Internals\LandingTable',
					array('=this.FOLDER_ID' => 'ref.ID')
				)
			)
		));
		while ($row = $res->fetch())
		{
			$domainReplace = false;
			$row['SITE_ID_ORIG'] = $row['SITE_ID'];
			// build site domain by default
			if (!$row['SITE_DOMAIN'])
			{
				if (!$domainDefault)
				{
					$domainDefault =  Domain::getList(array(
					  	'filter' => array(
							'ID' => Domain::getCurrentId()
					  	)
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
				$row['SITE_ID'] = '/' . $row['SITE_ID'] . '/';
			}
			if ($disableCloud)
			{
				$data[$row['ID']] = $pubPath .
									$row['SITE_ID'] .
									(self::$previewMode ? 'preview/' . Site::getPublicHash($row['SITE_ID_ORIG'], $row['SITE_DOMAIN']) . '/' : '') .
									($row['FOLDER_CODE'] ? $row['FOLDER_CODE'] . '/' : '') .
									(($row['ID'] == $row['SITE_ID_INDEX']) ? '' : $row['CODE'] . '/');
			}
			else
			{
				$data[$row['ID']] = (
									$absolute
										? (
											$row['SITE_PROTOCOL'] . '://' .
											$row['SITE_DOMAIN']
										)
										: ''
									) .
									(($domainReplace || !$bitrix24) ? $pubPath : '') .
									((self::$previewMode && !$bitrix24) ? '/preview/' . Site::getPublicHash($row['SITE_ID_ORIG'], $row['SITE_DOMAIN']) : '') .
									(($domainReplace && $bitrix24) ? $row['SITE_ID'] : '/') .
									((self::$previewMode && $bitrix24) ? 'preview/' . Site::getPublicHash($row['SITE_ID_ORIG'], $row['SITE_DOMAIN']) . '/' : '') .
									($row['FOLDER_CODE'] ? $row['FOLDER_CODE'] . '/' : '') .
									(($row['ID'] == $row['SITE_ID_INDEX']) ? '' : $row['CODE'] . '/');
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
				$res = self::getList(array(
					 'select' => array(
						'ID', 'TITLE'
					 ),
					'filter' => array(
						'ID' => $this->folderId
					)
		 		));
				if ($row = $res->fetch())
				{
					Manager::getApplication()->addChainItem(
						$row['TITLE'],
						'#landing' . $row['ID']
					);
				}
			}
			else
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
			$options = array(
				'site_id' => $this->siteId,
				'server_name' => $_SERVER['SERVER_NAME'],
				'url' => $this->getPublicUrl(),
				'xml_id' => $this->xmlId,
				'blocks' => Block::getRepository(),
				'style' => Block::getStyle()
			);
			// event for redefine $options
			$event = new Event('landing', 'onLandingView', array(
				'options' => $options
			));
			$event->send();
			foreach ($event->getResults() as $result)
			{
				if ($result->getResultType() != EventResult::ERROR)
				{
					if (($modified = $result->getModified()))
					{
						if (isset($modified['options']))
						{
							$options = $modified['options'];
						}
					}
				}
			}
			// output js
			Asset::getInstance()->addString(
				'<script type="text/javascript">' .
					'BX.ready(function(){'
						. 'if (typeof BX.Landing.Main !== "undefined")'
						. '{'
							. 'BX.Landing.Main.createInstance(' . $this->id . ', ' . \CUtil::phpToJSObject($options, false, false, true) . ');'
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
		foreach ($this->blocks as $block)
		{
			$block->view($blockEditMode, $this);
		}
		if ($this->mainInstance)
		{
			$this->execHooks();
		}
		$contentMain = ob_get_contents();
		ob_end_clean();

		// implode content and templates parts
		if ($content && strpos($content, '#CONTENT#') !== false)
		{
			$content = str_replace('#CONTENT#', $contentMain, $content);
		}
		else
		{
			$content = $contentMain;
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
					'#title#'
				),
				array(
					$breadcrumb,
					Manager::getApplication()->getTitle()
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
					'ID' => $this->tplId
				)
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
						'#CONTENT#' => $content ? $content : '#CONTENT#'
					);
				}
				else
				{
					$replace = array(
						'#CONTENT#' => $content ? $content : '#CONTENT#'
					);
				}
				// if areas exist, get landings
				if ($template['AREA_COUNT'] > 0)
				{
					foreach ($this->getAreas() as $area => $lid)
					{
						ob_start();
						$landing = self::createInstance($lid, array(
							'is_area' => true
						));
						if ($landing->exist())
						{
							$landing->view();
						}
						if ($editMode)
						{
							$replace['>#AREA_' . $area . '#<'] = ' data-site="' . $landing->getSiteId() .
																'" data-landing="' . $lid .
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
	 * Parse between-landings url in landing content.
	 * @param string $content Landing content.
	 * @return string
	 */
	protected function parseLocalUrl($content)
	{
		$pattern = '/([",\'\;]{1})#(landing|block)([\d]+)\@{0,1}([^\'"]*)([",\'\&]{1})/is';

		// replace catalog links in preview mode
		if (self::$previewMode)
		{
			$content = preg_replace_callback(
				'/href\="#catalog(Element|Section)([\d]+)"/i',
				function($href)
				{
					return 'href="' . PublicAction\Utils::getIblockURL(
							$href[2],
							strtolower($href[1])
						) . '"';
				},
				$content);
		}

		// prepare system pages
		$sysPages = array();
		foreach (Syspage::get($this->siteId) as $syspage)
		{
			$sysPages['@#system_' . $syspage['TYPE'] . '@'] = '#landing' . $syspage['LANDING_ID'];
		}
		if ($this->siteRow['LANDING_ID_INDEX'] > 0)
		{
			$sysPages['@#system_mainpage@'] = '#landing' . $this->siteRow['LANDING_ID_INDEX'];
		}
		if (!empty($sysPages))
		{
			$content = preg_replace(
				array_keys($sysPages),
				array_values($sysPages),
				$content
			);
		}

		// replace in content
		if (preg_match_all($pattern, $content, $matches))
		{
			$urls = array(
				'LANDING' => array(),
				'BLOCK' => array()
			);
			for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
			{
				if (strtoupper($matches[2][$i]) == 'LANDING')
				{
					$urls['LANDING'][] = $matches[3][$i];
				}
				else
				{
					$urls['BLOCK'][] = $matches[3][$i];
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
						'ID', 'LID', 'PARENT_ID', 'ANCHOR'
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
			// get anchors for public version
			if ($anchorsPublicId)
			{
				$anchorsPublicIdTmp = Block::getRowByBlockId(
					$anchorsPublicId,
					array(
						'ID', 'LID', 'PARENT_ID', 'ANCHOR'
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
			// get landing and blocks urls
			if (!empty($urls['LANDING']))
			{
				$urls['LANDING'] = $this->getPublicUrl($urls['LANDING']);
			}
			if (!empty($urls['BLOCK']))
			{
				foreach ($urls['BLOCK'] as $bid => $lid)
				{
					if (isset($urls['LANDING'][$lid]))
					{
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
					$pattern,
					function($matches) use($urls)
					{
						$matches[2] = strtoupper($matches[2]);
						if (isset($urls[$matches[2]][$matches[3]]))
						{
							return $matches[1] .
										$urls[$matches[2]][$matches[3]] . $matches[4] . 
									$matches[5];
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
	 * Get metadata of current landing.
	 */
	public function getMeta()
	{
		return $this->metaData;
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
	 * Get site id of main, if exist.
	 * @return string
	 */
	public function getSmnSiteId()
	{
		$siteId = Manager::getMainSiteId();

		if ($this->siteId)
		{
			$res = Site::getList(array(
				'select' => array(
					'SMN_SITE_ID'
				),
				'filter' => array(
					'ID' => $this->siteId
				)
			));
			if ($row = $res->fetch())
			{
				return $row['SMN_SITE_ID']
						? $row['SMN_SITE_ID']
						: $siteId;
			}
		}

		return $siteId;
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
	 * Change modified user and date for current landing.
	 * @return void
	 */
	public function touch()
	{
		self::update($this->id);
	}

	/**
	 * Publication current landing.
	 * @return boolean
	 */
	public function publication()
	{
		return Mutator::landingPublication($this);
	}

	/**
	 * Cancel publication of landing.
	 * @return boolean
	 */
	public function unpublic()
	{
		$date = new \Bitrix\Main\Type\DateTime;
		$res = parent::update($this->id, array(
			'ACTIVE' => 'N',
			'PUBLIC' => 'N',
			'DATE_MODIFY' => false
		));
		if ($res->isSuccess())
		{
			if (Manager::isB24())
			{
				Site::update($this->siteId, array());
			}
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
	 * @return int|false Id of new block or false on failure.
	 */
	public function addBlock($code, $data = array())
	{
		if (!isset($data['PUBLIC']))
		{
			$data['PUBLIC'] = $this::$editMode ? 'N' : 'Y';
		}

		$block = Block::createFromRepository($this, $code, $data);

		if ($block)
		{
			$this->touch();
			$this->addBlockToCollection($block);
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
			if ($this->blocks[$id]->getAccess() >= $this->blocks[$id]->ACCESS_X)
			{
				$this->blocks[$id]->markDeleted($mark);
				if ($this->blocks[$id]->save())
				{
					if ($mark)
					{
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
	protected function sortBlock($id, $action)
	{
		if (isset($this->blocks[$id]))
		{
			$blocks = array_keys($this->blocks);
			for ($i = 0, $c = count($blocks); $i < $c; $i++)
			{
				if ($blocks[$i] == $id)
				{
					// change sort between two blocks
					$targetKey = $i + ($action == 'up' ? -1 : 1);
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
					else
					{
						$this->error->addError(
							'BLOCK_WRONG_SORT',
							Loc::getMessage('LANDING_BLOCK_WRONG_SORT')
						);
						return false;
					}
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
	public function upBlock($id)
	{
		return $this->sortBlock($id, 'up');
	}

	/**
	 * Sort down the block on the landing.
	 * @param int $id Block id.
	 * @return boolean
	 */
	public function downBlock($id)
	{
		return $this->sortBlock($id, 'down');
	}

	/**
	 * Show/hide the block on the landing.
	 * @param int $id Block id.
	 * @param string $action Code: up or down.
	 * @return boolean
	 */
	protected function activateBlock($id, $action)
	{
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
	 * Copy/move other block to this landing.
	 * @param int $block Block id.
	 * @param array $params Params array.
	 * @return int New Block id.
	 */
	protected function changeParentOfBlock($block, $params)
	{
		$move = isset($params['MOVE']) && $params['MOVE'];
		$afterId = isset($params['AFTER_ID']) ? $params['AFTER_ID'] : 0;
		$fromLandingId = Block::getLandingIdByBlockId($block);
		$same = $this->id == $fromLandingId;

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
						'ACCESS' => $srcBlock->getAccess(),
						'SORT' => $srcBlock->getSort(),
						'CONTENT' => $srcBlock->getContent(),
						'PUBLIC' => 'N'
				));
				// copy files
				if ($newBlock)
				{
					File::copyBlockFiles(
						$srcBlock->getId(),
						$newBlock->getId()
					);
				}
			}
			// add block to collection and resort
			if (isset($newBlock) && $newBlock)
			{
				if ($afterId > 0 && isset($this->blocks[$afterId]))
				{
					$targetBlock = $this->blocks[$afterId];
				}
				else
				{
					$targetBlock = array_pop(array_values($this->blocks));
				}
				if ($targetBlock)
				{
					$newBlock->setSort($targetBlock->getSort() + 1);
				}
				$this->addBlockToCollection($newBlock);
				$this->resortBlocks();
			}
			//change dates
			if ($this->error->isEmpty())
			{
				$this->touch();
				if ($move && !$same)
				{
					$fromLanding->touch();
				}
			}
		}

		$this->error->copyError($fromLanding->getError());

		return isset($newBlock) ? $newBlock->getId() : null;
	}

	/**
	 * Copy other block to this landing.
	 * @param int $id Block id (from another landing).
	 * @param int $afterId Put after this block id (in this landing).
	 * @return int New Block id.
	 */
	public function copyBlock($id, $afterId)
	{
		$blockId = $this->changeParentOfBlock($id, array(
			'MOVE' => false,
			'AFTER_ID' => $afterId
		));
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
	 * @return void
	 */
	public function copyAllBlocks($lid)
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
						'PUBLIC' => $block->isPublic() ? 'Y' : 'N',
						'ACCESS' => $block->getAccess(),
						'SORT' => $block->getSort(),
						'CONTENT' => $block->getContent()
					));
				if ($newBlock)
				{
					$oldNew[$block->getId()] = $newBlock;
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
					$content = str_replace(
						'#block' . $oldId,
						'#block' . $newBlock->getId(),
						$content
					);
					$block->saveContent($content);
					$block->save();
				}
			}
			$this->touch();
		}

		$this->error->copyError($this->getError());
		$this->error->copyError($landing->getError());
	}

	/**
	 * Move other block to this landing.
	 * @param int $id Block id (from another landing).
	 * @param int $afterId Put after this block id (in this landing).
	 * @return int New Block id.
	 */
	public function moveBlock($id, $afterId)
	{
		$blockId = $this->changeParentOfBlock($id, array(
			'MOVE' => true,
			'AFTER_ID' => $afterId
		));
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
}