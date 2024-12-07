export const TextareaObserverDirective = {
	mounted(element, binding)
	{
		binding.instance.textareaResizeManager.observeTextarea(element);
	},
	beforeUnmount(element, binding)
	{
		binding.instance.textareaResizeManager.unobserveTextarea(element);
	},
};
