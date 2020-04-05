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
			var customControl = BX.create('div', {
				props: {
					className: className
				},
				attrs: {
					'data-name': editObject.NAME
				},
				html: editObject.HTML
			});
			customControl.querySelectorAll('input, select, checkbox, textarea').forEach(function(element) {
				switch (element.tagName)
				{
					case 'SELECT':
						element.value = '';
						if (element.multiple)
						{
							element.querySelectorAll('option').forEach(function(option) {
								option.selected = false;
								if (Array.isArray(editObject.VALUE))
								{
									if (BX.util.in_array(option.value, editObject.VALUE))
									{
										option.selected = true;
									}
								}
								else
								{
									if (option.value === editObject.VALUE)
									{
										option.selected = true;
									}
								}
							});
						}
						else
						{
							element.value = editObject.VALUE;
						}
						break;
					case 'INPUT':
						switch(element.type.toUpperCase())
						{
							case 'CHECKBOX':
								element.checked = (editObject.VALUE === '1' || editObject.VALUE === 'Y');
								break;
							case 'HIDDEN':
								break;
							default:
								element.value = editObject.VALUE;
						}
						break;
					default:
						element.value = editObject.VALUE;
				}
			});

			return customControl;
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
					'data-items': JSON.stringify(editObject.DATA.ITEMS),
					'data-value': valueItem.VALUE
				},
				children: [BX.create('span', {
					props: {className: 'main-dropdown-inner'},
					html: valueItem.NAME
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

					case this.types.CUSTOM : {
						control = this.createCustom(editObject);
						BX.bind(control, 'click', function(event) { event.stopPropagation(); });
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