;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


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
	BX.Landing.UI.Button.ColorAction = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.id = id;
		this.options = options;
		if (this.id !== 'tableBgColor')
		{
			this.layout.classList.add("landing-ui-button-editor-action-color");
		}
		this.colorPicker = new BX.Landing.UI.Tool.ColorPicker(this, this.onColorSelected.bind(this));
		BX.Landing.UI.Button.ColorAction.instances.push(this);
	};

	BX.Landing.UI.Button.ColorAction.instances = [];

	BX.Landing.UI.Button.ColorAction.hideAll = function()
	{
		BX.Landing.UI.Button.ColorAction.instances.forEach(function(button) {
			button.colorPicker.hide();
		});
	};

	BX.Landing.UI.Button.ColorAction.prototype = {
		constructor: BX.Landing.UI.Button.ColorAction,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,


		/**
		 * Handles event on this button click
		 * @param {MouseEvent} event
		 */
		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			var position = BX.Landing.UI.Panel.EditorPanel.getInstance().isFixed() ? "fixed" : "relative";

			if (!this.colorPicker.isShown())
			{
				this.colorPicker.show(position);
				BX.Landing.UI.Button.FontAction.hideAll();
				if (BX.Landing.UI.Button.ChangeTag.menu)
				{
					BX.Landing.UI.Button.ChangeTag.menu.close();
				}
			}
			else
			{
				this.colorPicker.hide();
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
			document.execCommand(this.id, false, color);
		},

		/**
		 * Apply selected color to text in table cells
		 * @param {string} color - Selected color
		 */
		applyColorInTableCells: function(color)
		{
			var setTd = Array.from(this.options.setTd);
			setTd.forEach(function(td) {
				if (td.nodeType === 1)
				{
					td.style.color = color;
				}
			})
			if (this.options.target === 'table')
			{
				this.options.table.setAttribute('text-color', color);
			}
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
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
			var setTd = Array.from(this.options.setTd);
			setTd.forEach(function(td) {
				if (td.nodeType === 1)
				{
					if (!td.classList.contains('landing-table-col-dnd')
						&& !td.classList.contains('landing-table-row-dnd')
						&& !td.classList.contains('landing-table-th-select-all'))
					{
						td.style.setProperty('background-color', color, 'important');
					}
				}
			})
			if (this.options.target === 'table')
			{
				this.options.table.setAttribute('bg-color', color);
			}
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		}
	};
})();