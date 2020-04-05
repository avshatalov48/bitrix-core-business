;(function()
{
	"use strict";

	BX.namespace("BX.Landing.UI.Field");


	/**
	 * Implements interface for works with range field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Field.Range = function(data)
	{
		this.items = BX.type.isArray(data.items) ? data.items : [];
		this.values = new BX.Landing.Collection.BaseCollection();
		this.isMultiple = typeof data.type === "string" && data.type === "multiple";
		this.inputInner = this.createInputInner();
		this.container = BX.create("div", {props: {className: "landing-ui-field-range-container"}});
		this.output = this.createOutput();
		this.sliderFrom = null;
		this.sliderTo = this.createSlider();
		this.sliderValue = this.createValue();
		this.elements = [];
		this.frame = typeof data.frame === "object" ? data.frame : null;
		this.format = typeof data.format === "function" ? data.format : (function() {});
		this.postfix = typeof data.postfix === "string" ? data.postfix : "";
		this.changeHandler = typeof data.onChange === "function" ? data.onChange : (function() {});
		this.onValueChangeHandler = data.onValueChange ? data.onValueChange : (function() {});
		this.dragStartHandler = typeof data.onDragStart === "function" ? data.onDragStart : (function() {});
		this.dragEndHandler = typeof data.onDragEnd === "function" ? data.onDragEnd : (function() {});
		this.jsDD = this.frame ? window.top.jsDD : window.jsDD;
		this.value = null;
		this.valueFrom = null;
		this.valueTo = null;

		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		this.layout.classList.add("landing-ui-field-range");
		this.stepPercent = 100 /this.values.length;

		if ((this.content === null || this.content === undefined))
		{
			if (this.isMultiple)
			{
				this.content = {
					from: this.items[0].value,
					to: this.items[this.items.length-1].value
				}
			}
			else
			{
				this.content = this.items[0].value;
				this.value = this.items[0].value;
			}
		}

		this.setValue(this.content, true);
	};


	BX.Landing.UI.Field.Range.prototype = {
		constructor: BX.Landing.UI.Field.Range,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		init: function ()
		{
			this.input.appendChild(this.inputInner);
			this.input.appendChild(this.sliderValue);
			this.layout.appendChild(this.container);
			this.container.appendChild(this.input);

			if (!this.isMultiple)
			{
				this.container.appendChild(this.output);
			}

			if (this.isMultiple)
			{
				this.sliderFrom = this.createSlider();
				this.inputInner.appendChild(this.sliderFrom);
			}

			this.inputInner.appendChild(this.sliderTo);

			this.items.forEach(function(item, index) {
				this.values.add({
					value: item.value,
					valuePercent: this.valueToPercent(index),
					left: this.valueToLeftPercent(item.value),
					name: item.name
				});
			}, this);

			if (this.isMultiple)
			{
				this.sliderFrom.onbxdragstart = this.onDragStart.bind(this);
				this.sliderFrom.onbxdrag = this.onDrag.bind(this);
				this.sliderFrom.onbxdragstop = this.onDragEnd.bind(this);
				this.jsDD.registerObject(this.sliderFrom);
			}

			this.sliderTo.onbxdragstart = this.onDragStart.bind(this);
			this.sliderTo.onbxdrag = this.onDrag.bind(this);
			this.sliderTo.onbxdragstop = this.onDragEnd.bind(this);
			this.jsDD.registerObject(this.sliderTo);

			if (this.isMultiple)
			{
				requestAnimationFrame(function () {
					this.sliderFrom.style.transform = "translateX(-" + this.values[this.values.length-1].valuePercent + "%)";
					this.sliderFrom.style.left = this.values[this.values.length-1].valuePercent + "%";
				}.bind(this));
			}

			if (this.frame)
			{
				this.onFrameLoad();
			}
		},

		createOutput: function()
		{
			this.outputInput = BX.create("div", {props: {className: "landing-ui-field-range-output-input"}, text: "0"});
			return BX.create("div", {
				props: {className: "landing-ui-field-range-output"},
				children: [
					this.outputInput,
					BX.create("div", {
						props: {className: "landing-ui-field-range-output-arrows"},
						children: [
							BX.create("div", {
								props: {className: "landing-ui-field-range-output-arrows-up"},
								events: {
									click: this.onArrowUpClick.bind(this)
								}
							}),
							BX.create("div", {
								props: {className: "landing-ui-field-range-output-arrows-down"},
								events: {
									click: this.onArrowDownClick.bind(this)
								}
							})
						]
					})
				]
			});
		},

		onArrowUpClick: function()
		{
			var index = !!this.value ? this.values.length-1 : 0;
			var result;

			this.values.forEach(function(item, i) {
				if (item.value === this.value)
				{
					index = i;
				}
			}, this);

			result = this.values[index+1] ? this.values[index+1] : this.values[index];
			this.setValue(result.value);
		},

		onArrowDownClick: function()
		{
			var index = 0;
			var result;

			this.values.forEach(function(item, i) {
				// noinspection EqualityComparisonWithCoercionJS
				if (item.value == this.value)
				{
					index = i;
				}
			}, this);

			result = this.values[index-1] ? this.values[index-1] : this.values[index];
			this.setValue(result.value);
		},

		onFrameLoad: function ()
		{
			this.elements = [].slice.call(this.frame.document.querySelectorAll(this.selector));

			if (this.elements.length)
			{
				var element = this.elements[0];

				if (this.isMultiple)
				{
					var from = this.values.find(function(item) {
						return element.classList.contains(item.value);
					});

					var to = this.values.find(function(item) {
						return element.classList.contains(item.value) && item.value !== from;
					});

					this.setValue({
						from: !!from ? from.value : null,
						to: !!to ? to.value : null
					}, true);
				}
				else
				{
					var value = this.values.find(function(item) {
						return element.classList.contains(item.value);
					});

					this.setValue(!!value ? value.value : null, true);
				}
			}
		},

		getStartXOffset: function ()
		{
			var left = parseFloat(this.jsDD.current_node.style.left) * (this.inputInner.getBoundingClientRect().width / 100);
			return this.jsDD.current_node.getBoundingClientRect().left + window.pageXOffset - (left === left ? left : 0);
		},

		onDragStart: function ()
		{
			this.offset = this.getStartXOffset();
			this.dragStartHandler();
		},

		onDrag: function (x)
		{
			x = ((x - this.offset) / this.inputInner.getBoundingClientRect().width) * 100;
			x = x < 0 ? 0 : x > 100 ? 100 : x;
			var xx = x;

			var pip;
			var sliderLeft = parseFloat(this.jsDD.current_node.style.left);
			sliderLeft = sliderLeft === sliderLeft ? sliderLeft : 0;

			if (x > this.lastPos)
			{
				xx += (this.stepPercent / 2);
				pip = this.values.filter(function (item) {
					return xx >= item.valuePercent && item.valuePercent > sliderLeft;
				}, this);

				pip = pip[pip.length - 1];
			}
			else
			{
				xx -= (this.stepPercent / 2);
				pip = this.values.filter(function (item) {
					return xx <= item.valuePercent && item.valuePercent < sliderLeft;
				}, this);

				pip = pip[0];
			}


			if (pip)
			{
				if (this.isMultiple)
				{
					if (this.jsDD.current_node === this.sliderFrom)
					{
						this.valueFrom = pip.value;
					}

					if (this.jsDD.current_node === this.sliderTo)
					{
						this.valueTo = pip.value;
					}

					this.setValue({from: this.valueFrom, to: this.valueTo});
				}
				else
				{
					this.setValue(pip.value);
				}
			}

			this.lastPos = x;
		},

		onChange: function()
		{
			this.changeHandler(this.getValue(), this.items, this.postfix, this.property);
			this.onValueChangeHandler(this);
		},

		onDragEnd: function()
		{
			this.dragEndHandler();
		},


		getValue: function()
		{
			var result;

			if (this.isMultiple)
			{
				result = {
					from: this.toPercent < this.fromPercent ? this.valueTo : this.valueFrom,
					to: this.toPercent > this.fromPercent ? this.valueTo : this.valueFrom
				};
			}
			else
			{
				result = this.value;
			}

			return result;
		},

		setValue: function(value, preventEvent)
		{
			if (value && typeof value === "object")
			{
				var from = this.values.filter(function(item) {
					// noinspection EqualityComparisonWithCoercionJS
					return item.value == value.from;
				});

				var to = this.values.filter(function(item) {
					// noinspection EqualityComparisonWithCoercionJS
					return item.value == value.to;
				});

				from = from.length ? from[0] : this.values[0];
				to = to.length ? to[0] : this.values[this.values.length-1];

				if (from)
				{
					requestAnimationFrame(function () {
						this.sliderFrom.style.transform = "translateX(-" + from.valuePercent + "%)";
						this.sliderFrom.style.left = from.valuePercent + "%";
					}.bind(this));
					this.valueFrom = from.value;
					this.fromPercent = from.valuePercent;
				}

				if (to)
				{
					requestAnimationFrame(function () {
						this.sliderTo.style.transform = "translateX(-" + to.valuePercent + "%)";
						this.sliderTo.style.left = to.valuePercent + "%";
					}.bind(this));
					this.valueTo = to.value;
					this.toPercent = to.valuePercent;
				}

				this.updateValuePosition(from.valuePercent, to.valuePercent);
			}
			else if (value)
			{
				var result = this.values.filter(function(item) {
					// noinspection EqualityComparisonWithCoercionJS
					return item.value == value;
				});

				result = result.length ? result[0] : null;

				if (result)
				{
					requestAnimationFrame(function () {
						this.sliderTo.style.transform = "translateX(-" + result.valuePercent + "%)";
						this.sliderTo.style.left = result.valuePercent + "%";
					}.bind(this));
				}

				this.value = value;
				this.updateValuePosition(result.valuePercent);
			}

			if (!preventEvent)
			{
				this.onChange();
			}
		},


		updateValuePosition: function(from, to)
		{
			if (typeof from !== "number" && typeof to !== "number")
			{
				from = 0;
				to = 100;
			}

			if (typeof from === "number" && typeof to !== "number")
			{
				to = from;
				from = 0;
			}

			requestAnimationFrame(function() {
				this.sliderValue.style.left = Math.min(from, to) + "%";
				this.sliderValue.style.right = (100 - Math.max(from, to)) + "%";
			}.bind(this));

			var result = this.values.filter(function(item) {
				// noinspection EqualityComparisonWithCoercionJS
				return item.valuePercent == to;
			});

			result = result.length ? result[0] : null;

			this.outputInput.innerText = !!result ? result.name : 0;
		},


		createInputInner: function()
		{
			return BX.create("div", {props: {className: "landing-ui-field-range-input-inner"}});
		},

		createValue: function()
		{
			return BX.create("div", {props: {className: "landing-ui-field-range-value"}});
		},

		createSlider: function()
		{
			return BX.create("div", {props: {className: "landing-ui-field-range-input-slider"}});
		},

		createInput: function()
		{
			return BX.create("div", {props: {className: "landing-ui-field-input landing-ui-field-range-input"}});
		},

		/**
		 * @param value
		 * @return {number}
		 */
		valueToPercent: function(value)
		{
			return (value / (this.items.length-1)) * 100;
		},

		valueToLeftPercent: function(value)
		{
			return (value / this.inputInner.getBoundingClientRect().width) * 100;
		},

		/**
		 * Checks that value changed
		 * @return {boolean}
		 */
		isChanged: function()
		{
			if (this.isMultiple)
			{
				return JSON.stringify(this.content) !== JSON.stringify(this.getValue());
			}

			// noinspection EqualityComparisonWithCoercionJS
			return this.content != this.getValue();
		}
	};
})();