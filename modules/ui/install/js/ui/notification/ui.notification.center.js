(function() {

"use strict";

BX.namespace("BX.UI.Notification");

var instance = null;

/**
 * @memberOf BX.UI.Notification
 * @name BX.UI.Notification#Center
 * @type BX.UI.Notification.Manager
 * @static
 * @readonly
 */
Object.defineProperty(BX.UI.Notification, "Center", {
	enumerable: false,
	get: function()
	{
		if (instance === null)
		{
			instance = new BX.UI.Notification.Manager({});
		}

		return instance;
	}
});

BX.UI.Notification.Manager = function()
{
	this.stacks = Object.create(null);
	this.balloons = Object.create(null);

	this.balloonDefaults = {};
	this.stackDefaults = {};
	this.defaultPosition = BX.UI.Notification.Position.TOP_RIGHT;

	BX.addCustomEvent(
		BX.UI.Notification.Event.getFullName("onClose"),
		this.handleBalloonClose.bind(this)
	);
};

BX.UI.Notification.Manager.prototype =
{
	/**
	 * @public
	 * @param {BX.UI.Notification.BalloonOptions} options
	 * @param {BX.UI.Notification.Position} [options.position]
	 * @param {string} [options.type]
	 * @param {boolean} [options.blinkOnUpdate]
	 */
	notify: function(options)
	{
		options = BX.type.isPlainObject(options) ? options : {};

		var currentBalloon = this.getBalloonById(options.id) || this.getBalloonByCategory(options.category);
		if (currentBalloon)
		{
			currentBalloon.setOptions(options);
			currentBalloon.show();
			
			if (options.blinkOnUpdate === false)
			{
				currentBalloon.update(null);
			}
			else
			{
				currentBalloon.blink();
			}

			return;
		}

		var stack = null;
		if (options.stack instanceof BX.UI.Notification.Stack)
		{
			stack = options.stack;
			this.addStack(stack);
		}
		else
		{
			if (BX.type.isNotEmptyString(options.position))
			{
				stack = this.getStackByPosition(options.position);
			}
			else
			{
				stack = this.getDefaultStack();
			}

			options.stack = stack;
		}

		var balloonOptions = BX.mergeEx({}, this.getBalloonDefaults(), options);
		var balloonType = stack.getBalloonType(options.type);
		var balloon = new balloonType(balloonOptions);
		if (!(balloon instanceof BX.UI.Notification.Balloon))
		{
			throw new Error("Balloon type must be an instance of BX.UI.Notification.Balloon");
		}

		this.balloons[balloon.getId()] = balloon;
		balloon.show();

		return balloon;
	},

	/**
	 * @public
	 * @param {string} balloonId
	 * @return {BX.UI.Notification.Balloon|null}
	 */
	getBalloonById: function(balloonId)
	{
		return this.balloons[balloonId] ? this.balloons[balloonId] : null;
	},

	/**
	 * @public
	 * @param {string} category
	 * @return {BX.UI.Notification.Balloon|null}
	 */
	getBalloonByCategory: function(category)
	{
		if (BX.type.isNotEmptyString(category))
		{
			for (var id in this.balloons)
			{
				var balloon = this.balloons[id];
				if (balloon.getCategory() === category)
				{
					return balloon;
				}
			}
		}

		return null;
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Balloon} balloon
	 */
	removeBalloon: function(balloon)
	{
		delete this.balloons[balloon.getId()];
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Event} event
	 */
	handleBalloonClose: function(event)
	{
		this.removeBalloon(event.getBalloon());
	},

	/**
	 * @public
	 * @param {string} stackId
	 * @return {BX.UI.Notification.Stack}
	 */
	getStack: function(stackId)
	{
		return this.stacks[stackId] ? this.stacks[stackId] : null;
	},

	/**
	 * @public
	 * @return {BX.UI.Notification.Stack}
	 */
	getDefaultStack: function()
	{
		return this.getStackByPosition(this.getDefaultPosition());
	},

	/**
	 * @private
	 * @param {string} position
	 * @return {BX.UI.Notification.Stack}
	 */
	getStackByPosition: function(position)
	{
		var stack = this.getStack(position);
		if (stack === null)
		{
			stack = new BX.UI.Notification.Stack(BX.mergeEx(
				{},
				this.getStackDefaults(),
				{
					id: position,
					position: position
				}
			));

			this.addStack(stack);
		}

		return stack;
	},

	/**
	 * @private
	 * @param {BX.UI.Notification.Stack} stack
	 */
	addStack: function(stack)
	{
		if (stack instanceof BX.UI.Notification.Stack && this.getStack(stack.getId()) === null)
		{
			this.stacks[stack.getId()] = stack;
		}
	},

	/**
	 * @public
	 * @param {BX.UI.Notification.BalloonOptions} options
	 */
	setBalloonDefaults: function(options)
	{
		if (BX.type.isPlainObject(options))
		{
			BX.mergeEx(this.balloonDefaults, options);
		}
	},

	/**
	 * @private
	 * @return {object}
	 */
	getBalloonDefaults: function()
	{
		return this.balloonDefaults;
	},

	/**
	 * @public
	 * @param {BX.UI.Notification.Position|BX.UI.Notification.StackOptions} position
	 * @param {BX.UI.Notification.StackOptions} [options]
	 */
	setStackDefaults: function(position, options)
	{
		if (BX.UI.Notification.Stack.getPositionCode(position))
		{
			var stack = this.getStackByPosition(position);
			stack.setOptions(options);
		}
		else if (BX.type.isPlainObject(position))
		{
			//Set defaults for all stacks.
			options = position;
			for (var code in BX.UI.Notification.Position)
			{
				position = BX.UI.Notification.Position[code];
				this.setStackDefaults(position, options);
			}
		}
	},

	/**
	 * @public
	 * @param {BX.UI.Notification.Position} position
	 */
	setDefaultPosition: function(position)
	{
		if (BX.UI.Notification.Stack.getPositionCode(position))
		{
			this.defaultPosition = position;
		}
	},

	/**
	 * @public
	 * @return {BX.UI.Notification.Position}
	 */
	getDefaultPosition: function()
	{
		return this.defaultPosition;
	},

	/**
	 * @private
	 * @return {object}
	 */
	getStackDefaults: function()
	{
		return this.stackDefaults;
	}
};

})();
