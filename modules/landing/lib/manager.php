<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Landing\Assets;

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
	const BUY_LICENSE_PATH = '/bitrix/tools/landing/ajax.php?redirect=upgrade';

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
	 * Returns main site row by id.
	 * @param string $siteId Main site id.
	 * @return array|bool
	 */
	protected static function getMainSiteById(string $siteId)
	{
		static $sites = [];

		if (!array_key_exists($siteId, $sites))
		{
			$sites[$siteId] = \Bitrix\Main\SiteTable::getById($siteId)->fetch();
		}

		return $sites[$siteId];
	}

	/**
	 * Create system dir for publication sites.
	 * @param string $basePath Publication physical dir.
	 * @param string|null $siteId Main site id.
	 * @return void
	 */
	protected static function createPublicationPath(string $basePath, string $siteId = null): void
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
				if ($smnSite = self::getMainSiteById($siteId))
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
				$fields = [
					'SORT' => 0,
					'SITE_ID' => $siteId,
					'CONDITION' => 'CSite::inDir(\'' . $subDirSite . $basePathOriginal . '\')',
					'TEMPLATE' => self::getTemplateId($siteId),
				];
				$check = \Bitrix\Main\SiteTemplateTable::getList(array(
					 'filter' => [
						 '=SITE_ID' => $fields['SITE_ID'],
						 '=CONDITION' => $fields['CONDITION'],
						 '=TEMPLATE' => $fields['TEMPLATE'],
					 ],
				 ))->fetch();
				if (!$check)
				{
					\Bitrix\Main\SiteTemplateTable::add(
						$fields
					);
					\Bitrix\Main\UrlRewriter::add(
						$siteId,
						[
							'ID' => 'bitrix:landing.pub',
							'PATH' => $subDirSite. $basePathOriginal . 'index.php',
							'CONDITION' => '#^' . $subDirSite. $basePathOriginal . '#',
						]
					);
				}
			}
		}
	}

	/**
	 * Get main site local dir.
	 * @param string|null $siteId Main site LID.
	 * @return string
	 */
	protected static function getSmnSiteDir(?string $siteId): string
	{
		static $sites = [];

		if (!$siteId)
		{
			$siteId = SITE_ID;
		}

		if (!isset($sites[$siteId]))
		{
			$sites[$siteId] = '';
			if ($smnSite = self::getMainSiteById($siteId))
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
		$typePublicationPath = Site\Type::getPublicationPath();

		$basePath = $typePublicationPath;
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

	public static function isAutoPublicationEnabled(): bool
	{
		return
			Site\Type::isPublicScope()
			&& \CUserOptions::getOption('landing', 'auto_publication', 'Y') === 'Y'
		;
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
	 * Adds content in specific area named by marker.
	 *
	 * @param string $marker Marker name.
	 * @param string $content Content.
	 * @param bool $skipTrim If true content will not be trimmed.
	 * @return void
	 */
	public static function setPageView(string $marker, string $content, bool $skipTrim = false): void
	{
		if (!$skipTrim)
		{
			$content = trim($content);
		}

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
	 * Returns randomize string.
	 * @param int $length String length.
	 * @return string
	 */
	public static function getRandomString(int $length): string
	{
		return mb_strtolower(\Bitrix\Main\Security\Random::getStringByCharsets($length, 'abcdefghijklmnopqrstuvwxyz'));
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
	 * Add assets to page from hooks and themes
	 * @param int $lid Landing id.
	 * @return void
	 */
	public static function initAssets($lid = 0)
	{
		$assets = Assets\Manager::getInstance();
		$assets->setOutput($lid);
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
			$httpClient->setPrivateIp(false);
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

		$isSvg = false;
		$isImage = \CFile::checkImageFile($file, 0, 0, 0, array('IMAGE')) === null;

		if (!$isImage && (Manager::getOption('allow_svg_content') === 'Y'))
		{
			$extension = \getFileExtension(mb_strtolower($file['name']));
			if ($extension === 'svg')
			{
				$isSvg = true;
			}
		}

		// post array or file from prev. steps
		if ($isImage || $isSvg)
		{
			// resize if needed
			if (
				$isImage &&
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
			// if duplicate change size little (bug #167903)
			if ($isImage && self::isDuplicateExistsInAnotherModule($file['tmp_name'], $file['size']))
			{
				[$width, $height] = getimagesize($file['tmp_name']) ?: [0, 0];
				if ($width && $height)
				{
					\CFile::resizeImage($file, ['width' => $width-1, 'height' => $height-1]);
				}
			}
			// save
			$module = 'landing';
			$file['name'] = File::transliterateFileName($file['name']);
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
	 * Detects file duplicates by file path.
	 * @param string $filePath Full path to the file.
	 * @param int $size Size of the file.
	 * @return bool
	 */
	private static function isDuplicateExistsInAnotherModule(string $filePath, int $size): bool
	{
		$hash = self::calculateHash($filePath, $size);
		if (!$hash)
		{
			return false;
		}

		$original = \CFile::findDuplicate($size, $hash);
		if ($original === null)
		{
			return false;
		}

		// we allow duplicate only within from current module
		return $original->getFile()->getModuleId() !== 'landing';
	}

	/**
	 * Calculates a hash of the file.
	 * @see \CFile::CalculateHash
	 * @param string $filePath Full path to the file.
	 * @param int $size Size of the file.
	 * @return string
	 */
	private static function calculateHash(string $filePath, int $size): string
	{
		$hash = '';

		if ($size > 0 && Option::get('main', 'control_file_duplicates', 'N') === 'Y')
		{
			$maxSize = (int)Option::get('main', 'duplicates_max_size', '100') * 1024 * 1024; //Mbytes
			if ($size <= $maxSize || $maxSize === 0)
			{
				$hash = hash_file('md5', $filePath);
			}
		}

		return $hash;
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
				$params,
				$feature
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
				$params,
				$feature
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
	 * Returns language code by ISO 639-1.
	 * @return string
	 */
	public static function getLangISO(): string
	{
		$transform = [
			'br' => 'pt-BR',
			'la' => 'es',
			'sc' => 'zh-Hans',
			'tc' => 'zh-Hant',
			'vn' => 'vi',
			'ua' => 'uk',
			'in' => 'hi',
		];

		return $transform[LANGUAGE_ID] ?? LANGUAGE_ID;
	}

	/**
	 * Check if something is available in current country.
	 * @param string $zone Zone code.
	 * @return bool
	 */
	public static function availableOnlyForZone(string $zone): bool
	{
		static $available = null;

		if ($available !== null)
		{
			return $available;
		}

		$available = true;

		if ($zone === 'ru')
		{
			if (!in_array(self::getZone(), ['ru', 'by', 'kz']))
			{
				$available = false;
			}
		}

		return $available;
	}

	/**
	 * Return ID for market collection, by zone
	 * @param string $type name of collection type
	 * @return int
	 */
	public static function getMarketCollectionId(string $type): int
	{
		$zone = self::getZone();
		switch ($type)
		{
			case 'form_minisite':
				$minisites = [
					'ru' => 18108954,
					'by' => 18108962,
					'kz' => 18108964,
					'en' => 18108970,
				];

				return $minisites[$zone] ?? $minisites['en'];

			default:
				return 0;
		}
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
	 * Check is current hit is ajax
	 * @return bool
	 */
	public static function isAjaxRequest(): bool
	{
		return Application::getInstance()->getContext()->getRequest()->isAjaxRequest();
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
	 * Get URL of external template preview server.
	 * Can be rewrite by LANDING_PREVIEW_URL constant.
	 * @return string
	 */
	public static function getPreviewHost(): string
	{
		if (!defined('LANDING_PREVIEW_URL'))
		{
			define('LANDING_PREVIEW_URL', 'https://preview.bitrix24.site');
		}

		return LANDING_PREVIEW_URL;
	}

	/**
	 * Get webhook to the external template preview server.
	 * Can be rewrite by LANDING_PREVIEW_WEBHOOK constant.
	 * @return string
	 */
	public static function getPreviewWebhook(): string
	{
		if (!defined('LANDING_PREVIEW_WEBHOOK'))
		{
			define('LANDING_PREVIEW_WEBHOOK', 'https://preview.bitrix24.site/rest/1/gvsn3ngrn7vb4t1m/');
		}

		return LANDING_PREVIEW_WEBHOOK;
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
	 * Is b24 portal hosted no cloud (not box)
	 * @return bool
	 */
	public static function isB24Cloud(): bool
	{
		return self::isB24() && ModuleManager::isModuleInstalled('bitrix24');
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
	 * Is license free and it not a knowledge and not a store
	 * @param string $type Type of landing
	 * @return bool
	 */
	public static function licenseIsFreeSite(string $type): bool
	{
		return
			$type !== 'KNOWLEDGE'
			&& $type !== 'STORE'
			&& (!\CBitrix24::isLicensePaid() || \CBitrix24::getLicenseType() === 'alive')
			&& !\CBitrix24::IsNfrLicense()
		;
	}

	public static function isFreePublicAllowed(): bool
	{
		return in_array(self::getZone(), ['ru', 'by', 'kz', 'es', 'la', 'mx', 'co', 'br', 'in', 'hi']);
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
					[' bxstyle="', '<sv g ', '<?', '?>', '<fo rm '],
					[' style="', '<svg ', '< ?', '? >', '<form '],
					$value
				);
			}
			else
			{
				$value = str_replace(
					[' bxstyle="', '<sv g ', '<?', '?>', '<fo rm '],
					[' style="', '<svg ', '< ?', '? >', '<form '],
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
		self::clearCache();
		self::setOption('html_disabled', 'Y');
		self::setOption('reset_to_free_time', time());
		Restriction\Site::manageFreeDomains(false, Restriction\Site::FREE_DOMAIN_GRACE_DAYS * 86400);
	}

	/**
	 * In cloud version on change highest plans.
	 * @param string $licenseType License Type.
	 * @return void
	 */
	public static function onBitrix24LicenseChange(string $licenseType): void
	{
		self::clearCache();
		self::setOption('reset_to_free_time', 0);
		Restriction\Site::manageFreeDomains(true, 5);
		Restriction\Site::checkLimitsByLicenseChange();
		Restriction\Site::publishByLicenseChange();
	}

	/**
	 * In cloud version clear cache when tariff change
	 * @return void
	 */
	public static function clearCache(): void
	{
		// for clear cache in cloud
		if (!self::isB24())
		{
			return;
		}
		$res = Site::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'ACTIVE' => 'Y',
			],
		]);
		while ($row = $res->fetch())
		{
			Site::update($row['ID'], []);
		}
	}

	/**
	 * Clear cache in cloud only for one site
	 * @param int $siteId
	 */
	public static function clearCacheForSite(int $siteId): void
	{
		if (!self::isB24())
		{
			return;
		}

		Site::update($siteId, []);
	}
	/**
	 * Clear cache in cloud only for one site by landing ID
	 * @param int $lid
	 */
	public static function clearCacheForLanding(int $lid): void
	{
		if (!self::isB24())
		{
			return;
		}
		$res = Landing::getList([
			'select' => [
				'SITE_ID',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=DELETED' => 'N',
				'=ID' => $lid,
			],
		]);
		if ($row = $res->fetch())
		{
			Site::update($row['SITE_ID'], []);
		}
	}

	/**
	 * Clear cache, if repository version and current is different.
	 * @return void
	 * @deprecated since 20.2.100
	 */
	public static function checkRepositoryVersion()
	{
	}

	/**
	 * Get themes from template dir.
	 * @deprecated since 20.5.0
	 */
	public static function getThemes()
	{
	}

	/**
	 * Get themes typo from template dir.
	 * @deprecated since 20.3.0, use THEMEFONTS hook settings
	 */
	public static function getThemesTypo()
	{
	}

	/**
	 * Set new colored theme id.
	 * @deprecated since 20.5.0
	 */
	public static function setThemeId()
	{
	}

	/**
	 * Get current theme id.
	 * @deprecated since 20.5.0
	 */
	public static function getThemeId()
	{
	}

	/**
	 * Set current selected or default color theme.
	 * @deprecated since 20.5.0
	 */
	public static function setTheme()
	{
	}
}
