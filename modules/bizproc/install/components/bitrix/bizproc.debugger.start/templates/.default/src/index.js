import {Dom, Event, Reflection, Type, Text} from 'main.core';
import {MessageBox } from 'ui.dialogs.messagebox';
import {Mode, Session} from 'bizproc.debugger';
import './css/style.css';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class DebuggerStartComponent
{
	#documentSigned: string = '';
	#activeSession: Session | null = null;

	constructor(options)
	{
		this.#documentSigned = options.documentSigned;
		this.#activeSession = options.activeSession ? new Session(options.activeSession) : null;
	}

	init()
	{
		if (this.#activeSession)
		{
			this.#disableButtons();
			this.#setActiveSessionHint();
		}
		else
		{
			this.#initEvents();
		}
	}

	get buttons(): object
	{
		const buttons = {};
		buttons[Mode.experimental.id] = document.getElementById('bizproc-debugger-start-experimental-element');
		buttons[Mode.interception.id] = document.getElementById('bizproc-debugger-start-interception-element');

		return buttons;
	}

	#disableButtons()
	{
		const buttons = this.buttons;
		Object.keys(buttons).forEach(key => {
			Dom.addClass(buttons[key], 'ui-btn-disabled');
		});
	}

	#enableButtons()
	{
		const buttons = this.buttons;
		Object.keys(buttons).forEach(key => {
			Dom.removeClass(buttons[key], 'ui-btn-disabled');
		});
	}

	#setActiveSessionHint()
	{
		if (!this.#activeSession)
		{
			return;
		}

		const buttons = this.buttons;
		Object.keys(buttons).forEach(key => {
			Dom.attr(buttons[key], 'data-hint', Text.encode(this.#activeSession.shortDescription));
			Dom.attr(buttons[key], 'data-hint-no-icon', 'y');

			BX.UI.Hint.init(BX(buttons[key].id).parentElement);
		});
	}

	#initEvents()
	{
		const buttons = this.buttons;
		Object.keys(buttons).forEach(key => {
			Event.bind(buttons[key], 'click', () => {
				this.#onStartSessionClick(buttons[key], key);
			});
		});
	}

	#onStartSessionClick(btn: HTMLButtonElement, modeId: number)
	{
		top.BX.Runtime.loadExtension('bizproc.debugger')
			.then((exports) => {
				this.#disableButtons();
				Dom.addClass(btn, 'ui-btn-wait');

				const {Manager} = exports;
				Manager.Instance.startSession(this.#documentSigned, Text.toInteger(modeId))
					.then(
						() => {
							this.#enableButtons();
							BX.SidePanel.Instance.closeAll();

							return true;
						},
						(response) => {
							if (Type.isArrayFilled(response.errors))
							{
								let message = '';
								response.errors.forEach((error)=>{
									message = message + '\n' + error.message;
								});

								MessageBox.alert(
									message,
									() => {
										this.#enableButtons();
										Dom.removeClass(btn, 'ui-btn-wait');

										return true;
									}
								);
							}

							return true;
						}
					)
				;
			}
		);
	}
}

namespace.DebuggerStartComponent = DebuggerStartComponent;