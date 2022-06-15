;(function () {
	"use strict";

	BX.namespace("BX.Landing.MediaService");

	/**
	 * Implements interface for works with Vk
	 * @inheritDoc
	 */
	BX.Landing.MediaService.Vk = function (url, settings) {
		BX.Landing.MediaService.BaseMediaService.apply(this, arguments);
		this.matcher = BX.Landing.Utils.Matchers.vk;
		this.isDataLoaded = false;
		this.loadEmbedInfo().then(res => {
			this.isDataLoaded = true;
			BX.onCustomEvent(this, 'onDataLoaded');
		});
		this.embedInfo = null;
		this.embedURL = () => {
			if (this.embedInfo)
			{
				const params = BX.util.objectMerge(
					this.getSettings(),
					this.getUserSettings(),
				);
				if (this.isAnotherService() && params.autoplay == 1)
				{
					params.mute = 1;
				}
				this.embedInfo.embedUrl = BX.util.add_url_param(this.embedInfo.embedUrl, params);
			}

			return this.embedInfo ? this.embedInfo['embedUrl'] : '';
		};
		this.previewURL = () => {
			return this.embedInfo ? this.embedInfo['preview'] : '';
		};
		this.idPlace = 2;
		this.params = {
			autoplay: 0,
		};
	};

	/**
	 * Checks that URL is valid Vk url
	 * @param {string} url
	 * @return {boolean}
	 */
	BX.Landing.MediaService.Vk.validate = function (url) {
		return BX.Landing.Utils.Matchers.vk.test(url);
	};

	BX.Landing.MediaService.Vk.prototype = {
		constructor: BX.Landing.MediaService.Vk,
		__proto__: BX.Landing.MediaService.BaseMediaService.prototype,

		/**
		 * Gets settings form
		 * @return {BX.Landing.UI.Form.BaseForm}
		 */
		getSettingsForm: function () {
			if (!this.form)
			{
				this.form = new BX.Landing.UI.Form.BaseForm();

				var settings = this.getSettings();

				this.form.addField(
					new BX.Landing.UI.Field.Dropdown({
						title: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_AUTOPLAY"),
						description: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_AUTOPLAY_DESC_NEW"),
						selector: "autoplay",
						content: parseInt(settings.autoplay),
						items: [
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_YES"), value: 1},
							{name: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_NO"), value: 0},
						],
					})
				);
			}

			return this.form;
		},


		/**
		 * Request info for embedding from VK
		 * @return {Promise}
		 */
		loadEmbedInfo: function()
		{
			const backend = BX.Landing.Backend.getInstance();
			return backend
				.action('Vk::isAuthorized')
				.then(result => {
					if (!result)
					{
						return backend.action('Vk::getAuthUrl').then(url => {
							return new Promise(resolve => {
								BX.addCustomEvent(window, 'seo-client-auth-result', eventData => {
									eventData.reload = false;
									resolve();
								});

								BX.util.popup(url,800,600);
							});
						});
					}

					return Promise.resolve();
				})
				.then(() => {
					const matchedUrl = this.url.match(this.matcher);
					if (matchedUrl && matchedUrl.length)
					{
						const videoId = matchedUrl[this.idPlace];
						return backend
							.action('Vk::getVideoInfo', {videoId: videoId})
							.then(result => {
								this.embedInfo = {
									embedUrl: result.player,
									preview: result.preview.url,
								};

								this.isAnotherService(this.embedInfo.embedUrl);
								{
									this.convertToAnotherService(this.embedInfo.embedUrl);
								}

								return this.embedInfo;
							});
					}

					return Promise.reject('Wrong VK video url');
				});
		},

		isAnotherService: function ()
		{
			return BX.Landing.Utils.Matchers.youtube.test(this.embedInfo.embedUrl);
		},

		convertToAnotherService: function ()
		{
			const embedUrl = this.embedInfo.embedUrl;
			if (BX.Landing.Utils.Matchers.youtube.test(embedUrl))
			{
				const videoId = embedUrl.match(BX.Landing.Utils.Matchers.youtube)[4];
				this.url = 'https://www.youtube.com/watch?v=' + videoId;
			}
		},
	};
})();