import type { FooterContent, FooterOptions } from '../footer/footer-content';
import type { ItemNodeOrder } from '../../item/item-node-options';
import type { TextNodeOptions } from '../../common/text-node-options';

export type TabOptions = {
	id: string,
	title?: string | TextNodeOptions,
	visible?: boolean,
	itemMaxDepth?: number,
	itemOrder?: ItemNodeOrder,
	icon?: TabLabelStates | string,
	textColor?: TabLabelStates | string,
	bgColor?: TabLabelStates | string,
	stub?: boolean | string | Function,
	stubOptions?: { [option: string]: any },
	footer?: FooterContent,
	footerOptions?: FooterOptions,
	showDefaultFooter?: boolean,
	showAvatars?: boolean
};

export type TabLabelState = 'default' | 'selected' | 'hovered' | 'selectedHovered';

export type TabLabelStates = {
	default?: string,
	selected?: string,
	hovered?: string,
	selectedHovered?: string
}