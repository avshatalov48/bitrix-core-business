import {Loc, Text} from 'main.core';
import {Element} from "./element";
import {SectionType} from "../types/section-type";
import {SeoDetail} from "../seo-detail";
import type {SectionOptions} from "../types/section-options";

export class Section extends Element
{
	constructor(options: SectionOptions = {}, form: SeoDetail)
	{
		super(options, form);

		this.type = SectionType.SECTION;
	}

	getInheritedLabel(): string
	{
		return Loc.getMessage('CSD_INHERIT_SECTION_OVERWRITE_CHECKBOX_INPUT_TITLE');
	}
}