'use strict';

const request = require('request');
const Promise = require('bluebird');

const roundToPlace = (number, place) => {
	const multiply = Math.pow(10, place);
	return Math.round(number * multiply) / multiply;
}; 

const rp = (opts) => {
	return new Promise((resolve, reject) => {
		let timer;
		const r = request.post(opts, (err, response, body) => {
			if (opts.onProgress) {
				clearInterval(timer);
			}
			
			if (err) {
				return reject(err);
			}
			
			resolve(body);
		});
		
		if (opts.onProgress) {
			const interval = opts.progressInterval || 500;
			let lastSent = 0;
			let speed = 0;
			timer = setInterval(() => {
				let sendByTick = r.req.connection.bytesWritten - lastSent;
				lastSent       = r.req.connection.bytesWritten;
				
				let secInterval = interval / 1000;
				speed = roundToPlace((sendByTick / secInterval) / Math.pow(2, 20), 2);
				opts.onProgress(lastSent, speed);
			}, interval);
		}
	});
};

module.exports = {
	rp,
	roundToPlace
};