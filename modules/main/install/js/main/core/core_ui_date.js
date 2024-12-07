;(function() {
	'use strict';

	BX.namespace('BX.Main.ui');
	BX.namespace('BX.Main.ui.block');

	BX.Main.ui.date = function(node) {
		this.node = null;
		this.classControl = 'main-ui-date';
		this.classButton = 'main-ui-date-button';
		this.classInput = 'main-ui-date-input';
		this.classValueDelete = 'main-ui-control-value-delete';
		this.classHide = 'main-ui-hide';
		this.button = null;
		this.input = null;
		this.valueDeleteButton = null;
		this.enableTime = false;
		this.datePicker = null;
		this.init(node);
	};

	BX.Main.ui.date.prototype = {
		init: function(node)
		{
			if (BX.type.isDomNode(node) && BX.hasClass(node, this.classControl))
			{
				this.node = node;
				this.enableTime = (BX.data(this.getInput(), 'time') == 'true');

				BX.bind(this.getButton(), 'click', () => {
					this.getDatePicker().show();
				});
				BX.bind(this.getInput(), 'input', BX.delegate(this._onInputInput, this));
				// BX.bind(this.getInput(), 'focus', BX.delegate(this._onInputFocus, this));
				BX.bind(this.getInput(), 'focusout', BX.delegate(this._onInputFocusOut, this));
				BX.bind(this.getInput(), 'click', BX.delegate(this._onInputClick, this));
				BX.bind(this.getValueDeleteButton(), 'click', BX.delegate(this._onValueDeleteButtonClick, this));
				this.controlDeleteButton();
			}
		},

		_onInputInput: function()
		{
			this.controlDeleteButton();
		},

		controlDeleteButton: function()
		{
			if (this.getInput().value.length)
			{
				BX.removeClass(this.getValueDeleteButton(), this.classHide);
			}
			else
			{
				BX.addClass(this.getValueDeleteButton(), this.classHide);
			}
		},

		_onInputFocus: function(event)
		{
			this.isFocus = true;
			this.eventTimestamp = event.timeStamp;
			this.getDatePicker().show();
		},

		_onInputFocusOut: function(event)
		{
			if (!this.getDatePicker().getContainer().contains(event.relatedTarget))
			{
				this.getDatePicker().hide();
			}
		},

		_onInputClick: function(event)
		{
			this.getDatePicker().show();

			event.preventDefault();
			event.stopPropagation();

			const focusTime = this.eventTimestamp;
			const clickTime = event.timeStamp;
			if (!this.isFocus || (clickTime - focusTime) > 1000)
			{
				this.getDatePicker().show();
			}

			this.isFocus = false;
		},

		_onValueDeleteButtonClick: function(event)
		{
			var target = event.currentTarget;
			var input;

			if (BX.type.isDomNode(target))
			{
				input = this.getInput();

				if (BX.type.isDomNode(input))
				{
					input.value = '';
					this.controlDeleteButton();
				}
			}
		},

		getValueDeleteButton: function()
		{
			if (!BX.type.isDomNode(this.valueDeleteButton))
			{
				this.valueDeleteButton = BX.findChild(this.getNode(), {className: this.classValueDelete}, true, false);
			}

			return this.valueDeleteButton;
		},

		getDatePicker()
		{
			if (this.datePicker === null)
			{
				const input = this.getInput();
				const button = this.getButton();
				this.datePicker = new BX.UI.DatePicker.DatePicker({
					targetNode: button,
					inputField: input,
					enableTime: this.enableTime,
					autoFocus: false,
					autoHide: true,
					useInputEvents: false,
					events: {
						onHide: () => {
							this.controlDeleteButton();
						},
						// onSelectChange: () => {
						// 	if (!this.enableTime)
						// 	{
						// 		setTimeout(() => { // a workaround for the focus event handler
						// 			this.datePicker.hide();
						// 		}, 0);
						// 	}
						// },
					},
				});
			}

			return this.datePicker;
		},

		getNode: function()
		{
			return this.node;
		},

		getButton: function()
		{
			if (!BX.type.isDomNode(this.button))
			{
				this.button = BX.findChild(this.getNode(), {class: this.classButton}, true, false);
			}

			return this.button;
		},

		getInput: function()
		{
			if (!BX.type.isDomNode(this.input))
			{
				this.input = BX.findChild(this.getNode(), {class: this.classInput}, true, false);
			}

			return this.input;
		}
	};


	BX.Main.ui.block['main-ui-date'] = function(data)
	{
		var control, calendarButton, input, valueDelete;

		control = {
			block: 'main-ui-date',
			mix: ['main-ui-control'],
			content: []
		};

		if ('mix' in data && BX.type.isArray(data.mix))
		{
			data.mix.forEach(function(current) {
				control.mix.push(current);
			});
		}

		if ('calendarButton' in data && data.calendarButton === true && (!('type' in data) || 'type' in data && data.type !== 'hidden'))
		{
			calendarButton = {
				block: 'main-ui-date-button',
				tag: 'span',
				attrs: {
					tabindex: '0',
				},
			};

			control.content.push(calendarButton);
		}

		input = {
			block: 'main-ui-date-input',
			mix: ['main-ui-control-input'],
			tag: 'input',
			attrs: {
				type: 'type' in data ? data.type : 'text',
				name: 'name' in data ? data.name : '',
				tabindex: 'tabindex' in data ? data.tabindex : '',
				value: 'value' in data ? data.value : '',
				placeholder: 'placeholder' in data ? data.placeholder : '',
				autocomplete: 'off',
				'data-time': data.enableTime
			}
		};


		control.content.push(input);

		if ('valueDelete' in data && data.valueDelete === true && (!('type' in data) || 'type' in data && data.type !== 'hidden'))
		{
			valueDelete = {
				block: 'main-ui-control-value-delete',
				mix: ['main-ui-hide'],
				content: {
					block: 'main-ui-control-value-delete-item',
					tag: 'span'
				}
			};

			control.content.push(valueDelete);
		}

		if (input.attrs.type === 'hidden')
		{
			control = input;
		}

		return control;
	};

})();
