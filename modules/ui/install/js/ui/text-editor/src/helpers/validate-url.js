export function validateUrl(url: string, allowDomainRelativeUrl: boolean = true): boolean
{
	if (allowDomainRelativeUrl)
	{
		return /^(http:|https:|mailto:|tel:|sms:|\/)/i.test(url);
	}

	return /^(http:|https:|mailto:|tel:|sms:)/i.test(url);
}
