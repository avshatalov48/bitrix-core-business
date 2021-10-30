export default function rgbToHex(rgb: {r: number, g: number, b: number}): string
{
	let r = rgb.r.toString(16);
	let g = rgb.g.toString(16);
	let b = rgb.b.toString(16);

	if (r.length === 1)
	{
		r = "0" + r;
	}
	if (g.length === 1)
	{
		g = "0" + g;
	}
	if (b.length === 1)
	{
		b = "0" + b;
	}

	return "#" + r + g + b;
}