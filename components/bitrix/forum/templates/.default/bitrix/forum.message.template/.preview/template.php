<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$res = $arParams["~MESSAGE"];
if (!isset($arParams["SHOW_HEADER"]) || $arParams["SHOW_HEADER"] != "N"):?>
<a name="postform"></a>
<!--MSG_PREVIEW-->
<div class="forum-preview">
	<div class="forum-header-box">
		<div class="forum-header-title"><span><?=GetMessage("F_VIEW")?></span></div>
	</div>

	<div class="forum-info-box forum-post-preview">
		<div class="forum-info-box-inner">
			<div class="forum-post-entry">
<?endif;?>
				<div class="forum-post-text"<?if(isset($res["ID"]) && $res["ID"] > 0): ?> id="message_text_<?=$res["ID"]?>"<? endif; ?>><?=(
					is_set($res, "POST_MESSAGE_TEXT") ? $res["POST_MESSAGE_TEXT"] : $res["TEXT"])?></div>
				<?
				if (!empty($res["FILES"]))
				{
					$arFilesHTML = array("thumb" => array(), "files" => array());

					foreach ($res["FILES"] as $arFile)
					{
						$bdraw = (is_array($arFile) ? !in_array($arFile["FILE_ID"], $res["FILES_PARSED"]) : !in_array($arFile, $res["FILES_PARSED"]));
						if ($bdraw)
						{
							$arFileTemplate = $GLOBALS["APPLICATION"]->IncludeComponent("bitrix:forum.interface", "show_file",
								Array(
									"FILE" => $arFile,
									"SHOW_MODE" => $arParams["ATTACH_MODE"],
									"WIDTH" => $arParams["ATTACH_SIZE"],
									"HEIGHT" => $arParams["ATTACH_SIZE"],
									"CONVERT" => "N",
									"FAMILY" => "FORUM",
									"SINGLE" => "Y",
									"RETURN" => "ARRAY",
									"SHOW_LINK" => "Y"
								),
								null,
								array("HIDE_ICONS" => "Y")
							);
							if (!empty($arFileTemplate["DATA"]))
								$arFilesHTML["thumb"][] = $arFileTemplate["RETURN_DATA"];
							else
								$arFilesHTML["files"][] = $arFileTemplate["RETURN_DATA"];
						}
					}

					if (!empty($arFilesHTML["thumb"]) || !empty($arFilesHTML["files"]))
					{
						?>
						<div class="forum-post-attachments">
							<label><?=GetMessage("F_ATTACH_FILES")?></label>
							<?
							if (!empty($arFilesHTML["thumb"]))
							{
								?><div class="forum-post-attachment forum-post-attachment-thumb"><fieldset><?=implode("", $arFilesHTML["thumb"])?></fieldset></div><?;
							}
							if (!empty($arFilesHTML["files"]))
							{
								?><div class="forum-post-attachment forum-post-attachment-files"><ul><li><?=implode("</li><li>", $arFilesHTML["files"])?></li></ul></div><?;
							}
							?>
						</div>
						<?
					}
				}
				if (!empty($res["EDITOR_NAME"]))
				{
				?><div class="forum-post-lastedit">
					<span class="forum-post-lastedit"><?=GetMessage("F_EDIT_HEAD")?>
						<span class="forum-post-lastedit-user"><?
							if (!empty($res["URL"]["EDITOR"]))
							{
								?><noindex><a rel="nofollow" href="<?=$res["URL"]["EDITOR"]?>"><?=$res["EDITOR_NAME"]?></a></noindex><?
							}
							else
							{
								?><?=$res["EDITOR_NAME"]?><?
							}?></span> - <span class="forum-post-lastedit-date"><?=$res["EDIT_DATE"]?></span>
						<?if (!empty($res["EDIT_REASON"]))
						{
							?><span class="forum-post-lastedit-reason">(<span><?=$res["EDIT_REASON"]?></span>)</span><?
						}
						?>
					</span>
				</div><?
				}
if (!isset($arParams["SHOW_HEADER"]) || $arParams["SHOW_HEADER"] != "N"):?>
			</div>
		</div>
	</div>
</div>
<!--MSG_END_MSG_PREVIEW-->

<?endif;?>
