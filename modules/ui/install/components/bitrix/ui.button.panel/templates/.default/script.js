;(function() {

	'use strict';

	BX.namespace('BX.UI');

	var Panel = function()
	{
		this.layout = {
			container: null
		}
	};
	Panel.prototype = {
		init: function (options)
		{
			this.layout.container= BX(options.containerId);
			this.isFrame = options.isFrame || false;
			this.hasHints = options.hasHints || false;
			this.pinnerContainer = options.pinnerContainer || false;

			this.pinner = new BX.UI.Pinner(
				this.layout.container,
				{
					fixBottom: this.isFrame,
					fullWidth: this.isFrame,
					anchorBottom: this.pinnerContainer
				}
			);

			if (this.hasHints)
			{
				BX.UI.Hint.init(this.layout.container);
			}

			options.buttons.forEach(this.initButton, this);
		},

		getContainer: function()
		{
			return this.layout.container;
		},

		hide: function()
		{
			if(!this.layout.container)
			{
				return;
			}

			this.layout.container.classList.add('ui-button-panel-wrapper-hide');
		},

		show: function()
		{
			this.layout.container.classList.remove('ui-button-panel-wrapper-hide');
		},

		initButton: function (button)
		{
			if (!button.ID)
			{
				return;
			}

			button.node = BX(button.ID);
			if (!button.node)
			{
				return;
			}

			BX.bind(button.node, 'click', this.onButtonClick.bind(this, button));
		},

		onButtonClick: function (button, e)
		{
			BX.onCustomEvent(this, 'button-click', [button]);

			if (button.WAIT)
			{
				BX.addClass(BX(button.ID), 'ui-btn-wait');
			}

			if (this.isFrame && (button.TYPE === 'close' || button.TYPE === 'cancel'))
			{
				e.preventDefault();
				top.BX.SidePanel.Instance.close();
			}
		}
	};

	BX.UI.ButtonPanel = new Panel();
})();
