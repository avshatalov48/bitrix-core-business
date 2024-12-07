(function() {
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
		init(parent) {
			this.parent = parent;
			this.offset = 10;
			this.animationDuration = 200;
			this.panel = this.getPanel();
			this.bindOnRowsEvents();
		},

		destroy()
		{
			this.unbindOnRowsEvents();
		},

		bindOnRowsEvents()
		{
			BX.addCustomEvent('Grid::thereSelectedRows', BX.proxy(this._onThereSelectedRows, this));
			BX.addCustomEvent('Grid::allRowsSelected', BX.proxy(this._onThereSelectedRows, this));
			BX.addCustomEvent('Grid::noSelectedRows', BX.proxy(this._onNoSelectedRows, this));
			BX.addCustomEvent('Grid::allRowsUnselected', BX.proxy(this._onNoSelectedRows, this));
			BX.addCustomEvent('Grid::updated', BX.proxy(this._onNoSelectedRows, this));
		},

		unbindOnRowsEvents()
		{
			BX.removeCustomEvent('Grid::thereSelectedRows', BX.proxy(this._onThereSelectedRows, this));
			BX.removeCustomEvent('Grid::allRowsSelected', BX.proxy(this._onThereSelectedRows, this));
			BX.removeCustomEvent('Grid::noSelectedRows', BX.proxy(this._onNoSelectedRows, this));
			BX.removeCustomEvent('Grid::allRowsUnselected', BX.proxy(this._onNoSelectedRows, this));
			BX.removeCustomEvent('Grid::updated', BX.proxy(this._onNoSelectedRows, this));
		},

		bindOnWindowEvents()
		{
			BX.bind(window, 'resize', BX.proxy(this._onResize, this));
			document.addEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({ passive: true }));
		},

		unbindOnWindowEvents()
		{
			BX.unbind(window, 'resize', BX.proxy(this._onResize, this));
			document.removeEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({ passive: true }));
		},

		getPanel() {
			this.panel = this.panel || this.parent.getActionsPanel().getPanel();

			return this.panel;
		},

		getScrollBottom()
		{
			return (BX.scrollTop(window) + this.getWindowHeight());
		},

		getPanelRect()
		{
			if (!BX.type.isPlainObject(this.panelRect))
			{
				this.panelRect = BX.pos(this.getPanel());
			}

			return this.panelRect;
		},

		getPanelPrevBottom()
		{
			const prev = BX.previousSibling(this.getPanel());

			return BX.pos(prev).bottom + parseFloat(BX.style(prev, 'margin-bottom'));
		},

		getWindowHeight()
		{
			this.windowHeight = this.windowHeight || BX.height(window);

			return this.windowHeight;
		},

		pinPanel(withAnimation)
		{
			const panel = this.getPanel();
			const width = BX.width(this.getPanel().parentNode);
			const height = BX.height(this.getPanel().parentNode);
			const bodyRect = BX.pos(this.parent.getBody());
			const offset = this.getStartDiffPanelPosition();

			panel.parentNode.style.setProperty('height', `${height}px`);

			panel.style.setProperty('transform', `translateY(${offset}px)`);
			panel.classList.add('main-grid-fixed-bottom');
			panel.style.setProperty('width', `${width}px`);
			panel.style.removeProperty('position');
			panel.style.removeProperty('top');

			requestAnimationFrame(() => {
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
				panel.style.setProperty('top', `${bodyRect.top}px`);
			}

			if (!this.isNeedPinAbsolute() && this.absolutePin)
			{
				this.absolutePin = false;
			}

			this.adjustPanelPosition();
			this.pinned = true;
		},

		unpinPanel(withAnimation)
		{
			const panel = this.getPanel();
			const panelRect = BX.pos(panel);
			const parentRect = BX.pos(panel.parentNode);
			const offset = Math.abs(panelRect.bottom - parentRect.bottom);

			if (withAnimation !== false)
			{
				panel.style.setProperty('transition', 'transform 200ms ease');
			}

			const translateOffset = offset < panelRect.height ? `${offset}px` : '100%';
			panel.style.setProperty('transform', `translateY(${translateOffset})`);

			const delay = function(cb, delay)
			{
				if (withAnimation !== false)
				{
					return setTimeout(cb, delay);
				}

				cb();
			};

			delay(() => {
				panel.parentNode.style.removeProperty('height');
				panel.classList.remove('main-grid-fixed-bottom');
				panel.style.removeProperty('transition');
				panel.style.removeProperty('transform');
				panel.style.removeProperty('width');
				panel.style.removeProperty('position');
				panel.style.removeProperty('top');
			}, withAnimation === false ? 0 : 200);

			this.pinned = false;
		},

		isSelectedRows()
		{
			return this.isSelected;
		},

		isNeedPinAbsolute()
		{
			return (
				((BX.pos(this.parent.getBody()).top + this.getPanelRect().height) >= this.getScrollBottom())
			);
		},

		isNeedPin()
		{
			return (this.getScrollBottom() - this.getPanelRect().height) <= this.getPanelPrevBottom();
		},

		adjustPanelPosition()
		{
			const scrollX = window.pageXOffset;
			this.lastScrollX = this.lastScrollX === null ? scrollX : this.lastScrollX;

			BX.Grid.Utils.requestAnimationFrame(BX.proxy(function() {
				if (scrollX !== this.lastScrollX)
				{
					const panelPos = this.getPanelRect();
					BX.style(this.getPanel(), 'left', `${panelPos.left - scrollX}px`);
				}
			}, this));

			this.lastScrollX = scrollX;
		},

		pinController(withAnimation)
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

		getEndDiffPanelPosition()
		{
			const panelPos = BX.pos(this.getPanel());
			const prevPanelPos = BX.pos(BX.previousSibling(this.getPanel()));
			const scrollTop = BX.scrollTop(window);
			const scrollBottom = scrollTop + BX.height(window);
			let diff = panelPos.height + this.offset;
			const prevPanelBottom = (prevPanelPos.bottom + parseFloat(BX.style(this.getPanel(), 'margin-top')));

			if (prevPanelBottom < scrollBottom && (prevPanelBottom + panelPos.height) > scrollBottom)
			{
				diff = Math.abs(scrollBottom - (prevPanelBottom + panelPos.height));
			}

			return diff;
		},

		getStartDiffPanelPosition()
		{
			const panelPos = BX.pos(this.getPanel());
			const scrollTop = BX.scrollTop(window);
			const scrollBottom = scrollTop + BX.height(window);
			let diff = panelPos.height;

			if (panelPos.bottom > scrollBottom && panelPos.top < scrollBottom)
			{
				diff = panelPos.bottom - scrollBottom;
			}

			return diff;
		},

		isPinned()
		{
			return this.pinned;
		},

		_onThereSelectedRows()
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

		_onNoSelectedRows()
		{
			this.unbindOnWindowEvents();
			this.isSelected = false;
			this.pinController();
			this.lastIsSelected = false;
		},

		_onScroll()
		{
			this.pinController(false);
		},

		_onResize()
		{
			this.windowHeight = BX.height(window);
			this.panel = this.parent.getActionsPanel().getPanel();
			this.panelRect = this.getPanel().getBoundingClientRect();
			this.pinController(false);
		},
	};
})();
