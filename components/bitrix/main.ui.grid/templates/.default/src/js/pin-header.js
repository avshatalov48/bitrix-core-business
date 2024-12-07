(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.PinHeader
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.PinHeader = function(parent)
	{
		this.parent = null;
		this.table = null;
		this.header = null;
		this.container = null;
		this.parentNodeResizeObserver = null;

		const adminPanel = this.getAdminPanel();

		if (adminPanel)
		{
			this.mo = new MutationObserver(this.onAdminPanelMutation.bind(this));
			this.mo.observe(document.documentElement, { attributes: true });
		}

		this.init(parent);
	};

	BX.Grid.PinHeader.prototype = {
		init(parent)
		{
			this.parent = parent;
			this.rect = BX.pos(this.parent.getHead());
			this.gridRect = BX.pos(this.parent.getTable());

			let workArea = BX.Grid.Utils.getBySelector(document, '#workarea-content', true);

			if (!workArea)
			{
				workArea = this.parent.getContainer().parentNode;
				workArea = workArea ? workArea.parentNode : workArea;
			}

			if (workArea)
			{
				this.parentNodeResizeObserver = new BX.ResizeObserver(BX.proxy(this.refreshRect, this));
				this.parentNodeResizeObserver.observe(workArea);
			}

			this.create(true);

			document.addEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({ passive: true }));
			document.addEventListener('resize', BX.proxy(this._onResize, this), BX.Grid.Utils.listenerParams({ passive: true }));
			BX.addCustomEvent('Grid::updated', BX.proxy(this._onGridUpdate, this));
			BX.addCustomEvent('Grid::resize', BX.proxy(this._onGridUpdate, this));
			BX.bind(window, 'resize', BX.proxy(this._onGridUpdate, this));
		},

		refreshRect()
		{
			this.gridRect = BX.pos(this.parent.getTable());
			this.rect = BX.pos(this.parent.getHead());
		},

		_onGridUpdate()
		{
			const isPinned = this.isPinned();

			BX.remove(this.getContainer());
			this.create();

			isPinned && this.pin();

			this.table = null;
			this.refreshRect();

			this._onScroll();

			BX.onCustomEvent(window, 'Grid::headerUpdated', []);
		},

		create(async)
		{
			const cells = BX.Grid.Utils.getByTag(this.parent.getHead(), 'th');
			const cloneThead = BX.clone(this.parent.getHead());
			const cloneCells = BX.Grid.Utils.getByTag(cloneThead, 'th');

			const resizeCloneCells = function()
			{
				cells.forEach(
					(cell, index) => {
						let width = BX.width(cell);

						if (index > 0)
						{
							width -= parseInt(BX.style(cell, 'border-left-width'));
							width -= parseInt(BX.style(cell, 'border-right-width'));
						}

						cloneCells[index].firstElementChild && (cloneCells[index].firstElementChild.style.width = `${width}px`);

						if (cells.length - 1 > index)
						{
							cloneCells[index].style.width = `${width}px`;
						}
					},
				);
			};

			async ? setTimeout(resizeCloneCells, 0) : resizeCloneCells();

			this.container = BX.decl({
				block: 'main-grid-fixed-bar',
				mix: 'main-grid-fixed-top',
				attrs: {
					style: `width: ${BX.width(this.parent.getContainer())}px`,
				},
				content: {
					block: 'main-grid-table',
					tag: 'table',
					content: cloneThead,
				},
			});

			this.container.hidden = true;

			this.parent.getWrapper().appendChild(this.container);
		},

		getContainer()
		{
			return this.container;
		},

		getFixedTable()
		{
			return this.table || (this.table = BX.Grid.Utils.getByTag(this.getContainer(), 'table', true));
		},

		getAdminPanel()
		{
			if (!this.adminPanel)
			{
				this.adminPanel = document.querySelector('.adm-header');
			}

			return this.adminPanel;
		},

		isAdminPanelPinned()
		{
			return BX.hasClass(document.documentElement, 'adm-header-fixed');
		},

		getPinOffset()
		{
			const adminPanel = this.getAdminPanel();

			if (adminPanel && this.isAdminPanelPinned())
			{
				return BX.Text.toNumber(BX.style(adminPanel, 'height'));
			}

			return 0;
		},

		pin()
		{
			const container = this.getContainer();

			if (container)
			{
				container.hidden = false;
			}

			BX.onCustomEvent(window, 'Grid::headerPinned', []);
		},

		unpin()
		{
			const container = this.getContainer();

			if (container)
			{
				container.hidden = true;
			}

			BX.onCustomEvent(window, 'Grid::headerUnpinned', []);
		},

		stopPin()
		{
			BX.Grid.Utils.styleForEach([this.getContainer()], {
				position: 'absolute',
				top: (`${this.gridRect.bottom - this.rect.height - this.gridRect.top}px`),
				'box-shadow': 'none',
			});
		},

		startPin()
		{
			BX.Grid.Utils.styleForEach([this.getContainer()], {
				position: 'fixed',
				top: `${this.getPinOffset()}px`,
				'box-shadow': '',
			});
		},

		isPinned()
		{
			return !this.getContainer().hidden;
		},

		_onScroll()
		{
			let scrollY = 0;

			if (this.scrollRect)
			{
				scrollY = this.scrollRect.scrollTop;
			}
			else
				if (document.scrollingElement)
				{
					this.scrollRect = document.scrollingElement;
				}
				else
					if (document.documentElement.scrollTop > 0)
					{
						this.scrollRect = document.documentElement;
					}
					else if (document.body.scrollTop > 0)
					{
						this.scrollRect = document.body;
					}

			if (this.gridRect.bottom > (scrollY + this.rect.height))
			{
				this.startPin();

				const offset = this.getPinOffset();

				if ((this.rect.top - offset) <= scrollY)
				{
					!this.isPinned() && this.pin();
				}
				else
				{
					this.isPinned() && this.unpin();
				}
			}
			else
			{
				this.stopPin();
			}
		},

		onAdminPanelMutation()
		{
			this._onScroll();
		},

		_onResize()
		{
			this.rect = BX.pos(this.parent.getHead());
		},
	};
})();
