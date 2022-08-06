export default function stringToRGB(str: string): {r: number, g: number, b: number, a: number}
{
	const regRGB = /\d{1,3}(\.\d+)?/g;
	const rgb = str.match(regRGB);
	const r = rgb[0] ? rgb[0] : null;
	const g = rgb[1] ? rgb[1] : null;
	const b = rgb[2] ? rgb[2] : null;
	const a = rgb[3] ? rgb[3] : 1;
	if (r === null || g === null || b === null)
	{
		return  false;
	}
	const rgbFormat = 'rgb(' + r + ',' + g + ','+ b + ')';
	return {
		r: r,
		g: g,
		b: b,
		a: a,
		rgb: rgbFormat,
	};
}