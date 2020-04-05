function BXMedialibAdmin(oConfig)
{
	window.MLItems = {};
	this.arCollections = window.MLCollections;
	this.arItemsCollList = {};

	this.oConfig = oConfig;
	this.sessid = this.oConfig.sessid;
	this.zIndex = 1000;
	this.arItems = {};
	this.curColl = this.oConfig.curColl;
	this.arExt = this.oConfig.strExt.split(',');
}

BXMedialibAdmin.prototype =
{
	OnStart: function()
	{
		this.pCollCont = BX('ml_coll_cont');
		this.pBread = BX('ml_breadcrumbs');

		this.Types = this.oConfig.Types;
		this.curType = '';
		this.requestTypes = [];
		this.imageTypeId = 0;

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
			{
				this.imageTypeId = this.Types[i].id;
				this.requestTypes.push(0);
			}
			this.requestTypes.push(this.Types[i].id);
		}

		this.InitMultiaction();
		this.InitContextMenu();
		this.InitTypeSelector();

		//this.InitSearch();
		this.Search = new BXMLSearch(this);

		// Build collections
		this.BuildCollections();

		//if (this.arCollections.length <= 0)
		//	BX('ml_no_colection_notice').style.display = "block";

		if (this.curColl > 0)
		{
			this.SelectCollection(this.curColl, true);
			this.OpenCollection(this.curColl);
		}

		// Temp hack for dialogs in Opera must die when redesigned
		if (BX('mlsd_item'))
			document.body.appendChild(BX('mlsd_item').parentNode);
	},

	BuildCollections: function()
	{
		this.oCollections = {};
		this.arCollectionsTree = [];
		this.bNoCollections = true;

		var
			arCollectionsTemp = [], newAr, it = 0,
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
		this.bRedrawCollections = true;

		if (this.bNoCollections)
			BX('ml_no_colection_notice').style.display = "block";
	},

	BuildCollection: function(oCol, ind)
	{
		if (!oCol)
			return false;

		if (!this.CheckMLType(oCol.type))
			return true;

		if (this.bNoCollections)
		{
			this.bNoCollections = false;
			BX('ml_no_colection_notice').style.display = "none";
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
			pCont = this.oCollections[oCol.parent].pCollsCont;
			level = this.oCollections[oCol.parent].level + 1;
			this.oCollections[oCol.parent].childCount++;

			if (this.oCollections[oCol.parent].childCount == 1)
				this.oCollections[oCol.parent].icon.className = 'ml-col-icon-closed';
			parAr = this._ReqFindChildCol(this.arCollectionsTree, oCol.parent);
		}
		else
			return false;

		parAr.push({id: oCol.id, child: []});

		if (pCont)
		{
			var
				html = '', i,
				titleDiv = BX.create("DIV", {props:{id : 'ml_coll_title_' + oCol.id}}),
				img = titleDiv.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-col-icon ml-col-icon-closed'}})),
				arHideItems = {length: 0},
				ch = titleDiv.appendChild(BX.create("INPUT", {props:{type: 'checkbox', value: 'c_' + oCol.id}})),
				menuIc = titleDiv.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-col-menu', id: 'mlccm_' + oCol.id}})),
				span = titleDiv.appendChild(BX.create("SPAN", {props: {title: bxspcharsback(oCol.desc || oCol.name)}, text: oCol.name})),
				childDiv = BX.create("DIV"),
				childTbl = childDiv.appendChild(BX.create("TABLE")),
				itemsTd = childTbl.insertRow(-1).insertCell(-1),
				colsTd = childTbl.insertRow(-1).insertCell(-1),
				cellX = childTbl.insertRow(-1).insertCell(-1);

			itemsTd.className = 'ml-coll-items-cont';
			colsTd.className = 'ml-coll-cols-cont';
			cellX.className = 'ml-coll-cols-cell-x';

			for (i = 0; i < 10; i++)
				html += "<img src='/bitrix/images/1.gif' />";
			cellX.innerHTML = html;

			if (arHideItems.del = !this.UserCan(oCol, 'del'))
				arHideItems.length++;
			if (arHideItems.edit = !this.UserCan(oCol, 'edit'))
				arHideItems.length++;
			if (arHideItems.add_col = !this.UserCan(oCol, 'new_col'))
				arHideItems.length++;
			if (arHideItems.add_item = !this.UserCan(oCol, 'new_item'))
				arHideItems.length++;
			if (arHideItems.access = !this.UserCan(oCol, 'access'))
				arHideItems.length++;

			if (arHideItems.length < 5)
			{
				menuIc.onmouseover = function(){BX.addClass(this, 'ml-col-menu-over');};
				menuIc.onmouseout = function(){BX.removeClass(this, 'ml-col-menu-over');};
				menuIc.onclick = function(e){_this.oColMenu.Show({pElement: this, arHideItems: arHideItems});return BX.PreventDefault(e);};
			}
			else
			{
				menuIc.className = 'ml-col-menu ml-col-menu-dis';
			}

			ch.onclick = function(e)
			{
				var id = this.value.substr('c_'.length);
				if (!this.checked)
				{
					var col = _this.GetCollection(id);
					if (col && col.parent > 0 && _this.oCollections[col.parent])
						_this.oCollections[col.parent].pCheck.checked = false;
				}

				_this.CheckAllCollChild(id, !!this.checked, true);

				if(!e) e = window.event;
				if(e.stopPropagation)
					e.stopPropagation();
				else
					e.cancelBubble = true;
			};

			_this._SetColTitleLevel(titleDiv, childDiv, level);
			titleDiv.onclick = function(){_this.OpenCollection(this.id.substr('ml_coll_title_'.length), true);};
			img.onclick = function(e){_this.OpenCollection(this.parentNode.id.substr('ml_coll_title_'.length), false, true); return BX.PreventDefault(e || window.event);};

			pCont.appendChild(titleDiv);
			pCont.appendChild(childDiv);

			this.oCollections[oCol.id] =
			{
				ind: ind,
				pTitle: titleDiv,
				pChildCont: childDiv,
				pCollsCont: colsTd,
				pItemsCont: itemsTd,
				icon: img,
				level: level,
				childCount: 0,
				bOpened: false,
				pCheck: ch
			};
			return true;
		}
	},

	ReNewCollectionTree: function()
	{
		this.bRedrawCollections = true;
		this.arCollectionsTree = [];

		var
			arTMP = [], newAr, it = 0,
			i, l = this.arCollections.length;

		for (i = 0; i < l; i++)
		{
			if (!this.CheckMLType(this.arCollections[i].type))
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
		BX.addClass(Col.pChildCont, 'mlcollt-active-ch');

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
	},

	DeSelectCollection: function(bDelCrumbs)
	{
		if (this.SelectedColId && this.oCollections[this.SelectedColId]) // Deselect
		{
			BX.removeClass(this.oCollections[this.SelectedColId].pTitle, 'mlcollt-active');
			BX.removeClass(this.oCollections[this.SelectedColId].pChildCont, 'mlcollt-active-ch');
		}

		if (bDelCrumbs !== false) // Clean BreadCrumbs
			while(this.pBread.childNodes.length > 0)
				this.pBread.removeChild(this.pBread.firstChild);
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

		var i, l = arr.length, l1 = oSel.options.length, j, html, opt;
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

	OpenCollection: function(id, bSelect, bClose)
	{
		var Col = this.oCollections[id];
		if (!Col || typeof Col != 'object')
			return;

		if (!Col.bOpened || !bClose)
		{
			if(bSelect)
				this.SelectCollection(id);

			// Show Items
			Col.icon.className = 'ml-col-icon ml-col-icon-opened';

			Col.pChildCont.style.display = 'block';
			if (Col.childCount > 0)
				Col.pCollsCont.style.display = 'block';

			this.ShowItems(id);
		}
		else
		{
			Col.pChildCont.style.display = 'none';
			Col.icon.className = 'ml-col-icon ml-col-icon-closed';
		}
		Col.bOpened = !Col.bOpened;
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
					Col.icon.className = 'ml-col-icon ' + (Col.bOpened ? 'ml-col-icon-opened' : 'ml-col-icon-closed');
			}
			else
			{
				Col.childCount--;
				if (Col.childCount <= 0)
					Col.icon.className = 'ml-col-icon';
			}
		}
	},

	_SetColTitleLevel: function(pTitle, pChild, level)
	{
		pTitle.className = 'ml-coll-title mlcolllevel-' + (level > 3 ? 3 : level);
		pChild.className = 'ml-coll-child-cont mlchlevel-' + (level > 3 ? 3 : level);

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
						if (_this.bNoCollections) // No collections for this type
							return _this.Refresh({curColl: oCol.id});

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

							_this._SetColTitleLevel(pTitle, pChildCont, level);
						}

						_this.arCollections[_this.oCollections[oCol.id].ind] = oCol;
						pTitle.childNodes[3].innerHTML = BX.util.htmlspecialchars(oCol.name);
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
			zIndex = 600,
			D = this.EditCollDialog,
			w = BX.GetWindowSize(),
			left = parseInt(w.scrollLeft + w.innerWidth / 2 - D.width / 2),
			top = parseInt(w.scrollTop + w.innerHeight / 2 - D.height / 2);

		if (this.bRedrawCollections)
		{
			this._ReqBuildCollSelect(D.pParent, this.arCollectionsTree, 0, true);
			this.bRedrawCollections = false;
		}

		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;

		D.pWnd.style.display = 'block';
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
		D.pName.focus();

		jsFloatDiv.Show(this.EditCollDialog.pWnd, left, top);
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
		this.bRedrawCollections = true;

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
		var
			_this = this,
			arCol = this.GetCollection(id),
			arAccess = {edit: this.UserCan(arCol, 'edit_item'), del: this.UserCan(arCol, 'del_item')};

		if (typeof MLItems[id] == 'object')
			return this.DisplayItems({Items: MLItems[id], id: id, Access: arAccess});

		this.Request({
			action: 'get_items',
			postData: {col_id: id},
			handler: function()
			{
				if (!window.MLItems[id])
					return false;
				_this.DisplayItems({Items: MLItems[id], id: id, Access: arAccess});
			}
		});
	},

	DisplayItems: function(Params)
	{
		var
			id = Params.id,
			pCont = this.oCollections[id].pItemsCont;

		pCont.style.display = 'block';
		// Clean
		while(pCont.firstChild)
			pCont.removeChild(pCont.lastChild);

		this.arItems[id] = {};

		if (Params.Items && Params.Items.length)
		{
			var i, l = Params.Items.length, bCheck = false;
			if (this.arLoadItems[id])
				bCheck = true;

			for (i = 0; i < l; i++)
				this.DisplayItem({Item: Params.Items[i], pCont: pCont, bCheck: bCheck, id: id, Access: Params.Access});
		}
	},

	DisplayItem: function(Params)
	{
		var
			oItem = Params.Item,
			_this = this,
			w = this.oConfig.thumbWidth,
			h = this.oConfig.thumbHeight,
			itemDiv = BX.create("DIV", {props:{id : 'ml_item_' + oItem.id, className: 'ml-item-cont', title: bxspcharsback(oItem.name)}, style:{width: (w + 15) + 'px', height: (h + 35) + 'px'}}),
			ch = itemDiv.appendChild(BX.create("INPUT", {props:{type: 'checkbox', className: 'item-checkbox', value: Params.id + '|' + oItem.id}})),
			tmbImg = itemDiv.appendChild(BX.create("IMG", {props:{src: oItem.thumb_path || '/bitrix/images/1.gif', className: 'ml-item-thumb'}})),
			titleDiv = itemDiv.appendChild(BX.create("DIV", {props:{className: 'ml-item-title'}, style:{width: (w + 8) + 'px'}}));

		var tmb_path = oItem.thumb_path || oItem.path;
		if (oItem.type == 'image' && tmb_path) // For small images
			tmbImg.style.backgroundImage = 'url(\'' + tmb_path + '\')';

        oItem.trueHeight = Params.Item.height;
		if (!oItem.thumb_path || !oItem.width || !oItem.height)
		{
			BX.addClass(tmbImg, 'ml-item-no-thumb');
			oItem.height = 100; // Bitrix thumb height
		}

		if (oItem.width < w && oItem.height < h) // Small image
		{
			var mt = Math.round((h - oItem.height) / 2);
			if (mt > 0)
				tmbImg.style.marginTop = mt + 'px';
		}

		titleDiv.appendChild(document.createTextNode(bxspcharsback(oItem.name)));
		if (Params.bCheck)
			ch.checked = true;

		ch.onclick = function()
		{
			if (!this.checked && _this.oCollections[Params.id])
				_this.oCollections[Params.id].pCheck.checked = false;

			_this.EnableMultiAction(this.checked || _this.AskAllCheckBoxes());
		};

		var butCont = itemDiv.appendChild(BX.create("DIV", {props:{className: 'ml-item-but-cont'}}));

		var view = butCont.appendChild(BX.create("IMG", {props: {src: '/bitrix/images/1.gif', className: 'ml-item-view', title: ML_MESS.ViewItem}}));
		view.onclick = function(e)
		{
			var id = this.parentNode.parentNode.id.substr('ml_item_'.length);
			_this.GetItemCollList(id, "OpenViewItDialog", {id: id, colId: Params.id, Access: Params.Access, bSearch: Params.bSearch});
			return BX.PreventDefault(e);
		};
		itemDiv.ondblclick = function(e)
		{
			var id = this.id.substr('ml_item_'.length);
			_this.GetItemCollList(id, "OpenViewItDialog", {id: id, colId: Params.id, Access: Params.Access, bSearch: Params.bSearch});
			return BX.PreventDefault(e);
		};

		if (Params.Access.edit || Params.Access.del)
		{
			if (Params.Access.edit)
			{
				var edit = butCont.appendChild(BX.create("IMG", {props: {src: '/bitrix/images/1.gif', className: 'ml-item-edit', title: ML_MESS.EditItem}}));
				edit.onclick = function(e)
				{
					var id = this.parentNode.parentNode.id.substr('ml_item_'.length);
					_this.GetItemCollList(id, "OpenEditItemDialog", {id: id, colId: Params.id, bSearch: Params.bSearch});
					return BX.PreventDefault(e || window.event);
				};
			}

			if (Params.Access.del)
			{
				var del = butCont.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-item-del', title: ML_MESS.DelItem}}));
				del.onclick = function(e)
				{
					var id = this.parentNode.parentNode.id.substr('ml_item_'.length);
					_this.GetItemCollList(id, "DelItem", {id: id, colId: Params.id, bSearch: Params.bSearch});
					return BX.PreventDefault(e || window.event);
				};
			}
			else
			{
				ch.disabled = true;
				ch.checked = false;
			}

			itemDiv.onmouseover = function(){BX.addClass(this, 'ml-item-cont-over');};
			itemDiv.onmouseout = function(){BX.removeClass(this, 'ml-item-cont-over');};
		}

		itemDiv.onclick = function(e)
		{
			if (!e)
				e = window.event;
			var targ = e.target || e.srcElement;
			if (targ.nodeType == 3) // defeat Safari bug
				targ = targ.parentNode;
			if (targ && targ.nodeName && targ.nodeName.toLowerCase() != 'input' && Params.Access.del)
				ch.checked = !ch.checked; ch.onclick();
		};

		if (!Params.bSearch)
			this.arItems[Params.id][oItem.id] = {oItem : oItem, pWnd: itemDiv};

		Params.pCont.appendChild(itemDiv);
	},

	OpenViewItDialog: function(Params)
	{
		if (!this.ViewItDialog)
			this.CreateViewItDialog(Params);

		var
			oItem, i, l,
			zIndex = 600,
			D = this.ViewItDialog;

		if (Params.bSearch)
		{
			l = window.MLSearchResult.length;
			for (i = 0; i < l; i++)
			{
				if (window.MLSearchResult[i].id == Params.id)
				{
					oItem = window.MLSearchResult[i];
					break;
				}
			}
		}
		else if(MLItems[Params.colId])
		{
			l = MLItems[Params.colId].length;
			for (i = 0; i < l; i++)
			{
				if (MLItems[Params.colId][i].id == Params.id)
				{
					oItem = MLItems[Params.colId][i];
					break;
				}
			}
		}

		if (!oItem)
			return;

		D.oItem = oItem;
		D.colId = Params.colId;

		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;
		D.pDel.style.display = Params.Access.del ? 'inline' : 'none';
		D.pEdit.style.display = Params.Access.edit ? 'inline' : 'none';
		D.pWnd.style.display = "block"
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
		this.ViewItDialog.pWnd.style.display = 'none';
		this.ViewItDialog.pCopyInput.style.display = 'none';
		if ((typeof videojs !== 'undefined') && (player = BX.findChild(BX('mlsd_item_cont'), {"class" : "video-js"}, false)))
			videojs(player.id).pause();
        if (player = BX.findChild(BX('mlsd_item_cont'), {"tag" : "div"}, false) && typeof jwplayer !== 'undefined')
            jwplayer(player.id).stop();
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
		//D.pLink.href = oItem.path;
		D.pLink.onclick = function () {
			if(oItem.path.substr(0,1) !== '/' || oItem.path !== oItem.path_external)
			{
				var link = oItem.path_external
			}
			else
			{
				link = 'fileman_file_download.php?path=' + BX.util.urlencode(oItem.path);
			}
			jsUtils.Redirect([], link);
		};

		D.pCopyLink.onclick = function() {
			if(oItem.path.substr(0,1) !== '/' || oItem.path !== oItem.path_external)
			{
				D.pCopyInput.value = oItem.path_external;
			}
			else
			{
				D.pCopyInput.value = window.location.protocol + '//' + window.location.host + oItem.path;
			}

            D.pCopyInput.style.display = 'block';
            D.pCopyInput.select();
        };

		// Keywords
		if (oItem.keywords.length > 0)
		{
			D.pKeys.parentNode.className = 'small-grey';
			D.pKeys.innerHTML = BX.util.htmlspecialchars(oItem.keywords);
			D.pKeys.title = oItem.keywords;
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
			Details += '<br />' + ML_MESS.ImageSize + ': ' + oItem.width + ' x ' + oItem.trueHeight + ' px';

		D.pDetails.innerHTML = Details;

		this.SetItemHTML(oItem);
	},

	SetItemHTML: function(oItem)
	{
		var
			D = this.ViewItDialog,
			_this = this;

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

					jsFloatDiv.Show(D.pWnd, left, top);
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

		if (!Params.mode && Params.bSearch)
			return this.OpenConfirm(
			{
				text: ML_MESS.DelElConfirm,
				but1: {text: ML_MESS.DelElConfirmYes, handler: function(){_this.DelItem({id: Params.id, colId: Params.colId, mode: 'all', bSearch: true})}, width: 100},
				width: 380,
				height: 100
			});

		if (!Params.mode)
			return this.OpenConfirm(
			{
				text: ML_MESS.DelItConfTxt,
				but1: {text: ML_MESS.DelItB1, handler: function(){_this.DelItem({id: Params.id, colId: Params.colId, mode: 'current'})}, width: 180},
				but2: {text: ML_MESS.DelItB2, handler: function(){_this.DelItem({id: Params.id, colId: Params.colId, mode: 'all'})}, disabled: bDisAll, width: 160}
			});

		var colId = Params.colId || 0;
		this.Request({
			action: 'del_item',
			postData: {id: Params.id, mode: Params.mode, col_id: colId},
			handler: function()
			{
				if (window.bx_req_res == true)
					_this.CSDelItem({id: Params.id, mode: Params.mode, colId: colId, bSearch: Params.bSearch});
				else if (window.bx_req_res !== false)
					return false;
			}
		});
	},

	_ChooseKeysCount: function(pk, pp, strKeys, h, bCut)
	{
		var _this = this;
		pk.innerHTML = strKeys;

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
			this[strCallback](oParam);
	},

	OpenEditItemDialog: function(Params, bFromOnload)
	{
		if (!this.EditItemDialog)
			return this.CreateEditItemDialog(Params);

		else if(!bFromOnload)
		{
			this.Request({action: 'bx_test', handler: function(){}}); // Only for access checking
			this.EditItemDialog.alreadySubmitted = false;
			this.EditItemDialog.alreadyLoaded = false;
			this.EditItemDialog.Params = Params || this.EditItemDialog.Params || {};
			this.EditItemDialog.pIfrm.src = this.GetRequestUrl('upload_form');
			return;
		}

		var
			_this = this,
			D = this.EditItemDialog,
			id = D.Params.id,
			zIndex = 600,
			w = BX.GetWindowSize(),
			left = parseInt(w.scrollLeft + w.innerWidth / 2 - D.width / 2),
			top = parseInt(w.scrollTop + w.innerHeight / 2 - D.height / 2);

		D.bNew = !id;
		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;
		D.pWnd.style.display = 'block';
		this.EditItemDialog.bShow = true;
		D.arColls = {};
		D.colLength = 0;

		if (!D.bNew)
		{
			if (D.Params.bSearch)
			{
				var i, l;
				l = window.MLSearchResult.length;
				for (i = 0; i < l; i++)
				{
					if (window.MLSearchResult[i].id == id)
					{
						oItem = window.MLSearchResult[i];
						break;
					}
				}
			}
			else
			{
				var oItem = this.arItems[D.Params.colId][id].oItem;
			}

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
				D.pSize.innerHTML = oItem.width + " x " + oItem.trueHeight;
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

		jsFloatDiv.Show(D.pWnd, left, top);

		window.MlEdItemOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27)
				_this.CloseEditItemDialog();
		};
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
	},

	EditItemDialogOnload: function()
	{
		var
			_this = this,
			D = this.EditItemDialog;

		D.pFrameDoc = D.pIfrm.contentDocument || D.pIfrm.contentWindow.document;
		D.pName = D.pFrameDoc.getElementById("mlsd_item_name");
		D.bFocusKeywords = false;

		if (!D.pName && !this.EditItemDialog.alreadyLoaded)
			return;
			//return alert(ML_MESS.AccessDenied);

		if (!D.pName && this.EditItemDialog.alreadyLoaded && !this.EditItemDialog.alreadySubmitted) // Result
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
		};

		D.pKeys.onchange = D.pKeys.onblur = function() {_this.EditItemDialog.bFocusKeywords = true;}

		this._ReqBuildCollSelect(D.pColSelect, this.arCollectionsTree, 0, true);

		this.OpenEditItemDialog(false, true);
	},

	EditItemDialogOnsubmit: function()
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
			return;

		var
			i, l, _this = this,
			pSel = this.EditItemDialog.pColSelect,
			pDiv = BX.create("DIV", {props: {className: 'mlsd-ch-col', title: oCol.name}}, this.EditItemDialog.pFrameDoc),
			pSpan = pDiv.appendChild(BX.create("SPAN", {text: oCol.name}, this.EditItemDialog.pFrameDoc)),
			pDel = pDiv.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif', className: 'ml-col-del', title: ML_MESS.DelColFromItem, id: 'mlsd_it_' + id}}, this.EditItemDialog.pFrameDoc));

		if (oCol.keywords && this.EditItemDialog.bNew && !this.EditItemDialog.bFocusKeywords)
			this.AppendKeywords(this.EditItemDialog.pKeys, oCol.keywords);

		this.EditItemDialog.pItCollCont.onmouseover = function(e){};
		this.EditItemDialog.pItCollCont.onmouseout = function(e){};

		pDiv.onmouseover = function(e){BX.addClass(this, 'col-over');};
		pDiv.onmouseout = function(e){parent.BX.removeClass(this, 'col-over');};
		pDel.onclick = function(e)
		{
			var cid = this.id.substr('mlsd_it_'.length);
			_this.EditItemDialog.pItCollCont.removeChild(_this.EditItemDialog.arColls[cid].pWnd);
			_this._SelectOptionInColList(_this.EditItemDialog.pColSelect, cid, false);
			_this.EditItemDialog.arColls[cid] = null;
			_this.EditItemDialog.colLength--;
			_this._ReHeightEditDialog();
		};

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

	GetRequestUrl: function(action, sessid)
	{
		return '/bitrix/admin/fileman_medialib.php?sessid=' + (sessid || this.sessid) + '&lang=' + this.oConfig.lang + (action ? '&action=' + action : '');
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


	Request : function(P)
	{
		this.ShowWaitWindow();
		var
			_this = this, iter = 0,
			q = new JCHttpRequest();

		q.Action = function(result)
		{
			var handleRes = function()
			{
				_this.CloseWaitWindow();

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
		q.Post(this.GetRequestUrl(P.action), P.postData ? ConvertArray2Post(P.postData) : '');
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
			zIndex = 700,
			_this = this;

		if (!this.Confirm)
		{
			var D = {
				pWnd: BX('ml_colfirm_dialog'),
				pText: BX('ml_confd_text'),
				but1: BX('ml_confd_b1'),
				but2: BX('ml_confd_b2'),
				butCancel: BX('ml_confd_cancel'),
				Overlay: new BXOverlay({id: 'bxml_conf_overlay', parCont: BX('ml_colfirm_dialog').parentNode})
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

		var
			ws = BX.GetWindowSize(),
			left = parseInt(ws.scrollLeft + ws.innerWidth / 2 - w / 2),
			top = parseInt(ws.scrollTop + ws.innerHeight / 2 - h / 2);

		jsFloatDiv.Show(D.pWnd, left, top, 0);

		//But 1
		D.but1.value = Params.but1.text;
		D.but1.onclick = function(e){Params.but1.handler(e);_this.CloseConfirm();}
		D.but1.disabled = !!Params.but1.disabled;
		if (Params.but1.width && !isNaN(parseInt(Params.but1.width)))
			D.but1.style.width = parseInt(Params.but1.width) + 'px';
		D.but1.focus();

		if (Params.but2)
		{
			D.but2.style.display = 'inline';
			D.but2.value = Params.but2.text;
			D.but2.disabled = !!Params.but2.disabled;
			D.but2.onclick = function(e){Params.but2.handler(e);_this.CloseConfirm();}
			if (Params.but2.width && !isNaN(parseInt(Params.but2.width)))
				D.but2.style.width = parseInt(Params.but2.width) + 'px';
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
		//this.bShow = false;
		this.Confirm.pWnd.style.display = 'none';
		jsFloatDiv.Close(this.Confirm.pWnd);
		this.Confirm.Overlay.Hide();
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

	CSDelCollection: function(id, childs)
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

			var pCont = C.pChildCont.parentNode;
			// Del from list
			if (pCont)
			{
				pCont.removeChild(C.pChildCont);
				pCont.removeChild(C.pTitle);
			}

			var bEmpty = true;
			for (var i = 0, l = pCont.childNodes.length; i < l; i++)
			{
				if (pCont.childNodes[i] && pCont.childNodes[i].nodeName && pCont.childNodes[i].nodeName.toUpperCase() == 'DIV')
				{
					bEmpty = false;
					break;
				}
			}

			if (bEmpty)
				return this.Refresh();

			// Del from obj
			this.oCollections[id] = null;

			if (childs)
			{
				for (var i = 0, l = childs.length; i < l; i++)
					this.CSDelCollection(childs[i], false);
			}

			if (childs !== false)
				this.ReNewCollectionTree();
		}
	},

	CSDelItem: function(Params)
	{
		var
			id = Params.id,
			arCols = [];

		if (Params.colId == 'search_result')
			Params.bSearch = true;

		if (Params.bSearch)
		{
			var i, l = window.MLSearchResult.length;
			for (i = 0; i < l; i++)
			{
				if (window.MLSearchResult[i].id == id)
				{
					Item = window.MLSearchResult[i];
					break;
				}
			}
		}
		else
		{
			var
				colId = parseInt(Params.colId),
				Item = this.arItems[colId][parseInt(id)];
		}

		if (!Item)
			return;

		if (this.ViewItDialog && this.ViewItDialog.bOpened)
			this.CloseViewItDialog();

		if (Params.mode == 'current')
		{
			arCols.push(colId);
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
				this.ShowItems(arCols[i]);
			}
		}

		if (this.Search.bShowed)
		{
			var is, ls = window.MLSearchResult.length, sItem;
			for (is = 0; is < ls; is++)
			{
				sItem = window.MLSearchResult[is];
				if (sItem.id == id)
				{
					window.MLSearchResult = BX.util.deleteFromArray(window.MLSearchResult, is);
					this.Search.DisplayResult(window.MLSearchResult, this.Search.Query);
					break;
				}
			}
		}
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

		this.arItemsCollList[id] = arColls;
		for (i = 0; i < l; i++)
			this.ShowItems(arColls[i]);

		if (this.Search.bShowed)
		{
			var is, ls = window.MLSearchResult.length, sItem;
			for (is = 0; is < ls; is++)
			{
				sItem = window.MLSearchResult[is];
				if (sItem.id == id)
				{
					arItem = this._MergeItemInfo(sItem, arItem);
					window.MLSearchResult[is] = arItem;
					window.MLSearchResult[is].collections = arColls;
					window.MLSearchResult[is].perm = sItem.perm;
					this.Search.DisplayResult(window.MLSearchResult, this.Search.Query);
					break;
				}
			}
		}
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

	InitMultiaction: function()
	{
		var _this = this;
		this.pMultiActCont = BX('ml_multiaction_cnt');
		if (!this.pMultiActCont)
			return;
		this.pCheckAll = BX('ml_action_target');
		this.arLoadItems = {};
		this.pDelBut = BX('action_delete_button');

		this.pCheckAll.onclick = function()
		{
			var
				bCheck = !!this.checked,
				arChecks = _this.pCollCont.getElementsByTagName('INPUT'),
				i, l = arChecks.length;

			for (i = 0; i < l; i++)
				if (arChecks[i].type == 'checkbox')
					arChecks[i].checked = bCheck;

			_this.EnableMultiAction(bCheck);

			_this.arLoadItems = {};
			if (bCheck)
				for (i = 0, l = _this.arCollections.length; i < l; i++)
					_this.arLoadItems[_this.arCollections[i].id] = true;
		};

		this.pDelBut.onclick = function()
		{
			if (!_this.bMultiActEnabled || !confirm(ML_MESS.MultiDelConfirm))
				return;

			var Res = _this.MultiActGetSelected();
			_this.Request({
				action: 'multi_del',
				postData: {
					cols: Res.Colls,
					items: Res.Items
				},
				handler: function()
				{
					_this.Refresh({curColl: _this.SelectedColId});
				}
			});
		};
	},

	AskAllCheckBoxes: function()
	{
		var arChecks = this.pCollCont.getElementsByTagName('INPUT');
		if (this.Search.bShowed)
			arChecks = [].concat(arChecks, this.Search.GetCheckboxes());

		for (var i = 0, l = arChecks.length; i < l; i++)
			if (arChecks[i].type == 'checkbox' && arChecks[i].checked)
				return true;
		return false;
	},

	EnableMultiAction: function(bEnable)
	{
		this.bMultiActEnabled = bEnable;
		if (bEnable)
			BX.removeClass(this.pMultiActCont, "multi-dis");
		else
			BX.addClass(this.pMultiActCont, "multi-dis");
	},

	CheckAllCollChild: function(colId, bCheck, bRoot)
	{
		var
			oCol = this.oCollections[colId],
			col,
			i, l = this.arCollections.length;

		oCol.pCheck.checked = bCheck;

		if (bRoot)
		{
			this.arMultiSelect = {
				Cols: [colId],
				Items:[],
				NotLoadedItems:[]
			};
		}

		if (oCol.childCount > 0) //Subcollections
		{
			for (i = 0; i < l; i++)
			{
				col = this.arCollections[i];
				if (col.parent == colId)
					this.CheckAllCollChild(col.id, bCheck);
			}
		}

		if (typeof MLItems[colId] == 'undefined') // Items not loaded
		{
			if (bCheck)
			{
				this.arLoadItems[colId] = true;
				//this.arMultiSelect.NotLoadedItems.push(colId);
			}
			else
				this.arLoadItems[colId] = false;
		}
		else if (typeof MLItems[colId] == 'object' && MLItems[colId].length > 0)
		{
			var
				arChecks = oCol.pChildCont.getElementsByTagName('INPUT'),
				l1 = arChecks.length;
			for (i = 0; i < l1; i++)
				if (arChecks[i].type == 'checkbox')
					arChecks[i].checked = bCheck;
		}

		if (bRoot)
			this.EnableMultiAction(bCheck || this.AskAllCheckBoxes());
	},

	MultiActGetSelected: function()
	{
		var
			arSelCols = [], arSelItems = {},
			arChecks = this.pCollCont.getElementsByTagName('INPUT'),
			i, l = arChecks.length, val, sep, cid, eid;

		for (i = 0; i < l; i++)
		{
			if (arChecks[i].type == 'checkbox' && arChecks[i].checked)
			{
				val = arChecks[i].value;
				if (val.indexOf('c_') != -1) // Collection
				{
					arSelCols.push(val.substr(2));
					continue;
				}

				sep = val.indexOf('|');
				if (sep != -1)
				{
					cid = val.substr(0, sep);
					eid = val.substr(sep + 1);
					if (!arSelItems[cid])
						arSelItems[cid] = [];
					arSelItems[cid].push(eid); // Items
				}
			}
		}

		if (this.Search.bShowed)
		{
			this.arChecks = false;
			var arSearchCh = this.Search.GetCheckboxes();
			l = window.MLSearchResult.length;

			var it, n, j;
			for (i = 0; i < l; i++)
			{
				it = window.MLSearchResult[i];
				if (this.Search.checkedChIndex[it.id])
				{
					if (it.collections)
					{
						for (j = 0, n = it.collections.length; j < n; j++)
						{
							cid = it.collections[j];
							if (!arSelItems[cid])
								arSelItems[cid] = [];
							arSelItems[cid].push(it.id); // Items
						}
					}
				}
			}
		}

		return {Colls: arSelCols, Items: arSelItems};
	},

	InitContextMenu: function()
	{
		var _this = this;
		var arMenuColl =
		[
			{id: 'edit', name: ML_MESS.Edit, title: ML_MESS.EditCollection, handler: function(pEl){_this.OpenEditCollDialog({id: pEl.id.substr('mlccm_'.length)});}},
			{id: 'del', name: ML_MESS.Delete,title: ML_MESS.DelCollection,handler: function(pEl){_this.DelCollection(pEl.id.substr('mlccm_'.length));}},
			{id: 'access', name: ML_MESS.Access,title: ML_MESS.AccessTitle,handler: function(pEl){window.location = "/bitrix/admin/fileman_medialib_access.php?col_id=" + pEl.id.substr('mlccm_'.length);}},
			{id: 'add_item', name: ML_MESS.AddElement,title: ML_MESS.AddElementTitle, handler: function(pEl){_this.OpenEditItemDialog({parentCol: pEl.id.substr('mlccm_'.length)});}},
			{id: 'add_col', name: ML_MESS.AddCollection,title: ML_MESS.AddCollectionTitle,handler: function(pEl){_this.OpenEditCollDialog({parentCol: pEl.id.substr('mlccm_'.length)});}}
		];

		arMenuColl.push({id: 'change_type', name: ML_MESS.ChangeType,title: ML_MESS.ChangeTypeTitle, handler: function(pEl)
		{
			_this.OpenChangeTypeDialog({id: pEl.id.substr('mlccm_'.length)});
		}});

		this.ClearOverlay = new BXOverlay({id: 'bx_clear', className: 'bx-clear-overlay'});
		this.oColMenu = new MLContextMenu({id: 'coll', Items: arMenuColl, Overlay: this.ClearOverlay, cmPushed: 'ml-col-menu-pushed'});
		//this.oItemMenu = new MLContextMenu({id: 'item'});
	},

	Refresh: function(Params)
	{
		var
			curColl = Params ? parseInt(Params.curColl) : 0,
			strLoc = window.location.toString();

		if (curColl > 0)
		{
			if (strLoc.indexOf('cur_col=') != -1)
				strLoc = strLoc.replace(/(cur_col=)(\d)+/g, '$1' + curColl);
			else
				strLoc += (strLoc.indexOf('?') == -1 ? '?' : '&') + 'cur_col=' + curColl;
		}

		window.location = strLoc;
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
			this.oTypeSelector.SetType(this.oConfig.curTypeInd, false);
			this.curType = this.Types[this.oConfig.curTypeInd];
		}
		else
		{
			this.curType = this.Types[this.oConfig.curTypeInd];
		}
	},

	TypeOnChange: function(Params)
	{
		if (this.Types[Params.typeInd].id != this.curType.id)
			window.location = "/bitrix/admin/fileman_medialib_admin.php?lang=" + this.oConfig.lang + "&type=" + this.Types[Params.typeInd].id; // + '&sessid=' + this.sessid;
	},

	CheckMLType: function(typeId)
	{
		typeId = parseInt(typeId);
		if (!this.bTypes || typeId == this.curType.id)
			return true;

		if ((!this.curType || this.curType.code == "image" && this.curType.system) && (!typeId || typeId == this.curType.id))
			return true;

		return false;
	},

	OpenChangeTypeDialog: function(Params)
	{
		if (!Params)
			Params = {};

		if (!this.ChangeTypeDialog)
			this.CreateChangeTypeDialog();

		var
			zIndex = 600,
			D = this.ChangeTypeDialog,
			w = BX.GetWindowSize(),
			left = parseInt(w.scrollLeft + w.innerWidth / 2 - D.width / 2),
			top = parseInt(w.scrollTop + w.innerHeight / 2 - D.height / 2);

		D.oCol = this.GetCollection(Params.id);
		D.Overlay.Show({zIndex: zIndex - 10});
		D.pWnd.style.zIndex = zIndex;
		D.pWnd.style.display = 'block';

		// Set current type value
		D.pType.value = "none";
		this._TypeOnChange();

		jsFloatDiv.Show(this.ChangeTypeDialog.pWnd, left, top);
		BX.bind(document, "keypress", window.MlChTypeOnKeypress);
	},

	CreateChangeTypeDialog: function(Params)
	{
		var
			_this = this,
			type, opt,
			D = {
				width: 360,
				height: 125,
				pWnd: BX('mlsd_change_type'),
				pType: BX('mlsd_chtype_type'),
				pParent: BX('mlsd_chtype_parent'),
				Overlay: new BXOverlay({id: 'bxml_ch_type_overlay'})
			};

		D.pType.onchange = function(){_this._TypeOnChange();};

		for (var i = 0, l = this.Types.length; i < l; i++)
		{
			if (this.Types[i].id != this.curType.id)
				D.pType.options.add(new Option(this.Types[i].name, i));
		}

		// Build sollections
		this._typeColInd = {};
		this.arTypeCol = {};

		var
			arCollectionsTemp = [], newAr, it = 0,
			i, l = this.arCollections.length;

		for (i = 0; i < l; i++)
			if (!this._buildTypeCol(this.arCollections[i], i))
				arCollectionsTemp.push([this.arCollections[i], i]);

		while(arCollectionsTemp.length > 0 && it < 50)
		{
			l = arCollectionsTemp.length;
			newAr = [];
			for (i = 0; i < l; i++)
				if (!this._buildTypeCol(arCollectionsTemp[i][0], arCollectionsTemp[i][1]))
					newAr.push(arCollectionsTemp[i]);

			arCollectionsTemp = newAr;
			it++;
		}

		BX('mlsd_chtype_save').onclick = function()
		{
			if (_this.ChangeColType() !== false);
				_this.CloseChangeTypeDialog();
		};
		BX('mlsd_chtype_cancel').onclick = function(){_this.CloseChangeTypeDialog();};
		BX('mlsd_chtype_close').onclick = function(){_this.CloseChangeTypeDialog();};

		window.MlChTypeOnKeypress = function(e)
		{
			if(!e) e = window.event;
			if(e && e.keyCode == 27)
				_this.CloseChangeTypeDialog();
		};

		D.pWnd.style.width = D.width + 'px';
		D.pWnd.style.height = D.height + 'px';
		this.ChangeTypeDialog = D;
	},

	CloseChangeTypeDialog: function()
	{
		this.ChangeTypeDialog.pWnd.style.display = 'none';
		jsFloatDiv.Close(this.ChangeTypeDialog.pWnd);
		this.ChangeTypeDialog.Overlay.Hide();
		BX.unbind(document, "keypress", window.MlChTypeOnKeypress);
	},

	_TypeOnChange: function()
	{
		var pParSel = this.ChangeTypeDialog.pParent;
		if (this.ChangeTypeDialog.pType.value == 'none')
		{
			pParSel.disabled = true;
			this._ReqBuildCollSelect(pParSel, [], 0, true);
		}
		else
		{
			pParSel.disabled = false;
			var type = this.Types[this.ChangeTypeDialog.pType.value];
			this._ReqBuildCollSelect(pParSel, this.arTypeCol[type.id] || [], 0, true);
		}
	},

	_buildTypeCol: function(oCol, ind)
	{
		if (!oCol)
			return false;

		var
			type = oCol.type || this.imageTypeId,
			level, parAr;

		if (!this.arTypeCol[type])
			this.arTypeCol[type] = [];

		oCol.parent = parseInt(oCol.parent);
		if (!oCol.parent) // Root element
		{
			level = 0;
			parAr = this.arTypeCol[type];
		}
		else if (this._typeColInd[oCol.parent])
		{
			level = this._typeColInd[oCol.parent].level + 1;
			this._typeColInd[oCol.parent].childCount++;
			parAr = this._ReqFindChildCol(this.arTypeCol[type], oCol.parent);
		}
		else
			return false;

		if (parAr && typeof parAr == 'object')
			parAr.push({id: oCol.id, child: []});
		this._typeColInd[oCol.id] = {ind: ind, level: level, childCount: 0};
		return true;
	},

	ChangeColType: function()
	{
		var
			_this = this,
			D = _this.ChangeTypeDialog,
			type = D.pType.value,
			par = D.pParent.value;

		if (type != 'none')
		{
			// Count child collections
			var children = this._IterGetChildCols(this._ReqFindChildCol(this.arCollectionsTree, D.oCol.id));
			if (children.length > 0 && !confirm(ML_MESS.ChangeTypeChildConf))
				return false;

			this.Request({
				action: 'change_col_type',
				postData: {col: D.oCol.id, type: this.Types[type].id, parent: par, children: children},
				handler: function()
				{
					if (window.bx_req_res === false)
						alert(ML_MESS.ChangeTypeError);
					else
						return _this.Refresh();
				}
			});
		}
		return true;
	},

	_IterGetChildCols: function(ar)
	{
		var arRes = [], i, l = ar.length;
		for (i = 0; i < l; i++)
		{
			arRes.push(ar[i].id);
			if (ar[i].child.length > 0)
				arRes = arRes.concat(this._IterGetChildCols(ar[i].child));
		}
		return arRes;
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
		this.pInput = BX('ml_search_input');
		this.pButton = BX('ml_search_button');
		this.pResultCont = BX('ml_search_res_cont');
		this.pResultContDiv = BX('ml_s_res_cnt_div');
		this.pResultContPar = BX('ml_search_res_cont_par');

		this.pResultTitle = BX('ml_srch_res_title');
		this.pResultCheckbox = BX('ml_srch_res_check');
		this.pResultFlip = BX('ml_srch_res_flip');
		this.pResultHide = BX('ml_srch_res_hide');

		this.pButton.onclick = function()
		{
			if (_this.pInput.value.length > 0)
				_this.Search(_this.pInput.value);
		};

		this.pInput.onkeydown = function(e) // Enter press
		{
			if (!e)
				e = window.event;
			if(e.keyCode == 13 && this.value.length > 0)
				_this.Search(this.value);
		};

		this.pResultHide.onclick = function(e){_this.pResultContPar.style.display = 'none'; _this.bShowed = false; return BX.PreventDefault(e);};
		this.pResultFlip.onclick = function(e)
		{
			_this.OpenResultCont();
			return BX.PreventDefault(e);
		};

		this.pResultCheckbox.onclick = function(e)
		{
			var
				bCheck = this.checked,
				arChecks = _this.GetCheckboxes(),
				i, l = arChecks.length;

			for (i = 0; i < l; i++)
				arChecks[i].checked = bCheck && !arChecks[i].disabled;

			_this.oML.EnableMultiAction(bCheck || _this.oML.AskAllCheckBoxes());

			if(!e) e = window.event;
			if(e.stopPropagation)
				e.stopPropagation();
			else
				e.cancelBubble = true;
		};
	},

	GetCheckboxes: function()
	{
		this.checkedChIndex = {};
		if (!this.arChecks)
		{
			this.arChecks = [];
			var
				arChecks = this.pResultCont.getElementsByTagName('INPUT'),
				i, l = arChecks.length;

			for (i = 0; i < l; i++)
			{
				if (arChecks[i].type == 'checkbox' && arChecks[i].value.indexOf('search_result') != -1)
				{
					this.arChecks.push(arChecks[i]);

					if (arChecks[i].checked)
						this.checkedChIndex[arChecks[i].value.substr(14)] = true;  // 'search_result|'.length
				}
			}
		}
		else
		{
			for (var i = 0, l = this.arChecks.length; i < l; i++)
				if (this.arChecks[i].checked)
					this.checkedChIndex[this.arChecks[i].value.substr(14)] = true;  // 'search_result|'.length
		}
		return this.arChecks;
	},

	Search: function(q)
	{
		var _this = this;
		window.MLSearchResult = false;
		this.oML.Request({
			action: 'search',
			postData: {q: q},
			handler: function()
			{
				if (window.MLSearchResult)
					_this.DisplayResult(window.MLSearchResult, q);
			}
		});
	},

	DisplayResult: function(Res, Query)
	{
		this.arChecks = false;

		this.bOpened = false;
		this.bShowed = true;
		this.Query = Query;
		this.pResultContPar.style.display = 'block';
		this.OpenResultCont();
		this.pResultTitle.innerHTML = ML_MESS.SearchResultEx.replace('#SEARCH_QUERY#', BX.util.htmlspecialchars(Query));

		// Clean
		while(this.pResultCont.firstChild)
			this.pResultCont.removeChild(this.pResultCont.lastChild);

		var i, l = Res.length;
		if (l > 0)
		{
			for (i = 0; i < l; i++)
				this.oML.DisplayItem({
					bSearch: true,
					Item: Res[i],
					pCont: this.pResultCont,
					bCheck: false,
					id: 'search_result',
					Access: Res[i].perm
				});
		}
		else
		{
			this.pResultCont.appendChild(BX.create('DIV', {props:{className: 'ml-search-no-result'}, text:ML_MESS.NoResult}));
		}
	},

	OpenResultCont: function()
	{
		if (!this.bOpened)
		{
			this.pResultFlip.className = 'ml-col-icon ml-col-icon-opened';
			this.pResultContDiv.style.display = 'block';
		}
		else
		{
			this.pResultContDiv.style.display = 'none';
			this.pResultFlip.className = 'ml-col-icon ml-col-icon-closed';

		}
		this.bOpened = !this.bOpened;
	}
}

// CONTEXT MENU FOR EDITING AREA
function MLContextMenu(arParams)
{
	this.Items = arParams.Items;
	this.oOverlay = arParams.Overlay;
	this.zIndex = arParams.zIndex || 600;
	this.id = arParams.id || 'menu';
	this.cmPushed = arParams.cmPushed;

	this.PreCreate();
}

MLContextMenu.prototype =
{
	PreCreate: function()
	{
		this.pref = 'ML_' + this.id + '_';
		this.oDiv = document.body.appendChild(BX.create('DIV', {props:{className: 'bx-cm', id: this.pref + '_cont'}, style:{zIndex: this.zIndex}, html: '<table><tr><td class="bxcm-popup"><table id="' + this.pref + '_cont_items"><tr><td></td></tr></table></td></tr></table>'}));

		// Part of logic of JCFloatDiv.Show()   Prevent bogus rerendering window in IE... And SpeedUp first context menu calling
		document.body.appendChild(BX.create('IFRAME', {props:{id: this.pref + '_frame', src: "javascript:void(0)"}, style:{position: 'absolute', zIndex: this.zIndex - 5, left: '-1000px', top: '-1000px', visibility: 'hidden'}}));
		this.menu = new PopupMenu(this.pref + '_cont');
	},

	Create: function()
	{
		this.BuildItems();
		this.bCreated = true;
	},

	Show: function(Params)
	{
		if (!this.bCreated)
			this.Create();

		this.oDiv.style.width = parseInt(this.oDiv.firstChild.offsetWidth) + 'px';
		this.pElement = Params.pElement;
		var
			pos = jsUtils.GetRealPos(this.pElement),
			_this = this,
			w = parseInt(this.oDiv.offsetWidth),
			h = parseInt(this.oDiv.offsetHeight),
			pOverlay = this.oOverlay.Show();

		if (this.cmPushed)
			BX.addClass(this.pElement, this.cmPushed);

		if (Params.arHideItems.length > 0)
		{
			var i, n = this.Items.length;
			for(i = 0; i < n; i++)
			{
				if (typeof this.Items[i] == 'object')
					this.Items[i].pWnd.style.display = Params.arHideItems[this.Items[i].id] ? 'none' : (BX.browser.IsIE() ? 'inline' : 'table-cell');
			}
		}

		pOverlay.onclick = function(){_this.Close()};
		window.overlay_keypress = function(e){_this.OnKeyPress(e);};
		BX.bind(window, "keypress", window.overlay_keypress);

		pos.top += 2;
		pos.left += 1;

		this.menu.PopupShow(pos);
	},

	Close: function()
	{
		this.menu.PopupHide();
		this.oOverlay.Hide();
		if (this.cmPushed)
			BX.removeClass(this.pElement, this.cmPushed);
		BX.unbind(window, "keypress", window.overlay_keypress);
	},

	BuildItems: function()
	{
		var
			contTbl = BX(this.pref + '_cont_items'),
			n = this.Items.length;
			_this = this;

		var i, cell, oItem;
		//Creation menu elements
		for(i = 0; i < n; i++)
		{
			oItem = this.Items[i];
			cell = contTbl.insertRow(-1).insertCell(-1);
			if(oItem == 'separator')
			{
				cell.innerHTML = '<div class="popupseparator"></div>';
			}
			else
			{
				cell.innerHTML =
					'<table class="popupitem" id="bx_cm_' + oItem.id + '"><tr><td class="gutter"><div class="bx-button" id="bx_btn_' + oItem.id + '" ></div></td><td class="item">' + oItem.name + '</td></tr></table>';
				var oTable = cell.firstChild;
				oTable.onmouseover = function(e){this.className='popupitem popupitemover';}
				oTable.onmouseout = function(e){this.className = 'popupitem';};
				oTable.onclick = function(e){_this.OnClick(this);};
				oItem.pWnd = cell;
			}
		}

		this.oDiv.style.width = contTbl.parentNode.offsetWidth;
		return true;
	},

	OnClick: function(pEl)
	{
		var i, n = this.Items.length, act = pEl.id.substring('bx_cm_'.length);
		this.Close();
		for(i = 0; i < n; i++)
			if (this.Items[i].id == act && this.Items[i].handler)
				return this.Items[i].handler(this.pElement);
	},

	OnKeyPress: function(e)
	{
		if(!e) e = window.event;
		if(e.keyCode == 27)
			this.Close();
	}
}






