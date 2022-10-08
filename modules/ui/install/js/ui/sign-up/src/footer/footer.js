import {Cache, Dom, Loc, Tag} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Button, ButtonColor, ButtonSize} from 'ui.buttons';
import type {FooterOptions} from '../types/footer-options';

import './css/style.css';

export class Footer extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: FooterOptions)
	{
		super();
		this.setEventNamespace('BX.UI.SignUp.Footer');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
	}

	setOptions(options: FooterOptions)
	{
		this.cache.set('options', options);
	}

	getOptions(): FooterOptions
	{
		return this.cache.get('options', {});
	}

	getSaveButton(): Button
	{
		return this.cache.remember('saveButtons', () => {
			return new Button({
				text: Loc.getMessage('UI_SIGN_UP_SAVE_BUTTON_LABEL'),
				color: BX.UI.Button.Color.PRIMARY,
				round: true,
				noCaps: true,
				className: `ui-sign-up-special-${this.getOptions().mode}-btn`,
				onclick: () => {
					this.emit('onSaveClick');
					const promise = this.emitAsync('onSaveClickAsync');
					if (promise)
					{
						this.getSaveButton().setWaiting(true);
						promise.then(() => {
							this.getSaveButton().setWaiting(false);
						});
					}
				},
			});
		});
	}

	getCancelButton(): Button
	{
		return this.cache.remember('cancelButtons', () => {
			return new Button({
				text: Loc.getMessage('UI_SIGN_UP_CANCEL_BUTTON_LABEL'),
				color: ButtonColor.LIGHT_BORDER,
				round: true,
				noCaps: true,
				className: `ui-sign-up-special-${this.getOptions().mode}-btn`,
				onclick: () => {
					this.emit('onCancelClick');
				},
			});
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			const layout = Tag.render`
				<div class="ui-sign-up-footer">
					${this.getSaveButton().render()}
				</div>
			`;

			if (this.getOptions().mode === 'desktop')
			{
				Dom.append(this.getCancelButton().render(), layout);
			}

			return layout;
		});
	}
}