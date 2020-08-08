<?php
namespace Bitrix\Rest\Marketplace\Urls
{
	class Templates
	{
		protected $directory = "marketplace/";
		protected $pages = [
			"index" => "",
			"list" => "list/",
			"detail" => "detail/#ID#/",
			"edit" => "edit/#ID#/"];
		private static $localDir = null;

		final public static function getInstance()
		{
			static $instance = null;

			if (null === $instance)
			{
				$instance = new static();
			}
			return $instance;
		}

		public function getIndexUrl()
		{
			return $this->getDir().$this->pages["index"];
		}

		public function getDetailUrl($id = null)
		{
			return $this->getReplacedId($this->pages["detail"], $id);
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
			if (substr($res, 0, -1) !== \Bitrix\Main\IO\Path::DIRECTORY_SEPARATOR)
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
			"placement" => "placement/#PLACEMENT_ID#/"
		];

		public function getCategoryUrl($id = null)
		{
			if ($id === null)
			{
				return $this->getReplacedId($this->pages["index"]);
			}
			return $this->getReplacedId($this->pages["category"], $id);
		}

		public function getPlacementUrl($placementId, $params)
		{
			$placementId = intVal($placementId);
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
			'import_manifest' => 'import_#MANIFEST_CODE#/',
			'export' => 'export_#MANIFEST_CODE#/',
			'export_element' => 'export_#MANIFEST_CODE#/#ITEM_CODE#/'
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
			$url = $this->getReplaced($this->pages["placement"], $replace, $subject);

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
			return $this->getReplaced($this->pages["section"], $replace, $subject);
		}

		public function getImport()
		{
			return $this->getReplaced($this->pages["import"]);
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
			return $this->getReplaced($this->pages["import_manifest"], $replace, $subject);
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
			return $this->getReplaced($this->pages["import_app"], $replace, $subject);
		}

		public function getImportRollback($appCode)
		{
			$replace = [
				'#APP#'
			];
			$subject = [
				$appCode
			];

			return $this->getReplaced($this->pages["import_rollback"], $replace, $subject);
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
			return $this->getReplaced($this->pages["export"], $replace, $subject);
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
			return $this->getReplaced($this->pages["export_element"], $replace, $subject);
		}

		protected function getReplaced(string $url, $replace = null, $subject = null)
		{
			$url = $this->getDir().$url;
			if (!is_null($replace) && !is_null($subject))
				$url = str_replace($replace, $subject, $url);
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

	class Url
	{
		public static function getCategoryUrl($id = null)
		{
			return MarketplaceUrls::getInstance()->getCategoryUrl($id);
		}

		public static function getApplicationDetailUrl($id = null)
		{
			return MarketplaceUrls::getInstance()->getDetailUrl($id);
		}
		public static function getApplicationUrl($id = null)
		{
			return ApplicationUrls::getInstance()->getDetailUrl($id);
		}
		public static function getApplicationAddUrl()
		{
			return LocalApplicationUrls::getInstance()->getIndexUrl();
		}
		public static function getWidgetAddUrl()
		{
			return "";
		}

		public static function getApplicationPlacementUrl($placementId = null, $params = null)
		{
			return MarketplaceUrls::getInstance()->getPlacementUrl($placementId, $params);
		}

		public static function getApplicationPlacementViewUrl($appCode = null, $params = null)
		{
			return MarketplaceUrls::getInstance()->getPlacementViewUrl($appCode, $params);
		}

		public static function getMarketplaceUrl()
		{
			return MarketplaceUrls::getInstance()->getIndexUrl();
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

		public static function getConfigurationExportUrl($manifestCode = null)
		{
			return Configuration::getInstance()->getExport($manifestCode);
		}

		public static function getConfigurationExportElementUrl($manifestCode = null, $itemCode = null)
		{
			return Configuration::getInstance()->getExportElement($manifestCode, $itemCode);
		}
	}
}

