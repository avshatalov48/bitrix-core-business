<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?CUtil::InitJSCore(array("image"));?>
<div id="blog-draft-content">
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	?>
	<div class="blog-notes">
		<div class="blog-note-text">
			<ul>
				<?
				foreach($arResult["OK_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["MESSAGE"]))
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<ul>
				<?
				foreach($arResult["MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<ul>
				<?
				foreach($arResult["ERROR_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if($arResult["FATAL_ERROR"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<ul>
				<?=$arResult["FATAL_ERROR"]?>
			</ul>
		</div>
	</div>
	<?
}
elseif(is_array($arResult["POST"]) && count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $ind => $CurPost)
	{
		$className = "blog-post";
		if($ind == 0)
			$className .= " blog-post-first";
		elseif(($ind+1) == count($arResult["POST"]))
			$className .= " blog-post-last";
		if($ind%2 == 0)
			$className .= " blog-post-alt";
		$className .= " blog-post-year-".$CurPost["DATE_PUBLISH_Y"];
		$className .= " blog-post-month-".intval($CurPost["DATE_PUBLISH_M"]);
		$className .= " blog-post-day-".intval($CurPost["DATE_PUBLISH_D"]);
		?>
			<script>
			BX.viewImageBind(
				'blg-post-<?=$CurPost["ID"]?>',
				{showTitle: false}, 
				{tag:'IMG', attr: 'data-bx-image'}
			);
			</script>
			<div class="<?=$className?>" id="blg-post-<?=$CurPost["ID"]?>">
				<h2 class="blog-post-title"><a href="<?=$CurPost["urlToEdit"]?>"><?=$CurPost["TITLE"]?></a></h2>
				<div class="blog-post-info-back blog-post-info-top">
					<div class="blog-post-info">
						<div class="blog-post-date"><span class="blog-post-day"><?=$CurPost["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$CurPost["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></span></div>
					</div>
				</div>
				<div class="blog-post-content">
					<?=$CurPost["TEXT_FORMATED"]?>
					<?if(!empty($CurPost["arImages"]))
					{
						?>
						<div class="feed-com-files">
							<div class="feed-com-files-title"><?=GetMessage("BLOG_PHOTO")?></div>
							<div class="feed-com-files-cont">
								<?
								foreach($CurPost["arImages"] as $val)
								{
									?><span class="feed-com-files-photo"><img src="<?=$val["small"]?>" alt="" border="0" data-bx-image="<?=$val["full"]?>"></span><?
								}
								?>
							</div>
						</div>
						<?
					}?>
					<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):
						$eventHandlerID = false;
						$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
						?>
						<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
						<?if(!empty($arPostField["VALUE"])):?>
						<div>
						<?=($FIELD_NAME=='UF_BLOG_POST_DOC' ? "" : "<b>".$arPostField["EDIT_FORM_LABEL"].":</b>&nbsp;")?>
							<?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view", 
								$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
								array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
						</div>
						<?endif;?>
						<?endforeach;?>
						<?
						if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
							RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					endif;?>
				</div>
				
				<div class="blog-post-meta">
					<div class="blog-post-info-bottom">
						<div class="blog-post-info">
							<div class="blog-post-date"><span class="blog-post-day"><?=$CurPost["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$CurPost["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></span></div>
						</div>
					</div>
					<div class="blog-post-meta-util">
						<span class="blog-post-views-link"><a href="<?=$CurPost["urlToEdit"]?>"><span class="blog-post-link-caption"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?></span><span class="blog-post-link-counter"><?=intval($CurPost["VIEWS"]);?></span></a></span>
						<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToEdit"]?>#comments"><span class="blog-post-link-caption"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?></span><span class="blog-post-link-counter"><?=intval($CurPost["NUM_COMMENTS"]);?></span></a></span>
						<span class="blog-post-publish-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_SHOW_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToShow"]?>'"><span class="blog-post-link-caption"><?=GetMessage("BLOG_MES_SHOW")?></span></a></span>
						<span class="blog-post-edit-link"><a href="<?=$CurPost["urlToEdit"]?>"><span class="blog-post-link-caption"><?=GetMessage("BLOG_MES_EDIT")?></span></a></span>
						<?if($CurPost["urlToDelete"] <> ''):?>
							<span class="blog-post-delete-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToDelete"]?>'"><span class="blog-post-link-caption"><?=GetMessage("BLOG_MES_DELETE")?></span></a></span>
						<?endif;?>
					</div>

					<div class="blog-post-tag">
						<noindex>
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>" rel="nofollow"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?>
						</noindex>
					</div>
				</div>
			</div>
		<?
	}
}
else
	echo GetMessage("B_B_DRAFT_NO_MES");
?>	
</div>