<?
/*
 * Status dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

?>
<form id="status_form">
	<div class="wrap">
		<div class="order_status_component">
			<div class="order_status_title"><?=GetMessage('SMOD_STATUS');?></div>
			<div class="order_status_infoblock">
				<ul>
					<?foreach ($arResult["STATUSES"] as $status):	?>
						<li>
							<div id="r_container_<?=$status["ID"]?>" title="<?=$status["ID"]?>" class="order_status_li_container <?=($status['ID'] == $arResult["ORDER"]['STATUS_ID'] ? ' checked' : '')?>" onclick="onClickRadio(this);">
								<table>
									<tr>
										<td><span class="inputradio"><input type="radio" id="r_<?=$status["ID"]?>"></span></td>
										<td><label for="r_<?=$status["ID"]?>"><span><?=$status["NAME"]?></span></label></td>
									</tr>
								</table>
							</div>
						</li>
					<?endforeach;?>
				</ul>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">
app.addButtons({
	cancelButton:
	{
		type: "back_text",
		style: "custom",
		position:"left",
		name: "<?=GetMessage('SMOD_BACK');?>",
		callback: function()
		{
			app.closeModalDialog();
		}
	},
	saveButton:
	{
		type: "right_text",
		style: "custom",
		name: "<?=GetMessage('SMOD_SAVE');?>",
		callback: function()
		{
			statusSave();
		}
	}
});

getRadioContainers = function()
{
	return BX.findChildren(BX("status_form"), {className: "order_status_li_container"}, true);
}

onClickRadio = function(divDomObj)
{
	var checkedId = getChecked();

	if(checkedId == divDomObj.id)
		return;

	resetChecked();

	BX.addClass(divDomObj,'checked');
};

resetChecked = function()
{
	for(var i in rContainers)
		if(BX.hasClass(rContainers[i],"checked"))
			BX.removeClass(rContainers[i],"checked");
}

getChecked = function()
{
	rContainers = getRadioContainers();

	for(var i in rContainers)
		if(BX.hasClass(rContainers[i],"checked"))
			return rContainers[i]["id"];

	return false;
};

statusSave = function()
{
	var statusId = BX(getChecked()).title,
		id = <?=$arResult['ORDER']['ID']?>;

	postData = {
		action: 'status_save',
		id: id,
		status_id: statusId
	};

	//app.showPopupLoader({"text":"saving status"});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'html',
		url:       '<?=$componentPath?>/ajax.php',
		data:      postData,
		onsuccess: function(result) {
			//app.hidePopupLoader();
			app.closeModalDialog();
			if(result)
			{
				app.onCustomEvent('onAfterOrderChange', {"id" : id});
			}
			else
			{
				//alert("statusSave !result"); //develop
			}
		},
		onfailure: function(){
			//alert("statusSave failure"); //develop
		}
	});
};
</script>