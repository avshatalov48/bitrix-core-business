import {Content} from '../content';
import {Loc, Tag} from 'main.core';
import 'ui.forms';
import 'ui.fonts.comforter-brush';
import {CanvasWrapper} from '../../canvas-wrapper/canvas-wrapper';

import './css/style.css';

export class InitialsContent extends Content
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.UI.SignUp.Content.InitialsContent');
		this.subscribeFromOptions(options?.events);
		this.onInput = this.onInput.bind(this);

		void this.forceLoadFonts();
	}

	forceLoadFonts(): Promise<any>
	{
		const allFonts = [
			...document.fonts,
		];
		const comforterBrushFonts = allFonts.filter((font) => {
			return String(font.family).includes('Comforter Brush');
		});

		return Promise.all(comforterBrushFonts.map((font) => font.load()));
	}

	getNameInput(): HTMLInputElement
	{
		return this.cache.remember('nameInput', () => {
			return Tag.render`
				<input type="text" class="ui-ctl-element" oninput="${this.onInput}">
			`;
		});
	}

	getInitialsInput(): HTMLInputElement
	{
		return this.cache.remember('initialsInput', () => {
			return Tag.render`
				<input type="text" class="ui-ctl-element" oninput="${this.onInput}">
			`;
		});
	}

	getTextValue(): string
	{
		const name = String(this.getNameInput().value);
		const initials = String(this.getInitialsInput().value);

		return `${name} ${initials}`;
	}

	onInput()
	{
		this.getCanvas().renderText(this.getTextValue(), this.getColor());
		this.emit('onChange');
	}

	getCanvas(): CanvasWrapper
	{
		return this.cache.remember('canvas', () => {
			return new CanvasWrapper({
				context2d: {
					fillStyle: '#000000',
					font: '34px Comforter Brush',
				},
			});
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-sign-up-content">
					<div class="ui-sign-up-initials-form">
						<div class="ui-sign-up-initials-form-left">
							<div class="ui-sign-up-initials-form-label">
								${Loc.getMessage('UI_SIGN_UP_TAB_INITIALS_LAST_NAME_LABEL')}
							</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
								${this.getNameInput()}
							</div>
						</div>
						<div class="ui-sign-up-initials-form-right">
							<div class="ui-sign-up-initials-form-label">
								${Loc.getMessage('UI_SIGN_UP_TAB_INITIALS_INITIALS_LABEL')}
							</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
								${this.getInitialsInput()}
							</div>
						</div>
					</div>
					<div class="ui-sign-up-initials-preview">
						${this.getCanvas().getLayout()}
					</div>
				</div>
			`;
		});
	}
}