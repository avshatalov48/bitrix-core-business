function BXDialogTree(){}

BXDialogTree.prototype =
{
	Init: function()
	{
		this.arIconList = {
			folder : '/bitrix/images/main/file_dialog/icons/folder.gif',
			folderopen : '/bitrix/images/main/file_dialog/icons/folderopen.gif',
			plus : '/bitrix/images/main/file_dialog/icons/plus.gif',
			minus : '/bitrix/images/main/file_dialog/icons/minus.gif',
			dot : '/bitrix/images/main/file_dialog/icons/dot.gif'
		};
		this.arDirConts = {};
	},

	DisplayTree : function()
	{
		if (!this.oCont)
			this.oCont = BX("__bx_treeContainer");
		BXFDCleanNode(this.oCont);

		var
			arItems = arFDDirs['/'],
			oTbl = jsUtils.CreateElement("TABLE", {className: 'bxfd-tree-tbl'}),
			len = arItems.length, i;

		for (i = 0; i < len; i++)
			this.DisplayElement(arItems[i], oTbl.insertRow(-1).insertCell(-1));

		this.oCont.appendChild(oTbl);
	},

	oPlusOnClick : function(el)
	{
		this.OpenTreeSection(el.parentNode.parentNode.getAttribute('__bxpath'), 'check');
	},

	oElementOnClick : function(el)
	{
		oBXFileDialog.SetFocus('tree');
		this.SelectElement(el.parentNode.parentNode.getAttribute('__bxpath'));
	},

	SelectElement : function(path, bOpen, bLoadCont)
	{
		if (this.curSelectedItem && this.curSelectedItem.path == path)
			return true;

		var oCont = this.arDirConts[path];

		if (oCont && oCont.firstChild)
		{
			var
				arSpans = oCont.firstChild.getElementsByTagName("SPAN"),
				oTitle = arSpans[0];

			if (this.curSelectedItem && this.curSelectedItem.oTitle)
				this.UnHighlightElement(this.curSelectedItem.oTitle);

			this.HighlightElement(oTitle);
			if (bOpen !== false)
				this.OpenTreeSection(path, true);

			oBXDialogControls.dirPath.Set(path);
			if (bLoadCont !== false)
				oBXDialogWindow.LoadFolderContent(path);

			this.curSelectedItem = {path: path, oTitle: oTitle};
		}
	},

	HighlightElement : function(El)
	{
		El.id = '__bx_SelectedTitle';
		El.className = 'bxfd-tree-item-sel';
	},

	UnHighlightElement : function(El)
	{
		El.id = '';
		El.className = 'bxfd-tree-item';
	},

	DisplayElement : function(oItem, oCont)
	{
		this.arDirConts[oItem.path] = oCont;

		var innerHTML = '<table>' +
			'<tr __bxpath="' + oItem.path + '" __bx_bOpen=0>' +
			'<td class="tree-node-folding" >';
		if (oItem.empty)
			innerHTML += '<img onclick="oBXDialogTree.oElementOnClick(this);" src="' + this.arIconList.dot + '" />';
		else
			innerHTML += '<img onclick="oBXDialogTree.oPlusOnClick(this);" src="' + this.arIconList.plus + '" />';
		innerHTML += '</td><td class="tree-node-icon" >' +
			'<img onclick="oBXDialogTree.oElementOnClick(this);" src="' + this.arIconList.folder + '" />' +
			'</td><td class="tree-node-name" >' +
			'<span onclick="oBXDialogTree.oElementOnClick(this);" class="bxfd-tree-item" unselectable="on">' + oItem.name + '</span>' +
			'</td></tr></table>';
		oCont.innerHTML = innerHTML;
	},

	LoadTree : function(path, oCont, dublReq)
	{
		var q = new JCHttpRequest();
		q.Action = function(result)
		{
			oWaitWindow.Hide();
			var new_sess = oBXFileDialog.CheckReqLostSessid(result);
			if (new_sess !== true)
			{
				if (dublReq)
					return alert('0' + FD_MESS.FD_SESS_EXPIRED);
				oBXFileDialog.sessid = new_sess;
				return oBXDialogTree.LoadTree(path, oCont, true);
			}

			var iter = 0;
			var loading_int = setInterval(
				function()
				{
					iter++;
					if (window.action_warning !== false)
					{
						clearInterval(loading_int);
						if (!oBXDialogWindow.lastCorrectPath)
							oBXDialogWindow.lastCorrectPath = '/';
						oBXDialogTree.SetPath(oBXDialogWindow.lastCorrectPath);
						return alert(window.action_warning);
					}
					else if (!window.load_items_correct)
					{
						clearInterval(loading_int);
						return alert(FD_MESS.FD_ERROR);
					}

					if (arFDDirs[path] || arFDFiles[path] || iter > 20)
					{
						clearInterval(loading_int);

						if (oBXDialogTree.bRedisplayTree)
							oBXDialogTree.DisplayTree();
						oBXDialogTree.bRedisplayTree = false;

						if (oCont === false)
							return oBXDialogTree.SetPath(path);
						if (oCont === 'timeout')
							return oBXDialogTree.SetPath(path, false);

						if (typeof arFDDirs[path] == 'object' && arFDDirs[path].length > 0)
							oBXDialogTree.DisplaySubTree(oCont, arFDDirs[path]);

						if (oBXDialogWindow.reloadWindowPath)
							oBXDialogWindow.LoadFolderContent(path);
						oBXDialogWindow.reloadWindowPath = true;
					}
				},
				5
			);
		};
		oWaitWindow.Show();
		window.action_warning = false;
		window.load_items_correct = false;

		this.curLoadingPath = path;
		q.Send(oBXFileDialog.GetRequestUrl(getSite()) + '&action=load&path=' + jsUtils.urlencode(path) + '&add_to_menu=' + (oBXFileDialog.oConfig.operation == 'S' ? '1' : '') + '&rec=' + (oCont === false ? '2' : '0'));
	},

	focusOnSelectedElment : function()
	{
		if (!this.curSelectedItem)
			return true;

		var
			El = this.curSelectedItem.oTitle,
			startTop = this.oCont.scrollTop;

		this.tmpFocusInp = jsUtils.CreateElement('INPUT', {size: 1, id: 'bx_fd_tmp_focus_inp'});
		El.parentNode.insertBefore(this.tmpFocusInp, El);

		setTimeout(function()
		{
			var inp = (oBXDialogTree.tmpFocusInp && oBXDialogTree.tmpFocusInp.parentNode) ? oBXDialogTree.tmpFocusInp : BX('bx_fd_tmp_focus_inp');
			if (inp)
			{
				inp.focus();
				var endTop = oBXDialogTree.oCont.scrollTop;
				if (startTop < endTop)
					oBXDialogTree.oCont.scrollTop += 120;

				inp.parentNode.removeChild(inp);
				inp = null;
			}
		}, 10);
	},

	OpenTreeSection : function(path, bOpen)
	{
		var oCont = this.arDirConts[path];
		if (!oCont)
			return;

		var
			arTables = oCont.getElementsByTagName("TABLE"),
			paramsCont = arTables[0].rows[0],
			bOpened = paramsCont.getAttribute('__bx_bOpen'),
			arImages = arTables[0].getElementsByTagName("IMG"),
			oPlus = arImages[0],
			oIcon = arImages[1];

		if (oPlus.src.indexOf('dot.gif') != -1)
			return;

		if (bOpen == 'check')
			bOpen = (bOpened != 1);

		if (bOpen)
		{
			paramsCont.setAttribute('__bx_bOpen', 1);
			oPlus.src = this.arIconList.minus;
			oIcon.src = this.arIconList.folderopen;

			if (!window.arFDDirs[path])
				this.LoadTree(path, oCont); // Load tree and open section
			else
				this.DisplaySubTree(oCont, window.arFDDirs[path]);
			oBXDialogControls.dirPath.Set(path);
		}
		else
		{
			var subTreeTable = arTables[1];
			if (!subTreeTable)
				return;
			oIcon.src = this.arIconList.folder;
			oPlus.src = this.arIconList.plus;
			subTreeTable.style.display = "none";
			paramsCont.setAttribute('__bx_bOpen', 0);
		}
	},

	HighlightPath : function(path)
	{
		try{
			if (path == "" || path == "/")
			{
				if (this.curSelectedItem && this.curSelectedItem.oTitle)
					this.UnHighlightElement(this.curSelectedItem.oTitle);
				this.oCont.scrollTop = 0;
				return;
			}

			path = path.replace(/\\/ig,"/");
			var
				arPath = path.split("/"),
				basePath = '',
				dir, i, l = arPath.length;

			for (i = 0; i < l; i++)
			{
				dir = arPath[i];
				if (dir != '')
				{
					basePath += '/' + dir;
					this.OpenTreeSection(basePath, true);
				}
			}

			var
				oCont = this.arDirConts[basePath],
				arSpans = oCont.firstChild.getElementsByTagName("SPAN"),
				oTitle = arSpans[0];

			if (this.curSelectedItem && this.curSelectedItem.oTitle)
				this.UnHighlightElement(this.curSelectedItem.oTitle);
			this.HighlightElement(oTitle);
			this.curSelectedItem = {path: basePath, oTitle: oTitle};

		}catch(e){
			setTimeout(function () {oBXDialogTree.HighlightPath(path);}, 100);
		}
		oBXDialogTree.focusOnSelectedElment();
	},

	SetPath : function(path, bHightlight)
	{
		path = path.replace(/\\/ig,"/");
		path = path.replace(/[\/]+$/g, "");
		path = BX.util.trim(path);

		if (path == '' || path.indexOf('..') != -1 || path == '/' || path == './' || path == '/.' || path == '.')
			path = '/';

		if (!window.arFDDirs[path] && !window.arFDFiles[path])
			return this.LoadTree(path, false);

		if (bHightlight !== false)
			oBXDialogTree.HighlightPath(path);
		oBXDialogControls.dirPath.Set(path);

		if (arFDDirs[path] && arFDFiles[path]) // Content
			return oBXDialogWindow.DisplayFolderContent(path);
	},

	DisplaySubTree : function(oCont, arSubTreeItems, bRefresh)
	{
		if (!oCont || arSubTreeItems === false)
			return;

		var arTbls = oCont.getElementsByTagName("TABLE");
		if (bRefresh && arTbls[1])
			arTbls[1].parentNode.removeChild(arTbls[1]); // Del sub tree

		if (arTbls[1] && !bRefresh)
		{
			arTbls[1].style.display = "block"; // subTreeTable
		}
		else
		{
			var
				contTable = jsUtils.CreateElement("TABLE", {}, {marginLeft: "15px"}),
				len = arSubTreeItems.length,
				oCell, i;

			for (i = 0; i < len; i++)
			{
				oCell = contTable.insertRow(-1).insertCell(-1);
				this.DisplayElement(arSubTreeItems[i], oCell);
			}
			oCont.appendChild(contTable);
		}
	},

	Append : function()
	{
		var path = oBXFileDialog.oConfig.path;
		if (path != '/' && path.substr(path.length - 1) == '/')
			path = path.substr(0, path.length - 1);

		this.Init();
		this.DisplayTree();
		this.HighlightPath(path);

		var iter = 0, maxIter = 20;
		var apint = setInterval(
			function()
			{
				iter++;
				if (arFDDirs[path] || arFDFiles[path] || iter > maxIter)
				{
					clearInterval(apint);
					if (iter < maxIter + 1)
						oBXDialogWindow.DisplayFolderContent(path);
				}
			},
			5
		);
		oBXDialogControls.dirPath.Set(path, false);
	},

	OnKeyDown : function(e)
	{
		if (!this.curSelectedItem)
			return;

		var
			path = this.curSelectedItem.path,
			selectPath = false,
			lind,
			parPath,
			parItems,
			i, curInd;

		switch(e.keyCode)
		{
			case 37: // Left
				if (this.SectionIsOpened(path))
				{
					this.OpenTreeSection(path, false); // Close tree section
					break;
				}

				// Get parent path
				lind = path.lastIndexOf('/');
				parPath = lind == 0 ? '/' : path.substr(0, lind);
				parItems = arFDDirs[parPath];

				selectPath = parPath == '/' ? parItems[0].path : parPath;
				break;
			case 38: // Up
				// Get parent path
				lind = path.lastIndexOf('/');
				parPath = lind == 0 ? '/' : path.substr(0, lind);
				parItems = arFDDirs[parPath];
				l = parItems.length;

				// find cur element in parent path array
				if (l > 0)
				{
					for (i = 0; i < l; i++)
					{
						if (parItems[i].path == path)
						{
							curInd = i;
							break;
						}
					}
				}

				if (curInd == 0 && parPath == '/') // Top of the top
					break;

				if (curInd == 0) // Select parent section
				{
					selectPath = parPath;
					break;
				}

				var upperItem = parItems[curInd - 1];
				if (this.SectionIsOpened(upperItem.path)) // section opened, select last item in subtree
				{
					var subLen = arFDDirs[upperItem.path].length;
					if (subLen > 0)
						selectPath = arFDDirs[upperItem.path][subLen - 1].path;
				}
				else // select upper item
				{
					selectPath = upperItem.path;
				}

				break;
			case 39: // Right
				// Get parent path
				lind = path.lastIndexOf('/');
				parPath = lind == 0 ? '/' : path.substr(0, lind);
				parItems = arFDDirs[parPath];
				l = parItems.length;

				// find cur element in parent path array
				if (l > 0)
				{
					for (i = 0; i < l; i++)
					{
						if (parItems[i].path == path)
						{
							curInd = i;
							break;
						}
					}
				}

				if (!parItems[curInd].empty)  // Section have children
				{
					if (this.SectionIsOpened(path) && arFDDirs[path] && arFDDirs[path][0])// section opened, select last item in subtree
						selectPath = arFDDirs[path][0].path;
					else // Open section
						this.OpenTreeSection(path, true);
				}
				break;
			case 40: // Down
				// Get parent path
				lind = path.lastIndexOf('/');
				parPath = lind == 0 ? '/' : path.substr(0, lind);
				parItems = arFDDirs[parPath];
				l = parItems.length;

				if (this.SectionIsOpened(path) && arFDDirs[path] && arFDDirs[path][0])
				{
					selectPath = arFDDirs[path][0].path;
					break;
				}

				// find cur element in parent path array
				if (l > 0)
				{
					for (i = 0; i < l; i++)
					{
						if (parItems[i].path == path)
						{
							curInd = i;
							break;
						}
					}
				}

				// Section closed
				if (curInd + 1 < l)
				{
					var lowerItem = parItems[curInd + 1];
					selectPath = lowerItem.path;
				}
				else // Last element selected
				{
					if (parPath == '/') // End of the end
						break;

					// Get parent of the parent path
					var
						lind2 = parPath.lastIndexOf('/'),
						parPath2 = lind2 == 0 ? '/' : parPath.substr(0, lind2),
						parItems2 = arFDDirs[parPath2],
						l2 = parItems2.length,
						i2, curInd2;

					if (l2)
					{
						for (i2 = 0; i2 < l2; i2++)
						{
							if (parItems2[i2].path == parPath)
							{
								curInd2 = i2;
								break;
							}
						}

						if (curInd2 < l2 - 1)
							selectPath = parItems2[curInd2 + 1].path;
					}
				}
				break;
			case 8: // Backspace
				// Get parent path
				lind = path.lastIndexOf('/');
				parPath = lind == 0 ? '/' : path.substr(0, lind);
				parItems = arFDDirs[parPath];
				selectPath = parPath == '/' ? parItems[0].path : parPath;
				break;
		}

		if (selectPath !== false)
		{
			this.TimeoutSelectElement(selectPath);
			return BX.PreventDefault(e);
		}
	},

	TimeoutSelectElement: function(path)
	{
		if (this.selectTimeout)
			clearTimeout(this.selectTimeout);

		this.SelectElement(path, false, false);
		this.selectTimeout = setTimeout(
			function()
			{
				if (oBXDialogTree.curSelectedItem.path == path)
				{
					oBXDialogWindow.LoadFolderContent(path, false, true);
					oBXDialogTree.focusOnSelectedElment();
				}
			}, 500
		);
	},

	SectionIsOpened: function(path)
	{
		try{
			return this.arDirConts[path].firstChild.rows[0].getAttribute('__bx_bOpen') == 1;
		}catch(e) {return false;}
	}
};

// *****************************************************************************
//                               BXDialogWindow
// *****************************************************************************

function BXDialogWindow() {this.Init();}

BXDialogWindow.prototype =
{
	Init: function()
	{
		this.pWnd = BX('__bx_windowContainer');
		this.view = oBXFileDialog.UserConfig.view;
		this.lastCorrectPath = "";
		this.sort = oBXFileDialog.UserConfig.sort;
		this.sort_order = oBXFileDialog.UserConfig.sort_order;
		this.filter = oBXDialogControls.Filter.curentFilter;
		this.arFiles = {};
		oBXDialogControls.ViewSelector.Set(this.view, false);
		oBXDialogControls.SortSelector.Set(this.sort,this.sort_order);

		var __title = BX('BX_file_dialog_title');
		this.cancelRename_innerHTML = '';

		if (oBXFileDialog.oConfig.operation == 'S')
			__title.innerHTML = FD_MESS.FD_SAVE_TAB_TITLE;
		else if (oBXFileDialog.oConfig.operation == 'O' && oBXFileDialog.oConfig.select == 'D')
			__title.innerHTML = FD_MESS.FD_OPEN_DIR;
		else
			__title.innerHTML = FD_MESS.FD_OPEN_TAB_TITLE;

		BX('BX_file_dialog_close').title = FD_MESS.FD_CLOSE;

		this.iconsPath = '/bitrix/images/main/file_dialog/icons/types/';
		this.arIcons =
		{
			css : {small:'css.gif', big:'css_big.gif', type:'CSS ' + FD_MESS.FD_FILE},
			csv : {small:'csv.gif', big:'csv_big.gif', type:'CSV ' + FD_MESS.FD_FILE},
			file : {small:'file.gif', big:'file_big.gif', type: FD_MESS.FD_FILE},
			flash : {small:'flash.gif', big:'flash_big.gif', type:'Adobe Macromedia Flash ' + FD_MESS.FD_FILE},
			folder : {small:'folder.gif', big:'folder_big.gif', type: FD_MESS.FD_FOLDER},
			gif : {small:'gif.gif', big:'gif_big.gif', type: FD_MESS.FD_IMAGE + ' GIF'},
			htaccess : {small:'htaccess.gif', big:'htaccess_big.gif', type:'htaccess ' + FD_MESS.FD_FILE},
			html : {small:'html.gif', big:'html_big.gif', type:'HTML ' + FD_MESS.FD_FILE},
			jpg : {small:'jpeg.gif', big:'jpeg_big.gif', type: FD_MESS.FD_IMAGE + ' JPG'},
			jpeg : {small:'jpeg.gif', big:'jpeg_big.gif', type: FD_MESS.FD_IMAGE + ' JPEG'},
			js : {small:'js.gif', big:'js_big.gif', type:'Javascript ' + FD_MESS.FD_FILE},
			php : {small:'php.gif', big:'php_big.gif', type:'PHP ' + FD_MESS.FD_FILE},
			png : {small:'png.gif', big:'png_big.gif', type: FD_MESS.FD_IMAGE + ' PNG'},
			txt : {small:'txt.gif', big:'txt_big.gif', type:'Text ' + FD_MESS.FD_FILE},
			xml : {small:'xml.gif', big:'xml_big.gif', type:'XML ' + FD_MESS.FD_FILE}
		};

		// *  *  *  *  *  *  CONTEXT MENU INIT *  *  *  *  *  *  *  *  *  *
		this.oCM = new BXFDContextMenu();
		this.oSorter = new BXFDSorter();
	},

	LoadFolderContent: function(path, hard_refresh, bForTimeout)
	{
		if (path.indexOf('..') != -1)
			path = '/';

		if (hard_refresh === true)
		{
			arFDDirs[path] = false;
			arFDFiles[path] = false;
		}

		if (arFDDirs[path] && arFDFiles[path] && hard_refresh !== true) // Content
			return this.DisplayFolderContent(path);

		if (oBXDialogTree.curLoadingPath != path)
			oBXDialogTree.LoadTree(path, bForTimeout === true ? 'timeout' : false);
	},

	DisplayFolderContent: function(path)
	{
		if (path == '')
			path = '/';

		var oPerm = window.arFDPermission[path];
		if (oBXFileDialog.oConfig.operation == 'O' && oBXFileDialog.oConfig.showUploadTab && oPerm)
			oBXDialogTabs.DisableTab("tab2", !oPerm.upload);

		if (arFDDirs[path] && arFDFiles[path])
		{
			oBXDialogWindow.DisplayElementsList(arFDDirs[path], arFDFiles[path], oBXDialogWindow.view, oBXDialogWindow.filter, oBXDialogWindow.sort, oBXDialogWindow.sort_order);
		}

		this.reloadWindowPath = path;
		this.lastCorrectPath = path;
		oBXDialogControls.Preview.Clear();
		if (oBXFileDialog.oConfig.operation == 'O')
			oBXDialogControls.filePath.Set('');

		//refresh menu types
		if (oBXFileDialog.oConfig.operation == 'S' && oBXFileDialog.oConfig.showAddToMenuTab)
			oBXMenuHandling.ChangeMenuType();
	},

	DisplayElementsList: function(arDirs, arFiles, view, filter, sort, sort_order)
	{
		//Folder doesn't exists
		if (arDirs === false && arFiles === false)
			return;
		if (typeof arDirs != 'object' || typeof arFiles != 'object')
			return;

		oBXDialogWindow.view = view;

		var
			_this = this,
			len1 = arDirs.length,
			len2 = arFiles.length,
			arElements = [],
			arFilter = (filter === '' || filter === false) ? '*' : oBXDialogControls.Filter.arFilters[filter],
			oDir, oFile, lenS, ext, icon, i, l, j, add;

		//Push directories to Elements array
		for (i = 0; i < len1; i++)
		{
			oDir = arDirs[i];
			arElements.push(
				{
					name : oDir.name,
					icon : 'folder',
					path : oDir.path,
					permission : oDir.permission,
					date : oDir.date,
					timestamp : oDir.timestamp,
					size : oDir.size
				}
			);
		}

		//Push files to Elements array
		if (oBXFileDialog.bSelectFiles)
		{
			for (i = 0; i < len2; i++)
			{
				add = false;
				oFile = arFiles[i];
				ext = oFile.name.substr(oFile.name.lastIndexOf(".")+1).toLowerCase();
				icon = (!this.arIcons[ext]) ? 'file' : ext;
				if (arFilter != '*')
				{
					l = arFilter.length;
					for (j = 0; j < l; j++)
					{
						if (ext == arFilter[j])
						{
							add = true;
							break;
						}
					}
				}
				else
					add = true;

				if (add)
				{
					arElements.push(
						{
							name : oFile.name,
							icon : icon,
							ext : ext,
							path : oFile.path,
							permission : oFile.permission,
							date : oFile.date,
							timestamp : oFile.timestamp,
							size : oFile.size,
							tmb : oFile.tmb_src || ''
						}
					);
				}
			}
		}

		oWaitWindow.Show();
		setTimeout(function ()
		{
			oBXDialogWindow.oNameInput = false;
			oBXDialogWindow.oSorter.Sort(arElements);
			_this.arElIndex = [];
			_this['DisplayElList_' + view](arElements); //DisplayElList_list, DisplayElList_detail, DisplayElList_preview
			oBXDialogControls.NewDirButtonChange();
			oWaitWindow.Hide();
		}, 3);
	},

	DisplayElList_list: function(arElements)
	{
		var addSubCont = function(oTable, oRow)
		{
			var curW = parseInt(oTable.style.width || oTable.offsetWidth);
			if (isNaN(curW))
				curW = 0;
			var w = 220;
			var oSC = oRow.insertCell(-1);
			oTable.style.width = (curW + w) + "px";
			oSC.className = 'bx-valign-top';
			oSC.style.width = w + 'px';
			return oSC;
		};

		var oSubContTable = BX("__bx_oSubContTable");
		if (oSubContTable)
			oSubContTable.parentNode.removeChild(oSubContTable);

		oSubContTable = this.pWnd.appendChild(jsUtils.CreateElement("TABLE", {id: "__bx_oSubContTable"}, {height: '228px', width: '0px'}));
		var oRow = oSubContTable.insertRow(-1);
		var l = arElements.length, i, oSSContTable, oSubCont;

		if (l == 0) // List empty, but we have to create subcont
		{
			oSubCont = addSubCont(oSubContTable, oRow);
			oSSContTable = oSubCont.appendChild(jsUtils.CreateElement("TABLE", {}, {width: "100%"}));
		}
		else
		{
			for (i = 0; i < l; i++)
			{
				if (i % 12 == 0)
				{
					oSubCont = addSubCont(oSubContTable, oRow);
					oSSContTable = oSubCont.appendChild(jsUtils.CreateElement("TABLE", {}, {width: "100%"}));
				}
				this.AddElementToList('list', oSSContTable, arElements[i], i);
			}
		}

		this.Last_ElList_len = l;
	},

	DisplayElList_detail: function(arElements)
	{
		var oSubContTable = BX("__bx_oSubContTable");
		if (oSubContTable)
			oSubContTable.parentNode.removeChild(oSubContTable);

		oSubContTable = document.createElement('TABLE');
		oSubContTable.id = "__bx_oSubContTable";
		this.pWnd.appendChild(oSubContTable);
		oSubContTable.style.height = '0%';
		oSubContTable.style.width = '100%';
		var oRow = oSubContTable.insertRow(-1);
		oRow.className = 'bxfd-det-view-head';

		var fill_innerHTML = function()
		{
			nameCell.innerHTML = FD_MESS.FD_SORT_NAME;
			sizeCell.innerHTML = FD_MESS.FD_SORT_SIZE;
			typeCell.innerHTML = FD_MESS.FD_SORT_TYPE;
			dateCell.innerHTML = FD_MESS.FD_SORT_DATE;
		};

		// Detail table header
		var
			iconCell = oRow.insertCell(-1),
			nameCell = oRow.insertCell(-1),
			sizeCell = oRow.insertCell(-1),
			typeCell = oRow.insertCell(-1),
			dateCell = oRow.insertCell(-1);

		nameCell.style.width = "45%";
		iconCell.style.width = "15px";

		fill_innerHTML();
		var arr_img = "<img src='/bitrix/images/main/file_dialog/arrow_" + (oBXDialogWindow.sort_order == 'asc' ? 'up' : 'down') + ".gif'>";

		switch(oBXDialogWindow.sort)
		{
			case 'name':
				nameCell.innerHTML += '&nbsp;'+arr_img;
				nameCell.setAttribute("sort_order",oBXDialogWindow.sort_order);
				break;
			case 'size':
				sizeCell.innerHTML += '&nbsp;'+arr_img;
				sizeCell.setAttribute("sort_order",oBXDialogWindow.sort_order);
				break;
			case 'type':
				typeCell.innerHTML += '&nbsp;'+arr_img;
				typeCell.setAttribute("sort_order",oBXDialogWindow.sort_order);
				break;
			case 'date':
				dateCell.innerHTML += '&nbsp;'+arr_img;
				dateCell.setAttribute("sort_order",oBXDialogWindow.sort_order);
				break;
		}

		var __onclick = function(__name,oCell)
		{
			fill_innerHTML();
			if (oBXDialogWindow.sort != __name)
			{
				oBXDialogWindow.sort = __name;
				var new_sort_order = 'asc';
			}
			else
				new_sort_order = (oCell.getAttribute("sort_order") == 'asc') ? 'des' : 'asc';

			oCell.setAttribute("sort_order",new_sort_order);
			oBXDialogWindow.sort_order = new_sort_order;

			var arr_img = "<img src='/bitrix/images/main/file_dialog/arrow_"+(oBXDialogWindow.sort_order == 'asc' ? 'up' : 'down')+".gif'>";
			oCell.innerHTML += '&nbsp;'+arr_img;

			oWaitWindow.Show();

			setTimeout(function ()
					{
						//__BXSort(arElements,__name,new_sort_order);
						oBXDialogControls.SortSelector.Set(__name, new_sort_order);
						oBXDialogWindow.oSorter.Sort(arElements);
						oBXDialogWindow.DisplayElList_detail(arElements);
						oWaitWindow.Hide();
					}, 5
				);
		};

		nameCell.onclick = function(){__onclick("name", nameCell);};
		sizeCell.onclick = function(){__onclick("size", sizeCell);};
		typeCell.onclick = function(){__onclick("type", typeCell);};
		dateCell.onclick = function(){__onclick("date", dateCell);};

		for (var i = 0, l = arElements.length; i < l; i++)
			this.AddElementToList('detail', oSubContTable, arElements[i], i);
	},

	DisplayElList_preview: function(arElements)
	{
		var oSubContTable = BX("__bx_oSubContTable");
		if (oSubContTable)
			oSubContTable.parentNode.removeChild(oSubContTable);

		oSubContTable = this.pWnd.appendChild(jsUtils.CreateElement("TABLE", {id: "__bx_oSubContTable"}, {height: '0%', width: '100%'}));

		var
			oCont = oSubContTable.insertRow(-1).insertCell(-1),
			i, l = arElements.length;

		for (i = 0; i < l; i++)
			this.AddElementToList('preview', oCont, arElements[i], i);
	},

	AddElementToList: function(view, oCont, oEl, ind)
	{
		if (!oEl)
			return;

		this.arElIndex[ind] = oEl;

		var
			_size = (oEl.icon != 'folder') ? getFileSize(oEl.size) : '',
			_title = (jsUtils.IsIE()) ? (oEl.name + (oEl.icon != 'folder' ? "\n"+FD_MESS.FD_SORT_SIZE+": " + _size : "") + "\n"+FD_MESS.FD_SORT_DATE+": "+oEl.date) : (oEl.name),
			oIconCell, src;

		if (view == 'list')
		{
			pEl = oCont.insertRow(-1);
			oIconCell = pEl.insertCell(-1);
			var oTitleCell = pEl.insertCell(-1);
			src = oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons[oEl.icon].small;

			oIconCell.innerHTML = '<img src="'+src+'" title="'+_title+'" />';
			oIconCell.style.width = '0%';

			oTitleCell.unselectable = "on";
			oTitleCell.title = _title;
			oTitleCell.className = "bxfd-win-item";
			oTitleCell.innerHTML = "<span class='title'>" + oBXDialogWindow.checkNameLength(oEl.name, 210) + "</span>";
		}
		else if(view == 'detail')
		{
			pEl = oCont.insertRow(-1);
			pEl.className = 'bxfd-det-list-row';

			oIconCell = pEl.insertCell(-1);
			var
				oNameCell = pEl.insertCell(-1),
				oSizeCell = pEl.insertCell(-1),
				oTypeCell = pEl.insertCell(-1),
				oDateCell = pEl.insertCell(-1),
				_type = oBXDialogWindow.arIcons[oEl.icon].type,
				_date = oEl.date;
			src = oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons[oEl.icon].small;

			oIconCell.innerHTML = '<img src="'+src+'" title="'+_title+'" />';
			oIconCell.style.width = '10px';
			oNameCell.unselectable = "on";
			oNameCell.style.cursor = "default";
			oNameCell.style.textAlign = 'left';
			oNameCell.title = _title;
			oNameCell.innerHTML = "<span class='title'>" + oBXDialogWindow.checkNameLength(oEl.name, 210)+"</span>";
			oNameCell.className = "bxfd-win-item";
			oSizeCell.style.textAlign = "right";
			oSizeCell.style.paddingRight = "5px";
			oSizeCell.innerHTML = _size;
			oTypeCell.innerHTML = _type;
			oDateCell.innerHTML = _date;
		}
		else if(view == 'preview')
		{
			var
				pEl = oCont.appendChild(jsUtils.CreateElement('DIV', {className: 'bxfd-prev-cont', title: _title})),
				elTable = pEl.appendChild(jsUtils.CreateElement('TABLE', {}, {width:"100%", height:"100%"})),
				oPreviewCell = elTable.insertRow(-1).insertCell(-1),
				oDetailsCell = elTable.insertRow(-1).insertCell(-1);
			src = (oBXFileDialog.oConfig.genThumb && oEl.tmb) ? oEl.tmb : oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons[oEl.icon].big; // Preview IMAGE

			oPreviewCell.align = "center";
			oPreviewCell.unselectable = "on";
			oPreviewCell.valign = "middle";
			oPreviewCell.style.height = "110px";
			oPreviewCell.appendChild(jsUtils.CreateElement('IMG', {src: src}));

			oDetailsCell.align = "center";
			oDetailsCell.unselectable = "on";
			oDetailsCell.style.cursor = "default";
			oDetailsCell.innerHTML = oBXDialogWindow.checkNameLength(oEl.name, 170) + (_size!="" ? "<br />"+_size : '');
		}

		oBXDialogWindow.AddElementsEventHandlers(pEl, oEl);
	},

	AddElementsEventHandlers: function(pEl, oEl)
	{
		if (!oEl._winCont)
			oEl._winCont = {};
		oEl._winCont[this.view] = pEl;

		this.arFiles[oEl.path] = oEl;
		pEl.setAttribute('__bxpath', oEl.path);

		if (oEl.icon == 'folder')
		{
			pEl.onclick = function(e)
			{
				oBXFileDialog.SetFocus('window');
				var path = this.getAttribute('__bxpath');
				var name = false;
				if (oBXFileDialog.bSelectDirs)
					name = getFileName(path);
				oBXDialogWindow.SelectElement(this, path, name);
			};

			pEl.ondblclick = function(e)
			{
				oBXFileDialog.SetFocus('window');
				oBXDialogTree.SetPath(this.getAttribute('__bxpath'));
			};
		}
		else
		{
			pEl.onclick = function(e)
			{
				if (!oBXFileDialog.bSelectFiles)
					return;
				oBXFileDialog.SetFocus('window');
				var path = this.getAttribute('__bxpath');
				oBXDialogWindow.SelectElement(this, path, getFileName(path));
			};

			pEl.ondblclick = function(e)
			{
				var path = this.getAttribute('__bxpath');
				oBXDialogWindow.SelectElement(this, path, getFileName(path));
				oBXFileDialog.SubmitFileDialog();
			};
		}

		pEl.oncontextmenu = oBXDialogWindow.OnContextMenu;
	},

	checkNameLength: function(name,width,bAddEllipsis)
	{
		if (name.length <= 12)
			return name;

		if (!bAddEllipsis)
			bAddEllipsis = false;

		oDiv = document.createElement('DIV');
		oDiv.style.position = "absolute";
		oDiv.innerHTML = name;
		document.body.appendChild(oDiv);
		w = oDiv.offsetWidth;
		document.body.removeChild(oDiv);

		if (w < width && !bAddEllipsis)
			return name;

		var len = name.length;
		name_base = name.substr(0,name.length - 7);
		name_end = name.substr(name.length - 7);

		if (w >= width)
			name = this.checkNameLength(name_base.substr(0, name_base.length - 3) + name_end, width, true);
		else if (bAddEllipsis)
			name = name_base + "..." + name_end;

		return name;
	},

	SelectElement: function(oCont, path, name)
	{
		this.curSelectedItem = {cont: oCont, path: path};

		if (this.view == 'preview')
		{
			var he = BX("bxfd_selected_element_preview");
			if (he)
				he.id = "";
			if (oCont)
				oCont.id = "bxfd_selected_element_preview";
		}
		else
		{
			var
				oldIcon = BX('bxfd_selected_element_icon'),
				oldTitle = BX('bxfd_selected_element_title');
			if (oldIcon)
				oldIcon.id = '';
			if (oldTitle)
				oldTitle.id = '';

			if (oCont)
				this.HighlightElement(oCont.cells[0], oCont.cells[1]);
		}

		if (name)
		{
			oBXDialogControls.filePath.Set(name);
			oBXDialogControls.Preview.Display(path);
		}
	},

	HighlightElement: function(ElIcon, ElTitle)
	{
		if (ElIcon)
			ElIcon.id = 'bxfd_selected_element_icon';

		ElTitle.id = 'bxfd_selected_element_title';
	},

	AddNewElement: function()
	{
		oBXDialogWindow.oNameInput = false;
		switch(oBXDialogWindow.view)
		{
			case 'list':
				this.AddNewElement_list();
				break;
			case 'detail':
				this.AddNewElement_detail();
				break;
			case 'preview':
				this.AddNewElement_preview();
				break;
		}
	},

	AddNewElement_list: function()
	{
		var addSubCont = function(oTable,oRow)
		{
			var w = 220;
			oTable.style.width = (parseInt(oTable.style.width)+w)+"px";
			var oSC = oRow.insertCell(-1);
			oSC.className = 'bx-valign-top';
			oSC.style.width = w+'px';
			return oSC;
		};

		var addElement_list = function(oTable, oEl)
		{
			oR = oTable.insertRow(-1);

			var oIconCell = oR.insertCell(-1);
			var src = oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons['folder'].small;
			oIconCell.innerHTML = '<img src="'+src+'"/>';
			oIconCell.style.width = '0%';

			oTitleCell = oR.insertCell(-1);
			oTitleCell.unselectable = "on";
			oTitleCell.style.cursor = "default";
			oTitleCell.style.width = '100%';
			oTitleCell.style.textAlign = 'left';

			var oNameInput = oTitleCell.appendChild(jsUtils.CreateElement('INPUT', {type:'text', value: oBXDialogControls.DefaultDirName, id:'__edited_element', __bx_mode:'new'}, {width:'100%'}));
			oBXDialogWindow.oNameInput = oNameInput;

			oBXDialogWindow.SelectInput(oNameInput);
			jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
			jsUtils.addEvent(oNameInput, "blur", oBXDialogWindow.OnElementBlur);
		};

		var
			oSubContTable = BX("__bx_oSubContTable"),
			oRow = oSubContTable.rows[0],
			oSSContTable = oRow.cells[oRow.cells.length - 1].childNodes[0];

		if (this.Last_ElList_len % 12 == 0 && this.Last_ElList_len > 0)
		{
			oSubCont = addSubCont(oSubContTable,oRow);
			oSSContTable = oSubCont.appendChild(document.createElement('TABLE'));
			oSSContTable.style.width = "100%";
		}
		addElement_list(oSSContTable);
	},

	AddNewElement_detail: function()
	{
		var addElement_detail = function(oTable)
		{
			var oR = oTable.insertRow(-1);
			var oIconCell = oR.insertCell(-1);
			var src = oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons['folder'].small;
			oIconCell.innerHTML = '<img src="'+src+'" />';
			oIconCell.style.width = '10px';
			var oNameCell = oR.insertCell(-1);
			oNameCell.className = "bxfd-win-item";
			var oSizeCell = oR.insertCell(-1);
			var oTypeCell = oR.insertCell(-1);
			var oDateCell = oR.insertCell(-1);

			var oNameInput = oNameCell.appendChild(jsUtils.CreateElement('INPUT', {type:'text', value:oBXDialogControls.DefaultDirName, id:'__edited_element', __bx_mode: 'new'}, {width:'100%'}));
			oBXDialogWindow.oNameInput = oNameInput;

			oBXDialogWindow.SelectInput(oNameInput);
			jsUtils.addEvent(document, "keydown", oBXDialogWindow.OnElementKeyPress);
			jsUtils.addEvent(oNameInput, "blur", oBXDialogWindow.OnElementBlur);
		};

		var oSubContTable = BX("__bx_oSubContTable");
		addElement_detail(oSubContTable);
	},

	AddNewElement_preview: function()
	{
		var addElement_preview = function(oCont)
		{
			var elDiv = oCont.appendChild(jsUtils.CreateElement('DIV', {className: "bxfd-prev-cont"}));
			var elTable = elDiv.appendChild(jsUtils.CreateElement('TABLE', {},{width:"100%", height:"100%"}));
			var oPreviewCell = elTable.insertRow(-1).insertCell(-1);
			oPreviewCell.align = "center";
			oPreviewCell.unselectable = "on";
			oPreviewCell.valign = "middle";
			oPreviewCell.style.height = "110px";

			var oDetailsCell = elTable.insertRow(-1).insertCell(-1);
			oPreviewCell.appendChild(jsUtils.CreateElement('IMG', {src:oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons['folder'].big}));


			var oNameInput = oDetailsCell.appendChild(jsUtils.CreateElement('INPUT', {type:'text', value:oBXDialogControls.DefaultDirName, id:'__edited_element', __bx_mode:'new'}, {width:'100%'}));
			oBXDialogWindow.oNameInput = oNameInput;

			oBXDialogWindow.oNameInput = oNameInput;

			oBXDialogWindow.SelectInput(oNameInput);
			jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
			jsUtils.addEvent(oNameInput, "blur", oBXDialogWindow.OnElementBlur);
		};

		var oSubContTable = BX("__bx_oSubContTable");
		var oCont = oSubContTable.rows[0].cells[0];

		addElement_preview(oCont);
	},

	RenameElement: function(ElementCont)
	{
		var
			path = ElementCont.getAttribute('__bxpath'),
			oEl = oBXDialogWindow.arFiles[path],
			ElCont;

		oBXFileDialog.SetFocus('');
		oBXDialogWindow.oNameInput = false;
		if (ElementCont.nodeName.toUpperCase() == 'TABLE') //List and detail mode
			ElCont = ElementCont.cells[1];
		else // Preview mode
			ElCont = ElementCont.getElementsByTagName('TD')[1];

		oBXDialogWindow.cancelRename_innerHTML = ElCont.innerHTML;
		ElCont.innerHTML = '';

		var oNameInput = ElCont.appendChild(jsUtils.CreateElement('INPUT', {type:'text', value:oEl.name, id: '__edited_element', __bx_mode: 'rename', __bx_old_name: oEl.name}, {width: '100%'}));
		oBXDialogWindow.oNameInput = oNameInput;

		oBXDialogWindow.SelectInput(oNameInput);
		jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
		jsUtils.addEvent(oNameInput, "blur", oBXDialogWindow.OnElementBlur);
	},

	OnElementKeyPress: function(e)
	{
		try{
			if (!e)
				e = window.event;
			if (!e)
				return;

			var esc = (e.keyCode == 27);
			var enter = (e.keyCode == 13);

			if (esc || enter)
			{
				var oElement = BX('__edited_element');
				jsUtils.removeEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
				jsUtils.removeEvent(oElement, "blur", oBXDialogWindow.OnElementBlur);

				var mode = oElement.getAttribute('__bx_mode');
				if (mode == 'new')
					oBXDialogWindow.NewDir((esc) ? oBXDialogControls.DefaultDirName : oElement.value);
				else if (mode == 'rename')
				{
					var old_name = oElement.getAttribute('__bx_old_name');
					if (esc)
						oBXDialogWindow.CancelRename();
					else
						oBXDialogWindow.Rename(old_name, oElement.value);
				}
			}
		} catch(e){}
	},

	OnElementBlur: function(e)
	{
		var oElement = BX("__edited_element");
		if (!oElement)
			return;
		jsUtils.removeEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
		jsUtils.removeEvent(oElement, "blur", oBXDialogWindow.OnElementBlur);
		var mode = oElement.getAttribute('__bx_mode');

		if (mode == 'new')
			oBXDialogWindow.NewDir(oElement.value);
		else if (mode == 'rename')
		{
			var old_name = oElement.getAttribute('__bx_old_name');
			oBXDialogWindow.Rename(old_name, oElement.value);
		}
	},

	NewDir: function(name, dublReq)
	{
		var path = oBXDialogControls.dirPath.Get();
		setTimeout(function ()
			{
				var nd = new JCHttpRequest();
				window.action_warning = '';
				nd.Action = function(result)
				{
					setTimeout(function ()
					{
						oWaitWindow.Hide();
						var new_sess = oBXFileDialog.CheckReqLostSessid(result);
						if (new_sess !== true)
						{
							if (dublReq)
								return alert(FD_MESS.FD_SESS_EXPIRED);
							oBXFileDialog.sessid = new_sess;
							return oBXDialogWindow.NewDir(name, true);
						}

						if (!window.action_status)
						{
							if (window.action_warning.length > 0)
								alert(window.action_warning);

							var oElement = BX('__edited_element');
							if (oElement)
							{
								oBXDialogWindow.SelectInput(oElement);
								jsUtils.addEvent(oElement, "blur", oBXDialogWindow.OnElementBlur);
								jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
							}
						}
						else if (window.action_status === true)
						{
							arFDDirs[path + '/' + name] = [];
							arFDFiles[path + '/' + name] = [];
							oBXDialogWindow.DisplayFolderContent(path);
							var
								lind = path.lastIndexOf('/'),
								parPath = lind == 0 ? '/' : path.substr(0, lind),
								parItems = arFDDirs[parPath],
								l = parItems.length, i;

							// find cur element in parent path array
							for (i = 0; i < l; i++)
							{
								if (parItems[i].path == path)
								{
									parItems[i].empty = false;
									break;
								}
							}
							oBXDialogTree.DisplaySubTree(oBXDialogTree.arDirConts[parPath], window.arFDDirs[parPath], true);
							oBXDialogTree.HighlightPath(path);

							setTimeout(function()
							{
								// Select folder after creation
								var tmpPath;
								if(path == '/')
									tmpPath = '/' + name;
								else
									tmpPath = path + '/' + name;

								var oFile = oBXDialogWindow.arFiles[tmpPath];
								if (oFile && oFile._winCont && oFile._winCont[oBXDialogWindow.view])
									oBXDialogWindow.SelectElement(oFile._winCont[oBXDialogWindow.view], name);

								if(path == '/')
									oBXDialogTree.DisplayTree();
								else
									oBXDialogTree.DisplaySubTree(oBXDialogTree.arDirConts[path], window.arFDDirs[path], true);
							}, 50);
						}
					}, 5);
				};

				var mess = oBXDialogWindow.ClientSideCheck(path, name, false, true);
				if (mess !== true)
				{
					setTimeout(function ()
					{
						if (!window.oBXFileDialog)
							return;
						alert(mess);
						var oElement = BX('__edited_element');
						if (oElement)
						{
							oBXDialogWindow.SelectInput(oElement);
							jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
							jsUtils.addEvent(oElement, "blur", oBXDialogWindow.OnElementBlur);
						}
					}, 250);
				}
				else
				{
					oWaitWindow.Show();
					oBXFileDialog.RequestUrl = oBXFileDialog.GetRequestUrl(getSite());
					nd.Send(oBXFileDialog.RequestUrl + '&action=new_dir&path=' + jsUtils.urlencode(path) + '&name=' + jsUtils.urlencode(name) + '&add_to_menu=' + (oBXFileDialog.oConfig.operation == 'S' ? '1' : ''));
				}
			}, 5
		);
	},

	// Remove file OR dir
	Remove: function(path, bFolder, dublReq)
	{
		var rf = new JCHttpRequest();
		window.action_warning = '';
		rf.Action = function(result)
		{
			setTimeout(function ()
			{
				oWaitWindow.Hide();
				var new_sess = oBXFileDialog.CheckReqLostSessid(result);
				if (new_sess !== true)
				{
					if (dublReq)
						return alert(FD_MESS.FD_SESS_EXPIRED);
					oBXFileDialog.sessid = new_sess;
					return oBXDialogWindow.Remove(path, bFolder, true);
				}

				if (!window.action_status)
				{
					if (window.action_warning.length > 0)
						return alert(window.action_warning);
				}
				else if (window.action_status === true)
				{
					var li = path.lastIndexOf('/');
					if (li != -1)
					{
						var p, pl = path.length;
						// Clean dirs
						for (p in window.arFDDirs)
						{
							if (p.substr(0, pl) == path)
							{
								window.arFDDirs[p] = false;
								//oBXDialogWindow.arFiles[p] = null;
							}
						}
						// Clean files
						for (p in window.arFDFiles)
						{
							if (p.substr(0, pl) == path)
								window.arFDFiles[p] = false;
						}

						var parPath = li == 0 ? '/' : path.substr(0, li);
						oBXDialogWindow.DisplayFolderContent(parPath);
						if (bFolder)
						{
							var
								openPath = parPath,
								parItems = arFDDirs[parPath];

							if (parItems.length == 0)
							{
								var
									li2 = parPath.lastIndexOf('/'),
									parPath2 = parPath.substr(0, li2),
									i;

								if (arFDDirs[parPath2] && arFDDirs[parPath2].length)
								{
									// find cur element in parent path array
									for (i = 0; i < arFDDirs[parPath2].length; i++)
									{
										if (arFDDirs[parPath2][i] && arFDDirs[parPath2][i].path == parPath)
										{
											arFDDirs[parPath2][i].empty = true;
											break;
										}
									}
								}
								openPath = parPath2;
							}

							oBXDialogTree.DisplaySubTree(oBXDialogTree.arDirConts[openPath], window.arFDDirs[openPath], true);
							oBXDialogTree.HighlightPath(parPath);
						}
					}
				}
			}, 5);
		};

		oWaitWindow.Show();
		oBXFileDialog.RequestUrl = oBXFileDialog.GetRequestUrl(getSite());
		rf.Send(oBXFileDialog.RequestUrl + '&action=remove&path=' + jsUtils.urlencode(path) + '&add_to_menu=' + (oBXFileDialog.oConfig.operation == 'S' ? '1' : ''));
	},

	Rename: function(old_name, name, dublReq)
	{
		if (old_name == name)
			return oBXDialogWindow.CancelRename();

		var
			path = oBXDialogControls.dirPath.Get(),
			oEl = oBXDialogWindow.arFiles[(path == '/' ? '' : path) + '/' + old_name],
			bFolder = oEl.icon == 'folder',
			mess = oBXDialogWindow.ClientSideCheck(path, name, !bFolder, bFolder);

		if (mess !== true) // Bad name - propose to set correct name
		{
			setTimeout(function ()
			{
				if (!window.oBXFileDialog)
					return;

				alert(mess);
				var oElement = BX('__edited_element');
				if (oElement)
				{
					oElement.value = old_name;
					oBXDialogWindow.SelectInput(oElement);
					jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
					jsUtils.addEvent(oElement, "blur", oBXDialogWindow.OnElementBlur);
				}
			}, 100);
		}
		else // Do rename
		{
			var rn = new JCHttpRequest();
			window.action_warning = '';

			rn.Action = function(result)
			{
				if (!window.oBXFileDialog)
					return;
				setTimeout(function ()
				{
					oWaitWindow.Hide();
					var new_sess = oBXFileDialog.CheckReqLostSessid(result);
					if (new_sess !== true)
					{
						if (dublReq)
							return alert(FD_MESS.FD_SESS_EXPIRED);
						oBXFileDialog.sessid = new_sess;
						return oBXDialogWindow.Rename(old_name, name, true);
					}

					if (!window.action_status)
					{
						if (window.action_warning.length > 0)
							alert(window.action_warning);

						var oElement = BX('__edited_element');
						if (oElement)
						{
							oBXDialogWindow.SelectInput(oElement);
							jsUtils.addEvent(oElement, "blur", oBXDialogWindow.OnElementBlur);
							jsUtils.addEvent(document, "keypress", oBXDialogWindow.OnElementKeyPress);
						}
					}
					else if (window.action_status === true)
					{
						oBXDialogWindow.DisplayFolderContent(path);
						var oCont = oBXDialogTree.arDirConts[path];
						if (oCont)
							oBXDialogTree.DisplaySubTree(oCont, window.arFDDirs[path], true);
					}
				}, 5);
			};

			oWaitWindow.Show();
			oBXFileDialog.RequestUrl = oBXFileDialog.GetRequestUrl(getSite());
			rn.Send(oBXFileDialog.RequestUrl + '&action=rename&path=' + jsUtils.urlencode(path) + '&add_to_menu=' + (oBXFileDialog.oConfig.operation == 'S' ? '1' : '') + '&name=' + jsUtils.urlencode(name) + '&old_name=' + jsUtils.urlencode(old_name));
		}
	},

	CancelRename: function()
	{
		var oElement = BX('__edited_element');
		if (oElement)
			oElement.parentNode.innerHTML = oBXDialogWindow.cancelRename_innerHTML;
		oBXDialogWindow.oNameInput = false;
	},

	ClientSideCheck: function(path, name, checkFiles, checkDirs)
	{
		if (name.length <= 0)
			return FD_MESS.FD_EMPTY_NAME;

		var p, new_name = name.replace(/[\\\/:*?\"\'<>|]/i, '');
		if (name !== new_name)
			return FD_MESS.FD_INCORRECT_NAME;

		if (checkFiles && oBXFileDialog.bSelectFiles)
			for (p in arFDFiles[path])
				if (arFDFiles[path][p].name == name)
					return FD_MESS.FD_NEWFILE_EXISTS;

		if (checkDirs)
			for (p in arFDDirs[path])
				if (arFDDirs[path][p].name == name)
					return FD_MESS.FD_NEWFOLDER_EXISTS;

		return true;
	},

	SelectInput: function(oElement, value)
	{
		if (!value)
			value = oBXDialogControls.DefaultDirName;

		if (!oElement)
			oElement = BX('__edited_element');
		if (!oElement)
			return;

		oElement.select();
		oElement.focus();
	},

	OnContextMenu: function(e)
	{
		var
			paramsCont = this, // TR or DIV
			path = paramsCont.getAttribute('__bxpath'),
			oEl = oBXDialogWindow.arFiles[path];

		if (!e)
			e = window.event;
		if (!e || !oEl)
			return;

		oBXFileDialog.SetFocus('window');
		oBXDialogWindow.SelectElement(paramsCont, path);

		if (oEl.permission.del || oEl.permission.ren)
		{
			if (e.pageX || e.pageY)
			{
				e.realX = e.pageX;
				e.realY = e.pageY;
			}
			else if (e.clientX || e.clientY)
			{
				e.realX = e.clientX + document.body.scrollLeft;
				e.realY = e.clientY + document.body.scrollTop;
			}

			var arItems = [];
			if (oEl.permission.ren)
			{
				arItems.push({
					id : 'rename',
					src : '/bitrix/images/main/file_dialog/rename.gif',
					name : FD_MESS.FD_RENAME,
					title : FD_MESS.FD_RENAME_TITLE,
					handler : function() {oBXDialogWindow.RenameElement(paramsCont);}
				});
			}

			if (oEl.permission.del)
			{
				if (arItems.length > 0)
					arItems.push('separator');

				arItems.push({
					id : 'delete',
					src : '/bitrix/images/main/file_dialog/delete.gif',
					name : FD_MESS.FD_DELETE,
					title : FD_MESS.FD_DELETE_TITLE,
					handler : function()
					{
						if (confirm(oEl.icon == 'folder' ? FD_MESS.FD_CONFIRM_DEL_DIR : FD_MESS.FD_CONFIRM_DEL_FILE))
							oBXDialogWindow.Remove(path, oEl.icon == 'folder');
					}
				});
			}

			oBXDialogWindow.oCM.Show(3500, 0, {left : e.realX, top : e.realY}, arItems);
		}
		else if (oBXDialogWindow.oCM)
			oBXDialogWindow.oCM.menu.PopupHide();

		return BX.PreventDefault(e);
	},

	OnKeyDown : function(e)
	{
		if (oBXDialogWindow.oNameInput !== false)
			return true;

		var newInd = false, curInd, l, oEl;
		switch(e.keyCode)
		{
			case 37: // Left
				if (this.view != 'detail') // For detail viewing it looks like "Up"
				{
					curInd = this.GetCurIndex(this.curSelectedItem.path);
					if (this.view == 'list')
						newInd = curInd - 12;
					else // preview
						newInd = curInd - 1;

					if (newInd < 0)
						newInd = 0;
					break;
				}
			case 38: // Up
				curInd = this.GetCurIndex(this.curSelectedItem.path);

				if (curInd === false || curInd <= 0)
					return;

				if (this.view == 'preview')
					newInd = (curInd > 2) ? curInd - 3 : 0;
				else
					newInd = curInd - 1;
				break;
			case 39: // Right
				if (this.view != 'detail') // For detail viewing it looks like "DOWN"
				{
					curInd = this.GetCurIndex(this.curSelectedItem.path);
					l = this.arElIndex.length;

					if (curInd === false || curInd == l - 1)
						return;

					if (this.view == 'list')
						newInd = curInd + 12;
					else // preview
						newInd = curInd + 1;

					if (newInd > l - 1)
						newInd = l - 1;
					break;
				}
			case 40: // Down
				curInd = this.GetCurIndex(this.curSelectedItem.path);
				l = this.arElIndex.length;

				if (curInd === false || curInd >= l - 1)
					return;

				if (this.view == 'preview')
					newInd = (curInd < l - 4) ? curInd + 3 : l - 1;
				else
					newInd = curInd + 1;

				break;
			case 46: // Del - delete
				oEl = this.arElIndex[this.GetCurIndex(this.curSelectedItem.path)];
				if (oEl)
				{
					if (oEl.permission.del && confirm(oEl.icon == 'folder' ? FD_MESS.FD_CONFIRM_DEL_DIR : FD_MESS.FD_CONFIRM_DEL_FILE))
						oBXDialogWindow.Remove(this.curSelectedItem.path, oEl.icon == 'folder');
					return BX.PreventDefault(e);
				}
				break;
			case 113: // F2 - rename
				oEl = this.arElIndex[this.GetCurIndex(this.curSelectedItem.path)];
				if (oEl)
				{
					if (oEl.permission.ren)
						oBXDialogWindow.RenameElement(this.curSelectedItem.cont);
					return BX.PreventDefault(e);
				}
				break;
			case 13: // Enter
				oEl = this.arElIndex[this.GetCurIndex(this.curSelectedItem.path)];
				if (oEl)
				{
					oEl = this.arElIndex[this.GetCurIndex(this.curSelectedItem.path)];
					if (oEl.icon == 'folder')
						oBXDialogTree.SetPath(this.curSelectedItem.path);
					else
						oBXFileDialog.SubmitFileDialog();
					return BX.PreventDefault(e);
				}
		}

		if (newInd !== false)
		{
			var newEl = this.arElIndex[newInd];
			var name = false;

			if (newEl.icon == 'folder' && oBXFileDialog.bSelectDirs || newEl.icon != 'folder' && oBXFileDialog.bSelectFiles)
				name = newEl.name;

			this.SelectElement(newEl._winCont[this.view], newEl.path, name);
			return BX.PreventDefault(e);
		}
	},

	GetCurIndex : function(path)
	{
		for (var i = 0, l = this.arElIndex.length; i < l; i++)
		{
			if (this.arElIndex[i].path == path)
				return i;
		}
		return false;
	}
};


function BXDialogTabs() {this.Init();}

BXDialogTabs.prototype =
{
	Init: function()
	{
		this.contTable = BX("__bx_tab_cont");
		this.arTabs = {};
		this.tabsCount = 0;
		this.activeTabName = '';
	},

	AddTab: function(name,title,fFunc,bActive)
	{
		this.arTabs[name] =
		{
			name : name,
			title : title,
			func : fFunc,
			active : bActive,
			disable : false
		};

		if (bActive)
		{
			if (this.activeTabName != '' && this.activeTabName != name)
				this.arTabs[this.activeTabName].active = false;

			this.activeTabName = name;
		}

		this.tabsCount++;
	},

	DisplayTabs: function()
	{
		this.contTable.innerHTML = "";
		if (this.tabsCount < 2)
			return;
		var _this = this;
		var createBlankImage = function(oCell,width,className)
		{
			var _style = "background-image: url(/bitrix/images/main/file_dialog/tabs/tab_icons.gif);";
			oCell.innerHTML = '<img class=" '+className+'" src="/bitrix/images/1.gif" height="27px" width="'+width+'px" style="'+_style+'"/>';
		};

		var createTitleArea = function(oCell,name,title,hint,bActive,bDisable)
		{
			oCell.innerHTML = "<span style='white-space: nowrap !important; margin: 0 5px;'>" + title + "</span>";
			oCell.style.cursor = "default";
			if (bActive)
				oCell.className = "fd_tabs_a";
			else if (bDisable)
				oCell.className = "fd_tabs_pd";
			else
				oCell.className = "fd_tabs_p";

			if (!bDisable)
				oCell.onclick = function(e){_this.SetActive(name,!bActive);};
			oCell.title = hint;
		};

		var
			count = 0,
			oTab,
			class1, class2, class3, name,
			oTable = this.contTable.appendChild(document.createElement("TABLE"));

		oTable.className = "tab-content-table";
		var oRow = oTable.insertRow(-1);
		for (name in this.arTabs)
		{
			count++;
			oTab = this.arTabs[name];
			oCell_1 = oRow.insertCell(-1);
			oCell_1.style.width = "0%";
			oCell_2 = oRow.insertCell(-1);
			oCell_2.style.width = "0%";

			if (count == 1)
			{
				if (oTab.active)
					createBlankImage(oCell_1,6,"fd_tabs_0a");
				else
					createBlankImage(oCell_1,6,"fd_tabs_0p");

				createTitleArea(oCell_2,oTab.name,oTab.title,_ReplaceNbspBySpace(oTab.title),oTab.active,oTab.disable);
			}
			else if (this.tabsCount == count)
			{
				oCell_3 = oRow.insertCell(-1);
				if (oTab.active)
				{
					createBlankImage(oCell_1, 11, "fd_tabs_pa");
					createTitleArea(oCell_2, oTab.name, oTab.title,_ReplaceNbspBySpace(oTab.title),oTab.active,oTab.disable);
					createBlankImage(oCell_3,9,"fd_tabs_a0");
				}
				else
				{
					createBlankImage(oCell_1,11,"fd_tabs_ap");
					createTitleArea(oCell_2,oTab.name,oTab.title,_ReplaceNbspBySpace(oTab.title),oTab.active,oTab.disable);
					createBlankImage(oCell_3,9,"fd_tabs_p0");
				}
			}
		}
		lastCell = oRow.insertCell(-1);
		lastCell.style.width = "100%";
		lastCell.className = "fd_tabs_0";
	},

	SetActive: function(tabName,bActive)
	{
		var oTab = this.arTabs[tabName];
		if (oTab.active)
			return;

		for (var name in this.arTabs)
			this.arTabs[name].active = false;

		oTab.active = true;

		if (oTab.func)
			oTab.func();

		this.DisplayTabs();
	},

	DisableTab: function(tabName, bDisable)
	{
		if (!this.arTabs[tabName] || this.arTabs[tabName].disable == bDisable)
			return;

		var br = false;
		for (var name in this.arTabs)
		{
			this.arTabs[name].active = false;
			if (name == tabName || br)
				continue;

			this.arTabs[name].active = true;
			this.arTabs[name].func();
			br = true;
		}
		this.arTabs[tabName].disable = bDisable;
		this.DisplayTabs();
	}
};


function BXDialogControls()
{
	var _this = this;
	this.DefaultDirName = 'New Folder';
	this.dirPath = new __DirPathBar();
	this.filePath = new __FilePathBar();
	this.Preview = new __Preview();
	this.ViewSelector = new __ViewSelector();
	this.SortSelector = new __SortSelector();
	this.Uploader = new __Uploader();
	this.Filter = new __FileFilter();
	this.History = new __History();

	this.currentSite = BXSite;

	// Part of logic of JCFloatDiv.Show()   Prevent bogus rerendering window in IE...
	window.fd_view_list.BuildItems();
	this.fd_view_list_frame = document.body.appendChild(jsUtils.CreateElement('IFRAME', {id: 'fd_view_list_frame', src: "javascript:''", className: 'bxfd-sys-frame'}));
	if (window.fd_site_list)
	{
		window.fd_site_list.BuildItems();
		this.fd_site_list_frame = document.body.appendChild(jsUtils.CreateElement('IFRAME', {id: 'fd_site_list_frame', src: "javascript:''", className: 'bxfd-sys-frame'}));
	}

	var SubmButton = BX("__bx_fd_submit_but");
	if (oBXFileDialog.oConfig.operation == 'O')
	{
		SubmButton.value = FD_MESS.FD_BUT_OPEN;
		SubmButton.onclick = SubmitFileDialog;
	}
	else if (oBXFileDialog.oConfig.operation == 'S')
	{
		SubmButton.value = FD_MESS.FD_BUT_SAVE;
		SubmButton.onclick = SubmitFileDialog;
		if (oBXFileDialog.oConfig.showAddToMenuTab)
		{
			var SubmButton2 = BX("__bx_fd_submit_but2");
			SubmButton2.value = FD_MESS.FD_BUT_SAVE;
			SubmButton2.onclick = SubmitFileDialog;
		}
	}

	if (oBXFileDialog.oConfig.operation == 'S' && oBXFileDialog.oConfig.showAddToMenuTab)
	{
		BX("__bx_page_title_cont").style.display = "block";
		this.PageTitle1 = BX("__bx_page_title1");
		this.PageTitle2 = BX("__bx_page_title2");
		this.PageTitle1.onchange = function(e)
		{
			_this.PageTitle2.value = this.value;
		};
		this.PageTitle2.onchange = function(e)
		{
			_this.PageTitle1.value = this.value;
		};
		this.PageTitle = {};
		this.PageTitle.Get = function()
		{
			return _this.PageTitle1.value;
		};

		this.PageTitle.Set = function(value)
		{
			_this.PageTitle1.value = _this.PageTitle2.value = value;
		};

		var defTitleInp = BX('title');
		if (defTitleInp)
			this.PageTitle.Set(defTitleInp.value);
		else
			this.PageTitle.Set('Title');
	}

	this.GoButton = BX("__bx_dir_path_go");
	this.GoButton.onclick = function(e) {oBXDialogTree.SetPath(oBXDialogControls.dirPath.Get(true));};

	this.UpButton = BX("__bx_dir_path_up");
	this.UpButton.onclick = function(e)
	{
		var sPath = oBXDialogControls.dirPath.Get(true);
		if (sPath != '/')
			oBXDialogTree.SetPath(sPath.substr(0, sPath.lastIndexOf('/')));
	};

	this.RootButton = BX("__bx_dir_path_root");
	this.RootButton.onclick = function(e)
	{
		if (oBXDialogControls.dirPath.Get(true) != '/')
			oBXDialogTree.SetPath('/');
	};

	this.NewDirButton = BX("__bx_new_dir");
	this.NewDirButton.onclick = function(e){oBXDialogWindow.AddNewElement();};

	this.NewDirButtonActive = true;
	this.NewDirButtonChange = function()
	{
		var path = oBXDialogControls.dirPath.Get();
		if (path == '')
			path = '/';
		var oEl = window.arFDPermission[path];
		if (oEl)
		{
			if (oEl.new_folder && !this.NewDirButtonActive)
			{
				this.NewDirButton.className = "fd_iconkit new_dir";
				this.NewDirButtonActive = true;
			}
			else if(!oEl.new_folder && this.NewDirButtonActive)
			{
				this.NewDirButton.className = "fd_iconkit new_dir_dis";
				this.NewDirButtonActive = false;
			}
		}
	};
}

BXDialogControls.prototype.RefreshOnclick = function()
{
	arFDDirs = {};
	arFDFiles = {};
	arFDPermission = {};
	BXDialogWindow.arFiles = {};
	oBXDialogTree.bRedisplayTree = true;

	var path = oBXDialogControls.dirPath.Get() || '/';
	oBXDialogTree.SetPath(path);
};

BXDialogControls.prototype.SiteSelectorOnChange = function(site)
{
	if (this.currentSite != site)
	{
		if (!window.bx_fd_site_selector)
			window.bx_fd_site_selector = BX('__bx_site_selector');
		window.bx_fd_site_selector.innerHTML = '<span>' + site + '</span><span class="fd_iconkit site_selector_div_arrow">&nbsp;&nbsp;</span>';
		this.currentSite = site;
		oBXDialogControls.dirPath.Set('/');
		this.RefreshOnclick();

		// Cange selected item in selector
		fd_site_list.SetItemIcon(window.bx_fd_site_selector.getAttribute('bxvalue'), '');
		fd_site_list.SetItemIcon(site, 'checked');
		window.bx_fd_site_selector.setAttribute('bxvalue', site);
	}
	window.fd_site_list.PopupHide();
};

BXDialogControls.prototype.SiteSelectorOnClick = function(node)
{
	var pos = jsUtils.GetRealPos(node);
	pos.left += 2;
	setTimeout(function(){window.fd_site_list.PopupShow(pos);}, 5);
};

function BXFDSorter(){}
BXFDSorter.prototype =
{
	name: function(a, b)
	{
		var _this = oBXDialogWindow.oSorter;
		if ((a.icon == 'folder' && b.icon == 'folder') || (a.icon != 'folder' && b.icon != 'folder'))
			return _this.common_sort(a.name,b.name);
		else if (a.icon == 'folder' && b.icon != 'folder')
			return (_this.order == 'des' ? 1 : -1);
		else
			return (_this.order == 'des' ? -1 : 1);
	},
	size: function(a, b)
	{
		return oBXDialogWindow.oSorter.common_sort(parseInt(a.size), parseInt(b.size));
	},
	type: function(a, b)
	{
		var _this = oBXDialogWindow.oSorter;
		if ((a.icon == 'folder' && b.icon == 'folder') || (a.ext == b.ext))
			return _this.common_sort(a.name,b.name);
		else if (a.icon != 'folder' && b.icon != 'folder')
			return _this.common_sort(a.ext,b.ext);
		else if (a.icon == 'folder' && b.icon != 'folder')
			return (_this.order == 'des' ? 1 : -1);
		else
			return (_this.order == 'des' ? -1 : 1);
	},
	date: function(a, b)
	{
		var
			_this = oBXDialogWindow.oSorter,
			_a = parseInt(a.timestamp),
			_b = parseInt(b.timestamp);

		if ((a.icon == 'folder' && b.icon == 'folder') || (a.icon != 'folder' && b.icon != 'folder'))
			return _this.common_sort(_a,_b);
		else if (a.icon == 'folder' && b.icon != 'folder')
			return (_this.order == 'des' ? 1 : -1);
		else
			return (_this.order == 'des' ? -1 : 1);
	},
	common_sort: function(a, b)
	{
		var
			_this = oBXDialogWindow.oSorter,
			res = 1;
		if (a < b)
			res = -1;
		else if (a == b)
			res = 0;

		if (_this.order == 'des')
			res = -res;

		return res;
	},
	Sort: function(arr)
	{
		this.order = oBXDialogWindow.sort_order;
		arr.sort(this[oBXDialogWindow.sort || 'name']);
		return arr;
	}
};

function __DirPathBar()
{
	this.oInput = BX("__bx_dir_path_bar");
	this.oInput.onclick = function(e) {oBXFileDialog.SetFocus('name');};
	this.value = this.oInput.value;

	this.butBack = BX("__bx_dir_path_back");
	this.butForward = BX("__bx_dir_path_forward");
	this.butBack.onclick = function(e)
	{
		var newPath = oBXDialogControls.History.Back();
		if (newPath !== false)
			oBXDialogTree.SetPath(newPath);
	};
	this.butForward.onclick = function(e)
	{
		var newPath = oBXDialogControls.History.Forward();
		if (newPath !== false)
			oBXDialogTree.SetPath(newPath);
	};

	__DirPathBar.prototype.Set = function(sValue, bSaveConfig)
	{
		if (!sValue || sValue == "")
			sValue = "/";
		sValue = sValue.replace(/\/\//ig,"/");

		if (this.value != sValue)
		{
			this.oInput.value = this.value = sValue;
			this.OnChange(bSaveConfig);
		}
		else
			this.oInput.value = this.value = sValue;
	};

	__DirPathBar.prototype.Get = function(bEmp)
	{
		var path = this.oInput.value;
		path = path.replace(/\\/ig,"/");
		path = path.replace(/\/\//ig,"/");
		if (path.substr(path.length-1) == "/")
			path = path.substr(0,path.length-1);

		if (path == '')
			path = '/';
		return path;
	};

	__DirPathBar.prototype.OnChange = function(bSaveConfig)
	{
		var _get = this.Get();
		oBXDialogControls.UpButton.className = "fd_iconkit "+(_get == "" ? "dir_path_up_dis" : "dir_path_up");
		oBXFileDialog.UserConfig.path = _get;
		oBXDialogControls.History.Push(_get);
		//if (bSaveConfig !== false)
		//	oBXFileDialog.SaveConfig();
	};
}

function __FilePathBar()
{
	__FilePathBar.prototype.Init = function()
	{
		this.oInput = BX("__bx_file_path_bar");
		if (oBXFileDialog.oConfig.operation == 'S')
		{
			var defFilenameInp = BX('filename');
			if (defFilenameInp && defFilenameInp.value.length > 0)
				this.defaultName = defFilenameInp.value;
			else
			{
				var exts = oBXFileDialog.oConfig.fileFilter, ext;
				if (exts.length > 0)
				{
					var ind = exts.indexOf(',');
					ext = (ind > 0) ? exts.substr(0, ind) : exts;
				}
				else
					ext = 'php';
				this.defaultName = "untitled." + ext;
			}
		}
		this.oInput.onclick = function()
		{
			this.focus();
			oBXFileDialog.SetFocus('name');
		};
	};

	__FilePathBar.prototype.Set = function(sValue)
	{
		this.oInput.value = sValue;
	};

	__FilePathBar.prototype.Get = function()
	{
		return this.oInput.value;
	};

	this.Init();
}


function __Preview() {this.Init();}
__Preview.prototype =
{
	Init : function()
	{
		this.oDiv = BX("bxfd_previewContainer");
		this.addInfoCont = BX("bxfd_addInfoContainer");
		if (oBXFileDialog.oConfig.select == 'D')
			this.oDiv.parentNode.style.visibility = "hidden";
	},

	Display : function(sPath)
	{
		this.Clear();
		if (!oBXDialogWindow.arFiles[sPath])
			return;

		if (BXFDIsImage(sPath))
			this.DisplayImage(sPath);
		else if (getExtension(sPath) == 'swf')
			this.DisplayFlash(sPath);
		else
			this.DisplayBigIcon(sPath);
	},

	DisplayImage : function(sPath)
	{
		var
			oEl = oBXDialogWindow.arFiles[sPath],
			_this = this,
			src;

		if (oBXFileDialog.oConfig.genThumb && oEl.tmb)
			return _this._DisplayImage(oEl);

		var div = BX("__bx_get_real_size_cont");
		BXFDCleanNode(div);
		var oImg = div.appendChild(jsUtils.CreateElement('IMG', {src: sPath}));
		oImg.onload = function() {_this._DisplayImage(oEl, this.offsetWidth || 100, this.offsetHeight || 100);};
	},

	_DisplayImage : function(oEl, w, h)
	{
		var
			newW, newH,
			date = oEl.date.substr(0, oEl.date.lastIndexOf(':')),
			sPath = oEl.tmb || oEl.path;

		this.addInfoCont.innerHTML = getFileSize(oEl.size) + "  " + date;
		if (w && h)
		{
			var a = 100, b = 130; //max height, width
			newW = w + "px";
			newH = h + "px";

			if (a/b > h/w)
			{
				//Resize by width
				if (w > b)
				{
					newW = b + "px";
					newH = Math.round(h * b / w) + "px";
				}
			}
			else
			{
				//Resize by height
				if (h > a)
				{
					newH = a+"px";
					newW = Math.round(w * a / h) + "px";
				}
			}
			sPath = oEl.path;
		}

		var oImg = this.oDiv.appendChild(jsUtils.CreateElement('IMG', {src: sPath, align: 'middle'}));
		if (newW && newH)
		{
			oImg.style.width = newW;
			oImg.style.height = newH;
		}

		this.oDiv.className = '';
	},

	DisplayFlash : function(sPath)
	{
		var
			oEl = oBXDialogWindow.arFiles[sPath],
			date = oEl.date.substr(0, oEl.date.lastIndexOf(':')),
			pFrame = this.oDiv.appendChild(jsUtils.CreateElement('IFRAME', {id: "bxfd_ifrm_flash", frameborder: "0"}));

		this.addInfoCont.innerHTML = getFileSize(oEl.size) + " " + date;
		pFrame.setAttribute("src", oBXFileDialog.GetRequestUrl(getSite()) + '&action=flash&path=' + jsUtils.urlencode(sPath));
		this.oDiv.className = '';
	},

	DisplayBigIcon : function(sPath)
	{
		var oEl = oBXDialogWindow.arFiles[sPath];
		if (oEl.icon == 'folder')
			return;

		var
			date = oEl.date.substr(0,oEl.date.lastIndexOf(':')),
			src = oBXDialogWindow.iconsPath + oBXDialogWindow.arIcons[oEl.icon].big;

		this.addInfoCont.innerHTML = getFileSize(oEl.size) + " " + date;
		this.oDiv.appendChild(jsUtils.CreateElement('IMG', {src: src}, {width: '25px', height: '25px'}));
		this.oDiv.className = 'bxfd-prev-big-icon';
	},

	Clear : function()
	{
		this.oDiv.innerHTML = "";
		this.oDiv.className = '';
		this.addInfoCont.innerHTML = "";
	}
};

function __ViewSelector()
{
	this.oSel = BX("__bx_view_selector");
	this.value = '';
	__ViewSelector.prototype.OnClick = function()
	{
		var pos = jsUtils.GetRealPos(this.oSel);
		pos.left += 7;
		pos.top += 6;
		setTimeout(function(){
			window.fd_view_list.PopupShow(pos);
			// Temp hack for old popupmenus
			if (BX('fd_view_list'))
				BX('fd_view_list').style.zIndex = 3510;
		}, 5);
	};

	__ViewSelector.prototype.OnChange = function(value)
	{
		oWaitWindow.Show();
		setTimeout(function ()
			{
				var path = oBXDialogControls.dirPath.Get(true);
				oBXDialogWindow.DisplayElementsList(arFDDirs[path], arFDFiles[path], value, oBXDialogWindow.filter,oBXDialogWindow.sort, oBXDialogWindow.sort_order);
				oWaitWindow.Hide();
				oBXDialogControls.ViewSelector.Set(value, true);
			}, 3
		);
	};

	__ViewSelector.prototype.Set = function(value, bSaveConfig)
	{
		// Cange selected item in selector
		var cur_val = this.oSel.getAttribute('bxvalue') || '';
		fd_view_list.SetItemIcon(cur_val, '');
		fd_view_list.SetItemIcon(value, 'checked');
		this.oSel.setAttribute('bxvalue', value);
		this.value = value;
		window.fd_view_list.PopupHide();
		oBXFileDialog.UserConfig.view = value;
		//if (bSaveConfig)
		//	oBXFileDialog.SaveConfig();
	};

	__ViewSelector.prototype.Get = function()
	{
		return this.value;
	}
}

function __SortSelector()
{
	var _this = this;
	this.oSel = BX("__bx_sort_selector");
	this.oCheck = BX("__bx_sort_order");

	this.oSel.onchange = function()
	{
		if (oBXDialogWindow.sort == this.value)
			return;

		oWaitWindow.Show();
		oBXDialogControls.SortSelector.OnChange();
		oBXDialogWindow.sort = this.value;
		setTimeout(function ()
		{
			var path = oBXDialogControls.dirPath.Get(true);
			oBXDialogWindow.DisplayElementsList(arFDDirs[path], arFDFiles[path], oBXDialogWindow.view, oBXDialogWindow.filter, oBXDialogWindow.sort, oBXDialogWindow.sort_order);
			oWaitWindow.Hide();
		}, 3);
	};

	this.oCheck.onclick = function()
	{
		var _new = (oBXDialogControls.SortSelector.SortOrderGet() == 'asc' ? 'des' : 'asc');
		oBXDialogControls.SortSelector.SortOrderSet(_new);
		oBXDialogWindow.sort_order = _new;
		oWaitWindow.Show();
		oBXDialogControls.SortSelector.OnChange();
		setTimeout(function ()
		{
			var path = oBXDialogControls.dirPath.Get(true);
			oBXDialogWindow.DisplayElementsList(arFDDirs[path], arFDFiles[path], oBXDialogWindow.view, oBXDialogWindow.filter, oBXDialogWindow.sort, oBXDialogWindow.sort_order);
			oWaitWindow.Hide();
		}, 3);
	};

	__SortSelector.prototype.Set = function(sort, sort_order)
	{
		this.oSel.value = sort;
		this.SortOrderSet(sort_order);

		if (window.oBXDialogWindow)
		{
			oBXDialogWindow.sort = sort;
			oBXDialogWindow.sort_order = sort_order;
		}

		this.OnChange();
	};

	__SortSelector.prototype.Get = function()
	{
		return {sort : this.oSel.value, sort_order : this.SortOrderGet()};
	};

	__SortSelector.prototype.SortOrderSet = function(sort_order)
	{
		this.oCheck.setAttribute("__bx_value", sort_order);
		this.oCheck.className = "fd_iconkit " + ((sort_order == 'asc') ? "sort_up" : "sort_down");
	};

	__SortSelector.prototype.SortOrderGet = function()
	{
		return this.oCheck.getAttribute("__bx_value");
	};

	__SortSelector.prototype.OnChange = function()
	{
		var r = this.Get();
		oBXFileDialog.UserConfig.sort = r.sort;
		oBXFileDialog.UserConfig.sort_order = r.sort_order;
		//oBXFileDialog.SaveConfig();
	}
}

function __FileFilter()
{
	__FileFilter.prototype.Init = function()
	{
		var filter = oBXFileDialog.oConfig.fileFilter;
		this.curentFilter = false;
		this.arFilters = [];
		var _this = this;
		this.oSel = BX("__bx_file_filter");
		if (!oBXFileDialog.bSelectFiles)
		{
			this.oSel.style.display = 'none';
			return;
		}

		this.oSel.options.length = 0;
		this.oSel.onchange = function(e)
		{
			_this.curentFilter = oBXDialogWindow.filter = this.value;
			var path = oBXDialogControls.dirPath.Get(true);
			oWaitWindow.Show();
			oBXDialogWindow.DisplayElementsList(arFDDirs[path], arFDFiles[path], oBXDialogWindow.view, oBXDialogWindow.filter, oBXDialogWindow.sort, oBXDialogWindow.sort_order);
			oWaitWindow.Hide();
		};

		var addOption = function(arExt, sExt, sTitle)
		{
			oOpt = document.createElement('OPTION');
			oOpt.value = _this.arFilters.length;
			_this.arFilters.push(arExt);
			oOpt.innerHTML = sTitle+" ("+sExt+")";
			_this.oSel.appendChild(oOpt);
			oOpt = null;
		};

		if (filter == '')
		{
			addOption('*','*.*',FD_MESS.FD_ALL_FILES);
			return;
		}

		this.oSel.style.display = 'block';
		var arExt, sExt, sTitle, oExt;
		if (typeof(filter) == 'object')
		{
			try
			{
				for (var i = 0; i < filter.length; i++)
				{
					oExt = filter[i];
					if (typeof(oExt.ext) == 'string')
						oExt.ext = oExt.ext.split(',');
					sExt = '*.'+oExt.ext.join(',*.');
					addOption(oExt.ext, sExt, oExt.title);
				}
			}
			catch(e)
			{
				arExt = filter;
				sExt = '*.'+arExt.join(',*.');
				sTitle = '';
				addOption(arExt, sExt, sTitle);
			}
		}
		else if (filter == 'image')
		{
			arExt = ['jpeg','jpg','gif','png','bmp'];
			sExt = '*.jpeg,*.jpg,*.gif,*.png,*.bmp';
			sTitle = FD_MESS.FD_ALL_IMAGES;
			addOption(arExt, sExt, sTitle);
		}
		else
		{
			arExt = filter.split(",");
			sExt = '*.'+arExt.join(',*.');
			sTitle = '';
			addOption(arExt, sExt, sTitle);
		}

		if (oBXFileDialog.oConfig.allowAllFiles)
			addOption('*','*.*', FD_MESS.FD_ALL_FILES);
		this.oSel.options[0].selected = "selected";
		this.curentFilter = 0;
	};
	this.Init();
}

function __Uploader() {this.Init();}
__Uploader.prototype =
{
	Init : function()
	{
		this.oCont = BX("bxfd_upload_container");
		this.oIfrm = BX('bxfd_iframe_upload');
		this.oIfrm.src = oBXFileDialog.GetRequestUrl(getSite()) + '&action=uploader&lang=' + BXLang;

		var _this = this;
		if (jsUtils.IsIE())
			this.oIfrm.onreadystatechange = function(){_this.OnLoad()};
		else
			this.oIfrm.onload = function(){_this.OnLoad()};
	},

	OnLoad : function()
	{
		var
			pFrameDoc = this.oIfrm.contentDocument || this.oIfrm.contentWindow.document,
			_this = this,
			inp = pFrameDoc.getElementById("__bx_fd_load_file");

		if (inp)
		{
			inp.onchange = function()
			{
				var pFrameDoc = _this.oIfrm.contentDocument || _this.oIfrm.contentWindow.document;
				_this.pFilename = pFrameDoc.getElementById("__bx_fd_server_file_name");
				_this.pFilename.value = getFileName(this.value.replace(/\\/ig,"/"));
			};
		}
	},

	OnSubmit : function()
	{
		this.pFrameDoc = this.oIfrm.contentDocument || this.oIfrm.contentWindow.document;

		var
			fileName = this.pFrameDoc.getElementById("__bx_fd_server_file_name").value,
			path = oBXDialogControls.dirPath.Get(),
			mess = oBXDialogWindow.ClientSideCheck(path, fileName, false, false),
			p,
			arExt = false;

		if (mess !== true)
		{
			alert(mess);
			return false;
		}

		//CHECK: If file extension is valid
		try
		{
			if (!oBXFileDialog.oConfig.allowAllFiles)
				arExt = oBXDialogControls.Filter.arFilters[oBXDialogWindow.filter];
		}
		catch(e)
		{
			arExt = false;
		}

		if (arExt !== false)
		{
			if (typeof(arExt) == 'object' && arExt.length > 0)
			{
				var fileExt = (fileName.lastIndexOf('.') != -1) ? fileName.substr(fileName.lastIndexOf('.')+1) : "";
				var res = false;
				for (var _i = 0; _i < arExt.length; _i++)
				{
					if (arExt[_i] == fileExt)
					{
						res = true;
						break;
					}
				}
				if (!res)
				{
					alert(FD_MESS.FD_INCORRECT_EXT);
					return false;
				}
			}
		}

		//3. CHECK: If such file already exists
		for (p in arFDFiles[path])
		{
			if (arFDFiles[path][p].name == fileName)
			{
				if (!confirm(FD_MESS.FD_LOAD_EXIST_CONFIRM))
					return false;
				this.pFrameDoc.getElementById('__bx_fd_rewrite').value = 'Y';
			}
		}

		//4. Set file name in hidden input
		this.pFrameDoc.getElementById('__bx_fd_upload_fname').value = fileName;
		//5. Set path in hidden input
		this.pFrameDoc.getElementById('__bx_fd_upload_path').value = path;
		this.pFrameDoc.getElementById('__bx_fd_server_site').value = getSite();

		oWaitWindow.Show();
	},

	OnAfterUpload: function(fileName, bClose)
	{
		oWaitWindow.Hide();
		oBXDialogControls.filePath.Set(fileName);
		if (bClose)
		{
			oBXDialogControls.filePath.Set(fileName);
			oBXFileDialog.SubmitFileDialog();
		}
		else
		{
			oBXDialogWindow.LoadFolderContent(window.oBXDialogControls.dirPath.Get(), true);
		}
	}
};

function __History()
{
	__History.prototype.Init = function()
	{
		this.arHistoryPath = [];
		this.currentPos = -1;
	};

	__History.prototype.Push = function(sValue)
	{
		var len = this.arHistoryPath.length;

		this.currentPos++;

		if (len == 0 || (this.arHistoryPath.length > this.currentPos-1 && this.arHistoryPath[this.currentPos-1] != sValue))
		{
			this.arHistoryPath[this.currentPos] = sValue;
			if (len > 0)
				this.ButBackDisable(false);
		}
		else
			this.currentPos--;
	};

	__History.prototype.RemoveLast = function()
	{
		this.arHistoryPath.splice(2,1);
		this.currentPos--;
		if (this.currentPos == this.arHistoryPath.length-1)
			this.ButForwardDisable(true);
	};

	__History.prototype.Back = function()
	{
		if (this.currentPos <= 0 || !this.CheckButBack())
			return false;

		this.currentPos--;

		var newPath = this.arHistoryPath[this.currentPos];

		if (newPath)
		{
			if (this.currentPos == 0)
				this.ButBackDisable(true);


			this.ButForwardDisable(false);
			return newPath;
		}
		return false;
	};

	__History.prototype.Forward = function()
	{
		var len = this.arHistoryPath.length;
		if (!this.CheckButForward() || (this.currentPos > len-2))
			return false;
		this.currentPos++;

		var newPath = this.arHistoryPath[this.currentPos];
		if (newPath)
		{
			if (this.currentPos == len-1)
				this.ButForwardDisable(true);

			this.ButBackDisable(false);
			return newPath;
		}
		return false;
	};

	__History.prototype.CheckButBack = function()
	{
		return (oBXDialogControls.dirPath.butBack.getAttribute("__bx_disable") != 'Y')
	};

	__History.prototype.ButBackDisable = function(bDisable)
	{
		if (bDisable)
		{
			oBXDialogControls.dirPath.butBack.setAttribute("__bx_disable",'Y');
			oBXDialogControls.dirPath.butBack.className = "fd_iconkit path_back_dis";
		}
		else
		{
			oBXDialogControls.dirPath.butBack.setAttribute("__bx_disable",'N');
			oBXDialogControls.dirPath.butBack.className = "fd_iconkit path_back";
		}
	};

	__History.prototype.CheckButForward = function()
	{
		return (oBXDialogControls.dirPath.butForward.getAttribute("__bx_disable") != 'Y')
	};

	__History.prototype.ButForwardDisable = function(bDisable)
	{
		if (bDisable)
		{
			oBXDialogControls.dirPath.butForward.setAttribute("__bx_disable",'Y');
			oBXDialogControls.dirPath.butForward.className = "fd_iconkit path_forward_dis";
		}
		else
		{
			oBXDialogControls.dirPath.butForward.setAttribute("__bx_disable",'N');
			oBXDialogControls.dirPath.butForward.className = "fd_iconkit path_forward";
		}
	};

	this.Init();
}

function SubmitFileDialog()
{
	var
		filename = oBXDialogControls.filePath.Get(),
		path = oBXDialogControls.dirPath.Get(),
		site = getSite();

	if (filename == '' && !oBXFileDialog.bSelectDirs)
		return alert(FD_MESS.FD_EMPTY_FILENAME);

	if (oBXFileDialog.oConfig.operation == 'S' && !oBXFileDialog.bSelectDirs && filename)
	{
		var
			clearName = filename,
			ext = '';

		if (filename.indexOf('.') !== -1)
		{
			clearName = filename.substr(0, filename.indexOf('.'));
			ext = filename.substr(filename.indexOf('.'));
		}

		if (ext == '' && oBXDialogControls.Filter)
		{
			if (oBXDialogControls.Filter.arFilters && oBXDialogControls.Filter.arFilters.length > 0)
			{
				var filter = oBXDialogControls.Filter.arFilters[oBXDialogControls.Filter.curentFilter];
				if (filter != '*')
				{
					if (typeof filter == 'object')
						filter = filter[0];
					filename = clearName + '.' + filter.toLowerCase();
				}
			}
		}
	}

	if (oBXFileDialog.oConfig.operation == 'O')
	{
		window[oBXFileDialog.oConfig.submitFuncName](filename, path, site);
	}
	else if (oBXFileDialog.oConfig.operation == 'S')
	{
		var
			title,
			menuObj = {type : false};

		if (oBXFileDialog.oConfig.showAddToMenuTab)
		{
			title = oBXDialogControls.PageTitle.Get();
			var add2MenuCheck = BX("__bx_fd_add_to_menu");
			if (add2MenuCheck.checked)
			{
				menuObj = {};
				menuObj.type = BX("__bx_fd_menutype").value;
				if (BX("__bx_fd_itemtype_n").checked)
				{
					menuObj.menu_add_new = true;
					menuObj.menu_add_name = BX("__bx_fd_newp").value;
					menuObj.menu_add_pos = BX("__bx_fd_newppos").value;

					if (menuObj.menu_add_name == '')
					{
						alert(FD_MESS.FD_INPUT_NEW_PUNKT_NAME);
						return;
					}
				}
				else
				{
					menuObj.menu_add_new = false;
					menuObj.menu_add_pos = BX("__bx_fd_menuitem").value;
				}
			}
		}
		window[oBXFileDialog.oConfig.submitFuncName](filename, path, site, title, menuObj);
	}
	oBXFileDialog.Close();
}


function BXFDIsImage(fileName)
{
	return BXFDIsUserExt(fileName, ['gif','jpg','jpeg','png','jpe','bmp']);
}

function _IsPHP(fileName)
{
	return BXFDIsUserExt(fileName,['php']);
}

function BXFDIsUserExt(fileName, arExt)
{
	var
		ext = getExtension(fileName),
		len = arExt.length, i;

	for (i = 0; i < len; i++)
		if (arExt[i] == ext)
			return true;

	return false;
}

function _ReplaceSpaceByNbsp(str)
{
	if (typeof(str)!='string')
		return str;
	str = str.replace(/\s/g, '&nbsp;');
	return str;
}

function _ReplaceNbspBySpace(str)
{
	if (typeof(str)!='string')
		return str;
	str = str.replace(/&nbsp;/g, ' ');
	return str;
}

function _Show_tab_OPEN()
{
	try{
		BX("__bx_fd_preview_and_panel").style.display = "block";
		BX("__bx_fd_load").style.display = "none";
		BX("__bx_fd_container_add2menu").style.display = "none";
	}catch(e){}
}

function _Show_tab_LOAD()
{
	try{
		BX("__bx_fd_preview_and_panel").style.display = "none";
		BX("__bx_fd_load").style.display = "block";
		BX("__bx_fd_container_add2menu").style.display = "none";
	}catch(e){}
}

function _Show_tab_SAVE()
{
	try{
		BX("__bx_fd_top_controls_container").style.display = "block";
		BX("__bx_fd_tree_and_window").style.display = "block";
		BX("__bx_fd_preview_and_panel").style.display = "block";
		BX("__bx_fd_load").style.display = "none";
		BX("__bx_fd_container_add2menu").style.display = "none";
	}catch(e){}
}

function _Show_tab_MENU()
{
	try{
		if (!oBXMenuHandling.Add2MenuCheckbox.checked)
		{
			oBXMenuHandling.Add2MenuCheckbox.checked = true;
			oBXMenuHandling.Add2MenuCheckbox.onclick();
		}

		BX("__bx_fd_top_controls_container").style.display = "none";
		BX("__bx_fd_tree_and_window").style.display = "none";
		BX("__bx_fd_preview_and_panel").style.display = "none";
		BX("__bx_fd_load").style.display = "none";
		BX("__bx_fd_container_add2menu").style.display = "block";
		BX("__bx_fd_file_name").innerHTML = oBXDialogControls.filePath.Get();
	}catch(e){}
}


function BXMenuHandling()
{
	var _this = this;
	this.Add2MenuCheckbox = BX("__bx_fd_add_to_menu");
	this.Add2MenuCheckbox.onclick = function(e)
	{
		//oBXDialogTabs.DisableTab('tab2', !this.checked);
		oBXMenuHandling.Show(this.checked);
	};
	this.MenuTypeSelect = BX("__bx_fd_menutype");
	this.MenuTypeSelect.onchange = function()
	{
		oBXMenuHandling.ChangeMenuType();
	};

	this.NewItemOpt = BX("__bx_fd_itemtype_n");
	this.ExsItemOpt = BX("__bx_fd_itemtype_e");

	var optCheck = function()
	{
		if (_this.NewItemOpt.checked)
		{
			_this._displayRow("__bx_fd_e1",true);
			_this._displayRow("__bx_fd_e2",true);
			_this._displayRow("__bx_fd_e3",false);
		}
		else
		{
			_this._displayRow("__bx_fd_e1",false);
			_this._displayRow("__bx_fd_e2",false);
			_this._displayRow("__bx_fd_e3",true);
		}
	};
	this.NewItemOpt.onclick = this.ExsItemOpt.onclick = optCheck;

	BXMenuHandling.prototype.Show = function(bShow)
	{
		if (bShow)
			BX("add2menuTable").style.display = "block";
		else
			BX("add2menuTable").style.display = "none";
	};
	//#################################################################################

	BXMenuHandling.prototype.ChangeMenuType = function()
	{
		var path = oBXDialogControls.dirPath.Get();
		if (!window.arFDMenuTypes[path])
			return;

		var
			arTypes = arFDMenuTypes[path].types,
			arItems = arFDMenuTypes[path].items,
			cur = this.MenuTypeSelect.value, i;

		for(i = 0; i < arTypes.length; i++)
		{
			if (cur == arTypes[i])
				break;
		}
		var itms = arItems[i];

		if (itms.length == 0)
		{
			this.NewItemOpt.checked = true;
			this.ExsItemOpt.disabled = "disabled";
			this._displayRow("__bx_fd_e1",true);
			this._displayRow("__bx_fd_e2",false);
			this._displayRow("__bx_fd_e3",false);
		}
		else if (this.NewItemOpt.checked)
		{
			this.ExsItemOpt.disabled = false;
			this._displayRow("__bx_fd_e1",true);
			this._displayRow("__bx_fd_e2",true);
			this._displayRow("__bx_fd_e3",false);
		}

		var list = BX("__bx_fd_menuitem");
		list.options.length = 0;

		for(i = 0; i < itms.length; i++)
			list.options.add(new Option(itms[i], i+1, false, false));

		list = BX("__bx_fd_newppos");
		list.options.length = 0;

		for(i=0; i<itms.length; i++)
			list.options.add(new Option(itms[i], i+1, false, false));

		list.options.add(new Option(FD_MESS.FD_LAST_POINT, 0, true, true));
	};

	BXMenuHandling.prototype._displayRow = function(rowId,bDisplay)
	{
		var row = BX(rowId);
		if (bDisplay)
		{
			try{row.style.display = 'table-row';}
			catch(e){row.style.display = 'block';}
		}
		else
		{
			row.style.display = 'none';
		}
	};
	//##################################################
}

function BXWaitWindow(){}
BXWaitWindow.prototype =
{
	Show: function()
	{
		if (!this.oDiv)
		{
			var fd = BX("BX_file_dialog");
			if (!fd)
				return;
			this.oDiv = document.createElement("DIV");
			this.oDiv.id = "__bx_wait_window";
			this.oDiv.className = "waitwindow";
			this.oDiv.style.position = "absolute";
			this.oDiv.innerHTML = FD_MESS.FD_LOADIND;//"Loading...";
			this.oDiv.style.zIndex = "3000";
			this.oDiv.width = "150px";
			this.oDiv.style.left = '320px';
			this.oDiv.style.top = '200px';
			fd.appendChild(this.oDiv);
		}

		this.oDiv.style.display = "block";
	},
	Hide: function()
	{
		if (!this.oDiv)
			this.oDiv = BX("__bx_wait_window");
		if (this.oDiv)
			this.oDiv.style.display = "none";
	}
};


//*********************************** CONTEXT MENU ********************************************//
function BXFDContextMenu() {this.Init()}

BXFDContextMenu.prototype =
{
	Init: function()
	{
		this.oDiv = document.body.appendChild(jsUtils.CreateElement('DIV', {id: '__BXFDContextMenu'}));
		this.oDiv.innerHTML = '<table cellpadding="0" cellspacing="0"><tr><td class="popupmenu"><table id="__BXFDContextMenu_items" cellpadding="0" cellspacing="0"><tr><td></td></tr></table></td></tr></table>';
		// Part of logic of JCFloatDiv.Show()   Prevent bogus rerendering window in IE...
		//document.body.appendChild(jsUtils.CreateElement('IFRAME', {id: '__BXFDContextMenu_frame', src: "javascript:''", className: 'bxfd-sys-frame'}));
		this.menu = new PopupMenu('__BXFDContextMenu');
	},

	Show : function(zIndex, dxShadow, oPos, arItems)
	{
		if (!arItems)
			return;
		this.menu.PopupHide();

		this.AddItems(arItems);
		if (!isNaN(zIndex))
			this.oDiv.style.zIndex = zIndex;
		if (!isNaN(dxShadow))
			this.menu.dxShadow = dxShadow;

		oPos.right = oPos.left + this.oDiv.offsetWidth;
		oPos.bottom = oPos.top;
		this.menu.PopupShow(oPos);
	},

	AddItems : function(arMenuItems)
	{
		//Cleaning menu
		var tbl = BX(this.menu.menu_id+'_items');
		while(tbl.rows.length>0)
			tbl.deleteRow(0);

		//Creation menu elements
		var
			row, cell, i, elpar, oTable,
			n = arMenuItems.length;

		for(i=0; i<n; i++)
		{
			row = tbl.insertRow(-1);
			cell = row.insertCell(-1);
			if (arMenuItems[i] == 'separator')
			{
				cell.innerHTML =
					'<table cellpadding="0" cellspacing="0" border="0" class="popupseparator"><tr><td><div class="empty"></div></td></tr></table>';
			}
			else
			{
				elpar = arMenuItems[i];
				cell.innerHTML =
					'<table cellpadding="0" cellspacing="0" class="popupitem" onMouseOver="this.className=\'popupitem popupitemover\';" onMouseOut="this.className=\'popupitem\';" __bx_i="'+i+'">\n'+
					'	<tr>\n'+
					'		<td class="gutter"><div style="background-image:url('+elpar.src+')"></div></td>\n'+
					'		<td class="item" title="'+((elpar.title) ? elpar.title : elpar.name)+'"'+'>'+elpar.name+'</td>\n'+
					'	</tr>\n'+
					'</table>';

				oTable = cell.firstChild;
				oTable.onclick = function(e)
				{
					arMenuItems[this.getAttribute('__bx_i')].handler();
					oBXDialogWindow.oCM.menu.PopupHide();
				};
				oTable.id=null;
			}
		}
		this.oDiv.style.width = tbl.parentNode.offsetWidth;
	}
};

function BXFDCleanNode(pNode)
{
	var c;
	while(c = pNode.lastChild)
		pNode.removeChild(c);
}


function BXFDCompareObj(obj1, obj2)
{
	for (var p in obj1)
		if (obj1[p] != obj2[p])
			return false;

	return true;
}

function BXFDCopyObj(obj)
{
	var newObj = {}, p;
	for (p in obj)
		newObj[p] = obj[p];
	return newObj;
}

function getFileName(sPath)
{
	sPath = sPath.replace(/\\/ig,"/");
	return sPath.substr(sPath.lastIndexOf("/") + 1);
}

function getExtension(sName)
{
	var li = sName.lastIndexOf('.');
	if (li > 0)
		return li > 0 ? sName.substr(li + 1).toLowerCase() : '';
}

function getFileSize(size)
{
	if (size < 1024)
		return size+" "+FD_MESS.FD_BYTE;

	size = Math.round(size/1024);
	if (size < 1024)
		return size+" K"+FD_MESS.FD_BYTE;

	size = Math.round(size/1024);
	if (size < 1024)
		return size+" M"+FD_MESS.FD_BYTE;
}

function getSite()
{
	if (window.oBXDialogControls && window.oBXDialogControls.currentSite)
		return window.oBXDialogControls.currentSite;
	return BXSite;
}


