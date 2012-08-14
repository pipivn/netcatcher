/* 
Devs: Lam Than <thanquoclam@gmail.com> 
Date: 12-2010 
*/

var fs = require ('fs'),
	$ = require('jquery');

Output = function(){

};

Output.prototype = {

    buffer: [],
    
    options : {
        // Number of records you want to write to output stream one time
        batch_size: 5
    },
    
    doPush : function(buffer){},
    doClose : function(){},
    doException : function(ex){},
    
    push : function(record) {
        record.timestamp = new Date();
        if (this.buffer.length < this.options.batch_size) {
            this.buffer.push(record);
        } else {
            this.flush();            
        }
    },
    
    flush : function() {
        if (!this.buffer.length) return;
        for (var i=0; i<this.buffer.length; i++) {
            this.doPush(this.buffer[i]);
        }
        this.buffer.length = 0; // reset buffer
    }    
}

exports.createConsoleOutput = function(options) 
{
    var output = new Output();
    if (options) $.extend(output.options, options);
    output.doPush = function(record) {
        console.log(JSON.stringify(record));
    }
    
    return output;
}

// params: options.filePath
exports.createFileOutput = function(options) 
{
    var output = new Output();
    $.extend(output.options, options);
    
    process.on('exit', function () {
        this.flush();
        this.doClose();
    });
    
    process.on('uncaughtException', function (ex) {
        this.doException(ex);
    });
    
    fileStream = fs.createWriteStream(options.filePath, {
        flags : 'w+',
        encoding: 'utf8',
        mode: 0777
    });
    
    output.doPush = function(record) {
        fileStream.write(JSON.stringify(record));
    }
    
    output.doClose = function(record) {
        fileStream.end();
    }
    
    output.doException = function(ex) {
        fileStream.end(timestamp() + '|' + ex.stringify());
    }
    
    return output;    
}

