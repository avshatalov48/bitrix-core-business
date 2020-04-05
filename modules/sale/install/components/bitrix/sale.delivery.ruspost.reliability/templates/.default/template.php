<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
?>

<div title="<?=Loc::getMessage('SALE_SDRR_INCEDENT_TITLE')?>" title="<?=Loc::getMessage('SALE_SDRR_INCEDENT_TITLE')?>">
	<?if($arResult['RELIABILITY'] == \Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service::RELIABLE):?>
        <span class="sale-ruspost-reliability-reliable"><?=Loc::getMessage('SALE_SDRR_INCEDENT_NOT_HAPPEND')?></span>
	<?elseif($arResult['RELIABILITY'] == \Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service::FRAUD):?>
        <span class="sale-ruspost-reliability-not-reliable"><?=Loc::getMessage('SALE_SDRR_INCEDENT_HAPPEND')?></span>
	<?else:?>
        <span><?=Loc::getMessage('SALE_SDRR_INCEDENT_UNKNOWN')?></span>
    <?endif;?>
</div>

