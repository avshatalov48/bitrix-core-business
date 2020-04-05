<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if ($arResult["STATUS"] == "Y"):?>
	<span id="url_notify_<?=$arParams['NOTIFY_ID']?>"><?=GetMessage('MAIN_NOTIFY_MESSAGE')?></span>
<?elseif ($arResult["STATUS"] == "N"):?>
	<span id="url_notify_<?=$arParams['NOTIFY_ID']?>"><a href="javascript:void(0)" onClick="notifyProduct('<?=$arResult["NOTIFY_URL"]?>', <?=$arParams['NOTIFY_ID']?>);"><?=GetMessage('NOTIFY');?></a></span>
<?elseif ($arResult["STATUS"] == "R"):?>
	<span id="url_notify_<?=$arParams['NOTIFY_ID']?>">
		<a href="javascript:void(0)" onClick="showNotify(<?=$arParams['NOTIFY_ID']?>)" id="notify_product_<?=$arParams['NOTIFY_ID']?>"><?=GetMessage('NOTIFY');?></a>
	</span>
<?endif;?>
<input type="hidden" value="<?=$arResult["NOTIFY_URL"]?>" name="notify_url_<?=$arParams['NOTIFY_ID']?>" id="notify_url_<?=$arParams['NOTIFY_ID']?>">

<?
if (!defined("EXIST_FORM")):
	define("EXIST_FORM", "Y");
?>

<div id="popup_form_notify" style="display: none;">
	<input type="hidden" value="" name="popup_notify_url" id="popup_notify_url">
	<table border="0" cellpadding="4" cellspacing="0" width="300">
		<tr>
			<td colspan="2"><?=GetMessage('NOTIFY_TITLE');?></td>
		</tr>
		<tr>
			<td colspan="2"><div id="popup_n_error" style="color:red;"></div></td>
		</tr>
		<tr>
			<td valign="top">
				<div id="popup-buyer-title-mail">
					<?=GetMessage('NOTIFY_POPUP_MAIL');?>
				</div><br>
				<div id="popup-buyer-title-auth">
					<a href="javascript:void(0)" onClick="showAuth('auth');"><?=GetMessage('NOTIFY_POPUP_AUTH');?></a>
				</div>
			</td>

			<td valign="top">
				<input type="text" value="" name="popup_user_email" id="popup_user_email" size="25">

				<table width="100%" style="display: none;" id="popup-buyer-auth-form">
					<tr>
						<td>
							<?=GetMessage('NOTIFY_POPUP_LOGIN');?>:<br />
							<input type="text" name="notify_user_login" id="notify_user_login" maxlength="50" value="" size="25" />
						</td>
					</tr>
					<tr>
						<td>
							<?=GetMessage('NOTIFY_POPUP_PASSW');?>:<br />
							<input type="password" name="notify_user_password" id="notify_user_password" maxlength="50" size="25" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="notify_user_auth" id="notify_user_auth" value="N" >
			</td>
		</tr>
		<?if($arResult["CAPTCHA_CODE"]):?>
			<tr>
				<td></td>
				<td><input type="hidden" name="popup_captcha_sid" id="popup_captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
				<span id="popup_captcha_img">
					<img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></td>
				</span>
			</tr>
			<tr>
				<td class="bx-auth-label"><?=GetMessage('NOTIFY_POPUP_CAPTHA');?></td>
				<td><input class="bx-auth-input" type="text" name="popup_captcha_word" id="popup_captcha_word" maxlength="50" value="" size="15" /></td>
			</tr>
		<?endif;?>
	</table>
</div>

<script>
var wind = new BX.PopupWindow('popup_notify', BX('popup_form_notify'), {
	lightShadow : true,
	offsetTop: 10,
	offsetLeft: 0,
	autoHide: true,
	closeByEsc: true,
	zIndex: -910,
	bindOptions: {position: "right"},
	buttons: [
		new BX.PopupWindowButton({
			text : '<?=GetMessage("NOTIFY_POPUP_OK");?>',
			events : {
				click : function() {
					var error = 'N';
					var useCaptha = 'N';
					BX('popup_n_error').innerHTML = '';

					var sessid = '';
					if (BX('sessid'))
						sessid = BX('sessid').value;
					var data = "sessid="+sessid+'&ajax=Y';

					if (BX('popup_user_email').value.length == 0 && BX('notify_user_login').value.length == 0 && BX('notify_user_password').value.length == 0)
					{
						BX('popup_n_error').innerHTML = '<?=GetMessageJS("NOTIFY_ERR_NULL");?>';
						error = "Y";
					}

					data = data + '&user_auth=Y&user_login='+BX('notify_user_login').value+'&user_password='+BX('notify_user_password').value;

					var reg = /@/i;
					if(BX('popup_user_email').value.length > 0 && !reg.test(BX('popup_user_email').value))
					{
						BX('popup_n_error').innerHTML = '<?=GetMessageJS("NOTIFY_POPUP_MAIL_ERR");?>';
						error = "Y";
					}
					else
					{
						data = data + '&user_mail=' + BX.util.urlencode(BX('popup_user_email').value);

						if (BX('popup_captcha_sid') && BX('popup_captcha_word'))
						{
							data = data + '&captcha_sid='+BX('popup_captcha_sid').value;
							data = data + '&captcha_word='+BX('popup_captcha_word').value;
							useCaptha = 'Y';
						}
					}

					if (error == 'N')
					{
						BX.showWait();

						BX.ajax.post('/bitrix/components/bitrix/sale.notice.product/ajax.php', data, function(res) {
							BX.closeWait();

							var rs = eval( '('+res+')' );

							if (rs['ERRORS'].length > 0)
							{
								if (rs['ERRORS'] == 'NOTIFY_ERR_NULL')
									BX('popup_n_error').innerHTML = '<?=GetMessageJS('NOTIFY_ERR_NULL')?>';
								else if (rs['ERRORS'] == 'NOTIFY_ERR_CAPTHA')
									BX('popup_n_error').innerHTML = '<?=GetMessageJS('NOTIFY_ERR_CAPTHA')?>';
								else if (rs['ERRORS'] == 'NOTIFY_ERR_MAIL_EXIST')
								{
									BX('popup_n_error').innerHTML = '<?=GetMessageJS('NOTIFY_ERR_MAIL_BUYERS_EXIST')?>';
									showAuth();
									BX('popup_user_email').value = '';
									BX('notify_user_login').focus();
								}
								else if (rs['ERRORS'] == 'NOTIFY_ERR_REG')
									BX('popup_n_error').innerHTML = '<?=GetMessageJS('NOTIFY_ERR_REG')?>';
								else
									BX('popup_n_error').innerHTML = rs['ERRORS'];

								if (useCaptha == 'Y')
								{
									BX.ajax.get('/bitrix/components/bitrix/sale.notice.product/ajax.php?reloadcaptha=Y', '', function(res) {
										BX('popup_captcha_sid').value = res;
										BX('popup_captcha_img').innerHTML = '<img src="/bitrix/tools/captcha.php?captcha_sid='+res+'" width="180" height="40" alt="CAPTCHA" />';
									});
								}
							}
							else if (rs['STATUS'] == 'Y')
							{
								notifyProduct(BX('popup_notify_url').value, '<?=$arParams['NOTIFY_ID']?>');
								wind.close();
							}
						});
					}
				}
			}
		}),
		new BX.PopupWindowButton({
			text : '<?=GetMessage('NOTIFY_POPUP_CANCEL')?>',
			events : {
				click : function() {
					wind.close();
				}
			}
		})
	]
});
wind.setContent(BX('popup_form_notify'));

function showNotify(id)
{
	wind.setBindElement(BX('notify_product_'+id));
	wind.show();

	BX('popup_notify_url').value = BX('notify_url_'+id).value;
	BX('popup_user_email').focus();
}

function notifyProduct(url, id)
{
	BX.showWait();

	BX.ajax.post(url, '', function(res) {
		BX.closeWait();
		document.body.innerHTML = res;
		if (BX('url_notify_'+id))
			BX('url_notify_'+id).innerHTML = '<?=GetMessage("MAIN_NOTIFY_MESSAGE");?>';
	});
}

function showAuth(type)
{
	if (type == 'auth')
	{
		BX('popup-buyer-auth-form').style["display"] = "block";
		BX('popup-buyer-title-auth').innerHTML = '<?=GetMessageJS('MAIN_NOTIFY_POPUP_AUTH');?>';
		BX('popup-buyer-title-mail').innerHTML = '<a href="javascript:void(0)" onClick="showAuth(\'mail\');"><?=GetMessageJS('MAIN_NOTIFY_POPUP_MAIL');?></a>';
		BX('popup_user_email').style["display"] = "none";
		BX('popup_user_email').value = '';
	}
	else
	{
		BX('popup-buyer-auth-form').style["display"] = "none";
		BX('popup-buyer-title-auth').innerHTML = '<a href="javascript:void(0)" onClick="showAuth(\'auth\');"><?=GetMessageJS('MAIN_NOTIFY_POPUP_AUTH');?></a>';
		BX('popup-buyer-title-mail').innerHTML = '<?=GetMessageJS('MAIN_NOTIFY_POPUP_MAIL');?>';
		BX('popup_user_email').style["display"] = "block";
		BX('notify_user_login').value = '';
		BX('notify_user_password').value = '';
	}
}
</script>
<?endif;?>