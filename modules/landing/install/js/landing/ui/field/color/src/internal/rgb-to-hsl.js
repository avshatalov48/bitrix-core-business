export default function rgbToHsla(
	rgb: {r: number, g: number, b: number, a?: number}
): {h: number, s: number, l: number, a: number}
{
	const r = rgb.r / 255;
	const g = rgb.g / 255;
	const b = rgb.b / 255;

	const max = Math.max(r, g, b);
	const min = Math.min(r, g, b);
	let h, s, l = (max + min) / 2;
	// let l = h;
	// let s;

	if (max === min)
	{
		h = s = 0;
	}
	else
	{
		const d = max - min;
		s = l > 0.5
			? d / (2 - max - min)
			: d / (max + min);

		switch (max)
		{
			case r:
				h = (g - b) / d + (g < b ? 6 : 0);
				break;
			case g:
				h = (b - r) / d + 2;
				break;
			case b:
				h = (r - g) / d + 4;
				break;
		}

		h *= 0.6;
	}

	return {
		h: Math.round(h * 100),
		s: Math.round(s * 100),
		l: Math.round(l * 100),
		a: ('a' in rgb) ? rgb.a : 1,
	};
}

// 	const v = Math.max(r, g, b);
// 	const diff = v - Math.min(r, g, b);
// 	const diffc = (c) => {
// 		return (v - c) / 6 / diff + 1 / 2;
// 	};
//
// 	if (diff === 0)
// 	{
// 		h = 0;
// 		s = 0;
// 	}
// 	else
// 	{
// 		s = diff / v;
// 		rdif = diffc(r);
// 		gdif = diffc(g);
// 		bdif = diffc(b);
//
// 		if (r === v)
// 		{
// 			h = bdif - gdif;
// 		}
// 		else if (g === v)
// 		{
// 			h = (1 / 3) + rdif - bdif;
// 		}
// 		else if (b === v)
// 		{
// 			h = (2 / 3) + gdif - rdif;
// 		}
//
// 		if (h < 0)
// 		{
// 			h += 1;
// 		}
// 		else if (h > 1)
// 		{
// 			h -= 1;
// 		}
// 	}
//
// 	return {
// 		h: h * 360,
// 		s: s * 100,
// 		l: v * 100,
// 		a: rgb.a || 1,
// 	};
// }