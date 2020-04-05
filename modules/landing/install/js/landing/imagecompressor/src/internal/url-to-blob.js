import {Type, ajax} from 'main.core';

export default function urlToBlob(url)
{
	if (!Type.isString(url))
	{
		return Promise.resolve(url);
	}

	return new Promise((resolve, reject) => {
		try
		{
			const xhr = ajax.xhr();
			xhr.open('GET', url);
			xhr.responseType = 'blob';
			xhr.onerror = () => {
				reject(new Error('Network error.'));
			};
			xhr.onload = () => {
				if (xhr.status === 200)
				{
					resolve(xhr.response);
				}
				else
				{
					reject(new Error(`Loading error: ${xhr.statusText}`));
				}
			};
			xhr.send();
		}
		catch (err)
		{
			reject(err.message);
		}
	});
}