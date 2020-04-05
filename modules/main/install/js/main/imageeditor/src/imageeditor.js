import {PopupWindow} from 'main.popup';
import {Text, Event, Cache, Loc, Tag, Dom, Type, Runtime} from 'main.core';
import {Loader} from 'main.loader';
import defaultOptions from './internal/default-options';
import license from './internal/license';
import locale from './internal/locale';
import loadImage from './internal/load-image';
import getFileName from './internal/get-file-name';
import changeFileExtension from './internal/change-file-extension';
import './css/style.css';
import adjustTransformOptions from './internal/adjust-transform-options';

const onPopupClose = Symbol('onPopupClose');
const onWindowResize = Symbol('onWindowResize');
const onEditorExport = Symbol('onEditorExport');
const onEditorClose = Symbol('onEditorClose');
const currentImage = Symbol('currentImage');
const resolver = Symbol('resolver');

export class ImageEditor
{
	static getInstance()
	{
		if (!ImageEditor.instance)
		{
			ImageEditor.instance = new ImageEditor();
		}

		return ImageEditor.instance;
	}

	static ratio = {
		CUSTOM: 'imgly_transform_common_custom',
		SQUARE: 'imgly_transform_common_square',
		'4/3': 'imgly_transform_common_4-3',
		'16/9': 'imgly_transform_common_16-9',
		PROFILE: 'imgly_transform_facebook_profile',
		FB_AD: 'imgly_transform_facebook_ad',
		FB_POST: 'imgly_transform_facebook_post',
		FB_COVER: 'imgly_transform_facebook_cover',
	};

	static renderType = {
		BASE64: 'data-url',
		IMAGE: 'image',
		BUFFER: 'buffer',
		BLOB: 'blob',
		MSBLOB: 'ms-blob',
	};

	constructor(options = {})
	{
		this.options = options;
		this.SDKInstance = null;

		this[onPopupClose] = this[onPopupClose].bind(this);
		this[onWindowResize] = this[onWindowResize].bind(this);
		this[onEditorExport] = this[onEditorExport].bind(this);
		this[onEditorClose] = this[onEditorClose].bind(this);

		this.cache = new Cache.MemoryCache();
		this.popup = this.getPopup();
		this.loader = this.getLoader();

		Event.bind(window, 'resize', this[onWindowResize]);
	}

	getPopup(): PopupWindow
	{
		return this.cache.remember('popup', () => {
			return new PopupWindow({
				id: `main-image-editor-${Text.getRandom()}`,
				width: window.innerWidth - 10,
				height: window.innerHeight - 10,
				zIndex: 900,
				overlay: 0.9,
				noAllPaddings: true,
				className: 'main-image-editor',
				animationOptions: {
					show: {
						className: 'main-image-editor-show',
						eventType: 'animation',
					},
					close: {
						className: 'main-image-editor-close',
						eventType: 'animation',
					},
				},
				events: {
					onPopupClose: this[onPopupClose],
				},
			});
		});
	}

	getLoader(): Loader
	{
		return this.cache.remember('loader', () => {
			return new Loader({target: this.getPopup().getPopupContainer()});
		});
	}

	show()
	{
		this.getPopup().show();
		Dom.style(document.documentElement, 'overflow', 'hidden');
	}

	close()
	{
		this.getPopup().close();
		Dom.style(document.documentElement, 'overflow', null);
	}

	[onEditorClose]()
	{
		this.close();
		Dom.clean(this.popup.contentContainer);
	}

	[onEditorExport](result, editor)
	{
		const options = editor.getOptions();
		const {BASE64} = BX.Main.ImageEditor.renderType;

		if (
			Type.isPlainObject(options)
			&& Type.isPlainObject(options.editor)
			&& Type.isPlainObject(options.editor.export)
			&& options.editor.export.type === BASE64
		)
		{
			const [meta, base64] = result.split(',');
			const [, fileExtension] = meta.match(/data:image\/(.*);base64/);
			const fileName = changeFileExtension(
				getFileName(this[currentImage].src),
				fileExtension,
			);

			this[resolver]([fileName, base64]);
			this.close();
			return;
		}

		this[resolver](result);
		this.close();
	}

	[onPopupClose]()
	{
		if (this.SDKInstance)
		{
			this.SDKInstance.off('export', this[onEditorExport]);
			this.SDKInstance.off('close', this[onEditorClose]);
			this.SDKInstance.dispose();
		}

		BX.onCustomEvent(this, 'BX.Main.ImageEditor:close', [this]);
	}

	[onWindowResize]()
	{
		const {innerWidth, innerHeight} = window;
		this.getPopup().setWidth(innerWidth - 10);
		this.getPopup().setHeight(innerHeight - 10);
	}

	createErrorMessage(): HTMLDivElement
	{
		return this.cache.remember('errorMessage', () => {
			const onButtonClick = () => this.getPopup().close();

			return Tag.render`
				<div class="main-image-editor-error">
					<div class="main-image-editor-error-text">
						${Loc.getMessage('IMAGE_EDITOR_POPUP_ERROR_MESSAGE_TEXT')}
					</div>
					<div>
						<button class="ui-btn" onclick="${onButtonClick}">
							${Loc.getMessage('IMAGE_EDITOR_CLOSE_POPUP')}
						</button>
					</div>
				</div>
			`;
		});
	}

	isValidEditOptions(options): boolean
	{
		return (
			(Type.isDomNode(options) && options instanceof HTMLImageElement)
			|| (Type.isString(options) && options.length > 0)
			|| (Type.isPlainObject(options) && this.isValidEditOptions(options.image))
		);
	}

	apply()
	{
		this.SDKInstance.export();
	}

	edit(options: Object | HTMLImageElement): Promise<any>
	{
		if (!this.isValidEditOptions(options))
		{
			throw new Error('BX.Main.ImageEditor: invalid options. options must be a string, HTMLImageElement or plainObject with image field.');
		}

		const config = (() => {
			const container = this.getPopup().contentContainer;

			if (Type.isPlainObject(options))
			{
				const {controlsOptions} = options;

				if (
					Type.isPlainObject(controlsOptions)
					&& Type.isPlainObject(controlsOptions.transform)
				)
				{
					controlsOptions.transform = adjustTransformOptions(
						controlsOptions.transform,
					);
				}

				return Runtime.merge(
					defaultOptions,
					options,
					{container},
				);
			}

			return {
				...defaultOptions,
				image: options,
				container,
			};
		})();

		this.show();
		this.getLoader().show();

		BX.onCustomEvent(this, 'BX.Main.ImageEditor:show', [this]);

		return loadImage({src: config.image, proxy: config.proxy})
			.then((image) => {
				this[currentImage] = image;

				return Runtime.loadExtension([
					'main.imageeditor.external.react.production',
					'main.imageeditor.external.photoeditorsdk',
				]);
			})
			.then(() => {
				const {DesktopUI} = window.PhotoEditorSDK.UI;

				this.SDKInstance = new DesktopUI({
					container: config.container,
					assets: config.assets,
					showHeader: false,
					responsive: true,
					preloader: false,
					versionCheck: false,
					logLevel: 'error',
					language: 'ru',
					editor: {
						preferredRenderer: config.preferredRenderer,
						maxMegaPixels: {
							desktop: config.megapixels,
						},
						forceCrop: config.forceCrop,
						displayCloseButton: true,
						export: config.export,
						controlsOptions: config.controlsOptions,
						defaultControl: config.defaultControl,
						image: this[currentImage],
					},
					extensions: {
						languages: {
							ru: locale,
						},
					},
					license: JSON.stringify(license),
				});

				this.SDKInstance.on('export', this[onEditorExport]);
				this.SDKInstance.on('close', this[onEditorClose]);

				this.getLoader().hide();

				return new Promise((resolve) => {
					this[resolver] = resolve;
				});
			});
	}
}