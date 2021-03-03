function BXFMSearch(Params)
{
	this.Init(Params);
}

BXFMSearch.prototype = {
	Init: function(Params)
	{
		if (this.bInited)
			return true;

		var _this = this;
		this.pAddLink = BX('bx_fms_add_lnk');
		this.pSearchTbl = BX('bx_fms_tbl');
		this.oSearchDialog = Params.oSearchDialog;
		this.pTabSearch = BX('bx_search_cont');
		this.pTabReplace = BX('bx_replace_cont');
		this.lang = Params.lang;
		this.site = Params.site;
		this.arLastPathes = Params.arLastPathes;
		this.sessid_get = Params.sessid_get;
		this.bUseLastValues = true;
        this.viewMsFilePath = Params.viewMsFilePath;
		this.viewMsFolderPath = Params.viewMsFolderPath;
		this.dateFormat = Params.dateFormat;
		this.pForm = document.forms['bx_search_form'];
		this.pSearchResultCont = BX('bx_search_res_cont');
		this.pSearchFile = BX('bx_search_file');
		this.pSearchPhrase = BX('bx_search_phrase');
		this.pReplacePhrase = BX('bx_replace_phrase');
		this.pSearchDir = BX('bx_search_dir');
		this.pSearchSubdir = BX('bx_search_subdir');
		this.pSearchDirsToo = BX('bx_search_dirs_too');
		this.pSearchEntire = BX('bx_search_entire');
		this.pSearchCase = BX('bx_search_case');
		this.pInResRow = BX('bx_search_in_res_tr');
		this.pInRes = BX('bx_search_in_res');
		this.pFDButton = BX('bx_search_fd_but');

		// Date
		this.pSearchDateSel = BX('bx_search_date_sel');
		this.pSearchDateFrom = BX('bx_search_date_from');
		this.pSearchDateTo = BX('bx_search_date_to');
		this.pSearchDateDiv = BX('bx_search_date_div');
		// Size
		this.pSearchSizeSel = BX('bx_search_size_sel');
		this.pSearchSizeFrom = BX('bx_search_size_from');
		this.pSearchSizeTo = BX('bx_search_size_to');
		this.pSearchSizeDiv = BX('bx_search_size_div');

		// We want to remember user choise for checkbockses values
		this.pSearchSubdir.onclick =
		this.pSearchDirsToo.onclick =
		this.pSearchEntire.onclick =
		this.pSearchCase.onclick = function(){_this.SaveConfig();};

		this.pAddLink.onclick = function(e)
		{
			var bHide = _this.pSearchTbl.className.indexOf('bxfm-d-params-add-hide') == -1;
			_this.bShowAdvanced = !bHide;
			if (bHide)
				BX.addClass(_this.pSearchTbl, 'bxfm-d-params-add-hide');
			else
				BX.removeClass(_this.pSearchTbl, 'bxfm-d-params-add-hide');

			_this.oSearchDialog.adjustSizeEx();
			_this.SaveConfig();
			return false;
		};

		this.pSearchDateSel.onchange = function()
		{
			_this.pSearchDateDiv.style.display = this.value == "set" ? "block" : "none";
			_this.oSearchDialog.adjustSizeEx();

			if (this.value !== 0)
			{
				var
					D1 = new Date(),
					oDate = new Date(),
					month = oDate.getMonth(),
					year = oDate.getFullYear(),
                    date = oDate.getDate(),
					hours = oDate.getHours(),
					min = oDate.getMinutes();

				_this.pSearchDateTo.value = "";
			}

			if (this.value == 0)
			{
				_this.pSearchDateFrom.value = "";
				_this.pSearchDateTo.value = "";
			}
			else if(this.value == "day")
			{
				D1.setFullYear(year, month, date - 1);
				_this.pSearchDateFrom.value = _this.FormatDate(D1.getDate(), D1.getMonth(), D1.getFullYear(), hours, min);
			}
			else if(this.value == "week")
			{
				D1.setFullYear(year, month, date - 7);
				_this.pSearchDateFrom.value = _this.FormatDate(D1.getDate(), D1.getMonth(), D1.getFullYear(), hours, min);
			}
			else if(this.value == "month")
			{
				D1.setFullYear(year, month - 1, date);
				_this.pSearchDateFrom.value = _this.FormatDate(D1.getDate(), D1.getMonth(), D1.getFullYear(), hours, min);
			}
			else if(this.value == "year")
			{
				D1.setFullYear(year - 1, month, date);
				_this.pSearchDateFrom.value = _this.FormatDate(D1.getDate(), D1.getMonth(), D1.getFullYear(), hours, min);
			}
		};

		this.pSearchSizeSel.onchange = function()
		{
			_this.pSearchSizeDiv.style.display = this.value == "set" ? "block" : "none";
			_this.oSearchDialog.adjustSizeEx();

			if (this.value == 0)
			{
				_this.pSearchSizeFrom.value = "";
				_this.pSearchSizeTo.value = "";
			}
			else if(this.value == "100")
			{
				_this.pSearchSizeFrom.value = "";
				_this.pSearchSizeTo.value = "100";
			}
			else if(this.value == "100_500")
			{
				_this.pSearchSizeFrom.value = "100";
				_this.pSearchSizeTo.value = "500";
			}
			else if(this.value == "500")
			{
				_this.pSearchSizeFrom.value = "500";
				_this.pSearchSizeTo.value = "";
			}
		};

		this.pInRes.onclick = function()
		{
			var checked = !!this.checked;
			_this.pFDButton.disabled = _this.pSearchDir.disabled = _this.pSearchSubdir.disabled = checked;
		};

		this.oSiteSel = new BXFMSiteSel({
			id: 'site_sel_search',
			pDiv: BX('bx_search_site_sel'),
			sites: Params.arSites
		});
		// this.oSearchDir = new BXFMInpSel({
			// id: 'dir',
			// pInput : this.pSearchDir,
			// Items: this.arLastPathes,
			// OnChange : function()
			// {
				// alert('OnChange');
			// },
			// NoValueMess: '/'
		// });

		BX.addCustomEvent(oSearchDialog, 'onWindowUnRegister', BX.proxy(this.OnClose, this));

		// Set checkboxes and advanced mode section corespond to user preferences
		if (Params.oUserConfig)
		{
			this.bShowAdvanced = Params.oUserConfig.advMode;
			if (this.bShowAdvanced)
				BX.removeClass(this.pSearchTbl, 'bxfm-d-params-add-hide');
			else
				BX.addClass(this.pSearchTbl, 'bxfm-d-params-add-hide');

			this.pSearchSubdir.checked = !!Params.oUserConfig.bSubdir;
			this.pSearchDirsToo.checked = !!Params.oUserConfig.bDirsToo;
			this.pSearchEntire.checked = !!Params.oUserConfig.entire;
			this.pSearchCase.checked = !!Params.oUserConfig.bCaseSens;
		}

		// Clean old enties in search result table
		this.Request('clean_old', {}, false, false);

		this.bInited = true;
	},

	OnOpen: function(Params)
	{
		this.sSess = Params.ssess || false;
		this.SetPath(Params.path);
		// Hide or show row with "Search in result checkbox"
		this.pInResRow.style.display = Params.bSearch ? "" : "none";

		if (Params.bSearch)
		{
			if (this.pInRes.checked)
				this.pFDButton.disabled = this.pSearchDir.disabled = this.pSearchSubdir.disabled = true;
		}
		else
		{
			this.pInRes.checked = false;
		}

		if (Params.lastValues && this.bUseLastValues)
		{
			this.bUseLastValues = false;
			this.pSearchFile.value = Params.lastValues.file || '';
			this.pSearchPhrase.value = Params.lastValues.search_phrase || '';
			this.pReplacePhrase.value = Params.lastValues.replace_phrase || '';
			this.pSearchDir.value = Params.lastValues.dir || '';
			this.pSearchSubdir.checked = !!Params.lastValues.subdir;
			this.pSearchDirsToo.checked = !!Params.lastValues.dirs_too;
			this.pSearchCase.checked = !!Params.lastValues.case_sens;

			if (Params.lastValues.date_sel)
			{
				this.pSearchDateSel.value = Params.lastValues.date_sel;
				this.pSearchDateSel.onchange();
				if (Params.lastValues.date_from || Params.lastValues.date_to)
				{
					this.pSearchDateFrom.value = Params.lastValues.date_from;
					this.pSearchDateTo.value = Params.lastValues.date_to;
				}
			}

			if (Params.lastValues.size_sel)
			{
				this.pSearchSizeSel.value = Params.lastValues.size_sel;
				this.pSearchSizeSel.onchange();
				if (Params.lastValues.size_from || Params.lastValues.size_to)
				{
					this.pSearchSizeFrom.value = Params.lastValues.size_from;
					this.pSearchSizeTo.value = Params.lastValues.size_to;
				}
			}
		}

		this.pSearchFile.focus();
		BX.bind(BX.browser.IsIE() ? document.body : window, "keydown", BX.proxy(this.OnKeyDown, this));
	},

	OnClose: function()
	{
		BX.unbind(BX.browser.IsIE() ? document.body : window, "keydown", BX.proxy(this.OnKeyDown, this));
	},

	OnKeyDown: function(e)
	{
		if (!e)
			e = window.event;

		if (window.oBXFileDialog && window.oBXFileDialog.bOpened)
			return;

		if (oSearchDialog.isOpen && e.keyCode == 13)
			return this.Search();
	},

	Count: function()
	{
		var
			_this = this,
			postParams = this.GetPostParams();

		var onResult = function()
		{
			if (!_this.oCountResDialog.isOpen)
				return;

			if (window.fmsBtimeout)
			{
				postParams.last_path = window.fmsLastPath;
				_this.count_xhr = _this.Request('count', postParams, onResult);
			}
			else
			{
				if (_this.oCountResInt)
					clearInterval(_this.oCountResInt);
				_this.oCountResIntCount = 0;

				BX.removeClass(_this.pCountResDiv, 'bxfm-count-wait');
				_this.oCountResDialog.SetTitle(FM_MESS.CountEnded);
			}
			_this.intCountResult += window.fmsResult;

			if (window.fmsBstoped)
			{
				_this.pReplResCntWarn.style.display = "inline";
				_this.pCountResDiv.title = FM_MESS.CountLimitWarn;
			}
			else
			{
				_this.pReplResCntWarn.style.display = "none";
				_this.pCountResDiv.title = "";
			}

			_this.pCountResCnt.innerHTML = _this.intCountResult;
		};

		if (!_this.oCountResDialog)
		{
			this.oCountResDialog = new BX.CDialog({
				title : FM_MESS.CountProgress,
				content: '<div id="bxfm_count_res_div" class="bxfm-count-res-div bxfm-count-wait">' + FM_MESS.CountedFiles + ': <span id="bxfm_count_res_cnt">0</span><span id="bxfm_cnt_res_warn" class="bxfm-warn">*</span></div>',
				height: 125,
				width: 250,
				resizable: false,
				buttons: [BX.CDialog.btnClose]
			});
			this.pCountResCnt = BX('bxfm_count_res_cnt');
			this.pCountResDiv = BX('bxfm_count_res_div');
			this.pReplResCntWarn = BX('bxfm_cnt_res_warn');
			BX.addClass(this.oCountResDialog.PARTS.CONTENT, "bxfm-dialog-content");
			if (BX.browser.IsIE())
				this.pCountResDiv.style.margin = "3px 0 2px 2px";
		}
		else
		{
			// Set counter to Zero
			_this.pCountResCnt.innerHTML = "0";
			_this.pReplResCntWarn.style.display = "none";
			_this.pCountResDiv.title = "";
			BX.addClass(_this.pCountResDiv, 'bxfm-count-wait');
			this.oCountResDialog.SetTitle(FM_MESS.CountProgress);
		}

		this.oCountResDialog.Show();
		this.intCountResult = 0;

		// Blink "..." in the title
		if (this.oCountResInt)
			clearInterval(this.oCountResInt);

		this.oCountResIntCount = 0;
		this.oCountResInt = setInterval(function()
		{
			if (_this.oCountResIntCount < 3)
			{
				_this.oCountResIntCount++;
				_this.oCountResDialog.SetTitle(_this.oCountResDialog.PARAMS.title + ' .');
			}
			else
			{
				_this.oCountResIntCount = 0;
				_this.oCountResDialog.SetTitle(FM_MESS.CountProgress);
			}
		}, 400);

		if (this.count_xhr)
			this.count_xhr.abort();

		this.count_xhr = this.Request('count', postParams, onResult);
		BX.addCustomEvent(this.oCountResDialog, 'onWindowUnRegister', BX.proxy(function()
		{
			if(this.count_xhr)
				this.count_xhr.abort();
		}, this));

	},

	Search: function()
	{
		if (this.bReplace)
			return this.Replace();

		var
			_this = this,
			postParams = this.GetPostParams();

		var onResult = function()
		{
			if (!_this.oSearchResDialog.isOpen || _this.bSearchDenied)
				return;

			// if (!window.fmsResult)
			// {
				// window.fmsBtimeout = false;
				// window.fmsBstoped = false;
				// window.fmsResult = [];
			// }

			var i, l = window.fmsResult.length, r, c, el, href;

			// Files or folders was found - display esult div, enable "Show result button"
			if (l > 0 && _this.arSearchResult.length == 0)
			{
				_this.pSearchRes.style.display = "block";
				_this.oSearchResDialog.PARAMS.buttons[0].enable();
				_this.oSearchResDialog.SetSize({width: 600, height: 400});
				_this.oSearchResDialog.adjustPos();
			}

			for(i = 0; i < l; i++)
			{
				el = window.fmsResult[i];
				_this.arSearchResult.push(el);
				_this.searchCount += parseInt(el.repl_count);

				r = _this.pSearchResTable.insertRow(-1);
				r.title = el.path;

				//type: foder or file
				c = r.insertCell(-1);
				c.appendChild(BX.create('IMG', {props: {
					src: el.type_src
				}}));

				//path
				c = r.insertCell(-1);
				c.style.textAlign = 'left';
                href = ((el.b_dir ? _this.viewMsFolderPath : _this.viewMsFilePath) + _this.oSiteSel.value).replace('#PATH#', BX.util.urlencode(el.path));
                c.appendChild(BX.create('A', {props: {href: href,	target: '_blank'}, text: el.path}));

				if (_this.pSearchPhrase.value != "")
					c.appendChild(BX.create('SPAN', {props: {className: 'bxfm-search-cnt', title: FM_MESS.SearchInFileTitle}, html: ' (<span>' + parseInt(el.repl_count) + '</span>)'}));

				//Date
				c = r.insertCell(-1);
				c.appendChild(document.createTextNode(el.str_date));

				//Size
				c = r.insertCell(-1);
				c.appendChild(document.createTextNode(el.str_size));
			}

			if (_this.pSearchPhrase.value == "")
			{
				_this.pSearchResCnt.innerHTML = _this.arSearchResult.length || 0;
			}
			else
			{
				_this.pSearchResCnt.innerHTML = _this.searchCount || 0;
				_this.pSearchResCntFiles.innerHTML = _this.arSearchResult.length || 0; // Whole count of the replaces
			}

			if (window.fmsBtimeout)
			{
				postParams.last_path = window.fmsLastPath;
				_this.xhr = _this.Request('search', postParams, onResult);
			}
			else
			{
				if (_this.oSearchResInt)
					clearInterval(_this.oSearchResInt);
				_this.oSearchResIntCount = 0;

				// Disable "Stop search" button cause it already finished
				//_this.oSearchResDialog.PARAMS.buttons[1].disable();

				var stopButton = BX("stop");
				if(stopButton)
					stopButton.disabled = true;

				BX.removeClass(_this.pSearchResDiv, 'bxfm-wait');
				_this.oSearchResDialog.PARAMS.buttons[0].enable();
				_this.oSearchResDialog.SetTitle(FM_MESS.SearchEnded);
			}

			if (window.fmsBstoped)
			{
				_this.pSearchResCntWarn.style.display = "inline";
				_this.pSearchResDiv.title = FM_MESS.CountLimitWarn;
			}
			else
			{
				_this.pSearchResCntWarn.style.display = "none";
				_this.pSearchResDiv.title = "";
			}
		};

		if (!this.oSearchResDialog)
		{
			this.oSearchResDialog = new BX.CDialog({
				title : FM_MESS.SearchProgress,
				content: '<div id="bxfm_search_res_div" class="bxfm-search-res-div bxfm-wait"><div class="bxfm-wait-1"></div>' + FM_MESS.Counted + ': <span id="bxfm_search_res_cnt">0</span><span id="bxfm_sres_warn" class="bxfm-warn">*</span><span class="bxfm-only-for-phrase"><br>' + FM_MESS.ReplCountInFiles + ': <span id="bxfm_search_res_files">0</span></span><div class="bxfm-search-res" id="bxfm_search_res"><table><tr class="bxfm-s-res-head"><td class="bxfm-h-type"></td><td class="bxfm-h-path">' + FM_MESS.Path + '</td><td class="bxfm-h-date">' + FM_MESS.Date + '</td><td class="bxfm-h-size">' + FM_MESS.Size + '</td></tr></table></div></div>',
				height: 125,
				width: 450,
				min_height: 220,
				min_width: 450,
				buttons: [
					new BX.CWindowButton(
					{
						title: FM_MESS.ShowRes,
						id: 'show_res',
						name: 'show_res',
						action: function() {_this.DisplaySearchResult();}
					}),
					new BX.CWindowButton(
					{
						title: FM_MESS.Stop,
						id: 'stop',
						name: 'stop',
						action: function()
						{
							if (_this.oSearchResInt)
								clearInterval(_this.oSearchResInt);
							_this.oSearchResIntCount = 0;

							_this.bSearchDenied = true;
							if(_this.xhr)
								_this.xhr.abort();

							BX.removeClass(_this.pSearchResDiv, 'bxfm-wait');
							_this.oSearchResDialog.PARAMS.buttons[0].enable();
							_this.oSearchResDialog.SetTitle(FM_MESS.SearchEnded);
						}
					}),
					BX.CDialog.btnClose
				]
			});

			BX.addClass(this.oSearchResDialog.PARTS.CONTENT, "bxfm-dialog-content");
			this.pSearchResCnt = BX('bxfm_search_res_cnt');
			this.pSearchResCntFiles = BX('bxfm_search_res_files');
			this.pSearchRes = BX('bxfm_search_res');
			this.pSearchResTable = this.pSearchRes.firstChild;
			this.pSearchResDiv = BX('bxfm_search_res_div');
			this.pSearchResCntWarn = BX('bxfm_sres_warn');

			if (BX.browser.IsIE())
				this.pSearchResDiv.style.margin = "3px 0 2px 2px";
		}
		else
		{
			// Return to original view after first showing
			// Hide result container
			_this.pSearchRes.style.display = "none";
			//_this.oSearchResDialog.DIV.style.height = "125px";

			// Set counter to Zero
			_this.pSearchResCnt.innerHTML = "0";
			_this.pSearchResCntFiles.innerHTML = "0";

			// Clean result container
			while (this.pSearchResTable.rows[1])
				this.pSearchResTable.deleteRow(-1);

			this.pSearchResCntWarn.style.display = "none";
			this.pSearchResDiv.title = "";

			BX.addClass(this.pSearchResDiv, 'bxfm-wait');
			BX.removeClass(this.pSearchResDiv, 'bxfm-with-phrase');
			_this.oSearchResDialog.SetTitle(FM_MESS.SearchProgress);
			_this.oSearchResDialog.PARAMS.buttons[1].enable();

			_this.oSearchResDialog.SetSize({width: 600, height: 125});
			_this.oSearchResDialog.adjustPos();
		}

		this.oSearchResDialog.Show();
		this.arSearchResult = [];
		this.searchCount = 0;
		this.bSearchDenied = false;
		this.oSearchResDialog.PARAMS.buttons[0].disable();

		if (this.pSearchPhrase.value != "")
			BX.addClass(this.pSearchResDiv, 'bxfm-with-phrase');

		// Blink "..." in the title
		this.oSearchResIntCount = 0;
		if (this.oSearchResInt)
			clearInterval(this.oSearchResInt);
		this.oSearchResInt = setInterval(function()
		{
			if (_this.oSearchResIntCount < 3)
			{
				_this.oSearchResIntCount++;
				_this.oSearchResDialog.SetTitle(_this.oSearchResDialog.PARAMS.title + ' .');
			}
			else
			{
				_this.oSearchResIntCount = 0;
				_this.oSearchResDialog.SetTitle(FM_MESS.SearchProgress);
			}
		}, 400);

		if (this.xhr)
			this.xhr.abort();

		this.xhr = this.Request('search', postParams, onResult);
		BX.addCustomEvent(this.oSearchResDialog, 'onWindowUnRegister', BX.proxy(function()
		{
			if(this.xhr)
				this.xhr.abort();
		}, this));

		BX.addCustomEvent(this.oSearchResDialog, 'onWindowResizeExt', function(oSize)
		{
			var
				w = oSize.width - 35;



			if (BX.browser.IsIE())
			{
				w -= 5;
			}

			if (w > 0)
				_this.pSearchRes.style.width = w + "px";
		});
	},

	Replace: function()
	{
		BX.WindowManager.disableKeyCheck();

		if (this.pSearchPhrase.value == "")
		{
			alert(FM_MESS.ReplacePhraseWarn);
			this.pSearchPhrase.focus();
			return setTimeout(function(){BX.WindowManager.enableKeyCheck();}, 500);
		}

		if (!confirm(FM_MESS.ReplaceConfirm))
			return setTimeout(function(){BX.WindowManager.enableKeyCheck();}, 500);

		BX.WindowManager.enableKeyCheck();

		var
			_this = this,
			postParams = this.GetPostParams();

		var onResult = function()
		{
			if (!_this.oReplaceResDialog.isOpen || _this.bReplaceDenied)
				return;

			var i, l = window.fmsResult.length, r, c, el;

			// Files or folders was found - display result div, enable "Show result button"
			if (l > 0 && _this.arReplaceResult.length == 0)
			{
				_this.pReplRes.style.display = "block";
				_this.oReplaceResDialog.PARAMS.buttons[0].enable();
				_this.oReplaceResDialog.SetSize({width: 600, height: 400});
				_this.oReplaceResDialog.adjustPos();
			}

			for(i = 0; i < l; i++)
			{
				el = window.fmsResult[i];
				_this.arReplaceResult.push(el);
				_this.replaceCount += parseInt(el.repl_count);

				r = _this.pReplResTable.insertRow(-1);
				r.title = el.path;

				//type: foder or file
				c = r.insertCell(-1);
				c.appendChild(BX.create('IMG', {props: {
					src: el.type_src
				}}));

				//path
				c = r.insertCell(-1);
				c.style.textAlign = 'left';
				href = ((el.b_dir ? _this.viewMsFolderPath : _this.viewMsFilePath) + _this.oSiteSel.value).replace('#PATH#', BX.util.urlencode(el.path));
                c.appendChild(BX.create('A', {props: {href: href,	target: '_blank'}, text: el.path}));

				c.appendChild(BX.create('SPAN', {props: {className: 'bxfm-rep-cnt', title: FM_MESS.ReplInFileTitle}, html: ' (<span>' + parseInt(el.repl_count) + '</span>)'}));

				//Date
				c = r.insertCell(-1);
				c.appendChild(document.createTextNode(el.str_date));

				//Size
				c = r.insertCell(-1);
				c.appendChild(document.createTextNode(el.str_size));
			}

			_this.pReplResFilesCnt.innerHTML = _this.arReplaceResult.length || 0; // Files with replaces
			_this.pReplResCnt.innerHTML = _this.replaceCount || 0; // Whole count of the replaces

			if (window.fmsBtimeout)
			{
				postParams.last_path = window.fmsLastPath;
				_this.replace_xhr = _this.Request('replace', postParams, onResult);
			}
			else
			{
				if (_this.oReplResInt)
					clearInterval(_this.oReplResInt);
				_this.oReplResIntCount = 0;

				// Disable "Stop replace" button cause it already finished
				//_this.oReplaceResDialog.PARAMS.buttons[1].disable();

				var stopButton = BX("stop");
				if(stopButton)
					stopButton.disabled = true;

				BX.removeClass(_this.pReplResDiv, 'bxfm-wait');
				_this.oReplaceResDialog.PARAMS.buttons[0].enable();
				_this.oReplaceResDialog.SetTitle(FM_MESS.ReplEnded);
			}

			if (window.fmsBstoped)
			{
				_this.pReplResCntWarn.style.display = "inline";
				_this.pReplResDiv.title = FM_MESS.CountLimitWarn;
			}
			else
			{
				_this.pReplResCntWarn.style.display = "none";
				_this.pReplResDiv.title = "";
			}
		};

		if (!this.oReplaceResDialog)
		{
			this.oReplaceResDialog = new BX.CDialog({
				title : FM_MESS.ReplProgress,
				content: '<div id="bxfm_repl_res_div" class="bxfm-search-res-div bxfm-wait"><div class="bxfm-wait-1"></div>' + FM_MESS.ReplCounted + ': <span id="bxfm_repl_res_cnt">0</span><br>' + FM_MESS.ReplCountInFiles + ': <span id="bxfm_repl_res_files">0</span><span id="bxfm_repl_res_warn" class="bxfm-warn">*</span><div class="bxfm-search-res" id="bxfm_repl_res"><table><tr class="bxfm-s-res-head"><td class="bxfm-h-type"></td><td class="bxfm-h-path">' + FM_MESS.Path + '</td><td class="bxfm-h-date">' + FM_MESS.Date + '</td><td class="bxfm-h-size">' + FM_MESS.Size + '</td></tr></table></div></div>',
				height: 150,
				width: 450,
				min_height: 250,
				min_width: 450,
				buttons: [
					new BX.CWindowButton(
					{
						title: FM_MESS.ReplShowRes,
						id: 'show_res',
						name: 'show_res',
						action: function() {_this.DisplayReplaceResult();}
					}),
					new BX.CWindowButton(
					{
						title: FM_MESS.Stop,
						id: 'stop',
						name: 'stop',
						action: function()
						{
							if (_this.oReplResInt)
								clearInterval(_this.oReplResInt);
							_this.oReplResIntCount = 0;

							_this.bReplaceDenied = true;
							if(_this.replace_xhr)
								_this.replace_xhr.abort();

							BX.removeClass(_this.pReplResDiv, 'bxfm-wait');
							_this.oReplaceResDialog.PARAMS.buttons[0].enable();
							_this.oReplaceResDialog.SetTitle(FM_MESS.ReplEnded);
						}
					}),
					BX.CDialog.btnClose
				]
			});

			BX.addClass(this.oReplaceResDialog.PARTS.CONTENT, "bxfm-dialog-content");
			this.pReplResCnt = BX('bxfm_repl_res_cnt');
			this.pReplResFilesCnt = BX('bxfm_repl_res_files');
			this.pReplRes = BX('bxfm_repl_res');
			this.pReplResTable = this.pReplRes.firstChild;
			this.pReplResDiv = BX('bxfm_repl_res_div');
			this.pReplResCntWarn = BX('bxfm_repl_res_warn');

			if (BX.browser.IsIE())
				this.pReplResDiv.style.margin = "4px 2px 2px 0px";
		}
		else
		{
			// Return to original view after first showing
			// Hide result container
			_this.pReplRes.style.display = "none";

			// Set counter to Zero
			_this.pReplResCnt.innerHTML = "0";
			_this.pReplResFilesCnt.innerHTML = "0";

			// Clean result container
			while (this.pReplResTable.rows[1])
				this.pReplResTable.deleteRow(-1);

			_this.pReplResCntWarn.style.display = "none";
			_this.pReplResDiv.title = "";

			BX.addClass(this.pReplResDiv, 'bxfm-wait');
			this.oReplaceResDialog.SetTitle(FM_MESS.ReplProgress);
			this.oReplaceResDialog.PARAMS.buttons[1].enable();

			this.oReplaceResDialog.SetSize({width: 600, height: 120});
			this.oReplaceResDialog.adjustPos();
		}

		this.oReplaceResDialog.Show();
		this.arReplaceResult = [];
		this.replaceCount = 0;
		this.bReplaceDenied = false;
		this.oReplaceResDialog.PARAMS.buttons[0].disable();

		// Blink "..." in the title
		this.oReplResIntCount = 0;
		if (this.oReplResInt)
			clearInterval(this.oReplResInt);
		this.oReplResInt = setInterval(function()
		{
			if (_this.oReplResIntCount < 3)
			{
				_this.oReplResIntCount++;
				_this.oReplaceResDialog.SetTitle(_this.oReplaceResDialog.PARAMS.title + ' .');
			}
			else
			{
				_this.oReplResIntCount = 0;
				_this.oReplaceResDialog.SetTitle(FM_MESS.ReplProgress);
			}
		}, 400);

		if (this.replace_xhr)
			this.replace_xhr.abort();

		this.replace_xhr = this.Request('replace', postParams, onResult);
		BX.addCustomEvent(this.oReplaceResDialog, 'onWindowUnRegister', BX.proxy(function()
		{
			if(this.replace_xhr)
				this.replace_xhr.abort();
		}, this));

		BX.addCustomEvent(this.oReplaceResDialog, 'onWindowResizeExt', function(oSize)
		{
			var
				w = oSize.width - 35;
			_this.pReplRes.style.width = w + "px";
		});
	},

	GetPostParams: function()
	{
		return {
			file: this.pSearchFile.value,
			phrase: this.pSearchPhrase.value,
			replace_phrase: this.pReplacePhrase.value,
			dir: this.pSearchDir.value,
			subdir: this.pSearchSubdir.checked ? 1 : 0,
			date_from: this.pSearchDateFrom.value,
			date_to: this.pSearchDateTo.value,
			size_from: this.pSearchSizeFrom.value,
			size_to: this.pSearchSizeTo.value,
			entire: this.pSearchEntire.checked ? 1 : 0,
			case_sens: this.pSearchCase.checked ? 1 : 0,
			dirs_too: this.pSearchDirsToo.checked ? 1 : 0,
			ssess: this.sSess ? this.sSess : 0,
			in_result: this.pInRes.checked ? 1 : 0
		};
	},

	DisplayReplaceResult: function()
	{
		this.pSearchResultCont.appendChild(BX.create("INPUT", {props: {name: "is_replace", type: "hidden", value: "Y"}}));
		this.DisplaySearchResult(this.arReplaceResult);
	},

	DisplaySearchResult: function(arResult)
	{
		if (typeof arResult != 'object') // Search result by default
			arResult = this.arSearchResult;

		var i, l = arResult.length, name, el;
		for (i = 0; i < l; i++)
		{
			name = "sres[" + i + "]";
			el = arResult[i];
			this.pSearchResultCont.appendChild(BX.create("INPUT", {props: {name: name + "[path]", type: "hidden", value: el.path}}));
			this.pSearchResultCont.appendChild(BX.create("INPUT", {props: {name: name + "[b_dir]", type: "hidden", value: el.b_dir}}));
			this.pSearchResultCont.appendChild(BX.create("INPUT", {props: {name: name + "[size]", type: "hidden", value: el.size}}));
			this.pSearchResultCont.appendChild(BX.create("INPUT", {props: {name: name + "[time]", type: "hidden", value: el.time}}));
		}

		this.pForm.submit();
	},

	Request : function(action, postParams, callBack, bShowWaitWin)
	{
		bShowWaitWin = bShowWaitWin !== false;

		if (bShowWaitWin)
			BX.showWait();

		var actionUrl = '/bitrix/admin/fileman_admin.php?lang=' + this.lang + "&fu_action=" + action + "&site=" + this.site + "&" + this.sessid_get + "&fu_site=" + this.oSiteSel.value;

		return BX.ajax.post(actionUrl, postParams || {},
			function(result)
			{
				if (bShowWaitWin)
					BX.closeWait();

				if(callBack)
					setTimeout(function(){callBack(result);}, 100);
			}
		);
	},

	SetTab: function(tabId)
	{
		var oBut = this.oSearchDialog.PARAMS.buttons[0];
		if (tabId == 'search')
		{
			this.pTabSearch.appendChild(this.pTabReplace.firstChild);
			oBut.btn.value = oBut.title = FM_MESS.Find;
			this.bReplace = false;
		}
		else
		{
			this.pTabReplace.appendChild(this.pTabSearch.firstChild);
			oBut.btn.value = oBut.title = FM_MESS.Replace;
			this.bReplace = true;
		}

		this.oSearchDialog.adjustSizeEx();
	},

	SetPath: function(path)
	{
		this.pSearchDir.value = path;
	},

	FormatDate: function(d, m, y, h, min)
	{
		var str = this.dateFormat;
		str = str.replace(/DD/ig, this.ZeroInt(d));
		str = str.replace(/MM/ig, this.ZeroInt(m + 1)); // JS count month from 0
		str = str.replace(/YY(YY)?/ig, y);

		if(typeof h != undefined && typeof min != undefined)
			str += " " + h + ":" + min + ":00";
		return str;
	},

	ZeroInt: function(x)
	{
		x = parseInt(x, 10);
		if (isNaN(x))
			x = 0;
		return x < 10 ? '0' + x.toString() : x.toString();
	},

	SaveConfig: function()
	{
		this.Request(
			'search_save_config',
			{
				adv_mode: this.bShowAdvanced ? 1 : 0,
				subdir: this.pSearchSubdir.checked ? 1 : 0,
				entire: this.pSearchEntire.checked ? 1 : 0,
				case_sens: this.pSearchCase.checked ? 1 : 0,
				dirs_too: this.pSearchDirsToo.checked ? 1 : 0
			},
		false, false);
	}
};

function BXFMServerPerm(Params)
{
	this.Params = Params;
	this.Params.bWindows = false;
	this.Init();
}

BXFMServerPerm.prototype = {
	Init: function()
	{
		var _this = this;
		this.pButSave = BX("bx_sp_save");
		this.pButApply = BX("bx_sp_apply");
		this.pButCancel = BX("bx_sp_cancel");
		this.pNoteSuccess = BX("bxsp_note_success");
		this.pRecursive = BX('bxsp_recurcive');
		this.InProcessMess = FM_MESS.InProcess + '...';

		this.pNoteErrors = BX('bxsp_note_errors');
		this.pNoteErrorsCont = BX('bxsp_note_errors_cont');

		this.pButSave.onclick = function() {return _this.Process(true);};
		this.pButApply.onclick = function() {return _this.Process(false);};
		this.pButCancel.onclick = function()
		{
			if (_this.xhr) // Stop
			{
				if(_this.xhr)
					_this.xhr.abort();
				_this.xhr = false;

				_this.bOnResultDenied = true;

				if (_this.pCurValDiff)
					_this.pCurValDiff.style.display = "none";

				_this.pButSave.disabled = _this.pButApply.disabled = false;

				_this.pButCancel.value = FM_MESS.Return;
				_this.pButCancel.Title = FM_MESS.ReturnTitle;

				for (var i = 1, rl = _this.pFileList.rows.length; i < rl; i++)
				{
					cell = _this.pFileList.rows[i].cells[3];
					if (cell.innerHTML == _this.InProcessMess)
						cell.innerHTML = FM_MESS.Stoped;
				}
			}
			else // Cancell, return
			{
				window.location = _this.Params.backUrl;
			}
		};

		this.pFileList = BX("bxsp_file_list");
		this.pCurValDiff = BX("bxsp_cur_val_diff");

		var i, k;
		this.arOwners = ['owner', 'group', 'public'];
		this.arFields = {};
		this.pResVal = BX('bxsp_res_value');

		this.pResVal.onblur = BX.proxy(this.SetValue2Checkboxes, this);
		this.pResVal.onkeyup = function()
		{
			if (this.value.length >= 3)
				_this.SetValue2Checkboxes();
		};

		for (i = 0; i < 3; i++)
		{
			k = this.arOwners[i];
			this.arFields[k] = {
				read: BX('bxsp_' + k + '_read'),
				write: BX('bxsp_' + k + '_write'),
				exec: BX('bxsp_' + k + '_exec'),
				value: BX('bxsp_' + k + '_value')
			};

			this.arFields[k].read.onclick =
			this.arFields[k].write.onclick =
			this.arFields[k].exec.onclick = BX.proxy(this.ChOnChange, this);
		}
		if (this.Params.currentValue)
		{
			this.pResVal.value = this.Params.currentValue;
			this.SetValue2Checkboxes();
		}
	},

	ChOnChange: function()
	{
		var resVal = '', i, k, val;
		for (i = 0; i < 3; i++)
		{
			k = this.arOwners[i];

			val = (this.arFields[k].read.checked ? '1' : '0') + (this.arFields[k].write.checked ? '1' : '0') + (this.arFields[k].exec.checked ? '1' : '0');
			val = parseInt(val, 2);
			this.arFields[k].value.value = val;
			resVal += val;
		}
		this.pResVal.value = resVal;
	},

	SetValue2Checkboxes: function()
	{
		var
			i, k, val, resVal2 = '',binVal,
			resVal = this.pResVal.value || '';

		if (resVal.length != 3)
			resVal = '000';

		for (i = 0; i < 3; i++)
		{
			val = parseInt(resVal.substr(i, 1));

			if (isNaN(val) || val > 7 || val < 0)
				val = 0;

			resVal2 += val.toString();

			k = this.arOwners[i];
			this.arFields[k].value.value = val;
			binVal = val.toString(2);

			if (binVal.length == 1)
				binVal = '00' + binVal;
			if (binVal.length == 2)
				binVal = '0' + binVal;

			this.arFields[k].read.checked = binVal.substr(0, 1) == 1;
			this.arFields[k].write.checked = binVal.substr(1, 1) == 1;
			this.arFields[k].exec.checked = binVal.substr(2, 1) == 1;
		}

		this.pResVal.value = resVal2;
	},

	Request : function(action, postParams, callBack, bShowWaitWin)
	{
		bShowWaitWin = bShowWaitWin !== false;

		if (bShowWaitWin)
			BX.showWait();
		return BX.ajax.post('/bitrix/admin/fileman_server_access.php?lang=' + this.Params.lang + "&fu_action=" + action + "&site=" + this.Params.site + "&" + this.Params.sessid_get, postParams || {},
			function(result)
			{
				if (bShowWaitWin)
					BX.closeWait();

				if(callBack)
					setTimeout(function(){callBack(result);}, 100);
			}
		);
	},

	Process: function(bSave)
	{
		var
			j, i, l = this.Params.arFiles.length, files = [],
			rl = this.pFileList.rows.length,
			_this = this;

		this.bOnResultDenied = false;
		for (i = 0; i < l; i++)
			files.push(this.Params.arFiles[i]['NAME']);

		for (i = 1; i < rl; i++)
			this.pFileList.rows[i].cells[3].innerHTML = this.InProcessMess;
		this.pNoteSuccess.style.display = "none";
		this.pNoteErrors.style.display = "none";
		BX.cleanNode(this.pNoteErrorsCont);

		BX.removeClass(this.pFileList, "bxsp-file-list-init");

		var postParams = {
			files: files,
			recurcive: (this.pRecursive && this.pRecursive.checked) ? "Y" : "N",
			path: this.Params.path
		};

		if (this.Params.bSearch)
		{
			postParams.search = "Y";
			postParams.ssess = this.Params.searchSess;
		}

		postParams.res_value = this.pResVal.value;

		var onResult = function(result)
		{
			if (_this.bOnResultDenied)
				return;

			var
				i, l = window.spResult.length, r, c, el, bHandeled, valHtml,
				pRow, pStatusCell, pNextRow, newRowIndex, pNewRow, pNameCell,
				j, itemPath, n = _this.Params.arFiles.length;

			for(i = 0; i < l; i++)
			{
				el = window.spResult[i][0];
				bHandeled = false;

				for (j = 0; j < n; j++)
				{
					itemPath = _this.Params.arFiles[j]["PATH"];

					valHtml = _this.pResVal.value;

					if (el == itemPath) // Same element
					{
						pRow = BX("bxsp_file_row_" + j);

						pRow.cells[2].innerHTML = "<b>" + valHtml + "</b>";
						pRow.cells[2].title = '';

						if (window.spResult[i][1])
							pRow.cells[3].innerHTML = "<span class='bxsp-green'>" + FM_MESS.Ok + "</span>";
						else
							pRow.cells[3].innerHTML = "<span class='bxsp-red'>" + FM_MESS.Error + "</span>";

						bHandeled = true;
						break;
					}
				}

				if (!bHandeled && !window.spResult[i][1])
				{
					_this.pNoteErrors.style.display = "block";
					_this.pNoteErrorsCont.appendChild(BX.create("DIV", {text: el}));
				}
			}

			if (window.spBtimeout)
			{
				postParams.last_path = window.spLastPath;
				_this.xhr = _this.Request('change_perms', postParams, onResult);
			}
			else
			{
				if (_this.pCurValDiff)
					_this.pCurValDiff.style.display = "none";
				_this.pNoteSuccess.style.display = "block";
				_this.pButSave.disabled = _this.pButApply.disabled = false;
				_this.pButCancel.value = FM_MESS.Return;
				_this.pButCancel.Title = FM_MESS.ReturnTitle;
				_this.xhr = false;

				if (bSave)
					setTimeout(function(){window.location = _this.Params.backUrl;}, 500);
			}
		};

		if (this.xhr)
			this.xhr.abort();

		this.xhr = this.Request('change_perms', postParams, onResult);

		this.pButSave.disabled = this.pButApply.disabled = true;
		this.pButCancel.value = FM_MESS.Stop;
		this.pButCancel.Title = FM_MESS.StopTitle;


		return false;
	}
};

function BXFMCopy(Params)
{
	this.Init(Params);
}

BXFMCopy.prototype = {
	Init: function(Params)
	{
		if (this.bInited)
			return true;

		this.oCopyDialog = Params.oCopyDialog;
		BX.addClass(this.oCopyDialog.PARTS.CONTENT, "bx-fm-copy-dialog");
		BX.cleanNode(this.oCopyDialog.PARTS.CONTENT_DATA);
		this.oCopyDialog.PARTS.CONTENT_DATA.appendChild(BX('bx_copy_dialog'));
		this.arLastPathes = Params.arLastPathes;

		var _this = this;
		this.pAddLink = BX('bx_copy_add_lnk');
		this.pCopyTbl = BX('bx_copy_table');
		this.pFilelist = BX('bx_copy_file_list');

		this.pCopyTo = BX('bx_copy_to');
		// Case options
		this.pCaseAsk = BX("bx_copy_ask_user");
		this.pCaseReplace = BX("bx_copy_replace");
		this.pCaseAutoRename = BX("bx_copy_auto_rename");
		this.pCaseSkip = BX("bx_copy_skip");

		this.pCaseAsk.onclick = this.pCaseReplace.onclick = this.pCaseAutoRename.onclick = this.pCaseSkip.onclick = function()
		{
			if (this.checked)
				_this.caseOption = this.value;
			_this.SaveConfig();
		};

		this.lang = Params.lang;
		this.site = Params.site;
		this.sessid_get = Params.sessid_get;
		BX('bx_copy_dialog').style.display = "block";

		this.viewMsFilePath = Params.viewMsFilePath;
		this.viewMsFolderPath = Params.viewMsFolderPath;

		this.oSiteSel = new BXFMSiteSel({
			id: 'site_sel_copy',
			pDiv: BX('bx_copy_site_sel'),
			sites: Params.arSites
		});

		this.oCopyTo = new BXFMInpSel({
			id: 'cm_copy_to',
			pInput : this.pCopyTo,
			Items: this.arLastPathes
		});

		this.pAddLink.onclick = function()
		{
			var
				cn = 'bx-copy-cont-tbl-add-hide',
				bHide = _this.pCopyTbl.className.indexOf(cn) == -1;

			_this.bShowAdvanced = !bHide;

			if (bHide)
				BX.addClass(_this.pCopyTbl, cn);
			else
				BX.removeClass(_this.pCopyTbl, cn);

			_this.oCopyDialog.adjustSizeEx();
			_this.SaveConfig();
			return false;
		};

		this.caseOption = 'ask';
		if (Params.oUserConfig)
		{
			this.bShowAdvanced = Params.oUserConfig.advMode;

			if (this.bShowAdvanced)
				BX.removeClass(_this.pCopyTbl, 'bx-copy-cont-tbl-add-hide');
			else
				BX.addClass(_this.pCopyTbl, 'bx-copy-cont-tbl-add-hide');

			this.caseOption = Params.oUserConfig.caseOption || 'ask';
		}

		switch(this.caseOption)
		{
			case "ask":
				this.pCaseAsk.checked = true;
				break;
			case "replace":
				this.pCaseReplace.checked = true;
				break;
			case "auto_rename":
				this.pCaseAutoRename.checked = true;
				break;
			case "skip":
				this.pCaseSkip.checked = true;
				break;
		}

		BX.addCustomEvent(this.oCopyDialog, 'onWindowUnRegister', BX.proxy(this.OnClose, this));

		this.bInited = true;
	},

	OnOpen: function(Params)
	{
		this.bCopy = Params.bCopy;
		this.arFiles = [];
		this.curPath = Params.path;

		this.bSearch = !!Params.bSearch;
		this.searchSess = Params.ssess;

		if (typeof Params.arFiles == 'object')
			this.arFiles = Params.arFiles;

		var oBut = this.oCopyDialog.PARAMS.buttons[0];
		if (this.bCopy)
		{
			this.oCopyDialog.SetTitle(FM_MESS.CopyTitle);
			oBut.btn.value = oBut.title = FM_MESS.Copy;
		}
		else
		{
			this.oCopyDialog.SetTitle(FM_MESS.MoveTitle);
			oBut.btn.value = oBut.title = FM_MESS.Move;
		}

		// Clean filelist
		BX.cleanNode(this.pFilelist);
		var i, l = this.arFiles.length;

		for (i = 0; i < l; i++)
		{
			this.pFilelist.appendChild(BX.create("A", {props: {href: this.GetViewUrl(this.arFiles[i]), target: '_blank'}, text: this.GetFileName(this.arFiles[i].path)}));

			if (i == 1 && l > 3)
			{
				this.pFilelist.appendChild(document.createTextNode(" (" + FM_MESS.More.replace("#COUNT#", parseInt(l - i - 1)) + ")"));
				break;
			}
			else if (i < l - 1)
			{
				this.pFilelist.appendChild(document.createTextNode(", "));
			}
		}

		this.oCopyDialog.adjustSizeEx();

		BX.bind(BX.browser.IsIE() ? document.body : window, "keydown", BX.proxy(this.OnKeyDown, this));
	},

	OnClose: function()
	{
		BX.unbind(BX.browser.IsIE() ? document.body : window, "keydown", BX.proxy(this.OnKeyDown, this));
	},

	OnKeyDown: function(e)
	{
		if (!e)
			e = window.event;

		if (window.oBXFileDialog && window.oBXFileDialog.bOpened)
			return;

		if (this.oCopyDialog.isOpen && e.keyCode == 13 && (!this.oAskUserDialog || !this.oAskUserDialog.isOpen))
			return this.Process();
	},

	Process: function(Params)
	{
		var
			_this = this,
			action = this.bCopy ? 'copy' : 'move',
			postParams = {
				case_option: this.caseOption,
				files: this.arFiles,
				copy_to: this.pCopyTo.value
			},
			onResult = function()
			{
				if (window.BXFM_NoCopyToDir)
				{
					if (window.BXFM_NoCopyToDir == "ask_user" && confirm(FM_MESS.NoFolder.replace('#FOLDER#', _this.pCopyTo.value)))
					{
						postParams.create_copy_to = "Y";
						_this.Request(action, postParams, onResult);
					}
					else if(window.BXFM_NoCopyToDir == "access_denied")
					{
						alert(FM_MESS.NoFolderNoAccess.replace('#FOLDER#', _this.pCopyTo.value));
					}

					window.BXFM_result = null;
					window.BXFM_NoCopyToDir = null;
				}

				//'FileExist'
				if (window.BXFM_fileExist)
				{
					// Run "Ask User Dialog"
					_this.ShowAskUserDialog(window.BXFM_fileExist);
					window.BXFM_fileExist = null;
				}

				// Finish
				if(window.BXFM_result)
				{
					var errorMess = "";
					// Errors occured during
					if (window.BXFM_result.status != 'ok')
					{
						for (var i = 0, l = window.BXFM_result.errors.length; i < l; i++)
							errorMess += window.BXFM_result.errors[i] + "\n";
					}

					// Display errors
					if (errorMess != "")
					{
						alert(errorMess);
					}
					else
					{
						// Files was moved and we have to refresh current dir
						if (!_this.bCopy || _this.pCopyTo.value === _this.curPath)
                            window.location = _this.bSearch ? window.location : (_this.viewMsFolderPath + _this.site).replace('#PATH#', BX.util.urlencode(_this.curPath));

						_this.oCopyDialog.Close();
					}
				}

			};

		if (Params)
		{
			postParams.uc_answer = Params.userCase;
			postParams.uc_to_all = Params.applyToAll;
			postParams.uc_last_path = Params.handledFilePath;
		}

		if (this.bSearch)
		{
			postParams.search = "Y";
			postParams.ssess = this.searchSess;
		}

		window.BXFM_noCopyToDir = window.BXFM_fileExist = window.BXFM_result = null;
		this.Request(action, postParams, onResult);
	},

	GetFileName: function(path)
	{
		var
			name = path,
			i = path.lastIndexOf("/");

		if (i !== -1)
			name = path.substr(i + 1);

		return name;
	},

	GetViewUrl: function(file, multisite)
	{
		var multisite = multisite || [false, ''];
        if (multisite[0])
            return ((!!file.isDir ? this.viewMsFolderPath : this.viewMsFilePath) + multisite[1]).replace('#PATH#', BX.util.urlencode(file.path));

		return ((!!file.isDir ? this.viewMsFolderPath : this.viewMsFilePath) + this.site).replace('#PATH#', BX.util.urlencode(file.path));
	},

	SaveConfig: function()
	{
		this.Request(
			'copy_save_config',
			{
				adv_mode: this.bShowAdvanced ? 1 : 0,
				case_option: this.caseOption
			},
		false, false);
	},

	Request : function(action, postParams, callBack, bShowWaitWin)
	{
		bShowWaitWin = bShowWaitWin !== false;

		if (bShowWaitWin)
			BX.showWait();

		var actionUrl = '/bitrix/admin/fileman_admin.php?lang=' + this.lang + "&fu_action=" + action + "&site=" + this.site + "&" + this.sessid_get + "&fu_site=" + this.oSiteSel.value;

		return BX.ajax.post(actionUrl, postParams || {},
			function(result)
			{
				if (bShowWaitWin)
					BX.closeWait();

				if(callBack)
					setTimeout(function(){callBack(result);}, 100);
			}
		);
	},

	ShowAskUserDialog: function(Params)
	{
		var _this = this;
		if (!this.oAskUserDialog)
		{
			this.oAskUserDialog = new BX.CAdminDialog({
				title : "",
				content: "&nbsp;",
				height: 240,
				width: 600,
				resizable: false
			});

			this.oAskUserDialog.SetButtons([
				new BX.CWindowButton(
				{
					title: FM_MESS.Replace,
					name: 'replace',
					id: 'ask_replace',
					action: function(){_this.UserAnswer('replace');}
				}),
				new BX.CWindowButton(
				{
					title: FM_MESS.Skip,
					name: 'skip',
					action: function(){_this.UserAnswer('skip');}
				}),
				new BX.CWindowButton(
				{
					title: FM_MESS.Rename,
					name: 'rename',
					action: function(){_this.UserAnswer('auto_rename');}
				}),
				this.oAskUserDialog.btnCancel
			]);

			BX.addClass(this.oAskUserDialog.PARTS.CONTENT, "bx-fm-copy-dialog");
			BX.addClass(this.oAskUserDialog.PARTS.FOOT, "bx-core-dialog-foot-ask");
			//BX.cleanNode(this.oAskUserDialog.PARTS.CONTENT);

			setTimeout(function()
			{
				var pAskPialog = BX('bx_copy_ask_dialog');
				_this.oAskUserDialog.SetContent(pAskPialog);
				pAskPialog.style.display = "block";

				_this.pAskToAllCont = pAskPialog.appendChild(BX.create("DIV", {props: {className: "bx-copy-to-all" }, html: "<table><tr><td><input type='checkbox' id='bx_copy_ask_to_all'></td><td><label  for='bx_copy_ask_to_all'>" + FM_MESS.ToAll + "</label></td></tr></table>"}));
				_this.oAskUserDialog.adjustSizeEx();

				BX.adminPanel.modifyFormElements(pAskPialog);
			}, 50);

			this.pAskFileName = BX("bx_copy_ask_file_name");
			this.pAskFolderName = BX("bx_copy_ask_folder");
			this.pAskSizeRow = BX("bx_copy_ask_size_row");

			this.pAskFileNew = {pName: BX("bx_copy_ask_file1"), pSize:  BX("bx_copy_ask_size1"), pDate:  BX("bx_copy_ask_date1")};
			this.pAskFileOld = {pName: BX("bx_copy_ask_file2"), pSize:  BX("bx_copy_ask_size2"), pDate:  BX("bx_copy_ask_date2")};

			this.pNewNameCont = BX('bxc_ask_nn_cont1');

			this.pRenBut = this.oAskUserDialog.PARAMS.buttons[2].btn;
			this.pRenBut.onmouseover = function()
			{
				_this._NewNamebShow = true;
				_this._ShadeIn(true);
			};
			this.pRenBut.onmouseout = function()
			{
				_this._NewNamebShow = false;
				setTimeout(function(){_this._ShadeIn(false);}, 3000);
			};

			BX.addCustomEvent(this.oAskUserDialog, 'onWindowUnRegister', function()
			{
				_this.oCopyTo.bDenyOpenPopup = false;
			});

		}

		this.oAskUserDialog.Show();
		this.oCopyTo.bDenyOpenPopup = true;
		this.oAskUserDialog.adjustSizeEx();

		//this.oAskUserDialog.PARTS.CONTENT.style.height = BX.browser.IsIE() ? "160px" : "170px";

		this.oAskUserDialog.SetTitle(Params.fileNew.bDir ? FM_MESS.FolderExistTitle : FM_MESS.FileExistTitle);
		// Copy to the same directory - disable "Replace" button

		var replaceButton = BX("ask_replace");

		if (this.curPath.replace(/[\s\r\n\/]+$/g, '') == this.pCopyTo.value.replace(/[\s\r\n\/]+$/g, '') && replaceButton)
			replaceButton.disabled = true;
		else
			replaceButton.disabled = false;

		if (this.arFiles.length <= 1) // Hide skip button
			this.oAskUserDialog.PARAMS.buttons[1].btn.style.display = "none";
		else // Show skip button
			this.oAskUserDialog.PARAMS.buttons[1].btn.style.display = "";

		setTimeout(function()
		{
			_this.pAskToAllCont.style.marginLeft = parseInt(_this.oAskUserDialog.PARAMS.buttons[0].btn.offsetLeft) + "px";
			BX('bx_copy_ask_to_all').disabled = !!(_this.arFiles.length <= 1);
		}, 200);

        var multisite = [false, ''];
        if (Params.fileNew.site && Params.fileOld.site && Params.fileNew.site !== Params.fileOld.site)
            multisite = [true, Params.fileOld.site];
		this.pAskFileName.innerHTML = Params.fileNew.name;
		this.pAskFolderName.innerHTML = this.pCopyTo.value;
		this.pAskFileOld.pName.innerHTML = this.pAskFileOld.pName.title = Params.fileOld.name;
		this.pAskFileOld.pName.href = this.GetViewUrl({
            'isDir' : Params.fileOld.bDir,
            'path' : Params.fileOld.path
        }, multisite);
		this.pAskFileOld.pDate.innerHTML = Params.fileOld.date;
		this.pAskFileNew.pName.innerHTML = this.pAskFileNew.pName.title = Params.fileNew.name;
		this.pAskFileNew.pName.href = this.GetViewUrl({
            'isDir' : Params.fileNew.bDir,
            'path' : Params.fileNew.path
        });
		this.pAskFileNew.pDate.innerHTML = Params.fileNew.date;
		this.oAskUserDialog.newFilePath = Params.fileNew.path;

		if (Params.fileNew.size == '-')
		{
			this.pAskSizeRow.style.display = 'none';
		}
		else
		{
			this.pAskSizeRow.style.display = '';
			this.pAskFileOld.pSize.innerHTML = Params.fileOld.size;
			this.pAskFileNew.pSize.innerHTML = Params.fileNew.size;
		}

		this.pNewNameCont.innerHTML = this.pNewNameCont.title = Params.fileNew.alt_name;
		this.pRenBut.title = FM_MESS.RenameTitle.replace("#NEW_NAME#", Params.fileNew.alt_name);
	},

	UserAnswer: function(userCase)
	{
		this.Process({
			userCase: userCase,
			applyToAll: BX('bx_copy_ask_to_all').checked ? 1 : 0,
			handledFilePath: this.oAskUserDialog.newFilePath
		});

		this.oAskUserDialog.Close();
	},

	_ShadeIn: function(bShow)
	{
		if (this._NewNamebShow != bShow)
			return;

		var _this = this;
		if (this._shadeInInterval)
		{
			clearInterval(this._shadeInInterval);
			this._shadeInInterval = false;
		}

		var shadeState = bShow ? 0 : 3;
		this._shadeInInterval = setInterval(function()
		{
			if (bShow)
				shadeState++;
			else
				shadeState--;
			_this.pNewNameCont.className = "bx-copy-new-name"+ " bxcnn-" + shadeState;

			if (shadeState == 0 || shadeState == 3)
			{
				clearInterval(_this._shadeInInterval);
				_this._shadeInInterval = false;
			}
		}, 100);
	}
};

var BXFMInpSel = function(Params)
{
	if (!Params.Items || !Params.Items.length || !Params.pInput)
		return;

	if (Params.popupWidth && !isNaN(parseInt(Params.popupWidth)))
		this.popupWidth = parseInt(Params.popupWidth);

	this.id = Params.id;
	this.Items = Params.Items;
	this.pInput = Params.pInput;
	this.posCorrection = Params.posCorrection || {left: 2, top: 21};
	this.onChange = typeof Params.OnChange == 'function' ? Params.OnChange : false;
	this.onEnterPress = typeof Params.OnEnterPress == 'function' ? Params.OnEnterPress : false;

	this.NoValueMess = Params.NoValueMess || '';
	this.selItemInd = false;
	BX.addClass(this.pInput, "bxfm-is-inp");

	var _this = this;
	this.pInput.onclick = function(e)
	{
		if (_this.selItemInd !== false)
			_this.DeSelectItem(_this.selItemInd);

		if (this.value == _this.NoValueMess)
		{
			BX.removeClass(this, "bxfm-is-label");
			this.value = '';
		}
		else if(this.value != "")
		{
			_this.bCheckValue = true;
			_this.CheckValue(false);
		}

		_this.ShowPopup();
		return BX.PreventDefault(e);
	};

	this.pInput.onfocus = function()
	{
		if (this.value == _this.NoValueMess)
		{
			BX.removeClass(this, "bxfm-is-label");
			this.value = '';
		}
		//_this.ShowPopup();
	};

	this.pInput.onblur = function()
	{
		if (!_this.bPopupShowed)
			_this.OnChange();
	};

	if (this.pInput.value == '')
	{
		this.OnChange(false);
	}

	this.pInput.autocomplete="off";
	this.pInput.onkeyup = function(e)
	{
		if (_this.bDenyOpenPopup)
			return true;
		if (!e)
			e = window.event;

		if (!e.altKey && !e.ctrlKey && e.keyCode != 17
		&& e.keyCode != 18 && e.keyCode != 16
		&& e.keyCode != 27 && e.keyCode != 13)
			return _this.CheckValue(true);
	};
	this.pInput.onkeydown = function(e){return _this.OnKeyDown(e);};
};

function BXFMPack(Params)
{
	this.Init(Params);
}

BXFMPack.prototype =
{
	Init: function(Params)
	{
		if (this.bInited)
			return true;

		this.oPackDialog = Params.oPackDialog;
		BX.addClass(this.oPackDialog.PARTS.CONTENT, "bx-fm-pack-dialog");
		BX.cleanNode(this.oPackDialog.PARTS.CONTENT_DATA);
		this.oPackDialog.PARTS.CONTENT_DATA.appendChild(BX('bx_pack_dialog'));
		this.arLastPathes = Params.arLastPathes;

		this.pPackCancel = BX("cancel-pack");
		this.pPackCancel.onclick = function()
		{
			if (_this.bPacking)
			{
				_this.oPackDialog.SetTitle(FM_MESS.PackFinishing);
				_this.bStopPacking = true;

				//if 'replace' dialog was shown, simply close the form
				if (_this.Params && _this.Params.fileOld)
				{
					_this.Params.fileOld = null;
					_this.bPacking = false;
					_this.oPackDialog.Close();
				}
			}
			else
			{
				_this.oPackDialog.Close();
			}
		};

		var _this = this;
		this.pCopyTbl = BX('bx_pack_table');
		this.pFilelist = BX('bx_pack_file_list');

		this.pPackTo = BX('bx_pack_to');

		// Case options
		this.pCaseReplace = BX("bx_pack_replace");
		this.pCaseSkip = BX("bx_pack_skip");

		this.pCaseReplace.onclick = this.pCaseSkip.onclick = function()
		{
			if (this.checked)
				_this.caseOption = this.value;
		};

		this.lang = Params.lang;
		this.site = Params.site;
		this.sessid_get = Params.sessid_get;
		BX('bx_pack_dialog').style.display = "block";

        this.viewMsFilePath = Params.viewMsFilePath;
        this.viewMsFolderPath = Params.viewMsFolderPath;

		//archive type selector
		this.oArcTypeSel = new BXFMArcTypeSel({
				id: 'arc_type_pack',
				pDiv: BX('bx_pack_arc_type'),
				types: Params.arTypes,
				bPack: true,
				typeChangeCallback: function(newtype) {_this.ChangeType(newtype)}
		});

		this.oPackTo = new BXFMInpSel({
			id: 'cm_pack_to',
			pInput : this.pPackTo,
			Items: this.arLastPathes
		});

        this.oSiteSel = new BXFMSiteSel({
            id: 'site_sel_pack',
            pDiv: BX('bx_pack_site_sel'),
            sites: Params.arSites
        });

		this.caseOption = 'skip';
		BX.removeClass(_this.pCopyTbl, 'bx-pack-cont-tbl-add-hide');

		switch(this.caseOption)
		{
			case "replace":
				this.pCaseReplace.checked = true;
				break;
			case "skip":
				this.pCaseSkip.checked = true;
				break;
		}

		BX.addCustomEvent(this.oPackDialog, 'onWindowUnRegister', BX.proxy(this.OnClose, this));

		this.pCaseSkip = BX("bx_pack_skip");
		this.pCaseReplace.onclick = this.pCaseSkip.onclick = function()
		{
			if (this.checked)
				_this.caseOption = this.value;
		};

		BX.addCustomEvent(this.oPackDialog, 'onBeforeWindowClose', function(){

			_this.oPackDialog.denyClose = false;

			if (_this.bPacking)
			{
				if (!_this.forceClose)
				{
					_this.oPackDialog.SetTitle(FM_MESS.PackFinishing);
					_this.bStopPacking = true;

					//if we tried to rename file...
					if (_this.Params && _this.Params.fileOld)
						_this.Params.fileOld = null;
					else
						_this.oPackDialog.denyClose = true;
				}
			}

		});

		this.bInited = true;
	},

	OnOpen: function(Params)
	{
		this.bPack = Params.bPack;
		this.arFiles = [];
		this.curPath = Params.path;
		this.bStopPacking = false;
		this.bPacking = false;
		this.forceClose = false;

		this.bSearch = !!Params.bSearch;
		this.searchSess = Params.ssess;

		if (typeof Params.arFiles == 'object')
			this.arFiles = Params.arFiles;

        if (Params.arFiles[0] && Params.arFiles[0] == 'action_target')
			this.arFiles = [{'path' : Params.path, 'isDir' : '1'}];

		//cancel button
		var cancelBut = this.oPackDialog.PARAMS.buttons[1];
		cancelBut.btn.value = FM_MESS.PackCancel;

		clearInterval(this.counterID);

		//generate name where to pack or unpack (/test/test.tar.gz or /test/)
		this.pPackTo.value = this.GeneratePath(this.bPack, this.arFiles, "." + this.oArcTypeSel.value.toLowerCase());

		var oBut = this.oPackDialog.PARAMS.buttons[0];
		if (this.bPack)
		{
			this.oPackDialog.SetTitle(FM_MESS.PackTitle);
			oBut.btn.value = oBut.title = FM_MESS.Pack;

			//when showing the pack dialog - make archive type selector clickable again
			if (this.oArcTypeSel.arcTypes.length == 1)
			{
				BX.addClass(this.oArcTypeSel.pDiv,"bx-fm-non-selectable");
				BX.unbindAll(this.oArcTypeSel.pDiv);
			}
			else
			{
				BX.removeClass(this.oArcTypeSel.pDiv,"bx-fm-non-selectable");
				BX.bind(this.oArcTypeSel.pDiv, "click", BX.proxy(this.oArcTypeSel.ShowPopup, this.oArcTypeSel));
			}

			BX('bxfm-arctype-line').style.display = "table-row";

			//rewriting options: set default value and hide unnecessary choices
			BX('bxfm-pack-option-replace').style.display = "none";
			BX('bxfm-pack-option-skip').style.display = "none";
			BX('bx-pack-d-title-label').style.display = "none";
			this.pCaseSkip.checked = true;
			this.caseOption = "ask";
		}
		else
		{
			this.oPackDialog.SetTitle(FM_MESS.UnpackTitle);
			oBut.btn.value = oBut.title = FM_MESS.Unpack;

			BX('bxfm-arctype-line').style.display = "none";

			//remove unnecessary rewriting options
			BX('bx-pack-d-title-label').style.display = "table-row";
			BX('bxfm-pack-option-skip').style.display = "table-row";
			BX('bxfm-pack-option-replace').style.display = "table-row";
			this.pCaseSkip.checked = true;
			this.caseOption = "skip";
		}
		//set 'Archive' button active
		BX('ok-pack').disabled = false;

		// Clean filelist
		BX.cleanNode(this.pFilelist);
		var i, l = this.arFiles.length;

		for (i = 0; i < l; i++)
		{
			this.pFilelist.appendChild(BX.create("A", {props: {href: this.GetViewUrl(this.arFiles[i], this.site), target: '_blank'}, text: this.GetFileName(this.arFiles[i])}));

			if (i == 1 && l > 3)
			{
				this.pFilelist.appendChild(document.createTextNode(" (" + FM_MESS.More.replace("#COUNT#", parseInt(l - i - 1)) + ")"));
				break;
			}
			else if (i < l - 1)
			{
				this.pFilelist.appendChild(document.createTextNode(", "));
			}
		}

		this.oPackDialog.adjustSizeEx();

		BX.bind(BX.browser.IsIE() ? document.body : window, "keydown", BX.proxy(this.OnKeyDown, this));
	},

	OnClose: function()
	{
		clearInterval(this.counterID);
		BX.unbind(BX.browser.IsIE() ? document.body : window, "keydown", BX.proxy(this.OnKeyDown, this));
	},

	OnKeyDown: function(e)
	{
		if (!e)
			e = window.event;

		if (window.oBXFileDialog && window.oBXFileDialog.bOpened)
			return;

		if (this.oPackDialog.isOpen && e.keyCode == 13 && (!this.oAskUserDialog || !this.oAskUserDialog.isOpen))
			return this.Process();
	},

	Process: function(Params)
	{
		var bPackReplace = null;

		if (Params)
		{
			switch(Params.userCase)
			{
				case "rename":
					var tmpName = this.GetFolderPath(this.pPackTo.value);
					this.pPackTo.value = tmpName + Params.newName;
					break;
				case "replace":
					bPackReplace = "replace";
					break;
			}
		}

		var
			_this = this,
			action = this.bPack ? 'pack' : 'unpack',
			postParams =
			{
				case_option: this.caseOption,
				files: this.arFiles,
				packTo: this.pPackTo.value,
                siteTo: this.oSiteSel.value,
				startFile: '',
				quickPath: BX('quick_path').value,
				arcType: this.oArcTypeSel.value,
				bPackReplace: bPackReplace
			};

			//stop progress bar
			if (this.counterID)
				clearInterval(this.counterID);

			//disable archive button
			BX('ok-pack').disabled = true;

			//progress bar blinking ... notification
			this.counterID = setInterval(function()
			{
				if ((_this.oPackDialog.PARAMS.title.split('.').length - 1) < 3)
				{
					this.oPackDialog.SetTitle(_this.oPackDialog.PARAMS.title + ' .');
				}
				else
				{
					this.oPackDialog.SetTitle(_this.oPackDialog.PARAMS.title.split(' .')[0]);
				}
			}, 500);

			var onResult = function()
			{
				if (!_this.oPackDialog.isOpen)
					return;

				if (_this.bStopPacking)
				{
					if (_this.bPack && !window.BXFM_archiveExists)
					{
						var fileID = _this.GetFileName(postParams.packTo),
							filePath = _this.GetFolderPath(postParams.packTo),
							deleteFileUrl = "/bitrix/admin/fileman_admin.php?action=delete&ID="
							 + fileID + "&path=" + filePath + "&" + _this.sessid_get + "&lang=" + _this.lang + "&site=" + _this.site;

						_this.bStopPacking = false;
						tbl_fileman_admin.GetAdminList(deleteFileUrl, function(){
							_this.forceClose = true;
							_this.oPackDialog.Close();
						}
						);
						return;
					}
					else
					{
						_this.forceClose = true;
						_this.oPackDialog.Close();
						return;
					}
				}

				if (window.BXFM_archivePermsError)
				{
					alert(FM_MESS.PackPermsError);
					BX('ok-pack').disabled = false;
					window.BXFM_archivePermsError = null;
					_this.oPackDialog.Close();
				}
				else if (window.BXFM_archiveExists)
				{
					_this.ShowAskUserDialog(window.BXFM_archiveExists);
					window.BXFM_archiveExists = null;
					//set Archive button as active again
					BX('ok-pack').disabled = false;
				}
				else if (window.BXFM_archiveFNameError)
				{
					alert(FM_MESS.PackFNameError);
					BX('ok-pack').disabled = false;
					window.BXFM_archiveFNameError = null;
					_this.forceClose = true;
					_this.oPackDialog.Close();
				}
				else
				{
					switch(action)
					{
						case "pack":
							if (window.fmPackTimeout)
							{
								postParams.startFile = window.fmPackLastFile;
							}
							else
							{
								//successfull packing
								if (window.fmPackSuccess)
								{
									_this.forceClose = true;
									_this.oPackDialog.Close();

									var redirectPath = _this.GetFolderPath(postParams.packTo);
                                    redirectPath = (redirectPath.length == (redirectPath.lastIndexOf('/') + 1) && redirectPath.length !== 1) ? redirectPath.substr(0, redirectPath.length - 1) : redirectPath;
                                    window.location = (_this.viewMsFolderPath + _this.oSiteSel.value).replace('#PATH#', BX.util.urlencode(redirectPath));

									return;
								}
								else
								{
									if (window.fmPackErrors)
										alert(FM_MESS.PackError + ": " + window.fmPackErrors);
									else
										alert(FM_MESS.PackError);
									BX.closeWait();
									_this.forceClose = true;
									_this.oPackDialog.Close();
									return;
								}
							}
							/*
							possible: update progress bar
							_this.pDiv.innerHTML = window.fmPackLastFile;
							Current path: /bigfile/bigfile.flv
							*/
						break;
						case "unpack":
							//successful unpacking
							if (window.fmUnpackSuccess)
							{
								_this.forceClose = true;
								_this.oPackDialog.Close();

                                var redirectPath = _this.GetFolderPath(postParams.packTo);
                                redirectPath = (redirectPath.length == (redirectPath.lastIndexOf('/') + 1) && redirectPath.length !== 1) ? redirectPath.substr(0, redirectPath.length - 1) : redirectPath;
								window.location = (_this.viewMsFolderPath + postParams.siteTo).replace('#PATH#', BX.util.urlencode(redirectPath));
								return;
							}
							else
							{
								if (window.fmUnpackErrors)
									alert(FM_MESS.UnpackError + ": " + window.fmUnpackErrors);
								else
									alert(FM_MESS.UnpackError);
								BX.closeWait();
								_this.forceClose = true;
								_this.oPackDialog.Close();
								return;
							}
						break;
					}

					if (action == "pack")
					{
						if (_this.rq)
							_this.rq.abort();

						_this.rq = _this.Request(action, postParams, onResult);
					}

				}
			};

		this.bPacking = true;
		this.rq = this.Request(action, postParams, onResult);
	},

	GetFolderPath: function(fullpath)
	{
		var tmpPath = fullpath;

		var i = tmpPath.lastIndexOf('/');
		if (i != '-1')
		{
			tmpPath = tmpPath.slice(0,i);
		}

		if (tmpPath != "/")
			tmpPath += "/";

		return tmpPath;
	},

	GetFileName: function(filePath)
	{
        if (typeof filePath == 'object')
            filePath = filePath.path;
		var
			name = filePath,
			i = filePath.lastIndexOf("/");

		if (i !== -1 && filePath.length !== 1)
			name = filePath.substr(i + 1);

		return name;
	},

	MakeArchiveName: function (str)
	{
		/* 	INPUT:
			/test1/test2
			/test1/myfile.doc
			/test/file.tar.gz
			/.access.php
			/.top.menu_ext.php
			/.htaccess

			OUTPUT:
			test2
			myfile
			file
			.access
			.top_menu_ext
			.htaccess
		*/

		//get only the last part of the name
		var tmp = str.substr(str.lastIndexOf('/') + 1);

		//check for .tar.gz
		if (tmp.slice(-7) == ".tar.gz")
		{
			tmp = tmp.slice(0,-7);
		}
		//else remove extension if exists
		else
		{
			if (tmp.lastIndexOf('.') != -1 && tmp.lastIndexOf('.') != 0)
				tmp = tmp.slice(0,tmp.lastIndexOf('.'))
		}

		return tmp;
	},

    MakeFolderArchiveName: function (str)
	{
		//get only the last part of the name
		var tmp = str.substr(str.lastIndexOf('/') + 1);

		return tmp;
	},

	GeneratePath: function(bpack, files, ext)
	{
        if (bpack && files.length == 1 && !!files[0].isDir == true)
        {
            return this.GetFolderPath(files[0].path) + this.MakeFolderArchiveName(files[0].path) + ext;
        }
		//returns /path/name.ext
		if (bpack && files.length > 0)
		{
			var tmpname = files.length == 1 ? files[0].path : "archive";
			return this.GetFolderPath(this.arFiles[0].path) + this.MakeArchiveName(tmpname) + ext;
		}

		if (!bpack)
		{
			/*
			INPUT: /test2/test1/test.tar.gz /test2/test1/test.zip
			OUTPUT: /test2/test1/
			*/
			var pth = files[0];

			return this.GetFolderPath(pth);
		}
	},

	ChangeType: function(newext)
	{

		newext = newext.toLowerCase();

		if (this.arFiles)
			this.pPackTo.value = this.GeneratePath(this.bPack, this.arFiles, "." + newext);

	},

	GetViewUrl: function(file, site)
	{
        if (typeof file == 'object')
		    return ((file.isDir ? this.viewMsFolderPath : this.viewMsFilePath) + site).replace('#PATH#', BX.util.urlencode(file.path));
        return (this.viewMsFilePath + site).replace('#PATH#', BX.util.urlencode(file));
	},

	//packing
	Request : function(action, postParams, callBack, bShowWaitWin)
	{
		bShowWaitWin = bShowWaitWin !== false;

		if (bShowWaitWin)
			BX.showWait();

		var actionUrl = '/bitrix/admin/fileman_admin.php?lang=' + this.lang
			+ "&fu_action=" + action + "&site=" + this.site + "&"
			+ this.sessid_get;

		//lock archive type selector if already packing
		if (action == "pack")
		{
			BX.addClass(this.oArcTypeSel.pDiv,"bx-fm-non-selectable");
			BX.unbindAll(this.oArcTypeSel.pDiv);
		}

		return BX.ajax.post(actionUrl, postParams || {},
			function(result)
			{
				if (bShowWaitWin)
					BX.closeWait();

				if(callBack)
					setTimeout(function(){callBack(result);}, 100);
			}
		);
	},

	//packing
	ShowAskUserDialog: function(Params)
	{
		var _this = this;
		_this.Params = Params;

		if (!this.oAskUserDialog)
		{
			this.oAskUserDialog = new BX.CAdminDialog({
				title : "",
				content: "&nbsp;",
				height: 240,
				width: 500,
				resizable: false
			});

			this.oAskUserDialog.SetButtons([
				new BX.CWindowButton(
				{
					title: FM_MESS.Replace,
					name: 'replace',
					action: function(){_this.UserAnswer('replace');}
				}),
				new BX.CWindowButton(
				{
					title: FM_MESS.Rename,
					name: 'rename',
					action: function()
					{
						var newName = prompt(FM_MESS.AskNewName, _this.Params.fileOld.name);
						_this.Params.fileOld.name = null;
						if (newName)
							_this.UserAnswer('rename', newName);
					}
				}),
				this.oAskUserDialog.btnCancel
			]);

			BX.addClass(this.oAskUserDialog.PARTS.CONTENT, "bx-fm-pack-dialog");
			BX.addClass(this.oAskUserDialog.PARTS.FOOT, "bx-core-dialog-foot-ask");
			//BX.cleanNode(this.oAskUserDialog.PARTS.CONTENT);

			setTimeout(function()
			{
				var pAskPialog = BX('bx_pack_ask_dialog');
				_this.oAskUserDialog.SetContent(pAskPialog);
				pAskPialog.style.display = "block";

				// _this.pAskToAllCont = pAskPialog.appendChild(BX.create("DIV", {props: {className: "bx-pack-to-all" }, html: "<table><tr><td><input type='checkbox' id='bx_copy_ask_to_all'></td><td><label  for='bx_copy_ask_to_all'>" + FM_MESS.ToAll + "</label></td></tr></table>"}));
				_this.oAskUserDialog.adjustSizeEx();
			}, 50);

			this.pAskFileName = BX("bx_pack_ask_file_name");
			this.pAskFolderName = BX("bx_pack_ask_folder");
			this.pAskSizeRow = BX("bx_pack_ask_size_row");

			this.pAskFileOld = {pName: BX("bx_pack_ask_file2"), pSize:  BX("bx_pack_ask_size2"), pDate:  BX("bx_pack_ask_date2")};

			this.pRenBut = this.oAskUserDialog.PARAMS.buttons[2].btn;

			BX.addCustomEvent(this.oAskUserDialog, 'onWindowUnRegister', function()
			{
				_this.oPackTo.bDenyOpenPopup = false;
			});
		}

		this.oAskUserDialog.Show();

		if (_this.counterID)
				clearInterval(_this.counterID);

		this.oPackTo.bDenyOpenPopup = true;
		this.oAskUserDialog.adjustSizeEx();

		//this.oAskUserDialog.PARTS.CONTENT.style.height = BX.browser.IsIE() ? "160px" : "170px";
		this.oAskUserDialog.SetTitle(FM_MESS.FileExistTitle);

		this.pAskFileName.innerHTML = Params.fileOld.name;
		this.pAskFolderName.innerHTML = _this.GetFolderPath(this.pPackTo.value);
		this.pAskFileOld.pName.innerHTML = this.pAskFileOld.pName.title = Params.fileOld.name;
		this.pAskFileOld.pName.href = this.GetViewUrl(Params.fileOld.path, Params.fileOld.site);
		this.pAskFileOld.pDate.innerHTML = Params.fileOld.date;
		this.pAskFileOld.pSize.innerHTML = Params.fileOld.size;
	},

	UserAnswer: function(userCase, newName)
	{
		if (userCase == "replace")
			this.Params = null;

		this.Process({
			userCase: userCase,
			newName: newName
		});
		this.oAskUserDialog.Close();
	}
};

BXFMInpSel.prototype = {
	ShowPopup: function(bSelectInput)
	{
		if (this.bPopupShowed || this.bDenyOpenPopup)
			return;

		var _this = this;
		if (bSelectInput !== false)
			this.pInput.select();

		if (!this.bPopupCreated)
			this.CreatePopup();

		this.Popup.style.display = 'block';
		this.bPopupShowed = true;

		setTimeout(function(){BX.bind(document, "click", BX.proxy(_this.ClosePopup, _this));}, 100);

		//GetRealPos
		var pos = jsUtils.GetRealPos(this.pInput);

		jsFloatDiv.Show(this.Popup, pos.left + this.posCorrection.left, pos.top + this.posCorrection.top, 3);
		BX.WindowManager.disableKeyCheck();
	},

	ClosePopup: function(bCheck)
	{
		BX.unbind(document, "click", BX.proxy(this.ClosePopup, this));
		setTimeout(function(){BX.WindowManager.enableKeyCheck();}, 200);

		if (!this.Popup || !this.pInput)
			return;

		this.Popup.style.display = 'none';
		this.bPopupShowed = false;
		jsFloatDiv.Close(this.Popup);

		if (bCheck !== false && this.pInput.value == '')
			this.OnChange();

		if (this.pInput.focus)
			this.pInput.focus();
	},

	CreatePopup: function()
	{
		var
			_this = this,
			pRow, i, l = this.Items.length;

		this.Popup = document.body.appendChild(BX.create("DIV", {props:{className: "bxfm-is-popup"}}));
		if (!this.popupWidth)
			this.Popup.style.width = parseInt(this.pInput.offsetWidth) + "px";

		this.bPopupCreated = true;

		for (i = 0; i < l; i++)
		{
			pRow = this.Popup.appendChild(BX.create("DIV", {
				props: {id: 'bx_' + this.id + '_' + i, title: this.Items[i].name || this.Items[i].title, className: 'bxfm-is-item'},
				text: this.Items[i].name,
				events: {
					mouseover: function(){BX.addClass(this, 'bxfm-is-item-over');},
					mouseout: function(){BX.removeClass(this, 'bxfm-is-item-over');},
					click: function()
					{
						var ind = this.id.substr(('bx_' + _this.id + '_').length);
						_this.curInd = ind;
						_this.pInput.value = _this.Items[ind].name;

						_this.OnChange();
						_this.ClosePopup();
					}
				}
			}));

			this.Items[i].pCont = pRow;
		}
	},

	OnChange: function(bOnChange)
	{
		var val = this.pInput.value;
		if (val == '' || val == this.NoValueMess)
		{
			BX.addClass(this.pInput, "bxfm-is-label");
			this.pInput.value = this.NoValueMess;
			val = '';
		}
		else
		{
			BX.removeClass(this.pInput, "bxfm-is-label");
		}

		if (this.onChange && bOnChange !== false)
			this.onChange({value: val});
	},

	CheckValue: function(bHighlight, bClose)
	{
		if (!this.bCheckValue)
			return;

		var
			bConcur = false,
			val, i, l = this.Items.length,
			curValue = this.pInput.value;

		for (i = 0; i < l; i++)
		{
			val = this.Items[i].name;

			if ((val.length > curValue.length && val.substr(0, curValue.length) == curValue) ||
				val == curValue)
			{
				this.SelectItem(i, bHighlight);
				bConcur = true;
				break;
			}
		}

		if (!bConcur && bClose !== false)
		{
			if (this.selItemInd !== false)
				this.DeSelectItem(this.selItemInd);

			this.ClosePopup(false);
		}
	},

	SelectItem: function(ind, bHighlight)
	{
		if (!this.bPopupShowed)
			this.ShowPopup(false);

		if (this.selItemInd !== false)
			this.DeSelectItem(this.selItemInd);

		var pCont = this.Items[ind].pCont;
		if (bHighlight)
		{
			var l = this.pInput.value.length;
			BX.cleanNode(pCont);
			pCont.appendChild(BX.create("SPAN", {props: {className: "bxfm-highlighted"}, text: this.Items[ind].name.substr(0, l)}));
			pCont.appendChild(document.createTextNode(this.Items[ind].name.substr(l)));
		}

		this.selItemInd = ind;
		BX.addClass(pCont, "bxfm-is-item-concur");
	},

	DeSelectItem: function(ind)
	{
		BX.cleanNode(this.Items[ind].pCont);
		this.Items[ind].pCont.appendChild(document.createTextNode(this.Items[ind].name));
		BX.removeClass(this.Items[ind].pCont, "bxfm-is-item-concur");
		this.selItemInd = false;
	},

	OnKeyDown: function(e)
	{
		if (this.bDenyOpenPopup)
			return true;

		if (window.oBXFileDialog && window.oBXFileDialog.bOpened)
			return;

		this.bCheckValue = true;

		if (!e)
			e = window.event;

		// select item - paste value to input and close popup
		if (e.keyCode == 13)
		{
			if (!this.bPopupShowed)
			{
				if (this.onEnterPress)
				{
					this.onEnterPress({value: this.pInput.value});
					return BX.PreventDefault(e);
				}

				this.bCheckValue = false;
				return;
			}

			if (this.selItemInd)
			{
				this.bCheckValue = false;

				this.OnChange();
				this.ClosePopup();
				return BX.PreventDefault(e);
			}
		}
		else if (e.keyCode == 27) // Esc
		{
			if (!this.bPopupShowed)
				return;

			this.ClosePopup();
			this.bCheckValue = false;
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 40 || e.keyCode == 38) // Down or Up
		{
			var ind;
			if (e.keyCode == 40)
			{
				if (!this.bPopupShowed)
				{
					this.CheckValue(false, false);
					this.bCheckValue = false;
					this.ShowPopup(false);
					return;
				}

				ind = this.selItemInd === false ? 0 : this.selItemInd + 1;
				if (ind > this.Items.length - 1)
					ind = 0;
			}
			else
			{
				if (!this.bPopupShowed)
					return;

				ind = this.selItemInd === false ? this.Items.length - 1 : this.selItemInd - 1;
				if (ind < 0)
					ind = this.Items.length - 1;
			}

			this.pInput.value = this.Items[ind].name;
			this.SelectItem(ind, false);
			this.bCheckValue = false;
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 39) // Right
		{
			if (this.selItemInd !== false && this.bPopupShowed)
			{
				this.pInput.value = this.Items[this.selItemInd].name;
				this.SelectItem(this.selItemInd, false);
				this.bCheckValue = false;
				this.ClosePopup();
			}
		}
	}
}

var BXFMSiteSel = function(Params)
{
	this.pDiv = Params.pDiv;
	this.sites = Params.sites;

	var i, l = this.sites.length;

	if (l == 1)
	{
		this.bOne = true;
		this.pDiv.style.display = "none";
		return this.SetSite(0, false);
	}

	this.pTitle = this.pDiv.appendChild(BX.create("DIV"));
	this.pDiv.onclick = BX.proxy(this.ShowPopup, this);

	this.id = Params.id || "site_sel";
	for (i = 0; i < l; i++)
	{
		if (this.sites[i].current)
		{
			this.SetSite(i, false);
			break;
		}
	}
};

BXFMSiteSel.prototype = {
	ShowPopup: function()
	{
		if (this.bShowed)
			return this.ClosePopup();

		this.bShowed = true;

		var _this = this;
		if (!this.bCreated)
			this.CreatePopup();

		var pos = BX.pos(this.pDiv);

		this.Popup.style.display = 'block';
		this.Popup.style.top = (pos.top + 18) + "px";
		this.Popup.style.left = pos.left  + "px";

		BX.ZIndexManager.bringToFront(this.Popup);

		BX.WindowManager.disableKeyCheck();
		setTimeout(function(){BX.bind(document, "click", BX.proxy(_this.ClosePopup, _this));}, 100);
		BX.bind(document, 'keydown', BX.proxy(this.OnKeyDown, this));
	},

	ClosePopup: function()
	{
		BX.unbind(document, "click", BX.proxy(this.ClosePopup, this));
		BX.unbind(document, 'keydown', BX.proxy(this.OnKeyDown, this));
		setTimeout(function(){BX.WindowManager.enableKeyCheck();}, 200);

		if (!this.Popup)
			return;

		this.Popup.style.display = 'none';
		this.bShowed = false;
	},

	CreatePopup: function()
	{
		var
			_this = this, site,
			pRow, i, l = this.sites.length;

		this.Popup = document.body.appendChild(BX.create("DIV", {props:{className: "bxfm-at-is-popup"}}));

		BX.ZIndexManager.register(this.Popup);

		this.Popup.style.width = "200px";
		this.bCreated = true;

		for (i = 0; i < l; i++)
		{
			site = this.sites[i];
			pRow = this.Popup.appendChild(BX.create("SPAN", {
				props: {id: 'bx_' + this.id + '_' + i, title: BX.util.htmlspecialchars(site.text), className: 'bxfm-site-sel-it'},
				events: {
					mouseover: function(){BX.addClass(this, 'bxfm-ss-over');},
					mouseout: function(){BX.removeClass(this, 'bxfm-ss-over');},
					click: function()
					{
						var ind = this.id.substr(('bx_' + _this.id + '_').length);
						_this.SetSite(parseInt(ind), true);
						_this.ClosePopup();
					}
				}
			}));
			pRow.appendChild(BX.create("DIV", {props: {className: 'bxfm-text-overflow'}, text: site.text}));

			if (this.curSiteInd === i)
				BX.addClass(pRow, "bxfm-ss-checked");
			this.sites[i].row = pRow;
		}
	},

	SetSite: function(ind)
	{
		var site = this.sites[ind];
		if (!site)
			return;

		if (!this.bOne && typeof this.curSiteInd != 'undefined' && this.sites[this.curSiteInd] && this.sites[this.curSiteInd].row)
			BX.removeClass(this.sites[this.curSiteInd].row, "bxfm-ss-checked");

		this.value = site.id;
		this.curSiteInd = ind;

        BX('bx_copy_to').value = site['dir'];
        BX('bx_copy_to').focus();
		if (this.bOne)
			return;

		this.pTitle.innerHTML = site.id.toUpperCase();

		if (this.sites[ind].row)
			BX.addClass(this.sites[ind].row, "bxfm-ss-checked");
	},

	OnKeyDown: function(e)
	{
		if (!e)
			e = window.event;
		if (window.oBXFileDialog && window.oBXFileDialog.bOpened)
			return;
		if (e.keyCode == 27)
			return this.ClosePopup();
	}
};

/* archive type selector */
var BXFMArcTypeSel = function(Params)
{
	this.pDiv = Params.pDiv;
	this.arcTypes = Params.types;
	this.bPack = Params.bPack;
	this.typeChangeCallback = Params.typeChangeCallback;

	var l = this.arcTypes.length;

	//if only one archive type is available, show it without selector
	if (l == 1)
	{
		this.bOne = true;
		BX.addClass(this.pDiv,"bx-fm-non-selectable");
		this.pDiv.innerHTML = this.arcTypes[0].text.toUpperCase();
		return this.SetArcType(0, false);
	}

	this.pTitle = this.pDiv.appendChild(BX.create("DIV"));
	BX.bind(this.pDiv, "click", BX.proxy(this.ShowPopup, this));

	this.id = Params.id || "arc_type_pack";

	//default
	this.SetArcType(0, false);
};

BXFMArcTypeSel.prototype = {

	ShowPopup: function()
	{
		if (this.bShowed)
			return this.ClosePopup();

		this.bShowed = true;

		var _this = this;
		if (!this.bCreated)
			this.CreatePopup();

		var pos = BX.pos(this.pDiv);

		this.Popup.style.display = 'block';
		this.Popup.style.top = (pos.top + 18) + "px";
		this.Popup.style.left = pos.left  + "px";

		BX.ZIndexManager.bringToFront(this.Popup);

		BX.WindowManager.disableKeyCheck();
		setTimeout(function(){BX.bind(document, "click", BX.proxy(_this.ClosePopup, _this));}, 100);
		BX.bind(document, 'keydown', BX.proxy(this.OnKeyDown, this));
	},

	ClosePopup: function()
	{
		BX.unbind(document, "click", BX.proxy(this.ClosePopup, this));
		BX.unbind(document, 'keydown', BX.proxy(this.OnKeyDown, this));
		setTimeout(function(){BX.WindowManager.enableKeyCheck();}, 200);

		if (!this.Popup)
			return;

		this.Popup.style.display = 'none';
		this.bShowed = false;
	},

	CreatePopup: function()
	{
		var
			_this = this, arctype,
			pRow, i, l = this.arcTypes.length;

		this.Popup = document.body.appendChild(BX.create("DIV", {props:{className: "bxfm-at-is-popup"}}));
		this.Popup.style.width = "260px";
		this.bCreated = true;

		BX.ZIndexManager.register(this.Popup);

		for (i = 0; i < l; i++)
		{
			arctype = this.arcTypes[i];

			pRow = this.Popup.appendChild(BX.create("DIV", {
				props: {id: 'bx_' + this.id + '_' + i, title: BX.util.htmlspecialchars(arctype.text), className: 'bxfm-arc-type-it'},
				events: {
					mouseover: function(){BX.addClass(this, 'bxfm-at-over');},
					mouseout: function(){BX.removeClass(this, 'bxfm-at-over');},
					click: function()
					{
						var ind = this.id.substr(('bx_' + _this.id + '_').length);
						_this.SetArcType(parseInt(ind), true);
						_this.ClosePopup();
					}
				}
			}));
			pRow.appendChild(BX.create("DIV", {props: {className: 'bxfm-text-overflow'}, text: arctype.text}));

			if (this.curArcTypeInd === i)
				BX.addClass(pRow, "bxfm-at-checked");
			this.arcTypes[i].row = pRow;
		}
	},

	SetArcType: function(ind)
	{
		var arctype = this.arcTypes[ind];
		if (!arctype)
			return;

		if (!this.bOne && typeof this.curArcTypeInd != 'undefined' && this.arcTypes[this.curArcTypeInd] && this.arcTypes[this.curArcTypeInd].row)
			BX.removeClass(this.arcTypes[this.curArcTypeInd].row, "bxfm-at-checked");

		this.value = arctype.id;
		this.curArcTypeInd = ind;

		if (this.bOne)
			return;

		this.pTitle.innerHTML = arctype.text.toUpperCase();

		if (this.bPack)
			this.typeChangeCallback(this.pTitle.innerHTML);

		if (this.arcTypes[ind].row)
			BX.addClass(this.arcTypes[ind].row, "bxfm-at-checked");
	},

	OnKeyDown: function(e)
	{
		if (!e)
			e = window.event;
		if (window.oBXFileDialog && window.oBXFileDialog.bOpened)
			return;
		if (e.keyCode == 27)
			return this.ClosePopup();
	}
};
