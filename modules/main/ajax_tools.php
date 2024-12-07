<?php

IncludeModuleLangFile(__FILE__);

class CAjax
{
	public static function Init()
	{
		// old version should be here because of compatibility
		global $APPLICATION;

		$APPLICATION->SetTemplateCSS('ajax/ajax.css');
		$APPLICATION->AddHeadScript('/bitrix/js/main/ajax.js');
	}

	public static function GetComponentID($componentName, $componentTemplate, $additionalID)
	{
		$aTrace = Bitrix\Main\Diag\Helper::getBackTrace(0, DEBUG_BACKTRACE_IGNORE_ARGS);

		$trace_count = count($aTrace);
		$trace_current = $trace_count - 1;
		for ($i = 0; $i < $trace_count; $i++)
		{
			if (strtolower($aTrace[$i]['function']) == 'includecomponent' && (($c = strtolower($aTrace[$i]['class'])) == 'callmain' || $c == 'cmain'))
			{
				$trace_current = $i;
				break;
			}
		}

		$sSrcFile = $aTrace[$trace_current]["file"];
		$iSrcLine = intval($aTrace[$trace_current]["line"]);

		if ($iSrcLine > 0 && $sSrcFile != '')
		{
			$session_string = $sSrcFile
				. '|' . $iSrcLine
				. '|' . $componentName
				. '|' . ($componentTemplate ?: '.default')
				. '|' . $additionalID
			;

			return md5($session_string);
		}
		return false;
	}

	public static function GetSession()
	{
		return $_REQUEST[BX_AJAX_PARAM_ID] ?? false;
	}

	public static function GetSessionParam($ajax_id = false)
	{
		if (!$ajax_id)
		{
			$ajax_id = CAjax::GetSession();
		}
		if ($ajax_id)
		{
			return BX_AJAX_PARAM_ID . '=' . $ajax_id;
		}
		return '';
	}

	public static function AddSessionParam($url, $ajax_id = false)
	{
		$url_anchor = mb_strstr($url, '#');
		if ($url_anchor !== false)
		{
			$url = mb_substr($url, 0, -mb_strlen($url_anchor));
		}

		$url .= str_contains($url, '?') ? '&' : '?';
		$url .= CAjax::GetSessionParam($ajax_id);

		if (isset($_REQUEST['AJAX_CALL']))
		{
			$url .= '&AJAX_CALL=Y';
		}

		if ($url_anchor !== false)
		{
			$url .= $url_anchor;
		}

		return $url;
	}

	// $text = htmlspecialcharred
	public static function GetLinkEx($real_url, $public_url, $text, $container_id, $additional = '')
	{
		if (!$public_url)
		{
			$public_url = $real_url;
		}

		return sprintf(
			'<a href="%s" onclick="BX.ajax.insertToNode(\'%s\', \'%s\'); return false;" %s>%s</a>',
			htmlspecialcharsbx($public_url),
			CUtil::JSEscape(htmlspecialcharsbx($real_url)),
			CUtil::JSEscape(htmlspecialcharsbx($container_id)),
			$additional,
			$text
		);
	}

	// $text - no htmlspecialchar
	public static function GetLink($url, $text, $container_id, $additional = '')
	{
		return CAjax::GetLinkEx($url, false, htmlspecialcharsbx($text), htmlspecialcharsbx($container_id), $additional);
	}

	public static function GetForm($form_params, $container_id, $ajax_id, $bReplace = true, $bShadow = true)
	{
		static $rndGenerator = null;
		if (!$rndGenerator)
		{
			$rndGenerator = new \Bitrix\Main\Type\RandomSequence("BX_AJAX");
		}
		$rnd = $rndGenerator->randString(6);
		return '
<form ' . trim($form_params) . '><input type="hidden" name="' . BX_AJAX_PARAM_ID . '" id="' . BX_AJAX_PARAM_ID . '_' . $ajax_id . '_' . $rnd . '" value="' . $ajax_id . '" /><input type="hidden" name="AJAX_CALL" value="Y" /><script>
function _processform_' . $rnd . '(){
	if (BX(\'' . BX_AJAX_PARAM_ID . '_' . $ajax_id . '_' . $rnd . '\'))
	{
		var obForm = BX(\'' . BX_AJAX_PARAM_ID . '_' . $ajax_id . '_' . $rnd . '\').form;
		BX.bind(obForm, \'submit\', function() {' . CAjax::GetFormEventValue($container_id, $bReplace, $bShadow, '"') . '});
	}
	BX.removeCustomEvent(\'onAjaxSuccess\', _processform_' . $rnd . ');
}
if (BX(\'' . BX_AJAX_PARAM_ID . '_' . $ajax_id . '_' . $rnd . '\'))
	_processform_' . $rnd . '();
else
	BX.addCustomEvent(\'onAjaxSuccess\', _processform_' . $rnd . ');
</script>';
	}

	public static function ClearForm($form_params, $ajax_id = false)
	{
		$form_params = str_replace(CAjax::GetSessionParam($ajax_id), '', $form_params);

		return '<form ' . trim($form_params) . '>';
	}

	public static function GetFormEvent($container_id)
	{
		return 'onsubmit="BX.ajax.submitComponentForm(this, \'' . htmlspecialcharsbx(CUtil::JSEscape($container_id)) . '\', true);"';
	}

	public static function GetFormEventValue($container_id, $bReplace = true, $bShadow = true, $event_delimiter = '\'')
	{
		$delimiter = $event_delimiter == '\'' ? '"' : '\'';
		return 'BX.ajax.submitComponentForm(this, ' . $delimiter . CUtil::JSEscape($container_id) . $delimiter . ', true)';
		//return 'jsAjaxUtil.'.($bReplace ? 'Insert' : 'Append').'FormDataToNode(this, '.$delimiter.$container_id.$delimiter.', '.($bShadow ? 'true' : 'false').')';
	}

	public static function encodeURI($str)
	{
		//$str = 'view'.$str;
		return $str;
	}

	public static function decodeURI($str)
	{
		global $APPLICATION;

		$pos = mb_strpos($str, 'view');
		if ($pos !== 0)
		{
			$APPLICATION->ThrowException(GetMessage('AJAX_REDIRECTOR_BAD_URL'));
			return false;
		}

		$str = str_replace(["\r", "\n"], "", mb_substr($str, 4));

		if (preg_match("'^(/bitrix/|http://|https://|ftp://)'i", $str))
		{
			$APPLICATION->ThrowException(GetMessage('AJAX_REDIRECTOR_BAD_URL'));
			return false;
		}

		return $str;
	}
}
