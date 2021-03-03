import {Type} from 'main.core';
export class Login
{
	constructor(options = {
		provider:{
			TYPE: null,
			AUTH_URL: null
		}
	})
	{
		this.provider = options.provider ?? null
	}

	login()
	{
		if(this.provider && Type.isString(this.provider['AUTH_URL']))
		{
			BX.util.popup(this.provider.AUTH_URL,800,600);

		}
	}
}