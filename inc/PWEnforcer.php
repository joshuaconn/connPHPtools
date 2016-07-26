<?php
/**
 * Class to set password rules and test to see if they are met
 * @author Joshua Conn
 */
class PWEnforcer 
{
	private $minChars=1;
	private $maxChars=255;
	private $minLowerChars=0;
	private $minUpperChars=0;
	private $minNumericChars=0;
	private $minSpecialChars=0;
	
	/**
	 * Error Constants
	 */
	const TOO_FEW_CHARS     =  1;
	const TOO_FEW_LOWER     =  2;
	const TOO_FEW_UPPER     =  4;
	const TOO_FEW_NUMERIC   =  8;
	const TOO_FEW_SPECIAL   = 16;
	const TOO_MANY_CHARS    = 32;
	const NONPRINTING_CHARS = 64;
	
	/**
	 * @param int $num value passed to test() must have at least this many characters
	 */
	public function setMinChars($num)
	{
		//TODO:require positive integer
		$this->minChars=$num;
	}
	
	/**
	 * @param int $num value passed to test() must at most this many characters
	 */
	public function setMaxChars($num)
	{
		//TODO:require positive integer
		$this->maxChars=$num;
	}
	
	/**
	 * @param int $num value passed to test() must have at least this many 
	 *  lower case characters
	 */
	public function setMinLowerChars($num)
	{
		//TODO:require positive integer
		$this->minLowerChars=$num;
	}
	
	/**
	 * @param int $num value passed to test() must have at least this many 
	 *  upper case characters
	 */
	public function setMinUpperChars($num)
	{
		//TODO:require positive integer
		$this->minUpperChars=$num;
	}
	
	/**
	 * @param int $num value passed to test() must have at least this many 
	 *  numeric characters
	 */
	public function setMinNumericChars($num)
	{
		//TODO:require positive integer
		$this->minNumericChars=$num;
	}
	
	/**
	 * @param int $num value passed to test() must have at least this many 
	 *  printing non-alphanumeric characters
	 */
	public function setMinSpecialChars($num)
	{
		//TODO:require positive integer
		$this->minSpecialChars=$num;
	}
	
	/**
	 * Main function, 
	 * tests to see if the given string matches rules set by setter functions
	 * triggers error if the other settings are invalid
	 * @param string $pw password
	 * @return int 0 for pass or one of the constants
	 */
	public function test($pw)
	{
		if(!$this->valid())
			trigger_error('Invalid PWEnforcer configuration',
				E_USER_ERROR);
		$len=strlen($pw);
		if($len<$this->minChars)
			return PWEnforcer::TOO_FEW_CHARS;
		if($this->maxChars!=null && $len>$this->maxChars)
			return PWEnforcer::TOO_MANY_CHARS;
		$l = $u = $n = $s = 0; //char type totals
		for($i=0;$i<$len;$i++){
			if(preg_match('#[[:lower:]]#',$pw[$i])){
				$l++;
			}elseif(preg_match('#[[:upper:]]#',$pw[$i])){
				$u++;
			}elseif(preg_match('#[[:digit:]]#',$pw[$i])){
				$n++;
			}elseif(preg_match('#[[:punct:]]#',$pw[$i])){
				$s++;
			}else{
				return PWEnforcer::NONPRINTING_CHARS; //non-printing characters not allowed
			}
		}
		if($l<$this->minLowerChars)
			return PWEnforcer::TOO_FEW_LOWER;
		if($u<$this->minUpperChars)
			return PWEnforcer::TOO_FEW_UPPER;
		if($n<$this->minNumericChars)
			return PWEnforcer::TOO_FEW_NUMERIC;
		if($s<$this->minSpecialChars)
			return PWEnforcer::TOO_FEW_SPECIAL;
		return 0;
	}
	
	/**
	 * Tests to see if the given rules are feasible
	 */
	protected function valid()
	{
		if($this->maxChars!==NULL &&
			$this->maxChars < 
				$this->minLowerChars+
				$this->minUpperChars+
				$this->minNumericChars+
				$this->minSpecialChars)
			return false;
		return true;
	}
}
?>