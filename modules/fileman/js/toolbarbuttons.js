var arButtons = Array();
arButtons['separator']	=	'separator';
arButtons['Fullscreen']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/fullscreen.gif',		'name': 'Fullscreen', 	'title': 'Fullscreen', 'codeEditorMode': true, 'handler': function() {
	if(this.pMainObj.bFullscreen)
		this.pMainObj.SetFullscreen(false);
	else
		this.pMainObj.SetFullscreen(true);
	this.Check(this.pMainObj.bFullscreen);
	}}];


arButtons['Cut']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/cut.gif',	'name': BX_MESS.Cut, 	'cmd': 'Cut'}];
arButtons['Copy']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/copy.gif',	'name': BX_MESS.Copy, 'cmd': 'Copy'}];
arButtons['Paste']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/paste.gif',	'name': BX_MESS.Paste, 'cmd': 'Paste'}];

arButtons['RemoveFormat']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/removeformat.gif',	'name': BX_MESS.RemoveFormat, 'cmd': 'RemoveFormat'}];
arButtons['Undo']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/undo.gif',	'name': 'Undo', 'title': BX_MESS.Undo, 'cmd': 'Undo'}];
arButtons['Redo']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/redo.gif',	'name': 'Redo', 'title': BX_MESS.Redo, 'cmd': 'Redo'}];
arButtons['borders']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/borders.gif',	'name': BX_MESS.BordersTitle, 'handler': function (){this.pMainObj.ShowTableBorder(!this.pMainObj.bTableBorder);this.Check(this.pMainObj.bTableBorder);}}];
arButtons['spellcheck']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/spellcheck.gif', 'name': BX_MESS.Spellcheck, 'handler': function (){}}];
arButtons['find']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/find.gif',	'name': BX_MESS.Find}];
arButtons['replace']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/replace.gif',	'name': BX_MESS.Replace}];

arButtons['pastetext']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/pastetext.gif', 'name': BX_MESS.PasteAsText,
				'handler': function ()
				{
					if(BXIsIE())
						this.pMainObj.PasteAsText(clipboardData.getData("Text"));
					else
					{
						this.bNotFocus = true;
						this.pMainObj.CreateCustomElement("BXDialog",
							{
								"width":"450",
								"height":"350",
								"name":"pasteastext",
								"params":{}
							});
					}
				}
	}];

arButtons['pasteword']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/pasteword.gif', 'name': BX_MESS.PasteAsWord,
				'handler': function ()
				{
					this.bNotFocus = true;
					this.pMainObj.CreateCustomElement("BXDialog",
						{
							"width":"450",
							"height":"450",
							"name":"pasteword",
							"params":{}
						});
				}
	}];

arButtons['new']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/new.gif', 'codeEditorMode': true, 'name': BX_MESS.TBNewPage,
							'handler': function ()
							{
								if(this.pMainObj.bNotSaved && !confirm("Документ был изменен. При создании нового документа, все изменения будут утеряны. Продолжить?"))
									return;

							}
						}];
arButtons['settings']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/settings.gif', 'name': BX_MESS.TBSettings}];
arButtons['exit']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/exit.gif', 'codeEditorMode': true, 'name': BX_MESS.TBExit,
							'handler': function ()
							{
								if(this.pMainObj.bNotSaved)
								{
									this.bNotFocus = true;
									this.pMainObj.CreateCustomElement("BXDialog",
										{
											"width":"600",
											"height":"180",
											"name":"asksave",
											"not_use_default": "Y",
											"params":{'window': window}
										});
								}
								else if(this.pMainObj.arConfig["sBackUrl"])
									window.location = this.pMainObj.arConfig["sBackUrl"];
							}
						}];

arButtons['saveas']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/saveas.gif', 'codeEditorMode': true, 'name': BX_MESS.TBSaveAs}];

arButtons['save']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/save.gif', 'codeEditorMode': true, 'name': BX_MESS.TBSave,
							'handler': function ()
							{
								this.pMainObj.SaveContent(true);
								this.pMainObj.pForm.submit();
							}
						}];


arButtons['pageprops']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/pageprops.gif', 'codeEditorMode': true, 'name': BX_MESS.TBProps,
							'handler': function ()
							{
								if(this.pMainObj.bNotSaved)
								{
									this.bNotFocus = true;
									this.pMainObj.CreateCustomElement("BXDialog",
										{
											"width":"600",
											"height":"180",
											"name":"pageprops",
											"params":{'window': window}
										});
								}
							}
						}];


arButtons['insertrow']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/insertrow.gif','name': BX_MESS.TBInsRow, 'handler': function (){this.pMainObj.TableOperation('insertrow');}}];
arButtons['deleterow']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/deleterow.gif','name': BX_MESS.TBDelRow, 'handler': function (){this.pMainObj.TableOperation('deleterow');}}];
arButtons['insertcolumn']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/insertcolumn.gif','name': BX_MESS.TBInsCol, 'handler': function (){this.pMainObj.TableOperation('insertcolumn');}}];
arButtons['deletecolumn']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/deletecolumn.gif','name': BX_MESS.TBDelCol, 'handler': function (){this.pMainObj.TableOperation('deletecolumn');}}];
arButtons['insertcell']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/insertcell.gif','name': BX_MESS.TBInsCell, 'handler': function (){this.pMainObj.TableOperation('insertcell');}}];
arButtons['deletecell']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/deletecell.gif','name': BX_MESS.TBDellCell, 'handler': function (){this.pMainObj.TableOperation('deletecell');}}];
arButtons['mergecell']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/mergecell.gif','name': BX_MESS.TBMergeCell, 'handler': function (){this.pMainObj.TableOperation('mergecell');}}];
arButtons['splitcell']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/splitcell.gif','name': BX_MESS.TBSplitCell, 'handler': function (){this.pMainObj.TableOperation('splitcell');}}];

arButtons['table']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/instable.gif',
							'name': BX_MESS.TBInsTable,
							'title': BX_MESS.TBInsTable,
							'handler': function ()
							{
								this.bNotFocus = true;
								this.pMainObj.CreateCustomElement("BXDialog",
									{
										"width":"450",
										"height":"260",
										"name":"table",
										"params":{}
									});
							}
						}];

arButtons['tableprop']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/instable.gif','name': BX_MESS.TBTableProp, 'title': BX_MESS.TBTableProp,
							'handler': function ()
							{
								this.bNotFocus = true;
								this.pMainObj.CreateCustomElement("BXDialog",
									{
										"width":"450",
										"height":"260",
										"name":"table",
										"params":{'check_exists':true}
									});
							}
						}];

arButtons['CreateLink']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/link.gif', 'name': BX_MESS.TBLink, 'title': BX_MESS.TBLink,
							'handler': function ()
							{
								this.bNotFocus = true;
								this.pMainObj.CreateCustomElement("BXDialog",
									{
										"width":"450",
										"height":"380",
										"name":"link",
										"params":{}
									});
							}
						}];
arButtons['deletelink']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/dellink.gif',	'name': BX_MESS.TBDelLink, 'cmd': 'Unlink',
							'handler': function()
							{
								var pElement = BXFindParentByTagName(this.pMainObj.GetSelectionObject(), 'A');
								if(pElement)
								{
									this.pMainObj.SelectElement(pElement);
									this.pMainObj.executeCommand('Unlink');
								}
							}
						}];
arButtons['anchor']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/anchor.gif',	'name': 'Anchor', 'title': BX_MESS.TBAnchor,
							'handler': function ()
							{
								this.bNotFocus = true;
								this.pMainObj.CreateCustomElement("BXDialog",
									{
										"width":"300",
										"height":"150",
										"name":"anchor",
										"params":{}
									});
							}
						}];
arButtons['image']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/image.gif',	'name': BX_MESS.TBImg,
							'handler': function ()
							{
								this.bNotFocus = true;
								this.pMainObj.CreateCustomElement("BXDialog",
									{
										"width":"400",
										"height":"350",
										"name":"image",
										"params":{}
									});
							}
						}];
arButtons['BackColor']	=	['BXColorPicker', {'title': 'Background color', 'icon': '/bitrix/images/fileman/htmledit2/bgcolor.gif',
			'onChange': function (color)
			{
				if(BXIsIE())
					this.pMainObj.executeCommand('BackColor', color);
				else
					this.pMainObj.executeCommand('hilitecolor', color);
			}
		}];
arButtons['ForeColor']	=	['BXColorPicker', {'title': 'Foreground Color', 'icon': '/bitrix/images/fileman/htmledit2/fgcolor.gif',
			'onChange': function (color)
			{
				this.pMainObj.executeCommand('ForeColor', color);
			}/*,
			'OnSelectionChange': function ()
			{
				var val;
				val = this.pMainObj.queryCommand('ForeColor');
				this.SetValue(val);
			}*/
	}];
arButtons['Bold']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/bold.gif',	'name': BX_MESS.TBBold,  'cmd': 'Bold'}];
arButtons['Italic']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/italic.gif',	'name': BX_MESS.TBItalic,  'cmd': 'Italic'}];
arButtons['Underline']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/under.gif',	'name': BX_MESS.TBUnderline, 'cmd': 'Underline'}];
arButtons['Outdent']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/deindent.gif','name': BX_MESS.TBOutdent, 'cmd': 'Outdent'}];
arButtons['Indent']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/inindent.gif','name': BX_MESS.TBIndent, 'cmd': 'Indent'}];
arButtons['JustifyLeft']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/left.gif','name': BX_MESS.TBJLeft, 'cmd': 'JustifyLeft'}];
arButtons['JustifyCenter']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/center.gif',	'name': BX_MESS.TBJCent, 'cmd': 'JustifyCenter'}];
arButtons['JustifyRight']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/right.gif',	'name': BX_MESS.TBJRig, 'cmd': 'JustifyRight'}];
arButtons['JustifyFull']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/justify.gif',	'name': BX_MESS.TBJFull, 'cmd': 'JustifyFull'}];
arButtons['InsertOrderedList']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/numlist.gif',	'name': BX_MESS.TBOList, 'cmd': 'InsertOrderedList'}];
arButtons['InsertUnorderedList']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/bullist.gif',	'name': BX_MESS.TBUnOList, 'cmd': 'InsertUnorderedList'}];
arButtons['InsertHorizontalRule']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/hr.gif','name': BX_MESS.TBHr, 'cmd': 'InsertHorizontalRule'}];

__BXSrcBtn = function (mode, split_mode)
{
	this.Check(mode==this.t);
	this._OnChangeView(mode, split_mode);
}

arButtons['source']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/text.gif',	'codeEditorMode': true, 'name': BX_MESS.TBSrc, 't': 'code', 'OnChangeView': __BXSrcBtn, 'handler': function (){this.pMainObj.SetView('code');}}];
arButtons['wysiwyg']	=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/wysiwyg.gif',	'codeEditorMode': true, 'name': BX_MESS.TBWysiwyg, 't': 'html', 'OnChangeView': __BXSrcBtn, 'handler': function (){this.pMainObj.SetView('wysiwyg');}}];
arButtons['split']		=	['BXButton', {'src': '/bitrix/images/fileman/htmledit2/split.gif',	'codeEditorMode': true, 'name': BX_MESS.TBSplitmode, 't': 'split', 'OnChangeView': __BXSrcBtn, 'handler': function (){this.pMainObj.SetView('split');}}];

arButtons['HeadingList']	=
	['BXList',
		{'width': '150', 'height': '150', 'field_size': '70', 'title': '(format)', 'disableOnCodeView': true,
			'values':
			[
				{'value': 'p', 'name': 'Normal'},
				{'value': 'h1', 'name': 'Heading 1'},
				{'value': 'h2', 'name': 'Heading 2'},
				{'value': 'h3', 'name': 'Heading 3'},
				{'value': 'h4', 'name': 'Heading 4'},
				{'value': 'h5', 'name': 'Heading 5'},
				{'value': 'h6', 'name': 'Heading 6'},
				{'value': 'pre', 'name': 'Preformatted'}
			],
			'OnSelectionChange': function (){
					var sel = 0;
					var frm = this.pMainObj.queryCommand('FormatBlock');
					if(frm)
					{
						var re = /[1-6]/;
						var r = frm.match(re);
						if(r>0)
							sel = r;
						else if(frm == 'pre')
							sel = 7;
					}

					this.Select(sel);
				},
			'onChange': function (selected){this.pMainObj.executeCommand('FormatBlock', (selected['value'].length>0?'<' + selected['value']+'>':'<p>'));},
			'onDrawItem': function (item){return (item['value'].length<=0?item['name']:'<'+item['value']+'>'+item['name']+'</'+item['value']+'>');}
		}
	];

arButtons['FontName']	=
	['BXList',
		{'width': '160', 'height': '120', 'field_size': '70', 'title': '(font)', 'disableOnCodeView': true,
			'values':
			[
				{'value': 'Times New Roman', 'name': 'Times New Roman'},
				{'value': 'Courier', 'name': 'Courier'},
				{'value': 'Arial', 'name': 'Arial'},
				{'value': 'Tahoma', 'name': 'Tahoma'},
				{'value': 'Verdana', 'name': 'Verdana'},
				{'value': 'Georgia', 'name': 'Georgia'}
			],
			'OnSelectionChange': function (){
					this.SelectByVal(this.pMainObj.queryCommand('FontName'));
				},
			'onChange': function (selected){this.pMainObj.executeCommand('FontName', selected['value']);},
			//text-overflow : ellipsis;
			'onDrawItem': function (item){return '<span style="white-space: nowrap; font-family:'+item['name']+';font-size: 10pt;">'+item['name']+'</span>';}
		}
	];


arButtons['FontSize']	=
	['BXList',
		{'width': '160', 'height': '175', 'field_size': '70', 'title': '(size)', 'disableOnCodeView': true,
			'values':
			[
				{'value': '1', 'name': 'xx-small'},
				{'value': '2', 'name': 'x-small'},
				{'value': '3', 'name': 'small'},
				{'value': '4', 'name': 'medium'},
				{'value': '5', 'name': 'large'},
				{'value': '6', 'name': 'x-large'},
				{'value': '7', 'name': 'xx-large'}
			],
			'OnSelectionChange': function (){
					this.SelectByVal(this.pMainObj.queryCommand('FontSize'));
				},
			'onChange': function (selected){this.pMainObj.executeCommand('FontSize', selected['value']);},
			'onDrawItem': function (item){return '<font size="'+item['value']+'">'+item['name']+'</font>';}
		}
	];

//arButtons['FontSize']	=	['FontSizeListSelect', '140', '200', '30', '(size)', ['8pt', '9pt', '10pt', '12pt', '14pt', '16pt', '18pt', '24pt', '36pt']];

arButtons['FontStyle']	=	['BXStyleList',
	{
		'width': '200',
		'height': '200',
		'field_size': '130',
		'title': '(CSS class)',
		'disableOnCodeView': true,
		'filter': ['DEFAULT'],
		'OnChangeElement': function (arSelected)
		{
			//alert('Element('+this.pElement.tagName+')');
			SAttr(this.pElement, 'className', arSelected["value"]);
		},
		'OnChangeText': function (arSelected)
		{
			/*
			oSelection = this.pMainObj.pEditorDocument.selection;
			var s = oSelection.createRange();
			alert('oSelection.type='+oSelection.type);
			alert('oSelection.htmlText='+s.htmlText);
			alert('oSelection.text='+s.text);
			alert('s.parentElement().innerText='+s.parentElement().innerText);
			*/
			/*
			oSelection = this.pMainObj.pEditorWindow.getSelection();
			alert('oSelection.rangeCount='+oSelection.rangeCount);
			var oRange;var container;
			oRange = oSelection.getRangeAt(0);
			container = oRange.startContainer;
			alert('container.nodeType='+container.nodeType);
			alert('container.childNodes.length='+container.childNodes.length);
			alert('container.' + container.nodeValue);
			alert('oRange.startOffse='+oRange.startOffset+' - '+(container.childNodes[oRange.startOffset]?container.childNodes[oRange.startOffset].nodeValue:""));
			alert('oRange.endOffse='+oRange.endOffset+' - '+(container.childNodes[oRange.endOffset]?container.childNodes[oRange.endOffset].nodeValue:''));
			*/
			/*
			if(container.childNodes.length>0)
			{
				for(var o=0; o<container.childNodes.length; o++)
					alert(container.childNodes[o].nodeType+'/'+container.childNodes[o].nodeValue);
			}
			if(container.nodeType!=3)
			{
				if(container.nodeType==1 && container.childNodes.length<=0)
					return container;
				else
					return false;
			}
			*/
			if(arSelected["value"]!='')
				this.pMainObj.WrapSelectionWith("span", {"class":arSelected["value"]});
			else
			{
				var pElement = this.pMainObj.GetSelectedNode();
				if(pElement)
				{
					while(pElement = pElement.parentNode)
					{
						if(pElement.nodeType == 1 && pElement.tagName.toUpperCase() == 'TABLE')
							break;
						if(pElement.nodeType == 1 && pElement.className)
						{
							if(pElement.tagName.toUpperCase() != 'SPAN' && pElement.tagName.toUpperCase() != 'FONT')
								break;
							SAttr(pElement, 'className', '');
							var attr = pElement.attributes;
							if(attr.length<=0)
								BXDeleteNode(pElement);
							break;
						}
					}
				}
			}
		},
		'OnSelectionChange': function ()
		{
			var pElement = this.pMainObj.GetSelectedNode();
			if(pElement && pElement.nodeType == 1)
			{
				if(this.prevType != pElement.tagName.toUpperCase())
				{
					this.prevType = pElement.tagName.toUpperCase();
					this.tag_name = pElement.tagName.toUpperCase();
					this.filter = [pElement.tagName.toUpperCase(), 'DEFAULT'];
					this.FillList();
				}

				this.pElement = pElement;
				this.onChange = this.OnChangeElement;
				this.SelectByVal(this.pElement.className);
			}
			else
			{
				this.onChange = this.OnChangeText;
				if(this.prevType != 'DEFAULT')
				{
					this.prevType = 'DEFAULT';
					this.filter = ['DEFAULT'];
					this.tag_name = '';
					this.FillList();
				}

				this.SelectByVal();
				if(pElement)
				{
					if(BXIsIE() && pElement.parentElement && (pElement = pElement.parentElement()))
						pElement = pElement.childNodes[0];

					while(pElement = pElement.parentNode)
					{
						if(pElement.nodeType == 1 && pElement.tagName.toUpperCase() == 'TABLE')
							break;
						if(pElement.nodeType == 1 && pElement.className)
						{
							if(pElement.tagName.toUpperCase() != 'SPAN' && pElement.tagName.toUpperCase() != 'FONT')
								break;
							this.SelectByVal(pElement.className);
							break;
						}
					}
				}
			}
		}
	}
	];

arButtons['Template']	=
	['BXList',
		{'width': '160', 'height': '110', 'field_size': '150', 'title': '(template)', 'bSetGlobalStyles': true,
			'values': arBXTemplates,
			'onCreate': function ()
			{
				this.pMainObj.pTemplateListbox = this;
			},
			'onInit': function ()
			{
				this.SelectByVal(this.pMainObj.templateID);
			},
			'onChange': function (selected)
			{
				this.pMainObj.SetTemplate(selected['value']);
			}
		}
	];

/*
arButtons['insrow']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/insrow.gif', ''];
arButtons['delrow']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/delrow.gif', ''];
arButtons['inscol']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/inscol.gif', ''];
arButtons['delcol']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/delcol.gif', ''];
arButtons['inscell']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/inscell.gif', ''];
arButtons['delcell']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/delcell.gif', ''];
arButtons['mergecell']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/mrgcell.gif', ''];
arButtons['splitcell']	=	['BXTButton', '/bitrix/images/fileman/htmledit2/spltcell.gif', ''];
arButtons['specialchar'] 	=	[];
*/

var arCMButtons = [];
arCMButtons["DEFAULT"] = [
	arButtons['Cut'],
	arButtons['Copy'],
	arButtons['Paste']
	];

arCMButtons["A"] = [
	arButtons['CreateLink'], arButtons['deletelink']
	];

arCMButtons["IMG"] = [
	arButtons['image']
	];

arCMButtons["TABLE"] = [
	arButtons['tableprop']
	];
/*
arCMButtons["TR"] = [
	arButtons['insertrow'], arButtons['deleterow']
	];

arCMButtons["TD"] = [
	arButtons['insertcolumn'],
	arButtons['deletecolumn'],
	arButtons['insertcell'],
	arButtons['deletecell'],
	arButtons['mergecell'],
	arButtons['splitcell']
	];
*/
var arToolbars = Array();
/*
arToolbars['manage'] = [
	'Управление и настройки',
		[ arButtons['exit'],
		'separator', arButtons['new'], arButtons['save'], arButtons['saveas'],
		'separator', arButtons['pageprops'],
		'separator', arButtons['settings']
		]
	];
*/
if(BXIsIE())
{
	arToolbars['standart'] = [
		BX_MESS.TBSStandart,
			[arButtons['Fullscreen'], 'separator',
			arButtons['Cut'], arButtons['Copy'], arButtons['Paste'], arButtons['pasteword'], arButtons['pastetext'], arButtons['separator'],
			//arButtons['Undo'], arButtons['Redo'], arButtons['separator'],
			arButtons['borders'], 'separator',
			//arButtons['spellcheck'], arButtons['find'], arButtons['replace'], arButtons['separator'],
			arButtons['table'], arButtons['anchor'], arButtons['CreateLink'], arButtons['deletelink'], arButtons['image']
			]
		];
}
else
{
	arToolbars['standart'] = [
		BX_MESS.TBSStandart,
			[arButtons['Fullscreen'], 'separator',
			arButtons['Cut'], arButtons['Copy'], arButtons['Paste'], arButtons['pasteword'], arButtons['pastetext'], arButtons['separator'],
			arButtons['Undo'], arButtons['Redo'], arButtons['separator'],
			arButtons['borders'], 'separator',
			//arButtons['spellcheck'], arButtons['find'], arButtons['replace'], arButtons['separator'],
			arButtons['table'], arButtons['anchor'], arButtons['CreateLink'], arButtons['deletelink'], arButtons['image']
			]
		];
}

arToolbars['style'] = [
	BX_MESS.TBSStyle,
		[arButtons['FontStyle'], arButtons['HeadingList'], arButtons['FontName'], arButtons['FontSize'], arButtons['separator'],
			arButtons['Bold'], arButtons['Italic'], arButtons['Underline'], 'separator',
			arButtons['RemoveFormat']
		]
	];

arToolbars['formating'] = [
	BX_MESS.TBSFormat,
			[arButtons['InsertHorizontalRule'], arButtons['separator'],
				arButtons['JustifyLeft'], arButtons['JustifyCenter'], arButtons['JustifyRight'], arButtons['JustifyFull'], arButtons['separator'],
				arButtons['InsertOrderedList'], arButtons['InsertUnorderedList'],arButtons['separator'],
				arButtons['Outdent'], arButtons['Indent'], arButtons['separator'],
				arButtons['BackColor'], arButtons['ForeColor']
			]
	];

arToolbars['source'] = [
	BX_MESS.TBSEdit,
	[arButtons['wysiwyg'], arButtons['source'], arButtons['split']]
	];

arToolbars['template'] = [
	BX_MESS.TBSTemplate,
	[arButtons['Template']]
	];

/*
arToolbars['table'] = [
	'Таблица',
	['insrow', 'delrow', 'inscol', 'delcol', 'inscell', 'delcell', 'mergecell', 'splitcell']
	];
*/

var arDefaultTBPositions = {
		'standart':	[0, 0, 0],
		'manage':	[0, 0, 1],
		'template':	[0, 0, 2],
		'source':	[1, 0, 0],
		'style':	[0, 1, 0],
		'formating':[0, 1, 1]
	};



var pPropertybarHandlers = Array();
pPropertybarHandlers['table'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = Array();
		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['table'])
		{
			tProp = arBarHandlersCache['table'][0];
			pTaskbar.arElements = arBarHandlersCache['table'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.pDocument.createElement("TABLE");
			tProp.className = "bxtaskbarprops";
			tProp.style.width = "100%";
			//tProp.style.height = "100%";
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row = tProp.insertRow(-1);

			var cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropSize}));

			cell = row.insertCell(-1); cell.noWrap = true;
			pTaskbar.arElements['width'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropW, 'type': 'text'}));
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'x'}));
			pTaskbar.arElements['height'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropH, 'type': 'text'}));


			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropBord}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['border'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': '5'}));
			////
			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropStyle}));
			cell = row.insertCell(-1);
			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList', {'width': '200', 'height': '200', 'field_size': '80', 'title': '(CSS class)', 'tag_name': 'TABLE', 'filter': ['TABLE', 'DEFAULT']});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropBG}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXColorPicker', {'with_input': true});
			pTaskbar.arElements['bgcolor'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			////
			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'CellPadding: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['cellpadding'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': '5'}));

			////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropAlign}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXTAlignPicker', {'type': 'table'});
			pTaskbar.arElements['talign'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);


			////
			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'CellSpacing: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['cellspacing'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': '5'}));
			cell = row.insertCell(-1);
			cell = row.insertCell(-1);

			arBarHandlersCache['table'] = [tProp, pTaskbar.arElements];
		}

		pTaskbar.pCellProps.appendChild(tProp);
	}

	pTaskbar.arElements['width'].value = GAttr(pElement, 'width');
	pTaskbar.arElements['height'].value = GAttr(pElement, 'height');
	pTaskbar.arElements['border'].value = GAttr(pElement, 'border');
	pTaskbar.arElements['cellpadding'].value = GAttr(pElement, 'cellPadding');
	pTaskbar.arElements['cellspacing'].value = GAttr(pElement, 'cellSpacing');
	pTaskbar.arElements['bgcolor'].SetValue(pElement.bgColor);
	pTaskbar.arElements['talign'].SetValue(pElement.align);
	pTaskbar.arElements['cssclass'].SelectByVal(pElement.className);

	var fChange = function (){
			SAttr(pElement, 'width', pTaskbar.arElements['width'].value);
			SAttr(pElement, 'height', pTaskbar.arElements['height'].value);
			SAttr(pElement, 'border', pTaskbar.arElements['border'].value);
			SAttr(pElement, 'cellPadding', pTaskbar.arElements['cellpadding'].value);
			SAttr(pElement, 'cellSpacing', pTaskbar.arElements['cellspacing'].value);
		}

	pTaskbar.arElements['height'].onchange = fChange;
	pTaskbar.arElements['width'].onchange = fChange;
	pTaskbar.arElements['border'].onchange = fChange;
	pTaskbar.arElements['cellpadding'].onchange = fChange;
	pTaskbar.arElements['cellspacing'].onchange = fChange;
	pTaskbar.arElements['bgcolor'].onChange = function (color) {pElement.bgColor = color;};
	pTaskbar.arElements['talign'].onChange = function (alH) {pElement.align = alH;};
	pTaskbar.arElements['cssclass'].onChange = function (className) {pElement.className=className.value;};
}

pPropertybarHandlers['td'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = Array();

		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['td'])
		{
			tProp = arBarHandlersCache['td'][0];
			pTaskbar.arElements = arBarHandlersCache['td'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.pDocument.createElement("TABLE");
			tProp.className = "bxtaskbarprops";
			tProp.style.width = "100%";
			//tProp.style.height = "100%";
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row = tProp.insertRow(-1);

			var cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropStyle}));

			cell = row.insertCell(-1);

			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList', {'width': '200', 'height': '200', 'field_size': '80', 'title': '(CSS class)', 'tag_name': 'TD', 'filter': ['TD', 'DEFAULT']});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropSize}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['width_val'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropW, 'type': 'text'}));
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'x'}));
			pTaskbar.arElements['height_val'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropH, 'type': 'text'}));

			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropAlign}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXTAlignPicker');
			pTaskbar.arElements['talign'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropBG}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXColorPicker', {'with_input': true});
			pTaskbar.arElements['bgcolor'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropNoWrap}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['nowrap'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'title': 'nowrap', 'type': 'checkbox'}));

			arBarHandlersCache['td'] = [tProp, pTaskbar.arElements];
		}

		pTaskbar.pCellProps.appendChild(tProp);
	}

	pTaskbar.arElements['width_val'].value = pElement.width;
	pTaskbar.arElements['height_val'].value = pElement.height;
	pTaskbar.arElements['nowrap'].checked = pElement.noWrap;
	pTaskbar.arElements['bgcolor'].SetValue(pElement.bgColor);
	pTaskbar.arElements['talign'].SetValue(pElement.align, pElement.vAlign);
	pTaskbar.arElements['cssclass'].SelectByVal(pElement.className);

	var fChange = function (){
			pElement.width = pTaskbar.arElements['width_val'].value;
			pElement.height = pTaskbar.arElements['height_val'].value;
			pElement.noWrap = pTaskbar.arElements['nowrap'].checked;
		}

	pTaskbar.arElements['height_val'].onchange = fChange;
	pTaskbar.arElements['width_val'].onchange = fChange;
	pTaskbar.arElements['nowrap'].onclick = fChange;
	pTaskbar.arElements['bgcolor'].onChange = function (color) {pElement.bgColor = color;};
	pTaskbar.arElements['talign'].onChange = function (alH, alV) {pElement.align = alH; pElement.vAlign = alV;};
	pTaskbar.arElements['cssclass'].onChange = function (className) {pElement.className = className.value;};
}

pPropertybarHandlers['a'] = function (bNew, pTaskbar, pElement)
{
	pTaskbar.pHtmlElement = pElement;
	if(bNew)
	{
		pTaskbar.arElements = Array();

		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['a'])
		{
			tProp = arBarHandlersCache['a'][0];
			pTaskbar.arElements = arBarHandlersCache['a'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.CreateElement("TABLE");
			tProp.className = "bxtaskbarprops";
			tProp.style.width = "100%";
			//tProp.style.height = "100%";
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row, cell;

			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'URL: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['href_val'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'40', 'title': BX_MESS.TPropURL, 'type': 'text'}));

			//////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'Title: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['title'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'30', 'title': BX_MESS.TPropTitle, 'type': 'text'}));

			//////
			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropTarget}));

			cell = row.insertCell(-1);
			var tTemp = cell.appendChild(pTaskbar.pMainObj.CreateElement("TABLE", {'cellPadding': '0', 'cellSpacing': '0'}));

			pTaskbar.arElements['target_list'] = pTaskbar.pMainObj.CreateCustomElement(
				"BXList",
				{'width': '150', 'height': '80', 'field_size': '130', 'bSetGlobalStyles': true, 'taskbar': pTaskbar,
				'values':
				[
					{'value': '', 'name': '&nbsp;'},
					{'value': '_blank', 'name': BX_MESS.TPropTargetBlank},
					{'value': '_parent', 'name': BX_MESS.TPropTargetParent},
					{'value': '_self', 'name': BX_MESS.TPropTargetSelf},
					{'value': '_top', 'name': BX_MESS.TPropTargetTop}
				],
				'OnSelectionChange': function (){
						//this.Select(sel);
					},
				'onChange': function (selected){
						this.taskbar.arElements['target'].disabled = (selected['value']!='');
						this.taskbar.arElements['target'].value = selected['value'];
						this.taskbar.pHtmlElement.target = selected['value'];
						}
			});
			tTemp.insertRow(-1).insertCell(-1).appendChild(pTaskbar.arElements['target_list'].pWnd);
			pTaskbar.arElements['target'] = tTemp.rows[0].insertCell(-1).appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'13', 'title': BX_MESS.TPropTargetWin, 'type': 'text'}));

			//////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropStyle}));

			cell = row.insertCell(-1);
			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList', {'width': '200', 'height': '200', 'field_size': '120', 'title': '(CSS class)', 'tag_name': 'A', 'filter': ['A', 'DEFAULT']});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			arBarHandlersCache['a'] = [tProp, pTaskbar.arElements];
		}

		//////
		pTaskbar.pCellProps.appendChild(tProp);
	}

	pTaskbar.arElements['cssclass'].SelectByVal(pElement.className);
	pTaskbar.arElements['target_list'].SelectByVal(pElement.target);
	pTaskbar.arElements['href_val'].value = pElement.getAttribute("href", 2);
	pTaskbar.arElements['title'].value = pElement.title;
	pTaskbar.arElements['target'].value = pElement.target;
	pTaskbar.arElements['target'].disabled = !(pElement.target!='_blank' && pElement.target!='_top' && pElement.target!='_self' && pElement.target!='_parent');

	var fChange = function (){
			pElement.href = pTaskbar.arElements['href_val'].value;
			pElement.title = pTaskbar.arElements['title'].value;
			pElement.target = pTaskbar.arElements['target'].value;
		}

	pTaskbar.arElements['href_val'].onchange = fChange;
	pTaskbar.arElements['title'].onchange = fChange;
	pTaskbar.arElements['target'].onchange = fChange;
	pTaskbar.arElements['cssclass'].onChange = function (sel) {pElement.className=sel['value'];};
};

pPropertybarHandlers['anchor'] = function (bNew, pTaskbar, pElement)
{
	pTaskbar.pHtmlElement = pElement;
	if(bNew)
	{
		pTaskbar.arElements = Array();
		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['anchor'])
		{
			tProp = arBarHandlersCache['anchor'][0];
			pTaskbar.arElements = arBarHandlersCache['anchor'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.CreateElement("TABLE");
			tProp.className = "bxtaskbarprops";
			tProp.style.width = "100%";
			//tProp.style.height = "100%";
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row, cell;

			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right'; cell.width="50%";
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropName}));

			cell = row.insertCell(-1); cell.width="50%";
			pTaskbar.arElements['name'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'40', 'title': BX_MESS.TPropAnchorName, 'type': 'text'}));

			arBarHandlersCache['anchor'] = [tProp, pTaskbar.arElements];
		}

		//////
		pTaskbar.pCellProps.appendChild(tProp);
	}

	var val = BXUnSerialize(pElement.getAttribute("__bxcontainer"));
	pTaskbar.arElements['name'].value = val["name"];

	var fChange = function (){
			pElement.setAttribute("__bxcontainer", BXSerialize({"name":pTaskbar.arElements['name'].value}));
		}

	pTaskbar.arElements['name'].onchange = fChange;
};


/*
pPropertybarHandlers['tr'] = {};
pPropertybarHandlers['default'] = pPropertybarHandlers['p'];
*/

pPropertybarHandlers['img'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = Array();

		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['img'])
		{
			tProp = arBarHandlersCache['img'][0];
			pTaskbar.arElements = arBarHandlersCache['img'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.CreateElement("TABLE");
			tProp.className = "bxtaskbarprops";
			tProp.style.width = "100%";
			//tProp.style.height = "100%";
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row, cell;

			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropImgPath}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['src'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'40', 'title': BX_MESS.TPropImgPath2, 'type': 'text'}));

			//////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'HSpace: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['hspace'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'6', 'title': 'HSpace', 'type': 'text'}));


			//////
			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropSize}));
			cell = row.insertCell(-1);
			pTaskbar.arElements['width'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropW, 'type': 'text'}));
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'x'}));
			pTaskbar.arElements['height'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropH, 'type': 'text'}));

			//////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'VSpace: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['vspace'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'6', 'title': 'VSpace', 'type': 'text'}));

			//////
			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'Title: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['alt'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'40', 'title': BX_MESS.TPropTitle, 'type': 'text'}));

			//////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropStyle}));

			cell = row.insertCell(-1);
			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList', {'width': '200', 'height': '200', 'field_size': '120', 'title': '(CSS class)', 'tag_name': 'A', 'filter': ['A', 'DEFAULT']});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			//////
			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'Border: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['border'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'4', 'title': BX_MESS.TPropImgBorder, 'type': 'text'}));

			//////
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropAlign}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXTAlignPicker', {'type': 'image'});
			pTaskbar.arElements['talign'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			arBarHandlersCache['img'] = [tProp, pTaskbar.arElements];
		}
		//////
		pTaskbar.pCellProps.appendChild(tProp);
	}

	pTaskbar.arElements['cssclass'].SelectByVal(pElement.className);
	pTaskbar.arElements['talign'].SetValue(pElement.align);
	pTaskbar.arElements['src'].value = GAttr(pElement, 'src');
	pTaskbar.arElements['alt'].value = GAttr(pElement, 'alt');
	pTaskbar.arElements['hspace'].value = GAttr(pElement, 'hspace');
	pTaskbar.arElements['vspace'].value = GAttr(pElement, 'vspace');
	pTaskbar.arElements['border'].value = GAttr(pElement, 'border');
	pTaskbar.arElements['width'].value = GAttr(pElement, 'width');
	pTaskbar.arElements['height'].value = GAttr(pElement, 'height');

	var fChange = function (){
			SAttr(pElement, 'src', pTaskbar.arElements['src'].value);
			SAttr(pElement, 'alt', pTaskbar.arElements['alt'].value);
			SAttr(pElement, 'hspace', pTaskbar.arElements['hspace'].value);
			SAttr(pElement, 'vspace', pTaskbar.arElements['vspace'].value);
			SAttr(pElement, 'border', pTaskbar.arElements['border'].value);
			SAttr(pElement, 'width', pTaskbar.arElements['width'].value);
			SAttr(pElement, 'height', pTaskbar.arElements['height'].value);
		}

	pTaskbar.arElements['src'].onchange = fChange;
	pTaskbar.arElements['alt'].onchange = fChange;
	pTaskbar.arElements['hspace'].onchange = fChange;
	pTaskbar.arElements['vspace'].onchange = fChange;
	pTaskbar.arElements['border'].onchange = fChange;
	pTaskbar.arElements['width'].onchange = fChange;
	pTaskbar.arElements['height'].onchange = fChange;
	pTaskbar.arElements['cssclass'].onChange = function (sel) {pElement.className=sel['value'];};
	pTaskbar.arElements['talign'].onChange = function (align) {pElement.align = align;};
	pTaskbar.pHtmlElement = pElement;
};

pPropertybarHandlers['php'] = function (bNew, pTaskbar, pElement)
{
	pTaskbar.pHtmlElement = pElement;
	if(bNew)
	{
		pTaskbar.arElements = Array();

		var tProp = pTaskbar.pMainObj.CreateElement("TABLE");
		tProp.className = "bxtaskbarprops";
		tProp.style.width = "100%";
		//tProp.style.height = "100%";
		tProp.cellSpacing = 0;
		tProp.cellPadding = 1;
		var row, cell;

		row = tProp.insertRow(-1);
		cell = row.insertCell(-1);
		pTaskbar.arElements['code'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'rows':'3', cols: '60', 'title': BX_MESS.TPropPHP}, {"width": "100%"}));

		//////
		pTaskbar.pCellProps.appendChild(tProp);
	}

	pTaskbar.arElements['code'].value = BXUnSerialize(pElement.getAttribute("__bxcontainer")).code;

	var fChange = function (){
			pElement.setAttribute("__bxcontainer", BXSerialize({'code':pTaskbar.arElements['code'].value}));
		}

	pTaskbar.arElements['code'].onchange = fChange;
};

var __BXSetOptionSelected = function (pOption, bSel)
{
	return function(){pOption.selected = bSel;};
}

var BXShowComponentPanel = function (bNew, pTaskbar, pElement)
{
	while(pTaskbar.pCellProps.childNodes.length>0)
		pTaskbar.pCellProps.removeChild(pTaskbar.pCellProps.childNodes[0]);

	pTaskbar.pHtmlElement = pElement;
	//alert(pElement.getAttribute("__bxcontainer"));
	var arSettings = BXUnSerialize(pElement.getAttribute("__bxcontainer"));

	var fChange = function (e)
	{
		//alert(e);
		var arAllFields = Array();
		function addel(arEls)
		{
			var el;
			for(var i=0; i<arEls.length; i++)
			{
				if(!arEls[i]["__exp"] || arEls[i]["__exp"]!="Y") continue;
				el = arEls[i];
				if(el["name"].substr(el["name"].length-2, 2) == '[]')
				{
					if(arAllFields[el["name"].substr(0, el["name"].length-2)])
						arAllFields[el["name"].substr(0, el["name"].length-2)].push(el);
					else
						arAllFields[el["name"].substr(0, el["name"].length-2)] = Array(el);
				}
				else
					arAllFields[el["name"]] = el;
			}
		}

		arSettings["PARAMS"] = {};
		var propID, i, j, val;
		addel(pTaskbar.pCellProps.getElementsByTagName("select"));
		addel(pTaskbar.pCellProps.getElementsByTagName("input"));
		addel(pTaskbar.pCellProps.getElementsByTagName("textarea"));

		for(i=0; i<pTaskbar.arElements.length; i++)
		{
			propID = pTaskbar.arElements[i];
			val = arAllFields[propID];

			if(arAllFields[propID+'_alt'] && val.selectedIndex == 0)
				val = arAllFields[propID+'_alt'];

			if(!val) continue;
			if(val.tagName) // one element
			{
				if(val.tagName.toUpperCase() == "SELECT")
				{
					for(j=0; j<val.length; j++)
					{
						if(val[j].selected && val[j].value!='')
							arSettings["PARAMS"][propID] = val[j].value;
					}
				}
				else
					arSettings["PARAMS"][propID] = val.value;
			}
			else
			{
				arSettings["PARAMS"][propID] = [];
				for(k=0; k<val.length; k++)
				{
					if(val[k].tagName.toUpperCase() == "SELECT")
					{
						for(j=0; j<val[k].length; j++)
						{
							if(val[k][j].selected && val[k][j].value!='')
								arSettings["PARAMS"][propID].push(val[k][j].value);
						}
					}
					else
						arSettings["PARAMS"][propID].push(val[k].value);
				}
			}
		}

		if(pElement)
			pElement.setAttribute("__bxcontainer", BXSerialize(arSettings));
	}


	pTaskbar.arElements = Array();

	var templateID = pTaskbar.pMainObj.templateID;

	var tProp = pTaskbar.pMainObj.CreateElement("TABLE");
	tProp.className = "bxtaskbarprops";
	tProp.style.width = "100%";
	//tProp.style.height = "100%";
	tProp.cellSpacing = 0;
	tProp.cellPadding = 1;
	var row, cell, arPropertyParams, bSel, arValues, res, pSelect, arUsedValues, bFound, key, oOption, val, xCell, opt_val, bBr, i, k, alt;

	//////////
	var sURL = '/bitrix/admin/fileman_get_xml.php?op=componentconfig&lang='+BXLang+'&site='+BXSite+'&templateID='+templateID+'&path='+arSettings["SCRIPT_NAME"];
	var arParams = pTaskbar.pMainObj.GetData(sURL, arSettings["PARAMS"]);
	if(typeof(arParams)!='object')
		arParams = {};
	for(var propertyID in arParams)
	{
		pTaskbar.arElements.push(propertyID);
		res = '';
		arUsedValues = [];
		arPropertyParams = arParams[propertyID];
		if(!arSettings["PARAMS"][propertyID] && arPropertyParams["DEFAULT"])
			arValues = arPropertyParams["DEFAULT"];
		else if(arSettings["PARAMS"][propertyID])
			arValues = arSettings["PARAMS"][propertyID];
		else
			arValues = '';

		if(!arPropertyParams["MULTIPLE"] || arPropertyParams["MULTIPLE"]!="Y")
			arPropertyParams["MULTIPLE"] = "N";
		if(!arPropertyParams["TYPE"])
			arPropertyParams["TYPE"] = "STRING";
		if(!arPropertyParams["CNT"])
			arPropertyParams["CNT"] = 0;
		if(!arPropertyParams["SIZE"])
			arPropertyParams["SIZE"] = 0;
		if(!arPropertyParams['ADDITIONAL_VALUES'])
			arPropertyParams['ADDITIONAL_VALUES'] = 'N';
		if(!arPropertyParams['ROWS'])
			arPropertyParams['ROWS'] = 0;
		if(!arPropertyParams["COLS"] || arPropertyParams["COLS"]<1)
			arPropertyParams["COLS"] = '30';

		if(arPropertyParams["MULTIPLE"] && arPropertyParams["MULTIPLE"]=='Y' && typeof(arValues)!='object')
		{
			if(!arValues)
				arValues = Array();
		}
		else if(arPropertyParams["TYPE"]&& arPropertyParams["TYPE"]=="LIST" && typeof(arValues)!='object')
			arValues = Array(arValues);

		if(arPropertyParams["MULTIPLE"] && arPropertyParams["MULTIPLE"]=='Y')
		{
			arPropertyParams["CNT"] = parseInt(arPropertyParams["CNT"]);
			if(arPropertyParams["CNT"]<1)
				arPropertyParams["CNT"] = 1;
		}

		row = tProp.insertRow(-1);
		row.className = "bxtaskbarpropscomp";
		cell = row.insertCell(-1);
		cell.width = "50%";
		cell.align = "right";
		cell.vAlign = "top";
		cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML': arPropertyParams['NAME']+':'}));
		cell = row.insertCell(-1);
		cell.width = "50%";

		arPropertyParams["TYPE"] = arPropertyParams["TYPE"].toUpperCase();
		switch(arPropertyParams["TYPE"])
		{
		case "LIST":
			arPropertyParams["SIZE"] = (arPropertyParams["MULTIPLE"]=='Y' && (parseInt(arPropertyParams["SIZE"])<=1 || isNaN(parseInt(arPropertyParams["SIZE"]))) ? '3' : arPropertyParams["SIZE"]);
			if(parseInt(arPropertyParams["SIZE"])<=0 || isNaN(parseInt(arPropertyParams["SIZE"])))
				arPropertyParams["SIZE"] = 1;

			pSelect = pTaskbar.pMainObj.CreateElement("SELECT", {'size': arPropertyParams["SIZE"], 'name': propertyID+(arPropertyParams["MULTIPLE"]=='Y'?'[]':''), '__exp': 'Y', 'onchange': fChange, "multiple":(arPropertyParams["MULTIPLE"]=="Y")});
			cell.appendChild(pSelect);

			if(!arPropertyParams["VALUES"])
				arPropertyParams["VALUES"] = [];

			bFound = false;
			for(opt_val in arPropertyParams["VALUES"])
			{
				bSel = false;
				oOption = new Option(arPropertyParams["VALUES"][opt_val], opt_val, false, false);
				pSelect.options.add(oOption);
				if(pSelect.options.length<=1)
					setTimeout(__BXSetOptionSelected(oOption, false), 1);

				key = BXSearchInd(arValues, opt_val);
				if(key>=0)
				{
					//alert(opt_val);
					bFound = true;
					arUsedValues[key]=true;
					bSel = true;
					setTimeout(__BXSetOptionSelected(oOption, true), 1);
				}
			}


			if(arPropertyParams['ADDITIONAL_VALUES']!='N')
			{
				oOption = document.createElement("OPTION");
				oOption.value = '';
				oOption.selected = !bFound;
				oOption.text = (arPropertyParams['MULTIPLE']=='Y'?BX_MESS.TPropCompNS:BX_MESS.TPropCompOth)+' ->';
				pSelect.options.add(oOption, 0);
			}

/*
if(propertyID == "IBLOCK")
{
	var x = pSelect;
			setTimeout(function (){
					x.options[2].selected = true;
					x.options[1].selected = true;
				}, 19);
}
			pSelect.options[0].selected = true;
*/
			if(arPropertyParams['ADDITIONAL_VALUES']!='N')
			{
				if(arPropertyParams['MULTIPLE']=='Y')
				{
					for(k=0; k<arValues.length; k++)
					{
						if(arUsedValues[k])
							continue;
						cell.appendChild(pTaskbar.pMainObj.CreateElement("BR"));
						if(arPropertyParams['ROWS']>1)
							cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': arValues[k], 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
						else
							cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': arValues[k], 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					}

					for(k=0; k<arPropertyParams["CNT"]; k++)
					{
						cell.appendChild(pTaskbar.pMainObj.CreateElement("BR"));
						if(arPropertyParams['ROWS']>1)
							cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
						else
							cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					}

					xCell = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'button', 'value': '+', 'pMainObj': pTaskbar.pMainObj,  'arPropertyParams': arPropertyParams}));
					cell.appendChild(pTaskbar.pMainObj.CreateElement("BR"));
					xCell.propertyID = propertyID;
					xCell.fChange = fChange;
					xCell.onclick = function ()
					{
						this.parentNode.insertBefore(this.pMainObj.CreateElement("BR"), this);
						if(this.arPropertyParams['ROWS'] && this.arPropertyParams['ROWS']>1)
							this.parentNode.insertBefore(this.pMainObj.CreateElement("TEXTAREA", {'cols': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
						else
							this.parentNode.insertBefore(this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
					}
				}
				else
				{
					val = '';
					for(k=0; k<arValues.length; k++)
					{
						if(arUsedValues[k])
							continue;
						val = arValues[k];
						break;
					}

					if(arPropertyParams['ROWS'] && arPropertyParams['ROWS']>1)
						alt = cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'disabled': bFound, 'name': propertyID+'_alt', '__exp': 'Y', 'onchange': fChange}));
					else
						alt = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'disabled': bFound, 'name': propertyID+'_alt', '__exp': 'Y', 'onchange': fChange}));

					pSelect.pAlt = alt;
					pSelect.onchange = function (e){this.pAlt.disabled = (this.selectedIndex!=0); fChange(e);};
				}
			}

			break;
		default:
			if(arPropertyParams["MULTIPLE"]=='Y')
			{
				bBr = false;
				for(val in arValues)
				{
					if(bBr)
						cell.appendChild(pTaskbar.pMainObj.CreateElement("BR"));
					else
						bBr = true;

					if(arPropertyParams['ROWS']>1)
						cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					else
						cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
				}

				for(k=0; k<arPropertyParams["CNT"]; k++)
				{
					if(bBr)
						cell.appendChild(pTaskbar.pMainObj.CreateElement("BR"));
					else
						bBr = true;

					if(arPropertyParams['ROWS']>1)
						cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					else
						cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
				}

				xCell = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'button', 'value': '+', 'pMainObj': pTaskbar.pMainObj,  'arPropertyParams': arPropertyParams}));
				xCell.propertyID = propertyID;
				xCell.fChange = fChange;
				xCell.onclick = function ()
				{
					this.parentNode.insertBefore(this.pMainObj.CreateElement("BR"), this);
					if(this.arPropertyParams['ROWS'] && this.arPropertyParams['ROWS']>1)
						this.parentNode.insertBefore(this.pMainObj.CreateElement("TEXTAREA", {'cols': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
					else
						this.parentNode.insertBefore(this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
				}
				cell.appendChild(pTaskbar.pMainObj.CreateElement("BR"));
			}
			else
			{
				val = arValues;

				if(arPropertyParams['ROWS'] && arPropertyParams['ROWS']>1)
					cell.appendChild(pTaskbar.pMainObj.CreateElement("TEXTAREA", {'cols': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'name': propertyID, '__exp': 'Y', 'onchange': fChange}));
				else
					cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'name': propertyID, '__exp': 'Y', 'onchange': fChange}));
			}
			break;
		}

		if(arPropertyParams["REFRESH"] && arPropertyParams["REFRESH"]=="Y")
		{
			xCell = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'button', 'value': 'ok', 'pMainObj': pTaskbar.pMainObj,  'arPropertyParams': arPropertyParams}));
			xCell.onclick = function (){BXShowComponentPanel(bNew, pTaskbar, pElement);};
		}

		/*
		row = tProp.insertRow(-1);
		cell = row.insertCell(-1);
		cell.colSpan = "2";
		cell.className = " ";
		*/
	}

	var arTemplate;
	if(tProp.rows.length>0 && (arTemplate = pTaskbar.pMainObj.FindComponentByPath(arSettings["SCRIPT_NAME"])))
	{
		cell = tProp.rows[0].cells[1];
		cell.noWrap = true;
		cell.insertBefore(pTaskbar.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/info.gif', 'title': arTemplate['FULL_PATH'], 'align': 'right', 'width': '16', 'height':'16'}), cell.childNodes[0]);
	}

	pTaskbar.pCellProps.appendChild(tProp);
	//////////
};

pPropertybarHandlers['component'] = BXShowComponentPanel;


pPropertybarHandlers['default'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = Array();

		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['default'])
		{
			tProp = arBarHandlersCache['default'][0];
			pTaskbar.arElements = arBarHandlersCache['default'][1];
		}
		else
		{
			tProp = pTaskbar.pMainObj.pDocument.createElement("TABLE");
			tProp.className = "bxtaskbarprops";
			tProp.style.width = "100%";
			//tProp.style.height = "100%";
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row = tProp.insertRow(-1);

			////
			var cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropStyle}));

			cell = row.insertCell(-1);
			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList', {'width': '200', 'height': '200', 'field_size': '80', 'title': '(CSS class)', 'filter':['DEFAULT']});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropBIU}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['Bold'][0], arButtons['Bold'][1]);
			cell.appendChild(pObjTemp.pWnd);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['Italic'][0], arButtons['Italic'][1]);
			cell.appendChild(pObjTemp.pWnd);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['Underline'][0], arButtons['Underline'][1]);
			cell.appendChild(pObjTemp.pWnd);

			////
			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML': BX_MESS.TPropFFace}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['FontName'][0], arButtons['FontName'][1])
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropFSize}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['FontSize'][0], arButtons['FontSize'][1])
			cell.appendChild(pObjTemp.pWnd);

			////
			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML': BX_MESS.TPropColor}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['ForeColor'][0], arButtons['ForeColor'][1]);
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML': BX_MESS.TPropBG}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement(arButtons['BackColor'][0], arButtons['BackColor'][1]);
			cell.appendChild(pObjTemp.pWnd);

			arBarHandlersCache['default'] = [tProp, pTaskbar.arElements];
		}
		pTaskbar.pCellProps.appendChild(tProp);
	}

	//pTaskbar.arElements['width_val'].value = Math.random();
	//pTaskbar.arElements['height_val'].value = pElement.height;
	//pTaskbar.arElements['nowrap'].checked = pElement.noWrap;
	//pTaskbar.arElements['style'];
	//pTaskbar.arElements['bgcolor'];
	//pTaskbar.arElements['style'];
}
