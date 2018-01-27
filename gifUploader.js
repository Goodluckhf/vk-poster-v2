const VkApi   = require('./VkApi');
const Promise = require('bluebird');
const fs      = Promise.promisifyAll(require('fs'));
const lib     = require('./lib');

const gifPath = './gifs/';
const _token  = '8ad59c39e12dd54444b130ace24f2110b7bc1db4def6de09c77851e7e1675dfe16eff2ce9919d5e3a8b51';
const apiUrl  = 'http://new.poster.dev/api/';

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
	
	const resSaveFile = await vkApi.saveFile({
		uploadFile : resultSendingFile,
		title      : title
	});
	const savedFile = JSON.parse(resSaveFile)['response'][0];
	
	const apiResult = await lib.rp({
		method : 'POST',
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
			await taskUploadGif(gif);
			console.log('\n__________');
		}
	} catch (error) {
		console.log('error! ', error);
	}
})();