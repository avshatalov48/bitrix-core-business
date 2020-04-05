/**
 * Fetches entities from entries
 * @param {BX.Landing.History.Entry[]} items
 * @return {Promise<any>}
 */
export default function fetchEntities(items): Promise<any>
{
	const entities = {blocks: [], images: []};

	items.forEach((item) => {
		if (item.command === 'addBlock')
		{
			entities.blocks.push(item.block);
		}

		if (item.command === 'editImage')
		{
			entities.images.push({block: item.block, id: item.redo.id});
		}
	});

	return Promise.resolve(entities);
}