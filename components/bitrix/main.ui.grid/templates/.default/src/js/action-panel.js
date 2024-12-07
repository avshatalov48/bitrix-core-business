(function() {
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
		this.button = [];
		this.elements = [];
		this.buttonOnChange = [];
		this.buttonData = {};
	};

	BX.Grid.ActionPanel.prototype = {
		init(parent, actions, types)
		{
			this.parent = parent;
			this.actions = eval(actions);
			this.types = eval(types);

			BX.addCustomEvent(window, 'Dropdown::change', BX.proxy(this._dropdownEventHandle, this));

			BX.addCustomEvent(window, 'Dropdown::load', BX.proxy(this._dropdownEventHandle, this));

			const panel = this.getPanel();
			BX.bind(panel, 'change', BX.delegate(this._checkboxChange, this));
			BX.bind(panel, 'click', BX.delegate(this._clickOnButton, this));

			BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this._gridUpdatedEventHandle, this));
		},

		destroy()
		{
			BX.removeCustomEvent(window, 'Dropdown::change', BX.proxy(this._dropdownEventHandle, this));
			BX.removeCustomEvent(window, 'Dropdown::load', BX.proxy(this._dropdownEventHandle, this));
			BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this._gridUpdatedEventHandle, this));
		},

		_gridUpdatedEventHandle()
		{
			const cancelButton = BX('grid_cancel_button');
			cancelButton && BX.fireEvent(BX.firstChild(cancelButton), 'click');
		},

		_dropdownEventHandle(id, event, item, dataItem)
		{
			this.isPanelControl(BX(id)) && this._dropdownChange(id, event, item, dataItem);
		},

		resetForAllCheckbox()
		{
			const checkbox = this.getForAllCheckbox();

			if (BX.type.isDomNode(checkbox))
			{
				checkbox.checked = null;
			}
		},

		getForAllCheckbox()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classForAllCheckbox'), true);
		},

		getPanel()
		{
			return BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classActionPanel'), true);
		},

		getApplyButton()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelApplyButton'), true);
		},

		isPanelControl(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classPanelControl'));
		},

		getTextInputs()
		{
			return BX.Grid.Utils.getBySelector(this.getPanel(), 'input[type="text"]');
		},

		getHiddenInputs()
		{
			return BX.Grid.Utils.getBySelector(this.getPanel(), 'input[type="hidden"]');
		},

		getSelects()
		{
			return BX.Grid.Utils.getBySelector(this.getPanel(), 'select');
		},

		getDropdowns()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classDropdown'));
		},

		getCheckboxes()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelCheckbox'));
		},

		getButtons()
		{
			return BX.Grid.Utils.getByClass(this.getPanel(), this.parent.settings.get('classPanelButton'));
		},

		isDropdown(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classDropdown'));
		},

		isCheckbox(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classPanelCheckbox'));
		},

		isTextInput(node)
		{
			return node.type === 'text';
		},

		isHiddenInput(node)
		{
			return node.type === 'hidden';
		},

		isSelect(node)
		{
			return node.tagName === 'SELECT';
		},

		createDropdown(data, relative)
		{
			const emptyText = data.EMPTY_TEXT || '';
			const isMultiple = data.MULTIPLE === 'Y';
			const container = this.createContainer(data.ID, relative, {});
			const dropdown = BX.create('div', {
				props: {
					className: 'main-dropdown main-grid-panel-control',
					id: `${data.ID}_control`,
				},
				attrs: {
					name: data.NAME,
					'data-name': data.NAME,
					'data-empty-text': emptyText,
					'data-multiple': isMultiple ? 'Y' : 'N',
					'data-items': JSON.stringify(data.ITEMS),
					'data-value': isMultiple ? '' : data.ITEMS[0].VALUE,
					'data-popup-position': 'fixed',
				},
				children: [BX.create('span', {
					props: { className: 'main-dropdown-inner' },
					html: isMultiple ? emptyText : data.ITEMS[0].NAME,
				})],
			});

			container.appendChild(dropdown);

			return container;
		},

		createCheckbox(data, relative)
		{
			const checkbox = this.createContainer(data.ID, relative, {});

			const inner = BX.create('span', {
				props: {
					className: 'main-grid-checkbox-container',
				},
			});

			const titleSpan = BX.create('span', {
				props: {
					className: 'main-grid-control-panel-content-title',
				},
			});

			const input = BX.create('input', {
				props: {
					type: 'checkbox',
					className: `${this.parent.settings.get('classPanelCheckbox')} main-grid-checkbox`,
					id: `${data.ID}_control`,
				},
				attrs: {
					value: data.VALUE || '',
					title: data.TITLE || '',
					name: data.NAME || '',
					'data-onchange': JSON.stringify(data.ONCHANGE),
				},
			});

			input.checked = data.CHECKED || null;

			checkbox.appendChild(inner);
			checkbox.appendChild(titleSpan);

			inner.appendChild(input);

			inner.appendChild(BX.create('label', {
				props: {
					className: 'main-grid-checkbox',
				},
				attrs: {
					for: `${data.ID}_control`,
					title: data.TITLE,
				},
			}));

			titleSpan.appendChild(BX.create('label', {
				attrs: {
					for: `${data.ID}_control`,
					title: data.TITLE,
				},
				html: data.LABEL,
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
		createText(data, relative)
		{
			const container = this.createContainer(data.ID, relative, {});
			const title = BX.type.isNotEmptyString(data.TITLE) ? data.TITLE : '';
			if (title !== '')
			{
				container.appendChild(
					BX.create(
						'label',
						{
							attrs: { title, for: `${data.ID}_control` },
							text: title,
						},
					),
				);
			}
			container.appendChild(
				BX.create(
					'input',
					{
						props:
							{
								className: 'main-grid-control-panel-input-text main-grid-panel-control',
								id: `${data.ID}_control`,
							},
						attrs:
							{
								name: data.NAME,
								title,
								placeholder: data.PLACEHOLDER || '',
								value: data.VALUE || '',
								type: 'text',
								'data-onchange': JSON.stringify(data.ONCHANGE || []),
							},
					},
				),
			);

			return container;
		},

		createHidden(data, relative)
		{
			const container = this.createContainer(
				data.ID,
				relative,
				{ CLASS: 'main-grid-panel-hidden-control-container' },
			);
			container.appendChild(
				BX.create(
					'input',
					{
						props:
							{
								id: `${data.ID}_control`,
								type: 'hidden',
							},
						attrs:
							{
								name: data.NAME,
								value: data.VALUE || '',
							},
					},
				),
			);

			return container;
		},

		createButton(data, relative)
		{
			this.buttonOnChange = (data.ONCHANGE || []);
			this.buttonData = data;

			this.button = this.createButtonNode(data);

			BX.removeCustomEvent(window, 'Grid::unselectRow', BX.proxy(this.prepareButton, this));
			BX.removeCustomEvent(window, 'Grid::selectRow', BX.proxy(this.prepareButton, this));
			BX.removeCustomEvent(window, 'Grid::allRowsSelected', BX.proxy(this.prepareButton, this));
			BX.removeCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this.prepareButton, this));

			if (
				this.buttonData.SETTINGS
				&& data.ID === this.buttonData.SETTINGS.buttonId
			)
			{
				BX.addCustomEvent(window, 'Grid::unselectRow', BX.proxy(this.prepareButton, this));
				BX.addCustomEvent(window, 'Grid::selectRow', BX.proxy(this.prepareButton, this));
				BX.addCustomEvent(window, 'Grid::allRowsSelected', BX.proxy(this.prepareButton, this));
				BX.addCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this.prepareButton, this));
			}

			this.prepareButton();

			const container = this.createContainer(data.ID, relative, {});
			container.appendChild(this.button);

			return container;
		},

		createButtonNode(data)
		{
			return BX.create('button', {
				props: {
					className: `ui-btn${data.CLASS ? ` ${data.CLASS}` : ''}`,
					id: `${data.ID}_control`,
					title: BX.type.isNotEmptyString(data.TITLE) ? data.TITLE : '',
				},
				attrs: {
					name: data.NAME || '',
				},
				html: data.TEXT,
			});
		},

		prepareButton()
		{
			if (this.isSetButtonDisabled())
			{
				BX.Dom.attr(this.button, 'data-onchange', []);
				BX.Dom.addClass(this.button, 'ui-btn-disabled');
			}
			else
			{
				BX.Dom.attr(this.button, 'data-onchange', this.buttonOnChange);
				BX.Dom.removeClass(this.button, 'ui-btn-disabled');
			}
		},

		isSetButtonDisabled()
		{
			return Boolean(this.buttonData.SETTINGS
				&& this.buttonData.SETTINGS.minSelectedRows
				&& (this.getSelectedIds().length < this.buttonData.SETTINGS.minSelectedRows));
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
		createLink(data, relative)
		{
			const container = this.createContainer(data.ID, relative, {});
			const link = BX.create('a', {
				props: {
					className: `main-grid-link${data.CLASS ? ` ${data.CLASS}` : ''}`,
					id: `${data.ID}_control`,
				},
				attrs: {
					href: data.HREF || '',
					'data-onchange': JSON.stringify(data.ONCHANGE || []),
				},
				html: data.TEXT,
			});

			container.appendChild(link);

			return container;
		},

		createCustom(data, relative)
		{
			const container = this.createContainer(
				data.ID,
				relative,
				{ CLASS: 'main-grid-panel-hidden-control-container' },
			);

			const custom = BX.create('div', {
				props: {
					className: `main-grid-panel-custom${data.CLASS ? ` ${data.CLASS}` : ''}`,
				},
				html: data.VALUE,
			});

			container.appendChild(custom);

			return container;
		},

		createContainer(id, relative, options)
		{
			id = id.replace('_control', '');
			relative = relative.replace('_control', '');
			options = options || {};

			return BX.create('span', {
				props: {
					className: this.parent.settings.get('classPanelControlContainer') + (options.CLASS ? ` ${options.CLASS}` : ''),
					id,
				},
				attrs: {
					'data-relative': relative,
				},
			});
		},

		removeItemsRelativeCurrent(node)
		{
			let element = node;
			const relative = [node.id];
			const result = [];
			let dataRelative;

			while (element)
			{
				dataRelative = BX.data(element, 'relative');

				if (relative.includes(dataRelative))
				{
					relative.push(element.id);
					result.push(element);
				}

				element = element.nextElementSibling;
			}

			result.forEach((current) => {
				BX.remove(current);
			});
		},

		validateData(data)
		{
			return (
				('ONCHANGE' in data)
				&& BX.type.isArray(data.ONCHANGE)
			);
		},

		activateControl(id)
		{
			const element = BX(id);

			if (BX.type.isDomNode(element))
			{
				BX.removeClass(element, this.parent.settings.get('classDisable'));
				element.disabled = null;
			}
		},

		deactivateControl(id)
		{
			const element = BX(id);

			if (BX.type.isDomNode(element))
			{
				BX.addClass(element, this.parent.settings.get('classDisable'));
				element.disabled = true;
			}
		},

		showControl(id)
		{
			const control = BX(id);
			control && BX.show(control);
		},

		hideControl(id)
		{
			const control = BX(id);
			control && BX.hide(control);
		},

		validateActionObject(action)
		{
			return (
				BX.type.isPlainObject(action) && ('ACTION' in action) && BX.type.isNotEmptyString(action.ACTION) && (
					action.ACTION === this.actions.RESET_CONTROLS
					|| ('DATA' in action) && BX.type.isArray(action.DATA)
				)
			);
		},

		validateControlObject(controlObject)
		{
			return (
				BX.type.isPlainObject(controlObject)
				&& ('TYPE' in controlObject)
				&& ('ID' in controlObject)
			);
		},

		createDate(data, relative)
		{
			const container = this.createContainer(data.ID, relative, {});
			const date = BX.decl({
				block: 'main-ui-date',
				mix: ['main-grid-panel-date'],
				calendarButton: true,
				valueDelete: true,
				placeholder: 'PLACEHOLDER' in data ? data.PLACEHOLDER : '',
				name: 'NAME' in data ? `${data.NAME}_from` : '',
				tabindex: 'TABINDEX' in data ? data.TABINDEX : '',
				value: 'VALUE' in data ? data.VALUE : '',
				enableTime: 'TIME' in data ? (data.TIME ? 'true' : 'false') : 'false',
			});

			container.appendChild(date);

			return container;
		},

		createControl(controlObject, relativeId)
		{
			let newElement = null;
			switch (controlObject.TYPE)
			{
				case this.types.DROPDOWN:
					newElement = this.createDropdown(controlObject, relativeId);
					break;

				case this.types.CHECKBOX:
					newElement = this.createCheckbox(controlObject, relativeId);
					break;

				case this.types.TEXT:
					newElement = this.createText(controlObject, relativeId);
					break;

				case this.types.HIDDEN:
					newElement = this.createHidden(controlObject, relativeId);
					break;

				case this.types.BUTTON:
					newElement = this.createButton(controlObject, relativeId);
					break;

				case this.types.LINK:
					newElement = this.createLink(controlObject, relativeId);
					break;

				case this.types.CUSTOM:
					newElement = this.createCustom(controlObject, relativeId);
					break;

				case this.types.DATE:
					newElement = this.createDate(controlObject, relativeId);
					break;
			}

			return newElement;
		},

		onChangeHandler(container, actions, isPseudo)
		{
			let newElement; let
				callback;
			const self = this;

			if (BX.type.isDomNode(container) && BX.type.isArray(actions))
			{
				actions.forEach(function(action) {
					if (self.validateActionObject(action))
					{
						if (action.ACTION === self.actions.CREATE)
						{
							self.removeItemsRelativeCurrent(container);
							const preparedData = BX.Runtime.clone(action.DATA).reverse();

							preparedData.forEach((controlObject) => {
								if (self.validateControlObject(controlObject))
								{
									newElement = self.createControl(controlObject, container.id || BX.data(container, 'relative'));

									if (BX.type.isDomNode(newElement))
									{
										BX.insertAfter(newElement, container);

										if (('ONCHANGE' in controlObject)
											&& controlObject.TYPE === self.types.CHECKBOX
											&& ('CHECKED' in controlObject)
											&& controlObject.CHECKED)
										{
											self.onChangeHandler(newElement, controlObject.ONCHANGE);
										}

										if (controlObject.TYPE === self.types.DROPDOWN
											&& BX.type.isArray(controlObject.ITEMS)
											&& controlObject.ITEMS.length > 0
											&& ('ONCHANGE' in controlObject.ITEMS[0])
											&& BX.type.isArray(controlObject.ITEMS[0].ONCHANGE))
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
								action.DATA.forEach((currentId) => {
									self.lastActivated.push(currentId.ID);
									self.activateControl(currentId.ID);
								});
							}
						}

						if (action.ACTION === self.actions.SHOW && BX.type.isArray(action.DATA))
						{
							action.DATA.forEach((showCurrent) => {
								self.showControl(showCurrent.ID);
							});
						}

						if (action.ACTION === self.actions.HIDE && BX.type.isArray(action.DATA))
						{
							action.DATA.forEach((hideCurrent) => {
								self.hideControl(hideCurrent.ID);
							});
						}

						if (action.ACTION === self.actions.HIDE_ALL_EXPECT && BX.type.isArray(action.DATA))
						{
							(self.getControls() || []).forEach((current) => {
								if (!action.DATA.some((el) => { return el.ID === current.id;
								}))
								{
									self.hideControl(current.id);
								}
							});
						}

						if (action.ACTION === self.actions.SHOW_ALL)
						{
							(self.getControls() || []).forEach((current) => {
								self.showControl(current.id);
							});
						}

						if (action.ACTION === self.actions.REMOVE && BX.type.isArray(action.DATA))
						{
							action.DATA.forEach((removeCurrent) => {
								BX.remove(BX(removeCurrent.ID));
							});
						}

						if (action.ACTION === self.actions.CALLBACK)
						{
							this.confirmDialog(action, BX.delegate(() => {
								if (BX.type.isArray(action.DATA))
								{
									action.DATA.forEach(
										(currentCallback) => {
											if (currentCallback.JS.includes('Grid.'))
											{
												callback = currentCallback.JS.replace('Grid', 'self.parent');
												callback = callback.replace('()', '');
												callback += '.apply(self.parent, [container])';
												try
												{
													eval(callback); // jshint ignore:line
												}
												catch (err)
												{
													throw new Error(err);
												}
											}
											else if (BX.type.isNotEmptyString(currentCallback.JS))
											{
												try
												{
													eval(currentCallback.JS);
												}
												catch (err)
												{
													throw new Error(err);
												}
											}
										},
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

				self.lastActivated.forEach((current) => {
					self.deactivateControl(current);
				});

				self.lastActivated = [];
			}
		},

		confirmDialog(action, then, cancel)
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
		_dropdownChange(id, event, item, dataItem)
		{
			const dropdown = BX(id);
			const container = dropdown.parentNode;
			const onChange = dataItem && ('ONCHANGE' in dataItem) ? dataItem.ONCHANGE : null;
			const isPseudo = dataItem && ('PSEUDO' in dataItem && dataItem.PSEUDO !== false);

			this.onChangeHandler(container, onChange, isPseudo);
		},

		_checkboxChange(event)
		{
			let onChange;

			try
			{
				onChange = eval(BX.data(event.target, 'onchange'));
			}
			catch
			{
				onChange = null;
			}

			this.onChangeHandler(
				BX.findParent(event.target, {
					className: this.parent.settings.get('classPanelContainer'),
				}, true, false),
				event.target.checked || event.target.id.includes('actallrows_') ? onChange : null,
			);
		},

		_clickOnButton(event)
		{
			let onChange;

			if (this.isButton(event.target))
			{
				event.preventDefault();

				try
				{
					onChange = eval(BX.data(event.target, 'onchange'));
				}
				catch
				{
					onChange = null;
				}

				this.onChangeHandler(
					BX.findParent(event.target, {
						className: this.parent.settings.get('classPanelContainer'),
					}, true, false),
					onChange,
				);
			}
		},

		isButton(node)
		{
			return BX.hasClass(node, this.parent.settings.get('classPanelButton'));
		},

		getSelectedIds()
		{
			const rows = this.parent.getRows().getSelected().filter((row) => { return row.isShown();
			});

			return rows.map((current) => {
				return current.getId();
			});
		},

		getControls()
		{
			return BX.findChild(this.getPanel(), {
				className: this.parent.settings.get('classPanelControlContainer'),
			}, true, true);
		},

		getValues()
		{
			const data = {};
			const self = this;
			const controls = [].concat(
				this.getDropdowns(),
				this.getTextInputs(),
				this.getHiddenInputs(),
				this.getSelects(),
				this.getCheckboxes(),
				this.getButtons(),
			);

			(controls || []).forEach((current) => {
				if (BX.type.isDomNode(current))
				{
					if (self.isDropdown(current))
					{
						let dropdownValue = BX.data(current, 'value');
						const multiple = BX.data(current, 'multiple') === 'Y';
						dropdownValue = (dropdownValue !== null && dropdownValue !== undefined) ? dropdownValue : '';
						data[BX.data(current, 'name')] = multiple ? dropdownValue.split(',') : dropdownValue;
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
						const name = BX.data(current, 'name');
						let value = BX.data(current, 'value');
						value = (value !== null && value !== undefined) ? value : '';

						if (name)
						{
							data[name] = value;
						}
					}
				}
			});

			return data;
		},

	};
})();
