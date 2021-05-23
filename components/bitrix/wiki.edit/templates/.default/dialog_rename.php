<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<table width="100%" height="100%">
	<tr>
		<td align="center" valign="middle">
			<?
			if($arResult['ERROR_MESSAGE'] == '')
			{
				$sCatName = '';
				if (CWikiUtils::IsCategoryPage($arResult['ELEMENT']['NAME'] , $sCatName))
					$catLocalName = CWikiUtils::UnlocalizeCategoryName($sPageName);
				?>
				<form action="<?=$arResult['PATH_TO_POST_EDIT']?>" name="rename_form" method="POST">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="<?=$arResult['PAGE_VAR']?>" value="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME_LOCALIZE'])?>"/>
					<input type="hidden" name="<?=$arResult['OPER_VAR']?>" value="rename_it"/>
					<input type="hidden" name="save" value="Y"/>
					<table>
						<tr>
							<td><?=GetMessage('WIKI_DIALOG_RENAME_PAGE_NAME').": "?></td>
							<td><input type="text" name="NEW_NAME" value="<? echo ($sCatName ? $sCatName : htmlspecialcharsbx($arResult['ELEMENT']['NAME_LOCALIZE']))?>"></td>
						</tr>
				</form>
				<script type="text/javascript">
					BX.WindowManager.Get().SetTitle('<?=GetMessage("WIKI_DIALOG_RENAME_TITLE")?>');
					var _BTN = [
						{
							'title': '<?=GetMessage("WIKI_DIALOG_RENAME_BUT_RENAME")?>',
							'id': 'wk_rename',
							'action': function () {
								document.forms.rename_form.submit();
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
				<form action="<?=$arResult['LIST_PAGE_URL']?>" name="rename_form" method="GET">
					<?=bitrix_sessid_post()?>
					<table cellspacing="0" cellpadding="0" border="0"  class="bx-width100">
						<tr>
							<td><?=$arResult['ERROR_MESSAGE']?></td>
						</tr>
				</form>
				<script type="text/javascript">
					BX.WindowManager.Get().SetTitle('<?=GetMessage("WIKI_DIALOG_RENAME_ERROR")?>');
					var _BTN = [
						{
							'title': "Ok",
							'id': 'wk_ok',
							'action': function () {
								document.forms.rename_form.submit();
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
