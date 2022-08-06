import {Cache, Tag, Text, Type, Loc, Reflection, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Popup} from 'main.popup';
import {Env} from 'landing.env';
import {Embed} from 'crm.form.embed';
import {PageObject} from 'landing.pageobject';

import './css/style.css';

type TextBlockOptions = {
	type: string,
	title: string,
	link: {
		label: string,
		onClick: () => void,
	},
	action: {
		label: string,
		onClick: () => void,
	},
};

/**
 * @memberOf BX.Landing.Form
 */
export class SharePopup extends EventEmitter
{
	#cache = new Cache.MemoryCache();

	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Landing.Form.SharePopup');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);

		console.log(PageObject.getEditorWindow().document);
		Event.bind(PageObject.getEditorWindow().document, 'click', () => {
			console.log('click');
			this.hide();
		});
	}

	setOptions(options)
	{
		this.#cache.set('options', {...options});
	}

	getOptions(): {[key: string]: any}
	{
		return this.#cache.get('options', {});
	}

	getPopup(): Popup
	{
		return this.#cache.remember('popup', () => {
			return new Popup({
				id: `form-share-popup-${Text.getRandom()}`,
				bindElement: this.getOptions().bindElement,
				content: this.getContent(),
				className: 'landing-form-share-popup',
				width: 410,
				autoHide: true,
				closeByEsc: true,
				noAllPaddings : true,
				angle: {
					position: 'top',
					offset: 115
				},
				minWidth: 410,
				contentBackground: 'transparent',
				background: '#E9EAED',
			});
		});
	}

	getContent(): HTMLDivElement
	{
		return this.#cache.remember('content', () => {
			return Tag.render`
				<div class="landing-form-share-popup-content">
					${this.getShareBlock()}
					${this.getCommunicationBlock()}
					${this.getHelpBlock()}
				</div>
			`;
		});
	}

	getShareBlock(): HTMLDivElement
	{
		return this.#cache.remember('shareBlock', () => {
			return this.createContentBlock({
				type: 'share',
				title: Loc.getMessage('LANDING_FORM_SHARE__SHARE_TITLE'),
				link: {
					label: Loc.getMessage('LANDING_FORM_SHARE__SHARE_LINK_LABEL'),
					onClick: () => {},
				},
				action: {
					label: Loc.getMessage('LANDING_FORM_SHARE__SHARE_ACTION_LABEL'),
					onClick: () => {
						this.showEmbedPanel();
						this.hide();
					},
				},
			});
		});
	}

	showEmbedPanel()
	{
		const {formEditorData} = Env.getInstance().getOptions();
		if (
			Type.isPlainObject(formEditorData)
			&& Type.isPlainObject(formEditorData.formOptions)
		)
		{
			const {id} = formEditorData.formOptions;
			Embed.open(id);
		}
	}

	showWidgetPanel()
	{
		const SidePanelInstance = Reflection.getClass('BX.SidePanel.Instance');

		SidePanelInstance.open(
			`/crm/button/`,
			{
				allowChangeHistory: false,
				cacheable: false,
			},
		);
	}

	getCommunicationBlock(): HTMLDivElement
	{
		return this.#cache.remember('communicationBlock', () => {
			return this.createContentBlock({
				type: 'communication',
				title: Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_TITLE'),
				link: {
					label: Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_LINK_LABEL'),
					onClick: () => {},
				},
				action: {
					label: Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_ACTION_LABEL'),
					onClick: () => {
						this.showWidgetPanel();
					},
				},
			});
		});
	}

	getHelpBlock(): HTMLDivElement
	{
		return this.#cache.remember('helpBlock', () => {
			return this.createContentBlock({
				type: 'help',
				title: Loc.getMessage('LANDING_FORM_SHARE__HELP_TITLE'),
				link: {
					label: Loc.getMessage('LANDING_FORM_SHARE__HELP_LINK_LABEL'),
					onClick: () => {},
				},
				action: {
					label: Loc.getMessage('LANDING_FORM_SHARE__HELP_ACTION_LABEL'),
					onClick: () => {},
				},
			});
		});
	}

	createContentBlock(options: TextBlockOptions): HTMLDivElement
	{
		return Tag.render`
				<div class="landing-form-share-popup-content-block" data-type="${Text.encode(options.type)}">
					<div class="landing-form-share-popup-content-block-icon"></div>
					<div class="landing-form-share-popup-content-block-text">
						<div class="landing-form-share-popup-content-block-text-title">
							${Text.encode(options.title)}
						</div>
						<div 
							class="landing-form-share-popup-content-block-text-link"
							onclick="${options.link.onClick}"
						>
							${Text.encode(options.link.label)}
						</div>
					</div>
					<div class="landing-form-share-popup-content-block-action">
						<span 
							class="ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-light-border"
							onclick="${options.action.onClick}"
						>${Text.encode(options.action.label)}</span>
					</div>
				</div>
			`;
	}

	show(options)
	{
		const popup: Popup = this.getPopup();

		if (Type.isPlainObject(options))
		{
			if (Type.isDomNode(options.bindElement))
			{
				popup.setBindElement(options.bindElement);
			}
		}

		popup.show();
	}

	hide()
	{
		this.getPopup().close();
	}
}