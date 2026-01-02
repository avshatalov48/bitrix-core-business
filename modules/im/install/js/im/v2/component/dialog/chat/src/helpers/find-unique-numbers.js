export function findUniqueNumbers(arr1: number[], arr2: number[]): number[]
{
	const set1 = new Set(arr1);
	const set2 = new Set(arr2);

	return [
		...arr1.filter((num) => !set2.has(num)),
		...arr2.filter((num) => !set1.has(num)),
	];
}
