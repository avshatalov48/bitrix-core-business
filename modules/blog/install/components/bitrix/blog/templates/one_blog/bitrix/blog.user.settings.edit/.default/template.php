<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if(strlen($arResult["FATAL_ERROR"])>0)
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<ul><?=$arResult["FATAL_ERROR"]?></ul>
		</div>
	</div>
	<?
}
else
{
	if(strlen($arResult["ERROR_MESSAGE"])>0)
	{
		?>
		<div class="blog-errors">
			<div class="blog-error-text">
				<ul><?=$arResult["ERROR_MESSAGE"]?></ul>
			</div>
		</div>
		<?
	}

	?>
	<form action="<?=POST_FORM_ACTION_URI?>" method="post">
	<table class="blog-table-header-left">
		<tr>
			<th><?=GetMessage("B_B_USE_USER")?></th>
			<td><a href="<?=$arResult["urlToUser"]?>"><?=$arResult["userName"]?></a></td>
		</tr>
		<tr>
			<th><?=GetMessage("B_B_USE_U_GROUPS")?></th>
			<td><?
					if(!empty($arResult["Groups"]))
					{
					foreach($arResult["Groups"] as $arBlogGroups)
					{
						?>
						<input type="checkbox" id="add2groups_<?= $arBlogGroups["ID"] ?>" name="add2groups[]" value="<?= $arBlogGroups["ID"] ?>"<?if (in_array($arBlogGroups["ID"], $arResult["arUserGroups"])) echo " checked";?>>
						<label for="add2groups_<?= $arBlogGroups["ID"] ?>"><?=$arBlogGroups["NAME"]?></label><br />
						<?
					}
					}
					?>
			</td>
		</tr>
	</table>
	<div class="blog-buttons">
		<input type="submit" value="<?=GetMessage("B_B_USE_SAVE")?>">
		<input type="submit" name="cancel" value="<?=GetMessage("B_B_USE_CANCEL")?>">
		<input type="hidden" name="user_action" value="Y">
		<?=bitrix_sessid_post()?>
	</div>
	</form>
	<?
}
?>