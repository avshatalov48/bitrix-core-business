const isFunction = BX.Landing.Utils.isFunction;
const isString = BX.Landing.Utils.isString;
const isPlainObject = BX.Landing.Utils.isPlainObject;
const isArray = BX.Landing.Utils.isArray;
const bind = BX.Landing.Utils.bind;
const proxy = BX.Landing.Utils.proxy;
const data = BX.Landing.Utils.data;

export class Node
{
	constructor(options)
	{
		this.node = options.node;
		this.manifest = isPlainObject(options.manifest) ? options.manifest : {};
		this.selector = isString(options.selector) ? options.selector : '';
		this.onChangeHandler = isFunction(options.onChange) ? options.onChange : (function() {});
		this.onDesignShow = isFunction(options.onDesignShow) ? options.onDesignShow : (function() {});
		this.changeOptionsHandler = isFunction(options.onChangeOptions) ? options.onChangeOptions : (function() {});

		this.onDocumentClick = proxy(this.onDocumentClick, this);
		this.onDocumentKeydown = proxy(this.onDocumentKeydown, this);

		// Bind on document events
		bind(document, 'click', this.onDocumentClick);
		bind(document, 'keydown', this.onDocumentKeydown);

		// Make manifest as reed only
		Object.freeze(this.manifest);

		// Add selector attribute
		this.node.dataset.selector = this.selector;

		if (this.isAllowInlineEdit())
		{
			this.onAllowInlineEdit();
		}
	}

	/**
	 * Handles document click event
	 * @param {MouseEvent} event
	 */
	onDocumentClick(event)
	{}

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
	{}

	/**
	 * Gets field for editor form
	 * @abstract
	 * @return {?BX.Landing.UI.Field.BaseField}
	 */
	getField()
	{
		throw new Error('Must be implemented by subclass');
	}

	/**
	 * Shows node content editor
	 */
	showEditor()
	{}

	/**
	 * Hides node content editor
	 */
	hideEditor()
	{}

	/**
	 * Handles allow inline edit event
	 */
	onAllowInlineEdit()
	{}

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
		return typeof this.manifest.group === 'string' && this.manifest.group.length > 0;
	}

	/**
	 * Sets node value
	 * @abstract
	 * @param {*} value
	 * @param {?boolean} [preventSave = false]
	 * @param {?boolean} [preventHistory = false]
	 * @return void
	 */
	setValue(value, preventSave, preventHistory)
	{
		throw new Error('Must be implemented by subclass');
	}

	/**
	 * Gets value
	 * @abstract
	 * @return {string|object}
	 */
	getValue()
	{
		throw new Error('Must be implemented by subclass');
	}

	/**
	 * Gets additional values
	 * @return {*}
	 */
	getAdditionalValue()
	{
		if (
			isPlainObject(this.manifest.extend)
			&& isArray(this.manifest.extend.attrs)
		)
		{
			return this.manifest.extend.attrs.reduce((accumulator, key) => {
				return (accumulator[key] = data(this.node, key)), accumulator;
			}, {});
		}

		return {};
	}

	/**
	 * Handles content change event and calls external onChange handler
	 * @param {?boolean} [preventHistory = false]
	 */
	onChange(preventHistory)
	{
		this.onChangeHandler.apply(null, [this, preventHistory]);
	}

	/**
	 * Gets node index
	 * @return {int}
	 */
	getIndex()
	{
		let index = parseInt(this.selector.split('@')[1], 10);
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

	/**
	 * Prepare pseudo url if needed
	 * @param {object} url
	 * @return {null|object}
	 */
	preparePseudoUrl(url)
	{
		let urlIsChange = false;
		if (!(url.href === '#' && url.target === ''))
		{
			urlIsChange = true;
		}

		if (url.href === 'selectActions:')
		{
			url.href = '';
			url.enabled = false;
			urlIsChange = true;
		}

		if (url.href.startsWith('product:'))
		{
			url.target = '_self';
			urlIsChange = true;
		}

		if (url.enabled !== false && (url.href === '' || url.href === '#'))
		{
			url.enabled = false;
			urlIsChange = true;
		}

		if (url.target === '')
		{
			url.target = '_blank';
			urlIsChange = true;
		}

		if (urlIsChange === true)
		{
			return url;
		}

		return null;
	}
}

BX.Landing.Node = Node;
BX.Landing.Node.storage = [];
