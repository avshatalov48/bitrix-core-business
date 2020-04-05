;(function() {
	'use strict';

	BX.namespace('BX.Filter');

	BX.Filter.FieldController = function(field, parent)
	{
		this.field = null;
		this.parent = null;
		this.type = null;
		this.input = null;
		this.deleteButton = null;
		this.init(field, parent);
	};

	BX.Filter.FieldController.prototype = {
		init: function(field, parent)
		{
			if (!BX.type.isDomNode(field))
			{
				throw 'BX.Filter.FieldController.init: field isn\'t dom node';
			}

			if (!(parent instanceof BX.Main.Filter))
			{
				throw 'BX.Filter.FieldController.init: parent not instance of BX.Main.ui.Filter';
			}

			this.field = field;
			this.parent = parent;
			this.bind();

			this.isShowDelete() ? this.showDelete() : this.hideDelete();
		},

		isShowDelete: function()
		{
			var squares = this.getSquares();
			return this.getInputValue() || (BX.type.isArray(squares) && squares.length);
		},

		getField: function()
		{
			return this.field;
		},

		getInput: function()
		{
			var type, types;

			if (!BX.type.isDomNode(this.input))
			{
				type = this.getType();
				types = this.parent.types;

				if (type === types.DATE)
				{
					this.input = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classDateInput);
				}

				if (type === types.NUMBER || type === 'number')
				{
					this.input = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classNumberInput);
				}

				if (type === types.STRING)
				{
					this.input = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classStringInput);
				}

				if (type === types.CUSTOM_ENTITY)
				{
					this.input = BX.Filter.Utils.getBySelector(this.getField(), 'input[type="hidden"]');
				}
			}

			return this.input;
		},

		getDeleteButton: function()
		{
			if (!BX.type.isDomNode(this.deleteButton))
			{
				this.deleteButton = BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classValueDelete);
			}

			return this.deleteButton;
		},

		getSquares: function()
		{
			return BX.Filter.Utils.getByClass(this.getField(), this.parent.settings.classSquare);
		},

		bind: function()
		{
			if (this.getType() !== this.parent.types.MULTI_SELECT && this.getType() !== this.parent.types.SELECT)
			{
				BX.bind(this.getDeleteButton(), 'click', BX.delegate(this._onDeleteClick, this));
				BX.bind(this.getInput(), 'input', BX.delegate(this._onInput, this));
			}
		},

		clearInput: function()
		{
			var input = this.getInput();

			if (BX.type.isDomNode(input))
			{
				input.value = '';
			}
		},

		hideDelete: function()
		{
			var deleteButton = this.getDeleteButton();

			if (BX.type.isDomNode(deleteButton))
			{
				BX.addClass(deleteButton, this.parent.settings.classHide);
			}
		},

		showDelete: function()
		{
			var deleteButton = this.getDeleteButton();

			if (BX.type.isDomNode(deleteButton))
			{
				BX.removeClass(deleteButton, this.parent.settings.classHide);
			}
		},

		removeSquares: function()
		{
			var squares = this.getSquares();

			if (BX.type.isArray(squares) && squares.length)
			{
				squares.forEach(function(square) {
					BX.remove(square);
				});
			}
		},

		_onDeleteClick: function()
		{
			this.removeSquares();
			this.clearInput();
			this.hideDelete();
		},

		_onInput: function()
		{
			this.getInputValue() ? this.showDelete() : this.hideDelete();
		},

		getInputValue: function()
		{
			var result = '';
			var input = this.getInput();

			if (BX.type.isDomNode(input))
			{
				result = input.value;
			}

			return result;
		},

		getType: function()
		{
			if (!BX.type.isNotEmptyString(this.type))
			{
				this.type = BX.data(this.getField(), 'type');
			}

			return this.type;
		}
	};

})();