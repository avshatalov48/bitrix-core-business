export default {
	data: () => {
		return {
			isKeyboardShown: false,
		};
	},
	methods: {
		subscribeToKeyboardEvents(): void
		{
			window.app.exec('enableCaptureKeyboard', true);
			BXMobileApp.addCustomEvent('onKeyboardWillShow', () => {
				this.isKeyboardShown = true;
			});
			BXMobileApp.addCustomEvent('onKeyboardWillHide', () => {
				this.isKeyboardShown = false;
			});
		},
		adjustWindowHeight(): void
		{
			const currentHeight = window.innerHeight;
			document.body.style.height = `${currentHeight}px`;
		},
	},
}
