export function validateImageUrl(url: string): boolean
{
	return /^(http:|https:|ftp:|\/)/i.test(url);
}
