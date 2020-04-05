arButtons['new'] = ['BXButton',
	{
		id : 'new',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBNewPage,
		handler : function ()
		{
			var _this = this;
			setTimeout(function()
			{
				new_doc_list.PopupShow(BX.pos(_this.pWnd));
				BX("new_doc_list").style.zIndex = 2100;
			}, 10);
		}
	}
];

arButtons['save_and_exit'] = ['BXButton',
	{
		id : 'save_and_exit',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSaveExit,
		title : BX_MESS.TBSaveExit,
		show_name : true,
		handler : function ()
		{
			if(_bEdit)
			{
				this.pMainObj.SaveContent(true);
				this.pMainObj.isSubmited = true;
				this.pMainObj.pForm.submit();
			}
			else
			{
				__bx_fd_save_as();
			}
		}
	}
];

arButtons['exit'] = ['BXButton',
	{
		id : 'exit',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBExit,
		handler : function ()
		{
			this.pMainObj.OnEvent("OnSelectionChange");
			var need_to_ask = (pBXEventDispatcher.arEditors[0].IsChanged() && !pBXEventDispatcher.arEditors[0].isSubmited);

			if(need_to_ask)
			{
				this.bNotFocus = true;
				this.pMainObj.OpenEditorDialog("asksave", false, 600, {window: window, savetype: _bEdit ? 'save' : 'saveas'}, true);
			}
			else if(this.pMainObj.arConfig["sBackUrl"])
				window.location = this.pMainObj.arConfig["sBackUrl"];
		}
	}
];

arButtons['saveas'] = ['BXButton',
	{
		id : 'saveas',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSaveAs,
		handler : function ()
		{
			this.bNotFocus = true;
			apply = true;
			__bx_fd_save_as();
		}
	}
];


arButtons['save'] = ['BXButton',
	{
		id : 'save',
		iconkit : '_global_iconkit.gif',
		codeEditorMode : true,
		name : BX_MESS.TBSave,
		handler : function ()
		{
			if(!_bEdit)
			{
				this.bNotFocus = true;
				apply = true;
				__bx_fd_save_as();
			}
			else
			{
				this.pMainObj.SaveContent(true);
				BX("apply2").value = 'Y';
				this.pMainObj.isSubmited = true;
				this.pMainObj.pForm.submit();
			}
		}
	}
];

arToolbars['manage'] = [FE_MESS.FILEMAN_HTMLED_MANAGE_TB, [arButtons['save_and_exit'], arButtons['exit'], arButtons['new'], arButtons['save'], arButtons['saveas']]];

if (window.bEditProps)
{
	arButtons['pageprops'] = ['BXButton',
		{
			id : 'pageprops',
			iconkit : '_global_iconkit.gif',
			codeEditorMode : true,
			name : BX_MESS.TBProps,
			handler : function ()
			{
				this.pMainObj.OpenEditorDialog("pageprops", false, 800);
			}
		}
	];
	arToolbars['manage'][1].push(arButtons['pageprops']);
}

arDefaultTBPositions['manage'] = [0, 0, 2];

window.onbeforeunload = function(e)
{
	try{
		var need_to_ask = (pBXEventDispatcher.arEditors[0].IsChanged() && !pBXEventDispatcher.arEditors[0].isSubmited);
		if (need_to_ask)
		{
			return BX_MESS.ExitConfirm;
		}
	} catch(e){}
}

arEditorFastDialogs['asksave'] = function(pObj)
{
	return {
		title: BX_MESS.EDITOR,
		innerHTML : "<div style='padding: 5px;'>" + BX_MESS.DIALOG_EXIT_ACHTUNG + "</div>",
		OnLoad: function()
		{
			window.oBXEditorDialog.SetButtons([
				new BX.CWindowButton(
				{
					title: BX_MESS.TBSaveExit,
					className: 'adm-btn-save',
					action: function()
					{
						pObj.pMainObj.isSubmited = true;
						if(pObj.params.savetype == 'saveas')
						{
							pObj.params.window.__bx_fd_save_as();
						}
						else
						{
							pObj.pMainObj.SaveContent(true);
							pObj.pMainObj.pForm.submit();
						}

						window.oBXEditorDialog.Close();
					}
				}),
				new BX.CWindowButton(
				{
					title: BX_MESS.DIALOG_EXIT_BUT,
					action: function()
					{
						pObj.pMainObj.isSubmited = true;
						if(pObj.pMainObj.arConfig["sBackUrl"])
							pObj.params.window.location = pObj.pMainObj.arConfig["sBackUrl"];

						window.oBXEditorDialog.Close();
					}
				}),
				window.oBXEditorDialog.btnCancel
			]);

			BX.addClass(window.oBXEditorDialog.PARTS.CONTENT_DATA, "bxed-dialog");
			window.oBXEditorDialog.adjustSizeEx();
		}
	};
}
