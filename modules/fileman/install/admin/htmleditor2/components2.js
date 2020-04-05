var components2_js = true;
function BXComponents2Taskbar()
{
	var oTaskbar = this;
	BXComponents2Taskbar.prototype.OnTaskbarCreate = function ()
	{
		if (!this.pMainObj.arComponents)
			this.pMainObj.arComponents = {};
		if (window.lca && !_$lca_to_output)
			this.pMainObj.AddEventHandler('SaveContentAfter', this.OnSaveLCA, this);

		this.pMainObj.C2Parser = new C2Parser(this.pMainObj, this);
		this.C2Parser = this.pMainObj.C2Parser;
		this.icon = 'components';
		this.iconDiv.className = 'tb_icon bxed-taskbar-icon-' + this.icon;

		this.pHeaderTable.setAttribute("__bxtagname", "_taskbar_cached"); // need for correct context menu for taskbar title
		if (window.lca)
			_$LCAContentParser_execed = false;

		oTaskbar.pCellComp = oTaskbar.CreateScrollableArea(oTaskbar.pWnd);

		oTaskbar.pCellComp.style.width = "100%";
		oTaskbar.pCellComp.style.height = "100%";
		this.pMainObj.pComponent2Taskbar = this;
		this.FetchArray();
		oBXEditorUtils.addPropertyBarHandler('component2', oTaskbar.ShowProps);
		this.pMainObj.AddEventHandler("OnDblClick", this.OnComp2DblClick, this);
		emptyRow = null;
		table = null;

		oTaskbar.DialogInit();
	}

	BXComponents2Taskbar.prototype.GetParams = function(P)
	{
		if (P.id && oTaskbar.pMainObj.arComponents[P.id])
			return oTaskbar.pMainObj.arComponents[P.id];
		return {};
	}

	BXComponents2Taskbar.prototype.SetParams = function(P)
	{
		if (P.params && P.id)
			oTaskbar.pMainObj.arComponents[P.id] = P.params;
	}

	BXComponents2Taskbar.prototype.OnElementDragEnd = function(oEl)
	{
		if (!oEl)
			return;

		var oTag = oTaskbar.pMainObj.GetBxTag(oEl);
		if (oTag.tag != 'component2')
			return;

		// Run it only when dropped into editor doc
		if (oEl.ownerDocument != oTaskbar.pMainObj.pEditorDocument)
			return oTaskbar.OnElementDragEnd(oTaskbar.pMainObj.pEditorDocument.body.appendChild(oEl.cloneNode(false)));

		oTag.id = null;
		delete oTag.id;
		oEl.id = '';
		oEl.removeAttribute('id');

		var draggedElId = oTaskbar.pMainObj.SetBxTag(oEl, copyObj(oTag));
		oEl.style.cursor = 'default';

		// Hack for safari
		if (BX.browser.IsSafari())
		{
			if (oEl && oEl.parentNode)
				oEl.parentNode.removeChild(oEl);

			oTaskbar.pMainObj.insertHTML('<img src="' + oEl.src + '" id="' + draggedElId + '">');
			setTimeout(function(){oTaskbar.pMainObj.SelectElement(oTaskbar.pMainObj.pEditorDocument.getElementById(draggedElId))}, 20);
		}

		if (oTaskbar.pMainObj.bRenderComponents)
		{
			var otherComp = BXFindParentByTagName(oEl, 'DIV', 'bxc2-block');
			if (otherComp) // Component dragged into another
				otherComp.parentNode.insertBefore(oEl, otherComp); // Put element before parent component
		}

		var props = oTag.params;
		oTaskbar.SetParams({id: oEl.id, params: props});

		if (oTaskbar.pMainObj.bRenderComponents)
		{
			oTaskbar.C2Parser.StartWaiting(oEl); // Show waiting icon instead of native component's icon
			oTaskbar.C2Parser.__bPreventComponentDeselect = true;
			oTaskbar.C2Parser.GetRenderedContent({name: props.name, pElement: oEl, bSelect: true}); // Get default html for new component
		}

		this.nLastDragNDropElement = null;
		this.nLastDragNDropElementFire = false;
	};

	BXComponents2Taskbar.prototype.OnComp2DblClick = function (e)
	{
		var pEl, oTag;
		if (!e)
			e = oTaskbar.pMainObj.pEditorWindow.event;
		if (e.target)
			pEl = e.target;
		else if (e.srcElement)
			pEl = e.srcElement;
		if (pEl.nodeType == 3)
			pEl = pEl.parentNode;

		if (pEl && pEl.nodeName)
		{
			oTag = oTaskbar.pMainObj.GetBxTag(pEl);
			if (oTag.tag != 'component2')
				return;

			if (oTaskbar.pMainObj.pMainObj.bRenderComponents && oTag.tag != 'component2')
				pEl = BXFindParentByTagName(pEl, 'DIV', 'bxc2-block');
			else if (pEl.nodeName.toLowerCase() == 'body' && pEl.lastChild && pEl.lastChild.getAttribute)
				pEl = pEl.lastChild;
		}

		if (!pEl || !pEl.getAttribute)
			return;

		oTag = oTaskbar.pMainObj.GetBxTag(pEl);
		if (oTag.tag != 'component2')
			return;

		this.pMainObj.OpenEditorDialog("component2", false, 650, {pElement: pEl});
		BX.PreventDefault(e);
	}

	BXComponents2Taskbar.prototype.FetchArray = function (clear_cache)
	{
		var loadComp2 = function()
		{
			oTaskbar.BuildList(window.arComp2Elements);
			window.as_arComp2Elements = [];
			var __len = window.arComp2Elements.length;
			for (var i = 0; i < __len; i++)
				window.as_arComp2Elements[window.arComp2Elements[i].name] = window.arComp2Elements[i];
			BX.closeWait();
		};

		CHttpRequest.Action = function(result)
		{
			try{
				setTimeout(loadComp2, 10);
			}catch(e){
				err_text = "ERROR in BXComponents2Taskbar.FetchArray(): ";
				if ((eind = result.indexOf('Fatal error')) != -1)
					err_text += "\n PHP error: \n\n....." + result.substr(eind - 10);
				alert(err_text);
			}
		};

		if (window.arComp2Elements)
			loadComp2();
		else
			CHttpRequest.Send('/bitrix/admin/fileman_load_components2.php?lang='+BXLang+'&site='+BXSite+'&load_tree=Y'+(clear_cache === true ? '&clear_comp2_cache=Y' : ''));
	}

	BXComponents2Taskbar.prototype.BuildList = function (__arElements)
	{
		var len = __arElements.length;
		if (len == 0) // if any allowed components in the list - close taskbar
			oTaskbar.Close();

		for (var i = 0; i < len; i++)
		{
			__arElements[i].tagname = 'component2';
			__arElements[i].childElements = [];
			__arElements[i].params.name = __arElements[i].name;

			if (__arElements[i].isGroup && !__arElements[i].path)
				oTaskbar.AddElement(__arElements[i], oTaskbar.pCellComp, __arElements[i].path);
		}
		this.fullyLoaded = false;
	}

	BXComponents2Taskbar.prototype.PreBuildList = function ()
	{
		var __arElements = window.arComp2Elements;
		var len = __arElements.length;
		for (var i = 0; i < len; i++)
		{
			if (!__arElements[i].isGroup || __arElements[i].path)
				oTaskbar.AddElement(__arElements[i], oTaskbar.pCellComp, __arElements[i].path);
		}
		this.fullyLoaded = true;
	}

	BXComponents2Taskbar.prototype.ShowProps = function (_bNew, _pTaskbar, _pElement, bReloadProps)
	{
		oTaskbar.bDialogMode = _pTaskbar.bDialogMode || false;
		oTaskbar.dialog_suffix = (_pTaskbar.bDialogMode) ? '_d' : '';

		if (bReloadProps || window._bx_reload_template_props || !_pTaskbar.bDialogMode)
		{
			oTaskbar._ShowProps(_bNew, _pTaskbar, _pElement, bReloadProps);
		}
		else
		{
			setTimeout(function() {
				if (!oTaskbar.bLoadProc || oTaskbar.bLoadProc != _pElement.id)
					oTaskbar._ShowProps(_bNew, _pTaskbar, _pElement, bReloadProps);
			}, 500);
		}
	}

	BXComponents2Taskbar.prototype._ShowProps = function (_bNew, _pTaskbar, _pElement, bReloadProps)
	{
		var arProps = this.GetParams({id: _pElement.id});
		if (bReloadProps === true || (!window._bx_reload_template_props && !window.as_arComp2Params[arProps.name + arProps.__bx_id]))
		{
			var str, j;
			for (j in arProps.paramvals)
			{
				str = arProps.paramvals[j];
				if (typeof str == 'string' && str.substr(0, 6).toLowerCase() == 'array(')
					arProps.paramvals[j] = str.toLowerCase() == 'array()' ? [] : eval("[" + str.substr(6, str.length - 7) + "]");
			}
			oTaskbar.bLoadProc = _pElement.id;
			var postData = oBXEditorUtils.ConvertArray2Post(oTaskbar.PrepareParamvals(arProps.paramvals), 'curval');
			oTaskbar.LoadComp2Params(arProps, oTaskbar.BXShowComponent2Panel, oTaskbar, [_bNew,  _pTaskbar, _pElement], 'POST', postData);
		}
		else if (window._bx_reload_template_props)
		{
			oTaskbar.DelTemplateParams(_pElement.id);
			var postData = oBXEditorUtils.ConvertArray2Post(oTaskbar.PrepareParamvals(arProps.paramvals), 'curval');
			oTaskbar.SetCompTemplate(_pElement, this); // TODO: remake this function
			//oTaskbar.pMainObj.GetBxTag(_pElement);

			arProps = this.GetParams({id: _pElement.id});
			oTaskbar.loadTemplateParams(arProps.name, arProps.template, oTaskbar.ShowProps, oTaskbar, [_bNew, _pTaskbar, _pElement, false], "POST", postData);
		}
		else if (window.as_arComp2Params[arProps.name + arProps.__bx_id])
		{
			oTaskbar.BXShowComponent2Panel(_bNew, _pTaskbar, _pElement);
		}
	}

	BXComponents2Taskbar.prototype.ClearCache = function ()
	{
		BX.showWait();
		BX.cleanNode(oTaskbar.pCellComp);
		window.arComp2Elements = false;
		oTaskbar.FetchArray(true);
	}

	BXComponents2Taskbar.prototype.GetPropFieldElements = function (_pTaskbar)
	{
		var arElements = {};
		var parentNode = _pTaskbar.pCellProps;
		arElements = this.AddPropFieldElements(parentNode.getElementsByTagName("SELECT"), arElements);
		arElements = this.AddPropFieldElements(parentNode.getElementsByTagName("INPUT"), arElements);
		arElements = this.AddPropFieldElements(parentNode.getElementsByTagName("TEXTAREA"), arElements);
		this.arCachedElements = arElements;
		return this.arCachedElements;
	}

	BXComponents2Taskbar.prototype.AddPropFieldElements = function(arNodes, arElements)
	{
		var el, name, i, l = arNodes.length;
		for(i = 0; i < l; i++)
		{
			el = arNodes[i];
			if(!isYes(el["__exp"])) continue;
			if(el.name.substr(el.name.length - 2, 2) == '[]')
			{
				name = el.name.substr(0, el.name.length - 2);
				if (!arElements[name])
					arElements[name] = [];
				arElements[name].push(el);
			}
			else
				arElements[el.name] = el;
		}
		return arElements;
	}

	BXComponents2Taskbar.prototype.ShowComponentParam = function(P)
	{
		var
			_this = this,
			_bNew = P._bNew,
			_pElement = P.pElement,
			pPropTsb = P.pPropTaskbar,
			pMainObj = oTaskbar.pMainObj,
			arPropertyParams = P.arProp,
			arAllProps = P.arAllProps,
			propertyID = arPropertyParams.param_name,
			refreshedByOnclick = false,
			arUsedValues = [],
			fChange = P.fChange,
			tProp, pParCell;

		pPropTsb.arElements.push(propertyID);

		if (arAllProps.paramvals && arAllProps.paramvals[propertyID] != undefined)
			arValues = arAllProps.paramvals[propertyID];
		else
			arValues = arPropertyParams.DEFAULT || '';

		oTaskbar.curParamvals[propertyID] = arValues;
		//__paramvals[propertyID] = arValues;
		if(!isYes(arPropertyParams.MULTIPLE))
			arPropertyParams.MULTIPLE = "N";
		if(!arPropertyParams.TYPE)
			arPropertyParams.TYPE = "STRING";
		if(!arPropertyParams.CNT)
			arPropertyParams.CNT = 0;
		if(!arPropertyParams.SIZE)
			arPropertyParams.SIZE = 0;
		if(!arPropertyParams.ADDITIONAL_VALUES)
			arPropertyParams.ADDITIONAL_VALUES = 'N';
		if(!arPropertyParams.ROWS)
			arPropertyParams.ROWS = 0;
		if(!arPropertyParams.COLS || parseInt(arPropertyParams.COLS) < 1)
			arPropertyParams.COLS = '30';

		var _arValues = [];
		if (typeof arValues == 'string')
		{
			var originalValue = arValues;
			if (isPHPBracket(arValues))
				arValues = JS_stripslashes(trimPHPBracket(arValues));

			if (isNum(arValues)) // If it's number - remove PHP brackets
				originalValue = arValues;

			if (arValues.substr(0, 6).toLowerCase() == 'array(')
				_arValues = _BXStr2Arr(arValues, true);
			else if (arPropertyParams.TYPE == "LIST")
				arValues = [originalValue];
			else
				arValues = originalValue;
		}

		if(isYes(arPropertyParams.MULTIPLE) && typeof(arValues) != 'object' && !arValues)
			arValues = _arValues;

		if(isYes(arPropertyParams.MULTIPLE))
		{
			arPropertyParams.CNT = parseInt(arPropertyParams.CNT);
			if (arPropertyParams.CNT < 1)
				arPropertyParams.CNT = 1;
		}
		if (isYes(arPropertyParams.HIDDEN))
			return;

		// If SEF = ON : show SEF_URL_TEMPLATES and SEF_FOLDER
		//     SEF = OFF: show VARIABLE_ALIASES
		if ((propertyID.substr(0, 17) == "SEF_URL_TEMPLATES" || propertyID == "SEF_FOLDER") && pPropTsb.__SEF_MODE === false)
			return;
		else if (propertyID.substr(0,16)=="VARIABLE_ALIASES" && pPropTsb.__SEF_MODE === true)
			return;

		if (!arPropertyParams.PARENT || !arPropertyParams.group)
		{
			arPropertyParams.PARENT = '__bx_additional_group';
			arPropertyParams.group_title = BX_MESS.ADD_INSERT;
		}

		// If it's grouped property
		if (arPropertyParams.PARENT)
		{
			if (!oTaskbar.arGroups[arPropertyParams.PARENT])
			{
				pParCell = oTaskbar.GetPropGroupDataCell(arPropertyParams.PARENT, arPropertyParams.group_title, P.oCont, [arAllProps.name]);
				oTaskbar.arGroups[arPropertyParams.PARENT] = {title: arPropertyParams.group_title, datacell: pParCell};
			}
			else
			{
				pParCell = oTaskbar.arGroups[arPropertyParams.PARENT].datacell;
			}

			var _tbls = pParCell.getElementsByTagName("TABLE");
			if (_tbls.length > 0)
				tProp = _tbls[0];
			else
				tProp =  pParCell.appendChild(BX.create('TABLE', {props: {className : "bxtaskbarprops"}}));
		}
		else
		{
			tProp = __tProp;
			oCont.appendChild(tProp);
		}

		row = tProp.insertRow(-1);
		row.className = "bxtaskbarpropscomp";
		cell = row.insertCell(-1);
		cell.className = "bxtaskbarprop-label";
		cell.innerHTML = "<SPAN>" + bxhtmlspecialchars(arPropertyParams.NAME) + ":</SPAN>";
		cell = row.insertCell(-1);

		arPropertyParams.TYPE = arPropertyParams.TYPE.toUpperCase();

		if (propertyID == "SEF_MODE")
			arPropertyParams.TYPE = "CHECKBOX";

		//* * * * * * Displaying data * * * * * *
		switch(arPropertyParams.TYPE)
		{
		case "LIST":
			arPropertyParams.SIZE = (isYes(arPropertyParams.MULTIPLE) && (parseInt(arPropertyParams.SIZE)<=1 || isNaN(parseInt(arPropertyParams.SIZE))) ? '3' : arPropertyParams.SIZE);
			if(parseInt(arPropertyParams.SIZE)<=0 || isNaN(parseInt(arPropertyParams.SIZE)))
				arPropertyParams.SIZE = 1;
			pSelect = pMainObj.CreateElement("SELECT", {size: arPropertyParams.SIZE, name: propertyID + (isYes(arPropertyParams.MULTIPLE) ? '[]' : ''), __exp: 'Y', onchange: fChange, multiple: isYes(arPropertyParams.MULTIPLE)});
			cell.appendChild(pSelect);
			if(!arPropertyParams.VALUES)
				arPropertyParams.VALUES = [];
			bFound = false;

			for(opt_val in arPropertyParams.VALUES)
			{
				if(typeof(arPropertyParams.VALUES[opt_val]) != 'string')
					continue;
				bSel = false;
				oOption = new Option(JS_stripslashes(arPropertyParams.VALUES[opt_val]), opt_val, false, false);
				pSelect.options.add(oOption);

				if(pSelect.options.length <= 1)
					setTimeout(__BXSetOptionSelected(oOption, false), 1);

				key = BXSearchInd(arValues, opt_val);
				if(key >= 0)
				{
					bFound = true;
					arUsedValues[key]=true;
					bSel = true;
					if (propertyID=="SEF_MODE")
						pPropTsb.__SEF_MODE = isYes(opt_val);
					setTimeout(__BXSetOptionSelected(oOption, true), 1);
				}
				else if(_arValues[opt_val])
				{
					bFound = true;
					arUsedValues[opt_val]=true;
					bSel = true;
					setTimeout(__BXSetOptionSelected(oOption, true), 1);
					delete _arValues[opt_val];
				}
			}

			if(arPropertyParams.ADDITIONAL_VALUES != 'N')
			{
				oOption = document.createElement("OPTION");
				oOption.value = '';
				oOption.selected = !bFound;
				oOption.text = (isYes(arPropertyParams.MULTIPLE) ? BX_MESS.TPropCompNS : BX_MESS.TPropCompOth)+' ->';
				pSelect.options.add(oOption, 0);
				oOption = null;

				if(isYes(arPropertyParams.MULTIPLE))
				{
					if (typeof(arValues) == 'string')
						arValues = _arValues;

					for(k in arValues)
					{
						if(typeof(arValues[k]) != 'string' || arUsedValues[k] || arValues[k] == '')
							continue;
						cell.appendChild(pMainObj.CreateElement("BR"));
						if(arPropertyParams.ROWS > 1)
						{
							var oTextarea = pMainObj.CreateElement("TEXTAREA", {cols: (isNaN(arPropertyParams.COLS) ? '20' : arPropertyParams.COLS), value: JS_stripslashes(arValues[k]), name: propertyID+'[]', __exp: 'Y', onchange: fChange});
							cell.appendChild(oTextarea);
							oTextarea = null;
						}
						else
						{
							var oInput = pMainObj.CreateElement("INPUT", {type: 'text', size: (isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), value: JS_stripslashes(arValues[k]), name: propertyID+'[]', '__exp': 'Y', onchange: fChange});
							cell.appendChild(oInput);
							oInput = null;
						}
					}

					for(k=0; k<arPropertyParams.CNT; k++)
					{
						cell.appendChild(pMainObj.CreateElement("BR"));
						if(arPropertyParams.ROWS>1)
						{
							var oTextarea = pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange});
							cell.appendChild(oTextarea);
							oTextarea = null;
						}
						else
						{
							var oInput = pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange});
							cell.appendChild(oInput);
							oInput = null;
						}
					}

					var oInput = pMainObj.CreateElement("INPUT", {'type': 'button', 'value': '+', 'pMainObj': pMainObj,  'arPropertyParams': arPropertyParams});
					xCell = cell.appendChild(oInput);
					oInput = null;
					var oBR = pMainObj.CreateElement("BR");
					cell.appendChild(oBR);
					oBR = null;
					xCell.propertyID = propertyID;
					xCell.fChange = fChange;
					xCell.onclick = function ()
					{
						this.parentNode.insertBefore(this.pMainObj.CreateElement("BR"), this);
						if(this.arPropertyParams['ROWS'] && this.arPropertyParams['ROWS']>1)
						{
							var oTextarea = this.pMainObj.CreateElement("TEXTAREA", {'cols': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange});
							this.parentNode.insertBefore(oTextarea, this);
							oTextarea = null;
						}
						else
						{
							var oInput = this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!this.arPropertyParams['COLS'] || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams['COLS']), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange});
							this.parentNode.insertBefore(oInput, this);
							oInput = null;
						}
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
						alt = cell.appendChild(pMainObj.CreateElement("TEXTAREA", {'cols': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'disabled': bFound, 'name': propertyID+'_alt', '__exp': 'Y', 'onchange': fChange}));
					else
						alt = cell.appendChild(pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (!arPropertyParams['COLS'] || isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': val, 'disabled': bFound, 'name': propertyID+'_alt', '__exp': 'Y', 'onchange': fChange}));

					pSelect.pAlt = alt;

					if (isYes(arPropertyParams.REFRESH))
						pSelect.onchange = function (e){fChange();_this.ShowProps(_bNew, pPropTsb, _pElement, true);};
					else
						pSelect.onchange = function (e){this.pAlt.disabled = (this.selectedIndex!=0); fChange();};
				}
			}

			if(!bFound)
				setTimeout(fChange, 100);

			if (isYes(arPropertyParams.REFRESH))
				pSelect.onchange = function (e){fChange();_this.ShowProps(_bNew, pPropTsb, _pElement, true);};

			if (propertyID=="SEF_MODE")
			{
				pSelect.onchange = function(e)
					{
						pPropTsb.__SEF_MODE = isYes(this.value);
						fChange();
						_this.ShowProps(_bNew, pPropTsb, _pElement, false);
					};
			}

			break;
		case "CHECKBOX":
			pCheckbox = pMainObj.CreateElement("INPUT", {'type':'checkbox', 'name': propertyID, '__exp': 'Y'});
			cell.appendChild(pCheckbox);

			if (arValues)
				oBXEditorUtils.setCheckbox(pCheckbox, isYes(arValues));
			else if (arPropertyParams.DEFAULT != undefined)
				oBXEditorUtils.setCheckbox(pCheckbox, isYes(arPropertyParams.DEFAULT));
			else
				oBXEditorUtils.setCheckbox(pCheckbox,false);

			if (propertyID=="SEF_MODE")
			{
				pCheckbox.onclick = function(e)
					{
						oBXEditorUtils.setCheckbox(this,this.checked);
						pPropTsb.__SEF_MODE = this.checked;
						fChange();
						_this.ShowProps(_bNew, pPropTsb, _pElement, false);
					}
				pPropTsb.__SEF_MODE = pCheckbox.checked;
			}
			else if(isYes(arPropertyParams.REFRESH))
			{
				pCheckbox.onclick = function(e)
				{
					oBXEditorUtils.setCheckbox(this,this.checked);
					fChange();
					_this.ShowProps(_bNew, pPropTsb, _pElement, true);
				}
				refreshedByOnclick = true;

			}
			else
			{
				pCheckbox.onclick = function(e) {oBXEditorUtils.setCheckbox(this, this.checked); fChange();}
			}
			//__paramvals[propertyID] = pCheckbox.value;
			oTaskbar.curParamvals[propertyID] = pCheckbox.value;
			break;
		case "STYLELIST":
			break;
		default: // 'STRING' OR 'FILE' OR 'COLORPICKER' OR 'CUSTOM'
			if (arPropertyParams.TYPE == 'COLORPICKER' || arPropertyParams.TYPE == 'FILE')
			{
				refreshedByOnclick = true;
				arPropertyParams.ROWS = 1;
				arPropertyParams.MULTIPLE = 'N';
				arPropertyParams.COLS = (arPropertyParams.TYPE == 'FILE') ? 40 : 6;
			}

			if(isYes(arPropertyParams.MULTIPLE))
			{
				bBr = false;
				var obVal = (typeof arValues == 'object') ? arValues : _arValues;
				for(k in obVal)
				{
					if(bBr)
						cell.appendChild(pMainObj.CreateElement("BR"));
					else
						bBr = true;

					val = obVal[k].replace(/(\\)?\\n/g, "\n");
					if(arPropertyParams.ROWS > 1)
						cell.appendChild(pMainObj.CreateElement("TEXTAREA", {cols: parseInt(arPropertyParams.COLS) || 20, value: JS_stripslashes(val), name: propertyID+'[]', __exp: 'Y', onchange: fChange}));
					else
						cell.appendChild(pMainObj.CreateElement("INPUT", {type: 'text', size: parseInt(arPropertyParams.COLS) || 20, value: JS_stripslashes(val), name: propertyID+'[]', __exp: 'Y', 'onchange': fChange}));
				}

				for(k=0; k<arPropertyParams.CNT; k++)
				{
					if(bBr)
						cell.appendChild(pMainObj.CreateElement("BR"));
					else
						bBr = true;

					if(arPropertyParams.ROWS > 1)
						cell.appendChild(pMainObj.CreateElement("TEXTAREA", {'cols': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
					else
						cell.appendChild(pMainObj.CreateElement("INPUT", {'type': 'text', 'size': (isNaN(arPropertyParams['COLS'])?'20':arPropertyParams['COLS']), 'value': '', 'name': propertyID+'[]', '__exp': 'Y', 'onchange': fChange}));
				}

				xCell = cell.appendChild(pMainObj.CreateElement("INPUT", {'type': 'button', 'value': '+', 'pMainObj': pMainObj,  'arPropertyParams': arPropertyParams}));
				xCell.propertyID = propertyID;
				xCell.fChange = fChange;
				xCell.onclick = function ()
				{
					this.parentNode.insertBefore(this.pMainObj.CreateElement("BR"), this);
					if(this.arPropertyParams.ROWS && this.arPropertyParams.ROWS > 1)
						this.parentNode.insertBefore(this.pMainObj.CreateElement("TEXTAREA", {cols: (!this.arPropertyParams.COLS || isNaN(this.arPropertyParams.COLS) ? '20' : this.arPropertyParams.COLS), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
					else
						this.parentNode.insertBefore(this.pMainObj.CreateElement("INPUT", {type: 'text', size: (!this.arPropertyParams.COLS || isNaN(this.arPropertyParams['COLS'])?'20':this.arPropertyParams.COLS), 'value': '', 'name': this.propertyID+'[]', '__exp': 'Y', 'onchange': this.fChange}), this);
				}
				cell.appendChild(pMainObj.CreateElement("BR"));
			}
			else
			{
				var oInput;
				val = arValues.replace(/(\\)?\\n/g, "\n");
				if(arPropertyParams.ROWS && arPropertyParams.ROWS > 1)
					cell.appendChild(pMainObj.CreateElement("TEXTAREA", {cols: (!arPropertyParams.COLS || isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), value: JS_stripslashes(val), name: propertyID, __exp: 'Y', onchange: fChange}));
				else
					oInput = cell.appendChild(pMainObj.CreateElement("INPUT", {type: 'text', size: (!arPropertyParams.COLS || isNaN(arPropertyParams.COLS)?'20':arPropertyParams.COLS), value: JS_stripslashes(val), name: propertyID, __exp: 'Y', onchange: fChange}))

				if (!oInput)
					break;
				if (arPropertyParams.TYPE == 'FILE')
				{
					oInput.className = 'fl';
					if (arPropertyParams.FD_USE_MEDIALIB && parseInt(arPropertyParams.FD_USE_MEDIALIB) > 0)
					{
						// Replace id, and increase "curCount"
						var html = window['_bxMlBrowseButton_' + propertyID.toLowerCase()];
						if(BX.browser.IsIE() && !this.bxAppendedCSSForML)
						{
							var
								s1 = html.indexOf('<' + 'style>'),
								s2 = html.indexOf('</' + 'style>'),
								css = html.substr(s1 + 7, s2 - s1 - 7);

							document.styleSheets[0].cssText += css;
							this.bxAppendedCSSForML = true;
						}

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

						cell.appendChild(BX.create("DIV", {props: {}, html: html}));

						xCell = BX("bx_fd_input_" + propertyID.toLowerCase());
						if (xCell)
							xCell.onclick = window['BX_FD_' + propertyID];
					}
					else
					{
						xCell = cell.appendChild(pMainObj.CreateElement("INPUT", {type: 'button', value: '...'}));
						xCell.onclick = window['BX_FD_' + propertyID];
					}

					// Result of selecting file
					if (isYes(arPropertyParams.REFRESH))
					{
						window['BX_FD_ONRESULT_' + propertyID] = function(filename, filepath)
						{
							if (typeof filename == 'object')
								oInput.value = filename.src; // From medialibrary
							else
								oInput.value = (filepath + "/" + filename).replace(/\/\//ig, '/'); // From old good file dialog
							fChange();
							_this.ShowProps(_bNew, pPropTsb, _pElement, true);
						}
					}
					else
					{
						window['BX_FD_ONRESULT_' + propertyID] =  function(filename, filepath)
						{
							if (typeof filename == 'object')
								oInput.value = filename.src; // From medialibrary
							else
								oInput.value = (filepath + "/" + filename).replace(/\/\//ig, '/'); // From old good file dialog
							fChange();
						};
					}

					if (isYes(arPropertyParams.REFRESH))
					{
						oInput.onblur = function()
						{
							fChange();
							_this.ShowProps(_bNew, pPropTsb, _pElement, true);
						}
					}
				}
				else if(arPropertyParams.TYPE == 'COLORPICKER')
				{
					var oCP = pMainObj.CreateCustomElement('BXEdColorPicker',
					{
						id : 'BackColor',
						title : arPropertyParams.NAME,
						OnChange : isYes(arPropertyParams.REFRESH) ?
						function (color) {oInput.value = color; fChange(); _this.ShowProps(_bNew, pPropTsb, _pElement, true);}
						:
						function (color){oInput.value = color.replace(/^#+/g, ""); fChange();}
					});
					xCell = cell.appendChild(oCP.pWnd);
					oInput.className = "compPropFloat";
					oCP.pWnd.className = "compPropFloat";
				}
				else if(arPropertyParams.TYPE == 'CUSTOM')
				{
					if (!arPropertyParams.JS_FILE || !arPropertyParams.JS_EVENT)
						break;
					var data = arPropertyParams.JS_DATA || '';
					oInput.style.display = "none";
					//var getComponentParamsElements = function(){return oTaskbar.GetPropFieldElements(pPropTsb);};
					var getFunction = function(arParams)
					{
						return function()
						{
							if (window[arParams.propertyParams.JS_EVENT])
								window[arParams.propertyParams.JS_EVENT](arParams);
						};
					};
					var oCallBack = getFunction({
						propertyID : propertyID,
						propertyParams: arPropertyParams,
						//getElements : getComponentParamsElements,
						getElements : function(){return oTaskbar.GetPropFieldElements(pPropTsb);},
						oInput : oInput,
						oCont : cell,
						data : data,
						fChange: fChange
					});
					BXLoadJSFiles([arPropertyParams.JS_FILE], {func: oCallBack, obj: {}}, true);
				}
			}
			break;
		}
		if(isYes(arPropertyParams.REFRESH) && !refreshedByOnclick)
		{
			xCell = cell.appendChild(pMainObj.CreateElement("INPUT", {type: 'button', value: 'ok', pMainObj: pMainObj,  'arPropertyParams': arPropertyParams}));
			xCell.onclick = function(){fChange(); _this.ShowProps(_bNew, pPropTsb, _pElement, true);};
		}

		// Tooltip
		if (oTaskbar.arCurrentTooltips[propertyID] && pMainObj.showTooltips4Components)
		{
			new BX.CHint(
			{
				hint: oTaskbar.arCurrentTooltips[propertyID],
				parent: cell.appendChild(BX.create("IMG", {props: {src: one_gif_src, className: "bx-comp2-hint"}}))
			});
		}
	}

	BXComponents2Taskbar.prototype.ParamsOnChange = function(P)
	{
		var
			pPropTsb = P.pPropTaskbar,
			arAllFields = oTaskbar.GetPropFieldElements(pPropTsb),
			arAllProps = P.arAllProps,
			arProps = {}, // Component parameters
			arProps_temp,
			l = pPropTsb.arElements.length,
			propID, i, j, val, k;

		if (!window.arVA)
			arVA = [];

		for(i = 0; i < l; i++)
		{
			propID = pPropTsb.arElements[i];
			val = arAllFields[propID];
			if(val && val.selectedIndex == 0 && arAllFields[propID + '_alt'])
				val = arAllFields[propID + '_alt'];

			if(propID.substr(0, 18) == "SEF_URL_TEMPLATES_")
			{
				if (oTaskbar.bFChangeSEFMode !== true)
					continue;

				if (val && val.value)
					_val = val.value;
				else if(arAllProps.paramvals[propID])
					_val = arAllProps.paramvals[propID];
				else
					continue;

				arVA[propID.substr(18)] = catchVariableAliases(_val);
			}

			if(!val)
			{
				if (arAllProps.paramvals[propID])
					arProps[propID] = arAllProps.paramvals[propID];
				continue;
			}

			if(val.tagName) // one element
			{
				if (propID == "SEF_MODE")
					oTaskbar.bFChangeSEFMode = isYes(val.value);

				if(val.tagName.toUpperCase() == "SELECT")
				{
					for(j=0; j<val.length; j++)
					{
						if(val[j].selected && val[j].value!='')
							arProps[propID] = val[j].value;
					}
				}
				else
				{
					arProps[propID] = val.value;
				}
			}
			else
			{
				arProps_temp = [];
				for(k = 0; k < val.length; k++)
				{
					if(val[k].tagName.toUpperCase() == "SELECT")
					{
						for(j = 0; j < val[k].length; j++)
							if(val[k][j].selected && val[k][j].value != '')
								arProps_temp.push(val[k][j].value);
					}
					else if (val[k].value.toString().length > 0)
					{
						arProps_temp.push(val[k].value);
					}
				}
				arProps[propID] = _BXArr2Str(arProps_temp);
			}
		}

		arAllProps.paramvals = arProps;
		oTaskbar.SetParams({id: P.id, params: arAllProps});
		// Rerender component with new params
		oTaskbar.C2Parser.ReRenderComponent(P.id, P.pElement);
	}

	BXComponents2Taskbar.prototype.BXShowComponent2Panel = function (_bNew, _pTaskbar, _pElement)
	{
		if (oTaskbar.bPropDialogOpened && _pTaskbar.pCellProps.className != 'c2dialog_propdiv')
			return;

		var
			id =_pElement.id,
			arAllProps = this.GetParams({id: id}),
			arProps = window.as_arComp2Params[arAllProps.name + arAllProps.__bx_id];

		if (!arProps)
			return;

		oTaskbar.bLoadProc = false;
		oTaskbar.bFChangeSEFMode = false;
		_pTaskbar.arElements = [];
		oTaskbar.arGroups = [];
		oTaskbar.arCurrentTooltips = arComp2Tooltips[arAllProps.name];
		oTaskbar.curParamvals = [];

		BX.cleanNode(_pTaskbar.pCellProps);

		var
			_this = this,
			row, cell, arPropertyParams, bSel, arValues, res, pSelect, arUsedValues, bFound, key, oOption, val, xCell, opt_val, bBr, i, k, alt,
			fChange = function(){_this.ParamsOnChange({id: id, pPropTaskbar: _pTaskbar, arAllProps: arAllProps, pElement: _pElement});},
			fDisplay = function(arProp, tProp, oCont){_this.ShowComponentParam({arProp: arProp, pPropTbl: tProp, oCont: oCont, pPropTaskbar: _pTaskbar, arAllProps: arAllProps, fChange: fChange, _bNew: _bNew, pElement: _pElement});};

		var BXCreateGroups = function(cn, oCont)
		{
			if (window.arComp2ParamsGroups[cn])
			{
				var
					groups = window.arComp2ParamsGroups[cn],
					key;
				for (key in groups)
				{
					oTaskbar.arGroups[key] =
					{
						title : groups[key],
						datacell : oTaskbar.GetPropGroupDataCell(key, groups[key], oCont, [arAllProps.name])
					};
				}
			}
		};

		//****** DISPLAY TITLE *******
		var
			compTitle = window.as_arComp2Elements[arAllProps.name].title,
			compDesc = window.as_arComp2Elements[arAllProps.name].params.DESCRIPTION,
			bComplex = isYes(window.as_arComp2Elements[arAllProps.name].complex),
			tCompTitle = _pTaskbar.pCellProps.appendChild(BX.create('TABLE', {props: {className : "componentTitle"}}));

		// Component title
		BX.adjust(tCompTitle.insertRow(-1).insertCell(-1), {props: {className: "titlecell"}, html: "<span class='title'>" + compTitle + "  (" + arAllProps.name + ")</span><br /><span class='description'>" + (bComplex ? BX_MESS.COMPLEX_COMPONENT : "") + compDesc+"</span>"});

		//DISPLAY COMPONENT TEMPLATE PARAMETERS
		if (arAllProps.template == undefined)
			arAllProps.template = "";

		var
			oOption, el, k, site_template, j, bSel,
			arTemplates = as_arComp2Templates[arAllProps.name],
			temlLen = arTemplates.length,
			oCont = _pTaskbar.pCellProps;

		if (temlLen > 0)
		{
			var pParDatacell = oTaskbar.GetPropGroupDataCell('templateParams', BX_MESS.COMPONENT_TEMPLATE, _pTaskbar.pCellProps, [arAllProps.name]);
			var tTProp = pParDatacell.appendChild(BX.create('TABLE', {props: {id: '__bx_tProp', className : "bxtaskbarprops"}}));

			var row = BX.adjust(tTProp.insertRow(-1), {props: {className: "bxtaskbarpropscomp"}});
			BX.adjust(row.insertCell(-1), {props: {className: 'bxtaskbarprop-label'}, html: '<label for="__bx_comp2templ_select">' + BX_MESS.COMPONENT_TEMPLATE + ':</label>'});

			var templList = row.insertCell(-1).appendChild(BX.create("SELECT", {props: {id: '__bx_comp2templ_select'}}));
			templList.onchange = function(e)
			{
				oTaskbar.DelTemplateParams(_pElement.id);
				var postData = oBXEditorUtils.ConvertArray2Post(oTaskbar.PrepareParamvals(arAllProps.paramvals), 'curval');
				oTaskbar.SetCompTemplate(_pElement, this.value);
				arAllProps = oTaskbar.GetParams({id: id});
				oTaskbar.loadTemplateParams(arAllProps.name, arAllProps.template, oTaskbar.ShowProps,oTaskbar, [_bNew, _pTaskbar, _pElement, false], "POST", postData);
			};

			//Displaying component template list
			for (j = 0; j < temlLen; j++)
			{
				el = arTemplates[j];
				site_template = '';
				if (el.template != '')
				{
					for (k in arBXTemplates)
					{
						if (arBXTemplates[k].value == el.template)
						{
							site_template = ' (' + arBXTemplates[k].name + ')';
							break;
						}
					}
				}
				else
				{
					site_template = ' ('+BX_MESS.BUILD_IN_TEMPLTE+')';
				}

				bSel = ((arAllProps.template == undefined && (el.name == ".default" || el.name=="")) || arAllProps.template == "" && el.name==".default" || arAllProps.template == el.name);
				templList.options.add(new Option(
					((el.title) ? el.title : el.name) + site_template, // text
					el.name, // value
					bSel, bSel)
				);
			}

			// Displaying component's template parameters
			var
				arTemplParams = window.as_arComp2TemplParams[arAllProps.name + arAllProps.__bx_id],
				cl = arTemplParams.length;

			BXCreateGroups(arAllProps.name, oCont);
			for (j = 0; j < cl; j++)
				fDisplay(arTemplParams[j], tTProp, oCont);
		}
		templList = oOption = null;

		var i, l = window.as_arComp2Groups[arAllProps.name + arAllProps.__bx_id].length, gr;
		for(i = 0; i < l; i++)
		{
			gr = window.as_arComp2Groups[arAllProps.name + arAllProps.__bx_id][i];
			oTaskbar.arGroups[gr.name] = {
				title: gr.title,
				datacell: oTaskbar.GetPropGroupDataCell(gr.name, gr.title, oCont, [arAllProps.name])
			};
		}

		//Displaying components params
		var
			templateID = _pTaskbar.pMainObj.templateID,
			oDiv = BXCreateElement('DIV', {}, {width: '100%', height: '0%'}, document),
			tProp = BXCreateElement('TABLE', {id: '__bx_tProp', className : 'bxtaskbarprops', cellSpacing: 0, cellPadding: 1}, {width: '100%'}, document);

		if (arProps && arProps.length > 0)
		{
			for(var i = 0, l = arProps.length; i < l; i++)
			{
				if (arProps[i].param_name == "SEF_FOLDER" && !arProps[i].DEFAULT)
					arProps[i].DEFAULT = (relPath!="/" ? relPath : "")+"/";
				fDisplay(arProps[i], tProp, oCont);
			}
		}

		arAllProps.paramvals = oTaskbar.curParamvals;
		oTaskbar.SetParams({id: id, params: arAllProps});

		// No params in component
		if (arProps.length == 0 && temlLen == 0)
			_pTaskbar.pCellProps.appendChild(BX.create('DIV', {style: {margin : "5px 0 0 22px"}, text: BX_MESS.CompNoParams}));
	}

	BXComponents2Taskbar.prototype.LoadComp2Params = function (arProps, calbackFunc, calbackObj, calbackParams, method, data)
	{
		this.arCachedElements = false;
		var
			elementName = arProps.name,
			templName = arProps.template || '',
			loadHelp = (this.pMainObj.showTooltips4Components) ? "Y" : "N";

		arComp2Tooltips[elementName] = [];
		window.arComp2Props = null;
		window.arComp2Templates = null;
		window.arComp2TemplateProps = null;

		function OnRequest()
		{
			try{
				setTimeout(function ()
					{
						var arProps = oTaskbar.GetParams({id: calbackParams[2].id});
						if (!arProps.__bx_id)
							arProps.__bx_id = push2Component2("__bx_c2_" + Math.random(), arProps.name);

						window.as_arComp2Params[elementName + arProps.__bx_id] = window.arComp2Props;
						window.as_arComp2Groups[elementName + arProps.__bx_id] = window.arComp2Groups;
						window.as_arComp2Templates[elementName] = window.arComp2Templates;
						window.as_arComp2TemplParams[elementName + arProps.__bx_id] = window.arComp2TemplateProps;

						if(calbackObj && calbackFunc)
							calbackFunc.apply(calbackObj, calbackParams ? calbackParams : []);
						else if(calbackFunc)
							calbackFunc();
					}, 50
				);
			}catch(e) {alert('Error >> LoadComp2Params');}
		}

		var url = '/bitrix/admin/fileman_load_comp2_params.php?lang=' + BXLang + '&site=' + BXSite + '&cname=' + elementName+'&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : '')+"&loadhelp="+loadHelp+'&tname=' + templName + '&bxsender=fileman_html_editor&bxeditor=' + this.pMainObj.name;

		this.pMainObj.__authFailureHandlerCallback = function(){oTaskbar.LoadComp2Params(arProps, calbackFunc, calbackObj, calbackParams, method, data);};
		this.pMainObj.__authFailureHandlerCallbackClose = function(){window.oBXEditorDialog.Close();};

		if (method == 'POST' && data)
			BX.ajax.post(url, data, OnRequest);
		else
			BX.ajax.get(url, {}, OnRequest);
	};

	//Set template
	BXComponents2Taskbar.prototype.SetCompTemplate = function(pEl, value)
	{
		var
			id = pEl.id,
			arProps = oTaskbar.GetParams({id: id});

		arProps.template = value || '';
		oTaskbar.SetParams({id: id, params: arProps});

		// Rerender component with new params
		oTaskbar.C2Parser.ReRenderComponent(id, pEl);
	}

	BXComponents2Taskbar.prototype.loadTemplateParams = function(componentName, templateName, calbackFunc, calbackObj, calbackParams, method, data)
	{
		var _CHttpRequest = new JCHttpRequest();
		_CHttpRequest.Action = function(result)
		{
			// try {
				setTimeout(function ()
					{
						//oTaskbar.pMainObj.GetBxTag(id);
						var arProps = oTaskbar.pMainObj.arComponents[calbackParams[2].id];
						window.as_arComp2TemplParams[componentName + arProps.__bx_id] = window.arComp2TemplateProps;
						window._bx_reload_template_props = false;

						if(calbackObj && calbackFunc)
							calbackFunc.apply(calbackObj, (calbackParams) ? calbackParams : []);
						else if(calbackFunc)
							calbackFunc();
					}, 10
				);
			// }catch(e) {alert('ERROR can\'t load template params...');}
		}

		if (method == 'POST' && data)
			_CHttpRequest.Post('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite + '&cname='+componentName+'&tname='+templateName+'&mode=params&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : ''),data);
		else
			_CHttpRequest.Send('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&cname='+componentName+'&tname='+templateName+'&mode=params&stid='+((this.pMainObj.templateID) ? this.pMainObj.templateID : ''));
	}

	BXComponents2Taskbar.prototype.DelTemplateParams = function(id)
	{
		var
			arAllProps = oTaskbar.GetParams({id: id}),
			arPropsVals = arAllProps.paramvals,
			len = window.arComp2TemplateProps.length,
			name, i;

		for (i = 0; i < len; i++)
		{
			name = window.arComp2TemplateProps[i].param_name;
			if (arPropsVals[name] != undefined)
				delete arPropsVals[name];
		}
		arAllProps.paramvals = arPropsVals;
		oTaskbar.SetParams({id: id, params: arAllProps});
		window.arComp2TemplateProps = [];
	}

	BXComponents2Taskbar.prototype.UnParseElement = function(node)
	{
		if (!window.arVA)
			window.arVA = [];

		var bxTag = oTaskbar.pMainObj.GetBxTag(node.arAttributes.id);
		if (!bxTag || bxTag.tag != 'component2')
			return false;

		var
			arAllProps = oTaskbar.GetParams({id: node.arAttributes["id"]}),
			arPropsVals = arAllProps.paramvals,
			res = "<?$APPLICATION->IncludeComponent(\n" +
				"\t\"" + arAllProps.name+"\",\n" +
				"\t\"" + (arAllProps.template ? arAllProps.template : "") + "\",\n";

		if (arPropsVals)
		{
			res += "\tArray(\n";
			var _len1 = "SEF_URL_TEMPLATES_".length;
			var _len2 = "VARIABLE_ALIASES_".length;
			var _SUT, _VA, lio, templ_key, var_fey, _count;
			var params_exist = false;
			var __val, __len;

			for (var i in arPropsVals)
			{
				try{
					if (!params_exist)
						params_exist = true;
					if (typeof(arPropsVals[i]) == 'function')
						continue;
					else if (typeof(arPropsVals[i]) == 'string')
						arPropsVals[i] = JS_stripslashes(arPropsVals[i]);
					else if (typeof(arPropsVals[i]) == 'object')
					{
						__val = 'array(';
						__len = 0;
						for (var _i in arPropsVals[i])
						{
							if (typeof(arPropsVals[i][_i]) == 'string')
							{
								__len++;
								__val += '"'+JS_stripslashes(arPropsVals[i][_i])+'",';
							}
						}
						if (__len > 0)
							__val = __val.substr(0,__val.length-1)+')';
						else
							__val += ')';

						arPropsVals[i] = __val;
					}

					if (isYes(arPropsVals["SEF_MODE"]))
					{
						//*** Handling SEF_URL_TEMPLATES in SEF = ON***
						if(i.substr(0,_len1) == "SEF_URL_TEMPLATES_")
						{
							_val = arPropsVals[i];
							__k = i.substr(_len1);
							arVA[__k] = catchVariableAliases(_val);

							if (!_SUT)
							{
								res += "\t\t\""+i.substr(0,_len1-1)+"\" => Array(\n"
								_SUT = true;
							}
							res += "\t\t\t\"" + i.substr(_len1) + "\" => ";
							if (isPHPBracket(arPropsVals[i]))
								res += trimPHPBracket(arPropsVals[i]);
							else
								res += "\"" + JS_addslashes(arPropsVals[i])+"\"";
							res += ",\n";
							continue;
						}
						else if (_SUT)
						{
							lio = res.lastIndexOf(",");
							res = res.substr(0,lio)+res.substr(lio+1);
							_SUT = false;
							res += "\t\t),\n";
						}

						//*** Handling  VARIABLE_ALIASES  in SEF = ON***
						if(i.substr(0,_len2)=="VARIABLE_ALIASES_")
							continue;

					}
					else if(arPropsVals["SEF_MODE"]=="N")
					{
						//*** Handling SEF_URL_TEMPLATES in SEF = OFF ***
						if (i.substr(0,_len1)=="SEF_URL_TEMPLATES_" || i=="SEF_FOLDER")
							continue;

						//*** Handling VARIABLE_ALIASES  in SEF = OFF ***
						if(i.substr(0,_len2)=="VARIABLE_ALIASES_")
						{
							if (!_VA)
							{
								res += "\t\t\""+i.substr(0,_len2-1)+"\" => Array(\n";
								_VA = true;
							}
							res += "\t\t\t\"" + i.substr(_len2) + "\" => \"" + JS_addslashes(arPropsVals[i]) + "\",\n";
							continue;
						}
						else if (_VA)
						{
							lio = res.lastIndexOf(",");
							res = res.substr(0,lio)+res.substr(lio+1);
							_VA = false;
							res += "\t\t),\n";
						}
					}

					res += "\t\t\""+i+"\" => ";
					if (isPHPBracket(arPropsVals[i]))
						res += trimPHPBracket(arPropsVals[i]);
					else if (arPropsVals[i].substr(0, 6).toLowerCase() == 'array(')
						res += arPropsVals[i];
					else
						res += '"' + JS_addslashes(arPropsVals[i]) + '"';
					res += ",\n";

				}catch(e){continue;}
			}

			if (_VA || _SUT)
			{
				lio = res.lastIndexOf(",");
				res = res.substr(0,lio)+res.substr(lio+1);
				_VA = false;
				_SUT = false;
				res += "\t\t),\n";
			}

			if (isYes(arPropsVals["SEF_MODE"]))
			{
				res += "\t\t\"VARIABLE_ALIASES\" => Array(\n";

				if (arVA)
				{
					for (templ_key in arVA)
					{
						if (typeof(arVA[templ_key]) != 'object')
							continue;
						res += "\t\t\t\""+templ_key+"\" => Array(";
						_count = 0;
						for (var_key in arVA[templ_key])
						{
							if (typeof(arVA[templ_key][var_key]) != 'string')
								continue;
							_count++;
							res += "\n\t\t\t\t\""+var_key+"\" => \""+arVA[templ_key][var_key]+"\",";
						}
						if (_count>0)
						{
							lio = res.lastIndexOf(",");
							res = res.substr(0,lio)+res.substr(lio+1);
							res += "\n\t\t\t),\n";
						}
						else
							res += "),\n";
					}
				}

				res += "\t\t),\n";
			}

			if (params_exist)
			{
				lio = res.lastIndexOf(",");
				res = res.substr(0,lio) + res.substr(lio+1);
			}
			res += "\t)";
		}
		else
		{
			res += "Array()"
		}

		if (arAllProps.parentComponent !== false || arAllProps.exParams !== false)
		{
			var pc = arAllProps.parentComponent;
			if (!pc || pc.toLowerCase() == '={false}')
			{
				res += ",\nfalse";
			}
			else
			{
				if (isPHPBracket(pc))
					res += ",\n" + trimPHPBracket(pc);
				else
					res += ",\n'" + pc + "'";
			}

			if (arAllProps.exParams !== false && typeof arAllProps.exParams == 'object')
			{
				res += ",\nArray(";
				for (var i in arAllProps.exParams)
				{
					if (typeof(arAllProps.exParams[i]) == 'string')
						res += "\n\t'" + i + "' => '" + JS_stripslashes(arAllProps.exParams[i]) + "',";
				}
				if (res.substr(res.length - 1) == ',')
					res = res.substr(0, res.length - 1) + "\n";
				res += ")";
			}
		}
		res += "\n);?>";
		if (window.lca)
		{
			var key = str_pad_left(++_$compLength, 4, '0');
			_$arComponents[key] = res;
			return '#COMPONENT'+String(key)+'#';
		}
		else
			return res;
	}

	BXComponents2Taskbar.prototype.GetPropGroupDataCell = function (name, title, oCont, arParams)
	{
		var _oTable = document.createElement('TABLE');
		_oTable.className = 'bxpropgroup';
		var rowTitle = _oTable.insertRow(-1);
		var c = rowTitle.insertCell(-1);
		c.style.width = '11px';
		c.appendChild(this.pMainObj.CreateElement("IMG", {src: one_gif_src, className: 'tskbr_common bx_btn_tabs_plus_big'}));
		c = rowTitle.insertCell(-1);
		c.style.width = '2000px';
		c.innerHTML = (title) ? BXReplaceSpaceByNbsp(title) : "";

		var rowData = _oTable.insertRow(-1);
		c = rowData.insertCell(-1);
		c.colSpan = 2;
		c.id = '__bxpropgroup_dc_' + name + oTaskbar.dialog_suffix;

		var compName = arParams[0];
		var _this = this;
		rowTitle.__bxhidden = false;
		rowTitle.id = '__bxpropgroup_tr_' + name + oTaskbar.dialog_suffix;
		rowTitle.className = "bxtskbrprp_title_d";
		rowTitle.onclick = function(){_this.HidePropGroup(name, !this.__bxhidden, [compName]);};
		oCont.appendChild(_oTable);

		if (!arComp2PropGroups[compName])
		{
			arComp2PropGroups[compName] = {};
			arComp2PropGroups[compName][name] = false;
			oTaskbar.HidePropGroup(name, false, arParams);
		}
		else
			oTaskbar.HidePropGroup(name, ((arComp2PropGroups[compName][name]===false) ? true : false), arParams);
		return c;
	}

	BXComponents2Taskbar.prototype.HidePropGroup = function (groupName, bHide, arParams)
	{
		if (!arParams)
			arParams = [];

		var compName = arParams[0];
		arComp2PropGroups[compName][groupName] = !bHide;

		var
			titleRow = BX('__bxpropgroup_tr_' + groupName + oTaskbar.dialog_suffix),
			dataCell = BX('__bxpropgroup_dc_' + groupName + oTaskbar.dialog_suffix);

		if (titleRow && dataCell)
		{
			if (bHide)
			{
				dataCell.style.display = GetDisplStr(0);
				titleRow.__bxhidden = true;
				titleRow.className = "bxtskbrprp_title_d";
				titleRow.cells[0].firstChild.className = 'tskbr_common bx_btn_tabs_plus_big';
			}
			else
			{
				dataCell.style.display = GetDisplStr(1);
				titleRow.__bxhidden = false;
				titleRow.className = "bxtskbrprp_title_a";
				titleRow.cells[0].firstChild.className = 'tskbr_common bx_btn_tabs_minus_big';
			}
		}
	}

	BXComponents2Taskbar.prototype.OnElementClick = function (oEl, arEl)
	{
		if (!this.pMainObj.oPropertiesTaskbar)
			return;

		if (!arEl.screenshots)
			arEl.screenshots = [];

		_pTaskbar = this.pMainObj.oPropertiesTaskbar;
		BX.cleanNode(_pTaskbar.pCellProps);

		//****** DISPLAY TITLE *******
		var compName = arEl.name;
		var compTitle = arEl.title;
		var compDesc = arEl.params.DESCRIPTION;
		var bComplex = isYes(arEl.complex);

		var tCompTitle = document.createElement("TABLE");
		tCompTitle.className = "componentTitle";
		var row = tCompTitle.insertRow(-1);
		var cell = row.insertCell(-1);
		cell.innerHTML = "<SPAN class='title'>"+compTitle+"  ("+compName+")</SPAN><BR /><SPAN class='description'>"+(bComplex ? BX_MESS.COMPLEX_COMPONENT : "")+compDesc+"</SPAN>";
		cell.className = "titlecell";
		cell.width = "100%";
		var _helpCell = row.insertCell(-1);
		_helpCell.className = "helpicon";

		_pTaskbar.pCellProps.appendChild(tCompTitle);
		var oDivSS;
		for (var i=0; i<arEl.screenshots.length; i++)
		{
			oDivSS = document.createElement("DIV");
			oDivSS.className = "scrshot";
			var imgSS = oTaskbar.pMainObj.CreateElement("IMG", {src: arEl.screenshots[i], title: compTitle, alt: compTitle});
			oDivSS.appendChild(imgSS);
			_pTaskbar.pCellProps.appendChild(oDivSS);
			oDivSS = null;
		}

		oDivSS = null;
		_helpCell = null;
		_helpicon = null;
		tCompTitle = null;
	}

	BXComponents2Taskbar.prototype.OnSaveLCA = function()
	{
		var sContent = this.pMainObj.GetContent();
		sContent = LCAContentParser(sContent, this.pMainObj, true);
		this.pMainObj.pValue.value = sContent;
	};

	BXComponents2Taskbar.prototype.PrepareParamvals = function(arParamvals)
	{
		var key, val;
		for (var key in arParamvals)
		{
			val = arParamvals[key];
			if (typeof val == 'function' || typeof val == 'object')
				continue;

			if (isPHPBracket(val))
			{
				val = trimPHPBracket(val);
				if (val.substr(0, 6).toLowerCase() == 'array(')
					arParamvals[key] = _BXStr2Arr(val, true);
			}
		}
		return arParamvals;
	};

	BXComponents2Taskbar.prototype.DialogInit = function()
	{
		window.arEditorFastDialogs.component2 = function(pObj)
		{
			var str = '<div id="bxc2_dialog_prop_div_' + oTaskbar.pMainObj.name + '" class="c2dialog_propdiv"></div>';

			var OnSave = function(t)
			{
				oTaskbar.SetParams({id: pObj.realComponentId, params: oTaskbar.GetParams({id: 'temp_c2_dialog_id'})});
				// Rerender component with new params
				if (pObj.pMainObj.bRenderComponents)
					oTaskbar.C2Parser.ReRenderComponent(pObj.realComponentId, pObj.params.pElement);
				else
					BXSelectRange(window.oPrevRange, pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);
				pObj.pMainObj.OnEvent("OnSelectionChange", ['always']);
			};

			var
				w = parseInt(window.comp2_dialog_size.width),
				h = parseInt(window.comp2_dialog_size.height);

			return {
				title: BX_MESS.Comp2Props,
				innerHTML : str,
				width: w,
				height: h,
				resizable: true,
				resize_id: 'bx_edc2_' + pObj.pMainObj.name,
				min_height: 200,
				min_width: 400,
				OnLoad: function()
				{
					var
						div = window.oBXEditorDialog.PARTS.CONTENT_DATA,
						id = pObj.params.pElement.id,
						pElement_temp = pObj.pMainObj.CreateElement('IMG', {id: 'temp_c2_dialog_id'});

					pObj.pMainObj.SetBxTag(false, {id: 'temp_c2_dialog_id', tag: 'component2'});
					pObj.pContDiv = BX('bxc2_dialog_prop_div_' + oTaskbar.pMainObj.name);

					BX.addClass(div, "c2dialog_cnt");
					pObj.pMainObj.pC2PropsDialog = pObj;
					oTaskbar.bPropDialogOpened = true;

					pObj.realComponentId = id;
					pObj.params.pElement_temp = pElement_temp;

					// Save cur params in temp object
					oTaskbar.SetParams({id: 'temp_c2_dialog_id', params: oTaskbar.GetParams({id: id})});

					// Build component properties
					oTaskbar.ShowProps(true, {pMainObj : pObj.pMainObj, pCellProps : pObj.pContDiv, bDialogMode : true}, pElement_temp, false);

					if (!pObj.pMainObj.bRenderComponents)
						window.oPrevRange = BXGetSelectionRange(pObj.pMainObj.pEditorDocument, pObj.pMainObj.pEditorWindow);

					window.oBXEditorDialog.SetButtons([
						new BX.CWindowButton(
						{
							title: BX_MESS.TBSave,
							className: 'adm-btn-save',
							action: function()
							{
								OnSave();
								window.oBXEditorDialog.Close();
							}
						}),
						window.oBXEditorDialog.btnCancel
					]);

					BX.addCustomEvent(window.oBXEditorDialog, 'onWindowUnRegister', function()
					{
						oTaskbar.bPropDialogOpened = false;
						if (window.oBXEditorDialog && window.oBXEditorDialog.DIV && window.oBXEditorDialog.DIV.parentNode)
							window.oBXEditorDialog.DIV.parentNode.removeChild(window.oBXEditorDialog.DIV);
					});
				}
			};
		}
	};
}

function C2Parser(pMainObj, C2Taskbar)
{
	this.pMainObj = pMainObj;
	this.C2Taskbar = C2Taskbar;

	this.arComponentsCSS = {};
	this.arComponentsSource = {};
	this.sCSS = '';
	this.bInited = false;

	if (pMainObj.bRenderComponents)
		this.InitRenderingSystem();
}

C2Parser.prototype =
{
	InitRenderingSystem : function()
	{
		this.bInited = true;
		this.sCSS = "\n"+
		".bxc2-block{border: 1px dotted #E4E4E4 !important; margin:2px !important;}\n" +
		".bxc2-block-selected{border: 1px solid #000 !important;}\n" +
		".bxc2-block *{-moz-user-select:none; cursor: default !important;}\n" +
		".bxc2-block-icon{padding: 0!important; margin:0!important;}\n" +
		".bxc2-cont-block{padding: 4px;}\n" +
		".bxc2-del{width: 21px; height: 18px; cursor: pointer !important; background: url(" + image_path + "/c2del.gif);}\n" +
		".bxc2-flip{width: 18px; height: 18px; cursor: pointer !important; background: url(" + image_path + "/c2flip.gif);}\n" +
		".bxc2-flop{width: 18px; height: 18px; cursor: pointer !important; background: url(" + image_path + "/c2flop.gif);}\n" +
		".bxc2-move{width: 12px; height: 18px; cursor: move !important; background: url(" + image_path + "/c2move.gif) 0 1px; padding: 0!important; margin:0!important;}\n" +
		".bxc2-block-tbl{width: 100%; height: 18px; background-color: #E4E4E4; border-collapse: collapse;}\n" +
		".bxc2-block-tbl td{padding: 0 0 0 0px; font-size: 13px; color: #404040; border-width: 0px !important; white-space: nowrap !important;}\n" +
		".bx-bogus-inp{width: 5px; position: absolute;}\n" +
		".bxc2-block-selected .bxc2-block-tbl td{font-weight: bold; color: #000; background-color: #C0C0C0;}\n";

		this.pMainObj.AddEventHandler("OnChangeView", this.COnChangeView, this);
		this.pMainObj.AddEventHandler("OnSelectionChange", this.COnSelectionChange, this);

		if (this.pMainObj.sEditorMode == 'html') // It's  always true at the start
			this.COnChangeView();
	},

	COnChangeView : function()
	{
		if (this.pMainObj.sEditorMode == 'html' || (this.pMainObj.sEditorMode == 'split' && this.pMainObj.sEditorSplitMode != 'code'))
		{
			var _this = this;
			window['COnKeyDown' + this.pMainObj.name] = function(e){_this.COnKeyDown(e);};
			window['COnMouseDown' + this.pMainObj.name] = function(e){_this.COnMouseDown(e);};
			window['COnDragEnd' + this.pMainObj.name] = function(e){_this.COnDragEnd(e);};

			if (!BX.browser.IsIE())
				addAdvEvent(this.pMainObj.pEditorDocument, 'dragdrop', window['COnDragEnd' + this.pMainObj.name]);

			addAdvEvent(this.pMainObj.pEditorDocument, 'keydown', window['COnKeyDown' + this.pMainObj.name]);
			addAdvEvent(this.pMainObj.pEditorDocument, 'mousedown', window['COnMouseDown' + this.pMainObj.name]);

			this.GetRenderedContent({bAllContent: true});
			this.pMainObj.pParser.AppendCSS(this.sCSS);
		}
		else
		{
			this.DeSelectComponent(false, false);
			if (this.pMainObj.oPropertiesTaskbar)
			{
				BX.cleanNode(this.pMainObj.oPropertiesTaskbar.pCellProps);
				this.pMainObj.oPropertiesTaskbar.OnSelectionChange('always', this.pMainObj.pEditorDocument.body);
			}
		}
	},

	COnMouseDown : function(e)
	{
		this.__bMouseDownComp = false;
		this.pMainObj.__bMouseDownComp = false;
		var
			bFlip = false,
			bDel = false,
			bMove = false,
			pElement, pElementTemp, tagName, cn;
		if (!e)
			e = this.pMainObj.pEditorWindow.event;
		if (e.target)
			pElement = e.target;
		else if (e.srcElement)
			pElement = e.srcElement;
		if (pElement.nodeType == 3) // defeat Safari bug
			pElement = pElement.parentNode;

		while(pElement && (pElementTemp = pElement.parentNode) != null)
		{
			if(pElementTemp.nodeType!=1 || !pElement.tagName)
			{
				pElement = pElementTemp;
				continue;
			}
			tagName = pElement.tagName.toLowerCase();
			cn = pElement.className;
			if (tagName == 'img' && cn.indexOf('bxc2-block-icon') != -1)
			{
				bDel = cn.indexOf('bxc2-del') != -1; // Delete
				bFlip = cn.indexOf('bxc2-flip') != -1; // Flip-flop
				bMove = cn.indexOf('bxc2-move') != -1; // Start drag
			}

			if(tagName == 'div' && cn.indexOf('bxc2-block') != -1)
			{
				if (bFlip)
				{
					this.FlipComponent(pElement);
				}

				if (bDel)
					this.DeleteComponent(pElement);
				if (bMove)
				{
					this.pDraggedElementId = pElement.id;
					break;
				}

				this.__bMouseDownComp = true;
				this.pMainObj.__bMouseDownComp = true;
				if (BX.browser.IsIE())
					this._IEClearStupidSelection(pElement);

				// Select component;
				this.SelectComponent(pElement);
				return BX.PreventDefault(e);
			}
			pElement = pElementTemp;
		}
		return true;
	},

	_IEClearStupidSelection : function(pEl)
	{
		try{
		var
			_this = this,
			id = pEl.id,
			pWin = this.pMainObj.pEditorWindow,
			pDoc = this.pMainObj.pEditorDocument;

		var dd = pDoc.getElementById('dd_toggle_' + id);
		if (dd && dd.parentNode)
		{
			var inp = this.pMainObj.CreateEditorElement("INPUT", {className: 'bx-bogus-inp', id: id + '_bogus_input'});
			dd.parentNode.insertBefore(inp, dd);
			setTimeout(function()
			{
				try{
					if (inp && inp.parentNode)
					{
						inp.focus();
						inp.parentNode.removeChild(inp);
					}
				}
				catch(e)
				{
					inp = _this.pMainObj.pEditorDocument.getElementById(id + '_bogus_input');
					if (inp)
					{
						inp.focus();
						inp.parentNode.removeChild(inp);
					}
				}
			}, 50);
		}
		}catch(e){};
	},

	COnDragEnd : function(e)
	{
		if (!this.pDraggedElementId)
			return;

		var
			o, par, bInside, pDel,
			id = this.pDraggedElementId,
			ddid = 'dd_toggle_' + id,
			_this = this;

		// Rerender component into the dragged toggle position
		setTimeout(function()
		{
			var
				arImgs = _this.pMainObj.pEditorDocument.getElementsByTagName('IMG'),
				el, i, l = arImgs.length;

			for (i = 0; i < l; i++)
			{
				el = arImgs[i];
				if (el && el.id == ddid)
				{
					// Check parent
					o = el;
					bInside = false;
					while(par = o.parentNode)
					{
						if(par.tagName && par.tagName.toUpperCase() == 'DIV' && par.className && par.className.indexOf('bxc2-block') != -1)
						{
							if (par.id != id) // Element inside other component
								pDel = el;
							bInside = true;
							break;
						}
						o = par;
					}

					if (!bInside)
						_this.MoveRenderedComponent(el, id);
				}
			}

			if (pDel)
				pDel.parentNode.removeChild(pDel);
		}, 5);
	},

	COnKeyDown : function(e)
	{
		var pElement, pElementTemp, tn, cn, _this = this;
		if (!e)
			e = this.pMainObj.pEditorWindow.event;
		if (e.target)
			pElement = e.target;
		else if (e.srcElement)
			pElement = e.srcElement;
		if (pElement.nodeType == 3)
			pElement = pElement.parentNode;

		if (this.lastSelectedComponent)
		{
			if (e.keyCode == 27) // Esc - deselect component
			{
				_this.DeSelectComponent(el);
			}
			else if (e.keyCode == 37) // left arrow
			{
				// Deselect and focus before
			}
			else if (e.keyCode == 39) // right arrow
			{
				// Deselect and focus after
			}
			else
			{
				var el = this.lastSelectedComponent;
				try{
					this.pMainObj.SelectElement(el);
				}catch(e){}
				setTimeout(function(){
					if (el && el.parentNode)
					{
						var tbl = el.getElementsByTagName('TABLE');
						if (tbl.length <= 0)
						{
							var innerHTML = el.innerHTML.toString();
							if (innerHTML.indexOf('>') == -1) // text content
							{
								var txt = _this.pMainObj.pEditorDocument.createTextNode(innerHTML);
								el.parentNode.insertBefore(txt, el);
							}
							_this.DeleteComponent(el);
						}
					}
				}, 1);
			}
		}

		while(pElement && (pElementTemp = pElement.parentNode) != null)
		{
			if(pElementTemp.nodeType!=1 || !pElement.tagName)
			{
				pElement = pElementTemp;
				continue;
			}
			tn = pElement.tagName.toLowerCase();
			cn = pElement.className;
			if(tn == 'div' && cn.indexOf('bxc2-block') != -1)
			{
				return BX.PreventDefault(e);
			}
			pElement = pElementTemp;
		}
	},

	COnSelectionChange : function(sReloadControl)
	{
		if (this.__bMouseDownComp || this.__bPreventComponentDeselect)
			return false;
		this.DeSelectComponent();
	},

	GetRenderedContent : function(P)
	{
		if (!this.pMainObj.bRenderComponents)
			return;

		var
			url = '/bitrix/admin/fileman_comp2_render.php?sessid=' + BX.bitrix_sessid() + '&site=' + BXSite,
			data = {stid: this.pMainObj.templateID || ''},
			_this = this;

		if (!P.bReRender)
			P.bReRender = false;

		if (P.bAllContent) // send all editor content with all components
		{
			data.source = this.pMainObj.GetContent();
		}
		else if (P.name) // get rendered content of the some component
		{
			data.name = P.name;
			if (P.template)
				data.template = P.template;
			// Get params
			if (P.paramvals)
				data.params = this.C2Taskbar.PrepareParamvals(P.paramvals);
		}

		var calbackFunc = function(result)
		{
			if (_this.pMainObj.IsSessionExpired(result))
			{
				if (!P.bReqestReply)
				{
					P.bReqestReply = true;
					_this.GetRenderedContent(P);
				}
				else
				{
					alert('Error: Session expired');
				}
			}

			var s = '';
			//try{
			setTimeout(function ()
			{
				if (result.length <= 0)
					result  = '#BX_RENDERED_COMPONENT##BX_RENDERED_COMPONENT#';

				if (window.arUsedCSS && window.arUsedCSS.length > 0)
				{
					_this.LoadComponentCSS(window.arUsedCSS);
					window.arUsedCSS = null;
				}

				if(P.bAllContent)
				{
					var
						_arC2RenderedContent = [],
						c2Name, c2List, i, l, id, ind, oEl;
					result.replace(/(#BX_RENDERED_COMPONENT_(\d+?)#)((?:\S|\s)*?)\1/ig, function(s, b1, b2, b3){_arC2RenderedContent.push(b3); return '';});

					// Rendering components in array
					for (c2Name in arComponents2)
					{
						c2List = arComponents2[c2Name];
						l = c2List.length;
						if (typeof c2List != 'object' || !c2List.length)
							continue;

						for (i = 0; i < l; i++)
						{
							id = c2List[i].id;
							ind = c2List[i].ind;

							oEl = _this.pMainObj.pEditorDocument.getElementById(id);
							s = _arC2RenderedContent[ind];
							_this.RenderComponent(id, s, false, oEl);
						}
					}
				}
				else if (P.name && P.pElement)
				{
					if (result.indexOf('#BX_RENDERED_COMPONENT#') != -1)
					{
						s = result.replace(/(#BX_RENDERED_COMPONENT#)((?:\S|\s)*?)\1/ig, "$2");
						s = s.replace("#BX_RENDERED_COMPONENT#", "");
					}
					oEl = _this.RenderComponent(P.pElement.id, s, P.bReRender, P.pElement);

					if (P.bSelect && oEl)
					{
						// Only for FF: Clear white markers after component's icon replacing
						if (!BX.browser.IsIE())
							_this.pMainObj.pEditorDocument.execCommand('RemoveFormat', false, null);
						_this.SelectComponent(oEl);
					}
				}
			}, 10);
			//}catch(e) {alert('Error >> RenderComponents');}
		}

		BX.ajax.post(url, data, calbackFunc);
	},

	RenderComponent : function(id, source, bReRender, repEl)
	{
		this.arComponentsSource[id] = source;

		if (typeof source != 'string' || source.trim().length <= 0) // Component return empty result
		{
			if (bReRender)
				source = BX_MESS.Comp2EmptyResult;
			else
				return this.StopWaiting(id, repEl);
		}

		var
			_this = this,
			pContentBlock = false, pHeader,
			title = BX_MESS.DefComp2Title,
			oEl = repEl || this.pMainObj.pEditorDocument.getElementById(id);

		if (!oEl)
			return false;

		if (bReRender)
		{
			var
				arDivs = oEl.getElementsByTagName('DIV'),
				pBlock = oEl,
				i, l = arDivs.length,
				arCh = pBlock.childNodes,
				node, l2 = arCh.length;

			pBlock.style.width = null;

			for (i = 0; i < l; i++)
			{
				if (arDivs[i].className == 'bxc2-cont-block')
				{
					pContentBlock = arDivs[i];
					break;
				}
			}

			for (i = 0; i < l2; i++)
			{
				node = arCh[i];
				if (node.nodeType == 1 && node.nodeName.toUpperCase() == 'TABLE' && node.className == 'bxc2-block-tbl')
				{
					pHeader = node;
					break;
				}
			}

			if (!pContentBlock)
				return this.RenderComponent(id, source, false);
		}
		else
		{
			if (this.pMainObj.arComponents[id] && this.pMainObj.arComponents[id].name && window.as_arComp2Elements[this.pMainObj.arComponents[id].name])
				title = window.as_arComp2Elements[this.pMainObj.arComponents[id].name].title;

			var pBlock = this.pMainObj.CreateEditorElement("DIV", {id: id, className: 'bxc2-block'});
			pHeader = this.pMainObj.CreateEditorElement("TABLE", {className: 'bxc2-block-tbl'});

			var r = pHeader.insertRow(-1);
			var c0 = r.insertCell(-1); // move
			c0.style.width = '18px';
			var pMoveIcon = c0.appendChild(this.pMainObj.CreateEditorElement("IMG", {id: 'dd_toggle_' + id, src: one_gif_src, className: 'bxc2-block-icon bxc2-move'}));
			pMoveIcon.title = BX_MESS.MoveComponent;

			var c1 = r.insertCell(-1); // title
			c1.className = 'bxc2-block-title';
			c1.innerHTML = BX_MESS.Comp2Name + ': ' + title;

			var c2 = r.insertCell(-1); // Buttons block
			c2.style.textAlign = 'right';
			//var pPropIcon = c2.appendChild(this.pMainObj.CreateEditorElement("IMG", {src: one_gif_src, className: 'bxc2-block-icon bxc2-prop'}));
			c2.appendChild(this.pMainObj.CreateEditorElement("IMG", {src: one_gif_src, className: 'bxc2-block-icon bxc2-flip', title: BX_MESS.FlipComponent}));

			c2.appendChild(this.pMainObj.CreateEditorElement("IMG", {src: one_gif_src, className: 'bxc2-block-icon bxc2-del', title: BX_MESS.DelComponent}));

			pBlock.appendChild(pHeader);
			pContentBlock = pBlock.appendChild(this.pMainObj.CreateEditorElement("DIV", {className: 'bxc2-cont-block'}));

			oEl.parentNode.insertBefore(pBlock, oEl); // Insert rendered block
			oEl.parentNode.removeChild(oEl); // Remove yelow pill

			this.pMainObj.nLastDragNDropElement = false;
			pBlock.style.MozUserSelect = 'none'; // For mozilla

			if (BX.browser.IsIE())
			{
				pMoveIcon.ondragend = window['COnDragEnd' + this.pMainObj.name];
				pBlock.ondragend = function(){_this._IEpBlockOnDragEnd(id);};
			}
		}

		try
		{
			pContentBlock.innerHTML = source;
		}
		catch(e) //IE BUG WORKAROUND:  "Unknown runtime error" when using innerHTML
		{
			var _p = this.pMainObj.CreateEditorElement("DIV", {className: 'bxc2-cont-block'});
			_p.innerHTML = source;
			pContentBlock.parentNode.insertBefore(_p, pContentBlock);
			pContentBlock.parentNode.removeChild(pContentBlock); //
			pContentBlock = _p;
		}
		this.arComponentsSource[id] = source;

		// Set width correct width depending on content
		this.ResizeAfterRendering(pBlock, pContentBlock, pHeader);
		return pBlock;
	},

	_IEpBlockOnDragEnd : function(id)
	{
		var _this = this;
		setTimeout(function()
		{
			var
				oEl = _this.pMainObj.pEditorDocument.getElementById(id),
				otherComp = BXFindParentByTagName(oEl, 'DIV', 'bxc2-block');

			if (otherComp) // Component dragget into another
			{
				var tmpIcon = _this.pMainObj.CreateEditorElement("IMG", {src: one_gif_src}); // Create simple image
				otherComp.parentNode.insertBefore(tmpIcon, otherComp); // Put element before parent component
				_this.MoveRenderedComponent(tmpIcon, id);
			}
			else //Rerender content after system dragging
			{
				_this.RenderComponent(id, _this.arComponentsSource[id], oEl);
			}
		}, 100);

	},

	StartWaiting: function(pIcon)
	{
		if (src = pIcon.getAttribute('src'))
			this.pMainObj.Add2BxTag(pIcon, {'_src': src});
		pIcon.src = c2wait_path;
	},

	StopWaiting : function(id, repEl)
	{
		var pEl = repEl || this.pMainObj.pEditorDocument.getElementById(id);
		if (pEl && pEl.nodeName.toLowerCase() == 'img')
		{
			var oTag = this.pMainObj.GetBxTag(pEl);
			if (oTag.params && oTag.params._src)
				pEl.src = oTag.params._src;
		}
		return pEl;
	},

	ReRenderComponent : function(id, pEl)
	{
		if (this.pMainObj.bRenderComponents)
		{
			if (this.C2Taskbar.bPropDialogOpened) // We rerender component later - when close dialog
				return;

			if (!pEl)
				pEl = this.pMainObj.pEditorDocument.getElementById(id);

			var props = this.pMainObj.arComponents[id];
			this.GetRenderedContent({name: props.name, pElement: pEl, template: props.template || '', paramvals: props.paramvals || false, bSelect: true, bReRender: true});
		}
	},

	ResizeAfterRendering : function(pBlock, pContentBlock, pHeader)
	{
		setTimeout(function()
		{
			var
				blockWidth = parseInt(pBlock.offsetWidth) - 20,
				arCh = pContentBlock.childNodes,
				maxWidth = 0,
				node, w, i, l = arCh.length;

			for (i = 0; i < l; i++) // For each child in content block
			{
				node = arCh[i];
				if (node && node.nodeType == 1) // If it's html element
				{
					w = parseInt(node.offsetWidth);
					if (!isNaN(w) && w > maxWidth)
						maxWidth = w;
				}
			}

			if (maxWidth > 0 && (blockWidth - maxWidth) > 20)
				pBlock.style.width = (maxWidth + 20) + 'px';

			if (maxWidth > 8)
				pContentBlock.style.width = (maxWidth - 8) + 'px';

			setTimeout(function()
			{
				if (pHeader)
				{
					var headerWidth = parseInt(pHeader.offsetWidth);
					if (!isNaN(headerWidth) && headerWidth > maxWidth)
						pBlock.style.width = (headerWidth) + 'px';
						//pBlock.style.width = (headerWidth + 20) + 'px';

					if (headerWidth > 8)
						pContentBlock.style.width = (headerWidth - 8) + 'px';
				}
			}, 20);

		}, 300);
	},

	MoveRenderedComponent : function(el, id)
	{
		var pEl = this.pMainObj.pEditorDocument.getElementById(id);
		pEl.parentNode.removeChild(pEl);
		this.RenderComponent(id, this.arComponentsSource[id], false, el);
	},

	SelectComponent : function(pEl)
	{
		//try{
		if (this.lastSelectedComponent)
		{
			if (pEl.id == this.lastSelectedComponent.id) // already selected
				return;
			this.DeSelectComponent(false, false);
		}

		if (pEl.nodeName.toLowerCase() != 'img')
			pEl.className = 'bxc2-block bxc2-block-selected';
		this.lastSelectedComponent = pEl;
		this.pMainObj.SetFocus();

		var _this = this;
		setTimeout(function()
		{
			_this.pMainObj.oPropertiesTaskbar.OnSelectionChange('always', pEl);
			_this.__bPreventComponentDeselect = false;
		}, 250);
		setTimeout(function(){_this.__bMouseDownComp = false;}, 500);
		setTimeout(function(){_this.pMainObj.__bMouseDownComp = false;}, 500);
		//}catch(e){}
	},

	DeSelectComponent : function(pEl, bCleanPropTaskbar)
	{
		try{ // For IE permission denied stupid errors
		if (!pEl)
			pEl = this.lastSelectedComponent;
		if (!pEl || !pEl.nodeName)
			return true;
		if (pEl.nodeName.toLowerCase() != 'img')
			pEl.className = 'bxc2-block';
		this.lastSelectedComponent = false;
		SETTINGS[obj.pMainObj.name].arTaskbarSettings['BXPropertiesTaskbar'].show = true; // hack
		if (bCleanPropTaskbar !== false)
			this.pMainObj.oPropertiesTaskbar.OnSelectionChange('always');
		}catch(e){}
	},

	FlipComponent : function(pEl)
	{
		var pContentBlock = BX.findChild(pEl, {tagName: 'DIV', className: 'bxc2-cont-block'}, false);
		if (!pContentBlock)
			return;

		var pBut = BX.findChild(pEl, {tagName: 'IMG', className: 'bxc2-flip'}, true);
		if (pContentBlock.style.display == 'none')
		{
			pContentBlock.style.display = 'block';
			if (pBut)
			{
				BX.removeClass(pBut, 'bxc2-flop');
				pBut.title = BX_MESS.FlipComponent;
			}
		}
		else
		{
			pContentBlock.style.display = 'none';
			if (pBut)
			{
				BX.addClass(pBut, 'bxc2-flop');
				pBut.title = BX_MESS.FlopComponent;
			}
		}
	},

	DeleteComponent : function(pEl)
	{
		// TODO: Del from array
		var id = pEl.id;
		//this.arComponents[id] = null;
		//this.arShadowedControls[id] = null;
		pEl.parentNode.removeChild(pEl);
		this.lastSelectedComponent = false;
	},

	LoadComponentCSS : function(arCSS)
	{
		var
			i, l = arCSS.length, css,
			_this = this, cssReq;

		for (i = 0; i < l; i++)
		{
			css = arCSS[i];
			if (this.arComponentsCSS[css])
				continue;

			this.arComponentsCSS[css] = css;
			cssReq = new JCHttpRequest();
			cssReq.Action = function(styles){
				var path2css = css.substr(0, css.lastIndexOf('/'));
				// Replace relative urls to absolute
				styles = styles.replace(/url\((?:"|\'*)([^\/]{1}.*?)(?:"|\'*)\)/ig, "url(\"" + path2css + "/" + "$1" + "\")");
				setTimeout(function(){_this.AddCSSToEditorFrame(styles);}, 10);
			};
			cssReq.Send(css + '?v=s' + parseInt(Math.random() * 100000)); // Request css file
		}
	},

	AddCSSToEditorFrame : function(styles)
	{
		if (styles.toLowerCase().indexOf('</html>') != -1) // Return if it's html page
			return false;
		this.sCSS += styles + "\n";
		this.pMainObj.pParser.AppendCSS(styles);
	}
}



function BXCheckForComponent2(_str, pMainObj, bLCA_mode)
{
	if (lca && _$lca_only && !bLCA_mode) // for LCA mode - components already parsed
		return false;

	_str = oBXEditorUtils.PHPParser.trimPHPTags(_str);
	_str = oBXEditorUtils.PHPParser.cleanCode(_str);

	var _oFunc = oBXEditorUtils.PHPParser.parseFunction(_str);
	if (!_oFunc)
		return false;

	if (_oFunc.name.toUpperCase() == '$APPLICATION->INCLUDECOMPONENT')
	{
		var
			arParams = oBXEditorUtils.PHPParser.parseParameters(_oFunc.params)
			name = arParams[0],
			template = arParams[1] || "",
			params = arParams[2] || {},
			parentComponent = (arParams[3] && arParams[3] != '={false}') ? arParams[3] : false,
			exParams = arParams[4] || false;

		for (var key in params)
			if (typeof params[key] == 'object')
				params[key] = _BXArr2Str(params[key]);

		//try{
			var
				comProps = window.as_arComp2Elements[name],
				icon = (comProps.icon) ? comProps.icon : '/bitrix/images/fileman/htmledit2/component.gif',
				tagname = (comProps.tagname) ? comProps.tagname : 'component2',
				allParams = copyObj(comProps.params);

			allParams.name = name;
			allParams.template = template;
			allParams.parentComponent = parentComponent;
			allParams.exParams = exParams;

			//Handling SEF_URL_TEMPLATES
			if (params["SEF_URL_TEMPLATES"])
			{
				var _str = params["SEF_URL_TEMPLATES"];
				var arSUT = oBXEditorUtils.PHPParser.getArray((_str.substr(0,8).toLowerCase() == "={array(") ? _str.substr(2,_str.length-3) : _str);

				for (var _key in arSUT)
					params["SEF_URL_TEMPLATES_"+_key] = arSUT[_key];

				delete params["SEF_URL_TEMPLATES"];
			}

			if (params["VARIABLE_ALIASES"])
			{
				if (params["SEF_MODE"]=="N")
				{
					var _str = params["VARIABLE_ALIASES"];
					var _arVA = oBXEditorUtils.PHPParser.getArray((_str.substr(0,8).toLowerCase() == "={array(") ? _str.substr(2,_str.length-3) : _str);

					for (var _key in _arVA)
						params["VARIABLE_ALIASES_"+_key] = _arVA[_key];
				}
				delete params["VARIABLE_ALIASES"];
			}

			allParams.paramvals = params;
			var bTagParams = {};
			if (pMainObj.bRenderComponents)
			{
				bTagParams._src = icon;
				icon = c2wait_path;
			}

			var id = pMainObj.SetBxTag(false, {tag: tagname, params: bTagParams});
			allParams.__bx_id = push2Component2(id, allParams.name); // Used to cache component-params for each component

			if (!pMainObj.arComponents)
				pMainObj.arComponents = {};
			pMainObj.arComponents[id] = allParams;

			return '<img style="cursor: default;" id="' + id + '" src="' + icon + '" />';
		//}catch(e) {}
	}
	return false;
}


function LCAContentParser(str, pMainObj, returnCode)
{
	returnCode = (returnCode === true);
	var replaceLCA = function(str, key)
	{
		var cCode = _$arComponents[key]; // Code of component: $APLICATION->IncludeComponent( .........
		if (!cCode)
			return '';
		if (returnCode)
			return cCode;
		return BXCheckForComponent2(cCode, pMainObj, true);
	};
	str = str.replace(/#COMPONENT(\d{4})#/ig, replaceLCA);
	_$LCAContentParser_execed = true;
	return str;
}

if (window.lca) //limit component access
	oBXEditorUtils.addContentParser(LCAContentParser);
oBXEditorUtils.addPHPParser(BXCheckForComponent2, 0, true);

function checkComp2Template(pMainObj)
{
	var compList, len, i, compName, arCompNames = [];
	for (compName in arComponents2)
		arCompNames.push(compName);

	var postData = oBXEditorUtils.ConvertArray2Post(arCompNames, 'complist');
	var params = [];
	loadComp2TemplateLists(pMainObj.templateID, __checkComp2Template, [pMainObj], "POST",postData);
}

function __checkComp2Template(params)
{
	var pMainObj = params[0];

	var template, oEl, allParams, name;
	for (compName in arComponents2)
	{
		compList = arComponents2[compName];
		len = compList.length;

		for (i =0; i < len; i++)
		{
			id = compList[i].id;

			oEl = pMainObj.pEditorDocument.getElementById(id);
			if (!oEl)
				continue;

			allParams = pMainObj.pComponent2Taskbar.GetParams({id: id});
			template = allParams.template;
			name = allParams.name;

			if (!arComp2TemplateLists[name][template] || ((template=="" || template==".default") && !(arComp2TemplateLists[name][''] || arComp2TemplateLists[name]['.default'])))
			{
				allParams.template = arComp2TemplateLists[name][''] ? "" : ".default";
				pMainObj.SetBxTag(oEl, {id: oEl.id, params: allParams});
			}
			as_arComp2Templates[name] = [];
			for (__i in arComp2TemplateLists[name])
				as_arComp2Templates[name].push(arComp2TemplateLists[name][__i]);

			window._bx_reload_template_props = true;
		}
	}
	setTimeout(function (){pMainObj.OnEvent("OnSelectionChange");}, 5);
}

function loadComp2TemplateLists(siteTemplate,calbackFunc, calbackParams,method,data)
{
	var _CHttpRequest = new JCHttpRequest();
	_CHttpRequest.Action = function(result)
	{
		try
		{
			setTimeout(function ()
				{
					if(calbackFunc)
						calbackFunc(calbackParams);
				}, 5
			);
		}
		catch(e)
		{
			alert('ERROR can\'t load template params...');
		}
	}
	if (method == 'POST' && data)
		_CHttpRequest.Post('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&mode=list&stid='+siteTemplate,data);
	else
		_CHttpRequest.Send('/bitrix/admin/fileman_load_templates.php?lang='+BXLang+'&site='+BXSite+'&mode=list&stid='+siteTemplate);
}

function push2Component2(id, name)
{
	if (!arComponents2[name])
		arComponents2[name] = [];

	arComponents2[name].push({'id':id, ind: arComponents2Length});
	arComponents2Length++;
	return arComponents2[name].length;
}

function _BXArr2Str(arObj)
{
	try
	{
		var _arObj = [];
		var str = 'array(';
		for (var _key in arObj)
		{
			if (typeof(arObj[_key]) == 'function')
				continue;
			if (parseInt(_key).toString()=='NaN')
				_arObj.push('"'+_key+'" => "'+arObj[_key]+'"');
			else
				_arObj[_key] = '"'+arObj[_key]+'"';
		}

		str += _arObj.join(", ");
		str += ')';
	}
	catch(e)
	{
		str = 'array()';
	}
	return str;
}

function _BXStr2Arr(str, bSkipEmpty)
{
	var
		arObj = oBXEditorUtils.PHPParser.getArray(str),
		res = {}, i;

	for (i in arObj)
	{
		if (i !== "" && typeof arObj[i] != 'function' && (arObj[i] !== '' || !bSkipEmpty))
			res[i] = arObj[i];
	}
	return res;
}

function catchVariableAliases(str)
{
	var arRes = [];
	var res = str.match(/(\?|&)(.+?)=#([^#]+?)#/ig);
	if (!res)
		return arRes;

	for (var l=0;l<res.length; l++)
	{
		var _res = res[l].match(/(\?|&)(.+?)=#([^#]+?)#/i);
		arRes[_res[3]] = _res[2];
	}
	return arRes;
}

oBXEditorUtils.addTaskBar('BXComponents2Taskbar', 2, BX_MESS.CompTBTitle, [], 10);