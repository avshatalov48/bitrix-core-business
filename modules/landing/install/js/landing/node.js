;(function() {
	"use strict";

	BX.namespace("BX.Landing.Block");

	var isFunction = BX.Landing.Utils.isFunction;
	var isString = BX.Landing.Utils.isString;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isArray = BX.Landing.Utils.isArray;
	var bind = BX.Landing.Utils.bind;
	var proxy = BX.Landing.Utils.proxy;
	var data = BX.Landing.Utils.data;

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
		this.manifest = isPlainObject(options.manifest) ? options.manifest : {};
		this.selector = isString(options.selector) ? options.selector : "";
		this.onChangeHandler = isFunction(options.onChange) ? options.onChange : (function() {});
		this.onDesignShow = isFunction(options.onDesignShow) ? options.onDesignShow : (function() {});
		this.changeOptionsHandler = isFunction(options.onChangeOptions) ? options.onChangeOptions : (function() {});

		this.onDocumentClick = proxy(this.onDocumentClick, this);
		this.onDocumentKeydown = proxy(this.onDocumentKeydown, this);

		// Bind on document events
		bind(document, "click", this.onDocumentClick);
		bind(document, "keydown", this.onDocumentKeydown);

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
		 * Gets additional values
		 * @return {*}
		 */
		getAdditionalValue: function()
		{
			if (isPlainObject(this.manifest.extend) &&
				isArray(this.manifest.extend.attrs))
			{
				return this.manifest.extend.attrs.reduce(function(accumulator, key) {
					return (accumulator[key] = data(this.node, key)), accumulator;
				}.bind(this), {});
			}

			return {};
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
		},


		/**
		 * Gets current block
		 * @return {number|*}
		 */
		getBlock: function()
		{
			return BX.Landing.PageObject.getBlocks().getByChildNode(this.node);
		}
	};
})();