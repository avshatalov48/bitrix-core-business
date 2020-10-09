<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;

CUtil::InitJSCore(array("ajax"));
?>
<div class="blog-comments">
<a name="comments"></a>
<?
include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");

if($arResult["MESSAGE"] <> '')
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
	<?
}
if($arResult["ERROR_MESSAGE"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
	<?
}
if($arResult["FATAL_MESSAGE"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["FATAL_MESSAGE"]?>
		</div>
	</div>
	<?
}
else
{
	?>
	<div id="form_comment_" >
		<div id="form_c_del" style="display:none;">
		<div class="blog-comment-form">

		<form method="POST" name="form_comment" id="form_comment" action="<?=POST_FORM_ACTION_URI?>">
		<input type="hidden" name="parentId" id="parentId" value="">
		<input type="hidden" name="edit_id" id="edit_id" value="">
		<input type="hidden" name="act" id="act" value="add">
		<input type="hidden" name="post" value="Y">
		<?=bitrix_sessid_post()?>

		<div class="blog-comment-fields">
			<?
			if(empty($arResult["User"]))
			{
				?>
				<div class="blog-comment-field blog-comment-field-user">
					<div class="blog-comment-field blog-comment-field-author"><div class="blog-comment-field-text"><label for="user_name"><?=GetMessage("B_B_MS_NAME")?></label><span class="blog-required-field">*</span></div><span><input maxlength="255" size="30" tabindex="3" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"></span></div>
					<div class="blog-comment-field-user-sep">&nbsp;</div>
					<div class="blog-comment-field blog-comment-field-email"><div class="blog-comment-field-text"><label for="">E-mail</label><span class="blog-required-field">*</span></div><span><input maxlength="255" size="30" tabindex="4" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></span></div>
					<div class="blog-clear-float"></div>
				</div>
				<?
			}
			?>
			<?if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
			{
				?>
				<div class="blog-comment-field blog-comment-field-title">
					<div class="blog-comment-field">
					<div class="blog-comment-field-text"><label for="user_name"><?=GetMessage("BPC_SUBJECT")?></label></div>
					<span><input size="70" type="text" name="subject" id="subject" value=""></span>
					<div class="blog-clear-float"></div>
					</div>
				</div>
				<?
			}

			// Light Visual BB Editor
			CModule::IncludeModule("fileman");
			function CustomizeLHEForBlogComments()
			{
				?>
				<script>
				// Rename image button and change Icon
				LHEButtons['Image'].id = 'ImageLink';
				LHEButtons['Image'].src = '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_image_upload.gif';
				LHEButtons['Image'].name = '<?=GetMessage("BLOG_P_IMAGE_LINK")?>';

				LHEButtons['BlogInputVideo'] = {
					id : 'BlogInputVideo',
					src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
					name : '<?=GetMessage("FPF_VIDEO")?>',
					title : '<?=GetMessage("FPF_VIDEO")?>',
					handler: function(pBut)
					{
						pBut.pLEditor.OpenDialog({id : 'BlogVideo', obj: false});
					},
					parser: {
						name: 'blogvideo',
						obj: {
							Parse: function(sName, sContent, pLEditor)
							{
								sContent = sContent.replace(/\[VIDEO\s*?width=(\d+)\s*?height=(\d+)\s*\]((?:\s|\S)*?)\[\/VIDEO\]/ig, function(str, w, h, src)
								{
									var
										w = parseInt(w) || 400,
										h = parseInt(h) || 300,
										src = BX.util.trim(src);

									return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + BX.message.Video + ": " + src + '" />';
								});
								return sContent;
							},
							UnParse: function(bxTag, pNode, pLEditor)
							{
								if (bxTag.tag == 'blogvideo')
								{
									return "[VIDEO WIDTH=" + pNode.arAttributes["width"] + " HEIGHT=" + pNode.arAttributes["height"] + "]" + bxTag.params.value + "[/VIDEO]";
								}
								return "";
							}
						}
					}
				};

				window.LHEDailogs['BlogVideo'] = function(pObj)
				{
					var str = '<table width="100%"><tr>' +
						'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_path"><b><?= GetMessage('BPC_VIDEO_P')?>:</b></label></td>' +
						'<td class="lhe-dialog-param">' +
						'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_path" value="" size="30"/>' +
						'</td>' +
					'</tr><tr>' +
						'<td></td>' +
						'<td style="padding: 0!important; font-size: 11px!important;"><?= GetMessage('BPC_VIDEO_PATH_EXAMPLE')?></td>' +
					'</tr><tr>' +
						'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_width">' + BX.message.ImageSizing + ':</label></td>' +
						'<td class="lhe-dialog-param">' +
							'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_width" value="" size="4"/>' +
							' x ' +
							'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_height" value="" size="4" />' +
						'</td>' +
					'</tr></table>';

					return {
						title: "<?= GetMessage('FPF_VIDEO')?>",
						innerHTML : str,
						width: 480,
						OnLoad: function()
						{
							pObj.pPath = BX(pObj.pLEditor.id + "lhed_blog_video_path");
							pObj.pWidth = BX(pObj.pLEditor.id + "lhed_blog_video_width");
							pObj.pHeight = BX(pObj.pLEditor.id + "lhed_blog_video_height");
						},
						OnSave: function()
						{
							pLEditor = window.oBlogComLHE;

							var
								src = BX.util.trim(pObj.pPath.value),
								w = parseInt(pObj.pWidth.value) || 400,
								h = parseInt(pObj.pHeight.value) || 300;

							if (src == "")
								return;

							if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
							{
								pLEditor.WrapWith("", "", "[VIDEO WIDTH=" + w + " HEIGHT=" + h + "]" + src + "[/VIDEO]");
							}
							else if(pLEditor.sEditorMode == 'html') // WYSIWYG
							{
								pLEditor.InsertHTML('<img id="' + pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + BX.message.Video + ": " + src + '" />');
							}
						}
					};
				};


				LHEButtons['CreateLinkNC'] = {
					id : 'CreateLinkNC',
					src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_link.gif',
					name : '<?=GetMessage("BPC_LINK")?>',
					title : '<?=GetMessage("BPC_LINK")?>',
					handler: function(pBut)
					{
						pBut.pLEditor.OpenDialog({id : 'CreateLinkNCDialog', obj: false});
					}
				};

				window.LHEDailogs['CreateLinkNCDialog'] = function(pObj)
				{
					var str = "";
					if(document.getElementById('nocommentreason'))
						str = document.getElementById('nocommentreason').innerHTML;

					return {
						title: "<?= GetMessage('BPC_LINK')?>",
						innerHTML : str,
						width: 480,
						OnLoad: function() {},
						OnSave: function() {}
					};
				};

				// Submit form by ctrl+enter
				window.blogCommentCtrlEnterHandler = function(e)
				{
					oBlogComLHE.SaveContent();
					if (document.forms.form_comment)
						document.forms.form_comment.submit();
				};

				document.forms.form_comment.onsubmit = function()
				{
					oBlogComLHE.SaveContent();
				};
				</script>
				<?
			}

			AddEventHandler("fileman", "OnIncludeLightEditorScript", "CustomizeLHEForBlogComments");
			$arSmiles = array();
			if(!empty($arResult["Smiles"]))
			{
				foreach($arResult["Smiles"] as $arSmile)
				{
					$arSmiles[] = array(
						'name' => $arSmile["~LANG_NAME"],
						'path' => "/bitrix/images/blog/smile/".$arSmile["IMAGE"],
						'code' => str_replace("\\\\","\\",$arSmile["TYPE"])
					);
				}
			}

			$LHE = new CLightHTMLEditor;

			$LHE->Show(array(
				'id' => 'LHEBlogCom',
				'height' => $arParams['EDITOR_DEFAULT_HEIGHT'],
				'inputId' => 'comment',
				'inputName' => 'comment',
				'content' => "",
				'bUseFileDialogs' => false,
				'bUseMedialib' => false,
				'toolbarConfig' => array(
					'Bold', 'Italic', 'Underline', 'Strike',
					'ForeColor','FontList', 'FontSizeList',
					'RemoveFormat',
					'Quote', 'Code',
					((!$arResult["NoCommentUrl"]) ? 'CreateLink' : 'CreateLinkNC'),
					((!$arResult["NoCommentUrl"]) ? 'DeleteLink' : ''),
					'Image',
					//'BlogImage',
					(($arResult["allowVideo"] == "Y") ? 'BlogInputVideo' : ''),
					'Table',
					'InsertOrderedList',
					'InsertUnorderedList',
					//'Translit',
					'SmileList',
					'Source'
				),
				'jsObjName' => 'oBlogComLHE',
				'arSmiles' => $arSmiles,
				'smileCountInToolbar' => $arParams['SMILES_COUNT'],
				'bSaveOnBlur' => false,
				//'BBCode' => !$arResult["allow_html"],
				'BBCode' => true,
				'bResizable' => $arParams['EDITOR_RESIZABLE'],
				'bQuoteFromSelection' => true,
				'ctrlEnterHandler' => 'blogCommentCtrlEnterHandler', // Ctrl+Enter handler name in global namespace
				'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'], // Set first view to CODE or to WYSIWYG
				'bBBParseImageSize' => true // [IMG ID=XXX WEIGHT=5 HEIGHT=6],  [IMGWEIGHT=5 HEIGHT=6]/image.gif[/IMG]
			));

			if($arResult["NoCommentReason"] <> '')
			{
				?>
				<div id="nocommentreason" style="display:none;"><?=$arResult["NoCommentReason"]?></div>
				<?
			}
			if($arResult["use_captcha"]===true)
			{
				?>
				<div class="blog-comment-field blog-comment-field-captcha">
					<div class="blog-comment-field-captcha-label">
						<label for=""><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></label><span class="blog-required-field">*</span><br>
						<input type="hidden" name="captcha_code" id="captcha_code" value="">
						<input type="text" size="30" name="captcha_word" id="captcha_word" value=""  tabindex="7">
						</div>
					<div class="blog-comment-field-captcha-image">
						<div id="div_captcha">
							<img src="" width="180" height="40" id="captcha" style="display:none;">
						</div>
					</div>
				</div>
				<?
			}
			?>

			<div class="blog-comment-buttons">
				<input tabindex="10" value="<?=GetMessage("B_B_MS_SEND")?>" type="submit" name="post">
				<input tabindex="11" name="preview" value="<?=GetMessage("B_B_MS_PREVIEW")?>" type="submit">
			</div>

		</div>
		</form>
		</div>
	</div>
	</div>
	<?
	$prevTab = 0;
	function ShowComment($comment, $tabCount=0, $tabSize=2.5, $canModerate=false, $User=Array(), $use_captcha=false, $bCanUserComment=false, $errorComment=false, $arParams = array())
	{
		if($comment["SHOW_AS_HIDDEN"] == "Y" || $comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
		{
			global $prevTab;
			$tabCount = intval($tabCount);
			if($tabCount <= 5)
				$paddingSize = 2.5 * $tabCount;
			elseif($tabCount > 5 && $tabCount <= 10)
				$paddingSize = 2.5 * 5 + ($tabCount - 5) * 1.5;
			elseif($tabCount > 10)
				$paddingSize = 2.5 * 5 + 1.5 * 5 + ($tabCount-10) * 1;

			if($prevTab > $tabCount)
				$prevTab = $tabCount;
			if($prevTab <= 5)
				$prevPaddingSize = 2.5 * $prevTab;
			elseif($prevTab > 5 && $prevTab <= 10)
				$prevPaddingSize = 2.5 * 5 + ($prevTab - 5) * 1.5;
			elseif($prevTab > 10)
				$prevPaddingSize = 2.5 * 5 + 1.5 * 5 + ($prevTab-10) * 1;
			$prevTab = $tabCount;
			?>
			<div class="blog-comment-line" style="margin-left:<?=$prevPaddingSize?>em;"></div>
			<a name="<?=$comment["ID"]?>"></a>
			<div class="blog-comment" style="padding-left:<?=$paddingSize?>em;">
			<?
			if($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
			{
				$aditStyle = "";
				if($comment["AuthorIsAdmin"] == "Y")
					$aditStyle = " blog-comment-admin";
				if(intval($comment["AUTHOR_ID"]) > 0)
					$aditStyle .= " blog-comment-user-".intval($comment["AUTHOR_ID"]);
				if($comment["AuthorIsPostAuthor"] == "Y")
					$aditStyle .= " blog-comment-author";
				if($comment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $comment["ID"] != "preview")
					$aditStyle .= " blog-comment-hidden";
				if($comment["ID"] == "preview")
					$aditStyle .= " blog-comment-preview";
				?>
				<div class="blog-comment-cont<?=$aditStyle?>">
				<div class="blog-comment-cont-white">
				<div class="blog-comment-info">
					<div class="blog-comment-avatar"><?
					if($comment["AVATAR_img"] <> '')
						echo $comment["AVATAR_img"];
					else
						echo '<img src="/bitrix/components/bitrix/blog/templates/.default/images/noavatar.gif" border="0">';
						?></div>
					<?if ($arParams["SHOW_RATING"] == "Y"):?>
						<div class="blog-post-rating rating_vote_graphic">
						<?
						$GLOBALS["APPLICATION"]->IncludeComponent(
							"bitrix:rating.vote", $arParams["RATING_TYPE"],
							Array(
								"ENTITY_TYPE_ID" => "BLOG_COMMENT",
								"ENTITY_ID" => $comment["ID"],
								"OWNER_ID" => $comment["arUser"]["ID"],
								"USER_VOTE" => $arParams["RATING"][$comment["ID"]]["USER_VOTE"],
								"USER_HAS_VOTED" => $arParams["RATING"][$comment["ID"]]["USER_HAS_VOTED"],
								"TOTAL_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_NEGATIVE_VOTES"],
								"TOTAL_VALUE" => $arParams["RATING"][$comment["ID"]]["TOTAL_VALUE"],
								"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							),
							$arParams["component"],
							array("HIDE_ICONS" => "Y")
						);?>
						</div>
						<?endif;?>
					<?
					if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($comment["urlToBlog"] <> '' || $comment["urlToAuthor"] <> '') && array_key_exists("ALIAS", $comment["BlogUser"]) && $comment["BlogUser"]["ALIAS"] <> '')
						$arTmpUser = array(
							"NAME" => "",
							"LAST_NAME" => "",
							"SECOND_NAME" => "",
							"LOGIN" => "",
							"NAME_LIST_FORMATTED" => $comment["BlogUser"]["~ALIAS"],
						);
					elseif ($comment["urlToBlog"] <> '' || $comment["urlToAuthor"] <> '')
						$arTmpUser = array(
							"NAME" => $comment["arUser"]["~NAME"],
							"LAST_NAME" => $comment["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $comment["arUser"]["~SECOND_NAME"],
							"LOGIN" => $comment["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);

					if($comment["urlToBlog"] <> '')
					{
						?>
						<div class="blog-author">
						<?
						$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $comment["arUser"]["ID"],
								"HTML_ID" => "blog_post_comment_".$comment["arUser"]["ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $comment["urlToAuthor"],
								"PROFILE_URL_LIST" => $comment["urlToBlog"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"PATH_TO_SONET_USER_PROFILE" => ($arParams["USE_SOCNET"] == "Y" ? $comment["urlToAuthor"] : $arParams["~PATH_TO_SONET_USER_PROFILE"]),
								"INLINE" => "Y",
								"SEO_USER" => $arParams["SEO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?>
						</div>
						<?
					}
					elseif($comment["urlToAuthor"] <> '')
					{
						?><div class="blog-author">
						<?if($arParams["SEO_USER"] == "Y"):?>
							<noindex>
						<?endif;?>
						<?
						$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $comment["arUser"]["ID"],
								"HTML_ID" => "blog_post_comment_".$comment["arUser"]["ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $comment["urlToAuthor"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"PATH_TO_SONET_USER_PROFILE" => ($arParams["USE_SOCNET"] == "Y" ? $comment["urlToAuthor"] : $arParams["~PATH_TO_SONET_USER_PROFILE"]),
								"INLINE" => "Y",
								"SEO_USER" => $arParams["SEO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?>
						<?if($arParams["SEO_USER"] == "Y"):?>
							</noindex>
						<?endif;?>
						</div>
						<?
					}
					else
					{
						?>
						<div class="blog-author"><?=$comment["AuthorName"]?></div>
						<?
					}

					if($comment["urlToDelete"] <> '' && $comment["AuthorEmail"] <> '')
					{
						?>
						(<a href="mailto:<?=$comment["AuthorEmail"]?>"><?=$comment["AuthorEmail"]?></a>)
						<?
					}

					?>
					<div class="blog-comment-date"><?=$comment["DateFormated"]?></div>
				</div>
				<div class="blog-clear-float"></div>
				<div class="blog-comment-content">
					<?if($comment["TitleFormated"] <> '')
					{
						?>
						<b><?=$comment["TitleFormated"]?></b><br />
						<?
					}
					?>
					<?=$comment["TextFormated"]?>

					<div class="blog-comment-meta">
					<?
					if($bCanUserComment===true)
					{
						?>
						<span class="blog-comment-answer"><a href="javascript:void(0)" onclick="return showComment('<?=$comment["ID"]?>', '<?=$comment["CommentTitle"]?>', '', '', '', '')"><?=GetMessage("B_B_MS_REPLY")?></a></span>
						<span class="blog-vert-separator"></span>
						<?
					}

					if(intval($comment["PARENT_ID"])>0)
					{
						?>
						<span class="blog-comment-parent"><a href="#<?=$comment["PARENT_ID"]?>"><?=GetMessage("B_B_MS_PARENT")?></a></span>
						<span class="blog-vert-separator"></span>
						<?
					}
					?>
					<span class="blog-comment-link"><a href="#<?=$comment["ID"]?>"><?=GetMessage("B_B_MS_LINK")?></a></span>
					<?
					if($comment["CAN_EDIT"] == "Y")
					{
						$Text = CUtil::JSEscape($comment["~POST_TEXT"]);
						$Title = CUtil::JSEscape($comment["TITLE"]);
						?>
						<script>
						var Text<?=$comment["ID"]?> = '<?=$Text?>';
						var Title<?=$comment["ID"]?> = '<?=$Title?>';
						</script>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-edit"><a href="javascript:void(0)" onclick="return editComment('<?=$comment["ID"]?>', Title<?=$comment["ID"]?>, Text<?=$comment["ID"]?>)"><?=GetMessage("BPC_MES_EDIT")?></a></span>
						<?
					}
					if($comment["urlToShow"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-show"><a href="<?=$comment["urlToShow"]."&".bitrix_sessid_get()?>"><?=GetMessage("BPC_MES_SHOW")?></a></span>
						<?
					}
					if($comment["urlToHide"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-show"><a href="<?=$comment["urlToHide"]."&".bitrix_sessid_get()?>"><?=GetMessage("BPC_MES_HIDE")?></a></span>
						<?
					}
					if($comment["urlToDelete"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-delete"><a href="javascript:if(confirm('<?=GetMessage("BPC_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$comment["urlToDelete"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BPC_MES_DELETE")?></a></span>
						<?
					}
					if($comment["urlToSpam"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-delete blog-comment-spam"><a href="<?=$comment["urlToSpam"]?>" title="<?=GetMessage("BPC_MES_SPAM_TITLE")?>"><?=GetMessage("BPC_MES_SPAM")?></a></span>
						<?
					}
					if ($arParams["SHOW_RATING"] == "Y")
					{
						?>
						<span class="rating_vote_text">
						<span class="blog-vert-separator"></span>
						<?$GLOBALS["APPLICATION"]->IncludeComponent(
							"bitrix:rating.vote", $arParams["RATING_TYPE"],
							Array(
								"ENTITY_TYPE_ID" => "BLOG_COMMENT",
								"ENTITY_ID" => $comment["ID"],
								"OWNER_ID" => $comment["arUser"]["ID"],
								"USER_VOTE" => $arParams["RATING"][$comment["ID"]]["USER_VOTE"],
								"USER_HAS_VOTED" => $arParams["RATING"][$comment["ID"]]["USER_HAS_VOTED"],
								"TOTAL_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_NEGATIVE_VOTES"],
								"TOTAL_VALUE" => $arParams["RATING"][$comment["ID"]]["TOTAL_VALUE"],
								"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							),
							$arParams["component"],
							array("HIDE_ICONS" => "Y")
						);?>
						</span>
						<?
					}
					?>
					</div>

				</div>
				</div>
				</div>
					<div class="blog-clear-float"></div>

				<?
				if($errorComment == '' && $_POST["preview"] <> '' && (intval($_POST["parentId"]) > 0 || intval($_POST["edit_id"]) > 0)
					&& ( (intval($_POST["parentId"])==$comment["ID"] && intval($_POST["edit_id"]) <= 0)
						|| (intval($_POST["edit_id"]) > 0 && intval($_POST["edit_id"]) == $comment["ID"] && $comment["CAN_EDIT"] == "Y")))
				{
					$commentPreview = Array(
							"ID" => "preview",
							"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
							"TextFormated" => htmlspecialcharsEx($_POST["commentFormated"]),
							"AuthorName" => htmlspecialcharsEx($User["NAME"]),
							"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
						);
					ShowComment($commentPreview, (intval($_POST["edit_id"]) == $comment["ID"] && $comment["CAN_EDIT"] == "Y") ? $level : ($level+1), 2.5, false, Array(), false, false, false, $arParams);
				}

				if($errorComment <> '' && $bCanUserComment===true
					&& (intval($_POST["parentId"])==$comment["ID"] || intval($_POST["edit_id"]) == $comment["ID"]))
				{
					?>
					<div class="blog-errors blog-note-box blog-note-error">
						<div class="blog-error-text">
							<?=$errorComment?>
						</div>
					</div>
					<?
				}
				?>
				<div id="form_comment_<?=$comment['ID']?>"></div>

				<?
				if(($errorComment <> '' || $_POST["preview"] <> '')
					&& (intval($_POST["parentId"])==$comment["ID"] || intval($_POST["edit_id"]) == $comment["ID"])
					&& $bCanUserComment===true)
				{
					$form1 = CUtil::JSEscape($_POST["comment"]);

					$subj = CUtil::JSEscape($_POST["subject"]);
					$user_name = CUtil::JSEscape($_POST["user_name"]);
					$user_email = CUtil::JSEscape($_POST["user_email"]);
					?>
					<script>
					<?
					if(intval($_POST["edit_id"]) == $comment["ID"])
					{
						?>editComment('<?=$comment["ID"]?>', '<?=$subj?>', '<?=$form1?>');<?
					}
					else
					{
						?>showComment('<?=$comment["ID"]?>', '<?=$subj?>', 'Y', '<?=$form1?>', '<?=$user_name?>', '<?=$user_email?>');<?
					}
					?>
					</script>
					<?
				}
			}
			elseif($comment["SHOW_AS_HIDDEN"] == "Y")
				echo "<b>".GetMessage("BPC_HIDDEN_COMMENT")."</b>";
			?>
			</div>
			<?
		}
	}

	function RecursiveComments($sArray, $key, $level=0, $first=false, $canModerate=false, $User, $use_captcha, $bCanUserComment, $errorComment, $arSumComments, $arParams)
	{
		if(!empty($sArray[$key]))
		{
			foreach($sArray[$key] as $comment)
			{
				if(!empty($arSumComments[$comment["ID"]]))
				{
					$comment["CAN_EDIT"] = $arSumComments[$comment["ID"]]["CAN_EDIT"];
					$comment["SHOW_AS_HIDDEN"] = $arSumComments[$comment["ID"]]["SHOW_AS_HIDDEN"];
					$comment["SHOW_SCREENNED"] = $arSumComments[$comment["ID"]]["SHOW_SCREENNED"];
				}
				ShowComment($comment, $level, 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);
				if(!empty($sArray[$comment["ID"]]))
				{
					foreach($sArray[$comment["ID"]] as $key1)
					{
						if(!empty($arSumComments[$key1["ID"]]))
						{
							$key1["CAN_EDIT"] = $arSumComments[$key1["ID"]]["CAN_EDIT"];
							$key1["SHOW_AS_HIDDEN"] = $arSumComments[$key1["ID"]]["SHOW_AS_HIDDEN"];
							$key1["SHOW_SCREENNED"] = $arSumComments[$key1["ID"]]["SHOW_SCREENNED"];
						}
						ShowComment($key1, ($level+1), 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);

						if(!empty($sArray[$key1["ID"]]))
						{
							RecursiveComments($sArray, $key1["ID"], ($level+2), false, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arSumComments, $arParams);
						}
					}
				}
				if($first)
					$level=0;
			}
		}
	}
	?>
	<?
	if($arResult["CanUserComment"])
	{
		$postTitle = "";
		if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
			$postTitle = "RE: ".CUtil::JSEscape($arResult["Post"]["TITLE"]);

		?>
		<div class="blog-add-comment"><a href="javascript:void(0)" onclick="return showComment('0', '<?=$postTitle?>')"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div>
		<a name="0"></a>
		<?
		if($arResult["COMMENT_ERROR"] == '' && mb_strlen($_POST["parentId"]) < 2
			&& intval($_POST["parentId"])==0 && $_POST["preview"] <> '' && intval($_POST["edit_id"]) <= 0)
		{
			?><div style="border:1px solid red"><?
				$commentPreview = Array(
						"ID" => "preview",
						"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
						"TextFormated" => htmlspecialcharsEx($_POST["commentFormated"]),
						"AuthorName" => htmlspecialcharsEx($arResult["User"]["NAME"]),
						"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
					);
				ShowComment($commentPreview, 0, 2.5, false, $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], false, $arParams);
			?></div><?
		}

		if($arResult["COMMENT_ERROR"] <> '' && mb_strlen($_POST["parentId"]) < 2
			&& intval($_POST["parentId"])==0 && intval($_POST["edit_id"]) <= 0)
		{
			?>
			<div class="blog-errors blog-note-box blog-note-error">
				<div class="blog-error-text"><?=$arResult["COMMENT_ERROR"]?></div>
			</div>
			<?
		}
		?>
		<div id=form_comment_0></div>
		<?
		if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '')
			&& intval($_POST["parentId"]) == 0 && mb_strlen($_POST["parentId"]) < 2 && intval($_POST["edit_id"]) <= 0)
		{
			$form1 = CUtil::JSEscape($_POST["comment"]);

			$subj = CUtil::JSEscape($_POST["subject"]);
			$user_name = CUtil::JSEscape($_POST["user_name"]);
			$user_email = CUtil::JSEscape($_POST["user_email"]);

			?>
			<script>
			showComment('0', '<?=$subj?>', 'Y', '<?=$form1?>', '<?=$user_name?>', '<?=$user_email?>');
			</script>
			<?
		}

		if($arResult["NEED_NAV"] == "Y")
		{
			?>
			<div class="blog-comment-nav">
				<?=GetMessage("BPC_PAGE")?>&nbsp;<?
				foreach($arResult["PAGES"] as $v)
				{
					echo $v;
				}


			?>
			</div>
			<?
		}
	}

	$arParams["RATING"] = $arResult["RATING"];
	RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);

	if($arResult["NEED_NAV"] == "Y")
	{
		?>
		<div class="blog-comment-nav">
			<?=GetMessage("BPC_PAGE")?>&nbsp;<?
			foreach($arResult["PAGES"] as $v)
			{
				echo $v;
			}


		?>
		</div>
		<?
	}

	if($arResult["CanUserComment"] && count($arResult["Comments"])>2)
	{
		?>
		<div class="blog-add-comment"><a href="#comments" onclick="return showComment('00', '<?=$postTitle?>')"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div><a name="00"></a>
		<?
		if($arResult["COMMENT_ERROR"] == '' && $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1 && $_POST["preview"] <> '')
		{
			?><div style="border:1px solid red"><?
				$commentPreview = Array(
						"ID" => "preview",
						"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
						"TextFormated" => htmlspecialcharsEx($_POST["commentFormated"]),
						"AuthorName" => htmlspecialcharsEx($arResult["User"]["NAME"]),
						"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
					);
				ShowComment($commentPreview, 0, 2.5, false, $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arParams);
			?></div><?
		}

		if($arResult["COMMENT_ERROR"] <> '' && $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
		{
			?>
			<div class="blog-errors blog-note-box blog-note-error">
				<div class="blog-error-text">
					<?=$arResult["COMMENT_ERROR"]?>
				</div>
			</div>
			<?
		}
		?>

		<div id=form_comment_00></div><br />
		<?
		if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '')
			&& $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
		{
			$form1 = CUtil::JSEscape($_POST["comment"]);

			$subj = CUtil::JSEscape($_POST["subject"]);
			$user_name = CUtil::JSEscape($_POST["user_name"]);
			$user_email = CUtil::JSEscape($_POST["user_email"]);
			?>
			<script>
			showComment('00', '<?=$subj?>', 'Y', '<?=$form1?>', '<?=$user_name?>', '<?=$user_email?>');
			</script>
			<?
		}
	}
}
?>
</div>