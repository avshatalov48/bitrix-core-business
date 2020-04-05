<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->SetPageProperty("BodyClass", "detail");

if(!$USER->IsAuthorized() && $arParams["ALLOW_AUTO_REGISTER"] == "N")
{
	if(!empty($arResult["ERROR"]))
	{
		foreach($arResult["ERROR"] as $v)
			echo ShowError($v);
	}
	elseif(!empty($arResult["OK_MESSAGE"]))
	{
		foreach($arResult["OK_MESSAGE"] as $v)
			echo ShowNote($v);
	}

	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/auth.php");
}
else
{
	if($arResult["USER_VALS"]["CONFIRM_ORDER"] == "Y")
	{
		if(strlen($arResult["REDIRECT_URL"]) > 0)
		{
			die();
		}
		else
		{
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/confirm.php");
		}
	}
	else
	{
		?>
	<script type="text/javascript">
		app.setPageTitle({"title" : "<?=GetMessage("SOA_ORDER_TITLE")?>"});
	</script>
	<a name="order_fform"></a>
	<div id="order_form_div" class="order-checkout">
	<NOSCRIPT>
		<div class="errortext"><?=GetMessage("SOA_NO_JS")?></div>
	</NOSCRIPT>

	<script>

		<?if(CSaleLocation::isLocationProEnabled()):?>

			var BXCallAllowed = false;
			function submitFormProxy(item, isFinal)
			{
				if(isFinal && BXCallAllowed){
					BXCallAllowed = false;
					submitForm();
				}
			}

			BX(function(){
				BXCallAllowed = true;
			});
		<?endif?>

		function submitForm(val)
		{
			if(val != 'Y') 
				BX('confirmorder').value = 'N';
			else
				BX('confirmorder').value = 'Y';
			var orderForm = BX('ORDER_FORM');
			
			/*if (val == "Y")
			{
				BX.ajax.submitComponentForm(orderForm, 'order_form_content', true);
				BX.submit(orderForm);
			}*/

			var data_form = {}, form = orderForm;
			for(var i = 0; i< form.elements.length; i++)
			{
				if (form[i].type == "radio")
				{
					if (form[i].checked)
						data_form[form[i].name] = form[i].value;
				}
				else
				{
					data_form[form[i].name] = form[i].value;
				}
			}
			if (val == "Y")
				data_form["ajax_submit_form"] = "Y";

			app.showPopupLoader({test:''});
			BX.ajax({
				timeout:   30,
				method:   'POST',
				url: '<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
				data: data_form,
				processData: false,
				onsuccess: function(reply){
					if (val == "Y")
					{
						try
						{
							var json = JSON.parse(reply);
							if (json.error)
							{
								app.alert(
									{
										text : json.error,
										title : "<?=GetMessage("ALERT_ERROR")?>",
										button:"OK"

									}
								);
							}
							else if (json.redirect)
							{
								app.onCustomEvent('onItemBuy', {});
								if (json.user_name)
									app.onCustomEvent('onAuthSuccess', {"user_name":json.user_name, "id":json.user_id});
								app.loadPage(json.redirect);
							}
						}
						catch(e)
						{
						}
					}
					else
					{
						var reply = BX.processHTML(reply);

						BXCallAllowed = false;

						BX('order_form_content').innerHTML = reply.HTML;
						if(typeof reply.SCRIPT !== 'undefined')
						{
							for(var k in reply.SCRIPT)
								BX.evalGlobal(reply.SCRIPT[k].JS);
						}

						BXCallAllowed = true;
					}
					app.hidePopupLoader();

				},
			});

			return true;
		}
		function SetContact(profileId)
		{
			BX("profile_change").value = "Y";
			submitForm();
			BX("profile_change").value = "N";
		}
	</script>
		<?if($_POST["is_ajax_post"] != "Y")
		{
			?><form action="" method="POST" name="ORDER_FORM" id="ORDER_FORM">
			<?=bitrix_sessid_post()?>
			<div id="order_form_content">
			<?
		}
		else
		{
			$APPLICATION->RestartBuffer();
		}

		if(count($arResult["PERSON_TYPE"]) > 1)
		{
			?>
			<div class="order_item_description">
				<h3><?=GetMessage("SOA_TEMPL_PERSON_TYPE")?></h3>
				<div class="ordering_container">
					<ul>
			<?
			foreach($arResult["PERSON_TYPE"] as $v)
			{
			?>
					<li>
						<div class="ordering_li_container <?if ($v["CHECKED"]=="Y"):?>checked<?endif?>">
							<table>
								<tr>
									<td><span class="inputradio"><input type="radio" id="PERSON_TYPE_<?= $v["ID"] ?>" name="PERSON_TYPE" value="<?= $v["ID"] ?>"<?if ($v["CHECKED"]=="Y") echo " checked=\"checked\"";?> onClick="submitForm()"></span></td>
									<td><label for="PERSON_TYPE_<?= $v["ID"] ?>"><?= $v["NAME"] ?></label></td>
								</tr>
							</table>
						</div>
					</li>
			<?
			}
			?>
				<input type="hidden" name="PERSON_TYPE_OLD" value="<?=$arResult["USER_VALS"]["PERSON_TYPE_ID"]?>">
					</ul>
				</div>
			</div>
			<?
		}
		else
		{
			if(IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"]) > 0)
			{
				?>
				<input type="hidden" name="PERSON_TYPE" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>">
				<input type="hidden" name="PERSON_TYPE_OLD" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>">
				<?
			}
			else
			{
				foreach($arResult["PERSON_TYPE"] as $v)
				{
					?>
					<input type="hidden" id="PERSON_TYPE" name="PERSON_TYPE" value="<?=$v["ID"]?>">
					<input type="hidden" name="PERSON_TYPE_OLD" value="<?=$v["ID"]?>">
					<?
				}
			}
		}

		include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/props.php");

		if ($arParams["DELIVERY_TO_PAYSYSTEM"] == "p2d")
		{
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/paysystem.php");
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/delivery.php");
		}
		else
		{
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/delivery.php");
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/paysystem.php");
		}

		include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/summary.php");
		?>
		<?if($_POST["is_ajax_post"] != "Y")
		{
			?>
				</div>
				<input type="hidden" name="confirmorder" id="confirmorder" value="Y">
				<input type="hidden" name="profile_change" id="profile_change" value="N">
				<input type="hidden" name="is_ajax_post" id="is_ajax_post" value="Y">
				<div class="tac">
					<a class="ordering button_red_big" ontouchstart="BX.toggleClass(this, 'active');" ontouchend="BX.toggleClass(this, 'active');" href="javascript:void(0)" onClick="submitForm('Y');"><?=GetMessage("SOA_TEMPL_BUTTON")?></a>
				<!--<input style="font-size: 15px; line-height: 18px;" class="ordering green_button" type="button" name="submitbutton" onClick="submitForm('Y');" value="<?=GetMessage("SOA_TEMPL_BUTTON")?>">-->
				</div>
			</form>
			<?if($arParams["DELIVERY_NO_AJAX"] == "N"):?>
				<?
				$APPLICATION->AddHeadScript("/bitrix/js/main/cphttprequest.js");
				$APPLICATION->AddHeadScript("/bitrix/components/bitrix/sale.ajax.delivery.calculator/templates/.default/proceed.js");
				?>
			<?endif;?>
			<?
		}
		else
		{
			?>
			<script>
				top.BX('confirmorder').value = 'Y';
				top.BX('profile_change').value = 'N';
			</script>
			<?
			die();
		}
	}
}
?>
</div>