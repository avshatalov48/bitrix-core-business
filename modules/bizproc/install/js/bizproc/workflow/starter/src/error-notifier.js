import { Text, Type, Loc } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';

export class ErrorNotifier
{
	#messages: [] = [];
	constructor(errors: ?[])
	{
		if (Type.isArrayFilled(errors))
		{
			this.#setMessages(errors);
		}
	}

	#setMessages(errors: [])
	{
		errors.forEach((error) => {
			if (Type.isStringFilled(error))
			{
				this.#messages.push(Text.encode(error));
			}
			else if (Type.isPlainObject(error) && Type.isStringFilled(error.message))
			{
				if (Type.isStringFilled(error.code) && error.code === 'NETWORK_ERROR')
				{
					this.#messages.push(Text.encode(this.#defaultErrorMessage));
				}
				else
				{
					this.#messages.push(Text.encode(error.message));
				}
			}
		});
	}

	show()
	{
		this.#showMessages(MessageBox);
	}

	showToWindow(targetWindow: Window)
	{
		targetWindow.BX.Runtime.loadExtension('ui.dialogs.messagebox')
			.then(() => {
				this.#showMessages(targetWindow.BX.UI.Dialogs.MessageBox);
			})
			.catch(() => {})
		;
	}

	#showMessages(messageBox)
	{
		if (!messageBox)
		{
			return;
		}

		if (Type.isArrayFilled(this.#messages))
		{
			messageBox.alert(this.#messages.join('<br>'));

			return;
		}

		messageBox.alert(Text.encode(this.#defaultErrorMessage));
	}

	get #defaultErrorMessage(): string
	{
		return Loc.getMessage('BIZPROC_JS_WORKFLOW_STARTER_REQUEST_FAILED');
	}
}
