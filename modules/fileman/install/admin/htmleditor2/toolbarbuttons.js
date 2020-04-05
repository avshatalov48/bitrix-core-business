var arButtons = [];
arButtons['separator'] = 'separator';
arButtons['Fullscreen']	= ['BXButton',
	{
		id : 'Fullscreen',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.Fullscreen,
		title : BX_MESS.Fullscreen,
		codeEditorMode : true,
		handler : function()
		{
			this.pMainObj.SetFullscreen(!this.pMainObj.bFullscreen);
			this.Check(this.pMainObj.bFullscreen);
		}
	}
];

arButtons['Settings'] = ['BXButton',
	{
		id : 'Settings',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.TBSettings,
		title: BX_MESS.TBSettings,
		codeEditorMode: true,
		handler: function()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("settings", false, 500, {bUseTabControl: true, lightMode: lightMode, PHPGetParams: '&light_mode=' + (lightMode ? 'Y' : 'N')});
		}
	}
];

arButtons['Cut'] = ['BXButton',
	{
		id : 'Cut',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.Cut,
		cmd: 'Cut',
		hideCondition: function() {return !BX.browser.IsIE();}
	}
];

arButtons['Copy'] = ['BXButton',
	{
		id : 'Copy',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.Copy,
		cmd: 'Copy',
		hideCondition: function() {return !BX.browser.IsIE();}
	}
];

arButtons['Paste'] =['BXButton',
	{
		id : 'Paste',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.Paste,
		cmd: 'Paste',
		hideCondition: function() {return !BX.browser.IsIE();}
	}
];

arButtons['pasteword'] = ['BXButton',
	{
		id : 'pasteword',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.PasteAsWord,
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("pasteword");
		}
	}
];

arButtons['pastetext'] = ['BXButton',
	{
		id : 'pastetext',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.PasteAsText,
		handler : function ()
			{
				if(BX.browser.IsIE())
				{
					if (clipboardData.getData("Text"))
						this.pMainObj.PasteAsText(clipboardData.getData("Text"));
				}
				else
				{
					this.bNotFocus = true;
					this.pMainObj.OpenEditorDialog("pasteastext", false, 450);
				}
			}
	}
];

arButtons['SelectAll'] = ['BXButton',
	{
		id : 'SelectAll',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.SelectAllTitle,
		cmd: 'SelectAll'
	}
];

if(BX.browser.IsIE())
{
	arButtons['Undo'] = ['BXButton',
	{
		id : 'Undo',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.Undo,
		OnChangeContent : function(){this.Disable(!this.pMainObj.UndoStatus());},
		OnCreate : function(){this.pMainObj.AddEventHandler("OnChange", this.OnChangeContent, this);},
		handler : function(){this.pMainObj.Undo(1);}
	}
	];

	arButtons['Redo'] = ['BXButton',
	{
		id : 'Redo',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.Redo,
		OnChangeContent: function(){this.Disable(!this.pMainObj.RedoStatus());},
		OnCreate: function(){this.pMainObj.AddEventHandler("OnChange", this.OnChangeContent, this);},
		handler: function(){this.pMainObj.Redo(1);}
	}
	];
}
else
{
	arButtons['Undo'] = ['BXButton',
	{
		id : 'Undo',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.Undo,
		cmd : 'Undo'
	}
	];

	arButtons['Redo'] = ['BXButton',
	{
		id : 'Redo',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.Redo,
		cmd : 'Redo'
	}
	];
}

arButtons['borders'] = ['BXButton',
	{
		name : BX_MESS.BordersTitle,
		id : 'borders',
		iconkit : '_global_iconkit.gif',
		handler : function ()
			{
				this.pMainObj.ShowTableBorder(!this.pMainObj.bTableBorder);
				this.Check(this.pMainObj.bTableBorder);
			},
		OnCreate : function ()
			{
				var _this = this;
				setTimeout(function(){_this.Check(_this.pMainObj.bTableBorder);}, 10);
				return true;
			}
	}
];

arButtons['table'] = ['BXButton',
	{
		id : 'table',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBInsTable,
		title : BX_MESS.TBInsTable,
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("table", false, 500);
		}
	}
];

arButtons['anchor'] = ['BXButton',
	{
		id : 'anchor',
		iconkit : '_global_iconkit.gif',
		name: 'Anchor',
		title: BX_MESS.TBAnchor,
		handler: function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("anchor", null, 400);
		}
	}
];

arButtons['CreateLink'] = ['BXButton',
	{
		id : 'CreateLink',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBLink,
		title : BX_MESS.TBLink,
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("editlink", null, 520);
		}
	}
];

arButtons['deletelink'] = ['BXButton',
	{
		id : 'deletelink',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBDelLink,
		cmd : 'Unlink',
		handler : function()
		{
			var pElement = BXFindParentByTagName(this.pMainObj.GetSelectionObject(), 'A');
			if(pElement)
			{
				this.pMainObj.SelectElement(pElement);
				this.pMainObj.executeCommand('Unlink');
			}
		}
	}
];

arButtons['image'] = ['BXButton',
	{
		id : 'image',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBImg,
		handler : function ()
		{
			this.bNotFocus = true;
			var p = this.pMainObj.GetSelectionObject();
			if (!p || p.tagName != 'IMG')
				p = false;
			this.pMainObj.OpenEditorDialog("image", p, 500);
		}
	}
];

arButtons['SpecialChar'] = ['BXButton',
	{
		id : 'SpecialChar',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.SpecialCharTitle,
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("specialchar", false, 610, {pMainObj:this.pMainObj});
		}
	}
];

arButtons['Bold'] = ['BXButton',
	{
		id : 'Bold',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBBold,
		title : BX_MESS.TBBold + " (Ctrl + B)",
		cmd : 'Bold'
	}
];

arButtons['Italic']	= ['BXButton',
	{
		id : 'Italic',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBItalic,
		title : BX_MESS.TBItalic + " (Ctrl + I)",
		cmd : 'Italic'
	}
];

arButtons['Underline'] = ['BXButton',
	{
		id : 'Underline',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBUnderline,
		title : BX_MESS.TBUnderline + " (Ctrl + U)",
		cmd : 'Underline'
	}
];

arButtons['Strike'] = ['BXButton',
	{
		id : 'Strike',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBStrike,
		handler : function ()
		{
			var
				pElement = this.pMainObj.GetSelectedNode(true),
				bFind = false, st;

			while(!bFind)
			{
				if (!pElement)
					break;

				if (pElement.nodeType == 1 && (pElement.style.textDecoration == "line-through" ||  pElement.nodeName.toLowerCase() == 'strike'))
					bFind = true;
				else
					pElement = pElement.parentNode;
			}

			if (bFind)
			{
				pElement.style.textDecoration = "";
				this.pMainObj.RidOfNode(pElement, pElement.nodeName.toLowerCase() == 'strike');
				this.Check(false);
			}
			else
			{
				this.pMainObj.WrapSelectionWith("span", {style: {textDecoration : "line-through"}});
				this.pMainObj.OnEvent("OnSelectionChange");
			}
		},
		OnSelectionChange: function ()
		{
			var
				pElement = this.pMainObj.GetSelectedNode(true),
				bFind = false, st;

			while(!bFind)
			{
				if (!pElement)
					break;

				if (pElement.nodeType == 1 && (BX.style(pElement, 'text-decoration', null) == "line-through" || pElement.nodeName.toLowerCase() == 'strike'))
				{
					bFind = true;
					break;
				}
				else
					pElement = pElement.parentNode;
			}

			this.Check(bFind);
		}
	}
];

arButtons['RemoveFormat'] = ['BXButton',
	{
		id : 'RemoveFormat',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.RemoveFormat,
		cmd : 'RemoveFormat'
	}
];

arButtons['Optimize'] = ['BXButton',
		{
			id : 'Optimize',
			iconkit : '_global_iconkit.gif',
			name : BX_MESS.Optimize,
			handler : function ()
			{
				var pMainObj = this.pMainObj;
				pMainObj.CollapseSelection();
				pMainObj.insertHTML('<a href="#" id="' + pMainObj.SetBxTag(false, {tag: "cursor"}) + '">|</a>');
				pMainObj.OnEvent('ClearResourcesBeforeChangeView');
				pMainObj.SaveContent();

				var content = pMainObj.GetContent();
				content = pMainObj.OptimizeHTML(content); // optimize
				content = pMainObj.pParser.SystemParse(content); // Parse
				pMainObj.pEditorDocument.body.innerHTML = content;

				setTimeout(function()
				{
					try{
						var pCursor = pMainObj.pEditorDocument.getElementById(pMainObj.lastCursorId);
						if (pCursor && pCursor.parentNode)
						{
							pMainObj.SelectElement(pCursor);
							pCursor.parentNode.removeChild(pCursor);
							pMainObj.SetFocus();
							pMainObj.insertHTML('');
						}
					}catch(e){}
				}, 100);
			}
		}
	];

// CELL
arButtons['insertcell_before'] = ['BXButton', {
	id : 'insertcell_before',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBInsCellBefore,
	handler: function () {this.pMainObj.TableOperation('cell', 'insert_before', arguments[0]);}
}];
arButtons['insertcell_after'] = ['BXButton', {
	id : 'insertcell_after',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBInsCellAfter,
	handler: function () {this.pMainObj.TableOperation('cell', 'insert_after', arguments[0]);}
}];
arButtons['deletecell'] = ['BXButton', {
	id : 'deletecell',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBDellCell,
	handler: function () {this.pMainObj.TableOperation('cell', 'delete', arguments[0]);}
}];
arButtons['mergecells'] = ['BXButton', {
	id : 'mergecells',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBMergeCell,
	handler: function () {this.pMainObj.TableOperation('cell', 'merge', arguments[0]);},
	disablecheck: function (oTable, pMainObj)
	{
		var arCells = pMainObj.getSelectedCells();
		if (arCells.length < 2)
			return true;
		return false;
	}
}];
arButtons['merge_right'] = ['BXButton', {
	id : 'merge_right',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBMergeRight,
	handler: function () {this.pMainObj.TableOperation('cell', 'mergeright', arguments[0]);},
	disablecheck: function (oTable, pMainObj)
	{
		var arCells = pMainObj.getSelectedCells();
		if (arCells.length != 1 || !arCells[0].parentNode.cells[arCells[0].cellIndex + 1])
			return true;
		return false;
	}
}];
arButtons['merge_bottom'] = ['BXButton', {
	id : 'merge_bottom',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBMergeBottom,
	handler: function () {this.pMainObj.TableOperation('cell', 'mergebottom', arguments[0]);},
	disablecheck: function (oTable, pMainObj)
	{
		var arCells = pMainObj.getSelectedCells();
		if (arCells.length != 1)
			return true;

		var oTR = arCells[0].parentNode;
		if (!oTR.parentNode.rows[oTR.rowIndex + 1])
			return true;
		return false;
	}
}];
arButtons['split_hor'] = ['BXButton', {
	id : 'split_hor',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBSplitCellHor,
	handler: function () {this.pMainObj.TableOperation('cell', 'splithorizontally', arguments[0]);},
	disablecheck: function (oTable, pMainObj)
	{
		var arCells = pMainObj.getSelectedCells();
		if (arCells.length != 1)
			return true;
		return false;
	}
}];
arButtons['split_ver'] = ['BXButton', {
	id : 'split_ver',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBSplitCellVer,
	handler: function () {this.pMainObj.TableOperation('cell', 'splitvertically', arguments[0]);},
	disablecheck: function (oTable, pMainObj)
	{
		var arCells = pMainObj.getSelectedCells();
		if (arCells.length != 1)
			return true;
		return false;
	}
}];
// ROW
arButtons['insertrow_before'] = ['BXButton', {
	id : 'insertrow_before',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBInsRowUpper,
	handler: function () {this.pMainObj.TableOperation('row', 'insertbefore', arguments[0]);}
}];
arButtons['insertrow_after'] = ['BXButton', {
	id : 'insertrow_after',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBInsRowLower,
	handler: function () {this.pMainObj.TableOperation('row', 'insertafter', arguments[0]);}
}];
arButtons['mergeallcellsinrow'] = ['BXButton', {
	id : 'mergeallcellsinrow',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBMergeRowCells,
	handler: function () {this.pMainObj.TableOperation('row', 'mergecells', arguments[0]);}
}];
arButtons['deleterow'] = ['BXButton', {
	id : 'deleterow',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBDelRow,
	handler: function () {
		this.pMainObj.TableOperation('row', 'delete', arguments[0]);}
}];
// COLUMN
arButtons['insertcolumn_before'] = ['BXButton', {
	id : 'insertcolumn_before',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBInsColLeft,
	handler: function () {this.pMainObj.TableOperation('column', 'insertleft', arguments[0]);}
}];
arButtons['insertcolumn_after'] = ['BXButton', {
	id : 'insertcolumn_after',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBInsColRight,
	handler: function () {this.pMainObj.TableOperation('column', 'insertright', arguments[0]);}
}];
arButtons['mergeallcellsincolumn'] = ['BXButton', {
	id : 'mergeallcellsincolumn',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBMergeColCells,
	handler: function () {this.pMainObj.TableOperation('column', 'mergecells', arguments[0]);},
	disablecheck: function (oTable, pMainObj)
	{
		return false;
		var arCells = pMainObj.getSelectedCells();
		if (arCells.length != 1 || arCells[0].parentNode.rowIndex == arCells[0].parentNode.parentNode.rows.length - 1)
			return true;
		return false;
	}
}];
arButtons['deletecolumn'] = ['BXButton', {
	id : 'deletecolumn',
	iconkit : '_global_iconkit.gif',
	name: BX_MESS.TBDelCol,
	handler: function () {this.pMainObj.TableOperation('column', 'delete', arguments[0]);}
}];

arButtons['deltable'] = ['BXButton',
	{
		id : 'deletetable',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.DeleteTable,
		title: BX_MESS.DeleteTable,
		handler: function ()
		{
			this.pMainObj.bSkipChanges = false;
			var pElement = BXFindParentByTagName(this.pMainObj.GetSelectionObject(), 'TABLE');
			if (pElement && pElement.nodeName.toUpperCase() == 'TABLE')
				pElement.parentNode.removeChild(pElement);
			this.pMainObj.OnChange("table", "deltable");
		}
	}];

arButtons['tableprop'] = ['BXButton',
	{
		id : 'tableprop',
		iconkit : '_global_iconkit.gif',
		name: BX_MESS.TBTableProp,
		title: BX_MESS.TBTableProp,
		handler: function ()
		{
			this.bNotFocus = true;
			var p = BXFindParentByTagName(this.pMainObj.GetSelectionObject(), 'TABLE');
			if (p) this.pMainObj.OpenEditorDialog("table", p, 450, {check_exists: true});
		}
	}];


arButtons['InsertHorizontalRule'] = ['BXButton',
	{
		id : 'InsertHorizontalRule',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBHr,
		handler: function()
		{
			if (BX.browser.IsIE())
				this.pMainObj.pMainObj.executeCommand('InsertHorizontalRule');
			else
				this.pMainObj.insertHTML('<img style="padding: 2px; width: 100%; height: 2px;" src="' + image_path + '/break_page.gif" id="' + this.pMainObj.SetBxTag(false, {tag: "hr", params: {value: "<hr/>"}}) + '"/>');
		}
	}
];

// arButtons['Justify'] = ['BXGroupedButton',
	// {
		// id: 'Justify',
		// buttons: [
			// {
				// id : 'JustifyLeft',
				// name : BX_MESS.TBJLeft,
				// cmd : 'JustifyLeft'
			// },
			// {
				// id : 'JustifyCenter',
				// name : BX_MESS.TBJCent,
				// cmd : 'JustifyCenter'
			// },
			// {
				// id : 'JustifyRight',
				// name : BX_MESS.TBJRig,
				// cmd : 'JustifyRight'
			// },
			// {
				// id : 'JustifyFull',
				// name : BX_MESS.TBJFull,
				// cmd : 'JustifyFull'
			// }
		// ]
	// }
// ];

arButtons['JustifyLeft'] = ['BXButton',
	{
		id : 'JustifyLeft',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBJLeft,
		cmd : 'JustifyLeft'
	}
];

arButtons['JustifyCenter'] = ['BXButton',
	{
		id : 'JustifyCenter',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBJCent,
		cmd : 'JustifyCenter'
	}
];

arButtons['JustifyRight'] = ['BXButton',
	{
		id : 'JustifyRight',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBJRig,
		cmd : 'JustifyRight'
	}
];

arButtons['JustifyFull'] = ['BXButton',
	{
		id : 'JustifyFull',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBJFull,
		cmd : 'JustifyFull'
	}
];

arButtons['InsertOrderedList'] = ['BXButton',
	{
		id : 'InsertOrderedList',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBOList,
		cmd : 'InsertOrderedList'
	}
];

arButtons['InsertUnorderedList'] = ['BXButton',
	{
		id : 'InsertUnorderedList',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBUnOList,
		cmd : 'InsertUnorderedList'
	}
];

arButtons['Outdent'] = ['BXButton',
	{
		id : 'Outdent',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.TBOutdent,
		title : BX_MESS.TBOutdent + "(Shift + Tab)",
		cmd : 'Outdent'
	}
];

arButtons['Indent'] = ['BXButton',
	{
		id : 'Indent',
		iconkit : '_global_iconkit.gif',
		title : BX_MESS.TBIndent + "(Tab)",
		name : BX_MESS.TBIndent,
		cmd : 'Indent'
	}
];

arButtons['BackColor'] = ['BXEdColorPicker',
	{
		id : 'BackColor',
		title : BX_MESS.Background_Color,
		disableOnCodeView: true,
		OnChange : function (color)
		{
			if(BX.browser.IsIE())
			{
				this.pMainObj.executeCommand('BackColor', color);
			}
			else
			{
				try{
					this.pMainObj.pEditorDocument.execCommand("styleWithCSS", false, true);
					if (!color)
						this.pMainObj.executeCommand('removeFormat'); //BXClearMozDirtyInRange(this.pMainObj);
					else
						this.pMainObj.executeCommand('hilitecolor', color);

					this.pMainObj.pEditorDocument.execCommand("styleWithCSS", false, false);
				}catch(e){_alert('Error: toolbarbuttons.js: arButtons["BackColor"]');}
			}
		}
	}
];

arButtons['ForeColor'] = ['BXEdColorPicker',
	{
		id : 'ForeColor',
		title : BX_MESS.Foreground_Color,
		disableOnCodeView : true,
		OnChange : function (color)
		{
			if (!color && !BX.browser.IsIE())
				this.pMainObj.executeCommand('removeFormat');
			else
				this.pMainObj.executeCommand('ForeColor', color);
		}
	}
];


var __BXSrcBtn = function (mode, split_mode)
{
	this.Check(mode==this.t);
	this._OnChangeView(mode, split_mode);
}

arButtons['wysiwyg'] = ['BXButton',
	{
		id : 'wysiwyg',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBWysiwyg,
		t : 'html',
		OnChangeView : __BXSrcBtn,
		handler : function () {this.pMainObj.SetView('html');}
	}
];

arButtons['source'] = ['BXButton',
	{
		id : 'source',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSrc,
		t : 'code',
		OnChangeView : __BXSrcBtn,
		handler : function () {this.pMainObj.SetView('code');}
	}
];

arButtons['split'] = ['BXButton',
	{
		id : 'split',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSplitmode,
		t : 'split',
		OnChangeView : __BXSrcBtn,
		handler : function () {this.pMainObj.SetView('split');}
	}
];


arButtons['Wrap'] = ['BXButton',
	{
		id : 'Wrap',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.WrapTitle,
		codeEditorMode : true,
		hideInHtmlEditorMode : true,
		//hideInFF : true,
		hideCondition: function() {return (navigator.appName=='Netscape');},
		defaultState : true,
		handler : function ()
		{
			if (!this.pWnd.checked)
			{
				this.pMainObj.pSourceFrame.wrap = 'soft';
				this.Check(true);
			}
			else
			{
				this.pMainObj.pSourceFrame.wrap = 'OFF';
				this.Check(false);
			}
			this.pMainObj.pSourceFrame.focus();
		}
	}
];

arButtons['HeadingList'] =
	['BXEdList',
		{
			id: 'HeadingList',
			field_size: 75,
			width: 210,
			title: '(' + BX_MESS.Format + ')',
			disableOnCodeView: true,
			bAdminConfigure: true,
			//bSetGlobalStyles: false,
			values:
			[
				{value: 'p', name: 'Normal'},
				{value: 'h1', name: 'Heading 1'},
				{value: 'h2', name: 'Heading 2'},
				{value: 'h3', name: 'Heading 3'},
				{value: 'h4', name: 'Heading 4'},
				{value: 'h5', name: 'Heading 5'},
				{value: 'h6', name: 'Heading 6'},
				{value: 'pre', name: 'Preformatted'}
			],
			OnSelectionChange: function (){
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
			OnChange: function (selected){this.pMainObj.executeCommand('FormatBlock', (selected['value'].length>0?'<' + selected['value']+'>':'<p>'));},
			OnDrawItem: function (item)
			{
				if (!styleList_render_style)
					return item['name'];
				return (item['value'].length <= 0 ? item['name'] : '<'+item['value']+'>'+item['name']+'</'+item['value']+'>');
			}
		}
	];

arButtons['FontName'] =
	['BXEdList',
		{
			id: 'FontName',
			field_size: 75,
			title: '('+BX_MESS.Font+')',
			disableOnCodeView: true,
			values:
			[
				{value: 'Times New Roman', name: 'Times New Roman'},
				{value: 'Courier', name: 'Courier'},
				{value: 'Arial', name: 'Arial'},
				{value: 'Tahoma', name: 'Tahoma'},
				{value: 'Verdana', name: 'Verdana'},
				{value: 'Georgia', name: 'Georgia'}
			],
			OnSelectionChange: function (){
					this.SelectByVal(this.pMainObj.queryCommand('FontName'));
				},
			OnChange: function (selected){this.pMainObj.executeCommand('FontName', selected['value']);},
			//text-overflow : ellipsis;
			OnDrawItem: function (item){return '<span style="white-space: nowrap; font-family:'+item['name']+';font-size: 10pt;">'+item['name']+'</span>';}
		}
	];

arButtons['FontSize'] =
	['BXEdList',
		{
			id: 'FontSize',
			width: 250,
			field_size: 75,
			title: '(' + BX_MESS.Size + ')',
			disableOnCodeView: true,
			values:
			[
				{value: '1', name: 'xx-small'},
				{value: '2', name: 'x-small'},
				{value: '3', name: 'small'},
				{value: '4', name: 'medium'},
				{value: '5', name: 'large'},
				{value: '6', name: 'x-large'},
				{value: '7', name: 'xx-large'}
			],
			OnSelectionChange: function (){
					this.SelectByVal(this.pMainObj.queryCommand('FontSize'));
				},
			OnChange: function (selected){this.pMainObj.executeCommand('FontSize', selected['value']);},
			OnDrawItem: function (item){return '<font size="'+item['value']+'">'+item['name']+'</font>';}
		}
	];

arButtons['FontStyle'] = ['BXStyleList',
	{
		id: 'FontStyle',
		width: 200,
		field_size: 130,
		title: '(' + BX_MESS.Style + ')',
		disableOnCodeView: true,
		filter: ['DEFAULT'],
		prevType : false,
		deleteIfNoItems : true,
		OnChangeElement: function (arSelected)
		{
			if (this.pElement.tagName.toUpperCase() == 'BODY')
				this.pElement.innerHTML = '<span class="'+arSelected["value"]+'">'+this.pElement.innerHTML+'</span>';
			else
				SAttr(this.pElement, 'className', arSelected["value"]);
		},
		OnChangeText: function (arSelected)
		{
			var pElement = this.pMainObj.GetSelectedNode(true);
			if(arSelected["value"] == '')
				this.RemoveClass(pElement);
			else
				this.OptimizeSelection(
				{
					nodes: this.pMainObj.WrapSelectionWith("span", {props: {className: arSelected["value"]}}),
					className: arSelected["value"]
				});
		},
		OnSelectionChange: function ()
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
				this.OnChange = this.OnChangeElement;
				this.SelectByVal(this.pElement.className);
			}
			else
			{
				this.OnChange = this.OnChangeText;
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
					if(BX.browser.IsIE() && pElement.parentElement && (pElement = pElement.parentElement()))
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

arButtons['Template'] =
	['BXEdList',
		{
			id: 'Template',
			width: 240,
			maxHeight: 250,
			field_size: 150,
			title: '('+BX_MESS.Template+')',
			values: window.arBXTemplates,
			bSetFontSize: true,
			bSetGlobalStyles: true,
			OnCreate: function ()
			{
				this.pMainObj.pTemplateListbox = this;
			},
			OnInit: function ()
			{
				this.SelectByVal(this.pMainObj.templateID);
			},
			OnChange: function (selected)
			{
				this.pMainObj.LoadTemplateParams(selected['value']);
				if (this.pMainObj.pComponent2Taskbar)
					checkComp2Template(this.pMainObj);
			}
		}
	];

// FLASH, BREAK, .....
arButtons['page_break'] = [
	'BXButton',
	{
		id : 'page_break',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.PAGE_BREAK,
		handler : function ()
		{
			this.pMainObj.insertHTML('<img style="width: 100%; height: 4px;" src="' + image_path + '/break_page.gif" id="' + this.pMainObj.SetBxTag(false, {tag: "break_page"}) + '"/>');
		}
	}
];

arButtons['break_tag'] = [
	'BXButton',
	{
		id : 'break_tag',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.BREAK_TAG,
		handler : function ()
		{
			this.pMainObj.insertHTML("<img src=\"" + image_path + "/break_tag.gif\" id=\"" + this.pMainObj.SetBxTag(false, {tag: 'break'}) + "\"/>");
		}
	}
];

arButtons['insert_flash'] = [
	'BXButton',
	{
		id : 'insert_flash',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.FLASH,
		handler : function () {this.bNotFocus = true; this.pMainObj.OpenEditorDialog("flash", false, 500, {bUseTabControl: true, pMainObj: this.pMainObj});}
	}
];

arButtons['edit_flash'] = [
	'BXButton',
	{
		id : 'insert_flash',
		iconkit : '_global_iconkit.gif',
		name : BX_MESS.FLASH_MOV,
		handler : function ()
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("flash", false, 500, {bUseTabControl: true, pMainObj: this.pMainObj});
		}
	}
];

arButtons['_taskbar_close'] = ['BXButton', {id : '_taskbar_close', iconkit : '_global_iconkit.gif', name :  BX_MESS.Close, title : BX_MESS.Close_toolbar, handler : function (arParams){arParams.pTaskbar.Close();}}];

arButtons['_taskbar_refresh'] = ['BXButton',{id : '_taskbar_refresh', iconkit : '_global_iconkit.gif', name : BX_MESS.RefreshData, title : BX_MESS.RefreshData, handler: function(arParams){arParams.pTaskbar.ClearCache();}}];

arButtons['_taskbar_hide'] = ['BXButton', {id : '_taskbar_hide', iconkit : '_global_iconkit.gif', name : BX_MESS.Hide, title : BX_MESS.Hide_toolbar, handler : function (arParams){arParams.pTaskbar.pTaskbarSet.Hide();}}];

arButtons['_settings'] = ['BXButton', {id : '_settings', iconkit : '_global_iconkit.gif', name : BX_MESS.Settings, title : BX_MESS.Settings_toolbar, handler :function (arParams) {this.pMainObj.OpenEditorDialog("settings", false, 600, {bUseTabControl: true, lightMode: lightMode, PHPGetParams: '&light_mode=' + (lightMode ? 'Y' : 'N')});}}];

var arCMButtons = [];
if (BX.browser.IsIE())
	arCMButtons["DEFAULT"] = [arButtons['Cut'], arButtons['Copy'], arButtons['Paste']];
else
	arCMButtons["DEFAULT"] = []; // FF - operations 'copy', 'cut' and 'paste' are unallowed

arCMButtons["A"] = [arButtons['CreateLink'], arButtons['deletelink']];
arCMButtons["IMG"] = [arButtons['image']];
arCMButtons["FLASH"] = [arButtons['edit_flash']];

arCMButtons["TABLE"] = [
	{
		isgroup : true,
		id : 'table_cell',
		name : BX_MESS.TBInsCell,
		elements : [
			arButtons['insertcell_before'],
			arButtons['insertcell_after'],
			arButtons['deletecell'],
			arButtons['mergecells'],
			arButtons['merge_right'],
			arButtons['merge_bottom'],
			arButtons['split_hor'],
			arButtons['split_ver']
		]
	},
	{
		isgroup : true,
		id : 'table_row',
		name : BX_MESS.TBInsRow,
		elements : [
			arButtons['insertrow_before'],
			arButtons['insertrow_after'],
			arButtons['deleterow'],
			arButtons['mergeallcellsinrow']
		]
	},
	{
		isgroup : true,
		id : 'table_column',
		name : BX_MESS.TBInsColumn,
		elements : [
			arButtons['insertcolumn_before'],
			arButtons['insertcolumn_after'],
			arButtons['deletecolumn'],
			arButtons['mergeallcellsincolumn']
		]
	},
	arButtons['deltable'],
	arButtons['tableprop']
];

arCMButtons["_TASKBAR_DEFAULT"] = [arButtons['_taskbar_hide'], arButtons['_settings'], arButtons['_taskbar_close']];
arCMButtons["_TASKBAR_PROPERTIES"] = [arButtons['_taskbar_hide'], arButtons['_settings']];
arCMButtons["_TASKBAR_CACHED"] = [arButtons['_taskbar_hide'], arButtons['_taskbar_refresh'], arButtons['_settings'], arButtons['_taskbar_close']];

if (!window.arToolbars)
	arToolbars = {};

arToolbars['standart'] = [
	BX_MESS.TBSStandart,
	[
	arButtons['Fullscreen'], 'separator',
	arButtons['Settings'], arButtons['separator'],
	arButtons['Cut'], arButtons['Copy'], arButtons['Paste'], arButtons['pasteword'], arButtons['pastetext'], arButtons['SelectAll'], arButtons['separator'],
	arButtons['Undo'], arButtons['Redo'], arButtons['separator'],
	arButtons['borders'], 'separator',
	arButtons['table'], arButtons['anchor'], arButtons['CreateLink'], arButtons['deletelink'], arButtons['image'],  'separator',
	arButtons['SpecialChar'],
	arButtons['page_break'],
	arButtons['break_tag'],
	arButtons['insert_flash']
	]
];

arToolbars['style'] = [
	BX_MESS.TBSStyle,
		[arButtons['FontStyle'], arButtons['HeadingList'], arButtons['FontName'], arButtons['FontSize'], arButtons['separator'],
			arButtons['Bold'], arButtons['Italic'], arButtons['Underline'], arButtons['Strike'], 'separator',
			arButtons['RemoveFormat'], arButtons['Optimize']
		]
	];

arToolbars['formating'] = [
	BX_MESS.TBSFormat,
			[arButtons['InsertHorizontalRule'], arButtons['separator'],
				//arButtons['Justify'], arButtons['separator'],
				arButtons['JustifyLeft'], arButtons['JustifyCenter'], arButtons['JustifyRight'], arButtons['JustifyFull'], arButtons['separator'],
				arButtons['InsertOrderedList'], arButtons['InsertUnorderedList'],arButtons['separator'],
				arButtons['Outdent'], arButtons['Indent'], arButtons['separator'],
				arButtons['BackColor'], arButtons['ForeColor']
			]
	];

arToolbars['source'] = [
		BX_MESS.TBSEdit,
		[arButtons['wysiwyg'], arButtons['source'], arButtons['split'], arButtons['Wrap']]
	];

arToolbars['template'] = [
	BX_MESS.TBSTemplate,
	[arButtons['Template']]
	];

var arDefaultTBPositions = {
		standart: [0, 0, 0],
		template: [0, 0, 2],
		source: [1, 0, 0],
		style: [0, 1, 0],
		formating: [0, 1, 1]
	};


pPropertybarHandlers['table'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = [];
		var tProp;
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['table'])
		{
			var tProp = arBarHandlersCache['table'][0];
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
			//1
			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList',
				{
					id: 'tableStyleList',
					width: 200,
					field_size: 80,
					title: '(' + BX_MESS.Style + ')',
					tag_name: 'TABLE',
					filter: ['TABLE', 'DEFAULT'],
					disableOnCodeView: true,
					OnChangeElement: function (arSelected)
					{
						SAttr(this.pElement, 'className', arSelected["value"]);
					},
					OnChangeText: function (arSelected)
					{
						var pElement = this.pMainObj.GetSelectedNode(true);
						if(arSelected["value"] == '')
							this.RemoveClass(pElement);
						else
							this.OptimizeSelection(
							{
								nodes: this.pMainObj.WrapSelectionWith("span", {props: {className: arSelected["value"]}}),
								className: arSelected["value"]
							});
					},
					OnSelectionChange: function ()
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
							this.OnChange = this.OnChangeElement;
							this.SelectByVal(this.pElement.className);
						}
						else
						{
							this.OnChange = this.OnChangeText;
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
								if(BX.browser.IsIE() && pElement.parentElement && (pElement = pElement.parentElement()))
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
				});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);
			pObjTemp = null;

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropBG}));
			pObjTemp = null;

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXEdColorPicker', {with_input: true});
			pTaskbar.arElements['bgcolor'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);
			pObjTemp = null;

			////
			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'CellPadding: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['cellpadding'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': '5'}));

			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropAlign}));

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXTAlignPicker', {'type': 'table'});
			pTaskbar.arElements['talign'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'CellSpacing: '}));

			cell = row.insertCell(-1);
			pTaskbar.arElements['cellspacing'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': '5'}));
			cell = row.insertCell(-1);
			cell = row.insertCell(-1);

			arBarHandlersCache['table'] = [tProp, pTaskbar.arElements];
		}
		tProp.id = 'tProp_leak';
		pTaskbar.pCellProps.appendChild(tProp);
		cell = null;
		row = null;
		tProp = null;
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
	};

	pTaskbar.arElements['height'].onchange = fChange;
	pTaskbar.arElements['width'].onchange = fChange;
	pTaskbar.arElements['border'].onchange = fChange;
	pTaskbar.arElements['cellpadding'].onchange = fChange;
	pTaskbar.arElements['cellspacing'].onchange = fChange;
	pTaskbar.arElements['bgcolor'].OnChange = function (color) {pElement.bgColor = color;};
	pTaskbar.arElements['talign'].OnChange = function (alH) {pElement.align = alH;};
	pTaskbar.arElements['cssclass'].OnChange = function (className) {pElement.className=className.value;};
}

pPropertybarHandlers['td'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = [];

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
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row = tProp.insertRow(-1);

			var cell = row.insertCell(-1); cell.align = 'right';
			oSpan = pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropStyle});
			cell.appendChild(oSpan);
			oSpan = null;

			cell = row.insertCell(-1);

			//2
			var pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXStyleList',
				{
					id: 'tdStyleList',
					width: 200,
					field_size: 80,
					title: '(' + BX_MESS.Style + ')',
					tag_name: 'TD',
					filter: ['TD', 'DEFAULT'],
					disableOnCodeView: true,
					OnChangeElement: function (arSelected) {SAttr(this.pElement, 'className', arSelected["value"]);},
					OnChangeText: function (arSelected)
					{
						var pElement = this.pMainObj.GetSelectedNode(true);
						if(arSelected["value"] == '')
							this.RemoveClass(pElement);
						else
							this.OptimizeSelection(
							{
								nodes: this.pMainObj.WrapSelectionWith("span", {props: {className: arSelected["value"]}}),
								className: arSelected["value"]
							});
					},
					OnSelectionChange: function ()
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
							this.OnChange = this.OnChangeElement;
							this.SelectByVal(this.pElement.className);
						}
						else
						{
							this.OnChange = this.OnChangeText;
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
								if(BX.browser.IsIE() && pElement.parentElement && (pElement = pElement.parentElement()))
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
				});
			pTaskbar.arElements['cssclass'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);

			cell = row.insertCell(-1); cell.align = 'right';
			var oSpan = pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropSize});
			cell.appendChild(oSpan);
			oSpan = null;

			cell = row.insertCell(-1);
			var oInput = pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropW, 'type': 'text'});
			pTaskbar.arElements['width_val'] = cell.appendChild(oInput);
			oInput = null;
			var oSpan = pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':'x'});
			cell.appendChild(oSpan);
			oSpan = null;
			var oInput = pTaskbar.pMainObj.CreateElement("INPUT", {'size':'5', 'title': BX_MESS.TPropH, 'type': 'text'});
			pTaskbar.arElements['height_val'] = cell.appendChild(oInput);
			oInput = null;

			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			var oSpan = pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropAlign});
			cell.appendChild(oSpan);
			oSpan = null;

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXTAlignPicker');
			pTaskbar.arElements['talign'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);
			pObjTemp = null;

			cell = row.insertCell(-1); cell.align = 'right';
			var oSpan = pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropBG});
			cell.appendChild(oSpan);
			oSpan = null;

			cell = row.insertCell(-1);
			pObjTemp = pTaskbar.pMainObj.CreateCustomElement('BXEdColorPicker', {'with_input': true});
			pTaskbar.arElements['bgcolor'] = pObjTemp;
			cell.appendChild(pObjTemp.pWnd);
			pObjTemp = null;

			row = tProp.insertRow(-1);
			cell = row.insertCell(-1); cell.align = 'right';
			var oSpan = pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML': BX_MESS.TPropNoWrap+':'});
			cell.appendChild(oSpan);
			oSpan = null;

			cell = row.insertCell(-1);
			var oInput = pTaskbar.pMainObj.CreateElement("INPUT", {'title': BX_MESS.TPropNoWrap, 'type': 'checkbox'});
			pTaskbar.arElements['nowrap'] = cell.appendChild(oInput);
			oInput = null;

			arBarHandlersCache['td'] = [tProp, pTaskbar.arElements];
		}
		tProp.id = 'tProp_td_leak';
		pTaskbar.pCellProps.appendChild(tProp);

		pObjTemp = null;
		cell = null;
		row = null
		tProp = null;
	}

	pTaskbar.arElements['width_val'].value = pElement.width;
	pTaskbar.arElements['height_val'].value = pElement.height;
	pTaskbar.arElements['nowrap'].checked = pElement.noWrap;
	pTaskbar.arElements['bgcolor'].SetValue(pElement.bgColor);
	pTaskbar.arElements['talign'].SetValue(pElement.align, pElement.vAlign);
	pTaskbar.arElements['cssclass'].SelectByVal(pElement.className);

	var fChange = function ()
	{
		pElement.width = pTaskbar.arElements['width_val'].value;
		pElement.height = pTaskbar.arElements['height_val'].value;
		pElement.noWrap = pTaskbar.arElements['nowrap'].checked;
	};

	pTaskbar.arElements['height_val'].onchange = fChange;
	pTaskbar.arElements['width_val'].onchange = fChange;
	pTaskbar.arElements['nowrap'].onclick = fChange;
	pTaskbar.arElements['bgcolor'].OnChange = function (color) {pElement.bgColor = color;};
	pTaskbar.arElements['talign'].OnChange = function (alH, alV) {pElement.align = alH; pElement.vAlign = alV;};
	pTaskbar.arElements['cssclass'].OnChange = function (className) {pElement.className = className.value;};
}

pPropertybarHandlers['a'] = function (bNew, pTaskbar, pElement)
{
	var tProp, row, cell;

	if(bNew)
	{
		pTaskbar.arElements = [];
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		if(arBarHandlersCache['a'])
		{
			tProp = arBarHandlersCache['a'][0];
			pTaskbar.arElements = arBarHandlersCache['a'][1];
		}
		else
		{
			tProp = BX.create('TABLE', {props: {className : "bxtaskbarprops"}});
			row = tProp.insertRow(-1);

			// Url
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_a_url" class="bxpr-main">' + BX_MESS.TPropURL + ':</label>'});
			pTaskbar.arElements.href = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '40', title: BX_MESS.TPropURL, type: 'text', id: "bxp_a_url"}}));

			// Target
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_a_target">' + BX_MESS.TPropTarget + ':</label>'});
			pTaskbar.arElements.target = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("SELECT", {props: {title: BX_MESS.TPropTarget, id: "bxp_a_target"}}));
			var arOpt = [
				['', '- ' + BX_MESS.NoVal + ' -'],
				['_blank', BX_MESS.TPropTargetBlank],
				['_parent', BX_MESS.TPropTargetParent],
				['_self', BX_MESS.TPropTargetSelf],
				['_top', BX_MESS.TPropTargetTop]
			], i, l = arOpt.length;

			for(i = 0; i < l; i++)
				pTaskbar.arElements.target.options.add(new Option(arOpt[i][1], arOpt[i][0], false, false));

			row = tProp.insertRow(-1);
			// Title
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_a_title" class="bxpr-main">' + BX_MESS.TPropTitle + ':</label>'});
			pTaskbar.arElements.title = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '40', type: 'text', title: BX_MESS.TPropTitle, id: "bxp_a_title"}}));

			// Class
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropStyle + '</label>'});
			pTaskbar.arElements.cssclass = pTaskbar.pMainObj.CreateCustomElement('BXStyleList',
			{
				id: 'linkStyleList',
				width: 200,
				field_size: 120,
				title: '(' + BX_MESS.Style + ')',
				tag_name: 'A',
				filter: ['A', 'DEFAULT'],
				disableOnCodeView: true,
				OnChange: function (arSelected)
				{
					SAttr(this.pElement, 'className', arSelected["value"]);
				}
			});
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.arElements.cssclass.pWnd);

			row = tProp.insertRow(-1);
			// Noindex
			pTaskbar.arElements.noindex = BX.adjust(row.insertCell(-1), {props: {align: 'right'}}).appendChild(BX.create("INPUT", {props: {type: 'checkbox', title: BX_MESS.LinkNoindex, id: "bxp_a_noindex"}}));
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}, html: '<label for="bxp_a_noindex">' + BX_MESS.LinkNoindex + '</label>'});

			// Id
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_a_id">ID:</label>'});
			pTaskbar.arElements.id = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.TPropTitle, id: "bxp_a_id"}}));

			row = tProp.insertRow(-1);
			row.insertCell(-1);
			row.insertCell(-1);
			// Rel
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_a_rel">' + BX_MESS.Rel + ':</label>'});
			pTaskbar.arElements.rel = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.Rel, id: "bxp_a_rel"}}));

			arBarHandlersCache['a'] = [tProp, pTaskbar.arElements];
		}

		pTaskbar.pCellProps.appendChild(tProp);
	}

	var oTag = pTaskbar.pMainObj.GetBxTag(pElement);
	if (oTag.tag != 'a' || !oTag.params)
		return;

	pTaskbar.arElements.href.value = oTag.params.href || '';
	pTaskbar.arElements.target.value = oTag.params.target || '';
	pTaskbar.arElements.title.value = oTag.params.title || '';
	pTaskbar.arElements.id.value = oTag.params.id || '';
	pTaskbar.arElements.noindex.checked = !!oTag.params.noindex;
	if (oTag.params.noindex)
	{
		oTag.params.rel = "nofollow";
		pTaskbar.arElements.rel.disabled = true;
	}
	else
	{
		pTaskbar.arElements.rel.disabled = false;
	}
	pTaskbar.arElements.rel.value = oTag.params.rel || '';

	pTaskbar.arElements.cssclass.pElement = pElement;
	setTimeout(function()
	{
		//pTaskbar.arElements.cssclass.SelectByVal(oTag.params['class'], true);
		pTaskbar.arElements.cssclass.SelectByVal(pElement.className || "", true);
	}, 10);

	var arEls = ['href', 'title', 'id', 'rel', 'target'];

	pTaskbar.arElements.href.onchange =
	pTaskbar.arElements.title.onchange =
	pTaskbar.arElements.id.onchange =
	pTaskbar.arElements.rel.onchange =
	pTaskbar.arElements.noindex.onclick =
	pTaskbar.arElements.target.onchange = function ()
	{
		oTag.params.href = pTaskbar.arElements.href.value;
		oTag.params.title = pTaskbar.arElements.title.value;
		oTag.params.id = pTaskbar.arElements.id.value;
		oTag.params.target = pTaskbar.arElements.target.value;
		oTag.params.noindex = !!pTaskbar.arElements.noindex.checked;
		if (oTag.params.noindex)
		{
			pTaskbar.arElements.rel.value = "nofollow";
			pTaskbar.arElements.rel.disabled = true;
		}
		else
		{
			if (pTaskbar.arElements.rel.value == "nofollow" && this != pTaskbar.arElements.rel)
				pTaskbar.arElements.rel.value = "";
			pTaskbar.arElements.rel.disabled = false;
		}
		oTag.params.rel = pTaskbar.arElements.rel.value;

		for (i = 0; i < l; i++)
			if (!pTaskbar.pMainObj.pParser.isPhpAttribute(oTag.params[arEls[i]]))
				SAttr(pElement, arEls[i], oTag.params[arEls[i]]);

		pTaskbar.pMainObj.SetBxTag(pElement, oTag);
	};
};

pPropertybarHandlers['anchor'] = function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = [];
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
			tProp.cellSpacing = 0;
			tProp.cellPadding = 1;
			var row, cell;

			row = tProp.insertRow(-1); cell = row.insertCell(-1); cell.align = 'right'; cell.width="50%";
			cell.appendChild(pTaskbar.pMainObj.CreateElement("SPAN", {'innerHTML':BX_MESS.TPropName}));

			cell = row.insertCell(-1); cell.width="50%";
			pTaskbar.arElements['name'] = cell.appendChild(pTaskbar.pMainObj.CreateElement("INPUT", {'size':'40', 'title': BX_MESS.TPropAnchorName, 'type': 'text'}));

			arBarHandlersCache['anchor'] = [tProp, pTaskbar.arElements];
		}

		pTaskbar.pCellProps.appendChild(tProp);
	}

	var bxTag = pTaskbar.pMainObj.GetBxTag(pElement);
	pTaskbar.arElements['name'].value = pTaskbar.pMainObj.pParser.GetAnchorName(bxTag.params.value);

	pTaskbar.arElements['name'].onchange = function ()
	{
		var bxTag = pTaskbar.pMainObj.GetBxTag(pElement);
		bxTag.params.value = pTaskbar.pMainObj.pParser.GetAnchorName(bxTag.params.value, pTaskbar.arElements.name.value);
		pTaskbar.pMainObj.SetBxTag(false, bxTag);
	};
};

pPropertybarHandlers['img'] = function (bNew, pTaskbar, pElement)
{
	var tProp, row, cell;
	if(bNew)
	{
		pTaskbar.arElements = [];
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
		var arBarHandlersCache = [];

		if(arBarHandlersCache['img'])
		{
			tProp = arBarHandlersCache['img'][0];
			pTaskbar.arElements = arBarHandlersCache['img'][1];
		}
		else
		{
			tProp = BX.create('TABLE', {props: {className : "bxtaskbarprops"}});
			row = tProp.insertRow(-1);

			// Src
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_src" class="bxpr-main">'+BX_MESS.TPropImgPath+'</label>'});
			pTaskbar.arElements.src = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '40', title: BX_MESS.TPropImgPath2, type: 'text', id: "bxp_img_src"}}));

			// hspace
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_hspace">' + BX_MESS.TPropImgHSpace + '</label>'});

			pTaskbar.arElements.hspace = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '6', title: BX_MESS.TPropImgHSpace, type: 'text', id: "bxp_img_hspace"}}));

			row = tProp.insertRow(-1);
			// Width and height
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_width" class="bxpr-main">' + BX_MESS.TPropSize + '</label>'});
			var r = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("TABLE", {props: {className: "bx-sp-props-tbl"}})).insertRow(-1);
			var cell1 = r.insertCell(-1);
			pTaskbar.arElements.width = cell1.appendChild(BX.create("INPUT", {props: {size: '5', title: BX_MESS.TPropW, type: 'text', id: "bxp_img_width"}}));
			cell1.appendChild(BX.create("SPAN", {text : 'x', style: {margin: "0 3px"}}));
			pTaskbar.arElements.height = cell1.appendChild(BX.create("INPUT", {props: {size: '5', title: BX_MESS.TPropH, type: 'text', id: "bxp_img_height"}}));

			pTaskbar.arElements.saveProp = r.insertCell(-1).appendChild(BX.create("INPUT", {props: {title: BX_MESS.TPropW, type: 'checkbox', id: "bxp_img_save_prop", checked: true}, style: {marginLeft: "10px"}}));

			r.insertCell(-1).appendChild(BX.create("LABEL", {text: BX_MESS.SaveProp})).setAttribute("for", "bxp_img_save_prop");

			// Vspace
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_vspace">' + BX_MESS.TPropImgVSpace + '</label>'});
			pTaskbar.arElements.vspace = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '6', title: BX_MESS.TPropImgVSpace, type: 'text', id: "bxp_img_vspace"}}));

			row = tProp.insertRow(-1);
			// Title
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_title" class="bxpr-main">' + BX_MESS.TPropImgTitle + ':</label>'});
			pTaskbar.arElements.title = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '40', title: BX_MESS.TPropImgTitle, type: 'text', id: "bxp_img_title"}}));

			// Style
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropStyle + '</label>'});
			pTaskbar.arElements.cssclass = pTaskbar.pMainObj.CreateCustomElement('BXStyleList',
				{
					id: 'imgStyleList',
					width: 200,
					field_size: 120,
					title: '(' + BX_MESS.Style + ')',
					tag_name: 'IMG',
					filter: ['IMG', 'DEFAULT'],
					disableOnCodeView: true,
					OnChange: function(arSelected)
					{
						SAttr(this.pElement, 'className', arSelected["value"]);
					}
				}
			);
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.arElements.cssclass.pWnd);

			row = tProp.insertRow(-1);
			// Alt
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_alt">' + BX_MESS.TPropImgAlt + ':</label>'});
			pTaskbar.arElements.alt = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '40', title: BX_MESS.TPropImgAlt, type: 'text', id: "bxp_img_alt"}}));

			// Align
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropAlign + '</label>'});
			pTaskbar.arElements.talign = pTaskbar.pMainObj.CreateCustomElement('BXTAlignPicker',
				{
					type: 'image',
					OnChange: function (align)
					{
						pElement.align = align;
					}
				}
			);
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.arElements.talign.pWnd);

			row = tProp.insertRow(-1);
			// Border
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_img_border">' + BX_MESS.TPropImgBorder + ':</label>'});
			pTaskbar.arElements.border = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {size: '4', title: BX_MESS.TPropImgBorder, type: 'text', id: "bxp_img_border"}}));

			// Empty cells
			row.insertCell(-1);
			row.insertCell(-1);

			arBarHandlersCache['img'] = [tProp, pTaskbar.arElements];
		}
		pTaskbar.pCellProps.appendChild(tProp);
	}

	var oTag = pTaskbar.pMainObj.GetBxTag(pElement);
	if (oTag.tag != 'img' || !oTag.params)
		return;

	var arEls = ['src', 'alt', 'title', 'hspace', 'vspace', 'border', 'width', 'height'], i, l = arEls.length;

	pTaskbar.arElements.src.value = oTag.params.src || '';
	pTaskbar.arElements.alt.value = oTag.params.alt || '';
	pTaskbar.arElements.title.value = oTag.params.title || '';
	pTaskbar.arElements.hspace.value = oTag.params.hspace || '';
	pTaskbar.arElements.vspace.value = oTag.params.vspace || '';
	pTaskbar.arElements.border.value = oTag.params.border || '';
	pTaskbar.arElements.talign.SetValue(oTag.params.align);

	pTaskbar.arElements.cssclass.pElement = pElement;
	pTaskbar.arElements.cssclass.SelectByVal(pElement.className || "", true);

	var
		w1 = parseInt(pElement.style.width) || parseInt(pElement.width) || parseInt(pElement.offsetWidth),
		h1 = parseInt(pElement.style.height) || parseInt(pElement.height) || parseInt(pElement.offsetHeight);

	pTaskbar.arElements.width.value = w1;
	pTaskbar.arElements.height.value = h1;

	if (w1 && h1)
		pTaskbar.iRatio = w1 / h1;
	else
		pTaskbar.iRatio = 1;

	var fChange = function ()
	{
		oTag.params.src = pTaskbar.arElements.src.value;
		oTag.params.alt = pTaskbar.arElements.alt.value;
		oTag.params.title = pTaskbar.arElements.title.value;
		oTag.params.hspace = pTaskbar.arElements.hspace.value;
		oTag.params.vspace = pTaskbar.arElements.vspace.value;
		oTag.params.border = pTaskbar.arElements.border.value;

		var w = parseInt(pTaskbar.arElements.width.value);
		if (isNaN(w))
		{
			pElement.removeAttribute('width');
			pElement.style.width = "";
		}
		else
		{
			pElement.removeAttribute('width');
			if (parseInt(pElement.style.width) > 0)
				pElement.style.width = w + "px";
			else
				SAttr(pElement, 'width', w);
		}

		var h = parseInt(pTaskbar.arElements.height.value);
		if (isNaN(h))
		{
			pElement.removeAttribute('height');
			pElement.style.height = "";
		}
		else
		{
			pElement.removeAttribute('height');
			if (parseInt(pElement.style.height) > 0)
				pElement.style.height = h + "px";
			else
				SAttr(pElement, 'height', h);
		}

		for (i = 0; i < l; i++)
		{
			if (oTag.params[arEls[i]] && !pTaskbar.pMainObj.pParser.isPhpAttribute(oTag.params[arEls[i]]) && arEls[i] !== 'width' && arEls[i] !== 'height')
				SAttr(pElement, arEls[i], oTag.params[arEls[i]]);
		}

		pTaskbar.pMainObj.SetBxTag(pElement, oTag);
	}

	for (i = 0; i < l; i++)
		if (pTaskbar.arElements[arEls[i]])
			pTaskbar.arElements[arEls[i]].onchange = fChange;

	// Save proportion feature
	pTaskbar.arElements.saveProp.onclick = function()
	{
		if (this.checked)
			pTaskbar.arElements.width.onchange();
	};

	pTaskbar.arElements.width.onchange = function()
	{
		var wval = parseInt(this.value);
		if (isNaN(wval))
			return;
		pTaskbar.arElements.width.value = wval;
		if (pTaskbar.arElements.saveProp.checked && pTaskbar.iRatio)
			pTaskbar.arElements.height.value = Math.round(wval / pTaskbar.iRatio);
		fChange();
	};

	pTaskbar.arElements.height.onchange = function()
	{
		var hval = parseInt(this.value);
		if (isNaN(hval))
			return;

		pTaskbar.arElements.height.value = hval;
		if (pTaskbar.arElements.saveProp.checked)
			pTaskbar.arElements.width.value = parseInt(hval * pTaskbar.iRatio);

		fChange();
	};

	pTaskbar.arElements.cssclass.onchange = function (sel) {pElement.className = sel['value'];};
	pTaskbar.arElements.talign.OnChange = function (align) {pElement.align = align;};
};

pPropertybarHandlers['comments'] =
pPropertybarHandlers['script'] =
pPropertybarHandlers['php'] =
pPropertybarHandlers['code'] = // For .Net
pPropertybarHandlers['aspx_comment'] = // For .Net
pPropertybarHandlers['.net component'] = // For .Net
function (bNew, pTaskbar, pElement)
{
	if(bNew)
	{
		pTaskbar.arElements = {};
		pTaskbar.arElements.text = pTaskbar.pCellProps.appendChild(BX.create("TEXTAREA", {props: {cols: '60'}, style:{width: "100%", height: "120px"}}));

		if (IEplusDoctype)
			pTaskbar.arElements.text.rows = "20";
	}

	var bxTag = pTaskbar.pMainObj.GetBxTag(pElement);

	pTaskbar.arElements.text.value = bxTag.params.value;
	pTaskbar.arElements.text.onchange = function ()
	{
		var bxTag = pTaskbar.pMainObj.GetBxTag(pElement);
		bxTag.params.value = pTaskbar.arElements.text.value;
		pTaskbar.pMainObj.SetBxTag(false, bxTag);
	};
};

pPropertybarHandlers['default'] = function (bNew, pTaskbar, pElement)
{
	if(!bNew)
		return;

	pTaskbar.arElements = [];
	var tProp, row;
	var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;
	if(arBarHandlersCache['default'])
	{
		tProp = arBarHandlersCache['default'][0];
		pTaskbar.arElements = arBarHandlersCache['default'][1];
	}
	else
	{
		tProp = BX.create('TABLE', {props: {className : "bxtaskbarprops"}});
		row = tProp.insertRow(-1);

		// Style
		BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropStyle + '</label>'});

		pTaskbar.arElements.cssclass = pTaskbar.pMainObj.CreateCustomElement('BXStyleList',
			{
				id: 'defaultStyleList',
				width: 200,
				field_size: 80,
				title: '(' + BX_MESS.Style + ')',
				filter: ['DEFAULT'],
				disableOnCodeView: true,
				OnChangeElement : function (arSelected)
				{
					SAttr(this.pElement, 'className', arSelected["value"]);
				},
				OnChangeText : function (arSelected)
				{
					var pElement = this.pMainObj.GetSelectedNode(true);
					if(arSelected["value"] == '')
						this.RemoveClass(pElement);
					else
						this.OptimizeSelection(
						{
							nodes: this.pMainObj.WrapSelectionWith("span", {props: {className: arSelected["value"]}}),
							className: arSelected["value"]
						});
				},
				OnSelectionChange: function ()
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
						this.OnChange = this.OnChangeElement;
						this.SelectByVal(this.pElement.className);
					}
					else
					{
						this.OnChange = this.OnChangeText;
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
							if(BX.browser.IsIE() && pElement.parentElement && (pElement = pElement.parentElement()))
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
			});
		BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.arElements.cssclass.pWnd);

		// B I U S
		BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropBIU + '</label>'});
		cell = BX.adjust(row.insertCell(-1), {props: {align: 'left'}});
		// Bold
		cell.appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['Bold'][0], arButtons['Bold'][1]).pWnd);
		// Italic
		cell.appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['Italic'][0], arButtons['Italic'][1]).pWnd);
		// Underline
		cell.appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['Underline'][0], arButtons['Underline'][1]).pWnd);
		// Strike
		cell.appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['Strike'][0], arButtons['Strike'][1]).pWnd);

		row = tProp.insertRow(-1);
		// Font
		BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.Font + ':</label>'});
		BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['FontName'][0], arButtons['FontName'][1]).pWnd);

		// Font size
		BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.Size + ':</label>'});
		BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['FontSize'][0], arButtons['FontSize'][1]).pWnd);

		row = tProp.insertRow(-1);
		// Font Color
		BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropColor + '</label>'});
		BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['ForeColor'][0], arButtons['ForeColor'][1]).pWnd);

		// Background Color
		BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label>' + BX_MESS.TPropBG + '</label>'});
		BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(pTaskbar.pMainObj.CreateCustomElement(arButtons['BackColor'][0], arButtons['BackColor'][1]).pWnd);

		arBarHandlersCache['default'] = [tProp, pTaskbar.arElements];
	}
	pTaskbar.pCellProps.appendChild(tProp);
}

function _DOMHandler(oDocument)
{
	var
		ar = oDocument.getElementsByTagName('DIV'),
		pMainObj = oDocument.pMainObj,
		iLen = ar.length, j;

	for (j = 0; j < iLen; j++)
	{
		try {
			if (ar[j].style.pageBreakAfter == 'always')
			{
				var oImg = BX.adjust(oDocument.createElement("IMG"), {props: {src: image_path + "/break_page.gif"}, style: {height: "4px", width: "100%"}});
				pMainObj.SetBxTag(oImg, {tag: "break_page"});
				ar[j].parentNode.insertBefore(oImg, ar[j]);
				ar[j].parentNode.removeChild(ar[j]);
			}
		}catch(e){continue;}
	}
}
oBXEditorUtils.addDOMHandler(_DOMHandler);

function Flash_Reload(oPreviewCont, src, height, width)
{
	var flash_preview = BX("flash_preview_iframe");
	if (flash_preview)
		flash_preview.parentNode.removeChild(flash_preview);
	oPreviewCont.appendChild(BX.create("IFRAME", {props: {id: "flash_preview_iframe", src: flash_preview_path + "?path=" + BX.util.urlencode(src) + "&width=" + width + "px&height=" + height + "px"}, style: {width: "97%", height: "97%"}}));
}

pPropertybarHandlers['flash'] = function (bNew, pTaskbar, pElement)
{
	var tProp, row, cell, r, tProp1, arOpt;

	if(bNew)
	{
		pTaskbar.arElements = [];
		var arBarHandlersCache = pTaskbar.pMainObj.arBarHandlersCache;

		if(arBarHandlersCache['flash'])
		{
			var tProp = arBarHandlersCache['flash'][0];
			pTaskbar.arElements = arBarHandlersCache['flash'][1];
			pTaskbar.prCell = arBarHandlersCache['flash'][2];
		}
		else
		{
			tProp = BX.create('TABLE', {props: {className : "bxtaskbarprops"}});
			row = tProp.insertRow(-1);

			pTaskbar.prCell = BX.adjust(row.insertCell(-1), {props: {className: 'bxed-flash-prop'}});
			tProp1 = row.insertCell(-1).appendChild(BX.create('TABLE'));

			// Path
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_path" class="bxpr-main">' + BX_MESS.PATH2SWF + ':</label>'});
			pTaskbar.arElements.src = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.PATH2SWF, id: "bxp_swf_path", size: '50'}}));

			// Width & Height
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_width" class="bxpr-main">' + BX_MESS.TPropSize + '</label>'});
			var r = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("TABLE", {props: {className: "bx-sp-props-tbl"}})).insertRow(-1);
			var cell1 = r.insertCell(-1);
			pTaskbar.arElements.width = cell1.appendChild(BX.create("INPUT", {props: {size: '5', title: BX_MESS.TPropW, type: 'text', id: "bxp_swf_width"}}));
			cell1.appendChild(BX.create("SPAN", {text : 'x', style: {margin: "0 3px"}}));
			pTaskbar.arElements.height = cell1.appendChild(BX.create("INPUT", {props: {size: '5', title: BX_MESS.TPropH, type: 'text', id: "bxp_swf_height"}}));
			//pTaskbar.arElements.saveProp = r.insertCell(-1).appendChild(BX.create("INPUT", {props: {title: BX_MESS.TPropW, type: 'checkbox', id: "bxp_swf_save_prop"}, style: {marginLeft: "10px"}}));
			//r.insertCell(-1).appendChild(BX.create("LABEL", {text: BX_MESS.SaveProp})).setAttribute("for", "bxp_img_save_prop");

			// Id
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_id">' + BX_MESS.SWF_ID + ':</label>'});
			pTaskbar.arElements.id = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.SWF_ID, id: "bxp_swf_id", size: '50'}}));

			// Title
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_title">' + BX_MESS.SWF_TITLE + ':</label>'});
			pTaskbar.arElements.title = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.SWF_TITLE, id: "bxp_swf_title", size: '50'}}));

			// Class
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_className">' + BX_MESS.SWF_CLASSNAME + ':</label>'});
			pTaskbar.arElements.className = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.SWF_CLASSNAME, id: "bxp_swf_className", size: '50'}}));

			// Style
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_style">' + BX_MESS.TPropStyle + ':</label>'});
			pTaskbar.arElements.style = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("INPUT", {props: {type: 'text', title: BX_MESS.TPropStyle, id: "bxp_swf_style", size: '50'}}));

			// Quality
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_quality">' + BX_MESS.SWF_QUALITY + ':</label>'});
			pTaskbar.arElements.quality = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("SELECT", {props: {title: BX_MESS.SWF_QUALITY, id: "bxp_swf_quality"}}));

			arOpt = [
				['', '- ' + BX_MESS.NoVal + ' -'],
				['low', 'low'],
				['medium', 'medium'],
				['high', 'high'],
				['autolow', 'autolow'],
				['autohigh', 'autohigh'],
				['best', 'best']
			], i, l = arOpt.length;

			for(i = 0; i < l; i++)
				pTaskbar.arElements.quality.options.add(new Option(arOpt[i][1], arOpt[i][0], false, false));

			// WMode
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_wmode">' + BX_MESS.SWF_WMODE + ':</label>'});
			pTaskbar.arElements.wmode = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("SELECT", {props: {title: BX_MESS.SWF_WMODE, id: "bxp_swf_wmode"}}));

			arOpt = [
				['', '- ' + BX_MESS.NoVal + ' -'],
				['window', 'window'],
				['opaque', 'opaque'],
				['transparent', 'transparent']
			], i, l = arOpt.length;

			for(i = 0; i < l; i++)
				pTaskbar.arElements.wmode.options.add(new Option(arOpt[i][1], arOpt[i][0], false, false));

			// Scale
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_scale">' + BX_MESS.SWF_SCALE + ':</label>'});
			pTaskbar.arElements.scale = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("SELECT", {props: {title: BX_MESS.SWF_SCALE, id: "bxp_swf_scale"}}));

			arOpt = [
				['', '- ' + BX_MESS.NoVal + ' -'],
				['showall', 'showall'],
				['noborder', 'noborder'],
				['exactfit', 'exactfit']
			], i, l = arOpt.length;

			for(i = 0; i < l; i++)
				pTaskbar.arElements.scale.options.add(new Option(arOpt[i][1], arOpt[i][0], false, false));

			// Salign
			row = tProp1.insertRow(-1);
			BX.adjust(row.insertCell(-1), {props: {align: 'right'}, html: '<label for="bxp_swf_salign">' + BX_MESS.SWF_SALIGN + ':</label>'});
			pTaskbar.arElements.salign = BX.adjust(row.insertCell(-1), {props: {align: 'left'}}).appendChild(BX.create("SELECT", {props: {title: BX_MESS.SWF_SALIGN, id: "bxp_swf_salign"}}));

			arOpt = [
				['', '- ' + BX_MESS.NoVal + ' -'],
				['left', 'left'],
				['top', 'top'],
				['right', 'right'],
				['bottom', 'bottom'],
				['top left', 'top left'],
				['top right', 'top right'],
				['bottom left', 'bottom left'],
				['bottom right', 'bottom right']
			], i, l = arOpt.length;

			for(i = 0; i < l; i++)
				pTaskbar.arElements.salign.options.add(new Option(arOpt[i][1], arOpt[i][0], false, false));

			// Autoplay
			row = tProp1.insertRow(-1);
			pTaskbar.arElements.autoplay = BX.adjust(row.insertCell(-1), {props: {align: 'right'}}).appendChild(BX.create("INPUT", {props: {type: 'checkbox', title: BX_MESS.SWF_AUTOPLAY, id: "bxp_swf_autoplay"}}));
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}, html: '<label for="bxp_swf_autoplay">' + BX_MESS.SWF_AUTOPLAY + '</label>'});

			// Loop
			row = tProp1.insertRow(-1);
			pTaskbar.arElements.loop = BX.adjust(row.insertCell(-1), {props: {align: 'right'}}).appendChild(BX.create("INPUT", {props: {type: 'checkbox', title: BX_MESS.SWF_LOOP, id: "bxp_swf_loop"}}));
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}, html: '<label for="bxp_swf_loop">' + BX_MESS.SWF_LOOP + '</label>'});

			// Showmenu
			row = tProp1.insertRow(-1);
			pTaskbar.arElements.showmenu = BX.adjust(row.insertCell(-1), {props: {align: 'right'}}).appendChild(BX.create("INPUT", {props: {type: 'checkbox', title: BX_MESS.SWF_SHOW_MENU, id: "bxp_swf_showmenu"}}));
			BX.adjust(row.insertCell(-1), {props: {align: 'left'}, html: '<label for="bxp_swf_showmenu">' + BX_MESS.SWF_SHOW_MENU + '</label>'});

			arBarHandlersCache['flash'] = [tProp, pTaskbar.arElements, pTaskbar.prCell];
		}
		pTaskbar.pCellProps.appendChild(tProp);
	}

	var oTag = pTaskbar.pMainObj.GetBxTag(pElement);
	if (oTag.tag != 'flash' || !oTag.params)
		return;

	var k, i, el;
	for (i in oTag.params)
	{
		k = (i.toLowerCase() == 'class') ? 'className' : i;
		if (!pTaskbar.arElements[k])
			continue

		if (pTaskbar.arElements[k].type.toLowerCase() == 'checkbox' && oTag.params[i])
			pTaskbar.arElements[k].checked = true;
		else
			pTaskbar.arElements[k].value = oTag.params[i];
	}
	pTaskbar.arElements.width.value = (parseInt(pElement.width) || parseInt(pElement.style.width));
	pTaskbar.arElements.height.value = (parseInt(pElement.height) || parseInt(pElement.style.height));

	Flash_Reload(pTaskbar.prCell, oTag.params.src, 250, 250);

	var fChange = function()
	{
		var
			i = this.id.substr('bxp_swf_'.length),
			k = (i.toLowerCase() == 'classname') ? 'class' : i;

		if (pTaskbar.arElements[i].type.toLowerCase() == 'checkbox')
		{
			oTag.params[k] = pTaskbar.arElements[i].checked || null;
		}
		else
		{
			if (k == 'width' || k == 'height')
			{
				pElement.style.width = (parseInt(pTaskbar.arElements.width.value) || 50) + 'px';
				pElement.style.height = (parseInt(pTaskbar.arElements.height.value) || 25) + 'px';
			}
			else if(k == 'src' && pTaskbar.arElements.src.value)
			{
				Flash_Reload(pTaskbar.prCell, pTaskbar.arElements.src.value, 250, 250);
			}
			oTag.params[k] = pTaskbar.arElements[i].value || null;
		}

		pTaskbar.pMainObj.SetBxTag(pElement, oTag);
	};

	for (i in pTaskbar.arElements)
	{
		el = pTaskbar.arElements[i];
		if (el.type.toLowerCase() == 'checkbox')
			el.onclick = fChange;
		else
			el.onchange = fChange;
	}
};

if (!window.lightMode || window._showAllButtons)
{
	oBXEditorUtils.appendButton("page_break", arButtons['page_break'], "standart");
	oBXEditorUtils.appendButton("break_tag", arButtons['break_tag'], "standart");
	oBXEditorUtils.appendButton("insert_flash", arButtons['insert_flash'], "standart");
}

// *   *   *   *   *   *   *   *   *   *   *   *   *   *   *   *   *   *
// Light mode Toolbars:
if (window.lightMode || window._showAllButtons)
{
	var arGlobalToolbar = [

		/* arButtons['Fullscreen'], */
		arButtons['Settings'],
		arButtons['Cut'], arButtons['Copy'], arButtons['Paste'], arButtons['pasteword'], arButtons['pastetext'], arButtons['SelectAll'],
		arButtons['Undo'], arButtons['Redo'],
		arButtons['borders'], 'separator',
		arButtons['table'], arButtons['anchor'], arButtons['CreateLink'], arButtons['deletelink'], arButtons['image'],
		arButtons['SpecialChar'],
		arButtons['insert_flash'],
		arButtons['InsertHorizontalRule'], 'separator',
		arButtons['InsertOrderedList'], arButtons['InsertUnorderedList'], 'separator',
		arButtons['Outdent'], arButtons['Indent'], 'separator',
		arButtons['JustifyLeft'], arButtons['JustifyCenter'], arButtons['JustifyRight'], arButtons['JustifyFull'],

		'new_line',

		arButtons['wysiwyg'],
		arButtons['source'],
		arButtons['split'],
		arButtons['Wrap'],

		arButtons['FontStyle'], arButtons['HeadingList'], arButtons['FontName'], arButtons['FontSize'], 'separator',
		arButtons['Bold'], arButtons['Italic'], arButtons['Underline'], arButtons['Strike'], 'separator',
		arButtons['RemoveFormat'], arButtons['Optimize'], 'separator',
		arButtons['BackColor'], arButtons['ForeColor']
	];
}