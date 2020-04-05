;(function ()
{
	"use strict";

	BX.namespace("BX.UI");
	if (BX.UI.Hint)
	{
		return;
	}

	/**
	 * Hint manager.
	 *
	 * @param {object} [parameters] - Parameters.
	 * @param {string} [parameters.attributeName] - Name of hint attribute.
	 * @param {string} [parameters.attributeInitName] - Name of init hint attribute.
	 * @param {string} [parameters.classNameIcon]
	 * @param {string} [parameters.className]
	 * @param {Element} [parameters.content] - Content node for text of hint.
	 * @param {BX.PopupWindow} [parameters.popup] - Custom popup instance.
	 * @param {object} [parameters.popupParameters] - Parameters for standard popup.
	 * @constructor
	 */
	function Manager (parameters)
	{
		parameters = parameters || {};
		if (parameters.attributeName)
		{
			this.attributeName = parameters.attributeName;
		}
		if (parameters.classNameIcon)
		{
			this.classNameIcon = parameters.classNameIcon;
		}
		if (parameters.className)
		{
			this.className = parameters.className;
		}
		if (parameters.attributeInitName)
		{
			this.attributeInitName = parameters.attributeInitName;
		}
		if (parameters.content)
		{
			if (!BX.type.isDomNode(parameters.content))
			{
				throw new Error('Parameter `content` should be a DOM Node.');
			}
			this.content = parameters.content;
		}
		if (parameters.popup)
		{
			if (!(parameters.popup instanceof BX.PopupWindow))
			{
				throw new Error('Parameter `popup` should be an instance of BX.PopupWindow.');
			}
			this.popup = parameters.popup;
		}
		if (parameters.popupParameters)
		{
			this.popupParameters = parameters.popupParameters;
		}

		this.initByClassName();
	}
	Manager.prototype = {
		attributeName: 'data-hint',
		attributeInitName: 'data-hint-init',
		className: 'ui-hint',
		classNameIcon: 'ui-hint-icon',
		classNameContent: 'ui-hint-content',
		popup: null,
		content: null,
		popupParameters: null,

		/**
		 * Create instance of manager. Use for customization purposes.
		 *
		 * @param {object} [parameters] - Parameters.
		 * @returns {Manager}
		 */
		createInstance: function (parameters)
		{
			return new Manager(parameters);
		},

		/**
		 * Init all hints founded by class name on document.
		 */
		initByClassName: function ()
		{
			var nodes = document.getElementsByClassName(this.className);
			nodes = BX.convert.nodeListToArray(nodes);
			nodes.forEach(this.initNode, this);
		},

		/**
		 * Init hints that was found in context. Use for mass initialization of hints.
		 *
		 * @param {HTMLElement} [context] - Context.
		 */
		init: function (context)
		{
			context = context || document.body;
			var nodes = context.querySelectorAll('[' + this.attributeName + ']');
			nodes = BX.convert.nodeListToArray(nodes);
			nodes.forEach(this.initNode, this);
		},

		/**
		 * Create hint node. Use in js-generated layout.
		 * Return node with styles, icon and bound event handlers.
		 *
		 * @param {string} text - Text of hint.
		 * @returns {Element|*}
		 */
		createNode: function (text)
		{
			var node = document.createElement('span');
			node.setAttribute(this.attributeName, text);
			this.initNode(node);

			return node;
		},

		/**
		 * Init hind node. Add styles, icon, event handlers.
		 *
		 * @param {Element} node - Element node of hint.
		 */
		initNode: function (node)
		{
			if (node.getAttribute(this.attributeInitName))
			{
				return;
			}

			node.setAttribute(this.attributeInitName, 'y');

			var text = node.getAttribute(this.attributeName);
			if (!BX.type.isNotEmptyString(text))
			{
				return;
			}

			if (!node.hasAttribute('data-hint-no-icon'))
			{
				BX.addClass(node, this.className);
				node.innerHTML = '';

				var iconNode = document.createElement('span');
				BX.addClass(iconNode, this.classNameIcon);
				node.appendChild(iconNode);
			}

			BX.bind(node, 'mouseenter', this.show.bind(this, node, text));
			BX.bind(node, 'mouseleave', this.hide.bind(this));
		},

		/**
		 * Show hint window. Automatically calls on `mouseenter` event.
		 *
		 * @param {Element} anchorNode - Anchor node for popup with text.
		 * @param {string } text - Text of hint.
		 */
		show: function (anchorNode, text)
		{
			if (!this.content)
			{
				this.content= document.createElement('div');
				BX.addClass(this.content, this.classNameContent);
			}

			if (!this.popup)
			{
				var parameters = this.popupParameters || {};
				if (typeof parameters.zIndex  === "undefined")
				{
					parameters.zIndex = 1000;
				}
				if (typeof parameters.darkMode  === "undefined")
				{
					parameters.darkMode = true;
				}
				if (typeof parameters.animationOptions  === "undefined")
				{
					/*
					// bug with fast hide/show
					parameters.animationOptions = {
						show: {
							type: "opacity"
						},
						close: {
							type: "opacity"
						}
					};
					*/
				}
				if (typeof parameters.content  === "undefined")
				{
					parameters.content = this.content;
				}

				this.popup = new BX.PopupWindow('ui-hint-popup', anchorNode, parameters);
			}

			this.content.innerHTML = text;
			this.popup.setBindElement(anchorNode);
			this.popup.show();
		},

		/**
		 * Hide hint window. Automatically calls on `mouseleave` event.
		 */
		hide: function ()
		{
			if (!this.popup)
			{
				return;
			}

			this.popup.close();
		}
	};

	BX.UI.Hint = new Manager();

})();