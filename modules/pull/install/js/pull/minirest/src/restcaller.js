export interface RestCaller {
	callMethod(method: string, params: Object): Promise
}
