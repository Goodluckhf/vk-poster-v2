const Promise = require('bluebird');
const fs = Promise.promisifyAll(require('fs'));
const request = require('request');

const _token = '8ad59c39e12dd54444b130ace24f2110b7bc1db4def6de09c77851e7e1675dfe16eff2ce9919d5e3a8b51';

const rp = (opts) => {
	return new Promise((resolve, reject) => {
		let timer;
		const r = request.post(opts, (err, response, body) => {
			if (opts.onProgress) {
				timer.clearInterval();
			}

			if (err) {
				return reject(err);
			}

			resolve(body);
		});

		if (opts.onProgress) {
			const interval = opts.progressInterval || 300;			
			
			timer = setInterval(() => {
				opts.onProgress(r.req.connection.bytesWritten);
			}, interval);
		}
	});
};

const getuploadUrl = async () => {
	const result = await rp({
		method: 'POST',
		url: 'https://api.vk.com/method/docs.getUploadServer',
		form: {
			access_token: _token			
		}
	});

	return JSON.parse(result).response.upload_url;
};

const sendFile = async (url) => {
	const path = 'test.gif';
	const stat = await fs.statAsync(path);
	const uploadResult = await rp({
		method: 'POST',
		uri: url, 
		formData: {
			file: fs.createReadStream(path),
		},
		onProgress: function (sent) {
			const percent = Math.ceil(sent / stat.size * 10000) / 100;
			console.log("Uploaded: " + percent + "% | all: " + stat.size);
		}
	});

	return uploadResult;
};

(async () => {
	try {
		const uploadUrl = await getuploadUrl();
		console.log('upload: ', uploadUrl);
		const resultSendingFile = await sendFile(uploadUrl);
	} catch (error) {
		console.log('error! ', error);
	}
})();