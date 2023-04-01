import {Dom} from "main.core";
import {Base} from "./base";
import {CacheCheckbox} from "../field/cache-checkbox";

export class Management extends Base
{
	getWrapper(): HTMLElement
	{
		const wrapper = super.getWrapper();
		const field = new CacheCheckbox(this.fields['SEO_CLEAR_VALUES'], this);

		Dom.append(field.layout(), wrapper);

		return wrapper;
	}
}