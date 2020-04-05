;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements concrete interface of editor action panel button
	 *
	 * @extends {BX.Landing.UI.Button.BaseButton}
	 *
	 * @param {?string} id - Action id for document.execCommand
	 * @param {?object} [options]
	 * @constructor
	 */
	BX.Landing.UI.Button.EditorAction = function(id, options)
	{
		BX.Landing.UI.Button.BaseButton.apply(this, arguments);
		this.layout.classList.add("landing-ui-button-editor-action");
		this.init();
	};


	BX.Landing.UI.Button.EditorAction.prototype = {
		constructor: BX.Landing.UI.Button.EditorAction,
		__proto__: BX.Landing.UI.Button.BaseButton.prototype,

		init: function()
		{
			this.on("click", this.onClick, this);
		},

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			document.execCommand(this.id);
			BX.Landing.UI.Tool.ColorPicker.hideAll();
		}
	};
})();