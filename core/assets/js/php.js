function isset()
{
	
	//  discuss at: http://locutus.io/php/isset/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: FremyCompany
	// improved by: Onno Marsman (https://twitter.com/onnomarsman)
	// improved by: RafaÅ Kukawski (http://blog.kukawski.pl)
	//   example 1: isset( undefined, true)
	//   returns 1: false
	//   example 2: isset( 'Kevin van Zonneveld' )
	//   returns 2: true
	
	var a = arguments
	var l = a.length
	var i = 0
	var undef
	
	if (l === 0) {
		throw new Error('Empty isset')
	}
	
	while (i !== l) {
		if (a[i] === undef || a[i] === null) {
			return false
		}
		i++
	}
	
	return true
}



function empty (mixedVar) {
	//  discuss at: http://locutus.io/php/empty/
	// original by: Philippe Baumann
	//    input by: Onno Marsman (https://twitter.com/onnomarsman)
	//    input by: LH
	//    input by: Stoyan Kyosev (http://www.svest.org/)
	// bugfixed by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Onno Marsman (https://twitter.com/onnomarsman)
	// improved by: Francesco
	// improved by: Marc Jansen
	// improved by: RafaÅ Kukawski (http://blog.kukawski.pl)
	//   example 1: empty(null)
	//   returns 1: true
	//   example 2: empty(undefined)
	//   returns 2: true
	//   example 3: empty([])
	//   returns 3: true
	//   example 4: empty({})
	//   returns 4: true
	//   example 5: empty({'aFunc' : function () { alert('humpty'); } })
	//   returns 5: false
	
	var undef
	var key
	var i
	var len
	var emptyValues = [undef, null, false, 0, '', '0']
	
	for (i = 0, len = emptyValues.length; i < len; i++) {
		if (mixedVar === emptyValues[i]) {
			return true
		}
	}
	
	if (typeof mixedVar === 'object') {
		for (key in mixedVar) {
			if (mixedVar.hasOwnProperty(key)) {
				return false
			}
		}
		return true
	}
	
	return false
}


function trim (str, charlist) {
	//  discuss at: http://locutus.io/php/trim/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: mdsjack (http://www.mdsjack.bo.it)
	// improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Steven Levithan (http://blog.stevenlevithan.com)
	// improved by: Jack
	//    input by: Erkekjetter
	//    input by: DxGx
	// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
	//   example 1: trim('    Kevin van Zonneveld    ')
	//   returns 1: 'Kevin van Zonneveld'
	//   example 2: trim('Hello World', 'Hdle')
	//   returns 2: 'o Wor'
	//   example 3: trim(16, 1)
	//   returns 3: '6'
	
	var whitespace = [
		' ',
		'\n',
		'\r',
		'\t',
		'\f',
		'\x0b',
		'\xa0',
		'\u2000',
		'\u2001',
		'\u2002',
		'\u2003',
		'\u2004',
		'\u2005',
		'\u2006',
		'\u2007',
		'\u2008',
		'\u2009',
		'\u200a',
		'\u200b',
		'\u2028',
		'\u2029',
		'\u3000'
	].join('')
	var l = 0
	var i = 0
	str += ''
	
	if (charlist) {
		whitespace = (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^:])/g, '$1')
	}
	
	l = str.length
	for (i = 0; i < l; i++) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(i)
			break
		}
	}
	
	l = str.length
	for (i = l - 1; i >= 0; i--) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(0, i + 1)
			break
		}
	}
	
	return whitespace.indexOf(str.charAt(0)) === -1 ? str : ''
}

function count (mixedVar, mode) {
	//  discuss at: http://locutus.io/php/count/
	// original by: Kevin van Zonneveld (http://kvz.io)
	//    input by: Waldo Malqui Silva (http://waldo.malqui.info)
	//    input by: merabi
	// bugfixed by: Soren Hansen
	// bugfixed by: Olivier Louvignes (http://mg-crea.com/)
	// improved by: Brett Zamir (http://brett-zamir.me)
	//   example 1: count([[0,0],[0,-4]], 'COUNT_RECURSIVE')
	//   returns 1: 6
	//   example 2: count({'one' : [1,2,3,4,5]}, 'COUNT_RECURSIVE')
	//   returns 2: 6
	
	var key
	var cnt = 0
	
	if (mixedVar === null || typeof mixedVar === 'undefined') {
		return 0
	} else if (mixedVar.constructor !== Array && mixedVar.constructor !== Object) {
		return 1
	}
	
	if (mode === 'COUNT_RECURSIVE') {
		mode = 1
	}
	if (mode !== 1) {
		mode = 0
	}
	
	for (key in mixedVar) {
		if (mixedVar.hasOwnProperty(key)) {
			cnt++
			if (mode === 1 && mixedVar[key] &&
				(mixedVar[key].constructor === Array ||
					mixedVar[key].constructor === Object)) {
				cnt += count(mixedVar[key], 1)
			}
		}
	}
	
	return cnt
}

function end (arr) {
	//  discuss at: http://locutus.io/php/end/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// bugfixed by: Legaev Andrey
	//  revised by: J A R
	//  revised by: Brett Zamir (http://brett-zamir.me)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	//      note 1: Uses global: locutus to store the array pointer
	//   example 1: end({0: 'Kevin', 1: 'van', 2: 'Zonneveld'})
	//   returns 1: 'Zonneveld'
	//   example 2: end(['Kevin', 'van', 'Zonneveld'])
	//   returns 2: 'Zonneveld'
	
	var $global = (typeof window !== 'undefined' ? window : GLOBAL)
	$global.$locutus = $global.$locutus || {}
	var $locutus = $global.$locutus
	$locutus.php = $locutus.php || {}
	$locutus.php.pointers = $locutus.php.pointers || []
	var pointers = $locutus.php.pointers
	
	var indexOf = function (value) {
		for (var i = 0, length = this.length; i < length; i++) {
			if (this[i] === value) {
				return i
			}
		}
		return -1
	}
	
	if (!pointers.indexOf) {
		pointers.indexOf = indexOf
	}
	if (pointers.indexOf(arr) === -1) {
		pointers.push(arr, 0)
	}
	var arrpos = pointers.indexOf(arr)
	if (Object.prototype.toString.call(arr) !== '[object Array]') {
		var ct = 0
		var val
		for (var k in arr) {
			ct++
			val = arr[k]
		}
		if (ct === 0) {
			// Empty
			return false
		}
		pointers[arrpos + 1] = ct - 1
		return val
	}
	if (arr.length === 0) {
		return false
	}
	pointers[arrpos + 1] = arr.length - 1
	return arr[pointers[arrpos + 1]]
}


function array_keys (input, searchValue, argStrict) { // eslint-disable-line camelcase
                                                      //  discuss at: http://locutus.io/php/array_keys/
                                                      // original by: Kevin van Zonneveld (http://kvz.io)
                                                      //    input by: Brett Zamir (http://brett-zamir.me)
                                                      //    input by: P
                                                      // bugfixed by: Kevin van Zonneveld (http://kvz.io)
                                                      // bugfixed by: Brett Zamir (http://brett-zamir.me)
                                                      // improved by: jd
                                                      // improved by: Brett Zamir (http://brett-zamir.me)
                                                      //   example 1: array_keys( {firstname: 'Kevin', surname: 'van Zonneveld'} )
                                                      //   returns 1: [ 'firstname', 'surname' ]
	
	var search = typeof searchValue !== 'undefined'
	var tmpArr = []
	var strict = !!argStrict
	var include = true
	var key = ''
	
	for (key in input) {
		if (input.hasOwnProperty(key)) {
			include = true
			if (search) {
				if (strict && input[key] !== searchValue) {
					include = false
				} else if (input[key] !== searchValue) {
					include = false
				}
			}
			
			if (include) {
				tmpArr[tmpArr.length] = key
			}
		}
	}
	
	return tmpArr
}


function in_array (needle, haystack, argStrict) { // eslint-disable-line camelcase
                                                  //  discuss at: http://locutus.io/php/in_array/
                                                  // original by: Kevin van Zonneveld (http://kvz.io)
                                                  // improved by: vlado houba
                                                  // improved by: Jonas Sciangula Street (Joni2Back)
                                                  //    input by: Billy
                                                  // bugfixed by: Brett Zamir (http://brett-zamir.me)
                                                  //   example 1: in_array('van', ['Kevin', 'van', 'Zonneveld'])
                                                  //   returns 1: true
                                                  //   example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'})
                                                  //   returns 2: false
                                                  //   example 3: in_array(1, ['1', '2', '3'])
                                                  //   example 3: in_array(1, ['1', '2', '3'], false)
                                                  //   returns 3: true
                                                  //   returns 3: true
                                                  //   example 4: in_array(1, ['1', '2', '3'], true)
                                                  //   returns 4: false
	
	var key = ''
	var strict = !!argStrict
	
	// we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] === ndl)
	// in just one for, in order to improve the performance
	// deciding wich type of comparation will do before walk array
	if (strict) {
		for (key in haystack) {
			if (haystack[key] === needle) {
				return true
			}
		}
	} else {
		for (key in haystack) {
			if (haystack[key] == needle) { // eslint-disable-line eqeqeq
				return true
			}
		}
	}
	
	return false
}




function explode (delimiter, string, limit) {
	//  discuss at: http://locutus.io/php/explode/
	// original by: Kevin van Zonneveld (http://kvz.io)
	//   example 1: explode(' ', 'Kevin van Zonneveld')
	//   returns 1: [ 'Kevin', 'van', 'Zonneveld' ]
	
	if (arguments.length < 2 ||
		typeof delimiter === 'undefined' ||
		typeof string === 'undefined') {
		return null
	}
	if (delimiter === '' ||
		delimiter === false ||
		delimiter === null) {
		return false
	}
	if (typeof delimiter === 'function' ||
		typeof delimiter === 'object' ||
		typeof string === 'function' ||
		typeof string === 'object') {
		return {
			0: ''
		}
	}
	if (delimiter === true) {
		delimiter = '1'
	}
	
	// Here we go...
	delimiter += ''
	string += ''
	
	var s = string.split(delimiter)
	
	if (typeof limit === 'undefined') return s
	
	// Support for limit
	if (limit === 0) limit = 1
	
	// Positive limit
	if (limit > 0) {
		if (limit >= s.length) {
			return s
		}
		return s
			.slice(0, limit - 1)
			.concat([s.slice(limit - 1)
				.join(delimiter)
			])
	}
	
	// Negative limit
	if (-limit >= s.length) {
		return []
	}
	
	s.splice(s.length + limit)
	return s
}

function nl2br (str, isXhtml) {
	//  discuss at: http://locutus.io/php/nl2br/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Philip Peterson
	// improved by: Onno Marsman (https://twitter.com/onnomarsman)
	// improved by: Atli ÃÃ³r
	// improved by: Brett Zamir (http://brett-zamir.me)
	// improved by: Maximusya
	// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
	// bugfixed by: Kevin van Zonneveld (http://kvz.io)
	//    input by: Brett Zamir (http://brett-zamir.me)
	//   example 1: nl2br('Kevin\nvan\nZonneveld')
	//   returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
	//   example 2: nl2br("\nOne\nTwo\n\nThree\n", false)
	//   returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
	//   example 3: nl2br("\nOne\nTwo\n\nThree\n", true)
	//   returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'
	
	// Adjust comment to avoid issue on locutus.io display
	var breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br ' + '/>' : '<br>'
	
	return (str + '')
		.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2')
}


function parse_str (str, array) { // eslint-disable-line camelcase
                                  //       discuss at: http://locutus.io/php/parse_str/
                                  //      original by: Cagri Ekin
                                  //      improved by: Michael White (http://getsprink.com)
                                  //      improved by: Jack
                                  //      improved by: Brett Zamir (http://brett-zamir.me)
                                  //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
                                  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
                                  //      bugfixed by: stag019
                                  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
                                  //      bugfixed by: MIO_KODUKI (http://mio-koduki.blogspot.com/)
                                  // reimplemented by: stag019
                                  //         input by: Dreamer
                                  //         input by: Zaide (http://zaidesthings.com/)
                                  //         input by: David Pesta (http://davidpesta.com/)
                                  //         input by: jeicquest
                                  //           note 1: When no argument is specified, will put variables in global scope.
                                  //           note 1: When a particular argument has been passed, and the
                                  //           note 1: returned value is different parse_str of PHP.
                                  //           note 1: For example, a=b=c&d====c
                                  //        example 1: var $arr = {}
                                  //        example 1: parse_str('first=foo&second=bar', $arr)
                                  //        example 1: var $result = $arr
                                  //        returns 1: { first: 'foo', second: 'bar' }
                                  //        example 2: var $arr = {}
                                  //        example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', $arr)
                                  //        example 2: var $result = $arr
                                  //        returns 2: { str_a: "Jack and Jill didn't see the well." }
                                  //        example 3: var $abc = {3:'a'}
                                  //        example 3: parse_str('a[b]["c"]=def&a[q]=t+5', $abc)
                                  //        example 3: var $result = $abc
                                  //        returns 3: {"3":"a","a":{"b":{"c":"def"},"q":"t 5"}}
	
	var strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&')
	var sal = strArr.length
	var i
	var j
	var ct
	var p
	var lastObj
	var obj
	var undef
	var chr
	var tmp
	var key
	var value
	var postLeftBracketPos
	var keys
	var keysLen
	
	var _fixStr = function (str) {
		return decodeURIComponent(str.replace(/\+/g, '%20'))
	}
	
	var $global = (typeof window !== 'undefined' ? window : GLOBAL)
	$global.$locutus = $global.$locutus || {}
	var $locutus = $global.$locutus
	$locutus.php = $locutus.php || {}
	
	if (!array) {
		array = $global
	}
	
	for (i = 0; i < sal; i++) {
		tmp = strArr[i].split('=')
		key = _fixStr(tmp[0])
		value = (tmp.length < 2) ? '' : _fixStr(tmp[1])
		
		while (key.charAt(0) === ' ') {
			key = key.slice(1)
		}
		if (key.indexOf('\x00') > -1) {
			key = key.slice(0, key.indexOf('\x00'))
		}
		if (key && key.charAt(0) !== '[') {
			keys = []
			postLeftBracketPos = 0
			for (j = 0; j < key.length; j++) {
				if (key.charAt(j) === '[' && !postLeftBracketPos) {
					postLeftBracketPos = j + 1
				} else if (key.charAt(j) === ']') {
					if (postLeftBracketPos) {
						if (!keys.length) {
							keys.push(key.slice(0, postLeftBracketPos - 1))
						}
						keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos))
						postLeftBracketPos = 0
						if (key.charAt(j + 1) !== '[') {
							break
						}
					}
				}
			}
			if (!keys.length) {
				keys = [key]
			}
			for (j = 0; j < keys[0].length; j++) {
				chr = keys[0].charAt(j)
				if (chr === ' ' || chr === '.' || chr === '[') {
					keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1)
				}
				if (chr === '[') {
					break
				}
			}
			
			obj = array
			for (j = 0, keysLen = keys.length; j < keysLen; j++) {
				key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '')
				lastObj = obj
				if ((key !== '' && key !== ' ') || j === 0) {
					if (obj[key] === undef) {
						obj[key] = {}
					}
					obj = obj[key]
				} else {
					// To insert new dimension
					ct = -1
					for (p in obj) {
						if (obj.hasOwnProperty(p)) {
							if (+p > ct && p.match(/^\d+$/g)) {
								ct = +p
							}
						}
					}
					key = ct + 1
				}
			}
			lastObj[key] = value
		}
	}
}

function strlen (string) {
	//  discuss at: http://locutus.io/php/strlen/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Sakimori
	// improved by: Kevin van Zonneveld (http://kvz.io)
	//    input by: Kirk Strobeck
	// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
	//  revised by: Brett Zamir (http://brett-zamir.me)
	//      note 1: May look like overkill, but in order to be truly faithful to handling all Unicode
	//      note 1: characters and to this function in PHP which does not count the number of bytes
	//      note 1: but counts the number of characters, something like this is really necessary.
	//   example 1: strlen('Kevin van Zonneveld')
	//   returns 1: 19
	//   example 2: ini_set('unicode.semantics', 'on')
	//   example 2: strlen('A\ud87e\udc04Z')
	//   returns 2: 3
	
	var str = string + ''
	/*
	  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('unicode.semantics') : undefined) || 'off'
	 if (iniVal === 'off') {
		return str.length
	  }
	*/
	var i = 0
	var lgth = 0
	
	var getWholeChar = function (str, i) {
		var code = str.charCodeAt(i)
		var next = ''
		var prev = ''
		if (code >= 0xD800 && code <= 0xDBFF) {
			// High surrogate (could change last hex to 0xDB7F to
			// treat high private surrogates as single characters)
			if (str.length <= (i + 1)) {
				throw new Error('High surrogate without following low surrogate')
			}
			next = str.charCodeAt(i + 1)
			if (next < 0xDC00 || next > 0xDFFF) {
				throw new Error('High surrogate without following low surrogate')
			}
			return str.charAt(i) + str.charAt(i + 1)
		} else if (code >= 0xDC00 && code <= 0xDFFF) {
			// Low surrogate
			if (i === 0) {
				throw new Error('Low surrogate without preceding high surrogate')
			}
			prev = str.charCodeAt(i - 1)
			if (prev < 0xD800 || prev > 0xDBFF) {
				// (could change last hex to 0xDB7F to treat high private surrogates
				// as single characters)
				throw new Error('Low surrogate without preceding high surrogate')
			}
			// We can pass over low surrogates now as the second
			// component in a pair which we have already processed
			return false
		}
		return str.charAt(i)
	}
	
	for (i = 0, lgth = 0; i < str.length; i++) {
		if ((getWholeChar(str, i)) === false) {
			continue
		}
		// Adapt this line at the top of any loop, passing in the whole string and
		// the current iteration and returning a variable to represent the individual character;
		// purpose is to treat the first part of a surrogate pair as the whole character and then
		// ignore the second part
		lgth++
	}
	
	return lgth
}


function strtolower (str) {
	//  discuss at: http://locutus.io/php/strtolower/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Onno Marsman (https://twitter.com/onnomarsman)
	//   example 1: strtolower('Kevin van Zonneveld')
	//   returns 1: 'kevin van zonneveld'
	
	return (str + '')
		.toLowerCase()
}

function strtoupper (str) {
	//  discuss at: http://locutus.io/php/strtoupper/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Onno Marsman (https://twitter.com/onnomarsman)
	//   example 1: strtoupper('Kevin van Zonneveld')
	//   returns 1: 'KEVIN VAN ZONNEVELD'
	
	return (str + '')
		.toUpperCase()
}

function ucfirst (str) {
	//  discuss at: http://locutus.io/php/ucfirst/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
	// improved by: Brett Zamir (http://brett-zamir.me)
	//   example 1: ucfirst('kevin van zonneveld')
	//   returns 1: 'Kevin van zonneveld'
	
	str += ''
	var f = str.charAt(0)
		.toUpperCase()
	return f + str.substr(1)
}

function ucwords (str) {
	//  discuss at: http://locutus.io/php/ucwords/
	// original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// improved by: Waldo Malqui Silva (http://waldo.malqui.info)
	// improved by: Robin
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
	//    input by: James (http://www.james-bell.co.uk/)
	//   example 1: ucwords('kevin van  zonneveld')
	//   returns 1: 'Kevin Van  Zonneveld'
	//   example 2: ucwords('HELLO WORLD')
	//   returns 2: 'HELLO WORLD'
	
	return (str + '')
		.replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
			return $1.toUpperCase()
		})
}

function number_format (number, decimals, decPoint, thousandsSep) { // eslint-disable-line camelcase
                                                                    //  discuss at: http://locutus.io/php/number_format/
                                                                    // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
                                                                    // improved by: Kevin van Zonneveld (http://kvz.io)
                                                                    // improved by: davook
                                                                    // improved by: Brett Zamir (http://brett-zamir.me)
                                                                    // improved by: Brett Zamir (http://brett-zamir.me)
                                                                    // improved by: Theriault (https://github.com/Theriault)
                                                                    // improved by: Kevin van Zonneveld (http://kvz.io)
                                                                    // bugfixed by: Michael White (http://getsprink.com)
                                                                    // bugfixed by: Benjamin Lupton
                                                                    // bugfixed by: Allan Jensen (http://www.winternet.no)
                                                                    // bugfixed by: Howard Yeend
                                                                    // bugfixed by: Diogo Resende
                                                                    // bugfixed by: Rival
                                                                    // bugfixed by: Brett Zamir (http://brett-zamir.me)
                                                                    //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
                                                                    //  revised by: Luke Smith (http://lucassmith.name)
                                                                    //    input by: Kheang Hok Chin (http://www.distantia.ca/)
                                                                    //    input by: Jay Klehr
                                                                    //    input by: Amir Habibi (http://www.residence-mixte.com/)
                                                                    //    input by: Amirouche
                                                                    //   example 1: number_format(1234.56)
                                                                    //   returns 1: '1,235'
                                                                    //   example 2: number_format(1234.56, 2, ',', ' ')
                                                                    //   returns 2: '1 234,56'
                                                                    //   example 3: number_format(1234.5678, 2, '.', '')
                                                                    //   returns 3: '1234.57'
                                                                    //   example 4: number_format(67, 2, ',', '.')
                                                                    //   returns 4: '67,00'
                                                                    //   example 5: number_format(1000)
                                                                    //   returns 5: '1,000'
                                                                    //   example 6: number_format(67.311, 2)
                                                                    //   returns 6: '67.31'
                                                                    //   example 7: number_format(1000.55, 1)
                                                                    //   returns 7: '1,000.6'
                                                                    //   example 8: number_format(67000, 5, ',', '.')
                                                                    //   returns 8: '67.000,00000'
                                                                    //   example 9: number_format(0.9, 0)
                                                                    //   returns 9: '1'
                                                                    //  example 10: number_format('1.20', 2)
                                                                    //  returns 10: '1.20'
                                                                    //  example 11: number_format('1.20', 4)
                                                                    //  returns 11: '1.2000'
                                                                    //  example 12: number_format('1.2000', 3)
                                                                    //  returns 12: '1.200'
                                                                    //  example 13: number_format('1 000,50', 2, '.', ' ')
                                                                    //  returns 13: '100 050.00'
                                                                    //  example 14: number_format(1e-8, 8, '.', '')
                                                                    //  returns 14: '0.00000001'
	
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
	var n = !isFinite(+number) ? 0 : +number
	var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
	var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
	var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
	var s = ''
	
	var toFixedFix = function (n, prec) {
		var k = Math.pow(10, prec)
		return '' + (Math.round(n * k) / k)
			.toFixed(prec)
	}
	
	// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || ''
		s[1] += new Array(prec - s[1].length + 1).join('0')
	}
	
	return s.join(dec)
}

function str_replace (search, replace, subject, countObj) { // eslint-disable-line camelcase
                                                            //  discuss at: http://locutus.io/php/str_replace/
                                                            // original by: Kevin van Zonneveld (http://kvz.io)
                                                            // improved by: Gabriel Paderni
                                                            // improved by: Philip Peterson
                                                            // improved by: Simon Willison (http://simonwillison.net)
                                                            // improved by: Kevin van Zonneveld (http://kvz.io)
                                                            // improved by: Onno Marsman (https://twitter.com/onnomarsman)
                                                            // improved by: Brett Zamir (http://brett-zamir.me)
                                                            //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
                                                            // bugfixed by: Anton Ongson
                                                            // bugfixed by: Kevin van Zonneveld (http://kvz.io)
                                                            // bugfixed by: Oleg Eremeev
                                                            // bugfixed by: Glen Arason (http://CanadianDomainRegistry.ca)
                                                            // bugfixed by: Glen Arason (http://CanadianDomainRegistry.ca)
                                                            //    input by: Onno Marsman (https://twitter.com/onnomarsman)
                                                            //    input by: Brett Zamir (http://brett-zamir.me)
                                                            //    input by: Oleg Eremeev
                                                            //      note 1: The countObj parameter (optional) if used must be passed in as a
                                                            //      note 1: object. The count will then be written by reference into it's `value` property
                                                            //   example 1: str_replace(' ', '.', 'Kevin van Zonneveld')
                                                            //   returns 1: 'Kevin.van.Zonneveld'
                                                            //   example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars')
                                                            //   returns 2: 'hemmo, mars'
                                                            //   example 3: str_replace(Array('S','F'),'x','ASDFASDF')
                                                            //   returns 3: 'AxDxAxDx'
                                                            //   example 4: var countObj = {}
                                                            //   example 4: str_replace(['A','D'], ['x','y'] , 'ASDFASDF' , countObj)
                                                            //   example 4: var $result = countObj.value
                                                            //   returns 4: 4
	
	var i = 0
	var j = 0
	var temp = ''
	var repl = ''
	var sl = 0
	var fl = 0
	var f = [].concat(search)
	var r = [].concat(replace)
	var s = subject
	var ra = Object.prototype.toString.call(r) === '[object Array]'
	var sa = Object.prototype.toString.call(s) === '[object Array]'
	s = [].concat(s)
	
	var $global = (typeof window !== 'undefined' ? window : GLOBAL)
	$global.$locutus = $global.$locutus || {}
	var $locutus = $global.$locutus
	$locutus.php = $locutus.php || {}
	
	if (typeof (search) === 'object' && typeof (replace) === 'string') {
		temp = replace
		replace = []
		for (i = 0; i < search.length; i += 1) {
			replace[i] = temp
		}
		temp = ''
		r = [].concat(replace)
		ra = Object.prototype.toString.call(r) === '[object Array]'
	}
	
	if (typeof countObj !== 'undefined') {
		countObj.value = 0
	}
	
	for (i = 0, sl = s.length; i < sl; i++) {
		if (s[i] === '') {
			continue
		}
		for (j = 0, fl = f.length; j < fl; j++) {
			temp = s[i] + ''
			repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0]
			s[i] = (temp).split(f[j]).join(repl)
			if (typeof countObj !== 'undefined') {
				countObj.value += ((temp.split(f[j])).length - 1)
			}
		}
	}
	return sa ? s : s[0]
}

function urlencode (str) {
	//       discuss at: http://locutus.io/php/urlencode/
	//      original by: Philip Peterson
	//      improved by: Kevin van Zonneveld (http://kvz.io)
	//      improved by: Kevin van Zonneveld (http://kvz.io)
	//      improved by: Brett Zamir (http://brett-zamir.me)
	//      improved by: Lars Fischer
	//         input by: AJ
	//         input by: travc
	//         input by: Brett Zamir (http://brett-zamir.me)
	//         input by: Ratheous
	//      bugfixed by: Kevin van Zonneveld (http://kvz.io)
	//      bugfixed by: Kevin van Zonneveld (http://kvz.io)
	//      bugfixed by: Joris
	// reimplemented by: Brett Zamir (http://brett-zamir.me)
	// reimplemented by: Brett Zamir (http://brett-zamir.me)
	//           note 1: This reflects PHP 5.3/6.0+ behavior
	//           note 1: Please be aware that this function
	//           note 1: expects to encode into UTF-8 encoded strings, as found on
	//           note 1: pages served as UTF-8
	//        example 1: urlencode('Kevin van Zonneveld!')
	//        returns 1: 'Kevin+van+Zonneveld%21'
	//        example 2: urlencode('http://kvz.io/')
	//        returns 2: 'http%3A%2F%2Fkvz.io%2F'
	//        example 3: urlencode('http://www.google.nl/search?q=Locutus&ie=utf-8')
	//        returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3DLocutus%26ie%3Dutf-8'
	
	str = (str + '')
	
	// Tilde should be allowed unescaped in future versions of PHP (as reflected below),
	// but if you want to reflect current
	// PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
	return encodeURIComponent(str)
		.replace(/!/g, '%21')
		.replace(/'/g, '%27')
		.replace(/\(/g, '%28')
		.replace(/\)/g, '%29')
		.replace(/\*/g, '%2A')
		.replace(/%20/g, '+')
}


function join(glue, pieces) {
	//  discuss at: http://locutus.io/php/implode/
	// original by: Kevin van Zonneveld (http://kvz.io)
	// improved by: Waldo Malqui Silva (http://waldo.malqui.info)
	// improved by: Itsacon (http://www.itsacon.net/)
	// bugfixed by: Brett Zamir (http://brett-zamir.me)
	//   example 1: implode(' ', ['Kevin', 'van', 'Zonneveld'])
	//   returns 1: 'Kevin van Zonneveld'
	//   example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'})
	//   returns 2: 'Kevin van Zonneveld'
	
	var i = ''
	var retVal = ''
	var tGlue = ''
	
	if (arguments.length === 1) {
		pieces = glue
		glue = ''
	}
	
	if (typeof pieces === 'object') {
		if (Object.prototype.toString.call(pieces) === '[object Array]') {
			return pieces.join(glue)
		}
		for (i in pieces) {
			retVal += tGlue + pieces[i]
			tGlue = glue
		}
		return retVal
	}
	
	return pieces
}