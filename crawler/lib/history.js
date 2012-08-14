/* 
Devs: Lam Than <thanquoclam@gmail.com> 
Date: 12-2010 
*/
var
	$ = require('./jquery_ext').jQuery,
    fs = require ('fs');

History = function(){};

History.prototype = {
    
    options : {
     	dirPath : './',
		fileName : 'history'
    },
    
    scan : 0,
    add : 0,
    update : 0,
    reject : 0,
    fail : 0,
    
    //Format:
    //{type:'start', pid:223, time:'2012-03-01 12:01:15', info:{options:{...}}}
    //{type:'stop', pid:345, time:'2012-03-01 12:01:15', info:{scan:345, new:33, update:2, reject:4, fail:3}}
    
    stop : function(status) {
    	var line = {
        	type: 'stop',
        	pid: process.pid, 
        	time: this._timestamp(),
        	info: {status:status, scan:this.scan, add:this.add, update:this.update, reject:this.reject, fail:this.fail}
        }
        this._writeLine(JSON.stringify(line));
    },
    
    start : function(options) {
        var line = {
        	type: 'start',
        	pid: process.pid,
        	time: this._timestamp(),
        	info: {options:options}
        }
        this._writeLine(JSON.stringify(line));
    },
    
    getLast : function(callback) {
    	this._lastLine(function(line){
    		if (callback) callback(line ? JSON.parse(line) : null);
    	});
    },
    
    _getFilename : function() {
    	return this.options.dirPath + this.options.fileName + '.txt';
    },
    
    _timestamp : function() {
    	return (new Date()).toLocaleString();
	},

    _writeLine : function (line) {
        var stream = fs.createWriteStream(this._getFilename(), {
			flags : 'a',
			encoding: 'utf8',
			mode: 0666 
		});
		stream.end('\n' + line);
    },
    
    _lastLine : function (callback) { 
		var input = fs.createReadStream(this._getFilename());
		var remaining = '';
		
		input.on('data', function(data) {
			remaining += data;
			var index = remaining.indexOf('\n');
			while (index > -1) {
				remaining = remaining.substring(index + 1);
				index = remaining.indexOf('\n');
			}
		});
		
		input.on('end', function() {
			if (remaining.length > 0) {
				if (callback) callback(remaining);
			} else { 
				if (callback) callback();
			}
			callback = null;
		});
		
		input.on('close', function(){
			if (callback) callback();
		});
		
		input.on('error', function(){
			if (callback) callback();
		});
    }
}

exports.createHistory = function(options) 
{
    var history = new History();
    if (options) $.extend(history.options, options);
    return history;
}