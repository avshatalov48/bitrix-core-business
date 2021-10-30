export default function hslToRgb(hsl: {h: number, s: number, l: number}): {r: number, g: number, b: number}
{
	// todo: a little not equal with reverce conversion :-/
	// todo: f.e. hsl(73.53.50) it 166,195,60 and #a5c33c,
	// todo: but in reverse #a5c33c => 165,195,60
	// todo: because we save ColorValue in hsl can be some differences
	const h = hsl.h;
	const s = hsl.s / 100;
	const l = hsl.l / 100;

	let c = (1 - Math.abs(2 * l - 1)) * s;
	let x = c * (1 - Math.abs((h / 60) % 2 - 1));
	let m = l - c / 2;
	let r = 0;
	let g = 0;
	let b = 0;

	if (0 <= h && h < 60)
	{
		r = c;
		g = x;
		b = 0;
	}
	else if (60 <= h && h < 120)
	{
		r = x;
		g = c;
		b = 0;
	}
	else if (120 <= h && h < 180)
	{
		r = 0;
		g = c;
		b = x;
	}
	else if (180 <= h && h < 240)
	{
		r = 0;
		g = x;
		b = c;
	}
	else if (240 <= h && h < 300)
	{
		r = x;
		g = 0;
		b = c;
	}
	else if (300 <= h && h < 360)
	{
		r = c;
		g = 0;
		b = x;
	}
	r = Math.round((r + m) * 255);
	g = Math.round((g + m) * 255);
	b = Math.round((b + m) * 255);

	return {r: r, g: g, b: b};
}