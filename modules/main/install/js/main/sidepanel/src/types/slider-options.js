import { type JsonObject } from 'main.core';
import { type SliderEvent } from '../slider-event';
import { type MinimizeOptions } from './minimize-options';
import { type OuterBoundary } from './outer-boundary';

export type SliderOptions = {
	contentCallback?: Function,
	width?: number,
	title?: string,
	cacheable?: boolean,
	autoFocus?: boolean,
	printable?: boolean,
	allowCrossOrigin?: boolean,
	allowChangeHistory?: boolean,
	allowChangeTitle?: boolean,
	hideControls?: boolean,
	requestMethod?: 'get' | 'post',
	requestParams?: JsonObject,
	loader?: string | HTMLElement,
	contentClassName?: string,
	containerClassName?: string,
	overlayClassName?: string,
	overlayOpacity?: number,
	overlayBgColor?: string,
	overlayBgCallback?: (state: { opacity: number, progress: number, intensity: number }) => string,
	typeLoader?: string, // compatibility
	data?: JsonObject,
	minimizeOptions?: MinimizeOptions,
	hideToolbarOnOpen?: boolean,
	animationDuration?: number,
	startPosition?: 'right' | 'bottom' | 'top',
	customLeftBoundary?: number,
	customRightBoundary?: number,
	customTopBoundary?: number,
	outerBoundary?: OuterBoundary,
	autoOffset?: boolean,
	label?: {
		text?: string,
		color?: string,
		bgColor?: string,
		opacity?: number,
	},
	newWindowLabel?: boolean,
	newWindowUrl?: string,
	copyLinkLabel?: boolean,
	minimizeLabel?: boolean,
	designSystemContext?: string,
	events?: SliderEvents | SliderEvents[],
	useGlobalOptions?: boolean,
};

export type SliderEvents = { [eventName: string]: (event: SliderEvent) => void };
