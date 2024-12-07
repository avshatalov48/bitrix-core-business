<?php

use Bitrix\Main;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

class CJSCore
{
	const USE_ADMIN = 'admin';
	const USE_PUBLIC = 'public';

	private static $arRegisteredExt = array();
	private static $arCurrentlyLoadedExt = array();

	private static $bInited = false;

	/*
	ex: CJSCore::RegisterExt('timeman', array(
		'js' => '/bitrix/js/timeman/core_timeman.js',
		'css' => '/bitrix/js/timeman/css/core_timeman.css',
		'lang' => '/bitrix/modules/timeman/js_core_timeman.php',
		'rel' => array(needed extensions for automatic inclusion),
		'use' => CJSCore::USE_ADMIN|CJSCore::USE_PUBLIC
	));
	*/
	public static function RegisterExt($name, $arPaths)
	{
		if(isset($arPaths['use']))
		{
			switch($arPaths['use'])
			{
				case CJSCore::USE_PUBLIC:
					if(defined("ADMIN_SECTION") && ADMIN_SECTION === true)
						return;

				break;
				case CJSCore::USE_ADMIN:
					if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
						return;

				break;
			}
		}

		//An old path format required a language id.
		if (isset($arPaths['lang']))
		{
			if (is_array($arPaths['lang']))
			{
				foreach ($arPaths['lang'] as $key => $lang)
				{
					$arPaths['lang'][$key] = str_replace('/lang/'.LANGUAGE_ID.'/', '/', $lang);
				}
			}
			else
			{
				$arPaths['lang'] = str_replace('/lang/'.LANGUAGE_ID.'/', '/', $arPaths['lang']);
			}
		}

		self::$arRegisteredExt[$name] = $arPaths;
	}

	public static function Init($arExt = array(), $bReturn = false)
	{
		if (!self::$bInited)
		{
			self::_RegisterStandardExt();
			self::$bInited = true;
		}

		if (!is_array($arExt) && $arExt <> '')
			$arExt = array($arExt);

		$bReturn = ($bReturn === true); // prevent syntax mistake

		$bNeedCore = false;
		if (!empty($arExt))
		{
			foreach ($arExt as $ext)
			{
				if (
					isset(self::$arRegisteredExt[$ext])
					&& (
						!isset(self::$arRegisteredExt[$ext]['skip_core'])
						|| !self::$arRegisteredExt[$ext]['skip_core']
					)
				)
				{
					$bNeedCore = true;
					break;
				}
			}
		}
		else
		{
			$bNeedCore = true;
		}

		$ret = '';

		if ($bNeedCore && !self::isCoreLoaded())
		{
			$config = self::getCoreConfig();

			self::markExtensionLoaded('core');
			self::markExtensionLoaded('main.core');

			$includes = '';
			if (is_array($config['includes']))
            {
                foreach ($config['includes'] as $item)
                {
					self::markExtensionLoaded($item);
                }

				$assets = Extension::getAssets($config['includes']);
                $includes .= static::registerAssetsAsLoaded($assets);
            }

			$relativities = '';

			if (is_array($config['rel']))
            {
                $relativities .= self::init($config['rel'], true);
            }

			$coreLang = self::_loadLang($config['lang'], true);
			$coreSettings = self::loadSettings('main.core', $config['settings'], true);
            $coreJs = self::_loadJS($config['js'], true);
            $registerExtension = self::registerExtensionAsLoaded('main.core', true);

			if ($bReturn)
			{
			    $ret .= $coreLang;
			    $ret .= $coreSettings;
				$ret .= $relativities;
			    $ret .= $coreJs;
			    $ret .= $registerExtension;
			    $ret .= $includes;
            }

			$asset = Asset::getInstance();
			$asset->addString($coreLang, true, AssetLocation::AFTER_CSS);
			$asset->addString($coreSettings, true, AssetLocation::AFTER_CSS);
            $asset->addString($relativities, true, AssetLocation::AFTER_CSS);
            $asset->addString($coreJs, true, AssetLocation::AFTER_CSS);
            $asset->addString($registerExtension, true, AssetLocation::AFTER_CSS);
            $asset->addString($includes, true, AssetLocation::AFTER_CSS);
		}

		for ($i = 0, $len = count($arExt); $i < $len; $i++)
		{
			$ret .= self::_loadExt($arExt[$i], $bReturn);
		}

		if (!defined('PUBLIC_MODE') && defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
			echo $ret;

		return $bReturn ? $ret : true;
	}

	protected static function registerAssetsAsLoaded($assets)
    {
        if (is_array($assets))
        {
            $result = '';

            if (!empty($assets['js']) && is_array($assets['js']))
            {
                $result .= "BX.setJSList(".Json::encode($assets['js']).");\n";
            }

			if (!empty($assets['css']) && is_array($assets['css']))
			{
				$result .= "BX.setCSSList(".Json::encode($assets['css']).");";
			}

            return '<script>'.$result.'</script>';
        }

        return '';
    }

	/**
	 * @param $code - name of extension
	 */
	public static function markExtensionLoaded($code)
	{
		self::$arCurrentlyLoadedExt[$code] = true;
	}

	/**
	 * Returns true if Core JS was inited
	 * @return bool
	 */
	public static function IsCoreLoaded()
	{
		return (
			self::isExtensionLoaded("core")
			|| self::isExtensionLoaded("main.core")
        );
	}

	/**
	 * Returns true if JS extension was loaded.
	 * @param string $code Code of JS extension.
	 * @return bool
	 */
	public static function isExtensionLoaded($code)
	{
		return isset(self::$arCurrentlyLoadedExt[$code]) && self::$arCurrentlyLoadedExt[$code];
	}

	public static function GetCoreMessagesScript($compositeMode = false)
	{
		if (!self::IsCoreLoaded())
		{
			return "";
		}

		return self::_loadLang("", true, self::GetCoreMessages($compositeMode));
	}

	public static function GetCoreMessages($compositeMode = false)
	{
		$arMessages = array(
			"LANGUAGE_ID" => LANGUAGE_ID,
			"FORMAT_DATE" => FORMAT_DATE,
			"FORMAT_DATETIME" => FORMAT_DATETIME,
			"COOKIE_PREFIX" => COption::GetOptionString("main", "cookie_name", "BITRIX_SM"),
			"SERVER_TZ_OFFSET" => date("Z"),
			"UTF_MODE" => 'Y',
		);

		if (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
		{
			$arMessages["SITE_ID"] = SITE_ID;
			$arMessages["SITE_DIR"] = SITE_DIR;
		}

		if (!$compositeMode)
		{
			global $USER;
			$userId = "";
			$autoTimeZone = "N";
			if (is_object($USER))
			{
				$autoTimeZone = $USER->GetParam("AUTO_TIME_ZONE");
				$autoTimeZone = $autoTimeZone ? trim($USER->GetParam("AUTO_TIME_ZONE")) : null;
				if ($USER->GetID() > 0)
				{
					$userId = $USER->GetID();
				}
			}

			$arMessages["USER_ID"] = $userId;
			$arMessages["SERVER_TIME"] = time();
			$arMessages["USER_TZ_OFFSET"] = CTimeZone::GetOffset();
			$arMessages["USER_TZ_AUTO"] = $autoTimeZone === "N" ? "N": "Y";
			$arMessages["bitrix_sessid"] = bitrix_sessid();
		}

		return $arMessages;
	}

	public static function GetHTML($arExt)
	{
		$tmp = self::$arCurrentlyLoadedExt;
		self::$arCurrentlyLoadedExt = array();
		$res = self::Init($arExt, true);
		self::$arCurrentlyLoadedExt = $tmp;
		return $res;
	}

	/**
	 *
	 * When all scripts are moved to the body, we need this code to add special classes (bx-chrome, bx-ie...) to <html> tag.
	 * @return string
	 */
	public static function GetInlineCoreJs()
	{
		$js = <<<JS
		(function(w, d, n) {

			var cl = "bx-core";
			var ht = d.documentElement;
			var htc = ht ? ht.className : undefined;
			if (htc === undefined || htc.indexOf(cl) !== -1)
			{
				return;
			}

			var ua = n.userAgent;
			if (/(iPad;)|(iPhone;)/i.test(ua))
			{
				cl += " bx-ios";
			}
			else if (/Windows/i.test(ua))
			{
				cl += ' bx-win';
			}
			else if (/Macintosh/i.test(ua))
			{
				cl += " bx-mac";
			}
			else if (/Linux/i.test(ua) && !/Android/i.test(ua))
			{
				cl += " bx-linux";
			}
			else if (/Android/i.test(ua))
			{
				cl += " bx-android";
			}

			cl += (/(ipad|iphone|android|mobile|touch)/i.test(ua) ? " bx-touch" : " bx-no-touch");

			cl += w.devicePixelRatio && w.devicePixelRatio >= 2
				? " bx-retina"
				: " bx-no-retina";

			if (/AppleWebKit/.test(ua))
			{
				cl += " bx-chrome";
			}
			else if (/Opera/.test(ua))
			{
				cl += " bx-opera";
			}
			else if (/Firefox/.test(ua))
			{
				cl += " bx-firefox";
			}

			ht.className = htc ? htc + " " + cl : cl;

		})(window, document, navigator);
JS;
		return '<script data-skip-moving="true">'.str_replace(array("\n", "\t"), "", $js)."</script>";
	}

	public static function GetScriptsList()
	{
		$scriptsList = array();
		foreach(self::$arCurrentlyLoadedExt as $ext=>$q)
		{
			if($ext!='core' && isset(self::$arRegisteredExt[$ext]['js']))
			{
				if(is_array(self::$arRegisteredExt[$ext]['js']))
				{
					$scriptsList = array_merge($scriptsList, self::$arRegisteredExt[$ext]['js']);
				}
				else
				{
					$scriptsList[] = self::$arRegisteredExt[$ext]['js'];
				}
			}
		}
		return $scriptsList;
	}

	public static function GetCoreConfig()
	{
		return Extension::getConfig('main.core');
	}

	private static function _loadExt($ext, $bReturn)
	{
		$ret = '';

		$ext = preg_replace('/[^a-z0-9_\.\-]/i', '', $ext);

		if (!self::IsExtRegistered($ext))
		{
			$success = Extension::register($ext);
			if (!$success)
			{
				return "";
			}
		}

		if (self::isExtensionLoaded($ext))
		{
			return "";
		}

		if(isset(self::$arRegisteredExt[$ext]['oninit']) && is_callable(self::$arRegisteredExt[$ext]['oninit']))
		{
			$callbackResult = call_user_func_array(
				self::$arRegisteredExt[$ext]['oninit'],
				array(self::$arRegisteredExt[$ext])
			);

			if(is_array($callbackResult))
			{
				foreach($callbackResult as $key => $value)
				{
					if(!is_array($value))
					{
						$value = array($value);
					}

					if(!isset(self::$arRegisteredExt[$ext][$key]))
					{
						self::$arRegisteredExt[$ext][$key] = array();
					}
					elseif(!is_array(self::$arRegisteredExt[$ext][$key]))
					{
						self::$arRegisteredExt[$ext][$key] = array(self::$arRegisteredExt[$ext][$key]);
					}

					self::$arRegisteredExt[$ext][$key] = array_merge(self::$arRegisteredExt[$ext][$key], $value);
				}
			}

			unset(self::$arRegisteredExt[$ext]['oninit']);
		}

		self::markExtensionLoaded($ext);

		if (isset(self::$arRegisteredExt[$ext]['rel']) && is_array(self::$arRegisteredExt[$ext]['rel']))
		{
			foreach (self::$arRegisteredExt[$ext]['rel'] as $rel_ext)
			{
				$ret .= self::_loadExt($rel_ext, $bReturn);
			}
		}

		if (!empty(self::$arRegisteredExt[$ext]['css']))
		{
			if (!empty(self::$arRegisteredExt[$ext]['bundle_css']))
			{
				self::registerCssBundle(
					self::$arRegisteredExt[$ext]['bundle_css'],
					self::$arRegisteredExt[$ext]['css']
				);
			}

			$ret .= self::_loadCSS(self::$arRegisteredExt[$ext]['css'], $bReturn);
		}

		if (isset(self::$arRegisteredExt[$ext]['js']))
		{
			if (!empty(self::$arRegisteredExt[$ext]['bundle_js']))
			{
				self::registerJsBundle(
					self::$arRegisteredExt[$ext]['bundle_js'],
					self::$arRegisteredExt[$ext]['js']
				);
			}

			$ret .= self::_loadJS(self::$arRegisteredExt[$ext]['js'], $bReturn);
		}

		if (isset(self::$arRegisteredExt[$ext]['lang']) || isset(self::$arRegisteredExt[$ext]['lang_additional']))
		{
			$ret .= self::_loadLang(
				self::$arRegisteredExt[$ext]['lang'] ?? null,
				$bReturn,
				!empty(self::$arRegisteredExt[$ext]['lang_additional'])? self::$arRegisteredExt[$ext]['lang_additional']: false
			);
		}

		if (isset(self::$arRegisteredExt[$ext]['settings']))
		{
			$ret .= self::loadSettings($ext, self::$arRegisteredExt[$ext]['settings'], $bReturn);
		}

		if (isset(self::$arRegisteredExt[$ext]['post_rel']) && is_array(self::$arRegisteredExt[$ext]['post_rel']))
		{
			foreach (self::$arRegisteredExt[$ext]['post_rel'] as $rel_ext)
			{
				$ret .= self::_loadExt($rel_ext, $bReturn);
			}
		}

        $ret .= self::registerExtensionAsLoaded($ext, $bReturn);

		return $ret;
	}

    public static function registerExtensionAsLoaded($extension, $bReturn = false): string
    {
        if (self::isCoreLoaded())
        {
            $options = [
                'name' => $extension,
                'namespace' => 'window',
                'loaded' => true,
            ];

            $bundleConfig = Extension::getBundleConfig($extension);
            if (is_array($bundleConfig))
            {
                if (isset($bundleConfig['namespace']) && is_string($bundleConfig['namespace']))
                {
                    $options['namespace'] = $bundleConfig['namespace'];
                }
            }

            $jsOptions = Json::encode($options);
            $result = '<script>BX.Runtime.registerExtension(' . $jsOptions . ');</script>';

            if ($bReturn)
            {
                return $result;
            }

            Asset::getInstance()->addString($result, true, AssetLocation::AFTER_CSS);
        }

        return '';
    }

	public static function ShowTimer($params)
	{
		$id = $params['id'] ? $params['id'] : 'timer_'.RandString(7);

		self::Init(array('timer'));

		$arJSParams = array();
		if ($params['from'])
			$arJSParams['from'] = MakeTimeStamp($params['from']).'000';
		elseif ($params['to'])
			$arJSParams['to'] = MakeTimeStamp($params['to']).'000';

		if ($params['accuracy'])
			$arJSParams['accuracy'] = intval($params['accuracy']).'000';

		$res = '<span id="'.htmlspecialcharsbx($id).'"></span>';
		$res .= '<script>BX.timer(\''.CUtil::JSEscape($id).'\', '.Json::encode($arJSParams).')</script>';

		return $res;
	}

	public static function IsExtRegistered($ext)
	{
		$ext = preg_replace('/[^a-z0-9_\.\-]/i', '', $ext);
		return isset(self::$arRegisteredExt[$ext]) && is_array(self::$arRegisteredExt[$ext]);
	}

	public static function getExtInfo($ext)
	{
		return self::$arRegisteredExt[$ext] ?? null;
	}

	private static function _RegisterStandardExt()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/jscore.php');
	}

	private static function _loadJS($js, $bReturn)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$js = (is_array($js) ? $js : array($js));
		if ($bReturn)
		{
			$res = '';
			foreach ($js as $val)
			{
				$fullPath = Asset::getInstance()->getFullAssetPath($val);

				if ($fullPath)
				{
					$res .= '<script src="'.$fullPath.'"></script>'."\r\n";
				}
			}
			return $res;
		}
		else
		{
			foreach ($js as $val)
			{
				$APPLICATION->AddHeadScript($val);
			}
		}
		return '';
	}

	private static function _loadLang($lang, $bReturn, $arAdditionalMess = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$jsMsg = '';

		if (!is_array($lang))
		{
			$lang = [$lang];
		}

		foreach ($lang as $path)
		{
			if (is_string($path))
			{
				$messLang = \Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$path);
				if (!empty($messLang))
				{
					$jsMsg .= '(window.BX||top.BX).message('.Json::encode($messLang).');';
				}
			}
		}

		if (is_array($arAdditionalMess))
		{
			$jsMsg = '(window.BX||top.BX).message('.Json::encode($arAdditionalMess).');'.$jsMsg;
		}

		if ($jsMsg !== '')
		{
			$jsMsg = '<script>'.$jsMsg.'</script>';
			if ($bReturn)
			{
				return $jsMsg."\r\n";
			}
			else
			{
				$APPLICATION->AddLangJS($jsMsg);
			}
		}

		return $jsMsg;
	}

	private static function _loadCSS($css, $bReturn)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if (is_array($css))
		{
			$ret = '';
			foreach ($css as $css_file)
				$ret .= self::_loadCSS($css_file, $bReturn);
			return $ret;
		}

		$css_filename = $_SERVER['DOCUMENT_ROOT'].$css;

		if (!file_exists($css_filename))
			return '';

		if ($bReturn)
		{
			$fullPath = Asset::getInstance()->getFullAssetPath($css);

			if ($fullPath)
			{
				return '<link href="'.$fullPath.'" type="text/css" rel="stylesheet" />'."\r\n";
			}

			return '';
		}

		$APPLICATION->SetAdditionalCSS($css);

		return '';
	}

	/**
	 * @param string $extension Extension name
	 * @param array $settings Extension settings
	 * @param bool $bReturn
	 * @return string
	 * @throws Main\ArgumentException
	 */
	private static function loadSettings($extension, $settings, $bReturn = false)
	{
		if (is_array($settings) && !empty($settings))
		{
			$encodedSettings = Main\Web\Json::encode($settings);
			$result = '<script type="extension/settings" data-extension="'.$extension.'">';
			$result .= $encodedSettings;
			$result .= '</script>';

			if ($bReturn)
			{
				return $result;
			}

			Asset::getInstance()->addString($result, true, AssetLocation::AFTER_CSS);
		}

		return '';
	}

	private static function registerJsBundle($bundleName, $files)
	{
		$files = is_array($files) ? $files : array($files);

		Asset::getInstance()->addJsKernelInfo($bundleName, $files);
	}

	private static function registerCssBundle($bundleName, $files)
	{
		$files = is_array($files) ? $files : array($files);

		Asset::getInstance()->addCssKernelInfo($bundleName, $files);
	}
}
