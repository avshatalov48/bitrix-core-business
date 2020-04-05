;(function() {

	'use strict';

	BX.namespace('BX.UI');

	var Panel = function()
	{

	};
	Panel.prototype = {
		init: function (options)
		{
			options = options || {};
			this.context = BX(options.containerId);
			this.isFrame = options.isFrame || false;
			this.hasHints = options.hasHints || false;

			this.pinner = new BX.UI.Pinner(
				this.context,
				{
					fixBottom: this.isFrame,
					fullWidth: this.isFrame
				}
			);

			if (this.hasHints)
			{
				BX.UI.Hint.init(this.context);
			}

			options.buttons.forEach(this.initButton, this);
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