;(function ()
{
	"use strict";

	BX.namespace("BX.Landing.VideoBGHelper");
	var videoBG = BX.Landing.VideoBGHelper;

	BX.Landing.VideoBGHelper.config = {
		videoIframeClass: "js-videobg",
		videoLinkClass: "js-videobg-link",
		videoContainerClass: "js-videobg-container",
		dataLink: "video-url"
	};

	BX.Landing.VideoBGHelper.init = function (node, type, params)
	{
		if (node.querySelectorAll('.js-videobg-link').length > 0)
		{
			// todo: foreach link node for multiple videos in one blocks
			var linkNode = node.querySelector('.' + videoBG.config.videoLinkClass);
			var href = BX.data(linkNode, videoBG.config.dataLink),
				container = linkNode.querySelector('.' + videoBG.config.videoContainerClass);

			// todo: not work in PUBLIC
			var ServiceFactory = new BX.Landing.MediaService.Factory();
			var mediaService = ServiceFactory.create(href, params);

			// incorrect link
			if (!mediaService)
			{
				BX.adjust(container, {'html': 'incorrect link'});
				return;
			}

			var iframe = mediaService.getEmbedElement();
			BX.adjust(iframe, {
				'props': {'className': 'embed-responsive-item' + ' ' + videoBG.config.videoIframeClass},
				'attrs': {
					webkitallowfullscreen: true,
					mozallowfullscreen: true,
					allowfullscreen: true
				}
			});
			BX.adjust(container, {'children': [iframe]});

			// unique ID for player container
			if (iframe.id === "")
			{
				// todo: rand_id for multiple videos in one blocks
				BX.adjust(iframe, {attrs: {'id': type + '_player_' + node.id}});
			}

			// save players objects
			if (!window["videobg_" + type])
			{
				window["videobg_" + type] = {};
			}
			// add current iblock to list
			if (!window["videobg_" + type]["containers"])
			{
				window["videobg_" + type]["containers"] = {};
			}
			window["videobg_" + type]["containers"][type + '_player_' + node.id] = {
				id: type + '_player_' + node.id
			};
		}
	};
})();