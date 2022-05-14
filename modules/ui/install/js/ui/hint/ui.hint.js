;(function ()
{
	"use strict";

	BX.namespace("BX.UI");
	if (BX.UI.Hint)
	{
		return;
	}

	/**
	 * Check is hovered element or not.
	 * @param {Object} cursorPosition
	 * @param {HTMLElement} element
	 * @returns
	 */
	var isHovered = function(cursorPosition, element) {
		var elementRect = element.getBoundingClientRect();
		var xMarginLeft = elementRect.x;
		var xMarginRight = elementRect.x + elementRect.width;
		var yMarginLeft = elementRect.y;
		var yMarginRight = elementRect.y + elementRect.height;

		return xMarginLeft <= cursorPosition.x && cursorPosition.x <= xMarginRight
			&& yMarginLeft <= cursorPosition.y && cursorPosition.y <= yMarginRight;
	};

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
		this.id = 'ui-hint-popup-' + (+new Date());
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
		BX.ready(this.initByClassName.bind(this));
	}
	Manager.prototype = {
		attributeName: 'data-hint',
		attributeHtmlName: 'data-hint-html',
		attributeInitName: 'data-hint-init',
		attributeInteractivityName: 'data-hint-interactivity',
		className: 'ui-hint',
		classNameIcon: 'ui-hint-icon',
		classNameContent: 'ui-hint-content',
		classNamePopup: 'ui-hint-popup',
		classNamePopupInteractivity: 'ui-hint-popup-interactivity',
		popup: null,
		content: null,
		popupParameters: null,
		ownerDocument: null,
		cursorPosition: {x:0, y:0},

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

			this.initOwnerDocument(context);
		},

		/**
		 * Init the owner document to track the cursor position.
		 * @param {HTMLElement} element
		 * @returns
		 */
		initOwnerDocument: function (element)
		{
			if (element.ownerDocument === this.ownerDocument)
			{
				return;
			}

			this.ownerDocument = element.ownerDocument;

			BX.bind(this.ownerDocument, 'mousemove', (e) => {
				this.cursorPosition.x = e.x;
				this.cursorPosition.y = e.y;
			});
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

			if (!node.hasAttribute(this.attributeHtmlName))
			{
				text = BX.util.htmlspecialchars(text);
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
			BX.bind(node, 'mouseleave', this.hide.bind(this, node));
		},

		/**
		 * Show hint window. Automatically calls on `mouseenter` event.
		 *
		 * @param {Element} anchorNode - Anchor node for popup with text.
		 * @param {string } html - Html of hint.
		 */
		show: function (anchorNode, html)
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

				if (typeof parameters.animation  === "undefined")
				{
					parameters.animation = "fading-slide";
				}

				if (typeof parameters.content === "undefined")
				{
					parameters.content = this.content;
				}

				if (typeof parameters.className === "undefined")
				{
					parameters.className = this.classNamePopup;
				}

				if (anchorNode.hasAttribute(this.attributeInteractivityName))
				{
					parameters.className += ' '+ this.classNamePopupInteractivity;
				}

				this.popup = new BX.PopupWindow(this.id, anchorNode, parameters);

				// register for working interactive mode
				BX.bind(this.popup.getPopupContainer(), 'mouseleave', () => this.popup.close());
			}

			this.content.innerHTML = html;
			this.popup.setBindElement(anchorNode);
			this.popup.show();
		},

		/**
		 * Hide hint window. Automatically calls on `mouseleave` event.
		 */
		hide: function (anchorNode)
		{
			if (!this.popup)
			{
				return;
			}

			if (anchorNode && anchorNode.hasAttribute(this.attributeInteractivityName))
			{
				// exit from flow with a short pause, so that when the mouse is moved diagonally, the popup does not disappear.
				setTimeout(() => {
					if (this.popup && !isHovered(this.cursorPosition, this.popup.getPopupContainer()))
					{
						this.popup.close();
					}
				}, 100);
			}
			else
			{
				this.popup.close();
			}
		}
	};

	BX.UI.Hint = new Manager();

})();