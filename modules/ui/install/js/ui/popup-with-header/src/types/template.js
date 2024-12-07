export type SalePopupTemplateOptions = {
	items: Array<SalePopupTemplateItemConfig>,
	analyticsCallback: ?Function,
}

export type ResultContent = {
	html: HTMLElement,
	backgroundColor?: string,
}

export type SalePopupTemplateItemConfig = {
	title?: TextConfig,
	icon?: IconConfig,
	description?: TextConfig,
	more?: MoreLinkConfig,
	button?: ButtonConfig,
	styles?: {
		background?: string,
		color?: string,
	}
}

export type IconConfig = {
	name: string,
	color?: string,
}

export type MoreLinkConfig = {
	text: TextConfig,
	code?: string,
	articleId?: string,
}

export type ButtonConfig = {
	text: string,
	backgroundColor?: string,
	color?: string,
	onclick?: function,
	description?: TextConfig,
	target?: string,
}

export type TextConfig = {
	text: string,
	fontSize?: string,
	color?: string,
	weight?: number,
}