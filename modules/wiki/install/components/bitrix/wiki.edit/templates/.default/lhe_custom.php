<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(CModule::IncludeModule('fileman')):
	AddEventHandler('fileman', 'OnIncludeLightEditorScript', 'CustomizeLightEditorForWiki');
	function CustomizeLightEditorForWiki()
	{
		?>
		<script>
		window.LHEButtons['Category'] = {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/category.gif',
			id : 'Category',
			name : '<?=GetMessage('WIKI_BUTTON_CATEGORY')?>',
			title : '<?=GetMessage('WIKI_BUTTON_CATEGORY')?>',
			handler : function (p)
			{
				this.bNotFocus = true;
				wikiMainEditor.ShowCategoryInsert();
			}
		};

		window.LHEButtons['ImageUpload'] = {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/image.gif',
			id : 'ImageUpload',
			name : '<?=GetMessage('WIKI_BUTTON_IMAGE_UPLOAD')?>',
			title : '<?=GetMessage('WIKI_BUTTON_IMAGE_UPLOAD')?>',
			handler : function (p)
			{
				this.bNotFocus = true;
				wikiMainEditor.ShowImageUpload();
			}
		};
		window.LHEButtons['ImageLink'] = {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/image_upload.gif',
			id : 'ImageLink',
			name : '<?=GetMessage('WIKI_BUTTON_IMAGE_LINK')?>',
			title : '<?=GetMessage('WIKI_BUTTON_IMAGE_LINK')?>',
			handler : function (p)
			{
				this.bNotFocus = true;
				wikiMainEditor.ShowImageInsert();
			},
			parser : {
				name: 'wiki_img',
				obj: {
					Parse: function(sName, sContent, pLEditor)
					{
						sContent = sContent.replace(/\[?\[((File|<?=GetMessage('FILE_NAME');?>):(.+?))\]\]?/ig, function(s, s1, s2, f)
						{
							var imageSrc = false;
							var _imgStyle = '';
							var id = 0;
							if (f.indexOf('http://') == 0)
								imageSrc = f;
							else
							{
								if (isFinite(f) && BX(f))
								{
									id = f;
									imageSrc = BX(f).src;
								}
								else
								{
									for (var i in wikiMainEditor.arWikiImg)
									{
										if (wikiMainEditor.arWikiImg[i] == f)
										{
											id = i;
											imageSrc = BX(id).src;
											break;
										}
									}
								}
								if (!imageSrc)
									return s;

								var lgi = new Image();
								lgi.src = imageSrc;
								var _imgWidth = lgi.width;

								if (_imgWidth > <?=COption::GetOptionString('wiki', 'image_max_width', 600);?>)
									_imgStyle += 'width: <?=COption::GetOptionString('wiki', 'image_max_width', 600);?>;';

							}

							if (imageSrc)
								return  '<img id="' + pLEditor.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : id, 'file_name' : f}}) + '" \
									src="'+imageSrc+'" style="'+_imgStyle+'" />';
							else
								return s;
						});
						return sContent;

					},
					UnParse: function(bxTag, pNode, pLEditor)
					{
						if (bxTag && bxTag.tag && bxTag.tag == "wiki_img")
						{
							return '[<?=GetMessage('FILE_NAME');?>:'+bxTag.params.file_name+']';
						}
						return '';
					}
				}
			}
		};
		window.LHEButtons['Signature']	= {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/signature.gif',
			id : 'Signature',
			name : '<?=GetMessage('WIKI_BUTTON_SIGNATURE')?>',
			title : '<?=GetMessage('WIKI_BUTTON_SIGNATURE')?>',
			handler : function (p)
			{
				wikiMainEditor.wiki_signature();
			}
		};

		window.LHEButtons['intenalLink'] = {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/link.gif',
			id : 'intenalLink',
			name : '<?=GetMessage('WIKI_BUTTON_HYPERLINK')?>',
			title : '<?=GetMessage('WIKI_BUTTON_HYPERLINK')?>',
			handler : function (p)
			{
				this.bNotFocus = true;
				wikiMainEditor.ShowInsertLink(false);
			}
		};

		window.LHEButtons['externalLink'] = {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/external_link.gif',
			id : 'externalLink',
			name : '<?=GetMessage('WIKI_BUTTON_EXTERNAL_HYPERLINK')?>',
			title : '<?=GetMessage('WIKI_BUTTON_EXTERNAL_HYPERLINK')?>',
			handler : function (p)
			{
				this.bNotFocus = true;
				ShowInsertLink(true);
			}
		};

		window.LHEButtons['nowiki'] = {
			src : '/bitrix/components/bitrix/wiki.edit/templates/.default/images/wcode/nowiki.gif',
			id : 'nowiki',
			name : '<?=GetMessage('WIKI_BUTTON_NOWIKI')?>',
			title : '<?=GetMessage('WIKI_BUTTON_NOWIKI')?>',
			handler : function (p)
			{
				var
					pElement = p.pLEditor.GetSelectionObjects(true),
					bFind = false, st;

				while(!bFind)
				{
					if (!pElement)
						break;

					if (pElement.nodeType == 1)
					{
						var bxTag = p.pLEditor.GetBxTag(pElement.id);
						if (bxTag && bxTag.tag && bxTag.tag == "wiki_no")
							bFind = true;
						else
							pElement = pElement.parentNode;
					}
					else
						pElement = pElement.parentNode;
				}

				if (bFind)
				{
					pElement.style.border = "";
					p.pLEditor.RidOfNode(pElement, true);
					this.Check(false);
				}
				else
				{
					p.pLEditor.WrapSelectionWith("span", {props:{id: p.pLEditor.SetBxTag(false, {'tag': 'wiki_no', 'params': {}})},
															style: {border : "1px dashed grey"}});
					//p.pLEditor.OnEvent("OnSelectionChange");
				}
			},
			OnSelectionChange: function (p) // ???
			{
				var
					pElement = p.pLEditor.GetSelectedNode(true),
					bFind = false, st;

				while(!bFind)
				{
					if (!pElement)
						break;

					if (pElement.nodeType == 1)
					{
						var bxTag = this.pMainObj.GetBxTag(pElement.id);
						if (bxTag && bxTag.tag && bxTag.tag == "wiki_no")
						{
							bFind = true;
							break;
						}
						else
							pElement = pElement.parentNode;
					}
					else
						pElement = pElement.parentNode;
				}

				this.Check(bFind);
			},
			parser : {
				name: 'wiki_no',
				obj: {
					Parse: function(sName, sContent, pLEditor)
					{
						sContent = sContent.replace(/<nowiki>(.*?)<\/nowiki>/igm, '<span style="border: 1px dashed grey" id="' + pLEditor.SetBxTag(false, {'tag': 'wiki_no', 'params': {}}) + '" >$1</span>');
						return sContent;
					},
					UnParse: function(bxTag, pNode, pLEditor)
					{

						if (bxTag && bxTag.tag && bxTag.tag == "wiki_no")
						{
							var res = "", i;
							for (var i = 0; i < pNode.arNodes.length; i++)
							res += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);


							return '<NOWIKI>'+res+'</NOWIKI>';
						}
						return '';
					}
				}
			}
		}

		</script>
		<?
	}
	?>
	<script>

	</script>
	<?

	$ar = array(
		'width' => '100%',
		'height' => '300',
		'inputName' => 'POST_MESSAGE_HTML',
		'inputId' => 'POST_MESSAGE_HTML',
		'id' => 'pLEditorWiki',
		'jsObjName' => 'pLEditorWiki',
		'content' => CWikiParser::Clear($arResult['ELEMENT']['~DETAIL_TEXT']),
		'bUseFileDialogs' => false,
		'bFloatingToolbar' => false,
		'bArisingToolbar' => false,
		'bResizable' => true,
		'bSaveOnBlur' => true,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', /*'RemoveFormat',*/
			/*'Header'*/ 'HeaderList', 'intenalLink', 'Category',
			'Signature', 'nowiki', 'CreateLink' /*'externalLink'*/ , 'DeleteLink', 'ImageLink', 'ImageUpload', 'Table',
			'BackColor', 'ForeColor',
			'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
			'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent','Code'
		)
	);
	$LHE = new CLightHTMLEditor;
	$LHE->Show($ar);

else:
	ShowError(GetMessage('FILEMAN_MODULE_NOT_INSTALLED'));

endif;