import {Type} from 'main.core';

export class Node
{
	constructor()
	{
		this.isFunction = BX.Landing.Utils.isFunction;
		this.isString = BX.Landing.Utils.isString;
		this.isPlainObject = BX.Landing.Utils.isPlainObject;
		this.isArray = BX.Landing.Utils.isArray;
		this.bind = BX.Landing.Utils.bind;
		this.proxy = BX.Landing.Utils.proxy;
		this.data = BX.Landing.Utils.data;

		this.node = options.node;
		this.manifest = this.isPlainObject(options.manifest) ? options.manifest : {};
		this.selector = this.isString(options.selector) ? options.selector : "";
		this.onChangeHandler = this.isFunction(options.onChange) ? options.onChange : (function() {});
		this.onDesignShow = this.isFunction(options.onDesignShow) ? options.onDesignShow : (function() {});
		this.changeOptionsHandler = this.isFunction(options.onChangeOptions) ? options.onChangeOptions : (function() {});

		this.onDocumentClick = this.proxy(this.onDocumentClick, this);
		this.onDocumentKeydown = this.proxy(this.onDocumentKeydown, this);

		// Bind on document events
		this.bind(document, "click", this.onDocumentClick);
		this.bind(document, "keydown", this.onDocumentKeydown);

		// Make manifest as reed only
		Object.freeze(this.manifest);

		// Add selector attribute
		this.node.dataset.selector = this.selector;

		if (this.isAllowInlineEdit())
		{
			this.onAllowInlineEdit();
		}
	}

	onDocumentClick(event)
	{

	}

	/**
	 * Handles document keydown event
	 * @param {KeyboardEvent} event
	 */
	onDocumentKeydown(event)
	{
		if (event.keyCode === 27)
		{
			this.onEscapePress();
		}
	}

	/**
	 * Handles escape press event
	 */
	onEscapePress()
	{

	}

	/**
	 * Gets field for editor form
	 * @abstract
	 * @return {?BX.Landing.UI.Field.BaseField}
	 */
	getField()
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Shows node content editor
	 */
	showEditor()
	{

	}

	/**
	 * Hides node content editor
	 */
	hideEditor()
	{

	}

	/**
	 * Handles allow inline edit event
	 */
	onAllowInlineEdit()
	{

	}

	/**
	 * Checks that allow inline edit
	 * @return {boolean}
	 */
	isAllowInlineEdit()
	{
		return this.manifest.allowInlineEdit !== false;
	}

	/**
	 * Checks that this node is grouped
	 * @return {boolean}
	 */
	isGrouped()
	{
		return typeof this.manifest.group === "string" && this.manifest.group.length > 0;
	}

	/**
	 * Sets node value
	 * @abstract
	 * @param {*} value
	 * @param {?boolean} [preventSave = false]
	 * @param {?boolean} [preventHistory = false]
	 */
	setValue(value, preventSave, preventHistory)
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Gets value
	 * @abstract
	 * @return {string|object}
	 */
	getValue()
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Gets additional values
	 * @return {*}
	 */
	getAdditionalValue()
	{
		if (this.isPlainObject(this.manifest.extend) &&
			this.isArray(this.manifest.extend.attrs))
		{
			return this.manifest.extend.attrs.reduce(function(accumulator, key) {
				return (accumulator[key] = this.data(this.node, key)), accumulator;
			}.bind(this), {});
		}

		return {};
	}

	/**
	 * Handles content change event and calls external onChange handler
	 */
	onChange()
	{
		this.onChangeHandler.apply(null, [this]);
	}

	/**
	 * Gets node index
	 * @return {int}
	 */
	getIndex()
	{
		var index = parseInt(this.selector.split("@")[1]);
		index = index === index ? index : 0;
		return index;
	}

	/**
	 * Prevents save
	 * @param {boolean} value
	 */
	preventSave(value)
	{
		this.isSavePreventedValue = value;
	}

	/**
	 * Checks that save is prevented
	 * @return {boolean}
	 */
	isSavePrevented()
	{
		return !!this.isSavePreventedValue;
	}

	/**
	 * Gets current block
	 * @return {number|*}
	 */
	getBlock()
	{
		return BX.Landing.PageObject.getBlocks().getByChildNode(this.node);
	}
}