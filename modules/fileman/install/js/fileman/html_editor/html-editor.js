	/**
	 * Bitrix HTML Editor 3.0
	 * Date: 24.04.13
	 * Time: 4:23
	 */
	(function(window) {
		var __BXHtmlEditorParserRules;

	function BXEditor(config)
	{
		// Container contains links to dom elements
		this.InitUtil();
		this.dom = {
			// cont -
			// iframeCont -
			// textareaCont -
			// iframe -
			// textarea -
		};

		this.bxTags = {};

		this.EMPTY_IMAGE_SRC = '/bitrix/images/1.gif';
		this.HTML5_TAGS = [
			"abbr", "article", "aside", "audio", "bdi", "canvas", "command", "datalist", "details", "figcaption",
			"figure", "footer", "header", "hgroup", "keygen", "mark", "meter", "nav", "output", "progress",
			"rp", "rt", "ruby", "svg", "section", "source", "summary", "time", "track", "video", "wbr"
		];
		this.BLOCK_TAGS = ["H1", "H2", "H3", "H4", "H5", "H6", "P", "BLOCKQUOTE", "DIV", "SECTION", "PRE"];
		this.NESTED_BLOCK_TAGS = ["BLOCKQUOTE", "DIV"];
		this.TABLE_TAGS = ["TD", "TR", "TH", "TABLE", "TBODY", "CAPTION", "COL", "COLGROUP", "TFOOT", "THEAD"];
		this.BBCODE_TAGS = ['P', 'U', 'TABLE', 'TR', 'TD', 'TH', 'IMG', 'A', 'CENTER', 'LEFT', 'RIGHT', 'JUSTIFY'];

		this.HTML_ENTITIES = ['&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;','&circ;','&tilde;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&bull;','&hellip;','&prime;','&Prime;','&oline;','&frasl;','&trade;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&part;','&sum;','&minus;','&radic;','&infin;','&int;','&asymp;','&ne;','&equiv;','&le;','&ge;','&loz;','&spades;','&clubs;','&hearts;'];

		if(!BX.browser.IsIE())
		{
			this.HTML_ENTITIES = this.HTML_ENTITIES.concat(['&thetasym;','&upsih;','&piv;','&weierp;','&image;','&real;','&alefsym;','&crarr;','&lArr;','&uArr;','&rArr;','&dArr;','&hArr;','&forall;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&lowast;','&prop;','&ang;','&and;','&or;','&cap;','&cup;','&there4;','&sim;','&cong;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&diams;']);
		}

		this.SHORTCUTS = {
			"66": "bold", // B
			"73": "italic", // I
			"85": "underline" // U
		};

		this.KEY_CODES = {
			'backspace': 8,
			'enter': 13,
			'escape': 27,
			'space': 32,
			'delete': 46,
			'left': 37,
			'right': 39,
			'up': 38,
			'down': 40,
			'z': 90,
			'y': 89,
			'shift': 16,
			'ctrl': 17,
			'alt': 18,
			'cmd': 91, // 93, 224, 17 Browser dependent
			'cmdRight': 93, // 93, 224, 17 Browser dependent?
			'pageUp': 33,
			'pageDown': 34
		};
		this.INVISIBLE_SPACE = "\uFEFF";
		this.INVISIBLE_CURSOR = "\u2060";
		this.NORMAL_WIDTH = 1020;
		this.MIN_WIDTH = 700;
		this.MIN_HEIGHT = 100;

		this.MAX_HANDLED_FORMAT_LENGTH = 50000; // Max length of code which will be formated
		this.MAX_HANDLED_FORMAT_TIME = 500;
		this.iframeCssText = ''; // Here some controls can add additional css

		this.InitConfig(this.CheckConfig(config));

		if (!config.lazyLoad)
		{
			this.Init();
		}
	}

	BXEditor.prototype = {
		Init: function()
		{
			if (this.inited)
				return;

			// Parser
			this.parser = new BXHtmlEditor.BXEditorParser(this);

			this.On("OnEditorInitedBefore", [this]);

			if (!this.BuildSceleton())
				return;

			this.HTMLStyler = HTMLStyler;

			// Textarea
			this.dom.textarea = this.dom.textareaCont.appendChild(BX.create("TEXTAREA", {props: {className: "bxhtmled-textarea"}}));
			this.dom.pValueInput = BX('bxed_' + this.id);
			if (!this.dom.pValueInput)
			{
				this.dom.pValueInput = this.dom.cont.appendChild(BX.create("INPUT", {props: {type: "hidden", id: 'bxed_' + this.id, name: this.config.inputName}}));
			}
			this.dom.pValueInput.value = this.config.content;

			this.dom.form = this.dom.textarea.form || false;
			this.document = null;

			// Protected iframe for wysiwyg
			this.sandbox = this.CreateIframeSandBox();
			var iframe = this.sandbox.GetIframe();

			iframe.style.width = '100%';
			// TODO: height in pixels
			iframe.style.height = '100%';

			// Views:
			// 1. TextareaView
			this.textareaView = new BXEditorTextareaView(this, this.dom.textarea, this.dom.textareaCont);
			// 2. IframeView
			this.iframeView = new BXEditorIframeView(this, this.dom.textarea, this.dom.iframeCont);
			// 3. Syncronizer
			this.synchro = new BXEditorViewsSynchro(this, this.textareaView, this.iframeView);

			if (this.bbCode)
			{
				this.bbParser = new BXHtmlEditor.BXEditorBbCodeParser(this);
			}

			// Php parser
			this.phpParser = new BXHtmlEditor.BXEditorPhpParser(this);
			this.components = new BXHtmlEditor.BXEditorComponents(this);

			this.styles = new BXStyles(this);

			// Toolbar
			this.overlay = new BXHtmlEditor.Overlay(this);
			this.BuildToolbar();

			// Taskbars
			if (this.showTaskbars)
			{
				this.taskbarManager = new BXHtmlEditor.TaskbarManager(this, true);

				// Components
				if (this.showComponents)
				{
					this.componentsTaskbar = new BXHtmlEditor.ComponentsControl(this);
					this.taskbarManager.AddTaskbar(this.componentsTaskbar);
				}
				// Snippets
				if (this.showSnippets)
				{
					this.snippets = new BXHtmlEditor.BXEditorSnippets(this);
					this.snippetsTaskbar = new BXHtmlEditor.SnippetsControl(this, this.taskbarManager);
					this.taskbarManager.AddTaskbar(this.snippetsTaskbar);
				}
				this.taskbarManager.ShowTaskbar(this.showComponents ? this.componentsTaskbar.GetId() : this.snippetsTaskbar.GetId());
			}
			else
			{
				this.dom.taskbarCont.style.display = 'none';
			}
			// Context menu
			this.contextMenu = new BXHtmlEditor.ContextMenu(this);

			if (this.config.showNodeNavi)
			{
				this.nodeNavi = new BXHtmlEditor.NodeNavigator(this);
				this.nodeNavi.Show();
			}

			this.pasteControl = new BXHtmlEditor.PasteControl(this);

			this.InitEventHandlers();
			this.ResizeSceleton();

			// Restore taskbar mode from user settings
			if (this.showTaskbars && this.config.taskbarShown)
			{
				this.taskbarManager.Show(false);
			}
			this.inited = true;
			this.On("OnEditorInitedAfter", [this]);

			if (!this.CheckBrowserCompatibility())
			{
				this.dom.cont.parentNode.insertBefore(BX.create("DIV", {props: {className: "bxhtmled-warning"}, text: BX.message('BXEdInvalidBrowser')}), this.dom.cont);
			}

			this.InitImageUploader();

			this.Show();

			BX.onCustomEvent(BXHtmlEditor, 'OnEditorCreated', [this]);
		},

		InitConfig: function(config)
		{
			this.config = config;
			this.id = this.config.id;
			this.dialogs = {};
			this.bbCode = !!this.config.bbCode;
			this.config.splitVertical = !!this.config.splitVertical;
			this.config.splitRatio = parseFloat(this.config.splitRatio);
			this.config.view = this.config.view || 'wysiwyg';
			this.config.taskbarShown = !!this.config.taskbarShown;
			this.config.taskbarWidth = parseInt(this.config.taskbarWidth);
			this.config.showNodeNavi = this.config.showNodeNavi !== false;
			this.config.setFocusAfterShow = this.config.setFocusAfterShow !== false;
			this.cssCounter = 0;
			this.iframeCssText = this.config.iframeCss;
			this.fileDialogsLoaded = this.bbCode || this.config.useFileDialogs === false;

			if (this.config.bbCodeTags && this.bbCode)
			{
				this.BBCODE_TAGS = this.config.bbCodeTags;
			}

			if (this.config.minBodyWidth)
				this.MIN_WIDTH = parseInt(this.config.minBodyWidth);
			if (this.config.minBodyHeight)
				this.MIN_HEIGHT = parseInt(this.config.minBodyHeight);

			if (this.config.normalBodyWidth)
				this.NORMAL_WIDTH = parseInt(this.config.normalBodyWidth);
			this.normalWidth = this.NORMAL_WIDTH;

			if (!this.config.height)
				this.config.height = this.MIN_HEIGHT;
			if (!this.config.width)
				this.config.width = this.MIN_WIDTH;

			if (this.config.smiles)
			{
				this.smilesIndex = {};
				this.sortedSmiles = [];

				var i, smile, j, arCodes;
				for(i = 0; i < this.config.smiles.length; i++)
				{
					smile = this.config.smiles[i];
					if (!smile['codes'] || smile['codes'] == smile['code'])
					{
						this.smilesIndex[this.config.smiles[i].code] = smile;
						this.sortedSmiles.push(smile);
					}
					else if(smile['codes'].length > 0)
					{
						arCodes = smile['codes'].split(' ');
						for(j = 0; j < arCodes.length; j++)
						{
							this.smilesIndex[arCodes[j]] = smile;
							this.sortedSmiles.push({name: smile.name, path: smile.path, code: arCodes[j]});
						}
					}
				}

				this.sortedSmiles = this.sortedSmiles.sort(function(a, b){return b.code.length - a.code.length;});
			}

			this.allowPhp = !!this.config.allowPhp;
			// Limited Php Access - when user can only move or delete php code or change component params
			this.lpa = !this.config.allowPhp && this.config.limitPhpAccess;
			this.templateId = this.config.templateId;
			this.componentFilter = this.config.componentFilter;
			this.showSnippets = this.config.showSnippets !== false;
			this.showComponents = this.config.showComponents !== false && (this.allowPhp || this.lpa);
			this.showTaskbars = this.config.showTaskbars !== false && (this.showSnippets || this.showComponents);

			this.templates = {};
			this.templates[this.templateId] = this.config.templateParams;
		},

		InitEventHandlers: function()
		{
			var _this = this;
			BX.bind(this.dom.cont, 'click', BX.proxy(this.OnClick, this));
			BX.bind(this.dom.cont, 'mousedown', BX.proxy(this.OnMousedown, this));

			BX.bind(window, 'resize', function(){_this.ResizeSceleton();});
			if (BX.adminMenu)
			{
				BX.addCustomEvent(BX.adminMenu, 'onAdminMenuResize', function(){_this.ResizeSceleton();});
			}

			BX.addCustomEvent(this, "OnIframeFocus", function()
			{
				_this.bookmark = null;
				if (_this.statusInterval)
				{
					clearInterval(_this.statusInterval);
				}
				_this.statusInterval = setInterval(BX.proxy(_this.CheckCurrentStatus, _this), 500);
			});
			BX.addCustomEvent(this, "OnIframeBlur", function()
			{
				_this.bookmark = null;
				if (_this.statusInterval)
				{
					clearInterval(_this.statusInterval);
				}
				_this.iframeView.stopBugusScroll = false;
			});
			BX.addCustomEvent(this, "OnTextareaFocus", function()
			{
				_this.bookmark = null;
				if (_this.statusInterval)
				{
					clearInterval(_this.statusInterval);
				}
			});

			// Surrogates
			BX.addCustomEvent(this, "OnSurrogateDblClick", function(bxTag, origTag, target, e)
			{
				if (origTag)
				{
					switch (origTag.tag)
					{
						case 'php':
						case 'javascript':
						case 'htmlcomment':
						case 'iframe':
						case 'style':
							_this.GetDialog('Source').Show(origTag);
							break;
					}
				}
			});

			if (this.dom.form)
			{
				BX.bind(this.dom.form, 'submit', BX.proxy(this.OnSubmit, this));

				// Autosave
				if (this.config.initAutosave !== false)
				{
					setTimeout(function()
					{
						if (_this.dom.form.BXAUTOSAVE)
						{
							_this.InitAutosaveHandlers();
						}
					}, 100);
				}
			}

			BX.addCustomEvent(this, "OnSpecialcharInserted", function(entity)
			{
				var
					lastChars = _this.GetLastSpecialchars(),
					exInd = BX.util.array_search(entity, lastChars);

				if (exInd !== -1)
				{
					lastChars = BX.util.deleteFromArray(lastChars, exInd);
					lastChars.unshift(entity);
				}
				else
				{
					lastChars.unshift(entity);
					lastChars.pop();
				}

				_this.config.lastSpecialchars = lastChars;
				_this.SaveOption('specialchars', lastChars.join('|'));
			});

			this.parentDialog = BX.WindowManager.Get();
			if (this.parentDialog && this.parentDialog.DIV && BX.isNodeInDom(this.parentDialog.DIV)
				&&
				BX.findParent(this.dom.cont, function(n){return n == _this.parentDialog.DIV;}))
			{
				BX.addCustomEvent(this.parentDialog, 'onWindowResizeExt', function(){_this.ResizeSceleton();});
			}

			if (this.config.autoResize)
			{
				BX.addCustomEvent(this, "OnIframeKeyup", BX.proxy(this.AutoResizeSceleton, this));
				BX.addCustomEvent(this, "OnInsertHtml", BX.proxy(this.AutoResizeSceleton, this));
				BX.addCustomEvent(this, "OnIframeSetValue", BX.proxy(this.AutoResizeSceleton, this));
				BX.addCustomEvent(this, "OnFocus", BX.proxy(this.AutoResizeSceleton, this));
			}

			BX.addCustomEvent(this, "OnIframeKeyup", BX.proxy(this.CheckBodyHeight, this));
		},

		BuildSceleton: function()
		{
			var result = false;
			// Main container contain all editor parts
			this.dom.cont = BX('bx-html-editor-' + this.id);
			if (this.dom.cont && BX.isNodeInDom(this.dom.cont))
			{
				this.dom.toolbarCont = BX('bx-html-editor-tlbr-cnt-' + this.id);
				this.dom.toolbar = BX('bx-html-editor-tlbr-' + this.id);
				this.dom.areaCont = BX('bx-html-editor-area-cnt-' + this.id);

				// Container for content editable iframe
				this.dom.iframeCont = BX('bx-html-editor-iframe-cnt-' + this.id);
				this.dom.textareaCont = BX('bx-html-editor-ta-cnt-' + this.id);

				this.dom.resizerOverlay = BX('bx-html-editor-res-over-' + this.id);
				this.dom.splitResizer = BX('bx-html-editor-split-resizer-' + this.id);
				this.dom.splitResizer.style.display = 'none';
				this.dom.splitResizer.className = this.config.splitVertical ? "bxhtmled-split-resizer-ver" : "bxhtmled-split-resizer-hor";
				BX.bind(this.dom.splitResizer, 'mousedown', BX.proxy(this.StartSplitResize, this));

				// Taskbars
				this.dom.taskbarCont = BX('bx-html-editor-tskbr-cnt-' + this.id);

				// Node navigation at the bottom
				this.dom.navCont = BX('bx-html-editor-nav-cnt-' + this.id);

				this.dom.fileDialogsWrap = BX('bx-html-editor-file-dialogs-' + this.id)

				result = true;
			}
			return result;
		},

		ResizeSceleton: function(width, height, params)
		{
			var _this = this;
			if (this.expanded)
			{
				var innerSize = BX.GetWindowInnerSize(document);
				width = this.config.width = innerSize.innerWidth;
				height = this.config.height = innerSize.innerHeight;
			}

			if (!width)
			{
				width = this.config.width;
			}
			if (!height)
			{
				height = this.config.height;
			}

			this.dom.cont.style.minWidth = this.MIN_WIDTH + 'px';
			this.dom.cont.style.minHeight = this.MIN_HEIGHT + 'px';

			var styleW, styleH;

			if (this.resizeTimeout)
			{
				clearTimeout(this.resizeTimeout);
				this.resizeTimeout = null;
			}

			if (width.toString().indexOf('%') !== -1)
			{
				styleW = width;
				width = this.dom.cont.offsetWidth;

				if (!width)
				{
					this.resizeTimeout = setTimeout(function(){_this.ResizeSceleton(width, height, params);}, 500);
					return;
				}
			}
			else
			{
				if (width < this.MIN_WIDTH)
				{
					width = this.MIN_WIDTH;
				}
				styleW = width + 'px';
			}

			this.dom.cont.style.width = styleW;
			this.dom.toolbarCont.style.width = styleW;

			if (height.toString().indexOf('%') !== -1)
			{
				styleH = height;
				height = this.dom.cont.offsetHeight;
			}
			else
			{
				if (height < this.MIN_HEIGHT)
				{
					height = this.MIN_HEIGHT;
				}
				styleH = height + 'px';
			}
			this.dom.cont.style.height = styleH;

			var
				w = Math.max(width, this.MIN_WIDTH),
				h = Math.max(height, this.MIN_HEIGHT),
				toolbarHeight = this.toolbar.GetHeight(),
				taskbarWidth = this.showTaskbars ? (this.taskbarManager.GetWidth(true, w * 0.8)) : 0,
				areaH = h - toolbarHeight - (this.config.showNodeNavi && this.nodeNavi ? this.nodeNavi.GetHeight() : 0),
				areaW = w - taskbarWidth;

			this.dom.areaCont.style.top = toolbarHeight ? toolbarHeight + 'px' : 0;

			// Area
			this.SetAreaContSize(areaW, areaH, params);

			// Taskbars
			this.dom.taskbarCont.style.height = areaH + 'px';
			this.dom.taskbarCont.style.width = taskbarWidth + 'px';
			if (this.showTaskbars)
			{
				this.taskbarManager.Resize(taskbarWidth, areaH);
			}
			this.toolbar.AdaptControls(width);

			this.On('OnEditorResizedAfter', [{
				width: width,
				height: height
			}]);
		},

		CheckBodyHeight: function()
		{
			if (this.iframeView.IsShown())
			{
				var
					padding = 8,
					minHeight,
					doc = this.GetIframeDoc();

				if (doc && doc.body)
				{
					minHeight = doc.body.parentNode.offsetHeight - padding * 2;
					if (minHeight <= 20)
					{
						setTimeout(BX.proxy(this.CheckBodyHeight, this), 300);
					}
					else if (this.config.autoResize || minHeight > doc.body.offsetHeight)
					{
						doc.body.style.minHeight = minHeight + 'px';
					}
				}
			}
		},

		GetSceletonSize: function()
		{
			return {
				width: this.dom.cont.offsetWidth,
				height: this.dom.cont.offsetHeight
			};
		},

		AutoResizeSceleton: function()
		{
			if (this.expanded || !this.IsShown() || this.iframeView.IsEmpty())
				return;

			var
				maxHeight = parseInt(this.config.autoResizeMaxHeight || 0),
				minHeight = parseInt(this.config.autoResizeMinHeight || 50),
				areaHeight = this.dom.areaCont.offsetHeight,
				newHeight,
				_this = this;

			if (this.autoResizeTimeout)
			{
				clearTimeout(this.autoResizeTimeout);
			}

			this.autoResizeTimeout = setTimeout(function()
			{
				newHeight = _this.GetHeightByContent();
				if (newHeight > areaHeight)
				{
					if (BX.browser.IsIOS())
					{
						maxHeight = Infinity;
					}
					else if (!maxHeight || maxHeight < 10)
					{
						maxHeight = Math.round(BX.GetWindowInnerSize().innerHeight * 0.9); // 90% from screen height
					}

					newHeight = Math.min(newHeight, maxHeight);
					newHeight = Math.max(newHeight, minHeight);
					_this.SmoothResizeSceleton(newHeight);
				}
			}, 300);
		},

		GetHeightByContent: function()
		{
			var
				heightOffset = parseInt(this.config.autoResizeOffset || 80),
				contentHeight;

			if (this.GetViewMode() == 'wysiwyg')
			{
				var
					body = this.GetIframeDoc().body,
					node = body.lastChild,
					offsetTop = false;

				contentHeight = body.offsetHeight;
				while (true)
				{
					if (!node)
					{
						break;
					}
					if (node.offsetTop)
					{
						offsetTop = node.offsetTop + (node.offsetHeight || 0);
						contentHeight = offsetTop + heightOffset;
						break;
					}
					else
					{
						node = node.previousSibling;
					}
				}

				var oEdSize = BX.GetWindowSize(this.GetIframeDoc());
				if (oEdSize.scrollHeight - oEdSize.innerHeight > 5)
				{
					contentHeight = Math.max(oEdSize.scrollHeight + heightOffset, contentHeight);
				}
			}
			else
			{
				contentHeight = (this.textareaView.element.value.split("\n").length /* rows count*/ + 5) * 17;
			}

			return contentHeight;
		},

		SmoothResizeSceleton: function(height)
		{
			this.On('AutoResizeStarted');

			var
				_this = this,
				size = this.GetSceletonSize(),
				curHeight = size.height,
				count = 0,
				bRise = height > curHeight,
				timeInt = 50,
				dy = 5;

			if (!bRise)
				return;

			if (this.smoothResizeInt)
			{
				clearInterval(this.smoothResizeInt);
			}

			this.smoothResizeInt = setInterval(function()
				{
					curHeight += Math.round(dy * count);
					var finished = curHeight >= height;
					if (curHeight > height)
					{
						clearInterval(_this.smoothResizeInt);
						if (curHeight > height)
						{
							curHeight = height;
						}
					}
					_this.config.height = curHeight;
					_this.ResizeSceleton();
					if (finished)
					{
						_this.On('AutoResizeFinished');
					}
					count++;
				},
				timeInt
			);
		},

		SetAreaContSize: function(areaW, areaH, params)
		{
			areaW += 2;
			this.dom.areaCont.style.width = areaW + 'px';
			this.dom.areaCont.style.height = areaH + 'px';

			if (params && params.areaContTop)
			{
				this.dom.areaCont.style.top = params.areaContTop + 'px';
			}

			var WIDTH_DIF = 3;

			if (this.currentViewName == 'split')
			{
				function getValue(value, min, max)
				{
					if (value < min)
					{
						value = min;
					}
					if (value > max)
					{
						value = max;
					}
					return value;
				}

				var MIN_SPLITTER_PAD = 10, delta, a, b;

				if (this.config.splitVertical == true)
				{
					delta = params && params.deltaX ? params.deltaX : 0;
					a = getValue((areaW * this.config.splitRatio / (1 + this.config.splitRatio)) - delta, MIN_SPLITTER_PAD, areaW - MIN_SPLITTER_PAD);
					b = areaW - a;

					this.dom.iframeCont.style.width = (a - WIDTH_DIF) + 'px';
					this.dom.iframeCont.style.height = areaH + 'px';
					this.dom.iframeCont.style.top = 0;
					this.dom.iframeCont.style.left = 0;

					this.dom.textareaCont.style.width = (b - WIDTH_DIF) + 'px';
					this.dom.textareaCont.style.height = areaH + 'px';
					this.dom.textareaCont.style.top = 0;
					this.dom.textareaCont.style.left = a + 'px';

					this.dom.splitResizer.className = 'bxhtmled-split-resizer-ver';
					this.dom.splitResizer.style.top = 0;
					this.dom.splitResizer.style.left = (a - 3) + 'px';

					this.dom.textareaCont.style.height = areaH + 'px';
				}
				else
				{
					delta = params && params.deltaY ? params.deltaY : 0;
					a = getValue((areaH * this.config.splitRatio / (1 + this.config.splitRatio)) - delta, MIN_SPLITTER_PAD, areaH - MIN_SPLITTER_PAD);
					b = areaH - a;

					this.dom.iframeCont.style.width = (areaW - WIDTH_DIF) + 'px';
					this.dom.iframeCont.style.height = a + 'px';
					this.dom.iframeCont.style.top = 0;
					this.dom.iframeCont.style.left = 0;

					this.dom.textareaCont.style.width = (areaW - WIDTH_DIF) + 'px';
					this.dom.textareaCont.style.height = b + 'px';
					this.dom.textareaCont.style.top = a + 'px';
					this.dom.textareaCont.style.left = 0;

					this.dom.splitResizer.className = 'bxhtmled-split-resizer-hor';
					this.dom.splitResizer.style.top = (a - 3) + 'px';
					this.dom.splitResizer.style.left = 0;
				}

				if (params && params.updateSplitRatio)
				{
					this.config.splitRatio = a / b;
					this.SaveOption('split_ratio', this.config.splitRatio);
				}
			}
			else
			{
				// Set size and position of iframe container to the normal state
				this.dom.iframeCont.style.width = (areaW - WIDTH_DIF) + 'px';
				this.dom.iframeCont.style.height = areaH + 'px';
				this.dom.iframeCont.style.top = 0;
				this.dom.iframeCont.style.left = 0;

				// Set size and position of textarea container to the normal state
				this.dom.textareaCont.style.width = (areaW - WIDTH_DIF) + 'px';
				this.dom.textareaCont.style.height = areaH + 'px';
				this.dom.textareaCont.style.top = 0;
				this.dom.textareaCont.style.left = 0;
			}
		},

		BuildToolbar: function()
		{
			this.toolbar = new BXHtmlEditor.Toolbar(this, this.GetTopControls());
		},

		GetTopControls: function()
		{
			this.On("GetTopButtons", [window.BXHtmlEditor.Controls]);
			return window.BXHtmlEditor.Controls;
		},

		CreateIframeSandBox: function()
		{
			return new Sandbox(
				// Callback
				BX.proxy(this.OnCreateIframe, this),
				// Config
				{
					editor: this,
					cont: this.dom.iframeCont
				}
			);
		},

		OnCreateIframe: function()
		{
			if (!document.body.contains(this.dom.iframeCont))
			{
				//do not create frame if DOM doesn't contain editor's html structure (autocomposite).
				return;
			}

			this.On('OnCreateIframeBefore');
			this.iframeView.OnCreateIframe();
			this.selection = new BXEditorSelection(this);
			this.action = new BXEditorActions(this);
			this.config.content = this.dom.pValueInput.value;
			this.SetContent(this.config.content, true);
			this.undoManager = new BXEditorUndoManager(this);
			this.action.Exec("styleWithCSS", false, true);
			this.iframeView.InitAutoLinking();
			// Simulate html5 placeholder attribute on contentEditable element
//			var placeholderText = typeof(this.config.placeholder) === "string"
//				? this.config.placeholder
//				: this.textarea.element.getAttribute("placeholder");
//			if (placeholderText) {
//				dom.simulatePlaceholder(this.parent, this, placeholderText);
//			}

			if (this.config.view != 'wysiwyg')
			{
				var i, changeViewBut = false, switchCodeButton = false, controls = this.toolbar.GetControlsMap();

				// Mantis: 72063
				if (this.config.view == 'split')
				{
					for (i = 0; i < controls.length; i++)
					{
						if (controls[i] && controls[i].id == 'ChangeView')
						{
							changeViewBut = true;
							break;
						}
					}
					if (!changeViewBut)
						this.config.view = 'wysiwyg';
				}

				// Mantis: 80663
				if (this.config.view != 'wysiwyg')
				{
					for (i = 0; i < controls.length; i++)
					{
						if (controls[i] && (controls[i].id == 'BbCode' || controls[i].id == 'ChangeView'))
						{
							switchCodeButton = true;
							break;
						}
					}
					if (!switchCodeButton)
						this.config.view = 'wysiwyg';
				}
			}

			this.SetView(this.config.view, false);
			if (this.config.setFocusAfterShow !== false)
			{
				this.Focus(false);
			}

			this.sandbox.inited = true;
			this.On('OnCreateIframeAfter', [this]);
		},

		GetDialog: function(dialogName, params)
		{
			if (!this.dialogs[dialogName] && window.BXHtmlEditor.dialogs[dialogName])
				this.dialogs[dialogName] = new window.BXHtmlEditor.dialogs[dialogName](this, params);

			return this.dialogs[dialogName] || null;
		},

		Show: function()
		{
			this.dom.cont.style.display = '';
		},

		Hide: function()
		{
			this.dom.cont.style.display = 'none';
		},

		IsShown: function()
		{
			return this.inited && this.dom.cont.style.display !== 'none' && this.dom.cont.offsetWidth > 0 && BX.isNodeInDom(this.dom.cont);
		},

		SetView: function(view, saveValue)
		{
			this.On('OnSetViewBefore');
			if (view == 'split' && this.bbCode)
				view = 'wysiwyg';

			if (this.currentViewName != view)
			{
				if (view == 'wysiwyg')
				{
					this.iframeView.Show();
					this.textareaView.Hide();
					this.dom.splitResizer.style.display = 'none';
					this.CheckBodyHeight();
				}
				else if (view == 'code')
				{
					this.iframeView.Hide();
					this.textareaView.Show();
					this.CheckCurrentStatus(false);
					this.dom.splitResizer.style.display = 'none';
				}
				else if (view == 'split')
				{
					this.textareaView.Show();
					this.iframeView.Show();
					this.dom.splitResizer.style.display = '';
					this.CheckBodyHeight();
				}

				this.currentViewName = view;
			}

			if (saveValue !== false)
			{
				this.SaveOption('view', view);
			}

			this.ResizeSceleton();
			this.On('OnSetViewAfter');
		},

		GetViewMode: function()
		{
			return this.currentViewName;
		},

		SetContent: function(value, bParse)
		{
			this.On('OnSetContentBefore');
			if (this.bbCode)
			{
				var htmlFromBbCode = this.bbParser.Parse(value);
				this.iframeView.SetValue(htmlFromBbCode, bParse);
			}
			else
			{
				this.iframeView.SetValue(value, bParse);
			}

			this.textareaView.SetValue(value, false);

			this.On('OnSetContentAfter');
		},

		Focus: function(setToEnd)
		{
			if (this.currentViewName == 'wysiwyg')
			{
				this.iframeView.Focus(setToEnd);
			}
			else if (this.currentViewName == 'code')
			{
				this.textareaView.Focus(setToEnd);
			}
			else if (this.currentViewName == 'split')
			{
				if (this.synchro.GetSplitMode() == 'wysiwyg')
				{
					this.iframeView.Focus(setToEnd);
				}
				else
				{
					this.textareaView.Focus(setToEnd);
				}
			}
			this.On('OnFocus');
			return this;
		},

		SaveContent: function()
		{
			if (this.currentViewName == 'wysiwyg' ||
				(this.currentViewName == 'split' && this.synchro.GetSplitMode() == 'wysiwyg'))
			{
				this.synchro.lastIframeValue = '';
				this.synchro.FromIframeToTextarea(true, true);
			}
			else
			{
				this.textareaView.SaveValue();
			}
		},

		GetContent: function()
		{
			this.SaveContent();
			return this.textareaView.GetValue();
		},

		IsExpanded: function()
		{
			return this.expanded;
		},

		Expand: function(bExpand)
		{
			if (bExpand == undefined)
			{
				bExpand = !this.expanded;
			}

			var
				_this = this,
				innerSize = BX.GetWindowInnerSize(document),
				startWidth, startHeight, startTop, startLeft,
				endWidth, endHeight, endTop, endLeft;

			if (bExpand)
			{
				var
					scrollPos = BX.GetWindowScrollPos(document),
					pos = BX.pos(this.dom.cont);

				startWidth = this.dom.cont.offsetWidth;
				startHeight = this.dom.cont.offsetHeight;
				startTop = pos.top;
				startLeft = pos.left;
				endWidth = innerSize.innerWidth;
				endHeight = innerSize.innerHeight;
				endTop = scrollPos.scrollTop;
				endLeft = scrollPos.scrollLeft;

				this.savedSize = {
					width: startWidth,
					height: startHeight,
					top: startTop,
					left: startLeft,
					scrollLeft: scrollPos.scrollLeft,
					scrollTop: scrollPos.scrollTop,
					configWidth: this.config.width,
					configHeight: this.config.height
				};

				this.config.width = endWidth;
				this.config.height = endHeight;

				BX.addClass(this.dom.cont, 'bx-html-editor-absolute');
				this._bodyOverflow = document.body.style.overflow;
				document.body.style.overflow = "hidden";

				// Create dummie div
				this.dummieDiv = BX.create('DIV');
				this.dummieDiv.style.width = startWidth + 'px';
				this.dummieDiv.style.height = startHeight + 'px';
				this.dom.cont.parentNode.insertBefore(this.dummieDiv, this.dom.cont);
				document.body.appendChild(this.dom.cont);

				BX.addCustomEvent(this, 'OnIframeKeydown', BX.proxy(this.CheckEscCollapse, this));
				BX.bind(document.body, "keydown", BX.proxy(this.CheckEscCollapse, this));
				BX.bind(window, "scroll", BX.proxy(this.PreventScroll, this));
			}
			else
			{
				startWidth = this.dom.cont.offsetWidth;
				startHeight = this.dom.cont.offsetHeight;
				startTop = this.savedSize.scrollTop;
				startLeft = this.savedSize.scrollLeft;
				endWidth = this.savedSize.width;
				endHeight = this.savedSize.height;
				endTop = this.savedSize.top;
				endLeft = this.savedSize.left;

				BX.removeCustomEvent(this, 'OnIframeKeydown', BX.proxy(this.CheckEscCollapse, this));
				BX.unbind(document.body, "keydown", BX.proxy(this.CheckEscCollapse, this));
				BX.unbind(window, "scroll", BX.proxy(this.PreventScroll, this));
			}

			this.dom.cont.style.width = startWidth + 'px';
			this.dom.cont.style.height = startHeight + 'px';
			this.dom.cont.style.top = startTop + 'px';
			this.dom.cont.style.left = startLeft + 'px';

			var content = this.GetContent();

			this.expandAnimation = new BX.easing({
				duration : 300,
				start : {
					height: startHeight,
					width: startWidth,
					top: startTop,
					left: startLeft
				},
				finish : {
					height: endHeight,
					width: endWidth,
					top: endTop,
					left: endLeft
				},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step : function(state)
				{
					_this.dom.cont.style.width = state.width + 'px';
					_this.dom.cont.style.height = state.height + 'px';
					_this.dom.cont.style.top = state.top + 'px';
					_this.dom.cont.style.left = state.left + 'px';
					_this.ResizeSceleton(state.width.toString(), state.height.toString());
				},
				complete : function()
				{
					_this.expandAnimation = null;
					if (!bExpand)
					{
						_this.util.ReplaceNode(_this.dummieDiv, _this.dom.cont);
						_this.dummieDiv = null;
						_this.dom.cont.style.width = '';
						_this.dom.cont.style.height = '';
						_this.dom.cont.style.top = '';
						_this.dom.cont.style.left = '';
						BX.removeClass(_this.dom.cont, 'bx-html-editor-absolute');
						document.body.style.overflow = _this._bodyOverflow;
						_this.config.width = _this.savedSize.configWidth;
						_this.config.height = _this.savedSize.configHeight;
						_this.ResizeSceleton();
					}
					setTimeout(function(){_this.CheckAndReInit(content)}, 10);
				}
			});

			this.expandAnimation.animate();
			this.expanded = bExpand;
		},

		CheckEscCollapse: function(e, keyCode, command, selectedNode)
		{
			if (!keyCode)
			{
				keyCode = e.keyCode;
			}

			if (
				this.IsExpanded() &&
					keyCode == this.KEY_CODES['escape'] &&
					!this.IsPopupsOpened()
				)
			{
				this.Expand(false);
				return BX.PreventDefault(e);
			}
		},

		PreventScroll: function(e)
		{
			window.scrollTo(this.savedSize.scrollLeft, this.savedSize.scrollTop);
			return BX.PreventDefault(e);
		},

		IsPopupsOpened: function()
		{
			return !!(this.dialogShown ||
					this.popupShown ||
					this.contextMenuShown ||
					this.overlay.bShown);
		},

		ReInitIframe: function()
		{
			this.sandbox.InitIframe();
			this.iframeView.OnCreateIframe();
			this.synchro.StopSync();
			this.synchro.lastTextareaValue = '';
			this.synchro.FromTextareaToIframe(true);
			this.synchro.StartSync();
			this.iframeView.ReInit();
			this.selection.lastCheckedRange = null;
			if (this.config.setFocusAfterShow !== false)
			{
				this.Focus();
			}
		},

		CheckAndReInit: function(content)
		{
			if (this.sandbox.inited)
			{
				var win = this.sandbox.GetWindow();
				if (win)
				{
					var doc = this.sandbox.GetDocument();
					if (doc !== this.iframeView.document || !doc.head || doc.head.innerHTML == '')
					{
						this.iframeView.document = doc;
						this.iframeView.element = doc.body;
						this.ReInitIframe();
					}
					else if(doc.body)
					{
						doc.body.style.minHeight = '';
					}
				}
				else
				{
					throw new Error("HtmlEditor: CheckAndReInit error iframe isn't in the DOM");
				}
			}

			if (content !== undefined)
			{
				this.SetContent(content, true);
				this.CheckBodyHeight();
			}
		},

		Disable: function()
		{
		},

		Enable: function()
		{
		},

		CheckConfig: function(config)
		{
			if (config.content === undefined)
			{
				config.content = '';
			}

			return config;
		},

		GetInnerHtml: function(el)
		{
			var
				TILDA = "%7E",
				AMP = "&amp;",
				innerHTML = el.innerHTML;

			if (innerHTML.indexOf(AMP) !== -1 || innerHTML.indexOf(TILDA) !== -1)
			{
				innerHTML = innerHTML.replace(/(?:href|src)\s*=\s*("|')([\s\S]*?)(\1)/ig, function(s)
				{
					// See https://bugzilla.mozilla.org/show_bug.cgi?id=664398
					s = s.replace(/%7E/ig, '~');
					s = s.replace(/&amp;/ig, '&');
					return s;
				});
			}

			innerHTML = innerHTML.replace(/(?:title|alt)\s*=\s*("|')([\s\S]*?)(\1)/ig, function(s)
			{
				s = s.replace(/</g, '&lt;');
				s = s.replace(/>/g, '&gt;');
				return s;
			});

			if (this.bbCode)
			{
				innerHTML = innerHTML.replace(/[\s\n\r]*?<!--[\s\S]*?-->[\s\n\r]*?/ig, "");
			}

			return innerHTML;
		},

		InitUtil: function()
		{
			var _this = this;

			this.util = {};
			if ("textContent" in document.documentElement)
			{
				this.util.SetTextContent = function(element, text){element.textContent = text;};
				this.util.GetTextContent = function(element){return element.textContent;};
			}
			else if ("innerText" in document.documentElement)
			{
				this.util.SetTextContent = function(element, text){element.innerText = text;};
				this.util.GetTextContent = function(element){return element.innerText;};
			}
			else
			{
				this.util.SetTextContent = function(element, text){element.nodeValue = text;};
				this.util.GetTextContent = function(element){return element.nodeValue;};
			}

			this.util.AutoCloseTagSupported = function()
			{
				var
					element = document.createElement("div"),
					result,
					innerHTML;

				element.innerHTML = "<p><div></div>";
				innerHTML = element.innerHTML.toLowerCase();
				result = innerHTML === "<p></p><div></div>" || innerHTML === "<p><div></div></p>";

				_this.util.AutoCloseTagSupported = function(){return result;};
				return result;
			};

			this.util.FirstLetterSupported = function()
			{
				var result = !BX.browser.IsChrome() && !BX.browser.IsSafari();
				_this.util.FirstLetterSupported = function(){return result;};
				return result;
			};

			// IE sometimes gives wrong results for hasAttribute/getAttribute
			this.util.CheckGetAttributeTruth = function()
			{
				var
					td = document.createElement("td"),
					result = td.getAttribute("rowspan") != "1";

				_this.util.CheckGetAttributeTruth = function(){return result;};
				return result;
			};

			// Check if browser supports HTML5
			this.util.CheckHTML5Support = function(doc)
			{
				if (!doc)
				{
					doc = document;
				}

				var
					result = false,
					html = "<article>bitrix</article>",
					el = doc.createElement("div");

				el.innerHTML = html;
				result = el.innerHTML.toLowerCase() === html;

				_this.util.CheckHTML5Support = function(){return result;};
				return result;
			};

			// Check if browser supports HTML5 (checks all tags)
			this.util.CheckHTML5FullSupport = function(doc)
			{
				if (!doc)
				{
					doc = document;
				}

				var
					html,
					tags = _this.GetHTML5Tags(),
					result = false,
					el = doc.createElement("div");

				for (var i = 0; i < tags.length; i++)
				{
					html = "<" + tags[i] + ">bitrix</" + tags[i] + ">";
					el.innerHTML = html;
					result = el.innerHTML.toLowerCase() === html;
					if (!result)
					{
						break;
					}
				}

				_this.util.CheckHTML5FullSupport = function(){return result;};
				return result;
			};

			this.util.GetEmptyImage = function()
			{
				return _this.EMPTY_IMAGE_SRC;
			};

			this.util.CheckDataTransferSupport = function()
			{
				var result = false;
				try {
					result = !!(window.Clipboard || window.DataTransfer).prototype.getData;
				} catch(e) {}
				_this.util.CheckDataTransferSupport = function(){return result;};
				return result;
			};

			this.util.CheckImageSelectSupport = function()
			{
				var result = !(BX.browser.IsChrome() || BX.browser.IsSafari());
				_this.util.CheckImageSelectSupport = function(){return result;};
				return result;
			};

			this.util.CheckPreCursorSupport = function()
			{
				var result = !(BX.browser.IsIE() || BX.browser.IsIE10() || BX.browser.IsIE11());
				_this.util.CheckPreCursorSupport = function(){return result;};
				return result;
			};

			// Following hack is needed for firefox to make sure that image resize handles are properly removed
			this.util.Refresh = function(element)
			{
				if (element && element.parentNode)
				{
					var cn = "bx-editor-refresh";

					BX.addClass(element, cn);
					BX.removeClass(element, cn);

					// Hack for firefox
					if (BX.browser.IsFirefox())
					{
						try {
							var
								i,
								doc = element.ownerDocument,
								italics = doc.getElementsByTagName('I'),
								italicLen = italics.length;

							for (i = 0; i < italics.length; i++)
							{
								italics[i].setAttribute('data-bx-orgig-i', true);
							}

							doc.execCommand("italic", false, null);
							doc.execCommand("italic", false, null);

							var italicsNew = doc.getElementsByTagName('I');
							if (italicsNew.length !== italicLen)
							{
								for (i = 0; i < italicsNew.length; i++)
								{
									if (italicsNew[i].getAttribute('data-bx-orgig-i'))
									{
										italicsNew[i].removeAttribute('data-bx-orgig-i');
									}
									else
									{
										_this.util.ReplaceWithOwnChildren(italicsNew[i]);
									}
								}
							}
						} catch(e) {}
					}
				}
			};

			this.util.addslashes = function(str)
			{
				str = str.replace(/\\/g,'\\\\');
				str = str.replace(/"/g,'\\"');
				return str;
			};

			this.util.stripslashes = function(str)
			{
				str = str.replace(/\\"/g,'"');
				str = str.replace(/\\\\/g,'\\');
				return str;
			};

			this.util.ReplaceNode = function(node, newNode)
			{
				node.parentNode.insertBefore(newNode, node);
				node.parentNode.removeChild(node);
				return newNode;
			};

			this.util.spaceUrlEncode = function(str)
			{
				str = str.replace(/ /g,'%20');
				return str;
			};

			this.util.spaceUrlDecode = function(str)
			{
				str = str.replace(/%20/g,' ');
				return str;
			};

			// Fast way to check whether an element with a specific tag name is in the given document
			this.util.DocumentHasTag = function(doc, tag)
			{
				var
					LIVE_CACHE = {},
					key = _this.id + ":" + tag,
					cacheEntry = LIVE_CACHE[key];

				if (!cacheEntry)
					cacheEntry = LIVE_CACHE[key] = doc.getElementsByTagName(tag);

				return cacheEntry.length > 0;
			};

			this.util.IsSplitPoint = function(node, offset)
			{
				var res = offset > 0 && offset < node.childNodes.length;
				if (rangy.dom.isCharacterDataNode(node))
				{
					if (offset == 0)
						res = !!node.previousSibling;
					else if (offset == node.length)
						res = !!node.nextSibling;
					else
						res = true;
				}
				return res;
			};

			this.util.SplitNodeAt = function(node, descendantNode, descendantOffset)
			{
				var newNode;
				if (rangy.dom.isCharacterDataNode(descendantNode))
				{
					if (descendantOffset == 0)
					{
						descendantOffset = rangy.dom.getNodeIndex(descendantNode);
						descendantNode = descendantNode.parentNode;
					}
					else if (descendantOffset == descendantNode.length)
					{
						descendantOffset = rangy.dom.getNodeIndex(descendantNode) + 1;
						descendantNode = descendantNode.parentNode;
					}
					else
					{
						newNode = rangy.dom.splitDataNode(descendantNode, descendantOffset);
					}
				}

				if (!newNode)
				{
					newNode = descendantNode.cloneNode(false);
					if (newNode.id)
					{
						newNode.removeAttribute("id");
					}

					var child;
					while ((child = descendantNode.childNodes[descendantOffset]))
					{
						newNode.appendChild(child);
					}

					rangy.dom.insertAfter(newNode, descendantNode);
				}

				if (descendantNode && descendantNode.nodeName == "BODY")
				{
					return newNode;
				}

				return (descendantNode == node) ? newNode : _this.util.SplitNodeAt(node, newNode.parentNode, rangy.dom.getNodeIndex(newNode));
			};

			this.util.ReplaceWithOwnChildren = function (el)
			{
				var parent = el.parentNode;
				while (el.firstChild)
				{
					parent.insertBefore(el.firstChild, el);
				}
				parent.removeChild(el);
			};

			this.util.IsBlockElement = function (node)
			{
				var styleDisplay = BX.style(node, 'display');
				return styleDisplay && styleDisplay.toLowerCase() === "block";
			};

			this.util.IsBlockNode = function (node)
			{
				return node && node.nodeType == 1 && BX.util.in_array(node.nodeName, _this.GetBlockTags());
			};

			this.util.CopyAttributes = function(attributes, from, to)
			{
				if (from && to)
				{
					var
						attribute,
						i,
						length = attributes.length;

					for (i = 0; i < length; i++)
					{
						attribute = attributes[i];
						if (from[attribute])
							to[attribute] = from[attribute];
					}
				}
			};

			this.util.RenameNode = function(node, newNodeName)
			{
				var
					newNode = node.ownerDocument.createElement(newNodeName),
					firstChild;

				while (firstChild = node.firstChild)
					newNode.appendChild(firstChild);

				_this.util.CopyAttributes(["align", "className"], node, newNode);

				if (node.style.cssText != '')
				{
					newNode.style.cssText = node.style.cssText;
				}

				node.parentNode.replaceChild(newNode, node);
				return newNode;
			};

			this.util.GetInvisibleTextNode = function()
			{
				return _this.iframeView.document.createTextNode(_this.INVISIBLE_SPACE);
			};

			this.util.IsEmptyNode = function(node, bCheckNewLine, bCheckSpaces)
			{
				var res;
				if (node.nodeType == 3)
				{
					res = node.data === "" || node.data === _this.INVISIBLE_SPACE || (node.data === '\n' && bCheckNewLine);
					if (!res && bCheckSpaces && node.data.toString().match(/^[\s\n\r\t]+$/ig))
					{
						res = true;
					}
				}
				else if(node.nodeType == 1)
				{
					res = node.innerHTML === "" || node.innerHTML === _this.INVISIBLE_SPACE;
					if (!res && bCheckSpaces && node.innerHTML.toString().match(/^[\s\n\r\t]+$/ig))
					{
						res = true;
					}
				}
				return res;
			};

			var documentElement = document.documentElement;
			if ("textContent" in documentElement)
			{
				this.util.SetTextContent = function(node, text)
				{
					node.textContent = text;
				};

				this.util.GetTextContent = function(node)
				{
					return node.textContent;
				};
			}
			else if ("innerText" in documentElement)
			{
				this.util.SetTextContent = function(node, text)
				{
					node.innerText = text;
				};

				this.util.GetTextContent = function(node)
				{
					return node.innerText;
				};
			}
			else
			{
				this.util.SetTextContent = function(node, text)
				{
					node.nodeValue = text;
				};

				this.util.GetTextContent = function(node)
				{
					return node.nodeValue;
				};
			}

			this.util.GetTextContentEx = function(node)
			{
				var
					i, html, linkMap = [],
					clone = node.cloneNode(true),
					scripts = clone.getElementsByTagName('SCRIPT'),
					links = clone.getElementsByTagName('A');

				for (i = scripts.length - 1; i >= 0 ; i--)
				{
					BX.remove(scripts[i]);
				}

				// mantis:64329, mantis:70550
				for (i = links.length - 1; i >= 0 ; i--)
				{
					var href = links[i].href;
					if (href.toLowerCase().indexOf('javascript:') !== -1)
					{
						_this.util.ReplaceNode(links[i], links[i].ownerDocument.createTextNode(_this.util.GetTextContent(links[i])));
					}
					else
					{
						linkMap.push('<a href="' + links[i].href + '">' + _this.util.GetTextContent(links[i]) + '</a>');
						_this.util.ReplaceNode(links[i], links[i].ownerDocument.createTextNode('#BX~TMP~LINK' + (linkMap.length - 1) + '#'));
					}
				}

				html = _this.util.GetTextContent(clone);

				if (linkMap.length > 0)
				{
					html = html.replace(/#BX~TMP~LINK(\d+)#/ig, function(s, num)
					{
						return linkMap[num] || '';
					});
				}
				return html;
			};

			this.util.RgbToHex = function(str)
			{
				if (!str)
					str = '';

				if (str.search("rgb") !== -1)
				{
					function hex(x)
					{
						return ("0" + parseInt(x).toString(16)).slice(-2);
					}

					str = str.replace(/rgba\(0,\s*0,\s*0,\s*0\)/ig, 'transparent');
					str = str.replace(/rgba?\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})(?:,\s*([\d\.]{1,3}))?\)/ig,
						function(s, s1, s2, s3, s4)
						{
							return "#" + hex(s1) + hex(s2) + hex(s3);
						}
					);
				}

				return str;
			};

			this.util.CheckCss = function(node, arCss, bMatch)
			{
				var res = true, i;
				for (i in arCss)
				{
					if (arCss.hasOwnProperty(i))
					{
						if (node.style[i] != '')
						{
							res = res && (bMatch ? node.style[i] == arCss[i] : true);
						}
						else
						{
							res = false;
						}
					}
				}
				return res;
			};

			this.util.SetCss = function(n, arCss)
			{
				if (n && arCss && typeof arCss == 'object')
				{
					for (var i in arCss)
					{
						if (arCss.hasOwnProperty(i))
						{
							n.style[i] = arCss[i];
						}
					}
				}
			};

			this.util.InsertAfter = function(node, precedingNode)
			{
				return rangy.dom.insertAfter(node, precedingNode);
			};

			this.util.GetNodeDomOffset = function(node)
			{
				var i = 0;
				while (node.parentNode && node.parentNode.nodeName !== 'BODY')
				{
					node = node.parentNode;
					i++;
				}
				return i;
			};

			this.util.CheckSurrogateNode = function(node)
			{
				return _this.phpParser.CheckParentSurrogate(node);
			};

			this.util.CheckSurrogateDd = function(node)
			{
				return _this.phpParser.CheckSurrogateDd(node);
			};

			this.util.GetPreviousNotEmptySibling = function(node)
			{
				var prev = node.previousSibling;
				while (prev && prev.nodeType == 3 && _this.util.IsEmptyNode(prev, true, true))
				{
					prev = prev.previousSibling;
				}
				return prev;
			};

			this.util.GetNextNotEmptySibling = function(node)
			{
				var next = node.nextSibling;
				while (next && next.nodeType == 3 && _this.util.IsEmptyNode(next, true, true))
				{
					next = next.nextSibling;
				}
				return next;
			};

			this.util.IsEmptyLi = function(li)
			{
				if (li && li.nodeName == 'LI')
				{
					return _this.util.IsEmptyNode(li, true, true) || li.innerHTML.toLowerCase() == '<br>';
				}
				return false;
			};

			this.util.FindParentEx = function(obj, params, maxParent)
			{
				if (BX.checkNode && BX.checkNode(obj, params))
					return obj;
				return BX.findParent(obj, params, maxParent);
			};

			this.util.IsEmptyObject = function(obj)
			{
				for (var i in obj)
				{
					if (obj.hasOwnProperty(i))
					{
						return false;
					}
				}
				return true;
			};
		},

		Parse: function(content, bParseBxNodes, bFormat)
		{
			bParseBxNodes = !!bParseBxNodes;
			this.content = content;
			this.On("OnParse", [bParseBxNodes]);

			if (bParseBxNodes)
			{
				this.content = this.parser.Parse(this.content, this.GetParseRules(), this.GetIframeDoc(), true, bParseBxNodes);
				if ((bFormat === true || this.textareaView.IsShown()) && !this.bbCode)
				{
					this.content = this.FormatHtml(this.content);
				}

				this.content = this.phpParser.ParseBxNodes(this.content);
			}
			else
			{
				this.content = this.phpParser.ParsePhp(this.content);
				this.content = this.parser.Parse(this.content, this.GetParseRules(), this.GetIframeDoc(), true, bParseBxNodes);
			}

			this.On("OnAfterParse", [bParseBxNodes]);

			return this.content;
		},

		On: function(eventName, arEventParams)
		{
			BX.onCustomEvent(this, eventName, arEventParams || []);
		},

		GetIframeDoc: function()
		{
			if (!this.document)
			{
				this.document = this.sandbox.GetDocument();
				BX.addCustomEvent(this, 'OnIframeReInit', BX.proxy(function(){this.document = this.sandbox.GetDocument();}, this));
			}
			return this.document;
		},

		GetParseRules: function()
		{
			this.rules = __BXHtmlEditorParserRules;
			// Here we can make changes to this.rules
			this.On("OnGetParseRules");
			var _this = this;
			this.GetParseRules = function(){return _this.rules;};
			return this.rules;
		},

		GetHTML5Tags: function()
		{
			return this.HTML5_TAGS;
		},

		GetBlockTags: function()
		{
			return this.BLOCK_TAGS;
		},

		SetBxTag: function(el, params)
		{
			var id;
			if (params.id || el && el.id)
				id = params.id || el.id;

			if (!id)
			{
				id = 'bxid' + Math.round(Math.random() * 1000000000);
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
			if (el)
				el.id = params.id;

			this.bxTags[params.id] = params;
			return params.id;
		},

		GetBxTag: function(element)
		{
			var id;
			if (typeof element == 'object' && element && element.id)
				id = element.id;
			else
				id = element;

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
		},

		OnMousedown: function(e)
		{
			var
				node = e.target || e.srcElement;

			if (node && (node.getAttribute || node.parentNode))
			{
				var
					_this = this,
					bxType = node.getAttribute('data-bx-type');

				if (!bxType)
				{
					node = BX.findParent(node, function(n)
					{
						return n == _this.dom.cont || (n.getAttribute && n.getAttribute('data-bx-type'));
					}, this.dom.cont);

					bxType = (node && node.getAttribute) ? node.getAttribute('data-bx-type') : null;
				}

				if (bxType == 'action') // Any type of button or element which runs action
				{
					return BX.PreventDefault(e);
				}
			}
			return true;
		},

		OnClick: function(e)
		{
			var
				target = e.target || e.srcElement,
				bxType = (target && target.getAttribute) ? target.getAttribute('data-bx-type') : false;

			this.On("OnClickBefore", [{e: e, target: target, bxType: bxType}]);
			this.CheckCommand(target);
		},

		CheckCommand: function(node)
		{
			if (node && (node.getAttribute || node.parentNode))
			{
				var
					_this = this,
					bxType = node.getAttribute('data-bx-type');

				if (!bxType)
				{
					node = BX.findParent(node, function(n)
					{
						return n == _this.dom.cont || (n.getAttribute && n.getAttribute('data-bx-type'));
					}, this.dom.cont);

					bxType = (node && node.getAttribute) ? node.getAttribute('data-bx-type') : null;
				}

				if (bxType == 'action') // Any type of button or element which runs action
				{
					var
						action = node.getAttribute('data-bx-action'),
						value = node.getAttribute('data-bx-value');

					if (this.action.IsSupported(action))
					{
						this.action.Exec(action, value);
					}
				}
			}
		},

		SetSplitMode: function(vertical, saveValue)
		{
			this.config.splitVertical = !!vertical;

			if (saveValue !== false)
			{
				this.SaveOption('split_vertical', this.config.splitVertical ? 1 : 0);
			}

			this.SetView('split', saveValue);
		},

		GetSplitMode: function()
		{
			return this.config.splitVertical;
		},

		StartSplitResize: function(e)
		{
			this.dom.resizerOverlay.style.display = 'block';
			var
				dX = 0, dY = 0,
				windowScroll = BX.GetWindowScrollPos(),
				startX = e.clientX + windowScroll.scrollLeft,
				startY = e.clientY + windowScroll.scrollTop,
				_this = this;

			function moveResizer(e, bFinish)
			{
				var
					x = e.clientX + windowScroll.scrollLeft,
					y = e.clientY + windowScroll.scrollTop;

				if(startX == x && startY == y)
				{
					return;
				}

				dX = startX - x;
				dY = startY - y;

				_this.ResizeSceleton(0, 0, {deltaX: dX, deltaY: dY, updateSplitRatio: bFinish});
			}

			function finishResizing(e)
			{
				moveResizer(e, true);
				BX.unbind(document, 'mousemove', moveResizer);
				BX.unbind(document, 'mouseup', finishResizing);
				_this.dom.resizerOverlay.style.display = 'none';
			}

			BX.bind(document, 'mousemove', moveResizer);
			BX.bind(document, 'mouseup', finishResizing);
		},

		Request: function(P)
		{
			if (!P.url)
				P.url = this.config.actionUrl;
			if (P.bIter !== false)
				P.bIter = true;

			if (!P.postData && !P.getData)
				P.getData = this.GetReqData();

//			var errorText;
//			if (!P.errorText)
//				errorText = false;

			var reqId = P.getData ? P.getData.reqId : P.postData.reqId;

			var _this = this, iter = 0;
			var handler = function(result)
			{
				function handleRes()
				{
					//_this.CloseWaitWindow();
//					var erInd = result.toLowerCase().indexOf('bx_event_calendar_action_error');
//					if (!result || result.length <= 0 || erInd != -1)
//					{
//						var errorText = '';
//						if (erInd >= 0)
//						{
//							var
//								ind1 = erInd + 'BX_EVENT_CALENDAR_ACTION_ERROR:'.length,
//								ind2 = result.indexOf('-->', ind1);
//							errorText = result.substr(ind1, ind2 - ind1);
//						}
//						if (P.onerror && typeof P.onerror == 'function')
//							P.onerror();
//
//						return _this.DisplayError(errorText || P.errorText || '');
//					}

					var res = P.handler(_this.GetRequestRes(reqId), result);
					if(res === false && ++iter < 20 && P.bIter)
						setTimeout(handleRes, 5);
					else
						_this.ClearRequestRes(reqId);
				}
				setTimeout(handleRes, 50);
			};
			//this.ShowWaitWindow();

			if (P.postData)
				BX.ajax.post(P.url, P.postData, handler);
			else
				BX.ajax.get(P.url, P.getData, handler);
		},

		GetRequestRes: function(key)
		{
			if (top.BXHtmlEditorAjaxResponse[key] != undefined)
				return top.BXHtmlEditorAjaxResponse[key];

			return {};
		},

		ClearRequestRes: function(key)
		{
			if (top.BXHtmlEditorAjaxResponse)
			{
				top.BXHtmlEditorAjaxResponse[key] = null;
				delete top.BXHtmlEditorAjaxResponse[key];
			}
		},

		GetReqData: function(action, O)
		{
			if (!O)
				O = {};
			if (action)
				O.action = action;
			O.sessid = BX.bitrix_sessid();
			O.bx_html_editor_request = 'Y';
			O.reqId = Math.round(Math.random() * 1000000);
			return O;
		},

		GetTemplateId: function()
		{
			return this.templateId;
		},

		GetSiteId: function()
		{
			return this.config.siteId;
		},

		GetComponentFilter: function()
		{
			return this.componentFilter;
		},

		GetTemplateParams: function()
		{
			return this.templates[this.templateId];
		},

		GetTemplateStyles: function()
		{
			var params = this.templates[this.templateId] || {};
			return params.STYLES || '';
		},

		ApplyTemplate: function(templateId)
		{
			if (this.templateId !== templateId)
			{
				if (this.templates[templateId])
				{
					this.templateId = templateId;
					var
						templ = this.templates[templateId],
						i,
						doc = this.sandbox.GetDocument(),
						head = doc.head || doc.getElementsByTagName('HEAD')[0],
						styles = head.getElementsByTagName('STYLE'),
						links = head.getElementsByTagName('LINK');

					// Clean old template styles
					for (i = 0; i < styles.length; i++)
					{
						if (styles[i].getAttribute('data-bx-template-style') == 'Y')
							BX.cleanNode(styles[i], true);
					}

					// Clean links with template styles
					i = 0;
					while (i < links.length)
					{
						if (links[i].getAttribute('data-bx-template-style') == 'Y')
						{
							BX.remove(links[i], true);
						}
						else
						{
							i++;
						}
					}

					// Add new node in the iframe head
					if (templ['STYLES'])
					{
						head.appendChild(BX.create('STYLE', {props: {type: 'text/css'}, text: templ['STYLES']}, doc)).setAttribute('data-bx-template-style', 'Y');
					}

					if (templ && templ['EDITOR_STYLES'])
					{
						for (i = 0; i < templ['EDITOR_STYLES'].length; i++)
						{
							head.appendChild(BX.create('link', {props: {rel: 'stylesheet', href: templ['EDITOR_STYLES'][i] + '_' + this.cssCounter++}}, doc)).setAttribute('data-bx-template-style', 'Y');
						}
					}

					this.On("OnApplySiteTemplate", [templateId]);
				}
				else
				{
					var _this = this;
					this.Request({
						getData: this.GetReqData('load_site_template',
							{
								site_template: templateId
							}
						),
						handler: function(res)
						{
							_this.templates[templateId] = res;
							_this.ApplyTemplate(templateId);
						}
					});
				}
			}
		},

		FormatHtml: function(html, bForceFormating)
		{
			if (html.length < this.MAX_HANDLED_FORMAT_LENGTH || bForceFormating === true)
			{
				if (!this.formatter)
					this.formatter = new window.BXHtmlEditor.BXCodeFormatter(this);

				var time1 = new Date().getTime();
				html = this.formatter.Format(html);
				var time2 = new Date().getTime();

				if (time2 - time1 > this.MAX_HANDLED_FORMAT_TIME)
					this.MAX_HANDLED_FORMAT_LENGTH -= 5000;
			}

			return html;
		},

		GetFontFamilyList: function()
		{
			if (!this.fontFamilyList)
			{
				this.fontFamilyList = [
					{value: ['Times New Roman', 'Times'], name: 'Times New Roman'},
					{value: ['Courier New'], name: 'Courier New'},
					{value: ['Arial', 'Helvetica'], name: 'Arial / Helvetica'},
					{value: ['Arial Black', 'Gadget'], name: 'Arial Black'},
					{value: ['Tahoma','Geneva'], name: 'Tahoma / Geneva'},
					{value: 'Verdana', name: 'Verdana'},
					{value: ['Georgia', 'serif'], name: 'Georgia'},
					{value: 'monospace', name: 'monospace'}
				];
				this.On("GetFontFamilyList", [this.fontFamilyList]);
			}
			return this.fontFamilyList;
		},

		CheckCurrentStatus: function(status)
		{
			var
				arAction, action, actionState, value,
				actionList = this.GetActiveActions();

			if (status === false)
			{
				for (action in actionList)
				{
					if (actionList.hasOwnProperty(action) && this.action.IsSupported(action))
					{
						arAction = actionList[action];
						arAction.control.SetValue(false, null, action);
					}
				}
			}

			if (!this.iframeView.IsFocused())
				return this.On("OnIframeBlur");

			var range = this.selection.GetRange();

			if (!range || !range.isValid())
				return this.On("OnIframeBlur");

			for (action in actionList)
			{
				if (actionList.hasOwnProperty(action) && this.action.IsSupported(action))
				{
					arAction = actionList[action];
					actionState = this.action.CheckState(action, arAction.value);
					value = arAction.control.GetValue();

					if (actionState)
					{
						arAction.control.SetValue(true, actionState, action);
					}
					else
					{
						arAction.control.SetValue(false, null, action);
					}
				}
			}
		},

		RegisterCheckableAction: function(action, params)
		{
			if (!this.checkedActionList)
				this.checkedActionList = {};

			this.checkedActionList[action] = params;
		},

		GetActiveActions: function()
		{
			return this.checkedActionList;
		},

		SaveOption: function(name, value)
		{
			BX.userOptions.save('html_editor', this.config.settingsKey, name, value);
		},

		GetCurrentCssClasses: function(filterTag)
		{
			return this.styles.GetCSS(this.templateId, this.templates[this.templateId].STYLES, this.templates[this.templateId].PATH || '', filterTag || false);
		},

		GetStylesDescription: function(templateId)
		{
			if (!templateId)
				templateId = this.templateId;
			var res = {};
			if (templateId && this.templates[templateId])
			{
				res = this.templates[templateId].STYLES_TITLE || {};
			}
			return res;
		},

		IsInited: function()
		{
			return !!this.inited;
		},

		IsContentChanged: function()
		{
			var
				cont1 = this.config.content.replace(/[\s\n\r\t]+/ig, ''),
				cont2 = this.GetContent().replace(/[\s\n\r\t]+/ig, '');

			return cont1 != cont2;
		},

		IsSubmited: function()
		{
			return this.isSubmited;
		},

		OnSubmit: function()
		{
			if (!this.isSubmited && this.dom.cont.style.display !== 'none')
			{
				this.RemoveCursorNode();
				this.isSubmited = true;

				if (this.iframeView.IsFocused())
					this.On("OnIframeBlur");

				this.On('OnSubmit');
				this.SaveContent();
			}
		},

		AllowBeforeUnloadHandler: function()
		{
			this.beforeUnloadHandlerAllowed = true;
		},

		DenyBeforeUnloadHandler: function()
		{
			this.beforeUnloadHandlerAllowed = false;
		},

		Destroy: function()
		{
			if (this.sandbox)
				this.sandbox.Destroy();

			if (this.Check())
				BX.remove(this.dom.cont);
		},

		Check: function()
		{
			return this.dom.cont && BX.isNodeInDom(this.dom.cont);
		},

		IsVisible: function()
		{
			return this.Check() && this.dom.cont.offsetWidth > 0;
		},

		GetLastSpecialchars: function()
		{
			var def = ['&cent;', '&sect;', '&euro;', '&pound;','&yen;','&copy;', '&reg;', '&laquo;', '&raquo;', '&deg;', '&plusmn;', '&para;', '&hellip;','&prime;','&Prime;', '&trade;', '&asymp;', '&ne;', '&lt;', '&gt;'];

			if (this.config.lastSpecialchars && typeof this.config.lastSpecialchars == 'object' && this.config.lastSpecialchars.length > 1)
			{
				return this.config.lastSpecialchars;
			}
			else
			{
				return def;
			}
		},

		GetIframeElement: function(id)
		{
			var doc = this.GetIframeDoc();
			return doc ? doc.getElementById(id) : null;
		},

		RegisterDialog: function(id, dialog)
		{
			window.BXHtmlEditor.dialogs[id] = dialog;
		},

		SetConfigHeight: function(height)
		{
			this.config.height = height;
			if (this.IsExpanded())
			{
				this.savedSize.configHeight = height;
				this.savedSize.height = height;
			}
		},

		CheckBrowserCompatibility: function()
		{
			return !(BX.browser.IsOpera() || BX.browser.IsIE8() || BX.browser.IsIE7() || BX.browser.IsIE6() || !document.querySelectorAll);
		},

		GetCursorHtml: function()
		{
			return '<span id="bx-cursor-node"> </span>';
		},

		SetCursorNode: function(range)
		{
			if (!range)
				range = this.selection.GetRange();
			this.RemoveCursorNode();
			this.selection.InsertHTML(this.GetCursorHtml(), range);
		},

		RestoreCursor: function()
		{
			var cursor = this.GetIframeElement('bx-cursor-node');
			if (cursor)
			{
				this.selection.SetAfter(cursor);
				BX.remove(cursor);
			}
		},

		RemoveCursorNode: function()
		{
			if (this.synchro.IsFocusedOnTextarea())
			{

			}
			else
			{
				var cursor = this.GetIframeElement('bx-cursor-node');
				if (cursor)
				{
					this.selection.SetAfter(cursor);
					BX.remove(cursor);
				}
			}
		},

		AddButton: function(params)
		{
			if (params.compact == undefined)
				params.compact = false;
			if (params.toolbarSort == undefined)
				params.toolbarSort = 301;
			if (params.hidden == undefined)
				params.hidden = false;

			// 1. Create Button
			var but = function(editor, wrap)
			{
				// Call parrent constructor
				but.superclass.constructor.apply(this, arguments);
				this.id = params.id;
				this.title = params.name;
				if (params.iconClassName)
					this.className += ' ' + params.iconClassName;
				if (params.action)
					this.action = params.action;

				if (params.disabledForTextarea !== undefined)
					this.disabledForTextarea = params.disabledForTextarea;

				this.Create();

				if (params.src)
					this.pCont.firstChild.style.background = 'url("' + params.src + '") no-repeat scroll 0 0';

				if (wrap)
					wrap.appendChild(this.GetCont());
			};

			BX.extend(but, window.BXHtmlEditor.Button);
			if (params.handler)
				but.prototype.OnClick = params.handler;

			window.BXHtmlEditor.Controls[params.id] = but;

			// 2. Add button to controls map
			BX.addCustomEvent(this, "GetControlsMap", function(controlsMap)
			{
				controlsMap.push({
					id: params.id,
					compact: params.compact,
					hidden: params.hidden,
					sort: params.toolbarSort,
					checkWidth: params.checkWidth == undefined ? true : !!params.checkWidth,
					offsetWidth: (params.offsetWidth || 32)
				});
			});
		},

		AddCustomParser: function(parser)
		{
			if (this.phpParser && this.phpParser.AddCustomParser)
			{
				this.phpParser.AddCustomParser(parser);
			}
			else
			{
				var _this = this;
				BX.addCustomEvent("OnEditorInitedAfter", function(){_this.phpParser.AddCustomParser(parser);});
			}
		},

		AddParser: function(parser)
		{
			if (parser && parser.name && typeof parser.obj == 'object')
			{
				this.parser.specialParsers[parser.name] = parser.obj;
			}
		},

		InsertHtml: function(html, range)
		{
			if (!this.synchro.IsFocusedOnTextarea())
			{
				this.Focus();

				if (this.selection.lastCheckedRange &&
					this.selection.lastCheckedRange.range &&
					!range)
				{
					try
					{
						this.selection.SetSelection(this.selection.lastCheckedRange.range);
					}
					catch(e){}
				}

				if (!range)
				{
					range = this.selection.GetRange();
				}

				if (!range && this.selection.lastRange)
					range = this.selection.lastRange;

				if (range)
				{
					if (!range.collapsed && range.startContainer == range.endContainer && range.startContainer.nodeName !== 'BODY')
					{
						var surNode = this.util.CheckSurrogateNode(range.startContainer);
						if (surNode)
						{
							this.selection.SetAfter(surNode);
						}
					}

					this.selection.InsertHTML(html, range);
					this.selection.ScrollIntoView();
				}
			}
		},

		ParseContentFromBbCode: function(content)
		{
			if (this.bbCode)
			{
				content = this.bbParser.Parse(content);
				content = this.Parse(content, true, true);
			}
			return content;
		},

		LoadFileDialogs: function(callback)
		{
			var _this = this;

			this.Request({
				getData: this.GetReqData('load_file_dialogs',
					{
						editor_id: this.id
					}
				),
				handler: function(res, html)
				{
					html = BX.util.trim(html);
					_this.dom.fileDialogsWrap.innerHTML = html;
					_this.fileDialogsLoaded = true;
					setTimeout(callback, 100);
				}
			});
		},

		InitAutosaveHandlers: function()
		{
			var
				editor = this,
				form = this.dom.form;

			try{
				BX.addCustomEvent(this, 'OnSubmit', function(){form.BXAUTOSAVE.Init();});
				BX.addCustomEvent(this, 'OnContentChanged', function(){form.BXAUTOSAVE.Init();});

				BX.addCustomEvent(form, 'onAutoSave', function (ob, data)
				{
					if (editor.IsShown() && !editor.IsSubmited())
					{
						// Get it from textarea and put to form_data to saving
						data[editor.config.inputName] = editor.GetContent();
					}
				});

				BX.addCustomEvent(form, 'onAutoSaveRestore', function (ob, data)
				{
					if (editor.IsShown())
					{
						editor.SetContent(data[editor.config.inputName], true);
					}
				});
			}catch(e){}
		},

		InitImageUploader: function()
		{
			if (this.config.uploadImagesFromClipboard !== false)
			{
				var uploadUrl = this.config.actionUrl;
				uploadUrl += (uploadUrl.indexOf('?') !== -1 ? "&" : "?") + 'action=uploadfile';
				this.imageUploader = BX.Uploader.getInstance({
					id: this.CID,
					streams: 1,
					allowUpload: "A",
					uploadFileUrl: uploadUrl,
					uploadMethod: "immediate",
					showImage: false,
					sortItems: false,
					input: null,
					placeHolder: null,
					uploadFormData: "N"
				});

				var _this = this;

				BX.addCustomEvent(this, "OnImageDataUriHandle", function (editor, imageBase64)
				{
					var blob = BX.UploaderUtils.dataURLToBlob(imageBase64.src);
					if (blob && blob.size > 0 && blob.type.indexOf("image/") == 0)
					{
						blob.name = (blob.name || imageBase64.title || (this.GetDefaultImageName() + "." + blob.type.substr(6)));
						blob.uniqId = imageBase64.uniqId;
						blob.editorBase64Src = imageBase64.src;
						_this.imageUploader.onChange([blob]);
					}
				});

				BX.addCustomEvent(this.imageUploader, "onFileIsCreated", function (id, item)
				{
					BX.addCustomEvent(item, "onUploadDone", function (item, result)
					{
						_this.HandleImageDataUriCaughtUploadedCallback({
							src: item.file.editorBase64Src, uniqId: item.file.uniqId
						}, {
							src: result.file.uploadedPath
						});
					});

					//BX.addCustomEvent(item, "onUploadError", function(item, result){});
				});
			}
		},

		GetDefaultImageName: function()
		{
			var imageName = {value: false};
			this.On("OnGetDefaultUploadImageName", [imageName]);
			if (!imageName.value)
			{
				imageName.value = 'content-img';
			}

			return imageName.value;
		},

		InitClipboardHandler: function()
		{
			var
				_this = this;

			this.base64Images = [];

			function checkImages(images)
			{
				_this.pasteCheckItteration++;
				var i;
				for (i = 0; i < images.length; i++)
				{
					if (!images[i].getAttribute('data-bx-paste-check'))
					{
						if (images[i].complete)
							_this.CheckImage(images[i], false);
						else
							BX.bind(images[i], 'load', BX.proxy(_this.CheckImage, _this));
						images[i].setAttribute('data-bx-paste-check', 'Y');
					}
				}

				if (_this.pasteCheckItteration == 1)
					setTimeout(function(){checkImages(images);}, 500);
				else if (_this.pasteCheckItteration < 15)
					setTimeout(function(){checkImages(images);}, 1000);
			}

			BX.bind(this.iframeView.element, 'paste', function (e)
			{
				var
					imageHandled = false,
					clipboard = e.clipboardData;

				if (clipboard && clipboard.items)
				{
					var item = clipboard.items[0];
					if (item && item.type.indexOf('image/') > -1)
					{
						var blob = item.getAsFile();
						if (blob)
						{
							var reader = new FileReader();
							reader.readAsDataURL(blob);
							reader.onload = function (event)
							{
								imageHandled = true;
								var img = new Image();
								img.src = event.target.result;

								setTimeout(function()
								{
									_this.selection.InsertNode(img);
									_this.HandleImageDataUri(img);
								}, 100);
							}
						}
					}
				}

				if (!imageHandled)
				{
					var
						doc = _this.GetIframeDoc(),
						images = doc.body.getElementsByTagName('IMG');

					_this.pasteCheckItteration = 0;
					checkImages(images);
				}
			});

			BX.addCustomEvent(this, 'OnImageDataUriCaughtUploaded', BX.proxy(this.HandleImageDataUriCaughtUploadedCallback, this));
			//BX.addCustomEvent(this, 'OnImageDataUriCaughtFailed', BX.proxy(this.HandleImageDataUriCaughtFailedCallback, this));
		},

		GetBase64Image: function(base64source)
		{
			var i, result = false;

			for (i = 0; i < this.base64Images.length; i++)
			{
				if (this.base64Images[i].source == base64source)
				{
					result = this.base64Images[i];
				}
			}
			return result;
		},

		RegisterBase64Image: function(base64source, status)
		{
			this.base64Images.push({
				source: base64source,
				status: status,
				index: this.base64Images.length
			});
		},

		CheckImage: function(image, unbind)
		{
			if (image && image.getAttribute)
			{
				var src = image.getAttribute('src');
				if (src.indexOf('data:image/') !== -1)
				{
					this.HandleImageDataUri(image);
				}

				if (unbind !== false)
					BX.unbind(image, 'load', BX.proxy(this.CheckImage, this));
			}
		},

		HandleImageDataUri: function(image)
		{
			if (!image.getAttribute('data-bx-unique-id'))
			{
				this.skipPasteControl = true;
				if (this.pasteControl.isOpened)
					this.pasteControl.Hide();

				var base64Image = this.GetBase64Image(image.src);

				if (base64Image === false)
				{
					this.RegisterBase64Image(image.src, 'requested');

					var uniqId = 'bx_base64_id_' + Math.round(Math.random() * 1000000000);
					image.setAttribute('data-bx-unique-id', uniqId);
					image.removeAttribute('data-bx-orig-src');

					this.On('OnImageDataUriHandle', [this,
						{
							src: image.src,
							title: image.title || '',
							uniqId: uniqId
						}]);
				}
				else
				{
					if (base64Image.status == 'uploaded')
					{
						this.HandleImageDataUriCaughtUploadedCallback(
							{
								src: image.src,
								title: image.title || '',
								uniqId: base64Image.uniqId
							},
							{
								src: base64Image.fileSrc
							},
							base64Image.htmlForInsert || null
						);
					}
				}
			}
		},

		HandleImageDataUriCaughtUploadedCallback: function(imageReferer, file, htmlForInsert)
		{
			if (imageReferer && imageReferer.uniqId && file && file.src)
			{
				var base64Image = this.GetBase64Image(imageReferer.src);
				if (base64Image && !base64Image.fileSrc)
				{
					base64Image.status = 'uploaded';
					base64Image.uniqId = imageReferer.uniqId;
					base64Image.fileSrc = file.src;
					base64Image.htmlForInsert = htmlForInsert;
				}

				var
						i,image,
						images = this.GetIframeDoc().body.getElementsByTagName('IMG');

				for (i = 0; i < images.length; i++)
				{
					image = images[i];
					if (image.getAttribute('data-bx-unique-id') == imageReferer.uniqId
						||
						image.getAttribute('src') == imageReferer.src
					)
					{
						if (htmlForInsert && htmlForInsert.replacement)
						{
							this.selection.SetAfter(image);
							this.selection.InsertHTML(htmlForInsert.replacement);

							BX.remove(image);
							if (htmlForInsert.callback)
							{
								setTimeout(htmlForInsert.callback, 300);
							}
						}
						else
						{
							image.src = file.src;
							image.setAttribute('src', file.src);
							image.setAttribute('data-bx-orig-src', file.src);
							image.removeAttribute('data-bx-paste-check');
							image.removeAttribute('data-bx-unique-id');
						}
					}
				}
			}
			this.skipPasteControl = false;
		}
	};

	window.BXEditor = BXEditor;

	function Sandbox(callBack, config)
	{
		this.callback = callBack || BX.DoNothing;
		this.config = config || {};
		this.editor = this.config.editor;
		this.iframe = this.CreateIframe();
		this.bSandbox = false;

		// Properties to unset/protect on the window object
		this.windowProperties = ["parent", "top", "opener", "frameElement", "frames",
			"localStorage", "globalStorage", "sessionStorage", "indexedDB"];
		//Properties on the window object which are set to an empty function
		this.windowProperties2 = ["open", "close", "openDialog", "showModalDialog", "alert", "confirm", "prompt", "openDatabase", "postMessage", "XMLHttpRequest", "XDomainRequest"];
		//Properties to unset/protect on the document object
		this.documentProperties = ["referrer", "write", "open", "close"];
	}

	Sandbox.prototype =
	{
		GetIframe: function()
		{
			return this.iframe;
		},

		GetWindow: function()
		{
			this._readyError();
		},

		GetDocument: function()
		{
			this._readyError();
		},

		Destroy: function()
		{
			var iframe = this.GetIframe();
			iframe.parentNode.removeChild(iframe);
		},

		_readyError: function()
		{
			throw new Error("Sandbox: Sandbox iframe isn't loaded yet");
		},

		CreateIframe: function()
		{
			var
				_this = this,
				iframe = BX.create("IFRAME", {
					props: {
						className: "bx-editor-iframe",
						frameborder: 0,
						allowtransparency: "true",
						width: 0,
						height: 0,
						marginwidth: 0,
						marginheight: 0
						//scrolling: 'no'
					}
				});

			iframe.onload = function()
			{
				iframe.onreadystatechange = iframe.onload = null;
				_this.OnLoadIframe(iframe);
			};

			iframe.onreadystatechange = function()
			{
				if (/loaded|complete/.test(iframe.readyState))
				{
					iframe.onreadystatechange = iframe.onload = null;
					_this.OnLoadIframe(iframe);
				}
			};

			// Append iframe to ext container
			this.config.cont.appendChild(iframe);

			return iframe;
		},

		OnLoadIframe: function(iframe)
		{
			if (BX.isNodeInDom(iframe))
			{
				var
					_this = this,
					iframeWindow = iframe.contentWindow,
					iframeDocument = iframeWindow.document;

				this.InitIframe(iframe);

				iframeWindow.onerror = function(errorMessage, fileName, lineNumber) {
					throw new Error("Sandbox: " + errorMessage, fileName, lineNumber);
				};

				if (this.bSandbox)
				{
					var i, length;
					for (i = 0, length = this.windowProperties.length; i < length; i++)
					{
						this._unset(iframeWindow, this.windowProperties[i]);
					}

					for (i=0, length = this.windowProperties2.length; i < length; i++)
					{
						this._unset(iframeWindow, this.windowProperties2[i], BX.DoNothing());
					}

					for (i = 0, length = this.documentProperties.length; i < length; i++)
					{
						this._unset(iframeDocument, this.documentProperties[i]);
					}

					this._unset(iframeDocument, "cookie", "", true);
				}

				this.loaded = true;
				// Trigger the callback
				setTimeout(function()
				{
					_this.callback(_this);
				}, 0);
			}
		},

		InitIframe: function(iframe)
		{
			iframe = this.iframe || iframe;
			var
				iframeDocument = iframe.contentWindow.document,
				iframeHtml = this.GetHtml(this.config.stylesheets, this.editor.GetTemplateStyles());

			// Create the basic dom tree including proper DOCTYPE and charset
			iframeDocument.open("text/html", "replace");
			iframeDocument.write(iframeHtml);
			iframeDocument.close();

			this.GetWindow = function()
			{
				return iframe.contentWindow;
			};
			this.GetDocument = function()
			{
				return iframe.contentWindow.document;
			};

			this.editor.On("OnIframeInit");
		},

		GetHtml: function(css, cssText)
		{
			var
				bodyParams = '',
				headHtml = "",
				i;

			if (this.editor.config.bodyClass || this.editor.IsExpanded())
			{
				var bodyClass = this.editor.config.bodyClass || '';
				if (this.editor.IsExpanded())
					bodyClass += ' fullscreen';
				bodyParams += ' class="' + BX.util.trim(bodyClass) + '"';
			}
			if (this.editor.config.bodyId)
			{
				bodyParams += ' id="' + this.editor.config.bodyId + '"';
			}

			var templ = this.editor.GetTemplateParams();
			if (templ && templ['EDITOR_STYLES'])
			{
				for (i = 0; i < templ['EDITOR_STYLES'].length; i++)
				{
					headHtml += '<link data-bx-template-style="Y" rel="stylesheet" href="' + templ['EDITOR_STYLES'][i] + '_' + this.editor.cssCounter++ + '">';
				}
			}

			css = typeof css === "string" ? [css] : css;
			if (css)
			{
				for (i = 0; i < css.length; i++)
				{
					headHtml += '<link rel="stylesheet" href="' + css[i] + '">';
				}
			}

			headHtml += '<link rel="stylesheet" href="' + this.editor.config.cssIframePath + '_' + this.editor.cssCounter++ + '">';

			if (typeof cssText === "string")
			{
				headHtml += '<style type="text/css" data-bx-template-style="Y">' + cssText + '</style>';
			}

			if (this.editor.iframeCssText && this.editor.iframeCssText.length > 0)
			{
				headHtml += '<style type="text/css">' + this.editor.iframeCssText + '</style>';
			}

			return '<!DOCTYPE html><html><head>' + headHtml + '</head><body' + bodyParams + '></body></html>';
		},

		/**
		 * Method to unset/override existing variables
		 * @example
		 * // Make cookie unreadable and unwritable
		 * this._unset(document, "cookie", "", true);
		 */
		_unset: function(object, property, value, setter)
		{
			try { object[property] = value; } catch(e) {}

			try { object.__defineGetter__(property, function() { return value; }); } catch(e) {}
			if (setter) {
				try { object.__defineSetter__(property, function() {}); } catch(e) {}
			}

			if (!crashesWhenDefineProperty(property))
			{
				try {
					var config = {
						get: function() { return value; }
					};
					if (setter) {
						config.set = function() {};
					}
					Object.defineProperty(object, property, config);
				} catch(e) {}
			}
		}
	};

	function BXEditorSelection(editor)
	{
		this.editor = editor;
		this.document = editor.sandbox.GetDocument();
		BX.addCustomEvent(this.editor, 'OnIframeReInit', BX.proxy(function(){this.document = this.editor.sandbox.GetDocument();}, this));
		// Make sure that our external range library is initialized
		window.rangy.init();
	}

	BXEditorSelection.prototype =
	{
		// Get the current selection as a bookmark to be able to later restore it
		GetBookmark: function()
		{
			if (!this.editor.synchro.IsFocusedOnTextarea())
			{
				var range = this.GetRange();
				return range && range.cloneRange();
			}
			return false;
		},

		// Restore a selection
		SetBookmark: function(bookmark)
		{
			if (bookmark && this.editor.currentViewName !== 'code')
			{
				this.SetSelection(bookmark);
			}
		},

		// Save current selection
		SaveBookmark: function()
		{
			this.lastRange = this.GetBookmark();
			return this.lastRange;
		},

		GetLastRange: function()
		{
			if (this.lastRange)
				return this.lastRange;
		},

		// Restore selection
		RestoreBookmark: function()
		{
			if (this.lastRange)
			{
				this.SetBookmark(this.lastRange);
				this.lastRange = false;
			}
		},

		/**
		 * Set the caret in front of the given node
		 * @param {Object} node The element or text node where to position the caret in front of
		 */
		SetBefore: function(node)
		{
			var range = rangy.createRange(this.document);
			range.setStartBefore(node);
			range.setEndBefore(node);
			return this.SetSelection(range);
		},

		/**
		 * Set the caret after the given node
		 *
		 * @param {Object} node The element or text node where to position the caret in front of
		 */
		SetAfter: function(node)
		{
			var range = rangy.createRange(this.document);
			range.setStartAfter(node);
			range.setEndAfter(node);
			return this.SetSelection(range);
		},

		/**
		 * Ability to select/mark nodes
		 *
		 * @param {Element} node The node/element to select
		 */
		SelectNode: function(node)
		{
			if (!node)
				return;

			var
				range = rangy.createRange(this.document),
				isElement = node.nodeType === 1,
				canHaveHTML = "canHaveHTML" in node ? node.canHaveHTML : (node.nodeName !== "IMG"),
				content = isElement ? node.innerHTML : node.data,
				isEmpty = (content === "" || content === this.editor.INVISIBLE_SPACE),
				styleDisplay = BX.style(node, 'display'),
				bBlock = (styleDisplay === "block" || styleDisplay === "list-item");

			if ((BX.browser.IsIE() || BX.browser.IsIE10() || BX.browser.IsIE11()) && node &&
				BX.util.in_array(node.nodeName.toUpperCase(), this.editor.TABLE_TAGS))
			{
				//"TD", "TR", "TH", "TABLE", "TBODY", "CAPTION", "COL", "COLGROUP", "TFOOT", "THEAD"];
				if (node.tagName == 'TABLE' || node.tagName == 'TBODY')
				{
					var
						firstRow = node.rows[0],
						lastRow = node.rows[node.rows.length - 1];

					range.setStartBefore(firstRow.cells[0]);
					range.setEndAfter(lastRow.cells[lastRow.cells.length - 1]);
				}
				else if (node.tagName == 'TR' || node.tagName == 'TH')
				{
					range.setStartBefore(node.cells[0]);
					range.setEndAfter(node.cells[node.cells.length - 1]);
				}
				else
				{
					range.setStartBefore(node);
					range.setEndAfter(node);
				}

				this.SetSelection(range);
				return range;
			}

			if (isEmpty && isElement && canHaveHTML)
			{
				// Make sure that caret is visible in node by inserting a zero width no breaking space
				try {
					node.innerHTML = this.editor.INVISIBLE_SPACE;
				} catch(e) {}
			}

			if (canHaveHTML)
				range.selectNodeContents(node);
			else
				range.selectNode(node);

			if (canHaveHTML && isEmpty && isElement)
			{
				range.collapse(bBlock);
			}
			else if (canHaveHTML && isEmpty)
			{
				range.setStartAfter(node);
				range.setEndAfter(node);
			}

			try
			{
				this.SetSelection(range);
			}
			catch(e){}

			return range;
		},

		/**
		 * Get the node which contains the selection
		 *
		 * @param {Boolean} [controlRange] (only IE) Whether it should return the selected ControlRange element when the selection type is a "ControlRange"
		 * @return {Object} The node that contains the caret
		 */
		GetSelectedNode: function(controlRange)
		{
			var
				res,
				selection,
				range;

			if (controlRange && this.document.selection && this.document.selection.type === "Control")
			{
				range = this.document.selection.createRange();
				if (range && range.length)
				{
					res = range.item(0);
				}
			}

			if (!res)
			{
				selection = this.GetSelection();
				if (selection.focusNode === selection.anchorNode)
				{
					res = selection.focusNode;
				}
			}

			if (!res)
			{
				range = this.GetRange();
				res = range ? range.commonparentContainer : this.document.body
			}

			if (res && res.ownerDocument != this.editor.GetIframeDoc())
			{
				res = this.document.body;
			}

			return res;
		},

		ExecuteAndRestore: function(method, restoreScrollPosition)
		{
			var
				body = this.document.body,
				oldScrollTop = restoreScrollPosition && body.scrollTop,
				oldScrollLeft = restoreScrollPosition && body.scrollLeft,
				className = "_bx-editor-temp-placeholder",
				placeholderHTML = '<span class="' + className + '">' + this.editor.INVISIBLE_SPACE + '</span>',
				range = this.GetRange(),
				newRange;

			// Nothing selected, execute and say goodbye
			if (!range)
			{
				method(body, body);
				return;
			}

			var node = range.createContextualFragment(placeholderHTML);
			range.insertNode(node);

			// Make sure that a potential error doesn't cause our placeholder element to be left as a placeholder
			try
			{
				method(range.startContainer, range.endContainer);
			}
			catch(e3)
			{
				setTimeout(function() { throw e3; }, 0);
			}

			if (document.querySelector)
			{
				var caretPlaceholder = this.document.querySelector("." + className);
				if (caretPlaceholder)
				{
					newRange = rangy.createRange(this.document);
					newRange.selectNode(caretPlaceholder);
					newRange.deleteContents();
					this.SetSelection(newRange);
				}
				else
				{
					// fallback for when all hell breaks loose
					body.focus();
				}
			}

			if (restoreScrollPosition)
			{
				body.scrollTop = oldScrollTop;
				body.scrollLeft = oldScrollLeft;
			}

			// Remove it again, just to make sure that the placeholder is definitely out of the dom tree
			try {
				if (caretPlaceholder && caretPlaceholder.parentNode)
					caretPlaceholder.parentNode.removeChild(caretPlaceholder);
			} catch(e4) {}
		},

		/**
		 * Different approach of preserving the selection (doesn't modify the dom)
		 * Takes all text nodes in the selection and saves the selection position in the first and last one
		 */
		ExecuteAndRestoreSimple: function(method)
		{
			var
				range = this.GetRange(),
				body = this.document.body,
				newRange,
				firstNode,
				lastNode,
				textNodes,
				rangeBackup;

			// Nothing selected, execute and say goodbye
			if (!range)
			{
				method(body, body);
				return;
			}

			textNodes = range.getNodes([3]);
			firstNode = textNodes[0] || range.startContainer;
			lastNode = textNodes[textNodes.length - 1] || range.endContainer;

			rangeBackup = {
				collapsed: range.collapsed,
				startContainer: firstNode,
				startOffset: firstNode === range.startContainer ? range.startOffset : 0,
				endContainer: lastNode,
				endOffset: lastNode === range.endContainer ? range.endOffset : lastNode.length
			};

			try
			{
				method(range.startContainer, range.endContainer);
			}
			catch(e)
			{
				setTimeout(function() { throw e; }, 0);
			}

			newRange = rangy.createRange(this.document);
			try { newRange.setStart(rangeBackup.startContainer, rangeBackup.startOffset); } catch(e1) {}
			try { newRange.setEnd(rangeBackup.endContainer, rangeBackup.endOffset); } catch(e2) {}
			try { this.SetSelection(newRange); } catch(e3) {}
		},

		/**
		 * Insert html at the caret position and move the cursor after the inserted html
		 *
		 * @param {String} html HTML string to insert
		 */
		InsertHTML: function(html, range)
		{
			var
				rng = rangy.createRangyRange(this.document),
				node = rng.createContextualFragment(html),
				lastChild = node.lastChild;

			this.InsertNode(node, range);
			if (lastChild)
			{
				this.SetAfter(lastChild);
			}

			this.editor.On('OnInsertHtml');
		},

		/**
		 * Insert a node at the caret position and move the cursor behind it
		 *
		 * @param {Object} node HTML string to insert
		 */
		InsertNode: function(node, range)
		{
			if (!range || !range.isValid || !range.isValid())
				range = this.GetRange();

			if (range)
			{
				range.insertNode(node);
			}

			this.editor.On('OnInsertHtml');
		},

		RemoveNode: function(node)
		{
			this.editor.On('OnHtmlContentChangedByControl');
			var
				parent = node.parentNode,
				cursorNode = node.nextSibling;
			BX.remove(node);
			this.editor.util.Refresh(parent);

			if (cursorNode)
			{
				this.editor.selection.SetBefore(cursorNode);
				this.editor.Focus();
			}
			this.editor.synchro.StartSync(100);
		},

		/**
		 * Wraps current selection with the given node
		 *
		 * @param {Object} node The node to surround the selected elements with
		 * @param {Object} range Current range
		 */
		Surround: function(node, range)
		{
			range = range || this.GetRange();
			if (range)
			{
				try
				{
					// This only works when the range boundaries are not overlapping other elements
					range.surroundContents(node);
					this.SelectNode(node);
				}
				catch(e)
				{
					node.appendChild(range.extractContents());
					range.insertNode(node);
				}
			}
		},

		ScrollIntoView: function()
		{
			var
				node,
				_this = this,
				doc = this.document,
				win = this.editor.sandbox.GetWindow(),
				bScrollBars = doc.documentElement.scrollHeight > doc.documentElement.offsetHeight;

			if (bScrollBars && win)
			{
				var
					tempNode = doc.__scrollIntoViewElement = doc.__scrollIntoViewElement || (function()
					{
						return BX.create("SPAN", {html: _this.editor.INVISIBLE_SPACE}, doc);
					})(),
					top = 0;

				this.InsertNode(tempNode);

				if (tempNode.parentNode)
				{
					node = tempNode;
					do
					{
						top += node.offsetTop || 0;
						node = node.offsetParent;
					} while (node);
					tempNode.parentNode.removeChild(tempNode);
				}

				var
					scrollPos = BX.GetWindowScrollPos(doc),
					innerSize = BX.GetWindowInnerSize(doc);

				if (top > scrollPos.scrollTop + innerSize.innerHeight - 40)
				{
					win.scrollTo(scrollPos.scrollLeft, top);
				}
			}
		},

		/**
		 * Select line where the caret is in
		 */
		SelectLine: function()
		{
			// See https://developer.mozilla.org/en/DOM/Selection/modify
			var bSelectionModify = "getSelection" in window && "modify" in window.getSelection();
			if (bSelectionModify)
			{
				var
					win = this.document.defaultView,
					selection = win.getSelection();
				selection.modify("move", "left", "lineboundary");
				selection.modify("extend", "right", "lineboundary");
			}
			else if (this.document.selection) // IE
			{
				var
					range = this.document.selection.createRange(),
					rangeTop = range.boundingTop,
					rangeHeight = range.boundingHeight,
					scrollWidth = this.document.body.scrollWidth,
					rangeBottom,
					rangeEnd,
					measureNode,
					i,
					j;

				if (!range.moveToPoint)
					return;

				if (rangeTop === 0)
				{
					// Don't know why, but when the selection ends at the end of a line
					// range.boundingTop is 0
					measureNode = this.document.createElement("span");
					this.insertNode(measureNode);
					rangeTop = measureNode.offsetTop;
					measureNode.parentNode.removeChild(measureNode);
				}
				rangeTop += 1;

				for (i =- 10; i < scrollWidth; i += 2)
				{
					try {
						range.moveToPoint(i, rangeTop);
						break;
					} catch(e1) {}
				}

				// Investigate the following in order to handle multi line selections
				// rangeBottom = rangeTop + (rangeHeight ? (rangeHeight - 1) : 0);
				rangeBottom = rangeTop;
				rangeEnd = this.document.selection.createRange();
				for (j = scrollWidth; j >= 0; j--)
				{
					try
					{
						rangeEnd.moveToPoint(j, rangeBottom);
						break;
					} catch(e2) {}
				}

				range.setEndPoint("EndToEnd", rangeEnd);
				range.select();
			}
		},

		GetText: function()
		{
			var selection = this.GetSelection();
			return selection ? selection.toString() : "";
		},

		GetNodes: function(nodeType, filter)
		{
			var range = this.GetRange();
			if (range)
				return range.getNodes([nodeType], filter);
			else
				return [];
		},

		GetRange: function(selection, bSetFocus)
		{
			if (!selection)
			{
				if (!this.editor.iframeView.IsFocused() && bSetFocus !== false)
				{
					var
						doc = this.editor.GetIframeDoc(),
						originalScrollTop = doc.documentElement.scrollTop || doc.body.scrollTop,
						originalScrollLeft = doc.documentElement.scrollLeft || doc.body.scrollLeft;

					this.editor.iframeView.Focus();

					var
						newScrollTop = doc.documentElement.scrollTop || doc.body.scrollTop,
						newScrollLeft = doc.documentElement.scrollLeft || doc.body.scrollLeft;

					if (newScrollTop !== originalScrollTop || newScrollLeft !== originalScrollLeft)
					{
						var win = this.editor.sandbox.GetWindow();
						if (win)
							win.scrollTo(originalScrollLeft, originalScrollTop);
					}
				}

				selection = this.GetSelection();
			}

			var range = selection && selection.rangeCount && selection.getRangeAt(0);
			if (range.commonAncestorContainer && range.commonAncestorContainer.nodeName == '#document')
			{
				range.selectNodeContents(this.editor.GetIframeDoc().body);
			}

			return range;
		},

		GetSelection: function(doc)
		{
			return rangy.getSelection(doc || this.document.defaultView || this.document.parentWindow);
		},

		SetSelection: function(range)
		{
			var
				win = this.document.defaultView || this.document.parentWindow,
				selection = rangy.getSelection(win);
			return selection.setSingleRange(range);
		},

		GetStructuralTags: function()
		{
			if (!this.structuralTags)
			{
				var tblRe = /^TABLE/i;
				this.structuralTags = {
					'LI': /^UL|OL|MENU/i,
					'DT': /^DL/i,
					'DD': /^DL/i,
					// table
					'TD': tblRe,
					'TR': tblRe,
					'TH': tblRe,
					'TBODY': tblRe,
					'TFOOT': tblRe,
					'THEAD': tblRe,
					'CAPTION': tblRe,
					'COL': tblRe,
					'COLGROUP': tblRe
				};
				this.structuralTagsMatchRe = /^LI|DT|DD|TD|TR|TH|TBODY|CAPTION|COL|COLGROUP|TFOOT|THEAD/i;
			}
			return this.structuralTags;
		},

		SetCursorBeforeNode: function(e)
		{

		},

		_GetNonTextLastChild: function(n)
		{
			var res = n.lastChild;
			while (res.nodeType != 1 && res.previousSibling)
				res = res.previousSibling;

			return res.nodeType == 1 ? res : false;
		},

		_GetNonTextFirstChild: function(n)
		{
			var res = n.firstChild;
			while (res.nodeType != 1 && res.nextSibling)
				res = res.nextSibling;

			return res.nodeType == 1 ? res : false;
		},

		_MoveCursorBeforeNode: function(node)
		{
			var
				_this = this,
				possibleParentRe, parNode, isFirstNode;

			this.GetStructuralTags();

			// Check if it's child node which have special parent
			// We can't add text node and put carret <td> and <tr> or between <li>...
			// So we trying handle the only case when carret before beginning of the first child of our structural tags (UL, OL, MENU, DIR, TABLE, DL)
			if (node.nodeType == 1 && node.nodeName.match(this.structuralTagsMatchRe))
			{
				isFirstNode = this._GetNonTextFirstChild(node.parentNode) === node;
				if (!isFirstNode)
				{
					return;
				}

				possibleParentRe = this.structuralTags[node.nodeName];
				if (possibleParentRe)
				{
					parNode = BX.findParent(node, function(n)
					{
						if (n.nodeName.match(possibleParentRe))
						{
							return true;
						}

						isFirstNode = isFirstNode && _this._GetNonTextFirstChild(n.parentNode) === n;
						return false;
					}, node.ownerDocument.BODY);

					if (parNode && isFirstNode)
					{
						node = parNode; // Put carret before parrent tag
					}
					else
					{
						return;
					}
				}
			}

			this.SetInvisibleTextBeforeNode(node);
		},

		_MoveCursorAfterNode: function(node)
		{
			var
				_this = this,
				possibleParentRe, parNode, isLastNode;

			this.GetStructuralTags();
			// Check if it's child node which have special parent
			// We can't add text node and put carret <td> and <tr> or between <li>...
			// So we trying handle the only case when carret in the end of the last child of last child of our structural tags (UL, OL, MENU, DIR, TABLE, DL)
			if (node.nodeType == 1 && node.nodeName.match(this.structuralTagsMatchRe))
			{
				isLastNode = this._GetNonTextLastChild(node.parentNode) === node;
				if (!isLastNode)
				{
					return;
				}

				possibleParentRe = this.structuralTags[node.nodeName];
				if (possibleParentRe)
				{
					parNode = BX.findParent(node, function(n)
					{
						if (n.nodeName.match(possibleParentRe))
						{
							return true;
						}

						isLastNode = isLastNode && _this._GetNonTextLastChild(n.parentNode) === n;
						return false;
					}, node.ownerDocument.BODY);

					if (parNode && isLastNode)
					{
						node = parNode; // Put carret after parrent tag
					}
					else
					{
						return;
					}
				}
			}

			this.SetInvisibleTextAfterNode(node);
		},

		SaveRange: function(bSetFocus)
		{
			var range = this.GetRange(false, bSetFocus);
			if (range)
			{
				this.lastCheckedRange = {endOffset: range.endOffset, endContainer: range.endContainer, range: range};
			}
			else
			{
				// Mantis: #66025
				setTimeout(BX.proxy(this.SaveRange, this), 0);
			}
		},

		CheckLastRange: function(range)
		{
			return this.lastCheckedRange && this.lastCheckedRange.endOffset == range.endOffset && this.lastCheckedRange.endContainer == range.endContainer;
		},

		SetInvisibleTextAfterNode: function(node, setCursorBefore)
		{
			var invis_text = this.editor.util.GetInvisibleTextNode();
			if (node.nextSibling && node.nextSibling.nodeType == 3 && this.editor.util.IsEmptyNode(node.nextSibling))
			{
				this.editor.util.ReplaceNode(node.nextSibling, invis_text);
			}
			else
			{
				this.editor.util.InsertAfter(invis_text, node);
			}

			if (setCursorBefore)
			{
				this.SetBefore(invis_text);
			}
			else
			{
				this.SetAfter(invis_text);
			}

			this.editor.Focus();
		},

		SetInvisibleTextBeforeNode: function(node)
		{
			var invis_text = this.editor.util.GetInvisibleTextNode();
			if (node.previousSibling && node.previousSibling.nodeType == 3 && this.editor.util.IsEmptyNode(node.previousSibling))
			{
				this.editor.util.ReplaceNode(node.previousSibling, invis_text);
			}
			else
			{
				node.parentNode.insertBefore(invis_text, node);
			}

			this.SetBefore(invis_text);
			this.editor.Focus();
		},

		GetCommonAncestorForRange: function(range)
		{
			return range.collapsed ?
				range.startContainer :
				rangy.dom.getCommonAncestor(range.startContainer, range.endContainer);
		}
	};

	function NodeMerge(firstNode)
	{
		this.isElementMerge = (firstNode.nodeType == 1);
		this.firstTextNode = this.isElementMerge ? firstNode.lastChild : firstNode;
		this.firstNode = firstNode;
		this.textNodes = [this.firstTextNode];
	}

	NodeMerge.prototype = {
		DoMerge: function()
		{
			var
				i, len = this.textNodes.length,
				textBits = [], textNode, parent, text;

			for (i = 0; i < len; ++i)
			{
				textNode = this.textNodes[i];
				if (this.textNodes[i].nodeType !== 3)
				{
					return false;
				}

				parent = textNode.parentNode;
				textBits[i] = textNode.data;
				if (i)
				{
					parent.removeChild(textNode);
					if (!parent.hasChildNodes())
						parent.parentNode.removeChild(parent);
				}
			}
			this.firstTextNode.data = text = textBits.join("");

			return text;
		},

		GetLength: function()
		{
			var i = this.textNodes.length, len = 0;
			while (i--)
				len += this.textNodes[i].length;
			return len;
		}

//		ToString: function()
//		{
//			var textBits = [];
//
//			for (var i = 0, len = this.textNodes.length; i < len; ++i)
//				textBits[i] = "'" + this.textNodes[i].data + "'";
//
//			return "[Merge(" + textBits.join(",") + ")]";
//		}
	};

	function HTMLStyler(editor, tagNames, arStyle, cssClass, normalize)
	{
		this.editor = editor;
		this.document = editor.iframeView.document;
		this.tagNames = tagNames || [defaultTagName];
		this.arStyle = arStyle || {};
		this.cssClass = cssClass || "";
		this.similarClassRegExp = null;
		this.normalize = normalize;
		this.applyToAnyTagName = false;
	}

	HTMLStyler.prototype =
	{
		GetStyledParent: function(node, bMatchCss)
		{
			bMatchCss = bMatchCss !== false;
			var cssClassMatch, cssStyleMatch;
			while (node)
			{
				if (node.nodeType == 1)
				{
					cssStyleMatch = this.CheckCssStyle(node, bMatchCss);
					cssClassMatch = this.CheckCssClass(node);

					if (BX.util.in_array(node.tagName.toLowerCase(), this.tagNames) && cssClassMatch && cssStyleMatch)
						return node;
				}

				node = node.parentNode;
			}
			return false;
		},

		CheckCssStyle: function(node, bMatch)
		{
			return this.editor.util.CheckCss(node, this.arStyle, bMatch);
		},

		SimplifyNodesWithCss: function(node)
		{
			var i, parent = node.parentNode;
			if (parent.childNodes.length == 1) // container over our node
			{
				if (node.nodeName == parent.nodeName)
				{
					for (i in this.arStyle)
					{
						if (this.arStyle.hasOwnProperty(i) && node.style[i])
						{
							parent.style[i] = node.style[i];
						}
					}
					this.editor.util.ReplaceWithOwnChildren(node);
				}
				else
				{
					for (i in this.arStyle)
					{
						if (this.arStyle.hasOwnProperty(i) && parent.style[i] && node.style[i])
						{
							parent.style[i] = '';
						}
					}
				}
			}
		},

		CheckCssClass: function(node)
		{
			return !this.cssClass || (this.cssClass && BX.hasClass(node, this.cssClass));
		},

		// Normalizes nodes after applying a CSS class to a Range.
		PostApply: function(textNodes, range)
		{
			var
				i,
				firstNode = textNodes[0],
				lastNode = textNodes[textNodes.length - 1],
				merges = [],
				currentMerge,
				rangeStartNode = firstNode,
				rangeEndNode = lastNode,
				rangeStartOffset = 0,
				rangeEndOffset = lastNode.length,
				textNode, precedingTextNode;

			for (i = 0; i < textNodes.length; ++i)
			{
				textNode = textNodes[i];
				precedingTextNode = this.GetAdjacentMergeableTextNode(textNode.parentNode, false);
				if (precedingTextNode)
				{
					if (!currentMerge)
					{
						currentMerge = new NodeMerge(precedingTextNode);
						merges.push(currentMerge);
					}
					currentMerge.textNodes.push(textNode);

					if (textNode === firstNode)
					{
						rangeStartNode = currentMerge.firstTextNode;
						rangeStartOffset = rangeStartNode.length;
					}

					if (textNode === lastNode)
					{
						rangeEndNode = currentMerge.firstTextNode;
						rangeEndOffset = currentMerge.GetLength();
					}
				}
				else
				{
					currentMerge = null;
				}
			}

			// Test whether the first node after the range needs merging
			var nextTextNode = this.GetAdjacentMergeableTextNode(lastNode.parentNode, true);
			if (nextTextNode)
			{
				if (!currentMerge)
				{
					currentMerge = new NodeMerge(lastNode);
					merges.push(currentMerge);
				}
				currentMerge.textNodes.push(nextTextNode);
			}

			// Do the merges
			if (merges.length)
			{
				for (i = 0; i < merges.length; ++i)
					merges[i].DoMerge();

				// Set the range boundaries
				range.setStart(rangeStartNode, rangeStartOffset);
				range.setEnd(rangeEndNode, rangeEndOffset);
			}

			// Simplify elements
			textNodes = range.getNodes([3]);
			for (i = 0; i < textNodes.length; ++i)
			{
				textNode = textNodes[i];
				this.SimplifyNodesWithCss(textNode.parentNode);
			}
		},

		NormalizeNewNode: function(node, range)
		{
			var
				parent = node.parentNode;

			if (parent && parent.nodeName !== 'BODY')
			{
				var
					childs = this.GetNonEmptyChilds(parent),
					cssStyleMatch = this.CheckCssStyle(parent, false),
					cssClassMatch = this.CheckCssClass(parent);

				if (childs.length == 1 && parent.nodeName == node.nodeName && cssStyleMatch && cssClassMatch)
				{
					parent.parentNode.insertBefore(node, parent);
					BX.remove(parent);
				}
			}

			return range;
		},

		GetNonEmptyChilds: function(node)
		{
			var
				i,
				childs = node.childNodes,
				res = [];

			for (i = 0; i < childs.length; i++)
			{
				if (childs[i].nodeType == 1 ||
					(childs[i].nodeType == 3
						&& childs[i].nodeValue != ""
						&& childs[i].nodeValue != this.editor.INVISIBLE_SPACE
						&& !childs[i].nodeValue.match(/^[\s\n\r\t]+$/ig)))
				{
					res.push(childs[i]);
				}
			}
			return res;
		},

		GetAdjacentMergeableTextNode: function(node, forward)
		{
			var
				isTextNode = (node.nodeType == 3),
				el = isTextNode ? node.parentNode : node,
				adjacentNode,
				propName = forward ? "nextSibling" : "previousSibling";

			if (isTextNode)
			{
				// Can merge if the node's previous/next sibling is a text node
				adjacentNode = node[propName];
				if (adjacentNode && adjacentNode.nodeType == 3)
				{
					return adjacentNode;
				}
			}
			else
			{
				// TODO: fix it. Now code fails on this example, try to make "22" italic
				// <i>11 </i>22<i><br>
				// 33 </i><br>
				/*
				// Compare element with its sibling
				adjacentNode = el[propName];
				if (adjacentNode && this.AreElementsMergeable(node, adjacentNode))
				{

					return adjacentNode[forward ? "firstChild" : "lastChild"];
				}
				*/
			}
			return null;
		},

		AreElementsMergeable: function(el1, el2)
		{
			return rangy.dom.arrayContains(this.tagNames, (el1.tagName || "").toLowerCase())
				&& rangy.dom.arrayContains(this.tagNames, (el2.tagName || "").toLowerCase())
				&& el1.className.replace(/\s+/g, " ") == el2.className.replace(/\s+/g, " ")
				&& this.CompareNodeAttributes(el1, el2);
		},

		CompareNodeAttributes: function(el1, el2)
		{
			if (el1.attributes.length != el2.attributes.length)
				return false;

			var i, len = el1.attributes.length, attr1, attr2, name;
			for (i = 0; i < len; i++)
			{
				attr1 = el1.attributes[i];
				name = attr1.name;
				if (name != "class")
				{
					attr2 = el2.attributes.getNamedItem(name);
					if (attr1.specified != attr2.specified)
						return false;

					if (attr1.specified && attr1.nodeValue !== attr2.nodeValue)
					{
						return false;
					}
				}
			}
			return true;
		},

		CreateContainer: function()
		{
			var el = this.document.createElement(this.tagNames[0]);
			if (this.cssClass)
			{
				el.className = this.cssClass;
			}
			if (this.arStyle)
			{
				for (var i in this.arStyle)
				{
					if (this.arStyle.hasOwnProperty(i))
					{
						el.style[i] = this.arStyle[i];
					}
				}
			}
			return el;
		},

		ApplyToTextNode: function(textNode)
		{
			var parent = textNode.parentNode, i;

			if (parent.childNodes.length == 1 && BX.util.in_array(parent.tagName.toLowerCase(), this.tagNames))
			{
				if (this.cssClass)
				{
					BX.addClass(parent, this.cssClass);
				}

				if (this.arStyle)
				{
					for (i in this.arStyle)
					{
						if (this.arStyle.hasOwnProperty(i))
						{
							parent.style[i] = this.arStyle[i];
						}
					}
				}
			}
			else
			{
				if (parent.childNodes.length == 1) // container over the text node
				{
					if (this.cssClass && BX.hasClass(parent, this.cssClass))
					{
						BX.removeClass(parent, this.cssClass);
					}

					if (this.arStyle)
					{
						for (i in this.arStyle)
						{
							if (this.arStyle.hasOwnProperty(i) && parent.style[i])
							{
								parent.style[i] = '';
							}
						}
					}
				}

				var el = this.CreateContainer();
				textNode.parentNode.insertBefore(el, textNode);
				el.appendChild(textNode);
			}
		},

		IsRemovable: function(el)
		{
			return rangy.dom.arrayContains(this.tagNames, el.tagName.toLowerCase()) && BX.util.trim(el.className) == this.cssClass;
		},

		IsAvailableTextNodeParent: function(node)
		{
			return node && node.nodeName &&
				node.nodeName !== 'OL' && node.nodeName !== 'UL' && node.nodeName !== 'MENU' && // LLIST
				node.nodeName !== 'TBODY' && node.nodeName !== 'TFOOT' && node.nodeName !== 'THEAD' && node.nodeName !== 'TABLE' && // TABLE
				node.nodeName !== 'DL';
		},

		UndoToTextNode: function(textNode, range, styledParent)
		{
			if (!range.containsNode(styledParent))
			{
				// Split out the portion of the parent from which we can remove the CSS class
				var parentRange = range.cloneRange();
				parentRange.selectNode(styledParent);


				BX.isParentForNode(styledParent, range.endContainer)
				if (
					range.endContainer.nodeName !== 'BODY' &&
					parentRange.isPointInRange(range.endContainer, range.endOffset) &&
					this.editor.util.IsSplitPoint(range.endContainer, range.endOffset) &&
					BX.isParentForNode(styledParent, range.endContainer)
				)
				{
					this.editor.util.SplitNodeAt(styledParent, range.endContainer, range.endOffset);
					range.setEndAfter(styledParent);
				}

				if (
					range.startContainer.nodeName !== 'BODY' &&
					parentRange.isPointInRange(range.startContainer, range.startOffset) &&
					this.editor.util.IsSplitPoint(range.startContainer, range.startOffset) &&
					BX.isParentForNode(styledParent, range.startContainer)
					)
				{
					styledParent = this.editor.util.SplitNodeAt(styledParent, range.startContainer, range.startOffset);
				}
			}

			if (styledParent && styledParent.nodeName != 'BODY' && this.IsRemovable(styledParent))
			{
				if (this.arStyle)
				{
					for (var i in this.arStyle)
					{
						if (this.arStyle.hasOwnProperty(i) && styledParent.style[i])
						{
							styledParent.style[i] = '';
						}
					}
				}

				if (!styledParent.style.cssText || BX.util.trim(styledParent.style.cssText) === '')
				{
					this.editor.util.ReplaceWithOwnChildren(styledParent);
				}
				else if (this.tagNames.length > 1 || this.tagNames[0] !== 'span')
				{
					this.editor.util.RenameNode(styledParent, "span");
				}
			}
		},

		ApplyToRange: function(range)
		{
			var textNodes = range.getNodes([3]);

			if (!textNodes.length)
			{
				try {
					var node = this.CreateContainer();
					range.surroundContents(node);
					range = this.NormalizeNewNode(node, range);
					this.SelectNode(range, node);
					return range;
				} catch(e) {}
			}

			range.splitBoundaries();
			textNodes = range.getNodes([3]);

			if (!textNodes.length && range.collapsed && range.startContainer == range.endContainer)
			{
				var inv = this.editor.util.GetInvisibleTextNode();
				this.editor.selection.InsertNode(inv);
				textNodes = [inv];
			}

			if (textNodes.length)
			{
				var textNode;

				for (var i = 0, len = textNodes.length; i < len; ++i)
				{
					textNode = textNodes[i];
					if (!this.GetStyledParent(textNode) && this.IsAvailableTextNodeParent(textNode.parentNode))
					{
						this.ApplyToTextNode(textNode);
					}
				}

				range.setStart(textNodes[0], 0);
				textNode = textNodes[textNodes.length - 1];
				range.setEnd(textNode, textNode.length);

				if (this.normalize)
				{
					this.PostApply(textNodes, range);
				}
			}
			return range;
		},

		UndoToRange: function(range, bMatchCss)
		{
			var
				textNodes = range.getNodes([3]),
				textNode,
				styledParent;

			bMatchCss = bMatchCss !== false;

			if (textNodes.length)
			{
				range.splitBoundaries();
				textNodes = range.getNodes([3]);
			}
			else
			{
				var node = this.editor.util.GetInvisibleTextNode();
				range.insertNode(node);
				range.selectNode(node);
				textNodes = [node];
			}

			var i, len, sorted = [];
			for (i = 0, len = textNodes.length; i < len; i++)
			{
				sorted.push({node: textNodes[i], nesting: this.GetNodeNesting(textNodes[i])});
			}

			sorted = sorted.sort(function(a, b){return b.nesting - a.nesting});

			for (i = 0, len = sorted.length; i < len; i++)
			{
				textNode = sorted[i].node;
				styledParent = this.GetStyledParent(textNode, bMatchCss);
				if (styledParent)
				{
					this.UndoToTextNode(textNode, range, styledParent);
					range = this.editor.selection.GetRange();
				}
			}

			if (len == 1)
			{
				this.SelectNode(range, textNodes[0]);
			}
			else
			{
				range.setStart(textNodes[0], 0);
				range.setEnd(textNodes[textNodes.length - 1], textNodes[textNodes.length - 1].length);
				this.editor.selection.SetSelection(range);

				if (this.normalize)
				{
					this.PostApply(textNodes, range);
				}
			}

			return range;
		},

		// Node dom offset
		GetNodeNesting: function(node)
		{
			return this.editor.util.GetNodeDomOffset(node);
		},

		SelectNode: function(range, node)
		{
			var
				isElement = node.nodeType === 1,
				canHaveHTML = "canHaveHTML" in node ? node.canHaveHTML : true,
				content = isElement ? node.innerHTML : node.data,
				isEmpty = (content === "" || content === this.editor.INVISIBLE_SPACE);

			if (isEmpty && isElement && canHaveHTML)
			{
				// Make sure that caret is visible in node by inserting a zero width no breaking space
				try {node.innerHTML = this.editor.INVISIBLE_SPACE;} catch(e) {}
			}

			range.selectNodeContents(node);
			if (isEmpty && isElement)
			{
				range.collapse(false);
			}
			else if (isEmpty)
			{
				range.setStartAfter(node);
				range.setEndAfter(node);
			}
		},

		GetTextSelectedByRange: function(textNode, range)
		{
			var textRange = range.cloneRange();
			textRange.selectNodeContents(textNode);

			var intersectionRange = textRange.intersection(range);
			var text = intersectionRange ? intersectionRange.toString() : "";
			textRange.detach();

			return text;
		},

		IsAppliedToRange: function(range, bMatchCss)
		{
			var
				parents = [],
				parent,
				textNodes = range.getNodes([3]);
			bMatchCss = bMatchCss !== false;

			if (!textNodes.length)
			{
				parent = this.GetStyledParent(range.startContainer, bMatchCss);
				return parent ? [parent] : false;
			}

			var i, selectedText;
			for (i = 0; i < textNodes.length; ++i)
			{
				selectedText = this.GetTextSelectedByRange(textNodes[i], range);
				parent = this.GetStyledParent(textNodes[i], bMatchCss);
				if (selectedText != "" && !parent)
				{
					return false;
				}
				else
				{
					parents.push(parent);
				}
			}
			return parents;
		},

		ToggleRange: function(range)
		{
			return this.IsAppliedToRange(range) ? this.UndoToRange(range) : this.ApplyToRange(range);
		}
	};

	function BXEditorUndoManager(editor)
	{
		this.editor = editor;
		this.history = [this.editor.iframeView.GetValue()];
		this.position = 1;
		this.document = editor.sandbox.GetDocument();
		this.historyLength = 30;
		BX.addCustomEvent(this.editor, 'OnIframeReInit', BX.proxy(function(){this.document = this.editor.sandbox.GetDocument();}, this));
		this.Init();
	}

	BXEditorUndoManager.prototype = {
		Init: function()
		{
			var _this = this;
			BX.addCustomEvent(this.editor, "OnHtmlContentChangedByControl", BX.delegate(this.Transact, this));
			BX.addCustomEvent(this.editor, "OnIframeNewWord", BX.delegate(this.Transact, this));
			BX.addCustomEvent(this.editor, "OnIframeKeyup", BX.delegate(this.Transact, this));
			BX.addCustomEvent(this.editor, "OnBeforeCommandExec", function(isContentAction)
			{
				if (isContentAction)
				{
					_this.Transact();
				}
			});

			// CTRL+Z and CTRL+Y and handle DEL & BACKSPACE
			BX.addCustomEvent(this.editor, "OnIframeKeydown", BX.proxy(this.Keydown, this));
		},

		Keydown: function(e, keyCode, command, selectedNode)
		{
			if ((e.ctrlKey || e.metaKey) && !e.altKey)
			{
				var
					isUndo = keyCode === this.editor.KEY_CODES['z'] && !e.shiftKey,
					isRedo = (keyCode === this.editor.KEY_CODES['z'] && e.shiftKey) || (keyCode === this.editor.KEY_CODES['y']);

				if (isUndo)
				{
					this.Undo();
					return BX.PreventDefault(e);
				}
				else if (isRedo)
				{
					this.Redo();
					return BX.PreventDefault(e);
				}
			}

			if (keyCode !== this.lastKey)
			{
				if (keyCode === this.editor.KEY_CODES['backspace'] || keyCode === this.editor.KEY_CODES['delete'])
				{
					this.Transact();
				}
				this.lastKey = keyCode;
			}
		},

		Transact: function()
		{
			var
				previousHtml = this.history[this.position - 1],
				currentHtml = this.editor.iframeView.GetValue();

			if (currentHtml !== previousHtml)
			{
				var length = this.history.length = this.position;
				if (length > this.historyLength)
				{
					this.history.shift();
					this.position--;
				}

				this.position++;
				this.history.push(currentHtml);

				this.CheckControls();
			}
		},

		Undo: function()
		{
			if (this.position > 1)
			{
				this.Transact();
				this.position--;
				this.Set(this.history[this.position - 1]);
				this.editor.On("OnUndo");
				this.CheckControls();
			}
		},

		Redo: function()
		{
			if (this.position < this.history.length)
			{
				this.position++;
				this.Set(this.history[this.position - 1]);
				this.editor.On("OnRedo");
				this.CheckControls();
			}
		},

		Set: function(html)
		{
			this.editor.iframeView.SetValue(html);
			this.editor.Focus(true);
		},

		CheckControls: function()
		{
			this.editor.On("OnEnableUndo", [this.position > 1]);
			this.editor.On("OnEnableRedo", [this.position < this.history.length]);
		}
	};

	function BXStyles(editor)
	{
		this.editor = editor;
		this.arStyles = {};
		this.sStyles = '';
	}

	BXStyles.prototype = {
		GetIframe: function(styles)
		{
			if (!this.cssIframe)
			{
				this.cssIframe = this.CreateIframe(styles);
			}
			return this.cssIframe;
		},

		CreateIframe: function(styles)
		{
			this.cssIframe = document.body.appendChild(BX.create("IFRAME", {props: {className: "bx-editor-css-iframe"}}));
			this.iframeDocument = this.cssIframe.contentDocument || this.cssIframe.contentWindow.document;
			this.iframeDocument.open("text/html", "replace");
			this.iframeDocument.write('<!DOCTYPE html><html><head><style type="text/css" data-bx-template-style="Y">' + styles + '</style></head><body></body></html>');
			this.iframeDocument.close();
			return this.cssIframe;
		},

		GetCSS: function(templateId, styles, templatePath, filter)
		{
			if (!this.arStyles[templateId])
			{
				if (!this.cssIframe)
				{
					this.cssIframe = this.CreateIframe(styles);
				}
				else
				{
					var
						i,
						doc = this.iframeDocument,
						head = doc.head || doc.getElementsByTagName('HEAD')[0],
						styleNodes = head.getElementsByTagName('STYLE');

					// Clean old template styles
					for (i = 0; i < styleNodes.length; i++)
					{
						if (styleNodes[i].getAttribute('data-bx-template-style') == 'Y')
							BX.cleanNode(styleNodes[i], true);
					}

					// Add new node in the iframe head
					if (styles)
					{
						head.appendChild(BX.create('STYLE', {props: {type: 'text/css'}, text: styles}, doc)).setAttribute('data-bx-template-style', 'Y');
					}
				}
				this.arStyles[templateId] = this.ParseCss();
			}

			var res = this.arStyles[templateId];

			if (filter)
			{
				var filteredRes = [], tag;
				if (typeof filter != 'object' )
				{
					filter = [filter];
				}
				filter.push('DEFAULT');
				for (i = 0; i < filter.length; i++)
				{
					tag = filter[i];
					if (res[tag] && typeof res[tag] == 'object')
					{
						filteredRes = filteredRes.concat(res[tag]);
					}
				}
				res = filteredRes;
			}

			return res;
		},

		ParseCss: function()
		{
			var
				doc = this.iframeDocument,
				arAllSt = [],
				result = {},
				rules,
				cssTag, arTags, i, j, k,
				t1, t2, l1, l2, l3;

			if(!doc.styleSheets)
			{
				return result;
			}

			var
				x1 = doc.styleSheets,
				stylesDescription = this.editor.GetStylesDescription();

			for(i = 0, l1 = x1.length; i < l1; i++)
			{
				rules = (x1[i].rules ? x1[i].rules : x1[i].cssRules);
				for(j = 0, l2 = rules.length; j < l2; j++)
				{
					if (rules[j].type != rules[j].STYLE_RULE)
					{
						continue;
					}

					cssTag = rules[j].selectorText;
					arTags = cssTag.split(",");
					for(k = 0, l3 = arTags.length; k < l3; k++)
					{
						t1 = arTags[k].split(" ");
						t1 = t1[t1.length - 1].trim();

						if(t1.substr(0, 1) == '.')
						{
							t1 = t1.substr(1);
							t2 = 'DEFAULT';
						}
						else
						{
							t2 = t1.split(".");
							t1 = (t2.length > 1) ? t2[1] : '';
							t2 = t2[0].toUpperCase();
						}

						if(!arAllSt[t1])
						{
							arAllSt[t1] = true;
							if(!result[t2])
							{
								result[t2] = [];
							}

							result[t2].push({
								className: t1,
								classTitle: stylesDescription[t1] || null,
								original: arTags[k],
								cssText: rules[j].style.cssText
							});
						}
					}
				}
			}
			return result;
		}
	};


	// Parse rules
	/**
	 * Full HTML5 compatibility rule set
	 * These rules define which tags and css classes are supported and which tags should be specially treated.
	 *
	 * Examples based on this rule set:
	 *
	 * <a href="http://foobar.com">foo</a>
	 * ... becomes ...
	 * <a href="http://foobar.com" target="_blank" rel="nofollow">foo</a>
	 *
	 * <img align="left" src="http://foobar.com/image.png">
	 * ... becomes ...
	 * <img class="wysiwyg-float-left" src="http://foobar.com/image.png" alt="">
	 *
	 * <div>foo<script>alert(document.cookie)</script></div>
	 * ... becomes ...
	 * <div>foo</div>
	 *
	 * <marquee>foo</marquee>
	 * ... becomes ...
	 * <span>foo</marquee>
	 *
	 * foo <br clear="both"> bar
	 * ... becomes ...
	 * foo <br class="wysiwyg-clear-both"> bar
	 *
	 * <div>hello <iframe src="http://google.com"></iframe></div>
	 * ... becomes ...
	 * <div>hello </div>
	 *
	 * <center>hello</center>
	 * ... becomes ...
	 * <div class="wysiwyg-text-align-center">hello</div>
	 */
	__BXHtmlEditorParserRules = {
		/**
		 * CSS Class white-list
		 * Following css classes won't be removed when parsed by the parser
		 */
		classes: {},

		"tags": {
			"b": {clean_empty: true},
			"strong": {clean_empty: true},
			"i": {clean_empty: true},
			"em": {clean_empty: true},
			"u": {clean_empty: true},
			"del": {clean_empty: true},
			"s": {rename_tag: "del"},
			"strike": {rename_tag: "del"},

			// headers
			"h1": {},
			"h2": {},
			"h3": {},
			"h4": {},
			"h5": {},
			"h6": {},

			// popular tags
			"span": {clean_empty: true},
			"p": {},
			"br": {},
			"div": {},
			"hr": {},
			"nobr": {},
			"code": {},
			"section": {},
			"figure": {},
			"figcaption": {},
			"fieldset": {},
			"legend": {},

			// Lists
			"menu": {rename_tag: "ul"}, // ??
			"ol": {},
			"ul": {},
			"li": {},
			"pre": {},

			// Table
			"table": {},
			"tr": {},
			"td": {
				"check_attributes": {
					"rowspan": "numbers",
					"colspan": "numbers"
				}
			},
			"tbody": {},
			"tfoot": {},
			"thead": {},
			"th": {
				"check_attributes": {
					"rowspan": "numbers",
					"colspan": "numbers"
				}
			},
			"caption": {},
			"col": {},
			"colgroup": {},

			// Definitions //  <dl>, <dt>, <dd>
			"dl": {rename_tag: ""},
			"dd": {rename_tag: ""},
			"dt": {rename_tag: ""},

			"iframe": {},
			"noindex": {},

			"font": {
				rename_tag: "span",
				convert_attributes: {
					face: 'fontFamily',
					size: 'fontSize',
					color: 'color'
				},
				replace_with_children: 1
			},

			"embed": {},
			"noembed": {},
			"object": {},
			"param": {},

			"sup": {},
			"sub": {},

			"address": {},
			"nav": {},
			"aside": {},
			"article": {},
			"main": {},
			"acronym": {},
			"abbr": {},
			"label": {},
			"time": {},

			"small": {},
			"big": {},

			"details": {},
			"summary": {},
			"footer": {},

			"video": {},
			"source": {},
			"audio": {},

			// tags to remove
			"title": {remove: 1},
			"area": {remove: 1},
			"command": {remove: 1},
			"noframes": {remove: 1},
			"bgsound": {remove: 1},
			"basefont": {remove: 1},
			"head": {remove: 1},
			"track": {remove: 1},
			"wbr": {remove: 1},
			"noscript": {remove: 1},
			"svg": {remove: 1},
			"keygen": {remove: 1},
			"meta": {remove: 1},
			"isindex": {remove: 1},
			"base": {remove: 1},
			"canvas": {remove: 1},
			"applet": {remove: 1},
			"spacer": {remove: 1},
			"frame": {remove: 1},
			"style": {remove: 1},
			"device": {remove: 1},
			"xml": {remove: 1},
			"nextid": {remove: 1},
			"link": {remove: 1},
			"script": {remove: 1},
			"comment": {remove: 1},
			"frameset": {remove: 1},

			// Tags to rename
			// to DIV
			"multicol": {rename_tag: "div"},
			"map": {rename_tag: "div"},
			"body": {rename_tag: "div"},
			"html": {rename_tag: "div"},
			"hgroup": {rename_tag: "div"},
			"listing": {rename_tag: "div"},
			"header": {rename_tag: "div"},
			// to SPAN
			"rt": {rename_tag: "span"},
			"xmp": {rename_tag: "span"},
			"bdi": {rename_tag: "span"},
			"progress": {rename_tag: "span"},
			"dfn": {rename_tag: "span"},
			"rb": {rename_tag: "span"},
			"mark": {rename_tag: "span"},
			"output": {rename_tag: "span"},
			"marquee": {rename_tag: "span"},
			"rp": {rename_tag: "span"},

			"var": {rename_tag: "span"},
			"tt": {rename_tag: "span"},
			"blink": {rename_tag: "span"},
			"plaintext": {rename_tag: "span"},
			"kbd": {rename_tag: "span"},
			"meter": {rename_tag: "span"},
			"datalist": {rename_tag: "span"},
			"samp": {rename_tag: "span"},
			"bdo": {rename_tag: "span"},
			"ruby": {rename_tag: "span"},
			"ins": {rename_tag: "span"},
			"optgroup": {rename_tag: "span"},

			// Form elements
			"form": {},
			"option": {},
			"select": {},
			"textarea": {},
			"button": {},
			"input": {},

			"dir": {rename_tag: "ul"},
			"a": {},
			"img": {
				"check_attributes": {
				"width": "numbers",
				"alt": "alt",
				"src": "url",
				"height": "numbers"
				},
				"add_class": {
				"align": "align_img"
				}
			},
			"q": {
				"check_attributes": {
				"cite": "url"
				}
			},
			"blockquote": {
				"check_attributes": {
				"cite": "url"
				}
			},
			"center": {
				rename_tag: "div",
				add_css:
				{
					textAlign : 'center'
				}
			},
			"cite": {}
		}
	};

})(window);
