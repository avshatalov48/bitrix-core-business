;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	BX.Landing.UI.Panel.SmallEditorPanel = function()
	{
		BX.Landing.UI.Panel.EditorPanel.apply(this, arguments);
		this.layout.classList.remove("landing-ui-hide");
		this.layout.classList.add("landing-ui-panel-small-content-edit");
	};


	/**
	 * Shows panel for text node
	 * @static
	 * @param {BX.Landing.Node} [node]
	 */
	BX.Landing.UI.Panel.SmallEditorPanel.show = function(node)
	{
		BX.Landing.UI.Panel.SmallEditorPanel.getInstance().show(node);
	};


	/**
	 * Hides panel
	 * @static
	 */
	BX.Landing.UI.Panel.SmallEditorPanel.hide = function()
	{
		BX.Landing.UI.Panel.SmallEditorPanel.getInstance().hide();
	};


	/**
	 * Stores instance of BX.Landing.UI.Panel.SmallEditorPanel
	 * @static
	 * @type {?BX.Landing.UI.Panel.SmallEditorPanel}
	 */
	BX.Landing.UI.Panel.SmallEditorPanel.instance = null;


	/**
	 * Gets instance on BX.Landing.UI.Panel.SmallEditorPanel
	 * @static
	 * @return {BX.Landing.UI.Panel.SmallEditorPanel}
	 */
	BX.Landing.UI.Panel.SmallEditorPanel.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.SmallEditorPanel.instance)
		{
			BX.Landing.UI.Panel.SmallEditorPanel.instance = new BX.Landing.UI.Panel.SmallEditorPanel();
		}

		return BX.Landing.UI.Panel.SmallEditorPanel.instance;
	};


	BX.Landing.UI.Panel.SmallEditorPanel.prototype = {
		constructor: BX.Landing.UI.Panel.SmallEditorPanel,
		__proto__: BX.Landing.UI.Panel.EditorPanel.prototype
	}
})();