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
		this.isSelected = null;
		this.offset = null;
		this.animationDuration = null;
		this.pinned = false;
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

		pinPanel: function(withAnimation)
		{
			var panel = this.getPanel();
			var width = BX.width(this.getPanel().parentNode);
			var height = BX.height(this.getPanel().parentNode);
			var bodyRect = BX.pos(this.parent.getBody());
			var offset = this.getStartDiffPanelPosition();

			panel.parentNode.style.setProperty('height', height + 'px');

			panel.style.setProperty('transform', 'translateY('+ offset + 'px)');
			panel.classList.add('main-grid-fixed-bottom');
			panel.style.setProperty('width', width + 'px');
			panel.style.removeProperty('position');
			panel.style.removeProperty('top');

			requestAnimationFrame(function() {
				if (withAnimation !== false)
				{
					panel.style.setProperty('transition', 'transform 200ms ease');
				}

				panel.style.setProperty('transform', 'translateY(0)');
			});

			if (this.isNeedPinAbsolute() && !this.absolutePin)
			{
				this.absolutePin = true;
				panel.style.removeProperty('transition');
				panel.style.setProperty('position', 'absolute');
				panel.style.setProperty('top', bodyRect.top + 'px');
			}

			if (!this.isNeedPinAbsolute() && this.absolutePin)
			{
				this.absolutePin = false;
			}

			this.adjustPanelPosition();
			this.pinned = true;
		},

		unpinPanel: function(withAnimation)
		{
			var panel = this.getPanel();
			var panelRect = BX.pos(panel);
			var parentRect = BX.pos(panel.parentNode);
			var offset = Math.abs(panelRect.bottom - parentRect.bottom);

			if (withAnimation !== false)
			{
				panel.style.setProperty('transition', 'transform 200ms ease');
			}

			var translateOffset = offset < panelRect.height ? offset + 'px' : '100%';
			panel.style.setProperty('transform', 'translateY('+translateOffset+')');

			var delay = function(cb, delay)
			{
				if (withAnimation !== false)
				{
					return setTimeout(cb, delay);
				}

				cb();
			};

			delay(function() {
				panel.parentNode.style.removeProperty('height');
				panel.classList.remove('main-grid-fixed-bottom');
				panel.style.removeProperty('transition');
				panel.style.removeProperty('transform');
				panel.style.removeProperty('width');
				panel.style.removeProperty('position');
				panel.style.removeProperty('top');
			}, withAnimation !== false ? 200 : 0);

			this.pinned = false;
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

		pinController: function(withAnimation)
		{
			if (this.getPanel())
			{
				if (!this.isPinned() && this.isNeedPin() && this.isSelectedRows())
				{
					return this.pinPanel(withAnimation);
				}

				if (this.isPinned() && !this.isNeedPin() || !this.isSelectedRows())
				{
					this.unpinPanel(withAnimation);
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
			var diff = panelPos.height;

			if (panelPos.bottom > scrollBottom && panelPos.top < scrollBottom)
			{
				diff = panelPos.bottom - scrollBottom;
			}

			return diff;
		},

		isPinned: function()
		{
			return this.pinned;
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
				this.pinController();
			}

		},

		_onNoSelectedRows: function()
		{
			this.unbindOnWindowEvents();
			this.isSelected = false;
			this.pinController();
			this.lastIsSelected = false;
		},

		_onScroll: function()
		{
			this.pinController(false);
		},

		_onResize: function()
		{
			this.windowHeight = BX.height(window);
			this.panel = this.parent.getActionsPanel().getPanel();
			this.panelRect = this.getPanel().getBoundingClientRect();
			this.pinController(false);
		}
	};
})();