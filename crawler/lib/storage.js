/* 
Devs: Lam Than <thanquoclam@gmail.com> 
Date: 02-2012 
*/
var $ = require('jquery');

Storage = function(){

}

Storage.prototype = {
    
    options : {
    	//This object could be overwrite by user to handle the loging stuff. If not, puts to screen
		logger : {
			write: function(type, message) {
				if ((type=='inf') || (type=='err')) {
					console.log('[' + type + ']' + message);
				}
			}
		}
    },
    
    exist : function(callback) {
    	if (callback) callback(false);
    },
    
    save : function(post, callback) {
    	console.log('Save: ' + post.title);
    	if (callback) callback();
    },
    
    load : function(hash, callback) {
    	 console.log('Load: ' + hash);
    	 if (callback) callback();
    }
    
}

exports.createFileStorage = function(options) 
{
    var storage = new Storage();
    if (options) $.extend(storage.options, options);
    
    
    return storage;
}


exports.createMongoStorage = function(options) 
{
    var storage = new Storage();
    
    //default options
    options = $.extend({
    	domain : '127.0.0.1',
    	port : 27017,
    	dbName : 'sandbox',
    	collectionName : 'junk',
    	dbOptions : {},
    	serverOptions : {}
    }, options);
    
    if (options) $.extend(storage.options, options);
	
	var mongodb = require('mongodb');
	
	var mongoserver = new mongodb.Server(storage.options.domain, storage.options.port, storage.options.serverOptions);
	var connector = new mongodb.Db(storage.options.dbName, mongoserver, storage.options.dbOptions);
	
	storage.db = null;
	storage.collection = null;
	
	var retry = 0;
	
	storage.connect = function(callback) {
		
		retry ++;
		if (retry >= 5) {
			storage.options.logger.write('err', 'Can not connect to mongodb server');
		}
		
		connector.open(function(err, db){
		
			if (err) {
				storage.options.logger.write('err', 'Error while connect to mongodb server: ' + err);
				return;
			}
			
			db.collection(storage.options.collectionName, function(err, collection) {
				if (err) {
					storage.options.logger.write('err', 'Error while access to mongodb collection: ' + err);
					return;
				}
				storage.db = db;
				storage.collection = collection;
				retry = 0;
				callback();
			});
		});
	}
	
	storage.save = function(post, callback) {
    	if (storage.collection) {
    		storage.collection.save(post,{safe:true}, function(err) {
    			if (err) {
					storage.options.logger.write('err', 'Error while save to mongodb server: ' + err);
					return;
				}
				if (callback) callback();
    		});
    	} else {
    		storage.connect(function(){
    			storage.save(post, callback);
    		});
    	}
    }

	storage.load = function(hash, callback) {
    	if (storage.collection) {
    		storage.collection.findOne({hash : hash}, function(err, document) {	
    			if (callback) callback(err, document);	
    		});
		} else {
    		storage.connect(function(){
    			storage.load(hash, callback);
    		});
    	}
    }

	storage.exist = function(hash, callback) {
		storage.load(hash, function(err, document){
			if (callback) callback(document != null);
		});
    }
    
    storage.finalize = function() {
    	if (storage.db) storage.db.close();
    }
    return storage;
}