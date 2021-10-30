import {Text} from 'main.core';
import {matcherHsl} from './is-hsl-string';

export default function hslStringToHsl(hslString: string): { h: number, s: number, l: number, a: number}
{
	let matches = hslString.trim().match(matcherHsl);
	if (matches && matches.length > 0)
	{
		return {
			h: Text.toNumber(matches[1]),
			s: Text.toNumber(matches[2]),
			l: Text.toNumber(matches[3]),
			a: matches[5] ? Text.toNumber(matches[5]) : 1,
		};
	}
}