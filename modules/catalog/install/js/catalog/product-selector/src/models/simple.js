import {Base} from "./base";

export class Simple extends Base
{
	getName(): string
	{
		return this.getConfig('NAME', '');
	}
}