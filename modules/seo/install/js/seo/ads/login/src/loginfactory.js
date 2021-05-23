import {Login} from './login';
import {FacebookLogin} from "./facebooklogin";

export class LoginFactory
{
	static pool = {};

	static getLoginObject(provider)
	{
		if(provider && provider.TYPE)
		{
			let loginObject;
			switch (provider.TYPE)
			{
				case "facebook":
				case "instagram":
					loginObject = FacebookLogin;
					break;
				default:
					loginObject = Login;
					break;
			}
			return this.pool[provider.ENGINE_CODE] = this.pool[provider.ENGINE_CODE] ?? new loginObject({
				provider: provider
			});
		}
	}
}