export function getGalleryGridRowsConfig(filesCount: number): { gridTemplateRows: string }
{
	let rowsTemplate = '140px 80px';
	if (filesCount >= 7)
	{
		rowsTemplate = '140px 80px 80px 58px';
	}
	else if (filesCount >= 3)
	{
		rowsTemplate = '140px 80px 80px';
	}

	return { gridTemplateRows: rowsTemplate };
}
