import { Event } from 'main.core';
import { EventEmitter } from 'main.core.events';

(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.InlineEditor
	 * @param {BX.Main.grid} parent
	 * @param {Object} types
	 * @constructor
	 */
	BX.Grid.InlineEditor = function(parent, types)
	{
		this.parent = null;
		this.types = null;
		this.isDropdownChangeEventSubscribed = false;
		this.init(parent, types);
	};

	BX.Grid.InlineEditor.prototype = {
		init(parent, types)
		{
			this.parent = parent;

			try
			{
				this.types = eval(types);
			}
			catch
			{
				this.types = null;
			}
		},

		createContainer()
		{
			return BX.create('div', {
				props: {
					className: this.parent.settings.get('classEditorContainer'),
				},
			});
		},

		createTextarea(editObject, height)
		{
			return BX.create('textarea', {
				props: {
					className: [
						this.parent.settings.get('classEditor'),
						this.parent.settings.get('classEditorTextarea'),
					].join(' '),
				},
				attrs: {
					name: editObject.NAME,
					style: `height:${height}px`,
				},
				html: editObject.VALUE || '',
			});
		},

		createInput(editObject)
		{
			let className = this.parent.settings.get('classEditorText');
			const attrs =				{
				value: (editObject.VALUE !== undefined && editObject.VALUE !== null) ? BX.util.htmlspecialcharsback(editObject.VALUE) : '',
				name: (editObject.NAME !== undefined && editObject.NAME !== null) ? editObject.NAME : '',
			};

			if (editObject.TYPE === this.types.CHECKBOX)
			{
				className = this.parent.settings.get('classEditorCheckbox');
				attrs.type = 'checkbox';
				attrs.checked = (attrs.value == 'Y');
			}

			if (editObject.TYPE === this.types.DATE)
			{
				className = [className, this.parent.settings.get('classEditorDate')].join(' ');
			}

			if (editObject.TYPE === this.types.NUMBER)
			{
				className = [className, this.parent.settings.get('classEditorNumber')].join(' ');
				attrs.type = 'number';
			}

			if (editObject.TYPE === this.types.RANGE)
			{
				className = [className, this.parent.settings.get('classEditorRange')].join(' ');
				attrs.type = 'range';

				if (BX.type.isPlainObject(editObject.DATA))
				{
					attrs.min = editObject.DATA.MIN || '0';
					attrs.max = editObject.DATA.MAX || 99999;
					attrs.step = editObject.DATA.STEP || '';
				}
			}

			if (BX.type.isNotEmptyString(editObject.PLACEHOLDER))
			{
				attrs.placeholder = BX.util.htmlspecialchars(editObject.PLACEHOLDER);
			}

			if (editObject.DISABLED)
			{
				attrs.disabled = true;
			}

			className = [this.parent.settings.get('classEditor'), className].join(' ');

			return BX.create('input', {
				props: {
					className,
					id: `${editObject.NAME}_control`,
				},
				attrs,
			});
		},

		createCustom(editObject)
		{
			let className = this.parent.settings.get('classEditorCustom');
			className = [this.parent.settings.get('classEditor'), className].join(' ');

			return BX.create('div', {
				props: {
					className,
				},
				attrs: {
					'data-name': editObject.NAME,
				},
				html: editObject.VALUE || '',
			});
		},

		createMoney(editObject)
		{
			const value = editObject.VALUE;
			const fieldChildren = [];

			const priceObject = value.PRICE || {};
			priceObject.PLACEHOLDER = editObject.PLACEHOLDER || '';
			fieldChildren.push(this.createMoneyPrice(priceObject));

			if ((BX.type.isArray(editObject.CURRENCY_LIST) && editObject.CURRENCY_LIST.length > 0))
			{
				const currencyObject = value.CURRENCY || {};
				currencyObject.DATA = {
					ITEMS: editObject.CURRENCY_LIST,
				};
				currencyObject.HTML_ENTITY = editObject.HTML_ENTITY || false;
				fieldChildren.push(this.createMoneyCurrency(currencyObject));
			}

			if (BX.type.isNotEmptyObject(value.HIDDEN))
			{
				for (const fieldName in value.HIDDEN)
				{
					if (value.HIDDEN.hasOwnProperty(fieldName) && BX.type.isNotEmptyString(fieldName))
					{
						const hidden = this.createInput({
							NAME: fieldName,
							VALUE: value.HIDDEN[fieldName],
							TYPE: this.types.TEXT,
						});
						hidden.type = 'hidden';
						fieldChildren.push(hidden);
					}
				}
			}

			let className = this.parent.settings.get('classEditorMoney');
			className = [this.parent.settings.get('classEditor'), className].join(' ');
			const attrs = value.ATTRIBUTES || {};
			attrs['data-name'] = editObject.NAME;

			return BX.create('div', {
				props: {
					className,
				},
				attrs,
				children: fieldChildren,
			});
		},

		createMoneyPrice(priceObject)
		{
			priceObject.TYPE = this.types.NUMBER;

			const priceInput = this.createInput(priceObject);
			priceInput.classList.add('main-grid-editor-money-price');
			Event.bind(priceInput, 'change', (event) => {
				const fieldNode = event.target.parentNode;
				const currencyDropdown = fieldNode.querySelector('.main-grid-editor-money-currency');
				const eventData = {
					field: fieldNode,
					values: {
						price: event.target.value || '',
						currency: currencyDropdown.dataset.value || '',
					},
				};

				EventEmitter.emit('Grid.MoneyField::change', eventData);
			});

			return priceInput;
		},

		createMoneyCurrency(currencyObject)
		{
			const currencyBlock = this.createDropdown(currencyObject);
			currencyBlock.dataset.menuOffsetLeft = 15;
			currencyBlock.dataset.menuMaxHeight = 200;
			currencyBlock.classList.add('main-grid-editor-money-currency');
			if (currencyObject.DISABLED === true)
			{
				currencyBlock.classList.remove('main-dropdown');
				currencyBlock.dataset.disabled = true;
			}

			if (!this.isDropdownChangeEventSubscribed)
			{
				this.isDropdownChangeEventSubscribed = true;
				EventEmitter.subscribe('Dropdown::change', (event) => {
					const [controlId] = event.getData();
					if (!BX.type.isNotEmptyString(controlId))
					{
						return;
					}

					const dropdownObject = BX.Main.dropdownManager.getById(controlId);
					if (dropdownObject.dropdown && dropdownObject.dropdown.classList.contains('main-grid-editor-money-currency'))
					{
						const fieldNode = dropdownObject.dropdown.parentNode;
						const priceField = fieldNode.querySelector('.main-grid-editor-money-price');
						const eventData = {
							field: fieldNode,
							values: {
								price: priceField.value || '',
								currency: dropdownObject.dropdown.dataset.value || '',
							},
						};

						EventEmitter.emit('Grid.MoneyField::change', eventData);
					}
				});
			}

			return currencyBlock;
		},

		createOutput(editObject)
		{
			return BX.create('output', {
				props: {
					className: this.parent.settings.get('classEditorOutput') || '',
				},
				attrs: {
					for: `${editObject.NAME}_control`,
				},
				text: editObject.VALUE || '',
			});
		},

		getDropdownValueItemByValue(items, value)
		{
			const preparedValue = String(value);
			const result = items.filter((current) => {
				return String(current.VALUE) === preparedValue;
			});

			return result.length > 0 ? result[0] : items[0];
		},

		createDropdown(editObject)
		{
			const valueItem = this.getDropdownValueItemByValue(
				editObject.DATA.ITEMS,
				editObject.VALUE,
			);
			const isHtmlEntity = 'HTML_ENTITY' in editObject && editObject.HTML_ENTITY === true;

			return BX.create('div', {
				props: {
					className: [
						this.parent.settings.get('classEditor'),
						'main-dropdown main-grid-editor-dropdown',
					].join(' '),
					id: `${editObject.NAME}_control`,
				},
				attrs: {
					name: editObject.NAME,
					tabindex: '0',
					'data-items': JSON.stringify(editObject.DATA.ITEMS),
					'data-value': valueItem.VALUE,
					'data-html-entity': editObject.HTML_ENTITY,
				},
				children: [BX.create('span', {
					props: { className: 'main-dropdown-inner' },
					html: isHtmlEntity ? valueItem.NAME : null,
					text: isHtmlEntity ? null : valueItem.NAME,
				})],
			});
		},

		createMultiselect(editObject)
		{
			const selectedValues = [];
			const squares = (() => {
				if (BX.Type.isArrayFilled(editObject.VALUE))
				{
					return editObject.VALUE.map((value) => {
						const item = this.getDropdownValueItemByValue(editObject.DATA.ITEMS, value);
						selectedValues.push(item);
						const itemName = item.HTML ?? BX.util.htmlspecialchars(item.NAME);
						const renderedItem = BX.Tag.render`
							<span class="main-ui-square">
								<span class="main-ui-square-item">${itemName}</span>
								<span class="main-ui-item-icon main-ui-square-delete"></span>
							</span>
						`;

						BX.Dom.attr(renderedItem, 'data-item', item);

						return renderedItem;
					});
				}

				return [];
			})();
			const layout = BX.Tag.render`
				<div
					class="main-grid-editor main-ui-control main-ui-multi-select"
					name="${BX.Text.encode(editObject.NAME)}"
					id="${`${BX.Text.encode(editObject.NAME)}_control`}"
				>
					<span class="main-ui-square-container">${squares}</span>
					<span class="main-ui-hide main-ui-control-value-delete">
						<span class="main-ui-control-value-delete-item"></span>
					</span>
					<span class="main-ui-square-search">
						<input type="text" class="main-ui-square-search-item">
					</span>
				</div>
			`;

			BX.Dom.attr(
				layout,
				{
					'data-params': { isMulti: true },
					'data-items': editObject.DATA.ITEMS,
					'data-value': selectedValues,
				},
			);

			return layout;
		},

		validateEditObject(editObject)
		{
			return (
				BX.type.isPlainObject(editObject)
				&& ('TYPE' in editObject)
				&& ('NAME' in editObject)
				&& ('VALUE' in editObject)
				&& (!('items' in editObject) || (BX.type.isArray(editObject.items) && editObject.items.length))
			);
		},

		initCalendar(event)
		{
			BX.calendar({ node: event.target, field: event.target });
		},

		bindOnRangeChange(control, output)
		{
			function bubble(control, output)
			{
				BX.html(output, control.value);

				const value = parseFloat(control.value);
				const max = parseFloat(control.getAttribute('max'));
				const min = parseFloat(control.getAttribute('min'));
				const thumbWidth = 16;
				const range = (max - min);
				const position = (((value - min) / range) * 100);
				const positionOffset = (Math.round(thumbWidth * position / 100) - (thumbWidth / 2));

				output.style.left = `${position}%`;
				output.style.marginLeft = `${-positionOffset}px`;
			}

			setTimeout(() => {
				bubble(control, output);
			}, 0);

			BX.bind(control, 'input', () => {
				bubble(control, output);
			});
		},

		createImageEditor(editObject)
		{
			return (new BX.Grid.ImageField(this.parent, editObject)).getLayout();
		},

		getEditor(editObject, height)
		{
			let control; let
				span;
			const container = this.createContainer();

			if (this.validateEditObject(editObject))
			{
				editObject.VALUE = editObject.VALUE === null ? '' : editObject.VALUE;

				switch (editObject.TYPE)
				{
					case this.types.TEXT: {
						control = this.createInput(editObject);
						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.DATE: {
						control = this.createInput(editObject);
						BX.bind(control, 'click', this.initCalendar);
						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.NUMBER: {
						control = this.createInput(editObject);
						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.RANGE: {
						control = this.createInput(editObject);
						span = this.createOutput(editObject);
						this.bindOnRangeChange(control, span);
						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.CHECKBOX: {
						control = this.createInput(editObject);
						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.TEXTAREA: {
						control = this.createTextarea(editObject, height);
						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.DROPDOWN: {
						control = this.createDropdown(editObject);
						break;
					}

					case this.types.MULTISELECT: {
						control = this.createMultiselect(editObject);
						break;
					}

					case this.types.IMAGE: {
						control = this.createImageEditor(editObject);
						break;
					}

					case this.types.MONEY: {
						control = this.createMoney(editObject);
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.CUSTOM: {
						control = this.createCustom(editObject);

						requestAnimationFrame(() => {
							const html = editObject.HTML || editObject.VALUE || null;

							if (html)
							{
								const res = BX.processHTML(html);

								res.SCRIPT.forEach((item) => {
									if (item.isInternal && item.JS)
									{
										BX.evalGlobal(item.JS);
									}
								});
							}
						});

						BX.bind(control, 'click', (event) => { event.stopPropagation();
						});
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					default: {
						break;
					}
				}
			}

			if (BX.type.isDomNode(span))
			{
				container.appendChild(span);
			}

			if (BX.type.isDomNode(control))
			{
				container.appendChild(control);
			}

			return container;
		},

		_onControlKeydown(event)
		{
			if (event.code === 'Enter')
			{
				event.preventDefault();

				const saveButton = BX.Grid.Utils.getBySelector(this.parent.getContainer(), '#grid_save_button > button', true);

				if (saveButton)
				{
					BX.fireEvent(saveButton, 'click');
				}
			}
		},
	};
})();
