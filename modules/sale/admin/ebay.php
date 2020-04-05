<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */

\Bitrix\Main\Page\Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/bitrix/css/sale/ebay.css">');
$APPLICATION->SetTitle(Loc::getMessage("SALE_EBAY_TITLE"));

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<div class="adm-detail-block">
	<div class="adm-detail-content-wrap">
		<div class="adm-detail-content">
			<div class="adm-detail-title adm-detail-title-colored"><?=Loc::getMessage('SALE_EBAY_TITLE2')?></div>
			<div class="ebay-info-container">
				<div class="ebay-info">
					<div class="ebay-info-container-title"><?=Loc::getMessage('SALE_EBAY_SALES_CHANNEL')?>:</div>
					<img src="/bitrix/images/sale/ebay/ebay.png" alt="">
					<span class="ebay-info-container-text"><?=Loc::getMessage('SALE_EBAY_FOR_SELLERS')?></span>
					<p><?=Loc::getMessage('SALE_EBAY_TRADING_PLATFORM')?></p>
				</div>
			</div>
			<div class="ebay-advantages">
				<div class="ebay-advantages-container">
					<img src="/bitrix/images/sale/ebay/ebay-img.jpg" alt="">
					<div class="ebay-advantages-list">
						<p><?=Loc::getMessage('SALE_EBAY_ADV1')?></p>
						<p><?=Loc::getMessage('SALE_EBAY_ADV2')?></p>
						<p><?=Loc::getMessage('SALE_EBAY_ADV3')?></p>
						<p><?=Loc::getMessage('SALE_EBAY_ADV4')?></p>
						<p><?=Loc::getMessage('SALE_EBAY_ADV5')?></p>
					</div>
				</div>
			</div>
			<div class="ebay-title-ribbon">
				<span><?=Loc::getMessage('SALE_EBAY_DIRECT_INTEGRATION')?></span>
			</div>
			<div class="ebay-conditions">
				<div class="ebay-conditions-item">
					<div class="ebay-icon-container">
						<img src="/bitrix/images/sale/ebay/ebay-img1.png" alt="">
					</div>
					<span><?=Loc::getMessage('SALE_EBAY_SALE_RUS')?>!</span>
				</div>
				<div class="ebay-conditions-item">
					<div class="ebay-icon-container">
						<img src="/bitrix/images/sale/ebay/ebay-img2.png" alt="">
					</div>
					<span><?=Loc::getMessage('SALE_EBAY_EXPORT')?>!</span>
				</div>
				<div class="ebay-conditions-item">
					<div class="ebay-icon-container">
						<img src="/bitrix/images/sale/ebay/ebay-img3.png" alt="">
					</div>
					<span><?=Loc::getMessage('SALE_EBAY_PART_OF_BITRIX')?>!</span>
				</div>
			</div>
			<div class="ebay-start">
				<div class="ebay-start-title"><?=Loc::getMessage('SALE_EBAY_HOWTO_START')?></div>
				<div class="ebay-start-content">
					<div class="ebay-start-content-item"><span>1</span><?=Loc::getMessage('SALE_EBAY_HOWTO_START1')?></div>
					<div class="ebay-start-content-item"><span>2</span><?=Loc::getMessage('SALE_EBAY_HOWTO_START2')?></div>
					<div class="ebay-start-content-item"><span>3</span><?=Loc::getMessage('SALE_EBAY_HOWTO_START3')?></div>
					<div class="ebay-start-content-item"><span>4</span><?=Loc::getMessage('SALE_EBAY_HOWTO_START4')?></div>
					<div class="ebay-start-content-item"><span>5</span><?=Loc::getMessage('SALE_EBAY_HOWTO_START5')?></div>
				</div>
				<div class="ebay-start-btn-container">
					<a href="/bitrix/admin/sale_ebay_wizard.php?lang=<?=LANGUAGE_ID?>" class="ebay-start-btn"><?=Loc::getMessage('SALE_EBAY_START')?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

