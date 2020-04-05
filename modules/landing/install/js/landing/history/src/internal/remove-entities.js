import type {History} from '../history';

/**
 * Makes request with removed entities
 * @param {{
 * 		blocks: int[],
 * 		images: {block: int, id: int}[]
 * 	}} entities
 * @param {History} history
 * @return {Promise<History>}
 */
export default function removeEntities(entities, history: History): Promise<History>
{
	// if (entities.blocks.length || entities.images.length)
	// {
	// 	return BX.Landing.Backend.getInstance().action("Landing::removeEntities", {data: entities})
	// 		.then(function() {
	// 			return onNewBranch(history);
	// 		})
	// 		.then(onUpdate);
	// }

	return Promise.resolve(history);
}