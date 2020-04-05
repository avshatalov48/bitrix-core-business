;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.PinPanel
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.PinPanel = function(parent)
	{
		this.parent = null;
		this.panel = null;
		this.panelRect = null;
		this.isSelected = null;
		this.offset = null;
		this.animationDuration = null;
		this.lastIsSelected = null;
		this.init(parent);
	};

	BX.Grid.PinPanel.prototype = {
		init: function(parent) {
			this.parent = parent;
			this.offset = 10;
			this.animationDuration = 200;
			this.panel = this.getPanel();
			this.bindOnRowsEvents();
		},

		bindOnRowsEvents: function()
		{
			BX.addCustomEvent('Grid::thereSelectedRows', BX.delegate(this._onThereSelectedRows, this));
			BX.addCustomEvent('Grid::allRowsSelected', BX.delegate(this._onThereSelectedRows, this));
			BX.addCustomEvent('Grid::noSelectedRows', BX.delegate(this._onNoSelectedRows, this));
			BX.addCustomEvent('Grid::allRowsUnselected', BX.delegate(this._onNoSelectedRows, this));
			BX.addCustomEvent('Grid::updated', BX.delegate(this._onNoSelectedRows, this));

		},

		bindOnWindowEvents: function()
		{
			BX.bind(window, 'resize', BX.proxy(this._onResize, this));
			document.addEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({passive: true}));
		},

		unbindOnWindowEvents: function()
		{
			BX.unbind(window, 'resize', BX.proxy(this._onResize, this));
			document.removeEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({passive: true}));
		},

		getPanel: function() {
			this.panel = this.panel || this.parent.getActionsPanel().getPanel();
			return this.panel;
		},

		getScrollBottom: function()
		{
			return (BX.scrollTop(window) + this.getWindowHeight());
		},

		getPanelRect: function()
		{
			if (!BX.type.isPlainObject(this.panelRect))
			{
				this.panelRect = BX.pos(this.getPanel());
			}

			return this.panelRect;
		},

		getPanelPrevBottom: function()
		{
			var prev = BX.previousSibling(this.getPanel());
			return BX.pos(prev).bottom + parseFloat(BX.style(prev, 'margin-bottom'));
		},

		getWindowHeight: function()
		{
			this.windowHeight = this.windowHeight || BX.height(window);
			return this.windowHeight;
		},

		pinPanel: function()
		{
			BX.style(this.getPanel(), 'width', BX.width(this.getPanel().parentNode) + 'px');
			BX.style(this.getPanel().parentNode, 'height', BX.height(this.getPanel().parentNode) + 'px');
			BX.addClass(this.getPanel(), 'main-grid-fixed-bottom');
			BX.style(this.getPanel(), 'bottom', '');
			setTimeout(BX.delegate(function() {
				BX.style(this.getPanel(), 'transition', 'none');
			}, this), 200);
		},

		unpinPanel: function()
		{
			BX.removeClass(this.getPanel(), 'main-grid-fixed-bottom');
			BX.style(this.getPanel(), 'width', '');
			BX.style(this.getPanel().parentNode, 'height', '');
			BX.style(this.getPanel(), 'bottom', '');
			setTimeout(BX.delegate(function() {
				BX.style(this.getPanel(), 'transition', '');
			}, this), 200);
		},

		isSelectedRows: function()
		{
			return this.isSelected;
		},

		isNeedPinAbsolute: function()
		{
			return (
				((BX.pos(this.parent.getBody()).top + this.getPanelRect().height) >= this.getScrollBottom())
			);
		},

		isNeedPin: function()
		{
			return (this.getScrollBottom() - this.getPanelRect().height) <= this.getPanelPrevBottom();
		},

		adjustPanelPosition: function()
		{
			var scrollX = window.pageXOffset;
			this.lastScrollX = this.lastScrollX !== null ? this.lastScrollX : scrollX;

			BX.Grid.Utils.requestAnimationFrame(BX.proxy(function() {
				if (scrollX !== this.lastScrollX)
				{
					var panelPos = this.getPanelRect();
					BX.style(this.getPanel(), 'left', panelPos.left - scrollX + 'px');
				}
			}, this));

			this.lastScrollX = scrollX;
		},

		pinController: function(isNeedAnimation)
		{
			if(!this.getPanel())
			{
				return;
			}
			var self = this;

			if (this.isNeedPin() && this.isSelectedRows())
			{
				if (isNeedAnimation)
				{
					BX.style(this.getPanel(), 'bottom', -this.getStartDiffPanelPosition() + 'px');
					setTimeout(function() {
						self.pinPanel();
					}, 200);
				}
				else
				{
					this.pinPanel();
				}

				if (this.isNeedPinAbsolute() && !this.absolutePin)
				{
					this.absolutePin = true;
					BX.style(this.getPanel(), 'transition', '');
					BX.style(this.getPanel(), 'top', (BX.pos(this.parent.getBody()).top - parseFloat(BX.style(this.getPanel(), 'margin-top'))) + 'px');
					BX.style(self.getPanel(), 'position', 'absolute');
				}
				else if (!this.isNeedPinAbsolute() && this.absolutePin)
				{
					this.absolutePin = false;
					BX.style(this.getPanel(), 'position', '');
					BX.style(this.getPanel(), 'top', '');
				}

				this.adjustPanelPosition();
			}
			else
			{
				if (isNeedAnimation)
				{
					BX.style(this.getPanel(), 'bottom', -this.getEndDiffPanelPosition() + 'px');
					setTimeout(function() {
						self.unpinPanel();
					}, 200);
				}
				else
				{
					this.unpinPanel();
				}
			}
		},

		getEndDiffPanelPosition: function()
		{
			var panelPos = BX.pos(this.getPanel());
			var prevPanelPos = BX.pos(BX.previousSibling(this.getPanel()));
			var scrollTop = BX.scrollTop(window);
			var scrollBottom = scrollTop + BX.height(window);
			var diff = panelPos.height + this.offset;
			var prevPanelBottom = (prevPanelPos.bottom + parseFloat(BX.style(this.getPanel(), 'margin-top')));

			if (prevPanelBottom < scrollBottom && (prevPanelBottom + panelPos.height) > scrollBottom)
			{
				diff = Math.abs(scrollBottom - (prevPanelBottom + panelPos.height));
			}

			return diff;
		},

		getStartDiffPanelPosition: function()
		{
			var panelPos = BX.pos(this.getPanel());
			var scrollTop = BX.scrollTop(window);
			var scrollBottom = scrollTop + BX.height(window);
			var diff = panelPos.height + this.offset;

			if (panelPos.bottom > scrollBottom && panelPos.top < scrollBottom)
			{
				diff = panelPos.bottom - scrollBottom;
			}

			return diff;
		},

		_onThereSelectedRows: function()
		{
			this.bindOnWindowEvents();
			this.isSelected = true;

			if (this.lastIsSelected)
			{
				this.pinController();
			}
			else
			{
				this.lastIsSelected = true;
				this.pinController(true);
			}

		},

		_onNoSelectedRows: function()
		{
			this.unbindOnWindowEvents();
			this.isSelected = false;
			this.pinController(true);
			this.lastIsSelected = false;
		},

		_onScroll: function()
		{
			this.pinController();
		},

		_onResize: function()
		{
			this.windowHeight = BX.height(window);
			this.panel = this.parent.getActionsPanel().getPanel();
			this.panelRect = this.getPanel().getBoundingClientRect();
			this.pinController();
		}
	};
})();