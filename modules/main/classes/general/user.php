<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Authentication\ApplicationPasswordTable;
use Bitrix\Main\PhoneNumber;

IncludeModuleLangFile(__FILE__);

global $BX_GROUP_POLICY;
$BX_GROUP_POLICY = array(
	"SESSION_TIMEOUT"	=>	0, //minutes
	"SESSION_IP_MASK"	=>	"0.0.0.0",
	"MAX_STORE_NUM"		=>	10,
	"STORE_IP_MASK"		=>	"0.0.0.0",
	"STORE_TIMEOUT"		=>	60*24*365, //minutes
	"CHECKWORD_TIMEOUT"	=>	60*24*365,  //minutes
	"PASSWORD_LENGTH"	=>	false,
	"PASSWORD_UPPERCASE"	=>	"N",
	"PASSWORD_LOWERCASE"	=>	"N",
	"PASSWORD_DIGITS"	=>	"N",
	"PASSWORD_PUNCTUATION"	=>	"N",
	"LOGIN_ATTEMPTS"	=>	0,
	"BLOCK_LOGIN_ATTEMPTS" => 0,
	"BLOCK_TIME" => 0,
);

abstract class CAllUser extends CDBResult
{
	var $LAST_ERROR = "";
	var $bLoginByHash = false;
	protected $admin = null;
	protected static $CURRENT_USER = false;
	protected $justAuthorized = false;
	protected static $userGroupCache = array();

	const STATUS_ONLINE = 'online';
	const STATUS_OFFLINE = 'offline';

	//in seconds
	const PHONE_CODE_OTP_INTERVAL = 30;
	const PHONE_CODE_RESEND_INTERVAL = 60;

	abstract public function Add($arFields);

	public function GetParam($name)
	{
		if(isset($_SESSION["SESS_AUTH"][$name]))
			return $_SESSION["SESS_AUTH"][$name];
		else
			return null;
	}

	public function GetSecurityPolicy()
	{
		if(!is_set($_SESSION["SESS_AUTH"], "POLICY"))
			$_SESSION["SESS_AUTH"]["POLICY"] = CUser::GetGroupPolicy($_SESSION["SESS_AUTH"]["USER_ID"]);
		return $_SESSION["SESS_AUTH"]["POLICY"];
	}

	public function SetParam($name, $value)
	{
		$_SESSION["SESS_AUTH"][$name] = $value;
	}

	public function GetID()
	{
		if(isset($_SESSION["SESS_AUTH"]["USER_ID"]))
			return $_SESSION["SESS_AUTH"]["USER_ID"];
		else
			return null;
	}

	public function GetLogin()
	{
		return $_SESSION["SESS_AUTH"]["LOGIN"];
	}

	public function GetEmail()
	{
		return $_SESSION["SESS_AUTH"]["EMAIL"];
	}

	public function GetFullName()
	{
		return $_SESSION["SESS_AUTH"]["NAME"];
	}

	public function GetFirstName()
	{
		return $_SESSION["SESS_AUTH"]["FIRST_NAME"];
	}

	public function GetLastName()
	{
		return $_SESSION["SESS_AUTH"]["LAST_NAME"];
	}

	public function GetSecondName()
	{
		return $_SESSION["SESS_AUTH"]["SECOND_NAME"];
	}

	public function GetFormattedName($bUseBreaks = true, $bHTMLSpec = true)
	{
		return CUser::FormatName(CSite::GetNameFormat($bUseBreaks),
			array(
				"TITLE" => $this->GetParam("TITLE"),
				"NAME" => $this->GetFirstName(),
				"SECOND_NAME" => $this->GetSecondName(),
				"LAST_NAME" => $this->GetLastName(),
				"LOGIN" => $this->GetLogin(),
			),
			true,
			$bHTMLSpec
		);
	}

	public function GetUserGroupArray()
	{
		if(
			!isset($_SESSION["SESS_AUTH"]["GROUPS"])
			|| !is_array($_SESSION["SESS_AUTH"]["GROUPS"])
			|| empty($_SESSION["SESS_AUTH"]["GROUPS"])
		)
			return array(2);

		//always unique and sorted, containing group ID=2
		return $_SESSION["SESS_AUTH"]["GROUPS"];
	}

	public function SetUserGroupArray($arr)
	{
		$arr = array_map("intval", $arr);
		$arr = array_filter($arr);
		$arr[] = 2;
		$arr = array_values(array_unique($arr));
		sort($arr);
		$_SESSION["SESS_AUTH"]["GROUPS"] = $arr;
	}

	public function GetUserGroupString()
	{
		return $this->GetGroups();
	}

	public function GetGroups()
	{
		return implode(",", $this->GetUserGroupArray());
	}

	public function RequiredHTTPAuthBasic($Realm = "Bitrix")
	{
		header("WWW-Authenticate: Basic realm=\"{$Realm}\"");
		if(stristr(php_sapi_name(), "cgi") !== false)
			header("Status: 401 Unauthorized");
		else
			header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");

		return false;
	}

	public function LoginByCookies()
	{
		global $USER;

		if(COption::GetOptionString("main", "store_password", "Y") == "Y")
		{
			$bLogout = isset($_REQUEST["logout"]) && (strtolower($_REQUEST["logout"]) == "yes");

			$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
			$cookie_login = strval($_COOKIE[$cookie_prefix.'_UIDL']);
			if($cookie_login == '')
			{
				//compatibility reasons
				$cookie_login = strval($_COOKIE[$cookie_prefix.'_LOGIN']);
			}
			$cookie_md5pass = strval($_COOKIE[$cookie_prefix.'_UIDH']);

			if($cookie_login <> '' && $cookie_md5pass <> '' && !$bLogout)
			{
				if($_SESSION["SESS_PWD_HASH_TESTED"] != md5($cookie_login."|".$cookie_md5pass))
				{
					$USER->LoginByHash($cookie_login, $cookie_md5pass);
					$_SESSION["SESS_PWD_HASH_TESTED"] = md5($cookie_login."|".$cookie_md5pass);
				}
			}
		}
	}

	public function LoginByHash($login, $hash)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$result_message = true;
		$user_id = 0;
		$arParams = array(
			"LOGIN" => $login,
			"HASH" => $hash,
		);

		$APPLICATION->ResetException();
		$bOk = true;
		foreach(GetModuleEvents("main", "OnBeforeUserLoginByHash", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$result_message = array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk && $arParams['HASH'] <> '')
		{
			$strSql =
				"SELECT U.ID, U.ACTIVE, U.STORED_HASH, U.EXTERNAL_AUTH_ID ".
				"FROM b_user U ".
				"WHERE U.LOGIN='".$DB->ForSQL($arParams['LOGIN'], 50)."' ";
			$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$bFound = false;
			$bHashFound = false;
			while(($arUser = $result->Fetch()))
			{
				$bFound = true;
				//there is no stored auth for external authorization, but domain spread auth should work
				$bExternal = ($arUser["EXTERNAL_AUTH_ID"] <> '');
				if(
					// if old method (STORED_HASH <> '') and exact match
					($arUser["STORED_HASH"] <> '' && $arUser["STORED_HASH"] == $arParams['HASH'])
					|| // or new method
					(CUser::CheckStoredHash($arUser["ID"], $arParams['HASH'], $bExternal))
				)
				{
					$bHashFound = true;
					if($arUser["ACTIVE"] == "Y")
					{
						$user_id = $arUser["ID"];
						$_SESSION["SESS_AUTH"]["SESSION_HASH"] = $arParams['HASH'];
						$this->bLoginByHash = true;
						$this->Authorize($arUser["ID"], !$bExternal);
					}
					else
					{
						$APPLICATION->ThrowException(GetMessage("LOGIN_BLOCK"));
						$result_message = array("MESSAGE"=>GetMessage("LOGIN_BLOCK")."<br>", "TYPE"=>"ERROR");
					}
					break;
				}
				else
				{
					//Delete invalid stored auth cookie
					$spread = (COption::GetOptionString("main", "auth_multisite", "N") == "Y"? (Main\Web\Cookie::SPREAD_SITES | Main\Web\Cookie::SPREAD_DOMAIN) : Main\Web\Cookie::SPREAD_DOMAIN);

					$cookie = new Main\Web\Cookie("UIDH", "", 0);
					$cookie->setSpread($spread);
					$cookie->setHttpOnly(true);
					Main\Context::getCurrent()->getResponse()->addCookie($cookie);
				}
			}
			if(!$bFound)
			{
				$APPLICATION->ThrowException(GetMessage("WRONG_LOGIN"));
				$result_message = array("MESSAGE"=>GetMessage("WRONG_LOGIN")."<br>", "TYPE"=>"ERROR");
			}
			elseif(!$bHashFound)
			{
				$APPLICATION->ThrowException(GetMessage("USER_WRONG_HASH"));
				$result_message = array("MESSAGE"=>GetMessage("USER_WRONG_HASH")."<br>", "TYPE"=>"ERROR");
			}
		}

		$arParams["USER_ID"] = $user_id;
		$arParams["RESULT_MESSAGE"] = $result_message;

		foreach (GetModuleEvents("main", "OnAfterUserLoginByHash", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arParams));

		if(($result_message !== true) && (COption::GetOptionString("main", "event_log_login_fail", "N") === "Y"))
			CEventLog::Log("SECURITY", "USER_LOGINBYHASH", "main", $login, $result_message["MESSAGE"]);

		return $arParams["RESULT_MESSAGE"];
	}

	public function LoginByHttpAuth()
	{
		$arAuth = CHTTP::ParseAuthRequest();

		foreach(GetModuleEvents("main", "onBeforeUserLoginByHttpAuth", true) as $arEvent)
		{
			$res = ExecuteModuleEventEx($arEvent, array(&$arAuth));
			if($res !== null)
			{
				return $res;
			}
		}

		if(isset($arAuth["basic"]) && $arAuth["basic"]["username"] <> '' && $arAuth["basic"]["password"] <> '')
		{
			// Authorize user, if it is http basic authorization, with no remembering
			if(!$this->IsAuthorized() || $this->GetLogin() <> $arAuth["basic"]["username"])
			{
				return $this->Login($arAuth["basic"]["username"], $arAuth["basic"]["password"], "N");
			}
		}
		elseif(isset($arAuth["digest"]) && $arAuth["digest"]["username"] <> '' && COption::GetOptionString('main', 'use_digest_auth', 'N') == 'Y')
		{
			// Authorize user by http digest authorization
			if(!$this->IsAuthorized() || $this->GetLogin() <> $arAuth["digest"]["username"])
			{
				return $this->LoginByDigest($arAuth["digest"]);
			}
		}

		return null;
	}

	public function LoginByDigest($arDigest)
	{
		//array("username"=>"", "nonce"=>"", "uri"=>"", "response"=>"")
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$APPLICATION->ResetException();

		$strSql =
			"SELECT U.ID, U.PASSWORD, UD.DIGEST_HA1, U.EXTERNAL_AUTH_ID ".
			"FROM b_user U LEFT JOIN b_user_digest UD ON UD.USER_ID=U.ID ".
			"WHERE U.LOGIN='".$DB->ForSQL($arDigest["username"])."' ";
		$res = $DB->Query($strSql);

		if($arUser = $res->Fetch())
		{
			$method = (isset($_SERVER['REDIRECT_REQUEST_METHOD']) ? $_SERVER['REDIRECT_REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD']);
			$HA2 = md5($method.':'.$arDigest['uri']);

			if($arUser["EXTERNAL_AUTH_ID"] == '' && $arUser["DIGEST_HA1"] <> '')
			{
				//digest is for internal authentication only
				$_SESSION["BX_HTTP_DIGEST_ABSENT"] = false;

				$HA1 = $arUser["DIGEST_HA1"];
				$valid_response = md5($HA1.':'.$arDigest['nonce'].':'.$HA2);

				if($arDigest["response"] === $valid_response)
				{
					//regular user password
					return $this->Login($arDigest["username"], $arUser["PASSWORD"], "N", "N");
				}
			}

			//check for an application password, including external users
			if(($appPassword = \Bitrix\Main\Authentication\ApplicationPasswordTable::findDigestPassword($arUser["ID"], $arDigest)) !== false)
			{
				return $this->Login($arDigest["username"], $appPassword["PASSWORD"], "N", "N");
			}

			if($arUser["DIGEST_HA1"] == '')
			{
				//this indicates that we still have no user digest hash
				$_SESSION["BX_HTTP_DIGEST_ABSENT"] = true;
			}
		}

		$APPLICATION->ThrowException(GetMessage("USER_AUTH_DIGEST_ERR"));
		return array("MESSAGE"=>GetMessage("USER_AUTH_DIGEST_ERR")."<br>", "TYPE"=>"ERROR");
	}

	public static function UpdateDigest($ID, $pass)
	{
		global $DB;
		$ID = intval($ID);

		$res = $DB->Query("
			SELECT U.LOGIN, UD.DIGEST_HA1
			FROM b_user U LEFT JOIN b_user_digest UD on UD.USER_ID=U.ID
			WHERE U.ID=".$ID
		);
		if($arRes = $res->Fetch())
		{
			if(defined('BX_HTTP_AUTH_REALM'))
				$realm = BX_HTTP_AUTH_REALM;
			else
				$realm = "Bitrix Site Manager";

			$digest = md5($arRes["LOGIN"].':'.$realm.':'.$pass);

			if($arRes["DIGEST_HA1"] == '')
			{
				//new digest
				$DB->Query("insert into b_user_digest (user_id, digest_ha1) values('".$ID."', '".$DB->ForSQL($digest)."')");
			}
			else
			{
				//update digest (login, password or realm were changed)
				if($arRes["DIGEST_HA1"] !== $digest)
					$DB->Query("update b_user_digest set digest_ha1='".$DB->ForSQL($digest)."' where user_id=".$ID);
			}
		}
	}

	public function LoginHitByHash()
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$hash = trim($_REQUEST["bx_hit_hash"]);
		if ($hash == '')
			return false;

		$APPLICATION->ResetException();

		$strSql =
			"SELECT UH.USER_ID AS USER_ID ".
			"FROM b_user_hit_auth UH ".
			"INNER JOIN b_user U ON U.ID = UH.USER_ID AND U.ACTIVE ='Y' ".
			"WHERE UH.HASH = '".$DB->ForSQL($hash, 32)."' ".
			"	AND '".$DB->ForSqlLike($APPLICATION->GetCurPageParam("", array(), true), 500)."' LIKE ".$DB->Concat("UH.URL", "'%'");

		if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			$strSql .= " AND UH.SITE_ID = '".SITE_ID."'";

		$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($arUser = $result->Fetch())
		{
			setSessionExpired(true);
			$this->Authorize($arUser["USER_ID"], false);

			$DB->Query("UPDATE b_user_hit_auth SET TIMESTAMP_X = ".$DB->GetNowFunction()." WHERE HASH='".$DB->ForSQL($hash, 32)."'");
			return true;
		}
		else
			return false;
	}

	public static function AddHitAuthHash($url, $user_id = false, $site_id = false)
	{
		global $USER, $DB;

		if ($url == '')
			return false;

		if (!$user_id)
			$user_id = $USER->GetID();

		if (!$site_id && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true))
			$site_id = SITE_ID;

		$hash = false;

		if ($user_id)
		{
			$hash = md5(uniqid(rand(), true));
			$arFields = array(
				'USER_ID' => $user_id,
				'URL' => $DB->ForSqlLike(trim($url), 500),
				'HASH' => $hash,
				'SITE_ID' => $DB->ForSQL(trim($site_id), 2),
				'~TIMESTAMP_X'=>$DB->CurrentTimeFunction()
			);
			$DB->Add("b_user_hit_auth", $arFields);
		}

		return $hash;
	}

	public static function GetHitAuthHash($url_mask, $userID = false)
	{
		global $USER, $DB;

		$url_mask = trim($url_mask);
		if ($url_mask == '')
			return false;

		if (!$userID)
		{
			if (!$USER->IsAuthorized())
				return false;
			else
				$userID = $USER->GetID();
		}

		$strSql =
			"SELECT ID, HASH ".
			"FROM b_user_hit_auth ".
			"WHERE URL = '".$DB->ForSqlLike($url_mask, 500)."' AND USER_ID = ".intval($userID);

		$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($arTmp = $result->Fetch())
			return $arTmp["HASH"];
		else
			return false;
	}

	public static function CleanUpHitAuthAgent()
	{
		global $DB;
		$cleanup_days = COption::GetOptionInt("main", "hit_auth_cleanup_days", 30);
		if($cleanup_days > 0)
		{
			$arDate = localtime(time());
			$date = mktime(0, 0, 0, $arDate[4]+1, $arDate[3]-$cleanup_days, 1900+$arDate[5]);
			$DB->Query("DELETE FROM b_user_hit_auth WHERE TIMESTAMP_X <= ".$DB->CharToDateFunction(ConvertTimeStamp($date, "FULL")));

		}
		return "CUser::CleanUpHitAuthAgent();";
	}

	protected function UpdateSessionData($id, $applicationId = null)
	{
		global $DB;

		unset($_SESSION["SESS_OPERATIONS"]);
		unset($_SESSION["MODULE_PERMISSIONS"]);
		$_SESSION["BX_LOGIN_NEED_CAPTCHA"] = false;

		$strSql =
			"SELECT U.* ".
			"FROM b_user U  ".
			"WHERE U.ID='".intval($id)."' ";
		$result = $DB->Query($strSql);

		if($arUser = $result->Fetch())
		{
			$_SESSION["SESS_AUTH"]["AUTHORIZED"] = "Y";
			$_SESSION["SESS_AUTH"]["USER_ID"] = $arUser["ID"];
			$_SESSION["SESS_AUTH"]["LOGIN"] = $arUser["LOGIN"];
			$_SESSION["SESS_AUTH"]["LOGIN_COOKIES"] = $arUser["LOGIN"];
			$_SESSION["SESS_AUTH"]["EMAIL"] = $arUser["EMAIL"];
			$_SESSION["SESS_AUTH"]["PASSWORD_HASH"] = $arUser["PASSWORD"];
			$_SESSION["SESS_AUTH"]["TITLE"] = $arUser["TITLE"];
			$_SESSION["SESS_AUTH"]["NAME"] = $arUser["NAME"].($arUser["NAME"] == '' || $arUser["LAST_NAME"] == ''? "":" ").$arUser["LAST_NAME"];
			$_SESSION["SESS_AUTH"]["FIRST_NAME"] = $arUser["NAME"];
			$_SESSION["SESS_AUTH"]["SECOND_NAME"] = $arUser["SECOND_NAME"];
			$_SESSION["SESS_AUTH"]["LAST_NAME"] = $arUser["LAST_NAME"];
			$_SESSION["SESS_AUTH"]["PERSONAL_PHOTO"] = $arUser["PERSONAL_PHOTO"];
			$_SESSION["SESS_AUTH"]["PERSONAL_GENDER"] = $arUser["PERSONAL_GENDER"];
			$_SESSION["SESS_AUTH"]["PERSONAL_WWW"] = $arUser["PERSONAL_WWW"];
			$_SESSION["SESS_AUTH"]["EXTERNAL_AUTH_ID"] = $arUser["EXTERNAL_AUTH_ID"];
			$_SESSION["SESS_AUTH"]["XML_ID"] = $arUser["XML_ID"];
			$_SESSION["SESS_AUTH"]["ADMIN"] = false;
			$_SESSION["SESS_AUTH"]["POLICY"] = CUser::GetGroupPolicy($arUser["ID"]);
			$_SESSION["SESS_AUTH"]["AUTO_TIME_ZONE"] = trim($arUser["AUTO_TIME_ZONE"]);
			$_SESSION["SESS_AUTH"]["TIME_ZONE"] = $arUser["TIME_ZONE"];
			$_SESSION["SESS_AUTH"]["TIME_ZONE_OFFSET"] = $arUser["TIME_ZONE_OFFSET"];
			$_SESSION["SESS_AUTH"]["APPLICATION_ID"] = $applicationId;
			$_SESSION["SESS_AUTH"]["BX_USER_ID"] = $arUser["BX_USER_ID"];

			// groups
			$_SESSION["SESS_AUTH"]["GROUPS"] = Main\UserTable::getUserGroupIds($arUser["ID"]);

			foreach ($_SESSION["SESS_AUTH"]["GROUPS"] as $groupId)
			{
				if ($groupId == 1)
				{
					$_SESSION["SESS_AUTH"]["ADMIN"] = true;
					break;
				}
			}
			return $arUser;
		}
		return false;
	}

	/**
	 * Performs the user authorization:
	 *    fills session parameters;
	 *    remembers auth;
	 *    spreads auth through sites.
	 * @param int $id An user ID.
	 * @param bool $bSave Save authorization in cookies.
	 * @param bool $bUpdate Update last login information in DB.
	 * @param string|null $applicationId An application password ID.
	 * @return bool
	 */
	public function Authorize($id, $bSave = false, $bUpdate = true, $applicationId = null)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$arUser = $this->UpdateSessionData($id, $applicationId);

		if($arUser !== false)
		{
			self::$CURRENT_USER = false;
			$this->justAuthorized = true;
			$this->SetControllerAdmin(false);

			//sometimes we don't need to update db (REST)
			if($bUpdate)
			{
				$tz = '';
				if(CTimeZone::Enabled())
				{
					if(!CTimeZone::IsAutoTimeZone(trim($arUser["AUTO_TIME_ZONE"])) || CTimeZone::GetCookieValue() !== null)
					{
						$offset = CTimeZone::GetOffset();
						$tz = ', TIME_ZONE_OFFSET = '.$offset;
						$_SESSION["SESS_AUTH"]["TIME_ZONE_OFFSET"] = $offset;
					}
				}

				$bxUid = '';
				if (!empty($_COOKIE['BX_USER_ID']) && preg_match('/^[0-9a-f]{32}$/', $_COOKIE['BX_USER_ID']))
				{
					if ($_COOKIE['BX_USER_ID'] != $arUser['BX_USER_ID'])
					{
						// save new bxuid value
						$bxUid = ", BX_USER_ID = '".$_COOKIE['BX_USER_ID']."'";

						$arUser['BX_USER_ID'] = $_COOKIE['BX_USER_ID'];
						$_SESSION["SESS_AUTH"]["BX_USER_ID"] = $_COOKIE['BX_USER_ID'];
					}
				}

				$DB->Query("
					UPDATE b_user SET
						STORED_HASH = NULL,
						LAST_LOGIN = ".$DB->GetNowFunction().",
						TIMESTAMP_X = TIMESTAMP_X,
						LOGIN_ATTEMPTS = 0
						".$tz."
						".$bxUid."
					WHERE
						ID=".$arUser["ID"]
				);

				if ($bSave || COption::GetOptionString("main", "auth_multisite", "N") == "Y")
				{
					$response = Main\Context::getCurrent()->getResponse();

					$hash = $this->GetSessionHash();
					$secure = (COption::GetOptionString("main", "use_secure_password_cookies", "N")=="Y" && CMain::IsHTTPS());

					if($bSave)
					{
						$period = time()+60*60*24*30*60;
						$spread = Main\Web\Cookie::SPREAD_SITES | Main\Web\Cookie::SPREAD_DOMAIN;
					}
					else
					{
						$period = 0;
						$spread = Main\Web\Cookie::SPREAD_SITES;
					}

					$cookie = new Bitrix\Main\Web\Cookie("UIDH", $hash, $period);

					$cookie->setSecure($secure)
						->setSpread($spread)
						->setHttpOnly(true);

					$response->addCookie($cookie);

					$cookie = new Bitrix\Main\Web\Cookie("UIDL", $arUser["LOGIN"], $period);

					$cookie->setSecure($secure)
						->setSpread($spread)
						->setHttpOnly(true);

					$response->addCookie($cookie);

					$stored_id = CUser::CheckStoredHash($arUser["ID"], $hash);
					if($stored_id)
					{
						$DB->Query(
							"UPDATE b_user_stored_auth SET
								LAST_AUTH=".$DB->CurrentTimeFunction().",
								".($this->bLoginByHash?"":"TEMP_HASH='".($bSave?"N":"Y")."', ")."
								IP_ADDR='".sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"]))."'
							WHERE ID=".$stored_id
						);
					}
					else
					{
						$arFields = array(
							'USER_ID'=>$arUser["ID"],
							'~DATE_REG'=>$DB->CurrentTimeFunction(),
							'~LAST_AUTH'=>$DB->CurrentTimeFunction(),
							'TEMP_HASH'=>($bSave?"N":"Y"),
							'~IP_ADDR'=>sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"])),
							'STORED_HASH'=>$hash
						);
						$stored_id = $DB->Add("b_user_stored_auth", $arFields);
					}
					$_SESSION["SESS_AUTH"]["STORED_AUTH_ID"] = $stored_id;
				}

				if(COption::GetOptionString("main", "event_log_login_success", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_AUTHORIZE", "main", $arUser["ID"], $applicationId);
			}

			$this->admin = null;

			$arParams = array(
				"user_fields" => $arUser,
				"save" => $bSave,
				"update" => $bUpdate,
				"applicationId" => $applicationId,
			);

			foreach (GetModuleEvents("main", "OnAfterUserAuthorize", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arParams));

			foreach (GetModuleEvents("main", "OnUserLogin", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($_SESSION["SESS_AUTH"]["USER_ID"], $arParams));

			if($bUpdate)
			{
				Main\Composite\Engine::onUserLogin();
			}

			return true;
		}
		return false;
	}

	public function GetSessionHash()
	{
		if($_SESSION["SESS_AUTH"]["SESSION_HASH"] == '')
		{
			$_SESSION["SESS_AUTH"]["SESSION_HASH"] = md5(CMain::GetServerUniqID().uniqid("", true));
		}
		return $_SESSION["SESS_AUTH"]["SESSION_HASH"];
	}

	/** @deprecated */
	public function GetPasswordHash($PASSWORD_HASH)
	{
		$add = COption::GetOptionString("main", "pwdhashadd", "");
		if($add == '')
		{
			$add = md5(uniqid(rand(), true));
			COption::SetOptionString("main", "pwdhashadd", $add);
		}

		return md5($add.$PASSWORD_HASH);
	}

	/** @deprecated */
	public function SavePasswordHash()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$hash = $this->GetSessionHash();
		$time = time()+60*60*24*30*60;
		$secure = (COption::GetOptionString("main", "use_secure_password_cookies", "N")=="Y" && CMain::IsHTTPS());
		$spread = (COption::GetOptionString("main", "auth_multisite", "N") == "Y"? (Main\Web\Cookie::SPREAD_SITES | Main\Web\Cookie::SPREAD_DOMAIN) : Main\Web\Cookie::SPREAD_DOMAIN);

		$cookie = new Main\Web\Cookie("UIDH", $hash, $time);

		$cookie->setSpread($spread)
			->setSecure($secure)
			->setHttpOnly(true);

		Main\Context::getCurrent()->getResponse()->addCookie($cookie);
	}

	/**
	 * Authenticates the user and then authorizes him
	 * @param string $login
	 * @param string $password
	 * @param string $remember
	 * @param string $password_original
	 * @return array|bool
	 */
	public function Login($login, $password, $remember="N", $password_original="Y")
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$result_message = true;
		$user_id = 0;
		$applicationId = null;
		$applicationPassId = null;

		$arParams = array(
			"LOGIN" => &$login,
			"PASSWORD" => &$password,
			"REMEMBER" => &$remember,
			"PASSWORD_ORIGINAL" => &$password_original,
		);

		unset($_SESSION["SESS_OPERATIONS"]);
		unset($_SESSION["MODULE_PERMISSIONS"]);
		$_SESSION["BX_LOGIN_NEED_CAPTCHA"] = false;

		$bOk = true;
		$APPLICATION->ResetException();
		foreach(GetModuleEvents("main", "OnBeforeUserLogin", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
				{
					$result_message = array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				}
				else
				{
					$APPLICATION->ThrowException("Unknown login error");
					$result_message = array("MESSAGE"=>"Unknown login error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			//external authentication
			foreach(GetModuleEvents("main", "OnUserLoginExternal", true) as $arEvent)
			{
				$user_id = ExecuteModuleEventEx($arEvent, array(&$arParams));

				if(isset($arParams["RESULT_MESSAGE"]))
				{
					$result_message = $arParams["RESULT_MESSAGE"];
				}
				if($user_id > 0)
				{
					break;
				}
			}

			if($user_id <= 0)
			{
				//internal authentication OR application password for external user

				$user_id = self::LoginInternal($arParams, $result_message, $applicationId, $applicationPassId);

				if($user_id <= 0)
				{
					//no user found by login - try to find an external user
					foreach(GetModuleEvents("main", "OnFindExternalUser", true) as $arEvent)
					{
						if(($external_user_id = intval(ExecuteModuleEventEx($arEvent, array($arParams["LOGIN"])))) > 0)
						{
							//external user authentication
							//let's try to find application password for the external user
							if(($appPassword = ApplicationPasswordTable::findPassword($external_user_id, $arParams["PASSWORD"], ($arParams["PASSWORD_ORIGINAL"] == "Y"))) !== false)
							{
								//bingo, the user has the application password
								$user_id = $external_user_id;
								$applicationId = $appPassword["APPLICATION_ID"];
								$applicationPassId = $appPassword["ID"];
							}
							break;
						}
					}
				}

				if($user_id <= 0 && $result_message === true)
				{
					$APPLICATION->ThrowException(GetMessage("WRONG_LOGIN"));
					$result_message = array("MESSAGE"=>GetMessage("WRONG_LOGIN")."<br>", "TYPE"=>"ERROR", "ERROR_TYPE" => "LOGIN");
				}
			}
		}

		// All except Admin
		if ($user_id > 1 && $arParams["CONTROLLER_ADMIN"] !== "Y")
		{
			if(!static::CheckUsersCount($user_id))
			{
				$user_id = 0;
				$APPLICATION->ThrowException(GetMessage("LIMIT_USERS_COUNT"));
				$result_message = array(
					"MESSAGE" => GetMessage("LIMIT_USERS_COUNT")."<br>",
					"TYPE" => "ERROR",
				);
			}
		}

		$arParams["USER_ID"] = $user_id;

		$doAuthorize = true;

		if($user_id > 0)
		{
			if($applicationId === null && CModule::IncludeModule("security"))
			{
				/*
				MFA can allow or disallow authorization.
				Allowed if:
				- OTP is not active for the user;
				- correct "OTP" in the $arParams (filled by the OnBeforeUserLogin event handler).
				Disallowed if:
				- OTP is not provided;
				- OTP is not correct.
				When authorization is disallowed the OTP form will be shown on the next hit.
				Note: there is no MFA check for an application password.
				*/

				$arParams["CAPTCHA_WORD"] = $_REQUEST["captcha_word"];
				$arParams["CAPTCHA_SID"] = $_REQUEST["captcha_sid"];

				$doAuthorize = \Bitrix\Security\Mfa\Otp::verifyUser($arParams);
			}

			if($doAuthorize)
			{
				$this->Authorize($user_id, ($arParams["REMEMBER"] == "Y"), true, $applicationId);

				if($applicationPassId !== null)
				{
					//update usage statistics for the application
					Main\Authentication\ApplicationPasswordTable::update($applicationPassId, array(
						'DATE_LOGIN' => new Main\Type\DateTime(),
						'LAST_IP' => $_SERVER["REMOTE_ADDR"],
					));
				}
			}
			else
			{
				$result_message = false;
			}

			if($applicationId === null && $arParams["LOGIN"] <> '')
			{
				//the cookie is for authentication forms mostly, does not make sense for applications
				$cookie = new Bitrix\Main\Web\Cookie("LOGIN", $arParams["LOGIN"], time()+60*60*24*30*60);
				Main\Context::getCurrent()->getResponse()->addCookie($cookie);
			}
		}

		$arParams["RESULT_MESSAGE"] = $result_message;

		$APPLICATION->ResetException();
		foreach(GetModuleEvents("main", "OnAfterUserLogin", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arParams));

		if($doAuthorize == true && $result_message !== true && (COption::GetOptionString("main", "event_log_login_fail", "N") === "Y"))
			CEventLog::Log("SECURITY", "USER_LOGIN", "main", $login, $result_message["MESSAGE"]);

		return $arParams["RESULT_MESSAGE"];
	}

	/**
	 * Internal authentication by login and password.
	 * @param array $arParams
	 * @param array|bool $result_message
	 * @param string|null $applicationId
	 * @param string|null $applicationPassId
	 * @return int User ID on success or 0 on failure. Additionally, $result_message will hold an error.
	 */
	public static function LoginInternal(&$arParams, &$result_message = true, &$applicationId = null, &$applicationPassId = null)
	{
		global $DB, $APPLICATION;

		$user_id = 0;

		$strSql =
			"SELECT U.ID, U.LOGIN, U.ACTIVE, U.PASSWORD, U.LOGIN_ATTEMPTS, U.CONFIRM_CODE, U.EMAIL ".
			"FROM b_user U  ".
			"WHERE U.LOGIN='".$DB->ForSQL($arParams["LOGIN"])."' ";

		if(isset($arParams["EXTERNAL_AUTH_ID"]) && $arParams["EXTERNAL_AUTH_ID"] <> '')
		{
			//external user
			$strSql .= " AND EXTERNAL_AUTH_ID='".$DB->ForSql($arParams["EXTERNAL_AUTH_ID"])."'";
		}
		else
		{
			//internal user (by default)
			$strSql .= " AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') ";
		}

		$result = $DB->Query($strSql);

		if(($arUser = $result->Fetch()))
		{
			if(strlen($arUser["PASSWORD"]) > 32)
			{
				$salt = substr($arUser["PASSWORD"], 0, strlen($arUser["PASSWORD"]) - 32);
				$db_password = substr($arUser["PASSWORD"], -32);
			}
			else
			{
				$salt = "";
				$db_password = $arUser["PASSWORD"];
			}

			$user_password_no_otp = "";
			if($arParams["PASSWORD_ORIGINAL"] == "Y")
			{
				$user_password = md5($salt.$arParams["PASSWORD"]);
				if($arParams["OTP"] <> '')
				{
					$user_password_no_otp = md5($salt.substr($arParams["PASSWORD"], 0, -6));
				}
			}
			else
			{
				if(strlen($arParams["PASSWORD"]) > 32)
				{
					$user_password = substr($arParams["PASSWORD"], -32);
				}
				else
				{
					$user_password = $arParams["PASSWORD"];
				}
			}

			$passwordCorrect = ($db_password === $user_password || ($arParams["OTP"] <> '' && $db_password === $user_password_no_otp));

			if($db_password === $user_password)
			{
				//this password has no added otp for sure
				$arParams["OTP"] = '';
			}

			if(!$passwordCorrect)
			{
				//let's try to find application password
				if(($appPassword = ApplicationPasswordTable::findPassword($arUser["ID"], $arParams["PASSWORD"], ($arParams["PASSWORD_ORIGINAL"] == "Y"))) !== false)
				{
					$passwordCorrect = true;
					$applicationId = $appPassword["APPLICATION_ID"];
					$applicationPassId = $appPassword["ID"];
				}
			}

			$arPolicy = CUser::GetGroupPolicy($arUser["ID"]);
			$usr_login_attempts = intval($arUser["LOGIN_ATTEMPTS"]) + 1;

			//show captcha after a serial of incorrect login attempts
			$pol_login_attempts = intval($arPolicy["LOGIN_ATTEMPTS"]);
			if($pol_login_attempts > 0 && $usr_login_attempts > $pol_login_attempts)
			{
				$_SESSION["BX_LOGIN_NEED_CAPTCHA"] = true;
				if(!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
				{
					$passwordCorrect = false;
				}
			}

			//deactivate the user after numerous of incorrect login attempts
			$policyBlockAttempts = intval($arPolicy["BLOCK_LOGIN_ATTEMPTS"]);
			$policyBlockTime = intval($arPolicy["BLOCK_TIME"]);
			if($policyBlockAttempts > 0 && $policyBlockTime > 0 && $usr_login_attempts > $policyBlockAttempts)
			{
				$passwordCorrect = false;

				if($arUser["ACTIVE"] == "Y")
				{
					$user = new CUser;
					$user->Update($arUser["ID"], ["ACTIVE" => "N"], false);

					$unblockDate = new Main\Type\DateTime();
					$unblockDate->add("T{$policyBlockTime}M"); //minutes

					CAgent::AddAgent("CUser::UnblockAgent({$arUser["ID"]});", "main", "Y", 0, "", "Y", $unblockDate->toString());
				}
			}

			if($passwordCorrect)
			{
				if($salt == '' && $arParams["PASSWORD_ORIGINAL"] == "Y" && $applicationId === null)
				{
					$salt = randString(8, array(
						"abcdefghijklnmopqrstuvwxyz",
						"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
						"0123456789",
						",.<>/?;:[]{}\\|~!@#\$%^&*()-_+=",
					));
					$new_password = $salt.md5($salt.$arParams["PASSWORD"]);
					$DB->Query("UPDATE b_user SET PASSWORD='".$DB->ForSQL($new_password)."', TIMESTAMP_X = TIMESTAMP_X WHERE ID = ".intval($arUser["ID"]));
				}

				if($arUser["ACTIVE"] == "Y")
				{
					$user_id = $arUser["ID"];

					//update digest hash for http digest authorization
					if($arParams["PASSWORD_ORIGINAL"] == "Y" && $applicationId === null && COption::GetOptionString('main', 'use_digest_auth', 'N') == 'Y')
					{
						CUser::UpdateDigest($arUser["ID"], $arParams["PASSWORD"]);
					}
				}
				else
				{
					//something wrong with the inactive user
					if($arUser["CONFIRM_CODE"] <> '')
					{
						//unconfirmed email registration
						$message = GetMessage("MAIN_LOGIN_EMAIL_CONFIRM", array("#EMAIL#" => $arUser["EMAIL"]));
					}
					else
					{
						//user blocked
						$message = GetMessage("LOGIN_BLOCK");

						//or possibly unconfirmed phone registration
						if(COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y")
						{
							$row = Main\UserPhoneAuthTable::getRowById($arUser["ID"]);
							if($row && $row["CONFIRMED"] == 'N')
							{
								$message = GetMessage("main_login_need_phone_confirmation", array("#PHONE#" => $row["PHONE_NUMBER"]));
							}
						}
					}
					$APPLICATION->ThrowException($message);
					$result_message = array("MESSAGE" => $message."<br>", "TYPE" => "ERROR");
				}
			}
			else
			{
				$DB->Query("UPDATE b_user SET LOGIN_ATTEMPTS = ".$usr_login_attempts.", TIMESTAMP_X = TIMESTAMP_X WHERE ID = ".intval($arUser["ID"]));
				$APPLICATION->ThrowException(GetMessage("WRONG_LOGIN"));
				$result_message = array("MESSAGE" => GetMessage("WRONG_LOGIN")."<br>", "TYPE" => "ERROR", "ERROR_TYPE" => "LOGIN");
			}
		}
		return $user_id;
	}

	protected static function CheckUsersCount($user_id)
	{
		$limitUsersCount = intval(COption::GetOptionInt("main", "PARAM_MAX_USERS", 0));
		if ($limitUsersCount > 0)
		{
			$by = "ID";
			$order = "ASC";
			$arFilter = array("LAST_LOGIN_1" => ConvertTimeStamp());

			//Intranet users only
			$intranet = IsModuleInstalled("intranet");
			if ($intranet)
			{
				$arFilter["!=UF_DEPARTMENT"] = false;
			}

			$rsUsers = CUser::GetList($by, $order, $arFilter, array("FIELDS" => array("ID")));

			while ($user = $rsUsers->fetch())
			{
				if ($user["ID"] == $user_id)
				{
					$limitUsersCount = 1;
					break;
				}
				$limitUsersCount--;
			}

			if ($limitUsersCount <= 0)
			{
				if($intranet)
				{
					//only intranet users are NOT allowed
					$currUserRs = CUser::GetByID($user_id);
					if($currUser = $currUserRs->Fetch())
					{
						if(!empty($currUser["UF_DEPARTMENT"]))
						{
							return false;
						}
					}
				}
				else
				{
					return false;
				}
			}
		}
		return true;
	}

	public function LoginByOtp($otp, $remember_otp = "N", $captcha_word = "", $captcha_sid = "")
	{
		if(!CModule::IncludeModule("security") || !\Bitrix\Security\Mfa\Otp::isOtpRequired())
		{
			return array("MESSAGE" => GetMessage("USER_LOGIN_OTP_ERROR")."<br>", "TYPE" => "ERROR");
		}

		$userParams = \Bitrix\Security\Mfa\Otp::getDeferredParams();

		$userParams["OTP"] = $otp;
		$userParams["OTP_REMEMBER"] = ($remember_otp === "Y");
		$userParams["CAPTCHA_WORD"] = $captcha_word;
		$userParams["CAPTCHA_SID"] = $captcha_sid;

		if(!\Bitrix\Security\Mfa\Otp::verifyUser($userParams))
		{
			return array("MESSAGE" => GetMessage("USER_LOGIN_OTP_INCORRECT")."<br>", "TYPE" => "ERROR");
		}

		$this->Authorize($userParams["USER_ID"], ($userParams["REMEMBER"] == "Y"));
		return true;
	}

	public function AuthorizeWithOtp($user_id)
	{
		$doAuthorize = true;

		if(CModule::IncludeModule("security"))
		{
			/*
			MFA can allow or disallow authorization.
			Allowed only if:
			- OTP is not active for the user;
			When authorization is disallowed the OTP form will be shown on the next hit.
			*/
			$doAuthorize = \Bitrix\Security\Mfa\Otp::verifyUser(array("USER_ID" => $user_id));
		}

		if($doAuthorize)
		{
			return $this->Authorize($user_id);
		}

		return false;
	}

	public function ChangePassword($LOGIN, $CHECKWORD, $PASSWORD, $CONFIRM_PASSWORD, $SITE_ID=false, $captcha_word = "", $captcha_sid = 0, $authActions = true, $phoneNumber = "")
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$arParams = array(
			"LOGIN" => &$LOGIN,
			"CHECKWORD" => &$CHECKWORD,
			"PASSWORD" => &$PASSWORD,
			"CONFIRM_PASSWORD" => &$CONFIRM_PASSWORD,
			"SITE_ID" => &$SITE_ID,
			"PHONE_NUMBER" => &$phoneNumber,
		);

		$APPLICATION->ResetException();
		foreach(GetModuleEvents("main", "OnBeforeUserChangePassword", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams)) === false)
			{
				if($err = $APPLICATION->GetException())
				{
					return array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				}
				return array("MESSAGE"=>GetMessage("main_change_pass_error")."<br>", "TYPE"=>"ERROR");
			}
		}

		if(COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y")
		{
			if (!($APPLICATION->CaptchaCheckCode($captcha_word, $captcha_sid)))
			{
				return array("MESSAGE"=>GetMessage("main_user_captcha_error")."<br>", "TYPE"=>"ERROR");
			}
		}

		$phoneAuth = ($arParams["PHONE_NUMBER"] <> '' && COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y");

		$strAuthError = "";
		if(strlen($arParams["LOGIN"]) < 3 && !$phoneAuth)
		{
			$strAuthError .= GetMessage('MIN_LOGIN')."<br>";
		}
		if($arParams["PASSWORD"] <> $arParams["CONFIRM_PASSWORD"])
		{
			$strAuthError .= GetMessage('WRONG_CONFIRMATION')."<br>";
		}
		if($strAuthError <> '')
		{
			return array("MESSAGE"=>$strAuthError, "TYPE"=>"ERROR");
		}

		$updateFields = array(
			"PASSWORD" => $arParams["PASSWORD"],
		);

		$res = [];
		if($phoneAuth)
		{
			$userId = self::VerifyPhoneCode($arParams["PHONE_NUMBER"], $arParams["CHECKWORD"]);

			if(!$userId)
			{
				return array("MESSAGE" => GetMessage("main_change_pass_code_error"), "TYPE" => "ERROR");
			}

			//activate user after phone number confirmation
			$updateFields["ACTIVE"] = "Y";
		}
		else
		{
			CTimeZone::Disable();
			$db_check = $DB->Query(
				"SELECT ID, LID, CHECKWORD, ".$DB->DateToCharFunction("CHECKWORD_TIME", "FULL")." as CHECKWORD_TIME ".
				"FROM b_user ".
				"WHERE LOGIN='".$DB->ForSql($arParams["LOGIN"], 0)."'".
				(
					// $arParams["EXTERNAL_AUTH_ID"] can be changed in the OnBeforeUserChangePassword event
					$arParams["EXTERNAL_AUTH_ID"] <> ''?
						"	AND EXTERNAL_AUTH_ID='".$DB->ForSQL($arParams["EXTERNAL_AUTH_ID"])."' " :
						"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') "
				)
			);
			CTimeZone::Enable();

			if(!($res = $db_check->Fetch()))
			{
				return array("MESSAGE"=>preg_replace("/#LOGIN#/i", htmlspecialcharsbx($arParams["LOGIN"]), GetMessage('LOGIN_NOT_FOUND')), "TYPE"=>"ERROR", "FIELD" => "LOGIN");
			}

			$userId = $res["ID"];
		}

		$arPolicy = CUser::GetGroupPolicy($userId);

		$passwordErrors = self::CheckPasswordAgainstPolicy($arParams["PASSWORD"], $arPolicy);
		if (!empty($passwordErrors))
		{
			return array(
				"MESSAGE" => implode("<br>", $passwordErrors)."<br>",
				"TYPE" => "ERROR"
			);
		}

		if(!$phoneAuth)
		{
			$salt = substr($res["CHECKWORD"], 0, 8);
			if($res["CHECKWORD"] == '' || $res["CHECKWORD"] != $salt.md5($salt.$arParams["CHECKWORD"]))
			{
				return array("MESSAGE"=>preg_replace("/#LOGIN#/i", htmlspecialcharsbx($arParams["LOGIN"]), GetMessage("CHECKWORD_INCORRECT"))."<br>", "TYPE"=>"ERROR", "FIELD"=>"CHECKWORD");
			}

			$site_format = CSite::GetDateFormat();
			if(time()-$arPolicy["CHECKWORD_TIMEOUT"]*60 > MakeTimeStamp($res["CHECKWORD_TIME"], $site_format))
			{
				return array("MESSAGE"=>preg_replace("/#LOGIN#/i", htmlspecialcharsbx($arParams["LOGIN"]), GetMessage("CHECKWORD_EXPIRE"))."<br>", "TYPE"=>"ERROR", "FIELD"=>"CHECKWORD_EXPIRE");
			}

			if($arParams["SITE_ID"] === false)
			{
				if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
					$arParams["SITE_ID"] = CSite::GetDefSite($res["LID"]);
				else
					$arParams["SITE_ID"] = SITE_ID;
			}
		}

		// change the password
		$obUser = new CUser;
		$res = $obUser->Update($userId, $updateFields, $authActions);
		if(!$res && $obUser->LAST_ERROR <> '')
		{
			return array("MESSAGE"=>$obUser->LAST_ERROR."<br>", "TYPE"=>"ERROR");
		}

		if($phoneAuth)
		{
			return array("MESSAGE"=>GetMessage("main_change_pass_changed")."<br>", "TYPE"=>"OK");
		}
		else
		{
			CUser::SendUserInfo($userId, $arParams["SITE_ID"], GetMessage('CHANGE_PASS_SUCC'), true, 'USER_PASS_CHANGED');

			return array("MESSAGE"=>GetMessage('PASSWORD_CHANGE_OK')."<br>", "TYPE"=>"OK");
		}
	}

	public static function GeneratePasswordByPolicy(array $groups)
	{
		$arPolicy = self::GetGroupPolicy($groups);

		$password_min_length = intval($arPolicy["PASSWORD_LENGTH"]);
		if($password_min_length <= 0)
			$password_min_length = 6;
		$password_chars = array(
			"abcdefghijklnmopqrstuvwxyz",
			"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
			"0123456789",
		);
		if($arPolicy["PASSWORD_PUNCTUATION"] === "Y")
			$password_chars[] = ",.<>/?;:'\"[]{}\\|`~!@#\$%^&*()-_+=";

		return randString($password_min_length, $password_chars);
	}

	public static function CheckPasswordAgainstPolicy($password, $arPolicy)
	{
		$errors = array();

		$password_min_length = intval($arPolicy["PASSWORD_LENGTH"]);
		if($password_min_length <= 0)
			$password_min_length = 6;
		if(strlen($password) < $password_min_length)
			$errors[] = GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_LENGTH", array("#LENGTH#" => $arPolicy["PASSWORD_LENGTH"]));

		if(($arPolicy["PASSWORD_UPPERCASE"] === "Y") && !preg_match("/[A-Z]/", $password))
			$errors[] = GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_UPPERCASE");

		if(($arPolicy["PASSWORD_LOWERCASE"] === "Y") && !preg_match("/[a-z]/", $password))
			$errors[] = GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_LOWERCASE");

		if(($arPolicy["PASSWORD_DIGITS"] === "Y") && !preg_match("/[0-9]/", $password))
			$errors[] = GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_DIGITS");

		if(($arPolicy["PASSWORD_PUNCTUATION"] === "Y") && !preg_match("/[,.<>\\/?;:'\"[\\]\\{\\}\\\\|`~!@#\$%^&*()_+=-]/", $password))
			$errors[] = GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_PUNCTUATION");

		return $errors;
	}

	/**
	 * Sends a profile information to email
	 */
	public static function SendUserInfo($ID, $SITE_ID, $MSG, $bImmediate=false, $eventName="USER_INFO")
	{
		global $DB;

		$arParams = [
			"ID" => $ID,
			"SITE_ID" => $SITE_ID,
		];

		foreach(GetModuleEvents("main", "OnBeforeSendUserInfo", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams)) === false)
			{
				return;
			}
		}

		// change CHECKWORD
		$ID = intval($ID);
		$salt = randString(8);
		$checkword = md5(CMain::GetServerUniqID().uniqid());
		$strSql = "UPDATE b_user SET ".
			"	CHECKWORD = '".$salt.md5($salt.$checkword)."', ".
			"	CHECKWORD_TIME = ".$DB->CurrentTimeFunction().", ".
			"	LID = '".$DB->ForSql($SITE_ID, 2)."', ".
			"   TIMESTAMP_X = TIMESTAMP_X ".
			"WHERE ID = '".$ID."'".
			(
				// $arParams["EXTERNAL_AUTH_ID"] can be changed in the OnBeforeSendUserInfo event
				$arParams["EXTERNAL_AUTH_ID"] <> ''?
					"	AND EXTERNAL_AUTH_ID='".$DB->ForSQL($arParams["EXTERNAL_AUTH_ID"])."' " :
					"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') "
			);

		$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$res = $DB->Query(
			"SELECT u.* ".
			"FROM b_user u ".
			"WHERE ID='".$ID."'".
			(
				$arParams["EXTERNAL_AUTH_ID"] <> ''?
					"	AND EXTERNAL_AUTH_ID='".$DB->ForSQL($arParams["EXTERNAL_AUTH_ID"])."' " :
					"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') "
			)
		);

		if($res_array = $res->Fetch())
		{
			$event = new CEvent;
			$arFields = array(
				"USER_ID"=>$res_array["ID"],
				"STATUS"=>($res_array["ACTIVE"]=="Y"?GetMessage("STATUS_ACTIVE"):GetMessage("STATUS_BLOCKED")),
				"MESSAGE"=>$MSG,
				"LOGIN"=>$res_array["LOGIN"],
				"URL_LOGIN"=>urlencode($res_array["LOGIN"]),
				"CHECKWORD"=>$checkword,
				"NAME"=>$res_array["NAME"],
				"LAST_NAME"=>$res_array["LAST_NAME"],
				"EMAIL"=>$res_array["EMAIL"]
			);

			$arParams = array(
				"FIELDS" => &$arFields,
				"USER_FIELDS" => $res_array,
				"SITE_ID" => &$SITE_ID,
				"EVENT_NAME" => &$eventName,
			);

			foreach (GetModuleEvents("main", "OnSendUserInfo", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(&$arParams));

			if (!$bImmediate)
				$event->Send($eventName, $SITE_ID, $arFields, "Y", "", array(), $res_array["LANGUAGE_ID"]);
			else
				$event->SendImmediate($eventName, $SITE_ID, $arFields, "Y", "", array(), $res_array["LANGUAGE_ID"]);
		}
	}

	public static function SendPassword($LOGIN, $EMAIL, $SITE_ID = false, $captcha_word = "", $captcha_sid = 0, $phoneNumber = "")
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$arParams = array(
			"LOGIN" => $LOGIN,
			"EMAIL" => $EMAIL,
			"SITE_ID" => $SITE_ID,
			"PHONE_NUMBER" => $phoneNumber,
		);

		$result_message = array("MESSAGE"=>GetMessage('ACCOUNT_INFO_SENT')."<br>", "TYPE"=>"OK");
		$APPLICATION->ResetException();
		$bOk = true;
		foreach(GetModuleEvents("main", "OnBeforeUserSendPassword", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");

				$bOk = false;
				break;
			}
		}

		if($bOk && COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y")
		{
			if (!($APPLICATION->CaptchaCheckCode($captcha_word, $captcha_sid)))
			{
				$result_message = array("MESSAGE"=>GetMessage("main_user_captcha_error")."<br>", "TYPE"=>"ERROR");
				$bOk = false;
			}
		}

		if($bOk)
		{
			$f = false;
			if($arParams["PHONE_NUMBER"] <> '' && COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y")
			{
				//user registered by phone number
				$number = Main\UserPhoneAuthTable::normalizePhoneNumber($arParams["PHONE_NUMBER"]);

				$select = ["USER_ID"];
				if($arParams["SITE_ID"] === false)
				{
					$select["LID"] = "USER.LID";
				}

				$row = Main\UserPhoneAuthTable::getList([
					"select" => $select,
					"filter" => ["=PHONE_NUMBER" => $number],
				])->fetch();

				if($row)
				{
					$f = true;

					if($arParams["SITE_ID"] === false)
					{
						$arParams["SITE_ID"] = CSite::GetDefSite($row["LID"]);
					}

					list($code, $number) = CUser::GeneratePhoneCode($row["USER_ID"]);

					$sms = new Main\Sms\Event(
						"SMS_USER_RESTORE_PASSWORD",
						[
							"USER_PHONE" => $number,
							"CODE" => $code,
						]
					);
					$sms->setSite($arParams["SITE_ID"]);
					$smsResult = $sms->send(true);

					if($smsResult->isSuccess())
					{
						$result_message = array("MESSAGE"=>GetMessage("main_user_pass_request_sent")."<br>", "TYPE"=>"OK", "TEMPLATE" => "SMS_USER_RESTORE_PASSWORD");
					}
					else
					{
						$result_message = array("MESSAGE"=>implode("<br>", $smsResult->getErrorMessages()), "TYPE"=>"ERROR");
					}

					if(COption::GetOptionString("main", "event_log_password_request", "N") === "Y")
					{
						CEventLog::Log("SECURITY", "USER_INFO", "main", $row["USER_ID"]);
					}
				}
			}
			elseif($arParams["LOGIN"] <> '' || $arParams["EMAIL"] <> '')
			{
				$confirmation = (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y");

				$strSql = "";
				if($arParams["LOGIN"] <> '')
				{
					$strSql =
						"SELECT ID, LID, ACTIVE, CONFIRM_CODE, LOGIN, EMAIL, NAME, LAST_NAME, LANGUAGE_ID ".
						"FROM b_user u ".
						"WHERE LOGIN='".$DB->ForSQL($arParams["LOGIN"])."' ".
						"	AND (ACTIVE='Y' OR NOT(CONFIRM_CODE IS NULL OR CONFIRM_CODE='')) ".
						(
							// $arParams["EXTERNAL_AUTH_ID"] can be changed in the OnBeforeUserSendPassword event
							$arParams["EXTERNAL_AUTH_ID"] <> ''?
								"	AND EXTERNAL_AUTH_ID='".$DB->ForSQL($arParams["EXTERNAL_AUTH_ID"])."' " :
								"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') "
						);
				}
				if($arParams["EMAIL"] <> '')
				{
					if($strSql <> '')
					{
						$strSql .= "\nUNION\n";
					}
					$strSql .=
						"SELECT ID, LID, ACTIVE, CONFIRM_CODE, LOGIN, EMAIL, NAME, LAST_NAME, LANGUAGE_ID ".
						"FROM b_user u ".
						"WHERE EMAIL='".$DB->ForSQL($arParams["EMAIL"])."' ".
						"	AND (ACTIVE='Y' OR NOT(CONFIRM_CODE IS NULL OR CONFIRM_CODE='')) ".
						(
							$arParams["EXTERNAL_AUTH_ID"] <> ''?
								"	AND EXTERNAL_AUTH_ID='".$DB->ForSQL($arParams["EXTERNAL_AUTH_ID"])."' " :
								"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') "
						);
				}
				$res = $DB->Query($strSql);

				while($arUser = $res->Fetch())
				{
					if($arParams["SITE_ID"]===false)
					{
						if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
							$arParams["SITE_ID"] = CSite::GetDefSite($arUser["LID"]);
						else
							$arParams["SITE_ID"] = SITE_ID;
					}

					if($arUser["ACTIVE"] == "Y")
					{
						CUser::SendUserInfo($arUser["ID"], $arParams["SITE_ID"], GetMessage("INFO_REQ"), true, 'USER_PASS_REQUEST');
						$f = true;
					}
					elseif($confirmation)
					{
						//unconfirmed registration - resend confirmation email
						$arFields = array(
							"USER_ID" => $arUser["ID"],
							"LOGIN" => $arUser["LOGIN"],
							"EMAIL" => $arUser["EMAIL"],
							"NAME" => $arUser["NAME"],
							"LAST_NAME" => $arUser["LAST_NAME"],
							"CONFIRM_CODE" => $arUser["CONFIRM_CODE"],
							"USER_IP" => $_SERVER["REMOTE_ADDR"],
							"USER_HOST" => @gethostbyaddr($_SERVER["REMOTE_ADDR"]),
						);

						$event = new CEvent;
						$event->SendImmediate("NEW_USER_CONFIRM", $arParams["SITE_ID"], $arFields, "Y", "", array(), $arUser["LANGUAGE_ID"]);

						$result_message = array("MESSAGE"=>GetMessage("MAIN_SEND_PASS_CONFIRM")."<br>", "TYPE"=>"OK");
						$f = true;
					}

					if(COption::GetOptionString("main", "event_log_password_request", "N") === "Y")
					{
						CEventLog::Log("SECURITY", "USER_INFO", "main", $arUser["ID"]);
					}
				}
			}
			if(!$f)
			{
				return array("MESSAGE"=>GetMessage('DATA_NOT_FOUND1')."<br>", "TYPE"=>"ERROR");
			}
		}
		return $result_message;
	}

	public function Register($USER_LOGIN, $USER_NAME, $USER_LAST_NAME, $USER_PASSWORD, $USER_CONFIRM_PASSWORD, $USER_EMAIL, $SITE_ID = false, $captcha_word = "", $captcha_sid = 0, $bSkipConfirm = false, $USER_PHONE_NUMBER = "")
	{
		/**
		 * @global CMain $APPLICATION
		 * @global CUserTypeManager $USER_FIELD_MANAGER
		 */
		global $APPLICATION, $DB, $USER_FIELD_MANAGER;

		$APPLICATION->ResetException();
		if(defined("ADMIN_SECTION") && ADMIN_SECTION===true && $SITE_ID!==false)
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_FUNCTION_REGISTER_NA_INADMIN"));
			return array("MESSAGE"=>GetMessage("MAIN_FUNCTION_REGISTER_NA_INADMIN"), "TYPE"=>"ERROR");
		}

		$strError = "";

		if (COption::GetOptionString("main", "captcha_registration", "N") == "Y")
		{
			if (!($APPLICATION->CaptchaCheckCode($captcha_word, $captcha_sid)))
			{
				$strError .= GetMessage("MAIN_FUNCTION_REGISTER_CAPTCHA")."<br>";
			}
		}

		if($strError)
		{
			if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
			{
				CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, $strError);
			}

			$APPLICATION->ThrowException($strError);
			return array("MESSAGE"=>$strError, "TYPE"=>"ERROR");
		}

		if($SITE_ID === false)
			$SITE_ID = SITE_ID;

		$bConfirmReq = !$bSkipConfirm && (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" && COption::GetOptionString("main", "new_user_email_required", "Y") <> "N");
		$phoneRegistration = (COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y");
		$phoneRequired = ($phoneRegistration && COption::GetOptionString("main", "new_user_phone_required", "N") == "Y");

		$checkword = md5(CMain::GetServerUniqID().uniqid());
		$active = ($bConfirmReq || $phoneRequired? "N": "Y");

		$arFields = array(
			"LOGIN" => $USER_LOGIN,
			"NAME" => $USER_NAME,
			"LAST_NAME" => $USER_LAST_NAME,
			"PASSWORD" => $USER_PASSWORD,
			"CHECKWORD" => $checkword,
			"~CHECKWORD_TIME" => $DB->CurrentTimeFunction(),
			"CONFIRM_PASSWORD" => $USER_CONFIRM_PASSWORD,
			"EMAIL" => $USER_EMAIL,
			"PHONE_NUMBER" => $USER_PHONE_NUMBER,
			"ACTIVE" => $active,
			"CONFIRM_CODE" => ($bConfirmReq? randString(8): ""),
			"SITE_ID" => $SITE_ID,
			"LANGUAGE_ID" => LANGUAGE_ID,
			"USER_IP" => $_SERVER["REMOTE_ADDR"],
			"USER_HOST" => @gethostbyaddr($_SERVER["REMOTE_ADDR"]),
		);
		$USER_FIELD_MANAGER->EditFormAddFields("USER", $arFields);

		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if($def_group!="")
			$arFields["GROUP_ID"] = explode(",", $def_group);

		$bOk = true;
		$result_message = true;
		foreach(GetModuleEvents("main", "OnBeforeUserRegister", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				if($err = $APPLICATION->GetException())
				{
					$result_message = array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				}
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$result_message = array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		$ID = false;
		$phoneReg = false;
		if($bOk)
		{
			if($arFields["SITE_ID"] === false)
			{
				$arFields["SITE_ID"] = CSite::GetDefSite();
			}
			$arFields["LID"] = $arFields["SITE_ID"];

			if($ID = $this->Add($arFields))
			{
				if($phoneRegistration && $arFields["PHONE_NUMBER"] <> '')
				{
					$phoneReg = true;

					//added the phone number for the user, now sending a confirmation SMS
					list($code, $phoneNumber) = CUser::GeneratePhoneCode($ID);

					$sms = new \Bitrix\Main\Sms\Event(
						"SMS_USER_CONFIRM_NUMBER",
						[
							"USER_PHONE" => $phoneNumber,
							"CODE" => $code,
						]
					);
					$sms->setSite($arFields["SITE_ID"]);
					$smsResult = $sms->send(true);

					$signedData = \Bitrix\Main\Controller\PhoneAuth::signData(['phoneNumber' => $phoneNumber]);

					if($smsResult->isSuccess())
					{
						$result_message = array(
							"MESSAGE" => GetMessage("main_register_sms_sent"),
							"TYPE" => "OK",
							"SIGNED_DATA" => $signedData,
							"ID" => $ID,
						);
					}
					else
					{
						$result_message = array(
							"MESSAGE" => $smsResult->getErrorMessages(),
							"TYPE" => "ERROR",
							"SIGNED_DATA" => $signedData,
							"ID" => $ID,
						);
					}

				}
				else
				{
					$result_message = array(
						"MESSAGE" => GetMessage("USER_REGISTER_OK"),
						"TYPE" => "OK",
						"ID" => $ID
					);
				}

				$arFields["USER_ID"] = $ID;

				$arEventFields = $arFields;
				unset($arEventFields["PASSWORD"]);
				unset($arEventFields["CONFIRM_PASSWORD"]);
				unset($arEventFields["~CHECKWORD_TIME"]);

				$event = new CEvent;
				$event->SendImmediate("NEW_USER", $arEventFields["SITE_ID"], $arEventFields);
				if($bConfirmReq)
				{
					$event->SendImmediate("NEW_USER_CONFIRM", $arEventFields["SITE_ID"], $arEventFields);
				}
			}
			else
			{
				$APPLICATION->ThrowException($this->LAST_ERROR);
				$result_message = array("MESSAGE"=>$this->LAST_ERROR, "TYPE"=>"ERROR");
			}
		}

		if(is_array($result_message))
		{
			if($result_message["TYPE"] == "OK")
			{
				if(COption::GetOptionString("main", "event_log_register", "N") === "Y")
				{
					$res_log["user"] = ($USER_NAME != "" || $USER_LAST_NAME != "") ? trim($USER_NAME." ".$USER_LAST_NAME) : $USER_LOGIN;
					CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID, serialize($res_log));
				}
			}
			else
			{
				if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
				{
					CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, $result_message["MESSAGE"]);
				}
			}
		}

		//authorize succesfully registered user, except email or phone confirmation is required
		$isAuthorize = false;
		if($ID !== false && $arFields["ACTIVE"] === "Y" && $phoneReg === false)
		{
			$isAuthorize = $this->Authorize($ID);
		}

		$agreementId = intval(COption::getOptionString("main", "new_user_agreement", ""));
		if ($agreementId && $isAuthorize)
		{
			$agreementObject = new \Bitrix\Main\UserConsent\Agreement($agreementId);
			if ($agreementObject->isExist() && $agreementObject->isActive() && $_REQUEST["USER_AGREEMENT"] == "Y")
			{
				\Bitrix\Main\UserConsent\Consent::addByContext($agreementId, "main/reg", "register");
			}
		}

		$arFields["RESULT_MESSAGE"] = $result_message;
		foreach (GetModuleEvents("main", "OnAfterUserRegister", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $arFields["RESULT_MESSAGE"];
	}

	public function SimpleRegister($USER_EMAIL, $SITE_ID = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB;

		$APPLICATION->ResetException();
		if(defined("ADMIN_SECTION") && ADMIN_SECTION===true && $SITE_ID===false)
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_FUNCTION_SIMPLEREGISTER_NA_INADMIN"));
			return array("MESSAGE"=>GetMessage("MAIN_FUNCTION_SIMPLEREGISTER_NA_INADMIN"), "TYPE"=>"ERROR");
		}

		if($SITE_ID===false)
			$SITE_ID = SITE_ID;

		global $REMOTE_ADDR;

		$checkword = md5(CMain::GetServerUniqID().uniqid());
		$arFields = array(
			"CHECKWORD" => $checkword,
			"~CHECKWORD_TIME" => $DB->CurrentTimeFunction(),
			"EMAIL" => $USER_EMAIL,
			"ACTIVE" => "Y",
			"NAME"=>"",
			"LAST_NAME"=>"",
			"USER_IP"=>$REMOTE_ADDR,
			"USER_HOST"=>@gethostbyaddr($REMOTE_ADDR),
			"SITE_ID" => $SITE_ID,
			"LANGUAGE_ID" => LANGUAGE_ID,
		);

		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if($def_group!="")
		{
			$arFields["GROUP_ID"] = explode(",", $def_group);
		}
		else
		{
			$arFields["GROUP_ID"] = array();
		}
		$arFields["PASSWORD"] = $arFields["CONFIRM_PASSWORD"] = self::GeneratePasswordByPolicy($arFields["GROUP_ID"]);

		$bOk = true;
		$result_message = false;
		foreach(GetModuleEvents("main", "OnBeforeUserSimpleRegister", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$result_message = array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		$bRandLogin = false;
		if(!is_set($arFields, "LOGIN"))
		{
			$arFields["LOGIN"] = randString(50);
			$bRandLogin = true;
		}

		$ID = 0;
		if($bOk)
		{
			$arFields["LID"] = $arFields["SITE_ID"];
			$arFields["CHECKWORD"] = $checkword;
			if($ID = $this->Add($arFields))
			{
				if($bRandLogin)
				{
					$this->Update($ID, array("LOGIN"=>"user".$ID));
					$arFields["LOGIN"] = "user".$ID;
				}

				$this->Authorize($ID);

				$event = new CEvent;
				$arFields["USER_ID"] = $ID;

				$arEventFields = $arFields;
				unset($arEventFields["PASSWORD"]);
				unset($arEventFields["CONFIRM_PASSWORD"]);

				$event->SendImmediate("NEW_USER", $arEventFields["SITE_ID"], $arEventFields);
				CUser::SendUserInfo($ID, $arEventFields["SITE_ID"], GetMessage("USER_REGISTERED_SIMPLE"), true);
				$result_message = array("MESSAGE"=>GetMessage("USER_REGISTER_OK"), "TYPE"=>"OK");
			}
			else
				$result_message = array("MESSAGE"=>$this->LAST_ERROR, "TYPE"=>"ERROR");
		}

		if(is_array($result_message))
		{
			if($result_message["TYPE"] == "OK")
			{
				if(COption::GetOptionString("main", "event_log_register", "N") === "Y")
				{
					$res_log["user"] = $arFields["LOGIN"];
					CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID, serialize($res_log));
				}
			}
			else
			{
				if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
				{
					CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, $result_message["MESSAGE"]);
				}
			}
		}

		$arFields["RESULT_MESSAGE"] = $result_message;
		foreach(GetModuleEvents("main", "OnAfterUserSimpleRegister", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $arFields["RESULT_MESSAGE"];
	}

	public function IsAuthorized()
	{
		return ($_SESSION["SESS_AUTH"]["AUTHORIZED"]=="Y");
	}

	public function HasNoAccess()
	{
		if (!$this->IsAuthorized())
		{
			return true;
		}

		$filePath = \Bitrix\Main\Context::getCurrent()->getRequest()->getScriptFile();

		return !$this->CanDoFileOperation('fm_view_file', [SITE_ID, $filePath]);
	}

	public function IsJustAuthorized()
	{
		return $this->justAuthorized;
	}

	public function IsJustBecameOnline()
	{
		if(!$_SESSION['SESS_AUTH']['PREV_LAST_ACTIVITY'])
		{
			return true;
		}
		else
		{
			return ($_SESSION['SESS_AUTH']['SET_LAST_ACTIVITY'] - $_SESSION['SESS_AUTH']['PREV_LAST_ACTIVITY']) > Main\UserTable::getSecondsForLimitOnline();
		}
	}

	public function IsAdmin()
	{
		if ($this->admin === null)
		{
			if(
				COption::GetOptionString("main", "controller_member", "N") == "Y"
				&& COption::GetOptionString("main", "~controller_limited_admin", "N") == "Y"
			)
			{
				if(
					isset($_SESSION["SESS_AUTH"])
					&& is_array($_SESSION["SESS_AUTH"])
					&& isset($_SESSION["SESS_AUTH"]["CONTROLLER_ADMIN"])
				)
					$this->admin = ($_SESSION["SESS_AUTH"]["CONTROLLER_ADMIN"] === true);
				else
					$this->admin = false;
			}
			else
			{
				if(
					isset($_SESSION["SESS_AUTH"])
					&& is_array($_SESSION["SESS_AUTH"])
					&& isset($_SESSION["SESS_AUTH"]["ADMIN"])
				)
					$this->admin = ($_SESSION["SESS_AUTH"]["ADMIN"] === true);
				else
					$this->admin = false;
			}
		}
		return $this->admin;
	}

	public function SetControllerAdmin($isAdmin=true)
	{
		$_SESSION["SESS_AUTH"]["CONTROLLER_ADMIN"] = $isAdmin;
	}

	public function Logout()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB;

		$USER_ID = $_SESSION["SESS_AUTH"]["USER_ID"];

		$arParams = array(
			"USER_ID" => &$USER_ID
		);

		$APPLICATION->ResetException();
		$bOk = true;
		foreach(GetModuleEvents("main", "OnBeforeUserLogout", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if(!($APPLICATION->GetException()))
				{
					$APPLICATION->ThrowException("Unknown logout error");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			foreach(GetModuleEvents("main", "OnUserLogout", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($USER_ID));

			if($_SESSION["SESS_AUTH"]["STORED_AUTH_ID"]>0)
				$DB->Query("DELETE FROM b_user_stored_auth WHERE ID=".intval($_SESSION["SESS_AUTH"]["STORED_AUTH_ID"]));

			$this->justAuthorized = false;
			$this->admin = null;

			$_SESSION["SESS_AUTH"] = array();
			unset($_SESSION["SESS_AUTH"]);
			unset($_SESSION["SESS_OPERATIONS"]);
			unset($_SESSION["MODULE_PERMISSIONS"]);
			unset($_SESSION["SESS_PWD_HASH_TESTED"]);
			unset($_SESSION['fixed_session_id']);

			//change session id for security reason after logout
			if(COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security"))
				CSecuritySession::UpdateSessID();
			else
				session_regenerate_id(true);

			$response = Main\Context::getCurrent()->getResponse();
			$spread = (COption::GetOptionString("main", "auth_multisite", "N") == "Y"? (Main\Web\Cookie::SPREAD_SITES | Main\Web\Cookie::SPREAD_DOMAIN) : Main\Web\Cookie::SPREAD_DOMAIN);

			$cookie = new Main\Web\Cookie("UIDH",  "", 0);
			$cookie->setSpread($spread);
			$cookie->setHttpOnly(true);
			$response->addCookie($cookie);

			$cookie = new Main\Web\Cookie("UIDL",  "", 0);
			$cookie->setSpread($spread);
			$cookie->setHttpOnly(true);
			$response->addCookie($cookie);

			Main\Composite\Engine::onUserLogout();
		}

		$arParams["SUCCESS"] = $bOk;
		foreach(GetModuleEvents("main", "OnAfterUserLogout", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arParams));

		if(COption::GetOptionString("main", "event_log_logout", "N") === "Y")
			CEventLog::Log("SECURITY", "USER_LOGOUT", "main", $USER_ID);
	}

	public static function GetUserGroup($ID)
	{
		$ID = (int)$ID;
		if (!isset(self::$userGroupCache[$ID]))
		{
			$arr = array();
			$res = static::GetUserGroupEx($ID);
			while ($r = $res->Fetch())
				$arr[] = $r["GROUP_ID"];

			self::$userGroupCache[$ID] = $arr;
		}

		return self::$userGroupCache[$ID];
	}

	public static function GetUserGroupEx($ID)
	{
		global $DB;

		$strSql = "
			SELECT UG.GROUP_ID, G.STRING_ID,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_FROM", "FULL")." as DATE_ACTIVE_FROM,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_TO", "FULL")." as DATE_ACTIVE_TO
			FROM b_user_group UG INNER JOIN b_group G ON G.ID=UG.GROUP_ID
			WHERE UG.USER_ID = ".intval($ID)."
				and ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction()."))
				and ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction()."))
				and G.ACTIVE = 'Y'
			UNION SELECT 2, 'everyone', NULL, NULL ".(strtoupper($DB->type) == "ORACLE"? " FROM dual " : "");

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	public static function GetUserGroupList($ID)
	{
		global $DB;

		$strSql = "
			SELECT
				UG.GROUP_ID,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_FROM", "FULL")." as DATE_ACTIVE_FROM,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_TO", "FULL")." as DATE_ACTIVE_TO
			FROM
				b_user_group UG
			WHERE
				UG.USER_ID = ".intval($ID)."
			UNION SELECT 2, NULL, NULL ".(strtoupper($DB->type) == "ORACLE"? " FROM dual " : "");

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	public function CheckFields(&$arFields, $ID=false)
	{
		/**
		 * @global CMain $APPLICATION
		 * @global CUserTypeManager $USER_FIELD_MANAGER
		 */
		global $DB, $APPLICATION, $USER_FIELD_MANAGER;

		$this->LAST_ERROR = "";

		$bInternal = true;
		if(is_set($arFields, "EXTERNAL_AUTH_ID"))
		{
			if(trim($arFields["EXTERNAL_AUTH_ID"]) <> '')
			{
				$bInternal = false;
			}
		}
		else
		{
			if($ID > 0)
			{
				$dbr = $DB->Query("SELECT EXTERNAL_AUTH_ID FROM b_user WHERE ID=".intval($ID));
				if(($ar = $dbr->Fetch()))
				{
					if($ar['EXTERNAL_AUTH_ID'] <> '')
					{
						$bInternal = false;
					}
				}
			}
		}

		if($bInternal)
		{
			$this->LAST_ERROR .= self::CheckInternalFields($arFields, $ID);
		}
		else
		{
			if(is_set($arFields, "EMAIL"))
			{
				if($arFields["EMAIL"] <> '' && !check_email($arFields["EMAIL"], true))
				{
					$this->LAST_ERROR .= GetMessage("WRONG_EMAIL")."<br>";
				}
			}
		}

		if(is_set($arFields, "PERSONAL_PHOTO") && $arFields["PERSONAL_PHOTO"]["name"] == '' && $arFields["PERSONAL_PHOTO"]["del"] == '')
			unset($arFields["PERSONAL_PHOTO"]);

		$maxWidth = COption::GetOptionInt("main", "profile_image_width", 0);
		$maxHeight = COption::GetOptionInt("main", "profile_image_height", 0);
		$maxSize = COption::GetOptionInt("main", "profile_image_size", 0);

		if(is_set($arFields, "PERSONAL_PHOTO"))
		{
			$res = CFile::CheckImageFile($arFields["PERSONAL_PHOTO"], $maxSize, $maxWidth, $maxHeight);
			if($res <> '')
				$this->LAST_ERROR .= $res."<br>";
		}

		if(is_set($arFields, "PERSONAL_BIRTHDAY") && $arFields["PERSONAL_BIRTHDAY"] <> '' && !CheckDateTime($arFields["PERSONAL_BIRTHDAY"]))
			$this->LAST_ERROR .= GetMessage("WRONG_PERSONAL_BIRTHDAY")."<br>";

		if(is_set($arFields, "WORK_LOGO") && $arFields["WORK_LOGO"]["name"] == '' && $arFields["WORK_LOGO"]["del"] == '')
			unset($arFields["WORK_LOGO"]);

		if(is_set($arFields, "WORK_LOGO"))
		{
			$res = CFile::CheckImageFile($arFields["WORK_LOGO"], $maxSize, $maxWidth, $maxHeight);
			if($res <> '')
				$this->LAST_ERROR .= $res."<br>";
		}

		if(is_set($arFields, "LOGIN"))
		{
			$res = $DB->Query(
				"SELECT 'x' ".
				"FROM b_user ".
				"WHERE LOGIN='".$DB->ForSql($arFields["LOGIN"], 50)."'	".
				"	".($ID===false ? "" : " AND ID<>".intval($ID)).
				"	".(!$bInternal ? "	AND EXTERNAL_AUTH_ID='".$DB->ForSql($arFields["EXTERNAL_AUTH_ID"])."' " : " AND (EXTERNAL_AUTH_ID IS NULL OR ".$DB->Length("EXTERNAL_AUTH_ID")."<=0)")
				);

			if($res->Fetch())
				$this->LAST_ERROR .= str_replace("#LOGIN#", htmlspecialcharsbx($arFields["LOGIN"]), GetMessage("USER_EXIST"))."<br>";
		}

		if(is_object($APPLICATION))
		{
			$APPLICATION->ResetException();

			if($ID===false)
				$events = GetModuleEvents("main", "OnBeforeUserAdd", true);
			else
			{
				$arFields["ID"] = $ID;
				$events = GetModuleEvents("main", "OnBeforeUserUpdate", true);
			}

			foreach($events as $arEvent)
			{
				$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
				if($bEventRes===false)
				{
					if($err = $APPLICATION->GetException())
						$this->LAST_ERROR .= $err->GetString()." ";
					else
					{
						$APPLICATION->ThrowException("Unknown error");
						$this->LAST_ERROR .= "Unknown error. ";
					}
					break;
				}
			}
		}

		if(is_object($APPLICATION))
			$APPLICATION->ResetException();
		if (!$USER_FIELD_MANAGER->CheckFields("USER", $ID, $arFields))
		{
			if(is_object($APPLICATION) && $APPLICATION->GetException())
			{
				$e = $APPLICATION->GetException();
				$this->LAST_ERROR .= $e->GetString();
				$APPLICATION->ResetException();
			}
			else
			{
				$this->LAST_ERROR .= "Unknown error. ";
			}
		}

		if($this->LAST_ERROR <> '')
			return false;

		return true;
	}

	/**
	 * @param array $arFields
	 * @param int|bool $ID
	 * @return string
	 */
	public static function CheckInternalFields($arFields, $ID = false)
	{
		global $DB;

		$resultError = '';

		$emailRequired = (COption::GetOptionString("main", "new_user_email_required", "Y") <> "N");
		$phoneRequired = (COption::GetOptionString("main", "new_user_phone_required", "N") == "Y");

		if($ID === false)
		{
			if(!isset($arFields["LOGIN"]))
			{
				$resultError .= GetMessage("user_login_not_set")."<br>";
			}

			if(!isset($arFields["PASSWORD"]))
			{
				$resultError .= GetMessage("user_pass_not_set")."<br>";
			}

			if($emailRequired && !isset($arFields["EMAIL"]))
			{
				$resultError .= GetMessage("user_email_not_set")."<br>";
			}

			if($phoneRequired && !isset($arFields["PHONE_NUMBER"]))
			{
				$resultError .= GetMessage("main_user_check_no_phone")."<br>";
			}
		}
		if(is_set($arFields, "LOGIN") && $arFields["LOGIN"] <> trim($arFields["LOGIN"]))
		{
			$resultError .= GetMessage("LOGIN_WHITESPACE")."<br>";
		}

		if(is_set($arFields, "LOGIN") && strlen($arFields["LOGIN"]) < 3)
		{
			$resultError .= GetMessage("MIN_LOGIN")."<br>";
		}

		if(is_set($arFields, "PASSWORD"))
		{
			if(array_key_exists("GROUP_ID", $arFields))
			{
				$arGroups = array();
				if(is_array($arFields["GROUP_ID"]))
				{
					foreach($arFields["GROUP_ID"] as $arGroup)
					{
						if(is_array($arGroup))
						{
							$arGroups[] = $arGroup["GROUP_ID"];
						}
						else
						{
							$arGroups[] = $arGroup;
						}
					}
				}
				$arPolicy = self::GetGroupPolicy($arGroups);
			}
			elseif($ID !== false)
			{
				$arPolicy = self::GetGroupPolicy($ID);
			}
			else
			{
				$arPolicy = self::GetGroupPolicy(array());
			}

			$passwordErrors = self::CheckPasswordAgainstPolicy($arFields["PASSWORD"], $arPolicy);
			if(!empty($passwordErrors))
			{
				$resultError .= implode("<br>", $passwordErrors)."<br>";
			}

			if(strlen($arFields["PASSWORD"]) > 50)
			{
				$resultError .= GetMessage("main_user_check_max_pass")."<br>";
			}
		}

		if(is_set($arFields, "EMAIL"))
		{
			if(($emailRequired && strlen($arFields["EMAIL"]) < 3) || ($arFields["EMAIL"] <> '' && !check_email($arFields["EMAIL"], true)))
			{
				$resultError .= GetMessage("WRONG_EMAIL")."<br>";
			}
			elseif(COption::GetOptionString("main", "new_user_email_uniq_check", "N") === "Y")
			{
				if($arFields["EMAIL"] <> '')
				{
					$oldEmail = '';
					if($ID > 0)
					{
						//the option 'new_user_email_uniq_check' might have been switched on after the DB already contained identical emails
						//so we let a user to have the old email, but not the existing new one
						$dbr = $DB->Query("SELECT EMAIL FROM b_user WHERE ID=".intval($ID));
						if(($ar = $dbr->Fetch()))
						{
							$oldEmail = $ar['EMAIL'];
						}
					}
					if($ID == false || $arFields["EMAIL"] <> $oldEmail)
					{
						$b = $o = "";
						$res = CUser::GetList($b, $o,
							array(
								"=EMAIL" => $arFields["EMAIL"],
								"EXTERNAL_AUTH_ID" => $arFields["EXTERNAL_AUTH_ID"]
							),
							array(
								"FIELDS" => array("ID")
							)
						);
						while($ar = $res->Fetch())
						{
							if(intval($ar["ID"]) !== intval($ID))
							{
								$resultError .= GetMessage("USER_WITH_EMAIL_EXIST", array("#EMAIL#" => htmlspecialcharsbx($arFields["EMAIL"])))."<br>";
							}
						}
					}
				}
			}
		}

		if(is_set($arFields, "PASSWORD") && is_set($arFields, "CONFIRM_PASSWORD") && $arFields["PASSWORD"] !== $arFields["CONFIRM_PASSWORD"])
		{
			$resultError .= GetMessage("WRONG_CONFIRMATION")."<br>";
		}

		if(isset($arFields["PHONE_NUMBER"]))
		{
			if($phoneRequired && $arFields["PHONE_NUMBER"] == '')
			{
				$resultError .= GetMessage("main_user_check_no_phone")."<br>";
			}
			elseif($arFields["PHONE_NUMBER"] <> '')
			{
				//normalize the number: we need it normalized for validation
				$phoneNumber = Main\UserPhoneAuthTable::normalizePhoneNumber($arFields["PHONE_NUMBER"]);

				//validation
				$field = Main\UserPhoneAuthTable::getEntity()->getField("PHONE_NUMBER");
				$result = new Main\ORM\Data\Result();
				$primary = ($ID === false? [] : ["USER_ID" => $ID]);
				$field->validateValue($phoneNumber, $primary, [], $result);
				if(!$result->isSuccess())
				{
					$resultError .= implode("<br>", $result->getErrorMessages());
				}
			}
		}

		if(is_array($arFields["GROUP_ID"]) && count($arFields["GROUP_ID"]) > 0)
		{
			if(is_array($arFields["GROUP_ID"][0]) && count($arFields["GROUP_ID"][0]) > 0)
			{
				foreach($arFields["GROUP_ID"] as $arGroup)
				{
					if($arGroup["DATE_ACTIVE_FROM"] <> '' && !CheckDateTime($arGroup["DATE_ACTIVE_FROM"]))
					{
						$error = str_replace("#GROUP_ID#", $arGroup["GROUP_ID"], GetMessage("WRONG_DATE_ACTIVE_FROM"));
						$resultError .= $error."<br>";
					}

					if($arGroup["DATE_ACTIVE_TO"] <> '' && !CheckDateTime($arGroup["DATE_ACTIVE_TO"]))
					{
						$error = str_replace("#GROUP_ID#", $arGroup["GROUP_ID"], GetMessage("WRONG_DATE_ACTIVE_TO"));
						$resultError .= $error."<br>";
					}
				}
			}
		}

		return $resultError;
	}

	public static function GetByID($ID)
	{
		global $USER;

		$userID = (is_object($USER)? intval($USER->GetID()): 0);
		$ID = intval($ID);
		if($userID > 0 && $ID == $userID && is_array(self::$CURRENT_USER))
		{
			$rs = new CDBResult;
			$rs->InitFromArray(self::$CURRENT_USER);
		}
		else
		{
			$rs = CUser::GetList(($by="id"), ($order="asc"), array("ID_EQUAL_EXACT"=>intval($ID)), array("SELECT"=>array("UF_*")));
			if($userID > 0 && $ID == $userID)
			{
				self::$CURRENT_USER = array($rs->Fetch());
				$rs = new CDBResult;
				$rs->InitFromArray(self::$CURRENT_USER);
			}
		}
		return $rs;
	}

	public static function GetByLogin($LOGIN)
	{
		$rs = CUser::GetList(($by="id"), ($order="asc"), array("LOGIN_EQUAL_EXACT"=>$LOGIN), array("SELECT"=>array("UF_*")));
		return $rs;
	}

	public function Update($ID, $arFields, $authActions = true)
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $DB, $USER_FIELD_MANAGER, $CACHE_MANAGER, $USER;

		$ID = intval($ID);

		if(!$this->CheckFields($arFields, $ID))
		{
			$result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			unset($arFields["ID"]);

			if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
				$arFields["ACTIVE"]="N";

			if(is_set($arFields, "PERSONAL_GENDER") && ($arFields["PERSONAL_GENDER"]!="M" && $arFields["PERSONAL_GENDER"]!="F"))
				$arFields["PERSONAL_GENDER"] = "";

			$saveHistory = (Main\Config\Option::get("main", "user_profile_history") === "Y");

			//we need old values for some actions
			$arUser = null;
			if((isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] == "N") || isset($arFields["PASSWORD"]) || $saveHistory)
			{
				$rUser = CUser::GetByID($ID);
				$arUser = $rUser->Fetch();
			}

			$newPassword = "";
			if(is_set($arFields, "PASSWORD"))
			{
				$original_pass = $arFields["PASSWORD"];
				$salt = randString(8, array(
					"abcdefghijklnmopqrstuvwxyz",
					"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
					"0123456789",
					",.<>/?;:[]{}\\|~!@#\$%^&*()-_+=",
				));
				$arFields["PASSWORD"] = $salt.md5($salt.$arFields["PASSWORD"]);

				if($arUser)
				{
					$oldSalt = substr($arUser["PASSWORD"], 0, 8);
					$newPassword = $oldSalt.md5($oldSalt.$original_pass);

					if($newPassword <> $arUser["PASSWORD"])
					{
						$DB->Query("DELETE FROM b_user_stored_auth WHERE USER_ID=".$ID);
					}
				}
				if(COption::GetOptionString("main", "event_log_password_change", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_PASSWORD_CHANGED", "main", $ID);
			}
			unset($arFields["STORED_HASH"]);

			$checkword = '';
			if(!is_set($arFields, "CHECKWORD"))
			{
				if(is_set($arFields, "PASSWORD") || is_set($arFields, "EMAIL") || is_set($arFields, "LOGIN")  || is_set($arFields, "ACTIVE"))
				{
					$salt =  randString(8);
					$checkword = md5(CMain::GetServerUniqID().uniqid());
					$arFields["CHECKWORD"] = $salt.md5($salt.$checkword);
				}
			}
			else
			{
				$salt =  randString(8);
				$checkword = $arFields["CHECKWORD"];
				$arFields["CHECKWORD"] = $salt.md5($salt.$checkword);
			}

			if(is_set($arFields, "CHECKWORD") && !is_set($arFields, "CHECKWORD_TIME"))
				$arFields["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();

			if(is_set($arFields, "WORK_COUNTRY"))
				$arFields["WORK_COUNTRY"] = intval($arFields["WORK_COUNTRY"]);

			if(is_set($arFields, "PERSONAL_COUNTRY"))
				$arFields["PERSONAL_COUNTRY"] = intval($arFields["PERSONAL_COUNTRY"]);

			if (
				array_key_exists("PERSONAL_PHOTO", $arFields)
				&& is_array($arFields["PERSONAL_PHOTO"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["PERSONAL_PHOTO"])
					|| $arFields["PERSONAL_PHOTO"]["MODULE_ID"] == ''
				)
			)
			{
				$arFields["PERSONAL_PHOTO"]["MODULE_ID"] = "main";
			}

			CFile::SaveForDB($arFields, "PERSONAL_PHOTO", "main");

			if (
				array_key_exists("WORK_LOGO", $arFields)
				&& is_array($arFields["WORK_LOGO"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["WORK_LOGO"])
					|| $arFields["WORK_LOGO"]["MODULE_ID"] == ''
				)
			)
			{
				$arFields["WORK_LOGO"]["MODULE_ID"] = "main";
			}

			CFile::SaveForDB($arFields, "WORK_LOGO", "main");

			$strUpdate = $DB->PrepareUpdate("b_user", $arFields);

			if(!is_set($arFields, "TIMESTAMP_X"))
				$strUpdate .= ($strUpdate <> ""? ",":"")." TIMESTAMP_X = ".$DB->GetNowFunction();

			$strSql = "UPDATE b_user SET ".$strUpdate." WHERE ID=".$ID;

			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$USER_FIELD_MANAGER->Update("USER", $ID, $arFields);

			if(isset($arFields["PHONE_NUMBER"]))
			{
				$numberExists = false;
				if($arFields["PHONE_NUMBER"] <> '')
				{
					$arFields["PHONE_NUMBER"] = Main\UserPhoneAuthTable::normalizePhoneNumber($arFields["PHONE_NUMBER"]);

					$numberExists = Main\UserPhoneAuthTable::getList(["filter" => [
						"=USER_ID" => $ID,
						"=PHONE_NUMBER" => $arFields["PHONE_NUMBER"],
					]])->fetch();
				}
				if($arFields["PHONE_NUMBER"] == '' || !$numberExists)
				{
					//number changed or added
					Main\UserPhoneAuthTable::delete($ID);

					if($arFields["PHONE_NUMBER"] <> '')
					{
						Main\UserPhoneAuthTable::add([
							"USER_ID" => $ID,
							"PHONE_NUMBER" => $arFields["PHONE_NUMBER"],
						]);
					}
				}
			}

			if(COption::GetOptionString("main", "event_log_user_edit", "N") === "Y")
			{
				$res_log["user"] = ($arFields["NAME"] != "" || $arFields["LAST_NAME"] != "") ? trim($arFields["NAME"]." ".$arFields["LAST_NAME"]) : $arFields["LOGIN"];
				CEventLog::Log("SECURITY", "USER_EDIT", "main", $ID, serialize($res_log));
			}

			if(is_set($arFields, "GROUP_ID"))
				CUser::SetUserGroup($ID, $arFields["GROUP_ID"]);

			//update digest hash for http digest authorization
			if(isset($arUser["ID"]) && is_set($arFields, "PASSWORD") && COption::GetOptionString('main', 'use_digest_auth', 'N') == 'Y')
			{
				/** @noinspection PhpUndefinedVariableInspection */
				CUser::UpdateDigest($arUser["ID"], $original_pass);
			}

			if($arUser && $authActions == true)
			{
				$authAction = false;
				if(isset($arFields["ACTIVE"]) && $arUser["ACTIVE"] == "Y" && $arFields["ACTIVE"] == "N")
				{
					$authAction = true;
				}

				$internalUser = true;
				if(isset($arFields["EXTERNAL_AUTH_ID"]))
				{
					if($arFields["EXTERNAL_AUTH_ID"] <> '')
					{
						$internalUser = false;
					}
				}
				elseif($arUser["EXTERNAL_AUTH_ID"] <> '')
				{
					$internalUser = false;
				}

				if($internalUser == true && isset($arFields["PASSWORD"]) && $newPassword <> $arUser["PASSWORD"])
				{
					$authAction = true;
					if(is_object($USER) && $USER->GetID() == $ID)
					{
						//changed password by himself
						$USER->SetParam("SELF_CHANGED_PASSWORD", true);
					}
				}

				if($authAction)
				{
					Main\UserAuthActionTable::add(array(
						'USER_ID' => $ID,
						'PRIORITY' => Main\UserAuthActionTable::PRIORITY_HIGH,
						'ACTION' => Main\UserAuthActionTable::ACTION_LOGOUT,
						'ACTION_DATE' => new Main\Type\DateTime(),
					));
				}
			}

			$result = true;
			$arFields["CHECKWORD"] = $checkword;

			//update session information and cache for current user
			if(is_object($USER) && $USER->GetID() == $ID)
			{
				static $arSessFields = array(
					'LOGIN'=>'LOGIN', 'EMAIL'=>'EMAIL', 'TITLE'=>'TITLE', 'FIRST_NAME'=>'NAME', 'SECOND_NAME'=>'SECOND_NAME', 'LAST_NAME'=>'LAST_NAME',
					'PERSONAL_PHOTO'=>'PERSONAL_PHOTO', 'PERSONAL_GENDER'=>'PERSONAL_GENDER', 'AUTO_TIME_ZONE'=>'AUTO_TIME_ZONE', 'TIME_ZONE'=>'TIME_ZONE');
				foreach($arSessFields as $key => $val)
					if(isset($arFields[$val]))
						$USER->SetParam($key, $arFields[$val]);
				$name = $USER->GetParam("FIRST_NAME");
				$last_name = $USER->GetParam("LAST_NAME");
				$USER->SetParam("NAME", $name.($name == '' || $last_name == ''? "":" ").$last_name);

				//cache for GetByID()
				self::$CURRENT_USER = false;
			}

			if($saveHistory && $arUser)
			{
				$rUser = CUser::GetByID($ID);
				$newUser = $rUser->Fetch();

				Main\UserProfileHistoryTable::addHistory($ID, Main\UserProfileHistoryTable::TYPE_UPDATE, $arUser, $newUser);
			}
		}

		$arFields["ID"] = $ID;
		$arFields["RESULT"] = &$result;

		foreach (GetModuleEvents("main", "OnAfterUserUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		if($arFields["RESULT"] == true)
		{
			\Bitrix\Main\UserTable::indexRecord($ID);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$userData = \Bitrix\Main\UserTable::getById($ID)->fetch();
				$isRealUser = !$userData['EXTERNAL_AUTH_ID'] || !in_array($userData['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes());

				$CACHE_MANAGER->ClearByTag("USER_CARD_".intval($ID / TAGGED_user_card_size));
				$CACHE_MANAGER->ClearByTag($isRealUser? "USER_CARD": "EXTERNAL_USER_CARD");

				static $arNameFields = array("NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "EMAIL", "PERSONAL_GENDER", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_BIRTHDAY", "TITLE", "EXTERNAL_AUTH_ID", "UF_DEPARTMENT");
				$bClear = false;
				foreach($arNameFields as $val)
				{
					if(isset($arFields[$val]))
					{
						$bClear = true;
						break;
					}
				}
				if ($bClear)
				{
					$CACHE_MANAGER->ClearByTag("USER_NAME_".$ID);
					$CACHE_MANAGER->ClearByTag($isRealUser? "USER_NAME": "EXTERNAL_USER_NAME");
				}
			}
		}

		return $result;
	}

	public static function SetUserGroup($USER_ID, $arGroups, $newUser = false)
	{
		global $DB;

		$USER_ID = intval($USER_ID);

		if ($USER_ID === 0)
		{
			return false;
		}

		//remember previous groups of the user
		$aPrevGroups = array();
		$res = CUser::GetUserGroupList($USER_ID);
		while($res_arr = $res->Fetch())
			if($res_arr["GROUP_ID"] <> 2)
				$aPrevGroups[$res_arr["GROUP_ID"]] = $res_arr;

		$DB->Query("DELETE FROM b_user_group WHERE USER_ID=".$USER_ID, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$inserted = array();
		if(is_array($arGroups))
		{
			foreach($arGroups as $group)
			{
				if(!is_array($group))
				{
					$group = array("GROUP_ID" => $group);
				}

				$group_id = intval($group["GROUP_ID"]);
				if($group_id > 0 && $group_id <> 2 && !isset($inserted[$group_id]))
				{
					$arInsert = $DB->PrepareInsert("b_user_group", $group);
					$strSql = "
						INSERT INTO b_user_group (
							USER_ID, ".$arInsert[0]."
						) VALUES (
							".$USER_ID.",
							".$arInsert[1]."
						)
					";
					$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					$inserted[$group_id] = $group;
				}
			}
		}
		self::clearUserGroupCache($USER_ID);

		foreach (GetModuleEvents("main", "OnAfterSetUserGroup", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array("USER_ID"=>$USER_ID, "GROUPS"=>$inserted));
		}

		if($aPrevGroups <> $inserted)
		{
			if($newUser == false)
			{
				$authActionCommon = false;
				$now = new Main\Type\DateTime();
				foreach($inserted as $group)
				{
					foreach(array("DATE_ACTIVE_FROM", "DATE_ACTIVE_TO") as $field)
					{
						if($group[$field] <> '')
						{
							$date = Main\Type\DateTime::createFromUserTime($group[$field]);
							if($date > $now)
							{
								//group membership is in the future, we need separate records for each group
								Main\UserAuthActionTable::add(array(
									'USER_ID' => $USER_ID,
									'PRIORITY' => Main\UserAuthActionTable::PRIORITY_LOW,
									'ACTION' => Main\UserAuthActionTable::ACTION_UPDATE,
									'ACTION_DATE' => $date,
								));
							}
							else
							{
								$authActionCommon = true;
							}
						}
						else
						{
							$authActionCommon = true;
						}
					}
				}

				if($authActionCommon == true)
				{
					//one action for all groups without dates in the future
					Main\UserAuthActionTable::add(array(
						'USER_ID' => $USER_ID,
						'PRIORITY' => Main\UserAuthActionTable::PRIORITY_LOW,
						'ACTION' => Main\UserAuthActionTable::ACTION_UPDATE,
						'ACTION_DATE' => new Main\Type\DateTime(),
					));
				}
			}

			if(COption::GetOptionString("main", "event_log_user_groups", "N") === "Y")
			{
				$UserName = '';
				$rsUser = CUser::GetByID($USER_ID);
				if($arUser = $rsUser->GetNext())
					$UserName = ($arUser["NAME"] != "" || $arUser["LAST_NAME"] != "") ? trim($arUser["NAME"]." ".$arUser["LAST_NAME"]) : $arUser["LOGIN"];
				$res_log = array(
					"groups" => serialize($aPrevGroups)." => ".serialize($inserted),
					"user" => $UserName
				);
				CEventLog::Log("SECURITY", "USER_GROUP_CHANGED", "main", $USER_ID, serialize($res_log));
			}
		}
		return null;
	}

	/**
	 * Appends groups to the list of existing user's groups.
	 *
	 * @param int $user_id
	 * @param array|int $groups A single number, or an array of numbers, or an array of arrays("GROUP_ID"=>$val, "DATE_ACTIVE_FROM"=>$val, "DATE_ACTIVE_TO"=>$val)
	 */
	public static function AppendUserGroup($user_id, $groups)
	{
		$arGroups = array();
		$res = CUser::GetUserGroupList($user_id);
		while($res_arr = $res->Fetch())
		{
			$arGroups[] = array(
				"GROUP_ID" => $res_arr["GROUP_ID"],
				"DATE_ACTIVE_FROM" => $res_arr["DATE_ACTIVE_FROM"],
				"DATE_ACTIVE_TO" => $res_arr["DATE_ACTIVE_TO"],
			);
		}

		if(!is_array($groups))
		{
			$groups = array($groups);
		}

		foreach($groups as $group)
		{
			if(!is_array($group))
			{
				$group = array("GROUP_ID" => $group);
			}
			$arGroups[] = $group;
		}

		CUser::SetUserGroup($user_id, $arGroups);
	}

	public static function GetCount()
	{
		global $DB;
		$r = $DB->Query("SELECT COUNT('x') as C FROM b_user");
		$r = $r->Fetch();
		return Intval($r["C"]);
	}

	public static function Delete($ID)
	{
		/** @global CMain $APPLICATION */
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $DB, $APPLICATION, $USER_FIELD_MANAGER, $CACHE_MANAGER;

		$ID = intval($ID);

		@set_time_limit(600);

		$rsUser = $DB->Query("SELECT ID, LOGIN, NAME, LAST_NAME, EXTERNAL_AUTH_ID FROM b_user WHERE ID=".$ID." AND ID<>1");
		$arUser = $rsUser->Fetch();
		if(!$arUser)
			return false;

		$events = array_merge(GetModuleEvents("main", "OnBeforeUserDelete", true), GetModuleEvents("main", "OnUserDelete", true));

		foreach($events as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR1").' '.$arEvent['TO_MODULE_ID'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				if(COption::GetOptionString("main", "event_log_user_delete", "N") === "Y")
				{
					$UserName = ($arUser["NAME"] != "" || $arUser["LAST_NAME"] != "") ? trim($arUser["NAME"]." ".$arUser["LAST_NAME"]) : $arUser["LOGIN"];
					$res_log = array(
						"user" => $UserName,
						"err" => $err
					);
					CEventLog::Log("SECURITY", "USER_DELETE", "main", $ID, serialize($res_log));
				}
				return false;
			}
		}

		$strSql = "SELECT F.ID FROM	b_user U, b_file F WHERE U.ID='$ID' and (F.ID=U.PERSONAL_PHOTO or F.ID=U.WORK_LOGO)";
		$z = $DB->Query($strSql, false, "FILE: ".__FILE__." LINE:".__LINE__);
		while ($zr = $z->Fetch())
			CFile::Delete($zr["ID"]);

		if(!$DB->Query("DELETE FROM b_user_group WHERE USER_ID=".$ID))
			return false;

		if(!$DB->Query("DELETE FROM b_user_digest WHERE USER_ID=".$ID))
			return false;

		if(!$DB->Query("DELETE FROM b_app_password WHERE USER_ID=".$ID))
			return false;

		Main\UserPhoneAuthTable::delete($ID);

		$USER_FIELD_MANAGER->Delete("USER", $ID);

		if(COption::GetOptionString("main", "event_log_user_delete", "N") === "Y")
		{
			$res_log["user"] = ($arUser["NAME"] != "" || $arUser["LAST_NAME"] != "") ? trim($arUser["NAME"]." ".$arUser["LAST_NAME"]) : $arUser["LOGIN"];
			CEventLog::Log("SECURITY", "USER_DELETE", "main", $arUser["LOGIN"], serialize($res_log));
		}

		if(!$DB->Query("DELETE FROM b_user WHERE ID=".$ID." AND ID<>1"))
			return false;

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$isRealUser = !$arUser['EXTERNAL_AUTH_ID'] || !in_array($arUser['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes());

			$CACHE_MANAGER->ClearByTag("USER_CARD_".intval($ID / TAGGED_user_card_size));
			$CACHE_MANAGER->ClearByTag($isRealUser? "USER_CARD": "EXTERNAL_USER_CARD");

			$CACHE_MANAGER->ClearByTag("USER_NAME_".$ID);
			$CACHE_MANAGER->ClearByTag($isRealUser? "USER_NAME": "EXTERNAL_USER_CARD");
		}

		self::clearUserGroupCache($ID);

		Main\UserAuthActionTable::add(array(
			'USER_ID' => $ID,
			'PRIORITY' => Main\UserAuthActionTable::PRIORITY_HIGH,
			'ACTION' => Main\UserAuthActionTable::ACTION_LOGOUT,
			'ACTION_DATE' => new Main\Type\DateTime(),
		));

		if(Main\Config\Option::get("main", "user_profile_history") === "Y")
		{
			Main\UserProfileHistoryTable::deleteByUser($ID);
			Main\UserProfileHistoryTable::addHistory($ID, Main\UserProfileHistoryTable::TYPE_DELETE);
		}

		\Bitrix\Main\UserTable::deleteIndexRecord($ID);

		foreach(GetModuleEvents("main", "OnAfterUserDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID));
		}

		return true;
	}

	public static function GetExternalAuthList()
	{
		$arAll = array();
		foreach(GetModuleEvents("main", "OnExternalAuthList", true) as $arEvent)
		{
			$arRes = ExecuteModuleEventEx($arEvent);
			if(is_array($arRes))
			{
				foreach($arRes as $v)
				{
					$arAll[] = $v;
				}
			}
		}

		$result = new CDBResult;
		$result->InitFromArray($arAll);
		return $result;
	}

	public static function GetGroupPolicy($iUserId)
	{
		global $DB;
		static $arPOLICY_CACHE;
		if(!is_array($arPOLICY_CACHE))
			$arPOLICY_CACHE = array();
		$CACHE_ID = md5(serialize($iUserId));
		if(array_key_exists($CACHE_ID, $arPOLICY_CACHE))
			return $arPOLICY_CACHE[$CACHE_ID];

		global $BX_GROUP_POLICY;
		$arPolicy = $BX_GROUP_POLICY;
		if($arPolicy["SESSION_TIMEOUT"]<=0)
			$arPolicy["SESSION_TIMEOUT"] = ini_get("session.gc_maxlifetime")/60;

		$arSql = array();
		$arSql[] =
			"SELECT G.SECURITY_POLICY ".
			"FROM b_group G ".
			"WHERE G.ID=2";

		if(is_array($iUserId))
		{
			$arGroups = array();
			foreach($iUserId as $value)
			{
				$value = intval($value);
				if($value > 0 && $value != 2)
					$arGroups[$value] = $value;
			}
			if(count($arGroups) > 0)
			{
				$arSql[] =
					"SELECT G.ID GROUP_ID, G.SECURITY_POLICY ".
					"FROM b_group G ".
					"WHERE G.ID in (".implode(", ", $arGroups).")";
			}
		}
		elseif(intval($iUserId) > 0)
		{
			$arSql[] =
				"SELECT UG.GROUP_ID, G.SECURITY_POLICY ".
				"FROM b_user_group UG, b_group G ".
				"WHERE UG.USER_ID = ".intval($iUserId)." ".
				"	AND UG.GROUP_ID = G.ID ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";
		}

		foreach($arSql as $strSql)
		{
			$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $res->Fetch())
			{
				if($ar["SECURITY_POLICY"])
					$arGroupPolicy = unserialize($ar["SECURITY_POLICY"]);
				else
					continue;

				if(!is_array($arGroupPolicy))
					continue;

				foreach($arGroupPolicy as $key=>$val)
				{
					switch($key)
					{
					case "STORE_IP_MASK":
					case "SESSION_IP_MASK":
					case "BLOCK_TIME":
						if($arPolicy[$key]<$val)
							$arPolicy[$key] = $val;
						break;
					case "SESSION_TIMEOUT":
						if($arPolicy[$key]<=0 || $arPolicy[$key]>$val)
							$arPolicy[$key] = $val;
						break;
					case "PASSWORD_LENGTH":
						if($arPolicy[$key]<=0 || $arPolicy[$key] < $val)
							$arPolicy[$key] = $val;
						break;
					case "PASSWORD_UPPERCASE":
					case "PASSWORD_LOWERCASE":
					case "PASSWORD_DIGITS":
					case "PASSWORD_PUNCTUATION":
						if($val === "Y")
							$arPolicy[$key] = "Y";
						break;
					case "LOGIN_ATTEMPTS":
					case "BLOCK_LOGIN_ATTEMPTS":
						if($val > 0 && ($arPolicy[$key] <= 0 || $arPolicy[$key] > $val))
							$arPolicy[$key] = $val;
						break;
					default:
						if($arPolicy[$key]>$val)
							$arPolicy[$key] = $val;
					}
				}
			}
			if($arPolicy["PASSWORD_LENGTH"] === false)
				$arPolicy["PASSWORD_LENGTH"] = 6;
		}
		$ar = array(
			GetMessage("MAIN_GP_PASSWORD_LENGTH", array("#LENGTH#" => intval($arPolicy["PASSWORD_LENGTH"])))
		);
		if($arPolicy["PASSWORD_UPPERCASE"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_UPPERCASE");
		if($arPolicy["PASSWORD_LOWERCASE"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_LOWERCASE");
		if($arPolicy["PASSWORD_DIGITS"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_DIGITS");
		if($arPolicy["PASSWORD_PUNCTUATION"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_PUNCTUATION");
		$arPolicy["PASSWORD_REQUIREMENTS"] = implode(", ", $ar).".";

		if(count($arPOLICY_CACHE)<=10)
			$arPOLICY_CACHE[$CACHE_ID] = $arPolicy;

		return $arPolicy;
	}

	public static function CheckStoredHash($iUserId, $sHash, $bTempHashOnly=false)
	{
		global $DB;
		$arPolicy = CUser::GetGroupPolicy($iUserId);

		$cnt = 0;
		$auth_id = false;
		$site_format = CSite::GetDateFormat();

		CTimeZone::Disable();
		$strSql =
			"SELECT A.*, ".
			"	".$DB->DateToCharFunction("A.DATE_REG", "FULL")." as DATE_REG, ".
			"	".$DB->DateToCharFunction("A.LAST_AUTH", "FULL")." as LAST_AUTH ".
			"FROM b_user_stored_auth A ".
			"WHERE A.USER_ID = ".intval($iUserId)." ".
			"ORDER BY A.LAST_AUTH DESC";
		$res = $DB->Query($strSql);
		CTimeZone::Enable();

		while($ar = $res->Fetch())
		{
			if($ar["TEMP_HASH"]=="N")
				$cnt++;
			if($arPolicy["MAX_STORE_NUM"] < $cnt
				|| ($ar["TEMP_HASH"]=="N" && time()-$arPolicy["STORE_TIMEOUT"]*60 > MakeTimeStamp($ar["LAST_AUTH"], $site_format))
				|| ($ar["TEMP_HASH"]=="Y" && time()-$arPolicy["SESSION_TIMEOUT"]*60 > MakeTimeStamp($ar["LAST_AUTH"], $site_format))
			)
			{
				$DB->Query("DELETE FROM b_user_stored_auth WHERE ID=".$ar["ID"]);
			}
			elseif(!$auth_id)
			{
				//for domain spreaded external auth we should check only temporary hashes
				if($bTempHashOnly == false || $ar["TEMP_HASH"] == "Y")
				{
					$remote_net = ip2long($arPolicy["STORE_IP_MASK"]) & ip2long($_SERVER["REMOTE_ADDR"]);
					$stored_net = ip2long($arPolicy["STORE_IP_MASK"]) & (float)$ar["IP_ADDR"];
					if($sHash == $ar["STORED_HASH"] && $remote_net == $stored_net)
						$auth_id = $ar["ID"];
				}
			}
		}
		return $auth_id;
	}


	public function GetAllOperations($arGroups = false)
	{
		global $DB;

		if ($arGroups)
		{
			$userGroups = "2,".implode(",", array_map("intval", $arGroups));
		}
		else
		{
			$userGroups = $this->GetGroups();
		}

		$sql_str = "
			SELECT O.NAME OPERATION_NAME
			FROM b_group_task GT
				INNER JOIN b_task_operation T_O ON T_O.TASK_ID=GT.TASK_ID
				INNER JOIN b_operation O ON O.ID=T_O.OPERATION_ID
			WHERE GT.GROUP_ID IN(".$userGroups.")
			UNION
			SELECT O.NAME OPERATION_NAME
			FROM b_option OP
				INNER JOIN b_task_operation T_O ON T_O.TASK_ID=".$DB->ToChar("OP.VALUE", 18)."
				INNER JOIN b_operation O ON O.ID=T_O.OPERATION_ID
			WHERE OP.NAME='GROUP_DEFAULT_TASK'
			UNION
			SELECT O.NAME OPERATION_NAME
			FROM b_option OP
				INNER JOIN b_task T ON T.MODULE_ID=OP.MODULE_ID AND T.BINDING='module' AND T.LETTER=".$DB->ToChar("OP.VALUE", 1)." AND T.SYS='Y'
				INNER JOIN b_task_operation T_O ON T_O.TASK_ID=T.ID
				INNER JOIN b_operation O ON O.ID=T_O.OPERATION_ID
			WHERE OP.NAME='GROUP_DEFAULT_RIGHT'
		";

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $z->Fetch())
			$arr[$r['OPERATION_NAME']] = $r['OPERATION_NAME'];

		return $arr;
	}

	public function CanDoOperation($op_name, $user_id = 0)
	{
		if ($user_id > 0)
		{
			$arGroups = array();
			$rsGroups = $this->GetUserGroupEx($user_id);
			while ($group = $rsGroups->Fetch())
			{
				$arGroups[] = $group["GROUP_ID"];
			}
			if (!$arGroups)
				return false;

			$op = $this->GetAllOperations($arGroups);
			return isset($op[$op_name]);
		}
		else
		{
			if ($this->IsAdmin())
				return true;

			if(!isset($_SESSION["SESS_OPERATIONS"]))
				$_SESSION["SESS_OPERATIONS"] = $this->GetAllOperations();

			return isset($_SESSION["SESS_OPERATIONS"][$op_name]);
		}
	}

	public static function GetFileOperations($arPath, $arGroups=false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$ar = $APPLICATION->GetFileAccessPermission($arPath, $arGroups, true);
		$arFileOperations = array();

		for ($i = 0, $len = count($ar); $i < $len; $i++)
			$arFileOperations = array_merge($arFileOperations, CTask::GetOperations($ar[$i], true));
		$arFileOperations = array_values(array_unique($arFileOperations));

		return $arFileOperations;
	}


	public function CanDoFileOperation($op_name, $arPath)
	{
		global $APPLICATION, $USER;

		if ($this->IsAdmin())
			return true;

		if(!isset($APPLICATION->FILEMAN_OPERATION_CACHE))
			$APPLICATION->FILEMAN_OPERATION_CACHE = array();

		$k = addslashes($arPath[0].'|'.$arPath[1]);
		if(array_key_exists($k, $APPLICATION->FILEMAN_OPERATION_CACHE))
		{
			$arFileOperations = $APPLICATION->FILEMAN_OPERATION_CACHE[$k];
		}
		else
		{
			$arFileOperations = $this->GetFileOperations($arPath);
			$APPLICATION->FILEMAN_OPERATION_CACHE[$k] = $arFileOperations;
		}

		$arAlowedOperations = array('fm_delete_file','fm_rename_folder','fm_view_permission');
		if(substr($arPath[1], -10)=="/.htaccess" && !$USER->CanDoOperation('edit_php') && !in_array($op_name,$arAlowedOperations))
			return false;
		if(substr($arPath[1], -12)=="/.access.php")
			return false;

		return in_array($op_name, $arFileOperations);
	}

	public static function UserTypeRightsCheck($entity_id)
	{
		global $USER;

		if($entity_id == "USER" && $USER->CanDoOperation('edit_other_settings'))
		{
			return "W";
		}
		else
			return "D";
	}

	public function CanAccess($arCodes)
	{
		if(!is_array($arCodes) || empty($arCodes))
			return false;

		if(in_array('G2', $arCodes))
			return true;

		if($this->IsAuthorized() && in_array('AU', $arCodes))
			return true;

		$bEmpty = true;
		foreach($arCodes as $code)
		{
			if(trim($code) <> '')
			{
				$bEmpty = false;
				break;
			}
		}

		if($bEmpty)
			return false;

		$res = CAccess::GetUserCodes($this->GetID(), array("ACCESS_CODE"=>$arCodes));
		if($res->Fetch())
			return true;

		return false;
	}

	public function GetAccessCodes()
	{
		if(!$this->IsAuthorized())
			return array('G2');

		static $arCodes = array();

		$USER_ID = intval($this->GetID());

		if(!array_key_exists($USER_ID, $arCodes))
		{
			$arCodes[$USER_ID] = CAccess::GetUserCodesArray($USER_ID);

			if($this->IsAuthorized())
				$arCodes[$USER_ID][] = "AU";
		}

		return $arCodes[$USER_ID];
	}

	public static function CleanUpAgent()
	{
		$cleanup_days = COption::GetOptionInt("main", "new_user_registration_cleanup_days", 7);
		if($cleanup_days > 0)
		{
			$date = new Main\Type\Date();
			$date->add("-{$cleanup_days}D");

			if(COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") === "Y")
			{
				//unconfirmed email confirmations
				$filter = array(
					"!CONFIRM_CODE" => false,
					"=ACTIVE" => "N",
					"<DATE_REGISTER" => $date,
				);
				$users = Main\UserTable::getList([
					"filter" => $filter,
					"select" => ["ID"],
				]);
				while($user = $users->fetch())
				{
					CUser::Delete($user["ID"]);
				}
			}
			if(COption::GetOptionString("main", "new_user_phone_auth", "N") === "Y")
			{
				//unconfirmed phone confirmations
				$filter = array(
					'=\Bitrix\Main\UserPhoneAuthTable:USER.CONFIRMED' => "N",
					"=ACTIVE" => "N",
					"<DATE_REGISTER" => $date,
				);
				$users = Main\UserTable::getList([
					"filter" => $filter,
					"select" => ["ID"],
				]);
				while($user = $users->fetch())
				{
					CUser::Delete($user["ID"]);
				}
			}
		}
		return "CUser::CleanUpAgent();";
	}

	public static function DeactivateAgent()
	{
		$blockDays = COption::GetOptionInt("main", "inactive_users_block_days", 0);
		if($blockDays > 0)
		{
			$userObj = new CUser();

			$date = new Main\Type\Date();
			$date->add("-{$blockDays}D");

			$filter = array(
				"=ACTIVE" => "Y",
				"<LAST_LOGIN" => $date,
			);
			$users = Main\UserTable::getList([
				"filter" => $filter,
				"select" => ["ID"],
			]);
			while($user = $users->fetch())
			{
				if($user["ID"] <> 1)
				{
					$userObj->Update($user["ID"], ["ACTIVE" => "N"], false);
				}
			}
		}
		return "CUser::DeactivateAgent();";
	}

	public static function UnblockAgent($userId)
	{
		$user = new CUser;
		$user->Update($userId, ["ACTIVE" => "Y", "LOGIN_ATTEMPTS" => 0]);

		return "";
	}

	public static function GetActiveUsersCount()
	{
		global $DB;

		$q = "SELECT COUNT(ID) as C FROM b_user WHERE ACTIVE = 'Y' AND LAST_LOGIN IS NOT NULL";
		if (IsModuleInstalled("intranet"))
			$q = "SELECT COUNT(U.ID) as C FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT 'x' FROM b_utm_user UF, b_user_field F WHERE F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT' AND UF.FIELD_ID = F.ID AND UF.VALUE_ID = U.ID AND UF.VALUE_INT IS NOT NULL AND UF.VALUE_INT <> 0)";

		$dbRes = $DB->Query($q, true);
		if ($dbRes && ($arRes = $dbRes->Fetch()))
			return $arRes["C"];
		else
			return 0;
	}

	public static function SetLastActivityDate($userId = null, $cache = false)
	{
		global $USER;

		if (is_null($userId))
		{
			$userId = $USER->GetId();
		}

		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		if($userId == $USER->GetId())
		{
			$_SESSION['SESS_AUTH']['PREV_LAST_ACTIVITY'] = $_SESSION['SESS_AUTH']['SET_LAST_ACTIVITY'];

			if ($cache)
			{
				if (
					isset($_SESSION['SESS_AUTH']['SET_LAST_ACTIVITY'])
					&& intval($_SESSION['SESS_AUTH']['SET_LAST_ACTIVITY'])+60 > time()
				)
				{
					return false;
				}
			}
			$_SESSION['SESS_AUTH']['SET_LAST_ACTIVITY'] = time();
		}

		self::SetLastActivityDateByArray(array($userId), $_SERVER['REMOTE_ADDR']);

		return true;
	}

	public static function SetLastActivityDateByArray($arUsers, $ip = null)
	{
		global $DB;

		if (!is_array($arUsers) || count($arUsers) <= 0)
			return false;

		$strSqlPrefix = "UPDATE b_user SET ".
			"TIMESTAMP_X = ".(strtoupper($DB->type) == "ORACLE"? "NULL":"TIMESTAMP_X").", ".
			"LAST_ACTIVITY_DATE = ".$DB->CurrentTimeFunction()." WHERE ID IN (";
		$strSqlPostfix = ")";
		$maxValuesLen = 2048;
		$strSqlValues = "";

		$arUsers = array_map("intval", $arUsers);
		foreach($arUsers as $userId)
		{
			$strSqlValues .= ",$userId";
			if(strlen($strSqlValues) > $maxValuesLen)
			{
				$DB->Query($strSqlPrefix.substr($strSqlValues, 1).$strSqlPostfix, false, "", array("ignore_dml"=>true));
				$strSqlValues = "";
			}
		}

		if(strlen($strSqlValues) > 0)
		{
			$DB->Query($strSqlPrefix.substr($strSqlValues, 1).$strSqlPostfix, false, "", array("ignore_dml"=>true));
		}

		$event = new \Bitrix\Main\Event("main", "OnUserSetLastActivityDate", array($arUsers, $ip));
		$event->send();

		return true;
	}

	public static function GetSecondsForLimitOnline()
	{
		return \Bitrix\Main\UserTable::getSecondsForLimitOnline();
	}

	public static function GetExternalUserTypes()
	{
		return Main\UserTable::getExternalUserTypes();
	}

	public static function GetOnlineStatus($userId, $lastseen, $now = false)
	{
		$userId = intval($userId);

		if ($lastseen instanceof \Bitrix\Main\Type\DateTime)
		{
			$lastseen = $lastseen->getTimestamp();
		}
		else if (is_int($lastseen))
		{
			$lastseen = intval($lastseen);
		}
		else
		{
			$lastseen = 0;
		}

		if ($now === false)
		{
			$now = time();
		}
		else if ($now instanceof \Bitrix\Main\Type\DateTime)
		{
			$now = $now->getTimestamp();
		}
		else
		{
			$now = intval($now);
		}

		$result = Array(
			'IS_ONLINE' => false,
			'STATUS' => self::STATUS_OFFLINE,
			'STATUS_TEXT' =>  GetMessage('USER_STATUS_OFFLINE'),
			'LAST_SEEN' => $lastseen,
			'LAST_SEEN_TEXT' => "",
			'NOW' => $now,
		);

		if ($lastseen === false)
		{
			return $result;
		}

		$result['IS_ONLINE'] = $now - $lastseen <= self::GetSecondsForLimitOnline();
		$result['STATUS'] = $result['IS_ONLINE']? self::STATUS_ONLINE: self::STATUS_OFFLINE;
		$result['STATUS_TEXT'] = GetMessage('USER_STATUS_'.strtoupper($result['STATUS']));

		if ($lastseen && $now - $lastseen > 300)
		{
			$result['LAST_SEEN_TEXT'] = self::FormatLastActivityDate($lastseen, $now);
		}

		if ($userId > 0)
		{
			if ($result['IS_ONLINE'])
			{
				foreach(GetModuleEvents("main", "OnUserOnlineStatusGetCustomOnlineStatus", true) as $arEvent)
				{
					$customStatus = ExecuteModuleEventEx($arEvent, array($userId, $lastseen, $now, self::STATUS_ONLINE));
					if (is_array($customStatus))
					{
						if (!empty($customStatus['STATUS']) && !empty($customStatus['STATUS_TEXT']))
						{
							$result['STATUS'] = strtolower($customStatus['STATUS']);
							$result['STATUS_TEXT'] = $customStatus['STATUS_TEXT'];
						}
						if (isset($customStatus['LAST_SEEN']) && intval($customStatus['LAST_SEEN']) > 0)
						{
							$result['LAST_SEEN'] = intval($customStatus['LAST_SEEN']);
						}
						if (isset($customStatus['LAST_SEEN_TEXT']))
						{
							$result['LAST_SEEN_TEXT'] = $customStatus['LAST_SEEN_TEXT'];
						}
					}
				}
			}
			else
			{
				foreach(GetModuleEvents("main", "OnUserOnlineStatusGetCustomOfflineStatus", true) as $arEvent)
				{
					$customStatus = ExecuteModuleEventEx($arEvent, array($userId, $lastseen, $now, self::STATUS_OFFLINE));
					if (is_array($customStatus))
					{
						if (!empty($customStatus['STATUS']) && !empty($customStatus['STATUS_TEXT']))
						{
							$result['STATUS'] = strtolower($customStatus['STATUS']);
							$result['STATUS_TEXT'] = $customStatus['STATUS_TEXT'];
						}
						if (isset($customStatus['LAST_SEEN']) && intval($customStatus['LAST_SEEN']) > 0)
						{
							$result['LAST_SEEN'] = intval($customStatus['LAST_SEEN']);
						}
						if (isset($customStatus['LAST_SEEN_TEXT']))
						{
							$result['LAST_SEEN_TEXT'] = $customStatus['LAST_SEEN_TEXT'];
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param int|bool|\Bitrix\Main\Type\DateTime $timestamp
	 * @param int|bool|\Bitrix\Main\Type\DateTime $now
	 *
	 * @return string
	 */
	public static function FormatLastActivityDate($timestamp, $now = false)
	{
		global $DB;

		if ($timestamp instanceof \Bitrix\Main\Type\DateTime)
		{
			$timestamp = $timestamp->getTimestamp();
		}
		else if (is_int($timestamp))
		{
			$timestamp = intval($timestamp);
		}
		else
		{
			return "";
		}

		if ($now === false)
		{
			$now = time();
		}
		else if ($now instanceof \Bitrix\Main\Type\DateTime)
		{
			$now = $now->getTimestamp();
		}
		else
		{
			$now = intval($now);
		}

		$ampm = IsAmPmMode(true);
		$timeFormat = ($ampm === AM_PM_LOWER? "g:i a" : ($ampm === AM_PM_UPPER? "g:i A" : "H:i"));

		$formattedDate = FormatDate(array(
			"tomorrow" => "#01#{$timeFormat}",
			"now" => "#02#",
			"todayFuture" => "#03#{$timeFormat}",
			"yesterday" => "#04#{$timeFormat}",
			"-" => preg_replace('/:s$/', '', $DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
			"s60" => "sago",
			"i60" => "iago",
			"H5" => "Hago",
			"H24" => "#03#{$timeFormat}",
			"d31" => "dago",
			"m12>1" => "mago",
			"m12>0" => "dago",
			"" => "#05#",
		), $timestamp, $now);

		if (preg_match('/^#(\d+)#(.*)/', $formattedDate, $match))
		{
			switch($match[1])
			{
				case "01":
					$formattedDate = str_replace("#TIME#", $match[2], GetMessage('USER_LAST_SEEN_TOMORROW'));
				break;
				case "02":
					$formattedDate = GetMessage('USER_LAST_SEEN_NOW');
				break;
				case "03":
					$formattedDate = str_replace("#TIME#", $match[2], GetMessage('USER_LAST_SEEN_TODAY'));
				break;
				case "04":
					$formattedDate = str_replace("#TIME#", $match[2], GetMessage('USER_LAST_SEEN_YESTERDAY'));
				break;
				case "05":
					$formattedDate = GetMessage('USER_LAST_SEEN_MORE_YEAR');
				break;
				default:
					$formattedDate = $match[2];
				break;
			}
		}

		return $formattedDate;
	}

	public static function SearchUserByName($arName, $email = "", $bLoginMode = false)
	{
		global $DB;

		$arNameReady = array();
		foreach ($arName as $s)
		{
			$s = Trim($s);
			if (StrLen($s) > 0)
				$arNameReady[] = $s;
		}

		if (Count($arNameReady) <= 0)
			return false;

		$strSqlWhereEMail = ((StrLen($email) > 0) ? " AND upper(U.EMAIL) = upper('".$DB->ForSql($email)."') " : "");

		if ($bLoginMode)
		{
			if (count($arNameReady) > 3)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (";
				$bFirst = true;
				for ($i = 0; $i < 4; $i++)
				{
					for ($j = 0; $j < 4; $j++)
					{
						if ($i == $j)
							continue;

						for ($k = 0; $k < 4; $k++)
						{
							if ($i == $k || $j == $k)
								continue;

							for ($l = 0; $l < 4; $l++)
							{
								if ($i == $l || $j == $l || $k == $l)
									continue;

								if (!$bFirst)
									$strSql .= " OR ";

								$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
									"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
									"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$k])."%') ".
									"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$l])."%'))";

								$bFirst = false;
							}
						}
					}
				}
				$strSql .= ")";
			}
			elseif (Count($arNameReady) == 3)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (";
				$bFirst = true;
				for ($i = 0; $i < 3; $i++)
				{
					for ($j = 0; $j < 3; $j++)
					{
						if ($i == $j)
							continue;

						for ($k = 0; $k < 3; $k++)
						{
							if ($i == $k || $j == $k)
								continue;

							if (!$bFirst)
								$strSql .= " OR ";

							$strSql .= "(";
							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= " OR ";
							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= " OR ";
							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= " OR ";
							$strSql .= "(U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= ")";

							$bFirst = false;
						}
					}
				}
				$strSql .= ")";
			}
			elseif (Count($arNameReady) == 2)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (";
				$bFirst = true;
				for ($i = 0; $i < 2; $i++)
				{
					for ($j = 0; $j < 2; $j++)
					{
						if ($i == $j)
							continue;

						if (!$bFirst)
							$strSql .= " OR ";

						$strSql .= "(";
						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= ")";
						$bFirst = false;
					}
				}
				$strSql .= ")";
			}
			else
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					"	OR U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					"	OR U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					"	OR U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[0])."%')) ";
			}
			$strSql .= $strSqlWhereEMail;
		}
		else
		{
			if (Count($arNameReady) >= 3)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE ";
				$bFirst = true;
				for ($i = 0; $i < 3; $i++)
				{
					for ($j = 0; $j < 3; $j++)
					{
						if ($i == $j)
							continue;

						for ($k = 0; $k < 3; $k++)
						{
							if ($i == $k || $j == $k)
								continue;

							if (!$bFirst)
								$strSql .= " OR ";

							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.SECOND_NAME IS NOT NULL AND upper(U.SECOND_NAME) LIKE upper('".$DB->ForSql($arNameReady[$k])."%')".$strSqlWhereEMail.")";

							$bFirst = false;
						}
					}
				}
			}
			elseif (Count($arNameReady) == 2)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE ";
				$bFirst = true;
				for ($i = 0; $i < 2; $i++)
				{
					for ($j = 0; $j < 2; $j++)
					{
						if ($i == $j)
							continue;

						if (!$bFirst)
							$strSql .= " OR ";

						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%')".$strSqlWhereEMail.")";

						$bFirst = false;
					}
				}
			}
			else
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					$strSqlWhereEMail;
			}
		}

		$dbRes = $DB->Query($strSql);
		return $dbRes;
	}

	public static function FormatName($NAME_TEMPLATE, $arUser, $bUseLogin = false, $bHTMLSpec = true)
	{
		if (isset($arUser["ID"]))
			$ID = intval($arUser['ID']);
		else
			$ID = '';

		$NAME_SHORT = ($arUser['NAME'] <> ''? substr($arUser['NAME'], 0, 1).'.' : '');
		$LAST_NAME_SHORT = ($arUser['LAST_NAME'] <> ''? substr($arUser['LAST_NAME'], 0, 1).'.' : '');
		$SECOND_NAME_SHORT = ($arUser['SECOND_NAME'] <> ''? substr($arUser['SECOND_NAME'], 0, 1).'.' : '');

		$res = str_replace(
			array('#TITLE#', '#NAME#', '#LAST_NAME#', '#SECOND_NAME#', '#NAME_SHORT#', '#LAST_NAME_SHORT#', '#SECOND_NAME_SHORT#', '#EMAIL#', '#ID#'),
			array($arUser['TITLE'], $arUser['NAME'], $arUser['LAST_NAME'], $arUser['SECOND_NAME'], $NAME_SHORT, $LAST_NAME_SHORT, $SECOND_NAME_SHORT, $arUser['EMAIL'], $ID),
			$NAME_TEMPLATE
		);

		while(strpos($res, "  ") !== false)
		{
			$res = str_replace("  ", " ", $res);
		}
		$res = trim($res);

		$res_check = "";
		if (strpos($NAME_TEMPLATE, '#NAME#') !== false || strpos($NAME_TEMPLATE, '#NAME_SHORT#') !== false)
			$res_check .= $arUser['NAME'];
		if (strpos($NAME_TEMPLATE, '#LAST_NAME#') !== false || strpos($NAME_TEMPLATE, '#LAST_NAME_SHORT#') !== false)
			$res_check .= $arUser['LAST_NAME'];
		if (strpos($NAME_TEMPLATE, '#SECOND_NAME#') !== false || strpos($NAME_TEMPLATE, '#SECOND_NAME_SHORT#') !== false)
			$res_check .= $arUser['SECOND_NAME'];

		if (trim($res_check) == '')
		{
			if ($bUseLogin && $arUser['LOGIN'] <> '')
				$res = $arUser['LOGIN'];
			else
				$res = GetMessage('FORMATNAME_NONAME');

			if (strpos($NAME_TEMPLATE, '[#ID#]') !== false)
				$res .= " [".$ID."]";
		}

		if ($bHTMLSpec)
			$res = htmlspecialcharsbx($res);

		$res = str_replace(array('#NOBR#', '#/NOBR#'), '', $res);

		return $res;
	}

	public static function clearUserGroupCache($ID = false)
	{
		if ($ID === false)
		{
			self::$userGroupCache = array();
		}
		else
		{
			$ID = (int)$ID;
			if (isset(self::$userGroupCache[$ID]))
				unset(self::$userGroupCache[$ID]);
		}
	}

	public function CheckAuthActions()
	{
		if(!$this->IsAuthorized())
		{
			return;
		}

		if(!is_array($_SESSION["AUTH_ACTIONS_PERFORMED"]))
		{
			$_SESSION["AUTH_ACTIONS_PERFORMED"] = array();
		}

		$user_id = $this->GetID();

		//calculate a session lifetime
		$policy = $this->GetSecurityPolicy();
		$phpSessTimeout = ini_get("session.gc_maxlifetime");
		if($policy["SESSION_TIMEOUT"] > 0)
		{
			$interval = min($policy["SESSION_TIMEOUT"]*60, $phpSessTimeout);
		}
		else
		{
			$interval = $phpSessTimeout;
		}
		$now = new Main\Type\DateTime();
		$date = new Main\Type\DateTime();
		$date->add("-T".$interval."S");

		$actions = Main\UserAuthActionTable::getList(array(
			"filter" => array("=USER_ID" => $user_id),
			"order" => array("USER_ID" => "ASC", "PRIORITY" => "ASC", "ID" => "DESC"),
			"cache" => array("ttl" => 3600),
		));

		$deleted = false;
		while($action = $actions->fetch())
		{
			if($deleted == false)
			{
				//clear expired records for the user
				Main\UserAuthActionTable::deleteByFilter(array(
					"=USER_ID" => $user_id,
					"<ACTION_DATE" => $date,
				));
				$deleted = true;
			}

			if(isset($_SESSION["AUTH_ACTIONS_PERFORMED"][$action["ID"]]))
			{
				//already processed the action in this session
				continue;
			}

			/** @var Main\Type\DateTime() $actionDate */
			$actionDate = $action["ACTION_DATE"];

			if($actionDate >= $date && $actionDate <= $now)
			{
				//remember that we already did the action
				$_SESSION["AUTH_ACTIONS_PERFORMED"][$action["ID"]] = true;

				if($this->IsJustAuthorized())
				{
					//no need to update the session
					continue;
				}

				switch($action["ACTION"])
				{
					case Main\UserAuthActionTable::ACTION_LOGOUT:
						if($this->GetParam("SELF_CHANGED_PASSWORD") == true)
						{
							//user's changed password by himself, skip logout
							$this->SetParam("SELF_CHANGED_PASSWORD", false);
							break;
						}
						//redirect is possible
						$this->Logout();
						break;

					case Main\UserAuthActionTable::ACTION_UPDATE:
						$this->UpdateSessionData($user_id, $this->GetParam("APPLICATION_ID"));
						break;
				}

				//we need to process only the first action by proirity
				break;
			}
		}
	}

	public static function AuthActionsCleanUpAgent()
	{
		$date = new Main\Type\DateTime();
		$date->add("-1D");
		Main\UserAuthActionTable::deleteByFilter(array("<ACTION_DATE" => $date));
		return 'CUser::AuthActionsCleanUpAgent();';
	}

	/**
	 * @param int $userId
	 * @return array|bool [code, phone_number]
	 */
	public static function GeneratePhoneCode($userId)
	{
		$row = Main\UserPhoneAuthTable::getRowById($userId);
		if($row && $row["OTP_SECRET"] <> '')
		{
			$secret = base64_decode($row["OTP_SECRET"]);

			$totp = new Main\Security\Mfa\TotpAlgorithm();
			$totp->setInterval(self::PHONE_CODE_OTP_INTERVAL);
			$totp->setSecret($secret);

			$timecode = $totp->timecode(time());
			$code = $totp->generateOTP($timecode);

			Main\UserPhoneAuthTable::update($userId, array(
				"ATTEMPTS" => 0,
				"DATE_SENT" => new Main\Type\DateTime(),
			));

			return [$code, $row["PHONE_NUMBER"]];
		}
		return false;
	}

	/**
	 * @param string $phoneNumber
	 * @param string $code
	 * @return bool|int User ID on success, false on error
	 */
	public static function VerifyPhoneCode($phoneNumber, $code)
	{
		if($code == '')
		{
			return false;
		}

		$phoneNumber = Main\UserPhoneAuthTable::normalizePhoneNumber($phoneNumber);

		$row = Main\UserPhoneAuthTable::getList(["filter" => ["=PHONE_NUMBER" => $phoneNumber]])->fetch();
		if($row && $row["OTP_SECRET"] <> '')
		{
			if($row["ATTEMPTS"] >= 3)
			{
				return false;
			}

			$secret = base64_decode($row["OTP_SECRET"]);

			$totp = new Main\Security\Mfa\TotpAlgorithm();
			$totp->setInterval(self::PHONE_CODE_OTP_INTERVAL);
			$totp->setSecret($secret);

			try
			{
				list($result, ) = $totp->verify($code);
			}
			catch(Main\ArgumentException $e)
			{
				return false;
			}

			$data = array();
			if($result)
			{
				if($row["CONFIRMED"] == "N")
				{
					$data["CONFIRMED"] = "Y";
				}

				$data['DATE_SENT'] = '';
			}
			else
			{
				$data["ATTEMPTS"] = (int)$row["ATTEMPTS"] + 1;
			}

			if(!empty($data))
			{
				Main\UserPhoneAuthTable::update($row["USER_ID"], $data);
			}

			if($result)
			{
				return $row["USER_ID"];
			}
		}
		return false;
	}
}

class CAllGroup
{
	var $LAST_ERROR;

	public static function err_mess()
	{
		return "<br>Class: CAllGroup<br>File: ".__FILE__;
	}

	public function CheckFields($arFields, $ID=false)
	{
		global $DB;
		$this->LAST_ERROR = "";

		if(is_set($arFields, "NAME") && $arFields["NAME"] == '')
			$this->LAST_ERROR .= GetMessage("BAD_GROUP_NAME")."<br>";

		if (is_array($arFields["USER_ID"]) && count($arFields["USER_ID"]) > 0)
		{
			if (is_array($arFields["USER_ID"][0]) && count($arFields["USER_ID"][0]) > 0)
			{
				foreach($arFields["USER_ID"] as $arUser)
				{
					if($arUser["DATE_ACTIVE_FROM"] <> '' && !CheckDateTime($arUser["DATE_ACTIVE_FROM"]))
					{
						$error = str_replace("#USER_ID#", $arUser["USER_ID"], GetMessage("WRONG_USER_DATE_ACTIVE_FROM"));
						$this->LAST_ERROR .= $error."<br>";
					}

					if($arUser["DATE_ACTIVE_TO"] <> '' && !CheckDateTime($arUser["DATE_ACTIVE_TO"]))
					{
						$error = str_replace("#USER_ID#", $arUser["USER_ID"], GetMessage("WRONG_USER_DATE_ACTIVE_TO"));
						$this->LAST_ERROR .= $error."<br>";
					}
				}
			}
		}
		if (isset($arFields['STRING_ID']) && $arFields['STRING_ID'] <> '')
		{
			$sql_str = "SELECT G.ID
					FROM b_group G
					WHERE G.STRING_ID='".$DB->ForSql($arFields['STRING_ID'])."'";
			$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			if ($r = $z->Fetch())
			{
				if ($ID === false || $ID != $r['ID'])
					$this->LAST_ERROR .= GetMessage('MAIN_ERROR_STRING_ID')."<br>";
			}
		}
		if($this->LAST_ERROR <> '')
			return false;

		return true;
	}

	public function Update($ID, $arFields)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		$ID = intval($ID);

		if(!$this->CheckFields($arFields, $ID))
			return false;

		foreach(GetModuleEvents("main", "OnBeforeGroupUpdate", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
					$this->LAST_ERROR .= "Unknown error in OnBeforeGroupUpdate handler."."<br>";
				return false;
			}
		}

		if($ID<=2)
			unset($arFields["ACTIVE"]);

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		$strUpdate = $DB->PrepareUpdate("b_group", $arFields);

		if(!is_set($arFields, "TIMESTAMP_X"))
			$strUpdate .= ", TIMESTAMP_X = ".$DB->GetNowFunction();


		$strSql = "UPDATE b_group SET $strUpdate WHERE ID=".$ID;
		if(is_set($arFields, "SECURITY_POLICY"))
		{
			if(COption::GetOptionString("main", "event_log_group_policy", "N") === "Y")
			{
				//get old security policy
				$aPrevPolicy = array();
				$res = $DB->Query("SELECT SECURITY_POLICY FROM b_group WHERE ID=".$ID);
				if(($res_arr = $res->Fetch()) && $res_arr["SECURITY_POLICY"] <> '')
					$aPrevPolicy = unserialize($res_arr["SECURITY_POLICY"]);
				//compare with new one
				$aNewPolicy = array();
				if($arFields["SECURITY_POLICY"] <> '')
					$aNewPolicy = unserialize($arFields["SECURITY_POLICY"]);
				$aDiff = array_diff_assoc($aNewPolicy, $aPrevPolicy);
				if(empty($aDiff))
					$aDiff = array_diff_assoc($aPrevPolicy, $aNewPolicy);
				if(!empty($aDiff))
					CEventLog::Log("SECURITY", "GROUP_POLICY_CHANGED", "main", $ID, print_r($aPrevPolicy, true)." => ".print_r($aNewPolicy, true));
			}
			$DB->QueryBind($strSql, array("SECURITY_POLICY"=>$arFields["SECURITY_POLICY"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		else
		{
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		if(is_set($arFields, "USER_ID") && is_array($arFields["USER_ID"]))
		{
			$log = (COption::GetOptionString("main", "event_log_user_groups", "N") === "Y");
			if($log)
			{
				//remember users in the group
				$aPrevUsers = array();
				$res = $DB->Query("SELECT USER_ID FROM b_user_group WHERE GROUP_ID=".$ID.($ID=="1"?" AND USER_ID<>1":""));
				while($res_arr = $res->Fetch())
					$aPrevUsers[] = $res_arr["USER_ID"];
			}

			$DB->Query("DELETE FROM b_user_group WHERE GROUP_ID=".$ID.($ID=="1"?" AND USER_ID<>1":""));

			$arUsers = $arFields["USER_ID"];
			$arTmp = array();
			foreach($arUsers as $user)
			{
				if(!is_array($user))
					$user = array("USER_ID" => $user);

				$user_id = intval($user["USER_ID"]);
				if(
					$user_id > 0
					&& !isset($arTmp[$user_id])
					&& ($ID != 1 || $user_id != 1)
				)
				{
					$arInsert = $DB->PrepareInsert("b_user_group", $user);
					$strSql = "
						INSERT INTO b_user_group (
							GROUP_ID, ".$arInsert[0]."
						) VALUES (
							".$ID.", ".$arInsert[1]."
						)
					";
					$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					$arTmp[$user_id] = true;
				}
			}
			$aNewUsers = array_keys($arTmp);
			CUser::clearUserGroupCache();

			if($log)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				foreach($aPrevUsers as $user_id)
				{
					if(!in_array($user_id, $aNewUsers))
					{
						$UserName = '';
						$rsUser = CUser::GetByID($user_id);
						if($arUser = $rsUser->GetNext())
							$UserName = ($arUser["NAME"] != "" || $arUser["LAST_NAME"] != "") ? trim($arUser["NAME"]." ".$arUser["LAST_NAME"]) : $arUser["LOGIN"];
						$res_log = array(
							"groups" => "-(".$ID.")",
							"user" => $UserName
						);
						CEventLog::Log("SECURITY", "USER_GROUP_CHANGED", "main", $user_id, serialize($res_log));
					}
				}

				foreach($aNewUsers as $user_id)
				{
					if(!in_array($user_id, $aPrevUsers))
					{
						$UserName = '';
						$rsUser = CUser::GetByID($user_id);
						if($arUser = $rsUser->GetNext())
							$UserName = ($arUser["NAME"] != "" || $arUser["LAST_NAME"] != "") ? trim($arUser["NAME"]." ".$arUser["LAST_NAME"]) : $arUser["LOGIN"];
						$res_log = array(
							"groups" =>  "+(".$ID.")",
							"user" => $UserName
						);
						CEventLog::Log("SECURITY", "USER_GROUP_CHANGED", "main", $user_id, serialize($res_log));
					}
				}
			}
		}

		foreach (GetModuleEvents("main", "OnAfterGroupUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, &$arFields));

		return true;
	}

	public static function Delete($ID)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB;

		$ID = intval($ID);
		if($ID<=2)
			return false;

		@set_time_limit(600);

		foreach(GetModuleEvents("main", "OnBeforeGroupDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}
		}

		foreach(GetModuleEvents("main", "OnGroupDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		CMain::DelGroupRight("",array($ID));

		if(!$DB->Query("DELETE FROM b_user_group WHERE GROUP_ID=".$ID." AND GROUP_ID>2", true))
			return false;
		CUser::clearUserGroupCache();

		return $DB->Query("DELETE FROM b_group WHERE ID=".$ID." AND ID>2", true);
	}

	public static function GetGroupUser($ID)
	{
		global $DB;
		$ID = intval($ID);

		if ($ID == 2)
		{
			$strSql = "SELECT U.ID as USER_ID FROM b_user U ";
		}
		else
		{
			$strSql =
				"SELECT UG.USER_ID ".
				"FROM b_user_group UG ".
				"WHERE UG.GROUP_ID = ".$ID." ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";
		}

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $res->Fetch())
			$arr[]=$r["USER_ID"];

		return $arr;
	}

	public static function GetGroupUserEx($ID)
	{
		global $DB;
		$ID = intval($ID);

		if ($ID == 2)
		{
			$strSql = "SELECT U.ID as USER_ID, NULL as DATE_ACTIVE_FROM, NULL as DATE_ACTIVE_TO FROM b_user U ";
		}
		else
		{
			$strSql =
				"SELECT UG.USER_ID, ".
				"	".$DB->DateToCharFunction("UG.DATE_ACTIVE_FROM", "FULL")." as DATE_ACTIVE_FROM, ".
				"	".$DB->DateToCharFunction("UG.DATE_ACTIVE_TO", "FULL")." as DATE_ACTIVE_TO ".
				"FROM b_user_group UG ".
				"WHERE UG.GROUP_ID = ".$ID." ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";
		}
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	public static function GetMaxSort()
	{
		global $DB;
		$err_mess = (CAllGroup::err_mess())."<br>Function: GetMaxSort<br>Line: ";
		$z = $DB->Query("SELECT max(C_SORT) M FROM b_group", false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["M"])+100;
	}

	public static function GetSubordinateGroups($grId)
	{
		global $DB, $CACHE_MANAGER;

		$groupFilter = array();
		if (is_array($grId))
		{
			foreach ($grId as $id)
			{
				$id = intval($id);
				if ($id > 0)
					$groupFilter[$id] = $id;
			}
		}
		else
		{
			$id = intval($grId);
			if ($id > 0)
				$groupFilter[$id] = $id;
		}

		$result = array(2);
		if (!empty($groupFilter))
		{
			if (CACHED_b_group_subordinate === false)
			{
				$z = $DB->Query("SELECT AR_SUBGROUP_ID FROM b_group_subordinate WHERE ID in (".implode(", ", $groupFilter).")");
				while ($zr = $z->Fetch())
				{
					$subordinateGroups = explode(",", $zr['AR_SUBGROUP_ID']);
					if (count($subordinateGroups) == 1 && !$subordinateGroups[0])
						continue;
					$result = array_merge($result, $subordinateGroups);
				}
			}
			else
			{
				if ($CACHE_MANAGER->Read(CACHED_b_group_subordinate, "b_group_subordinate"))
				{
					$cache = $CACHE_MANAGER->Get("b_group_subordinate");
				}
				else
				{
					$cache = array();
					$z = $DB->Query("SELECT ID, AR_SUBGROUP_ID FROM b_group_subordinate");
					while ($zr = $z->Fetch())
					{
						$subordinateGroups = explode(",", $zr['AR_SUBGROUP_ID']);
						if (count($subordinateGroups) == 1 && !$subordinateGroups[0])
							continue;
						$cache[$zr["ID"]] = $subordinateGroups;
					}
					$CACHE_MANAGER->Set("b_group_subordinate", $cache);
				}

				foreach ($cache as $groupId => $subordinateGroups)
				{
					if (isset($groupFilter[$groupId]))
					{
						$result = array_merge($result, $subordinateGroups);
					}
				}
			}
		}

		return array_unique($result);
	}

	public static function SetSubordinateGroups($grId, $arSubGroups=false)
	{
		global $DB, $CACHE_MANAGER;
		$grId = intval($grId);

		$DB->Query("DELETE FROM b_group_subordinate WHERE ID = ".$grId);
		if(is_array($arSubGroups))
		{
			$arInsert = $DB->PrepareInsert("b_group_subordinate", array(
				"ID" => $grId,
				"AR_SUBGROUP_ID" => implode(",", $arSubGroups),
			));
			$DB->Query("INSERT INTO b_group_subordinate(".$arInsert[0].") VALUES (".$arInsert[1].")");
		}
		$CACHE_MANAGER->Clean("b_group_subordinate");
	}


	public static function GetTasks($ID, $onlyMainTasks=true, $module_id=false)
	{
		global $DB;

		$sql_str = 'SELECT GT.TASK_ID,T.MODULE_ID,GT.EXTERNAL_ID
			FROM b_group_task GT
			INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
			WHERE GT.GROUP_ID='.intval($ID);
		if ($module_id !== false)
			$sql_str .= ' AND T.MODULE_ID="'.$DB->ForSQL($module_id).'"';

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		$ex_arr = array();
		while($r = $z->Fetch())
		{
			if (!$r['EXTERNAL_ID'])
				$arr[$r['MODULE_ID']] = $r['TASK_ID'];
			else
				$ex_arr[] = $r;
		}
		if ($onlyMainTasks)
			return $arr;
		else
			return array($arr,$ex_arr);
	}


	public static function SetTasks($ID, $arr)
	{
		global $DB;
		$ID = intval($ID);

		if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old values
			$arOldTasks = array();
			$rsTask = $DB->Query("SELECT TASK_ID FROM b_group_task WHERE GROUP_ID=".$ID);
			while($arTask = $rsTask->Fetch())
				$arOldTasks[] = $arTask["TASK_ID"];
			//compare with new ones
			$aNewTasks = array();
			foreach($arr as $task_id)
				if($task_id > 0)
					$aNewTasks[] = $task_id;
			$aDiff = array_diff($arOldTasks, $aNewTasks);
			if(empty($aDiff))
				$aDiff = array_diff($aNewTasks, $arOldTasks);
			if(!empty($aDiff))
				CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $ID, "(".implode(", ", $arOldTasks).") => (".implode(", ", $aNewTasks).")");
		}

		$sql_str = "DELETE FROM b_group_task WHERE GROUP_ID=".$ID.
				" AND (EXTERNAL_ID IS NULL OR EXTERNAL_ID = '')";
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$sID = "0";
		if(is_array($arr))
			foreach($arr as $task_id)
				$sID .= ",".intval($task_id);

		$DB->Query(
			"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
			"SELECT '".$ID."', ID, '' ".
			"FROM b_task ".
			"WHERE ID IN (".$sID.") "
			, false, "File: ".__FILE__."<br>Line: ".__LINE__
		);
	}


	public static function GetTasksForModule($module_id, $onlyMainTasks = true)
	{
		global $DB;

		$sql_str = "SELECT GT.TASK_ID,GT.GROUP_ID,GT.EXTERNAL_ID,T.NAME
			FROM b_group_task GT
			INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
			WHERE T.MODULE_ID='".$DB->ForSQL($module_id)."'";

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$main_arr = array();
		$ext_arr = array();
		while($r = $z->Fetch())
		{
			if (!$r['EXTERNAL_ID'])
			{
				$main_arr[$r['GROUP_ID']] = array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME']);
			}
			elseif(!$onlyMainTasks)
			{
				if (!isset($ext_arr[$r['GROUP_ID']]))
					$ext_arr[$r['GROUP_ID']] = array();
				$ext_arr[$r['GROUP_ID']][] = array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME'],'EXTERNAL_ID'=>$r['EXTERNAL_ID']);
			}
		}
		if ($onlyMainTasks)
			return $main_arr;
		else
			return array($main_arr,$ext_arr);
	}


	public static function SetTasksForModule($module_id, $arGroupTask)
	{
		global $DB;

		$module_id = $DB->ForSql($module_id);
		$sql_str = "SELECT T.ID
			FROM b_task T
			WHERE T.MODULE_ID='".$module_id."'";
		$r = $DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arIds = array();
		while($arR = $r->Fetch())
			$arIds[] = $arR['ID'];

		if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old values
			$arOldTasks = array();
			if(!empty($arIds))
			{
				$rsTask = $DB->Query("SELECT GROUP_ID, TASK_ID FROM b_group_task WHERE TASK_ID IN (".implode(",", $arIds).")");
				while($arTask = $rsTask->Fetch())
					$arOldTasks[$arTask["GROUP_ID"]] = $arTask["TASK_ID"];
			}
			//compare with new ones
			foreach($arOldTasks as $gr_id=>$task_id)
				if($task_id <> $arGroupTask[$gr_id]['ID'])
					CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $gr_id, $module_id.": (".$task_id.") => (".$arGroupTask[$gr_id]['ID'].")");
			foreach($arGroupTask as $gr_id => $oTask)
				if(intval($oTask['ID']) > 0 && !array_key_exists($gr_id, $arOldTasks))
					CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $gr_id, $module_id.": () => (".$oTask['ID'].")");
		}

		if(!empty($arIds))
		{
			$sql_str = "DELETE FROM b_group_task WHERE TASK_ID IN (".implode(",", $arIds).")";
			$DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		foreach($arGroupTask as $gr_id => $oTask)
		{
			if(intval($oTask['ID']) > 0)
			{
				$DB->Query(
					"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
					"SELECT G.ID, T.ID, '' ".
					"FROM b_group G, b_task T ".
					"WHERE G.ID = ".intval($gr_id)." AND
					T.ID = ".intval($oTask['ID']),
					false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
		}
	}

	public static function GetModulePermission($group_id, $module_id)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB;

		// check module permissions mode
		$strSql = "SELECT T.ID, GT.TASK_ID FROM b_task T LEFT JOIN b_group_task GT ON T.ID=GT.TASK_ID AND GT.GROUP_ID=".intval($group_id)." WHERE T.MODULE_ID='".$DB->ForSql($module_id)."'";
		$dbr_tasks = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($ar_task = $dbr_tasks->Fetch())
		{
			do
			{
				if($ar_task["TASK_ID"]>0)
					return $ar_task["TASK_ID"];
			}
			while ($ar_task = $dbr_tasks->Fetch());

			return false;
		}

		return $APPLICATION->GetGroupRight($module_id, array($group_id), "N", "N");
	}

	public static function SetModulePermission($group_id, $module_id, $permission)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		if(intval($permission)<=0 && $permission != false)
		{
			$strSql = "SELECT T.ID FROM b_task T WHERE T.MODULE_ID='".$DB->ForSql($module_id)."' AND NAME='".$DB->ForSql($permission)."'";
			$db_task = $DB->Query($strSql);
			if($ar_task=$db_task->Fetch())
				$permission = $ar_task['ID'];
		}

		$permission_letter = '';
		if(intval($permission)>0 || $permission === false)
		{
			$strSql = "SELECT T.ID FROM b_task T WHERE T.MODULE_ID='".$DB->ForSql($module_id)."'";
			$dbr_tasks = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arIds = array();
			while($arTask = $dbr_tasks->Fetch())
				$arIds[] = $arTask['ID'];

			if(!empty($arIds))
			{
				$strSql = "DELETE FROM b_group_task WHERE GROUP_ID=".intval($group_id)." AND TASK_ID IN (".implode(",", $arIds).")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			if(intval($permission)>0)
			{
				$DB->Query(
					"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
					"SELECT G.ID, T.ID, '' ".
					"FROM b_group G, b_task T ".
					"WHERE G.ID = ".intval($group_id)." AND T.ID = ".intval($permission),
					false,
					"File: ".__FILE__."<br>Line: ".__LINE__
				);

				$permission_letter = CTask::GetLetter($permission);
			}
		}
		else
		{
			$permission_letter = $permission;
		}

		if($permission_letter <> '')
			$APPLICATION->SetGroupRight($module_id, $group_id, $permission_letter);
		else
			$APPLICATION->DelGroupRight($module_id, array($group_id));
	}

	public static function GetIDByCode($code)
	{
		if(strval(intval($code)) == $code && $code > 0)
			return $code;

		if(strtolower($code) == 'administrators')
			return 1;

		if(strtolower($code) == 'everyone')
			return 2;

		global $DB;

		$strSql = "SELECT G.ID FROM b_group G WHERE G.STRING_ID='".$DB->ForSQL($code)."'";
		$db_res = $DB->Query($strSql);

		if($ar_res = $db_res->Fetch())
			return $ar_res["ID"];

		return false;
	}
}


class CAllTask
{
	public static function err_mess()
	{
		return "<br>Class: CAllTask<br>File: ".__FILE__;
	}

	public static function CheckFields(&$arFields, $ID = false)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		if($ID>0)
			unset($arFields["ID"]);

		$arMsg = array();

		if(($ID===false || is_set($arFields, "NAME")) && $arFields["NAME"] == '')
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage('MAIN_ERROR_STRING_ID_EMPTY'));

		$sql_str = "SELECT T.ID
			FROM b_task T
			WHERE T.NAME='".$DB->ForSQL($arFields['NAME'])."'";
		if ($ID !== false)
			$sql_str .= " AND T.ID <> ".intval($ID);

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if ($r = $z->Fetch())
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage('MAIN_ERROR_STRING_ID_DOUBLE'));

		if (isset($arFields['LETTER']))
		{
			if (preg_match("/[^A-Z]/i", $arFields['LETTER']) || strlen($arFields['LETTER']) > 1)
				$arMsg[] = array("id"=>"LETTER", "text"=> GetMessage('MAIN_TASK_WRONG_LETTER'));
			$arFields['LETTER'] = strtoupper($arFields['LETTER']);
		}
		else
		{
			$arFields['LETTER'] = '';
		}

		if(count($arMsg)>0)
		{
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		if (!isset($arFields['SYS']) || $arFields['SYS'] != "Y")
			$arFields['SYS'] = "N";
		if (!isset($arFields['BINDING']))
			$arFields['BINDING'] = 'module';

		return true;
	}

	public static function Add($arFields)
	{
		global $CACHE_MANAGER, $DB;

		if(!CTask::CheckFields($arFields))
			return false;

		if(CACHED_b_task !== false)
			$CACHE_MANAGER->CleanDir("b_task");

		$ID = $DB->Add("b_task", $arFields);
		return $ID;
	}

	public static function Update($arFields,$ID)
	{
		global $DB, $CACHE_MANAGER;

		if(!CTask::CheckFields($arFields,$ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_task", $arFields);

		if($strUpdate)
		{
			if(CACHED_b_task !== false)
				$CACHE_MANAGER->CleanDir("b_task");
			$strSql =
				"UPDATE b_task SET ".
					$strUpdate.
				" WHERE ID=".intval($ID);
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}

	public static function UpdateModuleRights($id, $moduleId, $letter, $site_id = false)
	{
		global $DB;

		if (!isset($id, $moduleId))
			return false;

		$sql = "SELECT GT.GROUP_ID
				FROM b_group_task GT
				WHERE GT.TASK_ID=".intval($id);
		$z = $DB->Query($sql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$arGroups = array();
		while($r = $z->Fetch())
		{
			$g = intval($r['GROUP_ID']);
			if ($g > 0)
				$arGroups[] = $g;
		}
		if (count($arGroups) == 0)
			return false;

		$str_groups = implode(',', $arGroups);
		$moduleId = $DB->ForSQL($moduleId);
		$DB->Query(
			"DELETE FROM b_module_group
			WHERE
				MODULE_ID = '".$moduleId."' AND
				SITE_ID ".($site_id ? "='".$site_id."'" : "IS NULL")." AND
				GROUP_ID IN (".$str_groups.")",
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__
		);

		if ($letter == '')
			return false;

		$letter = $DB->ForSQL($letter);
		$DB->Query(
			"INSERT INTO b_module_group (MODULE_ID, GROUP_ID, G_ACCESS, SITE_ID) ".
			"SELECT '".$moduleId."', G.ID, '".$letter."', ".($site_id ? "'".$site_id."'" : "NULL")." ".
			"FROM b_group G ".
			"WHERE G.ID IN (".$str_groups.")"
			, false, "File: ".__FILE__."<br>Line: ".__LINE__
		);
		return true;
	}

	public static function Delete($ID, $protect = true)
	{
		global $DB, $CACHE_MANAGER;

		$ID = intval($ID);

		if(CACHED_b_task !== false)
			$CACHE_MANAGER->CleanDir("b_task");

		$sql_str = "DELETE FROM b_task WHERE ID=".$ID;
		if ($protect)
			$sql_str .= " AND SYS='N'";
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (!$protect)
		{
			if(CACHED_b_task_operation !== false)
				$CACHE_MANAGER->CleanDir("b_task_operation");

			$DB->Query("DELETE FROM b_task_operation WHERE TASK_ID=".$ID, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
	}

	public static function GetList($arOrder = array('MODULE_ID'=>'asc','LETTER'=>'asc'), $arFilter = array())
	{
		global $DB, $CACHE_MANAGER;;

		if(CACHED_b_task !== false)
		{
			$context = Main\Context::getCurrent();
			$cacheId = "b_task".md5(serialize($arOrder).".".serialize($arFilter).".".$context->getLanguage());
			if($CACHE_MANAGER->Read(CACHED_b_task, $cacheId, "b_task"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);
				$res = new CDBResult;
				$res->InitFromArray($arResult);
				return $res;
			}
		}

		static $arFields = array(
			"ID" => array("FIELD_NAME" => "T.ID", "FIELD_TYPE" => "int"),
			"NAME" => array("FIELD_NAME" => "T.NAME", "FIELD_TYPE" => "string"),
			"LETTER" => array("FIELD_NAME" => "T.LETTER", "FIELD_TYPE" => "string"),
			"MODULE_ID" => array("FIELD_NAME" => "T.MODULE_ID", "FIELD_TYPE" => "string"),
			"SYS" => array("FIELD_NAME" => "T.SYS", "FIELD_TYPE" => "string"),
			"BINDING" => array("FIELD_NAME" => "T.BINDING", "FIELD_TYPE" => "string")
		);

		$err_mess = (CAllTask::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		if(is_array($arFilter))
		{
			foreach($arFilter as $n => $val)
			{
				$n = strtoupper($n);
				if(strlen($val) <= 0 || strval($val) == "NOT_REF")
					continue;

				if(isset($arFields[$n]))
				{
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, ($n == 'NAME'? "Y" : "N"));
				}
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type)=="ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';

		if($strOrderBy <> '')
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				T.ID, T.NAME, T.DESCRIPTION, T.MODULE_ID, T.LETTER, T.SYS, T.BINDING
			FROM
				b_task T
			WHERE
				".$strSqlSearch."
			".$strOrderBy;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$arResult = array();
		while($arRes = $res->Fetch())
		{
			$arRes['TITLE'] = CTask::GetLangTitle($arRes['NAME'], $arRes['MODULE_ID']);
			$arRes['DESC'] = CTask::GetLangDescription($arRes['NAME'], $arRes['DESCRIPTION'], $arRes['MODULE_ID']);
			$arResult[] = $arRes;
		}
		$res->InitFromArray($arResult);

		if(CACHED_b_task !== false)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$CACHE_MANAGER->Set($cacheId, $arResult);
		}

		return $res;
	}


	public static function GetOperations($ID, $return_names = false)
	{
		global $DB, $CACHE_MANAGER;
		static $TASK_OPERATIONS_CACHE = array();
		$ID = intval($ID);

		if (!isset($TASK_OPERATIONS_CACHE[$ID]))
		{
			if(CACHED_b_task_operation !== false)
			{
				$cacheId = "b_task_operation_".$ID;
				if($CACHE_MANAGER->Read(CACHED_b_task_operation, $cacheId, "b_task_operation"))
				{
					$TASK_OPERATIONS_CACHE[$ID] = $CACHE_MANAGER->Get($cacheId);
				}
			}
		}

		if (!isset($TASK_OPERATIONS_CACHE[$ID]))
		{
			$sql_str = '
				SELECT T_O.OPERATION_ID, O.NAME
				FROM b_task_operation T_O
				INNER JOIN b_operation O ON T_O.OPERATION_ID = O.ID
				WHERE T_O.TASK_ID = '.$ID.'
			';

			$TASK_OPERATIONS_CACHE[$ID] = array(
				'names' => array(),
				'ids' => array(),
			);
			$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($r = $z->Fetch())
			{
				$TASK_OPERATIONS_CACHE[$ID]['names'][] = $r['NAME'];
				$TASK_OPERATIONS_CACHE[$ID]['ids'][] = $r['OPERATION_ID'];
			}

			if(CACHED_b_task_operation !== false)
			{
				/** @noinspection PhpUndefinedVariableInspection */
				$CACHE_MANAGER->Set($cacheId, $TASK_OPERATIONS_CACHE[$ID]);
			}
		}

		return $TASK_OPERATIONS_CACHE[$ID][$return_names ? 'names' : 'ids'];
	}

	public static function SetOperations($ID, $arr, $bOpNames=false)
	{
		global $DB, $CACHE_MANAGER;

		$ID = intval($ID);

		//get old operations
		$aPrevOp = array();
		$res = $DB->Query("
			SELECT O.NAME
			FROM b_operation O
			INNER JOIN b_task_operation T_OP ON O.ID = T_OP.OPERATION_ID
			WHERE T_OP.TASK_ID = ".$ID."
			ORDER BY O.ID
		");
		while(($res_arr = $res->Fetch()))
			$aPrevOp[] = $res_arr["NAME"];

		$sql_str = 'DELETE FROM b_task_operation WHERE TASK_ID='.$ID;
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if(is_array($arr) && count($arr)>0)
		{
			if($bOpNames)
			{
				$sID = "";
				foreach($arr as $op_id)
					$sID .= ",'".$DB->ForSQL($op_id)."'";
				$sID = LTrim($sID, ",");

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) ".
					"SELECT '".$ID."', O.ID ".
					"FROM b_operation O, b_task T ".
					"WHERE O.NAME IN (".$sID.") AND T.MODULE_ID=O.MODULE_ID AND T.ID=".$ID." "
					, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
			else
			{
				$sID = "0";
				foreach($arr as $op_id)
					$sID .= ",".intval($op_id);

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) ".
					"SELECT '".$ID."', ID ".
					"FROM b_operation ".
					"WHERE ID IN (".$sID.") "
					, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
		}

		if(CACHED_b_task_operation !== false)
			$CACHE_MANAGER->CleanDir("b_task_operation");

		//get new operations
		$aNewOp = array();
		$res = $DB->Query("
			SELECT O.NAME
			FROM b_operation O
			INNER JOIN b_task_operation T_OP ON O.ID = T_OP.OPERATION_ID
			WHERE T_OP.TASK_ID = ".$ID."
			ORDER BY O.ID
		");
		while(($res_arr = $res->Fetch()))
			$aNewOp[] = $res_arr["NAME"];

		//compare with old one
		$aDiff = array_diff($aNewOp, $aPrevOp);
		if(empty($aDiff))
			$aDiff = array_diff($aPrevOp, $aNewOp);
		if(!empty($aDiff))
		{
			if(COption::GetOptionString("main", "event_log_task", "N") === "Y")
				CEventLog::Log("SECURITY", "TASK_CHANGED", "main", $ID, "(".implode(", ", $aPrevOp).") => (".implode(", ", $aNewOp).")");
			foreach(GetModuleEvents("main", "OnTaskOperationsChanged", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $aPrevOp, $aNewOp));
		}
	}

	public static function GetTasksInModules($mode=false, $module_id=false, $binding = false)
	{
		$arFilter = array();
		if ($module_id !== false)
			$arFilter["MODULE_ID"] = $module_id;
		if ($binding !== false)
			$arFilter["BINDING"] = $binding;

		$z = CTask::GetList(
			array(
				"MODULE_ID" => "asc",
				"LETTER" => "asc"
			),
			$arFilter
		);

		$arr = array();
		if ($mode)
		{
			while($r = $z->Fetch())
			{
				if (!is_array($arr[$r['MODULE_ID']]))
					$arr[$r['MODULE_ID']] = array('reference_id'=>array(),'reference'=>array());

				$arr[$r['MODULE_ID']]['reference_id'][] = $r['ID'];
				$arr[$r['MODULE_ID']]['reference'][] = '['.($r['LETTER'] ? $r['LETTER'] : '..').'] '.CTask::GetLangTitle($r['NAME'], $r['MODULE_ID']);
			}
		}
		else
		{
			while($r = $z->Fetch())
			{
				if (!is_array($arr[$r['MODULE_ID']]))
					$arr[$r['MODULE_ID']] = array();

				$arr[$r['MODULE_ID']][] = $r;
			}
		}
		return $arr;
	}

	public static function GetByID($ID)
	{
		return CTask::GetList(array(), array("ID" => intval($ID)));
	}

	protected static function GetDescriptions($module)
	{
		static $descriptions = array();

		if(preg_match("/[^a-z0-9._]/i", $module))
		{
			return array();
		}

		if(!isset($descriptions[$module]))
		{
			if(($path = getLocalPath("modules/".$module."/admin/task_description.php")) !== false)
			{
				$descriptions[$module] = include($_SERVER["DOCUMENT_ROOT"].$path);
			}
			else
			{
				$descriptions[$module] = array();
			}
		}

		return $descriptions[$module];
	}

	public static function GetLangTitle($name, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["title"]))
		{
			return $descriptions[$nameUpper]["title"];
		}

		return $name;
	}

	public static function GetLangDescription($name, $desc, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["description"]))
		{
			return $descriptions[$nameUpper]["description"];
		}

		return $desc;
	}

	public static function GetLetter($ID)
	{
		$z = CTask::GetById($ID);
		if ($r = $z->Fetch())
			if ($r['LETTER'])
				return $r['LETTER'];
		return false;
	}

	public static function GetIdByLetter($letter, $module, $binding='module')
	{
		static $TASK_LETTER_CACHE = array();
		if (!$letter)
			return false;

		if (!isset($TASK_LETTER_CACHE))
			$TASK_LETTER_CACHE = array();

		$k = strtoupper($letter.'_'.$module.'_'.$binding);
		if (isset($TASK_LETTER_CACHE[$k]))
			return $TASK_LETTER_CACHE[$k];

		$z = CTask::GetList(
			array(),
			array(
				"LETTER" => $letter,
				"MODULE_ID" => $module,
				"BINDING" => $binding,
				"SYS"=>"Y"
			)
		);

		if ($r = $z->Fetch())
		{
			$TASK_LETTER_CACHE[$k] = $r['ID'];
			if ($r['ID'])
				return $r['ID'];
		}

		return false;
	}
}

class CAllOperation
{
	public static function err_mess()
	{
		return "<br>Class: CAllOperation<br>File: ".__FILE__;
	}

	public static function GetList($arOrder = array('MODULE_ID'=>'asc'),$arFilter=array())
	{
		global $DB;

		static $arFields = array(
			"ID" => array("FIELD_NAME" => "O.ID", "FIELD_TYPE" => "int"),
			"NAME" => array("FIELD_NAME" => "O.NAME", "FIELD_TYPE" => "string"),
			"MODULE_ID" => array("FIELD_NAME" => "O.MODULE_ID", "FIELD_TYPE" => "string"),
			"BINDING" => array("FIELD_NAME" => "O.BINDING", "FIELD_TYPE" => "string")
		);

		$err_mess = (CAllOperation::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		if(is_array($arFilter))
		{
			foreach($arFilter as $n => $val)
			{
				$n = strtoupper($n);
				if($val == '' || strval($val)=="NOT_REF")
					continue;
				if ($n == 'ID' || $n == 'MODULE_ID' || $n == 'BINDING')
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, 'N');
				elseif(isset($arFields[$n]))
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val);
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type)=="ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';

		if($strOrderBy <> '')
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT *
			FROM
				b_operation O
			WHERE
				".$strSqlSearch."
			".$strOrderBy;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetAllowedModules()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID FROM b_operation O';
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $z->Fetch())
			$arr[] = $r['MODULE_ID'];
		return $arr;
	}

	public static function GetBindingList()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID, O.BINDING FROM b_operation O';
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $z->Fetch())
			$arr[] = $r;
		return $arr;
	}

	public static function GetIDByName($name)
	{
		$z = COperation::GetList(array('MODULE_ID' => 'asc'), array("NAME" => $name));
		if ($r = $z->Fetch())
			return $r['ID'];
		return false;
	}

	protected static function GetDescriptions($module)
	{
		static $descriptions = array();

		if(preg_match("/[^a-z0-9._]/i", $module))
		{
			return array();
		}

		if(!isset($descriptions[$module]))
		{
			if(($path = getLocalPath("modules/".$module."/admin/operation_description.php")) !== false)
			{
				$descriptions[$module] = include($_SERVER["DOCUMENT_ROOT"].$path);
			}
			else
			{
				$descriptions[$module] = array();
			}
		}

		return $descriptions[$module];
	}

	public static function GetLangTitle($name, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["title"]))
		{
			return $descriptions[$nameUpper]["title"];
		}

		return $name;
	}

	public static function GetLangDescription($name, $desc, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if(isset($descriptions[$nameUpper]["description"]))
		{
			return $descriptions[$nameUpper]["description"];
		}

		return $desc;
	}
}
