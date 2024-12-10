/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * Setup (required for Joomla! 3)
 */
if (typeof(akeeba) == 'undefined')
{
	var akeeba = {};
}

if (typeof(akeeba.UserInterfaceCommon) == 'undefined')
{
	akeeba.UserInterfaceCommon = {};
}

/*!
 Math.uuid.js (v1.4)
 http://www.broofa.com
 mailto:robert@broofa.com

 Copyright (c) 2009 Robert Kieffer
 Dual licensed under the MIT and GPL licenses.

 Usage: Math.uuid()
 */
Math.uuid = (function ()
{
	// Private array of chars to use
	var CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');

	return function (len, radix)
	{
		var chars = CHARS, uuid = [];
		radix     = radix || chars.length;

		if (len)
		{
			// Compact form
			for (var i = 0; i < len; i++)
			{
				uuid[i] = chars[0 | Math.random() * radix];
			}
		}
		else
		{
			// rfc4122, version 4 form
			var r;

			// rfc4122 requires these characters
			uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
			uuid[14] = '4';

			// Fill in random data.  At i==19 set the high bits of clock sequence as
			// per rfc4122, sec. 4.1.5
			for (var i = 0; i < 36; i++)
			{
				if (!uuid[i])
				{
					r       = 0 | Math.random() * 16;
					uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
				}
			}
		}

		return uuid.join('');
	};
})();

/*
 * Courtesy of PHPjs -- http://phpjs.org
 * @license GPL, version 2
 */
function basename(path, suffix)
{
	var b = path.replace(/^.*[\/\\]/g, '');
	if (typeof(suffix) == 'string' && b.substr(b.length - suffix.length) == suffix)
	{
		b = b.substr(0, b.length - suffix.length);
	}
	return b;
}

/**
 * Checks if a variable is empty. From the php.js library.
 */
function empty(mixed_var)
{
	var key;

	if (mixed_var === "" ||
		mixed_var === 0 ||
		mixed_var === "0" ||
		mixed_var === null ||
		mixed_var === false ||
		typeof mixed_var === 'undefined'
	)
	{
		return true;
	}

	if (typeof mixed_var == 'object')
	{
		for (key in mixed_var)
		{
			return false;
		}
		return true;
	}

	return false;
}

function array_shift(inputArr)
{
	// http://kevin.vanzonneveld.net
	// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   improved by: Martijn Wieringa
	// %        note 1: Currently does not handle objects
	// *     example 1: array_shift(['Kevin', 'van', 'Zonneveld']);
	// *     returns 1: 'Kevin'

	var props                                                  = false,
		shift = undefined, pr = '', allDigits = /^\d$/, int_ct = -1,
		_checkToUpIndices                                      = function (arr, ct, key)
		{
			// Deal with situation, e.g., if encounter index 4 and try to set it to 0, but 0 exists later in loop (need to
			// increment all subsequent (skipping current key, since we need its value below) until find unused)
			if (arr[ct] !== undefined)
			{
				var tmp = ct;
				ct += 1;
				if (ct === key)
				{
					ct += 1;
				}
				ct      = _checkToUpIndices(arr, ct, key);
				arr[ct] = arr[tmp];
				delete arr[tmp];
			}
			return ct;
		};


	if (inputArr.length === 0)
	{
		return null;
	}
	if (inputArr.length > 0)
	{
		return inputArr.shift();
	}
}

function trim(str, charlist)
{
	var whitespace, l = 0, i = 0;
	str += '';

	if (!charlist)
	{
		// default list
		whitespace =
			" \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
	}
	else
	{
		// preg_quote custom list
		charlist += '';
		whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
	}

	l = str.length;
	for (i = 0; i < l; i++)
	{
		if (whitespace.indexOf(str.charAt(i)) === -1)
		{
			str = str.substring(i);
			break;
		}
	}

	l = str.length;
	for (i = l - 1; i >= 0; i--)
	{
		if (whitespace.indexOf(str.charAt(i)) === -1)
		{
			str = str.substring(0, i + 1);
			break;
		}
	}

	return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}

//=============================================================================
// Object.keys polyfill
//=============================================================================

// From https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/keys
if (!Object.keys) {
	Object.keys = (function() {
		'use strict';
		var hasOwnProperty = Object.prototype.hasOwnProperty,
			hasDontEnumBug = !({ toString: null }).propertyIsEnumerable('toString'),
			dontEnums = [
				'toString',
				'toLocaleString',
				'valueOf',
				'hasOwnProperty',
				'isPrototypeOf',
				'propertyIsEnumerable',
				'constructor'
			],
			dontEnumsLength = dontEnums.length;

		return function(obj) {
			if (typeof obj !== 'object' && (typeof obj !== 'function' || obj === null)) {
				throw new TypeError('Object.keys called on non-object');
			}

			var result = [], prop, i;

			for (prop in obj) {
				if (hasOwnProperty.call(obj, prop)) {
					result.push(prop);
				}
			}

			if (hasDontEnumBug) {
				for (i = 0; i < dontEnumsLength; i++) {
					if (hasOwnProperty.call(obj, dontEnums[i])) {
						result.push(dontEnums[i]);
					}
				}
			}
			return result;
		};
	}());
}

//=============================================================================
// Akeeba Backup Pro - Regular expression based files and folders filters
//=============================================================================

function escapeHTML(rawData)
{
	return rawData.split("&").join("&amp;").split("<").join("&lt;").split(">").join("&gt;");
}