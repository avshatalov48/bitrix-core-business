<?php
namespace Bitrix\Rest\Marketplace\Urls
{

	use Bitrix\Main\Config\Option;
	use Bitrix\Main\ModuleManager;
	use Bitrix\Rest\Marketplace\Client;

	class Templates
	{
		protected $directory = "marketplace/";
		protected $directoryAdmin = '/bitrix/admin/';
		protected $pages = [
			"index" => "",
			"list" => "list/",
			"detail" => "detail/#ID#/",
			"edit" => "edit/#ID#/",
		];
		protected $pageListAdmin = [];
		protected $adminSectionMode = false;
		private static $localDir = null;

		private function __construct()
		{
			if (defined('ADMIN_SECTION'))
			{
				$this->adminSectionMode = true;
			}
		}

		protected function getPage(string $code): string
		{
			return $this->adminSectionMode && !empty($this->pageListAdmin[$code])
				? $this->directoryAdmin . $this->pageListAdmin[$code]
				: $this->getDir() . $this->pages[$code]
			;
		}

		final public static function getInstance()
		{
			static $instance = null;

			if (null === $instance)
			{
				$instance = new static();
			}
			return $instance;
		}

		/**
		 * @param $url string
		 * @param $from string
		 *
		 * @return string
		 */
		public function addUrlFrom($url, $from) : string
		{
			if($from !== '')
			{
				if (mb_strpos($url, '?') === false)
				{
					$url .= '?from=' . $from;
				}
				else
				{
					$url .= '&from=' . $from;
				}
			}

			return $url;
		}

		public function getIndexUrl($from = '')
		{
			$url = $this->getDir() . $this->pages["index"];
			$url = $this->addUrlFrom($url, $from);

			return $url;
		}

		public function getDetailUrl($id = null, $from = '')
		{
			$url = $this->getReplacedId($this->pages["detail"], $id);
			$url = $this->addUrlFrom($url, $from);

			return $url;
		}

		public function getEditUrl($id = null)
		{
			return $this->getReplacedId($this->pages["edit"], $id);
		}

		public function getDir()
		{
			if (null === self::$localDir)
			{
				$siteId = SITE_ID;
				self::$localDir = \Bitrix\Main\IO\Path::DIRECTORY_SEPARATOR;
				if (($site = \CSite::getById($siteId)->fetch()) && !empty($site["DIR"]))
				{
					$path = [\Bitrix\Main\SiteTable::getDocumentRoot($siteId), $site["DIR"], $this->directory];
					$dir = new \Bitrix\Main\IO\Directory(\Bitrix\Main\IO\Path::combine($path), $siteId);
					if ($dir->isExists())
					{
						self::$localDir = \Bitrix\Main\IO\Path::combine([self::$localDir, $site["DIR"]]);
					}
				}
			}
			$res = \Bitrix\Main\IO\Path::combine([self::$localDir, $this->directory]);
			if (mb_substr($res, 0, -1) !== \Bitrix\Main\IO\Path::DIRECTORY_SEPARATOR)
				$res .= \Bitrix\Main\IO\Path::DIRECTORY_SEPARATOR;
			return $res;
		}

		protected function getReplacedId(string $url, $id = null)
		{
			$url = $this->getDir().$url;
			if (!is_null($id))
				$url = str_replace("#ID#", $id, $url);
			return $url;
		}

		protected function getReplaced(string $url, $replace = null, $subject = null)
		{
			$url = $this->getDir().$url;
			if (!is_null($replace) && !is_null($subject))
				$url = str_replace($replace, $subject, $url);
			return $url;
		}
	}

	class Marketplace extends Templates
	{
		protected $directory = "marketplace/";
		protected $pages = [
			"index" => "",
			"list" => "installed/",
			"detail" => "detail/#ID#/",
			"category" => "category/#ID#/",
			"placement_view" => "view/#APP#/",
			"placement" => "placement/#PLACEMENT_ID#/",
			"booklet" => "booklet/#CODE#/"
		];

		public function getCategoryUrl($id = null)
		{
			if ($id === null)
			{
				return $this->getReplacedId($this->pages["index"]);
			}
			return $this->getReplacedId($this->pages["category"], $id);
		}

		public function getSubscriptionBuyUrl()
		{
			$result = '';
			if (ModuleManager::isModuleInstalled('bitrix24'))
			{
				$result = '/settings/license_buy.php?product=subscr';
			}
			else
			{
				$region = Option::get('main', '~PARAM_CLIENT_LANG', LANGUAGE_ID);

				if ($region === 'ru')
				{
					$result = 'https://www.1c-bitrix.ru/buy/products/b24.php?subscr=y';
				}
				elseif ($region === 'ua')
				{
					$result = 'https://www.bitrix.ua/buy/products/b24.php?subscr=y';
				}
			}

			return $result;
		}

		public function getPlacementUrl($placementId, $params)
		{
			$placementId = intval($placementId);
			$replace = null;
			$subject = null;
			if ($placementId > 0)
			{
				$replace = [
					'#PLACEMENT_ID#'
				];
				$subject = [
					$placementId
				];
			}
			$url = $this->getReplaced($this->pages["placement"], $replace, $subject);

			if(is_array($params))
			{
				$uri = new \Bitrix\Main\Web\Uri($url);
				$uri->addParams(
					[
						'params' => $params
					]
				);
				$url = $uri->getUri();
			}
			return $url;
		}

		public function getBooklet($code = null, $from = '')
		{
			$replace = null;
			$subject = null;
			if (!is_null($code))
			{
				$replace = [
					"#CODE#"
				];
				$subject = [
					$code
				];
			}
			$url = $this->getReplaced($this->pages["booklet"], $replace, $subject);

			$url = $this->addUrlFrom($url, $from);

			return $url;
		}

		public function getPlacementViewUrl($appCode, $params)
		{
			$replace = null;
			$subject = null;
			if ($appCode)
			{
				$replace = [
					'#APP#'
				];
				$subject = [
					$appCode
				];
			}
			$url = $this->getReplaced($this->pages["placement_view"], $replace, $subject);

			if (is_array($params))
			{
				$uri = new \Bitrix\Main\Web\Uri($url);
				$uri->addParams(
					[
						'params' => $params
					]
				);
				$url = $uri->getUri();
			}

			return $url;
		}
	}
	class Application extends Templates
	{
		protected $directory = "marketplace/app/";
		protected $pages = [
			"index" => "",
			"list" => "",
			"detail" => "#ID#/",
			"edit" => "edit/#ID#/"
		];
	}

	class LocalApplication extends Templates
	{
		protected $directory = "marketplace/local/";
		protected $pages = [
			"index" => "",
			"list" => "list/",
			"detail" => "detail/#ID#/",
			"edit" => "edit/#ID#/"
		];
	}

	class Configuration extends Templates
	{
		protected $directory = 'marketplace/configuration/';
		protected $pages = [
			'index' => '',
			'placement' => 'placement/#PLACEMENT_CODE#/',
			'section' => 'section/#MANIFEST_CODE#/',
			'import' => 'import/',
			'import_app' => 'import/#APP_CODE#/',
			'import_rollback' => 'import_rollback/#APP#/',
			'import_zip' => 'import_zip/#ZIP_ID#/',
			'import_manifest' => 'import_#MANIFEST_CODE#/',
			'export' => 'export_#MANIFEST_CODE#/',
			'export_element' => 'export_#MANIFEST_CODE#/#ITEM_CODE#/'
		];

		protected $pageListAdmin = [
			'import_zip' => 'rest_import_zip.php?id=#ZIP_ID#',
		];

		public function getPlacement($code = null, $context = null)
		{
			$replace = null;
			$subject = null;
			if (!is_null($code))
			{
				$replace = [
					'#PLACEMENT_CODE#'
				];
				$subject = [
					$code
				];
			}
			$url = $this->getReplaced($this->getPage('placement'), $replace, $subject);

			if(!is_null($context))
			{
				$uri = new \Bitrix\Main\Web\Uri($url);
				$uri->addParams(
					[
						"from" => $context
					]
				);
				$url = $uri->getUri();
			}
			return $url;
		}

		public function getSection($manifestCode = null)
		{
			$replace = null;
			$subject = null;
			if (!is_null($manifestCode))
			{
				$replace = [
					'#MANIFEST_CODE#'
				];
				$subject = [
					$manifestCode
				];
			}
			return $this->getReplaced($this->getPage('section'), $replace, $subject);
		}

		public function getImport()
		{
			return $this->getReplaced($this->getPage('import'));
		}

		public function getImportManifest($manifestCode)
		{
			$replace = null;
			$subject = null;
			if (!is_null($manifestCode))
			{
				$replace = [
					'#MANIFEST_CODE#'
				];
				$subject = [
					$manifestCode
				];
			}
			return $this->getReplaced($this->getPage('import_manifest'), $replace, $subject);
		}

		public function getImportApp($code = null)
		{
			$replace = null;
			$subject = null;
			if (!is_null($code))
			{
				$replace = [
					'#APP_CODE#'
				];
				$subject = [
					$code
				];
			}
			return $this->getReplaced($this->getPage('import_app'), $replace, $subject);
		}

		public function getImportRollback($appCode)
		{
			$replace = [
				'#APP#'
			];
			$subject = [
				$appCode
			];

			return $this->getReplaced($this->getPage('import_rollback'), $replace, $subject);
		}

		public function getImportZip($zipId)
		{
			$replace = [
				'#ZIP_ID#'
			];
			$subject = [
				(int) $zipId
			];

			return $this->getReplaced($this->getPage('import_zip'), $replace, $subject);
		}

		public function getExport($manifestCode = null)
		{
			$replace = null;
			$subject = null;
			if (!is_null($manifestCode))
			{
				$replace = [
					'#MANIFEST_CODE#'
				];
				$subject = [
					$manifestCode
				];
			}
			return $this->getReplaced($this->getPage('export'), $replace, $subject);
		}

		public function getExportElement($manifestCode = null, $itemCode = null)
		{
			$replace = null;
			$subject = null;
			if (!is_null($manifestCode))
			{
				$replace = [
					'#MANIFEST_CODE#',
					'#ITEM_CODE#'
				];
				$subject = [
					$manifestCode,
					$itemCode
				];
			}
			return $this->getReplaced($this->getPage('export_element'), $replace, $subject);
		}

		protected function getReplaced(string $url, $replace = null, $subject = null)
		{
			if (!is_null($replace) && !is_null($subject))
			{
				$url = str_replace($replace, $subject, $url);
			}

			return $url;
		}
	}
}
namespace Bitrix\Rest\Marketplace
{

	use Bitrix\Rest\Marketplace\Urls\Marketplace as MarketplaceUrls;
	use Bitrix\Rest\Marketplace\Urls\Application as ApplicationUrls;
	use Bitrix\Rest\Marketplace\Urls\LocalApplication as LocalApplicationUrls;
	use Bitrix\Rest\Marketplace\Urls\Configuration;
	use Bitrix\Rest\Url\DevOps;

	class Url
	{
		public static function getCategoryUrl($id = null)
		{
			return MarketplaceUrls::getInstance()->getCategoryUrl($id);
		}

		public static function getApplicationDetailUrl($id = null, $from = '')
		{
			return MarketplaceUrls::getInstance()->getDetailUrl($id, $from);
		}
		public static function getApplicationUrl($id = null)
		{
			return ApplicationUrls::getInstance()->getDetailUrl($id);
		}

		/**
		 * @see \Bitrix\Rest\Url\DevOps
		 * @deprecated
		 */
		public static function getApplicationAddUrl()
		{
			return LocalApplicationUrls::getInstance()->getIndexUrl();
		}
		public static function getWidgetAddUrl()
		{
			return "";
		}

		/**
		 * @deprecated use \Bitrix\Rest\Url\DevOps->getPlacementUrl()
		 *
		 * @param null $placementId
		 * @param null $params
		 * @return string
		 */
		public static function getApplicationPlacementUrl($placementId = null, $params = null)
		{
			return DevOps::getInstance()->getPlacementUrl((int)$placementId, $params);
		}

		public static function getApplicationPlacementViewUrl($appCode = null, $params = null)
		{
			return MarketplaceUrls::getInstance()->getPlacementViewUrl($appCode, $params);
		}

		public static function getMarketplaceUrl($from = '')
		{
			return MarketplaceUrls::getInstance()->getIndexUrl($from);
		}

		public static function getBookletUrl($code = null, $from = '')
		{
			return MarketplaceUrls::getInstance()->getBooklet($code, $from);
		}

		public static function getConfigurationUrl()
		{
			return Configuration::getInstance()->getIndexUrl();
		}

		public static function getConfigurationPlacementUrl($code = null, $context = null)
		{
			return Configuration::getInstance()->getPlacement($code, $context);
		}

		public static function getConfigurationSectionUrl($manifestCode = null)
		{
			return Configuration::getInstance()->getSection($manifestCode);
		}

		public static function getConfigurationImportUrl()
		{
			return Configuration::getInstance()->getImport();
		}

		public static function getConfigurationImportManifestUrl($code)
		{
			return Configuration::getInstance()->getImportManifest($code);
		}

		public static function getConfigurationImportAppUrl($code = null)
		{
			return Configuration::getInstance()->getImportApp($code);
		}

		public static function getConfigurationImportRollbackUrl($appCode)
		{
			return Configuration::getInstance()->getImportRollback($appCode);
		}

		public static function getConfigurationImportZipUrl($zipId)
		{
			return Configuration::getInstance()->getImportZip($zipId);
		}

		public static function getConfigurationExportUrl($manifestCode = null)
		{
			return Configuration::getInstance()->getExport($manifestCode);
		}

		public static function getConfigurationExportElementUrl($manifestCode = null, $itemCode = null)
		{
			return Configuration::getInstance()->getExportElement($manifestCode, $itemCode);
		}

		public static function getSubscriptionBuyUrl() : string
		{
			return MarketplaceUrls::getInstance()->getSubscriptionBuyUrl();
		}
	}
}

