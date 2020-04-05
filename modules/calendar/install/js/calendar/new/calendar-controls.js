;(function(window) {
	function ViewSwitcher(params)
	{
		this.calendar = params.calendar;
		this.wrap = params.wrap;
		this.popupId = this.calendar.id + '_view_switcher';
		if (params.dropDownMode)
		{
			this.createDropDown();
		}
		else
		{
			this.createSelector();
		}

		BX.addCustomEvent(this.calendar, "afterSetView", BX.proxy(this.onAfterSetView, this));
	}

	ViewSwitcher.prototype = {
		createSelector:function ()
		{
			var
				wrap = this.wrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-view-switcher-list'},
					events: {
						click: BX.delegate(function(e)
						{
							var target = e.target || e.srcElement;
							if (target && target.getAttribute)
							{
								var viewName = target.getAttribute('data-bx-calendar-view');
								this.calendar.setView(viewName, {animation: true});
							}
						}, this)
					}
				}));

			this.calendar.views.forEach(function(view)
			{
				view.switchNode = wrap.appendChild(
					BX.create('SPAN', {
						props: {className: 'calendar-view-switcher-list-item' + (this.calendar.currentViewName == view.name ? ' calendar-view-switcher-list-item-active' : '')},
						attrs: {'data-bx-calendar-view': view.name},
						text: view.title || view.name
					})
				);
			}, this);
		},

		createDropDown: function ()
		{
			this.selectorText = BX.create("div", {props: {className:"calendar-view-switcher-text"}});
			this.selectorTextInner = this.selectorText.appendChild(BX.create("div", {props: {className: "calendar-view-switcher-text-inner"}}));

			BX.adjust(this.wrap, {
				children: [
					this.selectorText,
					BX.create("div", {props: {className:"calendar-view-switcher-dropdown"}})
				],
				events: {click: BX.proxy(this.showPopup, this)}
			});

			if (BX.type.isArray(this.calendar.util.config.additionalViewModes))
			{
				this.viewModeTextInner = this.selectorText.appendChild(BX.create("div", {props: {className: "calendar-view-switcher-text-mode-inner"}}));
			}

			this.getMenuItems();
		},

		getMenuItems: function()
		{
			this.menuItems = [];
			this.calendar.views.forEach(function(view)
			{
				this.menuItems.push({
					text: view.title || view.name,
					className: this.calendar.currentViewName == view.name ? 'menu-popup-item-accept' : ' ',
					onclick: BX.delegate(function(){
						this.calendar.setView(view.name, {animation: true});
						this.menuPopup.close();
					}, this)
				});

				if (this.calendar.currentViewName == view.name)
				{
					this.selectorTextInner.innerHTML = view.title || view.name;
				}
			}, this);

			if (BX.type.isArray(this.calendar.util.config.additionalViewModes))
			{
				var i, mode;

				this.menuItems.push({
					text: '<span>' + BX.message('EC_VIEW_MODE_SHOW_BY') + '</span>',
					className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label'
				});

				for (i = 0; i < this.calendar.util.config.additionalViewModes.length; i++)
				{
					mode = this.calendar.util.config.additionalViewModes[i];
					this.menuItems.push({
						dataset: mode,
						text: BX.util.htmlspecialchars(mode.label),
						className: mode.selected ? 'menu-popup-item-accept' : ' ',
						onclick: BX.delegate(function(e, item){
							this.calendar.triggerEvent('changeViewMode', item.dataset);
							this.viewModeTextInner.innerHTML = '(' + BX.util.htmlspecialchars(item.dataset.label) + ')';
							for (j = 0; j < this.calendar.util.config.additionalViewModes.length; j++)
							{
								this.calendar.util.config.additionalViewModes[j].selected = item.dataset.id == this.calendar.util.config.additionalViewModes[j].id;
							}
							this.menuPopup.close();
						}, this)
					});

					if (mode.selected)
					{
						this.viewModeTextInner.innerHTML = '(' + BX.util.htmlspecialchars(mode.label) + ')';
					}
				}
			}
			this.calendar.triggerEvent('beforeViewModePopupOpened', this.menuItems);

			return this.menuItems;
		},

		showPopup: function ()
		{
			if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
			{
				return this.menuPopup.close();
			}

			this.getMenuItems();

			this.menuPopup = BX.PopupMenu.create(
				this.popupId,
				this.selectorText,
				this.menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: -3,
					offsetLeft: this.selectorText.offsetWidth - 6,
					angle: true
				}
			);

			this.menuPopup.show();

			BX.addCustomEvent(this.menuPopup.popupWindow, 'onPopupClose', BX.delegate(function()
			{
				BX.PopupMenu.destroy(this.popupId);
			}, this));
		},

		onAfterSetView: function()
		{
			var
				newView = this.calendar.getView(),
				i, nodes = this.wrap.querySelectorAll('.calendar-view-switcher-list-item-active');

			for (i = 0; i < nodes.length; i++)
			{
				BX.removeClass(nodes[i], 'calendar-view-switcher-list-item-active');
			}

			if (newView)
			{
				if (newView.switchNode)
				{
					BX.addClass(this.calendar.getView().switchNode, 'calendar-view-switcher-list-item-active');
				}

				if (this.selectorTextInner)
				{
					this.selectorTextInner.innerHTML = newView.title || newView.name;
				}
			}
		}
	};


	function AddButton(params)
	{
		this.calendar = params.calendar;
		this.wrap = params.wrap;
		this.id = this.calendar.id + '_top_add_button';
		this.create();
	}

	AddButton.prototype = {
		create: function ()
		{
			this.button = this.wrap.appendChild(BX.create("SPAN", {
				props: {className: "webform-small-button webform-small-button-blue"},
				html: BX.message('EC_ADD'),
				events: {click: BX.proxy(this.addEntry, this)}
			}));

			this.menuItems = [
				{
					text: BX.message('EC_ADD_EVENT'),
					onclick: BX.proxy(this.addEntry, this)
				}
			];

			if (this.calendar.showTasks)
			{
				this.menuItems.push({
					text: BX.message('EC_ADD_TASK'),
					onclick: BX.proxy(this.addTask, this)
				});
			}

			if (this.menuItems.length > 1)
			{
				BX.addClass(this.wrap, 'webform-small-button-separate-wrap');
				this.addButtonMore = this.wrap.appendChild(BX.create("SPAN", {
					props: {className: "webform-small-button-right-part"},
					events: {click: BX.proxy(this.showPopup, this)}
				}));
			}
		},

		showPopup: function ()
		{
			if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
			{
				return this.menuPopup.close();
			}

			this.menuPopup = BX.PopupMenu.create(
				this.id,
				this.addButtonMore,
				this.menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: 0,
					offsetLeft: 15,
					angle: true
				}
			);

			this.menuPopup.show();

			BX.addCustomEvent(this.menuPopup.popupWindow, 'onPopupClose', BX.delegate(function()
			{
				BX.PopupMenu.destroy(this.addButtonMorePopupId);
				BX.PopupMenu.destroy(this.id);
			}, this));
		},

		addEntry: function()
		{
			if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
			{
				this.menuPopup.close();
			}

			if (!this.calendar.editSlider)
			{
				this.calendar.editSlider = new window.BXEventCalendar.EditEntrySlider(this.calendar);
			}
			this.calendar.editSlider.show({});
		},

		addTask: function()
		{
			if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
			{
				this.menuPopup.close();
			}

			BX.SidePanel.Instance.open(this.calendar.util.getEditTaskPath(), {loader: "task-new-loader"});
		}
	};


	function SelectInput(params)
	{
		this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);
		this.values = params.values || false;
		this.input = params.input;
		this.defaultValue = params.defaultValue || '';
		this.openTitle = params.openTitle || '';
		this.className = params.className || '';
		this.currentValue = params.value;
		this.currentValueIndex = params.valueIndex;
		this.onChangeCallback = params.onChangeCallback || null;
		this.zIndex = params.zIndex || 1200;
		this.disabled = params.disabled;
		if (this.onChangeCallback)
		{
			BX.bind(this.input, 'change', this.onChangeCallback);
			BX.bind(this.input, 'keyup', this.onChangeCallback);
		}

		if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex])
		{
			this.input.value = this.values[this.currentValueIndex].label;
		}

		this.curInd = false;

		if (this.values)
		{
			BX.bind(this.input, 'click', BX.proxy(this.onClick, this));
			BX.bind(this.input, 'focus', BX.proxy(this.onFocus, this));
			BX.bind(this.input, 'blur', BX.proxy(this.onBlur, this));
			BX.bind(this.input, 'keyup', BX.proxy(this.onKeyup, this));
		}
	}

	SelectInput.prototype = {
		showPopup: function()
		{
			if (this.shown || this.disabled)
				return;

			var
				ind = 0,
				j = 0,
				menuItems = [],
				i, _this = this;

			for (i = 0; i < this.values.length; i++)
			{
				if (this.values[i].delimiter)
				{
					menuItems.push(this.values[i]);
				}
				else
				{
					if (this.currentValue && this.values[i] && this.values[i].value == this.currentValue.value)
					{
						ind = j;
					}

					menuItems.push({
						id: this.values[i].value,
						text: this.values[i].label,
						onclick: this.values[i].callback || (function (value, label)
						{
							return function ()
							{
								_this.input.value = label;
								_this.popupMenu.close();
								_this.onChange();
							}
						})(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
					});
					j++;
				}
			}

			this.popupMenu = BX.PopupMenu.create(
				this.id,
				this.input,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: 0,
					offsetLeft: 0
				}
			);
			this.popupMenu.popupWindow.setWidth(this.input.offsetWidth - 2);

			var menuContainer = this.popupMenu.layout.menuContainer;
			BX.addClass(this.popupMenu.layout.menuContainer, 'calendar-select-popup');
			this.popupMenu.show();

			var menuItem = this.popupMenu.menuItems[ind];
			if (menuItem && menuItem.layout)
			{
				menuContainer.scrollTop = menuItem.layout.item.offsetTop - menuItem.layout.item.offsetHeight;
			}

			BX.addCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', function()
			{
				BX.PopupMenu.destroy(_this.id);
				_this.shown = false;
			});

			this.input.select();

			this.shown = true;
		},

		closePopup: function()
		{
			BX.PopupMenu.destroy(this.id);
			this.shown = false;
		},

		onFocus: function()
		{
			setTimeout(BX.delegate(function(){
				if (!this.shown)
				{
					this.showPopup();
				}
			}, this), 200);
		},

		onClick: function()
		{
			if (this.shown)
			{
				this.closePopup();
			}
			else
			{
				this.showPopup();
			}
		},

		onBlur: function()
		{
			setTimeout(BX.delegate(this.closePopup, this), 200);
		},

		onKeyup: function()
		{
			setTimeout(BX.delegate(this.closePopup, this), 50);
		},

		onChange: function()
		{
			var val = this.input.value;
			BX.onCustomEvent(this, 'onSelectInputChanged', [this, val]);
			if (this.onChangeCallback && typeof this.onChangeCallback == 'function')
				this.onChangeCallback({value: val});
		},

		destroy: function()
		{
			if (this.onChangeCallback)
			{
				BX.unbind(this.input, 'change', this.onChangeCallback);
				BX.unbind(this.input, 'keyup', this.onChangeCallback);
			}

			BX.unbind(this.input, 'click', BX.proxy(this.onClick, this));
			BX.unbind(this.input, 'focus', BX.proxy(this.onFocus, this));
			BX.unbind(this.input, 'blur', BX.proxy(this.onBlur, this));
			BX.unbind(this.input, 'keyup', BX.proxy(this.onKeyup, this));

			if (this.popupMenu)
				this.popupMenu.close();
			BX.PopupMenu.destroy(this.id);
			this.shown = false;
		}
	};

	function Reminder(params)
	{
		this.controlList = {};
		this.selectedValues = [];
		this.values = params.values;
		this.addButton = params.addButtonNode;
		this.valuesWrap = params.valuesContainerNode;
		this.changeCallack = params.changeCallack;
		this.showPopupCallBack = params.showPopupCallBack;
		this.hidePopupCallBack = params.hidePopupCallBack;
		this.id = params.id || 'reminder-' + Math.round(Math.random() * 1000000);
		this.zIndex = params.zIndex || 3200;

		BX.unbind(this.addButton, 'click', BX.proxy(this.showPopup, this));
		BX.bind(this.addButton, 'click', BX.proxy(this.showPopup, this));

		if (params.selectedValues && params.selectedValues.length > 0)
		{
			for (var i = 0; i < params.selectedValues.length; i++)
			{
				this.addValue(params.selectedValues[i]);
			}
		}
	}

	Reminder.prototype = {
		showPopup: function()
		{
			var
				_this = this,
				i, menuItems = [];

			for (i = 0; i < this.values.length; i++)
			{
				if (!BX.util.in_array(this.values[i].value, this.selectedValues))
				{
					menuItems.push({
						text: this.values[i].label, onclick: (function (value)
						{
							return function ()
							{
								_this.addValue(value);
								_this.reminderMenu.close();
							}
						})(this.values[i].value)
					});
				}
			}

			this.reminderMenu = BX.PopupMenu.create(
				this.id,
				this.addButton,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: 0,
					offsetLeft: 9,
					angle: true
				}
			);

			this.reminderMenu.show();
			if (this.showPopupCallBack)
				this.showPopupCallBack();

			BX.addCustomEvent(_this.reminderMenu.popupWindow, 'onPopupClose', function()
			{
				if (_this.hidePopupCallBack)
					_this.hidePopupCallBack();
				BX.PopupMenu.destroy(_this.id);
			});
		},

		addValue: function(value)
		{
			var i, item, closeIcon, _this = this;
			for (i = 0; i < this.values.length; i++)
			{
				if (this.values[i].value == value && value >= 0 && !BX.util.in_array(value, this.selectedValues))
				{
					if (!this.selectedValues.length)
						BX.cleanNode(this.valuesWrap);

					item = this.valuesWrap.appendChild(BX.create('SPAN', {props: {className: 'calendar-reminder-item'}, text: this.values[i].shortLabel || this.values[i].label}));
					closeIcon = item.appendChild(BX.create('SPAN', {props: {className: 'calendar-reminder-clear-icon'}, events: {click: function(){_this.removeValue(value);}}}));
					this.selectedValues.push(value);
					this.controlList[value] = item;
					break;
				}
			}

			if (this.selectedValues.length == this.values.length)
			{
				this.addButton.style.display = 'none';
			}

			if (this.changeCallack)
				this.changeCallack(this.selectedValues);
		},

		removeValue: function(value)
		{
			if (this.controlList[value] && BX.isNodeInDom(this.controlList[value]))
			{
				BX.cleanNode(this.controlList[value], true);
			}
			this.selectedValues = BX.util.deleteFromArray(this.selectedValues, BX.util.array_search(value, this.selectedValues));

			if (this.selectedValues.length < this.values.length)
			{
				this.addButton.style.display = '';
			}

			if (!this.selectedValues.length)
			{
				this.valuesWrap.appendChild(BX.create('SPAN', {props: {className: ''}, text: ' ' + BX.message('EC_REMIND_NO')}));
			}
			if (this.changeCallack)
				this.changeCallack(this.selectedValues);
		}
	};


	function DestinationSelector(id, params)
	{
		this.params = params;
		this.id = id;
		this.calendar = params.calendar;
		this.zIndex = params.zIndex || 3100;
		this.wrapNode = params.wrapNode;
		this.destinationInputName = params.inputName || 'EVENT_DESTINATION';

		if (this.params.itemsSelected && this.params.itemsSelected.length)
		{
			this.params.itemsSelected = this.convertAttendeesCodes(this.params.itemsSelected);
		}

		this.create();
	}

	DestinationSelector.prototype = {
		create: function()
		{
			var id = this.id;

			this.socnetDestinationWrap = this.wrapNode.appendChild(BX.create('DIV', {
				props: {className: 'event-grid-dest-wrap'},
				events: {
					click : function(e)
					{
						BX.SocNetLogDestination.openDialog(id);
						BX.PreventDefault(e);
					}
				}
			}));

			this.socnetDestinationItems = this.socnetDestinationWrap.appendChild(BX.create('SPAN', {
				props: {className: ''},
				events: {
					click : function(e)
					{
						var targ = e.target || e.srcElement;
						if (targ.className == 'feed-event-del-but') // Delete button
						{
							BX.SocNetLogDestination.deleteItem(targ.getAttribute('data-item-id'), targ.getAttribute('data-item-type'), id);
							e.preventDefault();
							e.stopPropagation();
						}
					},
					mouseover: function(e)
					{
						var targ = e.target || e.srcElement;
						if (targ.className == 'feed-event-del-but') // Delete button
							BX.addClass(targ.parentNode, 'event-grid-dest-hover');
					},
					mouseout: function(e)
					{
						var targ = e.target || e.srcElement;
						if (targ.className == 'feed-event-del-but') // Delete button
							BX.removeClass(targ.parentNode, 'event-grid-dest-hover');
					}
				}
			}));

			this.socnetDestinationInputWrap = this.socnetDestinationWrap.appendChild(BX.create('SPAN', {props: {className: 'feed-add-destination-input-box'}}));
			this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(
				BX.create('INPUT', {
					props: {id: id + '-inp', className: 'feed-add-destination-inp'},
					attrs: {value: '', type: 'text'},
					events: {
						keydown : function(e){
							return BX.SocNetLogDestination.searchBeforeHandler(e, {
								formName: id,
								inputId: id + '-inp'
							});
						},
						keyup : function(e){
							return BX.SocNetLogDestination.searchHandler(e, {
								formName: id,
								inputId: id + '-inp',
								linkId: 'event-grid-dest-add-link',
								sendAjax: true
							});
						}
					}
				})
			);
			this.socnetDestinationLink = this.socnetDestinationWrap.appendChild(BX.create('SPAN', {
				html: this.params.addLinkMessage || BX.message('EC_DESTINATION_ADD_USERS'),
				props: {id: id + '-link', className: 'feed-add-destination-link'},
				events: {
					keydown : function(e){
						return BX.SocNetLogDestination.searchBeforeHandler(e, {
							formName: id,
							inputId: id + '-inp'
						});
					},
					keyup : function(e){
						return BX.SocNetLogDestination.searchHandler(e, {
							formName: id,
							inputId: id + '-inp',
							linkId: 'event-grid-dest-add-link',
							sendAjax: true
						});
					}
				}
			}));

			this.params.items = this.calendar.util.getSocnetDestinationConfig('items');
			this.params.itemsLast = this.calendar.util.getSocnetDestinationConfig('itemsLast');

			if (this.params.itemsSelected && !this.checkItemsSelected(
					this.params.items,
					this.params.itemsLast,
					this.params.itemsSelected,
					BX.proxy(this.init, this)
				))
			{
				return;
			}

			this.init();
		},

		init: function()
		{
			if (!this.socnetDestinationInput || !this.socnetDestinationWrap)
				return;

			var _this = this;

			if(this.params.selectGroups === false)
			{
				this.params.items.groups = {};
				this.params.items.department = {};
				this.params.items.sonetgroups = {};
			}

			if(this.params.selectUsers === false)
			{
				this.params.items.users = {};
				this.params.items.groups = {};
				this.params.items.department = {};
			}

			BX.SocNetLogDestination.init({
				name : this.id,
				searchInput : this.socnetDestinationInput,
				extranetUser :  false,
				userSearchArea: 'I',
				bindMainPopup : {
					node : this.socnetDestinationWrap,
					offsetTop : '5px',
					offsetLeft: '15px'
				},
				bindSearchPopup : {
					node : this.socnetDestinationWrap,
					offsetTop : '5px',
					offsetLeft: '15px'
				},
				callback : {
					select : BX.proxy(this.selectCallback, this),
					unSelect : BX.proxy(this.unSelectCallback, this),
					openDialog : BX.proxy(this.openDialogCallback, this),
					closeDialog : BX.proxy(this.closeDialogCallback, this),
					openSearch : BX.proxy(this.openDialogCallback, this),
					closeSearch : function(){_this.closeDialogCallback(true);}
				},
				items : this.params.items,
				itemsLast : this.params.itemsLast,
				itemsSelected : this.params.itemsSelected,
				departmentSelectDisable: this.params.selectGroups === false
			});
		},

		checkItemsSelected: function (items, itemsLast, selected, callback)
		{
			var codes = [];
			for (var code in selected)
			{
				if (selected.hasOwnProperty(code))
				{
					if (selected[code] == 'users' && !items.users[code])
					{
						codes.push(code);
					}
				}
			}

			if (codes.length > 0)
			{
				var loader = this.socnetDestinationWrap.appendChild(BX.adjust(this.calendar.util.getLoader(40), {style: {height: '50px'}}));

				this.calendar.request({
					type: 'get',
					data: {
						action: 'get_destination_items',
						codes: codes
					},
					handler: BX.delegate(function(response)
					{
						if (loader)
							BX.remove(loader);

						this.calendar.util.mergeSocnetDestinationConfig(response.destinationItems);
						this.params.items = this.calendar.util.getSocnetDestinationConfig('items');
						this.params.itemsLast = this.calendar.util.getSocnetDestinationConfig('itemsLast');

						if (callback && typeof callback == 'function')
							callback();
					}, this)
				});
				return false;
			}

			return true;
		},

		closeAll: function ()
		{
			if (BX.SocNetLogDestination.isOpenDialog())
			{
				BX.SocNetLogDestination.closeDialog();
			}
			BX.SocNetLogDestination.closeSearch();
		},

		selectCallback: function(item, type)
		{
			var
				type1 = type,
				prefix = 'S';

			if (type == 'sonetgroups')
			{
				prefix = 'SG';
			}
			else if (type == 'groups')
			{
				prefix = 'UA';
				type1 = 'all-users';
			}
			else if (type == 'users')
			{
				prefix = 'U';
			}
			else if (type == 'department')
			{
				prefix = 'DR';
			}

			this.socnetDestinationItems.appendChild(
				BX.create("span", { attrs : {'data-id' : item.id }, props : {className : "event-grid-dest event-grid-dest-" + type1 }, children: [
					BX.create("input", { attrs : {type : 'hidden', name : this.destinationInputName + '[' + prefix + '][]', value : item.id }}),
					BX.create("span", { props : {className : "event-grid-dest-text" }, html : item.name}),
					BX.create("span", { props : {className : "feed-event-del-but"}, attrs: {'data-item-id': item.id, 'data-item-type': type}})
				]})
			);

			BX.onCustomEvent('OnDestinationAddNewItem', [item]);
			this.socnetDestinationInput.value = '';
			this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
		},

		unSelectCallback: function(item, type, search)
		{
			var elements = BX.findChildren(this.socnetDestinationItems, {attribute: {'data-id': item.id}}, true);
			if (elements != null)
			{
				for (var j = 0; j < elements.length; j++)
				{
					BX.remove(elements[j]);
				}
			}

			BX.onCustomEvent('OnDestinationUnselect');
			this.socnetDestinationInput.value = '';
			this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
		},

		openDialogCallback: function ()
		{
			if (BX.SocNetLogDestination.popupWindow)
			{
				// Fix zIndex for slider issues
				BX.SocNetLogDestination.popupWindow.params.zIndex = this.zIndex;
				BX.SocNetLogDestination.popupWindow.popupContainer.style.zIndex = this.zIndex;
			}

			if (BX.SocNetLogDestination.popupSearchWindow)
			{
				// Fix zIndex for slider issues
				BX.SocNetLogDestination.popupSearchWindow.params.zIndex = this.zIndex;
				BX.SocNetLogDestination.popupSearchWindow.popupContainer.style.zIndex = this.zIndex;
			}

			BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
			BX.style(this.socnetDestinationLink, 'display', 'none');
			BX.focus(this.socnetDestinationInput);
		},

		closeDialogCallback: function(cleanInputValue)
		{
			if (!BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0)
			{
				BX.style(this.socnetDestinationInputWrap, 'display', 'none');
				BX.style(this.socnetDestinationLink, 'display', 'inline-block');
				if (cleanInputValue === true)
					this.socnetDestinationInput.value = '';

				// Disable backspace
				if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
					BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);

				BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(e)
				{
					if (e.keyCode == 8)
					{
						e.preventDefault();
						return false;
					}
				});

				setTimeout(function()
				{
					BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
					BX.SocNetLogDestination.backspaceDisable = null;
				}, 5000);
			}
		},

		getCodes: function()
		{
			var
				inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
				codes = [], i;

			for (i = 0; i < inputsList.length; i++)
			{
				codes.push(inputsList[i].value);
			}
			return codes;
		},

		getAttendeesCodes: function()
		{
			var
				inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
				values = [],
				i, code;

			for (i = 0; i < inputsList.length; i++)
			{
				values.push(inputsList[i].value);
			}

			return this.convertAttendeesCodes(values);
		},

		convertAttendeesCodes: function(values)
		{
			var attendeesCodes = {};

			if (BX.type.isArray(values))
			{
				values.forEach(function(code){
					if (code.substr(0, 2) == 'DR')
					{
						attendeesCodes[code] = "department";
					}
					else if (code.substr(0, 2) == 'UA')
					{
						attendeesCodes[code] = "groups";
					}
					else if (code.substr(0, 2) == 'SG')
					{
						attendeesCodes[code] = "sonetgroups";
					}
					else if (code.substr(0, 1) == 'U')
					{
						attendeesCodes[code] = "users";
					}
				});
			}

			return attendeesCodes;
		},

		getAttendeesCodesList: function(codes)
		{
			var result = [];
			if (!codes)
				codes = this.getAttendeesCodes();
			for (var i in codes)
			{
				if (codes.hasOwnProperty(i))
				{
					result.push(i);
				}
			}
			return result;
		}
	};

	function LocationSelector(id, params, calendar)
	{
		this.params = params;
		this.id = id;
		this.zIndex = params.zIndex || 3100;
		this.DOM = {wrapNode: params.wrapNode};
		this.calendar = calendar;
		this.disabled = !this.calendar.util.isRichLocationEnabled();
		this.value = {type: '', text: '', value: ''};

		if (params.value && typeof params.value === 'object')
		{
			this.value.text = params.value.text || '';
			this.value.type = params.value.type || '';
			this.value.value = params.value.value || '';
		}
		else if(params.value && params.value !== '')
		{
			this.value = this.calendar.util.parseLocation(params.value);
		}

		this.create();
	}

	LocationSelector.prototype = {
		create: function()
		{
			this.DOM.inputWrap = this.DOM.wrapNode.appendChild(BX.create('DIV', {
				props: {
					className: 'calendar-field-block'
				}
			}));

			if (this.disabled)
			{
				BX.addClass(this.DOM.wrapNode, 'locked');
				this.DOM.inputWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-lock-icon'},
					events: {
						click: function(){
							B24.licenseInfoPopup.show('calendar_location', BX.message('EC_B24_LOCATION_LIMITATION_TITLE'), BX.message('EC_B24_LOCATION_LIMITATION'));
						}
					}
				}))
			}

			this.DOM.input = this.DOM.inputWrap.appendChild(BX.create('INPUT', {
				attrs: {
					name: this.params.inputName || '',
					placeholder: BX.message('EC_LOCATION_LABEL'),
					type: 'text'
				},
				props: {
					className: 'calendar-field calendar-field-select'
				}
			}));

			this.setValues();
		},

		setValues: function()
		{
			if (this.selectContol)
			{
				this.selectContol.destroy();
			}

			var
				menuItemList = [],
				meetingRooms = this.calendar.util.getMeetingRoomList(),
				locationList = this.calendar.util.getLocationList(),
				selectedIndex = false;

			// Old meeting rooms
			if (meetingRooms && meetingRooms.length)
			{
				for (i = 0; i < meetingRooms.length; i++)
				{
					menuItemList.push({
						ID: parseInt(meetingRooms[i].ID),
						label: BX.util.htmlspecialchars(meetingRooms[i].NAME),
						value: meetingRooms[i].ID,
						type: 'mr'
					});


					if (this.value.type == 'mr' && this.value.value == meetingRooms[i].ID)
					{
						selectedIndex = menuItemList.length - 1;
					}
				}
				menuItemList.push({delimiter: true});
			}

			if (!locationList || !locationList.length)
			{
				menuItemList.push({
					label: BX.message('EC_ADD_LOCATION'),
					callback: BX.delegate(this.editMeetingRooms, this)
				});
			}
			else
			{
				var i;
				for (i = 0; i < locationList.length; i++)
				{
					menuItemList.push({
						ID: parseInt(locationList[i].ID),
						label: BX.util.htmlspecialchars(locationList[i].NAME),
						labelRaw: locationList[i].NAME,
						value: parseInt(locationList[i].ID),
						type: 'calendar'
					});
					if (this.value.type == 'calendar' && this.value.value == locationList[i].ID)
					{
						selectedIndex = menuItemList.length - 1;
					}
				}

				menuItemList.push({delimiter: true});
				menuItemList.push({
					label: BX.message('EC_LOCATION_MEETING_ROOM_SET'),
					callback: BX.delegate(this.editMeetingRooms, this)
				});
			}

			if (this.value)
			{
				this.DOM.input.value = this.value.str || '';
				if (this.value.type && this.value.str == this.calendar.util.getTextLocation(this.value))
				{
					this.DOM.input.value = BX.message('EC_LOCATION_404');
				}
			}

			if (this.selectContol)
			{
				this.selectContol.destroy();
			}

			this.selectContol = new SelectInput({
				input: this.DOM.input,
				values: menuItemList,
				valueIndex: selectedIndex,
				zIndex: this.zIndex,
				disabled: this.disabled,
				onChangeCallback: BX.delegate(function()
				{
					var i, value = this.DOM.input.value;
					this.value = {text: value};
					for (i = 0; i < menuItemList.length; i++)
					{
						if (menuItemList[i].label === value)
						{
							this.value.type = menuItemList[i].type;
							this.value.value = menuItemList[i].value;
							break;
						}
					}

					if (this.params.onChangeCallback && typeof this.params.onChangeCallback == 'function')
					{
						this.params.onChangeCallback();
					}
				}, this)
			});
		},

		editMeetingRooms: function()
		{
			var params = {};
			if (this.params.getControlContentCallback)
				params.wrap = this.params.getControlContentCallback();

			if (!params.wrap)
			{
				params.wrap = this.showEditMeetingRooms();
			}

			this.buildLocationEditControl(params);
		},

		showEditMeetingRooms: function()
		{
			var _this = this;
			if (this.editDialog)
			{
				this.editDialog.destroy();
			}

			this.editDialogContent = BX.create('DIV');

			this.editDialog = new BX.PopupWindow(this.id + '_popup', null,
			{
				overlay: {opacity: 10},
				autoHide: true,
				closeByEsc : true,
				zIndex: this.zIndex,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: true,
				bindOnResize: false,
				titleBar: BX.message('EC_MEETING_ROOM_LIST_TITLE'),
				closeIcon: { right : "12px", top : "10px"},
				className: 'bxc-popup-window',
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('EC_SEC_SLIDER_SAVE'),
						events: {click : function()
						{
							_this.saveValues();
							if (_this.editDialog)
							{
								_this.editDialog.close();
							}
						}
					}}),

					new BX.PopupWindowButtonLink({
						text: BX.message('EC_SEC_SLIDER_CANCEL'),
						className: "popup-window-button-link-cancel",
						events: {click : function()
						{
							if (_this.editDialog)
							{
								_this.editDialog.close();
							}
						}}
					})
				],
				content: this.editDialogContent,
				events: {}
			});

			this.editDialog.show();
			return this.editDialogContent;
		},

		buildLocationEditControl: function(params)
		{
			var
				_this = this,
				i;

			this.locationEditControlShown = true;
			this.editDialogWrap = params.wrap;

			// Display meeting room list
			var locationList = this.calendar.util.getLocationList();
			this.locationList = [];
			this.addNewButtonField = false;
			for (i = 0; i < locationList.length; i++)
			{
				this.locationList.push({
					id: locationList[i].ID,
					name: locationList[i].NAME
				})
			}

			if (!this.locationList.length)
			{
				this.locationList.push({
					id: 0,
					name: ''
				});
			}

			for (i = 0; i < this.locationList.length; i++)
			{
				this.addRoomField(this.locationList[i], params.wrap);
			}

			// Display add button
			this.addNewButtonField = {
				outerWrap: params.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-container-text'}}))
			};
			this.addNewButtonField.innerWrap = this.addNewButtonField.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block'}}));

			this.addNewButtonField.innerCont = this.addNewButtonField.innerWrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-text'},
				html: '<span class="calendar-text-link">' + BX.message('EC_MEETING_ROOM_ADD') + '</span>',
				events: {
					click: function ()
					{
						var lastItem = _this.locationList[_this.locationList.length - 1];

						if (lastItem.id || lastItem.deleted || BX.util.trim(lastItem.field.input.value))
							_this.locationList.push(_this.addRoomField({id: 0}, params.wrap));
					}
				}
			}));
			params.wrap.appendChild(this.addNewButtonField.outerWrap);
		},

		addRoomField: function(room)
		{
			room.field = {
				outerWrap: this.editDialogWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-string'}}))
			};
			room.field.innerWrap = room.field.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block'}}));

			var _this = this;
			room.field.innerWrap.style.paddingRight = '40px';
			room.field.input = room.field.innerWrap.appendChild(BX.create('INPUT', {
				props: {className: 'calendar-field calendar-field-string'},
				attrs: {
					value: room.name || '',
					placeholder: BX.message('EC_MEETING_ROOM_PLACEHOLDER'),
					type: 'text'
				},
				events: {
					//blur: BX.delegate(function()
					//{
					//	setTimeout(BX.delegate(function()
					//	{
					//		//this.editRoom(room);
					//	}, this), 300);
					//}, this),
					keyup: function(e)
					{
						if (e.keyCode == 13)
						{
							_this.editRoom(room);
						}
					}
				}
			}));
			room.field.delRoomEntry = room.field.innerWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-remove-filed'},
				events: {click: function()
				{
					_this.deleteField(room);
				}}
			}));

			if (this.addNewButtonField)
			{
				this.editDialogWrap.appendChild(this.addNewButtonField.outerWrap);
			}

			if (!room.id)
				room.field.input.focus();

			return room;
		},

		editRoom: function(room)
		{
			if (!this.locationEditControlShown)
				return;

			room.field.input.value = BX.util.trim(room.field.input.value);
			if (!room.id)
			{
				if (room.field.input.value && room.field.input.value != room.name)
				{
					room.name = room.field.input.value;
					this.locationList.push(this.addRoomField({id: 0}));
				}
			}
			else
			{
				if (room.field.input.value != room.name)
				{
					room.name = room.field.input.value;
					room.changed = true;
				}
			}
		},

		deleteField: function(room)
		{
			BX.remove(room.field.outerWrap, true);
			room.deleted = true;
			room.changed = true;
		},

		saveValues: function()
		{
			var i, data = [];
			for (i = 0; i < this.locationList.length; i++)
			{
				if (this.locationList[i].field && this.locationList[i].field.input)
				{
					if (this.locationList[i].name !== this.locationList[i].field.input.value && this.locationList[i].id)
						this.locationList[i].changed = true;

					this.locationList[i].name = this.locationList[i].field.input.value;
				}

				if ((!this.locationList[i].deleted && this.locationList[i].name) || this.locationList[i].id)
				{
					data.push({
						id: this.locationList[i].id || 0,
						name: this.locationList[i].name || '',
						changed: (this.locationList[i].changed || !this.locationList[i].id) ? 'Y' : 'N',
						deleted: (this.locationList[i].deleted || !this.locationList[i].name) ? 'Y' : 'N'
					});
				}
			}

			this.calendar.request({
				type: 'post',
				data: {
					action: 'update_location_list',
					data: data
				},
				handler: BX.delegate(function(response)
				{
					this.calendar.util.setLocationList(response.locationList);
					this.setValues();
				}, this)
			});
			this.locationEditControlShown = false;
		},

		getTextValue: function(value)
		{
			if (!value)
			{
				value = this.value;
			}

			var res = value.str || value.text || '';
			if (value && value.type == 'mr')
			{
				res = 'ECMR_' + value.value;
			}
			else if (value && value.type == 'calendar')
			{
				res = 'calendar_' + value.value;
			}
			return res;
		},

		getValue: function()
		{
			return this.value;
		}
	};

	function NavigationCalendar(calendar, params)
	{
		this.calendar = calendar;
		this.outerWrap = params.wrap;
		this.created = false;
	}

	NavigationCalendar.prototype = {
		show: function ()
		{
			if (!this.created)
			{
				this.smallCalendar = new BX.JCCalendar();
				this.smallCalendar.month_popup_classname = 'calendar-navi-month-popup';
				this.smallCalendar.year_popup_classname = 'calendar-navi-year-popup';

				this.smallCalendar.Show({
					node: this.outerWrap,
					callback_after: BX.proxy(this.changeDate, this),
					bTime: false
				});

				this.outerWrap.appendChild(this.smallCalendar.DIV);
				this.smallCalendar.popup.close();
				this.created = true;

				BX.addCustomEvent(this.calendar, 'changeViewRange', BX.proxy(this.setDate, this));
			}
			this.outerWrap.style.display = '';
		},

		hide: function ()
		{
			this.outerWrap.style.display = 'none';
		},

		changeDate: function(date)
		{
			if(date
				&& this.calendar.util.getDayCode(this.calendar.getViewRangeDate()) != this.calendar.util.getDayCode(date)
				&& this.calendar.getView()
			)
			{
				this.calendar.getView().adjustViewRangeToDate(date);
			}
		},

		setDate: function(date)
		{
			if(date && this.smallCalendar.value
				&& this.calendar.util.getDayCode(this.smallCalendar.value) != this.calendar.util.getDayCode(date))
			{
				date.setHours(12, 0);
				this.smallCalendar.SetValue(date);
			}
		}
	};

	function DragDrop(calendar)
	{
		this.calendar = calendar;
	}

	DragDrop.prototype = {
		reset: function()
		{
			jsDD.Reset();
		},

		registerDay: function(day)
		{
			var dayNode = day.node;
			jsDD.registerDest(dayNode);

			dayNode.onbxdestdragfinish = BX.delegate(function()
			{
				if (this.draggedNode)
				{
					var entry = this.currentState.entry;
					day.date.setHours(0, 0, 0, 0);
					entry.from.setFullYear(day.date.getFullYear(), day.date.getMonth(), day.date.getDate());
					entry.to = new Date(entry.from.getTime() + (entry.data.DT_LENGTH - (entry.fullDay ? 1 : 0)) * 1000);
					entry.startDayCode = entry.from;
					entry.endDayCode = entry.to;
					entry.opacity = '0';

					this.calendar.getView().displayEntries({reloadEntries: false});
					var firstPart = entry.getWrap(0);

					BX.addClass(this.draggedNode, 'animate');
					setTimeout(BX.delegate(function ()
					{
						this.draggedNode.style.top = BX.pos(firstPart).top + 'px';
						this.draggedNode.style.left = BX.pos(firstPart).left + 'px';
					}, this), 1);

					setTimeout(BX.delegate(function ()
					{
						delete entry.opacity;
						entry.parts.forEach(function (part)
						{
							part.params.wrapNode.style.opacity = '';
						});
						BX.remove(this.draggedNode);
					}, this), 300);

					this.calendar.entryController.moveEventToNewDate(this.currentState.entry, day.date);
					BX.removeClass(dayNode, 'calendar-grid-drag-select');
				}
				return true;
			}, this);
			dayNode.onbxdestdraghover = function()
			{
				BX.addClass(dayNode, 'calendar-grid-drag-select');
			};
			dayNode.onbxdestdraghout = function()
			{
				BX.removeClass(dayNode, 'calendar-grid-drag-select');
			};
		},

		registerTimelineDay: function(day)
		{
			var dayNode = day.node;
			jsDD.registerDest(dayNode);

			dayNode.onbxdestdragfinish = BX.delegate(function(currentNode)
			{
				if (currentNode.getAttribute('data-bx-entry-resizer') == 'Y' && this.resizedState)
				{
					this.calendar.entryController.moveEventToNewDate(this.resizedState.entry, this.resizedState.entry.from, this.resizedState.entry.to);
					return true;
				}
				else if (this.draggedNode)
				{
					var entry = this.currentState.entry;
					entry.from.setFullYear(day.date.getFullYear(), day.date.getMonth(), day.date.getDate());
					entry.to = new Date(entry.from.getTime() + (entry.data.DT_LENGTH - (entry.fullDay ? 1 : 0)) * 1000);
					if (this.calendar.util.getDayCode(entry.from) != this.calendar.util.getDayCode(entry.to) && entry.to.getHours() == 0 && entry.to.getMinutes() == 0)
					{
						entry.to = new Date(entry.to.getTime() - 1000 * 60);
					}

					entry.startDayCode = entry.from;
					entry.endDayCode = entry.to;
					entry.opacity = '0';

					this.calendar.getView().displayEntries({reloadEntries: false});
					var firstPart = entry.getWrap(0);

					BX.addClass(this.draggedNode, 'animate');
					setTimeout(BX.delegate(function(){
						var partPos = BX.pos(firstPart);
						this.draggedNode.style.top = partPos.top + 'px';
						this.draggedNode.style.left = partPos.left + 'px';
						this.draggedNode.style.height = partPos.height + 'px';
						this.draggedNode.style.width = partPos.width + 'px';
						this.draggedNode.style.opacity = '0.6';
					}, this),1);

					setTimeout(BX.delegate(function()
					{
						delete entry.opacity;
						entry.parts.forEach(function(part){
							part.params.wrapNode.style.opacity = '';
						});
						BX.remove(this.draggedNode);
					}, this), 250);

					this.calendar.entryController.moveEventToNewDate(this.currentState.entry, entry.from, entry.to);
					BX.removeClass(dayNode, 'calendar-timeline-drag-select');
				}

				return true;
			}, this);
			dayNode.onbxdestdraghover = BX.delegate(function()
			{
				if (this.draggedNode)
				{
					var posLeft = (BX.pos(dayNode).left + 4);
					if (Math.abs(posLeft - parseInt(this.draggedNode.style.left)) > 30)
					{
						BX.addClass(this.draggedNode, 'animate');
						setTimeout(BX.delegate(function(){
							this.draggedNode.style.left = (BX.pos(dayNode).left + 4) + 'px';
						}, this),1);

						if (this.clearAnimateTimeout)
						{
							clearTimeout(this.clearAnimateTimeout);
						}
						this.clearAnimateTimeout = setTimeout(BX.delegate(function()
						{
							BX.removeClass(this.draggedNode, 'animate');
						}, this),300);
					}
					BX.addClass(dayNode, 'calendar-timeline-drag-select');
				}
			}, this);
			dayNode.onbxdestdraghout = BX.delegate(function()
			{
				if (this.draggedNode)
				{
					BX.removeClass(dayNode, 'calendar-timeline-drag-select');
				}
			}, this);
		},

		registerEntry: function(node, params)
		{
			var dragAllowed = this.calendar.entryController.canDo(params.entry, 'edit');
			jsDD.registerObject(node);

			node.onbxdragstart = BX.delegate(function()
			{
				if (!dragAllowed)
				{
					this.draggedNode = false;
					BX.addClass(node, 'calendar-entry-shake-mode');
					if (this.denyDragTimeout)
						clearTimeout(this.denyDragTimeout);
					this.denyDragTimeout = setTimeout(function(){BX.removeClass(node, 'calendar-entry-shake-mode');}, 1000);
					return;
				}

				this.currentState = params;
				this.draggedNode = document.body.appendChild(node.cloneNode(true));
				node.style.opacity = '0.3';
				BX.addClass(this.draggedNode, 'calendar-entry-drag-mode');
				BX.removeClass(this.draggedNode, 'calendar-event-line-start-yesterday');
				BX.removeClass(this.draggedNode, 'calendar-event-line-finish-tomorrow');

				if (this.calendar.currentViewName == 'week' || this.calendar.currentViewName == 'day')
				{
					this.draggedNode.style.left = (BX.pos(node).left + 2) + 'px';
					this.draggedNode.style.width = (this.calendar.getView().getDayWidth() - 5) + 'px';
					this.currentState.offtimeTuneBaseZeroPos = BX.pos(this.calendar.getView().timeLinesCont).top;
					this.currentState.bottomBasePos = BX.pos(this.calendar.getView().bottomOffHours).bottom - 2;
				}
				else
				{
					this.draggedNode.style.width = this.calendar.getView().getDayWidth() + 'px';
				}

				var
					entry = this.currentState.entry,
					dayLength = entry.getLengthInDays(),
					resizer = this.draggedNode.querySelector('.calendar-event-resizer'),
					innerContainer = this.draggedNode.querySelector('.calendar-event-line-inner-container'),
					innerBackground = this.draggedNode.querySelector('.calendar-event-block-background'),
					lineInner = this.draggedNode.querySelector('.calendar-event-line-inner');

				if (dayLength > 1)
				{
					var textNode = this.draggedNode.querySelector('.calendar-event-line-text');
					if (textNode)
					{
						textNode.innerHTML = '<span class="calendar-event-line-days-count">(' + BX.message('EC_DAY_LENGTH').replace('#COUNT#', dayLength) + ')</span> ' + textNode.innerHTML;
					}
				}

				if (innerContainer)
				{
					if (entry.isFullDay())
					{
						innerContainer.style.backgroundColor = this.calendar.util.hexToRgba(entry.color, 0.7);
						innerContainer.style.borderColor = this.calendar.util.hexToRgba(entry.color, 0.7);
					}
					else
					{
						if (entry.isLongWithTime())
						{
							innerContainer.style.borderColor = this.calendar.util.hexToRgba(entry.color, 0.7);
						}
					}
				}

				if (innerBackground)
				{
					innerBackground.style.opacity = '0.45';
				}

				if (lineInner)
				{
					lineInner.style.maxWidth = '';
				}

				if (this.calendar.getView().allEventsPopup)
				{
					this.calendar.getView().allEventsPopup.close()
				}
			}, this);

			node.onbxdrag = BX.delegate(function(x, y)
			{
				if (this.draggedNode)
				{
					if (this.calendar.currentViewName == 'week' || this.calendar.currentViewName == 'day')
					{
						var
							timeFrom,timeNode,
							deltaTop = 7,
							entry = this.currentState.entry,
							view = this.calendar.getView(),
							nodeHeight = this.draggedNode.offsetHeight,
							nodeTop = (y - deltaTop);

						if (nodeTop < this.currentState.offtimeTuneBaseZeroPos)
						{
							BX.addClass(this.draggedNode, 'calendar-entry-shake-mode');
							if (this.shakeTimeout)
								clearTimeout(this.shakeTimeout);
							this.shakeTimeout = setTimeout(BX.proxy(function(){BX.removeClass(this.draggedNode, 'calendar-entry-shake-mode');}, this), 400);
							nodeTop = this.currentState.offtimeTuneBaseZeroPos;
						}
						else if (nodeTop + nodeHeight > this.currentState.bottomBasePos)
						{
							BX.addClass(this.draggedNode, 'calendar-entry-shake-mode');
							if (this.shakeTimeout)
								clearTimeout(this.shakeTimeout);
							this.shakeTimeout = setTimeout(BX.proxy(function(){BX.removeClass(this.draggedNode, 'calendar-entry-shake-mode');}, this), 400);

							nodeTop = this.currentState.bottomBasePos - nodeHeight;
						}

						timeFrom = view.getTimeByPos(nodeTop - this.currentState.offtimeTuneBaseZeroPos, 5);
						timeNode = this.draggedNode.querySelector('.calendar-event-block-time');

						this.draggedNode.style.top = nodeTop + 'px';

						if (timeNode && timeFrom)
						{
							entry.from.setHours(timeFrom.h, timeFrom.m);
							entry.to = new Date(entry.from.getTime() + (entry.data.DT_LENGTH - (entry.fullDay ? 1 : 0)) * 1000);
							if (this.calendar.util.getDayCode(entry.from) != this.calendar.util.getDayCode(entry.to) && entry.to.getHours() == 0 && entry.to.getMinutes() == 0)
							{
								entry.to = new Date(entry.to.getTime() - 1000);
							}

							timeNode.innerHTML = this.calendar.util.formatTime(entry.from) + ' &ndash; ' + this.calendar.util.formatTime(entry.to);
						}
					}
					else
					{
						this.draggedNode.style.top = (y - 10) + 'px';
						this.draggedNode.style.left = (x - 20) + 'px';
					}

					//if (tab == 'week_title')
					//{
					//	// We move event from title to timeline (week, day mode)
					//	_this.CheckTimelineOverPos(x, y);
					//}
				}
			}, this);

			node.onbxdragstop = BX.delegate(function()
			{
				setTimeout(BX.delegate(function()
				{
					BX.remove(this.draggedNode);
				}, this), 400);
			}, this);


			if (params.part.params.resizerNode)
			{
				this.registerResizer(params.part.params.resizerNode, params);
			}
		},

		registerResizer: function(node, params)
		{
			node.setAttribute('data-bx-entry-resizer', 'Y');

			BX.bind(node, "mousedown", BX.delegate(function(e)
			{
				e = e || window.event;

				this.resizedState = {
					entry: params.entry,
					entryWrap: params.part.params.wrapNode,
					node: node,
					startY: e.clientY + BX.GetWindowSize().scrollTop,
					height: parseInt(params.part.params.wrapNode.offsetHeight) || 0
				};
			}, this));

			jsDD.registerObject(node);

			node.onbxdrag = BX.delegate(function(x, y)
			{
				if (this.resizedState)
				{
					var
						entry = this.resizedState.entry,
						height = Math.max((this.resizedState.height + y - this.resizedState.startY + 5), 5),
						timeTo = this.calendar.getView().getTimeByPos(parseInt(this.resizedState.entryWrap.style.top) + height, 5),
						timeLabel = this.calendar.util.formatTime(entry.from) + ' &ndash; ' + this.calendar.util.formatTime(timeTo.h, timeTo.m),
						timeNode = this.resizedState.entryWrap.querySelector('.calendar-event-block-time');

					entry.to.setHours(timeTo.h, timeTo.m, 0);

					if (timeNode)
					{
						timeNode.innerHTML = timeLabel + '<span class="calendar-event-block-time-shadow">'+ timeLabel +'</span>';
					}
					this.resizedState.entryWrap.style.height = height + 'px';
				}
			}, this);

			node.onbxdragstop = function()
			{
				setTimeout(BX.delegate(function()
				{
					if (this.resizedState)
					{
						this.resizedState = null;
					}
				}, this), 400);
			};
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.ViewSwitcher = ViewSwitcher;
		window.BXEventCalendar.AddButton = AddButton;
		window.BXEventCalendar.SelectInput = SelectInput;
		window.BXEventCalendar.ReminderSelector = Reminder;
		window.BXEventCalendar.DestinationSelector = DestinationSelector;
		window.BXEventCalendar.LocationSelector = LocationSelector;
		window.BXEventCalendar.NavigationCalendar = NavigationCalendar;
		window.BXEventCalendar.DragDrop = DragDrop;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.ViewSwitcher = ViewSwitcher;
			window.BXEventCalendar.AddButton = AddButton;
			window.BXEventCalendar.SelectInput = SelectInput;
			window.BXEventCalendar.ReminderSelector = Reminder;
			window.BXEventCalendar.DestinationSelector = DestinationSelector;
			window.BXEventCalendar.LocationSelector = LocationSelector;
			window.BXEventCalendar.NavigationCalendar = NavigationCalendar;
			window.BXEventCalendar.DragDrop = DragDrop;
		});
	}
})(window);