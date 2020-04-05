;(function() {
	"use strict";

	BX.namespace("BX.Landing");


	/**
	 * Implements interface for simple access to objects of page
	 * Implements PageObject pattern
	 * @constructor
	 */
	BX.Landing.PageObject = function()
	{
		this.store = {};
		this.store.topPanel = null;
		this.store.designPanel = null;
		this.store.contentPanel = null;
		this.store.inlineEditor = null;
		this.store.contentPanelEditorPanel = null;
		this.store.linkEditorPanel = null;
		this.store.linkEditor = null;
		this.store.view = null;
	};


	/**
	 * Gets instance of Page object
	 * @static
	 * @returns {BX.Landing.PageObject}
	 */
	BX.Landing.PageObject.getInstance = function()
	{
		if (!window.top.BX.Landing.PageObject.instance && !BX.Landing.PageObject.instance)
		{
			window.top.BX.Landing.PageObject.instance = new BX.Landing.PageObject();
		}

		return (window.top.BX.Landing.PageObject.instance || BX.Landing.PageObject.instance);
	};


	/**
	 * @static
	 * @type {BX.Landing.PageObject}
	 */
	BX.Landing.PageObject.instance = null;


	BX.Landing.PageObject.prototype = {
		/**
		 * Gets top panel element
		 * @returns {Promise<HTMLElement|string>}
		 */
		top: function()
		{
			return new Promise(function(resolve, reject) {
				if (!this.store.topPanel)
				{
					this.store.topPanel = window.top.document.querySelector(".landing-ui-panel-top");
				}

				if (this.store.topPanel)
				{
					resolve(this.store.topPanel);
				}
				else
				{
					reject("Top panel unavailable");
					console.warn("Top panel unavailable");
				}
			}.bind(this));
		},


		/**
		 * Gets style panel instance
		 * @returns {Promise<BX.Landing.UI.Panel.StylePanel|string>}
		 */
		design: function()
		{
			return new Promise(function(resolve, reject) {
				if (!this.store.designPanel)
				{
					this.store.designPanel = BX.Landing.UI.Panel.StylePanel.getInstance();
				}

				if (this.store.designPanel)
				{
					resolve(this.store.designPanel);
				}
				else
				{
					reject("BX.Landing.UI.Panel.StylePanel unavailable");
					console.warn("BX.Landing.UI.Panel.StylePanel unavailable");
				}
			}.bind(this));
		},


		/**
		 * Gets content panel
		 * @returns {Promise<BX.Landing.UI.Panel.ContentEdit|string>}
		 */
		content: function()
		{
			return new Promise(function(resolve, reject) {
				if (!this.store.contentPanel)
				{
					this.store.contentPanel = BX.Landing.UI.Panel.ContentEdit.getInstance();
				}

				if (this.store.contentPanel)
				{
					resolve(this.store.contentPanel);
				}
				else
				{
					reject("BX.Landing.UI.Panel.ContentEdit unavailable");
					console.warn("BX.Landing.UI.Panel.ContentEdit unavailable");
				}
			}.bind(this));
		},


		/**
		 * Gets inline editor
		 * @returns {Promise<BX.Landing.UI.Panel.EditorPanel|string>}
		 */
		inlineEditor: function()
		{
			return new Promise(function(resolve, reject) {
				if (!this.store.inlineEditor)
				{
					this.store.inlineEditor = BX.Landing.UI.Panel.EditorPanel.getInstance();
				}

				if (this.store.inlineEditor)
				{
					resolve(this.store.inlineEditor);
				}
				else
				{
					reject("BX.Landing.UI.Panel.EditorPanel unavailable");
					console.warn("BX.Landing.UI.Panel.EditorPanel unavailable");
				}
			}.bind(this));
		},

		view: function()
		{
			return new Promise(function(resolve, reject) {
				if (!this.store.view)
				{
					this.store.view = window.top.document.querySelector(".landing-ui-view");
				}

				if (this.store.view)
				{
					resolve(this.store.view);
				}
				else
				{
					reject("View iframe unavailable");
					console.warn("View iframe unavailable");
				}
			}.bind(this));
		},

		blocks: function()
		{
			return new Promise(function(resolve) {
				if (!this.store.blocks)
				{
					this.store.blocks = top.BX.Landing.Block.storage;
				}

				if (this.store.blocks)
				{
					resolve(this.store.blocks);
				}
				else
				{
					reject("Blocks unavailable");
				}
			}.bind(this));
		}
	};


})();