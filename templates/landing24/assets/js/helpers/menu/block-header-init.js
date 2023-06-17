;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	BX.Landing.BlockHeaders = function ()
	{
		/**
		 * @type {BX.Landing.BlockHeaderEntry[]}
		 */
		this.entries = [];

		this.observer = new IntersectionObserver(
			BX.Landing.BlockHeaderEntry.onIntersection,
			this.getObserverOptions()
		);
	};

	BX.Landing.BlockHeaders.prototype = {
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
			// todo: sort (what do you mean& :-/
			var entry = new BX.Landing.BlockHeaderEntry(node);
			this.entries.push(entry);
			this.observer.observe(entry.getNodeForObserve());
			// todo: how show hidden entries when then in viewport?
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

	BX.Landing.BlockHeaders.getInstance = function ()
	{
		return (
			BX.Landing.BlockHeaders.instance ||
			(BX.Landing.BlockHeaders.instance = new BX.Landing.BlockHeaders())
		);
	};

	var blockHeaders = BX.Landing.BlockHeaders.getInstance();
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;

	onCustomEvent("BX.Landing.Block:init", function (event)
	{
		var headerSelector = event.makeRelativeSelector('.u-header');
		// in edit mode menu must be like a usual block
		if (
			(BX.Landing.getMode() === "view" || BX.Landing.getMode() === "preview")
			&& event.block.querySelectorAll(headerSelector).length > 0)
		{
			blockHeaders.add(event.block.querySelector('.u-header'));
		}
		// todo: scroll-nav (scrollspy)
		// todo: scroll to top (with animate)
	});

})();