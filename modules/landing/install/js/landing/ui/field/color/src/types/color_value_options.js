import ColorValue from "../color_value";

export type ColorValueOptions = {
	h?: number,
	s?: number,
	l?: number,
	a?: number,
};
export const defaultColorValueOptions: ColorValueOptions = {
	h: 205,
	s: 1,
	l: 50,
	a: 1,
};

export type GradientValueOptions = {
	from: ColorValue,
	to: ColorValue,
	angle: number,
	type: 'linear' | 'radial'
};

export type BgImageValueOptions = {
	url: ?string,
	url2x: ?string,
	fileId: ?number,
	fileId2x: ?number,
	size: 'cover' | 'auto',
	attachment: 'scroll' | 'fixed',
	overlay: ?ColorValue,
};
export const defaultBgImageSize = 'cover';
export const defaultBgImageAttachment = 'scroll';
export const defaultOverlay = null;
export const defaultBgImageValueOptions: BgImageValueOptions = {
	url: null,
	size: defaultBgImageSize,
	attachment: defaultBgImageAttachment,
	overlay: defaultOverlay,
};
