<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	foreach($arResult["OK_MESSAGE"] as $v)
	{
		?>
		<span class='notetext'><?=$v?></span><br /><br />
		<?
	}
}
if(!empty($arResult["MESSAGE"]))
{
	foreach($arResult["MESSAGE"] as $v)
	{
		?>
		<?=$v?><br /><br />
		<?
	}
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
	{
		?>
		<span class='errortext'><?=$v?></span><br /><br />
		<?
	}
}
if(is_array($arResult["POST"]) && count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $CurPost)
	{
		?>
		<table class="blog-table-post">
		<tr>
			<th nowrap width="100%">
				<table class="blog-table-post-table">
				<tr>
					<td width="100%" align="left">
						<span class="blog-post-date"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></span><br />
						<span class="blog-author"><b><a href="<?=$CurPost["urlToPost"]?>"><?=$CurPost["TITLE"]?></a></b></span>
					</td>
					<?if($CurPost["urlToEdit"] <> ''):?>
						<td>
							<a href="<?=$CurPost["urlToEdit"]?>" class="blog-post-edit"></a>
						</td>
					<?endif;?>
					<?if($CurPost["urlToDelete"] <> ''):?>
						<td>
							<a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToDelete"]."&".bitrix_sessid_get()?>'" class="blog-post-delete"></a>
						</td>
					<?endif;?>
				</tr>
				</table>
			</th>
		</tr>
		<tr>
			<td>
				<span class="blog-text">
				<?
				if(array_key_exists("USE_SHARE", $arParams) && $arParams["USE_SHARE"] == "Y")
				{
					?>
					<div class="blog-post-share" style="float: right;">
						<noindex>
						<?
						$APPLICATION->IncludeComponent("bitrix:main.share", "", array(
								"HANDLERS" => $arParams["SHARE_HANDLERS"],
								"PAGE_URL" => htmlspecialcharsback($CurPost["urlToPost"]),
								"PAGE_TITLE" => htmlspecialcharsback($CurPost["TITLE"]),
								"SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
								"SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
								"ALIGN" => "right",
								"HIDE" => $arParams["SHARE_HIDE"],
							),
							$component,
							array("HIDE_ICONS" => "Y")
						);
						?>
						</noindex>
					</div>
					<?
				}
				?>					
				<?=$CurPost["TEXT_FORMATED"]?></span><?
				if ($CurPost["CUT"] == "Y")
				{
					?><br /><br /><div align="left" class="blog-post-date"><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></div><?
				}
				?>
				<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):?>
					<br /><br />
					<table cellpadding="0" cellspacing="0" border="0" class="blog-table-post-table" style="width:0%;">
					<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
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
				<table width="100%" cellspacing="0" cellpadding="0" border="0" class="blog-table-post-table">
				<tr>
					<td colspan="2"><div class="blog-line"></div></td>
				</tr>
				<tr>
					<td align="left">						
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?></td>
					<td align="right" nowrap><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_PERMALINK")?></a>&nbsp;|&nbsp;
					<?if($arResult["enable_trackback"] == "Y" && $CurPost["ENABLE_TRACKBACK"]=="Y"):?>
						<a href="<?=$CurPost["urlToPost"]?>#trackback">Trackbacks: <?=$CurPost["NUM_TRACKBACKS"];?></a>&nbsp;|&nbsp;
					<?endif;?>
					<a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?> <?=intval($CurPost["VIEWS"]);?></a>&nbsp;|&nbsp;
					<a href="<?=$CurPost["urlToPost"]?>#comments"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?> <?=$CurPost["NUM_COMMENTS"];?></a></td>

				</tr>
				</table>
			</td>
		</tr>
		</table>
		<br />
		<?
	}
	if($arResult["NAV_STRING"] <> '')
		echo $arResult["NAV_STRING"];
}
elseif(!empty($arResult["BLOG"]))
	echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
?>	