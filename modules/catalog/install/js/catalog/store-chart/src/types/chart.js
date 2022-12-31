export type TChartProps = {
	id: string,
	label: string | null,
	isCommonChart: boolean, // if its true - we summarize all the information by series and make a single column
	seriesList: Array<TSeries>,
	columns: Array<TColumn>,
}

/**
 * Series - is the colored rows in chart column
 */
export type TSeries = {
	id: string,
	color: string,
	title: string,
	getPopupContent: ((Object) => (string | {title: string | null, content: string})) | null,
	weight: number | null,
};

/**
 * Type of column must have additional fields, that describes series of each column in <b>format</b>
 * <ul>
 * <li>Series1Id: ValueOfSeries1
 * <li>Series2Id: ValueOfSeries2
 * <li>etc...
 * </ul>
 */
export type TColumn = {
	id: string | null,
	name: string,
};