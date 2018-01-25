const VkApi = require('./VkApi');

(async () => {
	try {
		const _token = '8ad59c39e12dd54444b130ace24f2110b7bc1db4def6de09c77851e7e1675dfe16eff2ce9919d5e3a8b51';
		const vkApi = new VkApi(_token);

		const uploadUrl = await vkApi.getuploadUrl();
		console.log('upload: ', uploadUrl);
		const resultSendingFile = await vkApi.sendFile({
			file: 'test.gif', 
			url : uploadUrl,
			onProgress: function (progress) {
				process.stdout.clearLine();
				process.stdout.cursorTo(0);
				process.stdout.write(`Uploaded: ${progress.percent}% | size: ${progress.mbSent} / ${progress.mbSize} Mb | speed: ${progress.speed} Mb/sec | `);
			}
		});

		console.log('resultSendingFile> ', resultSendingFile);

		const resSaveFile = await vkApi.saveFile({
			uploadFile : resultSendingFile,
			title      : 'lolRecepti'
		});

		console.log('resSaveFile', resSaveFile);
	} catch (error) {
		console.log('error! ', error);
	}
})();