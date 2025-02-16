(function() {

"use strict";

BX.namespace("BX.UI.Notification");

/**
 *
 * @enum {string}
 */
BX.UI.Notification.Position = {
	TOP_LEFT: "top-left",
	TOP_CENTER: "top-center",
	TOP_RIGHT: "top-right",
	BOTTOM_LEFT: "bottom-left",
	BOTTOM_CENTER: "bottom-center",
	BOTTOM_RIGHT: "bottom-right"
};

/**
 * @typedef {object} BX.UI.Notification.StackOptions
 * @property {BX.UI.Notification.Position} [position]
 * @property {number} [spacing=20]
 * @property {number} [offsetX=25]
 * @property {number} [offsetY=25]
 * @property {boolean} [newestOnTop=false]
 * @property {string} [balloonType]
 * @property {string} [id]
 */

/**
 *
 * @param {BX.UI.Notification.StackOptions} options
 * @constructor
 */
BX.UI.Notification.Stack = function(options)
{
	options = BX.type.isPlainObject(options) ? options : Object.create(null);

	/**
	 *
	 * @type {BX.UI.Notification.Balloon[]}
	 */
	this.balloons = [];

	/**
	 *
	 * @type {BX.UI.Notification.Balloon[]}
	 */
	this.queueStack = [];

	this.id = BX.type.isNotEmptyString(options.id) ? options.id : BX.util.getRandomString(8).toLowerCase();
	this.position = BX.UI.Notification.Stack.getPositionCode(options.position) ? options.position : "top-right";

	this.spacing = 20;
	this.offsetX = 25;
	this.offsetY = 25;
	this.newestOnTop = false;
	this.balloonType = BX.UI.Notification.Balloon;

	this.setOptions(options);

	BX.addCustomEvent(BX.UI.Notification.Event.getFullName("onClose"), this.handleBalloonClose.bind(this));
};

BX.UI.Notification.Stack.getPositionCode = function(position)
{
	for (var code in BX.UI.Notification.Position)
	{
		if (BX.UI.Notification.Position[code] === position)
		{
			return code;
		}
	}

	return null;
};

BX.UI.Notification.Stack.prototype =
{
	/**
	 * @public
	 * @param {BX.UI.Notification.Balloon} [balloon]
	 */
	adjustPosition: function(balloon)
	{
		var offset = 0;
		this.getBalloons().forEach(function(currentBalloon) {

			if (!balloon || balloon === currentBalloon)
			{
				if (currentBalloon.doNotAdjustPosition)
				{
					return;
				}

				switch (this.getPosition())
				{
					case BX.UI.Notification.Position.TOP_LEFT:
						currentBalloon.getContainer().style.left = this.getOffsetX() + "px";
						currentBalloon.getContainer().style.top = offset + this.getOffsetY() + "px";
						break;
					case BX.UI.Notification.Position.TOP_CENTER:
						currentBalloon.getContainer().style.left = "50%";
						currentBalloon.getContainer().style.transform = "translateX(-50%)";
						currentBalloon.getContainer().style.top = offset + this.getOffsetY() + "px";
						break;
					case BX.UI.Notification.Position.TOP_RIGHT:
						currentBalloon.getContainer().style.right = this.getOffsetX() + "px";
						currentBalloon.getContainer().style.top = offset + this.getOffsetY() + "px";
						break;
					case BX.UI.Notification.Position.BOTTOM_LEFT:
						currentBalloon.getContainer().style.left = this.getOffsetX() + "px";
						currentBalloon.getContainer().style.bottom = offset + this.getOffsetY() + "px";
						break;
					case BX.UI.Notification.Position.BOTTOM_CENTER:
						currentBalloon.getContainer().style.left = "50%";
						currentBalloon.getContainer().style.transform = "translateX(-50%)";
						currentBalloon.getContainer().style.bottom = offset + this.getOffsetY() + "px";
						break;
					case BX.UI.Notification.Position.BOTTOM_RIGHT:
						currentBalloon.getContainer().style.right = this.getOffsetX() + "px";
						currentBalloon.getContainer().style.bottom = offset + this.getOffsetY() + "px";
						break;
				}
			}

			offset += this.getSpacing() + currentBalloon.getHeight();

		}, this);

	},

	/**
	 * @public
	 * @param {BX.UI.Notification.Balloon} balloon
	 */
	add: function(balloon)
	{
		if (this.getBalloons().length > 0 && (this.getQueue().length > 0 || !this.isBalloonFitToViewport(balloon)))
		{
			this.queue(balloon);
		}
		else
		{
			this.push(balloon);
		}
	},

	clear()
	{
		const balloons = [...this.balloons, ...this.queueStack];
		this.queueStack = [];
		this.balloons = [];

		balloons.forEach((balloon) => {
			return balloon.close();
		});
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Balloon} balloon
	 */
	push: function(balloon)
	{
		if (!(balloon instanceof BX.UI.Notification.Balloon))
		{
			throw new Error("'balloon' must be an instance of BX.UI.Notification.Balloon.");
		}

		if (this.balloons.indexOf(balloon) === -1)
		{
			if (this.isNewestOnTop())
			{
				this.balloons.splice(0, 0, balloon);
			}
			else
			{
				this.balloons.push(balloon);
			}
		}
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Balloon} balloon
	 */
	queue: function(balloon)
	{
		if (!(balloon instanceof BX.UI.Notification.Balloon))
		{
			throw new Error("'balloon' must be an instance of BX.UI.Notification.Balloon.");
		}

		if (this.queueStack.indexOf(balloon) === -1)
		{
			balloon.setState(BX.UI.Notification.State.QUEUED);
			this.queueStack.push(balloon);
		}
	},

	/**
	 * @private
	 */
	checkQueue: function()
	{
		var queue = this.queueStack.slice();
		for (var i = 0; i < queue.length; i++)
		{
			var balloon = queue[i];
			if (!this.isBalloonFitToViewport(balloon) && this.getBalloons().length > 0)
			{
				break;
			}

			balloon.setState(BX.UI.Notification.State.INIT);
			this.queueStack.shift();
			this.push(balloon);

			balloon.show();
		}
	},

	/**
	 * @private
	 * @return {BX.UI.Notification.Balloon[]}
	 */
	getQueue: function()
	{
		return this.queueStack;
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Balloon} balloon
	 * @return {boolean}
	 */
	isBalloonFitToViewport: function(balloon)
	{
		var viewportHeight = document.documentElement.clientHeight;
		var balloonHeight = this.getSpacing() + balloon.getHeight();

		return this.getHeight() + balloonHeight <= viewportHeight;
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Event} event
	 */
	handleBalloonClose: function(event)
	{
		const closingBalloon = event.getBalloon();
		if (closingBalloon.getStack() !== this)
		{
			return;
		}

		this.balloons = this.balloons.filter((balloon) => {
			return closingBalloon !== balloon;
		});

		this.adjustPosition();
		this.checkQueue();
	},

	/**
	 * @package
	 * @param {BX.UI.Notification.StackOptions} options
	 */
	setOptions: function(options)
	{
		options = options || {};

		this.setSpacing(options.spacing);
		this.setOffsetX(options.offsetX);
		this.setOffsetY(options.offsetY);
		this.setNewestOnTop(options.newestOnTop);
		this.setBalloonType(options.balloonType);
	},

	/**
	 *
	 * @return {string}
	 */
	getId: function()
	{
		return this.id;
	},

	/**
	 * @public
	 * @return {BX.UI.Notification.Balloon[]}
	 */
	getBalloons: function()
	{
		return this.balloons;
	},

	/**
	 * @public
	 * @return {BX.UI.Notification.Position}
	 */
	getPosition: function()
	{
		return this.position;
	},

	/**
	 * @public
	 * @return {number}
	 */
	getSpacing: function()
	{
		return this.spacing;
	},

	/**
	 * @public
	 * @param {number} spacing
	 */
	setSpacing: function(spacing)
	{
		if (BX.type.isNumber(spacing))
		{
			this.spacing = spacing;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getOffsetX: function()
	{
		return this.offsetX;
	},

	/**
	 * @public
	 * @param {number} offsetX
	 */
	setOffsetX: function(offsetX)
	{
		if (BX.type.isNumber(offsetX))
		{
			this.offsetX = offsetX;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getOffsetY: function()
	{
		return this.offsetY;
	},

	/**
	 * @public
	 * @param {number} offsetY
	 */
	setOffsetY: function(offsetY)
	{
		if (BX.type.isNumber(offsetY))
		{
			this.offsetY = offsetY;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getHeight: function()
	{
		return this.getBalloons().reduce(function(height, balloon) {
			return height + balloon.getHeight() + this.getSpacing();
		}.bind(this), this.getOffsetY());
	},

	/**
	 *
	 * @param {string} [className]
	 * @return {BX.UI.Notification.Balloon}
	 */
	getBalloonType: function(className)
	{
		if (BX.type.isFunction(className))
		{
			return className;
		}

		var classFn = BX.getClass(className);

		if (BX.type.isFunction(classFn))
		{
			return classFn;
		}

		return this.balloonType || BX.UI.Notification.Balloon;
	},

	setBalloonType: function(balloonType)
	{
		if (BX.type.isFunction(balloonType))
		{
			this.balloonType = balloonType;
		}
		else if (BX.type.isNotEmptyString(balloonType))
		{
			var classFn = BX.getClass(balloonType);
			if (BX.type.isFunction(classFn))
			{
				this.balloonType = classFn;
			}
		}
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isNewestOnTop: function()
	{
		return this.newestOnTop;
	},

	/**
	 * @public
	 * @param {boolean} onTop
	 */
	setNewestOnTop: function(onTop)
	{
		if (BX.type.isBoolean(onTop))
		{
			this.newestOnTop = onTop;
		}
	}
};

})();
