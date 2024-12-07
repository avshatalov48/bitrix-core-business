/*
pMainObj - объект который хранит в себе все методы, создан для координации между компонентами
	pEditorFrame - ссылка на IFRAME для визуального редактирования
	pFrame - ссылка на таблицу, в которой все находится
	pDocument - ccылка на родительский документ
	pEditorDocument
	pWnd - ссылка на DIV, в котором все будет находиться
	arToolbarSet - массив ссылок на тулбарсеты
	pToolbar - ссылка на объект тулбара
      - AddButton
      - CheckButton
      -
*/
//var x =
function BXHTMLEditor(name)
{
	this.arBarHandlersCache = [];
	this.name = name;
	if(BXEditorLoaded)
		this.onLoad();
	else
		BXEditorRegister(this);
}

var BX_START_EXEC_TIME = new Date().getTime();

BXHTMLEditor.prototype.CreateElement = BXCreateElement;

BXHTMLEditor.prototype.onLoad = function()
{
	BX_START_EXEC_TIME = new Date().getTime();
	var obj = this;
	this.bShowed = true;
	this.bDragging = false;
	this.bNotSaved = true;	//!!!!!false;
	var name = this.name;
	this.className = 'BXHTMLEditor';
	this.arEventHandlers = Array();
	this.pDocument = document;
	this.bTableBorder = false;
	//this.pDocument.pMainObj = this;
	this.pWnd = this.pDocument.getElementById(name + '_object');
	this.pWnd.pMainObj = this;
	this.arToolbarSet = Array();
	this.arTaskbarSet = Array();
	this.pParser = new BXParser(this);
	this.bEditSource = false;
	this.arConfig = eval('('+this.pDocument.getElementById(name + '_config').value+')');

	if(!this.arConfig["width"])
		this.arConfig["width"] = 750;
	this.pWnd.style.width = this.arConfig["width"]+"px";

	if(!this.arConfig["height"])
		this.arConfig["height"] = 500;
	this.pWnd.style.height = this.arConfig["height"]+"px";

	if(this.arConfig["arToolbars"])
		this.arToolbars = this.arConfig["arToolbars"];
	else
		this.arToolbars = ["standart", "style", "formating", "source", "template"];

	var OnSubmit = function()
	{
		if(obj.bShowed)
			obj.SaveContent(true);
	}

	this.pForm = BXFindParentByTagName(this.pWnd, "FORM");
	if(this.pForm)
	{
		if(this.pForm.addEventListener)
			this.pForm.addEventListener("submit", OnSubmit, false);
		else
			this.pForm.attachEvent("onsubmit", OnSubmit);
	}

	/*
	this.pTempFrame = this.pDocument.getElementById(name + '_progress_object');
	//this.pTempFrame.style.width = 780+"px";
	//this.pTempFrame.style.height = 510+"px";
	this.pTempFrame.style.width = "100%";
	this.pTempFrame.style.height = "100%";
	*/

	var pFrame = this.pDocument.createElement("TABLE");
	pFrame.pMainObj = this;
	pFrame.cellSpacing = 0; pFrame.cellPadding = 0; pFrame.border = 0;
	pFrame.className = "bxedmainframe";
	pFrame.style.width = "100%";
	pFrame.style.height = "100%";

	this.pFrame = this.pWnd.appendChild(pFrame);

	var r, c;
	r = pFrame.insertRow(-1); r.style.height="0%"; c = r.insertCell(-1); this.arToolbarSet[0] = new BXToolbarSet(c, this, false);
	r = pFrame.insertRow(-1); r.style.height="100%"; c = r.insertCell(-1);
	//{
		var incTable = c.appendChild(this.pDocument.createElement("TABLE")); incTable.cellSpacing = 0; incTable.cellPadding = 0; incTable.border = 0; incTable.style.width = "100%"; incTable.style.height = "100%";
		var incRow = incTable.insertRow(-1);

		c = incRow.insertCell(-1); c.style.width = "0%"; this.arToolbarSet[1] = new BXToolbarSet(c, this, true);
		c = incRow.insertCell(-1); c.style.width = "100%";
		var incTable2 = c.appendChild(this.pDocument.createElement("TABLE")); incTable2.cellSpacing = 0; incTable2.cellPadding = 0; incTable2.border = 0; incTable2.style.width = "100%"; incTable2.style.height = "100%";
		//{
			var incRow2 = incTable2.insertRow(-1); incRow2.style.height = "0%";
			//{
				var incCell2 = incRow2.insertCell(-1);
				incCell2.colSpan = 3;
				this.arTaskbarSet[0] = new BXTaskbarSet(incCell2, this, 0);
			//}
			incRow2 = incTable2.insertRow(-1); incRow2.style.height = "100%";
			//{
				incCell2 = incRow2.insertCell(-1); incCell2.style.width = "0%"; this.arTaskbarSet[1] = new BXTaskbarSet(incCell2, this, 1);
				cEditor = incRow2.insertCell(-1); cEditor.style.width = "100%";
				incCell2 = incRow2.insertCell(-1); incCell2.style.width = "0%"; this.arTaskbarSet[2] = new BXTaskbarSet(incCell2, this, 2);
			//}
			incRow2 = incTable2.insertRow(-1); incRow2.style.height = "0%";
			//{
				incCell2 = incRow2.insertCell(-1);
				incCell2.colSpan = 3;
				this.arTaskbarSet[3] = new BXTaskbarSet(incCell2, this, 3);
			//}
		//}

		c = incRow.insertCell(-1); c.style.width = "0%"; this.arToolbarSet[2] = new BXToolbarSet(c, this, true);
	//}
	r = pFrame.insertRow(-1); r.style.height="0%"; c = r.insertCell(-1); this.arToolbarSet[3] = new BXToolbarSet(c, this, false);


	//this.pFrame.style.border = "1px #FF0000 solid";
	//return;


	//this.pTempFrame.style.display = "none";

	/*
	cEditor.unselectable = "on";
	var pFrameX = cEditor.appendChild(this.CreateElement("IMG", {src: '/images/top_logo.gif'}));
	*/

	/* ------- html-editor frame -----*/
	var ifrm = document.createElement("IFRAME");
	ifrm.id = "ed_"+name;
	ifrm.setAttribute("src", "about:blank");
	//ifrm.style.border = "1px #FF0000 solid";
	//ifrm.style.width = "400px";//(BXIsIE()?"100%":"99%");
	//ifrm.style.height = "200px";//(BXIsIE()?"100%":"99%");
	ifrm.style.width = (BXIsIE()?"100%":"99%");
	ifrm.style.height = (BXIsIE()?"100%":"99%");
	this.pEditorFrame = cEditor.appendChild(ifrm);


	if(this.pEditorFrame.contentDocument)
		this.pEditorDocument = this.pEditorFrame.contentDocument;
	else
		this.pEditorDocument = this.pEditorFrame.contentWindow.document;

	/*
	this.pEditorDocument.open();
	this.pEditorDocument.write('<html><head></head><body id="C"></body></html>');
	this.pEditorDocument.close();
	*/
	//!!this.pEditorDocument.body.innerHTML = '';

	/*
	if(this.pEditorDocument.body.contentEditable)
		this.pEditorDocument.body.contentEditable=true;
	*/

	this.pEditorWindow = this.pEditorFrame.contentWindow;
	this.pEditorDocument.className = "pEditorDocument";
	this.pEditorDocument.pMainObj = this;
	// --------------------------------


	// ------- edit sources frame -----
	/*
	ifrm = document.createElement("IFRAME");
	ifrm.id = "src_"+name;
	ifrm.setAttribute("src", "about:blank");
	ifrm.style.width = "100%";
	ifrm.style.height = "100%";
	ifrm.style.display = "none";
	this.pSourceFrame = cEditor.appendChild(ifrm);
	if(this.pSourceFrame.contentDocument)
		this.pSourceDocument = this.pSourceFrame.contentDocument;
	else
		this.pSourceDocument = this.pSourceFrame.contentWindow.document;

	if(BXIsIE())
	{
		this.pSourceDocument.open();
		this.pSourceDocument.write('<html><body style="margin: 0px 0px 0px 2px; background-color: #FFFFFF; font-family: Courier New; font-size: 10pt; "></body></html>');
		this.pSourceDocument.close();
	}
	else
	{
		this.pSourceDocument.body.innerHTML = '';
		this.pSourceDocument.body.style.cssText = "margin: 0px 0px 0px 2px; background-color: #FFFFFF; font-family: Courier New; font-size: 10pt;";
	}


	if(this.pSourceDocument.body.contentEditable)
		this.pSourceDocument.body.contentEditable=true;

	this.pSourceWindow = this.pSourceFrame.contentWindow;
	this.pSourceDocument.className = "pSourceDocument";
	this.pSourceDocument.pMainObj = this;
	----------*/
	ifrm = this.pDocument.createElement("TEXTAREA");//, {}, {'width': '100%', 'height': '100%', 'display':'none'});
	ifrm.style.display = "none";
	ifrm.wrap = "OFF";
	ifrm.style.width = "100%";
	ifrm.style.height = "100%";
	ifrm.style.overflow = "auto";
	this.pSourceFrame = cEditor.appendChild(ifrm);
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
					var selectionStart = this.selectionStart;
					var selectionEnd = this.selectionEnd;
					var scrollTop = this.scrollTop;
					var scrollLeft = this.scrollLeft;
					this.value = this.value.substring(0, selectionStart)+ replaceWith + this.value.substring(selectionEnd);
				    this.focus();
					this.setSelectionRange(selectionStart + (selectionStart != selectionEnd?0:1), selectionStart + replaceWith.length);
					this.scrollTop = scrollTop;
					this.scrollLeft = scrollLeft;
					return false;
				}
			}
		}


	pBXEventDispatcher.__Add(this);
	BXHTMLEditor.prototype.onDragDrop = function (e)
	{
		if(this.nLastDragNDropComponent && this.nLastDragNDropComponent>0)
		{
			var obj = this;
			setTimeout(function ()
					{
						var pComponent = obj.pEditorDocument.getElementById(obj.nLastDragNDropComponent);
						if(obj.pEditorWindow.getSelection)
							obj.pEditorWindow.getSelection().selectAllChildren(pComponent);
						obj.onClick(e);
						//alert(pComponent);
					}, 10
				);
		}
	}


	BXHTMLEditor.prototype.__ShowTableBorder = function (pTable, bShow)
	{
		var arTableBorderStyles = ["border", "borderBottom", "borderBottomColor", "borderBottomStyle", "borderBottomWidth", "borderCollapse", "borderColor", "borderLeft", "borderLeftColor", "borderLeftStyle", "borderLeftWidth", "borderRight", "borderRightColor", "borderRightStyle", "borderRightWidth", "borderStyle", "borderTop", "borderTopColor", "borderTopStyle", "borderTopWidth", "borderWidth"]
		if(pTable.border == "0")
		{
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

			var pCell, arCells = pTable.getElementsByTagName("TD");
			for(var j=0; j<arCells.length; j++)
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
	}

	BXHTMLEditor.prototype.Show = function (flag)
	{
		this.bShowed = flag;
		if(flag && this.pWnd.style.display=='none')
		{
			this.pWnd.style.display='block';
		}
		else if(!flag && this.pWnd.style.display!='none')
			this.pWnd.style.display='none';
	}

	BXHTMLEditor.prototype.ShowTableBorder = function (bShow)
	{
		if(this.bTableBorder == bShow)
			return false;

		this.bTableBorder = bShow;
		var arTables = this.pEditorDocument.getElementsByTagName("TABLE");
		for(var i=0; i<arTables.length; i++)
			this.__ShowTableBorder(arTables[i], bShow);

		return true;
	}



	BXHTMLEditor.prototype.onClick = function (e)
	{
		if(this.pOnChangeTimer)
			clearTimeout(this.pOnChangeTimer);
		var obj = this;
		this.pOnChangeTimer = setTimeout(function (){obj.OnEvent("OnSelectionChange");}, 400);
		//this.OnEvent("OnSelectionChange");
	}

	//
	this.pSourceFrame.onblur = function (e)
	{
		obj.pEditorFrame.onfocus(e);
	}

	this.pSourceFrame.onfocus = function (e)
	{
		if(obj.bEditSource)
			return;

		obj.bEditSource = true;
		if(obj.sEditorMode=='split')
		{
			obj.SetCodeEditorContent(obj.GetEditorContent(true, true));
			obj.sEditorSplitMode = 'code';
			obj.OnEvent("OnChangeView", [this.sEditorMode, this.sEditorSplitMode]);
		}
	}

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
	}
	this.value = this.pDocument.getElementById(name).value+'';

	BXStyleParser.Create();
	this.oStyles = new BXStyles(this);
/*
	if(this.arConfig["styles"])
		this.oStyles.Parse(this.arConfig["styles"]);
*/
	if(this.arConfig["TEMPLATE"])
		this.SetTemplate(this.arConfig["TEMPLATE"]["ID"], this.arConfig["TEMPLATE"]);


	var el;
	// добавляем все тулбары и кнопки в них
	for(var sToolBarId in arToolbars)
	{
		if(BXSearchInd(this.arToolbars, sToolBarId)<0)
			continue;

		var pToolbar = new BXToolbar(this, arToolbars[sToolBarId][0]);

		for(var i=0; i<arToolbars[sToolBarId][1].length; i++)
		{
			var arButton = arToolbars[sToolBarId][1][i];
			if(arButton!='separator')
			{
				el = this.CreateCustomElement(arButton[0], arButton[1]);
				pToolbar.AddButton(el);
			}
			else
			{
				el = this.CreateCustomElement('BXButtonSeparator');
				pToolbar.AddButton(el);
			}
		}

		if(arDefaultTBPositions[sToolBarId])
			this.arToolbarSet[arDefaultTBPositions[sToolBarId][0]].AddToolbar(pToolbar, arDefaultTBPositions[sToolBarId][1], arDefaultTBPositions[sToolBarId][2]);
		else
			this.arToolbarSet[0].AddToolbar(pToolbar, 100, 0);
	}

	// добавляем все таскбары
	pBXEventDispatcher.OnEditorEvent("OnCreate", this);

	this.SetView("html");

	this.SetFocus();
}

BXHTMLEditor.prototype.SetContent = function(content)
{
	this.value = content;
	//alert('Set='+this.value);
}

BXHTMLEditor.prototype.GetContent = function()
{
	//alert('Get='+this.value);
	return this.value.toString()+'';
}

BXHTMLEditor.prototype.PasteAsText = function(text)
{
	text = bxhtmlspecialchars(text);
	text = text.replace(/\r/g, '');
	text = text.replace(/\n/g, '<br/>');
	this.insertHTML(text) ;
}

BXHTMLEditor.prototype.PasteWord = function (text, arParams)
{
	text = text.replace(/<!--\[.*?\]-->/g, ""); //<!--[.....]-->	-	<!--[if gte mso 9]>...<![endif]-->
	text = text.replace(/<!\[.*?\]>/g, "");		//	<! [if !vml]>
	text = text.replace(/<\\?\?xml[^>]*>/gi, "");	//<xml...>, </xml...>
	//text = text.replace(/<o:p>.*?<\/o:p>/g, "&nbsp;");
	text = text.replace(/<\/?[a-z1-9]+:[^>]*>/gi, "");	//<o:p...>, </o:p>
	text = text.replace(/<([a-z1-9]+[^>]*) class=([^ |>]*)(.*?>)/gi, "<$1$3");
	text = text.replace(/<([a-z1-9]+[^>]*) [a-z]+:[a-z]+=([^ |>]*)(.*?>)/gi, "<$1$3"); //	xmlns:v="urn:schemas-microsoft-com:vml"

	// only in tags:
	text = text.replace(/mso-[^:]*:"[^"]*";/gi, "");
	text = text.replace(/mso-[^;'"]*;*(\n|\r)*/gi, "");
	text = text.replace(/\s*margin: 0cm 0cm 0pt\s*;/gi, "");
	text = text.replace(/\s*margin: 0cm 0cm 0pt\s*"/gi, "\"");

	/*
	text = text.replace(/\s*page-break-after[^;]*;/gi, "");
	text = text.replace(/\s*page-break-before: [^\s;]+;?"/gi, "\"" ) ;

	text = text.replace(/<([a-z][]*?>) style=['"]tab-interval:[^'"]*['"]/gi, "");
http://office.microsoft.com/en-gb/assistance/HA010549981033.aspx
	tab-stops
	language
	text-underline
	text-effect
	text-line-through
	font-color
	horiz-align
	list-image-1
	list-image-2
	list-image-3
	separator-image
	table-border-color-dark
	table-border-color-light
	vert-align
	vnd.ms-excel.numberformat
	*/
	//html = html.replace( /\s*TEXT-INDENT: 0cm\s*;/gi, "" ) ;
	//html = html.replace( /\s*TEXT-INDENT: 0cm\s*"/gi, "\"" ) ;
	//html = html.replace( /\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"" ) ;
	//html = html.replace( /\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"" ) ;
	//html = html.replace( /\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"" ) ;
	//html = html.replace( /\s*tab-stops:[^;"]*;?/gi, "" ) ;
	//html = html.replace( /\s*tab-stops:[^"]*/gi, "" ) ;


	//if(bIgnoreFont)
	//{
	//	html = html.replace( /\s*face="[^"]*"/gi, "" ) ;
	//	html = html.replace( /\s*face=[^ >]*/gi, "" ) ;
	//	html = html.replace( /\s*FONT-FAMILY:[^;"]*;?/gi, "" ) ;
	//}

	//if ( bRemoveStyles )
	//	html = html.replace( /<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3" ) ;

	//html =  html.replace( /\s*style="\s*"/gi, '' ) ;
	//html = html.replace( /<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/gi, '&nbsp;' ) ;
	//html = html.replace( /<SPAN\s*[^>]*><\/SPAN>/gi, '' ) ;

	// Remove Lang attributes
	text = text.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3") ;

	// Remove empty tags
	text = text.replace( /<SPAN\s*>(.*?)<\/SPAN>/gi, '$1' );
	text = text.replace( /<FONT\s*>(.*?)<\/FONT>/gi, '$1' );
	text = text.replace( /<([^\s>]+)[^>]*>\s*<\/\1>/g, '' );
	text = text.replace( /<([^\s>]+)[^>]*>\s*<\/\1>/g, '' );
	text = text.replace( /<([^\s>]+)[^>]*>\s*<\/\1>/g, '' );

	this.insertHTML(text);
}

BXHTMLEditor.prototype.LoadContent = function()
{
	switch(this.sEditorMode)
	{
		case 'code':
			this.SetCodeEditorContent(this.GetContent());
			break;
		case 'split':
			this.SetCodeEditorContent(this.GetContent())
			this.SetEditorContent(this.GetContent())
			break;
		case 'html':
			this.SetEditorContent(this.GetContent());
	}
}

BXHTMLEditor.prototype.SaveContent = function(bFull)
{
	//alert('this.sEditorMode='+this.sEditorMode);
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

	if(bFull)
		this.pDocument.getElementById(this.name).value = this.value;

	//alert('SaveContent='+this.sEditorMode);
}

BXHTMLEditor.prototype.LoadTemplateParams = function(templateID)
{
	var sURL = '/bitrix/admin/fileman_get_xml.php?op=sitetemplateparams&lang='+BXLang+'&site='+BXSite+'&templateID='+templateID;
	var ob = this.GetData(sURL);
	this.SetTemplate(ob["ID"], ob);
}

BXHTMLEditor.prototype.SetTemplate = function (templateID, arTemplateParams)
{
	if(this.templateID && this.templateID == templateID)
		return;

	if(arTemplateParams === false)
		return;

	if(!arTemplateParams)
	{
		this.LoadTemplateParams(templateID);
		return ;
	}

	/*
	arTemplateParams["ID"] - id
	arTemplateParams["NAME"] - name
	arTemplateParams["FOLDERS"] - [
		{"ID": id, "NAME": "name", "PARENT": parent_folder},
		...
		]
	arTemplateParams["COMPONENTS"] - [
		{"PATH": path, "NAME": name, "ICON": icon, "FOLDER": folderID, "FULL_PATH": phisical path},
		...
		]
	arTemplateParams["STYLES"] - styles (string)
	arTemplateParams["STYLES_TITLE"] - styles title
	*/
	this.templateID = arTemplateParams["ID"];

	if(this.pTemplateListbox)
		this.pTemplateListbox.SelectByVal(this.templateID);

	this.arTemplateParams = arTemplateParams;
	if(this.pComponentTaskbar)
		this.pComponentTaskbar.BuildList();

	// изменить компоненты
	this.SaveContent();
	this.LoadContent();

	// изменить стили
	this.oStyles.Parse(this.arTemplateParams["STYLES"]);

	// внедрить стили
	this.oStyles.SetToDocument(this.pEditorDocument);
	this.OnEvent("OnTemplateChanged");
}

BXHTMLEditor.prototype.FindComponentByPath = function (path)
{
	var v = this.arTemplateParams["COMPONENTS"];
	for(var i=0; i<v.length; i++)
		if(v[i]["PATH"] && v[i]["PATH"] == path)
			return v[i];

	return false;
}

BXHTMLEditor.prototype.SetFocus = function ()
{
	try
	{
		if(this.bEditSource)
		{
		}
		else
		{
			//!!!this.pEditorWindow.contentWindow.focus();
			if(this.pEditorWindow.focus)
				this.pEditorWindow.focus();
			else
				this.pEditorDocument.body.focus();
		}
	}
	catch(e) {}
}

BXHTMLEditor.prototype.insertHTML = function(sValue)
{
	this.SetFocus();

	if(BXIsIE())
	{
		var oRng = this.pEditorDocument.selection.createRange();
		oRng.pasteHTML(sValue);
		oRng.collapse(false);
		oRng.select();
	}
	else
	{
		try{
			this.pEditorWindow.document.execCommand('insertHTML', false, sValue);
		}catch(e){};
	}
}


BXHTMLEditor.prototype.onContextMenu = function (e)
{
	var obj = this;
	obj.OnEvent("OnSelectionChange");

	if(obj.pEditorWindow.event)
		e = obj.pEditorWindow.event;

	var arFramePos = GetRealPos(obj.pEditorFrame);

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX - obj.pEditorDocument.body.scrollLeft;
		e.realY = e.pageY - obj.pEditorDocument.body.scrollTop;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX;
		e.realY = e.clientY;
	}

	if(arFramePos)
	{
		e.realX += arFramePos["left"];
		e.realY += arFramePos["top"];
	}

	this.CreateCustomElement("BXContextMenu", {'x':e.realX, 'y':e.realY});

	if(e.stopPropagation)
	{
		e.preventDefault() ;
		e.stopPropagation() ;
	}
	else
	{
		e.cancelBubble	= true;
		e.returnValue	= false;
	}
	return false;
}

BXHTMLEditor.prototype.executeCommand = function(commandName, sValue)
{
	this.SetFocus();
	try{
		var res = this.pEditorWindow.document.execCommand(commandName, false, sValue);
	}catch(e){};
	this.SetFocus();
	this.OnEvent("OnSelectionChange");
	return res;
}

BXHTMLEditor.prototype.queryCommand = function(commandName)
{
	//this.SetFocus();
	var sValue = '' ;
	try{
		if(!this.pEditorDocument.queryCommandEnabled(commandName))
			return null;
	}catch(e){return null;}

	try{
		return this.pEditorDocument.queryCommandValue(commandName);
	}catch(e) {}

	return null;
}


BXHTMLEditor.prototype.queryCommandState = function(commandName)
{
	//this.SetFocus();
	var sValue = '' ;
	try{
		if(!this.pEditorDocument.queryCommandEnabled(commandName))
			return 'DISABLED';
	}catch(e){return 'DISABLED';}

	try{
		return (this.pEditorDocument.queryCommandState(commandName)?'CHECKED':'ENABLED');
	}catch(e) {
		return 'ENABLED';
	}

	return 'DISABLED';
}


BXHTMLEditor.prototype.update = function()
{
}

BXHTMLEditor.prototype.GetEditorContent = function(bOptimized, bFormatted)
{
	if(bOptimized || bFormatted)
	{
		var bBorders = this.bTableBorder;
		if(bBorders)
			this.ShowTableBorder(false);

		this.pParser.Parse();
		if(bOptimized)
			this.pParser.Optimize();

		if(bBorders)
			this.ShowTableBorder(true);

		sContent = this.pParser.GetHTML(true);
		sContent = sContent.replace(/^[\s\S]*?<body.*?>/i, "");
		sContent = sContent.replace(/<\/body>[\s\S]*?$/i, "");
		sContent = sContent.replace(/<\/html>[\s\S]*?$/i, "");
		sContent = sContent.replace(/\xA0/g, "&nbsp;");

		return sContent;//bFormatted);
	}

	/*
	var html = this.pEditorDocument.createRange();
	var xxx = this.pEditorDocument;
	html.selectNode(this.pEditorWindow.document.body);
	html = html.toString();
	*/
	return this.pEditorDocument.body.innerHTML;
}

BXHTMLEditor.prototype.SavePosition = function()
{
	if(this.bEditSource)
	{

	}
	else
	{
		var tmpid = 'x' + Math.round(Math.random()*1000000) + 'x';
		var oRange = this.pEditorDocument.selection.createRange();

		var oRangeLeft = oRange.duplicate();
		oRangeLeft.collapse(true);
		oRangeLeft.pasteHTML("<span id='L"+tmpid+"'/>");
		var elLeft = this.pEditorDocument.getElementById('L'+tmpid);

		var oRangeRight = oRange.duplicate();
		oRangeRight.collapse(false);
		oRangeRight.pasteHTML("<span id='R"+tmpid+"'/>");
		var elRight = this.pEditorDocument.getElementById('R'+tmpid);

		//elRight.parentElement.removeChild(elRight);
		//elLeft.parentElement.removeChild(elLeft);
	}
}

BXHTMLEditor.prototype.RestorePosition = function()
{
	if(this.bEditSource)
	{

	}
	else
	{

	}
}

BXHTMLEditor.prototype.__FFFocus = function(e)
{
	this.removeEventListener("focus", BXHTMLEditor.prototype.__FFFocus, false);
	this.pMainObj.pEditorDocument.designMode = "on";
	this.pMainObj = null;
}

BXHTMLEditor.prototype.SetEditorContent = function(sContent)
{
	//ToDo: вставить обработку целой страницы вместе с <html><head>....</head><body>....</body>
	sContent = sContent.replace(/^[\s\S]*?<body.*?>/i, "");
	sContent = sContent.replace(/<\/body>[\s\S]*?$/i, "");

	//анализируем контент в виде текста, вырезаем PHP, заменяем на <img>, вставляем в документ
	try{this.pEditorDocument.designMode='off';}catch(e){}
	this.pEditorDocument.open();
	if(this.arConfig["bWithoutPHP"])
		this.pEditorDocument.write('<html><head></head><body>'+sContent+'</body></html>');
	else
		this.pEditorDocument.write('<html><head></head><body>'+this.pParser.ParsePHP(sContent)+'</body></html>');
	this.pEditorDocument.close();

	if(this.bTableBorder)
	{
		this.bTableBorder = false; //чтобы не выйти из функции ShowTableBorder
		this.ShowTableBorder(true);
	}

	//this.pEditorDocument.body.innerHTML = this.pParser.ParsePHP(sContent);
	//alert(this.pEditorDocument.body.innerHTML);
	//удаляем лишний мусор
	//this.pParser.MakeValid();
	//получаем результат
	//sContent = this.pParser.GetEditorCode();

	//sContent = sContent.replace(/^[\s\S]*?<body.*?>/i, "");
	//sContent = sContent.replace(/<\/body>[\s\S]*?$/i, "");

	//this.pEditorWindow.document.body.innerHTML = sContent;

	//при помощи DOM в нем обрабатываем дополнительные теги (якоря, ...), дорабатываем PHP скрипты
	//this.pParser.ConvertTags();

	if(BXIsIE())
	{
		this.pEditorDocument.body.contentEditable = true;
	}
	else
	{
		try{
			this.pEditorDocument.designMode='on';
			this.pEditorDocument.execCommand("styleWithCSS", false, true);
		}catch(e){
			this.pEditorWindow.pMainObj = this;
			this.pEditorWindow.addEventListener("focus", BXHTMLEditor.prototype.__FFFocus, false);
		}
	}

	this.oStyles.SetToDocument(this.pEditorDocument);
	this.pEditorDocument.className = 'pEditorDocument';
	this.pEditorDocument.pMainObj = this;

	var obj = this;
	pBXEventDispatcher.SetEvents(this.pEditorDocument);
	addEvent(this.pEditorDocument, 'contextmenu', function (e){obj.onContextMenu(e);});
	addEvent(this.pEditorDocument, 'dragdrop', function (e){obj.onDragDrop(e);});
	addEvent(this.pEditorDocument, 'mouseup', function (e){obj.onClick(e);});
	addEvent(this.pEditorDocument, 'keyup', function (e){obj.onClick(e);});
	addEvent(this.pEditorDocument, 'focus', function (e){obj.onClick(e);});

	pBXEventDispatcher.OnEditorEvent("OnSetEditorContent", this);
}

BXHTMLEditor.prototype.WrapNodeWith = function (node)
{
	if(node.nodeType == 1)
		alert('element:'+node.innerHTML);
	else
		alert('text:'+node.nodeValue);
}

BXHTMLEditor.prototype.RemoveElements = function (arParentElement, tagName, arAttributes, oRange)
{
	var arChildren;
	arChildren = arParentElement.children;
	if(arChildren)
	{
		for(var i=0; i<arChildren.length; i++)
		{
			var elChild = arChildren[i];
			//if(elChild.nodeType!=1)
			//	continue;

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
							alert(elChild.style.cssText.toLowerCase().indexOf(styleName.toLowerCase()) + ':'+elChild.style.cssText.toLowerCase()+', '+styleName.toLowerCase());
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
}

BXHTMLEditor.prototype.WrapSelectionWith = function (tagName, arAttributes)
{
	this.SetFocus();

	//this.pParser.Parse(this.getContent);
	//return;


	var oRange, oSelection;

	//oRange = this.pEditorDocument.selection.createRange();

/*
	//ставим "точку" на месте левой границы
	var oRangeLeft = oRange.duplicate();
	oRangeLeft.collapse(true);
	var tmpid = 'x' + Math.round(Math.random()*1000000) + 'x';
	oRangeLeft.pasteHTML("<span id='L"+tmpid+"'/>");
	var elLeft = this.pEditorDocument.getElementById('L'+tmpid);

	//ставим "точку" на месте правой границы
	var oRangeRight = oRange.duplicate();
	oRangeRight.collapse(false);
	oRangeRight.pasteHTML("<span id='R"+tmpid+"'/>");
	var elRight = this.pEditorDocument.getElementById('R'+tmpid);
return;

	var elElement = this.pEditorDocument.createElement(tagName);
	for(var attr in arAttributes)
	{
		switch(attr.toLowerCase())
		{
			case 'style' :
				elElement.style.cssText = arAttributes[attr];
				break;
			case 'class' :
				elElement.className = arAttributes[attr];
				break;
			default:
				elElement[attr] = arAttributes[attr];
		}
	}
	*/


	if(this.pEditorDocument.selection)
	{
		var arB, pEl, arNodes, j;
		arB = this.pEditorDocument.getElementsByTagName("FONT");
		for(var i=arB.length-1; i>=0; i--)
		{
			if(arB[i].face)
			{
				arB[i].setAttribute("__bxtemp", arB[i].face);
				arB[i].removeAttribute('face');
			}
		}
		//alert('1='+this.pEditorDocument.body.innerHTML);

		this.executeCommand("FontName", "bitrixtemp");

		//alert('2='+this.pEditorDocument.body.innerHTML);

	 	arB = this.pEditorDocument.getElementsByTagName("FONT");
		for(i=arB.length-1; i>=0; i--)
		{
			if(arB[i].face && arB[i].face=='bitrixtemp')
			{
				pEl = this.pEditorDocument.createElement(tagName);
				for(var attr in arAttributes)
				{
					switch(attr.toLowerCase())
					{
						case 'style' :
							pEl.style.cssText = arAttributes[attr];
							break;
						case 'class':
							SAttr(pEl, 'className', arAttributes[attr]);
							break;
						default:
							pEl.setAttribute(attr, arAttributes[attr]);
					}
				}
				arNodes = arB[i].childNodes;
				while(arNodes.length>0)
					pEl.appendChild(arNodes[0]);
				arB[i].parentNode.insertBefore(pEl, arB[i]);
				arB[i].parentNode.removeChild(arB[i]);
			}
		}

		//alert('3='+this.pEditorDocument.body.innerHTML);

		arB = this.pEditorDocument.getElementsByTagName('FONT');
		for(i=arB.length-1; i>=0; i--)
		{
			if(!arB[i].getAttribute("__bxtemp"))
				continue;
			arB[i].face = arB[i].getAttribute("__bxtemp");
			arB[i].removeAttribute('__bxtemp');
		}

		//alert('Last='+this.pEditorDocument.body.innerHTML);

		/*
		oRange = this.pEditorDocument.selection.createRange();

		var oRangeLeft = oRange.duplicate();
		oRangeLeft.collapse(true);
		//var tmpid = 'x' + Math.round(Math.random()*1000000) + 'x';
		//oRangeLeft.pasteHTML("<span id='L"+tmpid+"'/>");
		//var elLeft = this.pEditorDocument.getElementById('L'+tmpid);
		var iMovedLeft =  - oRangeLeft.moveStart("character", -10000000);
		//alert('L:'+iMovedLeft);

		//ставим "точку" на месте правой границы
		var oRangeRight = oRange.duplicate();
		oRangeRight.collapse(false);
		//oRangeRight.pasteHTML("<span id='R"+tmpid+"'/>");
		//var elRight = this.pEditorDocument.getElementById('R'+tmpid);
		var iMovedRight = - oRangeRight.moveStart("character", -10000000);
		//alert(iMovedRight);

		elElement.innerHTML = oRange.htmlText;
		this.RemoveElements(elElement, tagName, arAttributes, oRange);
		//alert('what:'+oRange.htmlText);
		//alert(elElement.outerHTML);
		//oRange.pasteHTML('');
		oRange.pasteHTML(elElement.outerHTML);

		this.pParser.MakeValid();

		//alert(this.pParser.GetHTML());

		this.pEditorDocument.body.innerHTML = this.pParser.GetHTML();
		oRange = this.pEditorDocument.selection.createRange();
		oRange.moveEnd("character", -10000000);
		oRange.moveEnd("character", iMovedRight);
		oRange.moveStart("character", iMovedLeft);
		oRange.select();
		*/

	}
	else
	{
		var arB, pEl, arNodes, j, sBoldTag = 'B';
		arB = this.pEditorDocument.getElementsByTagName(sBoldTag);
		for(var i=arB.length-1; i>=0; i--)
		{
			pEl = this.pEditorDocument.createElement("FONT");
			pEl.setAttribute("__bxtemp", "yes");
			arNodes = arB[i].childNodes;
			while(arNodes.length>0)
			{
				//alert(arNodes[0].innerHTML);
				pEl.appendChild(arNodes[0]);
			}
			arB[i].parentNode.insertBefore(pEl, arB[i]);
			arB[i].parentNode.removeChild(arB[i]);
		}
		//alert('1='+this.pEditorDocument.body.innerHTML);

		try{this.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}
		this.executeCommand("Bold", true);
		try{this.pEditorDocument.execCommand("styleWithCSS", false, true);}catch(e){}

		//alert('2='+this.pEditorDocument.body.innerHTML);
	 	arB = this.pEditorDocument.getElementsByTagName(sBoldTag);
		for(i=arB.length-1; i>=0; i--)
		{
			pEl = this.pEditorDocument.createElement(tagName);
			for(var attr in arAttributes)
			{
				switch(attr.toLowerCase())
				{
					case 'style' :
						pEl.style.cssText = arAttributes[attr];
						break;
					case 'class':
						SAttr(pEl, 'className', arAttributes[attr]);
						break;
					default:
						pEl.setAttribute(attr, arAttributes[attr]);
				}
			}
			arNodes = arB[i].childNodes;
			while(arNodes.length>0)
				pEl.appendChild(arNodes[0]);
			arB[i].parentNode.insertBefore(pEl, arB[i]);
			arB[i].parentNode.removeChild(arB[i]);
		}

		//alert('3='+this.pEditorDocument.body.innerHTML);

		arB = this.pEditorDocument.getElementsByTagName('FONT');
		for(i=arB.length-1; i>=0; i--)
		{
			if(!arB[i].getAttribute("__bxtemp") || arB[i].getAttribute("__bxtemp", 2) != "yes")
				continue;

			pEl = this.pEditorDocument.createElement(sBoldTag);
			arNodes = arB[i].childNodes;
			while(arNodes.length>0)
				pEl.appendChild(arNodes[0]);
			arB[i].parentNode.insertBefore(pEl, arB[i]);
			arB[i].parentNode.removeChild(arB[i]);
		}

		//alert('Last='+this.pEditorDocument.body.innerHTML);
		/*
		oSelection = this.pEditorWindow.getSelection();
		oRange = oSelection.getRangeAt(0);
		//this.RemoveElements(elElement, tagName, arAttributes);
		oRange.surroundContents(elElement);
		*/
	}
}

BXHTMLEditor.prototype.GetToolbarSet = function ()
{
	return this.arToolbarSet;
}

BXHTMLEditor.prototype.GetTaskbarSet = function ()
{
	return this.arTaskbarSet;
}

BXHTMLEditor.prototype.SelectElement = function (pElement)
{
	if(this.pEditorWindow.getSelection)
		this.pEditorWindow.getSelection().selectAllChildren(pElement);
	else
	{
		this.pEditorDocument.selection.empty();
		var oRange = this.pEditorDocument.selection.createRange();
		oRange.moveToElementText(pElement) ;
		oRange.select();
	}
}

BXHTMLEditor.prototype.GetSelectedNode = function ()
{
	var oSelection;
	if(this.pEditorDocument.selection)
	{
		oSelection = this.pEditorDocument.selection;
		var s = oSelection.createRange();
		if(oSelection.type=="Control")
			return s.commonParentElement();

		if(s.parentElement() && s.text==s.parentElement().innerText)
			return s.parentElement();
		return s;
	}
	else
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection || oSelection.rangeCount!=1) return false;
		var oRange, container;
		oRange = oSelection.getRangeAt(0);
		container = oRange.startContainer;
		if(container.nodeType!=3)
		{
			if(container.nodeType==1 && container.childNodes.length<=0)
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
}


BXHTMLEditor.prototype.GetSelectionObjects = function ()
{
	var oSelection;
	if(this.pEditorDocument.selection)
	{
		oSelection = this.pEditorDocument.selection;
		var s = oSelection.createRange();
		if(oSelection.type=="Control")
			return s.commonParentElement();

		return s.parentElement();
	}
	else
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection)
			return false;
		var oRange;
		var container, temp;
		var res = Array();
		for(var i=0; i<oSelection.rangeCount; i++)
		{
			oRange = oSelection.getRangeAt(i);
			container = oRange.startContainer;
			if(container.nodeType!=3)
			{
				if(container.nodeType==1 && container.childNodes.length<=0)
					res[res.length] = container;
				else
					res[res.length] = container.childNodes[oRange.startOffset];
			}
			else
			{
				temp = oRange.commonAncestorContainer;
				while(temp && temp.nodeType==3)
					temp = temp.parentNode;
				res[res.length] = temp;
			}
		}
		if(res.length>1)
			return res;
		return res[0];
	}
}

BXHTMLEditor.prototype.GetSelectionObject = function ()
{
	var res = this.GetSelectionObjects();
	if(res && res.constructor == Array)
	{
		var root = res[0];
		for(var i=1; i<res.length; i++)
			root = BXFindParentElement(root, res[i]);

		return root;
	}
	return res;
}


BXHTMLEditor.prototype.CreateElement = BXCreateElement;
BXHTMLEditor.prototype.createEditorElement = function (sTagname, arParams, arStyles)
{
	return BXCreateElement(sTagname, arParams, arStyles, this.pEditorDocument);
}

function BXContextMenu()
{
	BXContextMenu.prototype._Create = function ()
	{
		var pElement, i, j, obj = this;
		this.className = 'BXContextMenu';

		BXPopupWindow.Create();

		this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
		this.pPopupNode.className = "bxcontextmenu";
		this.onclick = function (e){BXPopupWindow.Hide();};

		var t = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '0', 'cellPadding': '0'});
		t.onclick = function (e){BXPopupWindow.Hide();};
		t.className = 'bxcontextmenu';
		var r, c, arMenuItems = [], el, el_params;
		pElement = this.pMainObj.GetSelectionObject();
		var arUsed = [], strPath;
		for(i=0; i<arCMButtons["DEFAULT"].length; i++)
			arMenuItems.push(arCMButtons["DEFAULT"][i]);

		while(pElement && (pElementTemp = pElement.parentNode) != null)
		{
			if(pElementTemp.nodeType==1 && pElement.tagName && (strPath = pElement.tagName.toUpperCase()) && strPath != 'TBODY' && !arUsed[strPath])
			{
				arUsed[strPath] = pElement;
				if(arCMButtons[strPath])
				{
					arMenuItems.push('separator');
					for(i=0; i<arCMButtons[strPath].length; i++)
						arMenuItems.push(arCMButtons[strPath][i]);
				}
			}
			else
			{
				pElement = pElementTemp;
				continue;
			}
		}

		for(i=0; i<arMenuItems.length; i++)
		{
			if(arMenuItems[i] == 'separator')
			{
				r = t.insertRow(-1);
				c = r.insertCell(-1);
				c.colSpan = "2";
				r.className = 'bxcontextmenuitemseparator';
				c.innerHTML = '<img src="/bitrix/images/1.gif" width="1" height="1"/>';
			}
			else
			{
				r = t.insertRow(-1);
				c = r.insertCell(-1);
				c.style.width = "0%";
				c.id = "left";
				el_params = arMenuItems[i][1];
				el_params["no_actions"] = true;
				el = BXPopupWindow.CreateCustomElement(arMenuItems[i][0], el_params);
				el.pMainObj = this.pMainObj;
				c.appendChild(el.pWnd);

				c = r.insertCell(-1);
				c.style.width = "100%";
				c.innerHTML = el.name;
				c.noWrap = true;

				r.obj = el;
				r.onmouseover = function (e){this.className = 'bxcontextmenuitemover';};
				r.onmouseout = function (e){this.className = 'bxcontextmenuitem';};
				r.onclick = function (e){BXPopupWindow.Hide(); this.obj.onClick();};
				r.className = 'bxcontextmenuitem';
			}
		}

		this.pPopupNode.appendChild(t);

		BXPopupWindow.SetCurStyles();
		BXPopupWindow.Show(this.x, this.y, this.pPopupNode);
	}
}



BXHTMLEditor.prototype.CreateCustomElement = function(sTagName, arParams)
{
	var ob = eval('new '+sTagName+'()');
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
	ob._Create();
	return ob;
}



BXHTMLEditor.prototype.AddEventHandler = function (eventName, pEventHandler, pObject)
{
	if(!this.arEventHandlers[eventName])
		this.arEventHandlers[eventName] = new Array();
	this.arEventHandlers[eventName][this.arEventHandlers[eventName].length] = [pEventHandler, pObject];
}

BXHTMLEditor.prototype.OnEvent = function (eventName, arParams)
{
	if(!this.arEventHandlers[eventName])
		return true;

	var res = true;
	for(var i=0; i<this.arEventHandlers[eventName].length; i++)
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
}

BXHTMLEditor.prototype.GetData = function (sUrl, arParams, pCallback)
{
	var pObj = this;
	if(!this.pXML)
	{
		this.pXML = new BXXML();
		this.pXML.pMainObj = this;
	}

	this.pXML.Load(sUrl, arParams);
	return this.pXML.Unserialize();

	return true;
	if(!this.pLoaderFrame)
	{
		this.pLoaderFrame = this.CreateElement("IFRAME", {name: "edloader_"+name});
		this.pLoaderFrame.setAttribute("src", "about:blank");
		this.pLoaderFrame.style.width = "100";
		this.pLoaderFrame.style.height = "101";
		this.pLoaderFrame = this.pWnd.appendChild(this.pLoaderFrame);
		this.pLoaderWindow = this.pEditorFrame.contentWindow;
		this.pLoaderWindow.str = 'ssss';

		this.pLoaderForm = this.pWnd.appendChild(this.CreateElement("FORM", {target: "edloader_"+name, method: 'post'}));
		this.pLoaderHidden = this.pLoaderForm.appendChild(this.CreateElement("INPUT", {type: "hidden", name: 'value'}));
		//this.CreateElement("INPUT", {type: "submit", name: '', value: ''});
		/*
		if(this.pEditorFrame.contentDocument)
			this.pEditorDocument = this.pEditorFrame.contentDocument;
		else
			this.pEditorDocument = this.pEditorFrame.contentWindow.document;

		this.pEditorDocument.open();
		this.pEditorDocument.write('<html><head></head><body id="C"></body></html>');
		this.pEditorDocument.close();

		this.pEditorDocument.className = "pEditorDocument";
		this.pEditorDocument.pMainObj = this;
		*/
	}

	this.pLoaderForm.action = sUrl;
	this.pLoaderHidden.value = BXPHPVal(arParams);
	this.pLoaderWindow.pCallback = pCallback;
	this.pLoaderForm.submit();
	return true;
}

BXHTMLEditor.prototype.SetFullscreen = function (bFull)
{
	if(BXIsIE() && !this.__pUnderFrame)
	{
		this.__pUnderFrame = this.pDocument.createElement("IFRAME");
		this.__pUnderFrame.setAttribute("src", "");
	    this.__pUnderFrame.frameBorder	= "0";
	    this.__pUnderFrame.scrolling = "no";
		this.__pUnderFrame.style.position = "absolute";
		this.__pUnderFrame.unselectable = "on";
		this.__pUnderFrame.style.display = "none";
		this.__pUnderFrame.style.left = 0;
		this.__pUnderFrame.style.top = 0;
		this.pDocument.body.appendChild(this.__pUnderFrame);
	}

	if(bFull)
	{
		this.pWnd.style.position = "absolute";
		this.pWnd.style.top = "0px";
		this.pWnd.style.left = "0px";
		this.pDocument.body.style.overflow = "hidden";
		this.__oldSize = [this.pWnd.style.width, this.pWnd.style.height];

		if(BXIsIE())
		{
			this.__pUnderFrame.style.display = "block";
			this.__pUnderFrame.style.width = this.pDocument.body.clientWidth;
			this.__pUnderFrame.style.height = this.pDocument.body.clientHeight;
			this.__pUnderFrame.style.zIndex = 1000;
			this.pWnd.style.zIndex = 2000;
		}

		this.pWnd.style.width = this.pDocument.body.clientWidth;
		this.pWnd.style.height = this.pDocument.body.clientHeight;
		window.scrollTo(0, 0);
	}
	else
	{
		if(BXIsIE())
		{
			this.__pUnderFrame.style.display = "none";
		}

		this.pWnd.style.position = "relative";
		this.pDocument.body.style.overflow = "auto";
		this.pWnd.style.width = this.__oldSize[0];
		this.pWnd.style.height = this.__oldSize[1];
	}
	this.bFullscreen = bFull;
}


BXHTMLEditor.prototype.TableOperation = function (type)
{
	var pElement;
	switch (type)
	{
		case 'insertrow':
			pElement = BXFindParentByTagName(this.GetSelectionObject(), 'TR');
			break;
		case 'deleterow':
			pElement = BXFindParentByTagName(this.GetSelectionObject(), 'TR');
			break;
		case 'insertcolumn':
			break;
		case 'deletecolumn':
			break;
		case 'insertcell':
			break;
		case 'deletecell':
			break;
		case 'splitcell':
			break;
		case 'mergecell':
			break;
	}
}


BXHTMLEditor.prototype.ParseStyles = function ()
{
	this.arStyles = [];
	if(obj.arConfig['FULL_DOCUMENT']=="Y")
	{

	}
}

function BXStyles(pMainObj)
{
	this.pMainObj = pMainObj;
	this.arStyles = [];
	this.sStyles = '';

	BXStyles.prototype.Parse = function (styles)
	{
		this.sStyles = styles;
		this.arStyles = BXStyleParser.Parse(styles);
	}

	BXStyles.prototype.GetStyles = function (sFilter)
	{
		if(this.arStyles[sFilter.toUpperCase()])
			return this.arStyles[sFilter.toUpperCase()];
		return [];
	}

	BXStyles.prototype.SetToDocument = function(pDocument)
	{
		var cur = pDocument.getElementsByTagName("STYLE");
		for(var i=0; i<cur.length; i++)
			cur[i].parentNode.removeChild(cur[i]);

		var xStyle = pDocument.createElement("STYLE");
		if(pDocument.getElementsByTagName("HEAD").length>0)
			pDocument.getElementsByTagName("HEAD")[0].appendChild(xStyle);
		else
			return;

		try{
		if(BXIsIE())
			pDocument.styleSheets[0].cssText = this.sStyles;
		else
			xStyle.appendChild(pDocument.createTextNode(this.sStyles));
		}catch(e){}
	}
}

function _BXStyleParser()
{
	_BXStyleParser.prototype.Create = function()
	{
		if(this.pFrame)
			return;

		var obj = this;
		this.pFrame = document.createElement("IFRAME");
		this.pFrame.setAttribute("src", "");
	    this.pFrame.scrolling = "no";
		this.pFrame.style.position = "absolute";
		this.pFrame.style.zIndex = "0";
		this.pFrame.style.left = 0;
		this.pFrame.style.top = 0;
		this.pFrame.style.width = 0;
		this.pFrame.style.height = 0;
		//!this.pFrame.unselectable = "on";
		this.pFrame.style.display = "none";
		this.pFrame = document.body.appendChild(this.pFrame);

		if(this.pFrame.contentDocument)
			this.pDocument = this.pFrame.contentDocument;
		else
			this.pDocument = this.pFrame.contentWindow.document;

		this.pDocument.write("<html><head></head><body></body></html>");
		this.pDocument.close();
	}

	_BXStyleParser.prototype.Parse = function(strStyles)
	{
		this.pDocument.write("<html><head><style>\n"+strStyles+"\n</style></head><body></body></html>");
		this.pDocument.close();

		var arAllSt=[], rules, cssTag, arTags, cssText = '', i, j, k, result = new Object;
		if(!this.pDocument.styleSheets)
			return result;
		var x1 = this.pDocument.styleSheets;
		var t1, t2;
		for(i=0; i<x1.length; i++)
		{
			rules = (x1[i].rules ? x1[i].rules : x1[i].cssRules);
			for(j=0; j<rules.length; j++)
			{
				cssTag = rules[j].selectorText;
				arTags = cssTag.split(",");
				for(k=0; k<arTags.length; k++)
				{
					t1 = arTags[k].split(" ");
					t1 = t1[t1.length-1].trim();
					if(t1.substr(0, 1)=='.')
					{
						t1 = t1.substr(1);
						t2 = 'DEFAULT';
					}
					else
					{
						t2 = t1.split(".");
						if(t2.length>1)
							t1 = t2[1];
						else
							t1 = '';
						t2 = t2[0].toUpperCase();
					}

					if(arAllSt[t1]) continue;
					arAllSt[t1] = true;

					if(!result[t2])
						result[t2] = [{'className': t1, 'original': arTags[k], 'cssText': rules[j].style.cssText}];
					else
						result[t2].push({'className': t1, 'original': arTags[k], 'cssText': rules[j].style.cssText});
				}
			}
		}
		/*
		var x = '';
		for(var r in result)
		{
			x = r+':\n';
			for(i=0; i<result[r].length; i++)
				x = x + '\t' + result[r][i].className+' ---- {'+result[r][i].cssText+'}\n';
			alert(x);
		}
		*/
		return result;
	}
}

var BXStyleParser = new _BXStyleParser();
