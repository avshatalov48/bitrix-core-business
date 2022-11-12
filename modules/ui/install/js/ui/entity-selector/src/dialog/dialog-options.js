import type { PopupOptions } from 'main.popup';
import type { TabOptions } from './tabs/tab-options';
import type { ItemOptions } from '../item/item-options';
import type { EntityOptions } from '../entity/entity-options';
import type { TagSelectorOptions } from '../tag-selector/tag-selector-options';
import type { BaseEvent } from 'main.core.events';
import type { ItemId } from '../item/item-id';
import type { SearchOptions } from './search-options';
import type TagSelector from '../tag-selector/tag-selector';
import type { HeaderContent, HeaderOptions } from './header/header-content';
import type { FooterContent, FooterOptions } from './footer/footer-content';

export type DialogOptions = {
	targetNode: HTMLElement,
	id?: string,
	context?: string,
	items?: ItemOptions[],
	selectedItems?: ItemOptions[],
	preselectedItems?: ItemId[],
	undeselectedItems?: ItemId[],
	tabs?: TabOptions[],
	entities?: EntityOptions[],
	popupOptions?: PopupOptions,
	multiple?: boolean,
	preload?: boolean,
	dropdownMode?: boolean,
	enableSearch?: boolean,
	searchOptions?: SearchOptions,
	searchTabOptions?: TabOptions,
	recentTabOptions?: TabOptions,
	tagSelector?: TagSelector,
	tagSelectorOptions?: TagSelectorOptions,
	events?: { [eventName: string]: (event: BaseEvent) => void },
	hideOnSelect?: boolean,
	hideOnDeselect?: boolean,
	clearSearchOnSelect?: boolean,
	width?: number,
	height?: number,
	autoHide?: boolean,
	autoHideHandler?: (event: MouseEvent, dialog: Dialog) => boolean,
	hideByEsc?: boolean,
	offsetTop?: number,
	offsetLeft?: number,
	cacheable?: boolean,
	focusOnFirst?: boolean,
	header?: HeaderContent,
	headerOptions?: HeaderOptions,
	footer?: FooterContent,
	footerOptions?: FooterOptions,
	clearUnavailableItems?: boolean,
	showAvatars?: boolean,
	compactView?: boolean
};