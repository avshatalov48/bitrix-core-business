export type CollapserParams = {
	id?: string,
	isOpen?: boolean,
	outerContainer: HTMLElement,
	innerContainer: HTMLElement,
	duration?: number,
	calcProgress?: function,
	buttons?: HTMLElement,
}