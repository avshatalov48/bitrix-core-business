import {SectionOptions} from "./section-options";

export type FormOptions = {
	containerId: string,
	formId: string,
	schemeFields: Array<SectionOptions>,
	values: {},
	componentName: string,
	signedParameters: string,
	menuItems: {},
	readOnly: boolean,
	mode: string,
	helpArticleCode: number,
}