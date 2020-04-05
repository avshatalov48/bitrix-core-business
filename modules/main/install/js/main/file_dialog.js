var BXFileDialog = function()
{
	this.name = 'BXFileDialog';
	this.height = 476;
	this.width = 750;
};

BXFileDialog.prototype =
{
	Open: function(oConfig, UserConfig, dublReq)
	{
		if (!oConfig || !UserConfig)
		{
			alert('Error: Wrong params!');
			return;
		}

		if (window.oBXFileDialog && oBXFileDialog.bOpened)
			return;

		this.SetFocus('name');
		this.oConfig = oConfig;

		this.UserConfig = UserConfig;
		this.LastSavedConfig =
		{
			site : this.UserConfig.site,
			path : this.UserConfig.path,
			view : this.UserConfig.view,
			sort : this.UserConfig.sort,
			sort_order : this.UserConfig.sort_order
		};

		this.sessid = oConfig.sessid;
		this.bSelectFiles = oConfig.select.indexOf('F') !== -1;
		this.bSelectDirs = oConfig.select.indexOf('D') !== -1;

		this.RequestUrl = this.GetRequestUrl();
		this.bOpened = true;

		var div;
		var bCached = (window.fd_float_div_cached && this.CheckReConfig());
		if (bCached)
		{
			div = document.body.appendChild(window.fd_float_div_cached);
		}
		else
		{
			if(BX("BX_file_dialog"))
				this.Close();

			div = document.body.appendChild(document.createElement("DIV"));
			div.id = "BX_file_dialog";
			div.className = "editor_dialog";
			div.style.position = 'absolute';
			div.style.zIndex = oConfig.zIndex || 2300;
			div.style.overflow = 'hidden';

			div.innerHTML =
				'<div class="title">'+
				'<table cellspacing="0" width="100%" border="0">'+
				'	<tr>'+
				'		<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], BX(\'BX_file_dialog\'));" id="BX_file_dialog_title">Title</td>'+
				'		<td width="0%"><a id="BX_file_dialog_close" class="close" href="javascript:oBXFileDialog.Close();" onclick="oBXFileDialog.Close(); return false;"></a></td></tr>'+
				'</table>'+
				'</div>'+
				'<div class="content">'+
				'</div>';
		}
		div.style.width = parseInt(this.width) + 'px';
		div.style.height = parseInt(this.height) + 'px';
		this.floatDiv = div;
		this.content = jsUtils.FindChildObject(this.floatDiv, 'div', 'content');

		oDialogTitle = BX('BX_editor_dialog_title');
		var ShowDialog = function(innerHTML)
		{
			CloseWaitWindow();

			if (innerHTML)
			{
				if (innerHTML.indexOf('BX_FD_LOAD_OK') == -1)
				{
					alert(mess_ACCESS_DENIED);
					return;
				}

				var new_sess = oBXFileDialog.CheckReqLostSessid(innerHTML);
				if (new_sess !== true)
				{
					if (dublReq)
					{
						alert(mess_SESS_EXPIRED);
						return;
					}
					document.body.removeChild(div);
					oBXFileDialog.sessid = new_sess;
					oBXFileDialog.RequestUrl = oBXFileDialog.GetRequestUrl();
					oBXFileDialog.Open(oConfig, UserConfig, true);
					return;
				}
				oBXFileDialog.content.innerHTML = innerHTML;
			}

			var
				w = jsUtils.GetWindowSize(),
				left = parseInt(w.scrollLeft + w.innerWidth / 2 - div.offsetWidth / 2),
				top = parseInt(w.scrollTop + w.innerHeight / 2 - div.offsetHeight / 2);

			jsFloatDiv.Show(div, left, top);
			BX.addCustomEvent(window, 'onFileDialogLoaded', function(){
				if (window.oBXDialogTree)
					oBXDialogTree.SetPath(oConfig.path || UserConfig.path || '');
			});

			BX.onCustomEvent(window, 'onAfterFileDialogShow');
		};
		ShowWaitWindow();

		this.SetEventHandlers();
		if (bCached)
		{
			this.reConfigDialog();
			ShowDialog();
			return;
		}

		BX.ajax.get(this.RequestUrl + '&action=start&path=' + this.oConfig.path + '&add_to_menu=' + (this.oConfig.showAddToMenuTab ? '1' : ''), ShowDialog);
	},

	CheckReConfig: function()
	{
		return !(
			BX.browser.IsIE() ||
			this.oConfig.operation != window.fd_config_cached.operation ||
			this.oConfig.allowAllFiles != window.fd_config_cached.allowAllFiles ||
			this.oConfig.select != window.fd_config_cached.select ||
			this.oConfig.lang != window.fd_config_cached.lang ||
			this.oConfig.showAddToMenuTab != window.fd_config_cached.showAddToMenuTab ||
			this.oConfig.showUploadTab != window.fd_config_cached.showUploadTab ||
			this.oConfig.site != window.fd_config_cached.site
		);
	},

	reConfigDialog: function()
	{
		if (this.oConfig.fileFilter != window.fd_config_cached.fileFilter)
			oBXDialogControls.Filter = new __FileFilter();
		var path = this.oConfig.path || this.UserConfig.path || '';
		oBXFileDialog.SubmitFileDialog = SubmitFileDialog;

		if(this.oConfig.operation == 'S' && this.oConfig.showAddToMenuTab && !window.oBXMenuHandling)
			window.oBXMenuHandling = new BXMenuHandling();

		oBXDialogTree.SetPath(path);
		//oBXDialogWindow.LoadFolderContent(path);
		//oBXDialogTree.focusOnSelectedElment();
	},

	Close: function()
	{
		this.SaveConfig();
		if (window.oBXFDContextMenu)
			oBXFDContextMenu.menu.PopupHide();
		var oDiv = BX("BX_file_dialog");
		jsFloatDiv.Close(oDiv);
		oBXFileDialog.bOpened = false;
		jsFloatDiv.Close(this.floatDiv);
		oDiv.parentNode.removeChild(oDiv);
		window.fd_float_div_cached = this.floatDiv;
		window.fd_config_cached = this.oConfig;
		this.UnsetEventHandlers();
		if (window.fd_site_list && window.fd_site_list.PopupHide)
			window.fd_site_list.PopupHide();
	},

	GetRequestUrl: function(site, sessid)
	{
		return '/bitrix/admin/file_dialog.php?'
			+ 'lang=' + this.oConfig.lang
			+ '&operation=' + this.oConfig.operation
			+ '&site=' + (site || this.oConfig.site)
			+ '&sessid=' + (sessid || this.sessid)
			+ '&get_files=' + (this.bSelectFiles ? 1 : '')
		;
	},

	CheckReqLostSessid: function(result)
	{
		var
			LSS = 'BX_FD_DUBLICATE_ACTION_REQUEST',
			LSSIndex = result.indexOf(LSS);

		if (LSSIndex == -1)
			return true;

		var i1 = LSSIndex + LSS.length;
		return result.substr(i1, result.indexOf('-->') - i1);
	},

	SaveConfig: function(oConfig)
	{
		if (!oConfig)
			oConfig = oBXFileDialog.UserConfig;
		else
			oBXFileDialog.UserConfig = oConfig;

		if (!this.oConfig.saveConfig || !oConfig || !window.BXFDCompareObj || BXFDCompareObj(this.LastSavedConfig, oConfig))
			return;
		var sc = new JCHttpRequest();
		sc.Action = function(result){oBXFileDialog.LastSavedConfig = BXFDCopyObj(oConfig);};
		sc.Send(oBXFileDialog.GetRequestUrl(getSite()) + '&action=set_config&path=' + jsUtils.urlencode(oConfig.path) + '&view=' + oConfig.view + '&sort=' + oConfig.sort + '&sort_order=' + oConfig.sort_order);
	},

	SetFocus: function(focus)
	{
		this.dialogFocus = focus; // Can be: name, path, tree, window
	},

	SetEventHandlers : function()
	{
		window.BXFD_OnKeyDown = function(e){return oBXFileDialog.OnKeyDown(e);};
		jsUtils.addEvent(document, "keydown", window.BXFD_OnKeyDown);
	},

	UnsetEventHandlers : function()
	{
		jsUtils.removeEvent(document, "keydown", window.BXFD_OnKeyDown);
	},

	OnKeyDown : function(e)
	{
		if(!e)
			e = window.event;
		if(!e || e.shiftKey || e.ctrlKey || e.altKey)
			return;
		if (this.dialogFocus == 'tree')
		{
			oBXDialogTree.OnKeyDown(e);
		}
		else if (this.dialogFocus == 'window')
		{
			oBXDialogWindow.OnKeyDown(e);
		}
		else
		{
			if (e.keyCode == 27)
				this.Close();
			if (e.keyCode == 13)
			{
				if(e.target)
					e.targetElement = e.target;
				else if(e.srcElement)
					e.targetElement = e.srcElement;

				if (window.oBXDialogControls && e.targetElement == oBXDialogControls.dirPath.oInput)
					oBXDialogTree.SetPath(oBXDialogControls.dirPath.Get());
			}
		}
	}
};

