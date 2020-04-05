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
		this.onFrameLoad();
	};


	BX.Landing.UI.Style.prototype = {
		/**
		 * Handles iframe load event
		 */
		onFrameLoad: function()
		{
			if (!this.node)
			{
				this.node = this.getNode(true);
				this.currentTarget = this.node[0];
			}

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
			return window.localStorage.getItem("selectGroup") === "true";
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

			if (!!value && BX.type.isArray(items))
			{
				affect = !!affect ? affect : "";

				if (typeof value === "object")
				{
					if ("from" in value && "to" in value)
					{
						value.from += "-min";
						value.to += "-max";
					}

					var keys = Object.keys(value);
					value = keys.map(function(key) {
						return value[key];
					});
				}
				else
				{
					value = [value];
				}

				if (affect.length)
				{
					if (affect !== "background-image")
					{
						this.affects.add(affect);
					}
				}

				this.getNode().forEach(function(node) {
					value.forEach(function(valueItem) {
						items.forEach(function(item) {
							if (value.indexOf(item.value) === -1 &&
								value.indexOf(item.value+"-min") === -1 &&
								value.indexOf(item.value+"-max") === -1)
							{
								node.classList.remove(item.value);
								node.classList.remove(item.value+"-min");
								node.classList.remove(item.value+"-max");
							}
						});

						if (affect)
						{
							node.style[affect] = null;

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

						node.classList.add(valueItem);
					}, this);

					if (exclude)
					{
						exclude.items.forEach(function(item) {
							node.classList.remove(item.value);
						});
					}
				});
			}
		},


		/**
		 * Gets style node value
		 * @returns {{classList: string[], affect: ?string}}
		 */
		getValue: function()
		{
			return {
				classList: this.getNode().length ? this.getNode()[0].className.split(" ") : [],
				affect: this.affects.toArray()
			};
		}
	};
})();