<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
echo '<'.'?xml version="1.0" encoding="utf-8" ?'.'>';

//We'll show at least one price
$priceCounter = 0;
foreach($arResult["ITEMS"] as $arItem)
{
	if (isset($arItem["PRICE"]) && isset($arItem["VALUES"]["MIN"]["VALUE"]) && isset($arItem["VALUES"]["MAX"]["VALUE"]))
		$priceCounter++;
}

if ($priceCounter > 0)
{
	$priceCounter = 1;//When price is showed
	$rangeCounter = 0;//Range propery will be skipped
	$totalCounter = 8;//Overall properties count
}
else
{
	$priceCounter = 0;//When price is NOT showed
	$rangeCounter = 1;//We can show no more than one range propery
	$totalCounter = 8;//Overall properties count
}

?>
<site
	xmlns="http://interactive-answers.webmaster.yandex.ru/schemas/site/0.0.1"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://interactive-answers.webmaster.yandex.ru/schemas/site/0.0.1  http://interactive-answers.webmaster.yandex.ru/schemas/site-0.0.1.xsd"
>
	<title><?echo $arResult["SECTION_TITLE"]?></title>
	<description><?echo $arResult["SECTION_DESCRIPTION"]?></description>
	<rootUrl><?echo CHTTP::urn2uri("/");?></rootUrl>
	<resource>
		<fixed name="<?echo CHTTP::urn2uri($arResult["FORM_ACTION"]);?>"/>
	</resource>
	<filters>
	<constant key="Y">
		<description caption="set_filter">
			<setParameter name="set_filter"/>
		</description>
	</constant>
	<?foreach($arResult["HIDDEN"] as $arItem):?>
		<constant key="<?echo $arItem["HTML_VALUE"]?>">
			<description caption="<?echo $arItem["CONTROL_ID"]?>">
				<setParameter name="<?echo $arItem["CONTROL_NAME"]?>"/>
			</description>
		</constant>
	<?endforeach;?>
	<?foreach($arResult["ITEMS"] as $arItem):?>
		<?if($priceCounter && isset($arItem["PRICE"])):?>
			<?if(isset($arItem["VALUES"]["MIN"]["VALUE"]) && isset($arItem["VALUES"]["MAX"]["VALUE"])):?>
				<?$priceCounter--;$totalCounter--;?>
				<rangeFilter min="<?echo floor($arItem["VALUES"]["MIN"]["VALUE"])?>" max="<?echo ceil($arItem["VALUES"]["MAX"]["VALUE"])?>" step="1" <?if (count($arItem["CURRENCIES"]) == 1) echo ' unit="'.current($arItem["CURRENCIES"]).'"';?>>
					<description caption="<?echo $arItem["NAME"]?>">
						<setParameter name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"/>
						<setParameter name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"/>
					</description>
				</rangeFilter>
			<?endif;?>
		<?elseif($rangeCounter && $arItem["PROPERTY_TYPE"] == "N"):?>
			<?if(isset($arItem["VALUES"]["MIN"]["VALUE"]) && isset($arItem["VALUES"]["MAX"]["VALUE"])):?>
				<?$rangeCounter--;$totalCounter--;?>
				<rangeFilter min="<?echo floor($arItem["VALUES"]["MIN"]["VALUE"])?>" max="<?echo ceil($arItem["VALUES"]["MAX"]["VALUE"])?>" step="1" <?if (count($arItem["CURRENCIES"]) == 1) echo ' unit="'.current($arItem["CURRENCIES"]).'"';?>>
					<description caption="<?echo $arItem["NAME"]?>">
						<setParameter name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"/>
						<setParameter name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"/>
					</description>
				</rangeFilter>
			<?endif;?>
		<?elseif($totalCounter && !empty($arItem["VALUES"])):;?>
			<?$totalCounter--;?>
			<dropDown>
				<description caption="<?echo $arItem["NAME"]?>">
					<?$ar = current($arItem["VALUES"])?>
					<setParameter name="<?echo $ar["CONTROL_NAME_ALT"]?>"/>
				</description>
				<?foreach($arItem["VALUES"] as $val => $ar):?>
					<dropDownValue key="<?echo $ar["HTML_VALUE_ALT"]?>" caption="<?echo $ar["VALUE"];?>"/>
				<?endforeach;?>
			</dropDown>
		<?endif;?>
	<?endforeach;?>
	</filters>
</site>
