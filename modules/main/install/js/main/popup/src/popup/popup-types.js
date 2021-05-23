import { type BaseEvent } from 'main.core.events';
import { ZIndexComponentOptions } from 'main.core.z-index-manager';

export type PopupOptions = {
	id?: string,
	bindElement?: PopupTarget,
	bindOptions?: PopupTargetOptions,
	content?: string | Element | Node,
	closeByEsc?: boolean,
	buttons?: [],
	className?: string,
	width?: number,
	height?: number,
	minWidth?: number,
	minHeight?: number,
	maxWidth?: number,
	maxHeight?: number,
	resizable?: boolean,
	padding?: number,
	contentPadding?: number,
	background?: string,
	cacheable?: boolean,
	contentBackground?: string,
	animation?: PopupAnimationOptions,
	closeIcon?: boolean,
	autoHide?: boolean,
	autoHideHandler?: (event: MouseEvent) => boolean,
	zIndexOptions?: ZIndexComponentOptions,
	toFrontOnShow?: boolean,
	events?: { [eventName: string]: (event: BaseEvent) => void },
	titleBar?: string | { content: string },
	angle?: boolean | { offset: number, position?: 'top' | 'bottom' | 'left' | 'right' },
	overlay?: boolean | { backgroundColor?: string, opacity?: number },
	contentColor?: 'white' | 'gray',
	draggable?: boolean | { restrict: boolean },
	darkMode?: boolean,
	compatibleMode?: boolean,
	bindOnResize?: boolean,
	targetContainer?: HTMLElement,

	//Compatibility
	noAllPaddings?: boolean,
	contentNoPaddings?: boolean,
}

export type PopupTarget = Element | { left: number, top: number } | null | MouseEvent;
export type PopupTargetOptions = {
	forceBindPosition?: boolean,
	forceLeft?: boolean,
	forceTop?: boolean,
	position?: 'top' | 'bootom'
};

export type PopupAnimationOptions =
	string
	| boolean
	| { showClassName?: string, closeClassName?: string, closeAnimationType: ? string }