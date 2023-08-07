import FileType from "./file-type";

const pictureIcon = `<svg width="47" height="46" viewBox="0 0 47 46" xmlns="http://www.w3.org/2000/svg" fill="none">
	<g clip-path="url(#clip0_8133_191353)">
		<path opacity="0.9" d="M36.159 28.8509L10 54.3294L67 54.8122L40.3454 28.8509C39.1805 27.7164 37.3238 27.7164 36.159 28.8509Z" fill="#7FDEFC"/>
		<path opacity="0.9" d="M14.5661 21.8695L-20 56.7756H54L18.7904 21.8695C17.6209 20.7102 15.7356 20.7102 14.5661 21.8695Z" fill="#2FC6F6"/>
		<circle cx="31" cy="10" r="6" fill="white"/>
	</g>
	<defs>
		<clipPath id="clip0_8133_191353">
			<rect width="47" height="46" rx="2" fill="white"/>
		</clipPath>
	</defs>
</svg>`;

const audioIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none">
<path fill-rule="evenodd" clip-rule="evenodd" d="M15.0893 14.4632L12.8875 14.6591V24.7692C12.232 24.4188 11.4597 24.2166 10.6329 24.2166C8.25839 24.2166 6.3335 25.8843 6.3335 27.9415C6.3335 29.9987 8.25839 31.6663 10.6329 31.6663C13.005 31.6663 15.0856 30.0019 15.0893 27.9475M15.0893 14.4632V27.9415V14.4632Z" fill="#11A9D9"/>
<path d="M14.045 7.78021C13.3883 7.84485 12.886 8.42959 12.886 9.1295V14.6697L27.8159 13.1786V23.1779C27.1607 22.828 26.3889 22.6261 25.5627 22.6261C23.1883 22.6261 21.2634 24.2937 21.2634 26.3509C21.2634 28.4081 23.1883 30.0758 25.5627 30.0758C27.9041 30.0758 30.0278 28.4543 30.0804 26.4367L30.0815 26.3509V12.9772L30.0835 12.977V7.68821C30.0835 6.89039 29.4372 6.26523 28.6886 6.33891L14.045 7.78021Z" fill="#11A9D9"/>
</svg>`;

const getSvgFromString = (svg: string): SVGElement => {
	const parser = new DOMParser();
	const doc = parser.parseFromString(svg, "image/svg+xml");
	return doc.querySelector('svg');
};

const FileTypeIcon = Object.freeze({
	[FileType.PICTURE]: () => getSvgFromString(pictureIcon),
	[FileType.AUDIO]: () => getSvgFromString(audioIcon)
});

export const getFileTypeIcon = (fileType: string): SVGElement | null => {
	if (FileTypeIcon[fileType])
	{
		return FileTypeIcon[fileType]();
	}

	return null;
};