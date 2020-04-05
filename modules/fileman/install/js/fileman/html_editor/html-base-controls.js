/**
 * Bitrix HTML Editor 3.0
 * Date: 24.04.13
 * Time: 4:23
 *
 * Taskbarmanager
 * Taskbar
 * Context Menu
 * Search/Replace
 */
;(function() {
	function TaskbarManager(editor, init)
	{
		this.editor = editor;
		this.bShown = false;
		this.closedWidth = 20;
		this.MIN_CLOSED_WIDTH = 120;
		this.width = this.editor.config.taskbarWidth || 250;
		this.taskbars = {};
		this.freezeOnclickHandler = false;

		if (init)
		{
			this.Init();
		}
	}

	TaskbarManager.prototype = {
		Init: function()
		{
			this.pCont = this.editor.dom.taskbarCont;
			this.pCont.setAttribute('data-bx-type', 'taskbarmanager');

			this.pResizer = BX('bx-html-editor-tskbr-res-' + this.editor.id);
			this.pResizer.setAttribute('data-bx-type', 'taskbarflip');
			this.pTopCont = BX('bx-html-editor-tskbr-top-' + this.editor.id);

			BX.bind(this.pResizer, 'mousedown', BX.proxy(this.StartResize, this));
			BX.bind(this.pCont, 'click', BX.proxy(this.OnClick, this));

			// Search
			this.pSearchCont = BX('bxhed-tskbr-search-cnt-' + this.editor.id);
			this.pSearchAli = BX('bxhed-tskbr-search-ali-' + this.editor.id);
			this.pSearchInput = BX('bxhed-tskbr-search-inp-' + this.editor.id);
			this.pSearchNothingNotice = BX('bxhed-tskbr-search-nothing-' + this.editor.id);
			BX.bind(this.pSearchInput, 'keyup', BX.proxy(this.TaskbarSearch, this));
		},

		OnClick: function(e)
		{
			if (!e)
				e = window.event;

			if (this.freezeOnclickHandler)
				return;

			var
				_this = this,
				target = e.target || e.srcElement,
				type = (target && target.getAttribute) ? target.getAttribute('data-bx-type') : null;

			if (!type)
			{
				target = BX.findParent(target, function(node)
				{
					return node == _this.pCont || (node.getAttribute && node.getAttribute('data-bx-type'));
				}, this.pCont);
				type = (target && target.getAttribute) ? target.getAttribute('data-bx-type') : null;
			}

			if (type == 'taskbarflip' || (!this.bShown && (type == 'taskbarmanager' || !type)))
			{
				if (this.bShown)
				{
					this.Hide();
				}
				else
				{
					this.Show();
				}
			}
			else if(type == 'taskbargroup_title')
			{
				BX.onCustomEvent(this, 'taskbargroupTitleClick', [target]);
			}
			else if(type == 'taskbarelement')
			{
				BX.onCustomEvent(this, 'taskbarelementClick', [target]);
			}
			else if(type == 'taskbar_title_but')
			{
				BX.onCustomEvent(this, 'taskbarTitleClick', [target]);
			}
			else if(type == 'taskbar_top_menu')
			{
				BX.onCustomEvent(this, 'taskbarMenuClick', [target]);
			}
			else if(type == 'taskbar_search_cancel')
			{
				this.pSearchInput.value = '';
				this.TaskbarSearch();
			}
		},

		Show: function(saveValue)
		{
			if (!this.bShown)
			{
				this.bShown = true;
				this.pCont.className = 'bxhtmled-taskbar-cnt bxhtmled-taskbar-shown';
			}
			this.pCont.style.width = this.GetWidth(true) + 'px';
			this.editor.ResizeSceleton();

			if (saveValue !== false)
			{
				this.editor.SaveOption('taskbar_shown', 1);
			}
		},

		Hide: function(saveValue)
		{
			if (this.bShown)
			{
				this.bShown = false;
				this.pCont.className = 'bxhtmled-taskbar-cnt bxhtmled-taskbar-hidden';
			}
			this.pCont.style.width = this.GetWidth() + 'px';
			this.editor.ResizeSceleton();

			if (saveValue !== false)
			{
				this.editor.SaveOption('taskbar_shown', 0);
			}
		},

		GetWidth: function(bCheck, maxWidth)
		{
			var width;
			if (this.bShown)
			{
				width = bCheck ? Math.max(this.width, this.closedWidth + this.MIN_CLOSED_WIDTH) : this.width;
				if(maxWidth && width > maxWidth)
				{
					width = this.width = Math.round(maxWidth);
				}
			}
			else
			{
				width = this.closedWidth;
			}

			return width;
		},

		AddTaskbar: function(oTaskbar)
		{
			this.taskbars[oTaskbar.id] = oTaskbar;
			this.pCont.appendChild(oTaskbar.GetCont());
			this.pTopCont.appendChild(oTaskbar.GetTitleCont());
		},

		ShowTaskbar: function(taskbarId)
		{
			this.pSearchInput.value = '';
			for(var id in this.taskbars)
			{
				if (this.taskbars.hasOwnProperty(id))
				{
					if (id == taskbarId)
					{
						this.taskbars[id].Activate();
						this.pSearchInput.placeholder = this.taskbars[id].searchPlaceholder;
					}
					else
					{
						this.taskbars[id].Deactivate();
					}

					this.activeTaskbarId = taskbarId;
					this.taskbars[id].ClearSearchResult();
				}
			}
		},

		GetActiveTaskbar: function()
		{
			return this.taskbars[this.activeTaskbarId];
		},

		StartResize: function(e)
		{
			if(!e)
				e = window.event;

			var target = e.target || e.srcElement;
			if (target.getAttribute('data-bx-tsk-split-but') == 'Y')
				return true;

			this.freezeOnclickHandler = true;

			var
				width = this.GetWidth(),
				overlay = this.editor.dom.resizerOverlay,
				dX = 0, newWidth,
				windowScroll = BX.GetWindowScrollPos(),
				startX = e.clientX + windowScroll.scrollLeft,
				_this = this;

			overlay.style.display = 'block';

			function moveResizer(e, bFinish)
			{
				if(!e)
					e = window.event;

				var x = e.clientX + windowScroll.scrollLeft;

				if(startX == x)
					return;

				dX = startX - x;
				newWidth = width + dX;

				if (bFinish)
				{
					_this.width = Math.max(newWidth, _this.closedWidth + _this.MIN_CLOSED_WIDTH);
					if (isNaN(_this.width))
					{
						_this.width = _this.closedWidth + _this.MIN_CLOSED_WIDTH;
					}
				}
				else
				{
					_this.width = newWidth;
				}

				if (newWidth > _this.closedWidth + (bFinish ? 20 : 0))
				{
					_this.Show();
				}
				else
				{
					_this.Hide();
				}
			}

			function finishResizing(e)
			{
				moveResizer(e, true);
				BX.unbind(document, 'mousemove', moveResizer);
				BX.unbind(document, 'mouseup', finishResizing);
				overlay.style.display = 'none';
				setTimeout(function(){_this.freezeOnclickHandler = false;}, 10);
				BX.PreventDefault(e);

				_this.editor.SaveOption('taskbar_width', _this.GetWidth(true));
			}

			BX.bind(document, 'mousemove', moveResizer);
			BX.bind(document, 'mouseup', finishResizing);
		},

		Resize: function(w, h)
		{
			var topHeight = parseInt(this.pTopCont.offsetHeight, 10);
			for(var id in this.taskbars)
			{
				if (this.taskbars.hasOwnProperty(id) && this.taskbars[id].pTreeCont)
				{
					this.taskbars[id].pTreeCont.style.height = (h - topHeight - 42) + 'px';
				}
			}

			this.pSearchCont.style.width = w + 'px';
			if (!BX.browser.IsDoctype())
			{
				this.pSearchAli.style.width = (w - 20) + 'px';
			}

			var _this = this;
			if (this.resizeTimeout)
			{
				this.resizeTimeout = clearTimeout(this.resizeTimeout);
			}

			this.resizeTimeout = setTimeout(function()
			{
				if (parseInt(_this.pTopCont.offsetHeight, 10) !== topHeight)
				{
					_this.Resize(w, h);
				}
			}, 100);
		},

		TaskbarSearch: function(e)
		{
			var
				taskbar = this.GetActiveTaskbar(),
				value = this.pSearchInput.value;

			if (e && e.keyCode == this.editor.KEY_CODES['escape'])
			{
				value = this.pSearchInput.value = '';
			}

			if (value.length < 2)
			{
				taskbar.ClearSearchResult();
			}
			else
			{
				taskbar.Search(value);
			}
		}
	};


	/*
	 *
	 *
	 * */
	function Taskbar(editor)
	{
		this.editor = editor;
		this.manager = this.editor.taskbarManager;
		this.searchIndex = [];
		this._searchResult = [];
		this._searchResultSect = [];

		BX.addCustomEvent(this.manager, 'taskbargroupTitleClick', BX.proxy(this.OnGroupTitleClick, this));
		BX.addCustomEvent(this.manager, 'taskbarelementClick', BX.proxy(this.OnElementClick, this));
		BX.addCustomEvent(this.manager, 'taskbarTitleClick', BX.proxy(this.OnTitleClick, this));
		BX.addCustomEvent(this.manager, 'taskbarMenuClick', BX.proxy(this.OnMenuClick, this));
	}

	Taskbar.prototype = {
		GetCont: function()
		{
			return this.pTreeCont;
		},

		GetTitleCont: function()
		{
			return this.pTitleCont;
		},

		BuildSceleton: function()
		{
			// Build title & menu
			this.pTitleCont = BX.create("span", {props: {className: "bxhtmled-split-btn"},html: '<span class="bxhtmled-split-btn-l"><span class="bxhtmled-split-btn-bg">' + this.title + '</span></span><span class="bxhtmled-split-btn-r"><span data-bx-type="taskbar_top_menu" data-bx-taskbar="' + this.id + '" class="bxhtmled-split-btn-bg"></span></span>'});
			this.pTitleCont.setAttribute('data-bx-type', 'taskbar_title_but');
			this.pTitleCont.setAttribute('data-bx-taskbar', this.id);

			this.pTreeCont = BX.create("DIV", {props: {className: "bxhtmled-taskbar-tree-cont"}});
			this.pTreeInnerCont = this.pTreeCont.appendChild(BX.create("DIV", {props: {className: "bxhtmled-taskbar-tree-inner-cont"}}));
		},

		BuildTree: function(sections, elements)
		{
			BX.cleanNode(this.pTreeCont);
			this.treeSectionIndex = {};
			this.BuildTreeSections(sections);
			this.BuildTreeElements(elements);
		},

		BuildTreeSections: function(sections)
		{
			this.sections = [];
			for (var i = 0; i < sections.length; i++)
			{
				this.BuildSection(sections[i]);
			}
		},

		GetSectionsTreeInfo: function()
		{
			return this.sections;
		},

		BuildSection: function(section)
		{
			var
				parentCont = this.GetSectionContByPath(section.path),
				pGroup = BX.create("DIV", {props: {className: "bxhtmled-tskbr-sect-outer"}}),
				pGroupTitle = pGroup.appendChild(BX.create("DIV", {props: {className: "bxhtmled-tskbr-sect"}})),
				icon = pGroupTitle.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-tskbr-sect-icon"}})),
				title = pGroupTitle.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-tskbr-sect-title"}, text: section.title || section.name})),
				childCont = pGroup.appendChild(BX.create("DIV", {props: {className: "bxhtmled-tskb-child"}})),
				elementsCont = pGroup.appendChild(BX.create("DIV", {props: {className: "bxhtmled-tskb-child-elements"}}));

			var key = section.path == '' ? section.name : section.path + ',' + section.name;
			var depth = section.path == '' ? 0 : 1; // Todo....

			var sect = {
				key: key,
				children: [],
				section: section
			}

			this.treeSectionIndex[key] = {
				icon: icon,
				outerCont: pGroup,
				cont: pGroupTitle,
				childCont: childCont,
				elementsCont: elementsCont,
				sect: sect
			};

			this.GetSectionByPath(section.path).push(sect);

			if (depth > 0)
			{
				BX.addClass(pGroupTitle, "bxhtmled-tskbr-sect-" + depth);
				BX.addClass(icon, "bxhtmled-tskbr-sect-icon-" + depth);
			}

			pGroupTitle.setAttribute('data-bx-type', 'taskbargroup_title');
			pGroupTitle.setAttribute('data-bx-taskbar', this.id);

			pGroup.setAttribute('data-bx-type', 'taskbargroup');
			pGroup.setAttribute('data-bx-path', key);
			pGroup.setAttribute('data-bx-taskbar', this.id);

			parentCont.appendChild(pGroup);
		},

		BuildTreeElements: function(elements)
		{
			this.elements = elements;
			for (var i in elements)
			{
				if (elements.hasOwnProperty(i))
				{
					this.BuildElement(elements[i]);
				}
			}
		},

		BuildElement: function(element)
		{
			var
				_this = this,
				parentCont = this.GetSectionContByPath(element.key || element.path, true),
				pElement = BX.create("DIV", {props: {className: "bxhtmled-tskbr-element"}, html: '<span class="bxhtmled-tskbr-element-icon"></span><span class="bxhtmled-tskbr-element-text">' + element.title + '</span>'});

			var dd = pElement.appendChild(BX.create("IMG", {props: {
				src: this.editor.util.GetEmptyImage(),
				className: "bxhtmled-drag"
			}}));

			this.HandleElementEx(pElement, dd, element);

			this.searchIndex.push({
				content: (element.title + ' ' + element.name).toLowerCase(),
				element: pElement
			});

			dd.onmousedown = function (e)
			{
				if (!e)
				{
					e = window.event;
				}

				var
					target = e.target || e.srcElement,
					bxTag = _this.editor.GetBxTag(target);

				return _this.OnElementMouseDownEx(e, target, bxTag);
			};

			dd.ondblclick = function(e)
			{
				var
					target = e.target || e.srcElement,
					bxTag = _this.editor.GetBxTag(target);

				return _this.OnElementDoubleClick(e, target, bxTag);
			};

			dd.ondragend = function (e)
			{
				if (!e)
				{
					e = window.event;
				}
				_this.OnDragEndHandler(e, this);
			};

			pElement.setAttribute('data-bx-type', 'taskbarelement');

			parentCont.appendChild(pElement);
		},

		HandleElementEx: function(dd)
		{

		},

		GetSectionContByPath: function(path, bElement)
		{
			if (path == '' || !this.treeSectionIndex[path])
			{
				return this.pTreeCont;
			}
			else
			{
				return bElement ? this.treeSectionIndex[path].elementsCont : this.treeSectionIndex[path].childCont;
			}
		},

		GetSectionByPath: function(path)
		{
			if (path == '' || !this.treeSectionIndex[path])
			{
				return this.sections;
			}
			else
			{
				return this.treeSectionIndex[path].sect.children;
			}
		},

		// Open or close
		ToggleGroup: function(cont, bOpen)
		{
			// TODO: animation
			var path = cont.getAttribute('data-bx-path');
			if (path)
			{
				var group = this.treeSectionIndex[path];
				if (!group)
				{
					return;
				}

				if (bOpen !== undefined)
				{
					group.opened = !bOpen;
				}

				if (group.opened)
				{
					BX.removeClass(group.cont, 'bxhtmled-tskbr-sect-open');
					BX.removeClass(group.icon, 'bxhtmled-tskbr-sect-icon-open');
					BX.removeClass(group.outerCont, 'bxhtmled-tskbr-sect-outer-open');
					group.childCont.style.display = 'none';
					group.elementsCont.style.display = 'none';
					group.opened = false;
				}
				else
				{
					BX.addClass(group.cont, 'bxhtmled-tskbr-sect-open');
					BX.addClass(group.icon, 'bxhtmled-tskbr-sect-icon-open');
					BX.addClass(group.outerCont, 'bxhtmled-tskbr-sect-outer-open');
					group.childCont.style.display = 'block';
					group.elementsCont.style.display = group.elementsCont.childNodes.length > 0 ? 'block' : 'none';
					group.opened = true;
				}
			}
		},

		OnDragEndHandler: function(e, node)
		{
			var _this = this;
			this.editor.skipPasteHandler = true;
			this.editor.skipPasteControl = true;

			if (this.editor.iframeView.pasteHandlerTimeout)
				this.editor.iframeView.pasteHandlerTimeout = clearTimeout(this.editor.iframeView.pasteHandlerTimeout);

			setTimeout(function()
			{
				var dd = _this.editor.GetIframeElement(node.id);
				if (dd && dd.parentNode)
				{
					var sur = _this.editor.util.CheckSurrogateNode(dd.parentNode);
					if (sur)
					{
						_this.editor.util.InsertAfter(dd, sur);
					}
				}
				_this.editor.synchro.FullSyncFromIframe();

				_this.editor.skipPasteHandler = false;
				_this.editor.skipPasteControl = false;
			}, 20);
		},

		OnElementMouseDownEx: function(e)
		{
			return true;
		},

		OnElementClick: function(e)
		{
			this.OnElementClickEx();
			return true;
		},

		OnElementClickEx: function()
		{
			return true;
		},

		OnElementDoubleClick: function(e, target, bxTag)
		{
			if (target)
			{
				var dd = target.cloneNode(true);
				this.editor.Focus();
				this.editor.selection.InsertNode(dd);
				this.editor.synchro.FullSyncFromIframe();
			}
		},

		OnGroupTitleClick: function(pElement)
		{
			if (pElement && pElement.getAttribute('data-bx-taskbar') == this.id)
			{
				return this.ToggleGroup(pElement.parentNode);
			}
			return true;
		},

		OnTitleClick: function(pElement)
		{
			if (pElement && pElement.getAttribute('data-bx-taskbar') == this.id)
			{
				return this.manager.ShowTaskbar(this.id);
			}
			return true;
		},

		OnMenuClick: function(pElement)
		{
			if (pElement && pElement.getAttribute('data-bx-taskbar') == this.id)
				return this.ShowMenu(pElement);
			return true;
		},

		Activate: function()
		{
			this.pTreeCont.style.display = 'block';
			this.bActive = true;
			return true;
		},

		Deactivate: function()
		{
			this.pTreeCont.style.display = 'none';
			this.bActive = false;
			return true;
		},

		IsActive: function()
		{
			return !!this.bActive;
		},

		ShowMenu: function(pElement)
		{
			var arItems = this.GetMenuItems();
			BX.PopupMenu.destroy(this.uniqueId + "_menu");
			BX.PopupMenu.show(this.uniqueId + "_menu", pElement, arItems, {
					overlay: {opacity: 0.1},
					events: {
						onPopupClose: function(){BX.removeClass(this.bindElement, "bxec-add-more-over");}
					},
					offsetLeft: 1,
					zIndex: 3005
				}
			);
			return true;
		},

		GetMenuItems: function()
		{
			return [];
		},

		Search: function(value)
		{
			this.ClearSearchResult();
			var
				bFoundItems = false,
				pSect, el,
				i, l = this.searchIndex.length;

			value = BX.util.trim(value.toLowerCase());

			BX.addClass(this.pTreeCont, 'bxhtmled-taskbar-tree-cont-search');
			BX.addClass(this.manager.pSearchCont, 'bxhtmled-search-cont-res');

			for(i = 0; i < l; i++)
			{
				el = this.searchIndex[i];
				if (el.content.indexOf(value) !== -1) // Show element
				{
					bFoundItems = true;
					BX.addClass(el.element, 'bxhtmled-tskbr-search-res');
					this._searchResult.push(el.element);

					pSect = BX.findParent(el.element, function(node)
					{
						return node.getAttribute && node.getAttribute('data-bx-type') == 'taskbargroup';
					}, this.pTreeCont);

					while (pSect)
					{
						BX.addClass(pSect, 'bxhtmled-tskbr-search-res');
						this.ToggleGroup(pSect, true);
						this._searchResultSect.push(pSect);

						pSect = BX.findParent(pSect, function(node)
						{
							return node.getAttribute && node.getAttribute('data-bx-type') == 'taskbargroup';
						}, this.pTreeCont);
					}
				}
			}

			if (!bFoundItems)
			{
				this.manager.pSearchNothingNotice.style.display = 'block';
			}
		},

		ClearSearchResult: function()
		{
			BX.removeClass(this.pTreeCont, 'bxhtmled-taskbar-tree-cont-search');
			BX.removeClass(this.manager.pSearchCont, 'bxhtmled-search-cont-res');
			this.manager.pSearchNothingNotice.style.display = 'none';
			var i;
			if (this._searchResult)
			{
				for(i = 0; i < this._searchResult.length; i++)
				{
					BX.removeClass(this._searchResult[i], 'bxhtmled-tskbr-search-res');
				}
				this._searchResult = [];
			}
			if (this._searchResultSect)
			{
				for(i = 0; i < this._searchResultSect.length; i++)
				{
					BX.removeClass(this._searchResultSect[i], 'bxhtmled-tskbr-search-res');
					this.ToggleGroup(this._searchResultSect[i], false);
				}
				this._searchResultSect = [];
			}
		},

		GetId: function()
		{
			return this.id;
		}
	};

	function ComponentsControl(editor)
	{
		// Call parrent constructor
		ComponentsControl.superclass.constructor.apply(this, arguments);

		this.id = 'components';
		this.title = BX.message('ComponentsTitle');
		this.templateId = this.editor.templateId;
		this.uniqueId = 'taskbar_' + this.editor.id + '_' + this.id;
		this.searchPlaceholder = BX.message('BXEdCompSearchPlaceHolder');

		this.Init();
	}

	BX.extend(ComponentsControl, Taskbar);

	ComponentsControl.prototype.Init = function()
	{
		this.BuildSceleton();
		// Build structure
		var list = this.editor.components.GetList();
		this.BuildTree(list.groups, list.items);
	};

	ComponentsControl.prototype.HandleElementEx = function(wrap, dd, params)
	{
		this.editor.SetBxTag(dd, {tag: "component_icon", params: params});
		if (params.complex == "Y")
		{
			params.className = 'bxhtmled-surrogate-green';
			BX.addClass(wrap, 'bxhtmled-tskbr-element-green');
			wrap.title = BX.message('BXEdComplexComp');
		}
	};

	ComponentsControl.prototype.OnElementMouseDownEx = function(e, target, bxTag)
	{
		if (!bxTag || bxTag.tag !== 'component_icon')
		{
			return false;
		}

		this.editor.components.LoadParamsList({
			name: bxTag.params.name
		});
	};

	ComponentsControl.prototype.GetMenuItems = function()
	{
		var _this = this;
		return [
			{
				text : BX.message('RefreshTaskbar'),
				title : BX.message('RefreshTaskbar'),
				className : "",
				onclick: function()
				{
					_this.editor.componentsTaskbar.ClearSearchResult();
					_this.editor.components.ReloadList();
					BX.PopupMenu.destroy(_this.uniqueId + "_menu");
				}
			}
		];
	};

	// Editor dialog
	function Dialog(editor, params)
	{
		this.editor = editor;
		this.id = params.id;
		this.params = params;
		this.className = "bxhtmled-dialog" + (params.className ? ' ' + params.className : '');
		this.zIndex = params.zIndex || 3008;
		this.firstFocus = false;
		this.Init();
	}

	Dialog.prototype = {
		Init: function()
		{
			var
				_this = this,
				config = {
					title : this.params.title || this.params.name || '',
					width: this.params.width || 600,
					resizable: false
				};

			if (this.params.resizable)
			{
				config.resizable = true;
				config.min_width = this.params.min_width || 400;
				config.min_height = this.params.min_height || 250;
				config.resize_id = this.params.resize_id || this.params.id + '_res';
			}

			this.oDialog = new BX.CDialog(config);
			config.height = this.params.height || false;

			BX.addCustomEvent(this.oDialog, 'onWindowResize', BX.proxy(this.OnResize, this));
			BX.addCustomEvent(this.oDialog, 'onWindowResizeFinished', BX.proxy(this.OnResizeFinished, this));
			BX.addClass(this.oDialog.PARTS.CONTENT, this.className);

			// Clear dialog height for auto resizing
			if (!config.height)
			{
				this.oDialog.PARTS.CONTENT_DATA.style.height = null;
			}

			// Buttons
			this.oDialog.SetButtons([
				new BX.CWindowButton(
					{
						title: BX.message('DialogSave'),
						className: 'adm-btn-save',
						action: function()
						{
							BX.onCustomEvent(_this, "OnDialogSave");
							_this.oDialog.Close();
						}
					}),
				this.oDialog.btnCancel
			]);

			BX.addCustomEvent(this.oDialog, 'onWindowUnRegister', function()
			{
				BX.unbind(window, "keydown", BX.proxy(_this.OnKeyDown, _this));
				_this.dialogShownTimeout = setTimeout(function(){_this.editor.dialogShown = false;}, 300);
				_this.RestoreWindowOverflow();
			});
		},

		Show: function()
		{
			var _this = this;
			this.editor.dialogShown = true;
			if (this.dialogShownTimeout)
			{
				this.dialogShownTimeout = clearTimeout(this.dialogShownTimeout);
			}
			this.oDialog.Show();
			this.oDialog.DIV.style.zIndex = this.zIndex;
			this.oDialog.OVERLAY.style.zIndex = this.zIndex - 2;
			var
				top = parseInt(this.oDialog.DIV.style.top) - 180,
				scrollPos = BX.GetWindowScrollPos(document),
				scrollTop = scrollPos.scrollTop,
				minTop = scrollTop + 50;

			this.oDialog.DIV.style.top = (top > minTop ? top : minTop) + 'px';
			BX.bind(window, "keydown", BX.proxy(this.OnKeyDown, this));

			this.savedBodyOverflow = this.savedScrollLeft = this.savedScrollTop = false;

			setTimeout(function()
				{
					// Hack for Opera
					if (BX.browser.IsOpera())
						_this.oDialog.Move(1, 1);

					_this.oDialog.__resizeOverlay();

					if (_this.firstFocus)
					{
						BX.focus(_this.firstFocus);
						if (_this.selectFirstFocus)
							_this.firstFocus.select();
					}
				},
				100
			);
		},

		BuildTabControl: function(pCont, arTabs)
		{
			var
				i,
				pTabsWrap = BX.create('DIV', {props: {className: 'bxhtmled-dlg-tabs-wrap'}}),
				pContWrap = BX.create('DIV', {props: {className: 'bxhtmled-dlg-cont-wrap'}});

			for (i = 0; i < arTabs.length; i++)
			{
				arTabs[i].tab = pTabsWrap.appendChild(BX.create('SPAN', {props: {className: 'bxhtmled-dlg-tab' + (i == 0 ? ' bxhtmled-dlg-tab-active' : '')}, attrs: {'data-bx-dlg-tab-ind': i.toString()}, text: arTabs[i].name}));
				arTabs[i].cont = pContWrap.appendChild(BX.create('DIV', {props: {className: 'bxhtmled-dlg-cont'}, style: {'display' : i == 0 ? '' : 'none'}}));
			}

			BX.bind(pTabsWrap, 'click', function(e)
			{
				var
					ind,
					target = e.target || e.srcElement;

				if (target && target.getAttribute)
				{
					ind = parseInt(target.getAttribute('data-bx-dlg-tab-ind'));
					if (!isNaN(ind))
					{
						for (i = 0; i < arTabs.length; i++)
						{
							if (i == ind)
							{
								arTabs[i].cont.style.display = '';
								BX.addClass(arTabs[i].tab, 'bxhtmled-dlg-tab-active');
							}
							else
							{
								arTabs[i].cont.style.display = 'none';
								BX.removeClass(arTabs[i].tab, 'bxhtmled-dlg-tab-active');
							}
						}
					}
				}
			});

			pCont.appendChild(pTabsWrap);
			pCont.appendChild(pContWrap);

			return {
				cont: pCont,
				tabsWrap : pTabsWrap,
				contWrap : pContWrap,
				tabs: arTabs
			};
		},

		OnKeyDown: function(e)
		{
			if (e.keyCode == 13 && this.closeByEnter !== false)
			{
				var target = e.target || e.srcElement;
				if (target && target.nodeName !== 'TEXTAREA')
				{
					this.oDialog.PARAMS.buttons[0].emulate();
				}
			}
		},

		SetContent: function(html)
		{
			return this.oDialog.SetContent(html);
		},

		SetTitle: function(title)
		{
			return this.oDialog.SetTitle(title);
		},

		OnResize: function()
		{
		},

		OnResizeFinished: function()
		{
		},

		GetContentSize: function()
		{
			return {
				width : this.oDialog.PARTS.CONTENT_DATA.offsetWidth,
				height : this.oDialog.PARTS.CONTENT_DATA.offsetHeight
			};
		},

		Save: function()
		{
			if (this.savedRange)
			{
				this.editor.selection.SetBookmark(this.savedRange);
			}

			if (this.action && this.editor.action.IsSupported(this.action))
			{
				this.editor.action.Exec(this.action, this.GetValues());
			}
		},

		Close: function()
		{
			if (this.IsOpen())
			{
				this.oDialog.Close();
			}
		},

		IsOpen: function()
		{
			return this.oDialog.isOpen;
		},

		DisableKeyCheck: function()
		{
			this.closeByEnter = false;
			BX.WindowManager.disableKeyCheck();
		},

		EnableKeyCheck: function()
		{
			var _this = this;
			setTimeout(function()
			{
				_this.closeByEnter = true;
				BX.WindowManager.enableKeyCheck();
			}, 200);
		},

		AddTableRow: function (tbl, firstCell)
		{
			var r, c1, c2;

			r = tbl.insertRow(-1);
			c1 = r.insertCell(-1);
			c1.className = 'bxhtmled-left-c';

			if (firstCell && firstCell.label)
			{
				c1.appendChild(BX.create('LABEL', {props: {className: firstCell.required ? 'bxhtmled-req' : ''}, text: firstCell.label})).setAttribute('for', firstCell.id);
			}

			c2 = r.insertCell(-1);
			c2.className = 'bxhtmled-right-c';
			return {row: r, leftCell: c1, rightCell: c2};
		},

		SetValues: BX.DoNothing,
		GetValues: BX.DoNothing,

		CheckSize: function(timeout)
		{
			var _this = this;

			if (this.checkSizeTimeout)
				this.checkSizeTimeout = clearTimeout(this.checkSizeTimeout);

			if (timeout !== true)
			{
				this.checkSizeTimeout = setTimeout(function()
				{
					_this.CheckSize(true);
				}, 50);
				return;
			}

			var
				innerSize = BX.GetWindowInnerSize(document),
				dialogBottom = this.oDialog.DIV.offsetHeight + 50;

			if (dialogBottom >= innerSize.innerHeight)
			{
				var scrollPos = BX.GetWindowScrollPos(document);
				this.savedBodyOverflow = document.body.style.overflow;
				this.savedScrollTop = scrollPos.scrollTop;
				this.savedScrollLeft = scrollPos.scrollLeft;
				document.body.style.overflow = "auto";

				if (this.editor.expanded)
					BX.unbind(window, "scroll", BX.proxy(this.editor.PreventScroll, this.editor));
			}
			else
			{
				this.RestoreWindowOverflow();
			}
		},

		RestoreWindowOverflow: function()
		{
			if (this.savedBodyOverflow !== false)
			{
				document.body.style.overflow = this.savedBodyOverflow;
				this.savedBodyOverflow = false;
			}

			if (this.savedScrollTop !== false)
			{
				window.scrollTo(this.savedScrollLeft, this.savedScrollTop);
				this.savedScrollLeft = this.savedScrollTop = false;
			}

			if (this.editor.expanded)
				BX.bind(window, "scroll", BX.proxy(this.editor.PreventScroll, this.editor));
		}
	};

	function ContextMenu(editor)
	{
		this.editor = editor;
		BX.addCustomEvent(this.editor, 'OnIframeContextMenu', BX.delegate(this.Show, this));
		this.Init();
	}

	ContextMenu.prototype = {
		Init: function()
		{
			var
				_this = this,
				defaultItem = {
					TEXT: BX.message('ContMenuDefProps'),
					ACTION: function()
					{
						_this.editor.selection.SetBookmark(_this.savedRange);
						_this.editor.GetDialog('Default').Show(false, _this.savedRange);
						_this.Hide();
					}
				};

			// Remove format ?
			// Replace Node by children ?

			this.items = {
				// Surrogates
				'php' : [
					{
						TEXT: BX.message('BXEdContMenuPhpCode'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.php)
							{
								_this.editor.GetDialog('Source').Show(items.php.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'anchor' : [
					{
						TEXT: BX.message('BXEdEditAnchor'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.anchor)
							{
								_this.editor.GetDialog('Anchor').Show(items.anchor.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'javascript' : [
					{
						TEXT: BX.message('BXEdContMenuJavascript'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.javascript)
							{
								_this.editor.GetDialog('Source').Show(items.javascript.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'htmlcomment' : [
					{
						TEXT: BX.message('BXEdContMenuHtmlComment'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.htmlcomment)
							{
								_this.editor.GetDialog('Source').Show(items.htmlcomment.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'iframe' : [
					{
						TEXT: BX.message('BXEdContMenuIframe'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.iframe)
							{
								_this.editor.GetDialog('Source').Show(items.iframe.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'style' : [
					{
						TEXT: BX.message('BXEdContMenuStyle'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.style)
							{
								_this.editor.GetDialog('Source').Show(items.style.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'object' : [
					{
						TEXT: BX.message('BXEdContMenuObject'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.object)
							{
								_this.editor.GetDialog('Source').Show(items.object.bxTag);
							}
							_this.Hide();
						}
					}
				],
				'component' : [
					{
						TEXT: BX.message('BXEdContMenuComponent'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.component)
							{
								// Show dialog
								_this.editor.components.ShowPropertiesDialog(items.component.bxTag.params, _this.editor.GetBxTag(items.component.bxTag.surrogateId));
							}
							_this.Hide();
						}
					},
					{
						TEXT: BX.message('BXEdContMenuComponentRemove'),
						ACTION: function()
						{
							var items = _this.GetTargetItem();
							if (items && items.component)
							{
								BX.remove(items.component.element);
							}
							_this.Hide();
						}
					}
				],
				'printbreak' : [
					{
						TEXT: BX.message('NodeRemove'),
						ACTION: function(e)
						{
							var node = _this.GetTargetItem('printbreak');
							if (node && node.element)
							{
								_this.editor.selection.RemoveNode(node.element);
							}
							_this.Hide();
						}
					}
				],
				'video': [
					{
						TEXT: BX.message('BXEdVideoProps'),
						bbMode: true,
						ACTION: function()
						{
							var node = _this.GetTargetItem('video');
							if (node)
							{
								_this.editor.GetDialog('Video').Show(node.bxTag);
							}
							_this.Hide();
						}
					},
					{
						TEXT: BX.message('BXEdVideoDel'),
						bbMode: true,
						ACTION: function(e)
						{
							var node = _this.GetTargetItem('video');
							if (node && node.element)
							{
								_this.editor.selection.RemoveNode(node.element);
							}
							_this.Hide();
						}
					}
				],
				'smile' : [],

				// Nodes
				'A' : [
					{
						TEXT: BX.message('ContMenuLinkEdit'),
						bbMode: true,
						ACTION: function()
						{
							var node = _this.GetTargetItem('A');
							if (node)
							{
								_this.editor.GetDialog('Link').Show([node], this.savedRange);
							}
							_this.Hide();
						}
					},
					{
						TEXT: BX.message('ContMenuLinkDel'),
						bbMode: true,
						ACTION: function()
						{
							var link = _this.GetTargetItem('A');
							if (link && _this.editor.action.IsSupported('removeLink'))
							{
								_this.editor.action.Exec('removeLink', [link]);
							}
							_this.Hide();
						}
					}
				],
				'IMG': [
					{
						TEXT: BX.message('ContMenuImgEdit'),
						bbMode: true,
						ACTION: function()
						{
							var node = _this.GetTargetItem('IMG');
							if (node)
							{
								_this.editor.GetDialog('Image').Show([node], _this.savedRange);
							}
							_this.Hide();
						}
					},
					{
						TEXT: BX.message('ContMenuImgDel'),
						bbMode: true,
						ACTION: function()
						{
							var node = _this.GetTargetItem('IMG');
							if (node)
							{
								_this.editor.selection.RemoveNode(node);
							}
							_this.Hide();
						}
					}
				],
				'DIV': [
					{
						TEXT: BX.message('ContMenuCleanDiv'),
						title: BX.message('ContMenuCleanDiv_Title'),
						ACTION: function()
						{
							var node = _this.GetTargetItem('DIV');
							if (node)
							{
								_this.editor.On('OnHtmlContentChangedByControl');
								_this.editor.util.ReplaceWithOwnChildren(node);
								_this.editor.synchro.FullSyncFromIframe();
							}
							_this.Hide();
						}
					},
					defaultItem
				],
				'TABLE': [

					{
						TEXT: BX.message('BXEdTableInsertMenu'),
						HIDE_ITEM: function()
						{
							var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
							return !cells || cells.length != 1;
						},
						MENU: [
							// Column
							{
								TEXT: BX.message('BXEdTableInsColLeft'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'insertColumnLeft',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							},
							{
								TEXT: BX.message('BXEdTableInsColRight'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'insertColumnRight',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							},
							// Row
							{
								TEXT: BX.message('BXEdTableInsRowUpper'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'insertRowUpper',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							},
							{
								TEXT: BX.message('BXEdTableInsRowLower'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'insertRowLower',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							},
							// Cell
							{
								TEXT: BX.message('BXEdTableInsCellBefore'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'insertCellLeft',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							},
							{
								TEXT: BX.message('BXEdTableInsCellAfter'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'insertCellRight',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							}
						]
					},
					{
						TEXT: BX.message('BXEdTableRemoveMenu'),
						MENU: [
							{
								TEXT: BX.message('BXEdTableDelCol'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'removeColumn',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
									return !cells || cells.length != 1;
								}
							},
							{
								TEXT: BX.message('BXEdTableDelRow'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'removeRow',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
									return !cells || cells.length != 1;
								}
							},
							{
								TEXT: BX.message('BXEdTableDellCell'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'removeCell',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
									return !cells || cells.length != 1;
								}
							},
							{
								TEXT: BX.message('BXEdTableDellSelectedCells'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'removeSelectedCells',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
									return !cells || cells.length === 1;
								}
							}
						]
					},
					{
						TEXT: BX.message('BXEdTableMergeMenu'),
						MENU: [
							{
								TEXT: BX.message('BXEdTableMergeSelectedCells'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'mergeSelectedCells',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									if (!_this.savedRange.collapsed)
									{
										return !_this.editor.action.actions.tableOperation.canBeMerged(false, _this.savedRange, _this.GetTargetItem('TABLE'));
									}

									return true;
								}
							},
							{
								TEXT: BX.message('BXEdTableMergeRight'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'mergeRightCell',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									return !_this.editor.action.actions.tableOperation.canBeMergedWithRight(_this.savedRange, _this.GetTargetItem('TABLE'));
								}
							},
							{
								TEXT: BX.message('BXEdTableMergeBottom'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'mergeBottomCell',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									return !_this.editor.action.actions.tableOperation.canBeMergedWithBottom(_this.savedRange, _this.GetTargetItem('TABLE'));
								}
							},
							{
								TEXT: BX.message('BXEdTableMergeRowCells'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'mergeRow',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
									return !cells || cells.length > 1;
								}
							},
							{
								TEXT: BX.message('BXEdTableMergeColCells'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'mergeColumn',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								},
								HIDE_ITEM: function()
								{
									var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
									return !cells || cells.length > 1;
								}
							}
						]
					},
					{
						TEXT: BX.message('BXEdTableSplitMenu'),
						HIDE_ITEM: function()
						{
							var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
							return !cells || cells.length != 1;
						},
						MENU: [
							{
								TEXT: BX.message('BXEdTableSplitCellHor'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'splitHorizontally',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							},
							{
								TEXT: BX.message('BXEdTableSplitCellVer'),
								ACTION: function()
								{
									_this.editor.action.Exec('tableOperation', {
										actionType: 'splitVertically',
										tableNode: _this.GetTargetItem('TABLE'),
										range: _this.savedRange
									});
									_this.Hide();
								}
							}
						]
					},
					{SEPARATOR: true},

					{
						TEXT: BX.message('BXEdTableTableCellProps'),
						ACTION: function()
						{
							var node = _this.GetTargetItem('TABLE');
							if (node)
							{
								var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
								_this.editor.GetDialog('Default').Show(cells, _this.savedRange);
							}
							_this.Hide();
						},
						HIDE_ITEM: function()
						{
							var cells = _this.editor.action.actions.tableOperation.getSelectedCells(_this.savedRange, _this.GetTargetItem('TABLE'));
							return !cells || cells.length != 1;
						}
					},

					{
						TEXT: BX.message('BXEdTableTableProps'),
						ACTION: function()
						{
							var node = _this.GetTargetItem('TABLE');
							if (node)
							{
								_this.editor.GetDialog('Table').Show([node], _this.savedRange);
							}
							_this.Hide();
						}
					},
					{
						TEXT: BX.message('BXEdTableDeleteTable'),
						bbMode: true,
						ACTION: function()
						{
							var node = _this.GetTargetItem('TABLE');
							if (node)
							{
								_this.editor.selection.RemoveNode(node);
							}
							_this.Hide();
						}
					}
				],
				// ...
				'DEFAULT': [defaultItem]
			};
		},

		Show: function(e, target, collapsedSelection)
		{
			this.savedRange = this.editor.selection.GetBookmark();
			this.Hide();

			this.editor.contextMenuShown = true;
			if (this.contextMenuShownTimeout)
			{
				this.contextMenuShownTimeout = clearTimeout(this.contextMenuShownTimeout);
			}
			this.nodes = [];
			this.tagIndex = {};
			var
				bxTag,
				i, j, k, menuItems, item, itemGr,
				arItems = [],
				maxIter = 20, iter = 0,
				element = target,
				label;

			this.targetItems = {};
			while (true)
			{
				if (element.nodeName && element.nodeName.toUpperCase() != 'BODY')
				{
					if (element.nodeType != 3)
					{
						bxTag = this.editor.GetBxTag(element);

						if (bxTag && bxTag.tag == 'surrogate_dd')
						{
							var origTag = this.editor.GetBxTag(bxTag.params.origId);
							element = this.editor.GetIframeElement(origTag.id);
							this.PushTargetItem(origTag.tag, {element: element, bxTag: origTag});
							this.nodes = [element];
							this.tagIndex[origTag.tag] = 0;
							iter = 0;
							element = element.parentNode;
							continue;
						}
						else if (bxTag && bxTag.tag && this.items[bxTag.tag])
						{
							this.nodes = [element];
							this.PushTargetItem(bxTag.tag, {element: element, bxTag: bxTag.tag});
							this.nodes = [element];
							this.tagIndex[bxTag.tag] = 0;
							iter = 0;
							element = element.parentNode;
							continue;
						}

						label = element.nodeName;
						this.PushTargetItem(label, element);
						this.nodes.push(element);
						this.tagIndex[label] = this.nodes.length - 1;
					}
					iter++;
				}

				if (!element ||
					element.nodeName && element.nodeName.toUpperCase() == 'BODY' ||
					iter >= maxIter)
				{
					break;
				}

				element = element.parentNode;
			}

			for (i in this.items)
			{
				if (this.items.hasOwnProperty(i) && this.tagIndex[i] != undefined)
				{
					if (arItems.length > 0)
					{
						arItems.push({SEPARATOR : true});
					}

					for (j = 0; j < this.items[i].length; j++)
					{
						if (typeof this.items[i][j].HIDE_ITEM == 'function'  && this.items[i][j].HIDE_ITEM() === true)
							continue;

						if (this.editor.bbCode && !this.items[i][j].bbMode)
							continue;

						if (this.items[i][j].MENU)
						{
							itemGr = BX.clone(this.items[i][j]);
							menuItems = [];
							for (k = 0; k < itemGr.MENU.length; k++)
							{
								item = itemGr.MENU[k];
								if (typeof item.HIDE_ITEM == 'function'  && item.HIDE_ITEM() === true)
									continue;

								if (this.editor.bbCode && !item.bbMode)
									continue;

								menuItems.push(item);
							}

							if (menuItems.length === 0)
								continue;

							itemGr.MENU = menuItems;

							arItems.push(itemGr);
						}
						else
						{
							arItems.push(this.items[i][j]);
						}
					}
				}
			}

			if (arItems.length == 0 && (!this.editor.bbCode || this.items['DEFAULT'].bbMode))
			{
				if (!this.savedRange || (!this.savedRange.collapsed && !collapsedSelection))
				{
					for (j = 0; j < this.items['DEFAULT'].length; j++)
					{
						arItems.push(this.items['DEFAULT'][j]);
					}
				}
			}

			var
				x = e.clientX,
				y = e.clientY;

			if (!this.dummyTarget)
			{
				this.dummyTarget = this.editor.dom.iframeCont.appendChild(BX.create('DIV', {props: {className: 'bxhtmled-dummy-target'}}));
			}

			this.dummyTarget.style.left = x + 'px';
			this.dummyTarget.style.top = y + 'px';
			this.dummyTarget.style.zIndex = '2002';

			if (arItems.length > 0)
			{
				this.OPENER = new BX.COpener({
					DIV: this.dummyTarget,
					MENU: arItems,
					TYPE: 'click',
					ACTIVE_CLASS: 'adm-btn-active',
					CLOSE_ON_CLICK: true
				});
				this.OPENER.Open();
				var popupDiv = this.OPENER.GetMenu().DIV;
				popupDiv.style.zIndex = '3005';
				popupDiv.id = 'bx-admin-prefix';
				BX.addClass(popupDiv, 'bx-core-popup-menu-editor');

				this.isOpened = true;
				BX.addCustomEvent(this.editor, 'OnIframeClick', BX.proxy(this.Hide, this));
				BX.addCustomEvent(this.editor, 'OnIframeKeyup', BX.proxy(this.CheckEscapeClose, this));
				return BX.PreventDefault(e);
			}
		},

		Hide: function()
		{
			if (this.OPENER)
			{
				var _this = this;
				this.contextMenuShownTimeout = setTimeout(function(){_this.editor.contextMenuShown = false;}, 300);
				this.OPENER.bMenuInit = true;
				this.OPENER.Close();
				this.isOpened = false;
				BX.removeCustomEvent(this.editor, 'OnIframeClick', BX.proxy(this.Hide, this));
				BX.removeCustomEvent(this.editor, 'OnIframeKeyup', BX.proxy(this.CheckEscapeClose, this));
			}
		},

		CheckEscapeClose: function(e, keyCode)
		{
			if (keyCode == this.editor.KEY_CODES['escape'])
				this.Hide();
		},

		GetTargetItem: function(tag)
		{
			return tag ? (this.targetItems[tag] || null) : this.targetItems;
		},

		PushTargetItem: function(key, tag)
		{
			if (!this.targetItems[key])
				this.targetItems[key] = tag;
		}
	};

	function Toolbar(editor, topControls)
	{
		this.editor = editor;
		this.pCont = editor.dom.toolbar;
		this.controls = {};
		this.bCompact = false;
		this.topControls = topControls;
		this.showMoreButton = false;
		this.shown = true;
		this.height = 34;
		this.Init();
	}

	Toolbar.prototype = {
		Init: function()
		{
			this.BuildControls();
			// Init Event handlers
			BX.addCustomEvent(this.editor, "OnIframeFocus", BX.delegate(this.EnableWysiwygButtons, this));
			BX.addCustomEvent(this.editor, "OnTextareaFocus", BX.delegate(this.DisableWysiwygButtons, this));
		},

		BuildControls: function()
		{
			BX.cleanNode(this.pCont);
			var
				i,
				wrap, moreCont, cont,
				map = this.GetControlsMap(),
				wraps = {
					left: this.pCont.appendChild(BX.create('span', {props: {className: 'bxhtmled-top-bar-left-wrap'}, style: {display: 'none'}})),
					main: this.pCont.appendChild(BX.create('span', {props: {className: 'bxhtmled-top-bar-wrap'}, style: {display: 'none'}})),
					right: this.pCont.appendChild(BX.create('span', {props: {className: 'bxhtmled-top-bar-right-wrap'}, style: {display: 'none'}})),
					hidden: this.pCont.appendChild(BX.create('span', {props: {className: 'bxhtmled-top-bar-hidden-wrap'}}))
				};

			this.hiddenWrap = wraps.hidden;

			this.editor.normalWidth = this.editor.NORMAL_WIDTH;
			for (i = 0; i < map.length; i++)
			{
				if(map[i].hidden)
				{
					map[i].wrap = 'hidden';
					this.showMoreButton = true;
				}
				else if (map[i].checkWidth && map[i].offsetWidth)
				{
					this.editor.normalWidth += map[i].offsetWidth;
				}

				wrap = wraps[(map[i].wrap || 'main')];

				if (!wrap)
				{
					// We trying to find wrap as dom element by Id
					wrap = BX(map[i].wrap);
					if (wrap)
					{
						wraps[map[i].wrap] = wrap;
					}
					else
					{
						wrap = wraps['main'];
					}
				}

				if (wrap.style.display == 'none')
					wrap.style.display = '';

				if (map[i].separator)
				{
					wrap.appendChild(this.GetSeparator()); // Show separator
				}
				else if(this.topControls[map[i].id])
				{
					if (!this.controls[map[i].id])
					{
						this.controls[map[i].id] = new this.topControls[map[i].id](this.editor, wrap);
					}
					else
					{
						cont = this.controls[map[i].id].GetPopupBindCont ? this.controls[map[i].id].GetPopupBindCont() : this.controls[map[i].id].GetCont();

						if (this.controls[map[i].id].CheckBeforeShow && !this.controls[map[i].id].CheckBeforeShow())
							continue;

						if (this.controls.More && ((this.bCompact && !map[i].compact) || map[i].hidden))
						{
							if (!moreCont)
							{
								moreCont = this.controls.More.GetPopupCont();
							}
							moreCont.appendChild(cont);
						}
						else
						{
							wrap.appendChild(cont);
						}
					}
				}
			}

			for (i in wraps)
			{
				if (wraps.hasOwnProperty(i) && i !== 'main' && i !== 'left' && i !== 'right' && i !== 'hidden' && wraps[i].getAttribute('data-bx-check-command') !== 'N')
				{
					wraps[i].setAttribute('data-bx-check-command', 'N');
					BX.bind(wraps[i], "click", BX.proxy(function(e)
					{
						this.editor.CheckCommand(e.target || e.srcElement);
					}, this));
				}
			}
		},

		GetControlsMap: function()
		{
			if (this.controlsMap)
				return this.controlsMap;

			var res = this.editor.config.controlsMap;
			if (!res)
			{
				res = [
					//{id: 'SearchButton', wrap: 'left', compact: true},
					{id: 'ChangeView', wrap: 'left', compact: true, sort: 10},
					{id: 'Undo', compact: false, sort: 20},
					{id: 'Redo', compact: false, sort: 30},
					{id: 'StyleSelector', compact: true, sort: 40},
					{id: 'FontSelector', compact: false, sort: 50},
					{id: 'FontSize', compact: false, sort: 60},
					{separator: true, compact: false, sort: 70},
					{id: 'Bold', compact: true, sort: 80},
					{id: 'Italic', compact: true, sort: 90},
					{id: 'Underline', compact: true, sort: 100},
					{id: 'Strikeout', compact: true, sort: 110},
					{id: 'RemoveFormat', compact: true, sort: 120},
					{id: 'Color', compact: true, sort: 130},
					{separator: true, compact: false, sort: 140},
					{id: 'OrderedList', compact: true, sort: 150},
					{id: 'UnorderedList', compact: true, sort: 160},
					{id: 'IndentButton', compact: true, sort: 170},
					{id: 'OutdentButton', compact: true, sort: 180},
					{id: 'AlignList',compact: true, sort: 190},
					{separator: true, compact: false, sort: 200},
					{id: 'InsertLink', compact: true, sort: 210},
					{id: 'InsertImage', compact: true, sort: 220},
					{id: 'InsertVideo', compact: true, sort: 230},
					{id: 'InsertAnchor', compact: false, sort: 240},
					{id: 'InsertTable', compact: false, sort: 250},
					{id: 'InsertChar', compact: false, hidden: true, sort: 260},
					{id: 'PrintBreak', compact: false, hidden: true, sort: 270},
					{id: 'PageBreak', compact: false, hidden: true, sort: 275},
					{id: 'Spellcheck', compact: false, hidden: true, sort: 280},
					{id: 'InsertHr', compact: false, hidden: true, sort: 290},
					{id: 'Sub', compact: false, hidden: true, sort: 310},
					{id: 'Sup', compact: false, hidden: true, sort: 320},
					{id: 'TemplateSelector', compact: false, sort: 330},
					{id: 'Fullscreen', compact: true, sort: 340},

					{id: 'More', compact: true, sort: 400},
					{id: 'Settings',  wrap: 'right', compact: true, sort: 500}
				];
			}

			this.editor.On("GetControlsMap", [res]);
			res = res.sort(function(a, b){return a.sort - b.sort});
			this.controlsMap = res;
			return res;
		},

		GetSeparator: function()
		{
			return BX.create('span', {props: {className: 'bxhtmled-top-bar-separator'}});
		},

		GetHeight: function()
		{
			var res = 0;
			if (this.shown)
			{
				if (!this.height)
					this.height = parseInt(this.editor.dom.toolbarCont.offsetHeight);

				res = this.height;
			}
			return res;
		},

		DisableWysiwygButtons: function(bDisable)
		{
			bDisable = bDisable !== false;
			for (var i in this.controls)
			{
				if (this.controls.hasOwnProperty(i) && typeof this.controls[i].Disable == 'function' && this.controls[i].disabledForTextarea !== false)
					this.controls[i].Disable(bDisable);
			}
		},

		EnableWysiwygButtons: function()
		{
			this.DisableWysiwygButtons(false);
		},

		AdaptControls: function(width)
		{
			var bCompact = width < this.editor.normalWidth;
			if (this.controls.More)
			{
				if (bCompact || this.showMoreButton)
				{
					this.controls.More.GetCont().style.display = '';
				}
				else
				{
					this.controls.More.GetCont().style.display = 'none';
				}

				if (this.controls.More.pCont && this.controls.More.pCont.style.display !== 'none')
					this.controls.More.Close();
			}

			if (!bCompact && this.showMoreButton)
			{
				var moreCont = this.controls.More.GetPopupCont();
				while (this.hiddenWrap.firstChild)
				{
					moreCont.appendChild(this.hiddenWrap.firstChild);
				}
			}

			if (this.bCompact != bCompact)
			{
				this.bCompact = bCompact;
				this.BuildControls();
			}
		},

		Hide: function()
		{
			this.shown = false;
			this.editor.dom.toolbarCont.style.display = 'none';
			this.editor.ResizeSceleton();
		},

		Show: function()
		{
			this.shown = true;
			this.editor.dom.toolbarCont.style.display = '';
			this.editor.ResizeSceleton();
		},

		IsShown: function()
		{
			return this.shown;
		}
	};

	function NodeNavi(editor)
	{
		this.editor = editor;
		this.bShown = false;
		this.pCont = editor.dom.navCont;
		this.controls = {};
		this.height = 28;
		this.Init();
	}

	NodeNavi.prototype = {
		Init: function()
		{
			BX.addCustomEvent(this.editor, "OnIframeMouseDown", BX.proxy(this.OnIframeMousedown, this));
			BX.addCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this.OnIframeKeyup, this));
			BX.addCustomEvent(this.editor, "OnTextareaFocus", BX.delegate(this.Disable, this));
			BX.addCustomEvent(this.editor, "OnHtmlContentChangedByControl", BX.delegate(this.OnIframeKeyup, this));
			BX.bind(this.pCont, 'click', BX.delegate(this.ShowMenu, this));

			var _this = this;

			this.items = {
				// Surrogates
				'php' : function(node, bxTag)
				{
					_this.editor.GetDialog('Source').Show(bxTag);
				},
				'anchor' : function(node, bxTag)
				{
					_this.editor.GetDialog('Anchor').Show(bxTag);
				},
				'javascript' : function(node, bxTag)
				{
					_this.editor.GetDialog('Source').Show(bxTag);
				},
				'htmlcomment' : function(node, bxTag)
				{
					_this.editor.GetDialog('Source').Show(bxTag);
				},
				'iframe' : function(node, bxTag)
				{
					_this.editor.GetDialog('Source').Show(bxTag);
				},
				'style' : function(node, bxTag)
				{
					_this.editor.GetDialog('Source').Show(bxTag);
				},
				'video' : function(node, bxTag)
				{
					_this.editor.GetDialog('Video').Show(bxTag);
				},
				'component' : function(node, bxTag)
				{
					_this.editor.components.ShowPropertiesDialog(bxTag.params, _this.editor.GetBxTag(bxTag.surrogateId));
				},
				'printbreak' : false,

				// Nodes
				'A' : function(node)
				{
					_this.editor.GetDialog('Link').Show([node]);
				},
				'IMG' : function(node)
				{
					_this.editor.GetDialog('Image').Show([node]);
				},
				'TABLE' : function(node)
				{
					_this.editor.GetDialog('Table').Show([node]);
				},
				'DEFAULT' : function(node)
				{
					_this.editor.GetDialog('Default').Show([node]);
				}
			};
		},

		Show: function(bShow)
		{
			this.bShown = bShow = bShow !== false;
			this.pCont.style.display = bShow ? 'block' : 'none';
		},

		GetHeight: function()
		{
			if (!this.bShown)
				return 0;

			if (!this.height)
				this.height = parseInt(this.pCont.offsetHeight);

			return this.height;
		},

		OnIframeMousedown: function(e, target, bxTag)
		{
			this.BuildNavi(target);
		},

		OnIframeKeyup: function(e, keyCode, target)
		{
			this.BuildNavi(target);
		},

		BuildNavi: function(node)
		{
			BX.cleanNode(this.pCont);
			if (!node)
			{
				node = this.editor.GetIframeDoc().body;
			}
			this.nodeIndex = [];
			var itemCont, label, bxTag;
			while (node)
			{
				if (node.nodeType != 3)
				{
					bxTag = this.editor.GetBxTag(node);
					if (bxTag.tag)
					{
						if (bxTag.tag == "surrogate_dd")
						{
							node = node.parentNode;
							continue;
						}

						BX.cleanNode(this.pCont);
						this.nodeIndex = [];

						label = bxTag.name || bxTag.tag;
					}
					else
					{
						label = node.nodeName;
					}

					itemCont = BX.create("SPAN", {props: {className: "bxhtmled-nav-item"}, text: label});
					itemCont.setAttribute('data-bx-node-ind', this.nodeIndex.length.toString());

					this.nodeIndex.push({node: node, bxTag: bxTag.tag});

					if (this.pCont.firstChild)
					{
						this.pCont.insertBefore(itemCont, this.pCont.firstChild);
						if(!this.AdjustSize())
						{
							break;
						}
					}
					else
					{
						this.pCont.appendChild(itemCont);
					}
				}
				if (node.nodeName && node.nodeName.toUpperCase() == 'BODY')
				{
					break;
				}
				node = node.parentNode;
			}

			this.AdjustSize();
		},

		AdjustSize: function()
		{
			if (this.pCont.lastChild && this.pCont.lastChild.offsetTop > 0)
			{
				BX.remove(this.pCont.firstChild);
				return false;
			}
			return true;
		},

		ShowMenu: function(e)
		{
			if (!this.nodeIndex)
			{
				return;
			}

			var
				_this = this,
				nodeIndex,
				origNode,
				target;

			if (e.target)
			{
				target = e.target;
			}
			else if (e.srcElement)
			{
				target = e.srcElement;
			}
			if (target.nodeType == 3)
			{
				target = target.parentNode;
			}

			if (target)
			{
				nodeIndex = target.getAttribute('data-bx-node-ind');
				if (!this.nodeIndex[nodeIndex])
				{
					target = BX.findParent(target, function(node)
					{
						return node == _this.pCont || (node.getAttribute && node.getAttribute('data-bx-node-ind') >= 0);
					}, this.pCont);
					nodeIndex = target.getAttribute('data-bx-node-ind')
				}

				if (this.nodeIndex[nodeIndex])
				{
					var id = 'bx_node_nav_' + Math.round(Math.random() * 1000000000);
					origNode = this.nodeIndex[nodeIndex].node;

					var arItems = [];
					if (origNode.nodeName && origNode.nodeName.toUpperCase() != 'BODY')
					{
						if (!this.nodeIndex[nodeIndex].bxTag || !this.editor.phpParser.surrogateTags[this.nodeIndex[nodeIndex].bxTag])
						{
							arItems.push({
								text : BX.message('NodeSelect'),
								title : BX.message('NodeSelect'),
								className : "",
								onclick: function()
								{
									_this.editor.action.Exec('selectNode', origNode);
									this.popupWindow.close();
									this.popupWindow.destroy();
								}
							});
						}

						arItems.push({
							text : BX.message('NodeRemove'),
							title : BX.message('NodeRemove'),
							className : "",
							onclick: function()
							{
								if (origNode && origNode.parentNode)
								{
									_this.BuildNavi(origNode.parentNode);
									_this.editor.selection.RemoveNode(origNode);
								}
								this.popupWindow.close();
								this.popupWindow.destroy();
							}
						});

						var showProps = !(this.nodeIndex[nodeIndex] && this.nodeIndex[nodeIndex].bxTag && this.items[this.nodeIndex[nodeIndex].bxTag] == false);
						if (showProps)
						{
							arItems.push({
								text : BX.message('NodeProps'),
								title : BX.message('NodeProps'),
								className : "",
								onclick: function()
								{
									_this.ShowNodeProperties(origNode);
									this.popupWindow.close();
									this.popupWindow.destroy();
								}
							});
						}
					}
					else
					{
						arItems = [
							{
								text : BX.message('NodeSelectBody'),
								title : BX.message('NodeSelectBody'),
								className : "",
								onclick: function()
								{
									_this.editor.iframeView.CheckContentLastChild();
									_this.editor.action.Exec('selectNode', origNode);
									_this.editor.Focus();
									this.popupWindow.close();
									this.popupWindow.destroy();
								}
							},
							{
								text : BX.message('NodeRemoveBodyContent'),
								title : BX.message('NodeRemoveBodyContent'),
								className : "",
								onclick: function()
								{
									_this.BuildNavi(origNode);
									_this.editor.On('OnHtmlContentChangedByControl');
									_this.editor.iframeView.Clear();
									_this.editor.util.Refresh(origNode);
									_this.editor.synchro.FullSyncFromIframe();
									_this.editor.Focus();

									this.popupWindow.close();
									this.popupWindow.destroy();
								}
							}
						];
					}


					BX.PopupMenu.show(id + "_menu", target, arItems, {
							overlay: {opacity: 1},
							events: {
								onPopupClose: function()
								{
									//BX.removeClass(this.bindElement, "bxec-add-more-over");
								}
							},
							offsetLeft: 1,
							zIndex: 4000,
							bindOptions: { position: "top" }
						}
					);
				}
			}
		},

		ShowNodeProperties: function(node)
		{
			var bxTag, key;
			if (node.nodeName && node.nodeType == 1)
			{
				bxTag = this.editor.GetBxTag(node);
				key = bxTag.tag ? bxTag.tag : node.nodeName;

				if (this.items[key] && typeof this.items[key] == 'function')
				{
					this.items[key](node, bxTag);
				}
				else
				{
					this.items['DEFAULT'](node, bxTag);
				}
			}
		},

		// TODO: hide it ??
		Disable: function()
		{
			this.BuildNavi(false);
		},

		Enable: function()
		{
		}
	};

	function Overlay(editor, params)
	{
		this.editor = editor;
		this.id = 'bxeditor_overlay' + this.editor.id;
		this.zIndex = params && params.zIndex ? params.zIndex : 3001;
	}

	Overlay.prototype =
	{
		Create: function ()
		{
			this.bCreated = true;
			this.bShown = false;
			var ws = BX.GetWindowScrollSize();
			this.pWnd = document.body.appendChild(BX.create("DIV", {props: {id: this.id, className: "bxhtmled-overlay"}, style: {zIndex: this.zIndex, width: ws.scrollWidth + "px", height: ws.scrollHeight + "px"}}));
			this.pWnd.ondrag = BX.False;
			this.pWnd.onselectstart = BX.False;
		},

		Show: function(arParams)
		{
			if (!this.bCreated)
				this.Create();
			this.bShown = true;
			if (this.shownTimeout)
			{
				this.shownTimeout = clearTimeout(this.shownTimeout);
			}
			var ws = BX.GetWindowScrollSize();
			this.pWnd.style.display = 'block';
			this.pWnd.style.width = ws.scrollWidth + "px";
			this.pWnd.style.height = ws.scrollHeight + "px";

			if (!arParams)
			{
				arParams = {};
			}

			this.pWnd.style.zIndex = arParams.zIndex || this.zIndex;

			BX.bind(window, "resize", BX.proxy(this.Resize, this));
			return this.pWnd;
		},

		Hide: function ()
		{
			if (!this.bShown)
			{
				return;
			}
			var _this = this;
			_this.shownTimeout = setTimeout(function(){_this.bShown = false;}, 300);
			this.pWnd.style.display = 'none';
			BX.unbind(window, "resize", BX.proxy(this.Resize, this));
			this.pWnd.onclick = null;
		},

		Resize: function ()
		{
			if (this.bCreated)
			{
				var ws = BX.GetWindowScrollSize();
				this.pWnd.style.width = ws.scrollWidth + "px";
				this.pWnd.style.height = ws.scrollHeight + "px";
			}
		}
	}

	function Button(editor)
	{
		this.editor = editor;
		this.className = 'bxhtmled-top-bar-btn';
		this.activeClassName = 'bxhtmled-top-bar-btn-active';
		this.disabledClassName = 'bxhtmled-top-bar-btn-disabled';
		this.checkableAction = true;
		this.disabledForTextarea = true;
	}

	Button.prototype = {
		Create: function ()
		{
			this.pCont = BX.create("SPAN", {props: {className: this.className, title: this.title || ''}, html: '<i></i>'});
			BX.bind(this.pCont, "click", BX.delegate(this.OnClick, this));
			BX.bind(this.pCont, "mousedown", BX.delegate(this.OnMouseDown, this));
			BX.bind(this.pCont, "dblclick", function(e){return BX.PreventDefault(e);});

			if (this.action)
			{
				this.pCont.setAttribute('data-bx-type', 'action');
				this.pCont.setAttribute('data-bx-action', this.action);
				if (this.value)
					this.pCont.setAttribute('data-bx-value', this.value);

				if (this.checkableAction)
				{
					this.editor.RegisterCheckableAction(this.action, {
						action: this.action,
						control: this,
						value: this.value
					});
				}
			}
		},

		GetCont: function()
		{
			return this.pCont;
		},

		Check: function (bFlag)
		{
			if(bFlag == this.checked || this.disabled)
				return;

			this.checked = bFlag;
			if(this.checked)
			{
				BX.addClass(this.pCont, this.activeClassName);
			}
			else
			{
				BX.removeClass(this.pCont, this.activeClassName);
			}
		},

		Disable: function(bFlag)
		{
			if(bFlag != this.disabled)
			{
				this.disabled = !!bFlag;
				if(bFlag)
				{
					if (this.action)
					{
						this.pCont.setAttribute('data-bx-type', '');
					}
					BX.addClass(this.pCont, this.disabledClassName);
				}
				else
				{
					if (this.action)
					{
						this.pCont.setAttribute('data-bx-type', 'action');
					}
					BX.removeClass(this.pCont, this.disabledClassName);
				}
			}
		},

		OnClick: BX.DoNothing,
		OnMouseUp: function()
		{
			if(!this.checked)
			{
				BX.removeClass(this.pCont, this.activeClassName);
			}
			BX.unbind(document, 'mouseup', BX.proxy(this.OnMouseUp, this));
			BX.removeCustomEvent(this.editor, "OnIframeMouseUp", BX.proxy(this.OnMouseUp, this));

			if (this.editor.toolbar && this.editor.toolbar.controls && this.editor.toolbar.controls.More)
			{
				this.editor.toolbar.controls.More.Close();
			}
		},

		OnMouseDown: function()
		{
			if (!this.disabled)
			{
				if (this.disabledForTextarea || !this.editor.synchro.IsFocusedOnTextarea())
				{
					this.savedRange = this.editor.selection.SaveBookmark();
				}
				BX.addClass(this.pCont, this.activeClassName);
				BX.bind(document, 'mouseup', BX.proxy(this.OnMouseUp, this));
				BX.addCustomEvent(this.editor, "OnIframeMouseUp", BX.proxy(this.OnMouseUp, this));
			}
		},

		GetValue: function()
		{
			return !!this.checked;
		},

		SetValue: function(value)
		{
			this.Check(value);
		}
	};

	// List
	function DropDown(editor)
	{
		this.editor = editor;
		this.className = 'bxhtmled-top-bar-btn';
		this.activeClassName = 'bxhtmled-top-bar-btn-active';
		this.activeListClassName = 'bxhtmled-top-bar-btn-active';
		this.arValues = [];
		this.checkableAction = true;
		this.disabledForTextarea = true;
		this.posOffset = {top: 6, left: -4};
		this.zIndex = 3005;
	}

	DropDown.prototype = {
		Create: function ()
		{
			this.pCont = BX.create("SPAN", {props: {className: this.className}, html: '<i></i>'});
			this.pValuesCont = BX.create("DIV", {props: {className: "bxhtmled-popup bxhtmled-dropdown-cont"}, html: '<div class="bxhtmled-popup-corner"></div>'});
			if (this.title)
			{
				this.pCont.title = this.title;
			}

			if(this.zIndex)
			{
				this.pValuesCont.style.zIndex = this.zIndex;
			}

			this.valueIndex = {};
			this.pValuesContWrap = this.pValuesCont.appendChild(BX.create("DIV"));
			var but, value, _this = this;
			for (var i = 0; i < this.arValues.length; i++)
			{
				value = this.arValues[i];
				but = this.pValuesContWrap.appendChild(BX.create("SPAN", {props: {title: value.title, className: value.className}, html: '<i></i>'}));
				but.setAttribute('data-bx-dropdown-value', value.id);
				this.valueIndex[value.id] = i;

				if (value.action)
				{
					but.setAttribute('data-bx-type', 'action');
					but.setAttribute('data-bx-action', value.action);
					if (value.value)
					{
						but.setAttribute('data-bx-value', value.value);
					}
				}

				BX.bind(but, 'mousedown', function(e)
				{
					_this.SelectItem(this.getAttribute('data-bx-dropdown-value'));
					_this.editor.CheckCommand(this);
					_this.Close();
				});

				this.arValues[i].listCont = but;
			}

			if (this.action && this.checkableAction)
			{
				this.editor.RegisterCheckableAction(this.action, {
					action: this.action,
					control: this
				});
			}

			BX.bind(this.pCont, 'click', BX.proxy(this.OnClick, this));
			BX.bind(this.pCont, "mousedown", BX.delegate(this.OnMouseDown, this));
		},

		GetCont: function()
		{
			return this.pCont;
		},

		GetPopupBindCont: function()
		{
			return this.pCont;
		},

		Disable: function(bFlag)
		{
			if(bFlag != this.disabled)
			{
				this.disabled = !!bFlag;
				if(bFlag)
				{
					BX.addClass(this.pCont, 'bxhtmled-top-bar-btn-disabled');
				}
				else
				{
					BX.removeClass(this.pCont, 'bxhtmled-top-bar-btn-disabled');
				}
			}
		},

		OnKeyDown: function(e)
		{
			if(e.keyCode == 27)
			{
				this.Close();
			}
		},

		OnClick: function()
		{
			if(!this.disabled)
			{
				if (this.bOpened)
				{
					this.Close();
				}
				else
				{
					this.Open();
				}
			}
		},

		OnMouseUp: function()
		{
			this.editor.selection.RestoreBookmark();
			if(!this.checked)
			{
				BX.removeClass(this.pCont, this.activeClassName);
			}
			BX.unbind(document, 'mouseup', BX.proxy(this.OnMouseUp, this));
			BX.removeCustomEvent(this.editor, "OnIframeMouseUp", BX.proxy(this.OnMouseUp, this));
		},

		OnMouseDown: function()
		{
			if (!this.disabled)
			{
				if (this.disabledForTextarea || !this.editor.synchro.IsFocusedOnTextarea())
				{
					this.savedRange = this.editor.selection.SaveBookmark();
				}

				BX.addClass(this.pCont, this.activeClassName);
				BX.bind(document, 'mouseup', BX.proxy(this.OnMouseUp, this));
				BX.addCustomEvent(this.editor, "OnIframeMouseUp", BX.proxy(this.OnMouseUp, this));
			}
		},

		Close: function ()
		{
			var _this = this;
			this.popupShownTimeout = setTimeout(function(){_this.editor.popupShown = false;}, 300);
			BX.removeClass(this.pCont, this.activeClassName);
			this.pValuesCont.style.display = 'none';
			this.editor.overlay.Hide();

			BX.unbind(window, "keydown", BX.proxy(this.OnKeyDown, this));
			BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));

			BX.onCustomEvent(this, "OnPopupClose");

			this.bOpened = false;
		},

		CheckClose: function(e)
		{
			if (!this.bOpened)
			{
				return BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));
			}

			var pEl;
			if (e.target)
				pEl = e.target;
			else if (e.srcElement)
				pEl = e.srcElement;
			if (pEl.nodeType == 3)
				pEl = pEl.parentNode;

			if (!BX.findParent(pEl, {className: 'bxhtmled-popup'}))
			{
				this.Close();
			}
		},

		Open: function ()
		{
			this.editor.popupShown = true;
			if (this.popupShownTimeout)
			{
				this.popupShownTimeout = clearTimeout(this.popupShownTimeout);
			}
			document.body.appendChild(this.pValuesCont);
			this.pValuesCont.style.display = 'block';
			BX.addClass(this.pCont, this.activeClassName);
			var
				pOverlay = this.editor.overlay.Show({zIndex: this.zIndex - 1}),
				bindCont = this.GetPopupBindCont(),
				pos = BX.pos(bindCont),
				left = Math.round(pos.left - this.pValuesCont.offsetWidth / 2 + bindCont.offsetWidth / 2 + this.posOffset.left),
				top = Math.round(pos.bottom + this.posOffset.top),
				_this = this;

			BX.bind(window, "keydown", BX.proxy(this.OnKeyDown, this));
			pOverlay.onclick = function(){_this.Close()};

			this.pValuesCont.style.top = top + 'px';
			this.pValuesCont.style.left = left + 'px';
			this.bOpened = true;

			setTimeout(function()
			{
				BX.bind(document, 'mousedown', BX.proxy(_this.CheckClose, _this));
			},100);
		},

		SelectItem: function(id, val)
		{
			if (!val)
				val = this.arValues[this.valueIndex[id]];

			if (this.lastActiveItem)
				BX.removeClass(this.lastActiveItem, this.activeListClassName);

			if (val)
			{
				// Select value in list as active
				if (val.listCont)
				{
					this.lastActiveItem = val.listCont;
					BX.addClass(val.listCont, this.activeListClassName);
				}

				this.pCont.className = val.className;
				this.pCont.title = BX.util.htmlspecialchars(val.title || val.name || '');
			}
			else
			{
				this.pCont.className = this.className;
				this.pCont.title = this.title;
			}

			if (this.disabled)
			{
				this.disabled = false;
				this.Disable(true);
			}

			return val;
		},

		SetValue: function()
		{
		},

		GetValue: function()
		{
		}
	};

	function DropDownList(editor)
	{
		// Call parrent constructor
		DropDownList.superclass.constructor.apply(this, arguments);
		this.className = 'bxhtmled-top-bar-select';
		this.itemClassName = 'bxhtmled-dd-list-item';
		this.activeListClassName = 'bxhtmled-dd-list-item-active';
		this.disabledForTextarea = true;
	}
	BX.extend(DropDownList, DropDown);

	DropDownList.prototype.Create = function ()
	{
		this.pCont = BX.create("SPAN", {props: {className: this.className, title: this.title}, attrs: {unselectable: 'on'}, text: ''});
		if (this.width)
			this.pCont.style.width = this.width + 'px';

		this.pValuesCont = BX.create("DIV", {props: {className: "bxhtmled-popup bxhtmled-dropdown-list-cont"}, html: '<div class="bxhtmled-popup-corner"></div>'});
		this.pValuesContWrap = this.pValuesCont.appendChild(BX.create("DIV", {props: {className: "bxhtmled-dd-list-wrap"}}));
		this.valueIndex = {};

		if(this.zIndex)
		{
			this.pValuesCont.style.zIndex = this.zIndex;
		}

		var but, value, _this = this, itemClass, i, html;
		for (i = 0; i < this.arValues.length; i++)
		{
			value = this.arValues[i];
			itemClass = this.itemClassName;
			if (value.className)
				itemClass += ' ' + value.className;

			html = value.tagName ? ('<' + value.tagName + '>' + value.name + '</' + value.tagName + '>') : value.name;
			but = this.pValuesContWrap.appendChild(BX.create("SPAN", {props: {title: value.title || value.name, className: itemClass}, html: html, style: value.style}));

			but.setAttribute('data-bx-dropdown-value', value.id);
			this.valueIndex[value.id] = i;

			if (value.defaultValue)
				this.SelectItem(null, value);

			if (value.action)
			{
				but.setAttribute('data-bx-type', 'action');
				but.setAttribute('data-bx-action', value.action);
				if (value.value)
					but.setAttribute('data-bx-value', value.value);
			}

			BX.bind(but, 'mousedown', function(e)
			{
				if (!e)
					e = window.event;
				_this.SelectItem(this.getAttribute('data-bx-dropdown-value'));
				_this.editor.CheckCommand(this);
			});

			this.arValues[i].listCont = but;
		}

		if (this.action && this.checkableAction)
		{
			this.editor.RegisterCheckableAction(this.action, {
				action: this.action,
				control: this
			});
		}

		BX.bind(this.pCont, 'click', BX.proxy(this.OnClick, this));
	};

	DropDownList.prototype.SelectItem = function (valDropdown, val, bClose)
	{
		bClose = bClose !== false;
		if (!val)
		{
			val = this.arValues[this.valueIndex[valDropdown]];
		}

		if (this.lastActiveItem)
		{
			BX.removeClass(this.lastActiveItem, this.activeListClassName);
		}

		if (val)
		{
			this.pCont.innerHTML = BX.util.htmlspecialchars((val.topName || val.name || val.id));
			this.pCont.title = this.title + ': ' + BX.util.htmlspecialchars(val.title || val.name);


			// Select value in list as active
			if (val.listCont)
			{
				this.lastActiveItem = val.listCont;
				BX.addClass(val.listCont, this.activeListClassName);
			}
		}

		if (this.bOpened && bClose)
		{
			this.Close();
		}
	};

	DropDownList.prototype.SetValue = function(active, state)
	{
	};

	DropDownList.prototype.SetWidth = function(width)
	{
		width = parseInt(width, 10);
		if (width)
		{
			this.width = width;
			this.pCont.style.width = width + 'px';
		}
	};

	DropDownList.prototype.Disable = function(bFlag)
	{
		if(bFlag != this.disabled)
		{
			this.disabled = !!bFlag;
			if(bFlag)
			{
				BX.addClass(this.pCont, 'bxhtmled-top-bar-select-disabled');
			}
			else
			{
				BX.removeClass(this.pCont, 'bxhtmled-top-bar-select-disabled');
			}
		}
	};

	// Combobox with multiple choice of values
	function ComboBox(editor, params)
	{
		this.values = [];
		this.pInput = params.input;
		this.editor = editor;
		this.value = params.value || '';
		this.defaultValue = params.defaultValue || '';
		this.posOffset = {top: 8, left: -4};
		this.zIndex = 3010;
		this.SPLIT_SYMBOL = ',';
		this.itemClassName = 'bxhtmled-dd-list-item';
		this.itemClassNameActive = 'bxhtmled-dd-list-item-active';
	}

	ComboBox.prototype = {
		Init: function()
		{
			BX.bind(this.pInput, 'focus', BX.proxy(this.Focus, this));
			BX.bind(this.pInput, 'click', BX.proxy(this.Focus, this));
			BX.bind(this.pInput, 'blur', BX.proxy(this.Blur, this));
			BX.bind(this.pInput, 'keyup', BX.proxy(this.KeyUp, this));

			this.visibleItemsLength = this.values.length;
			this.currentItem = false;
		},

		UpdateValues: function(values)
		{
			this.bCreated = false;
			this.values = values;
			this.visibleItemsLength = this.values.length;
			this.currentItem = false;
			if (this.bOpened)
			{
				this.ClosePopup();
			}
		},

		Create: function()
		{
			this.pValuesCont = BX.create("DIV", {props: {className: "bxhtmled-popup bxhtmled-combo-cont"}, html: '<div class="bxhtmled-popup-corner"></div>'});
			this.pValuesCont.style.zIndex = this.zIndex;

			if (this.pValuesContWrap)
			{
				BX.cleanNode(this.pValuesContWrap);
				this.pValuesCont.appendChild(this.pValuesContWrap);
			}
			else
			{
				this.pValuesContWrap = this.pValuesCont.appendChild(BX.create("DIV", {props: {className: "bxhtmled-dd-list-wrap"}}));

				BX.bind(this.pValuesContWrap, 'mousedown', function(e)
				{
					var target = e.target || e.srcElement;
					if (!target.getAttribute('data-bx-dropdown-value'))
					{
						target = BX.findParent(target, function(n)
						{
							return n.getAttribute && n.getAttribute('data-bx-dropdown-value');
						}, _this.pValuesContWrap);
					}

					if (target)
					{
						_this.currentItem = parseInt(target.getAttribute('data-bx-dropdown-value'), 10);
						_this.SetValueFromList();
					}

					_this.ClosePopup();
				});
			}
			this.valueIndex = {};

			var but, value, _this = this, itemClass, i, html;
			for (i = 0; i < this.values.length; i++)
			{
				value = this.values[i];
				itemClass = this.itemClassName || '';
				this.values[i].TITLE = this.values[i].TITLE || this.values[i].NAME;

				if (this.values[i].VALUE && this.values[i].VALUE !== this.values[i].TITLE)
				{
					this.values[i].TITLE += ' (' + this.values[i].VALUE + ')';
				}
				else
				{
					this.values[i].VALUE = this.values[i].NAME;
				}

				but = this.pValuesContWrap.appendChild(BX.create("SPAN", {props: {className: itemClass}, html: value.TITLE}));
				but.setAttribute('data-bx-dropdown-value', i);
				this.values[i].cont = but;
			}

			this.bCreated = true;
		},

		KeyUp: function(e)
		{
			var keyCode = e.keyCode;
			if (keyCode == this.editor.KEY_CODES['down'])
			{
				this.SelectItem(1);
			}
			else if (keyCode == this.editor.KEY_CODES['up'])
			{
				this.SelectItem(-1);
			}
			else if (keyCode == this.editor.KEY_CODES['escape'])
			{
				if (this.bOpened)
				{
					this.ClosePopup();
					return BX.PreventDefault(e);
				}
			}
			else if (keyCode == this.editor.KEY_CODES['enter'])
			{
				if (this.bOpened)
				{
					this.SetValueFromList();
					this.ClosePopup();
					return BX.PreventDefault(e);
				}
			}
			else
			{
				this.FilterValue();
			}
		},

		FilterValue: function()
		{
			// Range
			var
				i, val,
				splitedVals = this.GetSplitedValues(),
				caretPos = this.GetCaretPos(this.pInput);

			for (i = 0; i < splitedVals.length; i++)
			{
				val = splitedVals[i];
				if (caretPos >= val.start && caretPos <= val.end)
				{
					break;
				}
			}

			// Filter values && highlight values
			this.FilterAndHighlight(val.value);
		},

		GetSplitedValues: function()
		{
			var
				arVals, i, gStart, gEnd, val,
				res = [],
				str = this.pInput.value;

			if (str.indexOf(this.SPLIT_SYMBOL) === -1 || this.bMultiple === false)
			{
				res.push(
					{
						start: 0,
						end: str.length,
						value: BX.util.trim(str)
					}
				);
			}
			else
			{
				arVals = str.split(this.SPLIT_SYMBOL);
				gStart = 0;
				gEnd = 0;
				for (i = 0; i < arVals.length; i++)
				{
					val = arVals[i];
					gEnd += val.length + i;
					res.push(
						{
							start: gStart,
							end: gEnd,
							value: BX.util.trim(val)
						}
					);
					gStart = gEnd;
				}
			}

			return res;
		},

		FilterAndHighlight: function(needle)
		{
			needle = BX.util.trim(needle);
			var val, i, showPopup = false, pos;

			this.visibleItemsLength = 0;
			for (i = 0; i < this.values.length; i++)
			{
				val = this.values[i];
				if (needle === '')
				{
					showPopup = true;
					val.cont.style.display = '';
					this.visibleItemsLength++;
				}
				else
				{
					pos = val.TITLE.toLowerCase().indexOf(needle.toLowerCase());
					if (pos !== -1 || needle == '')
					{
						val.cont.innerHTML = BX.util.htmlspecialchars(val.TITLE.substr(0, pos)) + '<b>' + BX.util.htmlspecialchars(needle) + '</b>' + BX.util.htmlspecialchars(val.TITLE.substr(pos + needle.length));
						showPopup = true;
						val.cont.style.display = '';
						val.cont.setAttribute('data-bx-dropdown-value', this.visibleItemsLength);
						this.visibleItemsLength++;
					}
					else
					{
						val.cont.innerHTML = BX.util.htmlspecialchars(val.TITLE);
						val.cont.style.display = 'none';
					}
				}
			}

			this.currentItem = false;

			if (showPopup && !this.bOpened)
			{
				this.ShowPopup();
			}
			else if (!showPopup && this.bOpened)
			{
				this.ClosePopup();
			}
		},

		GetCaretPos: function(input)
		{
			var caretPos = 0;

			// IE Support
			if (document.selection)
			{
				BX.focus(input);
				var oSel = document.selection.createRange();
				oSel.moveStart ('character', - input.value.length);
				// The caret position is selection length
				caretPos = oSel.text.length;
			}
			else if (input.selectionStart || input.selectionStart == '0')
			{
				caretPos = input.selectionStart;
			}
			return (caretPos);
		},

		SetValue: function(value)
		{
			this.pInput.value = value;
		},

		SetValueFromList: function()
		{
			var ind = 0, val, i;
			for (i = 0; i < this.values.length; i++)
			{
				val = this.values[i];
				if (val.cont.style.display != 'none')
				{
					if (ind == this.currentItem)
					{
						BX.addClass(val.cont, this.itemClassNameActive);
						break;
					}
					ind++;
				}
			}

			var
				splVal,
				splitedVals = this.GetSplitedValues(),
				caretPos = this.GetCaretPos(this.pInput);

			for (i = 0; i < splitedVals.length; i++)
			{
				splVal = splitedVals[i];
				if (caretPos >= splVal.start && caretPos <= splVal.end)
				{
					break;
				}
			}

			var
				glue = this.SPLIT_SYMBOL == ' ' ? ' ' : this.SPLIT_SYMBOL + ' ',
				curValue = this.pInput.value,
				before = curValue.substr(0, splVal.start),
				after = curValue.substr(splVal.end);

			before = before.replace(/^[\s\r\n\,]+/g, '').replace(/[\s\r\n\,]+$/g, '');
			after = after.replace(/^[\s\r\n\,]+/g, '').replace(/[\s\r\n\,]+$/g, '');

			this.pInput.value = before +
				(before == '' ? '' : glue) +
				val.VALUE +
				(after == '' ? '' : glue) +
				after;

			this.FilterAndHighlight('');
		},

		SelectItem: function(delta)
		{
			var ind, val, i, len;
			if (this.currentItem === false)
			{
				this.currentItem = 0;
			}
			else if(delta !== undefined)
			{
				this.currentItem += delta;

				if (this.currentItem > this.visibleItemsLength - 1)
				{
					this.currentItem = 0;
				}
				else if(this.currentItem < 0)
				{
					this.currentItem = this.visibleItemsLength - 1;
				}
			}

			if (document.querySelectorAll)
			{
				var selected = this.pValuesContWrap.querySelectorAll("." + this.itemClassNameActive);
				if (selected)
				{
					for (i = 0; i < selected.length; i++)
					{
						BX.removeClass(selected[i], this.itemClassNameActive);
					}
				}
			}

			ind = 0;
			len = this.values.length;
			for (i = 0; i < this.values.length; i++)
			{
				val = this.values[i];
				if (val.cont.style.display != 'none')
				{
					if (ind == this.currentItem)
					{
						BX.addClass(val.cont, this.itemClassNameActive);
						break;
					}
					ind++;
				}
			}
		},

		Focus: function(e)
		{
			if (this.values.length > 0 && !this.bFocused)
			{
				BX.focus(this.pInput);
				this.bFocused = true;
				if (this.value == this.defaultValue)
				{
					this.value = '';
				}

				this.ShowPopup();
			}
		},

		Blur: function()
		{
			if (this.values.length > 0 && this.bFocused)
			{
				this.bFocused = false;
				this.ClosePopup();
			}
		},

		ShowPopup: function()
		{
			if (!this.bCreated)
			{
				this.Create();
			}

			this.editor.popupShown = true;
			if (this.popupShownTimeout)
			{
				this.popupShownTimeout = clearTimeout(this.popupShownTimeout);
			}

			document.body.appendChild(this.pValuesCont);
			this.pValuesCont.style.display = 'block';

			var
				i,
				pos = BX.pos(this.pInput),
				left = pos.left + this.posOffset.left,
				top = pos.bottom + this.posOffset.top;

			this.pValuesCont.style.top = top + 'px';
			this.pValuesCont.style.left = left + 'px';
			this.bOpened = true;

			if (document.querySelectorAll)
			{
				var selected = this.pValuesContWrap.querySelectorAll("." + this.itemClassNameActive);
				if (selected)
				{
					for (i = 0; i < selected.length; i++)
					{
						BX.removeClass(selected[i], this.itemClassNameActive);
					}
				}
			}

			BX.onCustomEvent(this, "OnComboPopupOpen");
		},

		ClosePopup: function()
		{
			var _this = this;
			this.popupShownTimeout = setTimeout(function(){_this.editor.popupShown = false;}, 300);
			this.pValuesCont.style.display = 'none';
			this.editor.overlay.Hide();

			this.bOpened = false;
			BX.onCustomEvent(this, "OnComboPopupClose");
		},

		OnChange: function()
		{
		},

		CheckClose: function(e)
		{
			if (!this.bOpened)
			{
				return BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));
			}

			var pEl;
			if (e.target)
				pEl = e.target;
			else if (e.srcElement)
				pEl = e.srcElement;
			if (pEl.nodeType == 3)
				pEl = pEl.parentNode;

			if (!BX.findParent(pEl, {className: 'bxhtmled-popup'}))
				this.Close();
		}
	};

	function ClassSelector(editor, params)
	{
		// Call parrent constructor
		ClassSelector.superclass.constructor.apply(this, arguments);
		this.filterTag = params.filterTag || '';
		this.lastTemplateId = this.editor.GetTemplateId();
		this.values = this.GetClasses();
		this.SPLIT_SYMBOL = ' ';
		this.Init();
	}
	BX.extend(ClassSelector, ComboBox);

	ClassSelector.prototype.OnChange = function()
	{
		if (this.lastTemplateId != this.editor.GetTemplateId())
		{
			this.lastTemplateId = this.editor.GetTemplateId();
			this.values = this.GetClasses();
			this.bCreated = false;
		}
	};

	ClassSelector.prototype.GetClasses = function()
	{
		var
			title,
			classes = this.editor.GetCurrentCssClasses(this.filterTag);

		this.values = [];
		if (classes && classes.length > 0)
		{
			for (var i = 0; i < classes.length; i++)
			{
				title = null;
				if (classes[i].classTitle && typeof classes[i].classTitle == 'object')
				{
					if (classes[i].classTitle.title)
					{
						title = classes[i].classTitle.title;
					}
					else
					{
						continue;
					}
				}
				else if (classes[i].classTitle)
				{
					title = classes[i].classTitle;
				}

				this.values.push(
					{
						VALUE: classes[i].className,
						TITLE:  title,
						NAME: classes[i].className
					}
				);
			}
		}
		return this.values;
	};

	function PasteControl(editor)
	{
		this.editor = editor;
		if (this.editor.config.pasteSetColors !== this.editor.config.pasteSetBorders ||
				this.editor.config.pasteSetColors !== this.editor.config.pasteSetDecor)
		{
			this.mode = 'default';
		}
		else
		{
			this.mode = this.editor.config.pasteSetColors &&
			this.editor.config.pasteSetBorders &&
			this.editor.config.pasteSetDecor ? 'text' : 'rich';
		}

		var _this = this;

		this.items = [];
		if (this.mode == 'default')
		{
			this.items = [
				{
					TEXT: BX.message('BXEdPasteDefault'),
					ACTION: function()
					{
						_this.Hide('default');
					},
					CHECKED: this.mode == 'default',
					_MODE: 'default'
				}
			];
		}

		this.items.push({
			TEXT: BX.message('BXEdPasteText'),
			ACTION: function()
			{
				_this.Hide('text');
			},
			CHECKED: this.mode == 'text',
			_MODE: 'text'
		});
		this.items.push({
			TEXT: BX.message('BXEdPasteFormattedText'),
			ACTION: function()
			{
				_this.Hide('rich');
			},
			CHECKED: this.mode == 'rich',
			_MODE: 'rich'
		});

		// mantis: 71968
		BX.addCustomEvent('OnComponentParamsDisplay', BX.proxy(this.Hide, this));
	}

	PasteControl.prototype = {
		CheckAndShow: function ()
		{
			var
				isOpened = this.isOpened,
				_this = this;

			this.savedRange = this.editor.selection.GetBookmark();
			this.isOpened = true;
			this.lastPreviewMode = false;
			if (this.checkTimeout)
				this.checkTimeout = clearTimeout(this.checkTimeout);

			this.checkTimeout = setTimeout(function()
			{
				var skipPasteHandler = _this.editor.skipPasteHandler;
				_this.editor.skipPasteHandler = true;
				_this.PreviewContent({mode: 'rich', doTimeout: false, skipColors: true});
				var richContent = _this.editor.iframeView.GetValue();
				// Clear images before comparision
				richContent = richContent.replace(/<img((?:\s|\S)*?)>/ig, '');
				richContent = richContent.replace(/id="(\s|\S)*?"/ig, '');
				richContent = richContent.replace(/\s+/ig, ' ');

				_this.PreviewContent({mode: 'text', doTimeout: false});
				var textContent = _this.editor.iframeView.GetValue();
				// Clear images before comparision
				textContent = textContent.replace(/<img((?:\s|\S)*?)>/ig, '');
				textContent = textContent.replace(/id="(\s|\S)*?"/ig, '');
				textContent = textContent.replace(/\s+/ig, ' ');

				if (richContent != textContent)
				{
					_this.isOpened = isOpened;
					_this.editor.SetCursorNode(_this.savedRange);
					_this.Show();
				}

				_this.editor.skipPasteHandler = skipPasteHandler;
			}, 200);
		},

		Show: function ()
		{
			var _this = this;
			this.lastPreviewMode = false;
			this.Hide();

			this.pOverlay = this.editor.overlay.Show();
			BX.bind(this.pOverlay, 'click', BX.proxy(this.Hide, this));
			BX.bind(this.pOverlay, 'mousemove', BX.proxy(function(){this.PreviewContent({mode: 'default'});}, this));

			if (!this.dummyTarget)
			{
				this.dummyTarget = this.editor.dom.iframeCont.appendChild(BX.create('DIV', {props: {className: 'bxhtmled-dummy-target'}}));
			}

			var
				cursorNode = this.editor.GetIframeElement('bx-cursor-node'),
				top = 0, left = 0, node;

			if (cursorNode)
			{
				if (cursorNode.parentNode)
				{
					top += cursorNode.offsetHeight;
					node = cursorNode;
					do
					{
						top += node.offsetTop || 0;
						left += node.offsetLeft || 0;
						node = node.offsetParent;
					} while (node && node.nodeName != 'BODY');
				}
			}

			var scrollPos = BX.GetWindowScrollPos(this.editor.GetIframeDoc());

			top -= scrollPos.scrollTop;
			left -= scrollPos.scrollLeft;

			var
				editorSize = this.editor.GetSceletonSize(),
				maxTop = editorSize.height - this.items.length * 40,
				maxLeft = editorSize.width - 100;

			if (top < 0)
				top = 0;
			else if (top > maxTop)
				top = maxTop;

			if (left < 0)
				left = 0;
			else if (left > maxLeft)
				left = maxLeft;

			this.dummyTarget.style.left = left + 'px';
			this.dummyTarget.style.top = top + 'px';
			this.dummyTarget.style.zIndex = '2002';

			this.OPENER = new BX.COpener({
				DIV: this.dummyTarget, MENU: this.items, TYPE: 'click', ACTIVE_CLASS: 'adm-btn-active', CLOSE_ON_CLICK: true
			});
			this.OPENER.Open();

			var popupDiv = this.OPENER.GetMenu().DIV;
			popupDiv.style.zIndex = '3005';
			BX.addClass(popupDiv, 'bxhtmled-paste-control bx-core-popup-menu-editor');

			BX.addCustomEvent(this.OPENER.GetMenu(), 'onMenuClose', BX.proxy(this.Hide, this));

			var i, items = BX.findChild(popupDiv, {className: 'bx-core-popup-menu-item'}, 1, 1);
			for (i = 0; i < items.length; i++)
			{
				items[i].setAttribute('data-bx-mode', this.items[i]._MODE);
			}

			BX.bind(popupDiv, 'mousemove', function(e)
			{
				var
					target = e.target || e.srcElement,
					mode = (target && target.getAttribute) ? target.getAttribute('data-bx-mode') : null;

				if (!mode)
				{
					target = BX.findParent(target, function(n)
					{
						return n == popupDiv || (n.getAttribute && n.getAttribute('data-bx-mode'));
					}, popupDiv);
					mode = (target && target.getAttribute) ? target.getAttribute('data-bx-mode') : null;
				}

				_this.PreviewContent({mode: mode});
			});

			this.isOpened = true;
			BX.addCustomEvent(this.editor, 'OnIframeKeydown', BX.CMenu.broadcastCloseEvent);
			BX.bind(document.body, "keydown", BX.CMenu.broadcastCloseEvent);
		},

		Hide: function (mode)
		{
			if (this.isOpened)
			{
				this.editor.overlay.Hide();
				if (this.pOverlay)
				{
					BX.unbind(this.pOverlay, 'click', BX.proxy(this.Hide, this));
					BX.unbind(this.pOverlay, 'mousemove', BX.proxy(this.PreviewContent, this));
				}

				BX.removeCustomEvent(this.editor, 'OnIframeKeydown', BX.CMenu.broadcastCloseEvent);
				BX.unbind(document.body, "keydown", BX.CMenu.broadcastCloseEvent);

				if (!mode || typeof mode !== 'string' || (mode !== 'text' && mode !== 'rich'))
					mode = 'default';
				this.PreviewContent({mode: mode});
				this.editor.Focus();

				this.editor.On("OnIframePaste");
				this.editor.On("OnIframeNewWord");

				this.isOpened = false;
			}
		},

		PreviewContent: function(params)
		{
			if (this.isOpened)
			{
				if (this.lastPreviewMode != params.mode || !this.lastPreviewMode)
				{
					if (params.doTimeout !== false)
					{
						params.doTimeout = false;
						var _this = this;
						if (this.previewTimeout)
							clearTimeout(this.previewTimeout);
						this.previewTimeout = setTimeout(function(){_this.PreviewContent(params);}, 200);
						return;
					}

					if (params.mode == 'rich')
					{
						this.editor.config.pasteSetColors = !!params.skipColors;
						this.editor.config.pasteSetBorders = false;
						this.editor.config.pasteSetDecor = false;
					}
					else if (params.mode == 'text')
					{
						this.editor.config.pasteSetColors = true;
						this.editor.config.pasteSetBorders = true;
						this.editor.config.pasteSetDecor = true;
					}
					else // rich
					{
						this.editor.config.pasteSetColors = this.defPasteSetColors;
						this.editor.config.pasteSetBorders = this.defPasteSetBorders;
						this.editor.config.pasteSetDecor = this.defPasteSetDecor;
					}

					this.editor.pasteHandleMode = true;
					this.editor.bbParseContentMode = true;
					this.editor.synchro.lastIframeValue = false;

					this.editor.iframeView.SetValue(this.pastedContent, false);
					this.editor.synchro.FromIframeToTextarea(true, true);

					this.editor.pasteHandleMode = false;
					this.editor.bbParseContentMode = false;

					this.editor.synchro.lastTextareaValue = false;
					this.editor.synchro.FromTextareaToIframe(true);

					this.editor.RestoreCursor();

					this.editor.skipPasteHandler = false;

					// Restore settings
					if (params.mode != 'default')
					{
						this.editor.config.pasteSetColors = this.defPasteSetColors;
						this.editor.config.pasteSetBorders = this.defPasteSetBorders;
						this.editor.config.pasteSetDecor = this.defPasteSetDecor;
					}
				}
				this.lastPreviewMode = params.mode;
			}
		},

		SaveIframeContent: function(content)
		{
			// Save default values
			this.defPasteSetColors = this.editor.config.pasteSetColors;
			this.defPasteSetBorders = this.editor.config.pasteSetBorders;
			this.defPasteSetDecor = this.editor.config.pasteSetDecor;

			this.pastedContent = content;
		}
	};

	function __run()
	{
		window.BXHtmlEditor.TaskbarManager = TaskbarManager;
		window.BXHtmlEditor.Taskbar = Taskbar;
		window.BXHtmlEditor.ComponentsControl = ComponentsControl;
		window.BXHtmlEditor.ContextMenu = ContextMenu;
		window.BXHtmlEditor.Dialog = Dialog;
		window.BXHtmlEditor.Toolbar = Toolbar;
		window.BXHtmlEditor.NodeNavigator = NodeNavi;
		window.BXHtmlEditor.Button = Button;
		window.BXHtmlEditor.DropDown = DropDown;
		window.BXHtmlEditor.DropDownList = DropDownList;
		window.BXHtmlEditor.ComboBox = ComboBox;
		window.BXHtmlEditor.ClassSelector = ClassSelector;
		window.BXHtmlEditor.Overlay = Overlay;
		window.BXHtmlEditor.PasteControl = PasteControl;

		BX.onCustomEvent(window.BXHtmlEditor, 'OnEditorBaseControlsDefined');
	}

	if (window.BXHtmlEditor)
	{
		__run();
	}
	else
	{
		BX.addCustomEvent(window, "OnBXHtmlEditorInit", __run);
	}
})();
