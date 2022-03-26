;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI");


	var isString = BX.Landing.Utils.isString;

	/**
	 * Implements interface for works with style node
	 *
	 * @param {{
	 * 		id: [string],
	 * 		node: HTMLElement|HTMLElement[],
	 * 		selector: string,
	 * 		relativeSelector: string,
	 * 		property: string,
	 * 		[onClick]: function,
	 * 		iframe: HTMLIFrameElement|Window
	 * }} options
	 *
	 * @constructor
	 */
	BX.Landing.UI.Style = function(options)
	{
		this.node = "node" in options ? options.node : null;
		this.id = "id" in options ? options.id : null;
		this.selector = "selector" in options ? options.selector : null;
		this.relativeSelector = "relativeSelector" ? options.relativeSelector : null;
		this.clickHandler = "onClick" in options ? options.onClick : (function() {});
		this.iframe = "iframe" in options ? options.iframe : null;
		this.affects = new BX.Landing.Collection.BaseCollection();
		this.inlineProperties = [];
		this.computedProperties = [];
		this.pseudoElement = null;
		this.isSelectGroupFlag = null;
		this.onFrameLoad();
	};


	BX.Landing.UI.Style.prototype = {
		/**
		 * Handles iframe load event
		 */
		onFrameLoad: function()
		{
			if (this.node)
			{
				this.node = BX.type.isArray(this.node) ? this.node : [this.node];
			}
			else
			{
				this.node = this.getNode(true);
			}
			this.currentTarget = this.node[0];

			this.node.forEach(function (node) {
				node.addEventListener("click", this.onClick.bind(this));
				node.addEventListener("mouseover", this.onMouseEnter.bind(this));
				node.addEventListener("mouseleave", this.onMouseLeave.bind(this));
			}, this);

			this.value = this.getValue();
		},


		getNode: function(all)
		{
			var elements = [].slice.call(this.iframe.document.querySelectorAll(this.relativeSelector));

			if (this.isSelectGroup() || all)
			{
				return elements;
			}

			return this.currentTarget ? [elements[this.getElementIndex(this.currentTarget)]] : [];
		},

		getTargetElement: function()
		{
			return this.currentTarget;
		},

		getElementIndex: function(element)
		{
			return [].indexOf.call(this.getNode(true), element);
		},


		/**
		 * Handles node click event
		 * @param {MouseEvent} event
		 */
		onClick: function(event)
		{
			if (BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				event.preventDefault();
				event.stopPropagation();
				this.currentTarget = event.currentTarget;
				this.clickHandler();
			}
		},


		/**
		 * Handles mouse enter event on node
		 * @param {MouseEvent} event
		 */
		onMouseEnter: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.highlight(event.currentTarget);
		},


		/**
		 * Handles mouse leave event on node
		 * @param {MouseEvent} event
		 */
		onMouseLeave: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			this.unHighlight();

			var node = BX.type.isArray(this.node) && this.node.length ? this.node[0] : this.node;
			BX.fireEvent(node.parentNode, "mouseenter");
		},

		isSelectGroup: function()
		{
			if (this.isSelectGroupFlag !== null)
			{
				return this.isSelectGroupFlag;
			}

			return window.localStorage.getItem("selectGroup") === "true";
		},

		/**
		 * Forced set isSelectGroup flag just for current style node
 		 * @param value
		 */
		setIsSelectGroup: function(value)
		{
			this.isSelectGroupFlag = !!value;
		},

		/**
		 * Unset force set isSelectGroup flag
		 */
		unsetIsSelectGroupFlag: function()
		{
			this.isSelectGroupFlag = null;
		},

		/**
		 * Highlights this node
		 * @param {HTMLElement|Node} target
		 */
		highlight: function(target)
		{
			if (BX.Landing.UI.Panel.StylePanel.getInstance().isShown())
			{
				if (this.isSelectGroup())
				{
					target = this.node;
				}

				BX.Landing.UI.Highlight.getInstance().show(target);
			}
		},


		/**
		 * Hides highlight for this node
		 */
		unHighlight: function()
		{
			BX.Landing.UI.Highlight.getInstance().hide();
		},


		/**
		 * Checks that this node style is changed
		 * @returns {*}
		 */
		isChanged: function()
		{
			return JSON.stringify(this.value) !== JSON.stringify(this.getValue());
		},

		/**
		 * Set list of used inline properties for get them in value
		 * @param {string|array} property
		 */
		setInlineProperty: function (property)
		{
			if (!BX.Type.isArray(property))
			{
				property = [property];
			}
			property.forEach(function (prop)
			{
				if (this.inlineProperties.indexOf(prop) === -1)
				{
					this.inlineProperties.push(prop);
				}
			}, this);
		},

		/**
		 * Set list of properties for get computed styles
		 * @param {string|array} property
		 */
		setComputedProperty: function (property)
		{
			if (!BX.Type.isArray(property))
			{
				property = [property];
			}
			property.forEach(function (prop)
			{
				if (this.computedProperties.indexOf(prop) === -1)
				{
					this.computedProperties.push(prop);
				}
			}, this);
		},

		/**
		 * Set list of properties for get computed styles
		 * @param {string} pseudo
		 */
		setPseudoElement: function(pseudo)
		{
			this.pseudoElement = pseudo;
		},

		/**
		 * Sets node value
		 * @param {string|object.<string>} value className
		 * @param {object[]} items
		 * @param {string} postfix
		 * @param {string} affect
		 * @param {object} [exclude]
		 */
		setValue: function(value, items, postfix, affect, exclude)
		{
			this.lastValue = this.lastValue || this.getValue();

			if(!value)
			{
				return;
			}

			affect = !!affect ? affect : "";
			if (affect.length)
			{
				if (affect !== "background-image")
				{
					this.affects.add(affect);
				}
			}

			if (BX.type.isObjectLike(value))
			{
				if ("from" in value && "to" in value)
				{
					value.from += "-min";
					value.to += "-max";
				}

				if(!("style" in value))
				{
					var keys = Object.keys(value);
					value = keys.map(function(key) {
						return value[key];
					});
				}
			}
			else
			{
				value = [value];
			}

			this.getNode().forEach(function(node) {
				if (BX.type.isArray(value))
				{
					this.setValueClass(node, value, items, affect);
				}
				if (BX.type.isObjectLike(value))
				{
					// todo: need min max?
					if("style" in value)
					{
						this.setValueStyle(node, value.style);
					}

					if (
						"className" in value
						&& BX.type.isArray(value.className)
					)
					{
						this.setValueClass(node, value.className, items, affect);
					}
				}

				if (affect)
				{
					if (affect !== "background-image")
					{
						[].slice.call(node.querySelectorAll("*")).forEach(function(child) {
							child.style[affect] = null;
							if (affect === "color")
							{
								child.removeAttribute("color");
							}
						});
					}
				}

				if (exclude)
				{
					exclude.items.forEach(function(item)
					{
						node.classList.remove(item.value);
					});
				}
			}, this);
		},

		setValueClass: function(node, value, items, affect) {
			if (BX.type.isArray(items))
			{
				node.style[affect] = null;

				value.forEach(function(valueItem)
				{
					items.forEach(function(item)
					{
						if (value.indexOf(item.value) === -1 &&
							value.indexOf(item.value + "-min") === -1 &&
							value.indexOf(item.value + "-max") === -1)
						{
							node.classList.remove(item.value);
							node.classList.remove(item.value + "-min");
							node.classList.remove(item.value + "-max");
						}
					});

					node.classList.add(valueItem);
				});
			}
		},

		setValueStyle: function (node, style)
		{
			this.inlineProperties.forEach(function (prop)
			{
				if (prop in style)
				{
					node.style.setProperty(prop, style[prop]);
				}
			});
		},

		/**
		 * Gets style node value
		 * @param isNeedComputed boolean - true if need match computed styles
		 * @returns {{classList: string[], affect: ?string, style: ?{string: string}}}
		 */
		getValue: function(isNeedComputed)
		{
			var node = this.getNode().length ? this.getNode()[0] : null;
			if (node)
			{
				var style = {};
				var isAllInlineProps = false;
				if (this.inlineProperties.length)
				{
					isAllInlineProps = true;
					var styleObj = node.style;
					this.inlineProperties.forEach(function (prop) {
						style[prop] = styleObj.getPropertyValue(prop).trim() || null;
						isAllInlineProps = isAllInlineProps && !!style[prop];
					});
				}
				if (!!isNeedComputed && this.computedProperties.length && !isAllInlineProps)
				{
					this.computedProperties.forEach(function (prop) {
						style[prop] =
							getComputedStyle(node, this.pseudoElement).getPropertyValue(prop)
							|| null;
					}.bind(this));
				}
			}
			return {
				classList: node ? node.className.split(" ") : [],
				affect: this.affects.toArray(),
				style: style || {},
			};
		},

		/**
		 * Gets style in special format for save to history entry
		 */
		getValueForHistory: function ()
		{
			var value = {className: "", style: ""};

			if (this.node[0])
			{
				value.className = this.node[0].className;
				value.style = this.node[0].style.cssText;
			}

			return value;
		}
	};
})();