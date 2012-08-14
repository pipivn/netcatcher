var $ = require('jquery');

// Tels Utils ////////////////

$.tel = {
	parse : function(text) {
		return [];
	}
};
	
// Text Utils ////////////////

$.fn.ttext = function() {
	
  	var regexTag = /(<([^>]+)>)/ig;
  	var regexSpace = /(\s+)/ig;
  	var regexClean = /\|([\|\s]+)\|/ig;
  	
  	if ($(this).html && $(this).html())  {
		
		var result = '||' + 
			$(this)
			.html()
			.replace(regexTag, "||")
			.replace(regexSpace, " ")
		+ '||';
	
		return result.replace(regexClean, "||");
	}
	
	return "";
};


$.utils = {

	stripScript : function(text) {
		var result = '';
		var left = right = 0;
		var selected = true;
		while (1) {
			if (selected) {
				right = text.indexOf('<script', left);
				if (right < 0) {
					result += text.substring(left, text.length - left);
					break;
				}
				result += text.substr(left, right - left);
			} else {
				right = text.indexOf('</script>', left);
				if (right < 0) break;
				left = right + 9;
			}
			selected = !selected;
		}
		console.log(result);
		return result;
	},
	
	sub : function(text, leftAnchor, rightAnchor) {
				
		if (text.indexOf(leftAnchor) < 0) return '';
		var left = text.indexOf(leftAnchor) + leftAnchor.length;
		var right = text.indexOf(rightAnchor, left);
		if (right < 0) right = text.length;
		return text.substring(left, right);
		
	}
};

exports.jQuery = $;