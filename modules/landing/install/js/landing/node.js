;(function() {
	"use strict";

	BX.namespace("BX.Landing.Block");


	/**
	 * Implements base interface for works with block node
	 *
	 * @param {nodeOptions} options
	 *
	 * @property {HTMLElement} node
	 * @property {nodeManifest} manifest
	 * @property {string} selector - Node selector
	 *
	 * @constructor
	 */
	BX.Landing.Block.Node = function(options)
	{
		this.node = options.node;
		this.manifest = typeof options.manifest === "object" ? options.manifest : {};
		this.selector = typeof options.selector === "string" ? options.selector : "";
		this.onChangeHandler = typeof options.onChange === "function" ? options.onChange : (function() {});
		this.onDesignShow = typeof options.onDesignShow === "function" ? options.onDesignShow : (function() {});
		this.changeOptionsHandler = typeof options.onChangeOptions === "function" ? options.onChangeOptions : (function() {});

		this.onDocumentClick = this.onDocumentClick.bind(this);
		this.onDocumentKeydown = this.onDocumentKeydown.bind(this);

		// Bind on document events
		document.addEventListener("click", this.onDocumentClick);
		document.addEventListener("keydown", this.onDocumentKeydown);

		// Make manifest as reed only
		Object.freeze(this.manifest);

		// Add selector attribute
		this.node.dataset.selector = this.selector;

		if (this.isAllowInlineEdit())
		{
			this.onAllowInlineEdit();
		}
	};


	BX.Landing.Block.Node.storage = [];


	BX.Landing.Block.Node.prototype = {

		/**
		 * Handles document click event
		 * @param {MouseEvent} event
		 */
		onDocumentClick: function(event)
		{

		},


		/**
		 * Handles document keydown event
		 * @param {KeyboardEvent} event
		 */
		onDocumentKeydown: function(event)
		{
			if (event.keyCode === 27)
			{
				this.onEscapePress();
			}
		},


		/**
		 * Handles escape press event
		 */
		onEscapePress: function()
		{

		},


		/**
		 * Gets field for editor form
		 * @abstract
		 * @return {?BX.Landing.UI.Field.BaseField}
		 */
		getField: function()
		{
			throw new Error("Must be implemented by subclass");
		},


		/**
		 * Shows node content editor
		 */
		showEditor: function()
		{

		},


		/**
		 * Hides node content editor
		 */
		hideEditor: function()
		{

		},


		/**
		 * Handles allow inline edit event
		 */
		onAllowInlineEdit: function()
		{

		},


		/**
		 * Checks that allow inline edit
		 * @return {boolean}
		 */
		isAllowInlineEdit: function()
		{
			return this.manifest.allowInlineEdit !== false;
		},


		/**
		 * Checks that this node is grouped
		 * @return {boolean}
		 */
		isGrouped: function()
		{
			return typeof this.manifest.group === "string" && this.manifest.group.length > 0;
		},


		/**
		 * Sets node value
		 * @abstract
		 * @param {*} value
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			throw new Error("Must be implemented by subclass");
		},


		/**
		 * Gets value
		 * @abstract
		 * @return {string|object}
		 */
		getValue: function()
		{
			throw new Error("Must be implemented by subclass");
		},


		/**
		 * Handles content change event and calls external onChange handler
		 */
		onChange: function()
		{
			this.onChangeHandler.apply(null, [this]);
		},


		/**
		 * Gets node index
		 * @return {int}
		 */
		getIndex: function()
		{
			var index = parseInt(this.selector.split("@")[1]);
			index = index === index ? index : 0;
			return index;
		},


		/**
		 * Prevents save
		 * @param {boolean} value
		 */
		preventSave: function(value)
		{
			this.isSavePreventedValue = value;
		},


		/**
		 * Checks that save is prevented
		 * @return {boolean}
		 */
		isSavePrevented: function()
		{
			return !!this.isSavePreventedValue;
		}
	};
})();