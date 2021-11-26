import Uploader from "./uploader";

export default class Manager {
	static getById(id) {
		return Uploader.getById(id);
	}
}
