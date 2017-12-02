<?php

/*
* Class REGEX allow to check multiply strings against multiply regexs
* public static [string] $_error - holds the errors
* public static [function] is_regex - checks a string to be a regex
* public static [function] regex_match - checks strings against regex
*/
class REGEX{
	
	// Errors from regex_match
	public static $_error = false;
	
	/*
	* Checks if its a regex
	* @param [string] $regex - string to check
	*/
	public function is_regex( $regex ){
		
		if(@preg_match($regex, null) === false){
			
			return false;			
		}
		return true;
	}
	
	/*
	* Checks if STRINGS match REGEXS
	* @param [array/string] $string - strings for checking
	* @param [array/string] $regex - regexs to check
	* @param [boolean] $string_strict - all strings has to pass the check
	* @param [boolean] $string_strict - has to check with all regexs
	* @returns [boolean] TRUE if successful, FALSE otherwise
	*/
	public static function regex_match(
		$string,
		$regex,
		$string_strict = false,
		$regex_strict = false
	){
		
		/*
		*** HOW TO USE ***
		
		regex_match(
			array(			// strings to be checked
				string1,
				string2,
				...
			),
			array(			// regexs for check
				regex1,
				regex2,
				...
			),
			true,			// check all strings
			true			// check with all regexs
		);
		
		*/
		
		// if $string is a [string]
		if( gettype( "" ) === gettype( $string ) ){
			
			// if $regex is a [string]
			if( gettype( "" ) === gettype( $regex ) ){
			
				// If the regex is not valid
				if( REGEX::is_regex( $regex ) === false){
				
					REGEX::$_error = "regex_match:invalid regex";
					
					return false;
					
				}else{
					
					REGEX::$_error = false;
					
					// If the $string match the $regex
					if( @preg_match($regex, $string) === 1 ){
					
						return true;
						
					}
				}
				
			// if $regex is a [array]
			}else if( gettype( array() ) === gettype( $regex ) ){
				
				// Regexs that string is matching
				$matched = 0;
					
				for( $a = 0; $a < count( $regex ); $a ++ ){
					
					// Recursion about every $regex[$a]
					$result = REGEX::regex_match( $string, $regex[$a], $string_strict, $regex_strict );
					
					if( $result === false ){
						
						// No errors means that the string dont match the regex
						if( REGEX::$_error === false ){
							
							REGEX::$_error = "regex_match:string not matching regex[" . $a . "]";
							
						}else{// else there is some error with the regex
							
							REGEX::$_error = "regex_match:error at regex[" . $a . "]";
						}
						// Already one of them failed
						if( $regex_strict === true ) return false;
						
					}else{// Matched
						
						// Increace the successful matches
						$matched ++;
						
						// At least one is successful
						if( $regex_strict !== true ){
							
							REGEX::$_error = false;
							
							return true;
						}
					}
				}
				if( $matched > 0 ){// If there are successful matches
					
					REGEX::$_error = false;
					
					return true;
				}
			}else{// $regex is not [array] or [string]
				
				REGEX::$_error = "regex_match:invalid regex data type";
				
				return false;
			}
		
		// if $string is a [array]
		}else if( gettype( array() ) === gettype( $string ) ){
			
			for( $a = 0; $a < count( $string ); $a ++ ){
				
				// if $regex is a [string]
				if( gettype( "" ) === gettype( $regex ) ){
					
					// If the regex is not valid
					if(REGEX::is_regex( $regex ) === false){
					
						REGEX::$_error = "regex_match:invalid regex";
						
						return false;
						
					}else{
						
						REGEX::$_error = false;
						
						// If the $string[$a] match the $regex
						if( @preg_match($regex, $string[$a]) === 1 ){
						
							return true;
							
						}
					}
					
				// if $regex is a [array]
				}else if( gettype( array() ) === gettype( $regex ) ){
					
					// Regexs that string is matching
					$regex_matched = 0;
					// Strings thet has matched regex
					$string_matched = 0;
					
					for( $b = 0; $b < count( $regex ); $b ++ ){
						
						// Recursion about every $string[$a] and $regex[$b]
						$result = REGEX::regex_match( $string[$a], $regex[$b], $string_strict, $regex_strict );
						
						if( $result === false ){
							
							// No errors means that the string dont match the regex
							if( REGEX::$_error === false ){
								
								REGEX::$_error = "regex_match:string[" . $a . "] not matching regex[" . $b . "]";
								
							}else{// else there is some error with the regex
								
								REGEX::$_error = "regex_match:error at regex[" . $b . "]";
							}
							if( $regex_strict === true && $string_strict === true ) return false;
							
						}else{// Matched
							
							// Increace the successful matches
							$regex_matched ++;
							$string_matched ++;
							
							// At least one is successful
							if( $regex_strict !== true ){
							
								REGEX::$_error = false;
								
								return true;
							}
						}
					}
					if( $regex_matched > 0 ){// If there are successful matches
						
						REGEX::$_error = false;
					
						return true;
					}
					
				}else{// $regex is not [array] or [string]
					
					REGEX::$_error = "regex_match:invalid regex data type";
					
					return false;
				}
			}
			if( $string_matched > 0 ){// If there are successful matches
						
				REGEX::$_error = false;
				
				return true;
			}
			
		}else{// $string is not [array] or [string]
			
			REGEX::$_error = "regex_match:invalid string data type";
		}
		return false;
	}
}