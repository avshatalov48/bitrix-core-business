;(function()
{
	BX.namespace("BX.Call");

	var Events = {
		onClose: 'onClose',
		onDestroy: 'onDestroy',
		onCloseClicked: 'onCloseClicked',
	}

	BX.Call.Sidebar = function(options)
	{
		this.container = options.container;

		this.width = BX.prop.getInteger(options, 'width', 200);

		this.elements = {
			root: null,
			close: null,
			contentContainer: null,
		}
		this.eventEmitter = new BX.Event.EventEmitter(this, "BX.Call.Sidebar");

		if (options.events)
		{
			for (var eventName in options.events)
			{
				if (options.events.hasOwnProperty(eventName))
				{
					this.eventEmitter.subscribe(eventName, options.events[eventName]);
				}
			}
		}
	}

	BX.Call.Sidebar.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {
				className: "bx-messenger-call-sidebar-root"
			},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-call-sidebar-labels"},
					style: {top: '39px' /*'17px'*/},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-call-sidebar-label"},
							style: {maxWidth: '40px'},
							children: [BX.create("div", {
								props: {className: "bx-messenger-call-sidebar-label-icon-box"},
								attrs: {title: BX.message("IM_M_CALL_BTN_CLOSE")},
								children: [BX.create("div", {
									props: {className: "bx-messenger-call-sidebar-label-icon bx-messenger-call-sidebar-label-icon-close"},
								})]
							})],
							events: {
								click: function()
								{
									this.eventEmitter.emit(Events.onCloseClicked);
								}.bind(this)
							}
						})
					]
				}),
				this.elements.contentContainer = BX.create("div", {
					props: {className: "bx-messenger-call-sidebar-content-container"}
				})
			]
		});
		this.elements.root.style.setProperty('--sidebar-width', this.width + 'px');

		return this.elements.root;
	}

	BX.Call.Sidebar.prototype.setWidth = function(width)
	{
		if (this.width == width)
		{
			return;
		}

		this.width = width;
		this.elements.root.style.setProperty('--sidebar-width', this.width + 'px');
	}

	BX.Call.Sidebar.prototype.open = function(animation)
	{
		animation = animation !== false;
		return new Promise(function(resolve)
		{
			this.container.appendChild(this.render());
			if (animation)
			{
				this.elements.root.classList.add('opening');
				this.elements.root.addEventListener(
					'animationend',
					function()
					{
						this.elements.root.classList.remove('opening');
						resolve();
					}.bind(this),
					{
						once: true
					}
				)
			}
			else
			{
				resolve();
			}
		}.bind(this))
	}

	BX.Call.Sidebar.prototype.close = function(animation)
	{
		animation = animation !== false;
		return new Promise(function(resolve)
		{
			if (animation)
			{
				this.elements.root.classList.add('closing');
				this.elements.root.addEventListener(
					'animationend',
					function()
					{
						this.container.removeChild(this.elements.root);
						this.eventEmitter.emit(Events.onClose);
						resolve();
					}.bind(this),
					{
						once: true
					}
				)
			}
			else
			{
				this.container.removeChild(this.elements.root);
				this.eventEmitter.emit(Events.onClose);
				resolve();
			}
		}.bind(this))
	}

	BX.Call.Sidebar.prototype.toggleHidden = function(hidden)
	{
		this.elements.root.classList.toggle('hidden', hidden);
	}

	BX.Call.Sidebar.prototype.destroy = function()
	{
		this.eventEmitter.emit(Events.onDestroy);
		this.eventEmitter.unsubscribeAll(Events.onClose);
		this.eventEmitter.unsubscribeAll(Events.onDestroy);
		this.eventEmitter = null;
		this.elements = null;
		this.container = null;
	}

	BX.Call.Sidebar.Events = Events;
})();