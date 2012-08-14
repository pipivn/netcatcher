require('fibers')

var $ = require('jquery'),
    Log = require('./logger'),
    Crawler = require('./crawler'),
    Storage = require('./storage'),
	History = require('./history'),
	Crypto = require('crypto'),
	
Stream = function () {};

Stream.prototype = {

	options : {
		//NONE : 0, ERROR : 1, WARNING : 2, INFO : 4, DEBUG : 8, ALL : 15
		thingsWillBeLogged : Log.TYPE.ALL,
	
		//request timeout (miliseconds)
		requestTimeout : 10000,
		
		//waiting time between two request (miliseconds)
		requestDelay : 1000, 
		
		//waiting time between two re-update (minute)
		updateCycle : 60 * 5,
		
		//'file' | 'mongo'
		storageType : 'mongo',
		
		storagePath : __dirname + '/../../data/',
		
		//'file' | 'console'
		logType : 'file',

		logPath : __dirname + '/../log/',
		
		//name of stream
		name : 'no_name'
	},
    
    _storage : null,
    _success : false,
    
	update : function() {
	
		this._history = History.createHistory({
			dirPath : this.options.historyPath,
			fileName : this.options.name
		});
		
		switch (this.options.logType) {
			case 'file':
				logger = Log.createFileLogger({
					thingsWillBeLogged : this.options.thingsWillBeLogged,
					dirPath : this.options.logPath,
					fileName : this.options.name
				});
				break;
			case 'console':
				logger = Log.createConsoleLogger({
					thingsWillBeLogged : this.options.thingsWillBeLogged
				});
				break;
		}
		
		switch (this.options.storageType) {
			case 'file':
				this._storage = Storage.createFileStorage({
					dirPath : this.options.storagePath,
					fileName : this.options.name,
					logger : logger
				});
				break;
			case 'mongo':
				this._storage = Storage.createMongoStorage({
					domain : '127.0.0.1',
					port : 27017,
					dbName : 'crawldata',
					collectionName : this.options.name,
					dbOptions : {},
					serverOptions : {},
					logger : logger
				});
				break;
		}
		
		crawler = Crawler.createCrawler({
			requestTimeout : this.options.requestTimeout,
			delayTime : this.options.requestDelay,
			logger : logger
		});
		
		crawler.scan = this.scan;
		var current = this;
		
		process.on('exit', function (ex) {
			if (current) current._history.stop(current._success ? 'success' : 'cancel');
		});
    
    	this._history.getLast(function(lastAction){
			if (!lastAction || current.utils.getTimespanFrom(lastAction.time) > current.options.updateCycle * 60 * 1000) { 
				
				current._history.start(current.options);
				logger.write('inf', 'Options: ' + JSON.stringify(current.options));
				
				//RUN, FORREST, RUN
				crawler.run(function() {
					if (current._storage.finalize) current._storage.finalize();
					current._success = true;
					process.exit();
				});
				
			} else {
				logger.write('err', 'Just run, need to wait. ' 
					+ JSON.stringify(current.utils.getTimespanFrom(lastAction.time) / 60 / 1000) + '/' 
					+ JSON.stringify(current.options.updateCycle)
				);
			}
		});
		
	},
	
	shouldUpdate : function (hash) {
		//return false;
		return !this.exist(hash);
	},
	
	exist : function (hash) {
		var fiber = Fiber.current;
		var result = null;	
		this._storage.exist(hash, function(exist){
			result = exist;
			fiber.run();
		});
		yield();
		return result;
	},
	
	load : function (hash) {
		var fiber = Fiber.current;
		var result = null;	
		
		return this._storage.load(hash, function(doc){
			result = doc;
			fiber.run();
		});
		
		yield();
		return result;
	},
	
	save : function (post, callback) {
		var fiber = Fiber.current;
		this._storage.save(post, function(){
			fiber.run();
		});
		yield();
	},
	
	utils : {
		getTimespanFrom : function (since) {
			var since = new Date(since);
			return (new Date()).getTime() - since.getTime();
		},
		
		hash : function(text) {
			return Crypto.createHash('md5').update(text).digest('base64');
		}
	}
}

// Export things
exports.createStream = function(options)
{
    var stream = new Stream();
    if (options) $.extend(stream.options, options);
    return stream;
}

