import {EventEmitter} from 'main.core.events'


export default class UserController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		EventEmitter.subscribe('onChangeUser', this.markAsChanged.bind(this));
	}

	rollback()
	{
		super.rollback();
		if (this._isChanged)
		{
			this._isChanged = false;
		}
	}
}