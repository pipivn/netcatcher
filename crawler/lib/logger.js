/* 
Devs: Lam Than <thanquoclam@gmail.com> 
Date: 12-2010 
*/
var $ = require('jquery'),
	fs = require ('fs');

exports.TYPE = LOG_TYPE = {
    NONE : 0,
    ERROR : 1,
    WARNING : 2,
    INFO : 4,
    DEBUG : 8,
    ALL : 15
}

Logger = function(){};

Logger.prototype = {
    
    options : {
        // Things you want to put into log, use as a binary mask 
        thingsWillBeLogged : LOG_TYPE.ALL
    },
    
    doWrite : function(message){},
    
    write : function(type, message) {
        switch (type)
        {
            case 'err': 
                flag = LOG_TYPE.ERROR;
                message = '[ERR]' + message;
                break;
            case 'wan': 
                flag = LOG_TYPE.WARNING;
                message = '[WAN]' + message;
                break;
            case 'inf': 
                flag = LOG_TYPE.INFO;
                message = '[INF]' + message;
                break;
            case 'dbg':
                flag = LOG_TYPE.DEBUG;
                message = '[DBG]' + message;
                break;
        }
        
        message = timestamp() + '|' + message;
        
        if (flag & this.options.thingsWillBeLogged) {
            this.doWrite(message);
        }
    }
}

function timestamp() {
    return (new Date()).toLocaleString();
}

// Create a console logger
exports.createConsoleLogger = function(options) 
{
    var logger = new Logger();
	if (options) $.extend(logger.options, options);
	
    logger.doWrite = function(message) {
        console.log(message);
    }
    return logger;
}

function genFullFilename(dirPath, fileName) {
	var today = new Date();
	var curr_date = today.getDate();
	var curr_month = today.getMonth() + 1; //months are zero based
	var curr_year = today.getFullYear();
	
	return dirPath 
		+ fileName
		+ '_' + curr_year + '-' + curr_month + '-' + curr_date
		+ '.log';
}


// Create a file logger
// params: options.filePath
exports.createFileLogger = function(options) 
{
    var logger = new Logger();
    
    $.extend(logger.options, {
    	dirPath : './',
		fileName : ''
    });
    
    if (options) $.extend(logger.options, options);
    
    var logStream = fs.createWriteStream(genFullFilename(logger.options.dirPath, logger.options.fileName), {
        flags : 'a',
        encoding: 'utf8',
        mode: 0666 
    });
   
   // This is here incase any errors occur
	logStream.on('error', function (err) {
		console.log(err);
	});
    
    process.on('uncaughtException', function (ex) {
    	debugger;
        logStream.end(timestamp() + '|' + ex.stringify() + '\n');
    });
    
    process.on('exit', function () {
        logStream.end(timestamp() + '|STOP LOGGING.\n');
    });
  
    logStream.write(timestamp() + '|START LOGGING...\n');
  	
    logger.doWrite = function(message) {
        logStream.write(message + '\n');
    }
    
    return logger;    
}