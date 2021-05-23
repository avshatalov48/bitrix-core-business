import {SelectorField} from "./selector.field";

export class TagField extends SelectorField
{
	getValue()
	{
		return Object.keys(this.value).join(",");
	}
}