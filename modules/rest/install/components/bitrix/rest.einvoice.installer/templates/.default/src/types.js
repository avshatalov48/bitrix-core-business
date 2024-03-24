export type EInvoiceInstallerOptions = {
	wrapper: HTMLElement,
	apps: Array<AppConfig>,
	formConfiguration: FormConfiguration,
}

export type AppConfig = {
	name: string,
	code: string,
}

export type FormConfiguration = {
	from_domain: string,
	b24_plan: string,
}
