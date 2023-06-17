(function() {

	'use strict';

	BX.namespace('BX.Landing.Component');

	BX.Landing.Component.Demo = function()
	{
		this.wrapper = BX('grid-tile-wrap');
		this.inner = BX('grid-tile-inner');
		this.navigation = BX('landing-demo-navigation');
		this.workarea = BX('workarea-content');
		this.loader = new BX.Loader();

		if (!this.wrapper || !this.inner)
		{
			return;
		}

		if (!this.navigation)
		{
			this.navigation = BX.create('div', {
				props: {
					id: 'landing-demo-navigation',
					className: 'landing-navigation',
				}
			});
			BX.Dom.insertAfter(this.navigation, this.wrapper);
		}

		// show title
		this.isTitleShow = false;
		this.startHeight = 0;
		this.tileTitleCurrent = null;

		// event handlers
		this.onTileTitleClick = this.onTileTitleClick.bind(this);
		this.onTileTitleLeave = this.onTileTitleLeave.bind(this);
		this.onPseudoLinkClick = this.onPseudoLinkClick.bind(this);

		this.init();

		// event on app install
		top.BX.addCustomEvent(
			top,
			'Rest:AppLayout:ApplicationInstall',
			BX.delegate(this.onAppInstall, this)
		);

		BX.addCustomEvent(
			'BX.Main.Filter:apply',
			BX.delegate(this.onFilterApply, this)
		);

		BX.addCustomEvent(
			'BX.Main.Filter:beforeApply',
			BX.delegate(this.onBeforeFilterApply, this)
		);
	};

	BX.Landing.Component.Demo.CLASS_TILE = 'landing-item';
	BX.Landing.Component.Demo.CLASS_DESC_OPEN = 'landing-item-desc-open';
	BX.Landing.Component.Demo.CLASS_DESC_DESIGNED = 'landing-item-designed';

	BX.Landing.Component.Demo.prototype =
	{
		init: function()
		{
			// links handlers
			this.links = [].slice.call(this.inner.querySelectorAll('.landing-template-pseudo-link'));
			this.bindPseudoLinks();

			this.linkEmpty = BX('landing-demo-empty');
			BX.Event.bind(this.linkEmpty, 'click', this.onPseudoLinkClick);

			// tiles
			this.tiles = [].slice.call(this.inner.querySelectorAll('.' + BX.Landing.Component.Demo.CLASS_TILE));
			this.createTileList();
			this.bindTileTitles();
		},

		reinit: function()
		{
			this.unbindPseudoLinks();
			this.unbindTileTitles();

			this.tiles = [];
			this.links = [];

			this.init();
		},

		bindPseudoLinks: function()
		{
			this.links.forEach(link => {
				if (!BX.Dom.hasClass(link, 'landing-item-payment'))
				{
					BX.Event.bind(link, 'click', this.onPseudoLinkClick);
				}
			});
		},

		unbindPseudoLinks: function()
		{
			this.links.forEach(link => {
				if (!BX.Dom.hasClass(link, 'landing-item-payment'))
				{
					BX.Event.unbind(link, 'click', this.onPseudoLinkClick);
				}
			});
		},

		onPseudoLinkClick: function(event)
		{
			// scip label and title button
			if (
				BX.Dom.hasClass(event.target, BX.Landing.Component.Demo.CLASS_DESC_OPEN)
				|| BX.Dom.hasClass(event.target, BX.Landing.Component.Demo.CLASS_DESC_DESIGNED)
			)
			{
				return;
			}

			const link = event.currentTarget;
			const sliderHref = event.currentTarget.dataset.href;

			BX.SidePanel.Instance.open(sliderHref, {
				allowChangeHistory: false,
				width: link.dataset.sliderWidth ? parseInt(link.dataset.sliderWidth) : null,
				data: {
					rightBoundary: 0,
				},
				customLeftBoundary: 60,
				events: {
					onClose: function (eventClosed)
					{
						const openerSliderPath = sliderHref.split('?')[0];
						const currentSliderPath = eventClosed.slider.iframeSrc.split('?')[0];
						if (openerSliderPath !== currentSliderPath && sliderHref.indexOf('frameMode=Y') < 0)
						{
							top.location.reload();
						}
					},
				},
			});
		},

		createTileList: function ()
		{
			new BX.Landing.TileGrid({
				wrapper: this.wrapper,
				inner: this.inner,
				tiles: this.tiles,
				sizeSettings : {
					minWidth : 250,
					maxWidth: 281
				}
			});
		},

		bindTileTitles: function ()
		{
			this.tiles.forEach(tile =>
			{
				const openBtn = tile.querySelector('.' + BX.Landing.Component.Demo.CLASS_DESC_OPEN);
				if (openBtn)
				{
					BX.Event.bind(openBtn, 'click', this.onTileTitleClick);
					BX.Event.bind(tile, 'mouseleave', this.onTileTitleLeave);
				}

			}, this);
		},

		unbindTileTitles: function ()
		{
			this.tiles.forEach(tile =>
			{
				const openBtn = tile.querySelector('.' + BX.Landing.Component.Demo.CLASS_DESC_OPEN);
				if (openBtn)
				{
					BX.Event.unbind(openBtn, 'click', this.onTileTitleClick);
					BX.Event.unbind(openBtn, 'mouseleave', this.onTileTitleLeave);
				}

			}, this);
		},

		onTileTitleClick: function(event)
		{
			event.preventDefault();
			const tile = event.currentTarget.closest('.' + BX.Landing.Component.Demo.CLASS_TILE);
			this.showTileTitle(tile);
		},

		onTileTitleLeave: function(event)
		{
			const tile = event.currentTarget.closest('.' + BX.Landing.Component.Demo.CLASS_TILE);
			this.hideTileTitle(tile);
		},

		showTileTitle : function(tile)
		{
			this.tileTitleCurrent = tile.querySelector('.landing-item-desc-inner');

			const descHeightBlock = tile.querySelector('.landing-item-desc-height');

			tile.classList.add('landing-tile-title-show');
			this.startHeight = BX.style(this.tileTitleCurrent, 'height');
			this.tileTitleCurrent.style.height = descHeightBlock.offsetHeight + 'px';

			this.isTitleShow = true;
		},

		hideTileTitle: function (tile)
		{
			if (this.isTitleShow)
			{
				this.tileTitleCurrent.style.height = this.startHeight;

				setTimeout(function ()
				{
					tile.classList.remove('landing-tile-title-show');
					this.tileTitleCurrent.style.paddingTop = 0;
					this.tileTitleCurrent.style.marginBottom = 0;
				}.bind(this), 230);

				this.isTitleShow = false;
			}
		},

		onAppInstall: function(installed)
		{
			window.location.reload();
		},

		onBeforeFilterApply: function()
		{
			this.loader.show(this.workarea);
		},

		onFilterApply: function(filterId, values, filterInstance, promise, params)
		{
			if (params)
			{
				params.autoResolve = false;
			}

			BX.ajax({
				method: 'POST',
				dataType: 'html',
				url: BX.Landing.Component.Demo.ajaxPath,
				onsuccess: data => {
					const result = BX.Dom.create('div', {html: data});
					this.inner.innerHTML = result.querySelector('#grid-tile-inner').innerHTML;



					const navigation = result.querySelector('#landing-demo-navigation');
					if (navigation)
					{
						this.navigation.innerHTML = result.querySelector('#landing-demo-navigation').innerHTML;
					}
					else
					{
						BX.Dom.clean(this.navigation);
					}

					this.reinit();
					promise.fulfill();
					this.loader.hide();
				}
			});
		},
	};

})();

BX.ready(function ()
{
	new BX.Landing.Component.Demo();
});