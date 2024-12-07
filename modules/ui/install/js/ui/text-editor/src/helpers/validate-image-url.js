export function validateImageUrl(url: string): boolean
{
	return /^(http:|https:|ftp:|blob:|\/)/i.test(url);
}
