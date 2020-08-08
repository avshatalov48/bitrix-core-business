import {Event} from "main.core";
import {EventEmitter} from "main.core.events";

;(function() {
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
		this.init(parent, types);
	};

	BX.Grid.InlineEditor.prototype = {
		init: function(parent, types)
		{
			this.parent = parent;

			try {
				this.types = eval(types);
			} catch (err) {
				this.types = null;
			}
		},

		createContainer: function()
		{
			return BX.create('div', {
				props: {
					className: this.parent.settings.get('classEditorContainer')
				}
			});
		},

		createTextarea: function(editObject, height)
		{
			var textarea = BX.create('textarea', {
				props: {
					className: [
						this.parent.settings.get('classEditor'),
						this.parent.settings.get('classEditorTextarea')
					].join(' ')
				},
				attrs: {
					name: editObject.NAME,
					style: 'height:' + height + 'px'
				},
				html: editObject.VALUE
			});

			return textarea;
		},

		createInput: function(editObject)
		{
			var className = this.parent.settings.get('classEditorText');
			var attrs =
				{
					value: (editObject.VALUE !== undefined && editObject.VALUE !== null) ? BX.util.htmlspecialcharsback(editObject.VALUE) : '',
					name: (editObject.NAME !== undefined && editObject.NAME !== null) ? editObject.NAME : ''
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
					className: className,
					id: editObject.NAME + '_control'
				},
				attrs: attrs
			});
		},

		createCustom: function(editObject)
		{
			var className = this.parent.settings.get('classEditorCustom');
			className = [this.parent.settings.get('classEditor'), className].join(' ');

			return BX.create('div', {
				props: {
					className: className
				},
				attrs: {
					'data-name': editObject.NAME
				},
				html: editObject.VALUE || ""
			});
		},

		createMoney: function(editObject)
		{
			const value = editObject.VALUE;
			const fieldChildren = [];

			const priceObject = value.PRICE || {};
			fieldChildren.push(this.createMoneyPrice(priceObject));

			const currencyObject = value.CURRENCY || {};
			currencyObject.DATA = {
				ITEMS: editObject.CURRENCY_LIST
			};
			fieldChildren.push(this.createMoneyCurrency(currencyObject));

			if (BX.type.isNotEmptyObject(value.HIDDEN))
			{
				for (let fieldName in value.HIDDEN)
				{
					if (BX.type.isNotEmptyString(fieldName))
					{
						const hidden = this.createInput({
							NAME: fieldName,
							VALUE: value['HIDDEN'][fieldName],
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
					className: className
				},
				attrs: attrs,
				children: fieldChildren,
			});
		},

		createMoneyPrice: function(priceObject)
		{
			priceObject.TYPE = this.types.NUMBER;
			priceObject.PLACEHOLDER = "0";
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
					}
				};

				EventEmitter.emit('Grid.MoneyField::change', eventData);
			});
			return priceInput;
		},

		createMoneyCurrency: function(currencyObject)
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
					const priceField = fieldNode.querySelector('.main-grid-editor-money-price')
					const eventData = {
						field: fieldNode,
						values: {
							price: priceField.value || '',
							currency: dropdownObject.dropdown.dataset.value || '',
						}
					};

					EventEmitter.emit('Grid.MoneyField::change', eventData);
				}
			});

			return currencyBlock;
		},

		createOutput: function(editObject)
		{
			return BX.create('output', {
				props: {
					className: this.parent.settings.get('classEditorOutput') || ''
				},
				attrs: {
					for: editObject.NAME + '_control'
				},
				text: editObject.VALUE || ''
			});
		},

		getDropdownValueItemByValue: function(items, value)
		{
			var result = items.filter(function(current) {
				return current.VALUE === value;
			});

			return result.length > 0 ? result[0] : items[0];
		},

		createDropdown: function(editObject)
		{
			var valueItem = this.getDropdownValueItemByValue(
				editObject.DATA.ITEMS,
				editObject.VALUE
			);

			return BX.create('div', {
				props: {
					className: [
						this.parent.settings.get('classEditor'),
						'main-dropdown main-grid-editor-dropdown'
					].join(' '),
					id: editObject.NAME + '_control'
				},
				attrs: {
					name: editObject.NAME,
					tabindex: '0',
					'data-items': JSON.stringify(editObject.DATA.ITEMS),
					'data-value': valueItem.VALUE
				},
				children: [BX.create('span', {
					props: {className: 'main-dropdown-inner'},
					text: valueItem.NAME
				})]
			});

		},

		validateEditObject: function(editObject)
		{
			return (
				BX.type.isPlainObject(editObject) &&
				('TYPE' in editObject) &&
				('NAME' in editObject) &&
				('VALUE' in editObject) &&
				(!('items' in editObject) || (BX.type.isArray(editObject.items) && editObject.items.length))
			);
		},

		initCalendar: function(event)
		{
			BX.calendar({node: event.target, field: event.target});
		},

		bindOnRangeChange: function(control, output)
		{
			function bubble(control, output)
			{
				BX.html(output, control.value);

				var value = parseFloat(control.value);
				var max = parseFloat(control.getAttribute('max'));
				var min = parseFloat(control.getAttribute('min'));
				var thumbWidth = 16;
				var range = (max - min);
				var position = (((value - min) / range) * 100);
				var positionOffset = (Math.round(thumbWidth * position / 100) - (thumbWidth / 2));

				output.style.left = position + '%';
				output.style.marginLeft = -positionOffset + 'px';
			}

			setTimeout(function() {
				bubble(control, output);
			}, 0);

			BX.bind(control, 'input', function() {
				bubble(control, output);
			});
		},

		createImageEditor: function(editObject)
		{
			return (new BX.Grid.ImageField(this.parent, editObject)).getLayout();
		},

		getEditor: function(editObject, height)
		{
			var control, span;
			var container = this.createContainer();

			if (this.validateEditObject(editObject))
			{
				editObject.VALUE = editObject.VALUE === null ? '' : editObject.VALUE;

				switch (editObject.TYPE) {
					case this.types.TEXT : {
						control = this.createInput(editObject);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.DATE : {
						control = this.createInput(editObject);
						BX.bind(control, 'click', this.initCalendar);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.NUMBER : {
						control = this.createInput(editObject);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.RANGE : {
						control = this.createInput(editObject);
						span = this.createOutput(editObject);
						this.bindOnRangeChange(control, span);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.CHECKBOX : {
						control = this.createInput(editObject);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.TEXTAREA : {
						control = this.createTextarea(editObject, height);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.DROPDOWN : {
						control = this.createDropdown(editObject);
						break;
					}

					case this.types.IMAGE : {
						control = this.createImageEditor(editObject);
						break;
					}

					case this.types.MONEY : {
						control = this.createMoney(editObject);
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					case this.types.CUSTOM : {
						control = this.createCustom(editObject);

						requestAnimationFrame(function() {
							const html = editObject.HTML || editObject.VALUE || null;

							if (html)
							{
								const res = BX.processHTML(html);

								res.SCRIPT.forEach(function(item) {
									if (item.isInternal && item.JS)
									{
										BX.evalGlobal(item.JS);
									}
								})
							}
						});

						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
						BX.bind(control, 'keydown', BX.delegate(this._onControlKeydown, this));
						break;
					}

					default : {
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

		_onControlKeydown: function(event)
		{
			if (event.code === 'Enter')
			{
				event.stopPropagation();
				event.preventDefault();

				var saveButton = BX.Grid.Utils.getBySelector(this.parent.getContainer(), '#grid_save_button > button', true);

				if (saveButton)
				{
					BX.fireEvent(saveButton, 'click');
				}
			}
		}
	};
})();