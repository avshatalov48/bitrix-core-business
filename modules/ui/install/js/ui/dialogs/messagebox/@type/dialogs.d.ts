declare module 'ui.dialogs'
{
	namespace BX
	{
		namespace UI
		{
			namespace Dialogs
			{
				interface MessageBoxOptions
				{
					message?: string | Element | Node;
					title?: string;
					popupOptions?: object;
					modal?: boolean;
					cacheable?: boolean;
					minWidth?: number;
					minHeight?: number;
					maxWidth?: number;
					onOk?: Function;
					onCancel?: Function;
					onYes?: Function;
					onNo?: Function;
					okCaption?: string;
					cancelCaption?: string;
					yesCaption?: string;
					noCaption?: string;
					mediumButtonSize?: boolean;
					buttons?: string | Array<any>;
				}

				class MessageBox
				{
					constructor(options: MessageBoxOptions);

					static alert(
						message: string | Element | Node,
						okCallback?: Function,
						okCaption?: string
					);

					static alert(
						message: string | Element | Node,
						title?: string,
						okCallback?: Function,
						okCaption?: string
					);

					static confirm(
						message: string | Element | Node,
						okCallback?: Function,
						okCaption?: string,
						cancelCallback?: Function
					)

					static confirm(
						message: string | Element | Node,
						title: string,
						okCallback?: Function,
						okCaption?: string,
						cancelCallback?: Function
					)
				}
			}
		}
	}
}