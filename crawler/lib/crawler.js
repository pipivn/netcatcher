/* 
Devs: Lam Than <thanquoclam@gmail.com> 
Date: 12-2010 
*/
require('fibers')
	
var
    $ = require('./jquery_ext').jQuery,
    Http = require("http"),
    Url = require("url"),    
    Jsdom = require("jsdom").jsdom;
    
Crawler = function() {}

Crawler.prototype = {
    options : {
        // Delay time (milisecond) between each request (on one connection). 
        delayTime : 500,
        
        requestTimeout: 5000,
        
        //This object could be overwrite by user to handle the loging stuff. If not, puts to screen
		logger : {
			write: function(type, message) {
				if ((type=='inf') || (type=='err')) {
					console.log('[' + type + ']' + message);
				}
			}
		}
    },
    
    
    // Current location, use for the address that doesn't has hostname (example internal anchor)
    _location : null,
    
    run: function(callback) {
    	
    	var current = this;
    	current.options.logger.write('inf', 'START CRAWLING...');
   		
    	this._fiber = Fiber(function(){
    		if (current.scan) current.scan();
    		current.options.logger.write('inf', 'DONE!');
    		if (callback) callback();
    		
    		//process.exit(0);
		});
		
		this._fiber.run();
    },
    
    sleep : function (ms) {
		var fiber = this._fiber;
		setTimeout(function() {
			fiber.run();
		}, ms);
		yield();
	},
	
	
	_regexTripScript : new RegExp('\\bon[^=]*=[^>]*(?=>)|<\\s*(script|link)[^>]*[\\S\\s]*?<\\/\\1>|<[^>]*include[^>]*>', 'ig'),
	
    // User use this function to open web page 
    openPage : function (url) {
    	
    	var current = this;
    	this.sleep(this.options.delayTime);
    	
        this.options.logger.write('inf', 'openPage: ' + url);
        
        var fiber = this._fiber;
        var text = '';
        
        Crawler.sendRequest(url, this.options, function(result){
        	//text = $.utils.stripScript(result);
        	text = result.replace(current._regexTripScript, "");
        	fiber.run();
        });
        
        yield();
        
        
        return {
			'url' : url,
			'document' : Jsdom(text)
		}
    }
    
}

Crawler.simulateRequestOptions = function (urlInfo) {
	return {
		host: urlInfo.hostname,
  		port: 80,
  		path: urlInfo.pathname,
		method: "GET",
		headers: {
			"User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"
		}
	};
}

UrlParser = {
	_location : null,
	
	parse : function(url) {
	
		var urlInfo = Url.parse(url);
        
        if (!urlInfo.host && UrlParser._location) { // for the address does not has the host part (example an internal anchor), use current location
            urlInfo = Url.parse(Url.resolve(UrlParser._location, url));
        }
        
        if (!urlInfo.host) { // Ignore invalid request
            return; 
        } else { // If the current location is not empty, cache it
            if (!UrlParser._location) UrlParser._location = urlInfo.protocol + '//' + urlInfo.hostname;
        }
        
        return urlInfo;
	}
}
		
        
Crawler.sendRequest = function (url, options, callback) {
	
	var urlInfo = UrlParser.parse(url);
	var reqOptions = Crawler.simulateRequestOptions(urlInfo);
	
	var request = Http.request(reqOptions, function(res) {
		
		res.setEncoding("utf8");
		var text = '';
	
		res.addListener("data", function (chunk) {
			text += chunk;
		});
		
		res.addListener('end', function() {
			
			if (res.statusCode == 200) {                
				var contentType = res.headers['content-type'].split(';')[0];
				if (contentType == 'text/html' || contentType == 'text/plain' || contentType == '') {
					
					options.logger.write('dbg', 'openPage: Done');
					callback(text);
				} else {
					options.logger.write('wan','openPage: Content type-unknown ' + contentType);
					callback();
				}
			} else if (res.statusCode == 301 || res.statusCode == 303) {
				// Redirect to new location
				var redirectUrl =  Url.resolve(urlInfo.href, res.headers.location);
				options.logger.write('dbg','openPage: Redirect to : ' + redirectUrl);
				Crawler.sendRequest(redirectUrl, options, callback);
			} else if (res.statusCode == 404) {
				// 404 - Page not found
				options.logger.write('wan','openPage: 404');
				callback();             
			} else if (res.statusCode == 400) {                
				// 400 - Bad request
				options.logger.write('wan','openPage: Bad request');
				callback();
			} else {
				// Unknown http status code
				options.logger.write('err','openPage: Unknown code (' + res.statusCode + ')\nHeaders is: ' + JSON.stringify(res.headers));
				callback();
			}
		});
	});

	request.on('error', function(e) {
		options.logger.write('err', 'openPage: problem with request: ' + e.message);
		callback();
	});
	
	/*
	request.setTimeout(options.requestTimeout, function(e) {
		options.logger.write('err', 'openPage: Timeout');
		callback();
	});
	*/
	request.shouldKeepAlive = false;

	options.logger.write('dbg', 'openPage: Send request...');
	request.end();
}

// Export things
exports.createCrawler = function(options)
{
    var crawler = new Crawler();
    if (options) $.extend(crawler.options, options);
    return crawler;
}
