import ColumnItemOptions from "../columnitem";
import {EventEmitter} from "main.core.events";
import "ui.switcher";
import {Dom} from "main.core";
import Changer from "./changer";

export default class Toggler extends Changer
{
	static TYPE = 'toggler';

	constructor(options: ColumnItemOptions)
	{
		super(options);

		this.switcher = new BX.UI.Switcher(
			{
				size: 'small',
				checked: this.currentValue === '1',
				handlers: {
					checked: () => {
						EventEmitter.emit('BX.UI.AccessRights.ColumnItem:accessOn', this);
					},
					unchecked: () => {
						EventEmitter.emit('BX.UI.AccessRights.ColumnItem:accessOff', this);
					},
					toggled: () => {
						this.adjustChanger();
						EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
					}
				}
			}
		);
	}

	offChanger(): void
	{
		if (this.isModify)
		{
			this.switcher.check(!this.switcher.isChecked());
		}

		super.offChanger();
	}

	render(): HTMLElement
	{
		Dom.append(this.switcher.getNode(), this.getChanger());

		return this.getChanger();
	}
}
