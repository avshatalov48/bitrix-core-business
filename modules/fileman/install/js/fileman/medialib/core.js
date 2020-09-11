function BXMediaLib(oConfig)
{
	this.oConfig = oConfig;
	window.MLItems = {};
	this.arItemsCollList = {};
	this.userSettings = this.oConfig.userSettings;
	this.arExt = this.oConfig.strExt.split(',');
}

BXMediaLib.prototype =
{
	Open: function(dublReq)
	{
		if (window.oBXMedialib && oBXMedialib.bOpened)
			return;

		var _this = this;
		this.sessid = this.oConfig.sessid;
		this.bOpened = true;
		this.width = this.userSettings.width;
		this.height = this.userSettings.height;
		this.zIndex = this.oConfig.zIndex || 2050;
		this.Overlay = new BXOverlay({id: 'bx_ml_trans_overlay'});
		this.bSubdialogOpened = false;
		this.arRedrawCollections = {};
		this.Types = this.oConfig.Types;
		this.curType = '';
		this.requestTypes = [];

		var i, l, arExt_, arExt, j, s;
		for (i = 0, l = this.Types.length; i < l; i++)
		{
			arExt = [];
			arExt_ = this.Types[i].ext.split(',');
			for (j = 0; j < arExt_.length; j++)
			{
				s = BX.util.trim(arExt_[j]);
				if (s.length > 0)
					arExt.push(s.toLowerCase());
			}
			this.Types[i].arExt = arExt;

			if (this.Types[i].system && this.Types[i].code == 'image')
				this.requestTypes.push(0);
			this.requestTypes.push(this.Types[i].id);
		}

		this.Request(
		{
			action: 'start',
			postData: {types: _this.requestTypes},
			handler: function(innerHTML)
			{
				if (!window.MLCollections)
					return false;

				if (innerHTML)
				{
					_this.Overlay.Show({zIndex: _this.zIndex - 10, clickCallback:{func:_this.Close, obj: _this}});
					_this.arCollections = window.MLCollections;
					_this.pWnd = document.body.appendChild(BX.create("DIV", {props:{id: 'bxmedialib', className: 'bxml-dialog'}}));
					var
						w = BX.GetWindowSize(),
						left = parseInt(w.scrollLeft + w.innerWidth / 2 - _this.width / 2),
						top = parseInt(w.scrollTop + w.innerHeight / 2 - _this.height / 2);

					if (!_this.bReadOnly)
					{
						_this.pDialogCont = document.body.appendChild(BX.create("DIV", {props:{className: 'bxml-subdialog-cont'}}));
						_this.pDialogCont.innerHTML = innerHTML.substring(innerHTML.indexOf('#ML_SUBDIALOGS_BEGIN#') + 21, innerHTML.indexOf('#ML_SUBDIALOGS_END#'));
						_this.pDialogCont.style.zIndex = _this.zIndex + 250;
					}

					_this.pWnd.style.zIndex = _this.zIndex;
					_this.pWnd.innerHTML = innerHTML.substring(innerHTML.indexOf('#ML_MAIN_DIALOG_BEGIN#') + 22, innerHTML.indexOf('#ML_MAIN_DIALOG_END#'));
					jsFloatDiv.Show(_this.pWnd, left, top, 5, false, false);

					_this.OnDialogOpen();
				}
			}
		});
	},

	Close: function()
	{
		jsFloatDiv.Close(this.pWnd);
		BX.unbind(document, "keypress", window.MlOnKeypress);
		if (this.pWnd.parentNode)
			this.pWnd.parentNode.removeChild(this.pWnd);

		if (this.pDialogCont.parentNode)
			this.pDialogCont.parentNode.removeChild(this.pDialogCont);

		this.Overlay.Remove();
		if (this.EditCollDialog)
			this.EditCollDialog.Overlay.Remove();
		if (this.EditItemDialog)
			this.EditItemDialog.Overlay.Remove();
		if (this.Confirm)
			this.Confirm.Overlay.Remove();

		oBXMedialib.bOpened = false;
	},

	OnDialogOpen: function()
	{
		var _this = this;

		// Get dialog elements
		this.pLeftCont = BX('ml_left_cont');
		this.pRightCont = BX('ml_right_cont');
		this.pFrameTbl = BX('ml_frame');

		this.pCollCont = BX('ml_coll_cont');

		this.pHeaderCont = BX('ml_head_cont');
		this.pListCont = BX('ml_list_cont');
		this.pButCont = BX('ml_but_cont');

		this.Search = new BXMLSearch(this);

		this.pInfo = {
			pWnd: BX('ml_info_wnd'),
			name: BX('ml_info_name'),
			desc: BX('ml_info_desc'),
			keywords: BX('ml_info_keys'),
			collections: BX('ml_info_colls'),
			details: BX('ml_info_details')
		};

		this.pBread = BX('ml_breadcrumbs');
		this.pResizer = BX('bxml_resizer');
		this.pResizer.onmousedown = function(){_this.ResizerMouseDown()};
		this.pResizer.ondrag = BX.False;

		this.pAddNewColl = BX('ml_add_collection');
		this.pAddNewItem = BX('ml_add_item');

		// Types
		this.InitTypeSelector();

		// Build collections
		this.BuildCollections();
		if (this.userSettings.coll_id > 0)
			this.SelectCollection(this.userSettings.coll_id, true);

		if (!this.bReadOnly)
		{
			this.pAddNewColl.onclick = function(e){_this.OpenEditCollDialog({bGetSelCol: true}); return BX.PreventDefault(e);};
			this.pAddNewColl.style.display = this.bCommonEdit ? 'inline' : 'none';

			this.pAddNewItem.onclick = function(e){_this.OpenEditItemDialog({bGetSelCol: true}); return BX.PreventDefault(e);};
			this.pAddNewItem.style.display = this.bCommonItemEdit ? 'inline' : 'none';
		}
		else
		{
			this.pAddNewColl.style.display = 'none';
			this.pAddNewItem.style.display = 'none';
		}

		BX('medialib_but_cancel').onclick = BX('bxml_close').onclick = function(){_this.Close();};
		this.pButSave = BX('medialib_but_save');
		this.pButSave.onclick = function(){_this.Submit();};

		if (this.bNoCollections)
		{
			BX.addClass(this.pLeftCont, 'ml-no-colls-sect');
			this.pAddNewItem.style.display = 'none';
		}

		window.MlOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27 && !_this.bSubdialogOpened)
				_this.Close();
		};
		BX.bind(document, "keypress", window.MlOnKeypress);

		this.FillInfoPanel(false);
		setTimeout(function(){_this.Resize(_this.width, _this.height);}, 50);
	},

	Submit: function()
	{
		if (!this.SelectedItemId || !this.oCurItems[this.SelectedItemId] || !this.oConfig.resType || !this.oConfig.arResultDest)
			return false;

		var
			oItem = this.oCurItems[this.SelectedItemId].oItem,
			resType = this.oConfig.resType,
			oRes = this.oConfig.arResultDest;

		if (resType == "FUNCTION" && typeof window[oRes.FUNCTION_NAME] == 'function')
		{
			window[oRes.FUNCTION_NAME](
				{
					src: oItem.path,
					name: bxspcharsback(oItem.name),
					description: bxspcharsback(oItem.desc),
					width: oItem.width,
					height: oItem.height,
					file_size: oItem.file_size,
					type: 'image'
				}
			);
		}
		else if(resType == "FORM" && document.forms[oRes.FORM_NAME] && document.forms[oRes.FORM_NAME][oRes.FORM_ELEMENT_NAME])
		{
			document.forms[oRes.FORM_NAME][oRes.FORM_ELEMENT_NAME].value = oItem.path;
			BX.fireEvent(document.forms[oRes.FORM_NAME][oRes.FORM_ELEMENT_NAME], 'change');
		}
		else if(resType == "ID" && BX(oRes.ELEMENT_ID))
		{
			BX(oRes.ELEMENT_ID).value = oItem.path;
			BX.fireEvent(BX(oRes.ELEMENT_ID), 'change');
			if(this.oConfig.description_id.length > 0 && BX(this.oConfig.description_id))
				BX(this.oConfig.description_id).value = oItem.name;
		}
		else
		{
			alert(ML_MESS.BadSubmit);
		}

		this.Close();
	},

	BuildCollections: function()
	{
		this.oCollections = {};
		this.arCollectionsTree = [];
		this.bCommonEdit = !!this.oConfig.rootAccess.edit || false;
		this.bCommonItemEdit = !!this.oConfig.rootAccess.edit_item || false;
		this.bNoCollections = true;

		var
			arCollectionsTemp = [],
			newAr, it = 0,
			i, l = this.arCollections.length;

		for (i = 0; i < l; i++)
		{
			if (!this.BuildCollection(this.arCollections[i], i))
				arCollectionsTemp.push([this.arCollections[i], i]);
		}

		while(arCollectionsTemp.length > 0 && it < 50)
		{
			l = arCollectionsTemp.length;
			newAr = [];
			for (i = 0; i < l; i++)
			{
				if (!this.BuildCollection(arCollectionsTemp[i][0], arCollectionsTemp[i][1]))
					newAr.push(arCollectionsTemp[i]);
			}
			arCollectionsTemp = newAr;
			it++;
		}

		this.bReadOnly = !this.bCommonEdit && !this.bCommonItemEdit;

		if (this.bNoCollections)
		{
			BX.addClass(this.pLeftCont, 'ml-no-colls-sect');
			this.pAddNewItem.style.display = 'none';
		}
	},

	BuildCollection: function(oCol, ind)
	{
		if (typeof oCol != 'object' || !this.CheckMLType(oCol.type))
			return true;

		if (this.bNoCollections)
		{
			this.bNoCollections = false;
			BX.removeClass(this.pLeftCont, 'ml-no-colls-sect');
			this.pAddNewItem.style.display = 'inline';
		}

		var pCont, level, _this = this, parAr;
		oCol.parent = parseInt(oCol.parent);

		if (!oCol.parent) // Root element
		{
			pCont = this.pCollCont;
			level = 0;
			parAr = this.arCollectionsTree;
		}
		else if (this.oCollections[oCol.parent])
		{
			pCont = this.oCollections[oCol.parent].pChildCont;
			level = this.oCollections[oCol.parent].level + 1;
			this.oCollections[oCol.parent].childCount++;

			if (this.oCollections[oCol.parent].childCount == 1)
				this.oCollections[oCol.parent].icon.className = 'ml-col-icon-closed';
			parAr = this._ReqFindChildCol(this.arCollectionsTree, oCol.parent);
		}
		else
			return false;

		parAr.push({id: oCol.id, child: []});
		this.arRedrawCollections[this.curType.id] = true;

		if (pCont)
		{
			var
				bDel = this.UserCan(oCol, 'del'),
				bEdit = this.UserCan(oCol, 'edit'),
				titleDiv = BX.create("DIV", {props:{id : 'ml_coll_title_' + oCol.id}}),
				img = titleDiv.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-col-icon'}})),
				span = titleDiv.appendChild(BX.create("SPAN", {props: {title: bxspcharsback(oCol.desc || oCol.name)}, text: oCol.name})),
				childDiv = BX.create("DIV", {props:{className: 'ml-coll-child-cont'}});

			if (bDel)
			{
				var del = titleDiv.appendChild(BX.create("IMG", {props: {src: '/bitrix/images/1.gif', className: 'ml-col-del', title: ML_MESS.DelCollection}}));
				del.onclick = function(e)
				{
					_this.DelCollection(this.parentNode.id.substr('ml_coll_title_'.length));
					return BX.PreventDefault(e || window.event);
				};
			}

			if (bEdit)
			{
				var edit = titleDiv.appendChild(BX.create("IMG", {props: {src: '/bitrix/images/1.gif', className: 'ml-col-edit', title: ML_MESS.EditCollection}}));
				edit.onclick = function(e)
				{
					var id = this.parentNode.id.substr('ml_coll_title_'.length);
					_this.OpenEditCollDialog({id: id});
					return BX.PreventDefault(e || window.event);
				};
				this.bCommonEdit = true;
			}

			if (!this.bCommonItemEdit && this.UserCan(oCol, 'new_item'))
				this.bCommonItemEdit = true;

			if (bDel || bEdit)
			{
				titleDiv.onmouseover = function(){BX.addClass(this, 'mlcollt-over');}
				titleDiv.onmouseout = function(){BX.removeClass(this, 'mlcollt-over');}
			}
			_this._SetColTitleLevel(titleDiv, level);

			titleDiv.title = bxspcharsback(oCol.desc || oCol.name);
			titleDiv.onclick = function(){_this.SelectCollection(this.id.substr('ml_coll_title_'.length));};

			img.onclick = function(e)
			{
				var id = this.parentNode.id.substr('ml_coll_title_'.length);
				_this.OpenCollection(id);
				return BX.PreventDefault(e || window.event);
			};

			pCont.appendChild(titleDiv);
			pCont.appendChild(childDiv);

			this.oCollections[oCol.id] =
			{
				ind: ind,
				pTitle: titleDiv,
				pChildCont: childDiv,
				icon: img,
				level: level,
				childCount: 0,
				bOpened: false
			};
			return true;
		}
	},

	ReNewCollectionTree: function()
	{
		this.arRedrawCollections[this.curType.id] = true;

		this.arCollectionsTree = [];

		var
			arTMP = [], newAr, it = 0,
			i, l = this.arCollections.length;

		for (i = 0; i < l; i++)
		{
			if (typeof this.arCollections[i] != 'object' || !this.CheckMLType(this.arCollections[i].type))
				continue;

			if (!this.ReNewCol4Tree(this.arCollections[i]))
				arTMP.push(this.arCollections[i]);
		}

		while(arTMP.length > 0 && it < 50)
		{
			l = arTMP.length;
			newAr = [];
			for (i = 0; i < l; i++)
			{
				if (!this.ReNewCol4Tree(arTMP[i]))
					newAr.push(arTMP[i]);
			}
			arTMP = newAr;
			it++;
		}
	},

	ReNewCol4Tree: function(oCol)
	{
		var
			parId = parseInt(oCol.parent),
			parAr;

		if (!parId) // Root element
			parAr = this.arCollectionsTree;
		else if (this.oCollections[parId])
			parAr = this._ReqFindChildCol(this.arCollectionsTree, parId);

		if(!parAr)
			return false;
		parAr.push({id: oCol.id, child: []});
		return true;
	},

	SelectCollection: function(id, bOpenCrumbs)
	{
		var Col = this.oCollections[id];

		if (!Col || this.SelectedColId == id)
			return;

		this.DeSelectCollection(false);

		this.SelectedColId = id;
		BX.addClass(Col.pTitle, 'mlcollt-active');

		var arCrumbs = this.GetCollsCrumbs(id);
		this.BuildCrumbs(arCrumbs);

		if (bOpenCrumbs) // Open collection sections
		{
			var i, l = arCrumbs.length;
			for(i = 1; i < l; i++)
			{
				if (!this.oCollections[arCrumbs[i].id].bOpened)
					this.OpenCollection(arCrumbs[i].id);
			}
		}

		this.userSettings.coll_id = id;
		this.ShowItems(id);
		this.SelectItem();

		this.SaveSettings();
	},

	DeSelectCollection: function(bDelCrumbs)
	{
		if (this.SelectedColId && this.oCollections[this.SelectedColId]) // Deselect
			BX.removeClass(this.oCollections[this.SelectedColId].pTitle, 'mlcollt-active');

		if (bDelCrumbs !== false) // Clean BreadCrumbs
			this.BuildCrumbs([]);
	},

	UserCan: function(oCol, action)
	{
		var oAc;
		if (typeof oCol !== 'object')
		{
			if (oCol === 0)
			{
				oAc = this.oConfig.rootAccess;
			}
			else
			{
				oCol = this.GetCollection(oCol);
				if (typeof oCol !== 'object' )
					return false;
				oAc = oCol.access;
			}
		}
		else
		{
			oAc = oCol.access;
		}
		return oAc && oAc[action] === '1';
	},

	GetCollsCrumbs: function(id)
	{
		var arCrumbs = [], arCol;
		while(id)
		{
			arCol = this.GetCollection(id);
			if (arCol)
			{
				arCrumbs.push(arCol);
				id = arCol.parent;
			}
			else
				id = false;
		}
		return arCrumbs;
	},

	BuildCrumbs: function(arCrumbs)
	{
		// Clean
		while(this.pBread.childNodes.length > 0)
			this.pBread.removeChild(this.pBread.firstChild);

		var
			_this = this,
			coll,
			i, l = arCrumbs.length;

		for(i = l - 1; i >= 0; i--)
		{
			coll = arCrumbs[i];
			if (!coll || typeof coll != 'object')
				continue;

			pCr = this.pBread.appendChild(BX.create("DIV", {props: {className: 'ml-crumb', id : 'ml_crumb_' + coll.id, title: coll.desc}, text: coll.name}));

			if (i > 0)
			{
				// Add separator
				this.pBread.appendChild(BX.create("DIV", {props:{className: 'ml-crumb-sep'}})).appendChild(document.createTextNode(' '));
				pCr.onclick = function(){_this.SelectCollection(this.id.substr('ml_crumb_'.length));};
			}
			else
			{
				pCr.style.cursor = 'default';
			}
		}

		return arCrumbs;
	},

	GetCollection: function(id)
	{
		if (this.oCollections[id])
			return this.arCollections[this.oCollections[id].ind];

		// For collections from other types
		var i, l = this.arCollections.length;
		for (i = 0; i < l; i++)
			if (this.arCollections[i].id == id)
				return this.arCollections[i];

		return false;
	},

	_ReqFindChildCol: function(arr, id)
	{
		var i, l = arr.length, res = false;

		for (i = 0; i < l; i++)
		{
			if (arr[i].id == id)
			{
				res = arr[i].child;
				break;
			}
			else if (arr[i].child.length > 0)
			{
				res = this._ReqFindChildCol(arr[i].child, id);
				if (res)
					break;
			}
		}

		return res;
	},

	_ReqBuildCollSelect: function(oSel, arr, level, bClean)
	{
		if (!level)
			level = 0;

		var i, l = arr.length, l1 = oSel.options.length, j, html;
		if (bClean == true)
		{
			var ind = 1;
			while (oSel.options[ind])
				oSel.options[ind] = null;
		}

		for (i = 0; i < l; i++)
		{
			col = this.GetCollection(arr[i].id);
			if (col)
			{
				html = '';
				for (j = 0; j < level; j++)
					html += ' . ';

				html += bxspcharsback(col.name);
				opt = new Option(html, arr[i].id);
				opt.title = bxspcharsback(col.name);
				oSel.options.add(opt);

				if (arr[i].child.length > 0)
					this._ReqBuildCollSelect(oSel, arr[i].child, level + 1);
			}
		}
	},

	OpenCollection: function(id)
	{
		var Col = this.oCollections[id];
		if (Col.childCount > 0)
		{
			if (!Col.bOpened)
			{
				Col.pChildCont.style.display = 'block';
				Col.icon.className = 'ml-col-icon ml-col-icon-opened';
			}
			else
			{
				Col.pChildCont.style.display = 'none';
				Col.icon.className = 'ml-col-icon ml-col-icon-closed';
			}
			Col.bOpened = !Col.bOpened;
		}
	},

	DelCollection: function(id)
	{
		if (id > 0 && confirm(ML_MESS.DelCollectionConf))
		{
			var childs = [];
			if (this.oCollections[id].childCount > 0)
			{
				var
					arDivs = this.oCollections[id].pChildCont.getElementsByTagName('DIV'),
					i, l = arDivs.length, chId;

				for (i = 0; i < l; i++)
				{
					if (arDivs[i].id.substr(0, 14) == 'ml_coll_title_')
					{
						chId = parseInt(arDivs[i].id.substr(14));
						if (chId > 0)
							childs.push(chId);
					}
				}
			}

			var _this = this;
			this.Request({
				action: 'del_collection',
				postData: {id: id, child_cols: childs},
				handler: function()
				{
					if (window.bx_req_res)
						_this.CSDelCollection(id, childs);
				}
			});
		}
	},

	_IncreaseCollChild: function(id, i)
	{
		var Col = this.oCollections[id];
		if (Col)
		{
			if (i !== -1) // Increase
			{
				Col.childCount++;
				if (Col.childCount > 0)
				{
					Col.icon.className = 'ml-col-icon ' + (Col.bOpened ? 'ml-col-icon-opened' : 'ml-col-icon-closed');
					if (Col.bOpened)
						Col.pChildCont.style.display = 'block';
				}
			}
			else
			{
				Col.childCount--;
				if (Col.childCount <= 0)
				{
					Col.icon.className = 'ml-col-icon';
					Col.pChildCont.style.display = 'none';
				}
			}
		}
	},

	_SetColTitleLevel: function(pTitle, level)
	{
		pTitle.className = 'ml-coll-title mlcolllevel-' + (level > 3 ? 3 : level);
		pTitle.childNodes[0].style.marginLeft = (3 + level * 8) + 'px'; // Level padding
		if (level >= 3)
			pTitle.childNodes[1].className = 'ml-smaller-title';
	},

	SaveCollection: function()
	{
		var
			D = this.EditCollDialog,
			_this = this,
			postData =
			{
				name: encodeURIComponent(D.pName.value),
				desc: encodeURIComponent(D.pDesc.value),
				keywords: encodeURIComponent(D.pKeys.value),
				parent: D.pParent.value,
				type: D.typeId
			};

		// 1. Check name
		if(D.pName.value == '')
		{
			alert(ML_MESS.ColNameError);
			D.pName.focus();
			return false;
		}

		if (!D.bNew)
			postData.id = D.oCol.id;

		this.Request({
			action: 'edit_collection',
			postData: postData,
			handler: function()
			{
				if (window.bx_req_res !== false)
				{
					_this.CloseEditCollDialog();

					var oCol =
					{
						id: window.bx_req_res.id,
						name: D.pName.value,
						desc: D.pDesc.value,
						date: '',
						keywords: D.pKeys.value,
						parent: postData.parent,
						access: window.bx_req_res.access,
						type: D.typeId
					};

					// Cliend Side
					if (D.bNew)
					{
                        _this.bNoCollections = true;
						_this.arCollections.push(oCol);
						_this.BuildCollection(oCol, _this.arCollections.length - 1);
					}
					else
					{
						var
							pTitle = _this.oCollections[oCol.id].pTitle,
							pChildCont = _this.oCollections[oCol.id].pChildCont,
							oldParent = _this.arCollections[_this.oCollections[oCol.id].ind].parent,
							newParent = oCol.parent || 0;

						if (_this.arCollections[_this.oCollections[oCol.id].ind].parent != newParent) // Move
						{
							_this._IncreaseCollChild(oldParent, -1);
							_this._IncreaseCollChild(newParent);

							var pCont = newParent == 0 ? _this.pCollCont : _this.oCollections[newParent].pChildCont;

							pCont.appendChild(pTitle);
							pCont.appendChild(pChildCont);

							var level = newParent == 0 ? 0 : _this.oCollections[newParent].level + 1; // Level padding
							_this.oCollections[oCol.id].level = level;

							_this._SetColTitleLevel(pTitle, level);
						}

						_this.arCollections[_this.oCollections[oCol.id].ind] = oCol;
						pTitle.childNodes[1].innerHTML = BX.util.htmlspecialchars(oCol.name);
						pTitle.title = oCol.desc || oCol.name;
					}

					_this.ReNewCollectionTree();
					_this.SelectCollection(oCol.id);
				}
				else
				{
					// TODO: Error message
					alert('error');
				}
			}
		});
	},

	OpenEditCollDialog: function(Params)
	{
		if (!Params)
			Params = {};

		if (!this.EditCollDialog)
			this.CreateEditCollDialog();

		this.EditCollDialog.bNew = !Params.id;
		var
			zIndex = Params.zIndex || this.zIndex + 200,
			D = this.EditCollDialog,
			w = BX.GetWindowSize(),
			left = parseInt(w.scrollLeft + w.innerWidth / 2 - D.width / 2),
			top = parseInt(w.scrollTop + w.innerHeight / 2 - D.height / 2);

		if (this.arRedrawCollections[this.curType.id])
		{
			this._ReqBuildCollSelect(D.pParent, this.arCollectionsTree, 0, true);
			this.arRedrawCollections[this.curType.id] = false;
		}

		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;
		D.pWnd.style.display = 'block';

		this.bSubdialogOpened = true;
		this.EditCollDialog.bFocusKeywords = false;

		if (!D.bNew)
		{
			var oCol = this.GetCollection(Params.id);
			D.pName.value = bxspcharsback(oCol.name);
			D.pDesc.value = bxspcharsback(oCol.desc);
			D.pKeys.value = bxspcharsback(oCol.keywords);
			D.pParent.value = oCol.parent || 0;
			this.EditCollDialog.oCol = oCol;
		}
		else
		{
			D.pName.value = '';
			D.pDesc.value = '';
			D.pKeys.value = '';

			D.pParent.value = 0;
			this._SetFirstAvailableCol();

			if (!Params.parentCol && Params.bGetSelCol && this.SelectedColId && this.oCollections[this.SelectedColId])
				Params.parentCol = this.SelectedColId;

			if (Params.parentCol > 0 && this.UserCan(Params.parentCol, 'new_col'))
				D.pParent.value = Params.parentCol;

			var oCol = this.GetCollection(Params.parentCol);
			if (oCol && oCol.keywords)
				D.pKeys.value = oCol.keywords;
		}

		D.typeId = this.curType.id || ''; // Set ML Type
		D.pName.onchange(); // Set title

		jsFloatDiv.Show(this.EditCollDialog.pWnd, left, top, 5, false, false);
		BX.bind(document, "keypress", window.MlEdColOnKeypress);
	},

	CreateEditCollDialog: function(Params)
	{
		var
			_this = this,
			D = {
				width: 360,
				height: 230,
				pWnd: BX('mlsd_coll'),
				pTitle: BX('mlsd_coll_title'),
				pName: BX('mlsd_coll_name'),
				pDesc: BX('mlsd_coll_desc'),
				pKeys: BX('mlsd_coll_keywords'),
				pParent: BX('mlsd_coll_parent'),
				Overlay: new BXOverlay({id: 'bxml_ed_col_overlay'})
			};

		D.pName.onkeydown = D.pName.onchange = function()
		{
			setTimeout(
			function(){
				var
					D = _this.EditCollDialog,
					val = bxhtmlspecialchars(D.pName.value),
					t1 = D.bNew ? ML_MESS.NewCollection : ML_MESS.Collection;

				D.pTitle.title = t1 + (val.length > 0 ? ': ' + D.pName.value : '');
				D.pTitle.innerHTML = t1 + (val.length > 0 ? ': ' + val : '');
			}, 20);
		};
		D.pKeys.onchange = D.pKeys.onblur = function() {_this.EditCollDialog.bFocusKeywords = true;}

		D.pParent.onchange = function()
		{
			if (!_this.EditCollDialog.bNew && this.value == _this.EditCollDialog.oCol.parent)
				return true;

			if (_this.EditCollDialog.bNew && !_this.UserCan(parseInt(this.value), 'new_col') ||
			!_this.EditCollDialog.bNew && !_this.UserCan(parseInt(this.value), 'edit'))
			{
				_this._SetFirstAvailableCol();
				return alert(ML_MESS.CollAccessDenied3);
			}

			if (_this.EditCollDialog.oCol)
			{
				var arCol = _this._ReqFindChildCol(_this.arCollectionsTree, _this.EditCollDialog.oCol.id); // Put parent into child
				if (!arCol || _this._ReqFindChildCol(arCol, this.value))
				{
					alert(ML_MESS.ColLocEr2);
					this.value = _this.EditCollDialog.oCol.parent || 0;
					return true;
				}
			}

			if (!_this.EditCollDialog.bNew && _this.EditCollDialog.oCol.id == this.value)
			{
				alert(ML_MESS.ColLocEr);
				this.value = _this.EditCollDialog.oCol.parent || 0;
			}

			if (_this.EditCollDialog.bNew && !_this.EditCollDialog.bFocusKeywords && this.value > 0)
			{
				var oCol = _this.GetCollection(this.value);
				if (oCol && oCol.keywords)
					D.pKeys.value = oCol.keywords;
			}
		};

		BX('mlsd_coll_save').onclick = function(){_this.SaveCollection();};
		BX('mlsd_coll_cancel').onclick = function(){_this.CloseEditCollDialog();};
		BX('mlsd_coll_close').onclick = function(){_this.CloseEditCollDialog();};
		this.arRedrawCollections[this.curType.id] = true;

		window.MlEdColOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27)
				_this.CloseEditCollDialog();
		};

		D.pWnd.style.width = D.width + 'px';
		D.pWnd.style.height = 'auto';
		D.pWnd.style.minHeight = '10px';
		this.EditCollDialog = D;
	},

	CloseEditCollDialog: function()
	{
		this.EditCollDialog.pWnd.style.display = 'none';
		jsFloatDiv.Close(this.EditCollDialog.pWnd);
		this.EditCollDialog.Overlay.Hide();
		this.bSubdialogOpened = false;
		BX.unbind(document, "keypress", window.MlEdColOnKeypress);
	},

	_SetFirstAvailableCol: function(act)
	{
		var
			D = this.EditCollDialog,
			act = D.bNew ? 'new_col' : 'edit',
			cid, col, i, l = D.pParent.options.length;

		if (!D.bNew && D.oCol.parent)
			D.pParent.value = D.oCol.parent;

		if (this.oConfig.rootAccess[act])
			D.pParent.value = 0;
		else
		{
			for (i = 0; i < l; i++)
			{
				cid = D.pParent.options[i].value;
				col = this.GetCollection(cid);
				if (col && col.access && col.access[act])
				{
					D.pParent.value = cid;
					return;
				}
			}
		}
	},

	ShowItems: function(id)
	{
		var _this = this;
		if (this.currentIdShowed == id)
			return;
		var
			arCol = this.GetCollection(id),
			arAccess = {edit: this.UserCan(arCol, 'edit_item'), del: this.UserCan(arCol, 'del_item')};

		if (typeof MLItems[id] !== 'object')
		{
			this.Request({
				action: 'get_items',
				postData: {col_id: id},
				handler: function()
				{
					if (!window.MLItems[id])
						return false;

					_this.DisplayItems(MLItems[id], arAccess);
					this.currentIdShowed = id;
				}
			});
		}
		else // Display items
		{
			this.DisplayItems(MLItems[id], arAccess);
			this.currentIdShowed = id;
		}
	},

	DisplayItems: function(arItems, arAccess)
	{
		// Clean
		while(this.pListCont.childNodes[1])
			this.pListCont.removeChild(this.pListCont.lastChild);

		this.oCurItems = {};
		this.pListCont.firstChild.style.display = arItems && arItems.length ? 'none' : 'block'; // Show or hide 'no items' message

		if (arItems && arItems.length)
		{
			var i, l = arItems.length;
			for (i = 0; i < l; i++)
				this.DisplayItem(arItems[i], arAccess);
		}
	},

	DisplayItem: function(oItem, arAccess, bSearch)
	{
		if (!oItem || typeof oItem != 'object')
			return;

		var
			_this = this,
			w = this.oConfig.thumbWidth,
			h = this.oConfig.thumbHeight,
			itemDiv = BX.create("DIV", {props:{id : 'ml_item_' + oItem.id, className: 'ml-item-cont', title: bxspcharsback(oItem.name)}, style: {width: (w + 15) + 'px', height: (h + 35) + 'px'}}),
			tmbImg = itemDiv.appendChild(BX.create("IMG", {props: {src: oItem.thumb_path || '/bitrix/images/1.gif', className: 'ml-item-thumb'}})),
			titleDiv = itemDiv.appendChild(BX.create("DIV", {props:{className: 'ml-item-title'}, style: {width: (w + 8) + 'px'}}));

		var tmb_path = oItem.thumb_path || oItem.path;
		if (oItem.type == 'image' && tmb_path) // For small images
			tmbImg.style.backgroundImage = 'url(\'' + tmb_path + '\')';

		if (!oItem.thumb_path || !oItem.width || !oItem.height)
		{
			BX.addClass(tmbImg, 'ml-item-no-thumb');
			oItem.height = 100; // Bitrix thumb height
		}

		if(h > oItem.height)
		{
			var mt = Math.round((h - oItem.height) / 2);
			if (mt > 0)
			{
				tmbImg.style.marginTop = mt + 'px';
				tmbImg.style.marginBottom = mt + 'px';
			}
		}

		titleDiv.appendChild(document.createTextNode(bxspcharsback(oItem.name)));
		var butCont = itemDiv.appendChild(BX.create("DIV", {props:{className: 'ml-item-but-cont'}}));

		var view = butCont.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-item-view', title: ML_MESS.ViewItem}}));
		view.onclick = function(e)
		{
			var id = this.parentNode.parentNode.id.substr('ml_item_'.length);
			_this.GetItemCollList(id, "OpenViewItDialog", {id: id, Access: arAccess, bSearch: bSearch});
			return BX.PreventDefault(e || window.event);
		};

		if (arAccess.edit || arAccess.del)
		{
			if (arAccess.edit)
			{
				var edit = butCont.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-item-edit', title: ML_MESS.EditItem}}));
				edit.onclick = function(e)
				{
					var id = this.parentNode.parentNode.id.substr('ml_item_'.length);
					_this.GetItemCollList(id, "OpenEditItemDialog", {id: id});
					return BX.PreventDefault(e || window.event);
				};
			}

			if (arAccess.del)
			{
				var del = butCont.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-item-del', title: ML_MESS.DelItem}}));
				del.onclick = function(e)
				{
					var id = this.parentNode.parentNode.id.substr('ml_item_'.length);
					_this.GetItemCollList(id, "DelItem", {id: id});
					return BX.PreventDefault(e || window.event);
				};
			}

			itemDiv.onmouseover = function(){BX.addClass(this, 'ml-item-cont-over');};
			itemDiv.onmouseout = function(){BX.removeClass(this, 'ml-item-cont-over');};
		}
		itemDiv.onclick = function(e){_this.SelectItem(this.id.substr('ml_item_'.length));};
		itemDiv.ondblclick = function(e){_this.Submit();};

		this.oCurItems[oItem.id] = {oItem : oItem, pWnd: itemDiv};
		this.pListCont.appendChild(itemDiv);
	},

	OpenViewItDialog: function(Params)
	{
		if (!this.ViewItDialog)
			this.CreateViewItDialog(Params);

		var
			oItem, i, l,
			zIndex = 600,
			D = this.ViewItDialog;

		var oItem = this.oCurItems[Params.id].oItem;
		this.bSubdialogOpened = true;

		if (!oItem)
			return;

		D.oItem = oItem;
		D.colId = Params.colId;

		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;
		D.pDel.style.display = Params.Access.del ? 'inline' : 'none';
		D.pEdit.style.display = Params.Access.edit ? 'inline' : 'none';
		D.pWnd.style.display = "block";
		D.pWnd.style.visibility = "hidden";
		D.bOpened = true;

		this.SetItemInfo(oItem);
	},

	CreateViewItDialog: function(Params)
	{
		var
			_this = this,
			D = {
				width: 100,
				height: 100,
				pWnd: BX('mlsd_view_item'),
				pItemCont: BX('mlsd_item_cont'),
				pInfoCont: BX('mlsd_info_cont'),

				pName: BX('mlvi_info_name'),
				pCols: BX('mlvi_info_colls'),
				pKeys: BX('mlvi_info_keys'),
				pDesc: BX('mlvi_info_desc'),
				pDetails: BX('mlvi_info_details'),

				pDel: BX('mlsd_viewit_del'),
				pEdit: BX('mlsd_viewit_edit'),
				pLink: BX('mlvi_info_link'),
                pCopyLink: BX('mlvi_info_copy_link'),
                pCopyInput: BX('mlvi_info_copy_input'),
				pExt: BX('mlvi_info_ext'),

				Overlay: new BXOverlay({id: 'bxml_viewit_overlay'})
			};

		BX('mlsd_viewit_cancel').onclick = function(){_this.CloseViewItDialog();};
		BX('mlsd_viewit_close').onclick = function(){_this.CloseViewItDialog();};

		D.pDel.onclick = function(e){_this.DelItem({id: _this.ViewItDialog.oItem.id, colId: _this.ViewItDialog.colId});};
		D.pEdit.onclick = function(e)
		{
			_this.CloseViewItDialog();
			_this.OpenEditItemDialog({id: _this.ViewItDialog.oItem.id, colId: _this.ViewItDialog.colId, bSearch: Params.bSearch});
		};

		window.MlViewItOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27)
				_this.CloseViewItDialog();
		};
		this.ViewItDialog = D;
	},

	CloseViewItDialog: function()
	{
		this.ViewItDialog.bOpened = false;
		this.bSubdialogOpened = false;
		this.ViewItDialog.pWnd.style.display = 'none';
        this.ViewItDialog.pCopyInput.style.display = 'none';
		jsFloatDiv.Close(this.ViewItDialog.pWnd);
		this.ViewItDialog.Overlay.Hide();
		BX.unbind(document, "keypress", window.MlViewItOnKeypress);
	},

	SetItemInfo: function(oItem)
	{
		var
			_this = this,
			D = this.ViewItDialog,
			arCols = this.arItemsCollList[oItem.id],
			str = '', i, l = arCols.length, a, oCol;

		// Name
		D.pName.innerHTML = BX.util.htmlspecialchars(oItem.name);
		D.pName.title = oItem.name;

		// Link
		D.pLink.href = oItem.path;

        D.pCopyLink.onclick = function() {
            D.pCopyInput.value = oItem.path.substr(0,1) !== '/' ? oItem.path : window.location.protocol + '//' + window.location.host + oItem.path;
            D.pCopyInput.style.display = 'block';
            D.pCopyInput.select();
        };

		// Keywords
		if (oItem.keywords.length > 0)
		{
			D.pKeys.parentNode.className = 'small-grey';
			D.pKeys.innerHTML = BX.util.htmlspecialchars(oItem.keywords);
			D.pKeys.title = bxspcharsback(oItem.keywords);
		}
		else
		{
			D.pKeys.parentNode.className = 'ml-info-keys-h';
		}

		// Description
		if (oItem.desc.length > 0)
		{
			D.pDesc.innerHTML = BX.util.htmlspecialchars(oItem.desc.replace(/\n/g, '<br />'));
			D.pDesc.parentNode.className = 'small-grey';
		}
		else
		{
			D.pDesc.parentNode.className = 'ml-info-desc-h';
		}

		// Collections
		for (var j = D.pCols.childNodes.length - 1; j > 0; j--)
			if (D.pCols.childNodes[j].nodeName.toLowerCase() != 'span')
				D.pCols.removeChild(D.pCols.childNodes[j]);

		for (i = 0; i < l; i++)
			str += BX.util.htmlspecialcharsback(this.GetCollection(arCols[i]).name + (i != l - 1 ? ', ' : ''));

		D.pCols.appendChild(document.createTextNode(str));

		var Details = ML_MESS.FileExt + ': ' + oItem.path.substr(oItem.path.lastIndexOf('.') + 1);
		Details += '<br />' + ML_MESS.DateModified + ': ' + oItem.date_mod;
		if (oItem.file_size)
			Details += '<br />' + ML_MESS.FileSize + ': ' + oItem.file_size;
		if (oItem.width && oItem.height)
			Details += '<br />' + ML_MESS.ImageSize + ': ' + oItem.width + ' x ' + oItem.height + ' px';

		D.pDetails.innerHTML = Details;

		this.SetItemHTML(oItem);
	},

	SetItemHTML: function(oItem)
	{
		var D = this.ViewItDialog;

		this.Request({
			action: 'get_item_view',
			postData: {id: oItem.id},
			handler: function()
			{
				// Replace id, and increase "curCount"
				var html = window.bx_req_res.html;
				var code = [], start, end, i, cnt;
				while((start = html.indexOf('<' + 'script>')) != -1)
				{
					var end = html.indexOf('</' + 'script>', start);
					if(end == -1)
						break;
					code[code.length] = html.substr(start + 8, end - start - 8);
					html = html.substr(0, start) + html.substr(end + 9);
				}
				for(var i = 0, cnt = code.length; i < cnt; i++)
					if(code[i] != '')
						jsUtils.EvalGlobal(code[i]);

				D.pItemCont.innerHTML = html;

				var
					viewWidth = parseInt(window.bx_req_res.width) || 100,
					viewHeight = parseInt(window.bx_req_res.height) || 50;

				if (viewWidth < 100)
					viewWidth = 100;

				if (D.pDesc && parseInt(D.pDesc.offsetHeight) > (viewHeight - 200))
				{
					D.pDesc.style.height = (viewHeight >= 400 ? viewHeight - 200 : 200) + "px";
					D.pDesc.style.overflow = "auto";
				}

				var
					infoHeight = parseInt(D.pInfoCont.offsetHeight) || 0,
					dialogHeight = 80 + (viewHeight > infoHeight ? viewHeight : infoHeight),
					dialogWidth = 270 + viewWidth,
					w = BX.GetWindowSize(),
					left = parseInt(w.scrollLeft + w.innerWidth / 2 - dialogWidth / 2),
					top = parseInt(w.scrollTop + w.innerHeight / 2 - dialogHeight / 2);

				D.pWnd.style.width = dialogWidth + 'px';
				D.pWnd.style.height = dialogHeight + 'px';
				D.pWnd.style.overflow = 'hidden';

				jsFloatDiv.Show(D.pWnd, left, top, 5, false, false);
				BX.bind(document, "keypress", window.MlViewItOnKeypress);

				D.pItemCont.style.width = viewWidth + 'px';
				D.pItemCont.style.height = viewHeight + 'px';
				D.pWnd.style.visibility = 'visible';
			}
		});
	},

	DelItem: function(Params)
	{
		if (!Params.id)
			return;

		var
			_this = this,
			bDisAll = false,
			arCols = this.arItemsCollList[Params.id],
			i, l = arCols.length, oCol;

		for (i = 0; i < l; i++)
		{
			if (!this.GetCollection(arCols[i]))
			{
				bDisAll = true;
				break;
			}
		}

		if (!Params.mode)
			return this.OpenConfirm(
			{
				text: ML_MESS.DelItConfTxt,
				but1: {text: ML_MESS.DelItB1, handler: function(){_this.DelItem({id: Params.id, mode: 'current'})}},
				but2: {text: ML_MESS.DelItB2, handler: function(){_this.DelItem({id: Params.id, mode: 'all'})}, disabled: bDisAll}
			});

		var colId = this.SelectedColId || 0;
		this.Request({
			action: 'del_item',
			postData: {id: Params.id, mode: Params.mode, col_id: colId},
			handler: function()
			{
				if (window.bx_req_res == true)
					_this.CSDelItem({id: Params.id, mode: Params.mode, colId: colId});
				else if (window.bx_req_res !== false)
					return false;
			}
		});
	},

	SelectItem: function(id)
	{
		if (id && this.oCurItems && this.oCurItems[id])
		{
			if (this.SelectedItemId && this.oCurItems[this.SelectedItemId]) // Deselect
				BX.removeClass(this.oCurItems[this.SelectedItemId].pWnd, 'ml-item-active');

			this.SelectedItemId = id;
			BX.addClass(this.oCurItems[id].pWnd, 'ml-item-active'); // Mark item active

			this.GetItemCollList(id, "FillInfoPanel", this.oCurItems[id].oItem)	// Fill info panel in callback
		}
		else
		{
			this.SelectedItemId = false;
			this.FillInfoPanel(false);
		}
	},

	FillInfoPanel: function(oItem)
	{
		if (!oItem)
		{
			// Disable submit button
			if (this.pButSave)
				this.pButSave.disabled = true;
			BX.addClass(this.pInfo.pWnd, 'ml-no-info');
		}
		else
		{
			// Enable submit button
			if (this.pButSave)
				this.pButSave.disabled = false;

			var
				_this = this,
				arCols = this.arItemsCollList[oItem.id],
				str = '', i, l = arCols.length, a, oCol;

			BX.removeClass(this.pInfo.pWnd, 'ml-no-info');
			this.pInfo.name.innerHTML = BX.util.htmlspecialchars(oItem.name);

			// Keywords
			if (oItem.keywords.length > 0)
			{
				this.pInfo.keywords.parentNode.className = '';
				this._ChooseKeysCount(_this.pInfo.keywords, _this.pInfo.keywords.parentNode, oItem.keywords, 40);
				this.pInfo.keywords.title = bxspcharsback(oItem.keywords);
			}
			else
			{
				this.pInfo.keywords.parentNode.className = 'ml-info-keys-h';
			}

			// Description
			if (oItem.desc.length > 0)
			{
				BX.removeClass(this.pInfo.desc, 'mlid-scrld');
				this.pInfo.desc.innerHTML = BX.util.htmlspecialchars(oItem.desc.replace(/\n/g, '<br />'));
				setTimeout(function()
				{
					var dh = parseInt(_this.pInfo.desc.offsetHeight);
					if(isNaN(dh) || dh > 55)
						BX.addClass(_this.pInfo.desc, 'mlid-scrld');
				}, 5);

				this.pInfo.desc.parentNode.className = '';
			}
			else
			{
				this.pInfo.desc.parentNode.className = 'ml-info-desc-h';
			}

			this.pInfo.collections.innerHTML = '('; // Clean

			for (i = 0; i < l; i++)
			{
				oCol = this.GetCollection(arCols[i]);
				if (oCol)
				{
					a = this.pInfo.collections.appendChild(BX.create('A', {props: {id: 'ml_info_' + oCol.id, href: 'javascript:void(0);', title: ML_MESS.Collection + ': ' + oCol.name, className: 'ml-info-coll'}, text: oCol.name}));
					a.onclick = function(){_this.SelectCollection(this.id.substr('ml_info_'.length), true);};
				}
				else
				{
					a = this.pInfo.collections.appendChild(BX.create('SPAN', {props: {title: ML_MESS.CollAccessDenied, className: 'ml-info-coll'}, text: ML_MESS.Collection + ' ' + arCols[i]}));
				}

				if (i != l - 1)
					this.pInfo.collections.appendChild(document.createTextNode(', '));
			}
			this.pInfo.collections.appendChild(document.createTextNode(')'));
			var Details = ML_MESS.DateModified + ': ' + oItem.date_mod;
			if (oItem.file_size)
				Details += '<br />' + ML_MESS.FileSize + ': ' + oItem.file_size;

			if (oItem.width && oItem.height)
				Details += '<br />' + ML_MESS.ImageSize + ': ' + oItem.width + ' x ' + oItem.height + ' px';

			this.pInfo.details.innerHTML = Details;
		}
	},

	_ChooseKeysCount: function(pk, pp, strKeys, h, bCut)
	{
		var _this = this;
		pk.innerHTML = BX.util.htmlspecialchars(strKeys);

		setTimeout(function()
		{
			var
				kh = parseInt(pp.offsetHeight),
				ind = strKeys.lastIndexOf(',');

			if(kh > h && ind > 0)
				_this._ChooseKeysCount(pk, pp, BX.util.trim(strKeys.substr(0, ind)), h, true)
			else if(bCut)
				pk.innerHTML += '...';
		}, 1);
	},

	GetItemCollList: function(id, strCallback, oParam)
	{
		if (!this.arItemsCollList[id])
		{
			var _this = this;
			this.Request({
				action: 'get_item_coll_list',
				postData: {id: id},
				handler: function()
				{
					if (!window._ml_items_colls)
						return false;

					_this.arItemsCollList[id] = [];
					for (var i = 0, l = window._ml_items_colls.length; i < l; i++)
						_this.arItemsCollList[id].push(window._ml_items_colls[i]);

					_this[strCallback](oParam);
				}
			});
		}
		else
		{
			this[strCallback](oParam);
		}
	},

	OpenEditItemDialog: function(Params, bFromOnload)
	{
		if (!this.EditItemDialog)
			return this.CreateEditItemDialog(Params);

		if(!bFromOnload)
		{
			this.Request({action: 'bx_test', handler: function(){}}); // Only for access checking
			this.EditItemDialog.alreadySubmitted = false;
			this.EditItemDialog.alreadyLoaded = false;
			this.EditItemDialog.Params = Params || this.EditItemDialog.Params || {};
			this.EditItemDialog.pIfrm.src = this.GetRequestUrl('upload_form');
			return;
		}

		var
			D = this.EditItemDialog,
			id = D.Params.id,
			zIndex = Params.zIndex || this.zIndex + 200,
			w = BX.GetWindowSize(),
			left = parseInt(w.scrollLeft + w.innerWidth / 2 - D.width / 2),
			top = parseInt(w.scrollTop + w.innerHeight / 2 - D.height / 2);

		D.bNew = !id;
		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;
		D.pWnd.style.display = 'block';
		this.EditItemDialog.bShow = true;
		this.bSubdialogOpened = true;
		D.arColls = {};
		D.colLength = 0;

		if (!D.bNew)
		{
			var oItem = this.oCurItems[id].oItem;

			D.pPCFileCont.style.display = D.pLoadFDLink.style.display = D.pFDFileCont.style.display = D.pLoadPCLink.style.display = 'none';
			D.pFNFileCont.style.display = 'block';
			D.pChangeFileLink.style.display = 'inline';
			D.pChangeFileLinkBack.style.display = 'none';

			D.pFileName.innerHTML = oItem.file_name;
			this._AddItemsCollections(this.arItemsCollList[id]);

			D.pName.value = bxspcharsback(oItem.name);
			D.pDesc.value = bxspcharsback(oItem.desc);
			D.pKeys.value = bxspcharsback(oItem.keywords);

			if (oItem.width && oItem.height)
			{
				D.pSize.style.display = 'block';
				D.pSize.innerHTML = oItem.width + " x " + oItem.height;
			}
			else
			{
				D.pSize.style.display = 'none';
			}

			if (oItem.thumb_path)
			{
				D.pThumb.src = oItem.thumb_path;
				D.pNoPreview.style.display = "none";

				if (oItem.width > 146 || oItem.height > 136)
				{
					if (oItem.width - oItem.height > 0)
						D.pThumb.style.width = "146px";
					else
						D.pThumb.style.height = "136px";
				}
				else if (oItem.height < 126)
				{
					D.pThumb.style.marginTop = Math.round((126 - oItem.height) / 2) + 'px';
				}
			}

			D.oItem = oItem;
		}
		else
		{
			D.pFNFileCont.style.display = 'none';
			D.pChangeFileLink.style.display = 'none';
			D.pChangeFileLinkBack.style.display = 'none';

			D.pFileName.innerHTML = "";
			D.pName.value = '';
			D.pDesc.value = '';
			D.pKeys.value = '';

			D.pSize.style.display = 'none';

			if (!D.Params.parentCol && D.Params.bGetSelCol && this.SelectedColId && this.oCollections[this.SelectedColId])
				D.Params.parentCol = this.SelectedColId;

			if (D.Params.parentCol > 0 && this.UserCan(D.Params.parentCol, 'new_item'))
				this._AddItemsCollections([D.Params.parentCol]);
		}
		this._ReHeightEditDialog();
		D.pName.onchange(); // Set title

		jsFloatDiv.Show(D.pWnd, left, top, 5, false, false);
		BX.bind(document, "keypress", window.MlEdItemOnKeypress);
	},

	CreateEditItemDialog: function(Params)
	{
		var
			_this = this,
			D = {
				Params: Params || false,
				width: 420,
				height: 350,
				pWnd: BX('mlsd_item'),
				pTitle: BX('mlsd_item_title'),
				pIfrm: BX('mlsd_iframe_upload'),
				Overlay: new BXOverlay({id: 'bxml_ed_it_overlay'})
			};

		D.pIfrm.src = this.GetRequestUrl('upload_form');

		var _this = this;
		if (BX.browser.IsIE())
			D.pIfrm.onreadystatechange = function(){_this.EditItemDialogOnload()};
		else
			D.pIfrm.onload = function(){_this.EditItemDialogOnload()};

		BX('mlsd_item_cancel').onclick = BX('mlsd_item_close').onclick = function(){_this.CloseEditItemDialog();};

		D.pWnd.style.width = D.width + 'px';
		D.pWnd.style.height = D.height + 'px';
		this.EditItemDialog = D;

		window.MlEdItemOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27)
				_this.CloseEditItemDialog();
		};
	},

	EditItemDialogOnload: function(Params)
	{
		var
			_this = this,
			D = this.EditItemDialog;

		D.pFrameDoc = D.pIfrm.contentDocument || D.pIfrm.contentWindow.document;
		D.pName = D.pFrameDoc.getElementById("mlsd_item_name");
		D.bFocusKeywords = false;

		if (!D.pName && !this.EditItemDialog.alreadySubmitted) // Result
		{
			this.EditItemDialog.alreadySubmitted = true;
			return setTimeout(function(){_this.CSEditItem(top.bx_req_res, top._ml_items_colls);}, 50);
		}

		if (this.EditItemDialog.alreadyLoaded || this.EditItemDialog.alreadySubmitted)
			return;

		this.EditItemDialog.alreadyLoaded = true;
		D.pDesc = D.pFrameDoc.getElementById("mlsd_item_desc");
		D.pKeys = D.pFrameDoc.getElementById("mlsd_item_keywords");
		D.pColSelect = D.pFrameDoc.getElementById("mlsd_coll_sel");
		D.pItCollCont = D.pColSelect.parentNode.parentNode;
		D.pFNFileCont = D.pFrameDoc.getElementById("mlsd_fname_cont");
		D.pPCFileCont = D.pFrameDoc.getElementById("mlsd_load_cont");
		D.pFDFileCont = D.pFrameDoc.getElementById("mlsd_select_cont");
		D.pLoadFile = D.pFrameDoc.getElementById("ml_load_file");
        D.pLoadMaxSize = D.pFrameDoc.getElementById("ml_load_max_size");
		D.pChangeFileLink = D.pFrameDoc.getElementById("mlsd_fname_change");
		D.pChangeFileLinkBack = D.pFrameDoc.getElementById("mlsd_fname_change_back");
		D.pLoadPCLink = D.pFrameDoc.getElementById("mlsd_select_pc");
		D.pLoadFDLink = D.pFrameDoc.getElementById("mlsd_select_fd");
		D.pItemColls = D.pFrameDoc.getElementById('mlsd_item_collections');
		D.pFileName = D.pFrameDoc.getElementById("ml_file_name");
		D.pId = D.pFrameDoc.getElementById("mlsd_item_id");
		D.pNoPreview = D.pFrameDoc.getElementById("mlsd_no_preview");
		D.pFileName = D.pFrameDoc.getElementById("ml_file_name");
		D.pThumb = D.pFrameDoc.getElementById("mlsd_item_thumb");
		D.pSize = D.pFrameDoc.getElementById("mlsd_item_size");
		D.pItemPath = D.pFrameDoc.getElementById("mlsd_item_path");
		D.pOpenFD = D.pFrameDoc.getElementById("mlsd_open_fd");
		D.pSourceType = D.pFrameDoc.getElementById("mlsd_source_type");
		D.pSaveBut = BX('mlsd_item_save');
		D.pForm = D.pFrameDoc.forms['ml_item_form'];
		D.pTbl = D.pForm.firstChild;

		D.pItemPath.onchange = D.pLoadFile.onchange = function()
		{
			var val = this.value;
			if (!val || val.length <= 0)
				return;
			val = val.replace(/\\/g, '/');
			val = val.substr(val.lastIndexOf('/') + 1);
			D.pName.value = val;
			D.pName.onchange();
		};

		D.pName.onkeydown = D.pName.onchange = function()
		{
			setTimeout(
			function(){
				var
					D = _this.EditItemDialog,
					val = bxhtmlspecialchars(D.pName.value),
					t1 = D.bNew ? ML_MESS.NewItem : ML_MESS.Item;

				D.pTitle.title = t1 + (val.length > 0 ? ': ' + D.pName.value : '');
				D.pTitle.innerHTML = t1 + (val.length > 0 ? ': ' + val : '');
			}, 20);
		};

		D.pLoadFDLink.onclick = function()
		{
			D.pPCFileCont.style.display = D.pLoadFDLink.style.display = 'none';
			D.pFDFileCont.style.display = 'block';
			D.pLoadPCLink.style.display = 'inline';
			D.pSourceType.value = "FD";
		};

		D.pLoadPCLink.onclick = function()
		{
			D.pPCFileCont.style.display = 'block';
			D.pLoadFDLink.style.display = 'inline';
			D.pFDFileCont.style.display = D.pLoadPCLink.style.display = 'none';
			D.pSourceType.value = "PC";
		};

		D.pChangeFileLink.onclick = function()
		{
			D.pFNFileCont.style.display = 'none';
			D.pChangeFileLink.style.display = 'none';
			D.pChangeFileLinkBack.style.display = 'inline';
			D.pLoadPCLink.onclick();
		};

		D.pChangeFileLinkBack.onclick = function()
		{
			D.pPCFileCont.style.display = D.pLoadFDLink.style.display = D.pFDFileCont.style.display = D.pLoadPCLink.style.display = 'none';

			D.pFNFileCont.style.display = 'block';
			D.pChangeFileLink.style.display = 'inline';
			D.pChangeFileLinkBack.style.display = 'none';
			D.pSourceType.value = "N";
		};

		D.pColSelect.onchange = function(){_this._AddCollToItem(this.value); this.value = 0;};

		D.pSaveBut.onclick = function()
		{
			if (_this.EditItemDialogOnsubmit())
			{
				D.pForm.submit();
				_this.CloseEditItemDialog();
			}
		};

		D.pOpenFD.onclick = window.mlOpenFileDialog;
		window.mlOnFileDialogSave = function(name, path, site)
		{
			var url = (path == '/' ? '' : path) + '/' + name;
			D.pItemPath.value = url;
			D.pItemPath.focus();
			D.pItemPath.select();
			D.pItemPath.onchange();
			BX.fireEvent(D.pItemPath, 'change');
		};

		D.pKeys.onchange = D.pKeys.onblur = function() {_this.EditItemDialog.bFocusKeywords = true;}

		this._ReqBuildCollSelect(D.pColSelect, this.arCollectionsTree, 0, true);

		this.OpenEditItemDialog(false, true);
	},

	EditItemDialogOnsubmit: function(Params)
	{
		var
			D = this.EditItemDialog,
			cid, ar = this.EditItemDialog.arColls, val = '';
		for (cid in ar)
			if(ar[cid] && typeof ar[cid] == 'object' && ar[cid].pWnd)
				val += (val == '' ? '' : ',') + cid;

		// 1. Check source
		var srcVal = D.pSourceType.value == "PC" ? D.pLoadFile.value : D.pItemPath.value;

		// Check available extention
		if((D.bNew || srcVal !== '') && !this.CheckFileExt(srcVal))
		{
			alert(ML_MESS.ItemExtError);
			return;
		}

		// Check extention for current type
		if ((D.bNew || srcVal !== '') && !this.CheckFileExt(srcVal, this.curType.arExt) && !confirm(ML_MESS.CheckExtTypeConf))
			return false;

		if (D.bNew)
		{
			var bStop = true;
			if (srcVal == '')
				alert(ML_MESS.ItSourceError);
			else if(!this.CheckFileExt(srcVal))
				alert(ML_MESS.ItemExtError);
			else
				bStop = false;

			if (bStop)
			{
				if (D.pSourceType.value == "PC")
				{
					D.pLoadPCLink.onclick();
					D.pLoadFile.focus();
				}
				else
				{
					D.pLoadFDLink.onclick();
					D.pItemPath.focus();
				}
				return false;
			}
		}

		// 2. Check name
		if(D.pName.value == '')
		{
			alert(ML_MESS.ItNameError);
			D.pName.focus();
			return false;
		}

		// 3. Check collections
		if(val == '')
		{
			alert(ML_MESS.ItCollsError);
			D.pColSelect.focus();
			return false;
		}

		if (!this.EditItemDialog.bNew)
			this.EditItemDialog.pId.value = this.EditItemDialog.oItem.id;

		this.EditItemDialog.pItemColls.value = val;
		return true;
	},

	CloseEditItemDialog: function()
	{
		this.EditItemDialog.bShow = false;
		this.EditItemDialog.Params = false;
		this.bSubdialogOpened = false;
		this.EditItemDialog.pWnd.style.display = 'none';
		jsFloatDiv.Close(this.EditItemDialog.pWnd);
		this.EditItemDialog.Overlay.Hide();
		BX.unbind(document, "keypress", window.MlEdItemOnKeypress);
	},

	_AddItemsCollections: function(arColls)
	{
		var i, l = arColls.length;
		for (i = 0; i < l; i++)
			this._AddCollToItem(arColls[i], false);
	},

	_AddCollToItem: function(id, checkAccess)
	{
		if (this.EditItemDialog.arColls[id])
			return;

		if (this.EditItemDialog.bNew && !this.UserCan(parseInt(id), 'new_item') ||
		!this.EditItemDialog.bNew && !this.UserCan(parseInt(id), 'edit_item'))
			return alert(ML_MESS.CollAccessDenied4);

		var oCol = this.GetCollection(id);
		if (!oCol)
			oCol = {};

		var
			i, l, _this = this,
			pSel = this.EditItemDialog.pColSelect,
			pDiv = BX.create("DIV", {props: {className: 'mlsd-ch-col', title: oCol.name}}, this.EditItemDialog.pFrameDoc),
			pSpan = pDiv.appendChild(BX.create("SPAN", {text: oCol.name}, this.EditItemDialog.pFrameDoc)),
			pDel = pDiv.appendChild(BX.create("IMG", {props: {src: '/bitrix/images/1.gif', className: 'ml-col-del', title: ML_MESS.DelColFromItem, id: 'mlsd_it_' + id}}, this.EditItemDialog.pFrameDoc));

		if (oCol && oCol.name)
		{
			if (checkAccess !== false &&
			(this.EditItemDialog.bNew && !this.UserCan(oCol, 'new_item') ||
			!this.EditItemDialog.bNew && !this.UserCan(oCol, 'edit_item')))
				return alert(ML_MESS.CollAccessDenied2);

			if (oCol && oCol.keywords && this.EditItemDialog.bNew && !this.EditItemDialog.bFocusKeywords)
				this.AppendKeywords(this.EditItemDialog.pKeys, oCol.keywords);

			this.EditItemDialog.pItCollCont.onmouseover = function(e){};
			this.EditItemDialog.pItCollCont.onmouseout = function(e){};

			pDiv.onmouseover = function(){BX.addClass(this, 'col-over');}
			pDiv.onmouseout = function(){BX.removeClass(this, 'col-over');}
			pDel.onclick = function(e)
			{
				var cid = this.id.substr('mlsd_it_'.length);
				_this.EditItemDialog.pItCollCont.removeChild(_this.EditItemDialog.arColls[cid].pWnd);
				_this._SelectOptionInColList(_this.EditItemDialog.pColSelect, cid, false);
				_this.EditItemDialog.arColls[cid] = null;
				_this.EditItemDialog.colLength--;
				_this._ReHeightEditDialog();
			};
		}
		else
		{
			pDiv.title = ML_MESS.CollAccessDenied;
			pSpan.innerHTML = ML_MESS.Collection + ' ' + id;
		}

		this.EditItemDialog.colLength++;
		this.EditItemDialog.pItCollCont.insertBefore(pDiv, pSel.parentNode);
		_this._SelectOptionInColList(pSel, id);

		if (checkAccess !== false)
			this._ReHeightEditDialog();

		this.EditItemDialog.arColls[id] = {pWnd : pDiv};
	},

	_SelectOptionInColList: function(pSel, val, bSel)
	{
		for (var i = 0, l = pSel.options.length; i < l; i++)
		{
			if (pSel.options[i].value == val)
			{
				pSel.options[i].className = (bSel !== false) ? 'opt-checked' : '';
				pSel.options[i].title = (bSel !== false) ? ML_MESS.CheckedColTitle : '';
				break;
			}
		}
	},

	_ReHeightEditDialog: function()
	{
		var rows = Math.ceil((this.EditItemDialog.colLength + 2) / 4);
		if (rows < 2)
			rows = 2;

		var delta = (rows - 2) * 28;

		this.EditItemDialog.pItCollCont.style.height = rows * 28 + 'px';
		this.EditItemDialog.pIfrm.style.height = 275 + delta + 'px';
		this.EditItemDialog.pTbl.style.height = 265 + delta + 'px';
		this.EditItemDialog.pWnd.style.height = 350 + delta + 'px';
		jsFloatDiv.AdjustShadow(this.EditItemDialog.pWnd);
	},

	GetRequestUrl: function(action)
	{
		return '/bitrix/admin/fileman_medialib.php?sessid=' + this.sessid + '&lang=' + this.oConfig.lang + (action ? '&action=' + action : '');
	},

	CheckReqLostSessid: function(result)
	{
		var
			LSS = 'BX_ML_DUBLICATE_ACTION_REQUEST',
			LSSIndex = result.indexOf(LSS);

		if (LSSIndex == -1)
			return true;

		this.sessid = result.substring(LSSIndex + LSS.length, result.lastIndexOf('-->'));
		return this.sessid;
	},

	Resize: function(w, h)
	{
		if (w < 565)
			w = 565; // Minimum width
		if (h < 400)
			h = 400; // Minimum height

		this.width = w;
		this.height = h;

		// Dialog
		this.pWnd.style.width = w + 'px';
		this.pWnd.style.height = h + 'px';
		jsFloatDiv.AdjustShadow(this.pWnd);

		var
			contH = h - 95
			contW = w,
			rW = contW - 220;

		this.pHeaderCont.style.width = (w - 10) + 'px';
		this.pFrameTbl.style.height = h + 'px';
		this.pLeftCont.style.height = contH + 'px';
		this.pRightCont.style.height = contH + 'px';
		this.pRightCont.style.width = (rW + 3)+ 'px';

		this.pCollCont.style.height = (contH - (this.bTypes ? 39 : 0))+ 'px';
		this.pListCont.style.height = (contH - 110) + 'px';

		// Desc field in info-panel
		this.pInfo.desc.style.width = (Math.round(rW / 2) - 10) + 'px';

		this.pListCont.style.width = rW + 'px';
		this.pInfo.pWnd.style.width = (rW - 10)+ 'px';

		this.pButCont.style.width = contW + 'px';

		// Resizer position
		this.pResizer.style.left = (w - 20) + 'px';
		this.pResizer.style.top = (h - 20) + 'px';
	},

	ResizerMouseDown: function()
	{
		var _this = this;
		this.oPos = {top: parseInt(this.pWnd.style.top, 10), left: parseInt(this.pWnd.style.left, 10)};

		window['MLResizerMouseUp'] = function(){_this.ResizerMouseUp()};
		window['MLResizerMouseMove'] = function(e){_this.ResizerMouseMove(e)};

		BX.bind(document, "mouseup", window['MLResizerMouseUp']);
		BX.bind(document, "mousemove", window['MLResizerMouseMove']);
	},

	ResizerMouseUp: function()
	{
		this.SaveSettings();
		BX.unbind(document, "mouseup", window['MLResizerMouseUp']);
		BX.unbind(document, "mousemove", window['MLResizerMouseMove']);

		this.SelectItem(this.SelectedItemId);
	},

	ResizerMouseMove: function(e)
	{
		var
			windowSize = BX.GetWindowSize(),
			mouseX = e.clientX + windowSize.scrollLeft,
			mouseY = e.clientY + windowSize.scrollTop
			w = mouseX - this.oPos.left,
			h = mouseY - this.oPos.top;

		this.Resize(w, h);
	},

	Request : function(P)
	{
		P.url = this.GetRequestUrl(P.action);

		if (!P.postData)
			P.postData = {};

		var _this = this, iter = 0;
		var handler = function(result)
		{
			var handleRes = function()
			{
				_this.CloseWaitWindow();
				//if (!result || result.length <= 0 || result.toLowerCase().indexOf('bx_event_calendar_action_error') != -1)
				//	return _this.DisplayError(P.errorText || '');

				if (result.indexOf('BX_ML_LOAD_OK') == -1)
					return alert(ML_MESS.AccessDenied);

				var new_sess = _this.CheckReqLostSessid(result);
				if (new_sess !== true)
				{
					if (P.bRequestReply)
						alert(ML_MESS.SessExpired);
					else
					{
						P.bRequestReply = true;
						setTimeout(function(){_this.Request(P);}, 50);
					}
					return;
				}

				var res = P.handler(result);
				if(res === false && ++iter < 20)
					setTimeout(handleRes, 3);
			};
			setTimeout(handleRes, 10);
		};
		window.bx_req_res = false;

		this.ShowWaitWindow();
		jsAjaxUtil.PostData(P.url, P.postData, handler);
	},

	ShowWaitWindow: function()
	{
		if (window.ShowWaitWindow)
			ShowWaitWindow();
	},

	CloseWaitWindow: function()
	{
		if (window.CloseWaitWindow)
			CloseWaitWindow();
	},

	OpenConfirm: function(Params)
	{
		var
			w = Params.width || 560,
			h = Params.height || 100,
			zIndex = Params.zIndex || this.zIndex + 100;
			_this = this;

		if (!this.Confirm)
		{
			var D = {
				pWnd: BX('ml_colfirm_dialog'),
				pText: BX('ml_confd_text'),
				but1: BX('ml_confd_b1'),
				but2: BX('ml_confd_b2'),
				butCancel: BX('ml_confd_cancel'),
				Overlay: new BXOverlay({id: 'bxml_conf_overlay'})
			};

			D.butCancel.onclick = function(){_this.CloseConfirm();};
		}
		else
		{
			var D = this.Confirm;
		}

		D.pWnd.style.width = w + 'px';
		D.pWnd.style.height = h + 'px';
		D.pWnd.style.zIndex = zIndex;
		D.pWnd.style.display = 'block';
		D.Overlay.Show({zIndex: zIndex - 10, clickCallback:{func:this.CloseConfirm, obj: this}});
		this.bSubdialogOpened = true;

		var
			ws = BX.GetWindowSize(),
			left = parseInt(ws.scrollLeft + ws.innerWidth / 2 - w / 2),
			top = parseInt(ws.scrollTop + ws.innerHeight / 2 - h / 2);

		jsFloatDiv.Show(D.pWnd, left, top, 0, false, false);

		//But 1
		D.but1.value = Params.but1.text;
		D.but1.onclick = function(e){Params.but1.handler(e);_this.CloseConfirm();}
		D.but1.disabled = !!Params.but1.disabled;
		D.but1.focus();

		if (Params.but2)
		{
			D.but2.style.display = 'inline';
			D.but2.value = Params.but2.text;
			D.but2.disabled = !!Params.but2.disabled;
			D.but2.onclick = function(e){Params.but2.handler(e);_this.CloseConfirm();}
		}
		else
		{
			D.but2.style.display = 'none';
		}

		D.pText.innerHTML = Params.text;
		this.Confirm = D;

		window.MlConfDialofOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27)
				_this.CloseConfirm();
		};
		BX.bind(document, "keypress", window.MlConfDialofOnKeypress);
	},

	CloseConfirm: function()
	{
		this.Confirm.pWnd.style.display = 'none';
		jsFloatDiv.Close(this.Confirm.pWnd);
		this.Confirm.Overlay.Hide();
		this.bSubdialogOpened = false;
		BX.unbind(document, "keypress", window.MlConfDialofOnKeypress);
	},

	SaveSettings: function()
	{
		if (this.width && this.height)
		{
			this.userSettings.width = this.width;
			this.userSettings.height = this.height;
		}

		this.Request({
			action: 'save_settings',
			postData: this.userSettings,
			handler: function(){}
		});
	},

    CSDelCollection: function(id, childs, bEmpty)
	{
		var C = this.oCollections[id];
		if (C)
		{
			var col = this.GetCollection(id);
			if (childs !== false && typeof col == 'object' && col.parent > 0)
				this._IncreaseCollChild(parseInt(col.parent), -1);

			if (this.SelectedColId && this.SelectedColId == id)
				this.DeSelectCollection();

			//1. Del from array
			this.arCollections = BX.util.deleteFromArray(this.arCollections, C.ind);
            for (col_id in this.oCollections)
            {
                if(this.oCollections[col_id] && this.oCollections[col_id].ind > C.ind)
                    this.oCollections[col_id].ind--;
            }

			// Del from list
			var pCont = C.pChildCont.parentNode;
			if (pCont)
			{
				pCont.removeChild(C.pChildCont);
				pCont.removeChild(C.pTitle);
			}

            if (bEmpty === undefined)
                bEmpty = true;

            if (pCont.childNodes.length > 0)
            {
                for (var i = 0, l = pCont.childNodes.length; i < l; i++)
                {
                    if (pCont.childNodes[i] && pCont.childNodes[i].className && pCont.childNodes[i].className.indexOf('ml-no-colls') == -1)
                    {
                        bEmpty = false;
                        break;
                    }
                }
            }
            else
                bEmpty = false;

			if (bEmpty)
			{
				BX.addClass(this.pLeftCont, 'ml-no-colls-sect');
				this.pAddNewItem.style.display = 'none';
			}

			// Del from obj
			this.oCollections[id] = null;

			if (childs)
			{
				for (var i = 0, l = childs.length; i < l; i++)
					this.CSDelCollection(childs[i], false, bEmpty);
			}

			if (childs !== false)
				this.ReNewCollectionTree();
		}
	},

	CSDelItem: function(Params)
	{
		var
			id = Params.id,
			Item = this.oCurItems[id],
			arCols = [];

		if (!Item)
			return;

		if (this.ViewItDialog && this.ViewItDialog.bOpened)
			this.CloseViewItDialog();

		if (Params.mode == 'current')
		{
			arCols.push(parseInt(this.SelectedColId));
			this.arItemsCollList[id] = false;
		}
		else
		{
			if (!this.arItemsCollList[id])
				return this.GetItemCollList(id, "CSDelItem", Params);
			arCols = this.arItemsCollList[id];
		}

		// Del from collections
		var i, l = arCols.length, n, j, el, col;
		for (i = 0; i < l; i++)
		{
			if(MLItems[arCols[i]])
			{
				n = MLItems[arCols[i]].length;
				for (j = 0; j < n; j++)
				{
					el = MLItems[arCols[i]][j];
					if (el.id == id)
					{
						MLItems[arCols[i]] = BX.util.deleteFromArray(MLItems[arCols[i]], j);
						break;
					}
				}
			}
		}

		this.currentIdShowed = 0;
		col = this.SelectedColId;
		if (col)
		{
			this.SelectedColId = 0;
			this.SelectCollection(col);
		}

		this.SelectItem();
	},

	CSEditItem: function(arItem, arColls)
	{
        if(!arItem)
        {
            // Check size
            if(parseInt(this.EditItemDialog.pLoadFile.files[0].size) > parseInt(this.EditItemDialog.pLoadMaxSize.value))
            {
                var fileSize = parseInt(this.EditItemDialog.pLoadMaxSize.value)/(1024*1024);
                return alert(ML_MESS.ItFileSizeError.replace('#FILESIZE#', fileSize));
            }
        }

        if (!arItem || typeof arColls != 'object')
			return alert(ML_MESS.EditItemError);

		var
			i, l = arColls.length,
			id = arItem.id, ind,
			oldColls = this.arItemsCollList[id] || [],
			l2 = oldColls.length,
			used = {};

		if (oldColls.length > 0)
		{
			ind = this.FindItem(oldColls[0], id)
			if (ind !== false)
				arItem = this._MergeItemInfo(MLItems[oldColls[0]][ind], arItem);
		}

		for (i = 0; i < l; i++)
		{
			if (MLItems[arColls[i]])
			{
				ind = this.FindItem(arColls[i], id);
				if (ind === false)
					MLItems[arColls[i]].push(arItem);
				else
					MLItems[arColls[i]][ind] = arItem;
				used[arColls[i]] = true;
			}
		}

		for (i = 0; i < l2; i++)
		{
			if(!used[oldColls[i]])
			{
				ind = this.FindItem(oldColls[i], id);
				if (ind !== false)
				{
					MLItems[oldColls[i]] = BX.util.deleteFromArray(MLItems[oldColls[i]], ind);
					this.ShowItems(oldColls[i]);
				}
			}
		}

		this.currentIdShowed = 0;
		this.arItemsCollList[id] = arColls;

		var col = this.SelectedColId;
		if (col)
		{
			this.SelectedColId = 0;
			this.SelectCollection(col);
		}

		this.SelectItem(id);
	},

	_MergeItemInfo: function(ar1, ar2)
	{
		if (typeof ar1 == 'object' && typeof ar2 == 'object')
		{
			for (var i in ar2)
			{
				if (ar2[i] && (ar2[i].length > 0 || ar2[i] > 0))
					ar1[i] = ar2[i];
			}
		}
		return ar1;
	},

	FindItem: function(colId, itemId)
	{
		if (MLItems[colId] && typeof MLItems[colId] == 'object' && MLItems[colId].length > 0)
		{
			var i, l = MLItems[colId].length;
			for (i = 0; i < l; i++)
			{
				el = MLItems[colId][i];
				if (el && el.id == itemId)
					return i;
			}
		}
		return false;
	},

	CheckFileExt: function(ext, arExt)
	{
		ext = ext.substr(ext.lastIndexOf('.') + 1);
		ext = ext.toLowerCase();
		if (!arExt)
			arExt = this.arExt;

		for (var i = 0, l = arExt.length; i < l; i++)
			if (arExt[i] == ext)
				return true;

		return false;
	},

	AppendKeywords: function(pInput, value)
	{
		if (!pInput || !value)
			return;

		var
			arKeys = [],
			arKeysR = pInput.value.split(',').concat(value.split(',')),
			kw, i, l = arKeysR.length;

		for (i = 0; i < l; i++)
		{
			kw = BX.util.trim(arKeysR[i]);
			if (kw && !BX.util.in_array(kw, arKeys))
				arKeys.push(kw);
		}

		pInput.value = arKeys.join(', ');
	},

	InitTypeSelector: function()
	{
		this.bTypes = this.Types.length > 1;
		if (this.bTypes)
		{
			this.pTypeCont = BX('ml_type_cont');

			// Show selector cont
			this.pTypeCont.style.display = "block";
			this.oTypeSelector = new BXMLTypeSelector({
				oML: this,
				Types: this.Types,
				oCallback: {
					obj : this,
					func : this.TypeOnChange
				}
			});
			this.pTypeCont.appendChild(this.oTypeSelector.pWnd);

			// Set init type
			this.oTypeSelector.SetType(0, false);
			this.curType = this.Types[0];
		}
		else
		{
			this.curType = this.Types[0];
		}
	},

	TypeOnChange: function(Params)
	{
		this.curType = this.Types[Params.typeInd];

		// Clean collections cont
		var i, l = this.pCollCont.childNodes.length, ch;
		for (i = l - 1; i >= 0; i--)
		{
			ch = this.pCollCont.childNodes[i];
			if (ch.className.indexOf('ml-no-colls') == -1)
				this.pCollCont.removeChild(ch);
		}

		// Rebuild collections for this type
		this.BuildCollections();

		// Renew breadcrumbs
		this.BuildCrumbs([]);

		// Renew view section
		this.DisplayItems();
	},

	CheckMLType: function(typeId)
	{
		typeId = parseInt(typeId);
		if (!this.bTypes || typeId == this.curType.id)
			return true;

		if ((!this.curType || this.curType.code == "image" && this.curType.system) && (!typeId || typeId == this.curType.id))
			return true;

		return false;
	}
}

function BXMLSearch(oML)
{
	this.oML = oML;
	this.Init();
}

BXMLSearch.prototype = {
	Init: function()
	{
		var _this = this;
		this.bShowed = false;

		this.pInput = BX('medialib_search');

		this.pInput.onfocus = function(e)
		{
			if (this.value == ML_MESS.SearchDef)
			{
				this.value = '';
				this.className = 'ml-search';
			}
		};

		this.pInput.onblur = function(e)
		{
			if (this.value == ML_MESS.SearchDef || this.value == '')
			{
				this.value = ML_MESS.SearchDef;
				this.className = 'ml-search ml-search-empty';
			}
		};

		this.pInput.onkeydown = function(e) // Enter press
		{
			if (!e)
				e = window.event;

			if(e.keyCode == 13)
			{
				if(this.value.length > 0)
					_this.Search(this.value);
				return BX.PreventDefault(e);
			}
		};
	},

	Search: function(q)
	{
		var _this = this;
		window.MLSearchResult = false;
		this.oML.Request({
			action: 'search',
			postData: {q: q, types: _this.oML.requestTypes},
			handler: function()
			{
				if (window.MLSearchResult)
					_this.DisplayResult(window.MLSearchResult, q);
			}
		});
	},

	DisplayResult: function(Res, Query)
	{
		this.bShowed = true;
		this.Query = Query;
		this.oML.DeSelectCollection();

		this.oML.pBread.appendChild(BX.create('SPAN', {props:{className: 'ml-search-title'}, text: ML_MESS.SearchResultEx.replace('#SEARCH_QUERY#', Query)}));

		// Clean
		while(this.oML.pListCont.childNodes[1])
			this.oML.pListCont.removeChild(this.oML.pListCont.lastChild);

		this.oML.oCurItems = {};
		this.oML.pListCont.firstChild.style.display = 'none';

		var i, l = Res.length;
		if (l > 0)
		{
			for (i = 0; i < l; i++)
				this.oML.DisplayItem(Res[i], Res[i].perm, true);
		}
		else
		{
			this.oML.pListCont.appendChild(BX.create('DIV', {props: {className: 'ml-search-no-result'}, text: ML_MESS.NoResult}));
		}
	}
}
