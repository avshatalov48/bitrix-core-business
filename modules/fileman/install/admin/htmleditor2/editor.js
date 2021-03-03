// ========================
var editor_js = true;
// ========================
//pMainObj - name of MAIN object which contains all editor's methods. Used for coordinashion between different components and objects
//	pEditorFrame - link to IFRAME for visual editing
//	pFrame - link to table with editor
//	pDocument - parent document
//	pEditorDocument - document of edited file

function BXHTMLEditor(name, start_func)
{
	GLOBAL_pMainObj[name] = this;
	name_cur_obj = name;
	this.start_func = (start_func) ? start_func : function(){};
	this.pMainObj = this;
	this.arBarHandlersCache = [];
	this.name = name;
	this.showTooltips4Components = true;
	this.visualEffects = true;
	this.arUndoBuffer = [];
	this.SessionLostStr = 'BX_EDITOR_ERROR_SESSION_EXPIRED';
	this.iUndoPos = -1;
	this.sOnChangeLastType = '';
	this.customToolbars = true;
	this.bDotNet = window.bDotNet || false;
	this.limit_php_access = limit_php_access; // Limit php access
	this.lastCursorId = 'bx-editor-cursor-id';
	this.bxTags = {};

	this.bLoadFinish = false;
	this.isSubmited = false;
	// *** Limit component access (LCA) ***
	if(window.lca)
	{
		_$lca_only = false;
		_$arComponents = window._$arComponents || false;
		_$lca_to_output = _$arComponents ? true : false;
	}

	this.fullEdit = (this.name == 'CONTENT'); // For template edit
	this.sOnChangeLastSubType = '';
	this.sLastContent = '';
	this.bSkipChanges = false;
	this.sFirstContent = null;
	if(BXEditorLoaded)
		this.OnBeforeLoad();
	else
		BXEditorRegister(this);
}

BXHTMLEditor.prototype.CreateElement = BXCreateElement;

BXHTMLEditor.prototype.OnBeforeLoad = function()
{
	this.allowedTaskbars = window['ar_' + this.name + '_taskbars'];
	this.BXPreloader = new BXPreloader(
		[
			{func: BX.proxy(this.GetConfig, this), params: []},
			{obj: this, func: this.PreloadTaskbarsData}
		],
		{
			obj : this,
			func: this.OnLoad
		}
	);
	this.BXPreloader.LoadStep();
};


BXHTMLEditor.prototype.PreloadTaskbarsData = function(oCallBack)
{
	var arTsbSet = SETTINGS[this.name].arTaskbarSettings;
	try{
		if (this.bDotNet)
		{
			var bShow = !arTsbSet || !arTsbSet['ASPXComponentsTaskbar'] || arTsbSet['ASPXComponentsTaskbar'].show;
			if (this.allowedTaskbars['ASPXComponentsTaskbar'] && bShow)
				this.BXPreloader.AddStep({obj: this, func: this.LoadASPXComponents});
		}
		else
		{
			var settings = false;
			if (arTsbSet)
				settings =  arTsbSet['BXComponents2Taskbar'];

			if (this.allowedTaskbars['BXComponents2Taskbar'] && (!settings || settings.show))
				this.BXPreloader.AddStep({obj: this, func: this.LoadComponents2});
		}
	}catch(e){_alert(this.name+': ERROR:  pMainObj.PreloadTaskbarsData');}

	oCallBack.func.apply(oCallBack.obj);
};

BXHTMLEditor.prototype.OnLoad = function()
{
	//try{
	var obj = this;
	this.bShowed = true;
	this.bDragging = false;
	this.bNotSaved = false;
	this.bFirstClick = false;
	this.className = 'BXHTMLEditor';
	this.arEventHandlers = [];
	this.pDocument = document;
	this.bTableBorder = false;
	this.pWnd = BX(this.name + '_object');
	this.pValue = BX('bxed_' + this.name);
	this.arToolbarSet = [];
	this.toolArea = [];
	this.arTaskbarSet = [];
	this.pParser = new BXParser(this);
	this.bEditSource = false;
	this.arConfig = window['ar_' + this.name + '_config'];
	this.bRenderComponents = this.arConfig.renderComponents;
	this.bRenderStyleList = styleList_render_style;

	if (!this.pWnd || !BX.isNodeInDom(this.pWnd))
	{
		BX.closeWait();
		return;
	}

	this.bodyParams = ""; // Used to add some css for body
	if (this.arConfig.body_class)
		this.bodyParams += ' class="' + this.arConfig.body_class + '"';
	if (this.arConfig.body_id)
		this.bodyParams += ' id="' + this.arConfig.body_id + '"';

	if (BX.WindowManager)
	{
		BX.WindowManager.setStartZIndex(2010);
		BX.WindowManager.disableKeyCheck();
	}

	this.oTransOverlay = new BXTransOverlay({edId: this.name});

	this.fullEditMode = window.fullEditMode || false;

	this.pParser.ClearHBF(); // Init HBF
	window.CACHE_DISPATCHER = []; // GLOBAL CACHE
	if (this.arConfig.sBackUrl)
		this.arConfig.sBackUrl = this.arConfig.sBackUrl.replace(/&amp;/gi, '&');
	if (this.OnLoad_ex)
		this.OnLoad_ex();
	// ******** List of entities to replace **********
	if (this.arConfig["ar_entities"].toString() == '')
		this.arConfig["ar_entities"] = [];
	else
		this.arConfig["ar_entities"] = this.arConfig["ar_entities"].toString().split(',');

	var arAllEntities = {}, k;
	arAllEntities['umlya'] = ['&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;'];
	arAllEntities['greek'] = ['&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;'];
	arAllEntities['other'] = ['&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&bull;','&hellip;','&prime;','&Prime;','&oline;','&frasl;','&weierp;','&image;','&real;','&trade;','&alefsym;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&lArr;','&uArr;','&rArr;','&dArr;','&hArr;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&loz;','&spades;','&clubs;','&hearts;','&diams;'];

	this.arEntities = [];
	for(k in this.arConfig["ar_entities"])
	{
		if(arAllEntities[this.arConfig["ar_entities"][k]])
			this.arEntities = this.arEntities.concat(arAllEntities[this.arConfig["ar_entities"][k]]);
	}
	this.arEntities_h = BX.create("span", {html: this.arEntities.join(',')}).innerHTML.split(',');

	this.arConfig.undosize = this.arConfig.undosize || 25;
	this.arConfig.width = this.arConfig.width || "750";

	this.pWnd.style.width = parseInt(this.arConfig.width) + (this.arConfig.width.indexOf('%') == -1 ? "px" : '%');
	this.arConfig.height = this.arConfig.height || "500";
	this.pWnd.style.height = parseInt(this.arConfig.height) + (this.arConfig.height.indexOf('%') == -1 ? "px" : '%');

	this.arToolbars = this.arConfig.arToolbars || ["standart", "style", "formating", "source", "template"];

	if(this.arConfig["customToolbars"])
		this.customToolbars = this.arConfig["customToolbars"];

	this.pForm = BXFindParentByTagName(this.pWnd, "FORM");
	if(this.pForm)
		addAdvEvent(this.pForm, 'submit', window['OnSubmit_' + this.name]);

	BX.addCustomEvent(window, "OnHtmlEditorRequestAuthFailure", BX.proxy(this.AuthFailureHandler, this));

	//Table which makes structure of Toolbarsets, taskbarsets and editor area....
	var pFrame = this.pDocument.getElementById(this.name+'_pFrame');
	//Editor area
	this.cEditor = BX(this.name + '_cEditor');
	window.IEplusDoctype = (lightMode && BX.browser.IsDoctype() && BX.browser.IsIE());
	this.pFrame = pFrame;

	// Hack for render bug in IE
	if (BX.browser.IsIE())
	{
		setTimeout(function()
		{
			obj.pFrame.style.position = 'absolute';
			setTimeout(function(){obj.pFrame.style.position = 'static';}, 10);
		}, 800);
	}

	this.pEditorFrame = this.cEditor.appendChild(BX.create("IFRAME", {props: {id: "ed_" + this.name, className: "bx-editor-iframe", src: "javascript:void(0)", frameborder: 0}}));

	if(this.pEditorFrame.contentDocument && !BX.browser.IsIE())
		this.pEditorDocument = this.pEditorFrame.contentDocument;
	else
		this.pEditorDocument = this.pEditorFrame.contentWindow.document;
	this.pEditorWindow = this.pEditorFrame.contentWindow;
	this.pEditorDocument.className = "pEditorDocument";
	this.pEditorDocument.pMainObj = this;

	//Toolbarsets creation
	this.pTopToolbarset = BX(this.name + '_toolBarSet0');
	if(!lightMode)
	{
		this.arToolbarSet[0] = new BXToolbarSet(this.pTopToolbarset, this, false); // top toolbar
		this.arToolbarSet[1] = new BXToolbarSet(BX(this.name + '_toolBarSet1'), this, true); // left toolbar
	}

	//Taskbarsets creation
	this.arTaskbarSet[2] = new BXTaskbarSet(BX(this.name + '_taskBarSet2'), this, 2); // Right taskbar
	this.arTaskbarSet[3] = new BXTaskbarSet(BX(this.name + '_taskBarSet3'), this, 3); // Bottom taskbar
	this.pTaskTabs = BX(this.name + '_taskBarTabs'); // Taskbar Tabs


	var ta = BX.create("TEXTAREA", {props: {className: "bxeditor-textarea"}, style: {height: '100%'}});
	if (BX.browser.IsIE())
	{
		this.pSourceDiv = this.cEditor.appendChild(this.CreateElement("DIV", {}, {display: 'none', height: '100%', width: '100%', overflowX: 'hidden', overflowY: 'auto', overflow: 'auto'}));
		this.pSourceFrame = this.pSourceDiv.appendChild(ta);
	}
	else
	{
		this.pSourceFrame = this.cEditor.appendChild(ta);
	}

	this.pSourceFrame.onkeydown = function (e)
	{
		var tabKeyCode = 9;
		var replaceWith = "  ";
		if(window.event)
		{
			if(event.keyCode == tabKeyCode)
			{
				this.selection = document.selection.createRange();
				this.selection.text = replaceWith;
				event.returnValue = false;
				return false;
			}
		}
		else
		{
			if(e.keyCode == tabKeyCode)
			{
				var
					selectionStart = this.selectionStart,
					selectionEnd = this.selectionEnd,
					scrollTop = this.scrollTop,
					scrollLeft = this.scrollLeft;

				this.value = this.value.substring(0, selectionStart)+ replaceWith + this.value.substring(selectionEnd);
				this.focus();
				this.setSelectionRange(selectionStart + (selectionStart != selectionEnd?0:1), selectionStart + replaceWith.length);
				this.scrollTop = scrollTop;
				this.scrollLeft = scrollLeft;
				return false;
			}
		}
	};

	this.pSourceFrame.onkeyup = function (){BX.onCustomEvent(obj, 'onChange');};

	pBXEventDispatcher.__Add(this);
	if (this.bDotNet && this.pASPXParser && this.pASPXParser.OnLoadSystem)
		this.pASPXParser.OnLoadSystem();

	BXHTMLEditor.prototype.OnDragDrop = function (e)
	{
		if (this.sEditorMode == 'code' || this.sEditorMode == 'split' && this.sEditorSplitMode == 'code')
			return;

		if(this.nLastDragNDropElement && this.nLastDragNDropElement.length > 0)
		{
			var obj = this;
			setTimeout(function ()
			{
				var pEl = obj.pEditorDocument.getElementById(obj.nLastDragNDropElement);
				if (!pEl)
					pEl = BX(obj.nLastDragNDropElement);

				if(obj.pEditorWindow.getSelection)
					obj.pEditorWindow.getSelection().selectAllChildren(pEl);

				if (obj.nLastDragNDropElementFire !== false)
					obj.nLastDragNDropElementFire(pEl);
				obj.OnClick(e);
			}, 10);
		}
	};

	BXHTMLEditor.prototype.__ShowTableBorder = function (pTable, bShow)
	{
		var arTableBorderStyles = ["border", "borderBottom", "borderBottomColor", "borderBottomStyle", "borderBottomWidth", "borderCollapse", "borderColor", "borderLeft", "borderLeftColor", "borderLeftStyle", "borderLeftWidth", "borderRight", "borderRightColor", "borderRightStyle", "borderRightWidth", "borderStyle", "borderTop", "borderTopColor", "borderTopStyle", "borderTopWidth", "borderWidth"];
		if(!pTable.border || pTable.border == "0")
		{
			try{
				if(bShow)
				{
					pTable.setAttribute("__bxborderCollapse", pTable.style.borderCollapse);
					pTable.style.borderCollapse = "collapse";
				}
				else
				{
					pTable.style.borderCollapse = pTable.getAttribute("__bxborderCollapse");
					pTable.removeAttribute("__bxborderCollapse");
				}
			} catch(e){}

			var pCell, arCells = pTable.getElementsByTagName("TD");
			for(var j = 0; j < arCells.length; j++)
			{
				pCell = arCells[j];
				if(bShow)
				{
					if(!pCell.getAttribute("__bxborder"))
					{
						pCell.setAttribute("__bxborder", BXSerializeAttr(pCell.style, arTableBorderStyles));
						pCell.style.border = "1px #ACACAC dashed";
					}
				}
				else
				{
					if(pCell.getAttribute("__bxborder"))
					{
						pCell.style.borderWidth = "";
						pCell.style.borderColor = "";
						pCell.style.borderStyle = "";
						BXUnSerializeAttr(pCell.getAttribute("__bxborder"), pCell.style, arTableBorderStyles);
						pCell.removeAttribute("__bxborder");
					}
				}
			}
		}
	};

	BXHTMLEditor.prototype.Show = function (flag)
	{
		this.bShowed = flag;
		if(flag && this.pWnd.style.display=='none')
			this.pWnd.style.display='block';
		else if(!flag && this.pWnd.style.display != 'none')
			this.pWnd.style.display='none';
	};

	BXHTMLEditor.prototype.ShowTableBorder = function (bShow)
	{
		if(this.bTableBorder == bShow)
			return false;

		this.bTableBorder = bShow;
		var arTables = this.pEditorDocument.getElementsByTagName("TABLE");
		for(var i=0; i<arTables.length; i++)
			this.__ShowTableBorder(arTables[i], bShow);

		return true;
	};

	BXHTMLEditor.prototype.OnClick = function(e)
	{
		if (!e)
			e = this.pEditorWindow.event;
		if (!e)
			e = window.event;

		if (e)
		{
			var pElement = e.target || e.srcElement;
			if (pElement && pElement.nodeType == 1 && pElement.tagName && pElement.tagName.toLowerCase() == 'img')
				this.SelectElement(pElement);
		}

		if (this.__bMouseDownComp) // Prevent default for selecting other element after Rendered Component selection
			return;

		if(this.pOnChangeSelectionTimer)
			clearTimeout(this.pOnChangeSelectionTimer);

		BX.onCustomEvent(this, 'onChange');

		this.bFirstClick = true;
		var obj = this;
		this.pOnChangeSelectionTimer = setTimeout(function (){obj.OnEvent("OnSelectionChange");}, 200);
	};

	BXHTMLEditor.prototype.OnDblClick = function (e)
	{
		var pEl, oTag = false;
		if (!e)
			e = this.pEditorWindow.event;
		if (e.target)
			pEl = e.target;
		else if (e.srcElement)
			pEl = e.srcElement;
		if (pEl.nodeType == 3)
			pEl = pEl.parentNode;

		if (pEl && pEl.nodeName)
			oTag = this.GetBxTag(pEl);

		if (oTag)
		{
			if (oTag.tag == "img")
				this.OpenEditorDialog("image", null, 500, {pElement: pEl});
			else if (oTag.tag == "a")
				this.OpenEditorDialog("editlink", null, 520);
			if (oTag.tag == "anchor")
				this.OpenEditorDialog("anchor", null, 400);
			if (oTag.tag == "flash")
				this.OpenEditorDialog("flash", null, 500, {bUseTabControl: true, pMainObj: this});
		}

		obj.OnEvent("OnDblClick", [e]);
	};

	BXHTMLEditor.prototype.OnMouseUp = function (e)
	{
		this.bFirstClick = true;
		if(this.pOnChangeSelectionTimer)
			clearTimeout(this.pOnChangeSelectionTimer);
		var obj = this;
		this.pOnChangeSelectionTimer = setTimeout(function (){obj.OnEvent("OnSelectionChange");}, 100);
	};

	this.pSourceFrame.onblur = function (e){obj.pEditorFrame.onfocus(e);};

	this.pSourceFrame.onfocus = function (e)
	{
		if(obj.bEditSource)
			return;

		obj.bEditSource = true;
		if(obj.sEditorMode == 'split')
		{
			obj.SaveContent();
			obj.OnEvent('ClearResourcesBeforeChangeView');
			obj.SetCodeEditorContent(obj.GetContent());
			obj.sEditorSplitMode = 'code';
			obj.OnEvent("OnChangeView", [this.sEditorMode, this.sEditorSplitMode]);
		}
	};

	this.pEditorFrame.onfocus = function (e)
	{
		if(!obj.bEditSource)
			return;

		obj.bEditSource = false;
		if(obj.sEditorMode=='split')
		{
			obj.SetEditorContent(obj.GetCodeEditorContent());
			obj.sEditorSplitMode = 'html';
			obj.OnEvent("OnChangeView", [this.sEditorMode, this.sEditorSplitMode]);
		}
	};

	this.value = this.pValue.value;
	BXStyleParser.Create();
	this.oStyles = new BXStyles(this);

	if(this.arConfig["TEMPLATE"])
		this.SetTemplate(this.arConfig["TEMPLATE"]["ID"], this.arConfig["TEMPLATE"], true);
	// ***********************************************************************************************
	// 	Adding all toolbars and buttons to them
	// ***********************************************************************************************
	var arAllowedToolbars = window['ar_' + this.name + '_toolbars'];
	var arSet;
	if (!SETTINGS[this.name].arToolbarSettings)
		SETTINGS[this.name].arToolbarSettings = arToolbarSettings_default;
	var arToolbarSettings = SETTINGS[this.name].arToolbarSettings;
	if (lightMode)
	{
		var
			pGlobalToolbar = new BXGlobalToolbar(this),
			arSourceToolbar = [], val;

		if (this.arConfig.toolbarConfig)
		{
			var _handledButtons = {}, _val;
			for(var j = 0, n = this.arConfig.toolbarConfig.length; j < n ; j++)
			{
				val = this.arConfig.toolbarConfig[j];
				if (val.indexOf("-") === -1 && val == parseInt(val) && arGlobalToolbar[val])
				{
					arSourceToolbar.push(arGlobalToolbar[val]);
				}

				_val = val.replace("-", '');
				if (arGlobalToolbar[_val])
				{
					_handledButtons[arGlobalToolbar[_val][1].id] = true;
				}
			}

			for(j = 0; j < arGlobalToolbar.length ; j++)
			{
				if (!_handledButtons[arGlobalToolbar[j][1].id])
				{
					arSourceToolbar.push(arGlobalToolbar[j]);
				}
			}
		}
		else
		{
			arSourceToolbar = arGlobalToolbar;
		}

		pGlobalToolbar.LineBegin(true);
		for(var i = 0, l = arSourceToolbar.length; i < l ; i++)
		{
			var arButton = arSourceToolbar[i];
			if(!arButton || (arButton[1] && arButton[1].hideCondition && arButton[1].hideCondition(this)))
				continue;

			if (typeof(arButton) == 'object')
			{
				pGlobalToolbar.AddButton(this.CreateCustomElement(arButton[0], arButton[1]));
			}
			else if(arButton == 'new_line')
			{
				pGlobalToolbar.LineEnd();
				pGlobalToolbar.LineBegin();
			}
			else if(arButton == 'separator')
			{
				pGlobalToolbar.AddButton(this.CreateCustomElement('BXButtonSeparator'));
			}
		}
		pGlobalToolbar.LineEnd();
	}
	else // admin
	{
		for(var sToolBarId in arToolbars)
		{
			if (arAllowedToolbars !== false && !arAllowedToolbars[sToolBarId])
			{
				delete arToolbars[sToolBarId];
				continue;
			}

			//try{
			if (!arToolbarSettings[sToolBarId])
			{
				SETTINGS[this.name].arToolbarSettings[sToolBarId] = arToolbarSettings_default[sToolBarId];
				arSet = arToolbarSettings_default[sToolBarId];
			}
			else
			{
				arSet = arToolbarSettings[sToolBarId];
			}

			if(BXSearchInd(this.arToolbars, sToolBarId) < 0 && this.customToolbars !== true)
				continue;

			var arSourceToolbar = [], val;
			if (this.arConfig.toolbarConfig && this.arConfig.toolbarConfig[sToolBarId])
			{
				for(var j = 0, n = this.arConfig.toolbarConfig[sToolBarId].length; j < n ; j++)
				{
					val = this.arConfig.toolbarConfig[sToolBarId][j];
					if (val.indexOf("-") === -1 && val == parseInt(val) && arToolbars[sToolBarId][1][val])
						arSourceToolbar.push(arToolbars[sToolBarId][1][val]);
				}
			}
			else
			{
				arSourceToolbar = arToolbars[sToolBarId][1];
			}

			var arButton, i, l = arSourceToolbar.length;
			if (!l) // All buttons was disabled
			{
				// Have to del toolbar
				delete arToolbars[sToolBarId];
				continue;
			}

			var pToolbar = new BXToolbar(this, arToolbars[sToolBarId][0], sToolBarId);

			for(i = 0; i < l ; i++)
			{
				arButton = arSourceToolbar[i];
				if(!arButton || (arButton[1] && arButton[1].hideCondition && arButton[1].hideCondition(this)))
					continue;

				if(arButton == 'separator')
				{
					pToolbar.AddButton(this.CreateCustomElement('BXButtonSeparator'));
				}
				else if(!arButton[1].id || !pToolbar.buttons[arButton[1].id])
				{
					pToolbar.AddButton(this.CreateCustomElement(arButton[0], arButton[1]));
					pToolbar.buttons[arButton[1].id] = true;
				}
			}

			if (arSet.docked && arSet.position)
				arDefaultTBPositions[sToolBarId] = arSet.position;
			if(arDefaultTBPositions[sToolBarId])
				this.arToolbarSet[arDefaultTBPositions[sToolBarId][0]].AddToolbar(pToolbar, arDefaultTBPositions[sToolBarId][1], arDefaultTBPositions[sToolBarId][2]);
			else
				this.arToolbarSet[0].AddToolbar(pToolbar, 100, 0);

			if (!arSet.docked && arSet.position)
				pToolbar.SetPosition(arSet.position.x,arSet.position.y);

			if (!arSet.show)
			{
				pToolbar.Close();
				continue;
			}
			pToolbar = null;

			//}catch(e){_alert("Error: loading "+sToolBarId+" toolbar"); continue;}
		}
		arSet = null;
	}

	// Init event "OnCreate" : adding all taskbars
	setTimeout(function (){BXCreateTaskbars(obj, true);}, 50);
	this.SetView("html");
	if(this.arConfig["fullscreen"])
	{
		this.pDocument.body.style.display = 'block';
		this.SetFullscreen(true);
	}

	this.start_func(this);
	pFrame.style.display = ''; // Show Editor frame
	setTimeout(function ()
		{
			BX.closeWait();
			obj.bLoadFinish = true;
			obj.SetFocus();
			try{jsUtils.onCustomEvent('EditorLoadFinish_' + obj.name);}catch(e){}
		}, 10
	);

	//Table border = ON
	this.ShowTableBorder(true);

	oBXContextMenu = this.CreateCustomElement("BXContextMenu");
	oBXContextMenu.Create();

	this.oBXVM = new BXVisualMinimize();

	jsUtils.addCustomEvent('OnToggleTabs', this.ClearPosCache, [], this);
	ar_BXTaskbarS = [];
	BXPopupWindow.bCreated = false;

	if (BX.WindowManager)
	{
		var wnd = BX.WindowManager.Get();
		if (wnd)
		{
			BX.addCustomEvent(wnd, 'onWindowDragFinished', function()
			{
				CACHE_DISPATCHER['pEditorFrame_' + obj.name] = null;
				CACHE_DISPATCHER['pEditorFrame'] = null;
			});
		}
	}
	//}catch(e){alert('ERROR: BXHTMLEditor.prototype.OnLoad'); alert(e);}

	// Autosave handlers
	var pForm = obj.pValue.form;
	if (pForm)
	{
		//BX.addCustomEvent(pForm, 'onAutoSavePrepare', function()
		//{
			if (pForm && pForm.BXAUTOSAVE)
			{
				try{
					BX.addCustomEvent(obj, 'onChange', function()
					{
						pForm.BXAUTOSAVE.Init();
					});
					BX.addCustomEvent(pForm, 'onAutoSave', function (ob, data)
					{
						if (obj.bShowed)
						{
							obj.SaveContent(); // Save editor content
							data[obj.name] = obj.GetContent(); // Get it from textarea and put to form_data to saving
						}
					});

					BX.addCustomEvent(pForm, 'onAutoSaveRestore', function (ob, data)
					{
						if (obj.bShowed)
						{
							obj.SetContent(data[obj.name]);
							obj.LoadContent();
						}
					});
				}catch(e){}
			}
		//});
	}
};

BXHTMLEditor.prototype.SetContent = function(sContent)
{
	this.OnEvent('SetContentBefore', [sContent]);
	this.pValue.value = this.value = sContent;
	this.OnEvent('SetContentAfter', [sContent]);
};

BXHTMLEditor.prototype.GetContent = function()
{
	this.OnEvent('GetContent');
	return this.value.toString();
};

BXHTMLEditor.prototype.LoadContent = function()
{
	this.OnEvent('LoadContentBefore');
	var sContent = this.GetContent();
	if(this.sFirstContent == null)
		this.sFirstContent = sContent;

	switch(this.sEditorMode)
	{
		case 'code':
			this.SetCodeEditorContent(sContent);
			break;
		case 'split':
			this.SetCodeEditorContent(sContent)
			this.SetEditorContent(sContent)
			break;
		case 'html':
			this.SetEditorContent(sContent);
	}
	this.OnEvent('LoadContentAfter');
};

BXHTMLEditor.prototype.SaveContent = function()
{
	this.OnEvent('SaveContentBefore');
	switch(this.sEditorMode)
	{
		case 'code':
			this.SetContent(this.GetCodeEditorContent());
			break;
		case 'split':
			if(this.sEditorSplitMode == 'code')
				this.SetContent(this.GetCodeEditorContent());
			else
				this.SetContent(this.GetEditorContent(true, true));
			break;
		case 'html':
			this.SetContent(this.GetEditorContent(true, true));
	}
	this.OnEvent('SaveContentAfter');
};


BXHTMLEditor.prototype.SetEditorContent = function(sContent)
{
	var _this = this;
	sContent = this.pParser.SystemParse(sContent);

	if (this.pEditorDocument.designMode)
	{
		try{
			this.pEditorDocument.designMode = 'off';
		}catch(e){_alert('SetEditorContent: designMode=\'off\'');}
	}
	this.OnEvent('SetEditorContentBefore', [sContent]);
	//Writing content
	this.pEditorDocument.open();
	this.pEditorDocument.write('<html><head></head><body' + this.bodyParams + '>' + sContent + '</body></html>');
	this.pEditorDocument.close();

	this.pEditorDocument.body.style.padding = "5px";
	this.pEditorDocument.body.style.margin = "0";
	this.pEditorDocument.body.style.borderWidth = "0";

	//Handling DOM
	this.pParser.DOMHandle();
	if(this.bTableBorder)
	{
		this.bTableBorder = false;
		this.ShowTableBorder(true);
	}
	if(BX.browser.IsIE())
	{
		this.pEditorDocument.body.contentEditable = true;
		addAdvEvent(this.pEditorDocument, 'focus', window['onClick_'+this.name]);
	}
	else
	{
		this.pEditorWindow.__bxedname = this.name;
		this.pEditorWindow.addEventListener("focus", this.FFOnFocus, false);
	}

	this.oStyles.SetToDocument(this.pEditorDocument);
	this.pEditorDocument.className = 'pEditorDocument';
	this.pEditorDocument.pMainObj = this;
	pBXEventDispatcher.SetEvents(this.pEditorDocument);

	addAdvEvent(this.pEditorDocument, 'contextmenu', window['onContextMenu_'+this.name]);
	addAdvEvent(this.pEditorDocument, 'click', window['onClick_'+this.name]);
	addAdvEvent(this.pEditorDocument, 'dblclick', window['onDblClick_'+this.name]);
	addAdvEvent(this.pEditorDocument, 'mouseup', window['onMouseUp_'+this.name]);
	addAdvEvent(this.pEditorDocument, 'dragdrop', window['onDragDrop_'+this.name]);

	addAdvEvent(this.pEditorDocument, 'keydown', BX.proxy(function(e){return this.OnKeyPress(e, true)}, this));
	addAdvEvent(this.pEditorDocument, 'keyup', BX.proxy(function(e){_this.OnClick(e); _this.OnChange("keyup", "");}, this));

	if(BX.browser.IsIE())
		addAdvEvent(this.pEditorDocument.body, 'paste', window['onPaste_' + this.name]);
	addAdvEvent(this.pEditorDocument, 'keydown', window['onKeyDown_' + this.name]);

	pBXEventDispatcher.OnEditorEvent("OnSetEditorContent", this);
	this.OnEvent('SetEditorContentAfter');
};

BXHTMLEditor.prototype.GetEditorContent = function()
{
	this.OnEvent('GetEditorContentBefore');

	var bBorders = this.bTableBorder;
	if(bBorders) this.ShowTableBorder(false);
	this.pParser.Parse();
	if(bBorders) this.ShowTableBorder(true);

	var sContent = this.pParser.GetHTML(true);
	sContent = this.pParser.ClearFromHBF(sContent);
	sContent = this.pParser.SystemUnParse(sContent);

	if (this.fullEditMode)
		sContent = this.pParser.AppendHBF(sContent, true);

	this.OnEvent('GetEditorContentAfter', [sContent]);
	return sContent;
};


BXHTMLEditor.prototype.SetCodeEditorContent = function(sContent)
{
	this.pSourceFrame.value = sContent;
};

BXHTMLEditor.prototype.GetCodeEditorContent = function()
{
	return this.PreparseHeaders(this.pSourceFrame.value);
};

BXHTMLEditor.prototype.PreparseHeaders = function(sContent)
{
	if (!this.fullEditMode)
		return sContent;
	return this.pParser.GetHBF(sContent, true);
};

BXHTMLEditor.prototype.SetView = function(sType)
{
	if (this.sEditorMode == sType)
		return;
	var _this = this;
	this.SaveContent();
	switch(sType)
	{
		case 'code':
			this.pSourceFrame.style.height = "99%";
			this.pEditorFrame.style.display = "none";
			this._DisplaySourceFrame();

			if (BX.browser.IsIE())
			{
				this.pSourceFrame.rows = "50";
				this.pSourceDiv.style.height = "99%";
				this.pSourceDiv.style.display = "block";
			}
			this.pSourceFrame.style.borderTop = "0px solid #808080";

			// Hide taskbarsets
			var
				rightTaskbar = this.arTaskbarSet[2],
				bottomTaskbar = this.arTaskbarSet[3];

			this.oTaskbarsInHtmlMode = {
				rightTaskbar: rightTaskbar.bShowing,
				bottomTaskbar: bottomTaskbar.bShowing
			};

			if (rightTaskbar.bShowing)
				rightTaskbar.Display(false);
			if (bottomTaskbar.bShowing)
				bottomTaskbar.Display(false);

			this.oBXTaskTabs.Refresh();

			this.SetCodeEditorContent(this.GetContent());

			setTimeout(function(){_this.pSourceFrame.focus();}, 200);
			break;
		case 'split':
			this.pEditorFrame.style.height = "50%";
			if (BX.browser.IsIE())
			{
				this.pSourceFrame.style.height = "97%";
				this.pSourceFrame.rows = "40";
				this.pSourceDiv.style.overflow = "hidden";
				this.pSourceDiv.style.height = "49%";
				this.pSourceDiv.style.display = "block";
			}
			else
			{
				this.pSourceFrame.style.height = "49%";
			}
			this.pSourceFrame.style.borderTop = "2px solid #808080";
			this._DisplaySourceFrame();
			this.pEditorFrame.style.display = "block";
			if(this.sEditorMode == 'code')
				this.SetEditorContent(this.GetContent());
			else if(this.sEditorMode == 'html')
				this.SetCodeEditorContent(this.GetContent());

			break;
		default:
			this.pEditorFrame.style.height = "100%";
			this.pSourceFrame.style.display = "none";
			this.pEditorFrame.style.display = "block";

			if (IEplusDoctype)
				this.pSourceDiv.style.display = "none";

			// Hide taskbarsets
			if (this.oTaskbarsInHtmlMode)
			{
				if (this.oTaskbarsInHtmlMode.rightTaskbar)
					this.arTaskbarSet[2].Display(true); //rightTaskbar
				if (this.oTaskbarsInHtmlMode.bottomTaskbar)
					this.arTaskbarSet[3].Display(true); // bottomTaskbar

				this.oBXTaskTabs.Refresh();
				this.oTaskbarsInHtmlMode = null;
			}

			this.SetEditorContent(this.GetContent());
			sType = "html";
	}
	this.arTaskbarSet[3].Resize();

	this.sEditorMode = sType;
	this.SetCursorFF();

	this.OnEvent("OnChangeView", [this.sEditorMode, this.sEditorSplitMode]);
};

// Dirty hack for Firefox (Chrome, Safari, Opera)
BXHTMLEditor.prototype.SetCursorFF = function()
{
	if (this.sEditorMode != 'code' && !BX.browser.IsIE())
	{
		var _this = this;
		try{
			this.pEditorFrame.blur();
			this.pEditorFrame.focus();

			setTimeout(function(){
				_this.pEditorFrame.blur();
				_this.pEditorFrame.focus();
			}, 600);

			setTimeout(function(){
				_this.pEditorFrame.blur();
				_this.pEditorFrame.focus();
			}, 1000);
		}catch(e){}
	}
};

BXHTMLEditor.prototype._DisplaySourceFrame = function(bCheck)
{
	if (bCheck && this.sEditorMode != 'code' && this.sEditorMode != 'split')
		return;
	if (BX.browser.IsIE())
	{
		this.pSourceFrame.style.display = "none";
		var _this = this;
		setTimeout(function(){_this.pSourceFrame.style.display = "block";}, 100);
	}
	else
	{
		this.pSourceFrame.style.display = "block";
	}
}

BXHTMLEditor.prototype.PasteAsText = function(text)
{
	text = bxhtmlspecialchars(text);
	text = text.replace(/\r/g, '');
	text = text.replace(/\n/g, '<br/>');
	this.insertHTML(text);
};

BXHTMLEditor.prototype.CleanWordText = function(text, arParams)
{
	text = text.replace(/<(P|B|U|I|STRIKE)>&nbsp;<\/\1>/g, ' ');
	text = text.replace(/<o:p>([\s\S]*?)<\/o:p>/ig, "$1");
	//text = text.replace(/<o:p>[\s\S]*?<\/o:p>/ig, "&nbsp;");

	text = text.replace(/<span[^>]*display:\s*?none[^>]*>([\s\S]*?)<\/span>/gi, ''); // Hide spans with display none

	text = text.replace(/<!--\[[\s\S]*?\]-->/ig, ""); //<!--[.....]-->	-	<!--[if gte mso 9]>...<![endif]-->
	text = text.replace(/<!\[[\s\S]*?\]>/ig, "");		//	<! [if !vml]>
	text = text.replace(/<\\?\?xml[^>]*>/ig, "");	//<xml...>, </xml...>

	text = text.replace(/<o:p>\s*<\/o:p>/ig, "");

	text = text.replace(/<\/?[a-z1-9]+:[^>]*>/gi, "");	//<o:p...>, </o:p>
	text = text.replace(/<([a-z1-9]+[^>]*) class=([^ |>]*)(.*?>)/gi, "<$1$3");
	text = text.replace(/<([a-z1-9]+[^>]*) [a-z]+:[a-z]+=([^ |>]*)(.*?>)/gi, "<$1$3"); //	xmlns:v="urn:schemas-microsoft-com:vml"

	if (arParams.spaces)
	{
		text = text.replace(/&nbsp;/ig, ' ');
		text = text.replace(/\s+?/gi, ' ');
	}

	// Remove mso-xxx styles.
	text = text.replace(/\s*mso-[^:]+:[^;"]+;?/gi, "");

	// Remove margin styles.
	text = text.replace(/\s*margin: 0cm 0cm 0pt\s*;/gi, "");
	text = text.replace(/\s*margin: 0cm 0cm 0pt\s*"/gi, "\"");

	//if (removeIndents)
	if (arParams.indents)
	{
		text = text.replace(/\s*TEXT-INDENT: 0cm\s*;/gi, "");
		text = text.replace(/\s*TEXT-INDENT: 0cm\s*"/gi, "\"");
	}

	text = text.replace(/\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"");
	text = text.replace(/\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"");
	text = text.replace(/\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"");
	text = text.replace(/\s*tab-stops:[^;"]*;?/gi, "");
	text = text.replace(/\s*tab-stops:[^"]*/gi, "");

	// Remove FONTS
	if (arParams.fonts)
	{
		text = text.replace(/<FONT[^>]*>([\s\S]*?)<\/FONT>/gi, '$1');
		text = text.replace(/\s*face="[^"]*"/gi, "");
		text = text.replace(/\s*face=[^ >]*/gi, "");
		text = text.replace(/\s*FONT-FAMILY:[^;"]*;?/gi, "");
	}

	// Remove Class attributes
	text = text.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");

	// Remove styles.
	if (arParams.styles)
		text = text.replace(/<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3");

	// Remove empty styles.
	text = text.replace(/\s*style="\s*"/gi, '');

	// Remove Lang attributes
	text = text.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");

	var iter = 0;
	while (text.toLowerCase().indexOf('<span') != -1 && text.toLowerCase().indexOf('</span>') != -1 && iter++ < 20)
		text = text.replace(/<span[^>]*?>([\s\S]*?)<\/span>/gi, '$1');

	var
		_text,
		i, tag, arFormatTags = ['b', 'strong', 'i', 'u', 'font', 'span', 'strike'];

	while (true)
	{
		_text = text;
		for (i in arFormatTags)
		{
			tag = arFormatTags[i];
			text = text.replace(new RegExp('<' + tag + '[^>]*?>(\\s*?)<\\/' + tag + '>', 'gi'), '$1');
			text = text.replace(new RegExp('<\\/' + tag + '[^>]*?>(\\s*?)<' + tag + '>', 'gi'), '$1');
		}

		if (_text == text)
			break;
	}

	// Remove empty tags
	text = text.replace(/<(?:[^\s>]+)[^>]*>([\s\n\t\r]*)<\/\1>/g, "$1");
	text = text.replace(/<(?:[^\s>]+)[^>]*>(\s*)<\/\1>/g, "$1");
	text = text.replace(/<(?:[^\s>]+)[^>]*>(\s*)<\/\1>/g, "$1");

	//text = text.replace(/<\/?xml[^>]*>/gi, "");	//<xml...>, </xml...>
	text = text.replace(/<xml[^>]*?(?:>\s*?<\/xml)?(?:\/?)?>/ig, '');
	text = text.replace(/<meta[^>]*?(?:>\s*?<\/meta)?(?:\/?)?>/ig, '');
	text = text.replace(/<link[^>]*?(?:>\s*?<\/link)?(?:\/?)?>/ig, '');
	text = text.replace(/<style[\s\S]*?<\/style>/ig, '');

	if (arParams.tableAtr)
		text = text.replace(/<table([\s\S]*?)>/gi, "<table>");

	if (arParams.trtdAtr)
	{
		text = text.replace(/<tr([\s\S]*?)>/gi, "<tr>");
		text = text.replace(/(<td[\s\S]*?)width=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
		text = text.replace(/(<td[\s\S]*?)height=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
		text = text.replace(/(<td[\s\S]*?)style=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
		text = text.replace(/(<td[\s\S]*?)valign=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
		text = text.replace(/(<td[\s\S]*?)nowrap=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
		text = text.replace(/(<td[\s\S]*?)nowrap([\s\S]*?>)/gi, "$1$3");

		text = text.replace(/(<col[\s\S]*?)width=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
		text = text.replace(/(<col[\s\S]*?)style=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	}

	// For Opera (12.10+) only when in text we have reference links.
	if (BX.browser.IsOpera())
		text = text.replace(/REF\s+?_Ref\d+?[\s\S]*?MERGEFORMAT\s([\s\S]*?)\s[\s\S]*?<\/xml>/gi, " $1 ");

	return text;
};


BXHTMLEditor.prototype.PasteWord = function(text, arParams)
{
	this.insertHTML(this.CleanWordText(text, arParams));
};


BXHTMLEditor.prototype.LoadTemplateParams = function(templateID)
{
	var _this = this;
	return BX.ajax.post(editor_action_path + '&action=sitetemplateparams&lang=' + BXLang + '&site=' + BXSite + '&templateID=' + templateID, {}, function(){
		setTimeout(function(){
			_this.SetTemplate(window.bx_template_params["ID"], window.bx_template_params, false);
		}, 100);
	});
};

BXHTMLEditor.prototype.SetTemplate = function (templateID, arTemplateParams, bReload)
{
	//try{
	if(this.templateID && this.templateID == templateID || arTemplateParams === false)
		return;

	if(!arTemplateParams)
		return this.LoadTemplateParams(templateID);

	this.templateID = arTemplateParams["ID"];

	if(this.pTemplateListbox)
		this.pTemplateListbox.SelectByVal(this.templateID);

	this.arTemplateParams = arTemplateParams;
	if (bReload) // Reload contents
	{
		this.SaveContent();
		if (this.bDotNet)
			this.SetTemplate_ex();
		this.LoadContent();
	}

	// Change styles
	if (to_template_path && this.arTemplateParams.ID)
		this.oStyles.Parse(this.arTemplateParams["STYLES"], to_template_path + this.arTemplateParams.ID);

	var styleTitles = this.arTemplateParams["STYLES_TITLE"];
	if (styleTitles)
	{
		// Workaround for Chrome
		for (var title in styleTitles)
			if (title && title != title.toLowerCase() && !styleTitles[title.toLowerCase()])
				styleTitles[title.toLowerCase()] = styleTitles[title];
	}

	// Set styles
	this.oStyles.SetToDocument(this.pEditorDocument);

	var _this = this;

	if (this.pParser.strStyleNodes)
		setTimeout(function(){_this.pParser.AppendCSS(_this.pParser.strStyleNodes);}, 300);

	this.OnEvent("OnTemplateChanged");
	//}catch(e){_alert('ERROR: BXHTMLEditor.prototype.SetTemplate');}
};

BXHTMLEditor.prototype.SetFocus = function ()
{
	if(!this.bEditSource)
		BX.focus(this.pEditorWindow.focus ? this.pEditorWindow : this.pEditorDocument.body);
};

BXHTMLEditor.prototype.insertHTML = function(sValue)
{
	this.SetFocus();

	// Don't clear "try catch"... Some times browsers generetes failures
	try
	{
		if(BX.browser.IsIE())
		{
			var oRng = this.pEditorDocument.selection.createRange();
			oRng.pasteHTML(sValue);
			oRng.collapse(false);
			oRng.select();
		}
		else if(BX.browser.IsIE11())
		{
			this.PasteHtmlAtCaret(sValue);
		}
		else
		{
			this.pEditorWindow.document.execCommand('insertHTML', false, sValue);
		}
	}
	catch(e){}

	this.OnChange("insertHTML", "");
};

BXHTMLEditor.prototype.PasteHtmlAtCaret = function(html, selectPastedContent)
{
	var
		win = this.pEditorWindow,
		doc = this.pEditorDocument,
		sel, range;

	if (win.getSelection)
	{
		// IE9 and non-IE
		sel = win.getSelection();
		if (sel.getRangeAt && sel.rangeCount)
		{
			range = sel.getRangeAt(0);
			range.deleteContents();

			// Range.createContextualFragment() would be useful here but is
			// only relatively recently standardized and is not supported in
			// some browsers (IE9, for one)
			var el = doc.createElement("div");
			el.innerHTML = html;
			var frag = doc.createDocumentFragment(), node, lastNode;
			while ((node = el.firstChild))
				lastNode = frag.appendChild(node);

			var firstNode = frag.firstChild;
			range.insertNode(frag);

			// Preserve the selection
			if (lastNode)
			{
				range = range.cloneRange();
				range.setStartAfter(lastNode);
				if (selectPastedContent)
					range.setStartBefore(firstNode);
				else
					range.collapse(true);

				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	}
	else if ((sel = doc.selection) && sel.type != "Control")
	{
		// IE < 9
		var originalRange = sel.createRange();
		originalRange.collapse(true);
		sel.createRange().pasteHTML(html);
		if (selectPastedContent)
		{
			range = sel.createRange();
			range.setEndPoint("StartToStart", originalRange);
			range.select();
		}
	}
};

BXHTMLEditor.prototype.OnContextMenu = function (e, pElement, bNotFrame, arParams)
{
	var obj = this, arFramePos;
	obj.OnEvent("OnSelectionChange");
	if(obj.pEditorWindow.event)
		e = obj.pEditorWindow.event;
	if(!e)
		e = window.event;

	if (!pElement)
		pElement = e.target || e.srcElement;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
		if (!bNotFrame)
		{
			e.realX -= obj.pEditorDocument.body.scrollLeft;
			e.realY -= obj.pEditorDocument.body.scrollTop;
		}
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX;
		e.realY = e.clientY;
		if (bNotFrame)
		{
			e.realX += document.body.scrollLeft;
			e.realY += document.body.scrollTop;
		}
	}

	if(!bNotFrame)
	{
		if (!(arFramePos = CACHE_DISPATCHER['pEditorFrame_' + this.name]))
			CACHE_DISPATCHER['pEditorFrame_' + this.name] = arFramePos = BX.pos(obj.pEditorFrame);

		e.realX += arFramePos["left"];
		e.realY += arFramePos["top"];
	}
	oBXContextMenu.Show(2500, 0, {left : e.realX, top : e.realY}, pElement, arParams, this);

	return BX.PreventDefault(e);
};

BXHTMLEditor.prototype.executeCommand = function(commandName, sValue)
{
	this.SetFocus();
	try{
		var res = this.pEditorWindow.document.execCommand(commandName, false, sValue);
	}catch(e){};
	this.SetFocus();
	this.OnEvent("OnSelectionChange");
	this.OnChange("executeCommand", commandName);
	return res;
};

BXHTMLEditor.prototype.queryCommand = function(commandName)
{
	var sValue = '';
	try{
		if(!this.pEditorDocument.queryCommandEnabled(commandName))
			return null;
	}catch(e){return null;}

	try{
		return this.pEditorDocument.queryCommandValue(commandName);
	}catch(e) {}

	return null;
};


BXHTMLEditor.prototype.queryCommandState = function(commandName)
{
	var sValue = '';
	try
	{
		if(!this.pEditorDocument.queryCommandEnabled(commandName))
			return 'DISABLED';
	}
	catch(e){return 'DISABLED';}

	try
	{
		return (this.pEditorDocument.queryCommandState(commandName)?'CHECKED':'ENABLED');
	}
	catch(e) {return 'ENABLED';}

	return 'DISABLED';
};


BXHTMLEditor.prototype.updateBody = function()
{
	this.extractBodyParams(this._body);
};

BXHTMLEditor.prototype.extractBodyParams = function(_body)
{
	var sParams = _body.replace(/<body(.*?)>/i, "$1");
	var arBodyParams_src = sParams.match(/\w+\s*=".*?"/ig);
	var arBodyParams = [];
	var _val;

	for (var i in arBodyParams_src)
	{
		if (parseInt(i).toString()=="NaN") continue;
		var arBodyParams_src = sParams.match(/(\w+)\s*=".*?"/ig);
		_val = arBodyParams_src[i].replace(/(\w+)\s*="(.*?)"/ig,"$2");
		arBodyParams[RegExp.$1] = _val;
	}
};

BXHTMLEditor.prototype.FFOnFocus = function(e)
{
	try{
		var pMainObj = GLOBAL_pMainObj[this.__bxedname];
		if (pMainObj.pEditorDocument.designMode == 'on')
			return;

		pMainObj.pEditorDocument.designMode = "on";
		//pMainObj.pEditorDocument.execCommand("useCSS", false, true); //deprecated
		pMainObj.pEditorDocument.execCommand("styleWithCSS", false, false); // new moz call

		setTimeout(function(){
			try{pMainObj.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}
		}, 1000);

		this.document.execCommand("insertBrOnReturn", false, false); // new moz call
	}catch(e){/*_alert('Eror: pMainObj.FFOnFocus');*/}
};

BXHTMLEditor.prototype.onSubmit = function(e)
{
	if (!this.isSubmited)
	{
		this.isSubmited = true;
		BX.cleanNode(this.oPropertiesTaskbar.pCellProps);

		if (!this.sEditorMode)
			this.sEditorMode = 'html';

		this.OnEvent('OnSubmit');

		if(this.bShowed)
			this.SaveContent();

		this.Show(false);
	}
};

BXHTMLEditor.prototype.OnKeyDown = function (e)
{
	if (!e)
		e = this.pEditorWindow.event;

	var key = e.which || e.keyCode;

	if (!BX.browser.IsIE() && !BX.browser.IsOpera())
	{
		if (e.ctrlKey && !e.shiftKey && !e.altKey)
		{
			switch (key)
			{
				case 66 : // B
				case 98 : // b
					this.executeCommand('Bold');
					return BX.PreventDefault(e);
				case 105 : // i
				case 73 : // I
					this.executeCommand('Italic');
					return BX.PreventDefault(e);
				case 117 : // u
				case 85 : // U
					this.executeCommand('Underline');
					return BX.PreventDefault(e);
			}
		}
	}

	// Tab and Shift+Tab
	if (key == 16) // Shift
	{
		var _this = this;
		this._bShiftPressed = true;
		setTimeout(function(){_this._bShiftPressed = false;}, 200);
	}
	else if (key == 9)
	{
		// It's SHIFT + TAB
		if (this._bShiftPressed || e.shiftKey)
		{
			this.executeCommand('Outdent');
			return BX.PreventDefault(e);
		}
		else // It's TAB
		{
			this.executeCommand('Indent');
			return BX.PreventDefault(e);
		}
	}

	// Ctrl + V or Shift + Ins
	if ((e.ctrlKey && key == 86) ||
		((this._bShiftPressed || e.shiftKey) && key == 45))
		this.OnCtrlV();
};

BXHTMLEditor.prototype.OnCtrlV = function()
{
	var arUsedId = {}, _this = this;

	setTimeout(function(){
		CheckChilds(_this.pEditorDocument.body, {
			func: function(node)
			{
				if (node.nodeType != 1)
					return;

				var id = node.id;
				if (!id || id.substr(0, 5) != "bxid_")
					return;

				if (arUsedId[id] === true)
				{
					var oTag = _this.GetBxTag(node);
					if (oTag.tag)
					{
						oTag.id = null;
						delete oTag.id;
						node.id = '';
						node.removeAttribute('id');

						var newId = _this.SetBxTag(node, copyObj(oTag));

						// Temp hack. TODO: save comp params in oTag.params
						if (oTag.tag == 'component2' && _this.pComponent2Taskbar)
						{
							_this.pComponent2Taskbar.SetParams({id: newId, params: copyObj(_this.pComponent2Taskbar.GetParams({id: id}))});
						}
						arUsedId[newId] = true;
					}
				}
				else
				{
					arUsedId[id] = true;
				}
			},
			obj: _this
		});
		arUsedId = null;

	}, 500);
};

BXHTMLEditor.prototype.OnPaste = function (e)
{
	var clipboardHTML = this.GetClipboardHTML();
	var AutoDetectWordContent = true;
	if (AutoDetectWordContent)
	{
		var RE_MS_WORD = /<\w[^>]*(( class="?MsoNormal"?)|(="mso-))/gi;
		if (RE_MS_WORD.test(clipboardHTML))
		{
			if (confirm(BX_MESS.MaybeTextFromWord))
			{
				this.bNotFocus = true;
				this.pMainObj.OpenEditorDialog("pasteword", false, 450);
				e.returnValue = false;
				e.cancelBubble = true;
			}
			else
				return;
		}
	}
};

BXHTMLEditor.prototype.GetClipboardHTML = function()
{
	var oDiv = document.createElement('DIV');
	oDiv.style.visibility = 'hidden';
	oDiv.style.overflow = 'hidden';
	oDiv.style.position = 'absolute';
	oDiv.style.width = 1;
	oDiv.style.height = 1;

	document.body.appendChild(oDiv);
	oDiv.innerHTML = '';

	var oRange = document.body.createTextRange();
	oRange.moveToElementText(oDiv);
	oRange.execCommand("Paste");

	var sData = oDiv.innerHTML;
	oDiv.innerHTML = '';

	return sData;
};

BXHTMLEditor.prototype.OnKeyPress = function (e, bEdit)
{
	this.bFirstClick = true;
	if(!e)
		e = window.event;

	if(e.keyCode == 27)
	{
		if (this.oPublicDialog && !this.CheckSubdialogs())
			return this.oPublicDialog.Close();

		if (window.oBXEditorDialog && window.oBXEditorDialog.isOpen)
			return window.oBXEditorDialog.Close();

		if (window.oBXContextMenu && oBXContextMenu.menu && oBXContextMenu.menu.IsVisible())
			oBXContextMenu.menu.PopupHide();
	}

	if (!bEdit && e.keyCode == 13)
	{
		var target = e.target || e.srcElement;
		if (target && target.nodeName.toUpperCase() == 'TEXTAREA')
			return true;
		return BX.PreventDefault(e);
	}
	return true;
};

BXHTMLEditor.prototype.RemoveElements = function (arParentElement, tagName, arAttributes, oRange)
{
	var arChildren;
	arChildren = arParentElement.children;
	if(arChildren)
	{
		for(var i=0; i<arChildren.length; i++)
		{
			var elChild = arChildren[i];

			this.RemoveElements(elChild, tagName, arAttributes);

			if(elChild.tagName.toLowerCase() != tagName.toLowerCase())
				continue;


			var bEqual = true;
			for(var attrName in arAttributes)
			{
				attrValue = arAttributes[attrName];
				switch(attrName.toLowerCase())
				{
					case 'style':
						var styleValue = attrValue.toLowerCase();
						var re = /([^:]+):[^;]+/g;
						var arr;
						while((arr = re.exec(styleValue)) != null)
						{
							var styleName = RegExp.$1;
							if(elChild.style.cssText.toLowerCase().indexOf(styleName.toLowerCase())==-1)
							{
								bEqual = false;
								break;
							}
						}
						break;
					case 'class' :
						if(elChild.getAttribute('className', 0) != attrValue)
							bEqual = false;
						break;
					default:
						if(elChild.getAttribute(attrNalue, 0) != attrValue)
							bEqual = false;
				}
			}

			if(bEqual)
			{
				elChild.insertAdjacentHTML('beforeBegin', elChild.innerHTML);
				elChild.parentElement.removeChild(elChild);
			}
		}
	}
};

BXHTMLEditor.prototype.WrapSelectionWith = function (tagName, arAttributes)
{
	this.SetFocus();
	var oRange, oSelection;

	if (!tagName)
		tagName = 'SPAN';

	var sTag = 'FONT', i, pEl, arTags, arRes = [];

	try{this.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}
	this.executeCommand("FontName", "bitrixtemp");
	try{this.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}

	arTags = this.pEditorDocument.getElementsByTagName(sTag);

	for(i = arTags.length - 1; i >= 0; i--)
	{
		if (arTags[i].getAttribute('face') != 'bitrixtemp')
			continue;

		pEl = BX.create(tagName, arAttributes, this.pEditorDocument);
		arRes.push(pEl);

		while(arTags[i].firstChild)
			pEl.appendChild(arTags[i].firstChild);

		arTags[i].parentNode.insertBefore(pEl, arTags[i]);
		arTags[i].parentNode.removeChild(arTags[i]);
	}

	return arRes;
};

BXHTMLEditor.prototype.RidOfNode = function (pNode, bHard)
{
	if (!pNode || pNode.nodeType != 1)
		return;

	var i, nodeName = pNode.tagName.toLowerCase();
	if (nodeName == 'span' || nodeName == 'strike' || nodeName == 'font') // Check node names
	{
		if (bHard !== true)
		{
			for (i = pNode.attributes.length - 1; i >= 0; i--)
			{
				if (BX.util.trim(pNode.getAttribute(pNode.attributes[i].nodeName.toLowerCase())) != "")
					return false; // Node have attributes, so we cant get rid of it without loosing info
			}
		}

		var arNodes = pNode.childNodes;
		while(arNodes.length > 0)
			pNode.parentNode.insertBefore(arNodes[0], pNode);

		pNode.parentNode.removeChild(pNode);
		this.OnEvent("OnSelectionChange");
		return true;
	}

	return false;
}

BXHTMLEditor.prototype.GetToolbarSet = function ()
{
	return this.arToolbarSet;
};

BXHTMLEditor.prototype.GetTaskbarSet = function ()
{
	return this.arTaskbarSet;
};

BXHTMLEditor.prototype.SelectElement = function (pElement)
{
	if(this.pEditorWindow.getSelection)
	{
		var oSel = this.pEditorWindow.getSelection();
		oSel.selectAllChildren(pElement);
		oRange = oSel.getRangeAt(0);
	}
	else
	{
		this.pEditorDocument.selection.empty();
		var oRange = this.pEditorDocument.selection.createRange();

		if (oRange.moveToElementText)
			oRange.moveToElementText(pElement);
		oRange.select();
	}
	return oRange;
};

BXHTMLEditor.prototype.CollapseSelection = function ()
{
	if(this.pEditorWindow.getSelection)
	{
		var oSel = this.pEditorWindow.getSelection();
		if (oSel.collapseToEnd)
			oSel.collapseToEnd();
	}
	else if (this.pEditorDocument && this.pEditorDocument.selection && this.pEditorDocument.selection.empty)
	{
		this.pEditorDocument.selection.empty();
	}
}

BXHTMLEditor.prototype.GetSelectedNode = function(bOnlyNode)
{
	var oSelection;
	if(this.pEditorDocument.selection && !BX.browser.IsIE9())  // IE, exept IE9
	{
		oSelection = this.pEditorDocument.selection;

		var s = oSelection.createRange();
		if(oSelection.type=="Control")
			return s.commonParentElement();

		if(s.parentElement() && (s.text == s.parentElement().innerText || bOnlyNode))
			return (s.parentElement().childNodes.length == 1) ? s.parentElement().firstChild : s.parentElement();

		return s;
	}
	else
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection || oSelection.rangeCount!=1)
			return false;

		var oRange, container;
		oRange = oSelection.getRangeAt(0);
		container = oRange.startContainer;
		if(container.nodeType != 3)
		{
			if(container.nodeType == 1 && container.childNodes.length <= 0)
				return container;
			else if(oRange.endOffset - oRange.startOffset == container.childNodes.length)
				return container
			else if(oRange.endOffset - oRange.startOffset < 2)
				return container.childNodes[oRange.startOffset];
			else
				return false;
		}

		return container;
	}
};


BXHTMLEditor.prototype.GetSelectionObjects = function ()
{
	var oSelection;
	if(this.pEditorDocument.selection && !BX.browser.IsIE9()) // IE, exept IE9
	{
		oSelection = this.pEditorDocument.selection;
		var s = oSelection.createRange();

		if(oSelection.type=="Control")
			return s.commonParentElement();

		return s.parentElement();
	}
	else // FF
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection)
			return false;
		var oRange;
		var container, temp;
		var res = [];
		for(var i = 0; i < oSelection.rangeCount; i++)
		{
			oRange = oSelection.getRangeAt(i);
			container = oRange.startContainer;
			if(container.nodeType != 3)
			{
				if(container.nodeType == 1 && container.childNodes.length <= 0)
					res[res.length] = container;
				else
					res[res.length] = container.childNodes[oRange.startOffset];
			}
			else
			{
				temp = oRange.commonAncestorContainer;
				while(temp && temp.nodeType == 3)
					temp = temp.parentNode;
				res[res.length] = temp;
			}
		}
		if(res.length > 1)
			return res;
		return res[0];
	}
};

BXHTMLEditor.prototype.OptimizeHTML = function (str)
{
	// TODO: kill links without text and names
	// TODO: Kill multiple line ends

	var
		iter = 0,
		bReplasing = true,
		arTags = ['b', 'em', 'font', 'h\\d', 'i', 'li', 'ol', 'small', 'span', 'strong', 'u', 'ul'],
		replaceEmptyTags = function(){i--; bReplasing = true; return ' ';},
		re, tagName, i, l;

	while(iter++ < 20 && bReplasing)
	{
		bReplasing = false;
		for (i = 0, l = arTags.length; i < l; i++)
		{
			tagName = arTags[i];
			re = new RegExp('<'+tagName+'[^>]*?>\\s*?</'+tagName+'>', 'ig');
			str = str.replace(re, replaceEmptyTags);

			re = new RegExp('<' + tagName + '\\s+?[^>]*?/>', 'ig');
			str = str.replace(re, replaceEmptyTags);

			// Replace <b>text1</b>    <b>text2</b> ===>>  <b>text1 text2</b>
			if (tagName !== 'li')
			{
				re = new RegExp('<((' + tagName + '+?)(?:\\s+?[^>]*?)?)>([\\s\\S]+?)<\\/\\2>\\s*?<\\1>([\\s\\S]+?)<\\/\\2>', 'ig');
				str = str.replace(re, function(str, b1, b2, b3, b4)
					{
						bReplasing = true;
						return '<' + b1 + '>' + b3 + ' ' + b4 + '</' + b2 + '>';
					}
				);
			}
		}
	}
	return str;
};


BXHTMLEditor.prototype.GetSelectionObject = function ()
{
	var res = this.GetSelectionObjects();
	if(res && res.constructor == Array)
	{
		var root = res[0];
		for(var i = 1; i < res.length; i++)
			root = BXFindParentElement(root, res[i]);

		return root;
	}
	return res;
};

BXHTMLEditor.prototype.CreateEditorElement = function (sTagname, arParams, arStyles)
{
	return BXCreateElement(sTagname, arParams, arStyles, this.pEditorDocument);
};

BXHTMLEditor.prototype.CreateCustomElement = function(sTagName, arParams)
{
	var ob = new window[sTagName]();
	ar_CustomElementS.push(ob);
	ob.pMainObj = this;
	ob.pDocument = this.pDocument;
	ob.CreateElement = BXCreateElement;

	if(arParams)
	{
		var sParamName;
		for(sParamName in arParams)

			if(sParamName.toLowerCase() == '_oncreate')
				arParams[sParamName].apply(ob);
			else
				ob[sParamName] = arParams[sParamName];
	}
	if (ob._Create)
		ob._Create();
	return ob;
};


BXHTMLEditor.prototype.AddEventHandler = function (eventName, pEventHandler, pObject)
{
	if(!this.arEventHandlers[eventName])
		this.arEventHandlers[eventName] = [];
	this.arEventHandlers[eventName].push([pEventHandler, pObject]);
};

BXHTMLEditor.prototype.OnEvent = function (eventName, arParams)
{
	if(!this.arEventHandlers[eventName])
		return true;

	var res = true;
	for(var i=0; i < this.arEventHandlers[eventName].length; i++)
	{
		if(this.arEventHandlers[eventName][i][1])
		{
			if(!arParams)
				arParams = [];
			if(!this.arEventHandlers[eventName][i][0].apply(this.arEventHandlers[eventName][i][1], arParams))
				res = false;
		}
		else
		{
			if(!this.arEventHandlers[eventName][i][0](arParams))
				res = false;
		}
	}
	return res;
};

BXHTMLEditor.prototype.FullResize = function()
{
	var ws = BX.GetWindowInnerSize();
	window.__fswindow.style.width = parseInt(ws.innerWidth) + "px";
	window.__fswindow.style.height = parseInt(ws.innerHeight) + "px";

	this.OnEvent('OnFullResize', []);
};

BXHTMLEditor.prototype.ClearPosCache = function ()
{
	CACHE_DISPATCHER['BXTaskbarset_VPos_' + this.name] = null;
	CACHE_DISPATCHER['BXTasktab_VPos_' + this.name] = null;
	CACHE_DISPATCHER['pEditorFrame_' + this.name] = null;
	CACHE_DISPATCHER['pEditorFrame'] = null;
	CACHE_DISPATCHER['BXToolbarSet_pos_0'] = null;
	CACHE_DISPATCHER['BXToolbarSet_pos_1'] = null;
	CACHE_DISPATCHER['BXToolbarSet_pos_2'] = null;
	CACHE_DISPATCHER['BXToolbarSet_pos_3'] = null;
};

BXHTMLEditor.prototype.SetFullscreen = function (bFull)
{
	this.ClearPosCache();
	var _this = this;

	if(bFull)
	{
		var ws = BX.GetWindowInnerSize();
		BX.addClass(this.pWnd, "bxedmain-fullscreen");
		this.pDocument.body.style.overflow = "hidden";
		this.__oldSize = [this.pWnd.style.width, this.pWnd.style.height];

		var innerWidth = parseInt(ws.innerWidth);
		var innerHeight = parseInt(ws.innerHeight);

		if(BX.browser.IsIE() && !IEplusDoctype)
			innerWidth += 18;

		this.pWnd.style.width = innerWidth + "px";
		this.pWnd.style.height = innerHeight + "px";
		window.scrollTo(0, 0);

		window.__fswindow = this.pWnd;
		window._bxonresize = window.onresize || null;
		window.onresize = function(){_this.FullResize();};
	}
	else
	{
		BX.removeClass(this.pWnd, "bxedmain-fullscreen");
		this.pDocument.body.style.overflow = "auto";
		if (!this.__oldSize)
			return;
		this.pWnd.style.width = this.__oldSize[0];
		this.pWnd.style.height = this.__oldSize[1];
		window.__fswindow = null;
		window.onresize = window._bxonresize || null;

		var pWnd = this.arTaskbarSet[3].pWnd;
		if (parseInt(pWnd.offsetHeight) >= 245)
		{
			pWnd.style.height = '245px';
			var pParWnd = this.arTaskbarSet[2].pParentWnd;
			var display = pParWnd.style.display;
			pParWnd.style.display = 'none';
			var _this = this;
			setTimeout(function() {pParWnd.style.display = display; _this.IEplusDoctypePatchSizes();}, 10);
		}
		this._DisplaySourceFrame(true);
	}

	this.arTaskbarSet[2]._SetTmpClass(true);

	this.arTaskbarSet[2].Resize();
	this.arTaskbarSet[3].Resize();

	this.bFullscreen = bFull;
	if(this.pDocument.getElementById('fullscreen'))
		this.pDocument.getElementById('fullscreen').value = (bFull ? 'Y' : 'N');

	if (IEplusDoctype)
	{
		this.IEplusDoctypePatchSizes();
		// IE in standart mode needs to refresh DOM tree
		var pWnd = this.arTaskbarSet[3].arTaskbars[0].pWnd;
		pWnd.parentNode.appendChild(pWnd); // TODO: check this ??????????
	}

	this.SetCursorFF();

	this.OnEvent('OnFullscreen', [bFull]);
};


BXHTMLEditor.prototype.ParseStyles = function ()
{
	this.arStyles = [];
};


BXHTMLEditor.prototype._FuncOnChange = function(obj, type, subtype)
{
	return function(){obj._OnChange(type, subtype);}
};

BXHTMLEditor.prototype.OnChange = function(type, subtype)
{
	if(this.bSkipChanges == true)
		return;

	if(!subtype)
		subtype = "";


	if(this.sOnChangeLastType != type || this.sOnChangeLastSubType != subtype)
	{
		this._OnChange(type, subtype);
		return;
	}

	if(this.pOnChangeTimer)
		clearTimeout(this.pOnChangeTimer);

	this.pOnChangeTimer = setTimeout(this._FuncOnChange(this, type, subtype), 1000);
};

BXHTMLEditor.prototype.IsChanged = function()
{
	if (!this.bFirstClick)
		return false;
	if(this.bNotSaved)
		return true;
	this.SaveContent();

	var firstContent = this.sFirstContent.trim();
	var curContent = this.GetContent().trim();
	if(firstContent.length == curContent.length && firstContent == curContent)
		return false;

	return true;
};


BXHTMLEditor.prototype._OnChange = function(type, subtype)
{
	this.sOnChangeLastType = type;
	this.sOnChangeLastSubType = subtype;

	var curContent = this.pEditorDocument.body.innerHTML;
	if(this.sLastContent.length==curContent.length && this.sLastContent == curContent)
		return;

	var xx = this.sLastContent;
	this.sLastContent = curContent;

	if(BX.browser.IsIE())
	{
		if(type!='Undo' && type!='Redo')
		{
			var lastUndoItem = this.arUndoBuffer.length;
			if(this.iUndoPos + 1 < lastUndoItem)
			{
				this.arUndoBuffer.length = this.iUndoPos + 1;
				lastUndoItem = this.iUndoPos + 1;
			}

			var pos = false;
			if(this.pEditorDocument.selection)
			{
				if(this.pEditorDocument.selection.type == 'Text')
					pos = this.pEditorDocument.selection.createRange().getBookmark();
			}

			this.arUndoBuffer.push({'type': type, 'subtype': subtype, 'content': curContent, 'pos': pos});
			var cnt = lastUndoItem - this.arConfig["undosize"];
			if(cnt>0)
			{
				this.arUndoBuffer.reverse();
				this.arUndoBuffer.length = this.arUndoBuffer.length - cnt;
				this.arUndoBuffer.reverse();
			}

			this.iUndoPos = this.arUndoBuffer.length - 1;
		}
		this.bNotSaved = (this.iUndoPos > 0);
	}
	else
	{
		if(this.iUndoPos < 0)
			this.iUndoPos = 0;
		else
			this.bNotSaved = true;
	}

	this.OnEvent("OnChange");
};

BXHTMLEditor.prototype.SetXXdo = function(type)
{
	var arUndoInfo = this.arUndoBuffer[this.iUndoPos];
	this.pEditorDocument.body.innerHTML = arUndoInfo['content'];
	this._OnChange(type);
	this.sLastContent = this.pEditorDocument.body.innerHTML;

	if(arUndoInfo['pos'])
	{
		if(this.pEditorDocument.selection)
		{
			var oRange = this.pEditorDocument.selection.createRange();
			oRange.moveToBookmark(arUndoInfo['pos']);
			oRange.select();
		}
	}
};

BXHTMLEditor.prototype.UndoStatus = function()
{
	return !(this.iUndoPos < 1 || this.arUndoBuffer.length <= 0);
};

BXHTMLEditor.prototype.Undo = function(pos)
{
	if(!this.UndoStatus())
		return;

	if(this.iUndoPos<pos)
		this.iUndoPos = 0;
	else
		this.iUndoPos = this.iUndoPos - pos;

	this.SetXXdo("Undo");
};

BXHTMLEditor.prototype.RedoStatus = function(pos)
{
	return !(this.iUndoPos + 1 >= this.arUndoBuffer.length || this.arUndoBuffer.length<=0);
};

BXHTMLEditor.prototype.Redo = function(pos)
{
	if(!this.RedoStatus())
		return;

	if(this.iUndoPos + pos >= this.arUndoBuffer.length)
		this.iUndoPos = this.arUndoBuffer.length-1;
	else
		this.iUndoPos = this.iUndoPos + pos;

	this.SetXXdo("Redo");
};

BXHTMLEditor.prototype.Clean = function(pos)
{
	return;
	this.pFrame = null;
	this.pWnd.pMainObj = null;
	this.pWnd = null;
	this.pForm = null;
	this.pComponent2Taskbar = null;
	this.pLoaderFrame = null;

	for (var evname in this.arEventHandlers)
		this.arEventHandlers[evname] = null;
	this.arEventHandlers = null;

	var l = this.arToolbarSet.length;
	for (var i=0;i<l;i++)
		this.arToolbarSet[i] = null;

	var l = this.arTaskbarSet.length;
	for (var i=0;i<l;i++)
		this.arTaskbarSet[i] = null;

	this.lineNumCont = null;
	this.pSourceFrame.onkeydown = null;
	this.pSourceFrame = null;
	this.pEditorWindow = null;
	this.pEditorFrame = null;
	this.pEditorDocument.pMainObj = null;
	this.pEditorDocument = null;
	this.pDocument = null;
	this.pParser = null;

};

BXHTMLEditor.prototype.IEPatchSizesHandler = function(value)
{
	var _this = this;
	setTimeout(function(){_this.IEplusDoctypePatchSizes()}, 100);
}

BXHTMLEditor.prototype.IEplusDoctypePatchSizes = function(value)
{
	return;
	if (!IEplusDoctype)
		return;

	var tbs2 = this.arTaskbarSet[2];
	var tbs3 = this.arTaskbarSet[3];
	if (isNaN(value))
	{
		if (tbs3.pWnd.style.display != 'none')
			value = parseInt(tbs3.pWnd.style.height);
		else
			value = 0;
	}
	else
		value = value - 35;

	if (value == 0) // padding-bottom when hide bottom taskbarset
		value = - 33;

	var edHeight = parseInt((this.bFullscreen) ? BX.GetWindowInnerSize().innerHeight : this.arConfig["height"]);
	var centerRowH = edHeight - value - 114;

	if (isNaN(centerRowH))
		return;
	this.pFrame.rows[1].style.height = centerRowH + "px";


	if (this.sEditorMode == 'html')
	{
		this.pEditorFrame.style.height = centerRowH + "px";
	}
	else if (this.sEditorMode == 'split')
	{
		this.pEditorFrame.style.height = (Math.round(centerRowH / 2) - 3) + "px";
		this.pSourceFrame.style.height = (Math.round(centerRowH / 2) - 4) + "px";
	}
	else if (this.sEditorMode == 'code')
	{
		this.pSourceFrame.style.height = (centerRowH - 6)+ "px";
	}

	if (tbs2.bShowing)
	{
		var tb, titleCell, dataCell;
		var l = tbs2.arTaskbars.length;

		var bH, tH = 25;
		if (l > 1)
		{
			bH = 25;
			tbs2.pWnd.style.height = (centerRowH - 45) + "px";
			tbs2.pBottomColumn.style.height = bH + "px";
		}
		else
			bH = 0;

		var dH = centerRowH - tH - bH - 6;
		for(var i = 0; i < l; i++)
		{
			tb = tbs2.arTaskbars[i].pWnd;
			tb.rows[0].cells[0].style.height = tH + "px"; // title cell
			tb.rows[1].cells[0].style.height = dH + "px"; // data cell
		}
	}


	var o, btt;
};

BXHTMLEditor.prototype.OnSpellCheck = function()
{
	BX.closeWait();
	var alreadyCheck = false;
	if (this.pMainObj.arConfig["spellCheckFirstClient"] == "Y")
		alreadyCheck = SpellCheck_MS(this.pMainObj.pEditorDocument.body);

	var usePspell = this.pMainObj.arConfig["usePspell"];
	//var useCustomSpell = this.pMainObj.arConfig["useCustomSpell"];
	var useCustomSpell = "N";

	if (!alreadyCheck)
	{
		if (usePspell == "Y" || useCustomSpell == "Y")
		{
			this.bNotFocus = true;
			this.pMainObj.OpenEditorDialog("spellcheck", false, 400, {BXLang: BXLang, usePspell: usePspell, useCustomSpell: useCustomSpell}, true);
		}
		else
		{
			alert(BX_MESS.SpellCheckNotInstalled);
		}
	}
};

BXHTMLEditor.prototype.SaveConfig = function(sTarget, data)
{
	if (typeof data != 'object')
		data = {};

	data.edname = this.name;

	switch(sTarget)
	{
		case "tooltips":
			data.tooltips = this.showTooltips4Components ? "Y" : "N";
			break;
		case "visual_effects":
			data.visual_effects = this.visualEffects ? "Y" : "N";
			break;
		case "render_components":
			data.render_components = this.bRenderComponents ? "Y" : "N";
			break;
	}
	return BX.ajax.post(settings_page_path + '&target=' + sTarget, data);
};

BXHTMLEditor.prototype.GetConfig = function(oCallBack)
{
	//Tooltips
	this.showTooltips4Components = SETTINGS[this.name].showTooltips4Components;

	// Visual effects
	this.visualEffects = SETTINGS[this.name].visualEffects;

	oCallBack.func.apply(oCallBack.obj);
};

BXHTMLEditor.prototype.RestoreConfig = function()
{
	return BX.ajax.post(settings_page_path + '&target=unset&edname=' + this.name, {}, function(){alert(BX_MESS.RestoreSettingsMess);});
};

BXHTMLEditor.prototype.GetTaskbarConfig = function(name)
{
	if (SETTINGS[this.name].arTaskbarSettings[name])
		return SETTINGS[this.name].arTaskbarSettings[name];
	else if (arTaskbarSettings_default[name])
		return arTaskbarSettings_default[name];
	else
		return {show : true, set: 2, active: false};
}

BXHTMLEditor.prototype.CheckTaskbar = function(taskbar)
{
	return BXTaskbar && typeof BXTaskbar == 'object' && BXTaskbar.pMainObj && BXTaskbar.name && !BXTaskbar.bDeleted;
};

BXHTMLEditor.prototype.SetBxTag = function(pElement, params)
{
	var id;
	if (params.id || pElement && pElement.id)
	{
		id = params.id || pElement.id;
	}

	if (!id)
	{
		id = 'bxid_' + Math.round(Math.random() * 1000000);
	}
	else
	{
		if (this.bxTags[id])
		{
			if (!params.tag)
				params.tag = this.bxTags[id].tag;
		}
	}

	params.id = id;
	if (pElement)
		pElement.id = params.id;

	this.bxTags[params.id] = params;
	return params.id;
};

BXHTMLEditor.prototype.GetBxTag = function(id)
{
	if (id)
	{
		if (typeof id != "string" && id.id)
			id = id.id;

		if (id && id.length > 0 && this.bxTags[id] && this.bxTags[id].tag)
		{
			this.bxTags[id].tag = this.bxTags[id].tag.toLowerCase();
			return this.bxTags[id];
		}
	}

	return {tag: false};
}

BXHTMLEditor.prototype.Add2BxTag = function(id, params)
{
	if (typeof id != "string")
		id = id.id;

	if (!id)
		return;

	var oTag = this.GetBxTag(id), k;
	for(k in params)
	{
		if (typeof params[k] != 'function')
		{
			oTag.params[k] = params[k];
		}
	}
};

BXHTMLEditor.prototype.CheckSubdialogs = function()
{
	if (window.oBXEditorDialog && window.oBXEditorDialog.isOpen || this.oTransOverlay.bShowed)
		return true;
	return false;
};

BXHTMLEditor.prototype.AuthFailureHandler = function(name, arAuthResult)
{
	if (name != this.name || this._authShowed)
		return;

	var _this = this;
	function auth_callback()
	{
		_this._authShowed = false;
		if (_this.__authFailureHandlerCallback)
			_this.__authFailureHandlerCallback();
	}

	this._authShowed = true;
	var authDialog = new BX.CAuthDialog({
		content_url: '/bitrix/admin/fileman_editor_dialog.php',
		auth_result: arAuthResult,
		callback: BX.delegate(function(){
			if (auth_callback)
				auth_callback()
		}, this)
	});

	authDialog.Show();

	BX.addCustomEvent(authDialog, 'onWindowUnRegister', function()
	{
		_this._authShowed = false;
		if (_this.__authFailureHandlerCallbackClose)
			_this.__authFailureHandlerCallbackClose();
	});
};

BXHTMLEditor.prototype.InsertHtmlEx = function(html, timeout)
{
	if (!timeout)
		timeout = 50;
	var id = 'tmp_bxid_' + Math.round(Math.random() * 1000000);
	this.insertHTML('<a id="' + id + '" href="#" _moz_editor_bogus_node="on">+</a>');
	var pDoc = this.pEditorDocument;
	setTimeout(function(){
		var pTmp = pDoc.getElementById(id);
		if (pTmp)
		{
			pTmp.innerHTML = html;
			setTimeout(function(){
				var pTmp = pDoc.getElementById(id);
				if (pTmp)
				{
					for (var i = pTmp.childNodes.length - 1; i >= 0; i--)
						pTmp.parentNode.insertBefore(pTmp.childNodes[i], pTmp);
					if (pTmp.parentNode)
						pTmp.parentNode.removeChild(pTmp);
				}
			}, timeout);
		}
	}, timeout);
}


function BXContextMenuOnclick(e)
{
	removeEvent(this.pMainObj.pEditorDocument, "click", BXContextMenuOnclick);
	oBXContextMenu.menu.PopupHide();
};

function BXStyles(pMainObj)
{
	this.pMainObj = pMainObj;
	this.arStyles = [];
	this.sStyles = '';

	BXStyles.prototype.Parse = function (styles, template_path)
	{
		this.templatePath = template_path || '';
		this.sStyles = styles;
		this.arStyles = BXStyleParser.Parse(styles);
	};

	BXStyles.prototype.GetStyles = function (sFilter)
	{
		if(this.arStyles[sFilter.toUpperCase()])
			return this.arStyles[sFilter.toUpperCase()];
		return [];
	};

	BXStyles.prototype.SetToDocument = function(pDocument)
	{
		var pHeads = pDocument.getElementsByTagName("HEAD");
		if(pHeads.length != 1)
			return;

		var cur = pDocument.getElementsByTagName("STYLE");
		for(var i = 0; i < cur.length; i++)
			cur[i].parentNode.removeChild(cur[i]);

		var xStyle = pDocument.createElement("STYLE");
		pHeads[0].appendChild(xStyle);
		var styles = this.sStyles;

		//try{
		if(BX.browser.IsIE())
			pDocument.styleSheets[0].cssText = styles;
		else
			xStyle.appendChild(pDocument.createTextNode(styles));
		//}catch(e){}
	};
}

BX.ready(BXEditorLoad);