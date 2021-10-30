export const matcherHsl = /^hsla?\((\d{1,3}), ?(\d{1,3})%, ?(\d{1,3})%(, ?([\d .]+))?\)/i;

export default function isHslString(hsla: string)
{
	return !!hsla.trim().match(matcherHsl);
}