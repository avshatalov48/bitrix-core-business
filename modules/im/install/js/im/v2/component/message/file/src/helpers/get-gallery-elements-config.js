import type { JsonObject } from 'main.core';

export function getGalleryElementsConfig(filesCount: number, index: number): JsonObject
{
	const spanValues = {
		10: ['1-4', '1-1', '1-2', '1-1', '1-1', '1-2', '1-1', '1-1', '1-2', '1-1'],
		9: ['1-4', '1-1', '1-2', '1-1', '1-2', '1-2', '1-1', '1-2', '1-1'],
		8: ['1-4', '1-2', '1-2', '1-1', '1-2', '1-1', '1-2', '1-2'],
		7: ['1-4', '1-2', '1-2', '1-2', '1-2', '1-2', '1-2'],
		6: ['1-4', '1-2', '1-2', '1-1', '1-2', '1-1'],
		5: ['1-4', '1-2', '1-2', '1-2', '1-2'],
		4: ['2-4', '1-1', '1-2', '1-1'],
		3: ['2-4', '1-2', '1-2'],
		2: ['2-2', '2-2'],
	};

	const spanValue = spanValues[filesCount] && spanValues[filesCount][index];
	if (!spanValue)
	{
		return {
			'grid-row-end': 'span 1',
			'grid-column-end': 'span 1',
		};
	}

	const [rowSpan, colSpan] = spanValue.split('-');

	return {
		'grid-row-end': `span ${rowSpan}`,
		'grid-column-end': `span ${colSpan}`,
	};
}
