'use strict';

const Promise  = require('bluebird');
const lib      = require('./lib');
const fs       = Promise.promisifyAll(require('fs'));

const vkApiVersion = '3.0';
const vkApiUrl = 'https://api.vk.com/method';

class VkApi {
	constructor(token) {
		this._token = token;
	};
	
	apiRequest(method, opts) {
		opts = opts || {};
		
		const requestData = {
			uri    : `${vkApiUrl}/${method}`,
			method : 'post',
		};
		
		const form           = opts.data || {};
		form['access_token'] = this._token;
		///
		form['v'] = opts.vkApiVersion ? opts.vkApiVersion : '5.74';
		///
		requestData['form']  = form;
		
		if (opts.onProgress) {
			requestData[onProgress] = opts.onProgress;
		}
		//console.log();
		//console.log('Api request into ' + requestData.uri + ' | ' + requestData.method);
		
		return lib.rp(requestData);
	};
	
	async getuploadUrl() {
		console.log();
		console.log('Getting upload url');
		const result = await this.apiRequest('docs.getUploadServer');
		//console.log(result);
		return JSON.parse(result).response.upload_url;
	};
	
	async sendFile(opts) {
		const stat = await fs.statAsync(opts.file);
		//console.log();
		//console.log(opts.file);
		//console.log();
		const reqData = {
			uri    : opts.url,
			method : 'post',
			formData : {
				file : fs.createReadStream(opts.file),
			},
		};

		//console.log('reqData' +  reqData);
		
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
		//console.log('');
		//console.log('Uploading result:');
		const uploadResult = await lib.rp(reqData);
		console.log(uploadResult);
		try {
			//console.log('');
			//console.log('Try send');
			const jsonRes = JSON.parse(uploadResult);
			//console.log('Sent: ' + jsonRes.file);
			return jsonRes.file;
		} catch (error) {
			console.log('Send error');
			console.log(error, uploadResult);
		}
	};
	
	saveFile(opts) {
		if (!opts.uploadFile) {
			throw new Error("error while uploading gif");
		}
		
		const data = {
			file  : opts.uploadFile,
			title : opts.title,
		};
		
		if (opts.captcha) {
			data.captcha_sid = opts.captcha.sid;
			data.captcha_key = opts.captcha.answer;
		}
		
		return this.apiRequest('docs.save', {
			data : data,
			vkApiVersion : '3'
		});
	};
}

module.exports = VkApi;