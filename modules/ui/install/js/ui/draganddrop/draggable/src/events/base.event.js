import {Event} from 'main.core';

export default class BaseEvent extends Event.BaseEvent
{
	constructor(data)
	{
		super({data});
	}
}