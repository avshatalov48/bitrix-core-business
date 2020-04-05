<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(strlen($arResult["MESSAGE"])>0)
{
	?>
	<?=$arResult["MESSAGE"]?><br /><br />
	<?
}
if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	?>
	<span class='errortext'><?=$arResult["ERROR_MESSAGE"]?></span><br /><br />
	<?
}
if(strlen($arResult["FATAL_MESSAGE"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FATAL_MESSAGE"]?></span><br /><br />
	<?
}
else
{	
	if($arResult["imageUpload"] == "Y")
	{
		?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
				<title><?=GetMessage("BLOG_P_IMAGE_UPLOAD")?></title>
			</head>
			<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
			<?=bitrix_sessid_post()?>
			<style type="text/css">
				td.tableborder, table.tableborder {background-color:#8FB0D2;}
				table.tablehead, td.tablehead {background-color:#F1F5FA;}
				table.tablebody, td.tablebody {background-color:#FFFFFF;}
				.tableheadtext, .tablebodylink {font-family: Verdana,Arial,Hevetica,sans-serif; font-size:12px;}
				.tableheadtext {color:#456A74}
				H1, H2, H3, H4 {font-family: Verdana, Arial, Helvetica, sans-serif; color:#3A84C4; font-size:13px; font-weight:bold; line-height: 16px; margin-bottom: 1px;}
				errortext, .oktext, .notetext {font-family:Verdana,Arial,Hevetica,sans-serif; font-size:13px; font-weight:bold;}
				.errortext {color:red;}
			</style>
			<h1><?=GetMessage("BLOG_P_IMAGE_UPLOAD")?></h1>
			<br />
			<table border="0" cellspacing="1" cellpadding="3" class="tableborder">
			<tr>
				<td class="tablehead" valign="top" align="right" nowrap>
				<span class="tableheadtext"><b><?=GetMessage("BLOG_IMAGE")?></b></span></td>
				<td class="tablebody"><?=CFile::InputFile("FILE_ID", 20, 0)?></td>
			</tr>
			</table>
		<br />
		<input type="submit" value="<?=GetMessage("BLOG_P_DO_UPLOAD")?>" name="do_upload">
		<input type="button" value="<?=GetMessage("BLOG_P_CANCEL")?>" onclick="self.close()">
		</form>
		</html>
		<?
		if(strlen($_POST["do_upload"])>0)
		{
			?>
			<script>
				<?
				if(!empty($arResult["Image"]))
				{?>
				my_html = '<?=$arResult["ImageModified"]?>' +
					'<br /><input name=IMAGE_ID_title[<?=$arResult["Image"]["ID"]?>] value="<?=Cutil::JSEscape($arResult["Image"]["TITLE'"])?>" style="width:100px">' +
					'<br /><input type=checkbox name=IMAGE_ID_del[<?=$arResult["Image"]["ID"]?>] id=img_del_<?=$arResult["Image"]["ID"]?>> <label for=img_del_<?=$arResult["Image"]["ID"]?>><?=GetMessage("BLOG_DELETE")?></label>';
					
				if (!opener.document.getElementById('img_TABLE'))
				{
					main_table = opener.document.getElementById("main_table");
					tr_text = opener.document.getElementById("tr_TEXT");
					
					var oTR = main_table.insertRow(tr_text.rowIndex + 1);

					var oCell = opener.document.createElement("TH");
					oCell.vAlign = "top";
					oCell.align = "right";
					oCell.innerText = '<?=GetMessage("BLOG_P_IMAGES")?>';
					oCell.innerHTML = '<?=GetMessage("BLOG_P_IMAGES")?>';
					oTR.appendChild(oCell);

					oTD = oTR.insertCell(-1);
					oTD.innerHTML = '<table class="blog-blog-edit-table" id="img_TABLE"></table>';
				}

				imgTable = opener.document.getElementById('img_TABLE');

				if (imgTable.rows.length > 0)
				{
					oRow = imgTable.rows[imgTable.rows.length - 1];
					if (oRow.cells.length >= 4)
						oRow = imgTable.insertRow(-1);
				}
				else
					oRow = imgTable.insertRow(-1);
				
				oRow.vAlign = 'top';

				oCell = oRow.insertCell(-1);
				oCell.vAlign = 'top';
				oCell.innerHTML = my_html;
				
				<?
				if($_GET["htmlEditor"] == "Y")
				{
					?>
					var editorId = '<?=Cutil::JSEscape($_GET["editorId"])?>';
					if(editorId)
					{
						var pMainObj = window.opener.GLOBAL_pMainObj[editorId];
						if(pMainObj)
						{
							imageSrc = window.opener.document.getElementById(<?=$arResult["Image"]["ID"]?>).src;
							_str = '<img __bxtagname="blogImage" __bxcontainer="<?=$arResult["Image"]["ID"]?>" src="'+imageSrc+'">';
											
							pMainObj.insertHTML(_str);
							var i = window.opener.arImages.length++;
							window.opener.arImages[i] = '<?=$arResult["Image"]["ID"]?>';
						}
					}
					<?
				}
				else
				{
					?>
					opener.doInsert('[IMG ID=<?=$arResult["Image"]["ID"]?>]','',false);
					<?
				}
				}
				?>
				self.close();
			</script>
			<?
		}

		die();
	}
	elseif($_REQUEST["load_editor"] == "Y")
	{
		$APPLICATION->RestartBuffer();
		if(CModule::IncludeModule("fileman"))
		{
			?>
			<script language="JavaScript">
			<!--
			var arImages = Array();
			var arVideo = Array();
			var arVideoP = Array();
			var arVideoW = Array();
			var arVideoH = Array();
			<?
			$i = 0;
			foreach($arResult["Images"] as $aImg)
			{
				?>arImages['<?=$i?>'] = '<?=$aImg["ID"]?>';<?
				$i++;
			}

			$i = 0;
			preg_match_all("#\[video(.+?)\](.+?)\[/video[\s]*\]#ie", $arResult["PostToShow"]["~DETAIL_TEXT"], $matches);
			if(!empty($matches))
			{
				foreach($matches[0] as $key => $value)
				{
					if(strlen($value) > 0)
					{
						preg_match("#width=([0-9]+)#ie", $matches[1][$key], $width);
						preg_match("#height=([0-9]+)#ie", $matches[1][$key], $height);
						?>
						arVideo['<?=$i?>'] = '<?=CUtil::JSEscape($matches[0][$key])?>';
						arVideoP['<?=$i?>'] = '<?=CUtil::JSEscape($matches[2][$key])?>';
						arVideoW['<?=$i?>'] = '<?=IntVal($width[1])?>';
						arVideoH['<?=$i?>'] = '<?=IntVal($height[1])?>';
						<?
						$i++;
					}
				}
			}
			?>

			function BXDialogImageUpload()
			{
				BXDialogImageUpload.prototype._Create = function ()
				{
					jsUtils.OpenWindow('<?=$APPLICATION->GetCurPageParam("image_upload=Y")?>&editorId='+this.pMainObj.name+'&htmlEditor=Y', 400, 150);
				}
			}

			//-->
			</script>
			
			<?
			function CustomizeEditorForBlog()
			{
				?>
				<script>
				<!--
				function _blogImageLinkParser(_str)
				{
					for(var i=0, cnt = arImages.length; i<cnt; i++)
					{
						j = _str.indexOf("[IMG ID="+arImages[i]+"]");
						while(j > -1)
						{
							imageSrc = document.getElementById(arImages[i]).src;
							_str = _str.replace("[IMG ID="+arImages[i]+"]", '<img __bxtagname="blogImage" __bxcontainer="'+arImages[i]+'" src="'+imageSrc+'">');
							j = _str.indexOf("[IMG ID="+arImages[i]+"]");
						} 
					}
					
					for(var i=0, cnt = arVideo.length; i<cnt; i++)
					{
						j = _str.indexOf(arVideo[i]);
						while(j > -1)
						{
							_str = _str.replace(arVideo[i], '<img __bxtagname="blogVideo" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url('+document.getElementById('videoImg').src+'); background-position: center center; background-repeat: no-repeat; width: '+arVideoW[i]+'px; height: '+arVideoH[i]+'px;" __bxcontainer="'+arVideoP[i]+'" width="'+arVideoW[i]+'" height="'+arVideoH[i]+'" />');
							j = _str.indexOf(arVideo[i]);
						} 
					}
					return _str;
				}
				oBXEditorUtils.addContentParser(_blogImageLinkParser);

				function _blogImageLinkUnParser(_node)
				{
					if (_node.arAttributes["__bxtagname"] == "blogImage")
						return '[IMG ID='+_node.arAttributes["__bxcontainer"]+']';

					if (_node.arAttributes["__bxtagname"] == "blogVideo")
					{
						return '[video width='+_node.arAttributes["width"]+' height='+_node.arAttributes["height"]+']'+_node.arAttributes["__bxcontainer"]+'[/video]';
					}
					
					return false;
				}
				oBXEditorUtils.addUnParser(_blogImageLinkUnParser);
				
				arButtons['ImageLink']	=	[
						'BXButton',
						{
							src : document.getElementById('image-link').src,
							id : 'ImageLink',
							name : '<?=GetMessage("BLOG_P_IMAGE_LINK")?>',
							title : '<?=GetMessage("BLOG_P_IMAGE_LINK")?>',
							handler : function ()
							{
								this.pMainObj.CreateCustomElement("tag_image");
							}
						}
					];
				
				arButtons['image'][1].handler = function ()
					{
						this.bNotFocus = true;
						this.pMainObj.CreateCustomElement("BXDialogImageUpload");
					};
				arButtons['BlogInputVideo']	=	
					[
						'BXButton',
						{
							src : document.getElementById('videoImg').src,
							id : 'BlogInputVideo',
							name : '<?=GetMessage("FPF_VIDEO")?>',
							title : '<?=GetMessage("FPF_VIDEO")?>',
							handler : function ()
							{
								ShowVideoInput();
								
							}
						}
					];

				arButtons['BlogCUT']	=	
					[
						'BXButton',
						{
							src : document.getElementById('cutImg').src,
							id : 'BlogCUT',
							name : '<?=GetMessage("FPF_CUT")?>',
							title : '<?=GetMessage("FPF_CUT")?>',
							handler : function ()
							{
								this.pMainObj.insertHTML('[CUT]');
								
							}
						}
					];
					

				for(var i=0, cnt = arGlobalToolbar.length; i<cnt; i++)
				{
					if(arGlobalToolbar[i][1])
					{
						if(arGlobalToolbar[i][1].id == "image")
							imageID = i;						
						else if(arGlobalToolbar[i][1].id == "InsertHorizontalRule")
							cutID = i;
					}
				}

				if(imageID > 0)
				{
					tmpArray = arGlobalToolbar.slice(0, imageID).concat([arButtons['ImageLink']]);
					arGlobalToolbar = tmpArray.concat(arGlobalToolbar.slice(imageID));		
					imageID++;
					imageID++;
					
					tmpArray = arGlobalToolbar.slice(0, imageID).concat([arButtons['BlogInputVideo']]);
					arGlobalToolbar = tmpArray.concat(arGlobalToolbar.slice(imageID));	
				}
				if(cutID > 0)
				{
					tmpArray = arGlobalToolbar.slice(0, cutID).concat([arButtons['BlogCUT']]);
					arGlobalToolbar = tmpArray.concat(arGlobalToolbar.slice(cutID));					
				}										
				
				//-->
				</script>

				<?
			}

			AddEventHandler("fileman", "OnIncludeHTMLEditorScript", "CustomizeEditorForBlog");
			?>
			<script>
			jsUtils.addCustomEvent('EditorLoadFinish_POST_MESSAGE_HTML', BXBlogSetEditorContent);
			</script>
			<?

			CFileman::ShowHTMLEditControl("POST_MESSAGE_HTML", $arResult["PostToShow"]["~DETAIL_TEXT"], Array(
					"site" => SITE_ID,
					"templateID" => "",
					"bUseOnlyDefinedStyles" => "N",
					"bWithoutPHP" => true,
					"arToolbars" => Array("manage", "standart", "style", "formating", "source", "table"),
					"arTaskbars" => Array("BXPropertiesTaskbar"),
					"sBackUrl" => "",
					"fullscreen" => false,
					"path" => "",
					"limit_php_access" => true,
					'height' => '490',
					'width' => '100%',
					'light_mode' => true,
				));
		}
		else
		{
			ShowError(GetMessage("FILEMAN_MODULE_NOT_INSTALL"));
		}
		die();
	}
	else
	{
		include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");
		
		if($arResult["preview"] == "Y" && !empty($arResult["PostToShow"])>0)
		{
			echo "<span class=\"blogtext\"><b>".GetMessage("BLOG_PREVIEW_TITLE")."</b></span>";
			?>
			<table class="blog-table-post">
			<tr>
				<th nowrap width="100%">
					<table width="100%" cellspacing="0" cellpadding="0" border="0" class="blog-table-post-table">
					<tr>
						<td align="left">
							<span class="blog-post-date"><b><?=$arResult["postPreview"]["DATE_PUBLISH_FORMATED"]?></b></span>&nbsp;
						</td>
						<td align="right" nowrap>
							<table width="0%" class="blog-table-post-table">
							<tr>
								<td>
									<div class="blog-user"></div>
								</td>
								<td>
									<b><div class="blog-author"><?=$arResult["postPreview"]["AuthorName"]?></div></b>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</table>
				</th>
			</tr>
			<tr>
				<td>
					<?=$arResult["postPreview"]["BlogUser"]["AVATAR_img"]?>
					<?=$arResult["postPreview"]["textFormated"]?>
					<br clear="all" />
					<?if(!empty($arResult["postPreview"]["Category"]))
					{
						?>
						<div class="blog-line"></div>
						<?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?>
						<?$i=0;
						foreach($arResult["postPreview"]["Category"] as $v)
						{
							if($i!=0)
								echo ",";
							?> <?=$v["NAME"]?><?
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

		?>
		<form action="<?=POST_FORM_ACTION_URI?>" name="REPLIER" method="post" enctype="multipart/form-data" onmouseover="check_ctrl_enter">
		<?=bitrix_sessid_post();?>
			<table class="blog-blog-edit" id="main_table">
			<tr>
				<th width="1%" valign="top" align="right" nowrap><span class="blog-req">*</span> <b><?=GetMessage("BLOG_TITLE")?></b></th>
				<td>
					<input tabindex="1" type="text" name="POST_TITLE" value="<?=$arResult["PostToShow"]["TITLE"]?>" style="width:98%">
				</td>
			</tr>
			<tr id="tr_TEXT">
				<th valign="top" align="right" nowrap><b><?=GetMessage("BLOG_TEXT")?></b></th>
				<td class="blog-detail-text">
					<div id="edit-post-text" style="display:none;">
						<table class="blog-blog-edit-table">
						<tr>
							<td colspan="2">
								<table class="blog-blog-edit-table" cellspacing="0">
								<tr style="background-image:url(<?=$templateFolder?>/images/toolbarbg.gif);">
									<td>
									<select name="ffont" id="select_font" onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')">
											<option value="0"><?=GetMessage("FPF_FONT")?></option>
											<option value="Arial" style="font-family:Arial">Arial</option>
											<option value="Times" style="font-family:Times">Times</option>
											<option value="Courier" style="font-family:Courier">Courier</option>
											<option value="Impact" style="font-family:Impact">Impact</option>
											<option value="Geneva" style="font-family:Geneva">Geneva</option>
											<option value="Optima" style="font-family:Optima">Optima</option>
											<option value="Verdana" style="font-family:Verdana">Verdana</option>
									</select>
									</td>
									<td nowrap>
										&nbsp;<a id="FontColor" class="blogButton" href="javascript:ColorPicker()"><img class="blogButton" src="<?=$templateFolder?>/images/font_color.gif" width="20" height="20" title="<?echo GetMessage("FPF_IMAGE")?>"></a>
										<a class="blogButton" href="javascript:simpletag('B')"><img class="blogButton" src="<?=$templateFolder?>/images/bold.gif" width="20" height="20" title="<?echo GetMessage("FPF_BOLD")?>"></a>
										<a class="blogButton" href="javascript:simpletag('I')"><img class="blogButton" src="<?=$templateFolder?>/images/italic.gif" width="20" height="20" title="<?echo GetMessage("FPF_ITALIC")?>"></a>
										<a class="blogButton" href="javascript:simpletag('U')"><img class="blogButton" src="<?=$templateFolder?>/images/under.gif" width="20" height="20" title="<?echo GetMessage("FPF_UNDER")?>"></a>
										<a class="blogButton" href="javascript:tag_url()"><img class="blogButton" src="<?=$templateFolder?>/images/link.gif" width="20" height="20" title="<?echo GetMessage("FPF_HYPERLINK")?>"></a>
										<a class="blogButton" href="javascript:tag_image()"><img class="blogButton" src="<?=$templateFolder?>/images/image_link.gif" width="20" height="20" title="<?=GetMessage("BLOG_P_IMAGE_LINK")?>" id="image-link"></a>
										<a class="blogButton" href="javascript:ShowImageUpload()"><img class="blogButton" src="<?=$templateFolder?>/images/image.gif" width="20" height="20" title="<?=GetMessage("BLOG_P_DO_UPLOAD")?>" id="image-upload"></a>
										<a class="blogButton" href="javascript:ShowVideoInput()"><img class="blogButton" src="<?=$templateFolder?>/images/video.gif" width="20" height="20" title="<?=GetMessage("FPF_VIDEO")?>" id="videoImg"></a>
										<a class="blogButton" href="javascript:quoteMessage()"><img class="blogButton" src="<?=$templateFolder?>/images/quote.gif" width="20" height="20" title="<?echo GetMessage("FPF_QUOTE")?>"></a>
										<a class="blogButton" href="javascript:simpletag('CODE')"><img class="blogButton" src="<?=$templateFolder?>/images/code.gif" width="20" height="20" title="<?echo GetMessage("FPF_CODE")?>"></a>
										<a class="blogButton" href="javascript:tag_list()"><img class="blogButton" src="<?=$templateFolder?>/images/list.gif" width="20" height="20" title="<?echo GetMessage("FPF_LIST")?>"></a>
										<a class="blogButton" href="javascript:void(0)" onclick="doInsert('[CUT]', '', false)"><img class="blogButton" src="<?=$templateFolder?>/images/cut.gif" width="20" height="20" title="<?echo GetMessage("FPF_CUT")?>" id="cutImg"></a>
									</td>
									<td width="100%" align="right"><a id="close_all" style="visibility:hidden" class="blogButton" href="javascript:closeall()" title="<?=GetMessage("FPF_CLOSE_OPENED_TAGS")?>"><?=GetMessage("FPF_CLOSE_ALL_TAGS")?></a>
									</td>
								</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td width="100%"><textarea tabindex="2" name="POST_MESSAGE" style="width:99%" rows="15" id="MESSAGE" onKeyPress="check_ctrl_enter(arguments[0])"><?=$arResult["PostToShow"]["DETAIL_TEXT"]?></textarea></td>
							<td valign="middle">
								<table class="blog-blog-edit-table-smiles">
									<?
									$ind = 0;
									foreach($arResult["Smiles"] as $arSmiles)
									{
										if ($ind == 0)
											echo "<tr>";
										?>
											<td align="center"><img src="/bitrix/images/blog/smile/<?=$arSmiles["IMAGE"]?>" width="<?=$arSmiles["IMAGE_WIDTH"]?>" height="<?=$arSmiles["IMAGE_HEIGHT"]?>" title="<?=$arSmiles["LANG_NAME"]?>" OnClick="emoticon('<?=$arSmiles["TYPE"]?>')" style="cursor:pointer"></td>
										<?
										if($ind == 1)
										{
											echo "</tr>";
											$ind = 0;
										}
										else
											$ind++;
									}
									if($ind!=1)
										echo "</tr>";
									?>
								</table>
							</td>
						</table>
					</div>
				</td>
			</tr>
			<script>
			<!--
			showEditField('text', 'N');
			//-->
			</script>
			<?
			if (!empty($arResult["Images"]))
			{
				?>
				<tr>
					<th valign="top" align="right" nowrap>
						<b><?=GetMessage("BLOG_P_IMAGES")?></b>
					</th>
					<td>
						<table class="blog-blog-edit-table" id="img_TABLE">
						<?
						$i=0;
						foreach($arResult["Images"] as $aImg)
						{
							if ($i==0)
								print "<tr>";
							?>
								<td valign="top">
									<?=$aImg["FileShow"]?><br />
									<input name="IMAGE_ID_title[<?=$aImg["ID"]?>]" value="<?=$aImg["TITLE"]?>" style="width:100px;" title="<?=GetMessage("BLOG_BLOG_IN_IMAGES_TITLE")?>"><br />
									<input type="checkbox" name="IMAGE_ID_del[<?=$aImg["ID"]?>]" id="img_del_<?=$aImg["ID"]?>"> <label for="img_del_<?=$aImg["ID"]?>"><?=GetMessage("BLOG_DELETE")?></label>
								</td>
							<?
							if($i == 3)
							{
								echo "</tr>";
								$i = 0;
							}
							else
								$i++;
						}
						if($i!=3)
							echo "</tr>";
						?>
						</table>
					</td>
				</tr>
				<?
			}
			?>
				
			<tr>
				<th valign="top" align="right" nowrap>
				<b><?=GetMessage("BLOG_STATUS")?></b></th>
				<td>
					<select name="PUBLISH_STATUS">
						<option value="<?=BLOG_PUBLISH_STATUS_DRAFT?>"<?=($arResult["PostToShow"]["PUBLISH_STATUS"]==BLOG_PUBLISH_STATUS_DRAFT ? " selected" : "")?>><?=$GLOBALS["AR_BLOG_PUBLISH_STATUS"][BLOG_PUBLISH_STATUS_DRAFT]?></option>
						<option value="<?=BLOG_PUBLISH_STATUS_PUBLISH?>"<?=($arResult["PostToShow"]["PUBLISH_STATUS"]==BLOG_PUBLISH_STATUS_DRAFT ? "" : " selected")?>><?=$GLOBALS["AR_BLOG_PUBLISH_STATUS"][BLOG_PUBLISH_STATUS_PUBLISH]?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th valign="top" align="right" nowrap><sup>1</sup>
				<b><?=GetMessage("BLOG_FAVORITE_SORT")?></b><br /></th>
				<td>
					<input name="FAVORITE_SORT" type="text" value="<?=$arResult["PostToShow"]["FAVORITE_SORT"]?>">
				</td>
			</tr>
			<tr>
				<th valign="top" align="right" nowrap><b><?=GetMessage("BLOG_CATEGORY")?></b></th>
				<td>
					<?/*
					<select name="CATEGORY_ID[]" id="CATEGORY_ID" multiple="multiple" size="5">
						<option value="0"<?=(IntVal($arResult["PostToShow"]["CATEGORY_ID"])==0 ? " selected" : "")?>><?=GetMessage("BLOG_NO_CATEGORY")?></option>
						<?
						foreach($arResult["Category"] as $arCategory)
						{
							if($arCategory["Selected"]=="Y")
								$value .= $arCategory["~NAME"].", ";
							?>
							<option value="<?=$arCategory["ID"]?>"<?=($arCategory["Selected"]=="Y" ? " selected" : "")?>><?=$arCategory["NAME"]?></option>
							<?
						}
						$value = substr($value, 0, strlen($value)-2);
						?>
					</select>
					
					<br />
					<div id="category-add"><a href="javascript:AddCategory();" class="blog-category-add" title="<?=GetMessage("BLOG_CATEGORY_ADD")?>"></a>&nbsp;<a href="javascript:AddCategory();" title="<?=GetMessage("BLOG_CATEGORY_ADD")?>"><?=GetMessage("BLOG_CATEGORY_ADD")?></a></div>
					*/?>
					<div id="category-new">
						<?
						if(IsModuleInstalled("search"))
						{
							$APPLICATION->IncludeComponent("bitrix:search.tags.input", ".default", Array(
								"NAME"	=>	"TAGS",
								"VALUE"	=>	$arResult["PostToShow"]["CategoryText"],
								"arrFILTER"	=>	"blog",
								//"arrFILTER_blog"	=>	$arResult["Blog"]["ID"],
								"PAGE_ELEMENTS"	=>	"10",
								"SORT_BY_CNT"	=>	"Y",
								"TEXT" => 'size="30"'
								)
							);
						}
						else
						{
							?><input type="text" name="TAGS" value="">
							<?
						}?>
						<!--<input type="button" name="ok" OnClick="AddCategoryToList()" value="<?=GetMessage("BLOG_CATEGORY_ADD")?>">//-->
					</div>

					
				</td>
			</tr>
			<tr>
				<th valign="top" align="right" nowrap> <b><?=GetMessage("BLOG_DATE_PUBLISH")?></b></th>
				<td nowrap>
				<?
					$APPLICATION->IncludeComponent(
						'bitrix:main.calendar',
						'',
						array(
							'SHOW_INPUT' => 'Y',
							'FORM_NAME' => 'REPLIER',
							'INPUT_NAME' => 'DATE_PUBLISH',
							'INPUT_VALUE' => $arResult["PostToShow"]["DATE_PUBLISH"],
							'SHOW_TIME' => 'Y'
						),
						null,
						array('HIDE_ICONS' => 'Y')
					);
				?>
				</td>
			</tr>

			<?if($arResult["enable_trackback"] == "Y")
			{
				?>
				<tr>
					<th valign="top" align="right" nowrap><b><?=GetMessage("BLOG_ADDRESSES")?></b></th>
					<td><textarea name="TRACKBACK" style="width:99%" rows="5"><?=$arResult["PostToShow"]["TRACKBACK"]?></textarea></tr>
				<tr>
					<th valign="top" align="right" nowrap><b>Trackback:</b></th>
					<td>
						<input type="checkbox" name="ENABLE_TRACKBACK" value="Y" id="enable_tb"<?=($arResult["PostToShow"]["ENABLE_TRACKBACK"]=="Y" ? " checked" : "")?>>
						<label for="enable_tb"><?=GetMessage("BLOG_ALLOW_TRACKBACK")?></label>
					</td>
				</tr>
				<?
			}
			?>
			<?if($arResult["POST_PROPERTIES"]["SHOW"] == "Y"):?>
				<?foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
				<tr>
					<th><b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b></th>
					<td>
							<?$APPLICATION->IncludeComponent(
								"bitrix:system.field.edit", 
								$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
								array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
					</td>
				</tr>			
				<?endforeach;?>
			<?endif;?>
			<?if($USER->IsAdmin()):?>
				<tr>
					<td colspan="2"><a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=BLOG_POST&back_url=<?=$arResult["CUR_PAGE"]?>"><?=GetMessage("BLOG_POST_PROPERTY_ADD")?></a></th>
				</tr>
			<?endif;?>
			</table>
			<?
//			userconsent only for once for registered early users
			if ($arParams['USER_CONSENT'] == 'Y' && !$arParams['USER_CONSENT_WAS_GIVEN'])
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
							'fields' => array('Alias', 'Personal site', 'Birthday', 'Photo')
						),
					)
				);
			}
			?>
			<br />
			<input type="hidden" name="save" value="Y">
			<input tabindex="3" type="submit" name="save" value="<?=GetMessage("BLOG_SAVE")?>">

			<input type="submit" name="apply" value="<?=GetMessage("BLOG_APPLY")?>">
			<input type="submit" name="preview" value="<?=GetMessage("BLOG_PREVIEW")?>">
			<input type="submit" name="reset" value="<?=GetMessage("BLOG_CANCEL")?>">
		</form>
		<br />
		<span class="blogtext">
		<?echo GetMessage("FPF_TO_QUOTE_NOTE")?><br />
		<?echo GetMessage("STOF_REQUIED_FIELDS_NOTE")?><br />
		<sup>1</sup> - <?=GetMessage("BLOG_FAVORITE_SORT_HINT")?>
		</span>
		<script>
		<!--
		document.REPLIER.POST_TITLE.focus();
		//-->
		</script>
		<?
	}
}
?>