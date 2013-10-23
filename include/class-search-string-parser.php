<?php
/**
 * Search String Parser class
 *
 * Class that analyze a search string for getting
 * and, or and not parts of this string
 *
 * @author Frank Habermann <lordlamer@lordlamer.de>
 * @date 20100303
 */

/**
 * class Search_String_Parser
 */
class Search_String_Parser {
	/**
	 * word that is used for or conditions
	 * @var $parserOrWord string
	 */
	var $parserOrWord = 'OR';

	/**
	 * char that is used for phrases
	 * @var $parserQuoteChar string
	 */
	var $parserQuoteChar = '"';

	/**
	 * char that is used for splitting words
	 * @var $parserWordSplitChar string
	 */
	var $parserWordSplitChar = ' ';

	/**
	 * char that is used for prohibiting words
	 * @var $parserQuoteChar string
	 */
	var $parserNotWordChar = '-';

	/**
	 * data store for and words
	 * @var $_and array
	 */
	var $_and = null;

	/**
	 * data store for or words
	 * @var $_or array
	 */
	var $_or = null;

	/**
	 * data store for not words
	 * @var $_not array
	 */
	var $_not = null;

	/**
	 * parse the search string
	 * @param $searchString string
	 * @return void
	 */
	function parse($searchString) {
		// this will be used to save what we found
		$and = array();
		$or = array();
		$not = array();

		// init
		$tokenArr = preg_split("/\s+/", trim($searchString));
		$lastToken = null;
		$nextToken = null;
		$orGroup = 0;
		$wordGroup = '';
		$inWordGroup = false;
		$doNotAdd = false;
		$wordGroupNot = false;

		// run for each token
		foreach($tokenArr as $key => $token) {
			// set nextToken
			if(isset($tokenArr[$key+1])) $nextToken = $tokenArr[$key+1];
			else $nextToken = null;

			// if we are in a wordgroup and the last char is a quote
			if($inWordGroup && mb_substr($token, -1) == $this->parserQuoteChar) {
				$wordGroup .= $this->parserWordSplitChar . mb_substr($token, 0, strlen($token)-1);
				$inWordGroup = false;

				if($nextToken == $this->parserOrWord || $lastToken == $this->parserOrWord) {
					$or[$orGroup][] = $wordGroup;
				}
				elseif($wordGroupNot) {
					$not[] = $wordGroup;
					$wordGroupNot = false;
				}
				else $and[] = $wordGroup;

				$doNotAdd = true;
				$lastToken = $wordGroup;

				if($nextToken != $this->parserOrWord && $lastToken != $this->parserOrWord) $orGroup++;
				if($lastToken == $this->parserOrWord && $nextToken != $this->parserOrWord) $orGroup++;
			}

			// if we are in a wordgroup
			if($inWordGroup) {
				$wordGroup .= $this->parserWordSplitChar . $token;
				$doNotAdd = true;
			}

			// if a quote is our first char in token
			if(mb_substr($token, 0, 1) == $this->parserQuoteChar) {
				$wordGroup = substr($token, 1);
				$inWordGroup = true;
				$doNotAdd = true;
			}

			// if
			if(mb_substr($token, 0, 2) == $this->parserNotWordChar . $this->parserQuoteChar) {
				$wordGroup = substr($token, 2);
				$inWordGroup = true;
				$wordGroupNot = true;
				$doNotAdd = true;
			}

			// normal word token
			if($token != $this->parserOrWord && !$inWordGroup && !$doNotAdd) {
				if($nextToken == $this->parserOrWord || $lastToken == $this->parserOrWord) $or[$orGroup][] = $token;
				elseif(mb_substr($token, 0, 1) == $this->parserNotWordChar) $not[] = substr($token, 1);
				else $and[] = $token;

				if($lastToken == $this->parserOrWord && $nextToken != $this->parserOrWord) $orGroup++;
				if($nextToken != $this->parserOrWord && $lastToken != $this->parserOrWord) $orGroup++;
			}

			// set lastToken
			if(!$doNotAdd) $lastToken = $token;

			// reset
			$doNotAdd = false;
		}

		// save found values to datastore
		$this->_and = $and;
		$this->_not = $not;
		$this->_or = $or;
	}

	/**
	 * get and words
	 * @return array
	 */
	function getSearchAnd() {
		return (array) $this->_and;
	}

	/**
	 * get or words
	 * @return array
	 */
	function getSearchOr() {
		return (array) $this->_or;
	}

	/**
	 * get not words
	 * @return array
	 */
	function getSearchNot() {
		return (array) $this->_not;
	}
}

?>
