import {Text} from 'main.core';
import rgbToHsla from '../internal/rgb-to-hsl';
import {matcher} from './is-rgb-string';

export default function rgbStringToHsla(rgbString: string): { h: number, s: number, l: number, a: number}
{
	let matches = rgbString.trim().match(matcher);
	if (matches.length > 0)
	{
		return rgbToHsla({
			r: Text.toNumber(matches[1]),
			g: Text.toNumber(matches[2]),
			b: Text.toNumber(matches[3]),
			a: matches[5] ? Text.toNumber(matches[5]) : 1,
		});
	}
}