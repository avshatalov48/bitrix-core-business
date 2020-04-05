var snippets_js = true;
// ========================
window.arSnippets = {};
// ========================

function BXSnippetsTaskbar()
{
	var pListCell;
	var oTaskbar = this;
	oTaskbar._arSnippets = [];

	BXSnippetsTaskbar.prototype.OnTaskbarCreate = function ()
	{
		this.icon = 'snippets';
		this.iconDiv.className = 'tb_icon bxed-taskbar-icon-' + this.icon;
		this.pHeaderTable.setAttribute("__bxtagname", "_taskbar_cached");
		this.oTaskbar = ar_BXTaskbarS["BXSnippetsTaskbar_" + this.pMainObj.name];
		this.oTaskbar.pCellSnipp = this.oTaskbar.CreateScrollableArea(this.oTaskbar.pWnd);

		try{
			this.loadSnippets();
		}catch(e) { _alert('BXSnippetsTaskbar: OnTaskbarCreate');}
	};

	BXSnippetsTaskbar.prototype.loadSnippets = function(clear_cache)
	{
		var _this = this;
		var onload = function()
		{
			_this.BuildList(window.arSnippets);
			BX.closeWait();
		};

		var q = new JCHttpRequest();
		q.Action = function(result){try{setTimeout(onload, 5);}catch(e){_alert('error: loadSnippets');};};

		try{
			q.Send(manage_snippets_path + '&templateID='+oTaskbar.pMainObj.templateID+'&target=load'+(clear_cache === true ? '&clear_snippets_cache=Y' : ''));
		}catch(e){onload();}
	};


	BXSnippetsTaskbar.prototype.BuildList = function (__arElements)
	{
		this.oTaskbar.pMainObj.arSnippetsCodes = [];
		var _this = this, oEl, i, l, _path;
		this.__arGroups = {};
		var addGroup = function(name,path)
		{
			_this.oTaskbar.AddElement({name:name,tagname:'',isGroup:true,childElements:[],icon:'',path:path,code:''},_this.oTaskbar.pCellSnipp,path);
		};

		for (var key in __arElements)
		{
			oEl = __arElements[key];
			if (oEl.path != '')
			{
				arPath = oEl.path.split("/");
				l = arPath.length;
				for (i = 0; i < l; i++)
				{
					if (!this.__arGroups[i])
						this.__arGroups[i] = {};

					if(!this.__arGroups[i][arPath[i]])
					{
						this.__arGroups[i][arPath[i]] = true;
						addGroup(arPath[i], (i == 0) ? '' : arPath[0]);
					}
				}
			}

			oEl.tagname = 'snippet';
			oEl.isGroup = false;
			oEl.icon = '/bitrix/images/fileman/htmledit2/snippet.gif';
			oEl.path = oEl.path.replace("/", ",");
			oEl.code = this.Remove__script__(oEl.code);
			oEl.description = oEl.description;
			oEl.title = oEl.title;

			var c = "sn_"+Math.round(Math.random() * 1000000);
			this.oTaskbar.pMainObj.arSnippetsCodes[c] = key;
			oEl.params = {c : c};

			this.oTaskbar.AddElement(oEl, this.oTaskbar.pCellSnipp, oEl.path);
		}
		this.oTaskbar.AddSnippet_button();
	};

	BXSnippetsTaskbar.prototype.AddSnippet_button = function()
	{
		if (!oTaskbar)
			oTaskbar = this;

		var
			id = "___add_snippet___"+this.oTaskbar.pMainObj.name,
			oDiv = BX(id);
		if (oDiv)
			this.oTaskbar.pCellSnipp.removeChild(oDiv);

		oDiv = this.oTaskbar.pCellSnipp.appendChild(BX.create("DIV", {props: {id: id, className: 'bxed-add-snip-cont'}}));
		oDiv.appendChild(BX.create("A", {props: {href: 'javascript:void("")'}, text: BX_MESS.AddSnippet, events: {click: function(){oTaskbar.addSnippet();	return false;}}}));
	};

	BXSnippetsTaskbar.prototype.Remove__script__ = function (str)
	{
		str = str.toString();
		str = str.replace(/&lt;script/ig, "<script");
		str = str.replace(/&lt;\/script/ig, "</script");
		str = str.replace(/\\n/ig, "\n");
		return str;
	};

	BXSnippetsTaskbar.prototype.OnElementClick = function (oEl, arEl)
	{
		_pTaskbar = this.pMainObj.oPropertiesTaskbar;

		if (!_pTaskbar.bActivated || !_pTaskbar.pTaskbarSet.bShowing)
			return;

		//****** DISPLAY TITLE *******
		var
			_this = this,
			snippetName = arEl.name,
			snippetTitle = arEl.title,
			prCell,
			maxLength = 70,
			snippetShortTitle = (snippetTitle.length > maxLength) ? snippetTitle.substr(0, maxLength) + '...' : snippetTitle,
			key = (arEl.path == '' ? '' : arEl.path.replace(',', '/') + '/')+arEl.name,
			snippetCode = arSnippets[key].code,
			snippetDesc = arSnippets[key].description;

		var
			tCompTitle = BX.create("TABLE", {props: {className: "componentTitle"}, style:{height: "96%"}}),
			row = tCompTitle.insertRow(-1),
			cell = row.insertCell(-1);
		cell.innerHTML = "<table style='width:100%'><tr><td style='width:85%'><SPAN title='" + bxhtmlspecialchars(snippetTitle) + "' class='title'>" + bxhtmlspecialchars(snippetShortTitle) + "  (" + snippetName+")</SPAN><BR /><SPAN class='description'>" + bxhtmlspecialchars(snippetDesc) + "</SPAN></td><td style='width:15%; padding-right: 20px; text-align: right;' align='right'><div style='width: 62px; float: right;'><span id='__edit_snip_but' class= 'iconkit_c' style='width: 29px; display:inline-block; height: 17px; background-position: -29px -62px;' title='"+BX_MESS.EditSnippet+"'></span> <span id='__del_snip_but' class= 'iconkit_c' style='width: 29px; height: 17px; background-position: 0px -62px;  display:inline-block;' title='"+BX_MESS.DeleteSnippet+"'></span></td></tr></table>";

		cell.className = "titlecell";
		cell.width = "100%";
		cell.align = "left";
		row = tCompTitle.insertRow(-1);
		cell = row.insertCell(-1);
		cell.className = "bxcontentcell";

		_tbl = cell.appendChild(document.createElement("TABLE"));
		_tbl.style.width = "100%";
		_tbl.style.height = "100%";
		_r = _tbl.insertRow(-1);

		var thumb = arSnippets[key].thumb;
		if (thumb)
		{
			prCell = _r.insertCell(-1);
			prCell.className = 'bx_snip_preview bx_snip_valign';
			var thumb_src = BX_PERSONAL_ROOT + "/templates/"+arSnippets[key].template+"/snippets/images/"+(arEl.path == '' ? '' : (arEl.path.replace(',', '/')+'/'))+thumb;
			var img = prCell.appendChild(document.createElement("IMG"));
			img.src = thumb_src;
			img.onerror = function(){this.parentNode.removeChild(this);};
		}

		_c = _r.insertCell(-1);
		_c.style.width = "100%";
		_c.className = "bx_snip_valign";

		var _repl_tags = function(str)
		{
			str = str.replace(/</g,'&lt;');
			str = str.replace(/>/g,'&gt;');
			return str;
		};

		var _d = _c.appendChild(BX.create('DIV', {props: {className: 'bx_snip_code_preview'}, html: "<pre>"+_repl_tags(snippetCode)+"</pre>"}));

		BX.cleanNode(_pTaskbar.pCellProps);
		var par_w = parseInt(_pTaskbar.pCellProps.offsetWidth);

		setTimeout(function (){_pTaskbar.pCellProps.appendChild(tCompTitle);}, 10);
		setTimeout(function ()
		{
			var editBut = BX("__edit_snip_but");
			var delBut = BX("__del_snip_but");
			if (!editBut || !delBut)
				return;

			editBut.onclick = function(e){_this.editSnippet(arSnippets[key],oEl);};
			delBut.onclick = function(e){_this.delSnippet(arSnippets[key],oEl);};

			var w = parseInt(tCompTitle.offsetWidth);
			if (!isNaN(par_w) && !isNaN(w) && par_w + 10 < w)
			{
				pr_w = prCell ? parseInt(prCell.offsetWidth) : 0;
				_d.style.width = (par_w - (pr_w || 0) - 40) + 'px';
			}
		}, 100);
	};

	BXSnippetsTaskbar.prototype.OnElementDragEnd = function(oEl)
	{
		var oTag = oTaskbar.pMainObj.GetBxTag(oEl);
		if (oTag.tag != 'snippet')
			return;

		// Run it only when dropped into editor doc
		if (oEl.ownerDocument != oTaskbar.pMainObj.pEditorDocument)
			return oTaskbar.OnElementDragEnd(oTaskbar.pMainObj.pEditorDocument.body.appendChild(oEl.cloneNode(false)));

		var
			allParams = oTag.params,
			html = oTaskbar.pMainObj.pParser.SystemParse(arSnippets[this.pMainObj.arSnippetsCodes[allParams.c]].code);

		if (BX.browser.IsIE())
		{
			this.pMainObj.pEditorDocument.selection.clear();
			this.pMainObj.insertHTML(html);
			if (oEl && oEl.parentNode)
				oEl.parentNode.removeChild(oEl);
		}
		else
		{
			var id = 'bx_editor_snippet_tmp';
			this.pMainObj.insertHTML('<a id="' + id + '" href="#" _moz_editor_bogus_node="on">+</a>');
			var pDoc = this.pMainObj.pEditorDocument;
			setTimeout(function()
			{
				var pTmp = pDoc.getElementById(id);
				if (pTmp)
				{
					pTmp.innerHTML = html;
					setTimeout(function(){
						var pTmp = pDoc.getElementById(id);
						if (pTmp)
						{
							while(pTmp.firstChild)
								pTmp.parentNode.insertBefore(pTmp.firstChild, pTmp);
							if (pTmp.parentNode)
								pTmp.parentNode.removeChild(pTmp);
						}
					}, 50);
				}
			}, 50);
			oEl.parentNode.removeChild(oEl);
		}
		this.pMainObj.oPropertiesTaskbar.OnSelectionChange('always');
	};

	BXSnippetsTaskbar.prototype.addSnippet = function()
	{
		this.pMainObj.OpenEditorDialog("snippets", false, 500, {bUseTabControl: true, mode: 'add', BXSnippetsTaskbar: oTaskbar, PHPGetParams: '&mode=add'});
	};

	BXSnippetsTaskbar.prototype.editSnippet = function(oEl,elNode)
	{
		this.pMainObj.OpenEditorDialog("snippets", false, 500, {bUseTabControl: true, mode: 'edit', BXSnippetsTaskbar: oTaskbar, oEl: oEl, elNode: elNode, prop_taskbar: _pTaskbar.pCellProps});
	};

	BXSnippetsTaskbar.prototype.delSnippet = function(oEl, elNode)
	{
		var _this = this;
		var _ds = new JCHttpRequest();
		_ds.Action = function(result)
		{
			setTimeout(function ()
				{
					if (window.operation_success)
					{
						//Clean properties panel
						BX.cleanNode(_pTaskbar.pCellProps);
						//Remove Element from list
						var _id = elNode.parentNode.id;
						var elTable = elNode.parentNode.parentNode.parentNode.parentNode;
						elTable.parentNode.removeChild(elTable);
						elTable = null;
					}
				}, 5
			);
		}
		if (confirm(BX_MESS.DEL_SNIPPET_CONFIRM+' "'+oEl.title+'"?'))
		{
			window.operation_success = false;
			_ds.Send(manage_snippets_path + '&templateID='+escape(oEl.template)+'&target=delete&name='+escape(oEl.name)+'&path='+escape(oEl.path.replace(',', '/'))+'&thumb='+escape(oEl.thumb));
		}
	};

	BXSnippetsTaskbar.prototype.ClearCache = function(oEl, elNode)
	{
		BX.showWait();
		BX.cleanNode(oTaskbar.pCellSnipp);
		BX.cleanNode(oTaskbar.rootElementsCont);
		window.arSnippets = {};
		oTaskbar.loadSnippets(true);
	};
}

oBXEditorUtils.addTaskBar('BXSnippetsTaskbar', 2, BX_MESS.SnippetsTB, [], 20);
