const VkApi   = require('./VkApi');
const Promise = require('bluebird');
const fs      = Promise.promisifyAll(require('fs'));
const lib     = require('./lib');
const readline = require('readline');

const gifPath       = './gifs/';
const _token        = 'bb4b7eff46af950564f72c3b66e78e3441ed9a7da342c6c37167d2f7b023e356b27ab9d1b87b880611db0';
const captchaAPIKey = '2f54bb2ffb6a092f725a35366deed8f2';
const apiUrl        = 'http://web:80/api/';

const taskUploadGif = async (title) => {
	const vkApi = new VkApi(_token);
	
	const uploadUrl         = await vkApi.getuploadUrl();
	const resultSendingFile = await vkApi.sendFile({
		file: `${gifPath}${title}.gif`, 
		url : uploadUrl,
		onProgress: function (progress) {
			//process.stdout.clearLine();
			//process.stdout.cursorTo(0);
			readline.cursorTo(0);
			process.stdout.write(`Uploaded: ${progress.percent}% | size: ${progress.mbSent} / ${progress.mbSize} Mb | speed: ${progress.speed} Mb/sec |`);
		}
	});

	//console.log('resultSendingFile' + resultSendingFile);
	
	let resSaveFile = await vkApi.saveFile({
		uploadFile : resultSendingFile,
		title      : title
	});

	//console.log('taskuploadgif.resSaveFile = ' + resSaveFile);

	let jsonRes   = JSON.parse(resSaveFile);

	//console.log(jsonRes);

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
	//console.log();
	//console.log('Saved file :');
	//console.log(savedFile.preview.photo);

	/* 
{ response: 
 { id: 464403517,
   owner_id: 223261420,
   title: 'Shakal',
   size: 559135,
   ext: 'gif',
   url: 'https://vk.com/doc223261420_464403517?hash=3e4d02faa0e498ad69&dl=GIZDGMRWGE2DEMA:1524494581:64c7d4ea525bd715e9&api=1&no_preview=1',
   date: 1524494581,
   type: 3,
   preview: [Object] } ] }
	*/

	console.log();
	console.log(apiUrl + 'Gif.add:');
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
		console.log('------end try(()=>{})();------');

	} catch (error) {
		if (error.response) {
			console.log('----------body-------------');
			console.log(error.response.body);
			return console.log('status> ', error.response.statusCode);
		}
		
		console.log('error! ', error);
	}
})();
