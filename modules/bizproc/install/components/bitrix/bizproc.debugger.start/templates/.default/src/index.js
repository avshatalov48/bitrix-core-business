import { Dom, Event, Reflection, Type, Text, Loc } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Mode, Session } from 'bizproc.debugger';
import { Button, ButtonColor, ButtonSize } from 'ui.buttons';
import './css/style.css';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class DebuggerStartComponent
{
	#documentSigned: string = '';
	#activeSession: Session | null = null;
	#currentUserId: number = null;

	constructor(options)
	{
		this.#documentSigned = options.documentSigned;
		this.#activeSession = options.activeSession ? new Session(options.activeSession) : null;
		this.#currentUserId = Text.toInteger(options.currentUserId);
	}

	init()
	{
		if (this.#activeSession)
		{
			this.#disableButtons();
			this.#setActiveSessionHint();

			if (this.#currentUserId === this.#activeSession.startedBy)
			{
				this.#renderFinishSessionButton();
			}
		}
		else
		{
			this.#initEvents();
		}
	}

	get buttons(): Object
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
			Dom.attr(buttons[key], 'disabled', null);
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

	#renderFinishSessionButton()
	{
		const buttons = this.buttons;
		const mode = this.#activeSession.modeId;

		if (buttons[mode])
		{
			const buttonId = buttons[mode].id;

			const button = new Button({
				props: {
					id: buttonId,
				},
				color: ButtonColor.LIGHT_BORDER,
				size: ButtonSize.SMALL,
				text: Loc.getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_FINISH'),
				round: true,
				onclick: this.#onFinishSessionClick.bind(this),
			});

			Dom.replace(buttons[mode], (button).render());
		}
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
							this.#reject(
								response,
								() => {
									this.#enableButtons();
									Dom.removeClass(btn, 'ui-btn-wait');

									return true;
								}
							);
						},
					)
				;
			}
		);
	}

	#onFinishSessionClick(button: Button)
	{
		button.setDisabled(true);
		button.setWaiting(true);

		if (top.BX.Bizproc.Debugger)
		{
			top.BX.Bizproc.Debugger.Manager.Instance.askFinishSession(this.#activeSession)
				.then(
					this.#onAfterFinishSession.bind(this),
					(response) => {
						this.#reject(
							response,
							() => {
								button.setDisabled(false);
								button.setWaiting(false);

								return true;
							}
						);
					}
				)
			;
		}
		else
		{
			this.#activeSession.finish()
				.then(
					this.#onAfterFinishSession.bind(this),
					(response) => {
						this.#reject(
							response,
							() => {
								button.setDisabled(false);
								button.setWaiting(false);

								return true;
							}
						);
					}
				)
			;
		}
	}

	#onAfterFinishSession()
	{
		const buttons = this.buttons;
		const mode = this.#activeSession.modeId;
		const buttonId = buttons[mode].id;

		this.#enableButtons();
		this.#activeSession = null;

		Dom.replace(
			buttons[mode],
			(new Button({
				props: {
					id: buttonId,
				},
				color: ButtonColor.SUCCESS,
				size: ButtonSize.SMALL,
				text: Loc.getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_START'),
				round: true,
			})).render()
		);

		this.init();
	}

	#reject(response, callback: Function)
	{
		if (Type.isArrayFilled(response.errors))
		{
			let message = '';
			response.errors.forEach((error)=>{
				message = message + '\n' + error.message;
			});

			MessageBox.alert(message, callback);
		}
		else if (Type.isFunction(callback))
		{
			callback();
		}
	}
}

namespace.DebuggerStartComponent = DebuggerStartComponent;