(function ()
{
	window.JCCodeEditor = function(arConfig, MESS)
	{
		this.actionCount = 0;
		this.arConfig = arConfig;
		this.MESS = MESS;
		this.arSyntaxes = {};
		this.pTA = BX(this.arConfig.textareaId);
		if (!this.pTA || !BX.isNodeInDom(this.pTA))
			return false;
		this.saveSettings = !!arConfig.saveSettings;
		this.highlightMode = !!arConfig.highlightMode && this.IsValidBrowser();
		this.theme = arConfig.theme;
		this.tabSize = 4;
		this.undoDepth = 30;
		this.timeout = 100;
		this.delay = 200;
		this.pollInterval = 100;
		this.cursorInterval = 500;
		this.indentUnit = 4;
		this._cachedWidthFor = 0;
		this._cachedWidth = 0;
		this.prevInput = "";
		this._cachedHeight = null;
		this._cachedHeightFor = null;
		this._measurePre = null;
		this.lineNums = 0;
		this.tabSymTA = "        "; // 8 spaces;
		this.arBrackets = {
			"(": ")>",
			")": "(<",
			"[": "]>",
			"]": "[<",
			"{": "}>",
			"}": "{<"
		};

		this.Init();
	};

	window.JCCodeEditor.prototype = {
		Init: function()
		{
			var _this = this;

			if (this.pTA.parentNode.offsetWidth <= 0 || this.pTA.parentNode.offsetHeight <= 0)
			{
				return setTimeout(function(){_this.Init();}, 100);
			}

			if (this.arConfig.forceSyntax && {'php': 1, 'js': 1, 'css': 1, 'sql': 1}[this.arConfig.forceSyntax])
			{
				this.syntaxName = this.arConfig.forceSyntax;
			}
			else
			{
				this.syntaxName = 'php'; // php | js | css | sql
				this.arConfig.forceSyntax = false;
			}

			this.InitKeyEngine();
			this.BuildSceleton();

			if (BX.browser.IsIOS())
				this.pInput.style.width = "0px";

			if (!BX.browser.IsSafari() && !BX.browser.IsChrome())
				this.pScroller.draggable = true;

			this.pLinesCont.style.outline = "none";
			this.FocusInput();

			if (BX.browser.IsMac())
			{
				this.pScrollbar.style.zIndex = -2;
				this.pScrollbar.style.visibility = "hidden";
			}
			else if (BX.browser.IsIE() && !BX.browser.IsIE9())
			{
				this.pScrollbar.style.minWidth = "18px";
			}

			try
			{
				this.GetCharWidth();
			}
			catch (e)
			{
				return;
			}

			// Delayed object wrap timeouts, making sure only one is active. blinker holds an interval.
			this.pollDelayed = new Delayed();
			this.highlight = new Delayed();

			this.oDoc = new JCDocHolder([new JCLineHolder([new JCLine("")])]);
			this.focused = false;
			this.LoadSyntax();

			this.oSel = {
				from: {line: 0, ch: 0},
				to: {line: 0, ch: 0},
				inverted: false
			};

			this.lastClick = false;
			this.lastDoubleClick = false;
			this._lastScrollTop = 0;
			this._lastStoppedKey = null;
			this.suppressEdits = false;

			this.callbacks = null;
			this.arChanges = [];

			this.displayOffset = 0;
			this.showingFrom = 0;
			this.showingTo = 0;
			this.lastSizeC = 0;
			this.bracketHighlighted = null;
			this.maxLine = this.GetLine(0);
			this.updateMaxLine = false;
			this.maxLineChanged = true;
			this._tabCache = {};
			this.pollingFast = false;
			this.goalColumn = null;

			// Initialize the content.
			this.Action(this.SetValue, this)(this.pTA.value || "");
			this.updateInput = false;

			this.oHistory = new History();

			// Register our event handlers.
			BX.bind(this.pScroller, "mousedown", this.Action(this.OnMouseDown, this));
			BX.bind(this.pScroller, "dblclick", this.Action(this.OnDoubleClick, this));
			//BX.bind(this.pLinesCont, "selectstart", preventDefault);
			BX.bind(this.pLinesCont, "selectstart", BX.PreventDefault);
			BX.bind(this.pScroller, "scroll", BX.proxy(this.OnScrollMain, this));
			BX.bind(this.pScrollbar, "scroll", BX.proxy(this.OnScrollBar, this));
			BX.bind(this.pScrollbar, "mousedown", function ()
			{
				if (_this.highlightMode && _this.focused)
					setTimeout(_this.FocusInput, 0);
			});

			BX.bind(this.pInput, "keyup", this.Action(this.OnKeyUp, this));
			BX.bind(this.pInput, "input", BX.proxy(this.FastPoll, this));
			BX.bind(this.pInput, "keydown", this.Action(this.OnKeyDown, this));
			BX.bind(this.pInput, "keypress", this.Action(this.OnKeyPress, this));
			BX.bind(this.pInput, "focus", BX.proxy(this.OnFocus, this));
			BX.bind(this.pInput, "blur", BX.proxy(this.OnBlur, this));

			BX.bind(this.pScroller, "paste", function ()
			{
				_this.FocusInput();
				_this.FastPoll();
			});
			BX.bind(this.pInput, "paste", BX.proxy(this.FastPoll, this));
			BX.bind(this.pInput, "cut", this.Action(function(){_this.ReplaceSelection("");}, this));

			setTimeout(BX.proxy(this.OnFocus,this), 20);

			if (this.theme != 'dark')
			{
				this.theme = 'dark';
				this.SwitchTheme();
			}

			if (!this.highlightMode)
			{
				this.highlightMode = true;
				this.SwitchHightlightMode();
			}

			// Autosave handlers
			var pForm = this.pTA.form;
			if (pForm)
			{
				BX.addCustomEvent(pForm, 'onAutoSavePrepare', function(){
					if (pForm && pForm.BXAUTOSAVE)
					{
						try{
							BX.addCustomEvent(this, 'OnAfterActionSelectionChanged', function(){
								pForm.BXAUTOSAVE.Init();
							});

							BX.addCustomEvent(pForm, 'onAutoSave', function (ob, data)
							{
								if (_this.highlightMode)
									_this.Save();
								data[_this.pTA.name] = _this.GetTAValue();
							});

							BX.addCustomEvent(pForm, 'onAutoSaveRestore', function (ob, data)
							{
								if (_this.highlightMode)
								{
									_this.Action(_this.SetValue, _this)(data[_this.pTA.name]);
								}
								else
								{
									_this.pTA.value = data[_this.pTA.name];
									_this.CheckLineSelection();
								}
							});
						}catch(e){}
					}
				});

				BX.bind(pForm, "reset", function()
				{
					if (_this.highlightMode)
					{
						_this.Action(_this.SetValue, _this)("");
						_this.FocusInput();
						_this.OnFocus();
					}
				});
			}
		},

		BuildSceleton: function()
		{
			var _this = this;
			this.pDiv = this.pTA.parentNode.appendChild(BX.create("DIV", {props:{className: 'bxce bxce-hls'}}));
			this.pWarning = this.pDiv.appendChild(BX.create("DIV", {props:{className: 'bxce-warning-cont'}, text: this.MESS.HighlightWrongwarning, style:{display: 'none'}}));
			this.pInnerContHL = this.pDiv.appendChild(BX.create("DIV", {props:{className: "bxce-inner-hl"}}));

			// For textarea MODE
			this.pBaseContTA = this.pDiv.appendChild(BX.create("DIV", {props:{className: 'bxce-base-cont'}, style: {display: 'none'}}));
			this.pBaseContTA.onclick = function(e){BX.focus(_this.pTA);};


			// Relative div - contains textarea, line numbers, - ONLY for switch off hightlighting mode
			this.pContTA = this.pBaseContTA.appendChild(BX.create("DIV", {props: {className: 'bxce-cont'}}));
			this.pLineNumTABgTA = this.pBaseContTA.appendChild(BX.create("DIV", {props: {className: 'bxce-line-num-bg'}}));
			// Line numbers
			this.pLineNumTA = this.pContTA.appendChild(BX.create("DIV", {props: {className: 'bxce-line-num bxce-font'}}));
			this.pContTA.appendChild(this.pTA);
			this.pTA.className = 'bxce-ta';
			this.pTA.style.display = "none";
			this.pTA.removeAttribute("cols");
			this.pTA.removeAttribute("rows");
			this.pTA.setAttribute("spellcheck", false);
			this.pTA.setAttribute("hidefocus", true);
			this.pTA.setAttribute("autocomplete", "off");
			this.pTA.setAttribute("autocorrect", "off");
			this.pTA.setAttribute("autocapitalize", "off");
			this.pTA.setAttribute('wrap', "off");
			this.pTA.onkeyup = BX.proxy(this.TextOnKeyup, this);
			this.pTA.onkeydown = BX.proxy(this.TextOnKeydown, this);
			this.pTA.onmousedown = BX.proxy(this.TextOnMousedown, this);

			this.pInputCont = this.pInnerContHL.appendChild(BX.create("DIV", {props:{className: "bxce-inp-cont"}}));
			this.pInput = this.pInputCont.appendChild(BX.create("TEXTAREA", {props:{className: "bxce-inp"}}));
			this.pInput.setAttribute("wrap", "off");
			this.pInput.setAttribute("autocorrect", "off");
			this.pInput.setAttribute("autocapitalize", "off");

			this.pScrollbar = this.pInnerContHL.appendChild(BX.create("DIV", {props:{className: "bxce-scrollbar"}}));
			this.pScrollbarInner = this.pScrollbar.appendChild(BX.create("DIV", {props:{className: "bxce-scrollbar-inner"}})); // scrollbarInner

			this.pScroller = this.pInnerContHL.appendChild(BX.create("DIV", {props:{className: "bxce-scroller", tabIndex: -1}}));
			this.pSizeCont = this.pScroller.appendChild(BX.create("DIV", {props:{className: "bxce-size-cont", tabIndex: -1}}));
			this.pMover = this.pSizeCont.appendChild(BX.create("DIV", {props:{className: "bxce-inner-size-cont"}}));
			this.pLineNum = this.pMover.appendChild(BX.create("DIV", {props:{className: "bxce-hl-line-num"}}));


			this.pLineNumText = this.pLineNum.appendChild(BX.create("DIV", {props:{className: "bxce-hl-line-num-text"}}));
			this.pHighlight = this.pMover.appendChild(BX.create("DIV", {props:{className: "bxce-highlight"}}));
			this.pLinesCont = this.pHighlight.appendChild(BX.create("DIV", {props:{className: "bxce-lines-cnt"}}));
			this.pMeasure = this.pLinesCont.appendChild(BX.create("DIV", {props:{className: "bxce-liner-cont"}}));
			this.pCursor = this.pLinesCont.appendChild(BX.create("PRE", {props:{className: "bxce-cursor"}, text: "\u00a0"}));
			this.pWidthForcer = this.pLinesCont.appendChild(BX.create("PRE", {props:{className: "bxce-cursor"}, style: {visibility: 'hidden'}, text: "\u00a0"}));
			this.pSelectionDiv = this.pLinesCont.appendChild(BX.create("DIV", {props:{className: "bxce-sel-cont"}}));
			this.pLineDiv = this.pLinesCont.appendChild(BX.create("DIV"));

			// Foot cont
			this.pFootCont = this.pDiv.appendChild(BX.create("DIV", {props:{className: 'bxce-foot-cont'}}));
			this.pFastLineInp = this.pFootCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-go-to-line-cont'}})).appendChild(BX.create("INPUT", {props:{className: 'bxce-go-to-line', title: this.MESS.GoToLine}}));

			this.pFastLineInp.onfocus = function(){setTimeout(function(){_this.pFastLineInp.select();}, 10);};
			this.pFootCont.onclick = function(){setTimeout(function(){_this.pFastLineInp.select();}, 10);};
			this.pFastLineInp.onkeydown = BX.proxy(this.FastGoToLine, this);

			this.pCurPosCont = this.pFootCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-cur-cont'}, html: this.MESS.Line + ":"}));
			this.pInfoCurLine = this.pCurPosCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-cur-line', title: this.MESS.LineTitle}}));
			this.pCurPosCont.appendChild(document.createTextNode(this.MESS.Char + ":"));
			this.pInfoCurChar = this.pCurPosCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-cur-char', title: this.MESS.CharTitle}}));

			this.pTotalCont = this.pFootCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-total-cont'}, html: this.MESS.Total + "  " + this.MESS.Lines + ":"}));
			this.pInfoTotLines = this.pTotalCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-total-line'}}));

			// Mode toggle
			this.pModeToggle = this.pFootCont.appendChild(BX.create("A", {props:{href: 'javascript:void(0)',className: 'bxce-mode-link' + (this.highlightMode ? ' bxce-mode-link-on' : ''), title: this.MESS.EnableHighlightTitle}, html: '<span class="bxce-mode-txt">' + this.MESS.EnableHighlight + '</span><i></i>'}));
			this.pModeToggle.onclick = BX.proxy(this.SwitchHightlightMode, this);

			// Theme toggle
			this.pThemeToggle = this.pFootCont.appendChild(BX.create("A", {props:{href: 'javascript:void(0)',className: 'bxce-theme-toggle'}, html: '<i></i><span class="bxce-theme-toggle-text">' + this.MESS.LightTheme + '</span>'}));
			this.pThemeToggle.onclick = BX.proxy(this.SwitchTheme, this);
			this.pThemeToggleText = this.pThemeToggle.childNodes[1];//

			this.AdjustSceletonSize();
		},

		Action: function(func, obj)
		{
			var _this = this;
			if (obj == undefined)
				obj = this;

			return function ()
			{
				if (!_this.actionCount++)
					_this.OnBeforeAction();

				try
				{
					var res = func.apply(obj, arguments);
				}
				finally
				{
					if (!--_this.actionCount)
						_this.OnAfterAction();
				}

				return res;
			};
		},

		OnBeforeAction: function()
		{
			BX.onCustomEvent(this, "OnBeforeAction");
			this.updateInput = null;
			this.userSelChange = null;
			this.textChanged = null;
			this.changes = [];
			this.arChanges = [];
			this.selectionChanged = false;
			this.callbacks = [];
		},

		OnAfterAction: function()
		{
			if (this.updateMaxLine)
				this.ComputeMaxLength();

			if (this.maxLineChanged)
			{
				var
					cursorWidth = this.pWidthForcer.offsetWidth,
					left = this.MeasureLine(this.maxLine, this.maxLine.text.length).left;

				if (!BX.browser.IsIE() || BX.browser.IsIE9())
				{
					this.pWidthForcer.style.left = left + "px";
					this.pLinesCont.style.minWidth = (left + cursorWidth) + "px";
				}
				this.maxLineChanged = false;
			}

			var newScrollPos, updated;
			if (this.selectionChanged)
			{
				var coords = this.CalculateCursorCoords();
				newScrollPos = this.CalculateScrollPos(coords.x, coords.y, coords.x, coords.yBot);
			}

			if (this.arChanges.length || newScrollPos && newScrollPos.scrollTop != null)
				updated = this.UpdateDisplay(this.arChanges, true, newScrollPos && newScrollPos.scrollTop);

			if (!updated)
			{
				if (this.selectionChanged)
					this.UpdateSelection();
				if (this.lineNumDirty)
					this.UpdateLineNum();
			}

			if (newScrollPos)
				this.ScrollCursorIntoView();
			if (this.selectionChanged)
				this.RestartBlink();

			if (this.focused && (this.updateInput === true || (this.updateInput !== false && this.selectionChanged)))
				this.ResetInput(this.userSelChange);

			if (this.selectionChanged)
			{
				BX.onCustomEvent(this, "OnAfterActionSelectionChanged");
				setTimeout(this.Action(this.CheckMatchingBrackets, this), 20);
			}

			this.SetStatusBarInfo(this.oSel.to);

			BX.onCustomEvent(this, "OnAfterAction");
		},

		CheckMatchingBrackets: function()
		{
			if (this.bracketHighlighted)
			{
				this.bracketHighlighted();
				this.bracketHighlighted = null;
			}

			if (this.PosEq(this.oSel.from, this.oSel.to))
				this.MatchBrackets();
		},

		MatchBrackets: function()
		{
			var
				style,
				head = this.oSel.inverted ? this.oSel.from : this.oSel.to,
				line = this.GetLine(head.line),
				pos = head.ch - 1;

			var match = (pos >= 0 && this.arBrackets[line.text.charAt(pos)]) || this.arBrackets[line.text.charAt(++pos)];
			if (!match)
				return;

			var
				_this = this,
				off, i,
				ch = match.charAt(0),
				forward = match.charAt(1) == ">",
				d = forward ? 1 : -1,
				st = line.styles;

			for (off = pos + 1, i = 0; i < st.length; i += 2)
			{
				if ((off -= st[i].length) <= 0)
				{
					style = st[i + 1];
					break;
				}
			}

			var stack = [line.text.charAt(pos)], re = /[(){}[\]]/;

			function scan(line, from, to)
			{
				if (!line.text)
					return;
				var st = line.styles, pos = forward ? 0 : line.text.length - 1, cur;
				for (var i = forward ? 0 : st.length - 2, e = forward ? st.length : -2; i != e; i += 2 * d)
				{
					var text = st[i];
					if (st[i + 1] != style)
					{
						pos += d * text.length;
						continue;
					}
					for (var j = forward ? 0 : text.length - 1, te = forward ? text.length : -1; j != te; j += d, pos += d)
					{
						if (pos >= from && pos < to && re.test(cur = text.charAt(j)))
						{
							var match = _this.arBrackets[cur];
							if (match.charAt(1) == ">" == forward)
								stack.push(cur);
							else if (stack.pop() != match.charAt(0))
								return {pos: pos, match: false};
							else if (!stack.length)
								return {pos: pos, match: true};
						}
					}
				}
			}

			var e, first, found;
			for (i = head.line, e = forward ? Math.min(i + 100, this.oDoc.size) : Math.max(-1, i - 100); i != e; i += d)
			{
				line = this.GetLine(i);
				first = i == head.line;
				found = scan(line, first && forward ? pos + 1 : 0, first && !forward ? pos : line.text.length);
				if (found)
					break;
			}

			if (!found)
				found = {pos: null, match: false};


			var
				cn = found.match ? "bxce-hl-bracket" : "bxce-non-hl-bracket",
				firstBracket = this.HightlightFrag({line: head.line, ch: pos}, {line: head.line, ch: pos + 1}, cn),
				secondBracket = found.pos != null && this.HightlightFrag({line: i, ch: found.pos}, {line: i, ch: found.pos + 1}, cn);

			this.bracketHighlighted = this.Action(function ()
			{
				firstBracket.Clear();
				if (secondBracket)
					secondBracket.Clear();
			}, this);
		},

		SetStatusBarInfo: function(sel)
		{
			if (this.highlightMode || sel.nbLine == undefined)
			{
				this.pInfoCurLine.innerHTML = sel.line + 1;
				this.pInfoCurChar.innerHTML = sel.ch;
				this.pInfoTotLines.innerHTML = this.oDoc.size;
				this.pFastLineInp.value = sel.line + 1;
			}
			else
			{
				this.pInfoCurLine.innerHTML = sel.linePos;
				this.pInfoCurChar.innerHTML = sel.carretPos;
				this.pInfoTotLines.innerHTML = sel.nbLine;
				this.pFastLineInp.value = sel.linePos;
			}
		},

		FastGoToLine: function(e)
		{
			if(!e)
				e = window.event;

			var key = e.which || e.keyCode;
			if (key == 13)
			{
				this.GoToLine(this.pFastLineInp.value);
				return BX.PreventDefault(e);
			}
		},

		GoToLine: function(line)
		{
			line = parseInt(line, 10);
			if (isNaN(line))
				return;

			if (this.highlightMode)
			{
				line--;
				if (line < 0)
					line = 0;
				if (line >= this.oDoc.size)
					line = this.oDoc.size - 1;

				this.OnBeforeAction();
				this.SetCursor(line, 0, true);
				this.OnAfterAction();
				setTimeout(BX.proxy(this.FocusInput, this), 20);
			}
			else
			{
				if (line < 0)
					line = 0;

				var
					start = 0, i, dforIe = (BX.browser.IsIE() ? 0 : 1),
					value = this.GetTAValue(),
					lines = value.split("\n"),
					linesCount = lines.length;

				if(line > linesCount)
				{
					start = value.length;
				}
				else
				{
					for(i = 0; i < line - 1; i++)
						start += lines[i].length + dforIe;
				}

				this.SelectTA(start, 0);
				this.MoveLineSelection();

				if (BX.browser.IsChrome() || BX.browser.IsSafari())
				{
					if(this.pLineNumTA.childNodes[line - 1])
						this.pLineNumTA.childNodes[line - 1].scrollIntoView(false);
				}

				return start;
			}
		},

		SelectTA: function(start, len)
		{
			BX.focus(this.pTA);
			var l = this.pTA.value.length;
			if (start < 0)
				start = 0;
			if (start > l)
				start = l;
			var end = start + len;
			if (end > l)
				end = l;

			if(BX.browser.IsIE())
				this.SetIETASelection(start, end);
			else
				this.pTA.setSelectionRange(start, end);
		},

		SetIETASelection: function(start, end)
		{
			var
				val = this.GetTAValue().replace(/\r/g, ""),
				nbLineStart = val.substr(0, start).split("\n").length - 1,
				nbLineEnd = val.substr(0, end).split("\n").length - 1,
				range = document.selection.createRange();

			start += nbLineStart;
			end += nbLineEnd;

			range.moveToElementText(this.pTA);
			range.setEndPoint('EndToStart', range);

			range.moveStart('character', start - nbLineStart);
			range.moveEnd('character', end - nbLineEnd - (start - nbLineStart));
			range.select();
		},

		MoveLineSelection: function(bTimeout)
		{
			if (this.checkLineTimeout)
				this.checkLineTimeout = clearTimeout(this.checkLineTimeout);

			if (bTimeout === true)
				this.checkLineTimeout = setTimeout(BX.proxy(this.MoveLineSelection, this), 5);
			else
				this.CheckLineSelection(false);
		},

		SwitchTheme: function()
		{
			if (this.theme == 'dark')
			{
				this.theme = 'light';
				BX.addClass(this.pDiv, 'bxce--light');
				this.pThemeToggleText.innerHTML = this.MESS.DarkTheme;
			}
			else
			{
				BX.removeClass(this.pDiv, 'bxce--light');
				this.theme = 'dark';
				this.pThemeToggleText.innerHTML = this.MESS.LightTheme;
			}

			if (this.highlightMode)
			{
				if (!this.focused)
				{
					this.FocusInput();
					this.OnFocus();
				}
			}
			else
			{
				BX.focus(this.pTA);
			}

			this.SaveUserOption('theme', this.theme);
		},

		SwitchHightlightMode: function()
		{
			this.highlightMode = !this.highlightMode;

			if (this.__warnTimeout)
				this.__warnTimeout = clearTimeout(this.__warnTimeout);

			if(this.highlightMode && !this.IsValidBrowser())
			{
				var _this = this;
				this.pWarning.style.display = "";
				this.__warnTimeout = setTimeout(function(){_this.pWarning.style.display = "none";}, 4000);
			}
			else
			{
				this.pWarning.style.display = "none";
			}

			if (this.highlightMode)
			{
				this.pInnerContHL.style.display = "";
				this.pBaseContTA.style.display = "none";
				this.pTA.style.display = "none";
				BX.addClass(this.pModeToggle, 'bxce-mode-link-on');
				BX.addClass(this.pDiv, 'bxce-hls');

				if (!BX.browser.IsSafari() && !BX.browser.IsChrome())
					this.pScroller.setAttribute('draggable', true);

				this.Action(this.SetValue, this)(this.pTA.value || "");
				if (!this.focused)
				{
					this.FocusInput();
					this.OnFocus();
				}
			}
			else
			{
				this.Save();

				var
					w = this.pHighlight.offsetWidth - 60,
					h = this.pHighlight.offsetHeight;

				this.pTA.style.width = w + "px";
				this.pContTA.style.width = w + "px";

				this.pTA.style.height = h + "px";

				this.pInnerContHL.style.display = "none";
				this.pTA.style.display = "";
				this.pBaseContTA.style.display = "";
				BX.focus(this.pTA);
				setTimeout(BX.proxy(this.ManageSize, this), 100);

				BX.removeClass(this.pModeToggle, 'bxce-mode-link-on');
				BX.removeClass(this.pDiv, 'bxce-hls');

				if (!BX.browser.IsSafari() && !BX.browser.IsChrome())
					this.pScroller.removeAttribute('draggable');
			}

			this.SaveUserOption('highlight', this.highlightMode ? 1 : 0);
		},

		IsValidBrowser: function()
		{
			return !BX.browser.IsIE() || BX.browser.IsDoctype();
		},

		SaveUserOption: function(option, value)
		{
			if (this.saveSettings)
				BX.userOptions.save('fileman', 'code_editor', option, value);
		},

		SetValue: function(code)
		{
			var top = {line: 0, ch: 0};
			this.UpdateLines(
				top,
				{
					line: this.oDoc.size - 1,
					ch: this.GetLine(this.oDoc.size - 1).text.length
				},
				this.ExplodeLines(code), top, top
			);
			this.updateInput = true;
		},

		GetValue: function(lineSep)
		{
			var text = [];
			this.oDoc.Iteration(0, this.oDoc.size, function(line){text.push(line.text);});
			return text.join(lineSep || "\n");
		},

		GetTAValue: function()
		{
			return this.pTA.value;
		},

		SetTAValue: function(value)
		{
			if (value == undefined)
				value = this.GetTAValue();
			else
				value = value.replace(/\r/g, "");

			this.pTA.value = value;
		},

		Save: function()
		{
			this.pTA.value = this.GetValue();
		},

		AdjustSceletonSize: function(w, h)
		{
			var
				_this = this,
				w_ = w,
				h_ = h;

			if (!w || !h || w <= 0 || h <= 0)
			{
				if (this.arConfig.width)
					w = parseInt(this.arConfig.width);
				if (this.arConfig.height)
					h = parseInt(this.arConfig.height);

				if (this.pDiv.parentNode && !w)
					w = parseInt(this.pDiv.parentNode.offsetWidth) - (BX.browser.IsIE() ? 10 : 2);
				if (this.pDiv.parentNode && !h)
					h = parseInt(this.pDiv.parentNode.offsetHeight) - 2;

				if (!w || isNaN(w) || h < 100)
					w = 900;
				if (!h || isNaN(h) || h < 100)
					h = 400;
			}

			if (w <= 0 || h <= 0)
			{
				return setTimeout(function(){_this.AdjustSceletonSize(w_, h_);}, 300);
			}

			w = parseInt(w);
			h = parseInt(h);
			var
				baseW = w,
				botH = 20,
				baseH = h - botH;

			this.pScroller.style.height = baseH + 'px';
			this.pLineNumTABgTA.style.height = baseH + 'px';
			this.pBaseContTA.style.height = baseH + 'px';
			this.pBaseContTA.style.width = baseW + 'px';

			this.pDiv.style.width = w + 'px';
			this.pDiv.style.height = h + 'px';
		},

		Resize: function(w, h)
		{
			w = parseInt(w);
			h = parseInt(h);
			this.AdjustSceletonSize(w, h);
			this.UpdateDisplay(true);

			if (BX.browser.IsChrome())
			{
				var _this = this;
				setTimeout(function(){_this.UpdateDisplay(true);}, 300);
			}
		},

		HasSelection: (window.getSelection ?
			function (inp)
			{
				var res = false;
				try
				{
					res = inp.selectionStart != inp.selectionEnd;
				}
				catch (e){}
				return res;
			}
			:
			function (inp)
			{
				var res = false;
				try
				{
					res = inp.ownerDocument.selection.createRange();
				}
				catch (e){}

				if (!res || res.parentElement() != inp)
					res = false;
				else if(res)
					res = res.compareEndPoints("StartToEnd", res) != 0;
				return res;
			}
			),

		ExplodeLines: ("\n\nb".split(/\n/).length == 3 ?
			function (s)
			{
				return s.split(/\r\n?|\n/);
			}
			:
			function (s) // Bug in IE with split function
			{
				var
					nl, ln, rt,
					pos = 0,
					res = [],
					l = s.length;

				while (pos <= l)
				{
					nl = s.indexOf("\n", pos);
					if (nl == -1)
						nl = s.length;
					ln = s.slice(pos, s.charAt(nl - 1) == "\r" ? nl - 1 : nl);
					rt = ln.indexOf("\r");
					if (rt != -1)
					{
						res.push(ln.slice(0, rt));
						pos += rt + 1;
					}
					else
					{
						res.push(ln);
						pos = nl + 1;
					}
				}
				return res;
			}
			),

		HightlightFrag: function(from, to, className)
		{
			from = this.ClipPos(from);
			to = this.ClipPos(to);
			var _this = this;

			var oHL = this.GetHighlighter();
			if (!this.PosLess(from, to))
				return oHL;

			function add(line, from, to, className)
			{
				_this.GetLine(line).addMark(new JCHighlightedText(from, to, className, oHL));
			}

			if (from.line == to.line)
			{
				add(from.line, from.ch, to.ch, className);
			}
			else
			{
				add(from.line, from.ch, null, className);
				for (var i = from.line + 1, e = to.line; i < e; ++i)
					add(i, null, null, className);
				add(to.line, null, to.ch, className);
			}
			this.arChanges.push({from: from.line, to: to.line + 1});
			return oHL;
		},

		GetHighlighter: function()
		{
			var oHL = {'set': []};
			oHL.Clear = this.Action(function(){
				var
					i, line, j, lineN,
					min = Infinity,
					max = -Infinity;

				for (i = 0; i < oHL.set.length; ++i)
				{
					line = oHL.set[i];
					if (!line.marked || !line.parent)
						continue;

					lineN = lineNo(line);
					min = Math.min(min, lineN);
					max = Math.max(max, lineN);

					line.marked = [];
				}

				if (min != Infinity)
					this.arChanges.push({from: min, to: max + 1});
			}, this);

			return oHL;
		},

		GetCursor: function (start)
		{
			if (start == undefined)
				start = this.oSel.inverted;
			return this.CopyPos(start ? this.oSel.from : this.oSel.to);
		},

		SetCursor: function(line, ch, user)
		{
			var pos = this.ClipPos({line: line, ch: ch || 0});
			if (user)
				this.SetSelectionUser(pos, pos);
			else
				this.SetSelection(pos, pos);
		},

		ClipLine: function(n)
		{
			return Math.max(0, Math.min(n, this.oDoc.size - 1));
		},

		ClipPos: function(pos)
		{
			var res = pos;

			if (pos.line < 0)
			{
				res = {
					line: 0,
					ch: 0
				};
			}
			else if (pos.line >= this.oDoc.size)
			{

				res = {
					line: this.oDoc.size - 1,
					ch: this.GetLine(_this.oDoc.size - 1).text.length
				};
			}
			else
			{
				var
					ch = pos.ch,
					linelen = this.GetLine(pos.line).text.length;

				if (ch == null || ch > linelen)
					res = {line: pos.line, ch: linelen};
				else if (ch < 0)
					res = {line: pos.line, ch: 0};
			}

			return res;
		},

		GetLine: function(n)
		{
			return this.GetLineAt(this.oDoc, n);
		},

		GetLineAt: function(chunk, n)
		{
			while (!chunk.lines)
			{
				var i, child, size;
				for (i = 0; ; ++i)
				{
					child = chunk.children[i];
					if (!child || !child.GetSize)
						break;

					size = child.GetSize();
					if (n < size)
					{
						chunk = child;
						break;
					}

					n -= size;
				}
			}
			return chunk.lines[n];
		},

		InitSyntax: function(name, syntax)
		{
			if (arguments.length > 2)
			{
				this.arSyntaxControl = [];
				for (var i = 2; i < arguments.length; ++i)
					this.arSyntaxControl.push(arguments[i]);
			}
			this.arSyntaxes[name] = syntax;
		},

		LoadSyntax: function()
		{
			this.oSyntax = Syntaxes[this.syntaxName](!!this.arConfig.forceSyntax);
			this.oDoc.Iteration(0, this.oDoc.size, function (line){line.statusAfter = null;});
			this.work = [0];
			this.StartWorker();
		},

		StartWorker: function(time)
		{
			if (!this.work.length)
				return;

			this.highlight.set(time, this.Action(this.HighlightWorker));
		},

		HighlightWorker: function ()
		{
			var
				_this = this,
				task, start, status,
				unchanged, compare, realChange, i, bail,
				end = +new Date + this.timeout;

			while (this.work.length)
			{
				task = this.GetLine(this.showingFrom).statusAfter ? this.work.pop() : this.showingFrom;

				if (task >= this.oDoc.size)
					continue;

				start = this.FindStartLine(task);
				status = start && this.GetLine(start - 1).statusAfter;

				if (status)
					status = this.OnSyntaxCopyState(this.oSyntax, status);
				else
					status = this.OnSyntaxStartState(this.oSyntax);

				unchanged = 0;
				compare = this.oSyntax.compareStates;
				realChange = false;
				i = start;
				bail = false;

				this.oDoc.Iteration(i, this.oDoc.size, function (line)
				{
					var hadState = line.statusAfter;
					if (+new Date > end)
					{
						_this.work.push(i);
						_this.StartWorker(_this.delay);
						if (realChange)
							_this.arChanges.push({from: task, to: i + 1});
						return (bail = true);
					}

					var changed = line.highlight(_this.oSyntax, status, _this.tabSize);
					if (changed)
						realChange = true;

					line.statusAfter = _this.OnSyntaxCopyState(_this.oSyntax, status);

					var done = null;
					if (compare)
					{
						var same = hadState && compare(hadState, status);
						//if (same != Pass)
						done = !!same;
					}

					if (done == null)
					{
						if (changed !== false || !hadState)
							unchanged = 0;
						else if (++unchanged > 3 && (!_this.oSyntax.Indent || _this.oSyntax.Indent(hadState, "") == _this.oSyntax.Indent(status, "")))
							done = true;
					}

					if (done)
						return true;
					i++;
				});

				if (bail)
					return;

				if (realChange)
					this.arChanges.push({from: task, to: i + 1});
			}
		},

		OnSyntaxStartState: function(syntax, a1, a2)
		{
			return syntax.startStatus ? syntax.startStatus(a1, a2) : true;
		},

		OnSyntaxCopyState: function(syntax, status)
		{
			if (status === true)
				return status;
			if (syntax.copyStatus)
				return syntax.copyStatus(status);

			var nstatus = {};
			for (var n in status)
			{
				var val = status[n];
				if (val instanceof Array)
					val = val.concat([]);
				nstatus[n] = val;
			}
			return nstatus;
		},

		FindStartLine: function(n)
		{
			var
				minindent,
				minline,
				search,
				lim = n - 40;

			for (search = n; search > lim; --search)
			{
				if (search == 0)
					return 0;
				var line = this.GetLine(search - 1);
				if (line.statusAfter)
					return search;
				var indented = line.indentation(this.tabSize);

				if (minline == null || minindent > indented)
				{
					minline = search - 1;
					minindent = indented;
				}
			}
			return minline;
		},

		GetStateBefore: function(n)
		{
			var
				start = this.FindStartLine(n),
				status = start && this.GetLine(start - 1).statusAfter;

			if (status)
				status = this.OnSyntaxCopyState(this.oSyntax, status);
			else
				status = this.OnSyntaxStartState(this.oSyntax);

			this.oDoc.Iteration(start, n, function (line)
			{
				line.highlight(_this.oSyntax, status, _this.tabSize);
				line.statusAfter = _this.OnSyntaxCopyState(_this.oSyntax, status);
			});

			if (start < n)
				this.arChanges.push({from: start, to: n});
			if (n < this.oDoc.size && !this.GetLine(n).statusAfter)
				this.work.push(n);

			return status;
		},

		UpdateDisplay: function(arChanges, suppressCallback, scrollTop)
		{
			if (!this.pScroller.clientWidth)
			{
				this.showingFrom = this.showingTo = this.displayOffset = 0;
				return;
			}

			var visible = this.VisibleLines(scrollTop);
			if (arChanges !== true && arChanges.length == 0 && visible.from > this.showingFrom && visible.to < this.showingTo)
				return this.UpdateVerticalScroll(scrollTop);

			var
				from = Math.max(visible.from - 100, 0),
				to = Math.min(this.oDoc.size, visible.to + 100);

			if (this.showingFrom < from && from - this.showingFrom < 20)
				from = this.showingFrom;
			if (this.showingTo > to && this.showingTo - to < 20)
				to = Math.min(this.oDoc.size, this.showingTo);

			var unChanged = arChanges === true ? [] : this.GetUnchanged([{from: this.showingFrom, to: this.showingTo, domStart: 0}], arChanges);
			var unChangedLines = 0;
			for (var i = 0; i < unChanged.length; ++i)
			{
				var range = unChanged[i];
				if (range.from < from)
				{
					range.domStart += (from - range.from);
					range.from = from;
				}
				if (range.to > to)
					range.to = to;

				if (range.from >= range.to)
					unChanged.splice(i--, 1);
				else
					unChangedLines += range.to - range.from;
			}

			if (unChangedLines == to - from && from == this.showingFrom && to == this.showingTo)
				return this.UpdateVerticalScroll(scrollTop);

			unChanged.sort(function (a, b){return a.domStart - b.domStart;});

			var
				th = this.TextHeight(),
				lineNumDisplay = this.pLineNum.style.display;

			this.pLineDiv.style.display = "none";
			this.PatchDisplay(from, to, unChanged);
			this.pLineDiv.style.display = this.pLineNum.style.display = "";

			var different = from != this.showingFrom || to != this.showingTo || this.lastSizeC != this.pScroller.clientHeight + th;
			if (different)
				this.lastSizeC = this.pScroller.clientHeight + th;

			this.showingFrom = from;
			this.showingTo = to;
			this.displayOffset = this.HeightAtLine(this.oDoc, from);

			if (this.pLineDiv.childNodes.length != this.showingTo - this.showingFrom)
			{
				throw new Error("BAD PATCH! " + JSON.stringify(unChanged) + " size=" + (this.showingTo - this.showingFrom) +
					" nodes=" + this.pLineDiv.childNodes.length);
			}

			this.pLineNum.style.display = lineNumDisplay;
			if (different || this.lineNumDirty)
				this.UpdateLineNum();

			this.UpdateVerticalScroll(scrollTop);
			this.UpdateSelection();

			return true;
		},

		VisibleLines: function(scrollTop)
		{
			var
				lh = this.TextHeight(),
				top = (scrollTop != null ? scrollTop : this.pScrollbar.scrollTop) - this.PaddingTop();

			return {
				from: this.LineAtHeight(this.oDoc, Math.max(0, Math.floor(top / lh))),
				to: this.LineAtHeight(this.oDoc, Math.ceil((top + this.pScroller.clientHeight) / lh))
			};
		},

		TextHeight: function()
		{
			var cnt = 50;
			if (!this._measurePre)
			{
				this._measurePre = BX.create("PRE");
				for (var i = 0; i < cnt; ++i)
				{
					this._measurePre.appendChild(document.createTextNode("W"));
					if (i < cnt - 1)
						this._measurePre.appendChild(BX.create("br"));
				}
			}
			var offsetHeight = this.pLineDiv.clientHeight;
			if (offsetHeight != this._cachedHeightFor)
			{
				this._cachedHeightFor = offsetHeight;
				BX.cleanNode(this.pMeasure);
				this.pMeasure.appendChild(this._measurePre.cloneNode(true));
				this._cachedHeight = this.pMeasure.firstChild.offsetHeight / cnt || 1;
				BX.cleanNode(this.pMeasure);
			}

			return this._cachedHeight;
		},

		UpdateLinesNoUndo: function(from, to, newText, selFrom, selTo)
		{
			if (this.suppressEdits)
				return;
			var
				added,
				_this = this,
				recomputeMaxLength = false,
				maxLineLength = this.maxLine.text.length;


			this.oDoc.Iteration(from.line, to.line + 1, function (line)
			{
				if (!line.hidden && line.text.length == maxLineLength)
				{
					recomputeMaxLength = true;
					return true;
				}
			});

			if (from.line != to.line || newText.length > 1)
				this.lineNumDirty = true;

			var
				nlines = to.line - from.line,
				firstLine = this.GetLine(from.line),
				lastLine = this.GetLine(to.line);

			if (from.ch == 0 && to.ch == 0 && newText[newText.length - 1] == "")
			{
				added = [];
				var prevLine = null;

				if (from.line)
				{
					prevLine = this.GetLine(from.line - 1);
					prevLine.fixMarkEnds(lastLine);
				}
				else
				{
					lastLine.fixMarkStarts();
				}

				for (var i = 0, e = newText.length - 1; i < e; ++i)
					added.push(JCLine.inheritMarks(newText[i], prevLine));

				if (nlines)
					this.oDoc.Remove(from.line, nlines, this.callbacks);
				if (added.length)
					this.oDoc.Insert(from.line, added);
			}
			else if (firstLine == lastLine)
			{
				if (newText.length == 1)
				{
					firstLine.Replace(from.ch, to.ch, newText[0]);
				}
				else
				{
					lastLine = firstLine.split(to.ch, newText[newText.length - 1]);
					firstLine.Replace(from.ch, null, newText[0]);
					firstLine.fixMarkEnds(lastLine);
					added = [];
					for (var i = 1, e = newText.length - 1; i < e; ++i)
						added.push(JCLine.inheritMarks(newText[i], firstLine));
					added.push(lastLine);
					this.oDoc.Insert(from.line + 1, added);
				}
			}
			else if (newText.length == 1)
			{
				firstLine.Replace(from.ch, null, newText[0]);
				lastLine.Replace(null, to.ch, "");
				firstLine.append(lastLine);
				this.oDoc.Remove(from.line + 1, nlines, this.callbacks);
			}
			else
			{
				added = [];
				firstLine.Replace(from.ch, null, newText[0]);
				lastLine.Replace(null, to.ch, newText[newText.length - 1]);
				firstLine.fixMarkEnds(lastLine);
				for (var i = 1, e = newText.length - 1; i < e; ++i)
					added.push(JCLine.inheritMarks(newText[i], firstLine));

				if (nlines > 1)
					this.oDoc.Remove(from.line + 1, nlines - 1, this.callbacks);
				this.oDoc.Insert(from.line + 1, added);
			}

			this.oDoc.Iteration(from.line, from.line + newText.length, function (line)
			{
				var l = line.text;
				if (!line.hidden && l.length > maxLineLength)
				{
					_this.maxLine = line;
					maxLineLength = l.length;
					_this.maxLineChanged = true;
					recomputeMaxLength = false;
				}
			});

			if (recomputeMaxLength)
				this.updateMaxLine = true;


			var newWork = [], lendiff = newText.length - nlines - 1;
			for (var i = 0, l = _this.work.length; i < l; ++i)
			{
				var task = _this.work[i];
				if (task < from.line)
					newWork.push(task);
				else if (task > to.line)
					newWork.push(task + lendiff);
			}

			var hlEnd = from.line + Math.min(newText.length, 500);
			this.HighlightLines(from.line, hlEnd);

			newWork.push(hlEnd);
			this.work = newWork;
			this.StartWorker(100);

			// Remember that these lines changed, for updating the display
			this.arChanges.push({from: from.line, to: to.line + 1, diff: lendiff});
			var changeObj = {
				from: from,
				to: to,
				text: newText
			};

			if (this.textChanged)
			{
				for (var cur = this.textChanged; cur.next; cur = cur.next){}
				cur.next = changeObj;
			}
			else
			{
				this.textChanged = changeObj;
			}

			// Update the selection
			function updateLine(n)
			{
				return n <= Math.min(to.line, to.line + lendiff) ? n : n + lendiff;
			}

			this.SetSelection(this.ClipPos(selFrom), this.ClipPos(selTo), updateLine(this.oSel.from.line),updateLine(this.oSel.to.line));
		},

		UpdateLines: function(from, to, newText, selFrom, selTo)
		{
			if (this.suppressEdits)
				return;

			if (this.oHistory)
			{
				var old = [];
				this.oDoc.Iteration(from.line, to.line + 1, function (line)
				{
					old.push(line.text);
				});
				this.oHistory.addChange(from.line, newText.length, old);
				while (this.oHistory.done.length > this.undoDepth)
					this.oHistory.done.shift();
			}
			this.UpdateLinesNoUndo(from, to, newText, selFrom, selTo);
		},

		HighlightLines: function(start, end)
		{
			var
				_this = this,
				status = this.GetStateBefore(start);

			this.oDoc.Iteration(start, end, function (line)
			{
				line.highlight(_this.oSyntax, status, _this.tabSize);
				line.statusAfter = _this.OnSyntaxCopyState(_this.oSyntax, status);
			});
		},

		SetSelectionUser: function(from, to)
		{
			var sh = this.shiftSelecting && this.ClipPos(this.shiftSelecting);
			if (sh)
			{
				if (this.PosLess(sh, from))
					from = sh;
				else if (this.PosLess(to, sh))
					to = sh;
			}
			this.SetSelection(from, to);
			this.userSelChange = true;
		},

		SetSelection: function(from, to, oldFrom, oldTo)
		{
			this.goalColumn = null;
			if (oldFrom == null)
			{
				oldFrom = this.oSel.from.line;
				oldTo = this.oSel.to.line;
			}

			if (this.PosEq(this.oSel.from, from) && this.PosEq(this.oSel.to, to))
				return;

			if (this.PosLess(to, from))
			{
				var tmp = to;
				to = from;
				from = tmp;
			}

			// Skip over hidden lines.
			if (from.line != oldFrom)
			{
				var from1 = this.SkipHidden(from, oldFrom, this.oSel.from.ch);
				if (!from1)
					this.SetLineHidden(from.line, false);
				else
					from = from1;
			}
			if (to.line != oldTo)
				to = this.SkipHidden(to, oldTo, this.oSel.to.ch);

			if (this.PosEq(from, to) || this.PosEq(from, this.oSel.to))
				this.oSel.inverted = false;
			else if (this.PosEq(to, this.oSel.from))
				this.oSel.inverted = true;

			if (this.PosEq(this.oSel.from, this.oSel.to))
			{
				var head = this.oSel.inverted ? from : to;
				if (head.line != this.oSel.from.line && this.oSel.from.line < this.oDoc.size)
				{
					var oldLine = this.GetLine(this.oSel.from.line);
					if (/^\s+$/.test(oldLine.text))
					{
						setTimeout(this.Action(function(){
							if (oldLine.parent && /^\s+$/.test(oldLine.text))
							{
								var no = lineNo(oldLine);
								this.ReplaceRange("", {line: no, ch: 0}, {line: no, ch: oldLine.text.length});
							}
						}, this), 10);
					}
				}
			}

			this.oSel.from = from;
			this.oSel.to = to;
			this.selectionChanged = true;
		},

		PosEq: function(a, b)
		{
			return a.line == b.line && a.ch == b.ch;
		},

		PosLess: function(a, b)
		{
			return a.line < b.line || (a.line == b.line && a.ch < b.ch);
		},

		CopyPos: function(x)
		{
			return {line: x.line, ch: x.ch};
		},

		GetCharWidth: function()
		{
			if (this.pScroller.clientWidth == this._cachedWidthFor)
				return this._cachedWidth;
			this._cachedWidthFor = this.pScroller.clientWidth;

			var span = BX.create("SPAN", {text:" x"});
			BX.cleanNode(this.pMeasure);
			this.pMeasure.appendChild(BX.create("PRE", {children: [span]}));
			this._cachedWidth = span.offsetWidth || 10;

			return this._cachedWidth;
		},

		RestartBlink: function()
		{
			if (this._blinkerInterval)
				this._blinkerInterval = clearInterval(this._blinkerInterval);

			var
				cursor = this.pCursor,
				show = true;

			cursor.style.visibility = "";
			this._blinkerInterval = setInterval(function()
			{
				cursor.style.visibility = (show = !show) ? "" : "hidden";
			}, this.cursorInterval);
		},

		ReadInput: function()
		{
			if (!this.focused || this.HasSelection(this.pInput))
				return false;

			var text = this.pInput.value;
			if (text == this.prevInput)
				return false;

			this.shiftSelecting = null;
			var
				same = 0,
				l = Math.min(this.prevInput.length, text.length);

			while (same < l && this.prevInput[same] == text[same])
				++same;

			if (same < this.prevInput.length)
				this.oSel.from = {
					line: this.oSel.from.line,
					ch: this.oSel.from.ch - (this.prevInput.length - same)
				};
			this.ReplaceSelection(text.slice(same), "end");

			if (text.length > 1000)
				this.pInput.value = this.prevInput = "";
			else
				this.prevInput = text;

			return true;
		},

		ResetInput: function(user)
		{
			if (!this.PosEq(this.oSel.from, this.oSel.to))
			{
				this.prevInput = "";
				this.pInput.value = this.GetSelection();

				if (this.focused)
				{
					if (BX.browser.IsIOS())
					{
						this.pInput.selectionStart = 0;
						this.pInput.selectionEnd = this.pInput.value.length;
					}
					else
					{
						this.pInput.select();
					}
				}
			}
			else if (user)
			{
				this.prevInput = this.pInput.value = "";
			}
		},

		FocusInput: function()
		{
			BX.focus(this.pInput);
		},

		SetShift: function(val)
		{
			if (val)
				this.shiftSelecting = this.shiftSelecting || (this.oSel.inverted ? this.oSel.to : this.oSel.from);
			else
				this.shiftSelecting = null;
		},

		MakeTab: function(col)
		{
			var
				str,
				w = this.tabSize - col % this.tabSize;

			if (this._tabCache[w])
				return this._tabCache[w];

			for (str = "", i = 0; i < w; ++i)
				str += " ";

			this._tabCache[w] = {
				element: BX.create("TT", {text: str, props: {className: "bxce-tabspan"}}),
				width: w
			};

			return this._tabCache[w];
		},

		ComputeMaxLength: function()
		{
			this.maxLine = this.GetLine(0);
			this.maxLineChanged = true;
			var
				_this = this,
				maxLineLength = this.maxLine.text.length;


			this.oDoc.Iteration(1, this.oDoc.size, function (line)
			{
				var l = line.text;
				if (!line.hidden && l.length > maxLineLength)
				{
					maxLineLength = l.length;
					_this.maxLine = line;
				}
			});
			this.updateMaxLine = false;
		},

		MeasureLine: function(line, ch)
		{
			if (ch == 0)
				return {top: 0, left: 0};

			var pre = line.getElement(BX.proxy(this.MakeTab, this), ch, false);
			BX.cleanNode(this.pMeasure);
			this.pMeasure.appendChild(pre);

			var
				anchor = pre.anchor,
				top = anchor.offsetTop,
				left = anchor.offsetLeft;

			return {top: top, left: left};
		},

		ScrollCursorIntoView: function()
		{
			var coords = this.CalculateCursorCoords();
			this.ScrollIntoView(coords.x, coords.y, coords.x, coords.yBot);
			if (!this.focused)
				return;
			var
				box = this.pSizeCont.getBoundingClientRect(),
				doScroll = null;

			if (coords.y + box.top < 0)
				doScroll = true;
			else if (coords.y + box.top + this.TextHeight() > (window.innerHeight || document.documentElement.clientHeight))
				doScroll = false;

			if (doScroll != null)
			{
				var hidden = this.pCursor.style.display == "none";
				if (hidden)
				{
					this.pCursor.style.display = "";
					this.pCursor.style.left = coords.x + "px";
					this.pCursor.style.top = (coords.y - this.displayOffset) + "px";
				}
				this.pCursor.scrollIntoView(doScroll);
				if (hidden)
					this.pCursor.style.display = "none";
			}
		},

		CalculateCursorCoords: function()
		{
			var cur = this.LocalCoords(this.oSel.inverted ? this.oSel.from : this.oSel.to);
			return {x: cur.x, y: cur.y, yBot: cur.yBot};
		},

		ScrollIntoView: function(x1, y1, x2, y2)
		{
			var scrollPos = this.CalculateScrollPos(x1, y1, x2, y2);
			if (scrollPos.scrollLeft != null)
				this.pScroller.scrollLeft = scrollPos.scrollLeft;
			if (scrollPos.scrollTop != null)
				this.pScrollbar.scrollTop = this.pScroller.scrollTop = scrollPos.scrollTop;
		},

		CalculateScrollPos: function(x1, y1, x2, y2)
		{
			var
				pl = this.PaddingLeft(),
				pt = this.PaddingTop();

			y1 += pt;
			y2 += pt;
			x1 += pl;
			x2 += pl;

			var
				screen = this.pScroller.clientHeight,
				screentop = this.pScrollbar.scrollTop,
				result = {},
				docBottom = this.NeedsScrollbar() || Infinity,
				atTop = y1 < pt + 10,
				atBottom = y2 + pt > docBottom - 10,
				screenw = this.pScroller.clientWidth,
				screenleft = this.pScroller.scrollLeft,
				lineNumWidth = this.pLineNum.clientWidth,
				atLeft = x1 < lineNumWidth + pl + 10;

			if (y1 < screentop)
				result.scrollTop = atTop ? 0 : Math.max(0, y1);
			else if (y2 > screentop + screen)
				result.scrollTop = (atBottom ? docBottom : y2) - screen;

			if (x1 < screenleft + lineNumWidth || atLeft)
			{
				if (atLeft)
					x1 = 0;
				result.scrollLeft = Math.max(0, x1 - 10 - lineNumWidth);
			}
			else if (x2 > screenw + screenleft - 3)
			{
				result.scrollLeft = x2 + 10 - screenw;
			}

			return result;
		},

		LocalCoords: function(pos, inLineWrap)
		{
			var
				x,
				lh = this.TextHeight(),
				y = lh * (this.HeightAtLine(this.oDoc, pos.line) - (inLineWrap ? this.displayOffset : 0));

			if (pos.ch == 0)
				x = 0;
			else
				x = this.MeasureLine(this.GetLine(pos.line), pos.ch).left;

			return {x: x, y: y, yBot: y + lh};
		},

		CoordsChar: function(x, y)
		{
			var
				_this = this,
				th = this.TextHeight(),
				cw = this.GetCharWidth(),
				heightPos = this.displayOffset + Math.floor(y / th);

			if (heightPos < 0)
				return {line: 0, ch: 0};

			var lineNo = this.LineAtHeight(this.oDoc, heightPos);

			if (lineNo >= this.oDoc.size)
				return {
					line: this.oDoc.size - 1,
					ch: this.GetLine(this.oDoc.size - 1).text.length
				};

			var
				lineObj = this.GetLine(lineNo),
				text = lineObj.text,
				innerOff = 0;

			if (x <= 0 && innerOff == 0)
				return {
					line: lineNo,
					ch: 0
				};
			var wrongLine = false;

			function getX(len)
			{
				var sp = _this.MeasureLine(lineObj, len);
				return sp.left;
			}

			var from = 0, fromX = 0, to = text.length, toX;
			// Guess a suitable upper bound for our search.
			var estimated = Math.min(to, Math.ceil((x + innerOff * this.pScroller.clientWidth * .9) / cw));
			while (true)
			{
				var estX = getX(estimated);
				if (estX <= x && estimated < to)
				{
					estimated = Math.min(to, Math.ceil(estimated * 1.2));
				}
				else
				{
					toX = estX;
					to = estimated;
					break;
				}
			}
			if (x > toX)
				return {line: lineNo, ch: to};
			// Try to guess a suitable lower bound as well.
			estimated = Math.floor(to * 0.8);
			estX = getX(estimated);
			if (estX < x)
			{
				from = estimated;
				fromX = estX;
			}
			// Do a binary search between these bounds.
			while (true)
			{
				if (to - from <= 1)
				{
					var after = x - fromX < toX - x;
					return {line: lineNo, ch: after ? from : to, after: after};
				}
				var
					middle = Math.ceil((from + to) / 2),
					middleX = getX(middle);

				if (middleX > x)
				{
					to = middle;
					toX = middleX;
					if (wrongLine)
						toX += 1000;
				}
				else
				{
					from = middle;
					fromX = middleX;
				}
			}
		},

		UpdateVerticalScroll: function(scrollTop)
		{
			var _this = this;
			var scrollHeight = this.NeedsScrollbar();
			this.pScrollbar.style.display = scrollHeight ? "block" : "none";
			if (scrollHeight)
			{
				this.pScrollbarInner.style.height = this.pSizeCont.style.minHeight = scrollHeight + "px";
				this.pScrollbar.style.height = this.pScroller.clientHeight + "px";
				if (scrollTop != null)
				{
					this.pScrollbar.scrollTop = this.pScroller.scrollTop = scrollTop;
					// Chrome & Safari bug workaround
					if (BX.browser.IsSafari() || BX.browser.IsChrome())
					{
						setTimeout(function (){
							if (_this.pScrollbar.scrollTop != scrollTop)
								return;
							_this.pScrollbar.scrollTop = scrollTop + (scrollTop ? -1 : 1);
							_this.pScrollbar.scrollTop = scrollTop;
						}, 0);
					}
				}
			}
			else
			{
				this.pSizeCont.style.minHeight = "";
			}
			this.pMover.style.top = this.displayOffset * this.TextHeight() + "px";
		},

		NeedsScrollbar: function()
		{
			var realHeight = this.oDoc.size * this.TextHeight() + 2 * this.PaddingTop();
			return realHeight * .99 > this.pScroller.offsetHeight ? realHeight : false;
		},

		PaddingTop: function()
		{
			return this.pLinesCont.offsetTop;
		},

		PaddingLeft: function()
		{
			return this.pLinesCont.offsetLeft;
		},

		PosFromMouse: function(e, liberal)
		{
			var
				offW = GetOffset(this.pScroller, true),
				x = e.clientX,
				y = e.clientY;

			if (!liberal && (x - offW.left > this.pScroller.clientWidth || y - offW.top > this.pScroller.clientHeight))
				return null;

			var offL = GetOffset(this.pLinesCont, true);
			return this.CoordsChar(x - offL.left, y - offL.top);
		},

		LineAtHeight: function(chunk, h)
		{
			var n = 0;
			outer: do {
				for (var i = 0, e = chunk.children.length; i < e; ++i)
				{
					var child = chunk.children[i], ch = child.height;
					if (h < ch)
					{
						chunk = child;
						continue outer;
					}
					h -= ch;
					n += child.GetSize();
				}
				return n;
			}
			while (!chunk.lines);

			for (var i = 0; i < chunk.lines.length; ++i)
			{
				var
					line = chunk.lines[i],
					lh = line.height;
				if (h < lh)
					break;
				h -= lh;
			}
			return n + i;
		},

		HeightAtLine: function(chunk, n)
		{
			var h = 0;
			outer: do {
				for (var i = 0, e = chunk.children.length; i < e; ++i)
				{
					var child = chunk.children[i], sz = child.GetSize();
					if (n < sz)
					{
						chunk = child;
						continue outer;
					}
					n -= sz;
					h += child.height;
				}
				return h;
			}
			while (!chunk.lines);

			for (var i = 0; i < n; ++i)
				h += chunk.lines[i].height;

			return h;
		},

		GetUnchanged: function(unChanged, arChanges)
		{
			var
				change, unChanged2, diff, range,
				i, l = arChanges.length || 0, j;

			for (i = 0; i < l; ++i)
			{
				change = arChanges[i];
				unChanged2 = [];
				diff = change.diff || 0;

				for (j = 0; j < unChanged.length; ++j)
				{
					range = unChanged[j];
					if (change.to <= range.from && change.diff)
					{
						unChanged2.push({from: range.from + diff, to: range.to + diff, domStart: range.domStart});
					}
					else if (change.to <= range.from || change.from >= range.to)
					{
						unChanged2.push(range);
					}
					else
					{
						if (change.from > range.from)
							unChanged2.push({from: range.from, to: change.from, domStart: range.domStart});

						if (change.to < range.to)
							unChanged2.push({from: change.to + diff, to: range.to + diff, domStart: range.domStart + (change.to - range.from)});
					}
				}
				unChanged = unChanged2;
			}
			return unChanged;
		},

		PatchDisplay: function(from, to, unChanged)
		{
			function killNode(node)
			{
				var tmp = node.nextSibling;
				node.parentNode.removeChild(node);
				return tmp;
			}

			if (!unChanged.length)
			{
				BX.cleanNode(this.pLineDiv)
			}
			else
			{
				var
					domPos = 0, i, cur, j, e,
					curNode = this.pLineDiv.firstChild, n;

				for (i = 0; i < unChanged.length; ++i)
				{
					cur = unChanged[i];
					while (cur.domStart > domPos)
					{
						curNode = killNode(curNode);
						domPos++;
					}

					for (j = 0, e = cur.to - cur.from; j < e; ++j)
					{
						curNode = curNode.nextSibling;
						domPos++;
					}
				}

				while (curNode)
					curNode = killNode(curNode);
			}

			// This pass fills in the lines that actually changed.
			var
				_this = this,
				nextIntact = unChanged.shift(),
				j = from;

			curNode = this.pLineDiv.firstChild;
			this.oDoc.Iteration(from, to, function (line)
			{
				var pLine;
				if (nextIntact && nextIntact.to == j)
					nextIntact = unChanged.shift();
				if (!nextIntact || nextIntact.from > j)
				{
					if (line.hidden)
					{
						pLine = BX.create("PRE");
					}
					else
					{
						pLine = line.getElement(BX.proxy(_this.MakeTab, _this));
						if (line.className)
							pLine.className = line.className;
						if (line.bgClassName)
							pLine = BX.create("DIV", {children:
								[
									BX.create("PRE", {html:"\u00a0", props:{className: line.bgClassName}, style:{position: 'absolute', left: 0, right: 0, top: 0, bottom: 0, zIndex: -2}}),
									pLine
								], style: {position: 'relative'}});
					}
					_this.pLineDiv.insertBefore(pLine, curNode);
				}
				else
				{
					curNode = curNode.nextSibling;
				}
				++j;
			});
		},

		UpdateLineNum: function()
		{
			var
				hText = this.pMover.offsetHeight,
				hEditor = this.pScroller.clientHeight,
				frag = document.createDocumentFragment(),
				i = this.showingFrom,
				normalNode;

			this.pLineNum.style.height = (hText - hEditor < 2 ? hEditor : hText) + "px";

			this.oDoc.Iteration(this.showingFrom, Math.max(this.showingTo, this.showingFrom + 1), function (line)
			{
				if (line.hidden)
				{
					frag.appendChild(BX.create("PRE"));
				}
				else
				{
					var pLine = frag.appendChild(BX.create("PRE", {html: i + 1}));
					for (var j = 1; j < line.height; ++j)
					{
						pLine.appendChild(BX.create("BR"));
						pLine.appendChild(document.createTextNode("\u00a0"));
					}
					normalNode = i;
				}
				++i;
			});
			this.pLineNum.style.display = "none";

			// One more line
//			frag.appendChild(BX.create("PRE", {html: i}));
//			normalNode = i;

			BX.cleanNode(this.pLineNumText);
			this.pLineNumText.appendChild(frag);

			if (normalNode != null)
			{
				var
					val = '',
					node = this.pLineNumText.childNodes[normalNode - this.showingFrom],
					minwidth = String(this.oDoc.size).length,
					ch = node.firstChild,
					pad = "";

				if (ch)
					val = ch.textContent || ch.innerText || ch.nodeValue || '';

				while (val.length + pad.length < minwidth)
					pad += "\u00a0";

				if (pad)
					node.insertBefore(document.createTextNode(pad), node.firstChild);
			}
			this.pLineNum.style.display = "";

			var lineOffset = parseInt(this.pLineNum.offsetWidth);
			if (lineOffset >= 53)
				lineOffset = 53;
			var resized = Math.abs((parseInt(this.pLinesCont.style.marginLeft) || 0) - lineOffset) > 2;
			this.pLinesCont.style.marginLeft = lineOffset + "px";

			this.lineNumDirty = false;
			return resized;
		},

		UpdateSelection: function()
		{
			var
				collapsed = this.PosEq(this.oSel.from, this.oSel.to),
				fromPos = this.LocalCoords(this.oSel.from, true),
				toPos = collapsed ? fromPos : this.LocalCoords(this.oSel.to, true),
				headPos = this.oSel.inverted ? fromPos : toPos,
				th = this.TextHeight(),
				wrapOff = GetOffset(this.pInnerContHL),
				lineOff = GetOffset(this.pLineDiv);

			this.pInputCont.style.top = Math.max(0, Math.min(this.pScroller.offsetHeight, headPos.y + lineOff.top - wrapOff.top)) + "px";
			this.pInputCont.style.left = Math.max(0, Math.min(this.pScroller.offsetWidth, headPos.x + lineOff.left - wrapOff.left)) + "px";

			if (collapsed)
			{
				this.pCursor.style.top = headPos.y + "px";
				this.pCursor.style.left = headPos.x + "px";
				this.pCursor.style.display = "";
				this.pSelectionDiv.style.display = "none";
			}
			else
			{
				var
					sameLine = fromPos.y == toPos.y,
					frag = document.createDocumentFragment(),
					clientWidth = this.pLinesCont.clientWidth || this.pLinesCont.offsetWidth,
					clientHeight = this.pLinesCont.clientHeight || this.pLinesCont.offsetHeight;

				function add(left, top, right, height)
				{
					var pEl = frag.appendChild(BX.create("DIV", {props: {className: "bxce-selected"}, style:{position: "absolute", left: left + "px", top: top + "px", height: height + "px"}}));

//					if (BX.browser.IsDoctype())
					pEl.style.width = (right ? (clientWidth - right - left) : clientWidth) + "px";
//					else
//						pEl.style.right = right + "px";

				};

				if (this.oSel.from.ch && fromPos.y >= 0)
				{
					var right = sameLine ? clientWidth - toPos.x : 0;
					add(fromPos.x, fromPos.y, right, th);
				}

				var middleStart = Math.max(0, fromPos.y + (this.oSel.from.ch ? th : 0));
				var middleHeight = Math.min(toPos.y, clientHeight) - middleStart;

				if (middleHeight > 0.2 * th)
					add(0, middleStart, 0, middleHeight);
				if ((!sameLine || !this.oSel.from.ch) && toPos.y < clientHeight - .5 * th)
					add(0, toPos.y, clientWidth - toPos.x, th);

				BX.cleanNode(this.pSelectionDiv);
				this.pSelectionDiv.appendChild(frag);

				this.pCursor.style.display = "none";
				this.pSelectionDiv.style.display = "";
			}
		},

		// *****  Event handlers *****
		OnScrollBar: function(e)
		{
			if (this.pScrollbar.scrollTop != this._lastScrollTop)
			{
				this._lastScrollTop = this.pScroller.scrollTop = this.pScrollbar.scrollTop;
				this.UpdateDisplay([]);
			}
		},

		OnScrollMain: function(e)
		{
			if (this.pLineNum.style.left != this.pScroller.scrollLeft + "px")
				this.pLineNum.style.left = this.pScroller.scrollLeft + "px";

			if (this.pScroller.scrollTop != this._lastScrollTop)
			{
				this._lastScrollTop = this.pScroller.scrollTop;
				if (this.pScrollbar.scrollTop != this._lastScrollTop)
					this.pScrollbar.scrollTop = this._lastScrollTop;
				this.UpdateDisplay([]);
			}
		},

		OnMouseDown: function(e)
		{
			if (!e)
				e = window.event;

			if (!this.highlightMode)
				return;

			if (e.button && e.button !== 0)
				return;

			this.SetShift(e.shiftKey);
			var
				n,
				target = e.target || e.srcElement;

			for (n = target; n != this.pInnerContHL; n = n.parentNode)
				if (n.parentNode == this.pSizeCont && n != this.pMover)
					return;

			for (n = target; n != this.pInnerContHL; n = n.parentNode)
				if (n.parentNode == this.pLineNumText)
					return BX.PreventDefault(e);
			//return e_preventDefault(e);

			var start = this.PosFromMouse(e);
			if (mouseButton(e) == 2)
			{
				if (start)
					this.SetCursor(start.line, start.ch, true);
				setTimeout(BX.proxy(this.FocusInput, this), 20);
				return BX.PreventDefault(e);
				//e_preventDefault(e);
				//return;
			}

			if (!start)
			{
				if (target == this.pScroller)
					BX.PreventDefault(e);
				//e_preventDefault(e);
				return;
			}

			if (!this.focused)
				this.OnFocus();

			var now = +new Date;

			this._type = "single";
			if (this.lastDoubleClick && this.lastDoubleClick.time > now - 400 && this.PosEq(this.lastDoubleClick.pos, start))
			{
				this._type = "triple";
				BX.PreventDefault(e);
				//e_preventDefault(e);
				setTimeout(BX.proxy(this.FocusInput, this), 20);
				this.SelectLine(start.line);
			}
			else if (this.lastClick && this.lastClick.time > now - 400 && this.PosEq(this.lastClick.pos, start))
			{
				this._type = "double";
				this.LastDoubleClick = {time: now, pos: start};
				BX.PreventDefault(e);
				//e_preventDefault(e);
				var word = this.FindWordAt(start);
				this.SetSelectionUser(word.from, word.to);
			}
			else
			{
				this.lastClick = {time: now, pos: start};
			}

			this._selectingTimeout = null;
			this._last = start;
			this._start = start;

			BX.PreventDefault(e);
			//e_preventDefault(e);
			if (this._type == "single")
				this.SetCursor(start.line, start.ch, true);

			this._startstart = this.oSel.from;
			this._startend = this.oSel.to;

			BX.bind(document, "mousemove", BX.proxy(this.OnMouseMove, this));
			BX.bind(document, "mouseup", BX.proxy(this._OnDone, this));
		},

		_DoSelect: function(cur)
		{
			if (this._type == "single")
			{
				this.SetSelectionUser(this._start, cur);
			}
			else if (this._type == "double")
			{
				var word = this.FindWordAt(cur);
				if (this.PosLess(cur, this._startstart))
					this.SetSelectionUser(word.from, this._startend);
				else
					this.SetSelectionUser(this._startstart, word.to);
			}
			else if (this._type == "triple")
			{
				if (this.PosLess(cur, this._startstart))
					this.SetSelectionUser(this._startend, this.ClipPos({line: cur.line, ch: 0}));
				else
					this.SetSelectionUser(this._startstart, this.ClipPos({line: cur.line + 1, ch: 0}));
			}
		},

		OnMouseMove: function(e)
		{
			this.OnBeforeAction();
			if (this._selectingTimeout)
				this._selectingTimeout = clearTimeout(this._selectingTimeout);
			//e_preventDefault(e);
			BX.PreventDefault(e);
			if (!BX.browser.IsIE() && !mouseButton(e))
				this._OnDone(e);
			else
				this._OnExtend(e);
			this.OnAfterAction();
		},

		_OnDone: function(e)
		{
			if (this._selectingTimeout)
				this._selectingTimeout = clearTimeout(this._selectingTimeout);

			var cur = this.PosFromMouse(e);
			if (cur)
				this._DoSelect(cur);

			BX.PreventDefault(e);
			this.FocusInput();

			this.updateInput = true;
			//if (this.selectionChanged)
				this.ResetInput(this.userSelChange);

			BX.unbind(document, "mousemove", BX.proxy(this.OnMouseMove, this));
			BX.unbind(document, "mouseup", BX.proxy(this._OnDone, this));
		},

		_OnExtend: function(e)
		{
			var
				_this = this,
				cur = this.PosFromMouse(e, true);

			if (cur && !this.PosEq(cur, this._last))
			{
				if (!this.focused)
					this.OnFocus();
				this._last = cur;
				this._DoSelect(cur);
				this.updateInput = false;
				var visible = this.VisibleLines();
				if (cur.line >= visible.to || cur.line < visible.from)
					this._selectingTimeout = setTimeout(this.Action(function(){_this._OnExtend(e)}, this), 150);
			}
		},


		OnDoubleClick: function(e)
		{
//			for (var n = e_target(e); n != this.pInnerContHL; n = n.parentNode)
//				if (n.parentNode == this.pLineNumText)
//					return BX.PreventDefault(e);
//					//return e_preventDefault(e);
//			e_preventDefault(e);
			return BX.PreventDefault(e);
		},

		DoHandleBinding: function(bound, dropShift)
		{
			if (typeof bound == "string")
			{
				bound = this.GetCommand(bound);
				if (!bound)
					return false;
			}

			var prevShift = this.shiftSelecting;
			try
			{
				if (dropShift)
					this.suppressEdits = null;
				bound();
			}
			catch (e)
			{
				//if (e != Pass)
					throw e;
				return false;
			}
			finally
			{
				this.shiftSelecting = prevShift;
				this.suppressEdits = false;
			}
			return true;
		},

		HandleKeyBinding: function(e)
		{
			var
				_this = this,
				name = this.keyNames[e.keyCode],
				handled = false;

			if (name == null || e.altGraphKey)
				return false;
			if (e.altKey)
				name = "Alt-" + name;
			if (e.ctrlKey)
				name = "Ctrl-" + name;
			if (e.metaKey)
				name = "Cmd-" + name;

			var stopped = false;
			function stop()
			{
				stopped = true;
			}

			if (e.shiftKey)
			{
				handled = this.LookupKey(
					"Shift-" + name,
					function(b){return _this.DoHandleBinding(b, true);},
					stop
				)
					||
					this.LookupKey(
						name,
						function (b){
							if (typeof b == "string" && /^go[A-Z]/.test(b))
								return _this.DoHandleBinding(b);
						},
						stop
					);
			}
			else
			{
				handled = this.LookupKey(name, BX.proxy(this.DoHandleBinding, this), stop);
			}

			if (stopped)
				handled = false;

			if (handled)
			{
				//e_preventDefault(e);
				BX.PreventDefault(e);
				this.RestartBlink();
				if (BX.browser.IsIE())
				{
					e.oldKeyCode = e.keyCode;
					e.keyCode = 0;
				}
			}
			return handled;
		},

		HandleCharBinding: function(e, ch)
		{
			var _this = this;
			var handled = this.LookupKey("'" + ch + "'", function (b){return _this.DoHandleBinding(b, true);});

			if (handled)
			{
				//e_preventDefault(e);
				BX.PreventDefault(e);
				_this.RestartBlink();
			}
			return handled;
		},

		OnKeyDown: function(e)
		{
			if (!this.focused)
				this.OnFocus();

			if (BX.browser.IsIE() && e.keyCode == 27)
				e.returnValue = false;

			if (this.pollingFast && this.ReadInput())
				this.pollingFast = false;

			var code = e.keyCode;

			// IE does strange things with escape.
			this.SetShift(code == 16 || e.shiftKey);
			var handled = this.HandleKeyBinding(e);

			if (BX.browser.IsOpera())
			{
				this._lastStoppedKey = handled ? code : null;
				if (!handled && code == 88 && e.ctrlKey)
					this.ReplaceSelection("");
			}
		},

		OnKeyPress: function(e)
		{
			if (this.pollingFast)
				this.ReadInput();

			var
				keyCode = e.keyCode,
				charCode = e.charCode;

			if (BX.browser.IsOpera() && keyCode == this._lastStoppedKey)
			{
				this._lastStoppedKey = null;
				//e_preventDefault(e);
				return BX.PreventDefault(e);
				//return;
			}

			if (BX.browser.IsOpera() && (!e.which || e.which < 10) && this.HandleKeyBinding(e))
				return;

			var ch = String.fromCharCode(charCode == null ? keyCode : charCode);
			if (this.oSyntax.magicSym && this.oSyntax.magicSym.indexOf(ch) > -1)
				setTimeout(this.Action(function(){this.IndentLine(this.oSel.to.line, "smart");}, this), 75);

			if (this.HandleCharBinding(e, ch))
				return;
			this.FastPoll();
		},

		OnKeyUp: function(e)
		{
			if (e.keyCode == 16)
				this.shiftSelecting = null;
		},

		OnFocus: function()
		{
			if (!this.focused)
			{
				this.focused = true;
				BX.addClass(this.pScroller, "bxce-focused");
				this.ResetInput(true);
			}
			this.SlowPoll();
			this.RestartBlink();
		},

		OnBlur: function()
		{
			this.Save();

			var _this = this;
			if (this.focused)
			{
				this.focused = false;
				if (this.bracketHighlighted)
				{
					this.Action(function()
					{
						if (this.bracketHighlighted)
						{
							this.bracketHighlighted();
							this.bracketHighlighted = null;
						}
					}, this);
				}
				BX.removeClass(this.pScroller, "bxce-focused");
			}

			if (this._blinkerInterval)
				this._blinkerInterval = clearInterval(this._blinkerInterval);

			setTimeout(function ()
			{
				if (!_this.focused)
					_this.shiftSelecting = null;
			}, 150);
		},
		// END *****  Event handlers *****

		ReplaceRange: function(code, from, to)
		{
			var _this = this;
			from = this.ClipPos(from);
			to = to ? this.ClipPos(to) : from;
			code = this.ExplodeLines(code);

			function adjustPos(pos)
			{
				if (_this.PosLess(pos, from))
					return pos;
				if (!_this.PosLess(to, pos))
					return end;
				var line = pos.line + code.length - (to.line - from.line) - 1;
				var ch = pos.ch;
				if (pos.line == to.line)
					ch += code[code.length - 1].length - (to.ch - (to.line == from.line ? from.ch : 0));

				return {line: line, ch: ch};
			}

			var end;
			this.ReplaceRange1(code, from, to, function (end1)
			{
				end = end1;
				return {from: adjustPos(_this.oSel.from), to: adjustPos(_this.oSel.to)};
			});

			return end;
		},

		ReplaceSelection: function(code, collapse)
		{
			var _this = this;
			this.ReplaceRange1(this.ExplodeLines(code), this.oSel.from, this.oSel.to, function (end)
			{
				if (collapse == "end")
					return {from: end, to: end};
				else if (collapse == "start")
					return {from: _this.oSel.from, to: _this.oSel.from};
				else
					return {from: _this.oSel.from, to: end};
			});
		},

		ReplaceRange1: function(code, from, to, computeSel)
		{
			var
				endch = code.length == 1 ? code[0].length + from.ch : code[code.length - 1].length,
				newSel = computeSel({line: from.line + code.length - 1, ch: endch});

			this.UpdateLines(from, to, code, newSel.from, newSel.to);
		},

		GetRange: function(from, to, lineSep)
		{
			var
				l1 = from.line,
				l2 = to.line;
			if (l1 == l2)
				return this.GetLine(l1).text.slice(from.ch, to.ch);

			var code = [this.GetLine(l1).text.slice(from.ch)];
			this.oDoc.Iteration(l1 + 1, l2, function (line){code.push(line.text);});
			code.push(this.GetLine(l2).text.slice(0, to.ch));
			return code.join(lineSep || "\n");
		},

		GetSelection: function (lineSep)
		{
			return this.GetRange(this.oSel.from, this.oSel.to, lineSep);
		},

		SlowPoll: function()
		{
			if (!this.pollingFast)
				this.pollDelayed.set(this.pollInterval, BX.proxy(this.OnSlowPoolRun, this));
		},

		OnSlowPoolRun: function()
		{
			if (!this.highlightMode)
				return BX.DoNothing;

			this.OnBeforeAction();
			this.ReadInput();
			if (this.focused)
				this.SlowPoll();
			this.OnAfterAction();
		},

		FastPoll: function()
		{
			var
				_this = this,
				missed = false;
			this.pollingFast = true;

			function p()
			{
				_this.OnBeforeAction();
				var changed = _this.ReadInput();
				if (!changed && !missed)
				{
					missed = true;
					_this.pollDelayed.set(60, p);
				}
				else
				{
					_this.pollingFast = false;
					_this.SlowPoll();
				}
				_this.OnAfterAction();
			}

			this.pollDelayed.set(20, p);
		},

		ChangeLine: function(handle, op)
		{
			var no = handle, line = handle;
			if (typeof handle == "number")
				line = this.GetLine(this.ClipLine(handle));
			else
				no = this.LineNo(handle);

			if (no == null)
				return null;

			if (op(line, no))
				this.arChanges.push({from: no, to: no + 1});
			else
				return null;
			return line;
		},

		SetLineHidden: function(handle, hidden)
		{
			var _this = this;
			return this.ChangeLine(handle, function (line, no)
			{
				if (line.hidden != hidden)
				{
					line.hidden = hidden;

					if (hidden && line.text.length == this.maxLine.text.length)
					{
						this.updateMaxLine = true;
					}
					else if (!hidden && line.text.length > this.maxLine.text.length)
					{
						this.maxLine = line;
						this.updateMaxLine = false;
					}

					_this.UpdateLineHeight(line, hidden ? 0 : 1);
					var
						fline = this.oSel.from.line,
						tline = this.oSel.to.line;

					if (hidden && (fline == no || tline == no))
					{
						var from = fline == no ? _this.SkipHidden({line: fline, ch: 0}, fline, 0) : this.oSel.from;
						var to = tline == no ? _this.SkipHidden({line: tline, ch: 0}, tline, 0) : this.oSel.to;
						if (!to) // Can't hide the last visible line, we'd have no place to put the cursor
							return;
						_this.SetSelection(from, to);
					}
					return (_this.lineNumDirty = true);
				}
			});
		},

		UpdateLineHeight: function(line, height)
		{
			this.lineNumDirty = true;
			var diff = height - line.height;
			for (var n = line; n; n = n.parent)
				n.height += diff;
		},


		SkipHidden: function(pos, oldLine, oldCh)
		{
			var _this = this;
			function getNonHidden(dir)
			{
				var lNo = pos.line + dir, end = dir == 1 ? _this.oDoc.size : -1;
				while (lNo != end)
				{
					var line = getLine(lNo);
					if (!line.hidden)
					{
						var ch = pos.ch;
						if (toEnd || ch > oldCh || ch > line.text.length)
							ch = line.text.length;
						return {line: lNo, ch: ch};
					}
					lNo += dir;
				}
			}

			var
				line = this.GetLine(pos.line),
				toEnd = pos.ch == line.text.length && pos.ch != oldCh;

			if (!line.hidden)
				return pos;

			if (pos.line >= oldLine)
				return getNonHidden(1) || getNonHidden(-1);
			else
				return getNonHidden(-1) || getNonHidden(1);
		},

		SelectLine: function(line)
		{
			this.SetSelectionUser({line: line, ch: 0}, this.ClipPos({line: line + 1, ch: 0}));
		},

		IndentSelected: function(syntax)
		{
			if (this.PosEq(this.oSel.from, this.oSel.to))
				return this.IndentLine(this.oSel.from.line, syntax);
			var e = this.oSel.to.line - (this.oSel.to.ch ? 0 : 1);
			for (var i = this.oSel.from.line; i <= e; ++i)
				this.IndentLine(i, syntax);
		},

		IndentLine: function(n, how)
		{
			if (!how)
				how = "add";

			if (how == "smart")
			{
				if (!this.oSyntax.Indent)
					how = "prev";
				else
					var status = this.GetStateBefore(n);
			}

			var
				line = this.GetLine(n),
				curSpace = line.indentation(this.tabSize),
				curSpaceString = line.text.match(/^\s*/)[0],
				indentation;

			if (how == "smart")
			{
				indentation = this.oSyntax.Indent(status, line.text.slice(curSpaceString.length), line.text);
				//if (indentation == Pass)
				//	how = "prev";
			}

			if (how == "prev")
				indentation = n ? this.GetLine(n - 1).indentation(this.tabSize) : 0;
			else if (how == "add")
				indentation = curSpace + this.indentUnit;
			else if (how == "subtract")
				indentation = curSpace - this.indentUnit;

			indentation = Math.max(0, indentation);
			var
				i,
				indentString = "",
				pos = 0;

			for (i = Math.floor(indentation / this.tabSize); i; --i)
			{
				pos += this.tabSize;
				indentString += "\t";
			}

			while (pos < indentation)
			{
				++pos;
				indentString += " ";
			}

			if (indentString != curSpaceString)
				this.ReplaceRange(indentString, {line: n, ch: 0}, {line: n, ch: curSpaceString.length});
		},

		FindPosH: function(dir, unit)
		{
			var
				_this = this,
				end = this.oSel.inverted ? this.oSel.from : this.oSel.to,
				line = end.line,
				ch = end.ch,
				lineObj = this.GetLine(line);

			function findNextLine()
			{
				var l, lo, e;
				for (l = line + dir, e = dir < 0 ? -1 : _this.oDoc.size; l != e; l += dir)
				{
					lo = _this.GetLine(l);
					if (!lo.hidden)
					{
						line = l;
						lineObj = lo;
						return true;
					}
				}
				return false;
			}

			function moveOnce(boundToLine)
			{
				if (ch == (dir < 0 ? 0 : lineObj.text.length))
				{
					if (!boundToLine && findNextLine())
						ch = dir < 0 ? lineObj.text.length : 0;
					else
						return false;
				}
				else
				{
					ch += dir;
				}
				return true;
			}

			if (unit == "char")
			{
				moveOnce();
			}
			else if (unit == "column")
			{
				moveOnce(true);
			}
			else if (unit == "word")
			{
				var sawWord = false;
				for (; ;)
				{
					if (dir < 0) if (!moveOnce()) break;
					if (isWordChar(lineObj.text.charAt(ch)))
					{
						sawWord = true;
					}
					else if (sawWord)
					{
						if (dir < 0)
						{
							dir = 1;
							moveOnce();
						}
						break;
					}

					if (dir > 0 && !moveOnce())
						break;
				}
			}
			return {line: line, ch: ch};
		},

		MoveH: function(dir, unit)
		{
			var pos = dir < 0 ? this.oSel.from : this.oSel.to;
			if (this.shiftSelecting || this.PosEq(this.oSel.from, this.oSel.to))
				pos = this.FindPosH(dir, unit);

			this.SetCursor(pos.line, pos.ch, true);
		},

		DeleteH: function(dir, unit)
		{
			if (!this.PosEq(this.oSel.from, this.oSel.to))
				this.ReplaceRange("", this.oSel.from, this.oSel.to);
			else if (dir < 0)
				this.ReplaceRange("", this.FindPosH(dir, unit), this.oSel.to);
			else
				this.ReplaceRange("", this.oSel.from, this.FindPosH(dir, unit));

			this.userSelChange = true;
		},

		MoveV: function(dir, unit)
		{
			var
				screen, target, th,
				pos = this.LocalCoords(this.oSel.inverted ? this.oSel.from : this.oSel.to, true);

			if (this.goalColumn != null)
				pos.x = this.goalColumn;

			if (unit == "page")
			{
				screen = Math.min(this.pScroller.clientHeight, window.innerHeight || document.documentElement.clientHeight);
				target = this.CoordsChar(pos.x, pos.y + screen * dir);
			}
			else if (unit == "line")
			{
				th = this.TextHeight();
				target = this.CoordsChar(pos.x, pos.y + .5 * th + dir * th);
			}

			if (unit == "page")
				this.pScrollbar.scrollTop += this.LocalCoords(target, true).y - pos.y;
			this.SetCursor(target.line, target.ch, true);
			this.goalColumn = pos.x;
		},

		FindWordAt: function(pos)
		{
			var
				line = this.GetLine(pos.line).text,
				start = pos.ch,
				end = pos.ch;

			if (line)
			{
				if (pos.after === false || end == line.length)
					--start;
				else
					++end;

				var
					startChar = line.charAt(start),
					check = isWordChar(startChar) ? isWordChar : /\s/.test(startChar) ? function (ch){return /\s/.test(ch);} : function (ch){return !/\s/.test(ch) && !isWordChar(ch);};

				while (start > 0 && check(line.charAt(start - 1)))
					--start;
				while (end < line.length && check(line.charAt(end)))
					++end;
			}
			return {from: {line: pos.line, ch: start}, to: {line: pos.line, ch: end}};
		},


		// Key mapping
		InitKeyEngine: function()
		{
			this.Map = {
				"Left": "goCharLeft",
				"Right": "goCharRight",
				"Up": "goLineUp",
				"Down": "goLineDown",
				"End": "goLineEnd",
				"Home": "goHome",
				"PageUp": "goPageUp", "PageDown": "goPageDown",
				"Delete": "delCharRight",
				"Backspace": "delCharLeft",
				"Tab": "tab",
				"Shift-Tab": "indentLess",
				"Enter": "newlineAndIndent",
				"Insert": "toggleOverwrite"
			};
			if(BX.browser.IsMac())
			{
				this.Map["Cmd-A"] = "selectAll";
				this.Map["Cmd-D"] = "deleteLine";
				this.Map["Cmd-Z"] = "undo";
				//this.Map["Shift-Cmd-Z"] = "redo";
				//this.Map["Cmd-Y"] = "redo";
				this.Map["Cmd-Up"] = "goDocStart";
				this.Map["Cmd-End"] = "goDocEnd";
				this.Map["Cmd-Down"] = "goDocEnd";
				this.Map["Alt-Left"] = "goWordLeft";
				this.Map["Alt-Right"] = "goWordRight";
				this.Map["Cmd-Left"] = "goLineStart";
				this.Map["Cmd-Right"] = "goLineEnd";
				this.Map["Alt-Backspace"] = "delWordLeft";
				this.Map["Ctrl-Alt-Backspace"] = "delWordRight";
				this.Map["Alt-Delete"] = "delWordRight";
			}
			else
			{
				this.Map["Ctrl-A"] = "selectAll";
				this.Map["Ctrl-D"] = "deleteLine";
				this.Map["Ctrl-Z"] = "undo";
				//this.Map["Shift-Ctrl-Z"] = "redo";
				//this.Map["Ctrl-Y"] = "redo";
				this.Map["Ctrl-Home"] = "goDocStart";
				this.Map["Alt-Up"] = "goDocStart";
				this.Map["Ctrl-End"] = "goDocEnd";
				this.Map["Ctrl-Down"] = "goDocEnd";
				this.Map["Ctrl-Left"] = "goWordLeft";
				this.Map["Ctrl-Right"] = "goWordRight";
				this.Map["Alt-Left"] = "goLineStart";
				this.Map["Alt-Right"] = "goLineEnd";
				this.Map["Ctrl-Backspace"] = "delWordLeft";
				this.Map["Ctrl-Delete"] = "delWordRight";
			}

			this.keyNames = {3: "Enter",8: "Backspace",9: "Tab",13: "Enter",16: "Shift",17: "Ctrl",18: "Alt",19: "Pause",20: "CapsLock",27: "Esc", 32: "Space", 33: "PageUp", 34: "PageDown", 35: "End",36: "Home", 37: "Left", 38: "Up", 39: "Right", 40: "Down", 44: "PrintScrn", 45: "Insert",46: "Delete", 59: ";", 91: "Mod", 92: "Mod", 93: "Mod", 109: "-", 107: "=", 127: "Delete",186: ";", 187: "=", 188: ",", 189: "-", 190: ".", 191: "/", 192: "`", 219: "[", 220: "\\",221: "]", 222: "'", 63276: "PageUp", 63277: "PageDown", 63275: "End", 63273: "Home",63234: "Left", 63232: "Up", 63235: "Right", 63233: "Down", 63302: "Insert", 63272: "Delete"};

			var i;
			for (i = 0; i < 10; i++) // Number keys
				this.keyNames[i + 48] = String(i);

			for (i = 65; i <= 90; i++) // Alphabetic keys
				this.keyNames[i] = String.fromCharCode(i);

			for (i = 1; i <= 12; i++) // Function keys
				this.keyNames[i + 111] = this.keyNames[i + 63235] = "F" + i;
		},

		LookupKey: function(name, handle, stop)
		{
			var found = this.Map[name];
			if (found === false)
			{
				if (stop && typeof stop == 'function')
					stop();
				return true;
			}

			return found != null && handle(found);
		},

		GetCommand: function(command)
		{
			return {
				selectAll: BX.proxy(function(){this.DoSelection({line: 0, ch: 0}, {line: this.oDoc.size - 1});}, this),
				killLine: BX.proxy(function(){
					var
						from = this.GetCursor(true),
						to = this.GetCursor(false),
						sel = !this.PosEq(from, to);

					if (!sel && this.GetLineText(from.line).length == from.ch)
						this.ReplaceRange("", from, {line: from.line + 1, ch: 0});
					else
						this.ReplaceRange("", from, sel ? to : {line: from.line});

				}, this),
				deleteLine: BX.proxy(function(){
					var l = this.GetCursor().line;
					this.ReplaceRange("", {line: l, ch: 0}, {line: l});
				}, this),
				goCharLeft:BX.proxy(function(){this.MoveH(-1, "char");}, this),
				goCharRight: BX.proxy(function(){this.MoveH(1, "char");}, this),
				goColumnLeft: BX.proxy(function(){this.MoveH(-1, "column");}, this),
				goColumnRight: BX.proxy(function(){this.MoveH(-1, "column");}, this),
				goDocStart: BX.proxy(function(){this.SetCursor(0, 0, true);}, this),
				goDocEnd: BX.proxy(function(){this.DoSelection({line: this.oDoc.size - 1}, null, true);}, this),
				goLineStart: BX.proxy(function(){this.SetCursor(this.GetCursor().line, 0, true);}, this),
				goHome: BX.proxy(function(){
					var
						cur = this.GetCursor(),
						text = this.GetLineText(cur.line),
						firstNonWS = Math.max(0, text.search(/\S/));
					this.SetCursor(cur.line, cur.ch <= firstNonWS && cur.ch ? 0 : firstNonWS, true);
				}, this),
				goLineEnd: BX.proxy(function(){this.DoSelection({line: this.GetCursor().line}, null, true);}, this),
				goLineUp: BX.proxy(function(){this.MoveV(-1, "line");}, this),
				goLineDown: BX.proxy(function(){this.MoveV(1, "line");}, this),
				goPageUp: BX.proxy(function(){this.MoveV(-1, "page");}, this),
				goPageDown: BX.proxy(function(){this.MoveV(1, "page");}, this),
				goWordLeft: BX.proxy(function(){this.MoveH(-1, "word");}, this),
				goWordRight: BX.proxy(function(){this.MoveH(1, "word");}, this),
				delCharLeft: BX.proxy(function(){this.DeleteH(-1, "char");}, this),
				delCharRight: BX.proxy(function(){this.DeleteH(1, "char");}, this),
				delWordLeft: BX.proxy(function(){this.DeleteH(-1, "word");}, this),
				delWordRight: BX.proxy(function(){this.DeleteH(1, "word");}, this),
				indentLess: BX.proxy(function(){this.IndentSelection("subtract");}, this),
				tab: BX.proxy(function(){if (this.PosEq(this.oSel.from, this.oSel.to)){this.ReplaceSelection("\t", "end");}else{this.IndentSelection("add");}}, this),
				undo: BX.proxy(this.Undo, this),
				//redo: BX.proxy(this.Redo, this),
				enter: BX.proxy(function(){
					this.ReplaceSelection("\n", "end");
					this.IndentLine(this.GetCursor().line);
				}, this)
			}[command];
		},

		IndentSelection: function(syntax)
		{
			this.OnBeforeAction();
			this.IndentSelected(syntax);
			this.OnAfterAction();
		},

		DoSelection: function(from, to, bUser)
		{
			this.OnBeforeAction();
			var
				f = this.ClipPos(from),
				t = this.ClipPos(to || from);

			if (bUser)
				this.SetSelectionUser(f, t);
			else
				this.SetSelection(f, t);

			this.OnAfterAction();
		},

		GetLineText: function(line)
		{
			if (line >= 0 && line < this.oDoc.size)
				return this.GetLine(line).text;
			return '';
		},


		CheckLinesHeight: function(textValue, lineStart, lineEnd)
		{
			var arStr = textValue.split("\n"), i, s, h, result = false, h0;
			//if(lineEnd === false)
			lineEnd = arStr.length - 1;
			//if(lineStart < 0)
			lineStart = 0;

			for(i = lineStart; i <= lineEnd; i++)
			{
				if(s = this.pLineNumTA.childNodes[i])
				{
					h = this.GetLineHeight(arStr[i]);
					h0 = parseInt(s.style.height);
					if (h > this.lineHeight || s.style.height != "")
					{
						s.style.height = arStr[i] ? h + "px" : "";
						if (h0 != h) // Now we know that at least one line was changed
							result = true;
					}
				}
			}

			return result;
		},

		// Add new line nums or hide if count more than needed
		BuildLineNum: function(count)
		{
			count = parseInt(count);
			if (count !== this.lineNums)
			{
				if (count < 1)
					count = 1;

				if (this.lineNums < count)
				{
					for (var i = this.lineNums + 1; i <= count; i++)
						this.pLineNumTA.appendChild(BX.create("DIV", {html: i}));

					this.lineNums = count;
				}
			}
		},

		TextOnKeydown: function(e)
		{
			if(!e)
				e = window.event;

			var key = e.which || e.keyCode;
			if(key == 9) // Tab
			{
				this.OnTab(e, this.ShiftPressed(e));
				return BX.PreventDefault(e);
			}
		},

		TextOnKeyup: function(e, key)
		{
			if(!e)
				e = window.event;

			if (!key)
				key = e.which || e.keyCode;

			//17 - Ctrl, 18 Alt, 9 - tab, 27 - esc
			if (!{17: true, 18 : true, 9: true, 27: true}[key])
				this.CheckLineSelection();
		},

		TextOnMousedown: function(e)
		{
			this.MoveLineSelection(true);
		},

		ShiftPressed: function(e)
		{
			if (window.event)
				return !!window.event.shiftKey;
			return !!(e.shiftKey || e.modifiers > 3);
		},

		OnTab: function(e, bShift)
		{
			if(this.bDisableTab)
				return;

			if (BX.browser.IsIE())
				this.bDisableTab = true;

			var
				i, endText, startText, tmp,
				tab = "\t",
				taSel = this.GetTASelection(),
				from = taSel.start,
				to = taSel.end,
				source = this.GetTAValue().replace(/\r/g, ""),
				txt = source.substring(from, to),
				posFrom = from,
				posTo = to;

			if (!bShift) // Insert TABulation
			{
				if (txt == "") // One line
				{
					source = source.substr(0, from) + tab + source.substr(to);
					posFrom = from + 1;
					posTo = posFrom;
				}
				else
				{
					from = source.substr(0, from).lastIndexOf("\n") + 1;
					if (from <= 0)
						from = 0;
					endText = source.substr(to);
					startText = source.substr(0, from);
					tmp = source.substring(from, to).split("\n");
					txt = tab + tmp.join("\n" + tab);
					source = startText + txt + endText;
					posFrom = from;
					posTo= source.indexOf("\n", startText.length + txt.length);

					if(posTo == -1)
						posTo = source.length;
				}
			}
			else // Remove TABulation
			{
				if (txt == "") // One line
				{
					if (from <= 0)
						from = 1;
					if(source.substring(from - 1, from) == tab)
					{
						source = source.substr(0, from - 1) + source.substr(to);
						posFrom = posTo = from - 1;
					}
				}
				else
				{
					from = source.substr(0, from).lastIndexOf("\n") + 1;
					endText = source.substr(to);
					startText = source.substr(0, from);
					tmp = source.substring(from, to).split("\n");
					txt = "";
					for(i = 0; i < tmp.length; i++)
					{
						for(j = 0, l = this.tabSymTA.length; j < l; j++)
						{
							if(tmp[i].charAt(0) == tab)
							{
								tmp[i] = tmp[i].substr(1);
								j = l;
							}
						}
						txt += tmp[i];
						if(i < tmp.length - 1)
							txt += "\n";
					}

					source = startText + txt + endText;
					posFrom = from;
					posTo = source.indexOf("\n", startText.length + txt.length);
					if(posTo == -1)
						posTo = source.length;
				}
			}

			this.SetTAValue(source);

			if (BX.browser.IsIE())
			{
				var _this = this;
				this.SetIETASelection(posFrom, posTo);
				setTimeout(function(){_this.bDisableTab = false;}, 80);
			}
			else
			{
				this.pTA.selectionStart = posFrom;
				this.pTA.selectionEnd = posTo;
			}

			this.CheckLineSelection(true);
		},

		CheckLineSelection: function(bChanges)
		{
			if (this.highlightMode)
				return;

			var sel = this.GetSelectionInfo();

			if (bChanges !== false || sel.source != this.curSel.source)
				this.ManageSize(false);

			this.curSel = sel;
		},

		ManageSize: function(bCheckLines)
		{
			if(this.highlightMode)
				return false;

			var taW, taH;
			//Handle height ! it's important to handle height first
			taH = parseInt(this.pTA.scrollHeight, 10);

			this.curSel = this.GetSelectionInfo();
			if(!isNaN(taH) && taH >= 0)
			{
				this.pContTA.style.height = (taH + 2) + "px";
				this.pTA.style.height = (taH + 2) + "px";
			}

			taW = this.pTA.scrollWidth;
			// Mantis: 48575
			if (BX.browser.IsChrome())
			{
				taW += 18;
			}

			if (parseInt(this.pTA.style.width) < taW)
			{
				this.pTA.style.width = taW + "px";
				this.pContTA.style.width = taW + "px";
			}

			if (bCheckLines !== false)
				this.CheckLineSelection(true);

			if(this.curSel.nbLine >= 0)
			{
				this.BuildLineNum(this.curSel.nbLine);

				var lastLine = this.GetLinePos(this.curSel.nbLine); // Last line position
				if (lastLine.top + lastLine.height < taH && this.curSel.nbLine < this.lineNums)
				{
					var h = parseInt(lastLine.top + lastLine.height);
					if (!isNaN(h) && h >= 0)
					{
						this.pContTA.style.height = (h + 2) + "px";
						this.pTA.style.height = (h + 2) + "px";
					}
				}
			}

			this.pTA.scrollTop = "0px";
			this.pTA.scrollLeft = "0px";
		},

		GetSelectionInfo: function()
		{
			if (!this.curSel)
				this.curSel = {};

			var
				taSel = this.GetTASelection(),
				start = taSel.start,
				end = taSel.end,
				str;

			var
				sel = {
					selectionStart: start,
					selectionEnd: end,
					source: this.GetTAValue().replace(/\r/g, ""),
					linePos: 1,
					lineNb: 1,
					carretPos: 0,
					curLine: "",
					cursorIndex: 0,
					direction: this.curSel.direction
				},
				splitTab = sel.source.split("\n"),
				nbLine = splitTab.length,
				nbChar = sel.source.length - (nbLine - 1);

			if (nbChar < 0)
				nbChar = 0;

			sel.nbLine = nbLine;
			sel.nbChar = nbChar;

			if(start > 0)
			{
				str = sel.source.substr(0,start);
				sel.carretPos = start - str.lastIndexOf("\n");
				sel.linePos = str.split("\n").length;
			}
			else
			{
				sel.carretPos = 1;
			}

			if (sel.linePos < 1)
				sel.linePos = 1;

			if(end > start)
				sel.lineNb = sel.source.substring(start, end).split("\n").length;

			sel.cursorIndex = start;
			sel.curLine = splitTab[sel.linePos - 1];

			// Check selection direction
			if(sel.selectionStart == this.curSel.selectionStart)
			{
				if(sel.selectionEnd > this.curSel.selectionEnd)
					sel.direction = "down";
				else //if(sel.selectionEnd == this.curSel.selectionStart)
					sel.direction = this.curSel.direction;
			}
			else if(sel.selectionStart == this.curSel.selectionEnd && sel.selectionEnd > this.curSel.selectionEnd)
			{
				sel.direction = "down";
			}
			else
			{
				sel.direction = "up";
			}

			this.SetStatusBarInfo(sel);
			return sel;
		},

		GetTASelection: function()
		{
			var start = 0, end = 0;
			try
			{
				if (BX.browser.IsIE9() || this.pTA.selectionStart != undefined)
				{
					start = this.pTA.selectionStart;
					end = this.pTA.selectionEnd;
				}
				else if (BX.browser.IsIE())
				{
					var ch = "\001";
					var range = document.selection.createRange();
					var savedText = range.text.replace(/\r/g, "");
					var dubRange = range.duplicate();
					if (savedText != '')
						range.collapse();
					dubRange.moveToElementText(this.pTA);
					range.text = ch;
					end = start = dubRange.text.replace(/\r/g, "").indexOf(ch);
					range.moveStart('character', -1);
					range.text = "";

					if (savedText != '')
						end += savedText.length;
				}
				else
				{
					start = this.pTA.selectionStart;
					end = this.pTA.selectionEnd;
				}
			}
			catch(e)
			{
				start = 0;
				end = 0;
			}

			return {start: start, end: end};
		},

		GetLinePos: function(ind)
		{
			var
				pLine = this.pLineNumTA.childNodes[ind - 1],
				top = pLine ? pLine.offsetTop : this.lineHeight * (ind - 1),
				height = pLine ? pLine.offsetHeight : this.lineHeight;

			return {
				top: parseInt(top) || 0,
				height: parseInt(height) || 0
			};
		},

		Undo: function()
		{
			// Mantis: 61354
			//this.OnBeforeAction();
			this.UndoRedo(this.oHistory.done, this.oHistory.undone);
			//this.OnAfterAction();
		},

//		Redo: function()
//		{
//			this.OnBeforeAction();
//			this.UndoRedo(this.oHistory.undone, this.oHistory.done);
//			this.OnAfterAction();
//		},

		UndoRedo: function(from, to)
		{
			if (!from.length)
				return;
			var
				i,change,replaced,end,pos,
				set = from.pop(),
				out = [];

			for (i = set.length - 1; i >= 0; i -= 1)
			{
				change = set[i];
				replaced = [];
				end = change.start + change.added;
				pos = {
					line: change.start + change.old.length - 1,
					ch: this.GetUndoChar(replaced[replaced.length - 1], change.old[change.old.length - 1])
				};
				this.UpdateLinesNoUndo({line: change.start, ch: 0}, {line: end - 1, ch: this.GetLine(end - 1).text.length}, change.old, pos, pos);
			}
			this.updateInput = true;
			to.push(out);
		},

		GetUndoChar: function(from, to)
		{
			if (!to)
				return 0;
			if (!from)
				return to.length;

			for (var i = from.length, j = to.length; i >= 0 && j >= 0; --i, --j)
				if (from.charAt(i) != to.charAt(j))
					break;

			return j + 1;
		}
	};

	// The character stream used by a syntax's parser.
	function JCLineChain(string, tabSize)
	{
		this.pos = this.start = 0;
		this.string = string;
		this.tabSize = tabSize || 8;
	}

	JCLineChain.prototype = {
		eol: function ()
		{
			return this.pos >= this.string.length;
		},
		sol: function ()
		{
			return this.pos == 0;
		},
		peek: function ()
		{
			return this.string.charAt(this.pos) || undefined;
		},
		next: function ()
		{
			if (this.pos < this.string.length)
				return this.string.charAt(this.pos++);
		},
		eat: function (match)
		{
			var
				ch = this.string.charAt(this.pos),
				ok = (typeof match == "string") ? (ch == match) : (ch && (match.test ? match.test(ch) : match(ch)));

			if (ok)
			{
				++this.pos;
				return ch;
			}
		},
		eatWhile: function (match)
		{
			var start = this.pos;
			while (this.eat(match))
			{
			}
			return this.pos > start;
		},
		eatSpace: function ()
		{
			var start = this.pos;
			while (/[\s\u00a0]/.test(this.string.charAt(this.pos)))
				++this.pos;
			return this.pos > start;
		},
		skipToEnd: function ()
		{
			this.pos = this.string.length;
		},
		skipTo: function (ch)
		{
			var found = this.string.indexOf(ch, this.pos);
			if (found > -1)
			{
				this.pos = found;
				return true;
			}
		},
		backUp: function (n)
		{
			this.pos -= n;
		},
		column: function ()
		{
			return countColumn(this.string, this.start, this.tabSize);
		},
		indentation: function ()
		{
			return countColumn(this.string, null, this.tabSize);
		},
		match: function (pattern, consume, caseInsensitive)
		{
			var match;
			if (typeof pattern == "string")
			{
				var cased = function (str)
				{
					return caseInsensitive ? str.toLowerCase() : str;
				};
				if (cased(this.string).indexOf(cased(pattern), this.pos) == this.pos)
				{
					if (consume !== false)
						this.pos += pattern.length;
					return true;
				}
			}
			else if (pattern !== null)
			{
				match = this.string.slice(this.pos).match(pattern);
				if (match && consume !== false)
					this.pos += match[0].length;
			}
			return match;
		},
		current: function ()
		{
			return this.string.slice(this.start, this.pos);
		}
	};

	function JCLine(text, styles)
	{
		this.styles = styles || [text, null];
		this.text = text;
		this.height = 1;
	}

	JCLine.inheritMarks = function (text, orig)
	{
		var
			ln = new JCLine(text),
			mk = orig && orig.marked;

		if (mk)
		{
			for (var i = 0; i < mk.length; ++i)
			{
				if (mk[i].to == null && mk[i].style)
				{
					var
						newmk = ln.marked || (ln.marked = []),
						mark = mk[i],
						nmark = mark.dup();

					newmk.push(nmark);
					nmark.attach(ln);
				}
			}
		}
		return ln;
	};

	JCLine.prototype = {
		Replace: function (from, to_, text)
		{
			var
				st = [],
				mk = this.marked,
				to = to_ == null ? this.text.length : to_;

			copyStyles(0, from, this.styles, st);
			if (text) st.push(text, null);
			copyStyles(to, this.text.length, this.styles, st);
			this.styles = st;
			this.text = this.text.slice(0, from) + text + this.text.slice(to);
			this.statusAfter = null;
			if (mk)
			{
				var diff = text.length - (to - from);
				for (var i = 0; i < mk.length; ++i)
				{
					var mark = mk[i];
					mark.clipTo(from == null, from || 0, to_ == null, to, diff);
					if (mark.isDead())
					{
						mark.detach(this);
						mk.splice(i--, 1);
					}
				}
			}
		},
		split: function (pos, textBefore)
		{
			var
				st = [textBefore, null],
				mk = this.marked;

			copyStyles(pos, this.text.length, this.styles, st);
			var taken = new JCLine(textBefore + this.text.slice(pos), st);
			if (mk)
			{
				for (var i = 0; i < mk.length; ++i)
				{
					var mark = mk[i];
					var newmark = mark.split(pos, textBefore.length);
					if (newmark)
					{
						if (!taken.marked)
							taken.marked = [];
						taken.marked.push(newmark);
						newmark.attach(taken);
						if (newmark == mark)
							mk.splice(i--, 1);
					}
				}
			}
			return taken;
		},

		append: function (line)
		{
			var mylen = this.text.length, mk = line.marked, mymk = this.marked;
			this.text += line.text;
			copyStyles(0, line.text.length, line.styles, this.styles);
			if (mymk)
			{
				for (var i = 0; i < mymk.length; ++i)
					if (mymk[i].to == null) mymk[i].to = mylen;
			}
			if (mk && mk.length)
			{
				if (!mymk) this.marked = mymk = [];
				outer: for (var i = 0; i < mk.length; ++i)
				{
					var mark = mk[i];
					if (!mark.from)
					{
						for (var j = 0; j < mymk.length; ++j)
						{
							var mymark = mymk[j];
							if (mymark.to == mylen && mymark.sameSet(mark))
							{
								mymark.to = mark.to == null ? null : mark.to + mylen;
								if (mymark.isDead())
								{
									mymark.detach(this);
									mk.splice(i--, 1);
								}
								continue outer;
							}
						}
					}
					mymk.push(mark);
					mark.attach(this);
					mark.from += mylen;
					if (mark.to != null) mark.to += mylen;
				}
			}
		},
		fixMarkEnds: function (other)
		{
			var mk = this.marked, omk = other.marked;
			if (!mk) return;
			outer: for (var i = 0; i < mk.length; ++i)
			{
				var mark = mk[i], close = mark.to == null;
				if (close && omk)
				{
					for (var j = 0; j < omk.length; ++j)
					{
						var om = omk[j];
						if (!om.sameSet(mark) || om.from != null) continue;
						if (mark.from == this.text.length && om.to == 0)
						{
							omk.splice(j, 1);
							mk.splice(i--, 1);
							continue outer;
						}
						else
						{
							close = false;
							break;
						}
					}
				}
				if (close) mark.to = this.text.length;
			}
		},
		fixMarkStarts: function ()
		{
			var mk = this.marked;
			if (!mk) return;
			for (var i = 0; i < mk.length; ++i)
				if (mk[i].from == null) mk[i].from = 0;
		},
		addMark: function (mark)
		{
			mark.attach(this);
			if (this.marked == null)
				this.marked = [];
			this.marked.push(mark);
			this.marked.sort(function (a, b)
			{
				return (a.from || 0) - (b.from || 0);
			});
		},

		highlight: function (syntax, status, tabSize)
		{
			var stream = new JCLineChain(this.text, tabSize), st = this.styles, pos = 0;
			var changed = false, curWord = st[0], prevWord;
			if (this.text == "" && syntax.blankLine) syntax.blankLine(status);
			while (!stream.eol())
			{
				var style = syntax.HandleChar(stream, status);
				var substr = this.text.slice(stream.start, stream.pos);
				stream.start = stream.pos;
				if (pos && st[pos - 1] == style)
				{
					st[pos - 2] += substr;
				}
				else if (substr)
				{
					if (!changed && (st[pos + 1] != style || (pos && st[pos - 2] != prevWord)))
						changed = true;
					st[pos++] = substr;
					st[pos++] = style;
					prevWord = curWord;
					curWord = st[pos];
				}
				// Give up when line is ridiculously long
				if (stream.pos > 5000)
				{
					st[pos++] = this.text.slice(stream.pos);
					st[pos++] = null;
					break;
				}
			}
			if (st.length != pos)
			{
				st.length = pos;
				changed = true;
			}
			if (pos && st[pos - 2] != prevWord)
				changed = true;

			return changed || (st.length < 5 && this.text.length < 10 ? null : false);
		},
		getTokenAt: function (syntax, status, tabSize, ch)
		{
			var
				style,
				txt = this.text,
				stream = new JCLineChain(txt, tabSize);
			while (stream.pos < ch && !stream.eol())
			{
				stream.start = stream.pos;
				style = syntax.HandleChar(stream, status);
			}

			return {
				start: stream.start,
				end: stream.pos,
				string: stream.current(),
				className: style || null,
				status: status
			};
		},
		indentation: function (tabSize)
		{
			return countColumn(this.text, null, tabSize);
		},
		getElement: function (makeTab, wrapAt, wrapWBR)
		{
			var _this = this;
			var first = true, col = 0, specials = /[\t\u0000-\u0019\u200b\u2028\u2029\uFEFF]/g;
			var pre = BX.create("PRE");

			function span_(html, text, style)
			{
				if (!text)
					return;
				// Work around a bug where, in some compat syntaxs, IE ignores leading spaces
				if (first && BX.browser.IsIE() && text.charAt(0) == " ")
					text = "\u00a0" + text.slice(1);
				first = false;
				if (!specials.test(text))
				{
					col += text.length;
					var content = document.createTextNode(text);
				}
				else
				{
					var content = document.createDocumentFragment(), pos = 0;
					while (true)
					{
						specials.lastIndex = pos;
						var m = specials.exec(text);
						var skipped = m ? m.index - pos : text.length - pos;
						if (skipped)
						{
							content.appendChild(document.createTextNode(text.slice(pos, pos + skipped)));
							col += skipped;
						}
						if (!m) break;
						pos += skipped + 1;
						if (m[0] == "\t")
						{
							var tab = makeTab(col);
							content.appendChild(tab.element.cloneNode(true));
							col += tab.width;
						}
						else
						{
							content.appendChild(BX.create("TT", {props: {className: "bxce-invalidchar", title: "\\u" + m[0].charCodeAt(0).toString(16)}, html: "\u2022"}));
							col += 1;
						}
					}
				}
				if (style)
					html.appendChild(BX.create("TT", {props: {className: style}})).appendChild(content);
				else
					html.appendChild(content);
			}

			var span = span_;
			if (wrapAt != null)
			{
				var
					outPos = 0,
					anchor = pre.anchor = BX.create("TT", {props: {className: "bxce-anchor"}});
				span = function (html, text, style)
				{
					var l = text.length;
					if (wrapAt >= outPos && wrapAt < outPos + l)
					{
						if (wrapAt > outPos)
						{
							span_(html, text.slice(0, wrapAt - outPos), style);
							// See comment at the definition of spanAffectsWrapping
							if (wrapWBR)
								html.appendChild(BX.create("WBR"));
						}
						html.appendChild(anchor);
						var cut = wrapAt - outPos;
						span_(anchor, BX.browser.IsOpera() ? text.slice(cut, cut + 1) : text.slice(cut), style);
						if (BX.browser.IsOpera())
							span_(html, text.slice(cut + 1), style);
						wrapAt--;
						outPos += l;
					}
					else
					{
						outPos += l;
						span_(html, text, style);
						if (outPos == wrapAt && outPos == len)
						{
							BX.adjust(anchor, {text: _this.GetEolHtml()});
							html.appendChild(anchor);
						}
						// Stop outputting HTML when gone sufficiently far beyond measure
						else if (outPos > wrapAt + 10 && /\s/.test(text))
						{
							span = function ()
							{
							};
						}
					}
				};
			}

			var st = this.styles, allText = this.text, marked = this.marked;
			var len = allText.length;

			function styleToClass(style)
			{
				if (!style)
					return null;
				return "bxce-" + style.replace(/ +/g, " bxce-");
			}

			if (!allText && wrapAt == null)
			{
				span(pre, " ");
			}
			else if (!marked || !marked.length)
			{
				for (var i = 0, ch = 0; ch < len; i += 2)
				{
					var str = st[i], style = st[i + 1], l = str.length;
					if (ch + l > len) str = str.slice(0, len - ch);
					ch += l;
					span(pre, str, styleToClass(style));
				}
			}
			else
			{
				var pos = 0, i = 0, text = "", style, sg = 0;
				var nextChange = marked[0].from || 0, marks = [], markpos = 0;
				var advanceMarks = function ()
				{
					var m;
					while (markpos < marked.length &&
						((m = marked[markpos]).from == pos || m.from == null))
					{
						if (m.style != null) marks.push(m);
						++markpos;
					}
					nextChange = markpos < marked.length ? marked[markpos].from : Infinity;
					for (var i = 0; i < marks.length; ++i)
					{
						var to = marks[i].to;
						if (to == null)
							to = Infinity;
						if (to == pos)
							marks.splice(i--, 1);
						else
							nextChange = Math.min(to, nextChange);
					}
				};
				var m = 0;
				while (pos < len)
				{
					if (nextChange == pos) advanceMarks();
					var upto = Math.min(len, nextChange);
					while (true)
					{
						if (text)
						{
							var end = pos + text.length;
							var appliedStyle = style;
							for (var j = 0; j < marks.length; ++j)
								appliedStyle = (appliedStyle ? appliedStyle + " " : "") + marks[j].style;
							span(pre, end > upto ? text.slice(0, upto - pos) : text, appliedStyle);
							if (end >= upto)
							{
								text = text.slice(upto - pos);
								pos = upto;
								break;
							}
							pos = end;
						}
						text = st[i++];
						style = styleToClass(st[i++]);
					}
				}
			}
			return pre;
		},
		cleanUp: function ()
		{
			this.parent = null;
			if (this.marked)
			{
				for (var i = 0, e = this.marked.length; i < e; ++i)
					this.marked[i].detach(this);
			}
		},

		GetEolHtml: function()
		{
			if (this._eolHtml != undefined)
				return this._eolHtml;

			this._eolHtml = " ";
			if (BX.browser.IsFirefox() || BX.browser.IsIE())
				this._eolHtml = "\u200b";
			else if (BX.browser.IsOpera())
				this._eolHtml = "";

			return this._eolHtml;
		}
	};

	// Utility used by replace and split above
	function copyStyles(from, to, source, dest)
	{
		for (var i = 0, pos = 0, status = 0; pos < to; i += 2)
		{
			var
				part = source[i],
				end = pos + part.length;

			if (status == 0)
			{
				if (end > from)
					dest.push(part.slice(from - pos, Math.min(part.length, to - pos)), source[i + 1]);
				if (end >= from)
					status = 1;
			}
			else if (status == 1)
			{
				if (end > to)
					dest.push(part.slice(0, to - pos), source[i + 1]);
				else
					dest.push(part, source[i + 1]);
			}
			pos = end;
		}
	}

	// Data structure contains lines.
	function JCLineHolder(lines)
	{
		this.lines = lines;
		this.parent = null;
		for (var i = 0, e = lines.length, height = 0; i < e; ++i)
		{
			lines[i].parent = this;
			height += lines[i].height;
		}
		this.height = height;
	}

	JCLineHolder.prototype = {
		GetSize: function ()
		{
			return this.lines.length;
		},
		Remove: function (at, n, callbacks)
		{
			for (var i = at, e = at + n; i < e; ++i)
			{
				var line = this.lines[i];
				this.height -= line.height;
				line.cleanUp();
				if (line.handlers)
				{
					for (var j = 0; j < line.handlers.length; ++j)
						callbacks.push(line.handlers[j]);
				}
			}
			this.lines.splice(at, n);
		},
		Collapse: function (lines)
		{
			lines.splice.apply(lines, [lines.length, 0].concat(this.lines));
		},
		SetHeight: function (at, lines, height)
		{
			this.height += height;
			this.lines = this.lines.slice(0, at).concat(lines).concat(this.lines.slice(at));
			for (var i = 0, e = lines.length; i < e; ++i)
				lines[i].parent = this;
		},
		_Iteration: function (at, n, op)
		{
			for (var e = at + n; at < e; ++at)
				if (op(this.lines[at]))
					return true;
		}
	};

	function JCDocHolder(children)
	{
		this.children = children;
		var
			i, size = 0,
			height = 0;
		for (i = 0; i < children.length; ++i)
		{
			size += children[i].GetSize();
			height += children[i].height;
			children[i].parent = this;
		}
		this.size = size;
		this.height = height;
		this.parent = null;
	}

	JCDocHolder.prototype = {
		GetSize: function ()
		{
			return this.size;
		},
		Remove: function (at, n, callbacks)
		{
			this.size -= n;
			var i, child,sz, rm;
			for (i = 0; i < this.children.length; ++i)
			{
				child = this.children[i],
					sz = child.GetSize();
				if (at < sz)
				{
					rm = Math.min(n, sz - at), oldHeight = child.height;
					child.Remove(at, rm, callbacks);
					this.height -= oldHeight - child.height;
					if (sz == rm)
					{
						this.children.splice(i--, 1);
						child.parent = null;
					}
					if ((n -= rm) == 0)
						break;
					at = 0;
				}
				else
				{
					at -= sz;
				}
			}
			if (this.size - n < 25)
			{
				var lines = [];
				this.Collapse(lines);
				this.children = [new JCLineHolder(lines)];
				this.children[0].parent = this;
			}
		},
		Collapse: function (lines)
		{
			for (var i = 0, e = this.children.length; i < e; ++i)
				this.children[i].Collapse(lines);
		},
		Insert: function (at, lines)
		{
			var height = 0;
			for (var i = 0, e = lines.length; i < e; ++i)
				height += lines[i].height;
			this.SetHeight(at, lines, height);
		},
		SetHeight: function (at, lines, height)
		{
			this.size += lines.length;
			this.height += height;

			var i, child, size, spilled, lineHolder;
			for (i = 0; i < this.children.length; ++i)
			{
				child = this.children[i];
				size = child.GetSize();
				if (at <= size)
				{
					child.SetHeight(at, lines, height);
					if (child.lines && child.lines.length > 50)
					{
						while (child.lines.length > 50)
						{
							spilled = child.lines.splice(child.lines.length - 25, 25);
							lineHolder = new JCLineHolder(spilled);
							child.height -= lineHolder.height;
							this.children.splice(i + 1, 0, lineHolder);
							lineHolder.parent = this;
						}
						this.CheckSpill();
					}
					break;
				}
				at -= size;
			}
		},
		CheckSpill: function ()
		{
			if (this.children.length <= 10)
				return;
			var
				spilled,sibling, copy,
				_this = this;
			do {
				spilled = _this.children.splice(_this.children.length - 5, 5);
				sibling = new JCDocHolder(spilled);
				if (!_this.parent)
				{
					copy = new JCDocHolder(_this.children);
					copy.parent = _this;
					_this.children = [copy, sibling];
					_this = copy;
				}
				else
				{
					_this.size -= sibling.size;
					_this.height -= sibling.height;
					_this.parent.children.splice(indexOf(_this.parent.children, _this) + 1, 0, sibling);
				}
				sibling.parent = _this.parent;
			}

			while (_this.children.length > 10);
			_this.parent.CheckSpill();
		},

		Iteration: function (from, to, op)
		{
			this._Iteration(from, to - from, op);
		},

		_Iteration: function (at, n, op)
		{
			var i, used,size;
			for (i = 0; i < this.children.length; ++i)
			{
				size = this.children[i].GetSize();
				if (at < size)
				{
					used = Math.min(n, size - at);
					if (this.children[i]._Iteration(at, used, op))
						return true;
					if ((n -= used) == 0)
						break;
					at = 0;
				}
				else
				{
					at -= size;
				}
			}
		}
	};

	function lineNo(line)
	{
		if (line.parent == null) return null;
		var cur = line.parent, no = indexOf(cur.lines, line);
		for (var chunk = cur.parent; chunk; cur = chunk, chunk = chunk.parent)
		{
			for (var i = 0, e = chunk.children.length; ; ++i)
			{
				if (chunk.children[i] == cur) break;
				no += chunk.children[i].GetSize();
			}
		}
		return no;
	}

	function History()
	{
		this.time = 0;
		this.done = [];
		this.undone = [];
		this.compound = 0;
		this.closed = false;
	}

	History.prototype = {
		addChange: function (start, added, old)
		{
			this.undone.length = 0;
			var time = +new Date, cur = this.done[this.done.length - 1], last = cur && cur[cur.length - 1];
			var dtime = time - this.time;

			if (this.compound && cur && !this.closed)
			{
				cur.push({start: start, added: added, old: old});
			}
			else if (dtime > 400 || !last || this.closed ||
				last.start > start + old.length || last.start + last.added < start)
			{
				this.done.push([
					{start: start, added: added, old: old}
				]);
				this.closed = false;
			}
			else
			{
				var startBefore = Math.max(0, last.start - start),
					endAfter = Math.max(0, (start + old.length) - (last.start + last.added));
				for (var i = startBefore; i > 0; --i) last.old.unshift(old[i - 1]);
				for (var i = endAfter; i > 0; --i) last.old.push(old[old.length - i]);
				if (startBefore) last.start = start;
				last.added += added - (old.length - startBefore - endAfter);
			}
			this.time = time;
		},
		startCompound: function ()
		{
			if (!this.compound++) this.closed = true;
		},
		endCompound: function ()
		{
			if (!--this.compound) this.closed = true;
		}
	};

//	function e_preventDefault(e)
//	{
//		if (e.preventDefault)
//			e.preventDefault();
//		else
//			e.returnValue = false;
//	}
//	function stopPropagation(e)
//	{
//		if (e.stopPropagation)
//			e.stopPropagation();
//		else
//			e.cancelBubble = true;
//	}

//	function e_stop(e)
//	{
//		e_preventDefault(e);
//		e_stopPropagation(e);
//	}

	//JCCodeEditor.e_stop = e_stop;
	//JCCodeEditor.e_preventDefault = e_preventDefault;
	//JCCodeEditor.e_stopPropagation = e_stopPropagation;

	function mouseButton(e)
	{
		var but = e.which;
		if (but == null)
		{
			if (e.button & 1)
				but = 1;
			else if (e.button & 2)
				but = 3;
			else if (e.button & 4)
				but = 2;
		}
		if (BX.browser.IsMac() && e.ctrlKey && but == 1)
			but = 3;
		return but;
	}

	function Delayed()
	{
		this.id = null;
	}

	Delayed.prototype = {
		set: function (ms, f)
		{
			clearTimeout(this.id);
			this.id = setTimeout(f, ms);
		}
	};


// TODO: !!!!!!!!!!!!!!!!!!!!!!!
//	var Pass = JCCodeEditor.Pass = {toString: function ()
//	{
//		return "JCCodeEditor.Pass";
//	}};
//
//	var lineSep = function ()
//	{
//		var te = BX.create("TEXTAREA");
//		te.value = "foo\nbar";
//		if (te.value.indexOf("\r") > -1)
//			return "\r\n";
//		return "\n";
//	}();

	// Counts the column offset in a string, taking tabs into account.
	// Used mostly to find indentation.
	function countColumn(string, end, tabSize)
	{
		tabSize = 4;
		if (end == null)
		{
			end = string.search(/[^\s\u00a0]/);
			if (end == -1)
				end = string.length;
		}

		for (var i = 0, n = 0; i < end; ++i)
		{
			if (string.charAt(i) == "\t")
				n += tabSize - (n % tabSize);
			else
				++n;
		}
		return n;
	}

	function GetOffset(node, screen)
	{
		try
		{
			var box = node.getBoundingClientRect();
			box = {
				top: box.top,
				left: box.left
			};
		}
		catch (e)
		{
			box = {
				top: 0,
				left: 0
			};
		}

		if (!screen)
		{
			// Get the toplevel scroll, working around browser differences.
			if (window.pageYOffset == null)
			{
				var
					doc = document.documentElement || document.body.parentNode;
				if (doc.scrollTop == null)
					doc = document.body;
				box.top += doc.scrollTop;
				box.left += doc.scrollLeft;
			}
			else
			{
				box.top += window.pageYOffset;
				box.left += window.pageXOffset;
			}
		}
		return box;
	}

	function indexOf(haystack, needle)
	{
		if (haystack.indexOf)
			return haystack.indexOf(needle);

		if (haystack.length)
		{
			for (var i = 0; i < haystack.length; ++i)
				if (haystack[i] == needle)
					return i;
		}

		return -1;
	}

	function isWordChar(ch)
	{
		return /\w/.test(ch) || ch.toUpperCase() != ch.toLowerCase();
	}

	// ************* Syntaxes defenitions *************
	{
		var Syntaxes = {};

		function copySyntaxStatus(syntax, status)
		{
			if (status === true)
				return status;

			if (syntax.copyStatus)
				return syntax.copyStatus(status);

			var newStatus = {}, n;
			for (n in status)
			{
				var val = status[n];
				if (val instanceof Array)
					val = val.concat([]);
				newStatus[n] = val;
			}
			return newStatus;
		}

		function prepareKeywords(arr)
		{
			var keys = {}, i;
			for (i = 0; i < arr.length; ++i)
				keys[arr[i]] = true;
			return keys;
		}

		Syntaxes.html = function()
		{
			var
				indentUnit = 4,
				arTags = {
					autoSelfClosers: prepareKeywords(['area', 'base', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr']),
					implicitlyClosed: prepareKeywords(['dd', 'li', 'optgroup', 'option', 'p', 'rp', 'rt', 'tbody', 'td', 'tfoot', 'th', 'tr']),
					contextGrabbers: {
						dd: prepareKeywords(['dd', 'dt']),
						dt: prepareKeywords(['dd', 'dt']),
						li: prepareKeywords(['li']),
						option: prepareKeywords(['option', 'optgroup']),
						optgroup: prepareKeywords(['optgroup']),
						p: prepareKeywords(['address', 'article', 'aside', 'blockquote', 'dir', 'div', 'dl', 'fieldset', 'footer', 'form','h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'menu', 'nav', 'ol', 'p', 'pre', 'section', 'table', 'ul']),
						rp: prepareKeywords(['rp', 'rt']),
						rt: prepareKeywords(['rp', 'rt']),
						tbody: prepareKeywords(['tbody', 'tfoot']),
						td: prepareKeywords(['td', 'th']),
						tfoot: prepareKeywords(['tbody']),
						th: prepareKeywords(['td', 'th']),
						thead: prepareKeywords(['tbody', 'tfoot']),
						tr: prepareKeywords(['tr'])
					},
					doNotIndent: prepareKeywords(['pre']),
					allowUnquoted: true,
					allowMissing: true
				},
				tagName, type;

			function InsideText(stream, status)
			{
				function chain(parser)
				{
					status.tokenize = parser;
					return parser(stream, status);
				}

				var ch = stream.next(), c, bAtom;
				if (ch == "<")
				{
					if (stream.eat("!"))
					{
						if (stream.eat("["))
						{
							return stream.match("CDATA[") ? chain(InsideBlock("atom", "]]>")) : null;
						}
						else if (stream.match("--"))
						{
							return chain(InsideBlock("comment", "-->"));
						}
						else if (stream.match("DOCTYPE", true, true))
						{
							stream.eatWhile(/[\w\._\-]/);
							return chain(Doctype(1));
						}
						else
						{
							return null;
						}
					}
					else if (stream.eat("?"))
					{
						stream.eatWhile(/[\w\._\-]/);
						status.tokenize = InsideBlock("meta", "?>");
						return "meta";
					}
					else
					{
						type = stream.eat("/") ? "closeTag" : "openTag";
						stream.eatSpace();
						tagName = "";
						while ((c = stream.eat(/[^\s\u00a0=<>\"\'\/?]/)))
							tagName += c;
						status.tokenize = InsideTag;
						return "tag";
					}
				}
				else if (ch == "&")
				{
					if (stream.eat("#"))
						bAtom = stream.eat("x") ? (stream.eatWhile(/[a-fA-F\d]/) && stream.eat(";")) : (stream.eatWhile(/[\d]/) && stream.eat(";"));
					else
						bAtom = stream.eatWhile(/[\w\.\-:]/) && stream.eat(";");

					return bAtom ? "atom" : "error";
				}
				else
				{
					stream.eatWhile(/[^&<]/);
					return null;
				}
			}

			function InsideTag(stream, status)
			{
				var ch = stream.next();
				if (ch == ">" || (ch == "/" && stream.eat(">")))
				{
					status.tokenize = InsideText;
					type = ch == ">" ? "endTag" : "selfcloseTag";
					return "tag";
				}
				else if (ch == "=")
				{
					type = "equals";
					return null;
				}
				else if (/[\'\"]/.test(ch))
				{
					status.tokenize = InsideAttr(ch);
					return status.tokenize(stream, status);
				}
				else
				{
					stream.eatWhile(/[^\s\u00a0=<>\"\'\/?]/);
					return "word";
				}
			}

			function InsideAttr(quote)
			{
				return function(stream, status)
				{
					while (!stream.eol())
					{
						if (stream.next() == quote)
						{
							status.tokenize = InsideTag;
							break;
						}
					}
					return "string";
				};
			}

			function InsideBlock(style, terminator)
			{
				return function(stream, status) {
					while (!stream.eol()) {
						if (stream.match(terminator)) {
							status.tokenize = InsideText;
							break;
						}
						stream.next();
					}
					return style;
				};
			}

			function Doctype(depth)
			{
				return function(stream, status)
				{
					var ch;
					while ((ch = stream.next()) != null)
					{
						if (ch == "<")
						{
							status.tokenize = Doctype(depth + 1);
							return status.tokenize(stream, status);
						} else if (ch == ">")
						{
							if (depth == 1)
							{
								status.tokenize = InsideText;
								break;
							} else
							{
								status.tokenize = Doctype(depth - 1);
								return status.tokenize(stream, status);
							}
						}
					}
					return "meta";
				};
			}

			var curState, setStyle;
			function pass()
			{
				for (var i = arguments.length - 1; i >= 0; i--)
					curState.cc.push(arguments[i]);
			}
			function cont()
			{
				pass.apply(null, arguments);
				return true;
			}

			function pushContext(tagName, startOfLine)
			{
				var noIndent = arTags.doNotIndent.hasOwnProperty(tagName) || (curState.context && curState.context.noIndent);
				curState.context =
				{
					prev: curState.context,
					tagName: tagName,
					indent: curState.indented,
					startOfLine: startOfLine,
					noIndent: noIndent
				};
			}

			function popContext()
			{
				if (curState.context)
					curState.context = curState.context.prev;
			}

			function element(type)
			{
				if (type == "openTag")
				{
					curState.tagName = tagName;
					return cont(attributes, endtag(curState.startOfLine));
				}
				else if (type == "closeTag")
				{
					var err = false;
					if (curState.context)
					{
						if (curState.context.tagName != tagName)
						{
							if (arTags.implicitlyClosed.hasOwnProperty(curState.context.tagName.toLowerCase()))
								popContext();
							err = !curState.context || curState.context.tagName != tagName;
						}
					}
					else
					{
						err = true;
					}
					if (err)
						setStyle = "error";
					return cont(endclosetag(err));
				}
				return cont();
			}

			function endtag(startOfLine)
			{
				return function(type)
				{
					if (type == "selfcloseTag" || (type == "endTag" && arTags.autoSelfClosers.hasOwnProperty(curState.tagName.toLowerCase())))
					{
						maybePopContext(curState.tagName.toLowerCase());
						return cont();
					}
					if (type == "endTag")
					{
						maybePopContext(curState.tagName.toLowerCase());
						pushContext(curState.tagName, startOfLine);
						return cont();
					}
					return cont();
				};
			}

			function endclosetag(err)
			{
				return function(type)
				{
					if (err)
						setStyle = "error";
					if (type == "endTag")
					{
						popContext();
						return cont();
					}
					setStyle = "error";
					return cont(arguments.callee);
				};
			}
			function maybePopContext(nextTagName)
			{
				var parentTagName;
				while (true)
				{
					if (!curState.context)
						return;
					parentTagName = curState.context.tagName.toLowerCase();
					if (!arTags.contextGrabbers.hasOwnProperty(parentTagName) || !arTags.contextGrabbers[parentTagName].hasOwnProperty(nextTagName))
						return;
					popContext();
				}
			}

			function attributes(type)
			{
				if (type == "word")
				{
					setStyle = "attribute";
					return cont(attribute, attributes);
				}

				if (type == "endTag" || type == "selfcloseTag")
				{
					return pass();
				}

				setStyle = "error";
				return cont(attributes);
			}

			function attribute(type)
			{
				if (type == "equals")
					return cont(attvalue, attributes);
				if (!arTags.allowMissing)
					setStyle = "error";
				return (type == "endTag" || type == "selfcloseTag") ? pass() : cont();
			}

			function attvalue(type)
			{
				if (type == "string")
				{
					return cont(attvaluemaybe);
				}
				if (type == "word" && arTags.allowUnquoted)
				{
					setStyle = "string";
					return cont();
				}
				setStyle = "error";
				return (type == "endTag" || type == "selfCloseTag") ? pass() : cont();
			}

			function attvaluemaybe(type)
			{
				return type == "string" ? cont(attvaluemaybe) : pass();
			}

			return {
				startStatus: function()
				{
					return {
						tokenize: InsideText,
						cc: [],
						indented: 0,
						startOfLine: true,
						tagName: null,
						context: null
					};
				},
				HandleChar: function(stream, status)
				{
					if (stream.sol())
					{
						status.startOfLine = true;
						status.indented = stream.indentation();
					}
					if (stream.eatSpace()) return null;

					setStyle = type = tagName = null;
					var style = status.tokenize(stream, status);
					status.type = type;
					if ((style || type) && style != "comment")
					{
						curState = status;
						while (true)
						{
							var comb = status.cc.pop() || element;
							if (comb(type || style))
								break;
						}
					}
					status.startOfLine = false;
					return setStyle || style;
				},

				Indent: function(status, textAfter, fullLine)
				{
					var context = status.context;
					if ((status.tokenize != InsideTag && status.tokenize != InsideText) || context && context.noIndent)
						return fullLine ? fullLine.match(/^(\s*)/)[0].length : 0;

					if (context && /^<\//.test(textAfter))
						context = context.prev;
					while (context && !context.startOfLine)
						context = context.prev;
					if (context)
						return context.indent + indentUnit;
					else
						return 0;
				},

				compareStates: function(a, b)
				{
					if (a.indented != b.indented || a.tokenize != b.tokenize)
						return false;
					for (var ca = a.context, cb = b.context; ; ca = ca.prev, cb = cb.prev)
					{
						if (!ca || !cb)
							return ca == cb;
						if (ca.tagName != cb.tagName || ca.indent != cb.indent)
							return false;
					}
				},

				magicSym: "/"
			};
		};

		Syntaxes.js = function()
		{
			var indentUnit = 4;
			var parserConfig = {};
			var arKeywords = function()
			{
				function getKeyWord(type) {return {type: type, style: "keyword"};}
				var
					keyA = getKeyWord("keyword a"),
					keyB = getKeyWord("keyword b"),
					keyC = getKeyWord("keyword c"),
					keyVar = getKeyWord("var"),
					operator = getKeyWord("operator"),
					atom = {type: "atom", style: "atom"};

				return {
					"if": keyA, "while": keyA, "with": keyA,
					"else": keyB, "do": keyB, "try": keyB, "finally": keyB,
					"return": keyC, "break": keyC, "continue": keyC, "new": keyC, "delete": keyC, "throw": keyC,
					"var": keyVar, "const": keyVar, "let": keyVar,
					"function": getKeyWord("function"),
					"catch": getKeyWord("catch"), "for": getKeyWord("for"), "switch": getKeyWord("switch"), "case": getKeyWord("case"), "default": getKeyWord("default"),
					"in": operator, "typeof": operator, "instanceof": operator,
					"true": atom, "false": atom, "null": atom, "undefined": atom, "NaN": atom, "Infinity": atom
				};
			}();

			var isOperatorChar = /[+\-*&%=<>!?|]/;

			function chain(stream, status, f)
			{
				status.tokenize = f;
				return f(stream, status);
			}

			function nextUntilUnescaped(stream, end)
			{
				var escaped = false, next;
				while ((next = stream.next()) != null)
				{
					if (next == end && !escaped)
						return false;
					escaped = !escaped && next == "\\";
				}
				return escaped;
			}

			var type, content;
			function retStyle(tp, style, cont)
			{
				type = tp;
				content = cont;
				return style;
			}

			function jsTokenBase(stream, status)
			{
				var
					result,
					ch = stream.next();
				if (ch == '"' || ch == "'")
				{
					result = chain(stream, status, jsTokenString(ch));
				}
				else if (/[\[\]{}\(\),;\:\.]/.test(ch))
				{
					result = retStyle(ch);
				}
				else if (ch == "0" && stream.eat(/x/i))
				{
					stream.eatWhile(/[\da-f]/i);
					result = retStyle("number", "number");
				}
				else if (/\d/.test(ch) || ch == "-" && stream.eat(/\d/))
				{
					stream.match(/^\d*(?:\.\d*)?(?:[eE][+\-]?\d+)?/);
					result = retStyle("number", "number");
				}
				else if (ch == "/")
				{
					if (stream.eat("*"))
					{
						result = chain(stream, status, jsTokenComment);
					}
					else if (stream.eat("/"))
					{
						stream.skipToEnd();
						result = retStyle("comment", "comment");
					}
					else if (status.reAllowed)
					{
						nextUntilUnescaped(stream, "/");
						stream.eatWhile(/[gimy]/); // 'y' is "sticky" option in Mozilla
						result = retStyle("regexp", "string-2");
					}
					else
					{
						stream.eatWhile(isOperatorChar);
						result = retStyle("operator", null, stream.current());
					}
				}
				else if (ch == "#")
				{
					stream.skipToEnd();
					result = retStyle("error", "error");
				}
				else if (isOperatorChar.test(ch))
				{
					stream.eatWhile(isOperatorChar);
					result = retStyle("operator", null, stream.current());
				}
				else
				{
					stream.eatWhile(/[\w\$_]/);
					var
						word = stream.current(),
						known = arKeywords.propertyIsEnumerable(word) && arKeywords[word];
					result = known && status.kwAllowed ? retStyle(known.type, known.style || known.type, word) : retStyle("variable", "variable", word);
				}

				return result;
			}

			function jsTokenString(quote)
			{
				return function(stream, status)
				{
					if (!nextUntilUnescaped(stream, quote))
						status.tokenize = jsTokenBase;
					return retStyle("string", "string");
				};
			}

			function jsTokenComment(stream, status)
			{
				var maybeEnd = false, ch;
				while (ch = stream.next())
				{
					if (ch == "/" && maybeEnd)
					{
						status.tokenize = jsTokenBase;
						break;
					}
					maybeEnd = (ch == "*");
				}
				return retStyle("comment", "comment");
			}

			// Parser
			var atomicTypes = {"atom": true, "number": true, "variable": true, "string": true, "regexp": true};

			function JSLexical(indented, column, type, align, prev, info)
			{
				this.indented = indented;
				this.column = column;
				this.type = type;
				this.prev = prev;
				this.info = info;
				if (align != null)
					this.align = align;
			}

			function inScope(status, varname)
			{
				for (var v = status.localVars; v; v = v.next)
					if (v.name == varname)
						return true;
			}

			function parseJS(status, style, type, content, stream)
			{
				var cc = status.cc;
				cx.status = status; cx.stream = stream; cx.marked = null, cx.cc = cc;

				if (!status.lexical.hasOwnProperty("align"))
					status.lexical.align = true;

				while(true)
				{
					var combinator = cc.length ? cc.pop() : statement;
					if (combinator(type, content))
					{
						while(cc.length && cc[cc.length - 1].lex)
							cc.pop()();
						if (cx.marked)
							return cx.marked;
						if (type == "variable" && inScope(status, content))
							return "variable-2";
						return style;
					}
				}
			}

			var cx = {status: null, column: null, marked: null, cc: null};
			function pass()
			{
				for (var i = arguments.length - 1; i >= 0; i--)
					cx.cc.push(arguments[i]);
			}

			function cont()
			{
				pass.apply(null, arguments);
				return true;
			}

			function register(varname)
			{
				var status = cx.status;
				if (status.context)
				{
					cx.marked = "def";
					for (var v = status.localVars; v; v = v.next)
						if (v.name == varname)
							return;
					status.localVars = {name: varname, next: status.localVars};
				}
			}

			// Combinators
			var defaultVars = {name: "this", next: {name: "arguments"}};

			function pushcontext()
			{
				if (!cx.status.context)
					cx.status.localVars = defaultVars;

				cx.status.context =
				{
					prev: cx.status.context,
					vars: cx.status.localVars
				};
			}

			function popcontext()
			{
				cx.status.localVars = cx.status.context.vars;
				cx.status.context = cx.status.context.prev;
			}

			function pushlex(type, info)
			{
				var result = function()
				{
					var status = cx.status;
					status.lexical = new JSLexical(status.indented, cx.stream.column(), type, null, status.lexical, info);
				};
				result.lex = true;
				return result;
			}

			function poplex()
			{
				var status = cx.status;
				if (status.lexical.prev)
				{
					if (status.lexical.type == ")")
						status.indented = status.lexical.indented;
					status.lexical = status.lexical.prev;
				}
			}
			poplex.lex = true;

			function expect(wanted)
			{
				return function expecting(type)
				{
					if (type == wanted)
						return cont();
					else if (wanted == ";")
						return pass();
					else
						return cont(arguments.callee);
				};
			}

			function statement(type)
			{
				if (type == "var")
					return cont(pushlex("vardef"), vardef1, expect(";"), poplex);
				if (type == "keyword a")
					return cont(pushlex("form"), expression, statement, poplex);
				if (type == "keyword b")
					return cont(pushlex("form"), statement, poplex);
				if (type == "{")
					return cont(pushlex("}"), block, poplex);
				if (type == ";")
					return cont();
				if (type == "function")
					return cont(functiondef);
				if (type == "for")
					return cont(pushlex("form"), expect("("), pushlex(")"), forspec1, expect(")"),poplex, statement, poplex);
				if (type == "variable")
					return cont(pushlex("stat"), maybelabel);
				if (type == "switch")
					return cont(pushlex("form"), expression, pushlex("}", "switch"), expect("{"), block, poplex, poplex);
				if (type == "case")
					return cont(expression, expect(":"));
				if (type == "default")
					return cont(expect(":"));
				if (type == "catch")
					return cont(pushlex("form"), pushcontext, expect("("), funarg, expect(")"), statement, poplex, popcontext);
				return pass(pushlex("stat"), expression, expect(";"), poplex);
			}

			function expression(type)
			{
				if (atomicTypes.hasOwnProperty(type))
					return cont(maybeoperator);
				if (type == "function")
					return cont(functiondef);
				if (type == "keyword c")
					return cont(maybeexpression);
				if (type == "(")
					return cont(pushlex(")"), maybeexpression, expect(")"), poplex, maybeoperator);
				if (type == "operator")
					return cont(expression);
				if (type == "[")
					return cont(pushlex("]"), commasep(expression, "]"), poplex, maybeoperator);
				if (type == "{")
					return cont(pushlex("}"), commasep(objprop, "}"), poplex, maybeoperator);
				return cont();
			}
			function maybeexpression(type)
			{
				if (type.match(/[,;\}\)\]]/))
					return pass();
				return pass(expression);
			}

			function maybeoperator(type, value)
			{
				if (type == "operator" && /\+\+|--/.test(value))
					return cont(maybeoperator);
				if (type == "operator" && value == "?")
					return cont(expression, expect(":"), expression);
				if (type == ";")
					return;
				if (type == "(")
					return cont(pushlex(")"), commasep(expression, ")"), poplex, maybeoperator);
				if (type == ".")
					return cont(property, maybeoperator);
				if (type == "[")
					return cont(pushlex("]"), expression, expect("]"), poplex, maybeoperator);
			}

			function maybelabel(type)
			{
				if (type == ":")
					return cont(poplex, statement);
				return pass(maybeoperator, expect(";"), poplex);
			}

			function property(type)
			{
				if (type == "variable")
				{
					cx.marked = "property";
					return cont();
				}
			}

			function objprop(type)
			{
				if (type == "variable")
					cx.marked = "property";
				if (atomicTypes.hasOwnProperty(type))
					return cont(expect(":"), expression);
			}

			function commasep(what, end)
			{
				function proceed(type)
				{
					if (type == ",")
						return cont(what, proceed);
					if (type == end)
						return cont();
					return cont(expect(end));
				}

				return function commaSeparated(type)
				{
					if (type == end)
						return cont();
					else
						return pass(what, proceed);
				};
			}

			function block(type)
			{
				if (type == "}")
					return cont();
				return pass(statement, block);
			}

			function vardef1(type, value)
			{
				if (type == "variable")
				{
					register(value);
					return cont(vardef2);
				}
				return cont();
			}

			function vardef2(type, value)
			{
				if (value == "=")
					return cont(expression, vardef2);
				if (type == ",")
					return cont(vardef1);
			}

			function forspec1(type)
			{
				if (type == "var")
					return cont(vardef1, forspec2);
				if (type == ";")
					return pass(forspec2);
				if (type == "variable")
					return cont(formaybein);
				return pass(forspec2);
			}

			function formaybein(type, value)
			{
				if (value == "in")
					return cont(expression);
				return cont(maybeoperator, forspec2);
			}

			function forspec2(type, value)
			{
				if (type == ";")
					return cont(forspec3);
				if (value == "in")
					return cont(expression);
				return cont(expression, expect(";"), forspec3);
			}

			function forspec3(type)
			{
				if (type != ")")
					cont(expression);
			}

			function functiondef(type, value)
			{
				if (type == "variable")
				{
					register(value);
					return cont(functiondef);
				}

				if (type == "(")
					return cont(pushlex(")"), pushcontext, commasep(funarg, ")"), poplex, statement, popcontext);
			}

			function funarg(type, value)
			{
				if (type == "variable")
				{
					register(value);
					return cont();
				}
			}

			return {
				startStatus: function(basecolumn) {
					return {
						tokenize: jsTokenBase,
						reAllowed: true,
						kwAllowed: true,
						cc: [],
						lexical: new JSLexical((basecolumn || 0) - indentUnit, 0, "block", false),
						localVars: parserConfig.localVars,
						context: parserConfig.localVars && {vars: parserConfig.localVars},
						indented: 0
					};
				},

				HandleChar: function(stream, status)
				{
					if (stream.sol())
					{
						if (!status.lexical.hasOwnProperty("align"))
							status.lexical.align = false;
						status.indented = stream.indentation();
					}

					if (stream.eatSpace())
						return null;
					var style = status.tokenize(stream, status);
					if (type == "comment")
						return style;
					status.reAllowed = !!(type == "operator" || type == "keyword c" || type.match(/^[\[{}\(,;:]$/));
					status.kwAllowed = type != '.';
					return parseJS(status, style, type, content, stream);
				},

				Indent: function(status, textAfter)
				{
					if (status.tokenize != jsTokenBase)
						return 0;
					var
						firstChar = textAfter && textAfter.charAt(0),
						lexical = status.lexical;

					if (lexical.type == "stat" && firstChar == "}")
						lexical = lexical.prev;

					var
						type = lexical.type,
						closing = firstChar == type,
						res = lexical.indented + (closing ? 0 : indentUnit);

					if (type == "vardef")
						res = lexical.indented + 4;
					else if (type == "form" && firstChar == "{")
						res = lexical.indented;
					else if (type == "stat" || type == "form")
						res = lexical.indented + indentUnit;
					else if (lexical.info == "switch" && !closing)
						res = lexical.indented + (/^(?:case|default)\b/.test(textAfter) ? indentUnit : 2 * indentUnit);
					else if (lexical.align)
						res = lexical.column + (closing ? 0 : 1);

					return res;
				},

				magicSym: ":{}"
			};
		};

		Syntaxes.sql = function()
		{
			var
				indentUnit = 4,
				curPunc,
				ops = new RegExp("^(?:" + "str|lang|langmatches|datatype|bound|sameterm|isiri|isuri|isblank|isliteral|union|a" + ")$", "i"),
				keywords = new RegExp("^(?:" + "ACCESSIBLE|ALTER|AS|BEFORE|BINARY|BY|CASE|CHARACTER|COLUMN|CONTINUE|CROSS|CURRENT_TIMESTAMP|DATABASE|DAY_MICROSECOND|DEC|DEFAULT|DESC|DISTINCT|DOUBLE|EACH|ENCLOSED|EXIT|FETCH|FLOAT8|FOREIGN|GRANT|HIGH_PRIORITY|HOUR_SECOND|IN|INNER|INSERT|INT2|INT8|INTO|JOIN|KILL|LEFT|LINEAR|LOCALTIME|LONG|LOOP|MATCH|MEDIUMTEXT|MINUTE_SECOND|NATURAL|NULL|OPTIMIZE|OR|OUTER|PRIMARY|RANGE|READ_WRITE|REGEXP|REPEAT|RESTRICT|RIGHT|SCHEMAS|SENSITIVE|SHOW|SPECIFIC|SQLSTATE|SQL_CALC_FOUND_ROWS|STARTING|TERMINATED|TINYINT|TRAILING|UNDO|UNLOCK|USAGE|UTC_DATE|VALUES|VARCHARACTER|WHERE|WRITE|ZEROFILL|ALL|AND|ASENSITIVE|BIGINT|BOTH|CASCADE|CHAR|COLLATE|CONSTRAINT|CREATE|CURRENT_TIME|CURSOR|DAY_HOUR|DAY_SECOND|DECLARE|DELETE|DETERMINISTIC|DIV|DUAL|ELSEIF|EXISTS|FALSE|FLOAT4|FORCE|FULLTEXT|HAVING|HOUR_MINUTE|IGNORE|INFILE|INSENSITIVE|INT1|INT4|INTERVAL|ITERATE|KEYS|LEAVE|LIMIT|LOAD|LOCK|LONGTEXT|MASTER_SSL_VERIFY_SERVER_CERT|MEDIUMINT|MINUTE_MICROSECOND|MODIFIES|NO_WRITE_TO_BINLOG|ON|OPTIONALLY|OUT|PRECISION|PURGE|READS|REFERENCES|RENAME|REQUIRE|REVOKE|SCHEMA|SELECT|SET|SPATIAL|SQLEXCEPTION|SQL_BIG_RESULT|SSL|TABLE|TINYBLOB|TO|TRUE|UNIQUE|UPDATE|USING|UTC_TIMESTAMP|VARCHAR|WHEN|WITH|YEAR_MONTH|ADD|ANALYZE|ASC|BETWEEN|BLOB|CALL|CHANGE|CHECK|CONDITION|CONVERT|CURRENT_DATE|CURRENT_USER|DATABASES|DAY_MINUTE|DECIMAL|DELAYED|DESCRIBE|DISTINCTROW|DROP|ELSE|ESCAPED|EXPLAIN|FLOAT|FOR|FROM|GROUP|HOUR_MICROSECOND|IF|INDEX|INOUT|INT|INT3|INTEGER|IS|KEY|LEADING|LIKE|LINES|LOCALTIMESTAMP|LONGBLOB|LOW_PRIORITY|MEDIUMBLOB|MIDDLEINT|MOD|NOT|NUMERIC|OPTION|ORDER|OUTFILE|PROCEDURE|READ|REAL|RELEASE|REPLACE|RETURN|RLIKE|SECOND_MICROSECOND|SEPARATOR|SMALLINT|SQL|SQLWARNING|SQL_SMALL_RESULT|STRAIGHT_JOIN|THEN|TINYTEXT|TRIGGER|UNION|UNSIGNED|USE|UTC_TIME|VARBINARY|VARYING|WHILE|XOR|FULL|COLUMNS|MIN|MAX|STDEV|COUNT" + ")$", "i"),
				operatorChars = /[*+\-<>=&|]/;

			function tokenBase(stream, status)
			{
				var ch = stream.next();
				curPunc = null;
				if (ch == "$" || ch == "?")
				{
					stream.match(/^[\w\d]*/);
					return "variable-2";
				}
				else if (ch == "<" && !stream.match(/^[\s\u00a0=]/, false))
				{
					stream.match(/^[^\s\u00a0>]*>?/);
					return "atom";
				}
				else if (ch == "\"" || ch == "'")
				{
					status.tokenize = tokenLiteral(ch);
					return status.tokenize(stream, status);
				}
				else if (ch == "`")
				{
					status.tokenize = tokenOpLiteral(ch);
					return status.tokenize(stream, status);
				}
				else if (/[{}\(\),\.;\[\]]/.test(ch))
				{
					curPunc = ch;
					return null;
				}
				else if (ch == "-")
				{
					var ch2 = stream.next();
					if (ch2=="-")
					{
						stream.skipToEnd();
						return "comment";
					}
				}
				else if (operatorChars.test(ch))
				{
					stream.eatWhile(operatorChars);
					return null;
				}
				else if (ch == ":")
				{
					stream.eatWhile(/[\w\d\._\-]/);
					return "atom";
				}
				else
				{
					stream.eatWhile(/[_\w\d]/);
					if (stream.eat(":"))
					{
						stream.eatWhile(/[\w\d_\-]/);
						return "atom";
					}

					var word = stream.current(), type;
					if (ops.test(word))
						return null;
					else if (keywords.test(word))
						return "keyword";
					else
						return "variable";
				}
			}

			function tokenLiteral(quote)
			{
				return function(stream, status)
				{
					var escaped = false, ch;
					while ((ch = stream.next()) != null)
					{
						if (ch == quote && !escaped)
						{
							status.tokenize = tokenBase;
							break;
						}
						escaped = !escaped && ch == "\\";
					}
					return "string";
				};
			}

			function tokenOpLiteral(quote)
			{
				return function(stream, status)
				{
					var escaped = false, ch;
					while ((ch = stream.next()) != null)
					{
						if (ch == quote && !escaped)
						{
							status.tokenize = tokenBase;
							break;
						}
						escaped = !escaped && ch == "\\";
					}
					return "variable-2";
				};
			}


			function pushContext(status, type, col)
			{
				status.context = {prev: status.context, indent: status.indent, col: col, type: type};
			}

			function popContext(status)
			{
				status.indent = status.context.indent;
				status.context = status.context.prev;
			}

			return {
				startStatus: function(base)
				{
					return {
						tokenize: tokenBase,
						context: null,
						indent: 0,
						col: 0
					};
				},

				HandleChar: function(stream, status)
				{
					if (stream.sol())
					{
						if (status.context && status.context.align == null)
							status.context.align = false;
						status.indent = stream.indentation();
					}
					if (stream.eatSpace())
						return null;
					var style = status.tokenize(stream, status);

					if (style != "comment" && status.context && status.context.align == null && status.context.type != "pattern")
						status.context.align = true;

					if (curPunc == "(")
					{
						pushContext(status, ")", stream.column());
					}
					else if (curPunc == "[")
					{
						pushContext(status, "]", stream.column());
					}
					else if (curPunc == "{")
					{
						pushContext(status, "}", stream.column());
					}
					else if (/[\]\}\)]/.test(curPunc))
					{
						while (status.context && status.context.type == "pattern")
							popContext(status);
						if (status.context && curPunc == status.context.type)
							popContext(status);
					}
					else if (curPunc == "." && status.context && status.context.type == "pattern")
					{
						popContext(status);
					}
					else if (/atom|string|variable/.test(style) && status.context)
					{
						if (/[\}\]]/.test(status.context.type))
						{
							pushContext(status, "pattern", stream.column());
						}
						else if (status.context.type == "pattern" && !status.context.align)
						{
							status.context.align = true;
							status.context.col = stream.column();
						}
					}

					return style;
				},

				Indent: function(status, textAfter)
				{
					var
						firstChar = textAfter && textAfter.charAt(0),
						context = status.context;

					if (/[\]\}]/.test(firstChar))
					{
						while (context && context.type == "pattern")
							context = context.prev;
					}

					var
						closing = context && firstChar == context.type,
						result = context.indent + (closing ? 0 : indentUnit);

					if (!context)
						result = 0;
					else if (context.type == "pattern")
						result = context.col;
					else if (context.align)
						result = context.col + (closing ? 0 : 1);

					return result;
				}
			};
		};

		Syntaxes.phpcore = function()
		{
			var
				indentUnit = 4,
				keywords =  prepareKeywords(['echo', 'include', 'require', 'include_once', 'require_once','for', 'foreach', 'as', 'endswitch', 'return', 'break', 'continue', 'null', '__LINE__', '__FILE__', 'var', 'default', 'function', 'class', 'new', '&amp;new', 'this', '__FUNCTION__', '__CLASS__', '__METHOD__', 'PHP_VERSION', 'E_ERROR', 'E_WARNING','E_PARSE', 'E_NOTICE', 'E_CORE_ERROR', 'E_CORE_WARNING', 'E_COMPILE_ERROR', 'E_COMPILE_WARNING', 'E_USER_ERROR', 'E_USER_WARNING', 'E_USER_NOTICE', 'E_ALL', 'abstract', 'array']),
				blockKeywords = prepareKeywords(['catch', 'do', 'else', 'elseif', 'for', 'foreach', 'if', 'switch', 'try', 'while', 'endwhile', 'endif', 'case']),
				atoms = prepareKeywords(['true', 'false', 'null', 'TRUE', 'FALSE', 'NULL']),
				hooks = {
					"$": function (stream, status)
					{
						stream.eatWhile(/[\w\$_]/);
						return "variable-2";
					},
					"<": function (stream, status)
					{
						if (stream.match(/<</))
						{
							stream.eatWhile(/[\w\.]/);
							var delimiter = stream.current().slice(3);
							status.tokenize = function (stream, status)
							{
								if (stream.match(delimiter))
									status.tokenize = null;
								else
									stream.skipToEnd();
								return "string";
							};
							return status.tokenize(stream, status);
						}
						return false;
					},
					"#": function (stream, status)
					{
						while (!stream.eol() && !stream.match("?>", false))
							stream.next();
						return "comment";
					},
					"/": function (stream, status)
					{
						if (stream.eat("/"))
						{
							while (!stream.eol() && !stream.match("?>", false)) stream.next();
							return "comment";
						}
						return false;
					}
				},
				multiLineStrings = true,
				isOperatorChar = /[+\-*&%=<>!?|\/]/,
				curPunc;

			function tokenBase(stream, status)
			{
				var ch = stream.next();
				if (hooks[ch])
				{
					var result = hooks[ch](stream, status);
					if (result !== false)
						return result;
				}

				if (ch == '"' || ch == "'")
				{
					status.tokenize = tokenString(ch);
					return status.tokenize(stream, status);
				}
				if (/[\[\]{}\(\),;\:\.]/.test(ch))
				{
					curPunc = ch;
					return null;
				}
				if (/\d/.test(ch))
				{
					stream.eatWhile(/[\w\.]/);
					return "number";
				}
				if (ch == "/")
				{
					if (stream.eat("*"))
					{
						status.tokenize = tokenComment;
						return tokenComment(stream, status);
					}
					if (stream.eat("/"))
					{
						stream.skipToEnd();
						return "comment";
					}
				}
				if (isOperatorChar.test(ch))
				{
					stream.eatWhile(isOperatorChar);
					return "operator";
				}
				stream.eatWhile(/[\w\$_]/);
				var cur = stream.current();
				if (keywords.propertyIsEnumerable(cur))
				{
					if (blockKeywords.propertyIsEnumerable(cur))
						curPunc = "newstatement";
					return "keyword";
				}
				if (atoms.propertyIsEnumerable(cur))
					return "atom";
				return "variable";
			}

			function tokenString(quote)
			{
				return function(stream, status)
				{
					var
						escaped = false,
						next,
						end = false;
					while ((next = stream.next()) != null)
					{
						if (next == quote && !escaped)
						{
							end = true;
							break;
						}
						escaped = !escaped && next == "\\";
					}
					if (end || !(escaped || multiLineStrings))
						status.tokenize = null;
					return "string";
				};
			}

			function tokenComment(stream, status)
			{
				var
					maybeEnd = false,
					ch;
				while (ch = stream.next())
				{
					if (ch == "/" && maybeEnd)
					{
						status.tokenize = null;
						break;
					}
					maybeEnd = (ch == "*");
				}
				return "comment";
			}

			function JCContext(indented, column, type, align, prev)
			{
				this.indented = indented;
				this.column = column;
				this.type = type;
				this.align = align;
				this.prev = prev;
			}

			function pushContext(status, col, type)
			{
				return status.context = new JCContext(status.indented, col, type, null, status.context);
			}

			function popContext(status)
			{
				var t = status.context.type;
				if (t == ")" || t == "]" || t == "}")
					status.indented = status.context.indented;
				return status.context = status.context.prev;
			}

			// Interface
			return {
				startStatus: function(basecolumn)
				{
					return {
						tokenize: null,
						context: new JCContext((basecolumn || 0) - indentUnit, 0, "top", false),
						indented: 0,
						startOfLine: true
					};
				},

				HandleChar: function(stream, status)
				{
					var ctx = status.context;
					if (stream.sol())
					{
						if (ctx.align == null)
							ctx.align = false;
						status.indented = stream.indentation();
						status.startOfLine = true;
					}
					if (stream.eatSpace())
						return null;
					curPunc = null;
					var style = (status.tokenize || tokenBase)(stream, status);

					if (style == "comment" || style == "meta")
						return style;
					if (ctx.align == null)
						ctx.align = true;

					if ((curPunc == ";" || curPunc == ":") && ctx.type == "statement")
					{
						popContext(status);
					}
					else if (curPunc == "{")
					{
						pushContext(status, stream.column(), "}");
					}
					else if (curPunc == "[")
					{
						pushContext(status, stream.column(), "]");
					}
					else if (curPunc == "(")
					{
						pushContext(status, stream.column(), ")");
					}
					else if (curPunc == "}")
					{
						while (ctx.type == "statement")
							ctx = popContext(status);
						if (ctx.type == "}")
							ctx = popContext(status);
						while (ctx.type == "statement")
							ctx = popContext(status);
					}
					else if (curPunc == ctx.type)
					{
						popContext(status);
					}
					else if (ctx.type == "}" || ctx.type == "top" || (ctx.type == "statement" && curPunc == "newstatement"))
					{
						pushContext(status, stream.column(), "statement");
					}
					status.startOfLine = false;
					return style;
				},

				Indent: function(status, textAfter)
				{
					if (status.tokenize != tokenBase && status.tokenize != null)
						return 0;
					var
						ctx = status.context,
						firstChar = textAfter && textAfter.charAt(0);

					if (ctx.type == "statement" && firstChar == "}")
						ctx = ctx.prev;

					if (ctx.type == "statement")
						return ctx.indented + (firstChar == "{" ? 0 : indentUnit);
					else if (ctx.align)
						return ctx.column + (firstChar == ctx.type ? 0 : 1);
					else
						return ctx.indented + (firstChar == ctx.type ? 0 : indentUnit);
				},

				magicSym: "{}"
			};
		};

		Syntaxes.css = function()
		{
			var indentUnit = 4, type;
			var keywords = prepareKeywords(["above", "absolute", "activeborder", "activecaption", "afar", "after-white-space", "ahead", "alias", "all", "all-scroll", "alternate", "always", "amharic", "amharic-abegede", "antialiased", "appworkspace", "arabic-indic", "armenian", "asterisks","auto", "avoid", "background", "backwards", "baseline", "below", "bidi-override", "binary", "bengali", "blink", "block", "block-axis", "bold", "bolder", "border", "border-box", "both", "bottom", "break-all", "break-word", "button", "button-bevel", "buttonface", "buttonhighlight", "buttonshadow", "buttontext", "cambodian", "capitalize", "caps-lock-indicator", "caption", "captiontext", "caret", "cell", "center", "checkbox", "circle", "cjk-earthly-branch", "cjk-heavenly-stem", "cjk-ideographic", "clear", "clip", "close-quote", "col-resize", "collapse", "compact", "condensed", "contain", "content", "content-box", "context-menu", "continuous", "copy", "cover", "crop", "cross", "crosshair", "currentcolor", "cursive", "dashed", "decimal", "decimal-leading-zero", "default", "default-button", "destination-atop", "destination-in", "destination-out", "destination-over", "devanagari", "disc", "discard", "document", "dot-dash", "dot-dot-dash", "dotted", "double", "down", "e-resize", "ease", "ease-in", "ease-in-out", "ease-out", "element", "ellipsis", "embed", "end", "ethiopic", "ethiopic-abegede", "ethiopic-abegede-am-et", "ethiopic-abegede-gez", "ethiopic-abegede-ti-er", "ethiopic-abegede-ti-et", "ethiopic-halehame-aa-er", "ethiopic-halehame-aa-et", "ethiopic-halehame-am-et", "ethiopic-halehame-gez", "ethiopic-halehame-om-et", "ethiopic-halehame-sid-et", "ethiopic-halehame-so-et", "ethiopic-halehame-ti-er", "ethiopic-halehame-ti-et", "ethiopic-halehame-tig", "ew-resize", "expanded", "extra-condensed", "extra-expanded", "fantasy", "fast", "fill", "fixed", "flat", "footnotes", "forwards", "from", "geometricPrecision", "georgian", "graytext", "groove", "gujarati", "gurmukhi", "hand", "hangul", "hangul-consonant", "hebrew", "help", "hidden", "hide", "higher", "highlight", "highlighttext", "hiragana", "hiragana-iroha", "horizontal", "hsl", "hsla", "icon", "ignore", "inactiveborder", "inactivecaption", "inactivecaptiontext", "infinite", "infobackground", "infotext", "inherit", "initial", "inline", "inline-axis", "inline-block", "inline-table", "inset", "inside", "intrinsic", "invert", "italic", "justify", "kannada", "katakana", "katakana-iroha", "khmer", "landscape", "lao", "large", "larger", "left", "level", "lighter", "line-through", "linear", "lines", "list-item", "listbox", "listitem", "local", "logical", "loud", "lower", "lower-alpha", "lower-armenian", "lower-greek", "lower-hexadecimal", "lower-latin", "lower-norwegian", "lower-roman", "lowercase", "ltr", "malayalam", "match", "medium", "menu", "menulist", "menulist-button", "menulist-text", "menulist-textfield", "menutext", "message-box", "middle", "min-intrinsic", "mix", "mongolian", "monospace", "move", "multiple", "myanmar", "n-resize", "narrower", "navy", "ne-resize", "nesw-resize", "no-close-quote", "no-drop", "no-open-quote", "no-repeat", "none", "normal", "not-allowed", "nowrap", "ns-resize", "nw-resize", "nwse-resize", "oblique", "octal", "open-quote", "optimizeLegibility", "optimizeSpeed", "oriya", "oromo", "outset", "outside", "overlay", "overline", "padding", "padding-box", "painted", "paused", "persian", "plus-darker", "plus-lighter", "pointer", "portrait", "pre", "pre-line", "pre-wrap", "preserve-3d", "progress", "push-button", "radio", "read-only", "read-write", "read-write-plaintext-only", "relative", "repeat", "repeat-x", "repeat-y", "reset", "reverse", "rgb", "rgba", "ridge", "right", "round", "row-resize", "rtl", "run-in", "running", "s-resize", "sans-serif", "scroll", "scrollbar", "se-resize", "semi-condensed", "semi-expanded", "separate", "serif", "show", "sidama", "single", "skip-white-space", "slide", "slider-horizontal", "slider-vertical", "sliderthumb-horizontal", "sliderthumb-vertical", "slow","small", "small-caps", "small-caption", "smaller", "solid", "somali", "source-atop", "source-in", "source-out", "source-over", "space", "square", "square-button", "start", "static", "status-bar", "stretch", "stroke", "sub", "subpixel-antialiased", "super", "sw-resize", "table", "table-caption", "table-cell", "table-column", "table-column-group", "table-footer-group", "table-header-group", "table-row", "table-row-group", "telugu", "text", "text-bottom", "text-top", "textarea", "textfield", "thai", "thick", "thin", "threeddarkshadow", "threedface", "threedhighlight", "threedlightshadow", "threedshadow", "tibetan", "tigre", "tigrinya-er", "tigrinya-er-abegede", "tigrinya-et", "tigrinya-et-abegede", "to", "top", "transparent", "ultra-condensed", "ultra-expanded", "underline", "up", "upper-alpha", "upper-armenian", "upper-greek", "upper-hexadecimal", "upper-latin", "upper-norwegian", "upper-roman", "uppercase", "urdu", "url", "vertical", "vertical-text", "visible", "visibleFill", "visiblePainted", "visibleStroke", "visual", "w-resize", "wait", "wave", "white", "wider", "window", "windowframe", "windowtext", "x-large", "x-small", "xor", "xx-large", "xx-small", "yellow"]);

			function retStyle(style, tp)
			{
				type = tp;
				return style;
			}

			function tokenBase(stream, status)
			{
				var ch = stream.next();
				if (ch == "@")
				{
					stream.eatWhile(/[\w\\\-]/);
					return retStyle("meta", stream.current());
				}
				else if (ch == "/" && stream.eat("*"))
				{
					status.tokenize = tokenCComment;
					return tokenCComment(stream, status);
				}
				else if (ch == "<" && stream.eat("!"))
				{
					status.tokenize = tokenSGMLComment;
					return tokenSGMLComment(stream, status);
				}
				else if (ch == "=")
				{
					return retStyle(null, "compare");
				}
				else if ((ch == "~" || ch == "|") && stream.eat("="))
				{
					return retStyle(null, "compare");
				}
				else if (ch == "\"" || ch == "'")
				{
					status.tokenize = tokenString(ch);
					return status.tokenize(stream, status);
				}
				else if (ch == "#")
				{
					stream.eatWhile(/[\w\\\-]/);
					return retStyle("atom", "hash");
				}
				else if (ch == "!")
				{
					stream.match(/^\s*\w*/);
					return retStyle("keyword", "important");
				}
				else if (/\d/.test(ch))
				{
					stream.eatWhile(/[\w.%]/);
					return retStyle("number", "unit");
				}
				else if (/[,.+>*\/]/.test(ch))
				{
					return retStyle(null, "select-op");
				}
				else if (/[;{}:\[\]\(\)]/.test(ch))
				{
					return retStyle(null, ch);
				}
				else
				{
					stream.eatWhile(/[\w\\\-]/);
					return retStyle("variable", "variable");
				}
			}

			function tokenCComment(stream, status)
			{
				var maybeEnd = false, ch;
				while ((ch = stream.next()) != null)
				{
					if (maybeEnd && ch == "/")
					{
						status.tokenize = tokenBase;
						break;
					}
					maybeEnd = (ch == "*");
				}
				return retStyle("comment", "comment");
			}

			function tokenSGMLComment(stream, status)
			{
				var dashes = 0, ch;
				while ((ch = stream.next()) != null)
				{
					if (dashes >= 2 && ch == ">")
					{
						status.tokenize = tokenBase;
						break;
					}
					dashes = (ch == "-") ? dashes + 1 : 0;
				}
				return retStyle("comment", "comment");
			}

			function tokenString(quote)
			{
				return function(stream, status)
				{
					var escaped = false, ch;
					while ((ch = stream.next()) != null)
					{
						if (ch == quote && !escaped)
							break;
						escaped = !escaped && ch == "\\";
					}
					if (!escaped)
						status.tokenize = tokenBase;
					return retStyle("string", "string");
				};
			}

			return {
				startStatus: function(base)
				{
					return {tokenize: tokenBase,
						baseIndent: base || 0,
						stack: []};
				},

				HandleChar: function(stream, status)
				{
					if (stream.eatSpace())
						return null;
					var style = status.tokenize(stream, status);

					var context = status.stack[status.stack.length-1];
					if (type == "hash" && context != "rule")
					{
						style = "string-2";
					}
					else if (style == "variable")
					{
						if (context == "rule")
							style = keywords[stream.current()] ? "keyword" : "number";
						else if (!context || context == "@media{")
							style = "tag";
					}

					if (context == "rule" && /^[\{\};]$/.test(type))
					{
						status.stack.pop();
					}
					if (type == "{")
					{
						if (context == "@media")
							status.stack[status.stack.length-1] = "@media{";
						else
							status.stack.push("{");
					}
					else if (type == "}")
					{
						status.stack.pop();
					}
					else if (type == "@media")
					{
						status.stack.push("@media");
					}
					else if (context == "{" && type != "comment")
					{
						status.stack.push("rule");
					}
					return style;
				},

				Indent: function(status, textAfter)
				{
					var n = status.stack.length;
					if (/^\}/.test(textAfter))
						n -= status.stack[status.stack.length-1] == "rule" ? 2 : 1;
					return status.baseIndent + n * indentUnit;
				},

				magicSym: "}"
			};
		};

		Syntaxes.php = function(bForceSyntax)
		{
			var
				parserConfig = {},
				syntHtml = Syntaxes.html(),
				syntJs = Syntaxes.js(),
				syntCss = Syntaxes.css(),
				syntPhp = Syntaxes.phpcore();

			function dispatch(stream, status)
			{
				var
					style,
					isPHP = status.syntax == "php";

				if (!isPHP && bForceSyntax)
				{
					status.curSyntax = syntPhp;
					status.curState = status.php;
					//status.curClose = "?>";
					status.syntax = "php";
				}

				if (stream.sol() && status.pending != '"')
					status.pending = null;

				if (status.curSyntax == syntHtml)
				{
					if (stream.match(/^<\?\w*/))
					{
						status.curSyntax = syntPhp;
						status.curState = status.php;
						status.curClose = "?>";
						status.syntax = "php";
						return "meta";
					}

					if (status.pending == '"')
					{
						while (!stream.eol() && stream.next() != '"')
						{
						}
						style = "string";
					}
					else if (status.pending && stream.pos < status.pending.end)
					{
						stream.pos = status.pending.end;
						style = status.pending.style;
					}
					else
					{
						style = syntHtml.HandleChar(stream, status.curState);
					}

					status.pending = null;
					var
						cur = stream.current(),
						openPHP = cur.search(/<\?/);
					if (openPHP != -1)
					{
						if (style == "string" && /\"$/.test(cur) && !/\?>/.test(cur))
							status.pending = '"';
						else
							status.pending = {end: stream.pos, style: style};
						stream.backUp(cur.length - openPHP);
					}
					else if (style == "tag" && stream.current() == ">" && status.curState.context)
					{
						if (/^script$/i.test(status.curState.context.tagName))
						{
							status.curSyntax = syntJs;
							status.curState = syntJs.startStatus(syntHtml.Indent(status.curState, ""));
							status.curClose = /^<\/\s*script\s*>/i;
							status.syntax = "js";
						}
						else if (/^style$/i.test(status.curState.context.tagName))
						{
							status.curSyntax = syntCss;
							status.curState = syntCss.startStatus(syntHtml.Indent(status.curState, ""));
							status.curClose = /^<\/\s*style\s*>/i;
							status.syntax = "css";
						}
					}
					return style;
				}
				else if ((!isPHP || status.php.tokenize == null) && stream.match(status.curClose, isPHP))
				{
					status.curSyntax = syntHtml;
					status.curState = status.html;
					status.curClose = null;
					status.syntax = "html";
					return isPHP ? "meta" : dispatch(stream, status);
				}
				else
				{
					return status.curSyntax.HandleChar(stream, status.curState);
				}
			}

			return {
				startStatus: function ()
				{
					var html = syntHtml.startStatus();
					return {
						html: html,
						php: syntPhp.startStatus(),
						curSyntax: parserConfig.startOpen ? syntPhp : syntHtml,
						curState: parserConfig.startOpen ? syntPhp.startStatus() : html,
						curClose: parserConfig.startOpen ? /^\?>/ : null,
						syntax: parserConfig.startOpen ? "php" : "html",
						pending: null
					};
				},

				copyStatus: function (status)
				{
					var
						html = status.html,
						htmlNew = copySyntaxStatus(syntHtml, html),
						php = status.php,
						phpNew = copySyntaxStatus(syntPhp, php),
						cur;

					if (status.curState == html)
						cur = htmlNew;
					else if (status.curState == php)
						cur = phpNew;
					else
						cur = copySyntaxStatus(status.curSyntax, status.curState);

					return {
						html: htmlNew,
						php: phpNew,
						curSyntax: status.curSyntax,
						curState: cur,
						curClose: status.curClose,
						syntax: status.syntax,
						pending: status.pending
					};
				},

				HandleChar: dispatch,

				Indent: function (status, textAfter)
				{
					if ((status.curSyntax != syntPhp && /^\s*<\//.test(textAfter)) || (status.curSyntax == syntPhp && /^\?>/.test(textAfter)))
						return syntHtml.Indent(status.html, textAfter);
					return status.curSyntax.Indent(status.curState, textAfter);
				},

				magicSym: "/{}:"
			};
		};
	}
	// -- ************* Syntaxes defenitions *************

	function JCHighlightedText(from, to, className, marker)
	{
		this.from = from;
		this.to = to;
		this.style = className;
		this.marker = marker;
	}

	JCHighlightedText.prototype = {
		attach: function (line)
		{
			this.marker.set.push(line);
		},
		detach: function (line)
		{
			var ix = indexOf(this.marker.set, line);
			if (ix > -1)
				this.marker.set.splice(ix, 1);
		},
		split: function (pos, lenBefore)
		{
			if (this.to <= pos && this.to != null)
				return null;
			var
				from = this.from < pos || this.from == null ? null : this.from - pos + lenBefore,
				to = this.to == null ? null : this.to - pos + lenBefore;
			return new JCHighlightedText(from, to, this.style, this.marker);
		},
		dup: function ()
		{
			return new JCHighlightedText(null, null, this.style, this.marker);
		},
		clipTo: function (fromOpen, from, toOpen, to, diff)
		{
			if (fromOpen && to > this.from && (to < this.to || this.to == null))
				this.from = null;
			else if (this.from != null && this.from >= from)
				this.from = Math.max(to, this.from) + diff;
			if (toOpen && (from < this.to || this.to == null) && (from > this.from || this.from == null))
				this.to = null;
			else if (this.to != null && this.to > from)
				this.to = to < this.to ? this.to + diff : from;
		},
		isDead: function ()
		{
			return this.from != null && this.to != null && this.from >= this.to;
		},
		sameSet: function (x)
		{
			return this.marker == x.marker;
		}
	};

})();