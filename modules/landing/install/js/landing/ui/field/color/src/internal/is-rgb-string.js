export const matcher = /^rgba? ?\((\d{1,3})[, ]+(\d{1,3})[, ]+(\d{1,3})([, ]+([\d\.]{1,5}))?\)$/i;

export default function isRgbString(rgbString: string): boolean
{
	return !!rgbString.match(matcher);
}
