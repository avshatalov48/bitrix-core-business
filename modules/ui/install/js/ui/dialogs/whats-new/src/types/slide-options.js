import type { VideoOptions } from './video-options';

export type SlideOptions = {
	id?: string,
	title?: string,
	description?: string,
	className?: string,
	image?: string,
	video?: string | VideoOptions,
	autoplay?: boolean,
	html?: string | HTMLElement
};