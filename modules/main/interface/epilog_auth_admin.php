<?

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (isset($_REQUEST['bxsender']))
	return;

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/lang_files.php");
?>

	</div><?//login-main-wrapper?>

	<div style="display: none;" id="window_wrapper"></div>

<script>
BX.ready(BX.defer(function(){
	BX.addClass(document.body, 'login-animate');
	BX.addClass(document.body, 'login-animate-popup');
<?
$arPreload = array(
	'CSS' => array('/bitrix/panel/main/admin.css', '/bitrix/panel/main/admin-public.css', '/bitrix/panel/main/adminstyles_fixed.css', '/bitrix/themes/.default/modules.css'),
	'JS' => array('/bitrix/js/main/utils.js', '/bitrix/js/main/admin_tools.js', '/bitrix/js/main/popup_menu.js', '/bitrix/js/main/admin_search.js', '/bitrix/js/main/dd.js','/bitrix/js/main/date/main.date.js','/bitrix/js/main/core/core_date.js', '/bitrix/js/main/core/core_admin_interface.js', '/bitrix/js/main/core/core_autosave.js', '/bitrix/js/main/core/core_fx.js'),
);
foreach ($arPreload['CSS'] as $key=>$file)
	$arPreload['CSS'][$key] = CUtil::GetAdditionalFileURL($file,true);
foreach ($arPreload['JS'] as $key=>$file)
	$arPreload['JS'][$key] = CUtil::GetAdditionalFileURL($file,true);
?>

	//preload admin scripts&styles
	setTimeout(function() {
		BX.load(['<?=implode("','",$arPreload['CSS'])?>']);
		BX.load(['<?=implode("','",$arPreload['JS'])?>']);
	}, 2000);
}));

new BX.COpener({DIV: 'login_lang_button', ACTIVE_CLASS: 'login-language-btn-active', MENU: <?= Json::encode($arLangButton['MENU']) ?>});
</script>
</body>
</html>
