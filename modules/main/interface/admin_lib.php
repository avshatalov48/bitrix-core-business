<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\HttpResponse;
use Bitrix\Main\Application;

IncludeModuleLangFile(__FILE__);

define("ADMIN_THEMES_PATH", "/bitrix/themes");

class CAdminPage
{
	var $aModules = array();
	var $bInit = false;
	var $publicMode = false;

	private $isHideTitle = false;

	public function __construct()
	{
		if (defined("PUBLIC_MODE") && PUBLIC_MODE == 1)
		{
			$this->publicMode = true;
		}
	}

	public function Init()
	{
		if($this->bInit)
			return;
		$this->bInit = true;

		$module_list = CModule::GetList();
		while($module = $module_list->Fetch())
			$this->aModules[] = $module["ID"];
	}

	public function ShowTitle()
	{
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array(&$this, "GetTitle"));
	}

	public function ShowJsTitle()
	{
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array(&$this, "GetJsTitle"));
	}

	public function GetTitle()
	{
		global $APPLICATION;
		return htmlspecialcharsex($APPLICATION->GetTitle(false, true));
	}

	public function isHideTitle()
	{
		return $this->isHideTitle;
	}

	public function hideTitle()
	{
		$this->isHideTitle = true;
	}

	public function GetJsTitle()
	{
		global $APPLICATION;
		return CUtil::JSEscape($APPLICATION->GetTitle(false, true));
	}

	public function ShowPopupCSS()
	{
		if ($this->publicMode)
		{
			return '';
		}

		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$this->Init();

		$arCSS = array_merge(
			$this->GetModulesCSS($_REQUEST['from_module']),
			$APPLICATION->GetCSSArray()
		);

		$s = '<script type="text/javascript" bxrunfirst>'."\n";
		for ($i = 0, $cnt = count($arCSS); $i < $cnt; $i++)
		{
			$bExternalLink = (strncmp($arCSS[$i], 'http://', 7) == 0 || strncmp($arCSS[$i], 'https://', 8) == 0);
			if($bExternalLink || file_exists($_SERVER['DOCUMENT_ROOT'].$arCSS[$i]))
				$s .= 'top.BX.loadCSS(\''.CUtil::JSEscape($arCSS[$i]).'\');'."\n";
		}
		$s .= '</script>';
		return $s;
	}

	public function ShowCSS()
	{
		if ($this->publicMode)
		{
			return '';
		}

		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$this->Init();

		$arCSS = array_merge(array(
				ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/compatible.css',
				//ADMIN_THEMES_PATH.'/'.ADMIN_THEME_ID.'/adminstyles.css',
				'/bitrix/panel/main/adminstyles_fixed.css',
				'/bitrix/panel/main/admin.css',
			),
			$this->GetModulesCSS(),
			$APPLICATION->GetCSSArray()
		);

		$s = '';
		foreach($arCSS as $css)
		{
			$bExternalLink = (strncmp($css, 'http://', 7) == 0 || strncmp($css, 'https://', 8) == 0);
			if($bExternalLink || file_exists($_SERVER['DOCUMENT_ROOT'].$css))
				$s .= '<link rel="stylesheet" type="text/css" href="'.($bExternalLink? $css : CUtil::GetAdditionalFileURL($css, true)).'">'."\n";
		}
		return $s;
	}

	public function GetModulesCSS($module_id='')
	{
		global $CACHE_MANAGER;
		$rel_theme_path = ADMIN_THEMES_PATH."/".ADMIN_THEME_ID."/";
		$abs_theme_path = $_SERVER["DOCUMENT_ROOT"].$rel_theme_path;

		if($module_id <> '' && $this->aModules[$module_id] <> '')
		{
			if(file_exists($abs_theme_path.$module_id.".css"))
				return array($rel_theme_path.$module_id.'.css');
		}

		if($CACHE_MANAGER->Read(36000000, ADMIN_THEME_ID, "modules_css"))
			$time_cached = $CACHE_MANAGER->Get(ADMIN_THEME_ID);
		else
			$time_cached = '';

		//check modification time
		$time_fact = '';
		foreach($this->aModules as $module)
		{
			$fname = $abs_theme_path.$module.".css";
			if(file_exists($fname))
				$time_fact .= filemtime($fname);
		}

		$css_file = $abs_theme_path."modules.css";

		if($time_fact !== $time_cached)
		{
			//parse css files to create summary modules css
			$sCss = '';
			foreach($this->aModules as $module)
			{
				$fname = $abs_theme_path.$module.".css";
				if(file_exists($fname))
					$sCss .= file_get_contents($fname)."\n";
			}

			//create summary modules css
			file_put_contents($css_file, $sCss);

			if($time_cached !== '')
			{
				$CACHE_MANAGER->Clean(ADMIN_THEME_ID, "modules_css");
				$CACHE_MANAGER->Read(36000000, ADMIN_THEME_ID, "modules_css");
			}

			$CACHE_MANAGER->Set(ADMIN_THEME_ID, $time_fact);
		}

		if(file_exists($css_file))
			return array($rel_theme_path.'modules.css');
		else
			return array();
	}

	public function ShowScript()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
		$APPLICATION->AddHeadScript('/bitrix/js/main/hot_keys.js');

		$APPLICATION->SetAdditionalCSS('/bitrix/panel/main/hot_keys.css');

		if ($this->publicMode)
		{
			return '';
		}

		//PHP-depended variables
		$aUserOpt = CUserOptions::GetOption("global", "settings");
		$s = "
<script type=\"text/javascript\">
var phpVars = {
	'ADMIN_THEME_ID': '".CUtil::JSEscape(ADMIN_THEME_ID)."',
	'LANGUAGE_ID': '".CUtil::JSEscape(LANGUAGE_ID)."',
	'FORMAT_DATE': '".CUtil::JSEscape(FORMAT_DATE)."',
	'FORMAT_DATETIME': '".CUtil::JSEscape(FORMAT_DATETIME)."',
	'opt_context_ctrl': ".(($aUserOpt["context_ctrl"] ?? '') == "Y"? "true":"false").",
	'cookiePrefix': '".CUtil::JSEscape(COption::GetOptionString("main", "cookie_name", "BITRIX_SM"))."',
	'titlePrefix': '".CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))." - ',
	'bitrix_sessid': '".bitrix_sessid()."',
	'messHideMenu': '".CUtil::JSEscape(GetMessage("admin_lib_hide_menu"))."',
	'messShowMenu': '".CUtil::JSEscape(GetMessage("admin_lib_show_menu"))."',
	'messHideButtons': '".CUtil::JSEscape(GetMessage("admin_lib_less_buttons"))."',
	'messShowButtons': '".CUtil::JSEscape(GetMessage("admin_lib_more_buttons"))."',
	'messFilterInactive': '".CUtil::JSEscape(GetMessage("admin_lib_filter_clear"))."',
	'messFilterActive': '".CUtil::JSEscape(GetMessage("admin_lib_filter_set"))."',
	'messFilterLess': '".CUtil::JSEscape(GetMessage("admin_lib_filter_less"))."',
	'messLoading': '".CUtil::JSEscape(GetMessage("admin_lib_loading"))."',
	'messMenuLoading': '".CUtil::JSEscape(GetMessage("admin_lib_menu_loading"))."',
	'messMenuLoadingTitle': '".CUtil::JSEscape(GetMessage("admin_lib_loading_title"))."',
	'messNoData': '".CUtil::JSEscape(GetMessage("admin_lib_no_data"))."',
	'messExpandTabs': '".CUtil::JSEscape(GetMessage("admin_lib_expand_tabs"))."',
	'messCollapseTabs': '".CUtil::JSEscape(GetMessage("admin_lib_collapse_tabs"))."',
	'messPanelFixOn': '".CUtil::JSEscape(GetMessage("admin_lib_panel_fix_on"))."',
	'messPanelFixOff': '".CUtil::JSEscape(GetMessage("admin_lib_panel_fix_off"))."',
	'messPanelCollapse': '".CUtil::JSEscape(GetMessage("admin_lib_panel_hide"))."',
	'messPanelExpand': '".CUtil::JSEscape(GetMessage("admin_lib_panel_show"))."'
};
</script>
";
		$APPLICATION->AddHeadScript('/bitrix/js/main/admin_tools.js');
		$APPLICATION->AddHeadScript('/bitrix/js/main/popup_menu.js');
		$APPLICATION->AddHeadScript('/bitrix/js/main/admin_search.js');

		return $s;
	}

	public function ShowSectionIndex($menu_id, $module_id=false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		echo '<div id="index_page_result_div">';

		$sTableID = "module_index_table";
		$page = $APPLICATION->GetCurPage();
		$param = DeleteParam(array("show_mode", "mode"));
		echo '
			<script>
			var '.$sTableID.' = new JCAdminList("'.$sTableID.'");
			jsUtils.addEvent(window, "unload", function(){'.$sTableID.'.Destroy(true);});

			function LoadIndex(mode)
			{
				'.$sTableID.'.Destroy(false);
				jsUtils.LoadPageToDiv("'.$page.'?show_mode="+mode+"&mode=list'.($param<>""? "&".$param:"").'", "index_page_result_div");
			}
			</script>
			';

		if($module_id === false)
			$this->Init();

		/** @global CAdminMenu() $adminMenu */
		global $adminMenu;

		$adminMenu->Init(($module_id !== false? array($module_id) : $this->aModules));
		$adminMenu->ShowSubmenu($menu_id, "table");

		echo '</div>';
	}

	public function ShowSound()
	{
		/** @global CMain $APPLICATION */
		global $USER, $APPLICATION;

		$res = '';
		if($USER->IsAuthorized() && !isset($_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM").'_SOUND_LOGIN_PLAYED']))
		{
			$aUserOptGlobal = CUserOptions::GetOption("global", "settings");
			if($aUserOptGlobal["sound"] == 'Y')
			{
				if($aUserOptGlobal["sound_login"] == '')
					$aUserOptGlobal["sound_login"] = "/bitrix/sounds/main/bitrix_tune.mp3";

				ob_start();
				$APPLICATION->IncludeComponent("bitrix:player",	"audio",
					Array(
						"PATH" => htmlspecialcharsbx($aUserOptGlobal["sound_login"]),
						"WIDTH" => "1",
						"HEIGHT" => "1",
						"CONTROLBAR" => "none",
						"AUTOSTART" => "Y",
						"REPEAT" => "N",
						"VOLUME" => "90",
						"MUTE" => "N",
						"HIGH_QUALITY" => "Y",
						"BUFFER_LENGTH" => "2",
						"PROVIDER"=>"sound",
					),
					null, array("HIDE_ICONS"=>"Y")
				);
				$res = ob_get_contents();
				ob_end_clean();

				$res = '
<div style="position:absolute; top:-1000px; left:-1000px;">
'.$res.'
</div>
';
			}
		}
		return $res;
	}

	public function getSSOSwitcherButton()
	{
		global $CACHE_MANAGER, $USER;

		if($CACHE_MANAGER->Read(86400, "sso_portal_list_".$USER->GetID()))
		{
			$queryResult = $CACHE_MANAGER->Get("sso_portal_list_".$USER->GetID());
		}
		else
		{
			$queryResult = false;
			if(\Bitrix\Main\Loader::includeModule('socialservices'))
			{
				if(class_exists('CBitrix24NetTransport'))
				{
					$query = \CBitrix24NetTransport::init();
					if ($query)
					{
						$queryResult = $query->call('admin.profile.list', array());
					}

					$CACHE_MANAGER->Set("sso_portal_list_".$USER->GetID(), $queryResult);
				}
			}
		}

		if(is_array($queryResult))
		{
			$ssoMenu = array();

			if(isset($queryResult['error']))
			{
				if(
					$queryResult['error'] == 'insufficient_scope'
					&& \Bitrix\Main\Loader::includeModule('socialservices')
					&& class_exists("Bitrix\\Socialservices\\Network")
					&& method_exists("Bitrix\\Socialservices\\Network", "getAuthUrl")
				)
				{
					$n = new \Bitrix\Socialservices\Network();
					$ssoMenu[] =  array(
						"TEXT" => \Bitrix\Main\Localization\Loc::getMessage("admin_lib_sso_auth"),
						"TITLE" => \Bitrix\Main\Localization\Loc::getMessage("admin_lib_sso_auth_title"),
						"ONCLICK"=>"BX.util.popup('".CUtil::JSEscape($n->getAuthUrl("popup", array("admin")))."', 800, 600);",
					);
				}
			}
			elseif(isset($queryResult['result']))
			{
				$currentHost = \Bitrix\Main\Context::getCurrent()->getRequest()->getHttpHost();

				foreach($queryResult['result']['admin'] as $site)
				{
					if($site["TITLE"] != $currentHost)
					{
						$ssoMenu[] =  array(
							"TEXT" => $site["TITLE"],
							"TITLE"=> "Go to ". $site["TITLE"],
							"LINK"=>$site["URL"]."bitrix/admin/",
						);
					}
				}

				if(
					count($ssoMenu) > 0
					&& count($queryResult['result']["portal"]) > 0
				)
				{
					$ssoMenu[] = array("SEPARATOR" => true);
				}

				foreach($queryResult['result']['portal'] as $site)
				{
					$ssoMenu[] =  array(
						"TEXT" => $site["TITLE"],
						"TITLE"=> "Go to ". $site["TITLE"],
						"LINK"=>$site["URL"],
					);
				}
			}

			return $ssoMenu;
		}

		return false;
	}

	public function getSelfFolderUrl()
	{
		return (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
	}
}

class CAdminAjaxHelper
{
	/** @var  \Bitrix\Main\Context */
	protected $context;
	/** @var  \Bitrix\Main\HttpResponse */
	protected $httpResponse;
	/** @var  \Bitrix\Main\HttpRequest */
	protected $request;

	protected $skipResponse = false;

	public function __construct()
	{
		$this->context = Application::getInstance()->getContext();
		$this->request = $this->context->getRequest();
		$this->httpResponse = new HttpResponse();
	}

	/**
     * Sends JSON response with status "success".
	 */
	public function sendJsonSuccessResponse()
	{
		$this->sendJsonResponse(array("status" => "success"));
	}

	/**
     * Sends JSON response with status "error" and with errors.
	 * @param string $message Error message.
	 */
	public function sendJsonErrorResponse($message)
	{
		$this->sendJsonResponse(array("status" => "error", "message" => $message));
	}

	/**
	 * Sends JSON response.
	 * @param array $params Data structure.
	 */
	public function sendJsonResponse($params = array())
	{
		if ($this->isAjaxRequest() && !$this->skipResponse)
		{
			$response = new Bitrix\Main\Engine\Response\Json($params);
			$response = Bitrix\Main\Context::getCurrent()->getResponse()->copyHeadersTo($response);
			Bitrix\Main\Application::getInstance()->end(0, $response);
		}
	}

	public function decodeUriComponent(Bitrix\Main\HttpRequest $request = null)
	{
		if ($this->isAjaxRequest())
		{
			if ($request)
			{
				$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());
			}
			CUtil::decodeURIComponent($_GET);
			CUtil::decodeURIComponent($_POST);
			CUtil::decodeURIComponent($_REQUEST);
			$listKeys = array_keys($_REQUEST);
			foreach ($listKeys as $key)
				CUtil::decodeURIComponent($GLOBALS[$key]);
		}
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean
	 */
	public function isAjaxRequest()
	{
		return $this->request->isAjaxRequest();
	}

	protected function end()
	{
		define("ADMIN_AJAX_MODE", true);
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
		die();
	}
}

class CAdminSidePanelHelper extends CAdminAjaxHelper
{
	/** @var bool */
	protected $publicPageProcessMode;

	public function __construct()
	{
		parent::__construct();
		$this->initPublicPageProcessMode();
	}

	protected function initPublicPageProcessMode()
	{
		$this->setPublicPageProcessMode(
			$this->isPublicSidePanel()
			|| (defined("PUBLIC_MODE") && PUBLIC_MODE == 1)
		);
	}

	public function setPublicPageProcessMode(bool $mode): void
	{
		$this->publicPageProcessMode = $mode;
	}

	public function getPublicPageProcessMode(): bool
	{
		return $this->publicPageProcessMode;
	}

	public function setSkipResponse($skip)
	{
		$this->skipResponse = $skip;
	}

	public function sendSuccessResponse($responseType = "", $dataToForm = array())
	{
		$post = $this->request->getPostList()->toArray();

		$listActions = array();
		switch ($responseType)
		{
			case "base":
				if ($post["save"] != "")
					$listActions[] = "destroy";
				if ($post["save_and_add"] != "")
					$listActions[] = "closeAndOpen";
				break;
			case "apply":
				if ($post["save"] != "")
					$listActions[] = "close";
				if ($post["apply"] != "")
					$listActions[] = "reload";
				break;
			case "close":
				$listActions[] = "close";
				break;
			case "destroy":
				$listActions[] = "destroy";
				break;
			default:
				$listActions[] = "close";
		}

		if (!empty($dataToForm["reloadUrl"]))
		{
			if ($this->isPublicSidePanel())
			{
				if (mb_strpos("publicSidePanel",$dataToForm["reloadUrl"]) === false)
				{
					$dataToForm["reloadUrl"] = CHTTP::urlAddParams(
						$dataToForm["reloadUrl"], array("publicSidePanel" => "Y"));
				}
			}
		}

		$this->sendJsonResponse(
			array(
				"status" => "success",
				"listActions" => $listActions,

				"formParams" => $dataToForm
			)
		);
	}

	public function reloadPage($redirectUrl, $type)
	{
		if ($this->isSidePanelRequest())
		{
			$redirectUrl = CHTTP::urlAddParams($redirectUrl, array(
				"IFRAME" => "Y",
				"IFRAME_TYPE" => "SIDE_SLIDER",
				"sidePanelAction" => $type)
			);
			if ($this->isPublicSidePanel())
			{
				$redirectUrl = CHTTP::urlAddParams($redirectUrl, array("publicSidePanel" => "Y"));
			}
			LocalRedirect($redirectUrl);
		}
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request and SipePanel request.
	 * @return boolean
	 */
	public function isSidePanelRequest()
	{
		return (($_REQUEST["IFRAME"] ?? '') == "Y") && (($_REQUEST["IFRAME_TYPE"] ?? '') == "SIDE_SLIDER");
	}

	public function isSidePanel()
	{
		return (($_REQUEST["IFRAME"] ?? '') === "Y");
	}

	public function isPublicSidePanel()
	{
		return ($this->isSidePanel() && (($_REQUEST["publicSidePanel"] ?? '') === "Y" || ($_REQUEST["IFRAME_TYPE"] ?? '') == "PUBLIC_FRAME"));
	}

	public function isSidePanelFrame()
	{
		return (($_REQUEST["IFRAME"] ?? '') == "Y") && (($_REQUEST["IFRAME_TYPE"] ?? '') == "SIDE_SLIDER");
	}

	public function isPublicFrame()
	{
		return (($_REQUEST["IFRAME"] ?? '') == "Y") && (($_REQUEST["IFRAME_TYPE"] ?? '') == "PUBLIC_FRAME");
	}

	public function setDefaultQueryParams($url)
	{
		if ($this->isSidePanel())
		{
			$frameType = "SIDE_SLIDER";
			if ($this->isPublicFrame())
			{
				$frameType = "PUBLIC_FRAME";
			}
			$params = array("IFRAME" => "Y", "IFRAME_TYPE" => $frameType);
			if ($this->isPublicSidePanel())
			{
				$params["publicSidePanel"] = "Y";
			}
			return \CHTTP::urlAddParams($url, $params);
		}
		else
		{
			return $url;
		}
	}

	public function editUrlToPublicPage($url)
	{
		if ($this->getPublicPageProcessMode())
		{
			$url = str_replace(".php", "/", $url);
		}

		return $url;
	}

	public function localRedirect($url)
	{
		if ($this->isPublicFrame())
		{
			$url = (strpos($url, '/') === 0 ? $url: '/');
			$url = '/'.ltrim($url, '/');

			echo "<script>";
			echo "top.window.location.href = '".CUtil::JSEscape($url)."';";
			echo "</script>";
			exit;
		}
	}
}

/* Left tree-view menu */
class CAdminMenu
{
	var $aGlobalMenu, $aActiveSections=array(), $aOpenedSections=array();
	var $bInit = false;

	public function __construct()
	{
	}

	function Init($modules)
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $APPLICATION, $USER, $DB, $MESS;

		if($this->bInit)
			return;
		$this->bInit = true;

		$aOptMenu = CUserOptions::GetOption("admin_menu", "pos", array());
		$this->AddOpenedSections($aOptMenu["sections"]);

		$aModuleMenu = array();
		if(is_array($modules))
		{
			foreach($modules as $module)
			{
				$module = _normalizePath($module);

				//trying to include file menu.php in the /admin/ folder of the current module
				$fname = getLocalPath("modules/".$module."/admin/menu.php");
				if($fname !== false)
				{
					$menu = CAdminMenu::_IncludeMenu($_SERVER["DOCUMENT_ROOT"].$fname);
					if(is_array($menu) && !empty($menu))
					{
						if(isset($menu["parent_menu"]) && $menu["parent_menu"] <> "")
						{
							//one section
							$aModuleMenu[] = $menu;
						}
						else
						{
							//multiple sections
							foreach($menu as $submenu)
							{
								if(is_array($submenu) && !empty($submenu))
								{
									$aModuleMenu[] = $submenu;
								}
							}
						}
					}
				}
			}
		}

		//additional user menu
		$aMenuLinks = array();
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/admin/.left.menu.php"))
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/admin/.left.menu.php");
		if(!empty($aMenuLinks))
		{
			$bWasSeparator = false;
			$menu = array();
			foreach($aMenuLinks as $module_menu)
			{
				if($module_menu[3]["SEPARATOR"] == "Y")
				{
					//first level
					if(!empty($menu))
						$aModuleMenu[] = $menu;

					$menu = array(
						"parent_menu" => "global_menu_services",
						"icon" => "default_menu_icon",
						"page_icon" => "default_page_icon",
						"items_id"=>$module_menu[3]["SECTION_ID"],
						"items"=>array(),
						"sort"=>$module_menu[3]["SORT"],
						"text" => $module_menu[0],
					);
					$bWasSeparator = true;
				}
				elseif($bWasSeparator && $module_menu[3]["SECTION_ID"] == "")
				{
					//section items
					$menu["items"][] = array(
						"text" => $module_menu[0],
						"title"=>$module_menu[3]["ALT"],
						"url" => $module_menu[1],
						"more_url"=>$module_menu[2],
					);
				}
				elseif($module_menu[3]["SECTION_ID"] == "" || $module_menu[3]["SECTION_ID"] == "statistic" || $module_menu[3]["SECTION_ID"] == "sale")
				{
					//item in root
					$aModuleMenu[] = array(
						"parent_menu" => ($module_menu[3]["SECTION_ID"] == "statistic"? "global_menu_statistics" : ($module_menu[3]["SECTION_ID"] == "sale"? "global_menu_store":"global_menu_services")),
						"icon" => "default_menu_icon",
						"page_icon" => "default_page_icon",
						"sort"=>$module_menu[3]["SORT"],
						"text" => $module_menu[0],
						"title"=>$module_menu[3]["ALT"],
						"url" => $module_menu[1],
						"more_url"=>$module_menu[2],
					);
				}
				else
				{
					//item in section
					foreach($aModuleMenu as $i=>$section)
					{
						if($section["section"] == $module_menu[3]["SECTION_ID"])
						{
							if(!is_array($aModuleMenu[$i]["items"]))
								$aModuleMenu[$i]["items"] = array();

							$aModuleMenu[$i]["items"][] = array(
								"text" => $module_menu[0],
								"title"=>$module_menu[3]["ALT"],
								"url" => $module_menu[1],
								"more_url"=>$module_menu[2],
							);
							break;
						}
					}
				}
			}
			if(!empty($menu))
				$aModuleMenu[] = $menu;
		}

		$this->aGlobalMenu = array(
			"global_menu_desktop" => array(
				"menu_id" => "desktop",
				"text" => GetMessage('admin_lib_desktop'),
				"title" => GetMessage('admin_lib_desktop_title'),
				"url" => "index.php?lang=".LANGUAGE_ID,
				"sort" => 50,
				"items_id" => "global_menu_desktop",
				"help_section" => "desktop",
				"items" => array()
			),
			"global_menu_content" => array(
				"menu_id" => "content",
				"text" => GetMessage("admin_lib_menu_content"),
				"title" => GetMessage("admin_lib_menu_content_title"),
				"sort" => 100,
				"items_id" => "global_menu_content",
				"help_section" => "content",
				"items" => array()
			),
			"global_menu_landing" => array(
				"menu_id" => "landing",
				"text" => GetMessage("admin_lib_menu_landing"),
				"sort" => 130,
				"items_id" => "global_menu_landing",
				"help_section" => "landing",
				"items" => array()
			),
			"global_menu_marketing" => array(
				"menu_id" => "marketing",
				"text" => GetMessage("admin_lib_menu_marketing"),
				"sort" => 150,
				"items_id" => "global_menu_marketing",
				"help_section" => "marketing",
				"items" => array()
			),
			"global_menu_store" => array(
				"menu_id" => "store",
				"text" => GetMessage("admin_lib_menu_store"),
				"title" => GetMessage("admin_lib_menu_store_title"),
				"sort" => 200,
				"items_id" => "global_menu_store",
				"help_section" => "store",
				"items" => array()
			),
			"global_menu_services" => array(
				"menu_id" => "services",
				"text" => GetMessage("admin_lib_menu_services"),
				"title" => GetMessage("admin_lib_menu_service_title"),
				"sort" => 300,
				"items_id" => "global_menu_services",
				"help_section" => "service",
				"items" => array()
			),
			"global_menu_statistics" => array(
				"menu_id" => "analytics",
				"text" => GetMessage("admin_lib_menu_stat"),
				"title" => GetMessage("admin_lib_menu_stat_title"),
				"sort" => 400,
				"items_id" => "global_menu_statistics",
				"help_section" => "statistic",
				"items" => array()
			),
			"global_menu_marketplace" => array(
				"menu_id" => "marketPlace",
				"text" => GetMessage("admin_lib_menu_marketplace"),
				"title" => GetMessage("admin_lib_menu_marketplace_title"),
				"url" => "update_system_market.php?lang=".LANGUAGE_ID,
				"sort" => 450,
				"items_id" => "global_menu_marketplace",
				"help_section" => "marketplace",
				"items" => array()
			),
			"global_menu_settings" => array(
				"menu_id" => "settings",
				"text" => GetMessage("admin_lib_menu_settings"),
				"title" => GetMessage("admin_lib_menu_settings_title"),
				"sort" => 500,
				"items_id" => "global_menu_settings",
				"help_section" => "settings",
				"items" => array()
			),
		);

		//User defined global sections
		$bSort = false;
		foreach(GetModuleEvents("main", "OnBuildGlobalMenu", true) as $arEvent)
		{
			$bSort = true;
			$arRes = ExecuteModuleEventEx($arEvent, array(&$this->aGlobalMenu, &$aModuleMenu));
			if(is_array($arRes))
				$this->aGlobalMenu = array_merge($this->aGlobalMenu, $arRes);
		}
		if($bSort)
			uasort($this->aGlobalMenu, array($this, '_sort'));

		foreach($aModuleMenu as $menu)
			$this->aGlobalMenu[$menu["parent_menu"]]["items"][] = $menu;

		$sort_func = array($this, '_sort');
		foreach($this->aGlobalMenu as $key => $menu)
		{
			if(empty($menu["items"]) && $key != "global_menu_desktop")
			{
				unset($this->aGlobalMenu[$key]);
			}
			elseif(is_array($this->aGlobalMenu[$key]["items"]))
			{
				usort($this->aGlobalMenu[$key]["items"], $sort_func);
			}
		}

		foreach($this->aGlobalMenu as $key=>$menu)
			if($this->_SetActiveItems($this->aGlobalMenu[$key]))
				break;
	}

	function _sort($a, $b)
	{
		if($a["sort"] == $b["sort"])
			return 0;
		return ($a["sort"] < $b["sort"]? -1 : 1);
	}

	function _IncludeMenu($fname)
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $APPLICATION, $USER, $DB, $MESS;

		$aModuleMenuLinks = array();
		$menu =  include($fname);

		if(is_array($menu) && !empty($menu))
			return $menu;

		if(!empty($aModuleMenuLinks))
		{
			$menu = array();
			$n = 0;
			foreach($aModuleMenuLinks as $module_menu)
			{
				if($n == 0)
				{
					//first level
					$menu = array(
						"parent_menu" => "global_menu_services",
						"icon" => "default_menu_icon",
						"page_icon" => "default_page_icon",
						"items_id"=>"sect_".md5($fname),
						"items"=>array(),
						"sort"=>$module_menu[3]["SORT"],
						"text" => $module_menu[0],
						"url" => $module_menu[1],
					);
				}
				else
				{
					//section items
					$menu["items"][] = array(
						"text" => $module_menu[0],
						"title"=>$module_menu[3]["ALT"],
						"url" => $module_menu[1],
						"more_url"=>$module_menu[2],
					);
				}
				$n++;
			}
			return $menu;
		}
		return false;
	}

	function _SetActiveItems(&$aMenu, $aSections=array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$bSubmenu = (isset($aMenu["items"]) && is_array($aMenu["items"]) && !empty($aMenu["items"]));
		if($bSubmenu)
			$aSections[$aMenu["items_id"]] = array(
				"menu_id" => $aMenu["menu_id"],
				"items_id"=>$aMenu["items_id"],
				"page_icon"=>isset($aMenu["page_icon"])? $aMenu["page_icon"]: null,
				"text"=>$aMenu["text"],
				"url"=>isset($aMenu["url"]) ? $aMenu["url"] : null,
				"skip_chain"=>isset($aMenu["skip_chain"])? $aMenu["skip_chain"]: null,
				"help_section"=>isset($aMenu["help_section"])? $aMenu["help_section"]: null,
			);

		$bSelected = false;
		$mainUrl = (isset($aMenu["url"]) && $aMenu["url"] <> "");
		$bMoreUrl = (!empty($aMenu["more_url"]) && is_array($aMenu["more_url"]));
		if($mainUrl || $bMoreUrl)
		{
			$cur_page = $APPLICATION->GetCurPage();

			$all_links = array();
			if($mainUrl)
				$all_links[] = $aMenu["url"];
			if($bMoreUrl)
				$all_links = array_merge($all_links, $aMenu["more_url"]);

			$n = count($all_links);
			for($j = 0; $j < $n; $j++)
			{
				//"/admin/"
				//"/admin/index.php"
				//"/admin/index.php?module=mail"
				if(empty($all_links[$j]))
					continue;

				if(mb_strpos($all_links[$j],"/bitrix/admin/") !== 0)
					$tested_link = "/bitrix/admin/".$all_links[$j];
				else
					$tested_link = $all_links[$j];

				if($tested_link <> '' && mb_strpos($cur_page,$tested_link) === 0)
				{
					$bSelected = true;
					break;
				}

				if(($pos = mb_strpos($tested_link,"?"))!==false)
				{
					if(mb_substr($tested_link,0,$pos)==$cur_page)
					{
						$right = mb_substr($tested_link,$pos+1);
						$params = explode("&", $right);
						$bOK = true;

						foreach ($params as $paramKeyAndValue)
						{
							$eqpos = mb_strpos($paramKeyAndValue,"=");
							$varvalue = "";
							if($eqpos === false)
							{
								$varname = $paramKeyAndValue;
							}
							elseif($eqpos == 0)
							{
								continue;
							}
							else
							{
								$varname = mb_substr($paramKeyAndValue,0,$eqpos);
								$varvalue = urldecode(mb_substr($paramKeyAndValue,$eqpos+1));
							}

							$globvarvalue = isset($_REQUEST[$varname]) ? $_REQUEST[$varname] : "";
							if($globvarvalue != $varvalue)
							{
								$bOK = false;
								break;
							}
						} //foreach ($params as $paramKeyAndValue)

						if($bOK)
						{
							$bSelected = true;
							break;
						}
					}//if(substr($tested_link, 0, $pos)==$cur_page)
				} //if(($pos = strpos($tested_link, "?"))!==false)
			} //for($j = 0; $j < $n; $j++)
		}

		$bSelectedInside = false;
		if($bSubmenu)
		{
			foreach($aMenu["items"] as $key=>$submenu)
				if($this->_SetActiveItems($aMenu["items"][$key], $aSections))
				{
					$bSelectedInside = true;
					break;
				}
		}

		if($bSelected && !$bSelectedInside)
		{
			if(!$bSubmenu)
			{
				$aSections["_active"] = array(
					"menu_id"=>$aMenu["menu_id"],
					"page_icon"=>isset($aMenu["page_icon"])? $aMenu["page_icon"]: null,
					"text"=>$aMenu["text"],
					"url"=>$aMenu["url"],
					"skip_chain"=>isset($aMenu["skip_chain"])? $aMenu["skip_chain"]: null,
					"help_section"=>isset($aMenu["help_section"]) ? $aMenu["help_section"]: null,
				);
			}
			$aMenu["_active"] = true;
			$this->aActiveSections = $aSections;
		}

		return $bSelected || $bSelectedInside;
	}

	private function _get_menu_item_width($level)
	{
		static $START_MAGIC_NUMBER = 30, $STEP_MAGIC_NUMBER = 21;
		return $START_MAGIC_NUMBER + $level*$STEP_MAGIC_NUMBER;
	}

	private function _get_menu_item_padding($level)
	{
		static $ADDED_MAGIC_NUMBER = 8;
		return $this->_get_menu_item_width($level) + $ADDED_MAGIC_NUMBER;
	}

	function Show($aMenu, $level=0)
	{
		$scripts = '';

		$bSubmenu = (isset($aMenu["items"]) && is_array($aMenu["items"]) && !empty($aMenu["items"])) || isset($aMenu["dynamic"]) && $aMenu["dynamic"] == true;
		$bSectionActive = isset($aMenu["items_id"]) && (in_array($aMenu["items_id"], array_keys($this->aActiveSections)) || $this->IsSectionActive($aMenu["items_id"]));

		$icon = isset($aMenu["icon"]) && $aMenu["icon"] <> ""
			? '<span class="adm-submenu-item-link-icon '.$aMenu["icon"].'"></span>'
//			: ($level < 1 ? '<span class="adm-submenu-item-link-icon" id="default_menu_icon"></span>' : '');
			: '';
		$id = 'menu_item_'.RandString(10);
		?><div class="adm-sub-submenu-block<?=$level > 0 ? ' adm-submenu-level-'.($level+1) : ''?><?=$bSectionActive && isset($aMenu["items"]) && is_array($aMenu["items"]) && count($aMenu['items']) > 0 ? ' adm-sub-submenu-open' : ''?><?=$aMenu["_active"] ? ' adm-submenu-item-active' : ''?>"><?
		?><div class="adm-submenu-item-name<?=!$bSubmenu ? ' adm-submenu-no-children' : ''?>" id="<?=$id?>" data-type="submenu-item"<?=isset($aMenu['fav_id']) ? ' data-fav-id="'.intval($aMenu['fav_id']).'"' : ''?>><?
		$onclick = '';
		if ($bSubmenu)
		{
			if(isset($aMenu["dynamic"]) && $aMenu["dynamic"] == true && (!$aMenu["items"] || count($aMenu["items"]) <= 0))
			{
				$onclick = "BX.adminMenu.toggleDynSection(".$this->_get_menu_item_width($level).", this.parentNode.parentNode, '".htmlspecialcharsbx(CUtil::JSEscape($aMenu["module_id"]))."', '".urlencode(htmlspecialcharsbx(CUtil::JSEscape($aMenu["items_id"])))."', '".($level+1)."')";
			}
			elseif(!$aMenu["dynamic"] || !$bSectionActive || $aMenu['dynamic'] && $bSectionActive && isset($aMenu["items"]) && count($aMenu["items"]) > 0)
			{
				$onclick = "BX.adminMenu.toggleSection(this.parentNode.parentNode, '".htmlspecialcharsbx(CUtil::JSEscape($aMenu["items_id"]))."', '".($level+1)."')";
			} //endif;
		}

		?><span class="adm-submenu-item-arrow"<?=$level > 0 ? ' style="width:'.$this->_get_menu_item_width($level).'px;"' : ''?><?=$onclick ? ' onclick="'.$onclick.'"' : ''?>><span class="adm-submenu-item-arrow-icon"></span></span><?

		$menuText = htmlspecialcharsbx(htmlspecialcharsback($aMenu["text"]));
		if(isset($aMenu["url"]) && $aMenu["url"] <> ""):
			$menuUrl = htmlspecialcharsbx($aMenu["url"], ENT_COMPAT, false);
			?><a class="adm-submenu-item-name-link<?=(isset($aMenu["readonly"]) && $aMenu["readonly"] == true? ' menutext-readonly':'')?>"<?=$level > 0 ? ' style="padding-left:'.$this->_get_menu_item_padding($level).'px;"' : ''?> href="<?=$menuUrl?>"><?=$icon?><span class="adm-submenu-item-name-link-text"><?=$menuText?></span></a><?
		elseif ($bSubmenu):
			if(isset($aMenu["dynamic"]) && $aMenu["dynamic"] == true && !$bSectionActive && (!$aMenu["items"] || count($aMenu["items"]) <= 0)):
				?><a class="adm-submenu-item-name-link<?=(isset($aMenu["readonly"]) && $aMenu["readonly"] == true? ' menutext-readonly':'')?>"<?=$level > 0 ? ' style="padding-left:'.$this->_get_menu_item_padding($level).'px;"' : ''?> href="javascript:void(0)" onclick="BX.adminMenu.toggleDynSection(<?=$this->_get_menu_item_width($level)?>, this.parentNode.parentNode, '<?=htmlspecialcharsbx(CUtil::JSEscape($aMenu["module_id"]))?>', '<?=htmlspecialcharsbx(CUtil::JSEscape($aMenu["items_id"]))?>', '<?=$level+1?>')"><?=$icon?><span class="adm-submenu-item-name-link-text"><?=$menuText?></span></a><?
			elseif(!$aMenu["dynamic"] || !$bSectionActive || $aMenu['dynamic'] && $bSectionActive && isset($aMenu["items"]) && count($aMenu["items"]) > 0):
				?><a class="adm-submenu-item-name-link<?=(isset($aMenu["readonly"]) && $aMenu["readonly"] == true? ' menutext-readonly':'')?>"<?=$level > 0 ? ' style="padding-left:'.$this->_get_menu_item_padding($level).'px;"' : ''?> href="javascript:void(0)" onclick="BX.adminMenu.toggleSection(this.parentNode.parentNode, '<?=htmlspecialcharsbx(CUtil::JSEscape($aMenu["items_id"]))?>', '<?=$level+1?>')"><?=$icon?><span class="adm-submenu-item-name-link-text"><?=$menuText?></span></a><?
			else:
				?><span class="adm-submenu-item-name-link<?=(isset($aMenu["readonly"]) && $aMenu["readonly"] == true? ' menutext-readonly':'')?>"<?=$level > 0 ? ' style="padding-left:'.$this->_get_menu_item_padding($level).'px"' : ''?>><?=$icon?><span class="adm-submenu-item-name-link-text"><?=$menuText?></span></span><?
			endif;
		else:
			?><span class="adm-submenu-item-name-link<?=(isset($aMenu["readonly"]) && $aMenu["readonly"] == true? ' menutext-readonly':'')?>"<?=$level > 0 ? ' style="padding-left:'.$this->_get_menu_item_padding($level).'px"' : ''?>><?=$icon?><span class="adm-submenu-item-name-link-text"><?=$menuText?></span></span><?
		endif;
		?></div><?

		if(($bSubmenu || (isset($aMenu["dynamic"]) && $aMenu["dynamic"] == true)) && is_array($aMenu["items"]))
		{
			echo  "<div class=\"adm-sub-submenu-block-children\">";
			foreach($aMenu["items"] as $submenu)
			{
				if($submenu)
				{
					$scripts .= $this->Show($submenu, $level+1);
				}
			}
			echo "</div>";
		}
		else
			echo  "<div class=\"adm-sub-submenu-block-children\"></div>";
?></div><?
		$url = str_replace("&amp;", "&", $aMenu['url']);

		if (isset($aMenu["fav_id"]))
		{
			$scripts .= "BX.adminMenu.registerItem('".$id."', {FAV_ID:'".CUtil::JSEscape($aMenu['fav_id'])."'});";
		}
		elseif (isset($aMenu["items_id"]) && $aMenu['url'])
		{
			$scripts .= "BX.adminMenu.registerItem('".$id."', {ID:'".CUtil::JSEscape($aMenu['items_id'])."', URL:'".CUtil::JSEscape($url)."', MODULE_ID:'".$aMenu['module_id']."'});";
		}
		elseif (isset($aMenu["items_id"]))
		{
			$scripts .= "BX.adminMenu.registerItem('".$id."', {ID:'".CUtil::JSEscape($aMenu['items_id'])."', MODULE_ID:'".$aMenu['module_id']."'});";
		}
		elseif ($aMenu['url'])
		{
			$scripts .= "BX.adminMenu.registerItem('".$id."', {URL:'".CUtil::JSEscape($url)."'});";
		}

		return $scripts;
	}

	function ShowIcons($aMenu)
	{
		foreach($aMenu["items"] as $submenu)
		{
			if(!$submenu)
				continue;
			echo
				'<div class="index-icon-block" align="center">'.
				'<a href="'.$submenu["url"].'" title="'.$submenu["title"].'"><div class="index-icon" id="'.($submenu["page_icon"]<>""? $submenu["page_icon"]:$aMenu["page_icon"]).'"></div>'.
				'<div class="index-label">'.$submenu["text"].'</div></a>'.
				'</div>';
		}
		echo '<br clear="all">';
	}

	function ShowList($aMenu)
	{
		foreach($aMenu["items"] as $submenu)
		{
			if(!$submenu)
				continue;
			echo '<div class="index-list" id="'.($submenu["icon"]<>""? $submenu["icon"]:$aMenu["icon"]).'"><a href="'.$submenu["url"].'" title="'.$submenu["title"].'">'.$submenu["text"].'</a></div>';
		}
	}

	function ShowTable($aMenu)
	{
		$sTableID = "module_index_table";
		// List init
		$lAdmin = new CAdminList($sTableID);

		// List headers
		$lAdmin->AddHeaders(array(
			array("id"=>"NAME", "content"=>GetMessage("admin_lib_index_name"), "default"=>true),
			array("id"=>"DESCRIPTION", "content"=>GetMessage("admin_lib_index_desc"), "default"=>true),
		));

		$n = 0;
		foreach($aMenu["items"] as $submenu)
		{
			// Populate list with data
			if(!$submenu)
				continue;
			$row = &$lAdmin->AddRow(0, null, $submenu["url"], GetMessage("admin_lib_index_go"));
			$row->AddField("NAME", '<a href="'.$submenu["url"].'" title="'.$submenu["title"].'">'.$submenu["text"].'</a>');
			$row->AddField("DESCRIPTION", $submenu["title"]);
			$n++;
		}

		$lAdmin->Display();

		echo '
<script>
'.$sTableID.'.InitTable();
</script>
';
	}

	function ShowSubmenu($menu_id, $mode="menu")
	{
		foreach($this->aGlobalMenu as $key=>$menu)
			if($this->_ShowSubmenu($this->aGlobalMenu[$key], $menu_id, $mode))
				break;
	}

	function _ShowSubmenu(&$aMenu, $menu_id, $mode, $level=0)
	{
		$bSubmenu = (is_array($aMenu["items"]) && count($aMenu["items"])>0);
		if($bSubmenu)
		{
			if($aMenu["items_id"] == $menu_id)
			{
				if($mode == "menu")
				{
					$menuScripts = "";
					foreach($aMenu["items"] as $submenu)
					{
						$menuScripts .= $this->Show($submenu, $level);
					}
					if ($menuScripts != "")
						echo '<script type="text/javascript">'.$menuScripts.'</script>';
				}
				elseif($mode == "icon")
					$this->ShowIcons($aMenu);
				elseif($mode == "list")
					$this->ShowList($aMenu);
				elseif($mode == "table")
					$this->ShowTable($aMenu);

				return true;
			}
			else
			{
				foreach($aMenu["items"] as $submenu)
					if($this->_ShowSubmenu($submenu, $menu_id, $mode, $level+1))
						return true;
			}
		}
		return false;
	}

	function ActiveSection()
	{
		if(!empty($this->aActiveSections))
			foreach($this->aActiveSections as $menu)
				return $menu;

		foreach($this->aGlobalMenu as $menu)
			return $menu;

		return null;
	}

	function ActiveIcon()
	{
		if(!empty($this->aActiveSections))
		{
			$aSections = array_keys($this->aActiveSections);
			for($i=count($aSections)-1; $i>=0; $i--)
				if($this->aActiveSections[$aSections[$i]]["page_icon"] <> "")
					return $this->aActiveSections[$aSections[$i]]["page_icon"];
		}
		return "default_page_icon";
	}

	function AddOpenedSections($sections)
	{
		$aSect = explode(",", $sections);
		foreach($aSect as $sect)
			if(trim($sect) <> "")
				$this->aOpenedSections[] = trim($sect);
	}

	function IsSectionActive($section)
	{
		return in_array($section, $this->aOpenedSections);
	}

	function GetOpenedSections()
	{
		return implode(",", $this->aOpenedSections);
	}
}

/* Popup menu */
class CAdminPopup
{
	var $name;
	var $id;
	var $items;
	var $params;

	public function __construct($name, $id, $items=false, $params=false)
	{
		//SEPARATOR, ID, ONCLICK, ICONCLASS, TEXT, DEFAULT=>true|false, DISABLED=>true|false
		$this->name = $name;
		$this->id = $id;
		$this->items = $items;
		$this->params = $params;
	}

	function Show($bReturnValue=false)
	{
		$s = '';
		if(!isset($_REQUEST["mode"]) || $_REQUEST["mode"] != "frame")
		{
			$s .=
'<script>
window.'.$this->name.' = new PopupMenu("'.$this->id.'"'.
	(is_array($this->params) && isset($this->params['zIndex'])? ', '.$this->params['zIndex']:'').
	(is_array($this->params) && isset($this->params['dxShadow'])? ', '.$this->params['dxShadow']:'').
');
';
			if(is_array($this->items))
			{
				$s .=
'window.'.$this->name.'.SetItems('.CAdminPopup::PhpToJavaScript($this->items).');
';
			}
			$s .=
'</script>
';
		}
		if($bReturnValue)
			return $s;
		else
			echo $s;
		return null;
	}

	public static function GetGlobalIconClass($old_icon)
	{
		switch($old_icon)
		{
			case 'edit':
				return 'adm-menu-edit';

			case 'view':
			case 'btn_fileman_view':
				return 'adm-menu-view';

			case 'copy':
				return 'adm-menu-copy';

			case 'move':
				return 'adm-menu-move';

			case 'rename':
				return 'adm-menu-rename';

			case 'delete':
				return 'adm-menu-delete';

			case 'btn_fileman_html':
				return 'adm-menu-edit-htm';

			case 'btn_fileman_php':
				return 'adm-menu-edit-php';

			case 'btn_fileman_text':
				return 'adm-menu-edit-txt';

			case 'btn_fileman_galka': // so it is
				return 'adm-menu-edit-wf';

			case 'btn_download':
				return 'adm-menu-download';

			case 'pack':
				return 'adm-menu-pack';

			case 'unpack':
				return 'adm-menu-unpack';

			case 'access':
				return 'adm-menu-access';

			case 'btn_fileman_prop':
				return 'adm-menu-folder-props';
		}

		return false;
	}

	public static function PhpToJavaScript($items)
	{
		$sMenuUrl = "[";
		if(is_array($items))
		{
			$i = 0;
			foreach($items as $action)
			{
				if($i > 0)
					$sMenuUrl .= ",\n";

				if(isset($action["SEPARATOR"]) && ($action["SEPARATOR"] === true || $action["SEPARATOR"] == "Y"))
					$sMenuUrl .= "{'SEPARATOR':true}";
				else
				{
					if(($action["ONCLICK"] ?? '') <> "")
						$action["ACTION"] = $action["ONCLICK"];

					if (isset($action["ICON"]) && $action["ICON"]<>""
						&& empty($action["GLOBAL_ICON"]))
					{
						$icon_global_class = CAdminPopup::GetGlobalIconClass($action["ICON"]);
						if ($icon_global_class)
						{
							$action["GLOBAL_ICON"] = $icon_global_class;
							unset($action["ICON"]);
						}
					}

					$sItem =
						(isset($action["LINK"]) && $action["LINK"]<>""? "'LINK':'".CUtil::JSEscape($action["LINK"])."',":"").
						(isset($action["DEFAULT"]) && $action["DEFAULT"] === true? "'DEFAULT':true,":"").
						(isset($action["CHECKED"]) && $action["CHECKED"] === true? "'CHECKED':true,":"").
						(isset($action["ICON"]) && $action["ICON"]<>""? "'ICONCLASS':'".CUtil::JSEscape($action["ICON"])."',":"").
						(isset($action["GLOBAL_ICON"]) && $action["GLOBAL_ICON"]<>""? "'GLOBAL_ICON':'".CUtil::JSEscape($action["GLOBAL_ICON"])."',":"").
						(isset($action["IMAGE"]) && $action["IMAGE"]<>""? "'IMAGE':'".CUtil::JSEscape($action["IMAGE"])."',":"").
						(isset($action["ID"]) && $action["ID"]<>""? "'ID':'".CUtil::JSEscape($action["ID"])."',":"").
						(isset($action["DISABLED"]) && $action["DISABLED"] == true? "'DISABLED':true,":"").
						(isset($action["AUTOHIDE"]) && $action["AUTOHIDE"] == false? "'AUTOHIDE':false,":"").
						(isset($action["DEFAULT"]) && $action["DEFAULT"] == true? "'DEFAULT':true,":"").
						(($action["TEXT"] ?? '') <> '' ? "'TEXT':'".CUtil::JSEscape($action["TEXT"])."'," : "").
						(($action["HTML"] ?? '') <> '' ? "'HTML':'".CUtil::JSEscape($action["HTML"])."'," : "").
						(isset($action["TITLE"]) && $action["TITLE"]<>""? "'TITLE':'".CUtil::JSEscape($action["TITLE"])."',":"").
						(isset($action["SHOW_TITLE"]) && $action["SHOW_TITLE"] == true ? "'SHOW_TITLE':true,":"").
						(($action["ACTION"] ?? '') <> '' ? "'ONCLICK':'".CUtil::JSEscape(str_replace("&amp;", "&", $action["ACTION"]))."'," : "").
						(isset($action["ONMENUPOPUP"]) && $action["ONMENUPOPUP"]<>""? "'ONMENUPOPUP':'".CUtil::JSEscape($action["ONMENUPOPUP"])."',":"").
						(isset($action["MENU"]) && is_array($action["MENU"])? "'MENU':".CAdminPopup::PhpToJavaScript($action["MENU"]).",":"").
						(isset($action["MENU_URL"]) && $action["MENU_URL"]<>''? "'MENU_URL':'".CUtil::JSEscape($action["MENU_URL"])."',":"").
						(isset($action["MENU_PRELOAD"]) && $action["MENU_PRELOAD"] == true? "'MENU_PRELOAD':true,":"").
						(isset($action["CLOSE_ON_CLICK"]) && $action["CLOSE_ON_CLICK"] == false? "'CLOSE_ON_CLICK':false,":"");
					if($sItem <> "")
						$sItem = mb_substr($sItem,0,-1); //delete last comma
					$sMenuUrl .= "{".$sItem."}";
				}
				$i++;
			}
		}
		$sMenuUrl .= "]";
		return $sMenuUrl;
	}
}

class CAdminPopupEx extends CAdminPopup
{
	protected $element_id;

	/**
	 * @param string $element_id
	 * @param bool|array $items
	 * @param bool|array $params
	 */
	public function __construct($element_id, $items=false, $params=false)
	{
		//SEPARATOR, ID, ONCLICK|LINK, ICONCLASS, TEXT, DEFAULT=>true|false, MENU
		$this->element_id = $element_id;
		$this->items = $items;
		$this->params = $params;
	}

	public function Show($bReturnValue=false)
	{
		$s = '';
		if((!isset($_REQUEST["mode"]) || $_REQUEST["mode"] != "frame") && is_array($this->items))
		{
			$params = '';
			if (is_array($this->params))
				$params = ', '.CUtil::PhpToJsObject($params);

			$s .=
"<script type=\"text/javascript\">
BX.ready(function(){
	BX.bind(BX('".$this->element_id."'), 'click', function() {
		BX.adminShowMenu(this, ".CAdminPopup::PhpToJavaScript($this->items).$params.");
	});
});
</script>";
		}

		if($bReturnValue)
			return $s;
		else
			echo $s;
		return null;
	}
}

/* Context links menu for edit forms */
class CAdminContextMenu
{
	var $items;
	var $additional_items;
	var $bMenuAdded = false;
	var $bRightBarAdded = false;
	var $isSidePanel = false;
	var $isPublicMode = false;
	var $isPublicSidePanel = false;
	var $isPublicFrame = false;

	public function __construct($items, $additional_items = array())
	{
		global $adminSidePanelHelper;
		if (!is_object($adminSidePanelHelper))
		{
			require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
			$adminSidePanelHelper = new CAdminSidePanelHelper();
		}

		$this->isSidePanel = $adminSidePanelHelper->isSidePanelFrame();
		$this->isPublicMode = (defined("PUBLIC_MODE") && PUBLIC_MODE == 1);
		$this->isPublicSidePanel = $adminSidePanelHelper->isPublicSidePanel();
		$this->isPublicFrame = $adminSidePanelHelper->isPublicFrame();

		$this->prepareItemLink($items);
		$this->prepareItemLink($additional_items);

		$this->items = $items;
		$this->additional_items = $additional_items;
	}

	function prepareItemLink(array &$listItems)
	{
		foreach ($listItems as &$item)
		{
			if (!empty($item["LINK"]) && !$item["PUBLIC"])
			{

				$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");
				$reqValue = "/".str_replace("/", "\/", $selfFolderUrl)."/i";
				if (!preg_match($reqValue, $item["LINK"]) && !preg_match("/javascript:/", $item["LINK"]))
				{
					$item["LINK"] = $selfFolderUrl.$item["LINK"];
				}
			}

			switch ($item["ICON"])
			{
				case "btn_list":
					if ($this->isSidePanel)
					{
						if (isset($item["TYPE"]))
						{
							switch ($item["TYPE"])
							{
								case "default":
									global $adminSidePanelHelper;
									$item["LINK"] = $adminSidePanelHelper->setDefaultQueryParams($item["LINK"]);
									break;
							}
						}
						else
						{
							if (empty($item["ONCLICK"]))
							{
								$item["ONCLICK"] = "top.BX.onCustomEvent('SidePanel:close');";
							}
						}
					}
					else
					{
						if (!empty($item["LINK"]) && preg_match("/set_default/", $item["LINK"]))
							$item["LINK"] .= "&apply_filter=Y";
					}
					break;
				case "delete":
				case "btn_delete":
					if ($this->isSidePanel)
					{
						if (empty($item["ONCLICK"]))
						{
							$link = $item["ACTION"] ? $item["ACTION"] : $item["LINK"];
							if (preg_match("/javascript:/", $item["LINK"]) || !empty($item["ACTION"]))
							{
								if (preg_match("/window.location=(?P<postUrl>[^<]+(\"|\'))/", $link, $found) ||
									preg_match("/window.location.href=(?P<postUrl>[^<]+(\"|\'))/", $link, $found))
								{
									$confirmText = "";
									$postUrl = $found["postUrl"];
									if (preg_match("/confirm\((?P<text>[^<]+(\"|\'))\)/", $link, $found))
									{
										$confirmText = $found["text"];
									}

									if ($confirmText && $postUrl)
									{
										$item["ONCLICK"] = "if(confirm(".$confirmText.
										")) top.BX.onCustomEvent('AdminSidePanel:onSendRequest', [".$postUrl."]);";
									}

								}
								else
								{
									$item["ONCLICK"] = $item["LINK"];
								}
								unset($item["LINK"]);
								unset($item["ACTION"]);
							}
						}
					}
					break;
			}

			if (!empty($item["MENU"]))
			{
				$this->prepareItemLink($item["MENU"]);
			}
		}
	}

	function Show()
	{
		if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
			return;

		foreach(GetModuleEvents("main", "OnAdminContextMenuShow", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(&$this->items, &$this->additional_items));
		}

		if(empty($this->items) && empty($this->additional_items))
		{
			return;
		}

		$hkInst = CHotKeys::getInstance();

		$bFirst = true;
		$bNeedSplitClosing = false;
		foreach($this->items as $item)
		{
			if(!empty($item["NEWBAR"]))
				$this->EndBar();

			if($bFirst || !empty($item["NEWBAR"]))
				$this->BeginBar();

			if(!empty($item["NEWBAR"]) || !empty($item['SEPARATOR']))
				continue;

			if ($item['ICON'] != 'btn_list' && !$bNeedSplitClosing)
			{
				$this->BeginRightBar();
				$bNeedSplitClosing = true;
			}

			$this->Button($item, $hkInst);

			$bFirst = false;
		}

		if (!empty($this->additional_items))
		{
			if($bFirst)
			{
				$this->BeginBar();
			}

			$this->Additional();
		}

		if ($bNeedSplitClosing)
			$this->EndRightBar();

		$this->EndBar();
	}

	function BeginBar()
	{
?>
<div class="adm-detail-toolbar"><span style="position:absolute;"></span>
<?
	}

	function EndBar()
	{
?>
</div>
<?
	}

	function BeginRightBar()
	{
		$id = 'context_right_'.RandString(8);
?>
<script type="text/javascript">BX.ready(function(){
var right_bar = BX('<?=$id?>');
BX.Fix(right_bar, {type: 'right', limit_node: BX.previousSibling(right_bar)});
})</script>
<div class="adm-detail-toolbar-right" id="<?=$id?>">
<?
	}

	function EndRightBar()
	{
?>
</div>
<?
	}

	function GetClassByID($icon_id)
	{
		switch ($icon_id)
		{
			case 'btn_new':
				return 'adm-btn-add';
			case 'btn_copy':
				return 'adm-btn-copy';
			case 'btn_delete':
				return 'adm-btn-delete';
			case 'btn_desktop_gadgets':
				return 'adm-btn-desktop-gadgets';
			case 'btn_desktop_settings':
				return 'adm-btn-desktop-settings';
			case 'btn_active':
				return 'adm-btn-active';
			case 'btn_green':
				return 'adm-btn-green';
		}

		return '';
	}

	function GetActiveClassByID($icon_id)
	{
		return 'adm-btn-active';
	}

	/**
	 * @param array $item
	 * @param CHotKeys $hkInst
	 */
	function Button($item, $hkInst)
	{
		// $item["ICON"]
		if(isset($item["HTML"]) && $item["HTML"] <> "")
		{
			echo '<span class="adm-list-table-top-wrapper">'.$item['HTML'].'</span>';
		}
		elseif(!empty($item["MENU"]))
		{

			$sMenuUrl = "BX.adminShowMenu(this, ".htmlspecialcharsbx(CAdminPopup::PhpToJavaScript($item["MENU"])).
				", {active_class: '".$this->GetActiveClassByID($item["ICON"])."', public_frame: '".($this->isPublicFrame ? 1 : 0)."'});";
			$sClassName = $this->GetClassByID($item["ICON"]);
?>
	<a href="javascript:void(0)" hidefocus="true" onclick="this.blur();<?=$sMenuUrl?> return false;" class="adm-btn<?=$sClassName != '' ? ' '.$sClassName : ''?> adm-btn-menu" title="<?=($item["TITLE"] ?? '');?>"><?=$item["TEXT"]?></a>
<?
		}
		else
		{
			$link = htmlspecialcharsbx($item["LINK"], ENT_COMPAT, false);

			if ($item['ICON'] == 'btn_list'/* || $item['ICON'] == 'btn_up'*/):
?>
	<a <?if ($this->isPublicFrame):?>target="_top"<?endif;?> href="<?=($item["ONCLICK"] <> ''? 'javascript:void(0)' : $link)?>" <?=$item["LINK_PARAM"]?> class="adm-detail-toolbar-btn" title="<?=$item["TITLE"].$hkInst->GetTitle($item["ICON"])?>"<?=($item["ONCLICK"] <> ''? ' onclick="'.htmlspecialcharsbx($item["ONCLICK"]).'"':'')?><?=(!empty($item["ICON"])? ' id="'.$item["ICON"].'"':'')?>><span class="adm-detail-toolbar-btn-l"></span><span class="adm-detail-toolbar-btn-text"><?=$item["TEXT"]?></span><span class="adm-detail-toolbar-btn-r"></span></a>
<?
			else:
				$sClassName = $this->GetClassByID($item["ICON"]);
?>
	<a <?if ($this->isPublicFrame):?>target="_top"<?endif;?> href="<?=($item["ONCLICK"] <> ''? 'javascript:void(0)' : $link)?>" <?=$item["LINK_PARAM"]?> class="adm-btn<?=$sClassName != '' ? ' '.$sClassName : ''?>" title="<?=$item["TITLE"].$hkInst->GetTitle($item["ICON"])?>"<?=($item["ONCLICK"] <> ''? ' onclick="'.htmlspecialcharsbx($item["ONCLICK"]).'"' : '')?><?=(!empty($item["ICON"])? ' id="'.$item["ICON"].'"':'')?>><?=$item["TEXT"]?></a>

<?
			endif;

			$arExecs = $hkInst->GetCodeByClassName($item["ICON"]);
			echo $hkInst->PrintJSExecs($arExecs, "", true, true);
		}
	}

	function Additional()
	{
		$sMenuUrl = "BX.adminList.ShowMenu(this, ".htmlspecialcharsbx(CAdminPopup::PhpToJavaScript($this->additional_items)).");";
?>
	<div class="adm-small-button adm-table-setting" title="<?=htmlspecialcharsbx(GetMessage('admin_lib_context_sett_title'))?>" onclick="this.blur();<?=$sMenuUrl?> return false;" ></div>
<?
	}
}

/* Context links menu for lists */
class CAdminContextMenuList extends CAdminContextMenu
{
	function BeginBar()
	{
?>
<div class="adm-list-table-top">
<?
	}

	function GetClassByID($icon_id)
	{
		if (mb_substr($icon_id,0,7) == 'btn_new')
			return 'adm-btn-save adm-btn-add';
		else
			return parent::GetClassByID($icon_id);
	}

	function GetActiveClassByID($icon_id)
	{
		if (mb_substr($icon_id,0,7) == 'btn_new')
			return 'adm-btn-save-active';
		else
			return parent::GetActiveClassByID($icon_id);
	}

	function Button($item, $hkInst)
	{
		if (isset($item['ICON']) && $item['ICON'] == 'btn_list')
			$item['ICON'] = '';

		parent::Button($item, $hkInst);
	}

	function BeginRightBar() {}
	function EndRightBar() {}
}

/* Sorting in lists */
class CAdminSorting
{
	var $by_name;
	var $ord_name;
	var $table_id;
	var $by_initial;
	var $order_initial;

	protected $field;
	protected $order;

	/**
	 * @param string $table_id
	 * @param string|false $by_initial
	 * @param string|false $order_initial
	 * @param string $by_name
	 * @param string $ord_name
	 */
	public function __construct($table_id, $by_initial=false, $order_initial=false, $by_name="by", $ord_name="order")
	{
		$this->by_name = $by_name;
		$this->ord_name = $ord_name;
		$this->table_id = preg_replace('/[^a-z0-9_]/i', '', $table_id);
		$this->by_initial = $by_initial;
		$this->order_initial = $order_initial;

		$needUserByField = false;
		$needUserOrder = false;
		if(isset($GLOBALS[$this->by_name]))
		{
			\Bitrix\Main\Application::getInstance()->getSession()["SESS_SORT_BY"][$this->table_id] = $GLOBALS[$this->by_name];
		}
		elseif(isset(\Bitrix\Main\Application::getInstance()->getSession()["SESS_SORT_BY"][$this->table_id]))
		{
			$GLOBALS[$this->by_name] = \Bitrix\Main\Application::getInstance()->getSession()["SESS_SORT_BY"][$this->table_id];
		}
		else
		{
			$needUserByField = true;
		}

		if(isset($GLOBALS[$this->ord_name]))
		{
			\Bitrix\Main\Application::getInstance()->getSession()["SESS_SORT_ORDER"][$this->table_id] = $GLOBALS[$this->ord_name];
		}
		elseif(isset(\Bitrix\Main\Application::getInstance()->getSession()["SESS_SORT_ORDER"][$this->table_id]))
		{
			$GLOBALS[$this->ord_name] = \Bitrix\Main\Application::getInstance()->getSession()["SESS_SORT_ORDER"][$this->table_id];
		}
		else
		{
			$needUserOrder = true;
		}

		if ($needUserByField || $needUserOrder)
		{
			$userSorting = $this->getUserSorting();
			if ($needUserByField)
			{
				if (!empty($userSorting["by"]))
					$GLOBALS[$this->by_name] = $userSorting["by"];
				elseif ($this->by_initial !== false)
					$GLOBALS[$this->by_name] = $this->by_initial;
			}
			if ($needUserOrder)
			{
				if(!empty($userSorting["order"]))
					$GLOBALS[$this->ord_name] = $userSorting["order"];
				elseif($this->order_initial !== false)
					$GLOBALS[$this->ord_name] = $this->order_initial;
			}
		}

		$this->field = $GLOBALS[$this->by_name];
		$this->order = $GLOBALS[$this->ord_name];
	}

	/**
	 * @param string $text
	 * @param string $sort_by
	 * @param string|false $alt_title
	 * @param string $baseCssClass
	 * @return string
	 */
	public function Show($text, $sort_by, $alt_title = false, $baseCssClass = "")
	{
		$ord = "asc";
		$class = "";
		$title = GetMessage("admin_lib_sort_title")." ".($alt_title?$alt_title:$text);

		if(mb_strtolower($this->field) == mb_strtolower($sort_by))
		{
			if(mb_strtolower($this->order) == "desc")
			{
				$class = "-down";
				$title .= " ".GetMessage("admin_lib_sort_down");
			}
			else
			{
				$class = "-up";
				$title .= " ".GetMessage("admin_lib_sort_up");
				$ord = "desc";
			}
		}

		$path = $_SERVER["REQUEST_URI"];
		$sep = "?";
		if($_SERVER["QUERY_STRING"] <> "")
		{
			$path = preg_replace("/([?&])".$this->by_name."=[^&]*[&]*/i", "\\1", $path);
			$path = preg_replace("/([?&])".$this->ord_name."=[^&]*[&]*/i", "\\1", $path);
			$path = preg_replace("/([?&])mode=[^&]*[&]*/i", "\\1", $path);
			$path = preg_replace("/([?&])table_id=[^&]*[&]*/i", "\\1", $path);
			$path = preg_replace("/([?&])action=[^&]*[&]*/i", "\\1", $path);
			$sep = "&";
		}
		if(($last = mb_substr($path,-1,1)) == "?" || $last == "&")
			$sep = "";

		$url = $path.$sep.$this->by_name."=".$sort_by."&".$this->ord_name."=".($class <> ""? $ord:"");

		return 'class="'.$baseCssClass.' adm-list-table-cell-sort'.$class.'" onclick="'.$this->table_id.'.Sort(\''.htmlspecialcharsbx(CUtil::addslashes($url)).'\', '.($class <> ""? "false" : "true").', arguments);" title="'.$title.'"';
	}

	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field;
	}

	/**
	 * @return string
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @return array
	 */
	protected function getUserSorting()
	{
		$userSorting = CUserOptions::GetOption(
			"list",
			$this->table_id,
			array("by" => $this->by_initial, "order" => $this->order_initial)
		);
		return array(
			"by" => (!empty($userSorting["by"]) ? $userSorting["by"] : null),
			"order" => (!empty($userSorting["order"]) ? $userSorting["order"] : null)
		);
	}
}

/* Navigation */
/*
Important Notice:
	CIBlockResult has copy of the methods of this class
	because we need CIBlockResult::Fetch method to be called on iblock_element_admin.php page.
	So this page based on CIBlockResult not on CAdminResult!
*/
class CAdminResult extends CDBResult
{
	var $nInitialSize;
	var $table_id;

	/**
	* CAdminResult constructor.
	* @param mixed $res
	* @param string $table_id
	*/
	public function __construct($res, $table_id)
	{
		parent::__construct($res);
		$this->table_id = preg_replace('/[^a-z0-9_]/i', '', $table_id);
	}

	public function NavStart($nPageSize=20, $bShowAll=true, $iNumPage=false)
	{
		$nSize = self::GetNavSize($this->table_id, $nPageSize);

		if(!is_array($nPageSize))
			$nPageSize = array();

		$nPageSize["nPageSize"] = $nSize;
		if($_REQUEST["mode"] == "excel")
			$nPageSize["NavShowAll"] = true;

		$this->nInitialSize = $nPageSize["nPageSize"];

		parent::NavStart($nPageSize, $bShowAll, $iNumPage);
	}

	protected function parentNavStart($nPageSize, $bShowAll, $iNumPage)
	{
		parent::NavStart($nPageSize, $bShowAll, $iNumPage);
	}

	/**
	 * @param bool|string $table_id
	 * @param int|array $nPageSize
	 * @return int
	 */
	public static function GetNavSize($table_id=false, $nPageSize=20)
	{
		/** @global CMain $APPLICATION */
		global $NavNum, $APPLICATION;

		if (!isset($NavNum))
			$NavNum = 0;

		$bSess = (CPageOption::GetOptionString("main", "nav_page_in_session", "Y")=="Y");
		if(is_array($nPageSize))
			$sNavID = $nPageSize["sNavID"];
		$unique = md5((isset($sNavID)? $sNavID : $APPLICATION->GetCurPage()));

		if(isset($_REQUEST["SIZEN_".($NavNum+1)]))
		{
			$nSize = (int)$_REQUEST["SIZEN_".($NavNum+1)];
			if($bSess)
				\Bitrix\Main\Application::getInstance()->getSession()["NAV_PAGE_SIZE"][$unique] = $nSize;
		}
		elseif($bSess && isset(\Bitrix\Main\Application::getInstance()->getSession()["NAV_PAGE_SIZE"][$unique]))
		{
			$nSize = \Bitrix\Main\Application::getInstance()->getSession()["NAV_PAGE_SIZE"][$unique];
		}
		else
		{
			$aOptions = array();
			if($table_id)
				$aOptions = CUserOptions::GetOption("list", $table_id);
			if(intval($aOptions["page_size"]) > 0)
				$nSize = intval($aOptions["page_size"]);
			else
				$nSize = (is_array($nPageSize)? $nPageSize["nPageSize"]:$nPageSize);
		}
		return $nSize;
	}

	public function GetNavPrint($title, $show_allways=true, $StyleText="", $template_path=false, $arDeleteParam=false)
	{
		if($template_path === false)
			$template_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/navigation.php";
		return parent::GetNavPrint($title, $show_allways, $StyleText, $template_path, array('action', 'sessid'));
	}
}

class CAdminMessage
{
	/** @var CAdminException */
	var $exception;
	var	$message;

	/**
	 * @param string|array $message
	 * @param CAdminException|bool $exception
	 */
	public function __construct($message, $exception=false)
	{
		//array("MESSAGE"=>"", "TYPE"=>("ERROR"|"OK"|"PROGRESS"), "DETAILS"=>"", "HTML"=>true)
		if(!is_array($message))
			$message = array("MESSAGE"=>$message, "TYPE"=>"ERROR");
		if(empty($message["DETAILS"]) && $exception !== false)
			$message["DETAILS"] = $exception->GetString();
		$this->message = $message;
		$this->exception = $exception;
	}

	public function Show()
	{
		$publicMode = false;
		if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 && $this->message["TYPE"] != "PROGRESS" && (!isset($this->message['SKIP_PUBLIC_MODE']) || $this->message['SKIP_PUBLIC_MODE'] !== true))
		{
			$alertMessage = ($this->message['DETAILS'] <> ''? $this->message['DETAILS'] : $this->message['MESSAGE']);
			$alertMessage = htmlspecialcharsback($alertMessage); //we don't need html entities in an alert() box, see BX.CWindow.prototype.ShowError
			$alertMessage = str_replace(array('<br>', '<br />', '<BR>', '<BR />'), "\r\n", $alertMessage);

			ob_end_clean();
			echo "<script>
			var currentWindow = top.window;
			if (top.BX.SidePanel && top.BX.SidePanel.Instance && top.BX.SidePanel.Instance.getTopSlider())
			{
				currentWindow = top.BX.SidePanel.Instance.getTopSlider().getWindow();
			}
			currentWindow.BX.WindowManager.Get().ShowError('".CUtil::JSEscape($alertMessage)."');</script>";
			die();
		}

		if (defined('PUBLIC_MODE') && PUBLIC_MODE == 1)
		{
			$publicMode = true;
			\Bitrix\Main\UI\Extension::load("ui.alerts");
		}

		if($this->message["MESSAGE"])
			$title = '<div class="adm-info-message-title">'.$this->_formatHTML($this->message["MESSAGE"]).'</div>';
		else
			$title = '';

		if($this->message["DETAILS"])
			$details = $this->_formatHTML($this->message["DETAILS"]);
		else
			$details = '';

		if($this->message["TYPE"] == "OK")
		{
			$baseClass = "adm-info-message-wrap adm-info-message-green";
			$messageClass = "adm-info-message";
			if ($publicMode)
			{
				$baseClass = "ui-alert ui-alert-success";
				$messageClass = "ui-btn-message";
			}

			$s = '
			<div class="'.$baseClass.'">
				<div class="'.$messageClass.'">
					'.$title.'
					'.$details.'
					<div class="adm-info-message-icon"></div>
				</div>
			</div>
			';
		}
		elseif($this->message["TYPE"] == "PROGRESS")
		{
			$baseClass = "adm-info-message-wrap adm-info-message-gray";
			$messageClass = "adm-info-message";
			if ($publicMode)
			{
				$baseClass = "ui-alert ui-alert-primary";
				$messageClass = "ui-btn-message";
			}

			if ($this->message['PROGRESS_ICON'])
				$title = '<div class="adm-info-message-icon-progress"></div>'.$title;

			$details = str_replace("#PROGRESS_BAR#", $this->_getProgressHtml(), $details);
			$s = '
			<div class="'.$baseClass.'">
				<div class="'.$messageClass.'">
					'.$title.'
					'.$details.'
					<div class="adm-info-message-buttons">'.$this->_getButtonsHtml().'</div>
				</div>
			</div>
			';
		}
		else
		{
			$baseClass = "adm-info-message-wrap adm-info-message-red";
			$messageClass = "adm-info-message";
			if ($publicMode)
			{
				$baseClass = "ui-alert ui-alert-danger";
				$messageClass = "ui-btn-message";
			}
			$s = '
			<div class="'.$baseClass.'">
				<div class="'.$messageClass.'">
					'.$title.'
					'.$details.'
					<div class="adm-info-message-icon"></div>
				</div>
			</div>
			';
		}

		return $s;
	}

	public function _getProgressHtml()
	{
		$w = isset($this->message['PROGRESS_WIDTH']) ? intval($this->message['PROGRESS_WIDTH']) : 500;
		$p = 0;
		if ($this->message['PROGRESS_TOTAL'] > 0)
			$p = $this->message['PROGRESS_VALUE']/$this->message['PROGRESS_TOTAL'];

		if ($p < 0)
			$p = 0;
		elseif ($p > 1)
			$p = 1;

		$innerText = number_format(100*$p, 0) .'%';
		if ($this->message['PROGRESS_TEMPLATE'])
		{
			$innerText = str_replace(
				array('#PROGRESS_TOTAL#', '#PROGRESS_VALUE#', '#PROGRESS_PERCENT#'),
				array($this->message['PROGRESS_TOTAL'], $this->message['PROGRESS_VALUE'], $innerText),
				$this->message['PROGRESS_TEMPLATE']
			);
		}

		$s = '<div class="adm-progress-bar-outer" style="width: '.$w.'px;"><div class="adm-progress-bar-inner" style="width: '.intval($p*($w-4)).'px;"><div class="adm-progress-bar-inner-text" style="width: '.$w.'px;">'.$innerText.'</div></div>'.$innerText.'</div>';

		return $s;
	}

	public function _getButtonsHtml()
	{
		$s = '';
		if(isset($this->message["BUTTONS"]) && is_array($this->message["BUTTONS"]))
		{
			foreach($this->message["BUTTONS"] as $button)
				$s .= '<input type="button" onclick="'.htmlspecialcharsbx($button["ONCLICK"]).'" value="'.htmlspecialcharsbx($button["VALUE"]).'" '.($button["ID"]? 'id="'.htmlspecialcharsbx($button["ID"]).'"': '').'>';
		}
		return $s;
	}

	public function _formatHTML($html)
	{
		if($this->message["HTML"])
			return $html;
		else
			return _ShowHtmlspec($html);
	}

	public function GetMessages()
	{
		if($this->exception && method_exists($this->exception, 'GetMessages'))
			return $this->exception->GetMessages();
		return false;
	}

	public static function ShowOldStyleError($message)
	{
		if(!empty($message))
		{
			$m = new CAdminMessage(array("MESSAGE"=>GetMessage("admin_lib_error"), "DETAILS"=>$message, "TYPE"=>"ERROR"));
			echo $m->Show();
		}
	}

	public static function ShowMessage($message)
	{
		if(!empty($message))
		{
			$m = new CAdminMessage($message);
			echo $m->Show();
		}
	}

	public static function ShowNote($message)
	{
		if(!empty($message))
			CAdminMessage::ShowMessage(array("MESSAGE"=>$message, "TYPE"=>"OK"));
	}
}

class CAdminChain
{
	var $items = array();
	var $id, $bVisible;

	public function __construct($id=false, $bVisible=true)
	{
		$this->id = $id;
		$this->bVisible = $bVisible;
	}

	public function AddItem($item)
	{
		//array("TEXT"=>"", "LINK"=>"", "ONCLICK"=>"", "MENU"=>array(array("TEXT"=>"", "LINK"=>"", "CLASS"=>""), ...))
		$this->items[] = $item;
	}

	public function Show()
	{
		if(empty($this->items))
			return null;

		$chainScripts = '';

?>
<div class="adm-navchain"<?=($this->id ? ' id="'.$this->id.'"':'').($this->bVisible == false? ' style="display:none;"' : '')?>>
<?
		$last_item = null;

		$cnt = count($this->items)-1;
		foreach($this->items as $n => $item)
		{
			$openerUrl = '/bitrix/admin/get_start_menu.php?skip_recent=Y&lang='.LANGUAGE_ID.($item['ID'] ? '&mode=chain&admin_mnu_menu_id='.urlencode($item['ID']) : '');

			$className = !empty($item['CLASS'])?' '.htmlspecialcharsbx($item['CLASS']):'';

			$text = htmlspecialcharsbx(htmlspecialcharsback($item["TEXT"]));
			if (!empty($item['LINK']))
			{
				$link = htmlspecialcharsbx($item["LINK"], ENT_COMPAT, false);
				echo '<a class="adm-navchain-item" href="'.$link.'"'.
					(!empty($item["ONCLICK"])? ' onclick="'.htmlspecialcharsbx($item["ONCLICK"]).'"':'').
					'><span class="adm-navchain-item-text'.$className.'">'.$text.'</span></a>';
			}
			elseif (!empty($item['ID']))
			{
				echo '<a href="javascript:void(0)" class="adm-navchain-item" id="bx_admin_chain_item_'.$item['ID'].'"><span class="adm-navchain-item-text'.$className.'">'.$text.'</span></a>';

				$chainScripts .= 'new BX.COpener('.CUtil::PhpToJsObject(array(
					'DIV' => 'bx_admin_chain_item_'.$item['ID'],
					'ACTIVE_CLASS' => 'adm-navchain-item-active',
					'MENU_URL' => $openerUrl
				)).');';

			}
			else
			{
				echo '<span class="adm-navchain-item adm-navchain-item-empty'.$className.'"><span class="adm-navchain-item-text">'.$text.'</span></span>';
			}

			if ($n < $cnt)
			{
				if($item['ID'] || ($n==0 && $this->id == 'main_navchain'))
				{
					echo '<span class="adm-navchain-item" id="bx_admin_chain_delimiter_'.$item['ID'].'"><span class="adm-navchain-delimiter"></span></span>';

					$chainScripts .= 'new BX.COpener('.CUtil::PhpToJsObject(array(
							'DIV' => 'bx_admin_chain_delimiter_'.$item['ID'],
							'ACTIVE_CLASS' => 'adm-navchain-item-active',
							'MENU_URL' => $openerUrl
						)).');';
				}
				else
				{
					echo '<span class="adm-navchain-delimiter"></span>';
				}

			}

			$last_item = $item;
		}
?>
</div>
<?
		if ($chainScripts != '')
		{
?>
<script type="text/javascript"><?=$chainScripts?></script>
<?
		}

		return $last_item;
	}
}

class CAdminMainChain extends CAdminChain
{
	var $bInit = false;

	public function __construct($id=false, $bVisible=true)
	{
		parent::__construct($id, $bVisible);
	}

	function Init()
	{
		/** @global CAdminPage $adminPage */
		global $adminPage;
		/** @global CAdminMenu $adminMenu */
		global $adminMenu;

		if($this->bInit)
			return;
		$this->bInit = true;
		$adminPage->Init();
		$adminMenu->Init($adminPage->aModules);

		parent::AddItem(array("TEXT"=> GetMessage("admin_lib_navchain_first"), "LINK"=>"/bitrix/admin/index.php?lang=".LANGUAGE_ID, "CLASS" => "adm-navchain-item-desktop"));

		foreach($adminMenu->aActiveSections as $sect)
		{
			if($sect["skip_chain"] !== true)
				parent::AddItem(array("TEXT"=>$sect["text"], "LINK"=>$sect["url"], "ID" => $sect['items_id']));
		}
	}

	public function AddItem($item)
	{
		$this->Init();
		parent::AddItem($item);
	}
}

class CAdminTheme
{
	public static function GetList()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $MESS;

		$aThemes = array();
		$dir = $_SERVER["DOCUMENT_ROOT"].ADMIN_THEMES_PATH;
		if(is_dir($dir) && ($dh = opendir($dir)))
		{
			while (($file = readdir($dh)) !== false)
			{
				if(is_dir($dir."/".$file) && $file!="." && $file!="..")
				{
					$path = ADMIN_THEMES_PATH."/".$file;

					$sLangFile = $_SERVER["DOCUMENT_ROOT"].$path."/lang/".LANGUAGE_ID."/.description.php";
					if(file_exists($sLangFile))
						include($sLangFile);
					else
					{
						$sLangFile = $_SERVER["DOCUMENT_ROOT"].$path."/lang/en/.description.php";
						if(file_exists($sLangFile))
							include($sLangFile);
					}

					$aTheme = array();
					$sDescFile = $_SERVER["DOCUMENT_ROOT"].$path."/.description.php";
					if(file_exists($sDescFile))
						$aTheme = include($sDescFile);
					$aTheme["ID"] = $file;
					if($aTheme["NAME"] == "")
						$aTheme["NAME"] = $file;

					$aThemes[] = $aTheme;
				}
			}
			closedir($dh);
		}

		usort(
			$aThemes,
			function ($a, $b) {
				return strcasecmp($a["ID"], $b["ID"]);
			}
		);

		return $aThemes;
	}

	public static function GetCurrentTheme()
	{
		$aUserOpt = CUserOptions::GetOption("global", "settings");
		if (is_array($aUserOpt) && ($aUserOpt["theme_id"] ?? '') <> '')
		{
			$theme = preg_replace("/[^a-z0-9_.-]/i", "", $aUserOpt["theme_id"]);
			if($theme <> "")
			{
				return $theme;
			}
		}

		return ".default";
	}
}

class CAdminUtil
{
	public static function dumpVars($vars, $arExclusions = array())
	{
		$result = "";
		if (is_array($vars))
		{
			foreach ($vars as $varName => $varValue)
			{
				if (in_array($varName, $arExclusions))
					continue;

				$result .= self::dumpVar($varName, $varValue);
			}
		}

		return $result;
	}

	private static function dumpVar($varName, $varValue, $varStack = array())
	{
		$result = "";
		if (is_array($varValue))
		{
			foreach ($varValue as $key => $value)
			{
				$result .= self::dumpVar($key, $value, array_merge($varStack ,array($varName)));
			}
		}
		else
		{
			$htmlName = $varName;
			if (count($varStack) > 0)
			{
				$htmlName = $varStack[0];
				for ($i = 1, $intCount = count($varStack); $i < $intCount; $i++)
					$htmlName .= "[".$varStack[$i]."]";
				$htmlName .= "[".$varName."]";
			}

			return '<input type="hidden" name="'.htmlspecialcharsbx($htmlName).'" value="'.htmlspecialcharsbx($varValue).'">';
		}

		return $result;
	}
}

function ShowJSHint($text, $arParams=false)
{
	if ($text == '')
	{
		return '';
	}

	CJSCore::Init();

	$id = "h".mt_rand();

	$res = '
		<script type="text/javascript">BX.ready(function(){BX.hint_replace(BX("'.$id.'"), "'.CUtil::JSEscape($text).'");})</script>
		<span id="'.$id.'"></span>
	';

	if (isset($arParams['return']) && $arParams['return'])
	{
		return $res;
	}
	echo $res;
	return null;
}

