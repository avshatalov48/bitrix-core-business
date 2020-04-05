;(function() {
	'use strict';

	BX.namespace('BX.Main.ui');

	/**
	 * Factory of UI custom UI controls
	 * @returns {BX.Main.ui.Factory|*}
	 * @constructor
	 */
	BX.Main.ui.Factory = function()
	{
		if (BX.Main.ui.Factory instanceof BX.Main.ui.Factory)
		{
			return BX.Main.ui.Factory;
		}

		this.data = [];
		this.classControl = 'main-ui-control';
		this.classSelect = 'main-ui-select';
		this.classMultiSelect = 'main-ui-multi-select';
		this.classDate = 'main-ui-date';
		this.maxEventPathDepth = 5;
		this.init();
	};

	BX.Main.ui.Factory.prototype = {
		/**
		 * @private
		 */
		init: function()
		{
			BX.bind(document, 'click', BX.delegate(this._onHandleEvent, this));
			document.addEventListener('focus', BX.delegate(this._onHandleEvent, this), true);
		},

		/**
		 * @param event
		 * @private
		 */
		_onHandleEvent: function(event)
		{
			this.prepareEvent(event);

			event.path.forEach(function(current, index) {
				if (!this.validateIteration(current, index))
				{
					return false;
				}

				if (this.isSelect(current) && !this.isControlInitialized(current))
				{
					var select = {node: current, instance: new BX.Main.ui.select(current)};
					this.data.push(select);
					select.instance._onControlClick(event);
				}

				if (this.isDate(current) && !this.isControlInitialized(current))
				{
					var date = {node: current, instance: new BX.Main.ui.date(current)};
					this.data.push(date);
				}
			}, this);
		},

		/**
		 * @param current
		 * @param index
		 * @returns {boolean|*}
		 * @private
		 */
		validateIteration: function(current, index)
		{
			return index <= this.maxEventPathDepth && BX.type.isDomNode(current);
		},

		/**
		 * Prepares event.path property
		 * @param event
		 * @private
		 */
		prepareEvent: function(event)
		{
			if (!('path' in event) || !event.path.length)
			{
				event.path = [event.target];

				var i = 0;
				var x;

				while ((x = event.path[i++].parentNode) !== null)
				{
					event.path.push(x);
				}
			}
		},

		/**
		 * Checks whether node of select or multiselect control
		 * @param {HtmlElement} node
		 * @returns {boolean}
		 * @public
		 */
		isSelect: function(node)
		{
			return BX.hasClass(node, this.classSelect) || BX.hasClass(node, this.classMultiSelect);
		},

		/**
		 * Checks whether node of control
		 * @param {HtmlElement} node
		 * @returns {boolean}
		 * @public
		 */
		isControl: function(node)
		{
			return BX.hasClass(node, this.classControl);
		},

		/**
		 * Checks whether node of date control
		 * @param {HtmlElement} node
		 * @returns {boolean}
		 * @public
		 */
		isDate: function(node)
		{
			return BX.hasClass(node, this.classDate);
		},

		/**
		 * Checks is any control initialized with the node
		 * @param node
		 * @returns {boolean}
		 * @private
		 */
		isControlInitialized: function(node)
		{
			return this.data.some(function(current) {
				return current.node === node;
			}, this);
		},

		/**
		 * Gets control class instance by node
		 * @param node
		 * @returns {object}
		 * @public
		 */
		get: function(node)
		{
			var filtered = this.data.filter(function(current) {
				return node === current.node;
			});

			return filtered.length > 0 ? filtered[0] : null;
		}
	};

	BX.Main.ui.Factory = new BX.Main.ui.Factory();
})();