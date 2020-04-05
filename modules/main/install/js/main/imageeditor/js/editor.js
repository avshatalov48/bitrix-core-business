;(function() {
	"use strict";

	BX.namespace("BX.Main");

	var resolver;
	var currentImage;
	var license = JSON.stringify({
		"owner":"Bitrix, Inc.",
		"version":"2.1",
		"enterprise_license":false,
		"available_actions":["magic","filter","transform","sticker","text","adjustments","brush","focus","frames","camera"],
		"features":["adjustment","filter","focus","overlay","transform","text","sticker","frame","brush","camera","library","export"],
		"platform":"HTML5",
		"app_identifiers":[],
		"api_token":"QbbG4guiONSDiVtWkcvw8A",
		"domains":["https://api.photoeditorsdk.com"],
		"issued_at":1534847608,
		"expires_at":null,
		"signature":"QgxAUoamxsnyqgFEQIoyj7168MituWvgVbj8VIr5EBjVG0HZSBmDh3XLU+u3NWTC2GUiZ6FB9GGB0Otf6mZ4VlhiXtyE4Xf61tE+PiFt4LPjGlAURCGl1yT9oGVBdWgb8lu8QhZ224g4TmPzNBeA5lDZwOaS/ESOZjltp0T5RE70NMpSPkSj8HEgO5zX2LnBt0kBpVj7xGxiprFzSn8P30m8+9IX0OuwGJ4AJZnLOB97pz1V1/I50RUgyvRDh7esZ/GdqkewRoGUwkybqHC2oQH15koZThKnEJZ9ufw1JyNVeUDmNvDysDdiLh/zGFgx3yVrBzAxfDAMnQhPpHUhgSlOh1W1YA3TKU7itR2vbXs7sd0syUCvAYMHMjgfUvCBfUKG5d2GOhg1jvd3a+wuVeloTEGwWFnhCpuoY7fHc991inKKCfH4EG4aeAJ5dLnFsZznyOxKMTOWMlmsVMRpW5tjNHP9nSDlj5s5XBX2XVVDkp2gj3oU2znUGY/uc8lczDvHpx7s9PRd7lp5U16QMOXujWWY9iYraNzwyqa2mUrxDhS/PSrlgd8F39iadeIE8bJQHLTVZjlanVZEJwx19MuGEBnYc5SWPkauhVCXFhdlrLj2zIzd1KYEEs1sbMQ4H/IVszF9mHBGJXSZCdOweXiWVHeg0o9UvSyS/sVwZjw="
	});


	/**
	 * Implements interface for works with image editor
	 *
	 * @param {object} [predefinedOptions]
	 *
	 * @property {PhotoEditorSDK.UI.ReactUI} SDKInstance
	 * @property {BX.PopupWindow} popup
	 *
	 * @constructor
	 */
	BX.Main.ImageEditor = function(predefinedOptions)
	{
		this.predefinedOptions = predefinedOptions || {};
		this.popup = new BX.PopupWindow(
			"image-editor-" + BX.util.getRandomString(),
			null,
			{
				zIndex: 900,
				width: window.innerWidth - 10,
				height: window.innerHeight - 10,
				overlay: .9,
				noAllPaddings: true,
				className: "main-image-editor",
				animationOptions: {
					show: {
						className: "main-image-editor-show",
						eventType: "animation"
					},
					close: {
						className: "main-image-editor-close",
						eventType: "animation"
					}
				},
				events: {
					onPopupClose: this.onPopupClose.bind(this)
				}
			}
		);

		this.loader = new BX.Loader();

		BX.bind(window, "resize", this.onWindowResize.bind(this));
	};


	/**
	 * Available ratios
	 */
	BX.Main.ImageEditor.ratio = {
		"CUSTOM": "imgly_transform_common_custom",
		"SQUARE": "imgly_transform_common_square",
		"4/3": "imgly_transform_common_4-3",
		"16/9": "imgly_transform_common_16-9",
		"PROFILE": "imgly_transform_facebook_profile",
		"FB_AD": "imgly_transform_facebook_ad",
		"FB_POST": "imgly_transform_facebook_post",
		"FB_COVER": "imgly_transform_facebook_cover"
	};


	/**
	 * Available export format
	 */
	BX.Main.ImageEditor.renderType = {
		"BASE64": "data-url",
		"IMAGE": "image",
		"BUFFER": "buffer",
		"BLOB": "blob",
		"MSBLOB": "ms-blob"
	};


	/**
	 * Creates instance of image editor SDK
	 * @param {Image} image
	 * @param {Object} [options]
	 * @param {Object} [predefinedOptions]
	 * @return {PhotoEditorSDK.UI.DesktopUI}
	 */
	function createSDKInstance(image, options, predefinedOptions)
	{
		var assets = '/bitrix/js/main/imageeditor/external/photoeditorsdk/assets';

		var forceCrop = options.forceCrop || false;

		var controlsOptions = {
			library: false
		};

		if (BX.type.isPlainObject(predefinedOptions) &&
			BX.type.isPlainObject(predefinedOptions.controlsOptions))
		{
			controlsOptions = predefinedOptions.controlsOptions;
		}

		if (BX.type.isPlainObject(options.controlsOptions))
		{
			controlsOptions = Object.assign(
				controlsOptions,
				options.controlsOptions
			);

			if (BX.type.isPlainObject(controlsOptions.transform) &&
				BX.type.isArray(controlsOptions.transform.categories))
			{
				controlsOptions.transform.categories.forEach(function(category) {
					if (BX.type.isArray(category.ratios))
					{
						category.ratios.forEach(function(ratioItem) {
							if (BX.type.isPlainObject(ratioItem) &&
								BX.type.isPlainObject(ratioItem.dimensions))
							{
								var dimensions = ratioItem.dimensions;
								ratioItem.dimensions = (
									new PhotoEditorSDK.Math.Vector2(dimensions.width, dimensions.height)
								);
							}
						});
					}
				});
			}
		}

		var exportParams = {
			type: BX.Main.ImageEditor.renderType.BASE64,
			download: false
		};

		if (BX.type.isPlainObject(predefinedOptions) &&
			BX.type.isPlainObject(predefinedOptions.export))
		{
			exportParams = predefinedOptions.export;
		}

		if (BX.type.isPlainObject(options.export))
		{
			exportParams = Object.assign({}, exportParams, options.export);
		}

		var megapixels = 30;

		if (BX.type.isNumber(options.megapixels))
		{
			megapixels = options.megapixels;
		}

		var defaultControl = "";

		if (BX.type.isString(options.defaultControl))
		{
			defaultControl = options.defaultControl;
		}


		return new PhotoEditorSDK.UI.DesktopUI({
			container: options.container,
			license: license,
			assets: Object.assign({}, {baseUrl: assets}, options.assets || {}),
			showHeader: false,
			responsive: true,
			preloader: false,
			versionCheck: false,
			logLevel: "error",
			language: "ru",
			editor: {
				preferredRenderer: 'canvas',
				maxMegaPixels: {
					desktop: megapixels
				},
				forceCrop: forceCrop,
				displayCloseButton: true,
				image: image,
				"export": exportParams,
				controlsOptions: controlsOptions,
				defaultControl: defaultControl
			},
			extensions: {
				languages: {
					ru: BX.Main.ImageEditorLocale
				}
			}
		});
	}


	/**
	 * Prepares image for editor
	 * @param {Image|String} image
	 * @param {string} [proxy]
	 * @return {Promise<Image, Error>}
	 */
	function prepareImage(image, proxy)
	{
		return new Promise(function(resolve, reject) {
			if (typeof image === "string")
			{
				var src = image;
				image = new Image();

				if (!!proxy && BX.type.isString(proxy))
				{
					image.src = BX.util.add_url_param(proxy, {
						"sessid": BX.bitrix_sessid(),
						"url": src
					});
				}
				else
				{
					image.src = src;
				}
			}

			if (typeof image === "object" && image instanceof Image)
			{
				if (image.complete)
				{
					resolve(image);
					return;
				}

				image.onload = resolve.bind(null, image);
				image.onerror = reject;
			}
		});
	}


	/**
	 * Gets filename from file path
	 * @param src
	 * @return {string | undefined}
	 */
	function getFileName(src)
	{
		return ("" + src).split("/").pop();
	}


	/**
	 * Gets instance of BX.Main.ImageEditor
	 * @return {BX.Main.ImageEditor}
	 */
	BX.Main.ImageEditor.getInstance = function()
	{
		return (
			BX.Main.ImageEditor.instance || (
				BX.Main.ImageEditor.instance = new BX.Main.ImageEditor()
			)
		);
	};


	BX.Main.ImageEditor.prototype = {
		/**
		 * @typedef {object} EditOptions
		 * @property {String|Image} image
		 * @property {String} [proxy]
		 * @property {boolean} [forceCrop = false]
		 * @property {object} [export]
		 * @property {boolean} [export.type = BX.Main.ImageEditor.renderType.BASE64]
		 * @property {boolean} [export.download = false]
		 * @property {object} [controlsOptions]
		 * @property {object} [controlsOptions.filter]
		 * @property {object} [controlsOptions.focus]
		 * @property {object} [controlsOptions.adjustments]
		 * @property {object} [controlsOptions.transform]
		 * @property {object} [controlsOptions.library]
		 * @property {object} [controlsOptions.text]
		 * @property {object} [controlsOptions.textdesign]
		 * @property {object} [controlsOptions.sticker]
		 * @property {object} [controlsOptions.frame]
		 * @property {object} [controlsOptions.brush]
		 * @property {object} [controlsOptions.overlay]
		 */
		/**
		 * Opens editor for image or url
		 * @param {Image|String|EditOptions} options
		 * @return {Promise<Image>}
		 */
		edit: function(options)
		{
			var image;

			if (BX.type.isPlainObject(options))
			{
				image = options.image;
				delete options.image;
			}
			else
			{
				image = options;
			}

			if (!image)
			{
				throw new Error("Image should be a instance of Image or string path");
			}

			options = BX.type.isPlainObject(options) ? options : {};
			options.container = this.popup.contentContainer;

			this.popup.show();
			document.documentElement.style.overflow = "hidden";

			BX.onCustomEvent(this, "BX.Main.ImageEditor:show", [this]);

			var proxy = options.proxy || this.predefinedOptions.proxy;

			this.loader.show(this.popup.popupContainer);

			return prepareImage(image, proxy)
				.then(function(imageInstance) {
					var dependencies = [
						'main.imageeditor.external.react.production',
						'main.imageeditor.external.photoeditorsdk'
					];

					BX.loadExt(dependencies)
						.then(function() {
							currentImage = imageInstance;

							this.SDKInstance = createSDKInstance(
								imageInstance,
								options,
								this.predefinedOptions
							);

							this.SDKInstance.on("export", BX.proxy(this.onExport, this));
							this.SDKInstance.on("close", BX.proxy(this.popup.close, this.popup));

							this.loader.hide();
						}.bind(this));

					return new Promise(function(resolve) {
						resolver = resolve;
					});
				}.bind(this))
				.catch(function() {
					this.loader.hide();
					this.popup.setContent(this.createErrorMessage());
					return Promise.reject();
				}.bind(this));
		},


		createErrorMessage: function()
		{
			return BX.create("div", {
				props: {className: 'main-image-editor-error'},
				children: [
					BX.create('div', {
						props: {className: 'main-image-editor-error-text'},
						html: BX.message('IMAGE_EDITOR_POPUP_ERROR_MESSAGE_TEXT')
					}),
					BX.create('div', {
						children: [
							BX.create('button', {
								props: {className: 'ui-btn'},
								text: BX.message('IMAGE_EDITOR_CLOSE_POPUP'),
								events: {
									click: function() {
										this.popup.close();
									}.bind(this)
								}
							})
						]
					})
				]
			})
		},


		/**
		 * Applies changes
		 */
		apply: function()
		{
			this.SDKInstance.export();
		},


		/**
		 * Closes the editor
		 */
		close: function()
		{
			this.popup.close();
		},


		/**
		 * @private
		 */
		onPopupClose: function()
		{
			if (this.SDKInstance)
			{
				this.SDKInstance.off("export", BX.proxy(this.onExport, this));
				this.SDKInstance.off("close", BX.proxy(this.popup.close, this.popup));
				this.SDKInstance.dispose();
			}

			this.popup.contentContainer.innerHTML = "";
			document.documentElement.style.overflow = null;

			BX.onCustomEvent(this, "BX.Main.ImageEditor:close", [this]);
		},


		/**
		 * @private
		 * @param {string} result
		 * @param {PhotoEditorSDK.UI.DesktopUI.Editor} editor
		 */
		onExport: function(result, editor)
		{
			if (editor.getOptions().editor.export.type === BX.Main.ImageEditor.renderType.BASE64)
			{
				var fileName = getFileName(currentImage.src);
				var splitted = result.split(",");
				var base64Data = splitted[1];
				var format = splitted[0].match(new RegExp("data\:image\/(.*);base64"))[1];
				format = format === "jpeg" ? "jpg" : format;
				fileName = fileName.replace(/\.[^\.]+$/, "." + format);

				result = [fileName, base64Data];
			}

			resolver(result);
			this.popup.close();
		},


		/**
		 * @private
		 */
		onWindowResize: function()
		{
			this.popup.setWidth(window.innerWidth - 10);
			this.popup.setHeight(window.innerHeight - 10);
		}
	};
})();