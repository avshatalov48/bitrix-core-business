

export class TimeStamp
{
	/**
	 * Constructor.
	 */
	constructor()
	{
		this.removeTimestamp();
	}

	/**
	 * Removes 'ts' param from query string.
	 * @return {void}
	 */
	removeTimestamp()
	{
		let uri = window.location.toString();

		uri = uri.replace(/(ts=[\d]+[&]*)/, '');
		if (uri.slice(-1) === '?' || uri.slice(-1) === '&')
		{
			uri = uri.slice(0, -1);
		}

		window.history.replaceState({}, document.title, uri);
	}
}