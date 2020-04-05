<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParamsMAL = array(
					"TITLE" => GetMessage("BCLMMSL_TITLE")
				);

$converter = CBXPunycode::GetConverter();

if(isset($arResult["ITEMS"]))
{
	foreach ($arResult["ITEMS"] as $domain => $info)
	{
		$arData = array();

		$arData["DETAIL_LINK"] = $info["DETAIL_LINK"];

		$domain = $converter->Decode($domain);

		$arData["TITLE"] = $domain;
		$arData["ID"] = md5($domain);

		if(isset($info["PROBLEM"]) && $info["PROBLEM"] = true)
			$arData["TITLE_COLOR"] = "RED";
		else
			$arData["FOLDED"] =  true;

		foreach ($info as $paramId => $param)
		{
			if($paramId == "HTTP_RESPONSE_TIME")
			{
				$arData['BOTTOM']['LEFT'] = GetMessage("BCLMMSL_MONITORING_HTTP_RESPONSE_TIME").": ".$param["DATA"];
			}
			else
			{
				if(HasMessage("BCLMMSL_MONITORING_".$paramId))
				{
					$content = GetMessage("BCLMMSL_MONITORING_".$paramId).": ";

					if(isset($param["PROBLEM"]) && $param["PROBLEM"] == true)
						$content .= '<span style="color:red">'.$param["DATA"].'</span>';
					else
						$content .= $param["DATA"];

					$arData["ROW"][] = array(
										"CONTENT" => $content,
										"TYPE" => "BULLET"
									);
				}
			}

		}

		if(!isset($arData['BOTTOM']['LEFT']))
			$arData['BOTTOM']['LEFT'] = GetMessage("BCLMMSL_MONITORING_HTTP_RESPONSE_TIME").": ".GetMessage("BCLMMSL_NO_DATA");

		$arParamsMAL["ITEMS"][] = $arData;
	}
}
else
{
	?>
		<div class="order_acceptpay_infoblock">
			<div class="order_acceptpay_infoblock_title"><?=GetMessage("BCLMMSL_NO_SITES_TITLE")?></div>
			<ul>
				<li>
					<div class="order_acceptpay_li_container">
						<label><?=GetMessage("BCLMMSL_NO_SITES")?></label>
					</div>
				</li>
			</ul>
		</div>
	<?
}

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.list',
	'.default',
	$arParamsMAL,
	false
);

?>

<script type="text/javascript">
	var listMenuItems = { items: [] };

	<?if(empty($arResult["DOMAINS_TO_ADD"])):?>
	listMenuItems.items.push ({
								name: "<?=GetMessage("BCLMMSL_NO_DOMAINS")?>",
								icon: "default"
							});
	<?else:?>
		<?foreach ($arResult["DOMAINS_TO_ADD"] as $domId => $domain):?>
			listMenuItems.items.push ({
										name: "<?=$converter->Decode($domain)?>",
										url: "<?=CHTTP::urlAddParams($arParams["EDIT_URL"],
														array(
															"action" => "add",
															"domain" => urlencode($domId)
															)
											)?>",
										icon: "add"
			});
		<?endforeach;?>
	<?endif;?>

	app.menuCreate(listMenuItems);

	app.addButtons({
		menuButton:
		{
			type:     'plus',
			callback: function()
			{
				app.menuShow();
			}
		},
	});

	var bcmm = new __BitrixCloudMobMon;
	BX.addCustomEvent('onAfterBCMMSiteDelete', function (params){ bcmm.showRefreshing(); location.reload(true); });
	BX.addCustomEvent('onAfterBCMMSiteUpdate', function (params){ bcmm.showRefreshing(); location.reload(true); });
	app.hidePopupLoader();
</script>