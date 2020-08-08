(function() {

	'use strict';

	BX.namespace('BX.UI.Counter');

	BX.UI.Counter = function(options)
	{
		this.options = BX.type.isPlainObject(options) ? options : {};

		this.container = null;
		this.counterContainer = null;
		this.value = 0;
		this.animate = false;
		this.color = BX.UI.Counter.Color.DANGER;
		this.size = BX.UI.Counter.Size.MEDIUM;

		this.setValue(options.value);
		this.setColor(options.color);
		this.setSize(options.size);
		this.setAnimate(options.animate);
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.Counter.Color = {
		DANGER: "ui-counter-danger",
		SUCCESS: "ui-counter-success",
		PRIMARY: "ui-counter-primary",
		GRAY: "ui-counter-gray",
		LIGHT: "ui-counter-light",
		DARK: "ui-counter-dark"
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.Counter.Size = {
		LARGE: "ui-counter-lg",
		MEDIUM: "ui-counter-md"
	};

	BX.UI.Counter.prototype =
	{
		//region Parameters
		getValue: function()
		{
			return this.value;
		},

		setValue: function(value)
		{
			if (BX.type.isNumber(value))
			{
				this.value = (value < 0) ? 0 : value;
			}
		},

		setColor: function(color)
		{
			if (BX.type.isNotEmptyString(color))
			{
				BX.removeClass(this.getContainer(), this.color);
				this.color = color;
				BX.addClass(this.getContainer(), this.color);
			}
		},

		setSize: function(size)
		{
			if (BX.type.isNotEmptyString(size))
			{
				BX.removeClass(this.getContainer(), this.size);
				this.size = size;
				BX.addClass(this.getContainer(), this.size);
			}
		},

		setAnimate: function(animate)
		{
			this.animate = animate;
		},

		//endregion

		// region Counter
		update: function(value)
		{
			if (this.animate === true)
			{
				this.updateAnimated(value)
			}
			else
			{
				this.setValue(value);
				BX.adjust(this.counterContainer, {
					text: this.getValue()
				});
			}

		},

		updateAnimated: function(value)
		{
			if (value > this.getValue())
			{
				BX.addClass(this.counterContainer, "ui-counter-plus");
			}
			else
			{
				BX.addClass(this.counterContainer, "ui-counter-minus");
			}

			this.setValue(value);

			setTimeout(function()
				{
					BX.removeClass(this.counterContainer, "ui-counter-plus");
					BX.removeClass(this.counterContainer, "ui-counter-minus");
				}.bind(this),
				500);

			setTimeout(function()
				{
					BX.adjust(this.counterContainer, {
						text: this.getValue()
					});
				}.bind(this),
				250);
		},

		//endregion

		getCounterContainer: function()
		{
			if (this.counterContainer === null)
			{
				this.counterContainer = BX.create("div", {
					props: { className: "ui-counter-inner" },
					text: this.getValue()
				});
			}

			return this.counterContainer;
		},

		getContainer: function()
		{
			if (this.container === null)
			{
				this.container = BX.create("div", {
					props: { className: "ui-counter" },
					children: [this.getCounterContainer()]
				});
			}

			return this.container;
		}
	};

})();