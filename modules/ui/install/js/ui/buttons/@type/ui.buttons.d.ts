declare module 'ui.buttons'
{
	namespace UI
	{
		class BaseButton
		{
			constructor(options: { [key: string]: any })
			render(): HTMLElement
			renderTo(node: HTMLElement): HTMLElement | null
			getContainer(): HTMLElement
			setText(text: string): BaseButton
			getText(): string
			getTag(): any
			setProps(props: { [key: string]: string }): BaseButton
			getProps(): { [key: string]: string }
			setDataSet(dataset: { [key: string]: string }): { [key: string]: string }
			getDataSet(): DOMStringMap
			addClass(className: string): BaseButton
			removeClass(className: string): BaseButton
			setDisabled(state: boolean): BaseButton
			isDisabled(): boolean
			isInputType(): boolean
			bindEvents(events: { [key: string]: () => void }): BaseButton
			unbindEvents(events: { [key: string]: () => void }): BaseButton
			bindEvent(event: string, handler: (event: Event) => void): BaseButton
			unbindEvent(event: string, handler: (event: Event) => void): BaseButton
		}

		class Button extends BaseButton
		{
			static Size: {
				LARGE: string,
				MEDIUM: string,
				SMALL: string,
				EXTRA_SMALL: string,
			};

			static Color: {
				DANGER: string,
				DANGER_DARK: string,
				DANGER_LIGHT: string,
				SUCCESS: string,
				SUCCESS_LIGHT: string,
				PRIMARY_DARK: string,
				PRIMARY: string,
				SECONDARY: string,
				LINK: string,
				LIGHT: string,
				LIGHT_BORDER: string,
			};

			static State: {
				HOVER: string,
				ACTIVE: string,
				DISABLED: string,
				CLOCKING: string,
				WAITING: string,
			};

			static Icon: {
				UNFOLLOW: string,
				FOLLOW: string,
				ADD: string,
				STOP: string,
				START: string,
				ADD_FOLDER: string,
				PAUSE: string,
				SETTING: string,
				TASK: string,
				INFO: string,
				SEARCH: string,
				PRINT: string,
				LIST: string,
				BUSINESS: string,
				BUSINESS_CONFIRM: string,
				BUSINESS_WARNING: string,
				CAMERA: string,
				PHONE_UP: string,
				PHONE_DOWN: string,
				BACK: string,
				REMOVE: string,
				DONE: string,
				DISK: string,
			};

			static Tag: {
				BUTTON: number,
				LINK: number,
				SUBMIT: number,
				INPUT: number,
			};

			static Style: {
				NO_CAPS: string,
				ROUND: string,
				DROPDOWN: string,
			};

			setSize(size: string): Button
			getSize(): string
			setColor(color: string): Button
			getColor(): string
			setIcon(icon: string): Button
			getIcon(): string
			setState(state: string): Button
			getState(): string
			setNoCaps(value: boolean): Button
			setRound(value: boolean): Button
			setDropdown(value: boolean): Button
			setMenu(options: { [key: string]: any }): Button
			getMenuBindElement(): HTMLElement
			getMenuClickElement(): HTMLElement
			getMenuWindow(): any
			setId(id: string): Button
			getId(): string | null
			setActive(value: boolean): Button
			isActive(): boolean
			setHovered(value: boolean): Button
			isHover(): boolean
			setDisabled(value: boolean): Button
			isDisabled(): boolean
			setWaiting(value: boolean): Button
			isWaiting(): boolean
			setClocking(value: boolean): Button
			isClocking(): boolean
			setContext(context: any): void
			getContext(): any
		}

		class SaveButton extends Button
		{
		}

		class CreateButton extends Button
		{
		}

		class AddButton extends Button
		{
		}

		class SendButton extends Button
		{
		}

		class ApplyButton extends Button
		{
		}

		class CancelButton extends Button
		{
		}

		class CloseButton extends Button
		{
		}

		class SplitButton extends Button
		{
			static State: {
				HOVER: string,
				MAIN_HOVER: string,
				MENU_HOVER: string,
				ACTIVE: string,
				MAIN_ACTIVE: string,
				MENU_ACTIVE: string,
				DISABLED: string,
				MAIN_DISABLED: string,
				MENU_DISABLED: string,
				CLOCKING: string,
				WAITING: string,
			};

			getMainButton(): SplitSubButton
			getMenuButton(): SplitSubButton
			getMenuTarget(): any
		}

		class SplitSubButton extends BaseButton
		{
			static Type: {
				MAIN: string,
				MENU: string,
			};

			setSplitButton(button: SplitButton): SplitSubButton
			getSplitButton(): SplitButton
			isMainButton(): boolean
			isMenuButton(): boolean
			setActive(value: boolean): SplitSubButton
			isActive(): boolean
			setHovered(value: boolean): SplitSubButton
			isHovered(): boolean
		}

		class SaveSplitButton extends SplitButton
		{
		}

		class CreateSplitButton extends SplitButton
		{
		}

		class AddSplitButton extends SplitButton
		{
		}

		class SendSplitButton extends SplitButton
		{
		}

		class ApplySplitButton extends SplitButton
		{
		}

		class CancelSplitButton extends SplitButton
		{
		}

		class CloseSplitButton extends SplitButton
		{
		}
	}
}