<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\Web\Json;

class CComponentAjax
{
	var $componentID = '';
	var $bAjaxSession = false;
	var $bIFrameMode = false;
	var $componentName;
	var $componentTemplate;
	var $arParams;
	var $arCSSList;
	var $arHeadScripts;
	var $bShadow = true;
	var $bJump = true;
	var $bStyle = true;
	var $bHistory = true;
	var $bWrongRedirect = false;
	var $buffer_start_counter;
	var $buffer_finish_counter;
	var $bRestartBufferCalled;
	var $RestartBufferHandlerId;
	var $LocalRedirectHandlerId;
	var $currentUrl = false;
	var $dirname_currentUrl = false;
	var $basename_currentUrl = false;
	var $__nav_params = null;

	public function __construct($componentName, $componentTemplate, &$arParams, $parentComponent)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;

		if ($USER->IsAdmin())
		{
			$disableAjaxInGetParameter = $_GET['bitrix_disable_ajax'] ?? null;
			if ($disableAjaxInGetParameter === 'N')
			{
				unset(\Bitrix\Main\Application::getInstance()->getSession()['bitrix_disable_ajax']);
			}

			if ($disableAjaxInGetParameter === 'Y' || \Bitrix\Main\Application::getInstance()->getSession()['bitrix_disable_ajax'] == 'Y')
			{
				\Bitrix\Main\Application::getInstance()->getSession()['bitrix_disable_ajax'] = 'Y';
				return;
			}
		}

		if ($parentComponent && $this->_checkParent($parentComponent))
		{
			return;
		}

		$this->componentName = $componentName;
		$this->componentTemplate = $componentTemplate;
		$this->arParams = $arParams;

		$this->bShadow = !isset($this->arParams['AJAX_OPTION_SHADOW']) || $this->arParams['AJAX_OPTION_SHADOW'] != 'N';
		$this->bJump = !isset($this->arParams['AJAX_OPTION_JUMP']) || $this->arParams['AJAX_OPTION_JUMP'] != 'N';
		$this->bStyle = !isset($this->arParams['AJAX_OPTION_STYLE']) || $this->arParams['AJAX_OPTION_STYLE'] != 'N';
		$this->bHistory = !isset($this->arParams['AJAX_OPTION_HISTORY']) || $this->arParams['AJAX_OPTION_HISTORY'] != 'N';

		if (!$this->CheckSession())
		{
			return;
		}

		CJSCore::Init(['ajax']);

		$arParams['AJAX_ID'] = $this->componentID;

		if ($this->bAjaxSession)
		{
			// dirty hack: try to get breadcrumb call params
			for ($i = 0, $cnt = count($APPLICATION->buffer_content_type); $i < $cnt; $i++)
			{
				if (is_array($APPLICATION->buffer_content_type[$i]['F']) && $APPLICATION->buffer_content_type[$i]['F'][1] === 'GetNavChain')
				{
					$this->__nav_params = $APPLICATION->buffer_content_type[$i]['P'];
				}
			}

			$APPLICATION->RestartBuffer();

			if (!defined('PUBLIC_AJAX_MODE'))
			{
				define('PUBLIC_AJAX_MODE', 1);
			}

			if (isset($_REQUEST['AJAX_CALL']))
			{
				$this->bIFrameMode = true;
			}
		}

		if ($this->bStyle)
		{
			$this->arCSSList = $APPLICATION->sPath2css;
		}

		$this->arHeadScripts = $APPLICATION->arHeadScripts;

		if (!$this->bAjaxSession)
		{
			$APPLICATION->AddBufferContent([$this, '__BufferDelimiter']);
		}

		$this->buffer_start_counter = count($APPLICATION->buffer_content);

		$this->LocalRedirectHandlerId = AddEventHandler('main', 'OnBeforeLocalRedirect', [$this, "LocalRedirectHandler"]);
		$this->RestartBufferHandlerId = AddEventHandler('main', 'OnBeforeRestartBuffer', [$this, 'RestartBufferHandler']);
	}

	/**
	 * @param CBitrixComponent $parent
	 * @return bool
	 */
	protected function _checkParent($parent)
	{
		if (($parent->arParams['AJAX_MODE'] ?? null) === 'Y')
		{
			return true;
		}
		elseif (($parentComponent = $parent->GetParent()))
		{
			return $this->_checkParent($parentComponent);
		}

		return false;
	}

	/**
	 * @internal
	 */
	public function __BufferDelimiter()
	{
		return '';
	}

	protected function __removeHandlers()
	{
		RemoveEventHandler('main', 'OnBeforeRestartBuffer', $this->RestartBufferHandlerId);
		RemoveEventHandler('main', 'OnBeforeLocalRedirect', $this->LocalRedirectHandlerId);
	}

	/**
	 * @internal
	 */
	public function RestartBufferHandler()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$this->bRestartBufferCalled = true;
		//ob_end_clean();

		$APPLICATION->AddBufferContent([$this, '__BufferDelimiter']);
		$this->buffer_start_counter = count($APPLICATION->buffer_content);

		$this->__removeHandlers();
	}

	/**
	 * @internal
	 */
	public function LocalRedirectHandler(&$url)
	{
		if (!$this->bAjaxSession)
		{
			return;
		}

		if ($this->__isAjaxURL($url))
		{
			if (!$this->bIFrameMode)
			{
				Header('X-Bitrix-Ajax-Status: OK');
			}
		}
		else
		{
			if (!$this->bRestartBufferCalled)
			{
				ob_end_clean();
			}

			if (!$this->bIFrameMode)
			{
				Header('X-Bitrix-Ajax-Status: Redirect');
			}

			$this->bWrongRedirect = true;

			echo '<script>' . ($this->bIFrameMode ? 'top.' : 'window.') . 'location.href = \'' . CUtil::JSEscape($url) . '\'</script>';

			require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_after.php");
			exit();
		}

		$url = CAjax::AddSessionParam($url, $this->componentID);

		$this->__removeHandlers();
	}

	protected function CheckSession()
	{
		if ($this->componentID = CAjax::GetComponentID($this->componentName, $this->componentTemplate, $this->arParams['AJAX_OPTION_ADDITIONAL'] ?? null))
		{
			if ($current_session = CAjax::GetSession())
			{
				if ($this->componentID == $current_session)
				{
					$this->bAjaxSession = true;
					return true;
				}
				else
				{
					return false;
				}
			}
			return true;
		}
		return false;
	}

	protected function __GetSEFRealUrl($url)
	{
		$arResult = \Bitrix\Main\UrlRewriter::getList(SITE_ID, ['QUERY' => $url]);

		if (is_array($arResult) && !empty($arResult))
		{
			return $arResult[0]['PATH'];
		}
		else
		{
			return false;
		}
	}

	protected function __isAjaxURL($url)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if (preg_match("/^(#|mailto:|javascript:|callto:)/", $url))
		{
			return false;
		}

		if (str_contains($url, '://'))
		{
			return false;
		}

		if (CFile::IsImage($url))
		{
			return false;
		}

		$url = preg_replace('/#.*/', '', $url);

		if (($this->arParams['SEF_MODE'] ?? null) === 'Y')
		{
			if ($url == POST_FORM_ACTION_URI)
			{
				return true;
			}

			$url = $this->__GetSEFRealUrl($url);

			if ($url === false)
			{
				return false;
			}
		}
		else
		{
			if (str_contains($url, '?'))
			{
				$url = mb_substr($url, 0, mb_strpos($url, '?'));
			}

			if (!str_ends_with($url, '.php'))
			{
				if (!str_ends_with($url, '/'))
				{
					$url .= '/';
				}

				$url .= 'index.php';
			}
		}

		if (!$this->currentUrl)
		{
			$currentUrl = $APPLICATION->GetCurPage();

			if (($this->arParams['SEF_MODE'] ?? null) === 'Y')
			{
				$currentUrl = $this->__getSEFRealUrl($currentUrl);
			}

			if (str_contains($currentUrl, '?'))
			{
				$currentUrl = mb_substr($currentUrl, 0, mb_strpos($currentUrl, '?'));
			}

			if (!str_ends_with($currentUrl, '.php'))
			{
				if (!str_ends_with($currentUrl, '/'))
				{
					$currentUrl .= '/';
				}

				$currentUrl .= 'index.php';
			}

			$this->currentUrl = $currentUrl;
			$this->dirname_currentUrl = dirname($currentUrl);
			$this->basename_currentUrl = basename($currentUrl);
		}

		$dirname = dirname($url);
		if (
			(
				$dirname == $this->dirname_currentUrl
				||
				$dirname == ''
				||
				$dirname == '.'
			)
			&&
			basename($url) == $this->basename_currentUrl
		)
		{
			return true;
		}

		return false;
	}

	protected function _checkPcreLimit($data)
	{
		$pcre_backtrack_limit = intval(ini_get("pcre.backtrack_limit"));
		$text_len = strlen($data);
		$text_len++;

		if ($pcre_backtrack_limit > 0 && $pcre_backtrack_limit < $text_len)
		{
			@ini_set("pcre.backtrack_limit", $text_len);
			$pcre_backtrack_limit = intval(ini_get("pcre.backtrack_limit"));
		}

		return $pcre_backtrack_limit >= $text_len;
	}

	protected function __PrepareLinks(&$data)
	{
		$add_param = CAjax::GetSessionParam($this->componentID);

		$regexp_links = '/(<a\s[^>]*?>.*?<\/a>)/isu';
		$regexp_params = '/([\w\-]+)\s*=\s*([\"\'])(.*?)\2/isu';

		$this->_checkPcreLimit($data);
		$arData = preg_split($regexp_links, $data, -1, PREG_SPLIT_DELIM_CAPTURE);

		if (!is_array($arData))
		{
			return;
		}

		$cData = count($arData);
		if ($cData < 2)
		{
			return;
		}

		$arIgnoreAttributes = ['onclick' => true, 'target' => true];
		$arSearch = [
			$add_param . '&',
			$add_param,
			'AJAX_CALL=Y&',
			'AJAX_CALL=Y',
		];
		$bDataChanged = false;

		for ($iData = 1; $iData < $cData; $iData += 2)
		{
			if (!preg_match('/^<a\s([^>]*?)>(.*?)<\/a>$/isu', $arData[$iData], $match))
			{
				continue;
			}

			$params = $match[1];

			if (!preg_match_all($regexp_params, $params, $arLinkParams))
			{
				continue;
			}

			$strAdditional = ' ';
			$url_key = -1;
			$bIgnoreLink = false;

			foreach ($arLinkParams[0] as $pkey => $value)
			{
				if ($value == '')
				{
					continue;
				}

				$param_name = mb_strtolower($arLinkParams[1][$pkey]);

				if ($param_name === 'href')
				{
					$url_key = $pkey;
				}
				elseif (isset($arIgnoreAttributes[$param_name]))
				{
					$bIgnoreLink = true;
					break;
				}
				else
				{
					$strAdditional .= $value . ' ';
				}
			}

			if ($url_key >= 0 && !$bIgnoreLink)
			{
				$url = \Bitrix\Main\Text\Converter::getHtmlConverter()->decode($arLinkParams[3][$url_key]);
				$url = str_replace($arSearch, '', $url);

				if ($this->__isAjaxURL($url))
				{
					$real_url = $url;

					$pos = mb_strpos($url, '#');
					if ($pos !== false)
					{
						$real_url = mb_substr($real_url, 0, $pos);
					}

					$real_url .= !str_contains($url, '?') ? '?' : '&';
					$real_url .= $add_param;

					$url_str = CAjax::GetLinkEx($real_url, $url, $match[2], 'comp_' . $this->componentID, $strAdditional);

					$arData[$iData] = $url_str;
					$bDataChanged = true;
				}
			}
		}

		if ($bDataChanged)
		{
			$data = implode('', $arData);
		}
	}

	protected function __PrepareForms(&$data)
	{
		$this->_checkPcreLimit($data);
		$arData = preg_split('/(<form([^>]*)>)/iu', $data, -1, PREG_SPLIT_DELIM_CAPTURE);

		$bDataChanged = false;
		if (is_array($arData))
		{
			for ($key = 0, $l = count($arData); $key < $l; $key++)
			{
				if ($key % 3 != 0)
				{
					$arIgnoreAttributes = ['target'];
					$bIgnore = false;
					foreach ($arIgnoreAttributes as $attr)
					{
						if (mb_strpos($arData[$key], $attr . '="') !== false)
						{
							$bIgnore = true;
							break;
						}
					}

					if (!$bIgnore)
					{
						preg_match_all('/action=(["\']{1})(.*?)\1/i', $arData[$key], $arAction);
						$url = $arAction[2][0];

						if ($url === '' || $this->__isAjaxURL($url) || $this->__isAjaxURL(urldecode($url)))
						{
							$arData[$key] = CAjax::GetForm($arData[$key + 1], 'comp_' . $this->componentID, $this->componentID, true, $this->bShadow);
						}
						else
						{
							$new_url = str_replace(CAjax::GetSessionParam($this->componentID), '', $url);
							$arData[$key] = str_replace($url, $new_url, $arData[$key]);
						}

						$bDataChanged = true;
					}

					unset($arData[$key + 1]);
					$key++;
				}
			}
		}

		if ($bDataChanged)
		{
			$data = implode('', $arData);
		}
	}

	protected function __prepareScripts(&$data)
	{
		$regexp = '/(<script(?:[^>]*)?>)(.*?)<\/script>/isu';

		$this->_checkPcreLimit($data);
		$scripts_num = preg_match_all($regexp, $data, $out);

		$arScripts = [];

		if (false !== $scripts_num)
		{
			for ($i = 0; $i < $scripts_num; $i++)
			{
				$data = str_replace($out[0][$i], '', $data);

				if ($out[1][$i] <> '' && str_contains($out[1][$i], 'src='))
				{
					$regexp_src = '/src="([^"]*)?"/i';
					if (preg_match($regexp_src, $out[1][$i], $out1) != 0)
					{
						$arScripts[] = [
							'TYPE' => 'SCRIPT_SRC',
							'DATA' => $out1[1],
						];
					}
				}
				else
				{
					$out[2][$i] = str_replace('<!--', '', $out[2][$i]);
					$arScripts[] = [
						'TYPE' => 'SCRIPT',
						'DATA' => $out[2][$i],
					];
				}
			}
		}

		if (!empty($arScripts))
		{
			$data .= "
<script>
parent.bxcompajaxframeonload = function() {
    parent.BX.CaptureEventsGet();
    parent.BX.CaptureEvents(parent, 'load');
    parent.BX.evalPack(" . Json::encode($arScripts) . ");
    setTimeout('parent.BX.ajax.__runOnload();', 300);
}</script>
";
		}
	}

	/**
	 * @internal
	 */
	public function _PrepareAdditionalData()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		// get CSS changes list
		if ($this->bStyle)
		{
			$arCSSList = $APPLICATION->sPath2css;
			$cnt_old = count($this->arCSSList);
			$cnt_new = count($arCSSList);
			$arCSSNew = [];

			if ($cnt_old != $cnt_new)
			{
				for ($i = $cnt_old; $i < $cnt_new; $i++)
				{
					$css_path = $arCSSList[$i];
					if (mb_strtolower(mb_substr($css_path, 0, 7)) != 'http://' && mb_strtolower(mb_substr($css_path, 0, 8)) != 'https://')
					{
						if (($p = mb_strpos($css_path, "?")) > 0)
						{
							$css_file = mb_substr($css_path, 0, $p);
						}
						else
						{
							$css_file = $css_path;
						}

						if (file_exists($_SERVER["DOCUMENT_ROOT"] . $css_file))
						{
							$arCSSNew[] = $arCSSList[$i];
						}
					}
					else
					{
						$arCSSNew[] = $arCSSList[$i];
					}
				}
			}
		}

		// get scripts changes list
		$arHeadScripts = $APPLICATION->arHeadScripts;

		$cnt_old = count($this->arHeadScripts);
		$cnt_new = count($arHeadScripts);
		$arHeadScriptsNew = [];

		if ($cnt_old != $cnt_new)
		{
			for ($i = $cnt_old; $i < $cnt_new; $i++)
			{
				$arHeadScriptsNew[] = $arHeadScripts[$i];
			}
		}

		if (!$APPLICATION->oAsset->optimizeJs())
		{
			$arHeadScriptsNew = array_merge(CJSCore::GetScriptsList(), $arHeadScriptsNew);
		}

		// prepare additional data
		$arAdditionalData = [];
		$arAdditionalData['TITLE'] = htmlspecialcharsback($APPLICATION->GetTitle());
		$arAdditionalData['WINDOW_TITLE'] = htmlspecialcharsback($APPLICATION->GetTitle('title'));

		$arAdditionalData['SCRIPTS'] = [];
		$arHeadScriptsNew = array_unique($arHeadScriptsNew);

		foreach ($arHeadScriptsNew as $script)
		{
			$arAdditionalData['SCRIPTS'][] = CUtil::GetAdditionalFileURL($script);
		}

		if (null !== $this->__nav_params)
		{
			$arAdditionalData['NAV_CHAIN'] = $APPLICATION->GetNavChain($this->__nav_params[0], $this->__nav_params[1], $this->__nav_params[2], $this->__nav_params[3], $this->__nav_params[4]);
		}

		if ($this->bStyle)
		{
			$arAdditionalData["CSS"] = [];
			/** @noinspection PhpUndefinedVariableInspection */
			$arCSSNew = array_unique($arCSSNew);
			foreach ($arCSSNew as $style)
			{
				$arAdditionalData['CSS'][] = CUtil::GetAdditionalFileURL($style);
			}
		}

		$additional_data = '<script bxrunfirst="true">' . "\n";
		$additional_data .= 'var arAjaxPageData = ' . Json::encode($arAdditionalData) . ";\r\n";
		$additional_data .= 'parent.BX.ajax.UpdatePageData(arAjaxPageData)' . ";\r\n";

		$additional_data .= '</script><script>';

		if (!$this->bIFrameMode && $this->bHistory)
		{
			$additional_data .= 'top.BX.ajax.history.put(window.AJAX_PAGE_STATE.getState(), \'' . CUtil::JSEscape(CAjax::encodeURI($APPLICATION->GetCurPageParam('', [BX_AJAX_PARAM_ID], false))) . '\')' . ";\r\n";
		}

		if ($this->bJump)
		{
			$additional_data .= (
			$this->bIFrameMode
				? 'setTimeout(\'BX.scrollToNode("comp_' . $this->componentID . '")\', 100)' . ";\r\n"
				: 'top.BX.scrollToNode(\'comp_' . $this->componentID . '\')' . ";\r\n"
			);
		}

		$additional_data .= '</script>';

		return $additional_data;
	}

	/**
	 * @internal
	 */
	public function _PrepareData()
	{
		global $APPLICATION;

		if ($this->bWrongRedirect)
		{
			return null;
		}

		$arBuffer = array_slice($APPLICATION->buffer_content, $this->buffer_start_counter, $this->buffer_finish_counter - $this->buffer_start_counter);

		$delimiter = '###AJAX_' . $APPLICATION->GetServerUniqID() . '###';

		$data = implode($delimiter, $arBuffer);

		$this->__PrepareLinks($data);
		$this->__PrepareForms($data);

		if (!$this->bAjaxSession)
		{
			$data = '<div id="comp_' . $this->componentID . '">' . $data . '</div>';

			if ($this->bHistory)
			{
				$data =
					'<script>if (window.location.hash != \'\' && window.location.hash != \'#\') top.BX.ajax.history.checkRedirectStart(\'' . CUtil::JSEscape(BX_AJAX_PARAM_ID) . '\', \'' . CUtil::JSEscape($this->componentID) . '\')</script>'
					. $data
					. '<script>if (top.BX.ajax.history.bHashCollision) top.BX.ajax.history.checkRedirectFinish(\'' . CUtil::JSEscape(BX_AJAX_PARAM_ID) . '\', \'' . CUtil::JSEscape($this->componentID) . '\');</script>'
					. '<script>top.BX.ready(BX.defer(function() {window.AJAX_PAGE_STATE = new top.BX.ajax.component(\'comp_' . $this->componentID . '\'); top.BX.ajax.history.init(window.AJAX_PAGE_STATE);}))</script>';
			}
		}
		else
		{
			if ($this->bIFrameMode)
			{
				$this->__PrepareScripts($data);

				// fix IE bug;
				$data = '<html><head></head><body>' . $data . '</body></html>';
			}
		}

		$arBuffer = explode($delimiter, $data);
		for ($i = 0, $cnt = count($arBuffer); $i < $cnt; $i++)
		{
			$APPLICATION->buffer_content[$this->buffer_start_counter + $i] = $arBuffer[$i];
		}

		return '';
	}

	public function Process()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if ($this->componentID == '')
		{
			return;
		}

		$this->buffer_finish_counter = count($APPLICATION->buffer_content) + 1;

		$APPLICATION->AddBufferContent([$this, '_PrepareData']);

		$this->__removeHandlers();

		if ($this->bAjaxSession)
		{
			$eventManager = \Bitrix\Main\EventManager::getInstance();

			$eventManager->addEventHandlerCompatible('main', 'onAfterAjaxResponse', [$this, '_PrepareAdditionalData']);
			$eventManager->addEventHandlerCompatible('main', 'OnEndBufferContent', ['CComponentAjax', 'executeEvents']);

			require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_after.php");
			exit();
		}
	}

	// will be called as delay function and not in class entity context
	public static function executeEvents(&$content = '')
	{
		ob_start();

		foreach (GetModuleEvents('main', 'onAfterAjaxResponse', true) as $arEvent)
		{
			echo ExecuteModuleEventEx($arEvent);
		}

		$content = ob_get_clean() . $content;
	}
}
