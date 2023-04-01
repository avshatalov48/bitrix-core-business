import {EventEmitter} from 'main.core.events'


export default class IblockElementController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		EventEmitter.subscribe('onChangeIblockElement', this.markAsChanged.bind(this));
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