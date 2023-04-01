import type {FieldScheme} from "./field-scheme";
import type {InfoMessage} from "./info-message";
import {SectionTypes} from "./section-type";

export type SectionOptions = {
	ID: string,
	TITLE: string,
	FIELDS: Array<FieldScheme>,
	MESSAGE: InfoMessage,
	INHERITED: 'Y' | 'N',
	TYPE: SectionTypes,
}