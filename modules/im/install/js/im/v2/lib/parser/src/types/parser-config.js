export type ParserConfig = {
	text: string,
	attach: boolean | string | Object,
	files: boolean | Object[],
	showIconIfEmptyText: boolean,
	showPhraseMessageWasDeleted: boolean,
	showImageFromLink: boolean,
	urlTarget: string,
	removeLinks?: boolean,
	contextDialogId?: string
};
