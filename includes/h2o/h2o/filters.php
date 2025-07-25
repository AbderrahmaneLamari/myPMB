<?php

class FilterCollection {};


class CoreFilters extends FilterCollection {
    public static function first($value) {
        return $value[0];
    }
    
    public static function last($value) {
        return $value[count($value) - 1];
    }
    
    public static function join($value, $delimiter = ', ') {
        return join($delimiter, $value);
    }
    
    public static function length($value) {
        if(is_string($value)){
            return strlen($value);
        }
        return is_countable($value) ? count($value) : 0;
    }
    
    public static function urlencode($data) {
    	global $charset;
        if (is_array($data)) {
            $result;
            foreach ($data as $name => $value) {
                $result .= $name.'='.urlencode($value).'&'.$querystring;
            }
            $querystring = substr($result, 0, strlen($result)-1);
            return htmlentities($result, ENT_QUOTES, $charset);
        } else {
            return urlencode($data);
        }
    }
    
    public static function hyphenize ($string) {
        $rules = array('/[^\w\s-]+/'=>'','/\s+/'=>'-', '/-{2,}/'=>'-');
        $string = preg_replace(array_keys($rules), $rules, trim($string));
        return $string = trim(strtolower($string));
    }
 
	public static function urlize( $string, $truncate = false ) {
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		preg_match_all($reg_exUrl, $string, $matches);
		$usedPatterns = array();
		foreach($matches[0] as $pattern){
			if(!array_key_exists($pattern, $usedPatterns)){
				$usedPatterns[$pattern]=true;
				$string = str_replace($pattern, "<a href=\"{$pattern}\" rel=\"nofollow\">{$pattern}</a>", $string);
			}
		}
		$reg_exEmail = "/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/";
		preg_match_all($reg_exEmail, $string, $matches);
		$usedPatterns = array();
		foreach($matches[0] as $pattern){
			if(!array_key_exists($pattern, $usedPatterns)){
				$usedPatterns[$pattern]=true;
				$string = str_replace($pattern, "<a href=\"mailto:{$pattern}\">{$pattern}</a>", $string);
			}
		}
		return $string;
	}

    public static function set_default($object, $default) {
        return !$object ? $default : $object;
    }
}

class StringFilters extends FilterCollection {

    public static function humanize($string) {
        $string = preg_replace('/\s+/', ' ', trim(preg_replace('/[^A-Za-z0-9()!,?$]+/', ' ', $string)));
        return self::capfirst($string);
    }
    
    public static function capitalize($string) {
        return ucwords(strtolower($string)) ;
    }
    
    public static function titlize($string) {
        return self::capitalize($string);
    }
    
    public static function capfirst($string) {
        $string = strtolower($string);
        return ucfirst($string);
    }
    
    public static function tighten_space($value) {
        return preg_replace("/\s{2,}/", ' ', $value);
    }
    
    public static function escape($value, $attribute = false) {
    	global $charset;
        return htmlspecialchars($value, $attribute ? ENT_QUOTES : ENT_NOQUOTES,$charset);
    }
    
    public static function escapejson($value) {
    	// The standard django escapejs converts all non-ascii characters into hex codes.
    	// This function encodes the entire data structure, and strings get quotes around them.
    	return json_encode($value);
    }
    
    public static function force_escape($value, $attribute = false) {
    	return self::escape($value, $attribute);
    }
    
    public static function e($value, $attribute = false) {
        return self::escape($value, $attribute);
    }
    
    public static function safe($value) {
    	return $value;
    }
    
	public static function truncate ($string, $max = 50, $ends = '...') {
		return (strlen($string) > $max ? substr($string, 0, $max).$ends : $string);
    }
    
    public static function limitwords($text, $limit = 50, $ends = '...') {
        if (strlen($text) > $limit) {
            $words = str_word_count(convert_diacrit($text), 2);
            $pos = array_keys($words);

            if (isset($pos[$limit])) {
                $text = substr($text, 0, $pos[$limit]) . $ends;
            }
        }
        return $text;
    }
}

class NumberFilters extends FilterCollection {
	public static function filesize ($bytes, $round = 1) {
        global $lang;
        
    	if ($bytes === 0) {
        	switch ($lang) {
				case 'fr_FR':
	            	return '0 octets';
				default:
					return '0 bytes';
        	}
        } elseif ($bytes === 1) {
        	switch ($lang) {
        		case 'fr_FR':
        			return '1 octet';
        		default:
        			return '1 byte';
        	}
    	}
		switch ($lang) {
			case 'fr_FR':
				$units = array(
						'octets' => pow(2, 0), 'Ko' => pow(2, 10),
						'Mo' => pow(2, 20), 'Go' => pow(2, 30),
						'To' => pow(2, 40), 'Po' => pow(2, 50),
						'Eo' => pow(2, 60), 'Zo' => pow(2, 70)
				);
				$lastUnit = 'octets';
				break;
			default:
				$units = array(
						'bytes' => pow(2, 0), 'kB' => pow(2, 10),
						'MB' => pow(2, 20), 'GB' => pow(2, 30),
						'TB' => pow(2, 40), 'PB' => pow(2, 50),
						'EB' => pow(2, 60), 'ZB' => pow(2, 70)
				);
				$lastUnit = 'bytes';
				break;
		}

        
        foreach ($units as $unitName => $unitFactor) {
            if ($bytes >= $unitFactor) {
                $lastUnit = $unitName;
            } else {
                $number = round( $bytes / $units[$lastUnit], $round );
                return number_format($number) . ' ' . $lastUnit;
            }
        }
    }

    public static function currency($amount, $currency = 'USD', $precision = 2, $negateWithParentheses = false) {
        $definition = array(
            'EUR' => array('�','.',','), 'GBP' => '�', 'JPY' => '�', 
            'USD'=>'$', 'AU' => '$', 'CAN' => '$'
        );
        $negative = false;
        $separator = ',';
        $decimals = '.';
        $currency = strtoupper($currency);
    
        // Is negative
        if (strpos('-', $amount) !== false) {
            $negative = true;
            $amount = str_replace("-","",$amount);
        }
        $amount = (float) $amount;

        if (!$negative) {
            $negative = $amount < 0;
        }
        if ($negateWithParentheses) {
            $amount = abs($amount);
        }

        // Get rid of negative zero
        $zero = round(0, $precision);
        if (round($amount, $precision) === $zero) {
            $amount = $zero;
        }
    
        if (isset($definition[$currency])) {
            $symbol = $definition[$currency];
            if (is_array($symbol))
                @list($symbol, $separator, $decimals) = $symbol;
        } else {
            $symbol = $currency;
        }
        $amount = number_format($amount, $precision, $decimals, $separator);

        return $negateWithParentheses ? "({$symbol}{$amount})" : "{$symbol}{$amount}";
    }
}

class HtmlFilters extends FilterCollection {
    public static function base_url($url, $options = array()) {
        return $url;
    }
    
    public static function asset_url($url, $options = array()) {
        return self::base_url($url, $options);
    }
    
    public static function image_tag($url, $options = array()) {
        $attr = self::htmlAttribute(array('alt','width','height','border'), $options);
        return sprintf('<img src="%s" %s/>', $url, $attr);
    }

    public static function css_tag($url, $options = array()) {
        $attr = self::htmlAttribute(array('media'), $options);
        return sprintf('<link rel="stylesheet" href="%s" type="text/css" %s />', $url, $attr);
    }

    public static function script_tag($url) {
        return sprintf('<script src="%s" type="text/javascript"></script>', $url);
    }
    
    public static function links_to($text, $url, $options = array()) {
        $attrs = self::htmlAttribute(array('ref'), $options);
        $url = self::base_url($url, $options);
        return sprintf('<a href="%s" %s>%s</a>', $url, $attrs, $text);
    }
    
    public static function links_with ($url, $text, $options = array()) {
        return self::links_to($text, $url, $options);
    }
    
    public static function strip_tags($text) {
        $text = preg_replace(array('/</', '/>/'), array(' <', '> '),$text);
        return strip_tags($text);
    }

	public static function linebreaks($value, $format = 'p') {
        if ($format === 'br')
            return HtmlFilters::nl2br($value);
        return HtmlFilters::nl2pbr($value);
    }
    
    public static function nl2br($value) {
        return str_replace("\n", "<br />\n", $value);
    }
    
	public static function nl2pbr($value) {
        $result = array();
        $parts = preg_split('/(\r?\n){2,}/m', $value);
        foreach ($parts as $part) {
            $result[] = '<p>' . HtmlFilters::nl2br($part) . '</p>';
        }
        return implode("\n", $result);
    }

    protected static function htmlAttribute($attrs = array(), $data = array()) {
        $attrs = self::extract(array_merge(array('id', 'class', 'title', "style"), $attrs), $data);
        
        $result = array();
        foreach ($attrs as $name => $value) {
            $result[] = "{$name}=\"{$value}\"";
        }
        return join(' ', $result);
    }

    protected static function extract($attrs = array(), $data=array()) {
        $result = array();
        if (empty($data)) return array();
        foreach($data as $k => $e) {
            if (in_array($k, $attrs)) $result[$k] = $e;
        }
        return $result;
    }
}

class DatetimeFilters extends FilterCollection {
    public static function date($time, $format = 'jS F Y H:i') {
        if ($time instanceof DateTime) 
            $time  = (int) $time->format('U');
        if (!is_numeric($time)) 
          $time = strtotime($time);
          
        return date($format, $time);
    }

    public static function relative_time($timestamp, $format = 'g:iA') {
        if ($timestamp instanceof DateTime) 
            $timestamp = (int) $timestamp->format('U');

        $timestamp = is_numeric($timestamp) ? $timestamp: strtotime($timestamp);
        
        $time   = mktime(0, 0, 0);
        $delta  = time() - $timestamp;
        $string = '';
        
        if ($timestamp < $time - 86400) {
            return date("F j, Y, g:i a", $timestamp);
        }
        if ($delta > 86400 && $timestamp < $time) {
            return "Yesterday at " . date("g:i a", $timestamp);
        }

        if ($delta > 7200)
            $string .= floor($delta / 3600) . " hours, ";
        else if ($delta > 3660)
            $string .= "1 hour, ";
        else if ($delta >= 3600)
            $string .= "1 hour ";
        $delta  %= 3600;
        
        if ($delta > 60)
            $string .= floor($delta / 60) . " minutes ";
        else
            $string .= $delta . " seconds ";
        return "$string ago";
    }

    public static function relative_date($time) {
        if ($time instanceof DateTime) 
            $time = (int) $time->format('U');

        $time = is_numeric($time) ? $time: strtotime($time);
        $today = strtotime(date('M j, Y'));
        $reldays = ($time - $today)/86400;
        
        if ($reldays >= 0 && $reldays < 1)
            return 'today';
        else if ($reldays >= 1 && $reldays < 2)
            return 'tomorrow';
        else if ($reldays >= -1 && $reldays < 0)
            return 'yesterday';

        if (abs($reldays) < 7) {
            if ($reldays > 0) {
                $reldays = floor($reldays);
                return 'in ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
            } else {
                $reldays = abs(floor($reldays));
                return $reldays . ' day'  . ($reldays != 1 ? 's' : '') . ' ago';
            }
        }
        if (abs($reldays) < 182)
            return date('l, F j',$time ? $time : time());
        else
            return date('l, F j, Y',$time ? $time : time());
    }
    
    public static function relative_datetime($time) {
        $date = self::relative_date($time);
        
        if ($date === 'today')
            return self::relative_time($time);
        
        return $date;
    }
}

/*  Ultizie php funciton as Filters */
h2o::addFilter(array('md5', 'sha1', 'numberformat'=>'number_format', 'wordwrap', 'trim', 'upper' => 'strtoupper', 'lower' => 'strtolower'));

/* Add filter collections */
h2o::addFilter(array('CoreFilters', 'StringFilters', 'NumberFilters', 'DatetimeFilters', 'HtmlFilters'));

/* Alias default to set_default */
h2o::addFilter('default', array('CoreFilters', 'set_default'));
