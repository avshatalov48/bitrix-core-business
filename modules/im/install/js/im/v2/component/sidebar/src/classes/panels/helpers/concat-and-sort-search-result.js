export function concatAndSortSearchResult(concatArrayFirst: number[], concatArraySecond: number[]): number[]
{
	return [...concatArrayFirst, ...concatArraySecond].sort((a, z) => z - a);
}
