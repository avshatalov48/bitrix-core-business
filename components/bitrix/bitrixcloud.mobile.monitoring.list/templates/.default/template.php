<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$itemsCount = count($arResult["ITEMS"]);
$converter = CBXPunycode::GetConverter();

if($itemsCount <= 0)
{
	echo GetMessage("BCLMMSL_MONITORING_NO_DATA2");
	return;
}

if($arResult["HAVE_PROBLEM"])
{
	$sectionClass = $buttonClass = "yellow";
	$iconStyle = "badicon";
	$statusClass = "bad";
	$siteListClass = $itemsCount <= 2 ? "one" : "two";
}
else
{
	$sectionClass = $buttonClass = "blue";
	$iconStyle = "goodicon";
	$statusClass = "good";
	$siteListClass = $itemsCount <= 2 ? "one" : "two";
}

?>
<div class="security_wrap">
	<div class="security_section <?=$sectionClass?>">

		<h1><?=GetMessage("BCLMMSL_MONITORING_TITLE")?></h1>

		<div class="status <?=$statusClass?>">
			<div class="<?=$iconStyle?><?=($itemsCount > 2 ? " imgtoleft" : "")?>"></div>
			<p<?=($itemsCount > 2 ? " style=\"text-align: left;padding-top:6px;\"" : "")?>>
				<?if($arResult["HAVE_PROBLEM"] == true && isset($arResult["LOST_SUMM"])):?>
					<?=$arResult["COUNT_INTERVAL"]?><br>
					<strong <?=($itemsCount > 2 ? "style = \"font-size: 30px;text-align: left;color: #33290e;\"" : "")?>>
						<?=$arResult["LOST_SUMM"]?>
					</strong>
				<?else:?>
					<?=GetMessage("BCLMMSL_MONITORING_NO_PROBS")?>
				<?endif;?>
			</p>
		</div>

		<div class="sitelist <?=$siteListClass?>">
			<ul>
				<?foreach ($arResult["ITEMS"] as $domainName => $params):?>
					<?if($arResult["HAVE_PROBLEM"]):?>
						<?foreach ($params as $paramId => $state):?>
							<?if(isset($state["PROBLEM"]) && $state["PROBLEM"] == true):?>
								<li class="sitestatus bad domain">
									<a href="<?=$params["DETAIL_LINK"]?>">
										<?=$converter->Decode($domainName)?>
									</a>
								</li>
								<li class="sitestatus bad">
									<a href="<?=$params["DETAIL_LINK"]?>">
										<?=GetMessage("BCLMMSL_MONITORING_".$paramId)?>:
										<span>
											<?=$state["DATA"]?>
										</span>
									</a>
								</li>
							<?endif;?>
						<?endforeach;?>
					<?else:?>
						<li class="sitestatus good"><a href="<?=$params["DETAIL_LINK"]?>"><?=$converter->Decode($domainName)?></a></li>
					<?endif;?>
				<?endforeach;?>
			</ul>
		</div>

		<a href="<?=$arParams["LIST_URL"]?>" class="more_button <?=$buttonClass?>">
			<span></span>
			<?=GetMessage("BCLMMSL_MONITORING_BUT_DETAIL")?>
		</a>

	</div>
</div>

<script type="text/javascript">
	app.setPageTitle({title: "<?=GetMessage("BCLMMSL_TITLE")?>"});
	var bcmm = new __BitrixCloudMobMon;
	BX.addCustomEvent('onAfterBCMMSiteDelete', function (params){ bcmm.showRefreshing(); location.reload(true); });
	BX.addCustomEvent('onAfterBCMMSiteUpdate', function (params){ bcmm.showRefreshing(); location.reload(true); });
	app.hidePopupLoader();
</script>