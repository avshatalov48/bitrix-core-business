export type ParserConfig = {
	text: string,
	attach: boolean | string | Object,
	files: boolean | Object[],
	replaces: Object[],
	showIconIfEmptyText: boolean,
	showPhraseMessageWasDeleted: boolean,
	showImageFromLink: boolean,
	urlTarget: string,
	removeLinks?: boolean
};