<?php
/**
 * erase a string
 * @param string $pattern
 * @param string $string
 * @param bool  $case_insentitive
 *
 * @return string
 */
function str_erase(string|array $pattern, string $string, bool $case_insentitive=true):string
{
	$str = ($case_insentitive) ? str_ireplace($pattern, '', $string) : str_replace($pattern, '', $string);
	$str = trim($str);
	return $str;
}

/**
 * replace latin accent like éèêë by e for example
 *
 * @param string $str
 * @return string
 */
function str_replace_latin_accents(string $str):string
{
	$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
								'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
								'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
								'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
								'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
	
	$str = strtr($str, $unwanted_array);
	return $str;
}

/**
 * cut a string and concat (Warning UTF-8 uses mb_* function and strip_tags is applied before)
 *
 * @param string $str
 * @param int $max_caracters (default 80)
 * @param string $concat_str
 *
 * return string cutted string
 */
function str_cut(string $str, int $max_caracters=80, string $concat_str='...'):string
{
	$str = strip_tags($str);
	$str2 = mb_strcut($str, 0, $max_caracters, 'UTF-8');
	$str2 = trim($str2);
	if(mb_strlen($str) > $max_caracters)
		$str2 .= $concat_str;
	
	return $str2;
}

/**
 * Slugify a string
 *
 * @param string $text
 * @param string$empty_str replace if not found
 *
 * @return mixed|string
 */
function slugify(string $text, string $empty_str=''):string
{
	// replace non letter or digits by -
	$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
	$text = trim($text, '-');
	$text = str_replace_latin_accents($text);
	// $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	$text = strtolower($text);
	
	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);
	
	if(empty($text))$text = $empty_str;
	
	
	return $text;
}

/**
 * Extract a string
 *
 * @param string    $str_start
 * @param string    $str_end
 * @param string    $str
 * @param bool $inc_pattern
 *
 * @return string
 */
function str_extract(string $str_start, string $str_end, string $str, bool $inc_pattern=false):string
{
	$pos_start = strpos($str, $str_start);
	
	if($inc_pattern)
		$pos_end = strpos($str, $str_end, $pos_start);
	else
		$pos_end = strpos($str, $str_end, ($pos_start + strlen($str_start)));
	
	if(($pos_start !== false) && ($pos_end !== false))
	{
		if($inc_pattern)
		{
			$pos1 = $pos_start;
			$pos2 = ($pos_end + strlen($str_end)) - $pos1;
			
		}
		else
		{
			$pos1 = $pos_start + strlen($str_start);
			$pos2 = $pos_end - $pos1;
		}
		
		return substr($str, $pos1, $pos2);
	}
	
	return '';
}

/** generate a token string */
function generateToken(int $length=16):string
{
	$token = openssl_random_pseudo_bytes($length);
	$token = bin2hex($token);
	return $token;
}

/**
 * Generate a alphanumeric password
 *
 * @param int $length
 * @param string $add_specials add custom special characters
 * @return string
 */
function password_generate(int $length=6, string $add_specials=''):string
{
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	if(!empty($add_specials))$alphabet .= $add_specials;
	$pass = []; //remember to declare $pass as an array
	$alphabet_length = strlen($alphabet) - 1; //put the length -1 in cache
	
	for($i=0; $i < $length; $i++)
	{
		$n = rand(0, $alphabet_length);
		$pass[] = $alphabet[$n];
	}
	
	return join('', $pass);
	
}

/**
 * Generate image filesize readable
 * @param int $bytes
 * @param int $decimals
 *
 * @return string
 */
function human_filesize(int $bytes, int $decimals=2, string $spacer=' ', string $suffix='o'):string
{
	$sz = 'BKMGTP';
	$factor = (int)@floor((strlen($bytes) - 1) / 3);
	$str = @sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) .$spacer. @$sz[$factor]. $suffix;


	return $str;
}


/**
 * convert string strtolower and ucfirst
 *
 * @param string $str
 *
 * @return string
 */
function ucfirst_lower(string $str):string
{
	$str = mb_strtolower($str, 'utf-8');
	$str = ucfirst($str);
	return $str;
}

/**
 * format a float to be well displayed ex: 1234.5678 => 1 234.57
 *
 * @param float $num
 * @return float
 */
function number_formatX(float $num, int $decimal=2, string $dot_separator='.', string $thousand_separator=' ', bool $remove_decimal_zero=false):string
{
	$num = number_format($num, $decimal, $dot_separator, $thousand_separator);
	
	if($remove_decimal_zero)
	{
		$nums = explode($dot_separator, $num, 2);
		$dec = end($nums);
		if($dec == 0)$num = $nums[0];
	}
	
	return $num;
}

/**
 * format a float to be well displayed ex: 1234 => 1 234
 *
 * @param float $int
 *
 * @return float
 */
function int_formatX(int $int, string $thousand_separator=' '):string
{
	$int = number_format($int, 0, '.', $thousand_separator);
	return $int;
}

/**
 * check if string is email
 *
 * @param $email
 * @return int
 */
function isEmail(string$email):bool
{
	$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$/';
	return preg_match($regex, $email);
}

/**
 * convert relative url to absolute
 * @param $rel
 * @param $base
 *
 * @return mixed|string
 */
function url_absolute(string $rel, string $base):string
{
	
	// parse base URL  and convert to local variables: $scheme, $host,  $path
	extract(parse_url($base));
	
	if ( strpos( $rel,"//" ) === 0 ) {
		return $scheme . ':' . $rel;
	}
	
	// return if already absolute URL
	if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) {
		return $rel;
	}
	
	// queries and anchors
	if ( $rel[0] == '#' || $rel[0] == '?' ) {
		return $base . $rel;
	}
	
	// remove non-directory element from path
	$path = preg_replace( '#/[^/]*$#', '', $path );
	
	// destroy path if relative url points to root
	if ( $rel[0] ==  '/' ) {
		$path = '';
	}
	
	// dirty absolute URL
	$abs = $host . $path . "/" . $rel;
	
	// replace '//' or  '/./' or '/foo/../' with '/'
	$abs = preg_replace( "/(\/\.?\/)/", "/", $abs );
	$abs = preg_replace( "/\/(?!\.\.)[^\/]+\/\.\.\//", "/", $abs );
	
	// absolute URL is ready!
	return $scheme . '://' . $abs;
}


/**
 * @param string $expression strtotime expression
 * @param bool   $with_time add time Y-m-d H:i:s
 *
 * @return string sql date
 */
function now(string $expression='', bool $with_time=true):string
{
	$format = ($with_time) ? "Y-m-d H:i:s" : "Y-m-d";
	
	if(empty($expression))
		$datetime = date($format);
	else
		$datetime = date($format, strtotime($expression));
	
	return $datetime;
}

/**
 * get date system with default timezone
 * @param bool $with_time
 *
 * @return string
 */
function system_date(bool $with_time=true):string
{
	$format = ($with_time) ? "Y-m-d H:i:s" : "Y-m-d";
	
	$timezone = \Core\Config::get('timezone');
	// x($timezone);
	
	$datetime = date($format);
	
	
	return $datetime;
}



/**
 * convert sql date to desired format
 * @param string $sql_date
 * @param string $format
 *
 * @return string
 */
function db2date(string $sql_date, string $format="", string $language="en"):string
{
	if(empty($format))$format = \Core\Config::get('format/date');
	$timer = strtotime($sql_date);
	$date = date($format, $timer);

	if($language == 'en')
		return $date;

	$months = [];
	$months['en'] = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
	$months['fr'] = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

	if(isset($months[$language]))
	{
		if(str_contains($format, 'F'))
		{
			$date = str_replace($months['en'], $months[$language], $date);
		}


	}
	
	return $date;
}

/**
 * convert date to sql date
 * @param string $date
 * @param string $input_format (if not defined config with be used)
 *
 * @return string
 */
function date2db(string $date, string $input_format=""):string
{
	if(empty($input_format))$input_format = \Core\Config::get('format/date');
	
	$sql_date = date_create_from_format($input_format, $date);
	if($sql_date)$sql_date = date_format($sql_date, 'Y-m-d');
	
	return $sql_date;
}

/**
 * check if date has expired
 *
 * @param string $sql_date1
 * @param string $sql_date2 (now() by default)
 *
 * @return bool
 */
function dateHasExpired(string $sql_date1, string $sql_date2=''):bool
{
	if(empty($sql_date2))$sql_date2 = now();
	
	$date1 = new DateTime($sql_date1);
	$date2 = new DateTime($sql_date2);
	
	return ($date1 < $date2);
}

/**
 * get file extension
 * @param string $file
 *
 * @return string lower
 */
function file_get_extension(string $file, bool $lowercase=true):string
{
	$ext = trim(basename($file));
	$ext = explode('.', $file);
	$ext = end($ext);
	if($lowercase)$ext = strtolower($ext);
	
	return $ext;
}

/**
 * get abs path from path disk
 *
 * @param string $str
 * @return string
 */
function get_absolute_path(string $str):string
{
	$str = str_erase(APP_PATH, $str);
	$str = str_replace('//', '/', $str);
	return $str;
}

/**
 * add suffix to filename
 * @param string $suffix
 * @param string $filename
 *
 * @return string
 */
function filename_add_suffix(string $suffix, string $filename):string
{
	$filename2 = explode('/', $filename);
	$ext = file_get_extension(basename($filename), false);
	$file = basename($filename);
	
	$filename2[count($filename2)-1] = str_replace(".{$ext}", "{$suffix}.{$ext}", $filename2[count($filename2)-1]);
	
	$filename2 = join('/', $filename2);
	
	return $filename2;
}


function getCountryFromCountryCode(string $code):string
{
	$code = strtoupper($code);
	if ($code == 'AF') return 'Afghanistan';
	if ($code == 'AX') return 'Aland Islands';
	if ($code == 'AL') return 'Albania';
	if ($code == 'DZ') return 'Algeria';
	if ($code == 'AS') return 'American Samoa';
	if ($code == 'AD') return 'Andorra';
	if ($code == 'AO') return 'Angola';
	if ($code == 'AI') return 'Anguilla';
	if ($code == 'AQ') return 'Antarctica';
	if ($code == 'AG') return 'Antigua and Barbuda';
	if ($code == 'AR') return 'Argentina';
	if ($code == 'AM') return 'Armenia';
	if ($code == 'AW') return 'Aruba';
	if ($code == 'AU') return 'Australia';
	if ($code == 'AT') return 'Austria';
	if ($code == 'AZ') return 'Azerbaijan';
	if ($code == 'BS') return 'Bahamas the';
	if ($code == 'BH') return 'Bahrain';
	if ($code == 'BD') return 'Bangladesh';
	if ($code == 'BB') return 'Barbados';
	if ($code == 'BY') return 'Belarus';
	if ($code == 'BE') return 'Belgium';
	if ($code == 'BZ') return 'Belize';
	if ($code == 'BJ') return 'Benin';
	if ($code == 'BM') return 'Bermuda';
	if ($code == 'BT') return 'Bhutan';
	if ($code == 'BO') return 'Bolivia';
	if ($code == 'BA') return 'Bosnia and Herzegovina';
	if ($code == 'BW') return 'Botswana';
	if ($code == 'BV') return 'Bouvet Island (Bouvetoya)';
	if ($code == 'BR') return 'Brazil';
	if ($code == 'IO') return 'British Indian Ocean Territory (Chagos Archipelago)';
	if ($code == 'VG') return 'British Virgin Islands';
	if ($code == 'BN') return 'Brunei Darussalam';
	if ($code == 'BG') return 'Bulgaria';
	if ($code == 'BF') return 'Burkina Faso';
	if ($code == 'BI') return 'Burundi';
	if ($code == 'KH') return 'Cambodia';
	if ($code == 'CM') return 'Cameroon';
	if ($code == 'CA') return 'Canada';
	if ($code == 'CV') return 'Cape Verde';
	if ($code == 'KY') return 'Cayman Islands';
	if ($code == 'CF') return 'Central African Republic';
	if ($code == 'TD') return 'Chad';
	if ($code == 'CL') return 'Chile';
	if ($code == 'CN') return 'China';
	if ($code == 'CX') return 'Christmas Island';
	if ($code == 'CC') return 'Cocos (Keeling) Islands';
	if ($code == 'CO') return 'Colombia';
	if ($code == 'KM') return 'Comoros the';
	if ($code == 'CD') return 'Congo';
	if ($code == 'CG') return 'Congo the';
	if ($code == 'CK') return 'Cook Islands';
	if ($code == 'CR') return 'Costa Rica';
	if ($code == 'CI') return 'Cote d\'Ivoire';
	if ($code == 'HR') return 'Croatia';
	if ($code == 'CU') return 'Cuba';
	if ($code == 'CY') return 'Cyprus';
	if ($code == 'CZ') return 'Czech Republic';
	if ($code == 'DK') return 'Denmark';
	if ($code == 'DJ') return 'Djibouti';
	if ($code == 'DM') return 'Dominica';
	if ($code == 'DO') return 'Dominican Republic';
	if ($code == 'EC') return 'Ecuador';
	if ($code == 'EG') return 'Egypt';
	if ($code == 'SV') return 'El Salvador';
	if ($code == 'GQ') return 'Equatorial Guinea';
	if ($code == 'ER') return 'Eritrea';
	if ($code == 'EE') return 'Estonia';
	if ($code == 'ET') return 'Ethiopia';
	if ($code == 'FO') return 'Faroe Islands';
	if ($code == 'FK') return 'Falkland Islands (Malvinas)';
	if ($code == 'FJ') return 'Fiji Islands';
	if ($code == 'FI') return 'Finland';
	if ($code == 'FR') return 'France';
	if ($code == 'GF') return 'French Guiana';
	if ($code == 'PF') return 'French Polynesia';
	if ($code == 'TF') return 'French Southern Territories';
	if ($code == 'GA') return 'Gabon';
	if ($code == 'GM') return 'Gambia the';
	if ($code == 'GE') return 'Georgia';
	if ($code == 'DE') return 'Germany';
	if ($code == 'GH') return 'Ghana';
	if ($code == 'GI') return 'Gibraltar';
	if ($code == 'GR') return 'Greece';
	if ($code == 'GL') return 'Greenland';
	if ($code == 'GD') return 'Grenada';
	if ($code == 'GP') return 'Guadeloupe';
	if ($code == 'GU') return 'Guam';
	if ($code == 'GT') return 'Guatemala';
	if ($code == 'GG') return 'Guernsey';
	if ($code == 'GN') return 'Guinea';
	if ($code == 'GW') return 'Guinea-Bissau';
	if ($code == 'GY') return 'Guyana';
	if ($code == 'HT') return 'Haiti';
	if ($code == 'HM') return 'Heard Island and McDonald Islands';
	if ($code == 'VA') return 'Holy See (Vatican City State)';
	if ($code == 'HN') return 'Honduras';
	if ($code == 'HK') return 'Hong Kong';
	if ($code == 'HU') return 'Hungary';
	if ($code == 'IS') return 'Iceland';
	if ($code == 'IN') return 'India';
	if ($code == 'ID') return 'Indonesia';
	if ($code == 'IR') return 'Iran';
	if ($code == 'IQ') return 'Iraq';
	if ($code == 'IE') return 'Ireland';
	if ($code == 'IM') return 'Isle of Man';
	if ($code == 'IL') return 'Israel';
	if ($code == 'IT') return 'Italy';
	if ($code == 'JM') return 'Jamaica';
	if ($code == 'JP') return 'Japan';
	if ($code == 'JE') return 'Jersey';
	if ($code == 'JO') return 'Jordan';
	if ($code == 'KZ') return 'Kazakhstan';
	if ($code == 'KE') return 'Kenya';
	if ($code == 'KI') return 'Kiribati';
	if ($code == 'KP') return 'Korea';
	if ($code == 'KR') return 'Korea';
	if ($code == 'KW') return 'Kuwait';
	if ($code == 'KG') return 'Kyrgyz Republic';
	if ($code == 'LA') return 'Lao';
	if ($code == 'LV') return 'Latvia';
	if ($code == 'LB') return 'Lebanon';
	if ($code == 'LS') return 'Lesotho';
	if ($code == 'LR') return 'Liberia';
	if ($code == 'LY') return 'Libyan Arab Jamahiriya';
	if ($code == 'LI') return 'Liechtenstein';
	if ($code == 'LT') return 'Lithuania';
	if ($code == 'LU') return 'Luxembourg';
	if ($code == 'MO') return 'Macao';
	if ($code == 'MK') return 'Macedonia';
	if ($code == 'MG') return 'Madagascar';
	if ($code == 'MW') return 'Malawi';
	if ($code == 'MY') return 'Malaysia';
	if ($code == 'MV') return 'Maldives';
	if ($code == 'ML') return 'Mali';
	if ($code == 'MT') return 'Malta';
	if ($code == 'MH') return 'Marshall Islands';
	if ($code == 'MQ') return 'Martinique';
	if ($code == 'MR') return 'Mauritania';
	if ($code == 'MU') return 'Mauritius';
	if ($code == 'YT') return 'Mayotte';
	if ($code == 'MX') return 'Mexico';
	if ($code == 'FM') return 'Micronesia';
	if ($code == 'MD') return 'Moldova';
	if ($code == 'MC') return 'Monaco';
	if ($code == 'MN') return 'Mongolia';
	if ($code == 'ME') return 'Montenegro';
	if ($code == 'MS') return 'Montserrat';
	if ($code == 'MA') return 'Morocco';
	if ($code == 'MZ') return 'Mozambique';
	if ($code == 'MM') return 'Myanmar';
	if ($code == 'NA') return 'Namibia';
	if ($code == 'NR') return 'Nauru';
	if ($code == 'NP') return 'Nepal';
	if ($code == 'AN') return 'Netherlands Antilles';
	if ($code == 'NL') return 'Netherlands the';
	if ($code == 'NC') return 'New Caledonia';
	if ($code == 'NZ') return 'New Zealand';
	if ($code == 'NI') return 'Nicaragua';
	if ($code == 'NE') return 'Niger';
	if ($code == 'NG') return 'Nigeria';
	if ($code == 'NU') return 'Niue';
	if ($code == 'NF') return 'Norfolk Island';
	if ($code == 'MP') return 'Northern Mariana Islands';
	if ($code == 'NO') return 'Norway';
	if ($code == 'OM') return 'Oman';
	if ($code == 'PK') return 'Pakistan';
	if ($code == 'PW') return 'Palau';
	if ($code == 'PS') return 'Palestinian Territory';
	if ($code == 'PA') return 'Panama';
	if ($code == 'PG') return 'Papua New Guinea';
	if ($code == 'PY') return 'Paraguay';
	if ($code == 'PE') return 'Peru';
	if ($code == 'PH') return 'Philippines';
	if ($code == 'PN') return 'Pitcairn Islands';
	if ($code == 'PL') return 'Poland';
	if ($code == 'PT') return 'Portugal, Portuguese Republic';
	if ($code == 'PR') return 'Puerto Rico';
	if ($code == 'QA') return 'Qatar';
	if ($code == 'RE') return 'Reunion';
	if ($code == 'RO') return 'Romania';
	if ($code == 'RU') return 'Russian Federation';
	if ($code == 'RW') return 'Rwanda';
	if ($code == 'BL') return 'Saint Barthelemy';
	if ($code == 'SH') return 'Saint Helena';
	if ($code == 'KN') return 'Saint Kitts and Nevis';
	if ($code == 'LC') return 'Saint Lucia';
	if ($code == 'MF') return 'Saint Martin';
	if ($code == 'PM') return 'Saint Pierre and Miquelon';
	if ($code == 'VC') return 'Saint Vincent and the Grenadines';
	if ($code == 'WS') return 'Samoa';
	if ($code == 'SM') return 'San Marino';
	if ($code == 'ST') return 'Sao Tome and Principe';
	if ($code == 'SA') return 'Saudi Arabia';
	if ($code == 'SN') return 'Senegal';
	if ($code == 'RS') return 'Serbia';
	if ($code == 'SC') return 'Seychelles';
	if ($code == 'SL') return 'Sierra Leone';
	if ($code == 'SG') return 'Singapore';
	if ($code == 'SK') return 'Slovakia (Slovak Republic)';
	if ($code == 'SI') return 'Slovenia';
	if ($code == 'SB') return 'Solomon Islands';
	if ($code == 'SO') return 'Somalia, Somali Republic';
	if ($code == 'ZA') return 'South Africa';
	if ($code == 'GS') return 'South Georgia and the South Sandwich Islands';
	if ($code == 'ES') return 'Spain';
	if ($code == 'LK') return 'Sri Lanka';
	if ($code == 'SD') return 'Sudan';
	if ($code == 'SR') return 'Suriname';
	if ($code == 'SJ') return 'Svalbard & Jan Mayen Islands';
	if ($code == 'SZ') return 'Swaziland';
	if ($code == 'SE') return 'Sweden';
	if ($code == 'CH') return 'Switzerland, Swiss Confederation';
	if ($code == 'SY') return 'Syrian Arab Republic';
	if ($code == 'TW') return 'Taiwan';
	if ($code == 'TJ') return 'Tajikistan';
	if ($code == 'TZ') return 'Tanzania';
	if ($code == 'TH') return 'Thailand';
	if ($code == 'TL') return 'Timor-Leste';
	if ($code == 'TG') return 'Togo';
	if ($code == 'TK') return 'Tokelau';
	if ($code == 'TO') return 'Tonga';
	if ($code == 'TT') return 'Trinidad and Tobago';
	if ($code == 'TN') return 'Tunisia';
	if ($code == 'TR') return 'Turkey';
	if ($code == 'TM') return 'Turkmenistan';
	if ($code == 'TC') return 'Turks and Caicos Islands';
	if ($code == 'TV') return 'Tuvalu';
	if ($code == 'UG') return 'Uganda';
	if ($code == 'UA') return 'Ukraine';
	if ($code == 'AE') return 'United Arab Emirates';
	if ($code == 'GB') return 'United Kingdom';
	if ($code == 'US') return 'United States of America';
	if ($code == 'UM') return 'United States Minor Outlying Islands';
	if ($code == 'VI') return 'United States Virgin Islands';
	if ($code == 'UY') return 'Uruguay, Eastern Republic of';
	if ($code == 'UZ') return 'Uzbekistan';
	if ($code == 'VU') return 'Vanuatu';
	if ($code == 'VE') return 'Venezuela';
	if ($code == 'VN') return 'Vietnam';
	if ($code == 'WF') return 'Wallis and Futuna';
	if ($code == 'EH') return 'Western Sahara';
	if ($code == 'YE') return 'Yemen';
	if ($code == 'XK') return 'Kosovo';
	if ($code == 'ZM') return 'Zambia';
	if ($code == 'ZW') return 'Zimbabwe';
	return '';
}