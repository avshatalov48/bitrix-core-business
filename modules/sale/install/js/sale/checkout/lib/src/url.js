import { Uri } from 'main.core';

export class Url
{
	static getCurrentUrl()
	{
		return window.location.protocol + "//" + window.location.hostname + (window.location.port != '' ? ':' + window.location.port : '') +
			window.location.pathname + window.location.search;
	}
	
	static addLinkParam(link, name, value)
	{
		if(!link.length)
		{
			return '?' + name + '=' + value;
		}
		link = Uri.removeParam(link, name);
		if(link.indexOf('?') != -1)
		{
			return link + '&' + name + '=' + value;
		}
		return link + '?' + name + '=' + value;
	}
}