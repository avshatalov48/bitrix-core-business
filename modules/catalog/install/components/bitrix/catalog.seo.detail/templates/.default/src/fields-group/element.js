import {Dom, Loc, Text, Tag, Event} from 'main.core';
import {Base} from "./base";
import {SeoInput} from "../field/seo-input";
import {SectionType} from "../types/section-type";
import type {SectionOptions} from "../types/section-options";
import {SeoDetail} from "../seo-detail";
import {MessageCard} from "ui.messagecard";
import {EventEmitter} from "main.core.events";

export class Element extends Base
{
	inputFields: Array<SeoInput>;

	constructor(options: SectionOptions = {}, form: SeoDetail)
	{
		super(options, form);

		this.inputFields = [];

		Object.keys(options.FIELDS).forEach((fieldCode) => {
			const fieldScheme = options.FIELDS[fieldCode];
			this.inputFields.push(new SeoInput(fieldScheme, this));
		});

		if (options.MESSAGE)
		{
			this.message = new MessageCard({
				id: options.MESSAGE.ID,
				header: Text.encode(options.MESSAGE.HEADER),
				description: this.getInfoMessageDescription(options.MESSAGE.DESCRIPTION),
				angle: false,
				hidden: options.MESSAGE.HIDDEN === 'Y',
			});

			EventEmitter.subscribe(this.message, 'onClose', () => {
				this.form.hideInfoMessage(this.message.id);
			});
		}

		this.type = SectionType.ELEMENT;
	}

	getWrapper(): HTMLElement
	{
		const wrapper = super.getWrapper();

		this.inputFields.forEach((field) => {
			Dom.append(field.layout(), wrapper);
		})

		return wrapper;
	}

	getInfoWrapper(): ?HTMLElement
	{
		return this.message ? this.message.getLayout() : null;
	}

	getInfoMessageDescription(description: string): HTMLElement
	{
		const moreLink = Tag.render`<a href="#" class="ui-form-link">${Loc.getMessage('CSD_ELEMENT_INFO_MESSAGE_HELP_LINK_TITLE')}</a>`;

		Event.bind(moreLink, 'click', SeoDetail.openSeoHelpPage);
		const descriptionHtml = Tag.render`
			<div>${Text.encode(description).replace('#HELP_LINK#', '<help-link></help-link>')}</div>
		`;

		Dom.replace(descriptionHtml.querySelector('help-link'), moreLink);

		return descriptionHtml;
	}

	toggleInputMenu(field: SeoInput): void
	{
		this.getForm().toggleInputMenu(this, field);
	}

	getInheritedLabel(): string
	{
		if (!this.form.isElementMode())
		{
			return Loc.getMessage('CSD_INHERIT_SECTION_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE');
		}

		return Loc.getMessage('CSD_INHERIT_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE');
	}
}