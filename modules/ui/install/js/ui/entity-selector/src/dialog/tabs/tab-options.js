import type { FooterContent, FooterOptions } from '../footer/footer-content';

export type TabOptions = {
	id: string,
	title?: string,
	visible?: boolean,
	comparator?: Function,
	itemMaxDepth?: number,
	itemOrder?: {[key: string]: 'asc' | 'desc'},
	icon?: TabLabelStates | string,
	textColor?: TabLabelStates | string,
	bgColor?: TabLabelStates | string,
	stub?: boolean | string,
	stubOptions?: { [option: string]: any },
	footer?: FooterContent,
	footerOptions?: FooterOptions,
	showDefaultFooter?: boolean
};

export type TabLabelState = 'default' | 'selected' | 'hovered' | 'selectedHovered';

export type TabLabelStates = {
	default?: string,
	selected?: string,
	hovered?: string,
	selectedHovered?: string
}