<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="order_form_div">
<?


if ('' != $arResult["ERROR_MESSAGE"])
{
	ShowNote($arResult["ERROR_MESSAGE"]);
}

if (isset($arResult["ORDER_BASKET"]["CONFIRM_ORDER"]) && $arResult["ORDER_BASKET"]["CONFIRM_ORDER"] == "Y")
{
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_confirm.php");
}
else
{
	$arUrlTempl = Array(
		"delete" => $APPLICATION->GetCurPage()."?action=delete&id=#ID#",
		"shelve" => $APPLICATION->GetCurPage()."?action=shelve&id=#ID#",
		"add" => $APPLICATION->GetCurPage()."?action=add&id=#ID#",
	);
	?>
	<div style="display:none;">
		<div id="order_form_id">
			<input type="hidden" name="form" value="Y" />
			<?=bitrix_sessid_post()?>
			<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php");?>
			<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_delay.php"); ?>
			<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_notavail.php"); ?>
			<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_subscribe.php"); ?>
			<?
			if(count($arResult["ITEMS"]["AnDelCanBuy"]) > 0):

				$display = "none";
				if ($arParams['SHOW_BASKET_ORDER'] == 'Y')
					$display = "block";
				if (isset($_POST["display_props"]) && $_POST["display_props"] <> '')
					$display = htmlspecialcharsbx($_POST["display_props"]);
			?>
			<div id="delay_none" style="display:block">
				<input type="hidden" name="display_props" id="display_props" value="<?=$display?>" />

				<div id="order_props" style="display:<?=$display?>">
					<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_person_type.php");?>
					<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_props.php");?>

					<div class="order_submit"><input type="submit" value="<?=GetMessage("SALE_ORDER")?>" name="BasketOrder" id="basketOrderButton2" ></div>
				</div>
			</div>
			<?endif;?>
		</div>
	</div>

	<div id="form_new"></div>
	<script type="text/javascript">
		function ShowBasketItems(val)
		{
			if(val == 4)
			{
				if(document.getElementById("id-cart-list"))
					document.getElementById("id-cart-list").style.display = 'none';
				if(document.getElementById("id-shelve-list"))
					document.getElementById("id-shelve-list").style.display = 'none';
				if(document.getElementById("id-sub-list"))
					document.getElementById("id-sub-list").style.display = 'none';
				if(document.getElementById("id-noactive-list"))
					document.getElementById("id-noactive-list").style.display = 'block';

				if (document.getElementById("delay_none"))
					document.getElementById("delay_none").style.display = 'none';
			}
			else if(val == 3)
			{
				if(document.getElementById("id-cart-list"))
					document.getElementById("id-cart-list").style.display = 'none';
				if(document.getElementById("id-shelve-list"))
					document.getElementById("id-shelve-list").style.display = 'none';
				if(document.getElementById("id-noactive-list"))
					document.getElementById("id-noactive-list").style.display = 'none';
				if(document.getElementById("id-sub-list"))
					document.getElementById("id-sub-list").style.display = 'block';
				if (document.getElementById("delay_none"))
					document.getElementById("delay_none").style.display = 'none';
			}
			else if(val == 2)
			{
				if(document.getElementById("id-cart-list"))
					document.getElementById("id-cart-list").style.display = 'none';
				if(document.getElementById("id-sub-list"))
					document.getElementById("id-sub-list").style.display = 'none';
				if(document.getElementById("id-noactive-list"))
					document.getElementById("id-noactive-list").style.display = 'none';
				if(document.getElementById("id-shelve-list"))
					document.getElementById("id-shelve-list").style.display = 'block';
				if (document.getElementById("delay_none"))
					document.getElementById("delay_none").style.display = 'none';
			}
			else if(val == 1)
			{
				if(document.getElementById("id-cart-list"))
					document.getElementById("id-cart-list").style.display = 'block';
				if(document.getElementById("id-sub-list"))
					document.getElementById("id-sub-list").style.display = 'none';
				if(document.getElementById("id-noactive-list"))
					document.getElementById("id-noactive-list").style.display = 'none';
				if(document.getElementById("id-shelve-list"))
					document.getElementById("id-shelve-list").style.display = 'none';
				if (document.getElementById("delay_none"))
					document.getElementById("delay_none").style.display = 'block';
			}
		}
		function submitFormProxy()
		{
			if(BX.locationSelectorLock === true)
				return;

			submitForm();
		}
		function submitForm()
		{
			var orderForm = document.getElementById('ORDER_FORM_ID_NEW');

			var ajaxcall = BX.create('input', {
				props: {
					type: 'hidden',
					name: 'AJAX_CALL',
					value: 'Y'
				}
			});

			orderForm.appendChild(ajaxcall);

			BX.ajax.submitComponentForm(orderForm, 'order_form_div', true);
			BX.submit(orderForm);

			return true;
		}
		function ShowOrder()
		{
			if (BX('order_props').style.display == "block")
				BX('order_props').style.display = "none";
			else
				BX('order_props').style.display = "block";

			BX('display_props').value = BX('order_props').style.display;

			return false;
		}

		var newform = document.createElement("FORM");
		newform.method = "POST";
		newform.action = "";
		newform.name = "<?=$FORM_NAME?>";
		newform.id = "ORDER_FORM_ID_NEW";
		var im = document.getElementById('order_form_id');
		document.getElementById("form_new").appendChild(newform);
		newform.appendChild(im);

		<?if(CSaleLocation::isLocationProMigrated()):?>
			setTimeout(function(){
				BX.locationSelectorLock = true;
				BX.onCustomEvent(window, 'sboa-init-loc-selector');
				BX.locationSelectorLock = false;
			}, 15); <?//no way to hang on form repaint event, so too bad solution?>
		<?endif?>
	</script>
<?
}
?>
</div>