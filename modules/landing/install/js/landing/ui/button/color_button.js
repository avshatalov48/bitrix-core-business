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
		const pickerWindow = BX.Landing.UI.Panel.EditorPanel.getInstance().isOutOfFrame()
			? window.parent
			: window
		;
		this.colorPicker = new pickerWindow.BX.Landing.UI.Tool.ColorPicker(this, this.onColorSelected.bind(this));
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
			this.contextDocument.execCommand(this.id, false, color);
		},

		/**
		 * @param Document document
		 */
		setContextDocument: function(contextDocument)
		{
			BX.Landing.UI.Button.EditorAction.prototype.setContextDocument.apply(this, arguments);
			this.colorPicker.setContextDocument(contextDocument);
		},
	};
})();