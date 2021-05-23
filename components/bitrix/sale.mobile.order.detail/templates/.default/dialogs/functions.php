<?
/*
 * Usefull dialogs functions
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

function printStatusesListHtml($arStatuses, $currentStatusId, $showTitle = true)
{
	?>
	<form id="status_form">
			<div class="order_status_component">
				<div class="order_status_infoblock">
					<?if($showTitle):?>
						<div class="order_acceptpay_infoblock_title"><?=GetMessage('SMOD_STATUS');?></div>
					<?endif;?>
					<ul>
						<?foreach ($arStatuses as $status):?>
							<li>
								<div id="r_container_<?=$status["ID"]?>" title="<?=$status["ID"]?>" class="order_status_li_container <?=($status['ID'] == $currentStatusId ? ' checked' : '')?>" onclick="MAorderStatusControl._onStatusClick(this);">
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
	</form>

	<script type="text/javascript">

		MAorderStatusControl = {

			_getRadioContainers: function()
			{
				return BX.findChildren(BX("status_form"), {className: "order_status_li_container"}, true);
			},

			_onStatusClick: function(divDomObj)
			{
				var checkedId = MAorderStatusControl.getSelectedStatus();

				if(checkedId == divDomObj.id)
					return;

				MAorderStatusControl._resetSelectedStatus();

				BX.addClass(divDomObj,'checked');
			},

			_resetSelectedStatus: function()
			{
				for(var i in rContainers)
					if(BX.hasClass(rContainers[i],"checked"))
						BX.removeClass(rContainers[i],"checked");
			},

			getSelectedStatus: function()
			{
				rContainers = MAorderStatusControl._getRadioContainers();

				for(var i in rContainers)
					if(BX.hasClass(rContainers[i],"checked"))
						return rContainers[i].title;

				return false;
			}
		};
	</script>
	<?
}
?>