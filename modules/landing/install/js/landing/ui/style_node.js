;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI");

	const slice = BX.Landing.Utils.slice;

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

	BX.Landing.UI.Style.SERVICE_CLASSES = [
		'landing-card',
		'slick-slide',
		'slick-current',
		'slick-active',
	];

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
			const elements = slice(this.iframe.document.querySelectorAll(this.relativeSelector));

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
			const currentValue = this.getValue();
			if (JSON.stringify(this.value) !== JSON.stringify(currentValue))
			{
				return true;
			}
			else
			{
				return this.lastValue !== undefined && JSON.stringify(this.lastValue) !== JSON.stringify(currentValue);
			}
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

			// recalculate value after changes
			this.value = this.getValue();
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

			// recalculate value after changes
			this.value = this.getValue();
		},

		/**
		 * Set list of properties for get computed styles
		 * @param {string} pseudo
		 */
		setPseudoElement: function(pseudo)
		{
			this.pseudoElement = pseudo;

			// recalculate value after changes
			this.value = this.getValue();
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
			this.lastValue = this.currentValue;
			this.currentValue = this.getValue();

			if(!value)
			{
				return;
			}

			affect = !!affect ? affect : "";
			if (affect.length)
			{
				this.setAffects(affect);
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
						slice(node.querySelectorAll("*")).forEach(function(child) {
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

		/**
		 *
		 * @param {string|[string]} affects
		 */
		setAffects(affects)
		{
			affects = BX.Type.isArray(affects) ? affects : [affects];
			affects.forEach(affect => {
				if (affect !== "background-image")
				{
					this.affects.add(affect);
				}
			})
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
			const node = this.getNode()[0] || null;
			const style = {};
			if (node)
			{
				let isAllInlineProps = false;
				let propValue = null;
				if (this.inlineProperties.length > 0)
				{
					isAllInlineProps = true;
					const styleObj = node.style;
					this.inlineProperties.forEach((prop) => {
						propValue = styleObj.getPropertyValue(prop).trim() || null;
						if (propValue !== null || prop === 'background-image')
						{
							style[prop] = propValue;
							if (prop === 'background-image' && Boolean(style[prop]))
							{
								style[prop] = style[prop].replaceAll('"', '\'');
							}
							isAllInlineProps = isAllInlineProps && Boolean(style[prop]);
						}

						if (propValue === null)
						{
							style[prop] = null;
						}
					});
				}

				if (Boolean(isNeedComputed) && this.computedProperties.length > 0 && !isAllInlineProps)
				{
					this.computedProperties.forEach((prop) => {
						propValue = getComputedStyle(node, this.pseudoElement).getPropertyValue(prop) || null;
						if (propValue !== null)
						{
							style[prop] = propValue;
						}
					});
				}
			}

			return {
				classList: node ? this.sanitizeClassList(slice(node.classList)) : [],
				affect: this.affects.toArray(),
				style,
			};
		},

		/**
		 * Remove service classes from class list
		 * @param {[string]} classes
		 * @returns {[string]} - array without service classes
		 */
		sanitizeClassList: function(classes)
		{
			const result = [];
			classes.forEach(classItem => {
				if (BX.Landing.UI.Style.SERVICE_CLASSES.indexOf(classItem) === -1)
				{
					result.push(classItem);
				}
			});

			return result;
		},

		/**
		 * Remove service classes from classes string
		 * @param {string} classes
		 * @returns {string} - classes string without service classes
		 */
		sanitizeClassName: function(classes)
		{
			return this.sanitizeClassList(classes.split(' ')).join(' ');
		},
	};
})();