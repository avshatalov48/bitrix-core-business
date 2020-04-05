<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript">
	BX.addCustomEvent("onAuthSuccess", function(data) {
		document.location.href = document.location.href;
	});

	BX.addCustomEvent("onItemBuy", function() {
		document.location.href = document.location.href;;
	});
	app.setPageTitle({"title" : "<?=GetMessage("SALE_BASKET")?>"});
</script>
<?
//if (StrLen($arResult["ERROR_MESSAGE"])<=0)
//{
	$arUrlTempl = Array(
		"delete" => $APPLICATION->GetCurPage()."?action=delete&id=#ID#",
		"shelve" => $APPLICATION->GetCurPage()."?action=shelve&id=#ID#",
		"add" => $APPLICATION->GetCurPage()."?action=add&id=#ID#",
	);
	?>
	<script>
	function ShowBasketItems(val)
	{
		if(val == 2)
		{
			var tagBody = BX('body');
			BX.addClass(tagBody, "delayed");

			if(document.getElementById("id-cart-list"))
				document.getElementById("id-cart-list").style.display = 'none';
			if(document.getElementById("id-shelve-list"))
				document.getElementById("id-shelve-list").style.display = 'block';
			/*if(document.getElementById("id-subscribe-list"))
				document.getElementById("id-subscribe-list").style.display = 'none';
			if(document.getElementById("id-na-list"))
				document.getElementById("id-na-list").style.display = 'none'; */
		}
		/*else if(val == 3)
		{
			if(document.getElementById("id-cart-list"))
				document.getElementById("id-cart-list").style.display = 'none';
			if(document.getElementById("id-shelve-list"))
				document.getElementById("id-shelve-list").style.display = 'none';
			if(document.getElementById("id-subscribe-list"))
				document.getElementById("id-subscribe-list").style.display = 'block';
			if(document.getElementById("id-na-list"))
				document.getElementById("id-na-list").style.display = 'none';
		}
		else if (val == 4)
		{
			if(document.getElementById("id-cart-list"))
				document.getElementById("id-cart-list").style.display = 'none';
			if(document.getElementById("id-shelve-list"))
				document.getElementById("id-shelve-list").style.display = 'none';
			if(document.getElementById("id-subscribe-list"))
				document.getElementById("id-subscribe-list").style.display = 'none';
			if(document.getElementById("id-na-list"))
				document.getElementById("id-na-list").style.display = 'block';
		}   */
		else
		{
			var tagBody = BX('body');
			BX.removeClass(tagBody, "delayed");
			if(document.getElementById("id-cart-list"))
				document.getElementById("id-cart-list").style.display = 'block';
			if(document.getElementById("id-shelve-list"))
				document.getElementById("id-shelve-list").style.display = 'none';
			/*if(document.getElementById("id-subscribe-list"))
				document.getElementById("id-subscribe-list").style.display = 'none';
			if(document.getElementById("id-na-list"))
				document.getElementById("id-na-list").style.display = 'none';  */
		}
	}

	function changeMode() {
		var tagBody = BX('body');//document.getElementsByTagName('body');
		var qounters = BX.findChildren(BX('id-cart-list'), {className : "quantity_input"}, true);

		if (BX.hasClass(tagBody, 'edit')) {
			BX.addClass(tagBody, "noedit");
			BX.removeClass(tagBody, "edit");
			//qounters.disabled = true;
			return false;
		} else {
			BX.addClass(tagBody, "edit");
			BX.removeClass(tagBody, "noedit");
			//	qounters.disabled = false;
			return false;
		}
	};

	function ajaxInCart (href, data)
	{
		app.showPopupLoader({test:''});
		BX.ajax({
			timeout:   30,
			method:   'POST',
			url: href,
			data: data,
			processData: false,
			onsuccess: function(reply){
				var json = JSON.parse(reply);

				if (json.items && json.items.AnDelCanBuy)
				{
					for (var i=0; i<json.items.AnDelCanBuy.length; i++)
					{
						var curItem = json.items.AnDelCanBuy[i];
						var itemBlock = BX('basketItemID_'+curItem.ID);
						if (itemBlock)
						{
							var itemPriceBlock = BX.findChild(itemBlock, {class : "cart_price_conteiner"}, true, false);
							if (curItem.FULL_PRICE > 0)
							{
								BX.addClass(itemPriceBlock, 'oldprice');
								itemPriceBlock.innerHTML = '<span class="item_price">'+curItem.PRICE_FORMATED+'</span><span class="item_price_old">'+curItem.FULL_PRICE_FORMATED+'</span>';
							}
							else
							{
								BX.removeClass(itemPriceBlock, 'oldprice');
								itemPriceBlock.innerHTML = '<span class="item_price">'+curItem.PRICE_FORMATED+'</span>';
							}
						}
					}
				}

				app.hidePopupLoader();
				if (parseInt(json.num_cart_items) > 0)
				{
					BX('cart_item_bottom').style.display = "block";
					BX('empty_cart_text').style.display = "none";
				}
				else
				{
					BX('cart_item_bottom').style.display = "none";
					BX('empty_cart_text').style.display = "block";
				}
				var cart_title = BX.findChildren(BX('body'), {class : "cart-item-title"}, true);
				for (var i=0; i<cart_title.length; i++)
				{
					cart_title[i].innerHTML = "("+json.num_cart_items+")";
				}
				var delay_title = BX.findChildren(BX('body'), {class : "delay-item-title"}, true);
				for (var i=0; i<delay_title.length; i++)
				{
					delay_title[i].innerHTML = "("+json.num_delay_items+")";
				}

				BX('all_price').innerHTML = json.price;
				if (json.is_discount)
				{
					BX('all_discount').innerHTML = '<div class=\'cart_item_total_price\'><?echo GetMessage("SALE_CONTENT_DISCOUNT")?>: '+json.discount+'</div>';
				}
				else
				{
					BX('all_discount').innerHTML = '';
				}
				<?if (in_array("WEIGHT", $arParams["COLUMNS_LIST"])):?>
					BX('weight').innerHTML = json.weight;
					<?endif?>
				<?if ($arParams['PRICE_VAT_SHOW_VALUE'] == 'Y'):?>
					BX('vat_excluded').innerHTML = json.vat_excluded;
					BX('vat_included').innerHTML = json.vat_included;
				<?endif?>
			},
			onfailure: function(){
			}
		});
	}
	function DeleteFromCart(element)
	{
		app.confirm(
			{
				text : "<?=GetMessage("SALE_DELETE_CONFIRM")?>",
				title : "<?=GetMessage("MB_CONFIRM_TITLE_DELETE")?>",
				callback : function(a) {
					if (a == 2)
						return false;
					else if (a == 1)
					{
						BX.remove(BX.findParent(element, {tagName : "li"}, false));
						var data='';
						ajaxInCart(element.href, data);
					}
				},
				buttons : ["<?=GetMessage("MB_CONFIRM_YES")?>", "<?=GetMessage("MB_CONFIRM_CANCEL")?>"]

			}
		);
		return false;

	}
	function DelayInCart(element)
	{
		var element_href = element.href;
		var delayItem = BX.findParent(element, {tagName : "li"}, false);
		var countMinus = BX.findChild(delayItem, {class : "count_minus"}, true, false);
		countMinus.style.display = 'none';
		var countPlus = BX.findChild(delayItem, {class : "count_plus"}, true, false);
		countPlus.style.display = 'none';
		var quantity_input = BX.findChild(delayItem, {class : "quantity_input"}, true, false);
		quantity_input.readOnly = 1;

		var delayLink = BX.findChild(delayItem, {class : "cart_item_delayed"}, true, false);
		var delayHref = delayLink.href;
		delayHref = delayHref.replace("shelve", "add");
		delayLink.href = delayHref;
		delayLink.onclick = function(){  return Add2Order(element);}

		BX('id-shelve-ul').appendChild(delayItem);
		var data='';
		ajaxInCart(element_href, data);
		return false;
	}
	function Add2Order(element)
	{
		var element_href = element.href;
		var addItem = BX.findParent(element, {tagName : "li"}, false);
		var countMinus = BX.findChild(addItem, {class : "count_minus"}, true, false);
		countMinus.style.display = 'block';
		var countPlus = BX.findChild(addItem, {class : "count_plus"}, true, false);
		countPlus.style.display = 'block';
		var quantity_input = BX.findChild(addItem, {class : "quantity_input"}, true, false);
		quantity_input.readOnly = 0;

		var addLink = BX.findChild(addItem, {class : "cart_item_delayed"}, true, false);
		var addHref = addLink.href;
		addHref = addHref.replace("add", "shelve");
		addLink.href = addHref;
		addLink.onclick = function(){   return DelayInCart(element);}

		BX('id-cart-ul').appendChild(addItem);
		var data='';
		ajaxInCart(element_href, data);
		return false;
	}
	</script>
	<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="basket_form" id="basket_form">
		<?
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php");
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_delay.php");
		/*	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_subscribe.php");
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_notavail.php"); */
		?>
	</form>
<?
/*}
else
{
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php");
	//ShowNote($arResult["ERROR_MESSAGE"]);
} */
?>