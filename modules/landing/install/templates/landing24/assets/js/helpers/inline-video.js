;(function()
{
	"use strict";

	BX.addCustomEvent(window, "BX.Landing.Block:init", function (event)
	{
		initBlock([].slice.call(event.block.querySelectorAll('[data-source]')));
	});

	BX.addCustomEvent("BX.Landing.Block:Node:update", function (event)
	{
		if (event.node && event.node.hasAttribute('data-source'))
		{
			initBlock([].slice.call(event.block.querySelectorAll('[data-source]')));
		}
	});

	function initBlock(videos)
	{
		if (videos.length)
		{
			videos.forEach(function (video)
			{
				video = resetPlayerPreview(video);
				const source = video.dataset.source;
				const src = video.src ||  video.dataset.src;

				if (
					BX.Landing.Utils.Matchers.youtube.test(source)
					|| BX.Landing.Utils.Matchers.youtube.test(src)
				)
				{
					if (src.indexOf('autoplay=1') !== -1 && BX.Landing.getMode() !== 'edit')
					{
						loadPlayerYT(video, {autoplay: 1, mute: 1})
					}
					// div-preview
					else if (video.tagName !== 'IFRAME')
					{
						BX.bind(video, 'click', onYTPreviewClick);
					}
					// old format
					else
					{
						loadPlayerYT(video);
					}
				}
				else if (
					BX.Landing.Utils.Matchers.vimeo.test(source)
					|| BX.Landing.Utils.Matchers.vine.test(source)
					|| BX.Landing.Utils.Matchers.facebookVideos.test(source)
					|| BX.Landing.Utils.Matchers.vk.test(source)
					|| BX.Landing.Utils.Matchers.rutube.test(video.dataset.source)
				)
				{
					loadPlayerFrame(video);
				}
				else
				{
					showError(video)
				}
			});
		}
	}

	function showError(node)
	{
		node.classList.remove('g-video-preview');

		node.classList.add('g-video-preview-error');
		node.innerHTML = '<div class="g-landing-alert-v2">' +
			'<div class="g-landing-alert-title">' +
			BX.message('LANDING_VIDEO_ALERT_WRONG_SOURCE') +
			'</div>' +
			'<div class="g-landing-alert-text">' +
			BX.message('LANDING_VIDEO_ALERT_WRONG_SOURCE_TEXT_2') +
			'</div>' +
			'</div>';
	}

	function resetPlayerPreview(playerPreview)
	{
		// convert to div
		if (playerPreview.tagName === 'IFRAME')
		{
			var clearPlayerPreview = BX.create('div', {
				props: {
					className: playerPreview.className
				},
				style: {
					backgroundImage: 'url('+ playerPreview.dataset.preview +')'
				},
				dataset: {
					src: playerPreview.src || playerPreview.dataset.src,
					source: playerPreview.dataset.source
				}
			});

			BX.insertBefore(clearPlayerPreview, playerPreview);
			BX.remove(playerPreview);
			return clearPlayerPreview;
		}

		// clear events
		BX.unbind(playerPreview, 'click', onYTPreviewClick);

		// clear errors
		playerPreview.classList.add('g-video-preview');
		playerPreview.classList.remove('g-video-preview-error');
		playerPreview.innerHTML = '';

		return playerPreview;
	}

	var scheduledPlayers = [];

	function onYTPreviewClick(event)
	{
		var playerPreview = event.target;
		loadPlayerYT(playerPreview, {autoplay: 1});
	}

	/**
	 *
	 * @param {Element} playerPreview
	 * @param {Object} additionalParams
	 */
	function loadPlayerYT(playerPreview, additionalParams)
	{
		if (typeof YT === "undefined" || typeof YT.Player === "undefined")
		{
			if (!scheduledPlayers.includes(playerPreview))
			{
				scheduledPlayers.push(playerPreview);
			}

			window.onYouTubeIframeAPIReady = function ()
			{
				scheduledPlayers.forEach(function (item)
				{
					loadPlayerYT(item, additionalParams)
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
	 * @param {HTMLElement} playerPreview
	 * @returns {HTMLElement}
	 */
	function loadPlayerFrame(playerPreview)
	{
		// old format - iframe already loaded
		if (playerPreview.tagName === 'IFRAME')
		{
			return playerPreview;
		}

		const selector = playerPreview.className;
		playerPreview.className = 'landing-node-player-preview';

		const playerFrame = BX.create('iframe', {
			props: {
				className: selector
			},
			attrs: {
				src: BX.util.htmlspecialcharsback(playerPreview.dataset.src),
				frameborder: "0",
				allowfullscreen: "allowfullscreen",
				allow: "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
			},
			dataset: {
				src: BX.util.htmlspecialcharsback(playerPreview.dataset.src),
				source: BX.util.htmlspecialcharsback(playerPreview.dataset.source),
			},
			events: {
				load: function ()
				{
					BX.remove(playerPreview);
					loader.hide();
				}
			}
		});
		const loader = new BX.Loader({
			target: playerPreview
		});
		loader.show();

		BX.insertBefore(playerFrame, playerPreview);
		return playerFrame;
	}
})();
