(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Button');

	/**
	 * Implements interface for works with color picker button
	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @param {string} id - Action id
	 * @param {?object} [options]
	 *
	 * @constructor
	 */
	BX.Landing.UI.Button.TableColorAction = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.id = id;
		this.options = options;
		if (this.id !== 'tableBgColor')
		{
			BX.Dom.addClass(this.layout, 'landing-ui-button-editor-action-color');
		}
		const pickerWindow = BX.Landing.UI.Panel.EditorPanel.getInstance().isOutOfFrame()
			? window.parent
			: window
		;
		this.colorPicker = new pickerWindow.BX.Landing.UI.Tool.ColorPicker(this, this.onColorSelected.bind(this));
		BX.Landing.UI.Button.TableColorAction.instances.push(this);
	};

	BX.Landing.UI.Button.TableColorAction.instances = [];

	BX.Landing.UI.Button.TableColorAction.hideAll = function()
	{
		BX.Landing.UI.Button.TableColorAction.instances.forEach((button) => {
			button.colorPicker.hide();
		});
	};

	BX.Landing.UI.Button.TableColorAction.prototype = {
		constructor: BX.Landing.UI.Button.TableColorAction,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		/**
		 * Handles event on this button click
		 * @param {MouseEvent} event
		 */
		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			const position = BX.Landing.UI.Panel.EditorPanel.getInstance().isFixed() ? 'fixed' : 'relative';

			if (this.colorPicker.isShown())
			{
				this.colorPicker.hide();
			}
			else
			{
				this.colorPicker.show(position);
				if (BX.Landing.UI.Button.ChangeTag.menu)
				{
					BX.Landing.UI.Button.ChangeTag.menu.close();
				}
			}
		},

		/**
		 * Handles event on color selected
		 * @param {string} color - Selected color
		 */
		onColorSelected: function(color)
		{
			if (this.id === 'tableTextColor')
			{
				this.applyColorInTableCells(color);
			}

			if (this.id === 'tableBgColor')
			{
				this.applyBgInTableCells(color);
			}
		},

		/**
		 * Apply selected color to text in table cells
		 * @param {string} color - Selected color
		 */
		applyColorInTableCells: function(color)
		{
			const setTd = [...this.options.setTd];
			setTd.forEach((td) => {
				if (td.nodeType === 1)
				{
					BX.Dom.style(td, 'color', color);
				}
			});
			if (this.options.target === 'table')
			{
				this.options.table.setAttribute('text-color', color);
			}
			BX.Landing.Node.Text.currentNode.onChange(true);
		},

		/**
		 * Apply selected text color when changed table style
		 * @param {string} color - Needed color for dark or light table style
		 * @param {object} options - All options
		 */
		prepareOptionsForApplyColorInTableCells: function(color, options)
		{
			this.options = options;
			this.applyColorInTableCells(color);
		},

		/**
		 * Apply selected background color to table cells
		 * @param {string} color - Selected color
		 */
		applyBgInTableCells: function(color)
		{
			const setTd = [...this.options.setTd];
			setTd.forEach((td) => {
				if (
					td.nodeType === 1
					&& !BX.Dom.hasClass(td, 'landing-table-col-dnd')
					&& !BX.Dom.hasClass(td, 'landing-table-row-dnd')
					&& !BX.Dom.hasClass(td, 'landing-table-th-select-all')
				)
				{
					BX.Dom.style(td, 'background-color', color);
				}
			});
			if (this.options.target === 'table')
			{
				this.options.table.setAttribute('bg-color', color);
			}
			BX.Landing.Node.Text.currentNode.onChange(true);
		},

		/**
		 * @param contextDocument document
		 */
		setContextDocument: function(contextDocument)
		{
			BX.Landing.UI.Button.EditorAction.prototype.setContextDocument.apply(this, arguments);
			this.colorPicker.setContextDocument(contextDocument);
		},
	};
})();
