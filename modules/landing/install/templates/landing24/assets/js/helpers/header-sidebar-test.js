;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	var onCustomEvent = BX.Landing.Utils.onCustomEvent;

	BX.Landing.BlockHeaderSidebar = function ()
	{
		/**
		 * @type {BX.Landing.BlockHeaderEntry[]}
		 */
		this.entries = [];

		this.observer = new IntersectionObserver(
			BX.Landing.BlockHeaderSidebar.onIntersection,
			this.getObserverOptions()
		);
	};

	/**
	 * @param {IntersectionObserverEntry[]} entries
	 */
	BX.Landing.BlockHeaderSidebar.onIntersection = function(entries)
	{
		entries.forEach(function(entry)
		{
			// var headerHeight = null
			// // find headers for offset
			// document.querySelectorAll('.u-header.js-header-on-top').forEach(function(header){
			// 	headerHeight = Math.max(headerHeight, header.offsetHeight);
			// });
			//
			// if(headerHeight)
			// {
			// 	entry.target.style.top = headerHeight + 'px';
			// }
		});
	}

	BX.Landing.BlockHeaderSidebar.prototype = {
		getObserverOptions: function ()
		{
			return {threshold: [0, 1]};
		},


		/**
		 * Create entity by node and add in collection
		 * @param {HTMLElement} node
		 */
		add: function (node)
		{
			this.node = node;
			this.wrapper = BX.findParent(this.node, {className: 'block-wrapper'});

			this.wrapper.style.position = 'sticky';
			this.wrapper.style.top = '-1px';
			this.wrapper.style.transitionProperty = 'top';
			this.wrapper.style.transitionDuration = '.2s';

			this.observer.observe(this.wrapper);

			onCustomEvent("BX.Landing.BlockAssets.Header:onSetOnTop", function (event)
			{
				console.log("event set", event.data.height);
				this.wrapper.style.top = event.data.height + 'px';
			}.bind(this));

			onCustomEvent("BX.Landing.BlockAssets.Header:onUnsetOnTop", function (event)
			{
				console.log("event UNSET", event.data.height);
				this.wrapper.style.top = event.data.height + 'px';
			}.bind(this));

			onCustomEvent("BX.Landing.BlockAssets.Header:onSetInFlow", function (event)
			{
				console.log("event onSetInFlow", event.data.height);
				this.wrapper.style.top = '0px';
			}.bind(this));
		},

		/**
		 * Get entry by node
		 * @param {HTMLElement} node
		 * @returns {BX.Landing.BlockHeaderEntry}
		 */
		getEntryByIntersectionTarget: function (node)
		{
			var result = null;
			this.entries.forEach(function (entry)
			{
				if (node === entry.getNodeForObserve())
				{
					result = entry;
					return true;
				}
			});

			return result;
		},

		hidePrevEntriess: function ()
		{
			// todo: how show hidden entries when then in viewport?
		}
	};

	BX.Landing.BlockHeaderSidebar.getInstance = function ()
	{
		return (
			BX.Landing.BlockHeaderSidebar.instance ||
			(BX.Landing.BlockHeaderSidebar.instance = new BX.Landing.BlockHeaderSidebar())
		);
	};

	var headersSidebar = BX.Landing.BlockHeaderSidebar.getInstance();

	onCustomEvent("BX.Landing.Block:init", function (event)
	{
		var headerSelector = event.makeRelativeSelector('.landing-block-node-navbar');
		// in edit mode menu must be like a usual block
		if (BX.Landing.getMode() === "view" && event.block.querySelectorAll(headerSelector).length > 0)
		{
			headersSidebar.add(event.block.querySelector('.landing-block-node-navbar'));
		}
		// todo: scroll-nav (scrollspy)
		// todo: scroll to top (with animate)
	});
})();