<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

class CJSPopup
{
	var $__form_name = 'bx_popup_form';
	var $post_args;
	var $title = '';
	var $bDescriptionStarted = false;
	var $bContentStarted = false;
	var $bButtonsStarted = false;
	var $suffix = '';
	var $jsPopup = 'BX.WindowManager.Get()';
	var $bContentBuffered;
	var $cont_id;

	var $bInited = false;

	/*
	$arConfig = array(
		'TITLE' => 'Popup window title',
		'ARGS' => 'param1=values1&param2=value2', // additional GET arguments for POST query
	)
	*/
	public function __construct($title = '', $arConfig = array())
	{
		if ($title != '') $this->SetTitle($title);
		if (is_set($arConfig, 'TITLE')) $this->SetTitle($arConfig['TITLE']);
		if (is_set($arConfig, 'ARGS')) $this->SetAdditionalArgs($arConfig['ARGS']);
		if (is_set($arConfig, 'SUFFIX') && strlen($arConfig['SUFFIX']) > 0) $this->SetSuffix($arConfig['SUFFIX']);
	}

	function InitSystem()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if (!$this->bInited && $_REQUEST['bxsender'] != 'core_window_cauthdialog')
		{
			$this->InitScripts();

			$APPLICATION->AddBufferContent(array($this, "_InitSystem"));

			$APPLICATION->ShowHeadStrings();
			$APPLICATION->ShowHeadScripts();

			$this->bInited = true;
		}
	}

	function _InitSystem()
	{
		$adminPage = new CAdminPage();

		echo $adminPage->ShowPopupCSS();
		echo $adminPage->ShowScript();

	}

	function InitScripts()
	{
		CJSCore::Init(array('admin_interface'));
	}

	function SetAdditionalArgs($additional_args = '')
	{
		$this->post_args = $additional_args;
	}

	function SetTitle($title = '')
	{
		$this->title = trim($title);
	}

	function GetFormName()
	{
		return $this->__form_name;
	}

	function SetSuffix($suffix)
	{
		$this->suffix = '_'.trim($suffix);
		$this->__form_name .= $this->suffix;
	}

	function ShowTitlebar($title = '')
	{
		$this->InitSystem();

		if ($title == '')
			$title = $this->title;
		?>
		<script type="text/javascript">
			var currentWindow = top.window;
			if (top.BX.SidePanel.Instance && top.BX.SidePanel.Instance.getTopSlider())
			{
				currentWindow = top.BX.SidePanel.Instance.getTopSlider().getWindow();
			}
			currentWindow.<?=$this->jsPopup?>.SetTitle('<?echo CUtil::JSEscape($title)?>');
		</script>
		<?
	}

	function StartDescription($icon = false)
	{
		$this->InitSystem();

		$this->bDescriptionStarted = true;
?>
<script type="text/javascript"><?if ($icon):?>
	<?if (strpos($icon, '/') === false):?>

		<?=$this->jsPopup?>.SetIcon('<?echo CUtil::JSEscape($icon)?>');
	<?else:?>

		<?=$this->jsPopup?>.SetIconFile('<?echo CUtil::JSEscape($icon)?>');
	<?endif;?>
<?endif;?>
<?
			ob_start();
	}

	function EndDescription()
	{
		if ($this->bDescriptionStarted)
		{
			$descr = ob_get_contents();
			ob_end_clean();
?>

<?=$this->jsPopup?>.SetHead('<?echo CUtil::JSEscape($descr)?>');</script>
<?
			//echo '</div></div>';
			$this->bDescriptionStarted = false;
		}
	}

	function StartContent($arAdditional = array())
	{
		$this->InitSystem();

		$this->EndDescription();
		$this->bContentStarted = true;

		if ($arAdditional['buffer'])
		{
			$this->bContentBuffered = true;
			//ob_start();
			$this->cont_id = RandString(10);
			echo '<div id="'.$this->cont_id.'" style="display: none;">';
		}

		echo '<form name="'.$this->__form_name.'">'."\r\n";
		echo bitrix_sessid_post()."\r\n";

		if (is_set($_REQUEST, 'back_url'))
			echo '<input type="hidden" name="back_url" value="'.htmlspecialcharsbx($_REQUEST['back_url']).'" />'."\r\n";
	}

	function EndContent()
	{
		if ($this->bContentStarted)
		{
			echo '</form>'."\r\n";

			$hkInstance = CHotKeys::getInstance();
			$Execs = $hkInstance->GetCodeByClassName("CDialog");
			echo $hkInstance->PrintJSExecs($Execs, "", true, true);

			if ($this->bContentBuffered)
			{
?></div><script type="text/javascript">BX.ready(function() {<?=$this->jsPopup?>.SwapContent(BX('<?echo $this->cont_id?>'))});</script><?
			}

			if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE == false)
			{
?><script type="text/javascript"><?echo "BX.adminFormTools.modifyFormElements(".$this->jsPopup.".DIV);"?></script><?
			}

			$this->bContentStarted = false;
		}
	}

	function StartButtons()
	{
		$this->InitSystem();

		$this->EndDescription();
		$this->EndContent();

		$this->bButtonsStarted = true;

		ob_start();
	}

	function EndButtons()
	{
		if ($this->bButtonsStarted)
		{
			$buttons = ob_get_contents();
			ob_end_clean();
?>
		<script type="text/javascript"><?=$this->jsPopup?>.SetButtons('<?echo CUtil::JSEscape($buttons)?>');</script>
<?
			$this->bButtonsStarted = false;
		}
	}

	function ShowStandardButtons($arButtons = array('save', 'cancel'))
	{
		$this->InitSystem();

		if (!is_array($arButtons)) return;

		if ($this->bButtonsStarted)
		{
			$this->EndButtons();
		}

		$arSB = array('save' => $this->jsPopup.'.btnSave', 'cancel' => $this->jsPopup.'.btnCancel', 'close' => $this->jsPopup.'.btnClose');

		foreach ($arButtons as $key => $value)
			if (!$arSB[$value]) unset($arButtons[$key]);
		$arButtons = array_values($arButtons);

?>
<script type="text/javascript"><?=$this->jsPopup?>.SetButtons([<?
	foreach ($arButtons as $key => $btn)
		echo ($key ? ',' : '').$arSB[$btn];
?>]);</script><?
	}

	function ShowValidationError($errortext)
	{
		$this->EndDescription();
		echo '<script>top.'.$this->jsPopup.'.ShowError(\''.CUtil::JSEscape(str_replace(array('<br>', '<br />', '<BR>', '<BR />'), "\r\n", $errortext)).'\')</script>';
	}

	function ShowError($errortext, $title = '')
	{
		$this->ShowTitlebar($title != "" ? $title : $this->title);

		if (!$this->bDescriptionStarted)
			$this->StartDescription();

		ShowError($errortext);

		$this->ShowStandardButtons(array("close"));
		echo '<script>'.$this->jsPopup.'.AdjustShadow();</script>';
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");

		exit();
	}

	function Close($bReload = true, $back_url = false)
	{
		if (!$back_url && is_set($_REQUEST, 'back_url'))
			$back_url = $_REQUEST['back_url'];

		if(substr($back_url, 0, 1) != "/" || substr($back_url, 1, 1) == "/")
		{
			//only local /url is allowed
			$back_url = '';
		}

		echo '<script>';
		echo 'top.'.$this->jsPopup.'.Close(); ';

		if ($bReload)
		{
			echo 'top.BX.showWait(); ';
			echo "top.BX.reload('".CUtil::JSEscape($back_url)."', true);";
		}
		echo '</script>';
		die();
	}
}

class CJSPopupOnPage extends CJSPopup
{
	function InitSystem() {} // this SHOULD be empty!
}
