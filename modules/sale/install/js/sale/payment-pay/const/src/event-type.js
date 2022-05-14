export const EventType = Object.freeze({
	payment: {
		start: 'Sale:PaymentPay:Payment:Start',
		error: 'Sale:PaymentPay:Payment:Error',
		success: 'Sale:PaymentPay:Payment:Success',
		reset: 'Sale:PaymentPay:Payment:Reset',
	},
	consent: {
		accepted: 'Sale:PaymentPay:Consent:Accepted',
		refused: 'Sale:PaymentPay:Consent:Refused',
	},
	global: {
		paySystemAjaxError: 'onPaySystemAjaxError',
		paySystemUpdateTemplate: 'onPaySystemUpdateTemplate',
	},
});