export default function rgbaStringToRgbString(str: string): string | null
{
	const regRgba = /\d{1,3}(\.\d+)?/g;
	const rgba = str.match(regRgba);
	const r = rgba[0] ? rgba[0] : null;
	const g = rgba[1] ? rgba[1] : null;
	const b = rgba[2] ? rgba[2] : null;
	if (r === null || g === null || b === null)
	{
		return null;
	}
	return createRgbString(r, g, b);
}

function createRgbString(r, g, b): string
{
	return 'rgb(' + r + ',' + g + ','+ b + ')';
}