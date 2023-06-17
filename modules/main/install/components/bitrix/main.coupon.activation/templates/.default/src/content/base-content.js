import {Tag} from "main.core";
import {Popup} from "main.popup";

export default class BaseContent
{
	getContent(): HTMLElement
	{
		return Tag.render('<div></div>');
	}

	getButtonCollection(): Array
	{
		return [];
	}

	init(popup: Popup): void
	{

	}
}