const VkApi   = require('./VkApi');
const Promise = require('bluebird');
const fs      = Promise.promisifyAll(require('fs'));
const lib     = require('./lib');

const gifPath       = './gifs/';
const _token        = '8ad59c39e12dd54444b130ace24f2110b7bc1db4def6de09c77851e7e1675dfe16eff2ce9919d5e3a8b51';
const captchaAPIKey = '2f54bb2ffb6a092f725a35366deed8f2';
const apiUrl        = 'http://new.poster.dev/api/';

const taskUploadGif = async (title) => {
	const vkApi = new VkApi(_token);
	
	const uploadUrl         = await vkApi.getuploadUrl();
	const resultSendingFile = await vkApi.sendFile({
		file: `${gifPath}${title}.gif`, 
		url : uploadUrl,
		onProgress: function (progress) {
			process.stdout.clearLine();
			process.stdout.cursorTo(0);
			process.stdout.write(`Uploaded: ${progress.percent}% | size: ${progress.mbSent} / ${progress.mbSize} Mb | speed: ${progress.speed} Mb/sec |`);
		}
	});
	
	let resSaveFile = await vkApi.saveFile({
		uploadFile : resultSendingFile,
		title      : title
	});
	let jsonRes   = JSON.parse(resSaveFile);
	const jsonErr = jsonRes['error'];
	if (jsonErr) {
		if (jsonErr['error_code'] !== 14) {
			throw jsonErr;
		}
		
		// @TODO: при неправльной разгаданой капчи нужна рекурсия
		console.log('поймали капчу');
		const captchaAnswer = await sendCaptcha(jsonErr['captcha_img']);
		const resSaveFile   = await vkApi.saveFile({
			uploadFile : resultSendingFile,
			title      : title,
			captcha: {
				sid    : jsonErr['captcha_sid'],
				answer : captchaAnswer
			}
		});
		
		jsonRes = JSON.parse(resSaveFile);
	}
	
	const savedFile = jsonRes['response'][0];
	
	const apiResult = await lib.rp({
		method : 'post',
		uri    : apiUrl + 'Gif.add',
		form: {
			doc_id   : savedFile['did'],
			owner_id : savedFile['owner_id'],
			title    : title,
			url      : savedFile['url'],
			thumb    : savedFile['thumb']
		}
	});
};

const sendCaptcha =  async (image) => {
	const captchaImg = await lib.rp({
		method   : 'get',
		encoding : 'base64',
		uri      : image,
	});
	
	var formData = {
		key    : captchaAPIKey,
		body   : captchaImg,
		method : 'base64',
		json   : 1
	};
	
	const requestApiCaptcha = await lib.rp({
		method   : 'post',
		uri      : 'http://rucaptcha.com/in.php',
		formData : formData
	});
	
	await Promise.delay(5000);
	const captchaRes = await lib.rp({
		method : 'post',
		uri    : 'http://rucaptcha.com/res.php',
		form: {
			key    : captchaAPIKey,
			action : 'get',
			id     : JSON.parse(requestApiCaptcha)['request'],
			json   : 1
		}
	});
	
	return JSON.parse(captchaRes)['request'];
};

const loopTask = async (gif) => {
	try {
		await taskUploadGif(gif);
	} catch (error) {
		if (error.response && error.response.statusCode === 504) {
			console.log("Загрузка привела к 504, попробуем еще раз...");
			Promise.delay(1500);
			return loopTask(gif);
		}
		
		throw error;
	}
};


(async () => {
	try {
		const files = await fs.readdirAsync(gifPath);
		const gifRegExp = /\.gif$/;
		const gifs = files
			.filter(fileName => gifRegExp.test(fileName))
			.map(   fileName => fileName.replace(gifRegExp, ''));
		
		const gifLength = gifs.length;
		for ([index, gif] of gifs.entries()) {
			console.log(`Загружаем ("${gif}"): ${index + 1} / ${gifLength}`);
			await loopTask(gif);
			console.log('\n__________');
		}
	} catch (error) {
		if (error.response) {
			return console.log('status> ', error.response.statusCode);
		}
		
		console.log('error! ', error);
	}
})();