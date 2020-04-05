<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CUser $USER
 */

CJSCore::Init(array("popup", "ajax"));

ShowMessage($arResult["MESSAGE"]);

if($USER->IsAuthorized()):
?>

<script type="text/javascript">
var bx_app_pass_mess = {
	deleteButton: '<?=CUtil::JSEscape(GetMessage("main_app_pass_del"))?>',
	templatePath: '<?=CUtil::JSEscape($this->GetFolder())?>'
};
</script>

<div class="bx-otp-wrap-container">
	<h3 class="bx-otp-wrap-container-title"><?echo GetMessage("main_app_pass_title")?></h3>
	<p class="bx-otp-wrap-container-description">
		<?echo GetMessage("main_app_pass_text1")?>
	</p>
	<p class="bx-otp-wrap-container-description">
		<?echo GetMessage("main_app_pass_text2")?>
	</p>

	<div class="bx-otp-section-white">

	<?
	foreach($arResult["APPLICATIONS"] as $app_id => $app):
		if(isset($app["VISIBLE"]) && $app["VISIBLE"] === false)
		{
			continue;
		}
	?>
		<div class="bx-otp-accordion-container <?=(!empty($arResult["ROWS"][$app_id])? "open" : "close")?>" id="bx_app_pass_container_<?=$app_id?>">
			<div class="bx-otp-accordion-head-block" onclick="return bx_app_pass_toggle('bx_app_pass_container_<?=$app_id?>')">
				<div class="bx-otp-accordion-head-title"><?=HtmlFilter::encode($app["NAME"])?></div>
				<div class="bx-otp-accordion-head-description"><?=$app["DESCRIPTION"]?></div>
				<div class="bx-otp-accordion-action"></div>
			</div>
			<div class="bx-otp-accordion-content-block">
				<table class="bx-otp-access-table" id="bx_app_pass_table_<?=$app_id?>">
					<?if(!empty($arResult["ROWS"][$app_id])):?>
					<thead>
						<tr>
							<td style="width:100%"></td>
							<td><?echo GetMessage("main_app_pass_created")?></td>
							<td><?echo GetMessage("main_app_pass_last")?></td>
							<td><?echo GetMessage("main_app_pass_last_ip")?></td>
							<td><?echo GetMessage("main_app_pass_manage")?></td>
						</tr>
					</thead>
					<?endif?>
					<tbody>
					<?
					if(is_array($arResult["ROWS"][$app_id])):
						foreach($arResult["ROWS"][$app_id] as $pass):
					?>
						<tr id="bx_app_pass_row_<?=$pass["ID"]?>">
							<td class="bx-otp-access-table-param">
								<?=HtmlFilter::encode($pass["SYSCOMMENT"])?>
								<small><?=HtmlFilter::encode($pass["COMMENT"])?></small>
							</td>
							<td class="bx-otp-access-table-value">
								<?=$pass["DATE_CREATE"]?>
							</td>

							<td class="bx-otp-access-table-value">
								<?=$pass["DATE_LOGIN"]?>
							</td>
							<td class="bx-otp-access-table-value">
								<?=$pass["LAST_IP"]?>
							</td>
							<td class="bx-otp-access-table-action">
								<a class="bx-otp-btn big lightgray mb0" href="javascript:void(0);" onclick="bx_app_pass_show_delete_window(<?=$pass["ID"]?>)"><?echo GetMessage("main_app_pass_del")?></a>
							</td>
						</tr>
					<?
						endforeach;
					endif;
					?>
						<tr>
							<td class="bx-otp-access-table-param" colspan="3">
								<form id="bx_app_pass_form_<?=$app_id?>">
									<table>
										<thead>
											<tr>
												<td class="tal" style="padding: 0 30px 0 0;"><small class="fwn ttn m0"><?=($app["OPTIONS_CAPTION"] <> ''? HtmlFilter::encode($app["OPTIONS_CAPTION"]) : GetMessage("main_app_pass_link"))?></small></td>
												<td class="tal" style="padding: 0;"><small class="fwn ttn m0"><?echo GetMessage("main_app_pass_comment")?></small></td>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="tal" style="padding: 0 30px 0 0;">
													<select name="SYSCOMMENT" id="" class="bx-otp-slt medium">
													<?if(!empty($app["OPTIONS"]) && is_array($app["OPTIONS"])):?>
														<?foreach($app["OPTIONS"] as $opt):?>
														<option value="<?=HtmlFilter::encode($opt)?>"><?=HtmlFilter::encode($opt)?></option>
														<?endforeach?>
														<option value="<?echo GetMessage("main_app_pass_other")?>"><?echo GetMessage("main_app_pass_other")?></option>
													<?else:?>
														<option value="<?=HtmlFilter::encode($app["NAME"])?>"><?=HtmlFilter::encode($app["NAME"])?></option>
													<?endif?>
													</select>
												</td>
												<td class="tal" style="padding: 0;">
													<input type="text" name="COMMENT" class="bx-otp-slt medium m0" placeholder="<?echo GetMessage("main_app_pass_comment_ph")?>">
												</td>
											</tr>
										</tbody>
									</table>
									<input type="hidden" name="APPLICATION_ID" value="<?=$app_id?>">
								</form>
							</td>
							<td class="bx-otp-access-table-value" colspan="2">
								<a class="bx-otp-btn big green mb0" href="javascript:void(0);" onclick="bx_app_pass_show_create_window('bx_app_pass_form_<?=$app_id?>')"><?echo GetMessage("main_app_pass_get_pass")?></a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	<?
	endforeach;
	?>

	</div>
</div>

<div id="bx_app_pass_new_password" class="modal" style="margin: 0 auto;display: none; background: #fff;padding: 10px; max-width:500px;">
	<div class="bx-otp-popup-container">
		<div class="bx-otp-popup-content-title"><?echo GetMessage("main_app_pass_create_pass")?></div>
			<div class="bx-otp-popup-lottery-container">

			<p><?echo GetMessage("main_app_pass_create_pass_text")?> </p>
			<div class="bx-otp-popup-lottery bx-otp-popup-lottery-black" id="bx_app_pass_lottery">
				<span id="bx_app_pass_password"></span>
			</div>
		</div>
		<div class="bx-otp-popup-buttons">
			<a class="bx-otp-btn big lightgray" href="javascript:void(0);" onclick="BX.PopupWindowManager.getCurrentPopup().close();" id="bx_app_pass_close_button"><?echo GetMessage("main_app_pass_create_pass_close")?></a>
		</div>
	</div>
</div>

<div id="bx_app_pass_delete_password" class="modal" style="margin: 0 auto;display: none; background: #fff;padding: 10px; max-width:600px;">
	<div class="bx-otp-popup-container">
		<div class="bx-otp-popup-remove-container">
			<div class="bx-otp-popup-remove-title"><?echo GetMessage("main_app_pass_del_pass")?></div>
			<p class="tac"><?echo GetMessage("main_app_pass_del_pass_text")?></p>
		</div>

		<div class="bx-otp-popup-buttons">
			<a class="bx-otp-btn big red" href="javascript:void(0);" id="bx_app_pass_del_button"><?echo GetMessage("main_app_pass_del")?></a>
			<a class="bx-otp-btn big transparent" href="javascript:void(0);" onclick="BX.PopupWindowManager.getCurrentPopup().close();"><?echo GetMessage("main_app_pass_cancel")?></a>
		</div>
	</div>
</div>

<?endif?>