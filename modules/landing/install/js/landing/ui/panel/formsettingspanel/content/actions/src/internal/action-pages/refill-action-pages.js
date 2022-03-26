import {Dom, Tag, Type, Event, Text} from 'main.core';
import {Loc} from 'landing.loc';

import './css/style.css';
import {ActionPagesField} from "./action-pages";

export class RefillActionPagesField extends ActionPagesField
{
	constructor(options)
	{
		super(options);
	}

	getSuccess(): HTMLDivElement
	{
		return this.cache.remember('success', () => {
			return RefillActionPagesField.createPageBlock({
				type: 'success',
				title: Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_SUCCESS_PAGE_TITLE'),
				text: this.options.successText,
				buttonCaption: this.options.buttonCaption,
				onFocus: () => {
					this.emit('onShowSuccess', new Event.BaseEvent({data: {show: true}}));
				},
				onInput: () => {
					this.emit('onChange');
					this.emit('onShowSuccess', new Event.BaseEvent({data: {show: true}}));
				},
				onShowClick: () => {
					this.emit('onShowSuccess');
				},
				onBlur: () => {
					this.emit('onBlur');
				},
			});
		});
	}

	onDocumentClick()
	{
		const successInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text');
		const buttonInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-button');
		const failureInput = this.getFailure().querySelector('.landing-ui-field-action-pages-page-text');

		Dom.attr(successInput, 'contenteditable', null);
		Dom.attr(buttonInput, 'contenteditable', null);
		Dom.attr(failureInput, 'contenteditable', null);
	}

	static createPageBlock(
		options: {
			type: 'success' | 'failure',
			title: string,
			text: string,
			buttonCaption: string,
			onShowClick: (event: MouseEvent) => void,
			onEditClick: (event: MouseEvent) => void,
			onBlur: (event: Event) => void,
			onFocus: (event: Event) => void,
		},
	): HTMLDivElement
	{
		const classPrefix = 'landing-ui-field-action-pages-page';

		const onEditClick = (event: MouseEvent) => {
			event.preventDefault();
			event.stopPropagation();
			const inner = event.currentTarget.closest(`.${classPrefix}-inner`);
			const textContainer = inner.querySelector(`.${classPrefix}-text`);
			const buttonContainer = inner.querySelector(`.${classPrefix}-button`);

			Dom.attr(textContainer, 'contenteditable', !textContainer.isContentEditable);
			Dom.attr(buttonContainer, 'contenteditable', !buttonContainer.isContentEditable);

			if (Type.isFunction(options.onEditClick))
			{
				options.onEditClick(event);
			}
		};

		const onEditorClick = (event) => {
			event.stopPropagation();
		};

		const onViewClick = (event: MouseEvent) => {
			event.preventDefault();
			if (Type.isFunction(options.onShowClick))
			{
				options.onShowClick(event);
			}
		};

		const onBlur = (event: Event) => {
			event.preventDefault();
			if (Type.isFunction(options.onBlur))
			{
				options.onBlur(event);
			}
		};

		const onFocus = (event: Event) => {
			event.preventDefault();
			if (Type.isFunction(options.onFocus))
			{
				options.onFocus(event);
			}
		};

		let buttonTag = '';
		if (options.type === 'success')
		{
			const buttonCaption = Text.encode(options.buttonCaption)
				|| Loc.getMessage('LANDING_FORM_ACTIONS_REFILL_CAPTION');

			buttonTag = Tag.render`
				<div class="${classPrefix}-button" onclick="${onEditorClick}" 
					onfocus="${onFocus}" onblur="${onBlur}" oninput="${options.onInput}">
					${buttonCaption}
				</div>
			`;
		}

		return Tag.render`
			<div class="${classPrefix} ${classPrefix}-${options.type}">
				<div class="${classPrefix}-title">
					${options.title}
				</div>
				<div class="${classPrefix}-inner">
					<div class="${classPrefix}-header">
						<span class="${classPrefix}-header-view" onclick="${onViewClick}">
							${Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_SHOW')}
						</span>
					</div>
					<div class="${classPrefix}-icon"></div>
					<div class="${classPrefix}-text" onclick="${onEditorClick}" onfocus="${onFocus}" onblur="${onBlur}"  
						oninput="${options.onInput}">
						${Text.encode(options.text)}
					</div>
					${buttonTag}
					<div class="${classPrefix}-footer">
						<span class="${classPrefix}-footer-edit" onclick="${onEditClick}">
							${Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_EDIT')}
						</span>
					</div>
				</div>
			</div>
		`;
	}

	getButtonCaptionText(): string
	{
		return this.getSuccess()
		.querySelector('.landing-ui-field-action-pages-page-button')
			.innerText;
	}

	getValue()
	{
		return {
			success: this.getSuccessText(),
			buttonCaption: this.getButtonCaptionText(),
			failure: this.getFailureText(),
		};
	}
}