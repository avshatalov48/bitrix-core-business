;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	var onCustomEvent = BX.Landing.Utils.onCustomEvent;

	BX.Landing.BlockHeaderSidebar = function ()
	{

	};

	/**
	 * Create entity by node and add in collection
	 * @param {HTMLElement} node
	 */
	BX.Landing.BlockHeaderSidebar.init = function (node) {
		var wrapper = BX.findParent(node, {className: 'block-wrapper'});

		wrapper.style.position = 'sticky';
		wrapper.style.top = '-1px';
		wrapper.style.transitionProperty = 'top';
		wrapper.style.transitionDuration = '.2s';

		onCustomEvent("BX.Landing.BlockAssets.Header:onSetOnTop", function (event)
		{
			wrapper.style.top = event.data.height + 'px';
		});

		onCustomEvent("BX.Landing.BlockAssets.Header:onUnsetOnTop", function (event)
		{
			wrapper.style.top = event.data.height + 'px';
		});

		onCustomEvent("BX.Landing.BlockAssets.Header:onSetInFlow", function (event)
		{
			wrapper.style.top = '0';
		});
	};

	onCustomEvent("BX.Landing.Block:init", function (event)
	{
		var headerSelector = event.makeRelativeSelector('.landing-block-node-navbar');
		// in edit mode menu must be like a usual block
		if (
			(BX.Landing.getMode() === "view" || BX.Landing.getMode() === "preview")
			&& event.block.querySelectorAll(headerSelector).length > 0
		)
		{
			BX.Landing.BlockHeaderSidebar.init(event.block.querySelector(headerSelector));
		}
		// todo: scroll-nav (scrollspy)
		// todo: scroll to top (with animate)
	});
})();