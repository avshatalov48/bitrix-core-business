import {BaseBlock} from './base-block';

export class Loader extends BaseBlock
{
	layout()
	{
		this.getWrapper();
		this.clearLayout();
	}
}