<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Application;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Landing\Assets;
use \Bitrix\Bitrix24\Feature;

Loc::loadMessages(__FILE__);

class Manager
{
	/**
	 * User agreement actual version.
	 * @see Manager::getOption('user_agreement_version')
	 */
	const USER_AGREEMENT_VERSION = 4;

	/**
	 * Publication default path.
	 * @see Manager::getPublicationPathConst()
	 */
	const PUBLICATION_PATH = '/pub/site/';
	const PUBLICATION_PATH_SITEMAN = '/lp/';

	/**
	 * Path, where user can buy upgrade.
	 */
	const BUY_LICENSE_PATH = '/settings/license_all.php';

	/**
	 * Features codes for backward compatibility.
	 */
	const FEATURE_CREATE_SITE = 'create_site';
	const FEATURE_CREATE_PAGE = 'create_page';
	const FEATURE_CUSTOM_DOMAIN = 'custom_domain';
	const FEATURE_ENABLE_ALL_HOOKS = 'enable_all_hooks';
	const FEATURE_PUBLICATION_SITE = 'publication_site';
	const FEATURE_PUBLICATION_PAGE = 'publication_page';
	const FEATURE_PERMISSIONS_AVAILABLE = 'permissions_available';
	const FEATURE_DYNAMIC_BLOCK = 'dynamic_block';
	const FEATURE_FREE_DOMAIN = 'free_domain';
	const FEATURE_ALLOW_EXPORT = 'allow_export';
	const FEATURE_ALLOW_VIEW_PAGE = 'allow_view_page';

	/**
	 * If true, that self::isB24() returns false always.
	 * @var bool
	 */
	protected static $forceB24disable = false;

	/**
	 * Current temporary functions.
	 * @var array
	 */
	protected static $tmpFeatures = [];

	/**
	 * Selected template theme id.
	 * @var string
	 */
	private static $themeId = '';

	/**
	 * And ID for typography settings.
	 * @var string
	 * @deprecated since 20.3.0, use THEMEFONTS hook settings
	 */
	private static $themeTypoId = '';

	/**
	 * Get main instance of \CMain.
	 * @return \CMain
	 */
	public static function getApplication()
	{
		return $GLOBALS['APPLICATION'];
	}

	/**
	 * Get main instance of \CUser.
	 * @return \CUser
	 */
	public static function getUserInstance()
	{
		return $GLOBALS['USER'];
	}

	/**
	 * Get instance of CACHE_MANAGER;
	 * @return \CCacheManager
	 */
	public static function getCacheManager()
	{
		return $GLOBALS['CACHE_MANAGER'];
	}

	/**
	 * Get instance of USER_FIELD_MANAGER.
	 * @return \CUserTypeManager
	 */
	public static function getUfManager()
	{
		return $GLOBALS['USER_FIELD_MANAGER'];
	}

	/**
	 * Get current user id.
	 * @return int
	 */
	public static function getUserId(): int
	{
		$user = self::getUserInstance();
		if ($user instanceof \CUser)
		{
			return (int)$user->getId();
		}
		return 0;
	}

	/**
	 * Get current user full name.
	 * @return int
	 */
	public static function getUserFullName()
	{
		$user = self::getUserInstance();
		if ($user instanceof \CUser)
		{
			return $user->getFullName();
		}
		return '';
	}

	/**
	 * Admin or not.
	 * @return boolean
	 */
	public static function isAdmin()
	{
		$user = self::getUserInstance();

		if (!($user instanceof \CUser))
		{
			return false;
		}

		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $user->canDoOperation('bitrix24_config');
		}
		else
		{
			return $user->isAdmin();
		}
	}

	/**
	 * Get option from module settings.
	 * @param string $code Option code.
	 * @param mixed $default Default value.
	 * @return mixed
	 */
	public static function getOption($code, $default = null)
	{
		return Option::get('landing', $code, $default);
	}

	/**
	 * Set option for module settings.
	 * @param string $code Option code.
	 * @param string $value Option value.
	 * @return void
	 */
	public static function setOption($code, $value)
	{
		Option::set('landing', $code, $value);
	}

	/**
	 * Famous document root.
	 * @return string
	 */
	public static function getDocRoot()
	{
		static $docRoot = null;

		if ($docRoot === null)
		{
			$context = Application::getInstance()->getContext();
			$server = $context->getServer();
			$docRoot = $server->getDocumentRoot();
		}

		return $docRoot;
	}

	/**
	 * Returns current dir.
	 * @return false|string|null
	 */
	public static function getCurDir()
	{
		return Application::getInstance()->getContext()
										 ->getRequest()
										 ->getRequestedPageDirectory();
	}

	/**
	 * Set page title.
	 * @param string $title Page title.
	 * @param bool $single If true, then set title only once.
	 * @return void
	 */
	public static function setPageTitle($title, $single = false)
	{
		static $application = null;
		static $disable = false;

		if ($application === null)
		{
			$application = self::getApplication();
		}

		if ($title && !$disable)
		{
			$application->setTitle($title);
			$application->setPageProperty('title', $title);
			if ($single)
			{
				$disable = true;
			}
		}
	}

	/**
	 * Create system dir for publication sites.
	 * @param string $basePath Publication physical dir.
	 * @return void
	 */
	protected static function createPublicationPath($basePath, $siteId = null)
	{
		static $paths = [];

		if (!in_array($basePath, $paths))
		{
			$paths[] = $basePath;

			if (mb_substr($basePath, 0, 1) != '/')
			{
				$basePath = '/' . $basePath;
			}
			if (mb_substr($basePath, -1) != '/')
			{
				$basePath = $basePath . '/';
			}

			$docRoot = '';
			$subDirSite = '';
			$basePathOriginal = $basePath;

			// gets current doc root or gets from the site
			if ($siteId)
			{
				if ($smnSite = \Bitrix\Main\SiteTable::getById($siteId)->fetch())
				{
					if ($smnSite['DOC_ROOT'])
					{
						$docRoot = $smnSite['DOC_ROOT'] . $smnSite['DIR'];
					}
					else
					{
						$docRoot = self::getDocRoot() . $smnSite['DIR'];
					}
					$subDirSite = rtrim($smnSite['DIR'], '/');
				}
				$docRoot = rtrim($docRoot, '/');
			}
			if (!$docRoot)
			{
				$docRoot = self::getDocRoot();
			}
			$basePath = $docRoot . $basePath;

			// create path
			if (\checkDirPath($basePath))
			{
				if (!file_exists($basePath . 'index.php'))
				{
					\rewriteFile(
						$basePath . 'index.php',
						file_get_contents(
							self::getDocRoot() .
							'/bitrix/modules/landing/install/pub/site/index.php'
						)
					);
				}
			}
			// and add template rules for main
			if ($siteId)
			{
				$fields = array(
					'SORT' => 0,
					'SITE_ID' => $siteId,
					'CONDITION' => 'CSite::inDir(\'' . $subDirSite . $basePathOriginal . '\')',
					'TEMPLATE' => self::getTemplateId($siteId)
				);
				$check = \Bitrix\Main\SiteTemplateTable::getList(array(
					 'filter' => array(
						 '=SITE_ID' => $fields['SITE_ID'],
						 '=CONDITION' => $fields['CONDITION'],
						 '=TEMPLATE' => $fields['TEMPLATE']
					 )
				 ))->fetch();
				if (!$check)
				{
					\Bitrix\Main\SiteTemplateTable::add(
						$fields
					);
					\Bitrix\Main\UrlRewriter::add(
						$siteId,
						array(
							'ID' => 'bitrix:landing.pub',
							'PATH' => $subDirSite. $basePathOriginal . 'index.php',
							'CONDITION' => '#^' . $subDirSite. $basePathOriginal . '#'
						)
					);
					self::getCacheManager()->clean('b_site_template');
				}
			}
		}
	}

	/**
	 * Get main site local dir.
	 * @param string $siteId Site LID.
	 * @return string
	 */
	protected static function getSmnSiteDir($siteId)
	{
		static $sites = [];

		if (!$siteId)
		{
			$siteId = SITE_ID;
		}

		if (!isset($sites[$siteId]))
		{
			$sites[$siteId] = '';
			if ($smnSite = \Bitrix\Main\SiteTable::getById($siteId)->fetch())
			{
				$sites[$siteId] = rtrim($smnSite['DIR'], '/');
			}
		}

		return $sites[$siteId];
	}

	/**
	 * Get constantly publication path.
	 * @return string
	 */
	public static function getPublicationPathConst()
	{
		if (defined('LANDING_PUBLICATION_PATH_CONST'))
		{
			return LANDING_PUBLICATION_PATH_CONST;
		}
		return self::isB24()
				? self::PUBLICATION_PATH
				: self::PUBLICATION_PATH_SITEMAN;
	}

	/**
	 * Get path for publication sites.
	 * @param string|int $siteCode Site id or site code.
	 * @param string $siteId Main site id.
	 * @param bool $createPubPath Create path for publication.
	 * @return string
	 */
	public static function getPublicationPath($siteCode = null, $siteId = null, $createPubPath = false)
	{
		$tyePublicationPath = Site\Type::getPublicationPath();

		$basePath = $tyePublicationPath;
		if ($basePath === null)
		{
			$basePath = self::getOption(
				'pub_path_' . (!isset($siteId) ? (self::getMainSiteId()) : $siteId),
				self::getPublicationPathConst()
			);
		}
		$subDir = self::getSmnSiteDir($siteId);
		if ($siteCode === null)
		{
			if (
				$createPubPath &&
				ModuleManager::isModuleInstalled('bitrix24')
			)
			{
				$createPubPath = false;
			}
			if ($createPubPath && $siteId)
			{
				self::createPublicationPath(
					$basePath,
					$siteId
				);
			}
			return $subDir . $basePath;
		}
		else
		{
			return $subDir . str_replace(
				'#id#',
				$siteCode,
				$basePath . '#id#/'
			);
		}
	}

	/**
	 * Add some class to some marker.
	 * @param string $marker Marker.
	 * @param string $class Class.
	 * @deprecated since version 18.1.5
	 * @return void
	 */
	public static function setPageClass($marker, $class)
	{
		self::setPageView($marker, $class);
	}

	/**
	 * Add some content to some marker.
	 * @param string $marker Marker.
	 * @param string $content Content.
	 * @return void
	 */
	public static function setPageView($marker, $content)
	{
		$content = trim($content);
		if ($content)
		{
			$application = self::getApplication();
			$existContent = $application->getPageProperty($marker);
			$application->setPageProperty(
				$marker,
				$existContent . ($existContent != '' ? ' ' : '') . $content
			);
		}
	}

	/**
	 * Clears view by marker code.
	 * @param string $marker Marker code.
	 * @return void
	 */
	public static function clearPageView($marker): void
	{
		self::getApplication()->setPageProperty($marker, '');
	}

	/**
	 * Get some content from some marker.
	 * @param string $marker Marker.
	 * @return string
	 */
	public static function getPageView($marker)
	{
		return self::getApplication()->getPageProperty($marker);
	}

	/**
	 * Get themes entity from template dir.
	 * @param string $tplId Site template id.
	 * @param string $entityType - entity folder name.
	 * @return array
	 */
	protected static function getThemesEntity($tplId, $entityType)
	{
		$themes = array();

		$path = self::getDocRoot() . getLocalPath('templates/' . $tplId) . '/'.$entityType.'/';
		if (
			file_exists($path) &&
			($handle = opendir($path))
		)
		{
			while ((($entry = readdir($handle)) !== false))
			{
				if ($entry != '.' && $entry != '..')
				{
					$themes[] = pathinfo($entry, PATHINFO_FILENAME);
				}
			}
		}

		return $themes;
	}

	/**
	 * Get themes from template dir.
	 * @param string $tplId Site template id.
	 * @return array
	 */
	public static function getThemes($tplId)
	{
		return self::getThemesEntity($tplId, 'themes');
	}

	/**
	 * Get themes typo from template dir.
	 * @param string $tplId Site template id.
	 * @return array
	 *
	 * @deprecated since 20.3.0, use THEMEFONTS hook settings
	 */
	public static function getThemesTypo($tplId)
	{
		return self::getThemesEntity($tplId, 'themes-typo');
	}

	/**
	 * Gets site template id.
	 * @param string $siteId Site id (siteman).
	 * @return string
	 */
	public static function getTemplateId($siteId = null)
	{
		static $tplId = [];

		if (!isset($tplId[$siteId]))
		{
			if ($siteId)
			{
				$tplId[$siteId] = self::getOption('site_template_id_' . $siteId);
			}
			if (!$tplId[$siteId])
			{
				$tplId[$siteId] = self::getOption('site_template_id', 'landing24');
			}
		}

		return $tplId[$siteId];
	}

	/**
	 * Gets true, if this template id is system.
	 * @param string $templateId Site template id.
	 * @return bool
	 */
	public static function isTemplateIdSystem($templateId)
	{
		return $templateId === 'landing24';
	}

	/**
	 * Gets site id from main module.
	 * @return string
	 */
	public static function getMainSiteId()
	{
		return defined('SMN_SITE_ID') ? SMN_SITE_ID : SITE_ID;
	}


	/**
	 * Set new colored theme id.
	 * @param string $themeId Theme id.
	 * @return void
	 */
	public static function setThemeId($themeId)
	{
		self::$themeId = $themeId;
	}

	/**
	 * Set new colored theme id.
	 * @param string $themeTypoId Theme id.
	 * @return void
	 * @deprecated since 20.3.0, use THEMEFONTS hook settings
	 */
	public static function setThemeTypoId($themeTypoId)
	{
		self::$themeTypoId = $themeTypoId;
	}

	/**
	 * Get current theme id.
	 * @return string
	 */
	public static function getThemeId()
	{
		return self::$themeId;
	}

	/**
	 * Add assets to page from hooks and themes
	 * @param int $lid Landing id.
	 * @return void
	 */
	public static function initAssets($lid = 0)
	{
		self::setThemeAssets();

		$assets = Assets\Manager::getInstance();
		$assets->setOutput($lid);
	}

	/**
	 * Set assets for theme.
	 * @return void
	 */
	protected static function setThemeAssets()
	{
		$assets = Assets\Manager::getInstance();
		$tplId = self::getTemplateId(SITE_ID);

		if (self::$themeId)
		{
			foreach(self::findThemeFiles(self::$themeId, 'themes', $tplId) as $path)
			{
				$assets->addAsset($path);
			}
		}
	}


	/**
	 * Set current selected or default color theme.
	 * @return void
	 */
	public static function setTheme()
	{
		$tplId = self::getTemplateId(SITE_ID);
		$themes = Manager::getThemes($tplId);
		$request = Application::getInstance()->getContext()->getRequest();

		// set default theme ID
		if ($request->get('theme'))
		{
			self::setThemeId($request->get('theme'));
		}
		if (!self::$themeId || !in_array(self::$themeId, $themes))
		{
			self::setThemeId(array_pop($themes));
		}
	}

	/**
	 * Find all theme files.
	 * @param string $themeId Theme id.
	 * @param string $themeEntityId Entity code.
	 * @param $tplId Site template id.
	 * @return array
	 */
	protected static function findThemeFiles($themeId, $themeEntityId, $tplId)
	{
		$files = [];
		$themePath = \getLocalPath('templates/' . $tplId, BX_PERSONAL_ROOT) . '/'.$themeEntityId.'/' . $themeId;
		$themePathAbsolute = self::getDocRoot() . $themePath;
		if (is_dir($themePathAbsolute))
		{
			if ($handle = opendir($themePathAbsolute))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file != '.' && $file != '..')
					{
						$files[] = $themePath . '/' . $file;
					}
				}
				closedir($handle);
			}
		}
		elseif (is_file($themePathAbsolute . '.css'))
		{
			$files[] = $themePath . '.css';
		}

		return $files;
	}

	/**
	 * Save picture to db.
	 * @param mixed $file File array or path to file.
	 * @param string $ext File extension (if can't detected by file name).
	 * @param array $params Some file params.
	 * @return array|false Local file array or false on error.
	 */
	public static function savePicture($file, $ext = false, $params = array())
	{
		// local file
		if (!is_array($file) && mb_substr($file, 0, 1) == '/')
		{
			$file = \CFile::makeFileArray($file);
		}
		// url of picture
		else if (!is_array($file))
		{
			$httpClient = new \Bitrix\Main\Web\HttpClient();
			$httpClient->setTimeout(5);
			$httpClient->setStreamTimeout(5);
			$urlComponents = parse_url($file);

			// detect tmp file name
			if ($urlComponents && $urlComponents['path'] != '')
			{
				$tempPath = \CFile::getTempName('', bx_basename(urldecode($urlComponents['path'])));
			}
			else
			{
				$tempPath = \CFile::getTempName('', bx_basename(urldecode($file)));
			}
			if ($ext !== false && in_array($ext, explode(',', \CFile::getImageExtensions())))
			{
				if (mb_substr($tempPath, -3) != $ext)
				{
					$tempPath = $tempPath . '.' . $ext;
				}
			}

			// download and save
			if ($httpClient->download($file, $tempPath))
			{
				$fileName = $httpClient->getHeaders()->getFilename();
				$file = \CFile::makeFileArray($tempPath);
				if ($file && $fileName)
				{
					$file['name'] = $fileName;
				}
			}
		}

		// base64
		elseif (
			is_array($file) &&
			isset($file[0]) &&
			isset($file[1])
		)
		{
			$fileParts = explode('.', $file[0]);
			$ext = array_pop($fileParts);
			$tempPath = \CFile::getTempName(
				'',
				\CUtil::translit(
					implode('', $fileParts),
					'ru'
				) . '.' . $ext
			);
			$fileIO = new \Bitrix\Main\IO\File(
				$tempPath
			);
			$fileIO->putContents(
				base64_decode($file[1])
			);
			$file = \CFile::makeFileArray($tempPath);
		}

		// post array or file from prev. steps
		if (\CFile::checkImageFile($file, 0, 0, 0, array('IMAGE')) === null)
		{
			// resize if need
			if (
				isset($params['width']) &&
				isset($params['height'])
			)
			{
				\CFile::resizeImage(
					$file,
					$params,
					isset($params['resize_type'])
					? intval($params['resize_type'])
					: BX_RESIZE_IMAGE_PROPORTIONAL);
			}
			// save
			$module = 'landing';
			$file['name'] = File::sanitizeFileName($file['name']);
			$file['MODULE_ID'] = $module;
			$file = \CFile::saveFile($file, $module);
			if ($file)
			{
				$file = \CFile::getFileArray($file);
			}
			if ($file)
			{
				$file['SRC'] = str_replace(
					'%',
					'%25',
					$file['SRC']
				);
				return $file;
			}
		}

		return false;
	}

	/**
	 * Enable some feature for moment.
	 * @param string $feature Feature code.
	 * @return void
	 */
	public static function enableFeatureTmp($feature)
	{
		self::$tmpFeatures[$feature] = true;
	}

	/**
	 * Disable some tmp feature.
	 * @param string $feature Feature code.
	 * @return void
	 */
	public static function disableFeatureTmp($feature)
	{
		if (isset(self::$tmpFeatures[$feature]))
		{
			unset(self::$tmpFeatures[$feature]);
		}
	}

	/**
	 * Disable all tmp feature.
	 * @return void
	 */
	public static function disableAllFeaturesTmp()
	{
		self::$tmpFeatures = [];
	}

	/**
	 * Returns true, if all of features array is enabled.
	 * @param array $features Feature name.
	 * @param array $params Params array.
	 * @return bool
	 */
	public static function checkMultiFeature(array $features, array $params = [])
	{
		$features = array_unique($features);

		foreach ($features as $feature)
		{
			if (is_string($feature))
			{
				$check = self::checkFeature($feature, $params);
				if (!$check)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks that the feature is enabled.
	 * @param string $feature Feature code.
	 * @param array $params Additional params array.
	 * @return boolean
	 */
	public static function checkFeature(string $feature, array $params = array()): bool
	{
		// temporary set features
		if (
			isset(self::$tmpFeatures[$feature]) &&
			self::$tmpFeatures[$feature]
		)
		{
			return true;
		}
		if (!isset($params['type']) || !$params['type'])
		{
			$params['type'] = 'PAGE';
		}

		if (
			$feature == self::FEATURE_CREATE_SITE ||
			$feature == self::FEATURE_PUBLICATION_SITE
		)
		{
			$params['action_type'] = ($feature == self::FEATURE_CREATE_SITE)
				? 'create' : 'publication';
			return Restriction\Manager::isAllowed(
				'limit_sites_number',
				$params
			);
		}
		else if (
			$feature == self::FEATURE_CREATE_PAGE ||
			$feature == self::FEATURE_PUBLICATION_PAGE
		)
		{
			$params['action_type'] = ($feature == self::FEATURE_CREATE_PAGE)
				? 'create' : 'publication';
			return Restriction\Manager::isAllowed(
				'limit_sites_number_page',
				$params
			);
		}
		elseif ($feature == self::FEATURE_ENABLE_ALL_HOOKS)
		{
			if (isset($params['hook']))
			{
				return Restriction\Hook::isHookAllowed($params['hook']);
			}
			return true;
		}
		elseif ($feature == self::FEATURE_PERMISSIONS_AVAILABLE)
		{
			return Restriction\Manager::isAllowed(
				'limit_sites_access_permissions'
			);
		}
		elseif ($feature == self::FEATURE_DYNAMIC_BLOCK)
		{
			return Restriction\Manager::isAllowed(
				'limit_sites_dynamic_blocks',
				$params
			);
		}
		elseif ($feature == self::FEATURE_FREE_DOMAIN)
		{
			return Restriction\Manager::isAllowed(
				'limit_free_domen'
			);
		}
		elseif ($feature == self::FEATURE_ALLOW_EXPORT)
		{
			return Restriction\Manager::isAllowed(
				'limit_sites_transfer'
			);
		}
		elseif ($feature == self::FEATURE_ALLOW_VIEW_PAGE)
		{
			return Restriction\Manager::isAllowed(
				'limit_knowledge_base_number_page_view',
				$params
			);
		}
		// for backward compatibility
		elseif ($feature == self::FEATURE_CUSTOM_DOMAIN)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get site zone (ru, ua, en, etc).
	 * @return string
	 */
	public static function getZone()
	{
		static $zone = null;

		if ($zone !== null)
		{
			return $zone;
		}

		$request = Application::getInstance()->getContext()->getRequest();
		if ($request->get('user_lang'))
		{
			$zone = $request->get('user_lang');
		}
		else if (Loader::includeModule('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		if (!isset($zone) || !$zone)
		{
			$zone = Application::getInstance()->getContext()->getLanguage();
		}

		return $zone;
	}

	/**
	 * Check if something is available in current country.
	 * @param string $zone Zone code.
	 * @return bool
	 */
	public static function availableOnlyForZone($zone)
	{
		static $available = null;

		if ($available !== null)
		{
			return $available;
		}

		$available = true;

		if ($zone == 'ru')
		{
			if (!in_array(Manager::getZone(), array('ru', 'by', 'kz')))
			{
				$available = false;
			}
		}

		return $available;
	}

	/**
	 * Is https?
	 * @return bool
	 */
	public static function isHttps()
	{
		static $isHttps = null;

		if ($isHttps === null)
		{
			$context = Application::getInstance()->getContext();
			$isHttps = $context->getRequest()->isHttps();
		}

		return $isHttps;
	}

	/**
	 * Get current host.
	 * @return string
	 */
	public static function getHttpHost()
	{
		static $host = null;

		if ($host === null)
		{
			$context = Application::getInstance()->getContext();
			$host = $context->getServer()->getHttpHost();

			// strip port
			if (mb_strpos($host, ':') !== false)
			{
				[$host] = explode(':', $host);
			}
		}

		return $host;
	}

	/**
	 * Get full url of local file.
	 * @param string $file Local file name.
	 * @return string
	 */
	public static function getUrlFromFile($file)
	{
		if (
			mb_substr($file, 0, 1) == '/' &&
			mb_substr($file, 0, 2) != '//' &&
			self::getHttpHost()
		)
		{
			return '//' .
				   self::getHttpHost() .
				   $file;
		}
		else
		{
			return $file;
		}
	}

	/**
	 * Is B24 portal?
	 * @return bool
	 */
	public static function isB24(): bool
	{
		static $return = null;

		if (self::$forceB24disable === true)
		{
			return false;
		}

		if ($return === null)
		{
			if (
				defined('LANDING_DISABLE_B24_MODE') &&
				LANDING_DISABLE_B24_MODE === true
			)
			{
				$return = false;
			}
			else
			{
				$return = ModuleManager::isModuleInstalled('bitrix24') ||
							ModuleManager::isModuleInstalled('crm') ||
							ModuleManager::isModuleInstalled('intranet');
			}
		}

		return $return;
	}


	/**
	 * Is Site Manager and B24 connector
	 * @return bool|null
	 */
	public static function isB24Connector(): bool
	{
		static $return = null;

		if ($return === null)
		{
			$return =
				!self::isB24()
				&& Loader::includeModule('b24connector')
				&& Loader::includeModule('socialservices');
		}

		return $return;
	}

	/**
	 * Sets local flag to new state.
	 * @param boolean $flag Disable or not.
	 * @return void
	 */
	public static function forceB24disable($flag)
	{
		self::$forceB24disable = $flag === true;
	}

	/**
	 * Returns true, if SMN is extended for CRM.
	 * @return bool
	 */
	public static function isExtendedSMN()
	{
		static $option = null;

		if ($option === null)
		{
			$option = self::getOption('smn_extended', 'N') == 'Y';
		}

		return $option;
	}

	/**
	 * Enable or not shops in this edition.
	 * @return bool
	 */
	public static function isStoreEnabled()
	{
		return ModuleManager::isModuleInstalled('sale') &&
			   ModuleManager::isModuleInstalled('catalog') &&
			   ModuleManager::isModuleInstalled('iblock');
	}

	/**
	 * Get current REST url for work with cloud.
	 * @deprecated since 20.2.100
	 * @return string
	 */
	public static function getRestPath(): string
	{
		return '';
	}

	/**
	 * Check if cloud is disabled by settings.
	 * @return bool
	 */
	public static function isCloudDisable()
	{
		return defined('LANDING_DISABLE_CLOUD') &&
			   LANDING_DISABLE_CLOUD === true;
	}


	/**
	 * Get module version.
	 * @return string
	 */
	public static function getVersion()
	{
		static $arModuleVersion = null;

		if ($arModuleVersion === null)
		{
			$arModuleVersion = [];
			include self::getDocRoot() . '/bitrix/modules/landing/install/version.php';
		}

		return isset($arModuleVersion['VERSION']) ? $arModuleVersion['VERSION'] : null;
	}

	/**
	 * Check if license is expired.
	 * @return bool
	 */
	public static function licenseIsValid()
	{
		$finishDate = Option::get('main', '~support_finish_date');
		$finishDate = \makeTimestamp($finishDate, 'YYYY-MM-DD');
		if ($finishDate < time())
		{
			return false;
		}
		return true;
	}

	/**
	 * Sanitize bad value.
	 * @param string $value Bad value.
	 * @param bool &$bad Return true, if value is bad.
	 * @param string $splitter Splitter for bad content.
	 * @return string Good value.
	 */
	public static function sanitize($value, &$bad = false, $splitter = ' ')
	{
		static $sanitizer = null;

		if (!is_bool($bad))
		{
			$bad = false;
		}

		if ($sanitizer === null)
		{
			$sanitizer = false;
			if (Loader::includeModule('security'))
			{
				$sanitizer = new \Bitrix\Security\Filter\Auditor\Xss(
					$splitter
				);
			}
		}

		if ($sanitizer)
		{
			// bad value exists
			if (is_array($value))
			{
				foreach ($value as &$val)
				{
					$val = self::sanitize($val, $bad, $splitter);
				}
				unset($val);
			}
			else if ($sanitizer->process($value))
			{
				$bad = true;
				$value = $sanitizer->getFilteredValue();
				$value = str_replace(
					' bxstyle="',
					' style="',
					$value
				);
			}
			else
			{
				$value = str_replace(
					[
						' bxstyle="',
						'<?', '?>'
					],
					[
						' style="',
						'< ?', '? >'
					],
					$value
				);
			}
		}

		return $value;
	}

	/**
	 * Get deleted life time days.
	 * @return int
	 */
	public static function getDeletedLT()
	{
		$deletedDays = (int) Manager::getOption('deleted_lifetime_days', 30);
		$deletedDays = max(1, $deletedDays);
		return $deletedDays;
	}

	/**
	 * Return site controller class, or pseudo.
	 * @return string
	 */
	public static function getExternalSiteController()
	{
		static $class = '';

		if (!$class)
		{
			if (class_exists('\LandingSiteController'))
			{
				$class = '\LandingSiteController';
			}
			else if (
				Loader::includeModule('bitrix24') &&
				class_exists('\Bitrix\Bitrix24\SiteController')
			)
			{
				$class = '\Bitrix\Bitrix24\SiteController';
			}
			else if (class_exists('\Bitrix\Landing\External\Site24'))
			{
				$class = '\Bitrix\Landing\External\Site24';
			}
		}

		return $class;
	}

	/**
	 * In cloud version reset highest plans to free.
	 * @return void
	 */
	public static function resetToFree()
	{
		// for clear cache in cloud
		$res = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ACTIVE' => 'Y'
			]
		]);
		while ($row = $res->fetch())
		{
			Site::update($row['ID'], []);
		}
		self::setOption('html_disabled', 'Y');
	}

	/**
	 * Clear cache, if repository version and current is different.
	 * @deprecated since 20.2.100
	 * @return void
	 */
	public static function checkRepositoryVersion()
	{
	}
}
