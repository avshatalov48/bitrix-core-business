;(function ()
{
	"use strict";


	// todo: check count of video? f.e. if <5 - not need lazy?

	BX.addCustomEvent(window, "BX.Landing.Block:init", function (event)
	{
		var videos = [].slice.call(event.block.querySelectorAll("[data-source]"));
		if (videos.length)
		{
			videos.forEach(function (element)
			{
				// todo: check api load
				if (BX.Landing.Utils.Matchers.youtube.test(element.dataset.source))
				{
					var src = element.src ? element.src : element.dataset.src;
					// autoplay load immediately
					if (src.indexOf('autoplay=1') !== -1)
					{
						loadPlayerObj(element, {autoplay: 1, mute: 1})
					}
					// div-preview
					else if(element.tagName !== 'IFRAME')
					{
						BX.bind(element, 'click', onPreviewClick);
					}
					// old format
					else
					{
						loadPlayerObj(element);
					}
				}
				else if (element.tagName !== 'IFRAME')
				{
					loadPlayerFrame(element);
				}
			});
		}
	});

	// todo: update handler, update preview if not load, update video if play
	// todo: compare new source and data-source
	// BX.addCustomEvent(window, "BX.Landing.Block:Node:update", function(event) {
	// 	var videos = [].slice.call(event.block.querySelectorAll("[data-source]"));
	// 	if (videos.length)
	// 	{
	// 		BX.addClass(event.node, ['g-brd-red','g-brd-around']);
	// 	}
	// });

	function onPreviewClick(event)
	{
		var playerPreview = event.target;
		loadPlayerObj(playerPreview, {autoplay: 1});
	}

	var scheduledPlayers = [];

	/**
	 *
	 * @param {Element} playerPreview
	 * @param {Object} additionalParams
	 */
	function loadPlayerObj(playerPreview, additionalParams)
	{
		if (typeof YT === "undefined" || typeof YT.Player === "undefined")
		{
			if (!scheduledPlayers.includes(playerPreview))
			{
				scheduledPlayers.push(playerPreview);
			}

			window.onYouTubeIframeAPIReady = function ()
			{
				scheduledPlayers.forEach(function(item){
					loadPlayerObj(item, additionalParams)
				});
			};
		}
		else
		{
			var playerFrame = loadPlayerFrame(playerPreview);
			var player = BX.Landing.MediaPlayer.Factory.create(playerFrame);
			if (
				typeof additionalParams !== 'undefined'
				&& Object.keys(additionalParams).length
			)
			{
				player.parameters = Object.assign(player.parameters, additionalParams);
			}
		}
	}

	/**
	 *
	 * @param {Element} playerPreview
	 * @returns {Element}
	 */
	function loadPlayerFrame(playerPreview)
	{
		// old format - iframe already loaded
		if (playerPreview.tagName === 'IFRAME')
		{
			return playerPreview;
		}

		var playerFrame = BX.create('iframe', {
			props: {
				className: playerPreview.className
			},
			attrs: {
				src: playerPreview.dataset.src,
				frameborder: "0",
				allowfullscreen: "allowfullscreen",
				allow: "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
			},
			dataset: {
				source: playerPreview.dataset.source,
			},
			events: {
				load: function ()
				{
					BX.remove(playerPreview);
				},
			}
		});
		// todo: add loader img for iframe
		playerPreview.parentElement.insertBefore(playerFrame, playerPreview);
		return playerFrame;
	}
})();
