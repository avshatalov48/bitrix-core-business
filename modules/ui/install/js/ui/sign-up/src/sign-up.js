import {Cache, Dom, Loc, Tag, Type, Runtime} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Tabs} from './tabs/tabs';
import {Footer} from './footer/footer';

import {InitialsContent} from './content/initials/initials';
import {TouchContent} from './content/touch/touch';
import {PhotoContent} from './content/photo/photo';

import InitialsTabIcon from './images/initials.svg';
import InitialsActiveTabIcon from './images/initials-active.svg';
import TouchTabIcon from './images/touch.svg';
import TouchActiveTabIcon from './images/touch-active.svg';
import PhotoTabIcon from './images/photo.svg';
import PhotoActiveTabIcon from './images/photo-active.svg';

import type {SignUpOptions} from './types/sign-up-options';

import './css/style.css';

/**
 * @memberOf BX.UI
 */
export class SignUp extends EventEmitter
{
	cache = new Cache.MemoryCache();

	static MIN_PIXELS_REQUIRED = 100;

	constructor(options: SignUpOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.SignUp');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);

		this.onChangeDebounced = Runtime.debounce(this.onChangeDebounced, 200, this);

		if (!this.hasValue())
		{
			this.getFooter().getSaveButton().setDisabled(true);
		}
	}

	setOptions(options: SignUpOptions)
	{
		this.cache.set('options', {mode: 'desktop', ...options});
	}

	getOptions(): SignUpOptions
	{
		return this.cache.get('options', {});
	}

	getFooter(): Footer
	{
		return this.cache.remember('footer', () => {
			return new Footer({
				mode: this.getOptions().mode,
				events: {
					onSaveClickAsync: () => {
						return this.emitAsync('onSaveClickAsync');
					},
					onSaveClick: () => {
						this.emit('onSaveClick');
					},
					onCancelClick: () => {
						this.emit('onCancelClick');
					},
				},
			});
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-sign-up">
					${this.getTabs().getLayout()}
					${this.getFooter().getLayout()}
				</div>
			`;
		});
	}

	renderTo(target: HTMLElement)
	{
		if (!Type.isDomNode(target))
		{
			throw new TypeError('Target is not a HTMLElement');
		}

		Dom.append(this.getLayout(), target);
	}

	getInitialsContent(): InitialsContent
	{
		return this.cache.remember('initialsContent', () => {
			return new InitialsContent({
				events: {
					onChange: this.onChangeDebounced,
				},
				color: this.getOptions().signColor,
			});
		});
	}

	getTouchContent(): TouchContent
	{
		return this.cache.remember('touchContent', () => {
			return new TouchContent({
				mode: this.getOptions().mode,
				events: {
					onChange: this.onChangeDebounced,
				},
				color: this.getOptions().signColor,
			});
		});
	}

	getPhotoContent(): PhotoContent
	{
		return this.cache.remember('photoContent', () => {
			return new PhotoContent({
				mode: this.getOptions().mode,
				events: {
					onChange: this.onChangeDebounced,
				},
			});
		});
	}

	getTabs(): Tabs
	{
		return this.cache.remember('tabs', () => {
			return new Tabs({
				defaultState: this.getOptions().defaultState,
				tabs: [
					{
						id: 'initials',
						header: Loc.getMessage('UI_SIGN_UP_TAB_INITIALS_TITLE'),
						icon: InitialsTabIcon,
						activeIcon: InitialsActiveTabIcon,
						content: this.getInitialsContent(),
					},
					{
						id: 'touch',
						header: Loc.getMessage('UI_SIGN_UP_TAB_TOUCH_TITLE'),
						icon: TouchTabIcon,
						activeIcon: TouchActiveTabIcon,
						content: this.getTouchContent(),
					},
					{
						id: 'photo',
						header: Loc.getMessage('UI_SIGN_UP_TAB_PHOTO_TITLE'),
						icon: PhotoTabIcon,
						activeIcon: PhotoActiveTabIcon,
						content: this.getPhotoContent(),
					},
				],
			});
		});
	}

	getCanvas(): HTMLCanvasElement
	{
		return this.getTabs().getCurrentTab().getContent().getCanvas().getLayout();
	}

	onChangeDebounced()
	{
		this.getFooter().getSaveButton().setDisabled(!this.hasValue());
	}

	hasValue(): boolean
	{
		const canvas = this.getCanvas();
		const context = canvas.getContext('2d');

		const pixelBuffer = new Uint32Array(
			context.getImageData(0, 0, canvas.width, canvas.height).data.buffer,
		);

		let pixelsCount = 0;
		return pixelBuffer.some((color) => {
			return color !== 0 && (pixelsCount++) > SignUp.MIN_PIXELS_REQUIRED;
		})
	}

	async getValue(): Promise<File | Blob>
	{
		const canvas = this.getTabs().getCurrentTab().getContent().getCanvas().getLayout();
		return await new Promise((resolve) => {
			canvas.toBlob(resolve, 'image/png');
		});
	}
}