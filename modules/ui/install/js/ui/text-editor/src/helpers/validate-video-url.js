export function validateVideoUrl(url: string): boolean
{
	return /^(http:|https:|\/)/i.test(url);
}
