import PreviewContent from "../../src/view/previewcontent.js";

describe('BX.Sender.ConsentPreview', () => {

	describe('Basic usage', () => {
		it('Should call twice', () => {
			const previewMock = {
				reDraw: () => {
					return 'test text';
				}
			};
			const previewContent = new PreviewContent();
			const mock = sinon.replace(previewContent, "reDraw", sinon.fake(previewMock.reDraw))

			previewContent.changeActiveTab('mobile');
			previewContent.changeActiveTab('desktop');
			assert.equal(mock.callCount, 2);
		});
	});
});