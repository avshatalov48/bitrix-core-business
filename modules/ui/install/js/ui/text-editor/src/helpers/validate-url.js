export function validateUrl(url: string): boolean
{
	return /^(http:|https:|mailto:|tel:|sms:)/i.test(url);
}
