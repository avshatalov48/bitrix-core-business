import 'ui.design-tokens';

import {BaseField} from 'landing.ui.field.basefield';
import {Dom, Tag, Type, Event, Text} from 'main.core';
import {Loc} from 'landing.loc';

import './css/style.css';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

export class ActionPagesField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.ActionPagesField');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.setLayoutClass('landing-ui-field-action-pages');

		Dom.append(this.getSuccess(), this.input);
		Dom.append(this.getFailure(), this.input);

		Event.bind(document, 'click', this.onDocumentClick.bind(this));
		Event.bind(window.top.document, 'click', this.onDocumentClick.bind(this));
	}

	static createPageBlock(
		options: {
			type: 'success' | 'failure',
			title: string,
			text: string,
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
			Dom.attr(textContainer, 'contenteditable', !textContainer.isContentEditable);
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
					<div class="${classPrefix}-text" onclick="${onEditorClick}" 
						onfocus="${onFocus}" onblur="${onBlur}" oninput="${options.onInput}">
						${Text.encode(options.text)}
					</div>
					<div class="${classPrefix}-footer">
						<span class="${classPrefix}-footer-edit" onclick="${onEditClick}">
							${Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_EDIT')}
						</span>
					</div>
				</div>
			</div>
		`;
	}

	onDocumentClick()
	{
		const successInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text');
		const failureInput = this.getFailure().querySelector('.landing-ui-field-action-pages-page-text');

		Dom.attr(successInput, 'contenteditable', null);
		Dom.attr(failureInput, 'contenteditable', null);
	}

	getSuccess(): HTMLDivElement
	{
		return this.cache.remember('success', () => {
			return ActionPagesField.createPageBlock({
				type: 'success',
				title: Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_SUCCESS_PAGE_TITLE'),
				text: this.options.successText,
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

	getFailure(): HTMLDivElement
	{
		return this.cache.remember('failure', () => {
			return ActionPagesField.createPageBlock({
				type: 'failure',
				title: Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_FAILURE_PAGE_TITLE'),
				text: this.options.failureText,
				onFocus: () => {
					this.emit('onShowFailure', new Event.BaseEvent({data: {show: true}}));
				},
				onInput: () => {
					this.emit('onChange');
					this.emit('onShowFailure', new Event.BaseEvent({data: {show: true}}));
				},
				onShowClick: () => {
					this.emit('onShowFailure');
				},
				onBlur: () => {
					this.emit('onBlur');
				},
			});
		});
	}

	getSuccessText(): string
	{
		return this.getSuccess()
			.querySelector('.landing-ui-field-action-pages-page-text')
			.innerText;
	}

	getFailureText(): string
	{
		return this.getFailure()
			.querySelector('.landing-ui-field-action-pages-page-text')
			.innerText;
	}

	getValue()
	{
		return {
			success: this.getSuccessText(),
			failure: this.getFailureText(),
		};
	}
}