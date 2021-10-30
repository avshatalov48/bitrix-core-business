export const matcherHex = /^#([\da-f]{3}){1,2}$/i;

export default function isHex(hex: string)
{
	return !!hex.trim().match(matcherHex);
}