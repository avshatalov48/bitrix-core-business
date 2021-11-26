type UploaderType = {
	//main params
	uploadFileUrl: ?string,
	input: ?Element,
	id: string,

	//additional params to upload
	controlId: ?string,

	uploadMethod: ?string, // immediate || deferred
	uploadFormData: ?string, // Y || N
	filesInputMultiple: ?string, // Y || N

	uploadInputName: ?string,
	uploadInputInfoName: ?string,
	deleteFileOnServer: ?string,
	pasteFileHashInForm: ?string,

	// additional params for Queue
	queueFields: UploaderQueueType,
	fields: ?Object,
	copies: ?Object,
	placeHolder: ?Element,


	//additional params for visual
	dropZone: ?Element,
	events: ?Object, // just for binding events {eventName => eventHandler }
}
type UploaderQueueType = {
	fields: ?Object,
	copies: ?Object, // an object with copies for image {copyName => {width: number, height: number}} },
	placeHolder: ?Element,
	showImage: ?string, // Y | N
	sortItems: ?string // Y | N
	thumb: ?Object
};
export {UploaderType}