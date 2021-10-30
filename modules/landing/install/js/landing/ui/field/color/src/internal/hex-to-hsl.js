import hexToRgb from './hex-to-rgb';
import rgbToHsla from './rgb-to-hsl';

export default function hexToHsl(hex: string): {h: number, s: number, l: number, a: number}
{
	const rgb = hexToRgb(hex.trim());

	return rgbToHsla(rgb);
}