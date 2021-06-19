;(function() {
	'use strict';

	BX.namespace('BX.Main.ui');

	BX.Main.ui.CustomEntity = function()
	{
		this.field = null;
		this.labelInput = null;
		this.hiddenInput = null;
		this.popupContainer = null;
		this.inputClass = 'main-ui-control-string';
		this.squareClass = 'main-ui-square';
		this.multiple = null;
	};


	/**
	 * @static
	 * @param {HTMLElement} field
	 * @return {boolean}
	 */
	BX.Main.ui.CustomEntity.isMultiple = function(field)
	{
		if (!!field && !BX.hasClass(field, 'main-ui-control-entity'))
		{
			field = BX.Filter.Utils.getByClass(field, 'main-ui-control-entity');
		}

		return !!field && JSON.parse(BX.data(field, 'multiple'));
	};


	//noinspection JSUnusedGlobalSymbols
	BX.Main.ui.CustomEntity.prototype = {
		setField: function(field)
		{
			if (this.field !== field)
			{
				this.field = field;
				this.reset();
			}
		},

		isMultiple: function()
		{
			return BX.Main.ui.CustomEntity.isMultiple(this.getField());
		},

		reset: function()
		{
			this.labelInput = null;
			this.hiddenInput = null;
		},

		getField: function()
		{
			return this.field;
		},

		getId: function()
		{
			var hiddenNode = this.getHiddenNode();
			var id = null;

			if (BX.type.isDomNode(hiddenNode))
			{
				id = hiddenNode.name;
			}

			return id;
		},

		getLabelNode: function()
		{
			if (!BX.type.isDomNode(this.labelInput))
			{
				this.labelInput = BX.Filter.Utils.getBySelector(this.getField(), '.'+this.inputClass+'[type="text"]');
			}

			return this.labelInput;
		},

		getHiddenNode: function()
		{
			if (!BX.type.isDomNode(this.hiddenInput))
			{
				this.hiddenInput = BX.Filter.Utils.getBySelector(this.getField(), '.'+this.inputClass+'[type="hidden"]');
			}

			return this.hiddenInput;
		},

		getSquareByValue: function(value)
		{
			return BX.Filter.Utils.getBySelector(this.getField(), [
				'[data-item*=":'+BX.util.jsencode(value)+'}"]',
				'[data-item*=":\\"'+BX.util.jsencode(value)+'\\"}"]'
			].join(', '));
		},

		getSquares: function()
		{
			return BX.Filter.Utils.getByClass(this.getField(), this.squareClass, true);
		},

		removeSquares: function()
		{
			this.getSquares().forEach(BX.remove);
		},

		setSquare: function(label, value)
		{
			var field = this.getField();
			var squareData = {
				block: 'main-ui-square',
				name: label,
				item: {
					'_label': label,
					'_value': value
				}
			};
			var square = BX.decl(squareData);
			var squares = this.getSquares();

			if (!squares.length)
			{
				BX.prepend(square, field);
			}
			else
			{
				BX.insertAfter(square, squares[squares.length-1]);
			}
		},

		getCurrentValues: function()
		{
			var squares = this.getSquares();
			var data, result;
			if(this.isMultiple())
			{
				result = [];
				for(var i = 0, length = squares.length; i < length; i++)
				{
					try
					{
						data = JSON.parse(BX.data(squares[i], 'item'));
						result.push({ label: data._label, value: data._value });
					}
					catch (ex)
					{
					}
				}
			}
			else
			{
				if(squares.length === 0)
				{
					result = { label: '', value: '' };
				}
				else
				{
					try
					{
						data = JSON.parse(BX.data(squares[0], 'item'));
						result =  { label: data._label, value: data._value };
					}
					catch (ex)
					{
						result = { label: '', value: '' };
					}
				}
			}
			return result;
		},

		setData: function(label, value)
		{
			return this.isMultiple() ? this.setMultipleData(label, value) : this.setSingleData(label, value);
		},

		setSingleData: function(label, value)
		{
			var hiddenNode = this.getHiddenNode();
			this.removeSquares();
			this.setSquare(label, value);

			if (BX.type.isDomNode(hiddenNode))
			{
				hiddenNode.value = value;
				BX.fireEvent(hiddenNode, 'input');
			}
		},

		setMultipleData: function(items, value)
		{
			var values = [];
			var hiddenNode = this.getHiddenNode();

			if (BX.type.isArray(items))
			{
				this.removeSquares();

				if (BX.type.isArray(items))
				{
					items.forEach(function(item) {
						values.push(item.value);
						this.setSquare(item.label, item.value);
					}, this);

					if (BX.type.isDomNode(hiddenNode))
					{
						hiddenNode.value = JSON.stringify(values);
						BX.fireEvent(hiddenNode, 'input');
					}
				}
			}

			if (!BX.type.isArray(items) && value !== null)
			{
				if (!this.getSquareByValue(value))
				{
					this.setSquare(items, value);

					this.getSquares().forEach(function(square) {
						var squareData = JSON.parse(BX.data(square, 'item'));
						if (BX.type.isPlainObject(squareData))
						{
							values.push(squareData._value);
						}
					});

					hiddenNode.value = JSON.stringify(values);
					BX.fireEvent(hiddenNode, 'input');
				}
			}
		},

		clearValue: function()
		{
			this.removeSquares();

			var hiddenNode = this.getHiddenNode();
			hiddenNode.value = this.isMultiple() ? '[]': '';
		},

		setPopupContainer: function(container)
		{
			if (BX.type.isDomNode(container))
			{
				this.popupContainer = container;
			}
		},

		getPopupContainer: function()
		{
			return this.popupContainer;
		}
	};
})();