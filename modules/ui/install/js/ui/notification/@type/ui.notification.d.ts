declare module 'ui.notification'
{
	namespace UI
	{
		namespace Notification
		{
			namespace Center
			{
				function notify(options: {
					content?: string | Element,
					autoHide?: boolean,
					autoHideDelay?: number,
					zIndex?: number,
					closeButton?: boolean,
					category?: string,
					id?: string,
					actions?: Array<any>,
					render?: () => HTMLElement,
					width?: number,
					data?: object,
					events?: {[key: string]: () => void},
					position: "top-left" | "top-center" | "top-right" | "bottom-left" | "bottom-center" | "bottom-right",
					type?: string,
				});
			}
		}
	}
}