import {Tag, Text} from 'main.core';
import type {SectionOptions} from "../types/section-options";
import {SeoDetail} from "../seo-detail";
import {MessageCard} from 'ui.messagecard';
import {EventEmitter} from "main.core.events";


export class Base
{
	constructor(options: SectionOptions = {}, form: SeoDetail)
	{
		this.form = form;
		this.fields = options.FIELDS;
		this.title = options.TITLE;
		this.id = options.ID;
		this.type = null;
	}

	layout(): HTMLElement
	{
		return Tag.render`
			<div class='ui-slider-section'>
				<div class='ui-slider-heading-4'>${Text.encode(this.title)}</div>
				${this.getInfoWrapper()}
				${this.getWrapper()}
			</div>
		`;
	}

	getWrapper(): HTMLElement
	{
		return Tag.render`<div class='ui-form ui-form-section'></div>`;
	}

	getInfoWrapper(): ?HTMLElement
	{
		return null;
	}

	getForm(): SeoDetail
	{
		return this.form;
	}

	getType(): string
	{
		return this.type;
	}

	getInheritedLabel(): string
	{
		return '';
	}
}