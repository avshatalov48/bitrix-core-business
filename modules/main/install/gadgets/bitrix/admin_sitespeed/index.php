<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Analytics;

$APPLICATION->SetAdditionalCSS("/bitrix/gadgets/bitrix/admin_sitespeed/site_speed.css");
?>

<div class="bx-gadgets-speed-top" id="speed-top-block">
	<div class="bx-gadgets-title bx-gadgets-title-speed">
		<?=GetMessage("GD_SPEED_TITLE")?>:
		<a href="/bitrix/admin/site_speed.php?lang=<?=LANGUAGE_ID?>" class="bx-gadget-text-color" id="site-speed-index-text"></a>
	</div>
	<div class="bx-gadget-speed-num-block" id="bx-gadget-speed-num-block">
		<span class="bx-gadget-speed-num" id="site-speed-index"></span><span class="bx-gadget-speed-text"><?=GetMessage("GD_SPEED_UNIT")?></span>
		<div class="bx-gadget-speed-waiter"></div>
	</div>
</div>
<div class="bx-gadget-speed-speedo-block">
	<div class="bx-gadget-speed-ruler"><span class="bx-gadget-speed-ruler-start">0</span></div>
	<div class="bx-gadget-speed-graph">
		<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-varyfast">
			<span class="bx-gadget-speed-graph-text"><?=GetMessage("GD_SPEED_VERY_FAST")?></span>
		</span><span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-fast">
			<span class="bx-gadget-speed-graph-text"><?=GetMessage("GD_SPEED_FAST")?></span>
		</span><span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-notfast">
			<span class="bx-gadget-speed-graph-text"><?=GetMessage("GD_SPEED_NOT_FAST")?></span>
		</span><span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-slow">
			<span class="bx-gadget-speed-graph-text"><?=GetMessage("GD_SPEED_SLOW")?></span>
		</span><span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-veryslow">
			<span class="bx-gadget-speed-graph-text"><?=GetMessage("GD_SPEED_VERY_SLOW")?></span>
		</span>
		<div class="bx-gadget-speed-pointer" id="site-speed-pointer">
			<div class="bx-gadget-speed-value" id="site-speed-pointer-index"></div>
		</div>
	</div>
</div>
<div style="cursor:move;" class="bx-gadgets-side"></div>

<?
$currentHost = preg_replace("/:(80|443)$/", "", $_SERVER["HTTP_HOST"]);
$currentHost = CUtil::JSEscape($currentHost);
?>
<script>
	BX.ready(function() {

		BX.ajax({
			method: "POST",
			dataType: "json",
			url: document.location.protocol + "//www.1c-bitrix.ru/buy_tmp/ba.php",
			data : {
				license : "<?=CUtil::JSEscape(Analytics\Counter::getPrivateKey())?>",
				op : "site_index",
				domain : "<?=$currentHost?>",
				aid: "<?=CUtil::JSEscape(Analytics\Counter::getAccountId())?>",
				tmz: new Date().getTimezoneOffset()
			},
			onsuccess: function(data) {
				if (!data || !BX.type.isNumber(data["p50"]))
				{
					showError();
					return;
				}
				var siteIndex = (data["p50"]/1000).toFixed(2);
				var siteIndexPercent = data["p50"]/2500 * 100;
				siteIndexPercent = Math.min(Math.max(siteIndexPercent, 4), 98);

				BX("site-speed-index").innerHTML = siteIndex;
				BX("site-speed-pointer-index").innerHTML = siteIndex;
				BX.addClass(BX("speed-top-block"), "bx-gadgets-speed-ready");
				BX("site-speed-pointer").style.left = siteIndexPercent + "%";
				BX("site-speed-index-text").innerHTML = getIndexText(data["p50"]).replace("<br>", " ");
			},
			onfailure: function() {
				showError();
			}
		});

		function showError()
		{
			BX("bx-gadget-speed-num-block").style.display = "none";
			BX.addClass(BX("speed-top-block"), "bx-gadgets-speed-ready");
			BX("site-speed-index-text").innerHTML = "<?=GetMessageJs("GD_SPEED_NO_DATA")?>";
		}

		function getIndexText(index)
		{
			var intervals = [
				[500, "<?=GetMessageJs("GD_SPEED_VERY_FAST")?>"],
				[1000, "<?=GetMessageJs("GD_SPEED_FAST")?>"],
				[1500, "<?=GetMessageJs("GD_SPEED_NOT_FAST")?>"],
				[2000, "<?=GetMessageJs("GD_SPEED_SLOW")?>"],
				[2500, "<?=GetMessageJs("GD_SPEED_VERY_SLOW")?>"]
			];

			for (var i = 0; i < intervals.length; i++)
			{
				if (index < intervals[i][0])
				{
					return intervals[i][1];
				}
			}

			return intervals[intervals.length-1][1];
		}
	});
</script>