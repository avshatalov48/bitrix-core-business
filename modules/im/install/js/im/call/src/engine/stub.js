import {Type} from 'main.core';

export class CallStub
{
	constructor(config)
	{
		this.callId = config.callId;
		this.lifetime = config.lifetime || 120;
		this.callbacks = {
			onDelete: Type.isFunction(config.onDelete) ? config.onDelete : BX.DoNothing
		};

		this.deleteTimeout = setTimeout( () =>
		{
			this.callbacks.onDelete({
				callId: this.callId
			})
		}, this.lifetime * 1000);
	};

	__onPullEvent(command, params, extra)
	{
		// do nothing
	};

	isAnyoneParticipating()
	{
		return false;
	};

	addEventListener()
	{
		return false;
	};

	removeEventListener()
	{
		return false;
	};

	destroy()
	{
		clearTimeout(this.deleteTimeout);
		this.callbacks.onDelete = BX.DoNothing;
	};
}