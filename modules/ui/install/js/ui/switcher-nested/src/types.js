import { SwitcherNestedItem } from './switcher-nested-item';

export type SwitcherNestedOptions = {
	linkTitle?: string,
	link?: string,
	isChecked?: boolean,
	mainInputName: string,
	items?: Array<SwitcherNestedItem>,
	infoHelperCode?: string,
	isDefault?: boolean,
	helpMessage?: string,
}

export type SwitcherNestedItemOptions = {
	id?: string,
	inputName?: string,
	title?: string,
	isChecked: boolean,
	settingsPath?: string,
	settingsTitle?: string,
	infoHelperCode?: string,
	isDefault?: boolean,
	helpMessage?: string,
}

export type WarningMessageOptions = {
	id: string,
	bindElement: HTMLElement,
	message: HTMLElement,
}
