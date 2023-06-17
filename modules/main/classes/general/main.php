<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Composite;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Page\AssetMode;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Authentication\Internal\ModuleGroupTable;

IncludeModuleLangFile(__FILE__);

/**
 * @deprecated
 */
abstract class CAllMain
{
	var $ma;
	var $sDocPath2, $sDirPath, $sUriParam;
	var $sDocTitle;
	var $sDocTitleChanger = null;
	var $arPageProperties = array();
	var $arPagePropertiesChanger = array();
	var $arDirProperties = array();

	/** @var Asset */
	public $oAsset;

	/**
	 * Array of css, js, and inline strings
	 */
	var $sPath2css = array();
	var $arHeadStrings = array();
	var $arHeadScripts = array();

	/**
	 * Additional css, js and inline strings. Need to include in specifik place.
	 */
	var $arHeadAdditionalCSS = array();
	var $arHeadAdditionalScripts = array();
	var $arHeadAdditionalStrings = array();
	var $bInAjax = false;

	var $version;
	var $arAdditionalChain = array();
	var $FILE_PERMISSION_CACHE = array();
	var $arPanelButtons = array();
	var $arPanelFutureButtons = array();

	var $ShowPanel = null;
	var $PanelShowed = false;
	var $showPanelWasInvoked = false;

	var $buffer_content = array();
	var $buffer_content_type = array();
	var $buffer_man = false;
	var $buffer_manual = false;
	var $auto_buffer_cleaned, $buffered = false;
	/**
	 * @var CApplicationException
	 */
	var $LAST_ERROR = false;
	var $ERROR_STACK = array();
	var $arIncludeDebug = array();
	var $aCachedComponents = array();
	var $ShowIncludeStat = false;
	var $_menu_recalc_counter = 0;
	var $__view = array();
	/** @var CEditArea */
	var $editArea = false;
	/** @var array */
	var $arComponentMatch = false;
	var $arAuthResult;
	private $__componentStack = array();

	public function __construct()
	{
		global $QUERY_STRING;
		$this->sDocPath2 = GetPagePath(false, true);
		$this->sDirPath = GetDirPath($this->sDocPath2);
		$this->sUriParam = ($_SERVER["QUERY_STRING"] <> '') ? $_SERVER["QUERY_STRING"] : $QUERY_STRING;

		$this->oAsset = Asset::getInstance();
	}

	/**
	 * @deprecated Does nothing.
	 */
	public function reinitPath()
	{
	}

	public function GetCurPage($get_index_page=null)
	{
		if (null === $get_index_page)
		{
			if (defined('BX_DISABLE_INDEX_PAGE'))
				$get_index_page = !BX_DISABLE_INDEX_PAGE;
			else
				$get_index_page = true;
		}

		$str = $this->sDocPath2;

		if (!$get_index_page)
		{
			if (($i = mb_strpos($str, '/index.php')) !== false)
				$str = mb_substr($str, 0, $i).'/';
		}

		return $str;
	}

	public function SetCurPage($page, $param=false)
	{
		$this->sDocPath2 = GetPagePath($page);
		$this->sDirPath = GetDirPath($this->sDocPath2);
		if($param !== false)
			$this->sUriParam = $param;
	}

	public function GetCurUri($addParam="", $get_index_page=null)
	{
		$page = $this->GetCurPage($get_index_page);
		$param = $this->GetCurParam();
		if($param <> '')
			$url = $page."?".$param.($addParam!=""? "&".$addParam: "");
		else
			$url = $page.($addParam!=""? "?".$addParam: "");
		return $url;
	}

	public function GetCurPageParam($strParam="", $arParamKill=array(), $get_index_page=null)
	{
		$sUrlPath = $this->GetCurPage($get_index_page);

		$strNavQueryString = DeleteParam($arParamKill);
		if($strNavQueryString <> "" && $strParam <> "")
			$strNavQueryString = "&".$strNavQueryString;
		if($strNavQueryString == "" && $strParam == "")
			return $sUrlPath;
		else
			return $sUrlPath."?".$strParam.$strNavQueryString;
	}

	public function GetCurParam()
	{
		return $this->sUriParam;
	}

	public function GetCurDir()
	{
		return $this->sDirPath;
	}

	public function GetFileRecursive($strFileName, $strDir=false)
	{
		if($strDir === false)
			$strDir = $this->GetCurDir();

		$io = CBXVirtualIo::GetInstance();
		$fn = $io->CombinePath("/", $strDir, $strFileName);

		$p = null;
		while(!$io->FileExists($io->RelativeToAbsolutePath($fn)))
		{
			$p = bxstrrpos($strDir, "/");
			if($p === false)
				break;
			$strDir = mb_substr($strDir, 0, $p);
			$fn = $io->CombinePath("/", $strDir, $strFileName);
		}
		if($p === false)
			return false;

		return $fn;
	}

	public function IncludeAdminFile($strTitle, $filepath)
	{
		//define all global vars
		$keys = array_keys($GLOBALS);
		$keys_count = count($keys);
		for($i=0; $i<$keys_count; $i++)
			if($keys[$i]!="i" && $keys[$i]!="GLOBALS" && $keys[$i]!="strTitle" && $keys[$i]!="filepath")
				global ${$keys[$i]};

		//title
		$this->SetTitle($strTitle);

		include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
		include($filepath);
		include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
		die();
	}

	public function SetAuthResult($arAuthResult)
	{
		$this->arAuthResult = $arAuthResult;
	}

	public function AuthForm($mess, $show_prolog=true, $show_epilog=true, $not_show_links="N", $do_die=true)
	{
		$excl = array("excl"=>1, "key"=>1, "GLOBALS"=>1, "mess"=>1, "show_prolog"=>1, "show_epilog"=>1, "not_show_links"=>1, "do_die"=>1);
		foreach($GLOBALS as $key => $value)
			if(!array_key_exists($key , $excl))
				global ${$key};

		if(mb_substr($this->GetCurDir(), 0, mb_strlen(BX_ROOT."/admin/")) == BX_ROOT."/admin/" || (defined("ADMIN_SECTION") && ADMIN_SECTION===true))
			$isAdmin = "_admin";
		else
			$isAdmin = "";

		if(isset($this->arAuthResult) && $this->arAuthResult !== true && (is_array($this->arAuthResult) || $this->arAuthResult <> ''))
			$arAuthResult = $this->arAuthResult;
		else
			$arAuthResult = $mess;

		/** @global CMain $APPLICATION */
		global $APPLICATION, $forgot_password, $change_password, $register, $confirm_registration;

		//page title
		$APPLICATION->SetTitle(GetMessage("AUTH_TITLE"));

		if (
			is_array($arAuthResult)
			&& isset($arAuthResult["TYPE"])
			&& isset($arAuthResult["ERROR_TYPE"])
			&& $arAuthResult["TYPE"] === "ERROR"
			&& $arAuthResult["ERROR_TYPE"] === "CHANGE_PASSWORD"
		)
		{
			//require to change the password after N days
			$change_password = "yes";
		}

		$inc_file = "";
		if($forgot_password=="yes")
		{
			//pass request form
			$APPLICATION->SetTitle(GetMessage("AUTH_TITLE_SEND_PASSWORD"));
			$comp_name = "system.auth.forgotpasswd";
			$inc_file = "forgot_password";
		}
		elseif($change_password=="yes")
		{
			//pass change form
			$APPLICATION->SetTitle(GetMessage("AUTH_TITLE_CHANGE_PASSWORD"));
			$comp_name = "system.auth.changepasswd";
			$inc_file = "change_password";
		}
		elseif($register=="yes" && $isAdmin==""	&& COption::GetOptionString("main", "new_user_registration", "N")=="Y")
		{
			//registration form
			$APPLICATION->SetTitle(GetMessage("AUTH_TITLE_REGISTER"));
			$comp_name = "system.auth.registration";
		}
		elseif(($confirm_registration === "yes") && ($isAdmin === "") && (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") === "Y"))
		{
			//confirm registartion
			$APPLICATION->SetTitle(GetMessage("AUTH_TITLE_CONFIRM"));
			$comp_name = "system.auth.confirmation";
		}
		elseif(
			CModule::IncludeModule("security")
			&& \Bitrix\Security\Mfa\Otp::isOtpRequired()
			&& (!isset($_REQUEST["login_form"]) || $_REQUEST["login_form"] !== "yes")
		)
		{
			//otp form
			$APPLICATION->SetTitle(GetMessage("AUTH_TITLE_OTP"));
			$comp_name = "system.auth.otp";
			$inc_file = "otp";
		}
		else
		{
			header('X-Bitrix-Ajax-Status: Authorize');

			//auth form
			$comp_name = "system.auth.authorize";
			$inc_file = "authorize";
		}

		if($show_prolog)
		{
			CMain::PrologActions();

			define("BX_AUTH_FORM", true);
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog".$isAdmin. "_after.php");
		}

		if($isAdmin == "")
		{
			// form by Components 2.0
			$this->IncludeComponent(
				"bitrix:".$comp_name,
				COption::GetOptionString("main", "auth_components_template", ""),
				array(
					"AUTH_RESULT" => $arAuthResult,
					"NOT_SHOW_LINKS" => $not_show_links,
				)
			);
		}
		else
		{
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/auth/wrapper.php");
		}

		$autoCompositeArea = Main\Composite\Internals\AutomaticArea::getCurrentArea();
		if ($autoCompositeArea)
		{
			$autoCompositeArea->end();
		}

		if($show_epilog)
		{
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog".$isAdmin.".php");
		}

		if($do_die)
		{
			die();
		}
	}

	public function ShowAuthForm($message)
	{
		$this->AuthForm($message, false, false, "N", false);
	}

	/**
	 * @param bool $mode
	 */
	public function SetNeedCAPTHA($mode)
	{
		$kernelSession = Main\Application::getInstance()->getKernelSession();
		$kernelSession["BX_LOGIN_NEED_CAPTCHA"] = (bool)$mode;
	}

	/**
	 * @return bool
	 */
	public function NeedCAPTHA()
	{
		$kernelSession = Main\Application::getInstance()->getKernelSession();
		return (isset($kernelSession["BX_LOGIN_NEED_CAPTCHA"]) && $kernelSession["BX_LOGIN_NEED_CAPTCHA"]);
	}

	public function NeedCAPTHAForLogin($login)
	{
		//When last login was failed then ask for CAPTCHA
		if($this->NeedCAPTHA())
		{
			return true;
		}

		//This is local cache. May save one query.
		$USER_ATTEMPTS = false;

		$session = Main\Application::getInstance()->getSession();

		//Check if SESSION cache for POLICY_ATTEMPTS is actual for given login
		if(!$session->has("BX_LOGIN_NEED_CAPTCHA_LOGIN") || $session["BX_LOGIN_NEED_CAPTCHA_LOGIN"]["LOGIN"] !== $login)
		{
			$POLICY_ATTEMPTS = 0;
			if($login <> '')
			{
				$rsUser = CUser::GetList('LOGIN', 'DESC', array(
					"LOGIN_EQUAL_EXACT" => $login,
					"EXTERNAL_AUTH_ID" => "",
				),
					array('FIELDS' => array('ID', 'LOGIN', 'LOGIN_ATTEMPTS'))
				);
				$arUser = $rsUser->Fetch();
				if($arUser)
				{
					$policy = CUser::getPolicy($arUser["ID"]);
					$POLICY_ATTEMPTS = (int)$policy->getLoginAttempts();
					$USER_ATTEMPTS = (int)$arUser["LOGIN_ATTEMPTS"];
				}
			}
			$session["BX_LOGIN_NEED_CAPTCHA_LOGIN"] = [
				"LOGIN" => $login,
				"POLICY_ATTEMPTS" => $POLICY_ATTEMPTS,
			];
		}

		//For users who had successful login and if policy is set
		//check for CAPTCHA display
		if($login <> '' && $session["BX_LOGIN_NEED_CAPTCHA_LOGIN"]["POLICY_ATTEMPTS"] > 0)
		{
			//We need to know how many attempts user made
			if($USER_ATTEMPTS === false)
			{
				$rsUser = CUser::GetList('LOGIN', 'DESC', array(
					"LOGIN_EQUAL_EXACT" => $login,
					"EXTERNAL_AUTH_ID" => "",
				),
					array('FIELDS' => array('ID', 'LOGIN', 'LOGIN_ATTEMPTS'))
				);
				$arUser = $rsUser->Fetch();
				if($arUser)
					$USER_ATTEMPTS = intval($arUser["LOGIN_ATTEMPTS"]);
				else
					$USER_ATTEMPTS = 0;
			}
			//When user login attempts exceeding the policy we'll show the CAPTCHA
			if($USER_ATTEMPTS >= $session["BX_LOGIN_NEED_CAPTCHA_LOGIN"]["POLICY_ATTEMPTS"])
				return true;
		}

		return false;
	}

	public function GetMenuHtml($type="left", $bMenuExt=false, $template = false, $sInitDir = false)
	{
		$menu = $this->GetMenu($type, $bMenuExt, $template, $sInitDir);
		return $menu->GetMenuHtml();
	}

	public function GetMenuHtmlEx($type="left", $bMenuExt=false, $template = false, $sInitDir = false)
	{
		$menu = $this->GetMenu($type, $bMenuExt, $template, $sInitDir);
		return $menu->GetMenuHtmlEx();
	}

	public function GetMenu($type="left", $bMenuExt=false, $template = false, $sInitDir = false)
	{
		$menu = new CMenu($type);
		if($sInitDir===false)
			$sInitDir = $this->GetCurDir();
		if(!$menu->Init($sInitDir, $bMenuExt, $template))
			$menu->MenuDir = $sInitDir;
		return $menu;
	}

	/**
	 * @deprecated Use HttpRequest::isHttps()
	 * @return bool
	 */
	public static function IsHTTPS()
	{
		return Main\Context::getCurrent()->getRequest()->isHttps();
	}

	public function GetTitle($property_name = false, $strip_tags = false)
	{
		if($property_name!==false && $this->GetProperty($property_name) <> '')
			$res = $this->GetProperty($property_name);
		else
			$res = $this->sDocTitle;
		if($strip_tags && is_string($res))
			return strip_tags($res);
		return $res;
	}

	public function SetTitle($title, $arOptions = null)
	{
		$this->sDocTitle = $title;

		if (is_array($arOptions))
		{
			$this->sDocTitleChanger = $arOptions;
		}
		else
		{
			$arTrace = array_reverse(Main\Diag\Helper::getBackTrace(0, DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS));

			foreach ($arTrace as $arTraceRes)
			{
				if (isset($arTraceRes['class']) && isset($arTraceRes['function']))
				{
					if (strtoupper($arTraceRes['class']) == 'CBITRIXCOMPONENT' && strtoupper($arTraceRes['function']) == 'INCLUDECOMPONENT' && is_object($arTraceRes['object']))
					{
						/** @var CBitrixComponent $comp */
						$comp = $arTraceRes['object'];
						$this->sDocTitleChanger = array(
							'COMPONENT_NAME' => $comp->GetName(),
						);

						break;
					}
				}
			}
		}
	}

	public function ShowTitle($property_name="title", $strip_tags = true)
	{
		$this->AddBufferContent(array(&$this, "GetTitle"), $property_name, $strip_tags);
	}

	public function SetPageProperty($PROPERTY_ID, $PROPERTY_VALUE, $arOptions = null)
	{
		$this->arPageProperties[mb_strtoupper($PROPERTY_ID)] = $PROPERTY_VALUE;

		if (is_array($arOptions))
			$this->arPagePropertiesChanger[mb_strtoupper($PROPERTY_ID)] = $arOptions;
	}

	public function GetPageProperty($PROPERTY_ID, $default_value = false)
	{
		if(isset($this->arPageProperties[mb_strtoupper($PROPERTY_ID)]))
			return $this->arPageProperties[mb_strtoupper($PROPERTY_ID)];
		return $default_value;
	}

	public function ShowProperty($PROPERTY_ID, $default_value = false)
	{
		$this->AddBufferContent(array(&$this, "GetProperty"), $PROPERTY_ID, $default_value);
	}

	public function GetProperty($PROPERTY_ID, $default_value = false)
	{
		$propVal = $this->GetPageProperty($PROPERTY_ID);
		if($propVal !== false)
			return $propVal;

		$propVal = $this->GetDirProperty($PROPERTY_ID);
		if($propVal !== false)
			return $propVal;

		return $default_value;
	}

	public function GetPagePropertyList()
	{
		return $this->arPageProperties;
	}

	public static function InitPathVars(&$site, &$path)
	{
		$site = false;
		if(is_array($path))
		{
			$site = $path[0];
			$path = $path[1];
		}
		return $path;
	}

	public function SetDirProperty($PROPERTY_ID, $PROPERTY_VALUE, $path=false)
	{
		self::InitPathVars($site, $path);

		if($path === false)
			$path = $this->GetCurDir();
		if($site === false)
			$site = SITE_ID;

		if(!isset($this->arDirProperties[$site][$path]))
			$this->InitDirProperties(array($site, $path));

		$this->arDirProperties[$site][$path][mb_strtoupper($PROPERTY_ID)] = $PROPERTY_VALUE;
	}

	public function InitDirProperties($path)
	{
		self::InitPathVars($site, $path);

		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		if($path === false)
			$path = $this->GetCurDir();
		if($site === false)
			$site = SITE_ID;

		if(isset($this->arDirProperties[$site][$path]))
			return true;

		$io = CBXVirtualIo::GetInstance();

		$dir = $path;
		while (true) // until the root
		{
			$dir = rtrim($dir, "/");
			$section_file_name = $DOC_ROOT.$dir."/.section.php";

			if($io->FileExists($section_file_name))
			{
				$arDirProperties = false;
				include($io->GetPhysicalName($section_file_name));
				if(is_array($arDirProperties))
				{
					foreach($arDirProperties as $prid=>$prval)
					{
						$prid = mb_strtoupper($prid);
						if(!isset($this->arDirProperties[$site][$path][$prid]))
							$this->arDirProperties[$site][$path][$prid] = $prval;
					}
				}
			}

			if($dir == '')
				break;

			// file or folder
			$pos = bxstrrpos($dir, "/");
			if($pos===false)
				break;

			//parent folder
			$dir = mb_substr($dir, 0, $pos + 1);
		}

		return true;
	}

	public function GetDirProperty($PROPERTY_ID, $path=false, $default_value = false)
	{
		self::InitPathVars($site, $path);

		if($path === false)
			$path = $this->GetCurDir();
		if($site === false)
			$site = SITE_ID;

		if(!isset($this->arDirProperties[$site][$path]))
			$this->InitDirProperties(array($site, $path));

		$prop_id = mb_strtoupper($PROPERTY_ID);
		if(isset($this->arDirProperties[$site][$path][$prop_id]))
			return $this->arDirProperties[$site][$path][$prop_id];

		return $default_value;
	}

	public function GetDirPropertyList($path=false)
	{
		self::InitPathVars($site, $path);

		if($path === false)
			$path = $this->GetCurDir();
		if($site === false)
			$site = SITE_ID;

		if(!isset($this->arDirProperties[$site][$path]))
			$this->InitDirProperties(array($site, $path));

		if(is_array($this->arDirProperties[$site][$path]))
			return $this->arDirProperties[$site][$path];

		return false;
	}

	public function GetMeta($id, $meta_name=false, $bXhtmlStyle=true)
	{
		if($meta_name==false)
			$meta_name=$id;
		$val = $this->GetProperty($id);
		if(!empty($val))
			return '<meta name="'.htmlspecialcharsbx($meta_name).'" content="'.htmlspecialcharsEx($val).'"'.($bXhtmlStyle? ' /':'').'>'."\n";
		return '';
	}

	public function GetLink($id, $rel = null, $bXhtmlStyle = true)
	{
		if($rel === null)
		{
			$rel = $id;
		}
		$href = $this->GetProperty($id);
		if($href <> '')
		{
			return '<link rel="'.$rel.'" href="'.$href.'"'.($bXhtmlStyle? ' /':'').'>'."\n";
		}
		return '';
	}

	public static function ShowBanner($type, $html_before="", $html_after="")
	{
		if(!CModule::IncludeModule("advertising"))
			return;

		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array("CAdvBanner", "Show"), $type, $html_before, $html_after);
	}

	public function ShowMeta($id, $meta_name=false, $bXhtmlStyle=true)
	{
		$this->AddBufferContent(array(&$this, "GetMeta"), $id, $meta_name, $bXhtmlStyle);
	}

	public function ShowLink($id, $rel = null, $bXhtmlStyle = true)
	{
		$this->AddBufferContent(array(&$this, "GetLink"), $id, $rel, $bXhtmlStyle);
	}

	public function SetAdditionalCSS($Path2css, $additional=false)
	{
		$this->oAsset->addCss($Path2css, $additional);

		if($additional)
			$this->arHeadAdditionalCSS[] = $this->oAsset->getAssetPath($Path2css);
		else
			$this->sPath2css[] = $this->oAsset->getAssetPath($Path2css);
	}

	/** @deprecated */
	public function GetAdditionalCSS()
	{
		$n = count($this->sPath2css);
		if($n > 0)
		{
			return $this->sPath2css[$n-1];
		}
		return false;
	}

	public function GetCSSArray()
	{
		return array_unique($this->sPath2css);
	}

	/** @deprecated use Asset::getInstance()->getCss() */
	public function GetCSS($cMaxStylesCnt = true, $bXhtmlStyle = true, $assetTargetType = Main\Page\AssetShowTargetType::ALL)
	{
		if($cMaxStylesCnt === true)
		{
			$cMaxStylesCnt = Main\Config\Option::get('main', 'max_css_files', 20);
		}
		$this->oAsset->setMaxCss($cMaxStylesCnt);
		$this->oAsset->setXhtml($bXhtmlStyle);
		$res = $this->oAsset->getCss($assetTargetType);
		return $res;
	}

	public function ShowCSS($cMaxStylesCnt = true, $bXhtmlStyle = true)
	{
		$this->AddBufferContent(array(&$this, "GetHeadStrings"), 'BEFORE_CSS');
		$this->AddBufferContent(array(&$this, "GetCSS"), $cMaxStylesCnt, $bXhtmlStyle);
	}

	/** @deprecated $Asset::getInstance->addString($str, $bUnique, $location); */
	public function AddHeadString($str, $bUnique = false, $location = AssetLocation::AFTER_JS_KERNEL)
	{
		$location = Asset::getLocationByName($location);
		$this->oAsset->addString($str, $bUnique, $location);
	}

	public function GetHeadStrings($location = AssetLocation::AFTER_JS_KERNEL)
	{
		$location = Asset::getLocationByName($location);
		if($location === AssetLocation::AFTER_JS_KERNEL)
		{
			$res = $this->oAsset->getJs(1);
		}
		else
		{
			$res = $this->oAsset->getStrings($location);
		}

		return ($res == '' ? '' : $res."\n");
	}

	public function ShowHeadStrings()
	{
		if(!$this->oAsset->getShowHeadString())
		{
			$this->oAsset->setShowHeadString();
			$this->AddBufferContent(array(&$this, "GetHeadStrings"), 'DEFAULT');
		}
	}

	/** @deprecated use Asset::getInstance()->addJs($src, $additional) */
	public function AddHeadScript($src, $additional=false)
	{
		$this->oAsset->addJs($src, $additional);

		if($src <> '')
		{
			if($additional)
			{
				$this->arHeadAdditionalScripts[] = Asset::getAssetPath($src);
			}
			else
			{
				$this->arHeadScripts[] = Asset::getAssetPath($src);
			}
		}
	}

	/** @deprecated use Asset::getInstance()->addBeforeJs($content) */
	public function AddLangJS($content)
	{
		$this->oAsset->addString($content, true, 'AFTER_CSS');
	}

	/** @deprecated use Asset::getInstance()->addString($content, false, \Bitrix\Main\Page\AssetLocation::AFTER_JS, $mode) */
	public function AddAdditionalJS($content)
	{
		$this->oAsset->addString($content, false, AssetLocation::AFTER_JS);
	}

	public static function IsExternalLink($src)
	{
		return (strncasecmp($src, 'http://', 7) == 0 || strncasecmp($src, 'https://', 8) == 0 || strncmp($src, '//', 2) == 0);
	}

	/** @deprecated deprecated use Asset::addCssKernelInfo() */
	public function AddCSSKernelInfo($module = '', $arCSS = array())
	{
		$this->oAsset->addCssKernelInfo($module, $arCSS);
	}

	/** @deprecated deprecated use Asset::addJsKernelInfo() */
	public function AddJSKernelInfo($module = '', $arJS = array())
	{
		$this->oAsset->addJsKernelInfo($module, $arJS);
	}

	/** @deprecated use Asset::getInstance()->groupJs($from, $to) */
	public function GroupModuleJS($from = '', $to = '')
	{
		$this->oAsset->groupJs($from, $to);
	}

	/** @deprecated use Asset::getInstance()->moveJs($module) */
	public function MoveJSToBody($module = '')
	{
		$this->oAsset->moveJs($module);
	}

	/** @deprecated use Asset::getInstance()->groupCss($from, $to) */
	public function GroupModuleCSS($from = '', $to = '')
	{
		$this->oAsset->groupCss($from, $to);
	}

	/** @deprecated use Asset::getInstance()->setUnique($type, $id) */
	public function SetUniqueCSS($id = '', $cssType = 'page')
	{
		$cssType = (($cssType == 'page') ? 'PAGE' : 'TEMPLATE');
		$this->oAsset->setUnique($cssType, $id);
		return true;
	}

	/** @deprecated */
	public function SetUniqueJS($id = '', $jsType = 'page')
	{
		return true;
	}

	/** @deprecated use Asset::getInstance()->getJs($type) */
	public function GetHeadScripts($type = 0)
	{
		return $this->oAsset->getJs($type);
	}

	public function ShowHeadScripts()
	{
		$this->oAsset->setShowHeadScript();
		$this->AddBufferContent(array(&$this, "GetHeadScripts"), 2);
	}

	public function ShowBodyScripts()
	{
		$this->oAsset->setShowBodyScript();
		$this->AddBufferContent(array(&$this, "GetHeadScripts"), 3);
	}

	public function ShowHead($bXhtmlStyle=true)
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'"'.($bXhtmlStyle? ' /':'').'>'."\n";
		$this->ShowMeta("robots", false, $bXhtmlStyle);
		$this->ShowMeta("keywords", false, $bXhtmlStyle);
		$this->ShowMeta("description", false, $bXhtmlStyle);
		$this->ShowLink("canonical", null, $bXhtmlStyle);
		$this->ShowCSS(true, $bXhtmlStyle);
		$this->ShowHeadStrings();
		$this->ShowHeadScripts();
	}

	public function ShowAjaxHead($bXhtmlStyle = true, $showCSS = true, $showStrings = true, $showScripts = true)
	{
		$this->RestartBuffer();
		$this->sPath2css = array();
		$this->arHeadAdditionalCSS = array();
		$this->arHeadAdditionalStrings = array();
		$this->arHeadAdditionalScripts = array();
		$this->arHeadScripts = array();
		$this->arHeadStrings = array();
		$this->bInAjax = true;

		$this->oAsset = $this->oAsset->setAjax();

		if($showCSS === true)
		{
			$this->ShowCSS(true, $bXhtmlStyle);
		}

		if($showStrings === true)
		{
			$this->ShowHeadStrings();
		}

		if($showScripts === true)
		{
			$this->ShowHeadScripts();
		}
	}

	public function SetShowIncludeAreas($bShow=true)
	{
		Main\Application::getInstance()->getKernelSession()["SESS_INCLUDE_AREAS"] = $bShow;
	}

	public function GetShowIncludeAreas()
	{
		global $USER;

		if(!is_object($USER) || !$USER->IsAuthorized() || defined('ADMIN_SECTION') && ADMIN_SECTION == true)
			return false;
		$kernelSession = Main\Application::getInstance()->getKernelSession();
		if(isset($kernelSession["SESS_INCLUDE_AREAS"]) && $kernelSession["SESS_INCLUDE_AREAS"])
			return true;
		static $panel_dynamic_mode = null;
		if (!isset($panel_dynamic_mode))
		{
			$aUserOpt = CUserOptions::GetOption("global", "settings", array());
			$panel_dynamic_mode = (isset($aUserOpt["panel_dynamic_mode"]) && $aUserOpt["panel_dynamic_mode"] == "Y");
		}
		return $panel_dynamic_mode;
	}

	public function SetPublicShowMode($mode)
	{
		$this->SetShowIncludeAreas($mode != 'view');
	}

	public function GetPublicShowMode()
	{
		return $this->GetShowIncludeAreas() ? 'configure' : 'view';
	}

	public function SetEditArea($areaId, $arIcons)
	{
		if(!$this->GetShowIncludeAreas())
			return;

		if($this->editArea === false)
			$this->editArea = new CEditArea();

		$this->editArea->SetEditArea($areaId, $arIcons);
	}

	public function IncludeStringBefore()
	{
		if($this->editArea === false)
			$this->editArea = new CEditArea();
		return $this->editArea->IncludeStringBefore();
	}

	public function IncludeStringAfter($arIcons=false, $arParams=array())
	{
		return $this->editArea->IncludeStringAfter($arIcons, $arParams);
	}

	public function IncludeString($string, $arIcons=false)
	{
		return $this->IncludeStringBefore().$string.$this->IncludeStringAfter($arIcons);
	}

	public function GetTemplatePath($rel_path)
	{
		if(mb_substr($rel_path, 0, 1) != "/")
		{
			if(defined("SITE_TEMPLATE_ID"))
			{
				$path = getLocalPath("templates/".SITE_TEMPLATE_ID."/".$rel_path, BX_PERSONAL_ROOT);
				if($path !== false)
					return $path;
			}

			$path = getLocalPath("templates/.default/".$rel_path, BX_PERSONAL_ROOT);
			if($path !== false)
				return $path;

			//we don't use /local folder for components 1.0
			$module_id = mb_substr($rel_path, 0, mb_strpos($rel_path, "/"));
			if($module_id <> '')
			{
				$path = "/bitrix/modules/".$module_id."/install/templates/".$rel_path;
				if(file_exists($_SERVER["DOCUMENT_ROOT"].$path))
					return $path;
			}

			return false;
		}

		return $rel_path;
	}

	public function SetTemplateCSS($rel_path)
	{
		if($path = $this->GetTemplatePath($rel_path))
			$this->SetAdditionalCSS($path);
	}

	// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	// COMPONENTS 2.0 >>>>>
	public function IncludeComponent($componentName, $componentTemplate, $arParams = array(), $parentComponent = null, $arFunctionParams = array(), $returnResult = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;

		if(is_array($this->arComponentMatch))
		{
			$skipComponent = true;
			foreach($this->arComponentMatch as $cValue)
			{
				if(mb_strpos($componentName, $cValue) !== false)
				{
					$skipComponent = false;
					break;
				}
			}
			if($skipComponent)
				return false;
		}

		$componentRelativePath = CComponentEngine::MakeComponentPath($componentName);
		if ($componentRelativePath == '')
			return false;

		$debug = null;
		$bShowDebug = Main\Application::getInstance()->getKernelSession()["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y"
			&& (
				$USER->CanDoOperation('edit_php')
				|| Main\Application::getInstance()->getKernelSession()["SHOW_SQL_STAT"]=="Y"
			)
			&& !defined("PUBLIC_AJAX_MODE")
		;
		if($bShowDebug || $APPLICATION->ShowIncludeStat)
		{
			$debug = new CDebugInfo();
			$debug->Start($componentName);
		}

		if (is_object($parentComponent))
		{
			if (!($parentComponent instanceof cbitrixcomponent))
				$parentComponent = null;
		}

		$bDrawIcons = ((!isset($arFunctionParams["HIDE_ICONS"]) || $arFunctionParams["HIDE_ICONS"] <> "Y") && $APPLICATION->GetShowIncludeAreas());

		if($bDrawIcons)
			echo $this->IncludeStringBefore();

		$result = null;
		$bComponentEnabled = (!isset($arFunctionParams["ACTIVE_COMPONENT"]) || $arFunctionParams["ACTIVE_COMPONENT"] <> "N");

		$component = new CBitrixComponent();
		if($component->InitComponent($componentName))
		{
			$obAjax = null;
			if($bComponentEnabled)
			{
				if(($arParams['AJAX_MODE'] ?? '') == 'Y')
					$obAjax = new CComponentAjax($componentName, $componentTemplate, $arParams, $parentComponent);

				$this->__componentStack[] = $component;
				$result = $component->IncludeComponent($componentTemplate, $arParams, $parentComponent, $returnResult);

				array_pop($this->__componentStack);
			}

			if($bDrawIcons)
			{
				$panel = new CComponentPanel($component, $componentName, $componentTemplate, $parentComponent, $bComponentEnabled);
				$arIcons = $panel->GetIcons();

				echo $s = $this->IncludeStringAfter($arIcons["icons"], $arIcons["parameters"]);
			}

			if($bComponentEnabled && $obAjax)
			{
				$obAjax->Process();
			}
		}

		if($bShowDebug)
			echo $debug->Output($componentName, "/bitrix/components".$componentRelativePath."/component.php", ($arParams["CACHE_TYPE"] ?? '') . ($arParams["MENU_CACHE_TYPE"] ?? ''));
		elseif(isset($debug))
			$debug->Stop($componentName, "/bitrix/components".$componentRelativePath."/component.php", ($arParams["CACHE_TYPE"] ?? '') . ($arParams["MENU_CACHE_TYPE"] ?? ''));


		return $result;
	}

	/**
	 * Returns false or instance of current component being executed.
	 *
	 * @return boolean|CBitrixComponent
	 *
	 */
	public function getCurrentIncludedComponent()
	{
		return end($this->__componentStack);
	}

	/**
	 * Returns a current component stack.
	 * @return array
	 */
	public function getComponentStack()
	{
		return $this->__componentStack;
	}

	/**
	 * Clears vew content area by code.
	 * @param string $view View content code.
	 * @return void
	 */
	public function clearViewContent(string $view): void
	{
		if ($this->__view[$view] ?? null)
		{
			unset($this->__view[$view]);
		}
	}

	public function AddViewContent($view, $content, $pos = 500)
	{
		if (!isset($this->__view[$view]) || !is_array($this->__view[$view]))
		{
			$this->__view[$view] = [[$content, $pos]];
		}
		else
		{
			$this->__view[$view][] = [$content, $pos];
		}
	}

	public function ShowViewContent($view)
	{
		$this->AddBufferContent(array(&$this, "GetViewContent"), $view);
	}

	public function GetViewContent($view)
	{
		if(!isset($this->__view[$view]) || !is_array($this->__view[$view]))
			return '';

		uasort(
			$this->__view[$view],
			function ($a, $b) {
				if ($a[1] == $b[1])
				{
					return 0;
				}
				return ($a[1] < $b[1] ? -1 : 1);
			}
		);

		$res = array();
		foreach($this->__view[$view] as $item)
			$res[] = $item[0];

		return implode($res);
	}

	public static function OnChangeFileComponent($path, $site)
	{
		// kind of optimization
		if(HasScriptExtension($path) && basename($path) !== '.access.php')
		{
			if($site === false)
			{
				$site = SITE_ID;
			}
			$docRoot = CSite::GetSiteDocRoot($site);

			Main\UrlRewriter::delete($site, array("PATH" => $path, "!ID" => ''));
			Main\Component\ParametersTable::deleteByFilter(array("SITE_ID" => $site, "REAL_PATH" => $path));
			Main\UrlRewriter::reindexFile($site, $docRoot, $path);
		}
	}
	// <<<<< COMPONENTS 2.0
	// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

	// $arParams - do not change!
	public function IncludeFile($rel_path, $arParams = array(), $arFunctionParams = array())
	{
		/** @global CMain $APPLICATION */
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $APPLICATION, $USER, $DB, $MESS, $DOCUMENT_ROOT;

		if(Main\Application::getInstance()->getKernelSession()["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y" && ($USER->CanDoOperation('edit_php') || Main\Application::getInstance()->getKernelSession()["SHOW_SQL_STAT"]=="Y"))
		{
			$debug = new CDebugInfo();
			$debug->Start();
		}
		elseif($APPLICATION->ShowIncludeStat)
		{
			$debug = new CDebugInfo();
			$debug->Start();
		}
		else
		{
			$debug = null;
		}

		$sType = "TEMPLATE";
		$bComponent = false;
		if(mb_substr($rel_path, 0, 1) != "/")
		{
			$bComponent = true;
			$path = getLocalPath("templates/".SITE_TEMPLATE_ID."/".$rel_path, BX_PERSONAL_ROOT);
			if($path === false)
			{
				$sType = "DEFAULT";
				$path = getLocalPath("templates/.default/".$rel_path, BX_PERSONAL_ROOT);
				if($path === false)
				{
					$path = BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$rel_path;
					$module_id = mb_substr($rel_path, 0, mb_strpos($rel_path, "/"));
					if($module_id <> '')
					{
						$path = "/bitrix/modules/".$module_id."/install/templates/".$rel_path;
						$sType = "MODULE";
						if(!file_exists($_SERVER["DOCUMENT_ROOT"].$path))
						{
							$sType = "TEMPLATE";
							$path = BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$rel_path;
						}
					}
				}
			}
		}
		else
		{
			$path = $rel_path;
		}

		if($arFunctionParams["WORKFLOW"] && !IsModuleInstalled("workflow"))
			$arFunctionParams["WORKFLOW"] = false;
		elseif($sType!="TEMPLATE" && $arFunctionParams["WORKFLOW"])
			$arFunctionParams["WORKFLOW"] = false;

		$bDrawIcons = (
			$arFunctionParams["SHOW_BORDER"] !== false && $APPLICATION->GetShowIncludeAreas()
			&& (
				$USER->CanDoFileOperation('fm_edit_existent_file', array(SITE_ID, $path))
				|| ($arFunctionParams["WORKFLOW"] && $USER->CanDoFileOperation('fm_edit_in_workflow', array(SITE_ID, $path)))
			)
		);

		$iSrcLine = 0;
		$sSrcFile = '';
		$arIcons = array();

		if($bDrawIcons)
		{
			$path_url = "path=".$path;
			$encSiteTemplateId = urlencode(SITE_TEMPLATE_ID);
			$editor = '';
			$resize = 'false';

			if (!in_array($arFunctionParams['MODE'], array('html', 'text', 'php')))
			{
				$arFunctionParams['MODE'] = $bComponent ? 'php' : 'html';
			}

			if ($sType != 'TEMPLATE')
			{
				switch ($arFunctionParams['MODE'])
				{
					case 'html':
						$editor = "/bitrix/admin/fileman_html_edit.php?site=".SITE_ID."&";
						break;
					case 'text':
						$editor = "/bitrix/admin/fileman_file_edit.php?site=".SITE_ID."&";
						break;
					case 'php':
						$editor = "/bitrix/admin/fileman_file_edit.php?full_src=Y&site=".SITE_ID."&";
						break;
				}
				$editor .= "templateID=".$encSiteTemplateId."&";
			}
			else
			{
				switch ($arFunctionParams['MODE'])
				{
					case 'html':
						$editor = '/bitrix/admin/public_file_edit.php?site='.SITE_ID.'&bxpublic=Y&from=includefile&templateID='.$encSiteTemplateId.'&';
						break;

					case 'text':
						$editor = '/bitrix/admin/public_file_edit.php?site='.SITE_ID.'&bxpublic=Y&from=includefile&noeditor=Y&';
						$resize = 'true';
						break;

					case 'php':
						$editor = '/bitrix/admin/public_file_edit_src.php?site='.SITE_ID.'&templateID='.$encSiteTemplateId.'&';
						$resize = 'true';
						break;
				}
			}

			if($arFunctionParams["TEMPLATE"])
				$arFunctionParams["TEMPLATE"] = "&template=".urlencode($arFunctionParams["TEMPLATE"]);

			if($arFunctionParams["BACK_URL"])
				$arFunctionParams["BACK_URL"] = "&back_url=".urlencode($arFunctionParams["BACK_URL"]);
			else
				$arFunctionParams["BACK_URL"] = "&back_url=".urlencode($_SERVER["REQUEST_URI"]);

			if($arFunctionParams["LANG"])
				$arFunctionParams["LANG"] = "&lang=".urlencode($arFunctionParams["LANG"]);
			else
				$arFunctionParams["LANG"] = "&lang=".LANGUAGE_ID;

			$arPanelParams = array();

			$bDefaultExists = false;
			if($USER->CanDoOperation('edit_php') && $bComponent)
			{
				$bDefaultExists = true;
				$arPanelParams["TOOLTIP"] = array(
					'TITLE' => GetMessage("main_incl_component1"),
					'TEXT' => $rel_path
				);

				$aTrace = Main\Diag\Helper::getBackTrace(1, DEBUG_BACKTRACE_IGNORE_ARGS);

				$sSrcFile = $aTrace[0]["file"];
				$iSrcLine = intval($aTrace[0]["line"]);
				$arIcons[] = array(
					'URL' => 'javascript:'.$APPLICATION->GetPopupLink(array(
							'URL' => "/bitrix/admin/component_props.php?".
								"path=".urlencode(CUtil::addslashes($rel_path)).
								"&template_id=".urlencode(CUtil::addslashes(SITE_TEMPLATE_ID)).
								"&lang=".LANGUAGE_ID.
								"&src_path=".urlencode(CUtil::addslashes($sSrcFile)).
								"&src_line=".$iSrcLine.
								""
						)),
					'ICON'=>"parameters",
					'TITLE'=>GetMessage("main_incl_file_comp_param"),
					'DEFAULT'=>true
				);
			}

			if($sType == "MODULE")
			{
				$arIcons[] = array(
					'URL'=>'javascript:if(confirm(\''.GetMessage("MAIN_INC_BLOCK_MODULE").'\'))window.location=\''.$editor.'&path='.urlencode(BX_PERSONAL_ROOT.'/templates/'.SITE_TEMPLATE_ID.'/'.$rel_path).$arFunctionParams["BACK_URL"].$arFunctionParams["LANG"].'&template='.$path.'\';',
					'ICON'=>'copy',
					'TITLE'=>str_replace("#MODE#", $arFunctionParams["MODE"], str_replace("#BLOCK_TYPE#", (!is_set($arFunctionParams, "NAME")? GetMessage("MAIN__INC_BLOCK"): $arFunctionParams["NAME"]), GetMessage("main_incl_file_edit_copy")))
				);
			}
			elseif($sType == "DEFAULT")
			{
				$arIcons[] = array(
					'URL'=>'javascript:if(confirm(\''.GetMessage("MAIN_INC_BLOCK_COMMON").'\'))window.location=\''.$editor.$path_url.$arFunctionParams["BACK_URL"].$arFunctionParams["LANG"].$arFunctionParams["TEMPLATE"].'\';',
					'ICON'=>'edit-common',
					'TITLE'=>str_replace("#MODE#", $arFunctionParams["MODE"], str_replace("#BLOCK_TYPE#", (!is_set($arFunctionParams, "NAME")? GetMessage("MAIN__INC_BLOCK"): $arFunctionParams["NAME"]), GetMessage("MAIN_INC_BLOCK_EDIT")))
				);

				$arIcons[] = array(
					'URL'=>$editor.'&path='.urlencode(BX_PERSONAL_ROOT.'/templates/'.SITE_TEMPLATE_ID.'/'.$rel_path).$arFunctionParams["BACK_URL"].$arFunctionParams["LANG"].'&template='.$path,
					'ICON'=>'copy',
					'TITLE'=>str_replace("#MODE#", $arFunctionParams["MODE"], str_replace("#BLOCK_TYPE#", (!is_set($arFunctionParams, "NAME")? GetMessage("MAIN__INC_BLOCK"): $arFunctionParams["NAME"]), GetMessage("MAIN_INC_BLOCK_COMMON_COPY")))
				);
			}
			else
			{
				$arPanelParams["TOOLTIP"] = array(
					'TITLE' => GetMessage('main_incl_file'),
					'TEXT' => $path
				);

				$arIcons[] = array(
					'URL' => 'javascript:'.$APPLICATION->GetPopupLink(
							array(
								'URL' => $editor.$path_url.$arFunctionParams["BACK_URL"].$arFunctionParams["LANG"].$arFunctionParams["TEMPLATE"],
								"PARAMS" => array(
									'width' => 770,
									'height' => 470,
									'resize' => $resize
								)
							)
						),
					'ICON'=>'bx-context-toolbar-edit-icon',
					'TITLE'=>str_replace("#MODE#", $arFunctionParams["MODE"], str_replace("#BLOCK_TYPE#", (!is_set($arFunctionParams, "NAME")? GetMessage("MAIN__INC_BLOCK") : $arFunctionParams["NAME"]), GetMessage("MAIN_INC_ED"))),
					'DEFAULT'=>!$bDefaultExists
				);

				if($arFunctionParams["WORKFLOW"])
				{
					$arIcons[] = array(
						'URL'=>'/bitrix/admin/workflow_edit.php?'.$arFunctionParams["LANG"].'&fname='.urlencode($path).$arFunctionParams["TEMPLATE"].$arFunctionParams["BACK_URL"],
						'ICON'=>'bx-context-toolbar-edit-icon',
						'TITLE'=>str_replace("#BLOCK_TYPE#", (!is_set($arFunctionParams, "NAME")? GetMessage("MAIN__INC_BLOCK"): $arFunctionParams["NAME"]), GetMessage("MAIN_INC_ED_WF"))
					);
				}
			}

			echo $this->IncludeStringBefore();
		}

		$res = null;
		if(is_file($_SERVER["DOCUMENT_ROOT"].$path))
		{
			if(is_array($arParams))
				extract($arParams, EXTR_SKIP);

			$res = include($_SERVER["DOCUMENT_ROOT"].$path);
		}

		if(Main\Application::getInstance()->getKernelSession()["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y" && ($USER->CanDoOperation('edit_php') || Main\Application::getInstance()->getKernelSession()["SHOW_SQL_STAT"]=="Y"))
			echo $debug->Output($rel_path, $path);
		elseif(is_object($debug))
			$debug->Stop($rel_path, $path);

		if($bDrawIcons)
		{
			$comp_id = $path;
			if ($sSrcFile)
			{
				$comp_id .= '|'.$sSrcFile;
			}
			if ($iSrcLine)
			{
				$comp_id .= '|'.$iSrcLine;
			}

			$arPanelParams['COMPONENT_ID'] = md5($comp_id);
			echo $this->IncludeStringAfter($arIcons, $arPanelParams);
		}

		return $res;
	}

	public function AddChainItem($title, $link="", $bUnQuote=true)
	{
		if($bUnQuote)
			$title = str_replace(array("&amp;", "&quot;", "&#039;", "&lt;", "&gt;"), array("&", "\"", "'", "<", ">"), $title);
		$this->arAdditionalChain[] = array("TITLE"=>$title, "LINK"=>htmlspecialcharsbx($link));
	}

	public function GetNavChain($path=false, $iNumFrom=0, $sNavChainPath=false, $bIncludeOnce=false, $bShowIcons = true)
	{
		if($this->GetProperty("NOT_SHOW_NAV_CHAIN") == "Y")
			return "";

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		if($path===false)
			$path = $this->GetCurDir();

		$arChain = array();
		$strChainTemplate = $DOC_ROOT.SITE_TEMPLATE_PATH."/chain_template.php";
		if(!file_exists($strChainTemplate))
		{
			if(($template = getLocalPath("templates/.default/chain_template.php", BX_PERSONAL_ROOT)) !== false)
			{
				$strChainTemplate = $DOC_ROOT.$template;
			}
		}

		$io = CBXVirtualIo::GetInstance();

		while(true)//until the root
		{
			$path = rtrim($path, "/");

			$chain_file_name = $DOC_ROOT.$path."/.section.php";
			if($io->FileExists($chain_file_name))
			{
				$sChainTemplate = "";
				$sSectionName = "";
				include($io->GetPhysicalName($chain_file_name));
				if($sSectionName <> '')
					$arChain[] = array("TITLE"=>$sSectionName, "LINK"=>$path."/");
				if($sChainTemplate <> '')
				{
					$strChainTemplate = $sChainTemplate;
				}
			}

			if($path.'/' == SITE_DIR)
				break;

			if($path == '')
				break;

			//file or folder
			$pos = bxstrrpos($path, "/");
			if($pos===false)
				break;

			//parent folder
			$path = mb_substr($path, 0, $pos + 1);
		}

		if($sNavChainPath!==false)
			$strChainTemplate = $DOC_ROOT.$sNavChainPath;

		$arChain = array_reverse($arChain);
		$arChain = array_merge($arChain, $this->arAdditionalChain);
		if($iNumFrom>0)
			$arChain = array_slice($arChain, $iNumFrom);

		return $this->_mkchain($arChain, $strChainTemplate, $bIncludeOnce, $bShowIcons);
	}

	public function _mkchain($arChain, $strChainTemplate, $bIncludeOnce=false, $bShowIcons = true)
	{
		$strChain = $sChainProlog = $sChainEpilog = "";
		if(file_exists($strChainTemplate))
		{
			$ITEM_COUNT = count($arChain);
			$arCHAIN = $arChain;
			$arCHAIN_LINK = &$arChain;
			$arResult = &$arChain; // for component 2.0
			if($bIncludeOnce)
			{
				$strChain = include($strChainTemplate);
			}
			else
			{
				foreach($arChain as $i => $arChainItem)
				{
					$ITEM_INDEX = $i;
					$TITLE = $arChainItem["TITLE"];
					$LINK = $arChainItem["LINK"];
					$sChainBody = "";
					include($strChainTemplate);
					$strChain .= $sChainBody;
					if($i==0)
						$strChain = $sChainProlog . $strChain;
				}
				if(!empty($arChain))
					$strChain .= $sChainEpilog;
			}
		}

		/** @global CMain $APPLICATION */
		global $USER;
		if($this->GetShowIncludeAreas() && $USER->CanDoOperation('edit_php') && $bShowIcons)
		{
			$site = CSite::GetSiteByFullPath($strChainTemplate);
			$DOC_ROOT = CSite::GetSiteDocRoot($site);

			if(mb_strpos($strChainTemplate, $DOC_ROOT) === 0)
			{
				$path = mb_substr($strChainTemplate, mb_strlen($DOC_ROOT));

				$templ_perm = $this->GetFileAccessPermission($path);
				if((!defined("ADMIN_SECTION") || ADMIN_SECTION!==true) && $templ_perm>="W")
				{
					$arIcons = array();
					$arIcons[] = array(
						"URL"=>"/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".$site."&back_url=".urlencode($_SERVER["REQUEST_URI"])."&full_src=Y&path=".urlencode($path),
						"ICON"=>"nav-template",
						"TITLE"=>GetMessage("MAIN_INC_ED_NAV")
					);

					$strChain = $this->IncludeString($strChain, $arIcons);
				}
			}
		}
		return $strChain;
	}

	public function ShowNavChain($path=false, $iNumFrom=0, $sNavChainPath=false)
	{
		$this->AddBufferContent(array(&$this, "GetNavChain"), $path, $iNumFrom, $sNavChainPath);
	}

	public function ShowNavChainEx($path=false, $iNumFrom=0, $sNavChainPath=false)
	{
		$this->AddBufferContent(array(&$this, "GetNavChain"), $path, $iNumFrom, $sNavChainPath, true);
	}

	/*****************************************************/

	public function SetFileAccessPermission($path, $arPermissions, $bOverWrite=true)
	{
		global $CACHE_MANAGER;

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		$path = rtrim($path, "/");
		if($path == '')
			$path = "/";

		if(($p = bxstrrpos($path, "/")) !== false)
		{
			$path_file = mb_substr($path, $p + 1);
			$path_dir = mb_substr($path, 0, $p);
		}
		else
			return false;

		if($path_file == "" && $path_dir == "")
			$path_file = "/";

		$PERM = array();

		$io = CBXVirtualIo::GetInstance();
		if ($io->FileExists($DOC_ROOT.$path_dir."/.access.php"))
		{
			$fTmp = $io->GetFile($DOC_ROOT.$path_dir."/.access.php");
			//include replaced with eval in order to honor of ZendServer
			eval("?>".$fTmp->GetContents());
		}

		$FILE_PERM = $PERM[$path_file];
		if(!is_array($FILE_PERM))
			$FILE_PERM = array();

		if(!$bOverWrite && !empty($FILE_PERM))
			return true;

		$bDiff = false;

		$str="<?\n";
		foreach($arPermissions as $group=>$perm)
		{
			if($perm <> '')
				$str .= "\$PERM[\"".EscapePHPString($path_file)."\"][\"".EscapePHPString($group)."\"]=\"".EscapePHPString($perm)."\";\n";

			if(!$bDiff)
			{
				//compatibility with group id
				$curr_perm = $FILE_PERM[$group];
				if(!isset($curr_perm) && preg_match('/^G[0-9]+$/', $group))
					$curr_perm = $FILE_PERM[mb_substr($group, 1)];

				if($curr_perm != $perm)
					$bDiff = true;
			}
		}

		foreach($PERM as $file=>$arPerm)
		{
			if(strval($file) !== $path_file)
				foreach($arPerm as $group=>$perm)
					$str .= "\$PERM[\"".EscapePHPString($file)."\"][\"".EscapePHPString($group)."\"]=\"".EscapePHPString($perm)."\";\n";
		}

		if(!$bDiff)
		{
			foreach($FILE_PERM as $group=>$perm)
			{
				//compatibility with group id
				$new_perm = $arPermissions[$group];
				if(!isset($new_perm) && preg_match('/^G[0-9]+$/', $group))
					$new_perm = $arPermissions[mb_substr($group, 1)];

				if($new_perm != $perm)
				{
					$bDiff = true;
					break;
				}
			}
		}

		$str .= "?".">";

		$this->SaveFileContent($DOC_ROOT.$path_dir."/.access.php", $str);
		$CACHE_MANAGER->CleanDir("menu");
		CBitrixComponent::clearComponentCache("bitrix:menu");
		unset($this->FILE_PERMISSION_CACHE[$site."|".$path_dir."/.access.php"]);

		if($bDiff)
		{
			foreach(GetModuleEvents("main", "OnChangePermissions", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(array($site, $path), $arPermissions, $FILE_PERM));

			if(COption::GetOptionString("main", "event_log_file_access", "N") === "Y")
				CEventLog::Log("SECURITY", "FILE_PERMISSION_CHANGED", "main", "[".$site."] ".$path, print_r($FILE_PERM, true)." => ".print_r($arPermissions, true));
		}
		return true;
	}

	public function RemoveFileAccessPermission($path, $arGroups=false)
	{
		global $CACHE_MANAGER;

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		$path = rtrim($path, "/");
		if($path == '')
			$path = "/";

		if(($p = bxstrrpos($path, "/")) !== false)
		{
			$path_file = mb_substr($path, $p + 1);
			$path_dir = mb_substr($path, 0, $p);
		}
		else
			return false;

		$PERM = array();
		$io = CBXVirtualIo::GetInstance();
		if (!$io->FileExists($DOC_ROOT.$path_dir."/.access.php"))
			return true;

		include($io->GetPhysicalName($DOC_ROOT.$path_dir."/.access.php"));

		$str = "<?\n";
		foreach($PERM as $file=>$arPerm)
		{
			if($file != $path_file || $arGroups !== false)
			{
				foreach($arPerm as $group=>$perm)
				{
					$bExists = false;
					if(is_array($arGroups))
					{
						//compatibility with group id
						if(in_array($group, $arGroups))
							$bExists = true;
						elseif(preg_match('/^G[0-9]+$/', $group) && in_array(mb_substr($group, 1), $arGroups))
							$bExists = true;
						elseif(preg_match('/^[0-9]+$/', $group) && in_array('G'.$group, $arGroups))
							$bExists = true;
					}
					if($file != $path_file || ($arGroups !== false && !$bExists))
						$str .= "\$PERM[\"".EscapePHPString($file)."\"][\"".EscapePHPString($group)."\"]=\"".EscapePHPString($perm)."\";\n";
				}
			}
		}

		$str .= "?".">";

		$this->SaveFileContent($DOC_ROOT.$path_dir."/.access.php", $str);
		$CACHE_MANAGER->CleanDir("menu");
		CBitrixComponent::clearComponentCache("bitrix:menu");
		unset($this->FILE_PERMISSION_CACHE[$site."|".$path_dir."/.access.php"]);

		foreach(GetModuleEvents("main", "OnChangePermissions", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(array($site, $path), array()));

		return true;
	}

	public function CopyFileAccessPermission($path_from, $path_to, $bOverWrite=false)
	{
		CMain::InitPathVars($site_from, $path_from);
		$DOC_ROOT_FROM = CSite::GetSiteDocRoot($site_from);

		CMain::InitPathVars($site_to, $path_to);

		//upper .access.php
		if(($p = bxstrrpos($path_from, "/"))!==false)
		{
			$path_from_file = mb_substr($path_from, $p + 1);
			$path_from_dir = mb_substr($path_from, 0, $p);
		}
		else
			return false;

		$PERM = array();

		$io = CBXVirtualIo::GetInstance();
		if (!$io->FileExists($DOC_ROOT_FROM.$path_from_dir."/.access.php"))
			return true;

		include($io->GetPhysicalName($DOC_ROOT_FROM.$path_from_dir."/.access.php"));

		$FILE_PERM = $PERM[$path_from_file];
		if(!empty($FILE_PERM))
			return $this->SetFileAccessPermission(array($site_to, $path_to), $FILE_PERM, $bOverWrite);

		return true;
	}


	public function GetFileAccessPermission($path, $groups=false, $task_mode=false) // task_mode - new access mode
	{
		global $USER;

		if($groups === false)
		{
			if(!is_object($USER))
				$groups = array('G2');
			else
				$groups = $USER->GetAccessCodes();
		}
		elseif(is_array($groups) && !empty($groups))
		{
			//compatibility with user groups id
			$bNumbers = preg_match('/^[0-9]+$/', $groups[0]);
			if($bNumbers)
				foreach($groups as $key=>$val)
					$groups[$key] = "G".$val;
		}

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		//windows files are case-insensitive
		$bWin = (strncasecmp(PHP_OS, "WIN", 3) == 0);
		if($bWin)
			$path = mb_strtolower($path);

		if(trim($path, "/") != "")
		{
			$path = Rel2Abs("/", $path);
			if($path == "")
				return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));
		}

		if(COption::GetOptionString("main", "controller_member", "N") == "Y" && COption::GetOptionString("main", "~controller_limited_admin", "N") == "Y")
			$bAdminM = (is_object($USER)? $USER->IsAdmin() : false);
		else
			$bAdminM = in_array("G1", $groups);

		if($bAdminM)
			return (!$task_mode? 'X' : array(CTask::GetIdByLetter('X', 'main', 'file')));

		if(mb_substr($path, -12) == "/.access.php")
			return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));

		if(mb_substr($path, -10) == "/.htaccess")
			return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));

		$max_perm = "D";
		$arGroupTask = array();

		$io = CBXVirtualIo::GetInstance();

		//in the group list * === "any group"
		$groups[] = "*";
		while(true)//till the root
		{
			$path = rtrim($path, "\0");
			$path = rtrim($path, "/");

			if($path == '')
			{
				$access_file_name="/.access.php";
				$Dir = "/";
			}
			else
			{
				//file or folder
				$pos = bxstrrpos($path, "/");
				if($pos === false)
					break;
				$Dir = mb_substr($path, $pos + 1);

				//security fix: under Windows "my." == "my"
				$Dir = TrimUnsafe($Dir);

				//parent folder
				$path = mb_substr($path, 0, $pos + 1);

				$access_file_name=$path.".access.php";
			}

			if(array_key_exists($site."|".$access_file_name, $this->FILE_PERMISSION_CACHE))
			{
				$PERM = $this->FILE_PERMISSION_CACHE[$site."|".$access_file_name];
			}
			else
			{
				$PERM = array();

				//file with rights array
				if ($io->FileExists($DOC_ROOT.$access_file_name))
					include($io->GetPhysicalName($DOC_ROOT.$access_file_name));

				//windows files are case-insensitive
				if($bWin && !empty($PERM))
				{
					$PERM_TMP = array();
					foreach($PERM as $key => $val)
						$PERM_TMP[mb_strtolower($key)] = $val;
					$PERM = $PERM_TMP;
				}

				$this->FILE_PERMISSION_CACHE[$site."|".$access_file_name] = $PERM;
			}

			//check wheather the rights are assigned to this file\folder for these groups
			if(isset($PERM[$Dir]) && is_array($PERM[$Dir]))
			{
				$dir_perm = $PERM[$Dir];
				foreach($groups as $key => $group_id)
				{
					if(isset($dir_perm[$group_id]))
						$perm = $dir_perm[$group_id];
					elseif(preg_match('/^G([0-9]+)$/', $group_id, $match)) //compatibility with group id
					{
						if(isset($dir_perm[$match[1]]))
							$perm = $dir_perm[$match[1]];
						else
							continue;
					}
					else
						continue;

					if ($task_mode)
					{
						if(mb_substr($perm, 0, 2) == 'T_')
							$tid = intval(mb_substr($perm, 2));
						elseif(($tid = CTask::GetIdByLetter($perm, 'main', 'file')) === false)
							continue;

						$arGroupTask[$group_id] = $tid;
					}
					else
					{
						if(mb_substr($perm, 0, 2) == 'T_')
						{
							$tid = intval(mb_substr($perm, 2));
							$perm = CTask::GetLetter($tid);
							if($perm == '')
								$perm = 'D';
						}

						if($max_perm == "" || $perm > $max_perm)
						{
							$max_perm = $perm;
							if($perm == "W")
								break 2;
						}
					}

					if($group_id == "*")
						break 2;

					//delete the groip from the list, we have rights alredy for it
					unset($groups[$key]);

					if(count($groups) == 1 && in_array("*", $groups))
						break 2;
				}

				if(count($groups)<=1)
					break;
			}

			if($path == '')
				break;
		}

		if($task_mode)
		{
			$arTasks = array_unique(array_values($arGroupTask));
			if(empty($arTasks))
				return array(CTask::GetIdByLetter('D', 'main', 'file'));
			sort($arTasks);
			return $arTasks;
		}
		else
			return $max_perm;
	}

	public function GetFileAccessPermissionByUser($intUserID, $path, $groups=false, $task_mode=false) // task_mode - new access mode
	{
		$intUserIDTmp = intval($intUserID);
		if ($intUserIDTmp.'|' != $intUserID.'|')
			return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));
		$intUserID = $intUserIDTmp;

		if ($groups === false)
		{
			$groups = CUser::GetUserGroup($intUserID);
			foreach ($groups as $key=>$val)
				$groups[$key] = "G".$val;
		}
		elseif (is_array($groups) && !empty($groups))
		{
			$bNumbers = preg_match('/^[0-9]+$/', $groups[0]);
			if($bNumbers)
				foreach($groups as $key=>$val)
					$groups[$key] = "G".$val;
		}

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		$bWin = (strncasecmp(PHP_OS, "WIN", 3) == 0);
		if ($bWin)
			$path = mb_strtolower($path);

		if (trim($path, "/") != "")
		{
			$path = Rel2Abs("/", $path);
			if($path == "")
				return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));
		}

		$bAdminM = in_array("G1", $groups);

		if ($bAdminM)
			return (!$task_mode? 'X' : array(CTask::GetIdByLetter('X', 'main', 'file')));

		if (mb_substr($path, -12) == "/.access.php")
			return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));

		if (mb_substr($path, -10) == "/.htaccess")
			return (!$task_mode? 'D' : array(CTask::GetIdByLetter('D', 'main', 'file')));

		$max_perm = "D";
		$arGroupTask = array();

		$io = CBXVirtualIo::GetInstance();

		$groups[] = "*";
		while (true)
		{
			$path = rtrim($path, "\0");
			$path = rtrim($path, "/");

			if ($path == '')
			{
				$access_file_name="/.access.php";
				$Dir = "/";
			}
			else
			{
				$pos = mb_strrpos($path, "/");
				if ($pos === false)
					break;
				$Dir = mb_substr($path, $pos + 1);

				$Dir = TrimUnsafe($Dir);

				$path = mb_substr($path, 0, $pos + 1);

				$access_file_name=$path.".access.php";
			}

			if (array_key_exists($site."|".$access_file_name, $this->FILE_PERMISSION_CACHE))
			{
				$PERM = $this->FILE_PERMISSION_CACHE[$site."|".$access_file_name];
			}
			else
			{
				$PERM = array();

				if ($io->FileExists($DOC_ROOT.$access_file_name))
					include($io->GetPhysicalName($DOC_ROOT.$access_file_name));

				if ($bWin && !empty($PERM))
				{
					$PERM_TMP = array();
					foreach($PERM as $key => $val)
						$PERM_TMP[mb_strtolower($key)] = $val;
					$PERM = $PERM_TMP;
				}

				$this->FILE_PERMISSION_CACHE[$site."|".$access_file_name] = $PERM;
			}

			if ($PERM[$Dir] && is_array($PERM[$Dir]))
			{
				$dir_perm = $PERM[$Dir];
				foreach ($groups as $key => $group_id)
				{
					if(isset($dir_perm[$group_id]))
						$perm = $dir_perm[$group_id];
					elseif(preg_match('/^G[0-9]+$/', $group_id)) //compatibility with group id
						$perm = $dir_perm[mb_substr($group_id, 1)];
					else
						continue;

					if ($task_mode)
					{
						if(mb_substr($perm, 0, 2) == 'T_')
							$tid = intval(mb_substr($perm, 2));
						elseif(($tid = CTask::GetIdByLetter($perm, 'main', 'file')) === false)
							continue;

						$arGroupTask[$group_id] = $tid;
					}
					else
					{
						if(mb_substr($perm, 0, 2) == 'T_')
						{
							$tid = intval(mb_substr($perm, 2));
							$perm = CTask::GetLetter($tid);
							if($perm == '')
								$perm = 'D';
						}

						if ($max_perm == "" || $perm > $max_perm)
						{
							$max_perm = $perm;
							if($perm == "W")
								break 2;
						}
					}

					if($group_id == "*")
						break 2;

					unset ($groups[$key]);

					if (count($groups) == 1 && in_array("*", $groups))
						break 2;
				}

				if (count($groups)<=1)
					break;
			}

			if($path == '')
				break;
		}

		if ($task_mode)
		{
			$arTasks = array_unique(array_values($arGroupTask));
			if(empty($arTasks))
				return array(CTask::GetIdByLetter('D', 'main', 'file'));
			sort($arTasks);
			return $arTasks;
		}
		else
			return $max_perm;
	}
	/***********************************************/

	public function SaveFileContent($abs_path, $strContent)
	{
		$strContent = str_replace("\r\n", "\n", $strContent);

		$file = array();
		$this->ResetException();

		foreach(GetModuleEvents("main", "OnBeforeChangeFile", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($abs_path, &$strContent)) == false)
			{
				if(!$this->GetException())
					$this->ThrowException(GetMessage("main_save_file_handler_error", array("#HANDLER#"=>$arEvent["TO_NAME"])));
				return false;
			}
		}

		$io = CBXVirtualIo::GetInstance();
		$fileIo = $io->GetFile($abs_path);

		$io->CreateDirectory($fileIo->GetPath());

		if($fileIo->IsExists())
		{
			$file["exists"] = true;
			if (!$fileIo->IsWritable())
				$fileIo->MarkWritable();
			$file["size"] = $fileIo->GetFileSize();
		}

		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$quota = new CDiskQuota();
			if (false === $quota->checkDiskQuota(array("FILE_SIZE" => intval(mb_strlen($strContent) - intVal($file["size"])))))
			{
				$this->ThrowException($quota->LAST_ERROR, "BAD_QUOTA");
				return false;
			}
		}
		/****************************** QUOTA ******************************/
		if ($fileIo->PutContents($strContent))
		{
			$fileIo->MarkWritable();
		}
		else
		{
			if ($file["exists"])
				$this->ThrowException(GetMessage("MAIN_FILE_NOT_CREATE"), "FILE_NOT_CREATE");
			else
				$this->ThrowException(GetMessage("MAIN_FILE_NOT_OPENED"), "FILE_NOT_OPEN");
			return false;
		}

		bx_accelerator_reset();

		$site = CSite::GetSiteByFullPath($abs_path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		if(mb_strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN')
		{
			//Fix for name case under Windows
			$abs_path = mb_strtolower($abs_path);
			$DOC_ROOT = mb_strtolower($DOC_ROOT);
		}

		if(mb_strpos($abs_path, $DOC_ROOT) === 0 && $site!==false)
		{
			$DOC_ROOT = rtrim($DOC_ROOT, "/\\");
			$path = "/".ltrim(mb_substr($abs_path, mb_strlen($DOC_ROOT)), "/\\");

			foreach(GetModuleEvents("main", "OnChangeFile", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($path, $site));
		}
		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$fs = $fileIo->GetFileSize();
			CDiskQuota::updateDiskQuota("files", intval($fs - intVal($file["size"])), "update");
		}
		/****************************** QUOTA ******************************/
		return true;
	}

	public function GetFileContent($path)
	{
		clearstatcache();

		$io = CBXVirtualIo::GetInstance();

		if(!$io->FileExists($path))
			return false;
		$f = $io->GetFile($path);
		if($f->GetFileSize()<=0)
			return "";
		$contents = $f->GetContents();
		return $contents;
	}

	/**
	 * @deprecated Use LPA::Process()
	 */
	public static function ProcessLPA($filesrc = false, $old_filesrc = false)
	{
		return LPA::Process($filesrc, $old_filesrc);
	}

	/**
	 * @deprecated Use LPA::ComponentChecker()
	 */
	public static function LPAComponentChecker(&$arParams, &$arPHPparams, $parentParamName = false)
	{
		LPA::ComponentChecker($arParams, $arPHPparams, $parentParamName);
	}

	public static function _ReplaceNonLatin($str)
	{
		return preg_replace("/[^a-zA-Z0-9_:\\.!\$\\-;@\\^\\~]/is", "", $str);
	}

	public function GetLangSwitcherArray()
	{
		return $this->GetSiteSwitcherArray();
	}

	public function GetSiteSwitcherArray()
	{
		$cur_dir = $this->GetCurDir();
		$cur_page = $this->GetCurPage();
		$bAdmin = (mb_substr($cur_dir, 0, mb_strlen(BX_ROOT."/admin/")) == BX_ROOT."/admin/");

		$path_without_lang = $path_without_lang_tmp = "";

		$db_res = CSite::GetList('', '', array("ACTIVE"=>"Y","ID"=>LANG));
		if(($ar = $db_res->Fetch()) && mb_strpos($cur_page, $ar["DIR"]) === 0)
		{
			$path_without_lang = mb_substr($cur_page, mb_strlen($ar["DIR"]) - 1);
			$path_without_lang = LTrim($path_without_lang, "/");
			$path_without_lang_tmp = RTrim($path_without_lang, "/");
		}

		$result = array();
		$db_res = CSite::GetList("SORT", "ASC", array("ACTIVE"=>"Y"));
		while($ar = $db_res->Fetch())
		{
			$ar["NAME"] = htmlspecialcharsbx($ar["NAME"]);
			$ar["SELECTED"] = ($ar["LID"]==LANG);

			if($bAdmin)
			{
				global $QUERY_STRING;
				$p = rtrim(str_replace("&#", "#", preg_replace("/lang=[^&#]*&*/", "", $QUERY_STRING)), "&");
				$ar["PATH"] = $this->GetCurPage()."?lang=".$ar["LID"].($p <> ''? '&'.$p : '');
			}
			else
			{
				$ar["PATH"] = "";

				if(mb_strlen($path_without_lang) > 1 && file_exists($ar["ABS_DOC_ROOT"]."/".$ar["DIR"]."/".$path_without_lang_tmp))
					$ar["PATH"] = $ar["DIR"].$path_without_lang;

				if($ar["PATH"] == '')
					$ar["PATH"] = $ar["DIR"];

				if($ar["ABS_DOC_ROOT"]!==$_SERVER["DOCUMENT_ROOT"])
					$ar["FULL_URL"] = (CMain::IsHTTPS() ? "https://" : "http://").$ar["SERVER_NAME"].$ar["PATH"];
				else
					$ar["FULL_URL"] = $ar["PATH"];
			}

			$result[] = $ar;
		}
		return $result;
	}

	/*
	Returns an array of roles for a module
	W - max rights (admin)
	D - min rights (access denied)

	$module_id - a module id
	$arGroups - array of groups ID, if not set then for current useer
	$use_default_role - "Y" - use default role
	$max_role_for_super_admin - "Y" - for group ID=1 return max rights
	*/
	public static function GetUserRoles($module_id, $arGroups=false, $use_default_role="Y", $max_role_for_super_admin="Y", $site_id=false)
	{
		global $DB, $USER;
		static $MODULE_ROLES = array();

		$err_mess = (CAllMain::err_mess())."<br>Function: GetUserRoles<br>Line: ";
		$arRoles = array();
		$min_role = "D";
		$max_role = "W";
		if($arGroups===false)
		{
			if(is_object($USER))
				$arGroups = $USER->GetUserGroupArray();
			if(!is_array($arGroups))
				$arGroups[] = 2;
		}
		$key = $use_default_role."_".$max_role_for_super_admin;
		$groups = '';
		if(is_array($arGroups) && !empty($arGroups))
		{
			foreach($arGroups as $grp)
				$groups .= ($groups<>''? ',':'').intval($grp);
			$key .= "_".$groups;
		}

		$cache_site_key = ($site_id ? $site_id : "COMMON");

		if(isset($MODULE_ROLES[$module_id][$cache_site_key][$key]))
		{
			$arRoles = $MODULE_ROLES[$module_id][$cache_site_key][$key];
		}
		else
		{
			if(is_array($arGroups) && !empty($arGroups))
			{
				if(in_array(1,$arGroups) && $max_role_for_super_admin=="Y")
					$arRoles[] = $max_role;

				$strSql =
					"SELECT MG.G_ACCESS FROM b_group G ".
					"	LEFT JOIN b_module_group MG ON (G.ID = MG.GROUP_ID ".
					"		AND MG.MODULE_ID = '".$DB->ForSql($module_id,50)."') ".
					"		AND MG.SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL")." ".
					"WHERE G.ID in (".$groups.") AND G.ACTIVE = 'Y'";

				$t = $DB->Query($strSql, false, $err_mess.__LINE__);

				$default_role = $min_role;
				if($use_default_role=="Y")
					$default_role = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $min_role);

				while ($tr = $t->Fetch())
				{
					if ($tr["G_ACCESS"] !== null)
					{
						$arRoles[] = $tr["G_ACCESS"];
					}
					else
					{
						if($use_default_role=="Y")
							$arRoles[] = $default_role;
					}
				}

			}
			//if($use_default_role=="Y")
			//{
			//	$arRoles[] = COption::GetOptionString($module_id, "GROUP_DEFAULT_RIGHT", $min_role);
			//}
			$arRoles = array_unique($arRoles);
			$MODULE_ROLES[$module_id][$cache_site_key][$key] = $arRoles;
		}
		return $arRoles;
	}

	/**
	 * Returns an array of rights for a module
	 * W - max rights (admin)
	 * D - min rights (access denied)
	 * @param string $moduleId - a module id
	 * @param array | bool $groups - array of groups ID, if not set then for current useer
	 * @param string $use_default_level - "Y" - use default role
	 * @param string $max_right_for_super_admin - "Y" - for group ID=1 return max rights
	 * @param array | bool $siteId
	 */
	public static function GetUserRight($moduleId, $groups = false, $use_default_level = 'Y', $max_right_for_super_admin = 'Y', $siteId = false)
	{
		global $USER;

		$minRight = 'D';
		$maxRight = 'W';

		if ($groups === false && ($USER instanceof CUser))
		{
			if ($max_right_for_super_admin == 'Y' && $USER->IsAdmin())
			{
				return $maxRight;
			}
			$groups = $USER->GetUserGroupArray();
		}

		if (!is_array($groups))
		{
			$groups = [2];
		}

		if (
			$max_right_for_super_admin == 'Y'
			&& in_array(1, $groups)
			&& (
				COption::GetOptionString('main', 'controller_member', 'N') != 'Y'
				|| COption::GetOptionString('main', '~controller_limited_admin', 'N') != 'Y'
			)
		)
		{
			return $maxRight;
		}

		if (!is_array($siteId))
		{
			$siteId = [$siteId];
		}

		$modulePermissions = ModuleGroupTable::query()
			->setSelect(['*'])
			->where('MODULE_ID', $moduleId)
			->where('GROUP.ACTIVE', 'Y')
			->setCacheTtl(86400)
			->cacheJoins(true)
			->fetchAll()
		;

		$right = '';
		foreach ($modulePermissions as $permission)
		{
			// site filter
			if (in_array($permission['SITE_ID'], $siteId))
			{
				// group filter
				if (in_array($permission['GROUP_ID'], $groups))
				{
					// max
					if ($permission['G_ACCESS'] > $right)
					{
						$right = $permission['G_ACCESS'];
					}
				}
			}
		}

		if ($right == '' && $use_default_level == 'Y')
		{
			$right = COption::GetOptionString($moduleId, 'GROUP_DEFAULT_RIGHT', $minRight);
		}

		return $right;
	}

	public static function GetUserRightArray($moduleId, $groups)
	{
		$arRes = [];

		if (is_array($groups) && !empty($groups))
		{
			$query = ModuleGroupTable::query()
				->setSelect(['*'])
				->where('MODULE_ID', $moduleId)
				->where('GROUP.ACTIVE', 'Y')
				->setCacheTtl(86400)
				->cacheJoins(true)
				->exec()
			;

			while ($tr = $query->fetch())
			{
				if (in_array($tr['GROUP_ID'], $groups))
				{
					$arRes[($tr['SITE_ID'] != '' ? $tr['SITE_ID'] : 'common')][$tr['GROUP_ID']] = $tr['G_ACCESS'];
				}
			}
		}

		return $arRes;
	}

	public static function GetGroupRightList($arFilter, $site_id=false)
	{
		static $fields = ['MODULE_ID' => 1, 'GROUP_ID' => 1, 'G_ACCESS' => 1];

		$query = ModuleGroupTable::query()->setSelect(['*']);

		foreach ($arFilter as $field => $value)
		{
			if (isset($fields[$field]))
			{
				$query->where($field, $value);
			}
		}

		if ($site_id)
		{
			$query->where('SITE_ID', $site_id);
		}
		else
		{
			$query->whereNull('SITE_ID');
		}

		return $query->exec();
	}

	public static function GetGroupRight($module_id, $arGroups=false, $use_default_level="Y", $max_right_for_super_admin="Y", $site_id = false)
	{
		return CMain::GetUserRight($module_id, $arGroups, $use_default_level, $max_right_for_super_admin, $site_id);
	}

	public static function SetGroupRight($module_id, $group_id, $right, $site_id=false)
	{
		global $DB;
		$err_mess = (CAllMain::err_mess())."<br>Function: SetGroupRight<br>Line: ";
		$group_id = intval($group_id);

		if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old value
			$sOldRight = "";
			$rsRight = $DB->Query("SELECT G_ACCESS FROM b_module_group WHERE MODULE_ID='".$DB->ForSql($module_id,50)."' AND GROUP_ID=".$group_id." AND SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL"));
			if($arRight = $rsRight->Fetch())
				$sOldRight = $arRight["G_ACCESS"];
			if($sOldRight <> $right)
				CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $group_id, $module_id.($site_id ? "/".$site_id : "").": (".$sOldRight.") => (".$right.")");
		}

		$arFields = array(
			"MODULE_ID"	=> "'".$DB->ForSql($module_id,50)."'",
			"GROUP_ID"	=> $group_id,
			"G_ACCESS"	=> "'".$DB->ForSql($right,255)."'"
		);

		$rows = $DB->Update("b_module_group", $arFields, "WHERE MODULE_ID='".$DB->ForSql($module_id,50)."' AND GROUP_ID='".$group_id."' AND SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL"), $err_mess.__LINE__);
		if(intval($rows)<=0)
		{
			if ($site_id)
				$arFields["SITE_ID"] = "'".$DB->ForSql($site_id,2)."'";

			$DB->Insert("b_module_group",$arFields, $err_mess.__LINE__);
		}

		ModuleGroupTable::cleanCache();

		foreach (GetModuleEvents("main", "OnAfterSetGroupRight", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($module_id, $group_id));
		}
	}

	public static function DelGroupRight($module_id='', $arGroups=array(), $site_id=false)
	{
		global $DB;
		$err_mess = (CAllMain::err_mess())."<br>Function:  DelGroupRight<br>Line: ";
		$strSql = '';

		$sGroups = '';
		if(is_array($arGroups) && !empty($arGroups))
			foreach($arGroups as $grp)
				$sGroups .= ($sGroups <> ''? ',':'').intval($grp);

		if($module_id <> '')
		{
			if($sGroups <> '')
			{
				if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
				{
					//get old value
					$rsRight = $DB->Query("SELECT GROUP_ID, G_ACCESS FROM b_module_group WHERE MODULE_ID='".$DB->ForSql($module_id,50)."' AND GROUP_ID IN (".$sGroups.") AND SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL"));
					while($arRight = $rsRight->Fetch())
						CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $arRight["GROUP_ID"], $module_id.($site_id ? "/".$site_id : "").": (".$arRight["G_ACCESS"].") => ()");
				}
				$strSql = "DELETE FROM b_module_group WHERE MODULE_ID='".$DB->ForSql($module_id,50)."' and GROUP_ID in (".$sGroups.") AND SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL");
			}
			else
			{
				//on delete module
				$strSql = "DELETE FROM b_module_group WHERE MODULE_ID='".$DB->ForSql($module_id,50)."' AND SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL");
			}
		}
		elseif($sGroups <> '')
		{
			//on delete user group
			$strSql = "DELETE FROM b_module_group WHERE GROUP_ID in (".$sGroups.") AND SITE_ID ".($site_id ? "= '".$DB->ForSql($site_id)."'" : "IS NULL");
		}

		if($strSql <> '')
		{
			$DB->Query($strSql, false, $err_mess.__LINE__);

			ModuleGroupTable::cleanCache();

			foreach (GetModuleEvents("main", "OnAfterDelGroupRight", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($module_id, $arGroups));
			}
		}
	}

	public static function GetMainRightList()
	{
		$arr = array(
			"reference_id" => array(
				"D",
				"P",
				"R",
				"T",
				"V",
				"W"),
			"reference" => array(
				"[D] ".GetMessage("OPTION_DENIED"),
				"[P] ".GetMessage("OPTION_PROFILE"),
				"[R] ".GetMessage("OPTION_READ"),
				"[T] ".GetMessage("OPTION_READ_PROFILE_WRITE"),
				"[V] ".GetMessage("OPTION_READ_OTHER_PROFILES_WRITE"),
				"[W] ".GetMessage("OPTION_WRITE"))
		);
		return $arr;
	}

	public static function GetDefaultRightList()
	{
		$arr = array(
			"reference_id" => array("D","R","W"),
			"reference" => array(
				"[D] ".GetMessage("OPTION_DENIED"),
				"[R] ".GetMessage("OPTION_READ"),
				"[W] ".GetMessage("OPTION_WRITE"))
		);
		return $arr;
	}

	public static function err_mess()
	{
		return "<br>Class: CAllMain<br>File: ".__FILE__;
	}

	/*
	Returns a cookie value by the name

	$name			: cookie name (without prefix)
	$name_prefix	: name prefix (if not set get from options)
	*/
	public function get_cookie($name, $name_prefix=false)
	{
		if($name_prefix===false)
			$name = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_".$name;
		else
			$name = $name_prefix."_".$name;
		return ($_COOKIE[$name] ?? "");
	}

	/**
	 * Sets a cookie and spreads it through domains.
	 *
	 * @deprecated Use \Bitrix\Main\HttpResponse::addCookie().
	 *
	 * @param string $name Cookie name (without prefix)
	 * @param string $value value
	 * @param bool|int $time expire date
	 * @param string $folder cookie dir
	 * @param bool|string $domain cookie domain
	 * @param bool $secure secure flag
	 * @param bool|int $spread to spread or not to spread
	 * @param bool $name_prefix name prefix (if not set get from options)
	 * @param bool $httpOnly
	 */
	public function set_cookie($name, $value, $time=false, $folder="/", $domain=false, $secure=false, $spread=true, $name_prefix=false, $httpOnly=false)
	{
		if($time === false)
		{
			$time = null;
		}

		$cookie = new Main\Web\Cookie($name, $value, $time);

		if($name_prefix !== false)
		{
			$cookie->setName($name_prefix."_".$name);
		}

		if($domain !== false)
		{
			$cookie->setDomain($domain);
		}
		$cookie->setPath($folder);
		$cookie->setSecure($secure);
		$cookie->setHttpOnly($httpOnly);

		if($spread === "Y" || $spread === true)
		{
			$spread_mode = Main\Web\Cookie::SPREAD_DOMAIN | Main\Web\Cookie::SPREAD_SITES;
		}
		elseif($spread >= 1)
		{
			$spread_mode = $spread;
		}
		else
		{
			$spread_mode = Main\Web\Cookie::SPREAD_DOMAIN;
		}
		$cookie->setSpread($spread_mode);

		Main\Context::getCurrent()->getResponse()->addCookie($cookie);
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Cookie::getCookieDomain().
	 * @return string
	 */
	public function GetCookieDomain()
	{
		return Main\Web\Cookie::getCookieDomain();
	}

	public function StoreCookies()
	{
		$application = Main\Application::getInstance();

		if ($application->getSessionLocalStorageManager()->isReady())
		{
			$response = Main\Context::getCurrent()->getResponse();

			$localStorage = $application->getLocalSession('spreadCookies');
			$cookies = $localStorage->getData();

			foreach ($cookies as $cookie)
			{
				if ($cookie instanceof Main\Web\Cookie)
				{
					$response->addCookie($cookie, false);
				}
			}

			$localStorage->setData($response->getCookies());

			$this->HoldSpreadCookieHTML(true);
		}
	}

	public function HoldSpreadCookieHTML($bSet = false)
	{
		static $showed_already = false;
		$result = $showed_already;
		if ($bSet)
		{
			$showed_already = true;
		}
		return $result;
	}

	// Returns string with images to spread cookies
	public function GetSpreadCookieHTML()
	{
		$res = '';
		$request = Main\Context::getCurrent()->getRequest();

		if (
			$request->isHttps()
			&& !$this->HoldSpreadCookieHTML()
			&& COption::GetOptionString("main", "ALLOW_SPREAD_COOKIE", "Y") == "Y"
		)
		{
			foreach ($this->GetSpreadCookieUrls() as $url)
			{
				$res .= "new Image().src='" . CUtil::JSEscape($url) . "';\n";
			}

			if ($res)
			{
				$this->HoldSpreadCookieHTML(true);

				return '<script>' . $res . '</script>';
			}
		}

		return '';
	}

	/**
	 * Returns array of urls which contain signed cross domain cookies.
	 *
	 * @return array
	 */
	public function GetSpreadCookieUrls()
	{
		$res = array();
		if(COption::GetOptionString("main", "ALLOW_SPREAD_COOKIE", "Y")=="Y")
		{
			$response = Main\Context::getCurrent()->getResponse();
			$request = Main\Context::getCurrent()->getRequest();

			$application = Main\Application::getInstance();
			$localStorage = $application->getLocalSession('spreadCookies');
			$cookies = $localStorage->getData();

			foreach($cookies as $cookie)
			{
				if($cookie instanceof Main\Web\Cookie)
				{
					$response->addCookie($cookie, false);
				}
			}
			$localStorage->clear();

			$cookies = $response->getCookies();

			if(!empty($cookies))
			{
				$params = "";
				foreach($cookies as $cookie)
				{
					if($cookie->getSpread() & Main\Web\Cookie::SPREAD_SITES)
					{
						$params .= $cookie->getName().chr(1).
							$cookie->getValue().chr(1).
							$cookie->getExpires().chr(1).
							$cookie->getPath().chr(1).
							chr(1). //domain is empty
							$cookie->getSecure().chr(1).
							$cookie->getHttpOnly().chr(2);
					}
				}
				$salt = $_SERVER["REMOTE_ADDR"] . "|" . @filemtime($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/version.php") . "|" . $application->getLicense()->getKey();
				$params = "s=".urlencode(base64_encode($params))."&k=".urlencode(md5($params.$salt));

				$arrDomain = array();
				$arrDomain[] = $request->getHttpHost();

				$rs = CSite::GetList('', '', array("ACTIVE" => "Y"));
				while($ar = $rs->Fetch())
				{
					$arD = explode("\n", str_replace("\r", "\n", $ar["DOMAINS"]));
					if(is_array($arD))
						foreach($arD as $d)
							if(trim($d) <> '')
								$arrDomain[] = $d;
				}

				if(!empty($arrDomain))
				{
					$arUniqDomains = array();
					$arrDomain = array_unique($arrDomain);
					$arrDomain2 = array_unique($arrDomain);
					foreach($arrDomain as $domain1)
					{
						$bGood = true;
						foreach($arrDomain2 as $domain2)
						{
							if(mb_strlen($domain1) > mb_strlen($domain2) && mb_substr($domain1, -(mb_strlen($domain2) + 1)) == ".".$domain2)
							{
								$bGood = false;
								break;
							}
						}
						if($bGood)
							$arUniqDomains[] = $domain1;
					}

					$protocol = ($request->isHttps()? "https://" : "http://");
					$arrCurUrl = parse_url($protocol.$request->getHttpHost()."/");
					foreach($arUniqDomains as $domain)
					{
						if(trim($domain) <> '')
						{
							$url = $protocol.$domain."/bitrix/spread.php?".$params;
							$arrUrl = parse_url($url);
							if($arrUrl["host"] != $arrCurUrl["host"])
								$res[] = $url;
						}
					}
				}
			}
		}

		return $res;
	}

	public function ShowSpreadCookieHTML()
	{
		$this->AddBufferContent(array(&$this, "GetSpreadCookieHTML"));
	}

	public function AddPanelButton($arButton, $bReplace=false)
	{
		if(is_array($arButton) && !empty($arButton))
		{
			if(isset($arButton["ID"]) && $arButton["ID"] <> "")
			{
				if(!isset($this->arPanelButtons[$arButton["ID"]]))
				{
					$this->arPanelButtons[$arButton["ID"]] = $arButton;
				}
				elseif($bReplace)
				{
					if(
						isset($this->arPanelButtons[$arButton["ID"]]["MENU"])
						&& is_array($this->arPanelButtons[$arButton["ID"]]["MENU"])
					)
					{
						if(!isset($arButton["MENU"]) || !is_array($arButton["MENU"]))
						{
							$arButton["MENU"] = [];
						}
						$arButton["MENU"] = array_merge($this->arPanelButtons[$arButton["ID"]]["MENU"], $arButton["MENU"]);
					}
					$this->arPanelButtons[$arButton["ID"]] = $arButton;
				}

				if (isset($this->arPanelFutureButtons[$arButton['ID']]))
				{
					if (
						isset($this->arPanelButtons[$arButton["ID"]]["MENU"])
						&& is_array($this->arPanelButtons[$arButton["ID"]]["MENU"])
					)
					{
						$this->arPanelButtons[$arButton["ID"]]["MENU"] = array_merge(
							$this->arPanelButtons[$arButton["ID"]]["MENU"],
							$this->arPanelFutureButtons[$arButton["ID"]]
						);
					}
					else
					{
						$this->arPanelButtons[$arButton["ID"]]["MENU"] = $this->arPanelFutureButtons[$arButton["ID"]];
					}
					unset($this->arPanelFutureButtons[$arButton['ID']]);
				}
			}
			else
			{
				$this->arPanelButtons[] = $arButton;
			}
		}
	}

	public function AddPanelButtonMenu($button_id, $arMenuItem)
	{
		if(isset($this->arPanelButtons[$button_id]))
		{
			if(!isset($this->arPanelButtons[$button_id]['MENU']))
				$this->arPanelButtons[$button_id]['MENU'] = array();
			$this->arPanelButtons[$button_id]['MENU'][] = $arMenuItem;
		}
		else
		{
			if(!isset($this->arPanelFutureButtons[$button_id]))
				$this->arPanelFutureButtons[$button_id] = array();

			$this->arPanelFutureButtons[$button_id][] = $arMenuItem;
		}
	}

	public function GetPanel()
	{
		global $USER;

		$isFrameAjax = Composite\Engine::getUseHTMLCache() && Composite\Engine::isAjaxRequest();
		if (isset($GLOBALS["USER"]) && is_object($USER) && $USER->IsAuthorized() && !isset($_REQUEST["bx_hit_hash"]) && !$isFrameAjax)
		{
			echo CTopPanel::GetPanelHtml();
		}
	}

	public function ShowPanel()
	{
		global $USER;

		$isFrameAjax = Composite\Engine::getUseHTMLCache() && Composite\Engine::isAjaxRequest();
		if (isset($GLOBALS["USER"]) && is_object($USER) && $USER->IsAuthorized() && !isset($_REQUEST["bx_hit_hash"]) && !$isFrameAjax)
		{
			$this->showPanelWasInvoked = true;

			class_exists('CTopPanel'); //http://bugs.php.net/bug.php?id=47948
			AddEventHandler('main', 'OnBeforeEndBufferContent', array('CTopPanel', 'InitPanel'));
			$this->AddBufferContent(array('CTopPanel', 'GetPanelHtml'));

			//Prints global url classes and  variables for HotKeys
			$this->AddBufferContent(array('CAllMain',"PrintHKGlobalUrlVar"));

			//Prints global url classes and  variables for Stickers
			$this->AddBufferContent(array('CSticker',"InitJsAfter"));

			$this->AddBufferContent(array('CAdminInformer',"PrintHtmlPublic"));
		}
	}

	public static function PrintHKGlobalUrlVar()
	{
		return CHotKeys::GetInstance()->PrintGlobalUrlVar();
	}

	/**
	 * @deprecated
	 */
	public function GetLang($directory = false, $host = false)
	{
		$context = Main\Context::getCurrent();
		$culture = $context->getCulture();
		$site = $context->getSiteObject();

		return [
			"LID" => ($site ? $site->getLid() : $context->getLanguage()),
			"DIR" => ($site ? $site->getDir() : ''),
			"SERVER_NAME" => ($site ? $site->getServerName() : ''),
			"CHARSET" => $culture->getCharset(),
			"FORMAT_DATE" => $culture->getFormatDate(),
			"FORMAT_DATETIME" => $culture->getFormatDatetime(),
			"LANGUAGE_ID" => $context->getLanguage(),
		];
	}

	/**
	 * @deprecated
	 */
	public function GetSiteByDir($cur_dir=false, $cur_host=false)
	{
		return $this->GetLang($cur_dir, $cur_host);
	}

	public function RestartWorkarea($start = false)
	{
		static $index = null;
		static $view = null;

		

		if ($start)
		{
			$this->AddBufferContent("trim", ""); //Makes a placeholder after header.php
			$index = count($this->buffer_content);
			$view = $this->__view;

			return true;
		}
		elseif (
			!isset($index) //Was not started
			|| !isset($this->buffer_content_type[$index/2-1]) //RestartBuffer was called
			|| $this->buffer_content_type[$index/2-1]["F"] !== "trim"
		)
		{
			return false;
		}
		else
		{
			$autoCompositeArea = Main\Composite\Internals\AutomaticArea::getCurrentArea();
			if ($autoCompositeArea)
			{
				$autoCompositeArea->end();
			}

			$this->buffer_man = true;
			ob_end_clean();
			$this->buffer_man = false;

			array_splice($this->buffer_content, $index);
			array_splice($this->buffer_content_type, $index/2);

			ob_start(array(&$this, "EndBufferContent"));

			$this->__view = $view;

			return true;
		}
	}

	public function AddBufferContent($callback)
	{
		$args = array();
		$args_num = func_num_args();
		if($args_num>1)
			for($i=1; $i<$args_num; $i++)
				$args[] = func_get_arg($i);

		if(!defined("BX_BUFFER_USED") || BX_BUFFER_USED!==true)
		{
			echo call_user_func_array($callback, $args);
			return;
		}
		$this->buffer_content[] = ob_get_contents();
		$this->buffer_content[] = "";
		$this->buffer_content_type[] = array("F"=>$callback, "P"=>$args);
		$this->buffer_man = true;
		$this->auto_buffer_cleaned = false;
		ob_end_clean();
		$this->buffer_man = false;
		$this->buffered = true;
		if($this->auto_buffer_cleaned) // cross buffer fix
			ob_start(array(&$this, "EndBufferContent"));
		else
			ob_start();
	}

	public function RestartBuffer()
	{
		$this->oAsset->setShowHeadString(false);
		$this->oAsset->setShowHeadScript(false);
		$this->buffer_man = true;
		ob_end_clean();
		$this->buffer_man = false;
		$this->buffer_content_type = array();
		$this->buffer_content = array();

		if(function_exists("getmoduleevents"))
		{
			foreach(GetModuleEvents("main", "OnBeforeRestartBuffer", true) as $arEvent)
				ExecuteModuleEventEx($arEvent);
		}

		ob_start(array(&$this, "EndBufferContent"));
	}

	public function &EndBufferContentMan()
	{
		

		$res = null;

		if(!$this->buffered)
			return $res;

		$content = ob_get_contents();
		$this->buffer_man = true;
		ob_end_clean();
		$this->buffered = false;
		$this->buffer_man = false;

		$this->buffer_manual = true;
		$res = $this->EndBufferContent($content);
		$this->buffer_manual = false;

		$this->buffer_content_type = array();
		$this->buffer_content = array();

		return $res;
	}

	public function EndBufferContent($content="")
	{
		if ($this->buffer_man)
		{
			$this->auto_buffer_cleaned = true;
			return "";
		}

		Composite\Engine::checkAdminPanel();

		if (function_exists("getmoduleevents"))
		{
			foreach (GetModuleEvents("main", "OnBeforeEndBufferContent", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array());
			}
		}

		$asset = Asset::getInstance();
		$asset->addString(CJSCore::GetCoreMessagesScript(), false, AssetLocation::AFTER_CSS, AssetMode::STANDARD);
		$asset->addString(CJSCore::GetCoreMessagesScript(true), false, AssetLocation::AFTER_CSS, AssetMode::COMPOSITE);

		$asset->addString($this->GetSpreadCookieHTML(), false, AssetLocation::AFTER_JS, AssetMode::STANDARD);
		if ($asset->canMoveJsToBody() && \CJSCore::IsCoreLoaded())
		{
			$asset->addString(\CJSCore::GetInlineCoreJs(), false, AssetLocation::BEFORE_CSS, AssetMode::ALL);
		}

		if (is_object($GLOBALS["APPLICATION"])) //php 5.1.6 fix: http://bugs.php.net/bug.php?id=40104
		{
			$cnt = count($this->buffer_content_type);
			for ($i = 0; $i < $cnt; $i++)
			{
				$this->buffer_content[$i*2+1] = call_user_func_array($this->buffer_content_type[$i]["F"], $this->buffer_content_type[$i]["P"]);
			}
		}

		$compositeContent = Composite\Engine::startBuffering($content);
		$content = implode("", $this->buffer_content).$content;

		if (function_exists("getmoduleevents"))
		{
			foreach (GetModuleEvents("main", "OnEndBufferContent", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array(&$content));
			}
		}

		$wasContentModified = Composite\Engine::endBuffering($content, $compositeContent);
		if (!$wasContentModified && $asset->canMoveJsToBody())
		{
			$asset->moveJsToBody($content);
		}

		return $content;
	}

	public function ResetException()
	{
		if($this->LAST_ERROR)
			$this->ERROR_STACK[] = $this->LAST_ERROR;
		$this->LAST_ERROR = false;
	}

	public function ThrowException($msg, $id = false)
	{
		$this->ResetException();
		if(is_object($msg) && (is_subclass_of($msg, 'CApplicationException') || (mb_strtolower(get_class($msg)) == 'capplicationexception')))
			$this->LAST_ERROR = $msg;
		else
			$this->LAST_ERROR = new CApplicationException($msg, $id);
	}

	public function GetException()
	{
		return $this->LAST_ERROR;
	}

	/**
	 * @deprecated Use Main\Text\Encoding::convertEncoding()
	 * @param $string
	 * @param $charset_in
	 * @param $charset_out
	 * @return mixed
	 */
	public function ConvertCharset($string, $charset_in, $charset_out)
	{
		return Main\Text\Encoding::convertEncoding($string, $charset_in, $charset_out);
	}

	/**
	 * @deprecated Use Main\Text\Encoding::convertEncoding()
	 * @param $arData
	 * @param $charset_from
	 * @param $charset_to
	 * @return mixed
	 */
	public function ConvertCharsetArray($arData, $charset_from, $charset_to)
	{
		return Main\Text\Encoding::convertEncoding($arData, $charset_from, $charset_to);
	}

	public function CaptchaGetCode()
	{
		$cpt = new CCaptcha();
		$cpt->SetCode();

		return $cpt->GetSID();
	}

	public function CaptchaCheckCode($captcha_word, $captcha_sid)
	{
		$cpt = new CCaptcha();
		if ($cpt->CheckCode($captcha_word, $captcha_sid))
			return true;
		else
			return false;
	}

	public function UnJSEscape($str)
	{
		if(strpos($str, "%u") !== false)
		{
			$str = preg_replace_callback(
				"'%u([0-9A-F]{2})([0-9A-F]{2})'i",
				function ($ch) {
					$res = chr(hexdec($ch[2])).chr(hexdec($ch[1]));
					return $GLOBALS["APPLICATION"]->ConvertCharset($res, "UTF-16", LANG_CHARSET);
				},
				$str
			);
		}
		return $str;
	}

	/**
	 * @deprecated Use CAdminFileDialog::ShowScript instead
	 */
	public static function ShowFileSelectDialog($event, $arResultDest, $arPath = array(), $fileFilter = "", $bAllowFolderSelect = false)
	{
		CAdminFileDialog::ShowScript(array(
			"event" => $event,
			"arResultDest" => $arResultDest,
			"arPath" => $arPath,
			"select" => $bAllowFolderSelect ? 'DF' : 'F',
			"fileFilter" => $fileFilter,
			"operation" => 'O',
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"allowAllFiles" => true,
			"SaveConfig" => true
		));
	}

	/*
	array(
		"URL"=> 'url to open'
		"PARAMS"=> array('param' => 'value') - additional params, 2nd argument of jsPopup.ShowDialog()
	),
	*/
	public function GetPopupLink($arUrl)
	{
		CUtil::InitJSCore(array('window', 'ajax'));

		if (
			class_exists('CUserOptions')
			&& (
				!isset($arUrl['PARAMS'])
				|| !is_array($arUrl['PARAMS'])
				|| !isset($arUrl['PARAMS']['resizable'])
				|| $arUrl['PARAMS']['resizable'] !== false
			)
		)
		{
			$pos = mb_strpos($arUrl['URL'], '?');
			if ($pos === false)
				$check_url = $arUrl['URL'];
			else
				$check_url = mb_substr($arUrl['URL'], 0, $pos);

			if (defined('SITE_TEMPLATE_ID'))
			{
				$arUrl['URL'] = (new Uri($arUrl['URL']))
					->addParams(['siteTemplateId' => SITE_TEMPLATE_ID])
					->getUri()
				;
			}

			$arPos = CUtil::GetPopupSize($check_url);

			if ($arPos['width'])
			{
				if (!is_array($arUrl['PARAMS']))
					$arUrl['PARAMS'] = array();

				$arUrl['PARAMS']['width'] = $arPos['width'];
				$arUrl['PARAMS']['height'] = $arPos['height'];
			}
		}

		$dialog_class = 'CDialog';
		if (isset($arUrl['PARAMS']['dialog_type']) && $arUrl['PARAMS']['dialog_type'])
		{
			switch ($arUrl['PARAMS']['dialog_type'])
			{
				case 'EDITOR': $dialog_class = 'CEditorDialog'; break;
				case 'ADMIN': $dialog_class = 'CAdminDialog'; break;
			}
		}
		elseif (strpos($arUrl['URL'], 'bxpublic=') !== false)
		{
			$dialog_class = 'CAdminDialog';
		}

		$arDialogParams = array(
			'content_url' => $arUrl['URL'],
			'width' => null,
			'height' => null,
		);

		if (isset($arUrl['PARAMS']['width']))
			$arDialogParams['width'] = intval($arUrl['PARAMS']['width']);
		if (isset($arUrl['PARAMS']['height']))
			$arDialogParams['height'] = intval($arUrl['PARAMS']['height']);
		if (isset($arUrl['PARAMS']['min_width']))
			$arDialogParams['min_width'] = intval($arUrl['PARAMS']['min_width']);
		if (isset($arUrl['PARAMS']['min_height']))
			$arDialogParams['min_height'] = intval($arUrl['PARAMS']['min_height']);
		if (isset($arUrl['PARAMS']['resizable']) && $arUrl['PARAMS']['resizable'] === false)
			$arDialogParams['resizable'] = false;
		if (isset($arUrl['POST']) && $arUrl['POST'])
			$arDialogParams['content_post'] = $arUrl['POST'];

		return '(new BX.'.$dialog_class.'('.CUtil::PhpToJsObject($arDialogParams).')).Show()';
	}

	public static function GetServerUniqID()
	{
		static $uniq = null;
		if($uniq === null)
		{
			$uniq = COption::GetOptionString("main", "server_uniq_id", "");
		}
		if($uniq == '')
		{
			$uniq = Main\Security\Random::getString(32);
			COption::SetOptionString("main", "server_uniq_id", $uniq);
		}
		return $uniq;
	}

	public static function PrologActions()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;

		if(!defined("BX_BUFFER_USED") || BX_BUFFER_USED !== true)
		{
			ob_start(array(&$APPLICATION, "EndBufferContent"));
			$APPLICATION->buffered = true;
			define("BX_BUFFER_USED", true);

			register_shutdown_function(
				function()
				{
					define("BX_BUFFER_SHUTDOWN", true);
					for ($i=0, $n = ob_get_level(); $i < $n; $i++)
					{
						ob_end_flush();
					}
				}
			);
		}

		//session expander
		if ((!defined('PUBLIC_AJAX_MODE') || PUBLIC_AJAX_MODE !== true) && (!defined("BX_SKIP_SESSION_EXPAND") || BX_SKIP_SESSION_EXPAND === false))
		{
			if(COption::GetOptionString("main", "session_expand", "Y") <> "N")
			{
				//only for authorized
				if(COption::GetOptionString("main", "session_auth_only", "Y") <> "Y" || $USER->IsAuthorized())
				{
					Main\UI\SessionExpander::init();
				}
			}
		}

		

		//user auto time zone via js cookies
		if(CTimeZone::Enabled() && (!defined("BX_SKIP_TIMEZONE_COOKIE") || BX_SKIP_TIMEZONE_COOKIE === false))
		{
			CTimeZone::SetAutoCookie();
		}

		// check user options set via cookie
		if ($USER->IsAuthorized())
		{
			$cookieName = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LAST_SETTINGS";
			if(!empty($_COOKIE[$cookieName]))
			{
				CUserOptions::SetCookieOptions($cookieName);
			}
		}

		foreach(GetModuleEvents("main", "OnProlog", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent);
		}
	}

	/**
	 * Sends the response and terminates the application.
	 * @param string $output String to output before termination
	 */
	public static function FinalActions($output = "")
	{
		global $APPLICATION;

		if($output === "")
		{
			$output = $APPLICATION->EndBufferContentMan();
		}

		$response = Main\Context::getCurrent()->getResponse();
		$response->setContent($output);

		Main\Application::getInstance()->end();
	}

	/**
	 * @internal
	 */
	public static function RunFinalActionsInternal()
	{
		global $DB;

		if (!defined('BX_WITH_ON_AFTER_EPILOG'))
		{
			define('BX_WITH_ON_AFTER_EPILOG', true);
		}

		$events = GetModuleEvents("main", "OnAfterEpilog", true);

		define("START_EXEC_EVENTS_1", microtime());

		if(!defined('BX_SENDPULL_COUNTER_QUEUE_DISABLE') || BX_SENDPULL_COUNTER_QUEUE_DISABLE !== true)
		{
			$DB->StartUsingMasterOnly();
			if(CUserCounter::CheckLiveMode())
			{
				CUserCounterPage::checkSendCounter();
			}
			$DB->StopUsingMasterOnly();
		}

		define("START_EXEC_EVENTS_2", microtime());

		//OnAfterEpilog
		foreach($events as $event)
		{
			ExecuteModuleEventEx($event);
		}
	}

	/**
	 * @deprecated Will be removed soon
	 */
	public static function EpilogActions()
	{
	}

	/**
	 * @deprecated Use \Bitrix\Main\Application::addBackgroundJob()
	 * @param string|bool $func
	 * @param array $args
	 * @return bool|null
	 */
	public static function ForkActions($func = false, $args = array())
	{
		if($func !== false)
		{
			Main\Application::getInstance()->addBackgroundJob($func, $args);
		}
		return true;
	}

	/** @deprecated */
	public static function __GetConditionFName()
	{
		return "`CONDITION`";
	}
}

class CMain extends CAllMain
{
}
