'use strict';

const Promise  = require('bluebird');
const lib      = require('./lib');
const fs       = Promise.promisifyAll(require('fs'));

const vkApiUrl = 'https://api.vk.com/method';

class VkApi {
	constructor(token) {
		this._token = token;
	};

	apiRequest(method, opts) {
		opts = opts || {};

		const requestData = {
			uri    : `${vkApiUrl}/${method}`,
			method : 'POST',
		};

		const form           = opts.data || {};
		form['access_token'] = this._token;
		requestData['form']  = form;

		if (opts.onProgress) {
			requestData[onProgress] = opts.onProgress;
		}

		return lib.rp(requestData);
	};

	async getuploadUrl() {
		const result = await this.apiRequest('docs.getUploadServer');
		return JSON.parse(result).response.upload_url;
	};

	async sendFile(opts) {
		const stat = await fs.statAsync(opts.file);
		const reqData = {
			uri    : opts.url,
			method : 'POST',
			formData : {
				file : fs.createReadStream(opts.file),
			},
		};

		if (opts.onProgress) {
			reqData.onProgress = (sent, speed) => {
				const percent = lib.roundToPlace(sent * 100 / stat.size, 2);
				const mbSent  = lib.roundToPlace(sent / Math.pow(2, 20), 2);
				const mbSize  = lib.roundToPlace(stat.size / Math.pow(2, 20), 2);

				opts.onProgress({
					percent,
					mbSent,
					mbSize,
					speed
				});
			};
		}

		const uploadResult = await lib.rp(reqData);
		return JSON.parse(uploadResult).file;
	};

	saveFile(opts) {
		if (!opts.uploadFile) {
			throw new Error("error while uploading gif");
		}

		return this.apiRequest('docs.save', {
			data : {
				file  : opts.uploadFile,
				title : opts.title,
			}
		});
	};
}

module.exports = VkApi;