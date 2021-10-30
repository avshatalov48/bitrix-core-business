export default function hexToRgb(hex: string)
{
	if (hex.length === 4)
	{
		const r = parseInt(`0x${hex[1]}${hex[1]}`, 16);
		const g = parseInt(`0x${hex[2]}${hex[2]}`, 16);
		const b = parseInt(`0x${hex[3]}${hex[3]}`, 16);

		return {r, g, b};
	}

	if (hex.length === 7)
	{
		const r = parseInt(`0x${hex[1]}${hex[2]}`, 16);
		const g = parseInt(`0x${hex[3]}${hex[4]}`, 16);
		const b = parseInt(`0x${hex[5]}${hex[6]}`, 16);

		return {r, g, b};
	}

	return {r: 255, g: 255, b: 255};
}