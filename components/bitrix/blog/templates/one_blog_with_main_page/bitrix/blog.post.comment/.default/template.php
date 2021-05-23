<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CUtil::InitJSCore(array("ajax"));

include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");
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
else
{
	?>

	<div id="form_comment_" style="display:none;">
		<div id="form_c_del">
		<form method="POST" name="form_comment" id="form_comment" action="<?=POST_FORM_ACTION_URI?>">
		<input type="hidden" name="parentId" id="parentId" value="">
		<?=bitrix_sessid_post()?>
		<table class="blog-comment-form">
		<tr>
			<td>
				<table class="blog-comment-form-noborder" cellspacing="0">
					<tr valign="top">
						<td colspan="2">
						<table class="blog-comment-form-noborder-padding" cellspacing="0">
							<tr valign="middle" style="background-image:url(<?=$templateFolder?>/images/toolbarbg.gif)">
								<td><select name="ffont" id="select_font" onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')">
										<option value='0'><?=GetMessage("BPC_FONT")?></option>
										<option value='Arial' style='font-family:Arial'>Arial</option>
										<option value='Times' style='font-family:Times'>Times</option>
										<option value='Courier' style='font-family:Courier'>Courier</option>
										<option value='Impact' style='font-family:Impact'>Impact</option>
										<option value='Geneva' style='font-family:Geneva'>Geneva</option>
										<option value='Optima' style='font-family:Optima'>Optima</option>
										<option value='Verdana' style='font-family:Verdana'>Verdana</option>
								</select></td>
								<td nowrap>
									<a id=FontColor	class=blogButton href='javascript:ColorPicker()'><img src="<?=$templateFolder?>/images/font_color.gif" width="20" height="20" title="<?=GetMessage("BPC_IMAGE")?>" class="blogButton"></a>
									<a id=bold class=blogButton href='javascript:simpletag("B")'><img src="<?=$templateFolder?>/images/bold.gif" width="20" height="20" title="<?=GetMessage("BPC_BOLD")?>" class="blogButton"></a>
									<a id=italic class=blogButton href='javascript:simpletag("I")'><img src="<?=$templateFolder?>/images/italic.gif" width="20" height="20" title="<?=GetMessage("BPC_ITALIC")?>" class="blogButton"></a>
									<a id=under class=blogButton href='javascript:simpletag("U")'><img src="<?=$templateFolder?>/images/under.gif" width="20" height="20" title="<?=GetMessage("BPC_UNDER")?>" class="blogButton"></a>
									<a id=url class=blogButton href='javascript:tag_url()'><img src="<?=$templateFolder?>/images/link.gif" width="20" height="20" title="<?=GetMessage("BPC_HYPERLINK")?>" class="blogButton"></a>
									<a id=image class=blogButton href='javascript:tag_image()'><img src="<?=$templateFolder?>/images/image_link.gif" width="20" height="20" title="<?=GetMessage("BLOG_P_INSERT_IMAGE_LINK")?>" class="blogButton"></a>
									<a id=quote class=blogButton href='javascript:quoteMessage()'><img src="<?=$templateFolder?>/images/quote.gif" width="20" height="20" title="<?=GetMessage("BPC_QUOTE")?>" class="blogButton"></a>
									<a id=code class=blogButton href='javascript:simpletag("CODE")'><img src="<?=$templateFolder?>/images/code.gif" width="20" height="20" title="<?=GetMessage("BPC_CODE")?>" class="blogButton"></a>
									<a id=list class=blogButton href='javascript:tag_list()'><img src="<?=$templateFolder?>/images/list.gif" width="20" height="20" title="<?=GetMessage("BPC_LIST")?>" class="blogButton"<?if($arResult["use_captcha"]!==true) echo ' onload="imageLoaded()"'?>></a>
								</td>
								<td width=100% align=right nowrap><a id=close_all style=visibility:hidden class=blogButton href='javascript:closeall()' title='<?=GetMessage("BPC_CLOSE_OPENED_TAGS")?>'><?=GetMessage("BPC_CLOSE_ALL_TAGS")?></a></td>
							</tr>
					</table>
						</td>
					</tr>
					<?
					if(empty($arResult["User"]))
					{
						?>
						<tr valign="top">
							<td align="right" class="padding"><?=GetMessage("B_B_MS_NAME")?><font class="blog-req">*</font></td>
							<td><input size="50" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"></td>
						</tr>
						<tr valign="top">
							<td class="padding" align="right">Email:</td>
							<td><input size="50" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></td>
						</tr>
						<?
					}
					?>
					<?if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
					{
						?>
						<tr valign="top">
							<td class="padding" width="0%" align="right"><?=GetMessage("B_B_MS_SUBJECT")?></td>
							<td width="100%"><input size="50" type="text" name="subject" id="subject" value=""></td>
						</tr>
						<?
					}
					?>
					<tr valign="top">
						<td align="right"><?=GetMessage("B_B_MS_M_BODY")?><span class="blog-req">*</span></td>
						<td><textarea name="comment" id="comment" style="width:95%" rows="5" id="MESSAGE" onKeyPress="check_ctrl_enter(arguments[0])"></textarea>
						</td>
					</tr>
					<?
					if($arResult["use_captcha"]===true)
					{
						?>
						<tr valign="top">
							<td>&nbsp;</td>
							<td>
								<div id="div_captcha">
									<img src="" width="180" height="40" id="captcha" style="display:none;">
								</div>
							</td>
						</tr>
						<tr>
							<td class="padding" align="left" nowrap><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></td>
							<td>
								<input type="hidden" name="captcha_code" id="captcha_code" value="">
								<input type="text" size="10" name="captcha_word" id="captcha_word" value="">
							</td>
						</tr>
						<?
					}
					?>
					<tr>
						<td></td>
						<td><?
							//							userconsent only for unregistered users
							if (empty($arResult["User"]) && $arParams['USER_CONSENT'] == 'Y')
							{
								$APPLICATION->IncludeComponent(
									"bitrix:main.userconsent.request",
									"",
									array(
										"ID" => $arParams["USER_CONSENT_ID"],
										"IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
										"AUTO_SAVE" => "Y",
										"IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
										"ORIGIN_ID" => "sender/sub",
										"ORIGINATOR_ID" => "",
										"REPLACE" => array(
											'button_caption' => GetMessage("B_B_MS_SEND"),
											'fields' => array(GetMessage("B_B_MS_NAME"), 'E-mail')
										),
									)
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="left"><input type="hidden" name="post" value="Y"><input type="submit" name="post" value="<?=GetMessage("B_B_MS_SEND")?>"><input type="submit" name="preview" value="<?=GetMessage("B_B_MS_PREVIEW")?>"></td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</form>
		</div>
	</div>
	<script>
	<!--
	var last_div = '';
	function showComment(key, subject, error, comment, userName, userEmail)
	{
		<?
		if ($arResult["use_captcha"]===true)
		{
			?>
			BX.ajax.getCaptcha(function(data) {
				BX("captcha_word").value = "";
				BX("captcha_code").value = data["captcha_sid"];
				BX("captcha").src = '/bitrix/tools/captcha.php?captcha_code=' + data["captcha_sid"];
				BX("captcha").style.display = "";
			});
			<?
		}
		?>

		var cl = document.getElementById('form_c_del').cloneNode(true);
		var ld = document.getElementById('form_c_del');
		ld.parentNode.removeChild(ld);
		document.getElementById('form_comment_' + key).appendChild(cl);
		document.getElementById('form_c_del').style.display = "block";
		document.form_comment.parentId.value = key;
		document.form_comment.action = document.form_comment.action+"#"+key;

		if(subject.length>0)
			document.form_comment.subject.value = subject;

		if(error == "Y")
		{
			if(comment.length > 0)
				document.form_comment.comment.value = comment;
			if(userName.length > 0)
				document.form_comment.user_name.value = userName;
			if(userEmail.length > 0)
				document.form_comment.user_email.value = userEmail;
		}
		last_div = key;

		//document.form_comment.comment.focus();
		return false;
	}
	//-->
	</script>
	<?
	function ShowComment($comment, $tabCount=0, $tabSize=30, $canModerate=false, $User=Array(), $use_captcha=false, $bCanUserComment=false, $errorComment=false, $arParams)
	{
		$tabWidth = $tabCount*$tabSize;
		?>
		<a name="<?=$comment["ID"]?>"></a>
		<table width="100%" cellpadding="0" border="0">
		<tr>
			<td width="0%"><img src="/bitrix/images/1.gif" height="1" width="<?=$tabWidth?>"></td>
			<td width="100%" valign="top">
				<table class="blog-table-post-comment">
				<tr>
					<th nowrap width="100%">
					<table class="blog-table-post-comment-table">
					<tr>
						<td align="left"><?=$comment["DateFormated"]?></td>
						<td align="right">
						<table width="0%" class="blog-table-post-comment-table-date">
						<tr>
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
							<td align="right" nowrap><a href="<?=$comment["urlToAuthor"]?>" class="blog-user"></a></td>
							<td align="right" nowrap>
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
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
									"INLINE" => "Y",
									"SEO_USER" => $arParams["SEO_USER"],
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							?>
							</td>
							<?
						}
						elseif($comment["urlToAuthor"] <> '')
						{
							?>
							<td align="right" nowrap><a href="<?=$comment["urlToAuthor"]?>" class="blog-user"></a></td>
							<td align="right" nowrap>
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
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
									"INLINE" => "Y",
									"SEO_USER" => $arParams["SEO_USER"],
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							?>
							</td>
							<?
						}
						else
						{
							?>
							<td align="right" nowrap><div class="blog-user"></div></td>
							<td align="right" nowrap><?=$comment["AuthorName"]?></td>
							<?
						}

						if($comment["urlToDelete"] <> '')
						{
							if($comment["AuthorEmail"] <> '')
							{
								?>
								<td align="right" nowrap><small>(Email: <a href="mailto:<?=$comment["AuthorEmail"]?>"><?=$comment["AuthorEmail"]?></a>)</small></td>
								<?
							}

							if($comment["ShowIP"] == "Y")
							{
								?>
								<td align="right" nowrap><small>(<?=GetMessage("B_B_MS_FROM")?> <?=$comment["AUTHOR_IP"]?><?if($comment["AUTHOR_IP1"] <> '') echo ', '.$comment["AUTHOR_IP1"];?>)</small>&nbsp;</td>
								<?
							}
							?>
							<td align="right">
								<a href="javascript:if(confirm('<?=GetMessage("BPC_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$comment["urlToDelete"]."&".bitrix_sessid_get()?>'" class="blog-post-delete"></a>
							</td>
							<?
						}
						?>
						</tr>
						</table>
				</tr>
				</table>
				</th>
				</tr>
				<tr>
					<td>
						<?=$comment["AVATAR_img"]?>
						<?if($comment["TitleFormated"] <> '')
						{
							?>
							<b><?=$comment["TitleFormated"]?></b><br />
							<?
						}
						?>
						<?=$comment["TextFormated"]?>
						<br clear="all">
						<?
						if($bCanUserComment===true)
						{
							?>
							(<a href="javascript:void(0)" onclick="return showComment('<?=$comment["ID"]?>', '<?=$comment["CommentTitle"]?>', '', '', '', '')"><?=GetMessage("B_B_MS_REPLY")?></a>)&nbsp;
							<?
						}

						if(intval($comment["PARENT_ID"])>0)
						{
							?>
							(<a href="#<?=$comment["PARENT_ID"]?>"><?=GetMessage("B_B_MS_PARENT")?></a>)&nbsp;
							<?
						}
						?>
						(<a href="#<?=$comment["ID"]?>"><?=GetMessage("B_B_MS_LINK")?></a>)
					</td>
				</tr>
				</table>
						<?
						if($errorComment == '' && $_POST["parentId"]==$comment["ID"] && $_POST["preview"] <> '')
						{							
							?><div style="border:1px solid red"><?
								$commentPreview = Array(
										"ID" => "preview",
										"TitleFormated" => htmlspecialcharsbx($_POST["subject"]),
										"TextFormated" => htmlspecialcharsbx($_POST["commentFormated"]),
										"AuthorName" => htmlspecialcharsbx($User["NAME"]),
										"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
									);
								ShowComment($commentPreview, ($level+1), 30, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);
							?></div><?
						}
						if($errorComment <> '' && intval($_POST["parentId"])==$comment["ID"] && $bCanUserComment===true)
						{							
							?><span class='errortext'><?=$errorComment?></span><?
						}
						?>
						<div id="form_comment_<?=$comment['ID']?>"></div>
						<?
						if(($errorComment <> '' || $_POST["preview"] <> '') && intval($_POST["parentId"])==$comment["ID"] && $bCanUserComment===true)
						{
							$form1 = CUtil::JSEscape($_POST["comment"]);

							$subj = CUtil::JSEscape($_POST["subject"]);
							$user_name = CUtil::JSEscape($_POST["user_name"]);
							$user_email = CUtil::JSEscape($_POST["user_email"]);
							?>
							<script>
							<!--
							var cmt = '<?=$form1?>';
							showComment('<?=$comment["ID"]?>', '<?=$subj?>', 'Y', cmt, '<?=$user_name?>', '<?=$user_email?>');
							//-->
							</script>
							<?
						}
						?>
			</td>
		</tr>
		</table>
		<?
	}

	function RecursiveComments($sArray, $key, $level=0, $first=false, $canModerate=false, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams)
	{
		if(!empty($sArray[$key]))
		{
			foreach($sArray[$key] as $comment)
			{
				ShowComment($comment, $level, 30, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);
				if(!empty($sArray[$comment["ID"]]))
				{
					foreach($sArray[$comment["ID"]] as $key1)
					{
						ShowComment($key1, ($level+1), 30, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);

						if(!empty($sArray[$key1["ID"]]))
						{
							RecursiveComments($sArray, $key1["ID"], ($level+2), false, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);
						}
					}
				}
				if($first)
					$level=0;
			}
		}
	}
	?>
	<div class="blog-line"></div>

	<?
	if($arResult["NEED_NAV"] == "Y")
	{
		?><div align="center">
		<table class="blog-table-post-comment" style="width:0%;">
		<tr>
			<th nowrap><?=GetMessage("BPC_PAGE")?> <?=$arResult["PAGE"]?> <?=GetMessage("BPC_PAGE_OF")?> <?=$arResult["PAGE_COUNT"]?><br /><?
		foreach($arResult["PAGES"] as $v)
		{
			echo $v;
		}
		?></th>
		</tr>
		</table></div><?
	}
	?>

	<?
	if($arResult["CanUserComment"])
	{
		$postTitle = "";
		if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
			$postTitle = "RE: ".CUtil::JSEscape($arResult["Post"]["TITLE"]);
		
		?>
		<div align="center" class="blog-comment-text"><a name="comment"></a><a href="javascript:void(0)" onclick="return showComment('0', '<?=$postTitle?>')"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div>
		<a name="0"></a>
		<?
		if($arResult["COMMENT_ERROR"] == '' && mb_strlen($_POST["parentId"]) < 2 && intval($_POST["parentId"])==0 && $_POST["preview"] <> '')
		{							
			?><div style="border:1px solid red"><?
				$commentPreview = Array(
						"ID" => "preview",
						"TitleFormated" => htmlspecialcharsbx($_POST["subject"]),
						"TextFormated" => htmlspecialcharsbx($_POST["commentFormated"]),
						"AuthorName" => htmlspecialcharsbx($arResult["User"]["NAME"]),
						"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
					);
				ShowComment($commentPreview, 0, 30, false, $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], false, $arParams);
			?></div><?
		}

		if($arResult["COMMENT_ERROR"] <> '' && mb_strlen($_POST["parentId"]) < 2 && intval($_POST["parentId"])==0)
		{
			?>
			<span class='errortext'><?=$arResult["COMMENT_ERROR"]?></span>
			<?
		}
		?>
		<div id=form_comment_0></div><br />
		<?
		if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '') && intval($_POST["parentId"]) == 0 && mb_strlen($_POST["parentId"]) < 2)
		{
			$form1 = CUtil::JSEscape($_POST["comment"]);

			$subj = CUtil::JSEscape($_POST["subject"]);
			$user_name = CUtil::JSEscape($_POST["user_name"]);
			$user_email = CUtil::JSEscape($_POST["user_email"]);
			?>
			<script>
			<!--
			var cmt = '<?=$form1?>';
			showComment('0', '<?=$subj?>', 'Y', cmt, '<?=$user_name?>', '<?=$user_email?>');
			//-->
			</script>
			<?
		}
	}

	RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arParams);

	if($arResult["NEED_NAV"] == "Y")
	{
		?><div align="center">
		<table class="blog-table-post-comment" style="width:0%;">
		<tr>
			<th nowrap><?=GetMessage("BPC_PAGE")?> <?=$arResult["PAGE"]?> <?=GetMessage("BPC_PAGE_OF")?> <?=$arResult["PAGE_COUNT"]?><br /><?
		foreach($arResult["PAGES"] as $v)
		{
			echo $v;
		}
		?></th>
		</tr>
		</table></div><?
	}

	if($arResult["CanUserComment"] && count($arResult["Comments"])>2)
	{
		?>
		<div align="center" class="blog-comment-text"><a href="#comments" onclick="return showComment('00', '<?=$postTitle?>')"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div><a name="00"></a>
		<?
		if($arResult["COMMENT_ERROR"] == '' && $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1 && $_POST["preview"] <> '')
		{							
			?><div style="border:1px solid red"><?
				$commentPreview = Array(
						"ID" => "preview",
						"TitleFormated" => htmlspecialcharsbx($_POST["subject"]),
						"TextFormated" => htmlspecialcharsbx($_POST["commentFormated"]),
						"AuthorName" => htmlspecialcharsbx($arResult["User"]["NAME"]),
						"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
					);
				ShowComment($commentPreview, 0, 30, false, $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arParams);
			?></div><?
		}
		
		if($arResult["COMMENT_ERROR"] <> '' && $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
		{
			?>
			<span class='errortext'><?=$arResult["COMMENT_ERROR"]?></span>
			<?
		}
		?>

		<div id=form_comment_00></div><br />
		<?
		if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '') && $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
		{
			$form1 = CUtil::JSEscape($_POST["comment"]);

			$subj = CUtil::JSEscape($_POST["subject"]);
			$user_name = CUtil::JSEscape($_POST["user_name"]);
			$user_email = CUtil::JSEscape($_POST["user_email"]);
			?>
			<script>
			<!--
			var cmt = '<?=$form1?>';
			showComment('00', '<?=$subj?>', 'Y', cmt, '<?=$user_name?>', '<?=$user_email?>');
			//-->
			</script>
			<?
		}

	}
}
?>