;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.ActionPanel
	 *
	 * @param {BX.Main.grid} parent
	 * @param {object} actions List of available actions Bitrix\Main\Grid\Panel\Actions::getList()
	 * @param {string} actions.CREATE
	 * @param {string} actions.SEND
	 * @param {string} actions.ACTIVATE
	 * @param {string} actions.SHOW
	 * @param {string} actions.HIDE
	 * @param {string} actions.REMOVE
	 * @param {string} actions.CALLBACK
	 * @param {string} actions.INLINE_EDIT
	 * @param {string} actions.HIDE_ALL_EXPECT
	 * @param {string} actions.SHOW_ALL
	 * @param {string} actions.RESET_CONTROLS
	 *
	 * @param {object} types List of available control types
	 * of the actions panel Bitrix\Main\Grid\Panel\Types::getList()
	 * @param {string} types.DROPDOWN
	 * @param {string} types.CHECKBOX
	 * @param {string} types.TEXT
	 * @param {string} types.BUTTON
	 * @param {string} types.LINK
	 * @param {string} types.CUSTOM
	 * @param {string} types.HIDDEN
	 *
	 * @constructor
	 */
	BX.Grid.ActionPanel = function(parent, actions, types)
	{
		this.parent = null;
		this.rel = {};
		this.actions = null;
		this.types = null;
		this.lastActivated = [];
		this.init(parent, actions, types);
	};

	BX.Grid.ActionPanel.prototype = {
		init: function(parent, actions, types)
		{
			this.parent = parent;
			this.actions = eval(actions);
			this.types = eval(types);

			BX.addCustomEvent(window, 'Dropdown::change', BX.proxy(function(id, event, item, dataItem) {
				this.isPanelControl(BX(id))&& this._dropdownChange(id, event, item, dataItem);
			}, this));

			BX.addCustomEvent(window, 'Dropdown::load', BX.proxy(function(id, event, item, dataItem) {
				this.isPanelControl(BX(id)) && this._dropdownChange(id, event, item, dataItem);
			}, this));

			var panel = this.getPanel();
			BX.bind(panel, 'change', BX.delegate(this._checkboxChange, this));
			BX.bind(panel, 'click', BX.delegate(this._clickOnButton, this));

			BX.addCustomEvent(window, 'Grid::updated', function() {
				var cancelButton = BX('grid_cancel_button');
				cancelButton && BX.fireEvent(BX.firstChild(cancelButton), 'click');
			});
		},

		resetForAllCheckbox: function()
		{
			var checkbox = this.getForAllCheckbox();

			if (BX.type.isDomNode(checkbox))
			{
				checkbox.checked = null;
			}
		},

		getForAllCheckbox: function()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classForAllCheckbox'), true);
		},

		getPanel: function()
		{
			return BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classActionPanel'), true);
		},

		getApplyButton: function()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelApplyButton'), true);
		},

		isPanelControl: function(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classPanelControl'));
		},

		getTextInputs: function()
		{
			return BX.Grid.Utils.getBySelector(this.getPanel(), 'input[type="text"]');
		},

		getHiddenInputs: function()
		{
			return BX.Grid.Utils.getBySelector(this.getPanel(), 'input[type="hidden"]');
		},

		getSelects: function()
		{
			return BX.Grid.Utils.getBySelector(this.getPanel(), 'select');
		},

		getDropdowns: function()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classDropdown'));
		},

		getCheckboxes: function()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelCheckbox'));
		},

		getButtons: function()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelButton'));
		},

		isDropdown: function(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classDropdown'));
		},

		isCheckbox: function(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classPanelCheckbox'));
		},

		isTextInput: function(node)
		{
			return node.type === 'text';
		},

		isHiddenInput: function(node)
		{
			return node.type === 'hidden';
		},

		isSelect: function(node)
		{
			return node.tagName === 'SELECT';
		},

		createDropdown: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);
			var dropdown = BX.create('div', {
				props: {
					className: 'main-dropdown main-grid-panel-control',
					id: data.ID + '_control'
				},
				attrs: {
					name: data.NAME,
					'data-name': data.NAME,
					'data-items': JSON.stringify(data.ITEMS),
					'data-value': data.ITEMS[0].VALUE,
					'data-popup-position': 'fixed'
				},
				children: [BX.create('span', {
					props: {className: 'main-dropdown-inner'},
					html: data.ITEMS[0].NAME
				})]
			});

			container.appendChild(dropdown);

			return container;
		},

		createCheckbox: function(data, relative)
		{
			var checkbox = this.createContainer(data.ID, relative);

			var inner = BX.create('span', {
				props: {
					className: 'main-grid-checkbox-container'
				}
			});

			var titleSpan = BX.create('span', {
				props: {
					className: 'main-grid-control-panel-content-title'
				}
			});

			var input = BX.create('input', {
				props: {
					type: 'checkbox',
					className: this.parent.settings.get('classPanelCheckbox') + ' main-grid-checkbox',
					id: data.ID + '_control'
				},
				attrs: {
					value: data.VALUE || '',
					title: data.TITLE || '',
					name: data.NAME || '',
					'data-onchange': JSON.stringify(data.ONCHANGE)
				}
			});

			input.checked = data.CHECKED || null;

			checkbox.appendChild(inner);
			checkbox.appendChild(titleSpan);

			inner.appendChild(input);

			inner.appendChild(BX.create('label', {
				props: {
					className: 'main-grid-checkbox'
				},
				attrs: {
					for: data.ID + '_control',
					title: data.TITLE
				}
			}));

			titleSpan.appendChild(BX.create('label', {
				attrs: {
					for: data.ID + '_control',
					title: data.TITLE
				},
				html: data.LABEL
			}));

			return checkbox;
		},

		/**
		 * @param {object} data
		 * @param {object} data.ID
		 * @param {object} data.TITLE
		 * @param {object} data.PLACEHOLDER
		 * @param {object} data.ONCHANGE
		 * @param {string} relative
		 * @returns {*}
		 */
		createText: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);
			var title = BX.type.isNotEmptyString(data["TITLE"]) ? data["TITLE"] : "";
			if(title !== "")
			{
				container.appendChild(
					BX.create(
						'label',
						{
							attrs: { title: title, for: data.ID + '_control' },
							text: title
						}
					)
				);
			}
			container.appendChild(
				BX.create(
					'input',
					{
						props:
							{
								className: 'main-grid-control-panel-input-text main-grid-panel-control',
								id: data.ID + '_control'
							},
						attrs:
							{
								name: data.NAME,
								title: title,
								placeholder: data.PLACEHOLDER || '',
								value: data.VALUE || '',
								type: 'text',
								'data-onchange': JSON.stringify(data.ONCHANGE || [])
							}
					}
				)
			);

			return container;
		},

		createHidden: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);
			container.appendChild(
				BX.create(
					'input',
					{
						props:
							{
								id: data.ID + '_control',
								type: 'hidden'
							},
						attrs:
							{
								name: data.NAME,
								value: data.VALUE || ''
							}
					}
				)
			);

			return container;
		},

		createButton: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);
			var button = BX.create('button', {
				props: {
					className: 'main-grid-buttons' + (data.CLASS ? ' ' + data.CLASS : ''),
					id: data.id + '_control',
					title: BX.type.isNotEmptyString(data.TITLE) ? data.TITLE : ''
				},
				attrs: {
					name: data.NAME || '',
					'data-onchange': JSON.stringify(data.ONCHANGE || [])
				},
				html: data.TEXT
			});

			container.appendChild(button);

			return container;
		},

		/**
		 * @param {object} data
		 * @param {object} data.ID
		 * @param {object} data.TITLE
		 * @param {object} data.PLACEHOLDER
		 * @param {object} data.ONCHANGE
		 * @param {object} data.CLASS
		 * @param {object} data.HREF
		 * @param {string} relative
		 * @returns {*}
		 */
		createLink: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);
			var link = BX.create('a', {
				props: {
					className: 'main-grid-link' + (data.CLASS ? ' ' + data.CLASS : ''),
					id: data.ID + '_control'
				},
				attrs: {
					href: data.HREF || '',
					'data-onchange': JSON.stringify(data.ONCHANGE || [])
				},
				html: data.TEXT
			});

			container.appendChild(link);

			return container;
		},

		createCustom: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);

			var custom = BX.create('div', {
				props: {
					className: 'main-grid-panel-custom' + (data.CLASS ? ' ' + data.CLASS : '')
				},
				html: data.VALUE
			});

			container.appendChild(custom);

			return container;
		},

		createContainer: function(id, relative)
		{
			id = id.replace('_control', '');
			relative = relative.replace('_control', '');

			return BX.create('span', {
				props: {
					className: this.parent.settings.get('classPanelControlContainer'),
					id: id
				},
				attrs: {
					'data-relative': relative
				}
			});
		},

		removeItemsRelativeCurrent: function(node)
		{
			var element = node;
			var relative = node.id;
			var result = [];
			var dataRelative;

			while (element) {
				dataRelative = BX.data(element, 'relative');

				if (dataRelative === relative || dataRelative === node.id)
				{
					relative = element.id;
					result.push(element);
				}

				element = element.nextElementSibling;
			}

			result.forEach(function(current) {
				BX.remove(current);
			});
		},


		validateData: function(data)
		{
			return (
				('ONCHANGE' in data) &&
				BX.type.isArray(data.ONCHANGE)
			);
		},

		activateControl: function(id)
		{
			var element = BX(id);

			if (BX.type.isDomNode(element))
			{
				BX.removeClass(element, this.parent.settings.get('classDisable'));
				element.disabled = null;
			}
		},

		deactivateControl: function(id)
		{
			var element = BX(id);

			if (BX.type.isDomNode(element))
			{
				BX.addClass(element, this.parent.settings.get('classDisable'));
				element.disabled = true;
			}
		},

		showControl: function(id)
		{
			var control = BX(id);
			control && BX.show(control);
		},

		hideControl: function(id)
		{
			var control = BX(id);
			control && BX.hide(control);
		},


		validateActionObject: function(action)
		{
			return (
				BX.type.isPlainObject(action) && ('ACTION' in action) && BX.type.isNotEmptyString(action.ACTION) && (
					action.ACTION === this.actions.RESET_CONTROLS ||
					('DATA' in action) && BX.type.isArray(action.DATA)
				)
			);
		},

		validateControlObject: function(controlObject)
		{
			return (
				BX.type.isPlainObject(controlObject) &&
				('TYPE' in controlObject) &&
				('ID' in controlObject)
			);
		},

		createDate: function(data, relative)
		{
			var container = this.createContainer(data.ID, relative);
			var date = BX.decl({
				block: 'main-ui-date',
				mix: ['main-grid-panel-date'],
				calendarButton: true,
				valueDelete: true,
				placeholder: 'PLACEHOLDER' in data ? data.PLACEHOLDER : '',
				name: 'NAME' in data ? data.NAME + '_from' : '',
				tabindex: 'TABINDEX' in data ? data.TABINDEX : '',
				value: 'VALUE' in data ? data.VALUE : '',
				enableTime: 'TIME' in data ? (data.TIME ? 'true' : 'false') : 'false'
			});

			container.appendChild(date);
			return container;
		},

		createControl: function(controlObject, relativeId)
		{
			var newElement = null;
			switch (controlObject.TYPE)
			{
				case this.types.DROPDOWN :
					newElement = this.createDropdown(controlObject, relativeId);
					break;

				case this.types.CHECKBOX :
					newElement = this.createCheckbox(controlObject, relativeId);
					break;

				case this.types.TEXT :
					newElement = this.createText(controlObject, relativeId);
					break;

				case this.types.HIDDEN :
					newElement = this.createHidden(controlObject, relativeId);
					break;

				case this.types.BUTTON :
					newElement = this.createButton(controlObject, relativeId);
					break;

				case this.types.LINK :
					newElement = this.createLink(controlObject, relativeId);
					break;

				case this.types.CUSTOM :
					newElement = this.createCustom(controlObject, relativeId);
					break;

				case this.types.DATE :
					newElement = this.createDate(controlObject, relativeId);
					break;
			}

			return newElement;
		},

		onChangeHandler: function(container, actions, isPseudo)
		{
			var newElement, callback;
			var self = this;

			if (BX.type.isDomNode(container) && BX.type.isArray(actions))
			{
				actions.forEach(function(action) {
					if (self.validateActionObject(action))
					{
						if (action.ACTION === self.actions.CREATE)
						{
							self.removeItemsRelativeCurrent(container);
							action.DATA.reverse();

							action.DATA.forEach(function(controlObject) {
								if (self.validateControlObject(controlObject))
								{
									newElement = self.createControl(controlObject, BX.data(container, 'relative') || container.id);

									if (BX.type.isDomNode(newElement))
									{
										BX.insertAfter(newElement, container);

										if (('ONCHANGE' in controlObject) &&
											controlObject.TYPE === self.types.CHECKBOX &&
											('CHECKED' in controlObject) &&
											controlObject.CHECKED)
										{
											self.onChangeHandler(newElement, controlObject.ONCHANGE);
										}

										if (controlObject.TYPE === self.types.DROPDOWN &&
											BX.type.isArray(controlObject.ITEMS) &&
											controlObject.ITEMS.length &&
											('ONCHANGE' in controlObject.ITEMS[0]) &&
											BX.type.isArray(controlObject.ITEMS[0].ONCHANGE))
										{
											self.onChangeHandler(newElement, controlObject.ITEMS[0].ONCHANGE);
										}
									}
								}
							});
						}

						if (action.ACTION === self.actions.ACTIVATE)
						{
							self.removeItemsRelativeCurrent(container);

							if (BX.type.isArray(action.DATA))
							{
								action.DATA.forEach(function(currentId) {
									self.lastActivated.push(currentId.ID);
									self.activateControl(currentId.ID);
								});
							}
						}

						if (action.ACTION === self.actions.SHOW)
						{
							if (BX.type.isArray(action.DATA))
							{
								action.DATA.forEach(function(showCurrent) {
									self.showControl(showCurrent.ID);
								});
							}
						}

						if (action.ACTION === self.actions.HIDE)
						{
							if (BX.type.isArray(action.DATA))
							{
								action.DATA.forEach(function(hideCurrent) {
									self.hideControl(hideCurrent.ID);
								});
							}
						}

						if (action.ACTION === self.actions.HIDE_ALL_EXPECT)
						{
							if (BX.type.isArray(action.DATA))
							{
								(self.getControls() || []).forEach(function(current) {
									if (!action.DATA.some(function(el) { return el.ID === current.id}))
									{
										self.hideControl(current.id);
									}
								});
							}
						}

						if (action.ACTION === self.actions.SHOW_ALL)
						{
							(self.getControls() || []).forEach(function(current) {
								self.showControl(current.id);
							});
						}

						if (action.ACTION === self.actions.REMOVE)
						{
							if (BX.type.isArray(action.DATA))
							{
								action.DATA.forEach(function(removeCurrent) {
									BX.remove(BX(removeCurrent.ID));
								});
							}
						}

						if (action.ACTION === self.actions.CALLBACK)
						{
							this.confirmDialog(action, BX.delegate(function() {
								if (BX.type.isArray(action.DATA))
								{
									action.DATA.forEach(
										function(currentCallback)
										{
											if (currentCallback.JS.indexOf('Grid.') !== -1)
											{
												callback = currentCallback.JS.replace('Grid', 'self.parent');
												callback = callback.replace('()', '');
												callback += '.apply(self.parent, [container])';
												try
												{
													eval(callback); // jshint ignore:line
												}
												catch(err)
												{
													throw new Error(err);
												}
											}
											else if(BX.type.isNotEmptyString(currentCallback.JS))
											{
												try
												{
													eval(currentCallback.JS);
												}
												catch(err)
												{
													throw new Error(err);
												}
											}
										}
									);
								}
							}, this));
						}

						if (action.ACTION === self.actions.RESET_CONTROLS)
						{
							this.removeItemsRelativeCurrent(container);
						}
					}
				}, this);

			}
			else
			{
				if (!isPseudo)
				{
					this.removeItemsRelativeCurrent(container);
				}

				self.lastActivated.forEach(function(current) {
					self.deactivateControl(current);
				});

				self.lastActivated = [];
			}
		},

		confirmDialog: function(action, then, cancel)
		{
			this.parent.confirmDialog(action, then, cancel);
		},

		/**
		 * Dropdown value change handler
		 * @param {string} id Dropdown id
		 * @param {object} event
		 * @param item
		 * @param {object} dataItem
		 * @param {object} dataItem.ONCHANGE
		 * @param {boolean} dataItem.PSEUDO
		 * @private
		 */
		_dropdownChange: function(id, event, item, dataItem)
		{
			var dropdown = BX(id);
			var container = dropdown.parentNode;
			var onChange = dataItem && ('ONCHANGE' in dataItem) ? dataItem.ONCHANGE : null;
			var isPseudo = dataItem && ('PSEUDO' in dataItem && dataItem.PSEUDO !== false);

			this.onChangeHandler(container, onChange, isPseudo);
		},

		_checkboxChange: function(event)
		{
			var onChange;

			try {
				onChange = eval(BX.data(event.target, 'onchange'));
			} catch(err) {
				onChange = null;
			}

			this.onChangeHandler(
				BX.findParent(event.target, {
					className: this.parent.settings.get('classPanelContainer')
				}, true, false),
				event.target.checked || event.target.id.indexOf('actallrows_') !== -1 ? onChange : null
			);
		},

		_clickOnButton: function(event)
		{
			var onChange;

			if (this.isButton(event.target))
			{
				event.preventDefault();

				try {
					onChange = eval(BX.data(event.target, 'onchange'));
				} catch(err) {
					onChange = null;
				}

				this.onChangeHandler(
					BX.findParent(event.target, {
						className: this.parent.settings.get('classPanelContainer')
					}, true, false),
					onChange
				);
			}
		},

		isButton: function(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classPanelButton'));
		},

		getSelectedIds: function()
		{
			var rows = this.parent.getRows().getSelected().filter(function(row) { return row.isShown(); });

			return rows.map(function(current) {
				return current.getId();
			});
		},

		getControls: function()
		{
			return BX.findChild(this.getPanel(), {
				className: this.parent.settings.get('classPanelControlContainer')
			}, true, true);
		},

		getValues: function()
		{
			var data = {};
			var self = this;
			var controls = [].concat(
				this.getDropdowns(),
				this.getTextInputs(),
				this.getHiddenInputs(),
				this.getSelects(),
				this.getCheckboxes(),
				this.getButtons()
			);

			(controls || []).forEach(function(current) {
				if (BX.type.isDomNode(current))
				{
					if (self.isDropdown(current))
					{
						var dropdownValue = BX.data(current, 'value');
						dropdownValue = (dropdownValue !== null && dropdownValue !== undefined) ? dropdownValue : '';
						data[BX.data(current, 'name')] = dropdownValue;
					}

					if (self.isSelect(current))
					{
						data[current.getAttribute('name')] = current.options[current.selectedIndex].value;
					}

					if (self.isCheckbox(current) && current.checked)
					{
						data[current.getAttribute('name')] = current.value;
					}

					if (self.isTextInput(current) || self.isHiddenInput(current))
					{
						data[current.getAttribute('name')] = current.value;
					}

					if (self.isButton(current))
					{
						var name = BX.data(current, 'name');
						var value = BX.data(current, 'value');
						value = (value !== null && value !== undefined) ? value : '';

						if (name)
						{
							data[name] = value;
						}
					}
				}
			});

			return data;
		}

	};
})();