<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<table width="100%" height="100%">
	<tr>
		<td align="center" valign="middle">
		<?
		if($arResult['ERROR_MESSAGE'] == '')
		{
			?>
			<form action="<?=$arResult['PATH_TO_DELETE']?>" name="load_form" method="GET">
				<?=bitrix_sessid_post()?>
				<input type="hidden" name="<?=$arResult['PAGE_VAR']?>" value="<?=$arResult['ELEMENT']['ID']?>"/>
				<input type="hidden" name="<?=$arResult['OPER_VAR']?>" value="delete"/>
				<input type="hidden" name="save" value="Y"/>
				<input type="hidden" name="del_dialog" value="Y"/>
				<table>
					<tr>
						<td><?=GetMessage('WIKI_DELETE_PAGE')?></td>
					</tr>
				</table>
			</form>
			<script>
				BX.WindowManager.Get().SetTitle('<?=GetMessage('WIKI_DELETE_CONFIRM')?>');
				var _BTN = [
					{
						'title': "<?=GetMessage('WIKI_BUTTON_DELETE');?>",
						'id': 'wk_delete',
						'action': function () {
							document.forms.load_form.submit();
							BX.WindowManager.Get().Close();
						}
					},
					BX.CDialog.btnCancel
				];

				BX.WindowManager.Get().ClearButtons();
				BX.WindowManager.Get().SetButtons(_BTN);
				BX.WindowManager.Get().adjustSizeEx();
			</script>
		<?
		}
		else
		{

		?>
			<form action="<?=$arResult['LIST_PAGE_URL']?>" name="load_form" method="GET">
				<?=bitrix_sessid_post()?>
				<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
					<tr>
						<td><?=$arResult['ERROR_MESSAGE']?></td>
					</tr>
				</table>
			</form>
			<script>
				BX.WindowManager.Get().SetTitle('<?=GetMessage('WIKI_DELETE_CONFIRM')?>');
				var _BTN = [
					{
						'title': "Ok",
						'id': 'wk_ok',
						'action': function () {
							document.forms.load_form.submit();
							BX.WindowManager.Get().Close();
						}
					}

				];

				BX.WindowManager.Get().ClearButtons();
				BX.WindowManager.Get().SetButtons(_BTN);
			</script>
			<?
		}
		?>
		</td>
	</tr>
</table>
