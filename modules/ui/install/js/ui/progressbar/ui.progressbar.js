(function() {

	'use strict';

	BX.namespace('BX.UI.ProgressBar');

	BX.UI.ProgressBar = function(options)
	{
		this.options = BX.type.isPlainObject(options) ? options : {};

		this.bar = null;
		this.container = null;
		this.status = null;
		this.statusPercent = "0%";
		this.statusCounter = "0 / 0";
		this.textBefore = null;
		this.textAfter = null;
		this.maxValue = 100;
		this.value = 0;
		this.statusType = BX.UI.ProgressBar.Status.PERCENT;
		this.color = BX.UI.ProgressBar.Color.PRIMARY;
		this.size = BX.UI.ProgressBar.Size.MEDIUM;

		this.setValue(options.value);
		this.setMaxValue(options.maxValue);
		this.setStatusType(options.statusType);
		this.setColor(options.color);
		this.setSize(options.size);
		this.setFill(options.fill);
		this.setColumn(options.column);
		this.setTextBefore(options.textBefore);
		this.setTextAfter(options.textAfter);
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.ProgressBar.Status = {
		COUNTER: "COUNTER",
		PERCENT: "PERCENT"
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.ProgressBar.Color = {
		DANGER: "ui-progressbar-danger",
		SUCCESS: "ui-progressbar-success",
		PRIMARY: "ui-progressbar-primary",
		WARNING: "ui-progressbar-warning"
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.ProgressBar.Size = {
		LARGE: "ui-progressbar-lg",
		MEDIUM: "ui-progressbar-md"
	};

	BX.UI.ProgressBar.prototype =
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
				this.value = value;
			}
		},

		getMaxValue: function()
		{
			return this.maxValue;
		},

		setMaxValue: function(value)
		{
			if (BX.type.isNumber(value))
			{
				this.maxValue = value;
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

		setFill: function(fill)
		{
			if (fill === true)
			{
				BX.addClass(this.getContainer(), "ui-progressbar-bg");
			}
			else
			{
				BX.removeClass(this.getContainer(), "ui-progressbar-bg");
			}
		},

		setColumn: function(column)
		{
			if (column === true)
			{
				BX.addClass(this.getContainer(), "ui-progressbar-column");
			}
			else
			{
				BX.removeClass(this.getContainer(), "ui-progressbar-column");
			}
		},

		//endregion

		//region Text
		getTextBefore: function()
		{
			if (this.textBefore === null)
			{
				this.textBefore = BX.create("div", {
					props: { className: "ui-progressbar-text-before" },
					html: this.options.textBefore
				});
			}

			return this.textBefore;
		},

		setTextBefore: function(text)
		{
			BX.adjust(this.textBefore, {
				html: text
			});
		},

		getTextAfter: function()
		{
			if (this.textAfter === null)
			{
				this.textAfter = BX.create("div", {
					props: { className: "ui-progressbar-text-after" },
					html: this.options.textAfter
				});
			}

			return this.textAfter;
		},

		setTextAfter: function(text)
		{
			BX.adjust(this.textAfter, {
				html: text
			});
		},

		//endregion

		// region Status
		getStatus: function()
		{
			if (this.status === null)
			{
				if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER)
				{
					this.status = BX.create("div", {
						props: { className: "ui-progressbar-status" },
						text: this.getStatusCounter()
					});
				}
				else
				{
					this.status = BX.create("div", {
						props: { className: "ui-progressbar-status-percent" },
						text: this.getStatusPercent()
					});
				}
			}

			return this.status;
		},

		getStatusPercent: function()
		{
			this.statusPercent = Math.round(this.getValue() / (this.getMaxValue() / 100));
			if (this.statusPercent > 100)
			{
				this.statusPercent = 100;
			}

			return this.statusPercent + "%";
		},

		getStatusCounter: function()
		{
			this.statusCounter = Math.round(this.getValue()) + " / " + Math.round(this.getMaxValue());
			if (Math.round(this.getValue()) > Math.round(this.getMaxValue()))
			{
				this.statusCounter = Math.round(this.getMaxValue()) + " / " + Math.round(this.getMaxValue());
			}

			return this.statusCounter;
		},

		setStatus: function()
		{
			if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER)
			{
				BX.adjust(this.status, {
					text: this.getStatusCounter()
				});
			}
			else
			{
				BX.adjust(this.status, {
					text: this.getStatusPercent()
				});
			}
		},

		getStatusType: function()
		{
			return this.statusType;
		},

		setStatusType: function(type)
		{
			if (BX.type.isNotEmptyString(type))
			{
				this.statusType = type;
			}
		},

		//endregion

		// region ProgressBar
		getBar: function()
		{
			if (this.bar === null)
			{
				this.bar = BX.create("div", {
					props: { className: "ui-progressbar-bar" },
					style: { width: this.getStatusPercent() }
				});
			}

			return this.bar;
		},

		update: function(value)
		{
			this.setValue(value);
			this.setStatus();

			if (this.bar === null)
			{
				this.getBar();
			}

			BX.adjust(this.bar, {
				style: { width: this.getStatusPercent() }
			});
		},

		//endregion

		getContainer: function()
		{
			if (this.container === null)
			{
				this.container = BX.create("div", {
					props: { className: "ui-progressbar" },
					children: [
						this.getTextAfter(),
						this.getTextBefore(),
						this.getStatus(),
						BX.create("div", {
							props: { className: "ui-progressbar-track" },
							children: [
								this.getBar()
							]
						})
					]
				});
			}

			return this.container;
		}
	};

})();