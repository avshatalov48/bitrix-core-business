<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if($arResult["MESSAGE"] <> '')
{
	?>
	<?=$arResult["MESSAGE"]?><br /><br />
	<?
}
if($arResult["ERROR_MESSAGE"] <> '')
{
	?>
	<span class='errortext'><?=$arResult["ERROR_MESSAGE"]?></span><br /><br />
	<?
}
if($arResult["FATAL_MESSAGE"] <> '')
{
	?>
	<span class='errortext'><?=$arResult["FATAL_MESSAGE"]?></span><br /><br />
	<?
}
elseif($arResult["NOTE_MESSAGE"] <> '')
{
	?>
	<?=$arResult["NOTE_MESSAGE"]?><br /><br />
	<?
}
else
{
	if(!empty($arResult["Post"])>0)
	{
		?>
		<table class="blog-table-post">
		<tr>
			<th nowrap width="100%">
				<table width="100%" cellspacing="0" cellpadding="0" border="0" class="blog-table-post-table">
				<tr>
					<td align="left">
						<span class="blog-post-date"><b><?=$arResult["Post"]["DATE_PUBLISH_FORMATED"]?></b></span>&nbsp;
					</td>
					<td align="right" nowrap>
						<table width="0%" class="blog-table-post-table-author">
						<tr>
							<?if($arResult["urlToEdit"] <> ''):?>
								<td align="right">
									<a href="<?=$arResult["urlToEdit"]?>" class="blog-post-edit"></a>
								</td>
							<?endif;?>
							<?if($arResult["urlToDelete"] <> ''):?>
								<td align="right">
									<a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$arResult["urlToDelete"]."&".bitrix_sessid_get()?>'" class="blog-post-delete"></a>
								</td>
							<?endif;?>
						</tr>
						</table>
				</tr>
				</table>
			</th>
		</tr>
		<tr>
			<td>
				<?=$arResult["BlogUser"]["AVATAR_img"]?>
				<?=$arResult["Post"]["textFormated"]?>
				<br clear="all" />
				<?if($arResult["POST_PROPERTIES"]["SHOW"] == "Y"):?>
					<br />
					<table cellpadding="0" cellspacing="0" border="0" class="blog-table-post-table" style="width:0%;">
					<?foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
					<?if($arPostField["VALUE"] <> ''):?>
					<tr>
						<td><b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b></td>
						<td>

								<?$APPLICATION->IncludeComponent(
									"bitrix:system.field.view", 
									$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
									array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
						</td>
					</tr>			
					<?endif;?>
					<?endforeach;?>
					</table>
				<?endif;?>

		<?if(!empty($arResult["Category"]))
		{
			?>
			<div class="blog-line"></div>
			<?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?>
			<?$i=0;
			foreach($arResult["Category"] as $v)
			{
				if($i!=0)
					echo ",";
				?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
				$i++;
			}
		}
		?>
			</td>
		</tr>
		</table>
		<br />
		<?
	}
	else
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
}
?>