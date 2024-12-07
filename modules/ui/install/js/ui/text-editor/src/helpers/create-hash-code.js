export function createHashCode(s): number
{
	return [...s].reduce(
		(hash, c) => Math.trunc(Math.imul(31, hash) + c.codePointAt(0)),
		0,
	);
}
