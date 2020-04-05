<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/admin/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

Loc::loadMessages(__FILE__);

if (LANGUAGE_ID == "ru")
	$b24Lang = "ru";
elseif (LANGUAGE_ID == "de")
	$b24Lang = "de";
else
	$b24Lang = "com";

$APPLICATION->SetTitle(Loc::getMessage('B24C_TEL_TITLE'));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<div class="connector">
	<div class="connector-content">
		<p class="connector-title"><?=Loc::getMessage('B24C_TEL_TITLE')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_TEL_DESCR1')?></p>
		<img src="/bitrix/images/b24connector/img-2.png" alt="" class="connector-img">
		<p class="connector-title-sm"><?=Loc::getMessage('B24C_TEL_AUTO_FIX')?></p>
		<ul class="connector-description-ul-green">
			<li><?=Loc::getMessage('B24C_TEL_DESCR2')?></li>
			<li><?=Loc::getMessage('B24C_TEL_DESCR3')?></li>
			<li><?=Loc::getMessage('B24C_TEL_DESCR4')?></li>
			<li><?=Loc::getMessage('B24C_TEL_DESCR5')?></li>
			<li><?=Loc::getMessage('B24C_TEL_DESCR6')?></li>
			<li><?=Loc::getMessage('B24C_TEL_DESCR7')?></li>
		</ul>
		<p class="connector-title-sm"><?=Loc::getMessage('B24C_TEL_START')?></p>
		<div class="connector-callback">
			<div class="connector-callback-item">
				<p class="connector-callback-title"><?=Loc::getMessage('B24C_TEL_INSTALL_MOBILE')?></p>
				<div class="connector-callback-desc">
					<p class="connector-callback-text"><?=Loc::getMessage('B24C_TEL_CONNECT')?></p>
					<div class="connector-callback-app">
						<div class="connector-callback-app-title connector-callback-app-title-blue"><?=Loc::getMessage('B24C_TEL_MOBILE_APP')?></div>
						<div class="connector-callback-app-flex">
							<a href="https://itunes.apple.com/<?=LANGUAGE_ID == 'en/' ? '' : LANGUAGE_ID.'/'?>app/bitrix24/id561683423?mt=8" class="connector-callback-app-appstore">APP STORE</a>
							<a href="https://play.google.com/store/apps/details?id=com.bitrix24.android&hl=<?=LANGUAGE_ID?>" class="connector-callback-app-google">GOOGLE PLAY</a>
						</div>
					</div>
				</div>
			</div>
			<div class="connector-callback-item">
				<p class="connector-callback-title"><?=Loc::getMessage('B24C_TEL_INSTALL_DESKTOP')?></p>
				<div class="connector-callback-desc">
					<p class="connector-callback-text"><?=Loc::getMessage('B24C_TEL_CALLS')?></p>
					<div class="connector-callback-app">
						<div class="connector-callback-app-title"><?=Loc::getMessage('B24C_TEL_DESKTOP')?></div>
						<div class="connector-callback-app-flex">
							<a href="http://dl.bitrix24.com/b24/bitrix24_desktop.dmg" class="connector-callback-app-mac">MAC OS</a>
							<a href="http://dl.bitrix24.com/b24/bitrix24_desktop.exe" class="connector-callback-app-win">WINDOWS</a>
							<a href="https://github.com/buglloc/brick/" class="connector-callback-app-linux">LINUX</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="connector-create">
		<?if(Connection::isExist()):?>
			<a href=<?=Connection::getTelephonyConfigUrl()?> class="connector-btn-blue"><?=Loc::getMessage('B24C_TEL_GET_TELEPHONY')?></a>
		<?else:?>
			<?=Connection::getButtonHtml()?>&nbsp;&nbsp;
			<?='<a href="https://www.bitrix24.'.$b24Lang.'/" class="connector-button-green">'.Loc::getMessage('B24C_TEL_CREATE_B24').'</a>'?>
		<?endif;?>
	</div>
</div>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");