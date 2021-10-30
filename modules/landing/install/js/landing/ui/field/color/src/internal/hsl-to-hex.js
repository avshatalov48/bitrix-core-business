import rgbToHex from './rgb-to-hex';
import hslToRgb from './hsl-to-rgb';

export default function hslToHex(hsl: {h: number, s: number, l: number}): string
{
	const rgb = hslToRgb(hsl);

	return rgbToHex(rgb);
}