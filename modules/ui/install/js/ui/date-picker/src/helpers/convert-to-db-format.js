const replacements: Object<string, string> = {
	Y: 'YYYY', // 1999
	M: 'MMM', // Jan - Dec
	f: 'MMMM', // January - December
	m: 'MM', // 01 - 12
	d: 'DD', // 01 - 31
	A: 'TT', // AM - PM
	a: 'T', // am - pm
	i: 'MI', // 00 - 59
	s: 'SS', // 00 - 59
	H: 'HH', // 00 - 24
	h: 'H', // 01 - 12
	G: 'GG', // 0 - 24
	g: 'G', // 1 - 12
	j: 'DD', // 1 to 31
	n: 'MM', // 1 to 31
};

export function convertToDbFormat(format: string): string
{
	let result = format;
	for (const [from, to] of Object.entries(replacements))
	{
		result = result.replace(from, to);
	}

	return result;
}

// const tests = {
// 	'Y-m-d H:i': 'YYYY-MM-DD HH:MI:SS',
// 	'Y/m/d G:i': 'YYYY/MM/DD HH:MI:SS',
// 	'd-m-Y H:i': 'DD/MM/YYYY HH:MI:SS',
// 	'd.m.Y H:i': 'DD.MM.YYYY HH:MI:SS',
// 	'd/m/Y H:i': 'DD/MM/YYYY HH:MI:SS',
// 	'd/m/Y H:i \น\.': 'DD/MM/YYYY HH:MI:SS',
// 	'd/m/Y g:i a': 'DD/MM/YYYY H:MI:SS T',
// 	'd/m/Y g:i a': 'DD/MM/YYYY HH:MI:SS',
// 	'j.m.Y H:i': 'DD.MM.YYYY HH:MI:SS',
// 	'j/n/Y G:i': 'DD.MM.YYYY HH:MI:SS',
// 	'j/n/Y G:i': 'DD/MM/YYYY HH:MI:SS',
// 	'j/n/Y H:i': 'DD/MM/YYYY HH:MI:SS',
// 	'j/n/Y g:i a': 'DD/MM/YYYY HH:MI:SS', //
// 	'j/n/Y g:i a': 'DD/MM/YYYY H:MI:SS T', // co
// 	'n/j/Y g:i a': 'MM/DD/YYYY H:MI:SS T',
// 	// 'n/j/Y g:i a': 'DD-MM-YYYY H:MI:SS T', // hi
// };
