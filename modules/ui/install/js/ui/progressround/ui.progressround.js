(function() {

	'use strict';

	BX.namespace('BX.UI.ProgressRound');

	BX.UI.ProgressRound = function(options)
	{
		this.options = BX.type.isPlainObject(options) ? options : {};

		this.bar = null;
		this.container = null;
		this.status = null;
		this.statusPercent = "0%";
		this.statusCounter = "0 / 0";
		this.textBefore = null;
		this.textBeforeContainer = null;
		this.textAfter = null;
		this.textAfterContainer = null;
		this.maxValue = 100;
		this.value = 0;
		this.width = 100;
		this.lineSize = 5;
		this.statusType = BX.UI.ProgressRound.Status.NONE;
		this.color = BX.UI.ProgressRound.Color.PRIMARY;

		this.setValue(options.value);
		this.setWidth(options.width);
		this.setLineSize(options.lineSize);
		this.setMaxValue(options.maxValue);
		this.setStatusType(options.statusType);
		this.setColor(options.color);
		this.setFill(options.fill);
		this.setTextBefore(options.textBefore);
		this.setTextAfter(options.textAfter);
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.ProgressRound.Status = {
		COUNTER: "COUNTER",
		PERCENT: "PERCENT",
		INCIRCLE: "INCIRCLE",
		INCIRCLECOUNTER: "INCIRCLECOUNTER",
		NONE: "NONE",
	};

	/**
	 *
	 * @enum {string}
	 */
	BX.UI.ProgressRound.Color = {
		DANGER: "ui-progressround-danger",
		SUCCESS: "ui-progressround-success",
		PRIMARY: "ui-progressround-primary",
		WARNING: "ui-progressround-warning"
	};

	BX.UI.ProgressRound.prototype =
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
					this.value = (value > this.maxValue) ? this.maxValue : value;
				}
			},

			getWidth: function()
			{
				return this.width;
			},

			setWidth: function(value)
			{
				if (BX.type.isNumber(value))
				{
					this.width = value;
				}
			},

			getLineSize: function()
			{
				return this.lineSize;
			},

			setLineSize: function(value)
			{
				if (BX.type.isNumber(value))
				{
					this.lineSize = (value > (this.width / 2)) ? (this.width / 2) : value ;
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

			//endregion

			//region Text
			createTextBefore: function(text)
			{
				if ((this.textBeforeContainer === null) && (text !== null))
				{
					this.textBeforeContainer = BX.create("div", {
						props: {className: "ui-progressround-text-before"},
						html: text
					});
				}
			},

			getTextBefore: function()
			{
				if (this.textBeforeContainer === null)
				{
					this.createTextBefore(this.textBefore);
				}
				return this.textBeforeContainer;
			},

			setTextBefore: function(text)
			{
				if (this.textBeforeContainer === null)
				{
					this.createTextBefore(text);
				}
				else
				{
					BX.adjust(this.textBeforeContainer, {
						html: text
					});
				}
			},

			createTextAfter: function(text)
			{
				if ((this.textAfterContainer === null) && (text !== null))
				{
					this.textAfterContainer = BX.create("div", {
						props: {className: "ui-progressround-text-after"},
						html: text
					});
				}
			},

			getTextAfter: function()
			{
				if (this.textAfterContainer === null)
				{
					this.createTextAfter(this.textAfter);
				}
				return this.textAfterContainer;
			},

			setTextAfter: function(text)
			{
				if (this.textAfterContainer === null)
				{
					this.createTextAfter(text);
				}
				else
				{
					BX.adjust(this.textAfterContainer, {
						html: text
					});
				}
			},

			//endregion

			// region Status
			getStatus: function()
			{
				if (this.status === null)
				{
					if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER)
					{
						this.status = BX.create("div", {
							props: { className: "ui-progressround-status" },
							text: this.getStatusCounter()
						});
					}
					else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE)
					{
						this.status = BX.create("div", {
							props: { className: "ui-progressround-status-percent-incircle" },
							text: this.getStatusPercent()
						});
					}
					else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER)
					{
						this.status = BX.create("div", {
							props: { className: "ui-progressround-status-incircle" },
							text: this.getStatusCounter()
						});
					}
					else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT)
					{
						this.status = BX.create("div", {
							props: { className: "ui-progressround-status-percent" },
							text: this.getStatusPercent()
						});
					}
					else
					{
						this.status = BX.create("span", {});
					}
				}

				return this.status;
			},

			getStatusPercent: function()
			{
				if (this.maxValue === 0)
				{
					return "0%"
				}
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
				if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER)
				{
					BX.adjust(this.status, {
						text: this.getStatusCounter()
					});
				}
				else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT)
				{
					BX.adjust(this.status, {
						text: this.getStatusPercent()
					});
				}
				else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE)
				{
					BX.adjust(this.status, {
						text: this.getStatusPercent()
					});
				}
				else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER)
				{
					BX.adjust(this.status, {
						text: this.getStatusCounter()
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

			// region ProgressRound
			getCircleFerence: function() {
				return (this.width / 2 - this.lineSize / 2) * 2 * 3.14;
			},

			getCircleProgress: function() {
				return this.getCircleFerence() - (this.getCircleFerence() / this.maxValue * this.value);
			},

			getBar: function() {
				var factRadius = this.width / 2 - (this.lineSize / 2);

				this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				this.svg.setAttributeNS(null, 'class', 'ui-progressround-track-bar');
				this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.width + ' ' + this.width);
				this.svg.setAttributeNS(null, 'width', this.width);
				this.svg.setAttributeNS(null, 'height', this.width);

				this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				this.progressBg.setAttributeNS(null, 'r', factRadius);
				this.progressBg.setAttributeNS(null, 'cx', (this.width / 2));
				this.progressBg.setAttributeNS(null, 'cy', (this.width / 2));
				this.progressBg.setAttributeNS(null, 'stroke-width', this.lineSize);
				this.progressBg.setAttributeNS(null, 'class', 'ui-progressround-track-bar-bg');

				this.svg.appendChild(this.progressBg);

				this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				this.progressMove.setAttributeNS(null, 'r', factRadius);
				this.progressMove.setAttributeNS(null, 'cx', (this.width / 2));
				this.progressMove.setAttributeNS(null, 'cy', (this.width / 2));
				this.progressMove.setAttributeNS(null, 'stroke-width', this.lineSize);
				this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircleFerence());
				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircleFerence());
				this.progressMove.setAttributeNS(null, 'class', 'ui-progressround-track-bar-progress');

				this.svg.appendChild(this.progressMove);

				return this.svg;
			},

			animateProgressBar: function() {
				this.svg.setAttributeNS(null, 'class', 'task-report-circle-bar task-report-circle-bar-animate');

				var progressDashoffset = (this.maxValue === 0) ? this.getCircleFerence() : this.getCircleProgress();

				this.progressMove.setAttributeNS(null, 'stroke-dashoffset', progressDashoffset);
			},

			update: function(value)
			{
				this.setValue(value);
				this.setStatus();

				if (this.svg === null)
				{
					this.getBar();
				}

				this.animateProgressBar();
			},

			//endregion

			getContainer: function()
			{
				if (this.container === null)
				{
					this.container = BX.create("div", {
						props: { className: "ui-progressround" },
						children: [
							this.getTextAfter(),
							this.getTextBefore(),
							BX.create("div", {
								props: { className: "ui-progressround-track" },
								children: [
									this.getStatus(),
									this.getBar(),
									this.animateProgressBar()
								]
							})
						]
					});
				}

				return this.container;
			}
		};

})();