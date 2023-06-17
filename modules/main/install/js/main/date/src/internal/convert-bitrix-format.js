import { Type } from 'main.core';

export function convertBitrixFormat(format: any): string
{
	if (!Type.isStringFilled(format))
	{
		return '';
	}

	return format.replace('YYYY', 'Y')    // 1999
		.replace('MMMM', 'F')    // January - December
		.replace('MM', 'm')    // 01 - 12
		.replace('M', 'M')    // Jan - Dec
		.replace('DD', 'd')    // 01 - 31
		.replace('G', 'g')    //  1 - 12
		.replace(/GG/i, 'G')    //  0 - 23
		.replace('H', 'h')    // 01 - 12
		.replace(/HH/i, 'H')    // 00 - 24
		.replace('MI', 'i')    // 00 - 59
		.replace('SS', 's')    // 00 - 59
		.replace('TT', 'A')    // AM - PM
		.replace('T', 'a');	// am - pm
}
