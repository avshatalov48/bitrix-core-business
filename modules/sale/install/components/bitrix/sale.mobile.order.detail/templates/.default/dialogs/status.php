<?
/*
 * Status dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

unset($arResult["DAILOG"]["STATUSES"]["TITLE"]);
?>
<form id="status_form">
<div class="order_status_component">
	<div class="order_status_title"><?=GetMessage('SMOD_STATUS');?></div>
	<?
		$APPLICATION->IncludeComponent(
			'bitrix:mobileapp.interface.radiobuttons',
			'.default',
			$arResult["DAILOG"]["STATUSES"],
			false
		);
	?>
</div>
</form>

<script type="text/javascript">

	app.setPageTitle({title: "<?=GetMessage('SMOD_D_STATUS')?>"});

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

	statusSave = function()
	{
		var statusId = false,
			id = <?=$arResult['ORDER']['ID']?>,
			form = BX("status_form");

		//get selected status
		for(var s=0, sl=form.elements["<?=$arResult["DAILOG"]["STATUSES"]["RADIO_NAME"]?>"].length; s<sl; s++)
		{
			var el = form.elements["<?=$arResult["DAILOG"]["STATUSES"]["RADIO_NAME"]?>"][s];

			if(el.checked)
			{
				statusId = el.value;
				break;
			}
		}


		postData = {
			action: 'status_save',
			id: id,
			status_id: statusId,
			sessid: BX.bitrix_sessid()
		};

		BX.ajax({
			timeout:   30,
			method:   'POST',
			dataType: 'html',
			url:       '<?=$componentPath?>/ajax.php',
			data:      postData,
			onsuccess: function(result) {
				app.closeModalDialog();
				if(result)
				{
					app.onCustomEvent('onAfterOrderChange', {"id" : id});
				}
			}
		});
	};
</script>