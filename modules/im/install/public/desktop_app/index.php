<?
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

\Bitrix\Main\Page\Asset::getInstance()->setJsToBody(false);

if (!CModule::IncludeModule('im'))
	return;

if (intval($USER->GetID()) <= 0 || \Bitrix\Im\User::getInstance()->isConnector())
{
	?>
<script type="text/javascript">
	if (typeof(BXDesktopSystem) != 'undefined')
		BXDesktopSystem.Login({});
	else
		location.href = '/';
</script><?
	return true;
}
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public/desktop_app/index.php");

if (isset($_GET['IFRAME']) == 'Y')
{
	$APPLICATION->IncludeComponent("bitrix:im.messenger", "iframe", Array(
		"CONTEXT" => "FULLSCREEN",
	), false, Array("HIDE_ICONS" => "Y"));
}
else if (!isset($_GET['BXD_API_VERSION']) && strpos($_SERVER['HTTP_USER_AGENT'], 'BitrixDesktop') === false)
{
	$APPLICATION->IncludeComponent("bitrix:im.messenger", "fullscreen", Array(
		"CONTEXT" => "FULLSCREEN",
		"DESIGN" => "DESKTOP",
	), false, Array("HIDE_ICONS" => "Y"));
}
else
{
	?>
	<script type="text/javascript">
		if (typeof(BXDesktopSystem) != 'undefined')
			BX.desktop.init();
		<?if (!isset($_GET['BXD_MODE'])):?>
		else
			location.href = '/';
		<?endif;?>
	</script>
	<?
	$APPLICATION->IncludeComponent("bitrix:im.messenger", "", Array(
		"CONTEXT" => "DESKTOP"
	), false, Array("HIDE_ICONS" => "Y"));

	$diskEnabled = false;
	if(IsModuleInstalled('disk'))
	{
		$diskEnabled =
			\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false) &&
			CModule::includeModule('disk');
		if($diskEnabled && \Bitrix\Disk\Configuration::REVISION_API >= 5)
		{
			$APPLICATION->IncludeComponent('bitrix:disk.bitrix24disk', '', array('AJAX_PATH' => '/desktop_app/disk.ajax.new.php'), false, Array("HIDE_ICONS" => "Y"));
		}
		else
		{
			$diskEnabled = false;
		}
	}

	if(!$diskEnabled && IsModuleInstalled('webdav'))
	{
		$APPLICATION->IncludeComponent('bitrix:webdav.disk', '', array('AJAX_PATH' => '/desktop_app/disk.ajax.php'), false, Array("HIDE_ICONS" => "Y"));
	}

	if (CModule::IncludeModule('timeman'))
	{
		CJSCore::init('im_timecontrol');
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
