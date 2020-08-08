<?php
namespace Bitrix\Sale\CrmSiteMaster\Tools;

use Bitrix\Main,
	Bitrix\Catalog,
	Bitrix\Main\UrlRewriter,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Config\Configuration;

/**
 * Class SitePatcher
 * @package Bitrix\Sale\CrmSiteMaster\Tools
 */
class SitePatcher
{
	/** @var string */
	const CRM_WIZARD_SITE_ID = "~CRM_WIZARD_SITE_ID";
	const CRM_COMPANY_DEPARTMENT_ID = "~CRM_COMPANY_DEPARTMENT_ID";
	const SELECTED_USER_GROUPS = "~SELECTED_USER_GROUPS";
	const EMPLOYEE_USER_GROUP_ID = "~EMPLOYEE_USER_GROUP_ID";
	const CONFIG_1C = "~CONFIG_1C";
	const FORCE_ENABLE_SELF_HOSTED_COMPOSITE = "force_enable_self_hosted_composite";

	private static $instance;

	private $siteId;
	private $sitePath;
	private $siteDir;
	private $siteName;
	private $serverName;

	/**
	 * SitePatcher constructor.
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function __construct()
	{
		Main\Loader::includeModule("crm");
		Main\Loader::includeModule("iblock");
		Main\Loader::includeModule("catalog");

		$this->siteId = self::getCrmSiteId();
		$this->initSiteFields();
	}

	/**
	 * @return SitePatcher
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add url rewrite conditions
	 *
	 * @throws Main\ArgumentNullException
	 */
	public function addUrlRewrite()
	{
		$arUrlRewrite = array();
		if (Main\IO\File::isFileExists($this->getCrmSitePath()."/urlrewrite.php"))
		{
			include($this->getCrmSitePath()."/urlrewrite.php");
		}

		$arNewUrlRewrite = [];

		if (Main\ModuleManager::isModuleInstalled("disk"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."docs/pub/(?<hash>[0-9a-f]{32})/(?<action>[0-9a-zA-Z]+)/\?#",
				"RULE" => "hash=$1&action=$2&",
				"ID" => "bitrix:disk.external.link",
				"PATH" => $this->getCrmSiteDir()."docs/pub/index.php"
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."disk/(?<action>[0-9a-zA-Z]+)/(?<fileId>[0-9]+)/\?#",
				"RULE" => "action=$1&fileId=$2&",
				"ID" => "bitrix:disk.services",
				"PATH" => "/bitrix/services/disk/index.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("crm"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."pub/pay/([\\w\\W]+)/([0-9a-zA-Z]+)/([^/]*)#",
				"RULE" => "account_number=$1&hash=$2",
				"PATH" => $this->getCrmSiteDir()."pub/payment.php",
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."crm/invoicing/#",
				"RULE" => "",
				"PATH" => $this->getCrmSiteDir()."crm/invoicing/index.php",
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."stssync/contacts_crm/#",
				"RULE" => "",
				"ID" => "bitrix:stssync.server",
				"PATH" => "/bitrix/services/stssync/contacts_crm/index.php",
			];

			$arNewUrlRewrite[] = [
				'CONDITION' => "#^".$this->getCrmSiteDir()."shop/orderform/#",
				'RULE' => "",
				'ID' => "bitrix:crm.order.matcher",
				'PATH' => $this->getCrmSiteDir()."shop/orderform/index.php",
			];

			$arNewUrlRewrite[] = [
				'CONDITION' => "#^".$this->getCrmSiteDir()."shop/buyer_group/#",
				'RULE' => "",
				'ID' => "bitrix:crm.order.buyer_group",
				'PATH' => $this->getCrmSiteDir()."shop/buyer_group/index.php",
			];

			$arNewUrlRewrite[] = [
				'CONDITION' => "#^".$this->getCrmSiteDir()."shop/buyer/#",
				'RULE' => "",
				'ID' => "bitrix:crm.order.buyer",
				'PATH' => $this->getCrmSiteDir()."shop/buyer/index.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("intranet"))
		{
			$arNewUrlRewrite[] = [
				'CONDITION' => "#^".$this->getCrmSiteDir()."stssync/contacts/#",
				'RULE' => "",
				'ID' => "bitrix:stssync.server",
				'PATH' => "/bitrix/services/stssync/contacts/index.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("tasks"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."stssync/tasks/#",
				"RULE" => "",
				"ID" => "bitrix:stssync.server",
				"PATH" => "/bitrix/services/stssync/tasks/index.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("dav"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^".$this->getCrmSiteDir()."\\.well-known#",
				"RULE" => "",
				"ID" => "",
				"PATH" => "/bitrix/groupdav.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("mobile"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^\/?\/mobile/mobile_component\/(.*)\/.*#",
				"RULE" => "componentName=$1",
				"PATH" => "/bitrix/services/mobile/jscomponent.php",
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^\/?\/mobile/jn\/(.*)\/.*#",
				"RULE" => "componentName=$1",
				"PATH" => "/bitrix/services/mobile/jscomponent.php",
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^\/?\/mobile/jn/(.*)\/(.*)\/.*#",
				"RULE" => "componentName=$2&namespace=$1",
				"PATH" => "/bitrix/services/mobile/jscomponent.php",
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^\/?\/mobile/web_mobile_component\/(.*)\/.*#",
				"RULE" => "componentName=$1",
				"PATH" => "/bitrix/services/mobile/webcomponent.php",
			];

			$arNewUrlRewrite[] = [
				"CONDITION" => "#^/mobile/disk/(?<hash>[0-9]+)/download#",
				"RULE" => "download=1&objectId=\$1",
				"ID" => "bitrix:mobile.disk.file.detail",
				"PATH" => "/mobile/disk/index.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("mobileapp"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^\/?\/mobileapp/jn\/(.*)\/.*#",
				"RULE" => "componentName=$1",
				"PATH" => "/bitrix/services/mobileapp/jn.php",
			];
		}

		if (Main\ModuleManager::isModuleInstalled("rest"))
		{
			$arNewUrlRewrite[] = [
				"CONDITION" => "#^/rest/#",
				"RULE" => "",
				"PATH" => "/bitrix/services/rest/index.php",
			];
		}

		foreach ($arNewUrlRewrite as $arUrl)
		{
			if (!in_array($arUrl, $arUrlRewrite))
			{
				UrlRewriter::add($this->siteId, $arUrl);
			}
		}
	}

	/**
	 * Delete old extra files
	 */
	public function deleteFiles()
	{
		// public
		$arToDelete = array(
			".left.menu.php",
			".left.menu_ext.php",
			".left.menu_ext_old.php",
			"crm/company/.left.menu.php",
			"crm/company/.left.menu_ext.php",
			"crm/configs/.left.menu.php",
			"crm/configs/.left.menu_ext.php",
			"crm/contact/.left.menu.php",
			"crm/contact/.left.menu_ext.php",
			"crm/deal/.left.menu.php",
			"crm/deal/.left.menu_ext.php",
			"crm/invoice/.left.menu.php",
			"crm/invoice/.left.menu_ext.php",
			"crm/lead/.left.menu.php",
			"crm/lead/.left.menu_ext.php",
			"crm/quote/.left.menu.php",
			"crm/quote/.left.menu_ext.php",
			"crm/reports/.left.menu.php",
			"crm/reports/.left.menu_ext.php",
			"crm/events/.left.menu.php",
			"crm/events/.left.menu_ext.php",
			"services/openlines/edit.php",
			"company/personal/mail/index.php",
			"crm/configs/emailtracker/index.php",
		);

		foreach ($arToDelete as $file)
		{
			$this->deleteDirFiles($this->getCrmSitePath().$this->getCrmSiteDir().$file);
		}

		$arToDelete = array(
			"company/personal/mail/",
			"crm/configs/emailtracker/",
		);

		foreach ($arToDelete as $file)
		{
			if (!glob($this->getCrmSitePath().$this->getCrmSiteDir().$file."*"))
			{
				$this->deleteDirFiles($this->getCrmSitePath().$this->getCrmSiteDir().$file);
			}
		}

		// kernel
		$arToDelete = array(
			"components/bitrix/intranet.contact_center.list/lang/de/class.php",
			"components/bitrix/intranet.contact_center.list/lang/en/class.php",
			"components/bitrix/intranet.contact_center.list/lang/ru/class.php",
			"components/bitrix/intranet.contact_center.list/lang/ua/class.php",
			"components/bitrix/intranet.contact_center.list/templates/.default/result_modifier.php",
			"components/bitrix/intranet.otp.info/templates/.default/images/otp_trans_speed.gif",
			"components/bitrix/intranet.otp.info/templates/.default/images/otp_trans_speed_bg.gif",
			"modules/intranet/install/components/bitrix/intranet.contact_center.list/lang/de/class.php",
			"modules/intranet/install/components/bitrix/intranet.contact_center.list/lang/en/class.php",
			"modules/intranet/install/components/bitrix/intranet.contact_center.list/lang/ru/class.php",
			"modules/intranet/install/components/bitrix/intranet.contact_center.list/lang/ua/class.php",
			"modules/intranet/install/components/bitrix/intranet.contact_center.list/templates/.default/result_modifier.php",
			"modules/intranet/install/components/bitrix/intranet.otp.info/templates/.default/images/otp_trans_speed.gif",
			"modules/intranet/install/components/bitrix/intranet.otp.info/templates/.default/images/otp_trans_speed_bg.gif",
			"modules/intranet/install/js/intranet/phonenumber/base/countries.json",
			"modules/intranet/install/js/intranet/phonenumber/base/country_ru.json",
			"modules/intranet/install/js/intranet/phonenumber/base/country_ua.json",
			"modules/intranet/install/js/intranet/phonenumber/css/flag.css",
			"modules/intranet/install/js/intranet/phonenumber/images/LICENSE.txt",
			"modules/intranet/install/js/intranet/phonenumber/images/sprite-16.png",
			"modules/intranet/install/js/intranet/phonenumber/images/sprite-24.png",
			"modules/intranet/install/js/intranet/phonenumber/images/sprite-32.png",
			"modules/intranet/install/js/intranet/phonenumber/phonenumber.js",
			"modules/intranet/install/public/bitrix24/company/personal/mail/index.php",
			"modules/intranet/install/public/bitrix24/crm/analytics/index.php",
			"modules/intranet/install/public/bitrix24/crm/configs/emailtracker/index.php",
			"modules/intranet/install/public/bitrix24/crm/reportboard/index.php",
			"modules/intranet/install/public/bitrix24/report/analytics/index.php",
			"modules/intranet/install/templates/bitrix24/components/bitrix/breadcrumb/",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Bold.eot",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Bold.ttf",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Bold.woff",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Light.eot",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Light.ttf",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Light.woff",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Regular.eot",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Regular.ttf",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Regular.woff",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Semibold.eot",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Semibold.ttf",
			"modules/intranet/install/templates/bitrix24/components/bitrix/menu/left_vertical/fonts/OpenSans-Semibold.woff",
			"modules/intranet/install/templates/bitrix24/components/bitrix/socialnetwork.group/short/",
			"modules/intranet/install/templates/bitrix24/components/bitrix/socialnetwork.group_menu/.default/script.js",
			"modules/intranet/install/templates/bitrix24/components/bitrix/socialnetwork.group_menu/.default/style.css",
			"modules/intranet/install/templates/bitrix24/components/bitrix/socialnetwork.log.filter",
			"modules/intranet/install/templates/bitrix24/components/bitrix/socialnetwork.user_groups.link.add/.default/images/group-list-sprite.png",
			"modules/intranet/install/templates/bitrix24/components/bitrix/system.auth.form/.default/functions.php",
			"modules/intranet/install/templates/bitrix24/components/bitrix/system.auth.form/.default/style.css",
			"modules/intranet/install/templates/bitrix24/slider",
			"modules/intranet/install/templates/bitrix24/telephony.css",
			"modules/intranet/install/tools/ws_calendar/",
			"modules/intranet/install/tools/ws_contacts/",
			"modules/intranet/install/tools/ws_tasks/",
			"modules/intranet/install/wizards/bitrix/portal/site/public/crm/reportboard/index.php",
			"modules/intranet/install/wizards/bitrix/portal/site/public/services/openlines/edit.php",
			"modules/intranet/install/wizards/bitrix/portal/site/public/timeman/bitrix24time.php",
			"modules/intranet/install/wizards/bitrix/portal/site/services/main/lang/de/property_names.php",
			"modules/intranet/install/wizards/bitrix/portal/site/services/main/lang/en/property_names.php",
			"modules/intranet/install/wizards/bitrix/portal/site/services/main/lang/ru/property_names.php",
			"modules/intranet/install/wizards/bitrix/portal/site/services/main/lang/ua/property_names.php",
			"modules/intranet/install/wizards/bitrix/portal/site/services/main/property.php",
			"modules/intranet/lang/de/include.php",
			"modules/intranet/lang/de/public/services/openlines/.left.menu.php",
			"modules/intranet/lang/de/public/services/openlines/edit.php",
			"modules/intranet/lang/en/include.php",
			"modules/intranet/lang/en/public/services/openlines/.left.menu.php",
			"modules/intranet/lang/en/public/services/openlines/edit.php",
			"modules/intranet/lang/ru/include.php",
			"modules/intranet/lang/ru/public/company/absence.php",
			"modules/intranet/lang/ru/public/company/timeman.php",
			"modules/intranet/lang/ru/public/company/work_report.php",
			"modules/intranet/lang/ru/public/services/meeting/index.php",
			"modules/intranet/lang/ru/public/services/openlines/.left.menu.php",
			"modules/intranet/lang/ru/public/services/openlines/edit.php",
			"modules/intranet/lang/ua/include.php",
			"modules/intranet/lang/ua/public/services/openlines/.left.menu.php",
			"modules/intranet/lang/ua/public/services/openlines/edit.php",
			"modules/intranet/public/",
			"templates/bitrix24/components/bitrix/breadcrumb/",
			"templates/bitrix24/components/bitrix/lists.list",
			"templates/bitrix24/components/bitrix/socialnetwork.group/short/",
			"templates/bitrix24/components/bitrix/socialnetwork.group_menu/.default/script.js",
			"templates/bitrix24/components/bitrix/socialnetwork.group_menu/.default/style.css",
			"templates/bitrix24/components/bitrix/socialnetwork.log.filter",
			"templates/bitrix24/components/bitrix/socialnetwork.user_groups.link.add/.default/images/group-list-sprite.png",
			"templates/bitrix24/components/bitrix/system.auth.form/.default/style.css",
			"templates/bitrix24/slider",
			"wizards/bitrix/portal/site/public/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/about/calendar.php",
			"wizards/bitrix/portal/site/public/company/absence.php",
			"wizards/bitrix/portal/site/public/company/personal/mail/index.php",
			"wizards/bitrix/portal/site/public/company/timeman.php",
			"wizards/bitrix/portal/site/public/company/work_report.php",
			"wizards/bitrix/portal/site/public/crm/company/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/company/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/configs/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/configs/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/configs/emailtracker/index.php",
			"wizards/bitrix/portal/site/public/crm/contact/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/contact/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/deal/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/deal/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/events/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/events/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/invoice/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/invoice/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/lead/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/lead/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/quote/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/quote/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/crm/reports/.left.menu.php",
			"wizards/bitrix/portal/site/public/crm/reports/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/services/bp/.left.menu_ext.php",
			"wizards/bitrix/portal/site/public/services/bp/index.php",
			"wizards/bitrix/portal/site/public/services/meeting/index.php",
			"wizards/bitrix/portal/site/public/services/processes/index.php",
			"wizards/bitrix/portal/site/public/timeman/bitrix24time.php",
			"wizards/bitrix/portal/site/services/bizproc/image/1.jpg",
			"wizards/bitrix/portal/site/services/bizproc/image/2.jpg",
			"wizards/bitrix/portal/site/services/bizproc/image/3.jpg",
			"wizards/bitrix/portal/site/services/bizproc/index.php",
			"wizards/bitrix/portal/site/services/bizproc/lang/de/index.php",
			"wizards/bitrix/portal/site/services/bizproc/lang/en/index.php",
			"wizards/bitrix/portal/site/services/bizproc/lang/ru/index.php",
			"wizards/bitrix/portal/site/services/bizproc/lang/ua/index.php",
			"wizards/bitrix/portal/site/services/main/lang/de/property_names.php",
			"wizards/bitrix/portal/site/services/main/lang/en/property_names.php",
			"wizards/bitrix/portal/site/services/main/lang/ru/property_names.php",
			"wizards/bitrix/portal/site/services/main/lang/ua/property_names.php",
			"wizards/bitrix/portal/site/services/main/property.php",
			"wizards/bitrix/portal/site/services/video/",
		);

		foreach ($arToDelete as $file)
		{
			$this->deleteDirFiles($this->getCrmSitePath().$this->getCrmSiteDir()."bitrix/".$file);
		}

		$this->clearCache("menu", "bitrix:menu");
	}

	/**
	 * @param $dir
	 * @param $componentName
	 */
	public static function clearCache($dir, $componentName)
	{
		$GLOBALS["CACHE_MANAGER"]->CleanDir($dir);
		\CBitrixComponent::clearComponentCache($componentName);
	}

	/**
	 * Init site fields
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function initSiteFields()
	{
		$site = Main\SiteTable::getList([
			"select" => ["*"],
			"filter" => ["LID" => $this->siteId]
		])->fetch();

		$this->siteDir = $site["DIR"];
		$this->sitePath = $site["DOC_ROOT"];
		$this->siteName = $site["NAME"];
		$this->serverName = $site["SERVER_NAME"];
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getCrmSiteId()
	{
		return Option::get("sale", self::CRM_WIZARD_SITE_ID);
	}

	/**
	 * @return mixed
	 */
	public function getCrmSiteDir()
	{
		return $this->siteDir;
	}

	/**
	 * @return mixed
	 */
	public function getCrmSitePath()
	{
		return $this->sitePath;
	}

	/**
	 * @return mixed
	 */
	public function getCrmSiteName()
	{
		return $this->siteName;
	}

	public function getCrmServerName()
	{
		return $this->serverName;
	}

	/**
	 * @param $path
	 * @return bool
	 */
	private function deleteDirFiles($path)
	{
		if (!file_exists($path))
		{
			return false;
		}

		if (is_file($path))
		{
			@unlink($path);
			return true;
		}

		if ($handle = @opendir($path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
				{
					continue;
				}

				if (is_dir($path."/".$file))
				{
					$this->deleteDirFiles($path."/".$file);
				}
				else
				{
					@unlink($path."/".$file);
				}
			}
		}

		@closedir($handle);
		@rmdir($path);
		return true;
	}

	/**
	 * Set new conditions for template
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	public static function updateSiteTemplateConditions()
	{
		foreach (Main\SiteTemplateTable::getList() as $template)
		{
			if (
				$template['CONDITION'] === '!$GLOBALS[\'USER\']->IsAuthorized() && $_SERVER[\'REMOTE_USER\']==\'\''
				&& $template['TEMPLATE'] === 'login'
			)
			{
				Main\SiteTemplateTable::update($template['ID'], [
					'CONDITION' => '((method_exists("CUser", "HasNoAccess") && $GLOBALS["USER"]->HasNoAccess()) || !$GLOBALS["USER"]->IsAuthorized()) && $_SERVER["REMOTE_USER"]==""'
				]);
			}
		}
	}

	/**
	 * Copy dir /docs/ from disk to crm site
	 */
	public function patchDisk()
	{
		if (!Main\ModuleManager::isModuleInstalled("disk"))
		{
			return;
		}

		$server = Main\Context::getCurrent()->getServer();

		\CopyDirFiles(
			$server->getDocumentRoot()."/bitrix/modules/disk/install/public/docs",
			$this->getCrmSitePath().$this->getCrmSiteDir()."docs",
			true,
			true);

		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;
		$APPLICATION->SetFileAccessPermission(array($this->siteId, $this->getCrmSiteDir().'docs/pub/'), array('*' => 'R'));
	}

	/**
	 * Path module (dav)
	 */
	public function patchDav()
	{
		if (!Main\ModuleManager::isModuleInstalled("dav"))
		{
			return;
		}

		\CBXFeatures::SetFeatureEnabled("DAV", true);
	}

	/**
	 * Path module (timeman)
	 */
	public function patchTimeman()
	{
		if (!Main\ModuleManager::isModuleInstalled("timeman"))
		{
			return;
		}

		\CBXFeatures::SetFeatureEnabled("timeman", true);
	}

	/**
	 * Path module (meeting)
	 */
	public function patchMeeting()
	{
		if (!Main\ModuleManager::isModuleInstalled("meeting"))
		{
			return;
		}

		\CBXFeatures::SetFeatureEnabled("Meeting", true);
	}

	/**
	 * Path module (imconnector)
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function patchImconnector()
	{
		if (!Main\ModuleManager::isModuleInstalled("imconnector"))
		{
			return;
		}

		$server = Main\Context::getCurrent()->getServer();

		$publicUrl = (Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$this->getCrmServerName().(in_array($server->getServerPort(), array(80, 443))?'':':'.$server->getServerPort());

		Option::set("imconnector", "uri_client", $publicUrl);

		$arToDelete = array(
			"/pub/imconnector/",
		);

		foreach ($arToDelete as $file)
		{
			$this->deleteDirFiles($server->getDocumentRoot().$file);
		}

		$arToDelete = array(
			"/pub/",
		);

		foreach ($arToDelete as $file)
		{
			if (!glob($server->getDocumentRoot().$file."*"))
			{
				$this->deleteDirFiles($server->getDocumentRoot().$file);
			}
		}

		\CopyDirFiles(
			$server->getDocumentRoot()."/bitrix/modules/imconnector/install/pub/imconnector",
			$this->getCrmSitePath().$this->getCrmSiteDir()."pub/imconnector",
			true,
			true
		);
	}

	/**
	 * Path module (imopenlines)
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function patchImopenlines()
	{
		if (!Main\ModuleManager::isModuleInstalled("imopenlines"))
		{
			return;
		}

		$server = Main\Context::getCurrent()->getServer();

		$publicUrl = (Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$this->getCrmServerName().(in_array($server->getServerPort(), array(80, 443))?'':':'.$server->getServerPort());

		Option::set("imopenlines", "portal_url", $publicUrl);

		$arToDelete = array(
			"/desktop_app/iframe/",
			"/pub/imconnector/",
		);

		foreach ($arToDelete as $file)
		{
			$this->deleteDirFiles($server->getDocumentRoot().$file);
		}

		\CopyDirFiles(
			$server->getDocumentRoot()."/bitrix/modules/imopenlines/install/public",
			$this->getCrmSitePath().$this->getCrmSiteDir(),
			true,
			true
		);

		\CopyDirFiles(
			$server->getDocumentRoot()."/bitrix/modules/imconnector/install/pub",
			$this->getCrmSitePath().$this->getCrmSiteDir()."pub",
			true,
			true
		);

		// update events
		$eventMessageIterator = Main\Mail\Internal\EventMessageTable::getList([
			"select" => ["ID"],
			"filter" => ["%EVENT_NAME" => "IMOL"]
		]);
		$eventMessageIdList = [];
		while ($eventMessage = $eventMessageIterator->fetch())
		{
			$eventMessageIdList[] = $eventMessage["ID"];
		}

		if ($eventMessageIdList)
		{
			$eventMessageSiteIterator = Main\Mail\Internal\EventMessageSiteTable::getList([
				"select" => ["EVENT_MESSAGE_ID", "SITE_ID"],
				"filter" => ["EVENT_MESSAGE_ID" => $eventMessageIdList]
			]);

			$eventMessageSiteList = [];
			foreach ($eventMessageIdList as $eventMessageId)
			{
				$eventMessageSiteList[$eventMessageId][] = $this->siteId;
			}

			while ($eventMessageSite = $eventMessageSiteIterator->fetch())
			{
				$eventMessageSiteList[$eventMessageSite["EVENT_MESSAGE_ID"]][] = $eventMessageSite["SITE_ID"];
			}

			$eventMessage = new \CEventMessage();
			foreach ($eventMessageSiteList as $eventMessageId => $eventMessageSite)
			{
				$eventMessage->Update($eventMessageId, [
					"LID" => $eventMessageSite
				]);
			}
		}
	}

	/**
	 * Path module (voximplant)
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function patchVoximplant()
	{
		if (!Main\ModuleManager::isModuleInstalled("voximplant"))
		{
			return;
		}

		$server = Main\Context::getCurrent()->getServer();

		$publicUrl = (Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$this->getCrmServerName().(in_array($server->getServerPort(), array(80, 443))?'':':'.$server->getServerPort());

		Option::set("voximplant", "portal_url", $publicUrl);

		$GLOBALS["CACHE_MANAGER"]->CleanDir("menu");
		\CBitrixComponent::clearComponentCache("bitrix:menu");
	}

	/**
	 * Path module (mobile)
	 */
	public function patchMobile()
	{
		if (!Main\ModuleManager::isModuleInstalled("mobile"))
		{
			return;
		}

		$server = Main\Context::getCurrent()->getServer();

		$arToDelete = array(
			"/mobile/",
		);

		foreach ($arToDelete as $file)
		{
			$this->deleteDirFiles($server->getDocumentRoot().$file);
		}

		CopyDirFiles(
			$server->getDocumentRoot()."/bitrix/modules/mobile/public/mobile/",
			$this->getCrmSitePath().$this->getCrmSiteDir()."mobile/",
			true,
			true
		);

		// set template
		$arAppTempalate = Array(
			"SORT" => 1,
			"CONDITION" => "CSite::InDir('/mobile/')",
			"TEMPLATE" => "mobile_app"
		);

		$arFields = Array("TEMPLATE" => Array());
		$dbTemplates = \CSite::GetTemplateList($this->siteId);
		$mobileAppFound = false;
		while ($template = $dbTemplates->Fetch())
		{
			if ($template["TEMPLATE"] == "mobile_app")
			{
				$mobileAppFound = true;
				$template = $arAppTempalate;
			}
			$arFields["TEMPLATE"][] = array(
				"TEMPLATE" => $template['TEMPLATE'],
				"SORT" => $template['SORT'],
				"CONDITION" => $template['CONDITION']
			);
		}
		if (!$mobileAppFound)
		{
			$arFields["TEMPLATE"][] = $arAppTempalate;
		}

		$obSite = new \CSite;
		$arFields["LID"] = $this->siteId;
		$obSite->Update($this->siteId, $arFields);

		// reindex
		if(Main\IO\File::isFileExists($this->getCrmSitePath().$this->getCrmSiteDir()."mobile/webdav/index.php"))
		{
			UrlRewriter::reindexFile($this->siteId, $this->getCrmSitePath(), "/mobile/webdav/index.php");
		}
		if(Main\IO\File::isFileExists($this->getCrmSitePath().$this->getCrmSiteDir()."mobile/disk/index.php"))
		{
			UrlRewriter::reindexFile($this->siteId, $this->getCrmSitePath(), "/mobile/disk/index.php");
		}
	}

	public function patchIm()
	{
		if (!Main\ModuleManager::isModuleInstalled("im"))
		{
			return;
		}

		$desktopAppFound = false;
		$arAppTempalate = Array(
			"SORT" => 1,
			"CONDITION" => "CSite::InDir('/desktop_app/')",
			"TEMPLATE" => "desktop_app"
		);

		$pubAppFound = false;
		$arPubTempalate = Array(
			"SORT" => 100,
			"CONDITION" => 'preg_match("#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))',
			"TEMPLATE" => "pub"
		);

		$arFields = Array("TEMPLATE"=>Array());
		$dbTemplates = \CSite::GetTemplateList($this->siteId);
		while($template = $dbTemplates->Fetch())
		{
			if ($template["CONDITION"] == "CSite::InDir('/desktop_app/')")
			{
				$desktopAppFound = true;
				$template = $arAppTempalate;
			}
			else if ($template["CONDITION"] == 'preg_match("#^/online/([\.\-0-9a-zA-Z]+)(/?)([^/]*)#", $GLOBALS[\'APPLICATION\']->GetCurPage(0))')
			{
				$pubAppFound = true;
				$template = $arPubTempalate;
			}
			$arFields["TEMPLATE"][] = array(
				"SORT" => $template['SORT'],
				"CONDITION" => $template['CONDITION'],
				"TEMPLATE" => $template['TEMPLATE'],
			);
		}
		if (!$desktopAppFound)
			$arFields["TEMPLATE"][] = $arAppTempalate;
		if (!$pubAppFound)
			$arFields["TEMPLATE"][] = $arPubTempalate;

		$obSite = new \CSite;
		$arFields["LID"] = $this->siteId;
		$obSite->Update($this->siteId, $arFields);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function prepareCrmCatalog()
	{
		if (!$this->isDefaultCatalogExists())
		{
			$catalogId = $this->getCatalogId();
			if ($catalogId)
			{
				$this->setDefaultProductCatalogId($catalogId);
			}
		}
	}

	/**
	 * @return bool
	 */
	private function isDefaultCatalogExists()
	{
		$id = \CAllCrmCatalog::GetDefaultID();
		return $id ? true : false;
	}

	/**
	 * @return int|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getCatalogId()
	{
		$catalogId = null;

		$catalogDb = Catalog\CatalogIblockTable::getList([
			"filter" => [
				"PRODUCT_IBLOCK_ID" => 0,
				"IBLOCK.ACTIVE" => "Y",
			],
			"select" => ["IBLOCK_ID"],
			"order" => ["IBLOCK_ID" => "ASC"]
		]);
		if ($catalog = $catalogDb->fetch())
		{
			$catalogId = (int)$catalog["IBLOCK_ID"];
		}

		return $catalogId;
	}

	/**
	 * @param $catalogId
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function setDefaultProductCatalogId($catalogId)
	{
		Option::set('crm', 'default_product_catalog_id', $catalogId);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function setCrmUserGroups()
	{
		$selectedGroups = $this->getSelectedUserGroups();
		if ($selectedGroups)
		{
			$this->deleteSelectedUserGroups();
		}

		if (!\CheckSerializedData($selectedGroups))
		{
			return;
		}

		$selectedGroups = @unserialize($selectedGroups);
		if (!is_array($selectedGroups))
		{
			return;
		}

		foreach ($selectedGroups as $type => $groups)
		{
			if ($groups && is_array($groups))
			{
				$userList = $this->getUserIdList($groups);
				if (!$userList)
				{
					continue;
				}

				$groupCrmList = $this->getCrmGroupIdList($type);
				$this->addNewGroup($userList, $groupCrmList);
			}
		}
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 */
	public function setCrmGroupRights()
	{
		$iblockId = Option::get('crm', 'default_product_catalog_id', false);
		if (!$iblockId)
		{
			return;
		}

		$iblockTypeId = $this->getIblockTypeID($iblockId);
		if (!$iblockTypeId)
		{
			return;
		}

		\CIBlockRights::setGroupRight(\CCrmSaleHelper::getShopGroupIdByType('admin'), $iblockTypeId, 'X', $iblockId);
		\CIBlockRights::setGroupRight(\CCrmSaleHelper::getShopGroupIdByType('manager'), $iblockTypeId, 'W', $iblockId);
		if (Main\Loader::includeModule('catalog'))
		{
			$catalog = \CCatalogSku::GetInfoByProductIBlock($iblockId);
			if (!empty($catalog))
			{
				\CIBlockRights::setGroupRight(\CCrmSaleHelper::getShopGroupIdByType('admin'), $iblockTypeId, 'X', $catalog['IBLOCK_ID']);
				\CIBlockRights::setGroupRight(\CCrmSaleHelper::getShopGroupIdByType('manager'), $iblockTypeId, 'W', $catalog['IBLOCK_ID']);
			}
			unset($catalog);
		}
		unset($iblockTypeId);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function addEmployeesToCompanyStructure()
	{
		$employeeGroupId = $this->getEmployeesGroupId();
		if (!$employeeGroupId)
		{
			return;
		}

		$userIdList = $this->getUserIdList([$employeeGroupId]);
		if (!$userIdList)
		{
			return;
		}

		$sectionId = $this->getCompanyDepartmentId();
		if (!$sectionId)
		{
			return;
		}

		$user = new \CUser();
		foreach ($userIdList as $userId)
		{
			$user->Update($userId, array("UF_DEPARTMENT"=>[$sectionId]));
		}
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getSelectedUserGroups()
	{
		return Option::get("sale", self::SELECTED_USER_GROUPS, false);
	}

	/**
	 * @throws Main\ArgumentNullException
	 */
	public static function deleteSelectedUserGroups()
	{
		Option::delete("sale", ["name" => self::SELECTED_USER_GROUPS]);
	}

	/**
	 * @return int|bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getEmployeesGroupId()
	{
		return Option::get("crm", self::EMPLOYEE_USER_GROUP_ID, false);
	}

	/**
	 * @throws Main\ArgumentNullException
	 */
	public static function deleteEmployeesGroupId()
	{
		Option::delete("crm", ["name" => self::EMPLOYEE_USER_GROUP_ID]);
	}

	/**
	 * @return int|bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getCompanyDepartmentId()
	{
		return Option::get("crm", self::CRM_COMPANY_DEPARTMENT_ID, false);
	}

	/**
	 * @throws Main\ArgumentNullException
	 */
	public static function deleteCompanyDepartmentId()
	{
		Option::delete("crm", ["name" => self::CRM_COMPANY_DEPARTMENT_ID]);
	}

	/**
	 * @param $iblockId
	 * @return string|null
	 */
	private function getIblockTypeID($iblockId)
	{
		$iblockTypeId = null;

		$iblockDb = \CIBlock::GetByID($iblockId);
		if ($result = $iblockDb->Fetch())
			$iblockTypeId = $result["IBLOCK_TYPE_ID"];

		return $iblockTypeId;
	}

	/**
	 * @param $name
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function createDepartment($name)
	{
		$departmentIblockId = $this->getDepartmentIblockId();
		if ($departmentIblockId)
		{
			$headDepartmentSectionId = $this->getHeadDepartmentSectionId($departmentIblockId);
			if ($headDepartmentSectionId === null)
			{
				$arFields = Array(
					"ACTIVE" => "Y",
					"IBLOCK_ID" => $departmentIblockId,
					"NAME" => $name,
				);
				$iblockSection = new \CIBlockSection();
				$headDepartmentSectionId = $iblockSection->Add($arFields);
			}

			if ($headDepartmentSectionId)
			{
				Option::set("crm", self::CRM_COMPANY_DEPARTMENT_ID, $headDepartmentSectionId);
			}
		}
	}

	/**
	 * @return int|null
	 */
	private function getDepartmentIblockId()
	{
		$iblockCode = "departments";
		$iblockType = "structure";
		$rsIBlock = \CIBlock::GetList(["SORT"=>"ASC"], ["CODE" => $iblockCode, "TYPE" => $iblockType]);
		$departmentIblockId = null;
		if ($arIBlock = $rsIBlock->Fetch())
		{
			$departmentIblockId = (int)$arIBlock["ID"];
		}

		return $departmentIblockId;
	}

	/**
	 * @param $departmentIblockId
	 * @return int|null
	 */
	private function getHeadDepartmentSectionId($departmentIblockId)
	{
		$headDepartmentSectionId = null;
		$rsIBlockSection = \CIBlockSection::GetList(
			["SORT"=>"ASC"],
			['IBLOCK_ID' => $departmentIblockId, 'SECTION_ID' => 0],
			false,
			['ID']
		);
		if ($arIBlockSection = $rsIBlockSection->Fetch())
		{
			$headDepartmentSectionId = (int)$arIBlockSection["ID"];
		}

		return $headDepartmentSectionId;
	}

	/**
	 * @param array $groups
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getUserIdList(array $groups)
	{
		$userIdList = [];

		$userIterator = Main\UserGroupTable::getList([
			"select" => ["USER_ID"],
			"filter" => ["GROUP_ID" => $groups]
		]);
		while ($user = $userIterator->fetch())
		{
			$userIdList[] = $user["USER_ID"];
		}

		if ($userIdList)
		{
			$userIdList = array_unique($userIdList);
		}

		return $userIdList;
	}

	/**
	 * @param $type
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getCrmGroupIdList($type)
	{
		$groupIdList = [];

		$groupIterator = Main\GroupTable::getList([
			"select" => ["ID"],
			"filter" => ["=STRING_ID" => "CRM_SHOP_".$type],
		]);
		while ($group = $groupIterator->fetch())
		{
			$groupIdList[] = $group["ID"];
		}

		return $groupIdList;
	}

	/**
	 * @param array $users
	 * @param array $groups
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function addNewGroup(array $users, array $groups)
	{
		$currentUserGroupList = $this->getCurrentUserGroupList($users);
		foreach ($users as $user)
		{
			$currentGroups = $currentUserGroupList[$user];
			$newGroups = array_unique(array_merge($currentGroups, $groups));
			\CUser::SetUserGroup($user, $newGroups);
		}
	}

	/**
	 * @param array $users
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getCurrentUserGroupList(array $users)
	{
		$result = [];

		$userIterator = Main\UserGroupTable::getList([
			"select" => ["USER_ID", "GROUP_ID"],
			"filter" => ["USER_ID" => $users]
		]);
		while ($user = $userIterator->fetch())
		{
			$result[$user["USER_ID"]][] = $user["GROUP_ID"];
		}

		return $result;
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function saveConfig1C()
	{
		$config1C = [
			"catalog" => [
				"1CE_ELEMENTS_PER_STEP" => Option::get("catalog", "1CE_ELEMENTS_PER_STEP", ""),
				"1CE_GROUP_PERMISSIONS" => Option::get("catalog", "1CE_GROUP_PERMISSIONS", ""),
				"1CE_IBLOCK_ID" => Option::get("catalog", "1CE_IBLOCK_ID", ""),
				"1CE_INTERVAL" => Option::get("catalog", "1CE_INTERVAL", ""),
				"1CE_USE_ZIP" => Option::get("catalog", "1CE_USE_ZIP", ""),
				"1C_DETAIL_HEIGHT" => Option::get("catalog", "1C_DETAIL_HEIGHT", ""),
				"1C_DETAIL_RESIZE" => Option::get("catalog", "1C_DETAIL_RESIZE", ""),
				"1C_DETAIL_WIDTH" => Option::get("catalog", "1C_DETAIL_WIDTH", ""),
				"1C_ELEMENT_ACTION" => Option::get("catalog", "1C_ELEMENT_ACTION", ""),
				"1C_FILE_SIZE_LIMIT" => Option::get("catalog", "1C_FILE_SIZE_LIMIT", ""),
				"1C_FORCE_OFFERS" => Option::get("catalog", "1C_FORCE_OFFERS", ""),
				"1C_GENERATE_PREVIEW" => Option::get("catalog", "1C_GENERATE_PREVIEW", ""),
				"1C_GROUP_PERMISSIONS" => Option::get("catalog", "1C_GROUP_PERMISSIONS", ""),
				"1C_IBLOCK_TYPE" => Option::get("catalog", "1C_IBLOCK_TYPE", ""),
				"1C_INTERVAL" => Option::get("catalog", "1C_INTERVAL", ""),
				"1C_PREVIEW_HEIGHT" => Option::get("catalog", "1C_PREVIEW_HEIGHT", ""),
				"1C_PREVIEW_WIDTH" => Option::get("catalog", "1C_PREVIEW_WIDTH", ""),
				"1C_SECTION_ACTION" => Option::get("catalog", "1C_SECTION_ACTION", ""),
				"1C_SITE_LIST" => Option::get("catalog", "1C_SITE_LIST", ""),
				"1C_SKIP_ROOT_SECTION" => Option::get("catalog", "1C_SKIP_ROOT_SECTION", ""),
				"1C_TRANSLIT_ON_ADD" => Option::get("catalog", "1C_TRANSLIT_ON_ADD", ""),
				"1C_TRANSLIT_ON_UPDATE" => Option::get("catalog", "1C_TRANSLIT_ON_UPDATE", ""),
				"1C_USE_CRC" => Option::get("catalog", "1C_USE_CRC", ""),
				"1C_USE_IBLOCK_PICTURE_SETTINGS" => Option::get("catalog", "1C_USE_IBLOCK_PICTURE_SETTINGS", ""),
				"1C_USE_IBLOCK_TYPE_ID" => Option::get("catalog", "1C_USE_IBLOCK_TYPE_ID", ""),
				"1C_USE_OFFERS" => Option::get("catalog", "1C_USE_OFFERS", ""),
				"1C_USE_ZIP" => Option::get("catalog", "1C_USE_ZIP", ""),
			],
			"sale" => [
				"1C_EXPORT_ALLOW_DELIVERY_ORDERS" => Option::get("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", ""),
				"1C_EXPORT_FINAL_ORDERS" => Option::get("sale", "1C_EXPORT_FINAL_ORDERS", ""),
				"1C_EXPORT_PAYED_ORDERS" => Option::get("sale", "1C_EXPORT_PAYED_ORDERS", ""),
				"1C_FINAL_STATUS_ON_DELIVERY" => Option::get("sale", "1C_FINAL_STATUS_ON_DELIVERY", ""),
				"1C_REPLACE_CURRENCY" => Option::get("sale", "1C_REPLACE_CURRENCY", ""),
				"1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX" => Option::get("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", ""),
				"1C_SALE_GROUP_PERMISSIONS" => Option::get("sale", "1C_SALE_GROUP_PERMISSIONS", ""),
				"1C_SALE_SITE_LIST" => Option::get("sale", "1C_SALE_SITE_LIST", ""),
				"1C_SALE_USE_ZIP" => Option::get("sale", "1C_SALE_USE_ZIP", ""),
			],
		];

		$config1C = serialize($config1C);
		Option::set("sale", self::CONFIG_1C, $config1C);
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function retrieveConfig1C()
	{
		$config1C = Option::get("sale", self::CONFIG_1C);
		$config1C = unserialize($config1C);

		foreach ($config1C as $module => $options)
		{
			foreach ($options as $name => $value)
			{
				Option::set($module, $name, $value);
			}
		}

		Option::delete("sale", ["name" => self::CONFIG_1C]);
	}

	/**
	 * @throws Main\ArgumentNullException
	 */
	public static function disableRegularArchive()
	{
		Option::delete("sale", ["name" => "regular_archive_active"]);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function addSiteToCatalog()
	{
		$siteId = self::getCrmSiteId();
		if (!$siteId)
			return;

		$catalogIblocIdkList = $this->getCatalogIblockIdList();
		if (!$catalogIblocIdkList)
			return;

		foreach ($catalogIblocIdkList as $catalogIblockId)
		{
			$siteList = $this->getIblockSiteList($catalogIblockId);
			$siteList[$catalogIblockId][] = $siteId;

			$iblock = new \CIBlock();
			$iblock->Update($catalogIblockId,
				[
					"LID" => $siteList[$catalogIblockId]
				]
			);
		}
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getCatalogIblockIdList()
	{
		$catalogIblocIdkList = [];
		$catalogResult = Catalog\CatalogIblockTable::getList([
			'select' => ['IBLOCK_ID', 'PRODUCT_IBLOCK_ID']
		]);
		while ($row = $catalogResult->fetch())
		{
			$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
			$row['PRODUCT_IBLOCK_ID'] = (int)$row['PRODUCT_IBLOCK_ID'];

			$catalogIblocIdkList[$row['IBLOCK_ID']] = $row['IBLOCK_ID'];
			if ($row['PRODUCT_IBLOCK_ID'] > 0)
			{
				$catalogIblocIdkList[$row['PRODUCT_IBLOCK_ID']] = $row['PRODUCT_IBLOCK_ID'];
			}
		}

		return $catalogIblocIdkList;
	}

	/**
	 * @param $catalogIblocIdkList
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getIblockSiteList($catalogIblocIdkList)
	{
		$siteList = [];
		$iblockSiteResult = \Bitrix\Iblock\IblockSiteTable::getList([
			'select' => ['IBLOCK_ID', 'SITE_ID'],
			'filter' => ['@IBLOCK_ID' => $catalogIblocIdkList]
		]);
		while ($row = $iblockSiteResult->fetch())
		{
			$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
			if (!isset($siteList[$row['IBLOCK_ID']]))
			{
				$siteList[$row['IBLOCK_ID']] = [];
			}

			$siteList[$row['IBLOCK_ID']][] = $row['SITE_ID'];
		}

		return $siteList;
	}

	/**
	 * Enable composite using option in .settings.php
	 */
	public static function enableComposite()
	{
		if (self::isCanEnableComposite())
		{
			Configuration::setValue(self::FORCE_ENABLE_SELF_HOSTED_COMPOSITE, true);
		}
	}

	/**
	 * @return bool
	 */
	private static function isCanEnableComposite()
	{
		if (Configuration::getValue(self::FORCE_ENABLE_SELF_HOSTED_COMPOSITE) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function crmShopEnable()
	{
		Option::set("crm", "crm_shop_enabled", "Y");
	}
}