<?php

/*!
 * featureFamily v0.1
 * a helper library for Detector that classifies browsers based on features
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

class featureFamily {
	
	private static $familyDefault = "basic";
	
	/**
	* Decides which family this device should be a part of
	* @param  {Object}        the set of features that have already been defined for the user agent
	*
	* @return {String}        the name of the family that this user agent matches. might just be the default.
	*/
	public static function find($obj) {
				
		// set-up the configuration options for the system
		if (!($familiesJSON = @file_get_contents(__DIR__."/../../config/families.json"))) {
			// config.ini didn't exist so attempt to create it using the default file
			if (!@copy(__DIR__."/../../config/families.json.default", __DIR__."/../../config/families.json")) {
			    print "Please make sure families.json.default exists before trying to have Detector build the families.json file automagically.";
				exit;
			} else {
				$familiesJSON = @file_get_contents(__DIR__."/../../config/families.json");	
			}
		}
		
		$familiesJSON = json_decode($familiesJSON);
		
		foreach ($familiesJSON as $familyName => $familyTests) {
						
			$finalFamilyResult = true;
			
			foreach ($familyTests as $featureKey => $featureValue) {
				if (is_string($featureValue) || is_bool($featureValue)) {
					$familyResult = self::runTest($featureKey,$featureValue,$obj,true);
				} else if (is_array($featureValue)) {
					$familySubResultFinal = true;
					foreach($featureValue as $featureSubValue) {
						$familySubResult     = self::runTest($featureKey,$featureSubValue,$obj,false);
						$familySubResultFinal = ($familySubResultFinal && $familySubResult) ? true : false;
					}	
					$familyResult = $familySubResultFinal;
				}
				$finalFamilyResult = ($finalFamilyResult && $familyResult) ? true : false;
			}
			
			if ($finalFamilyResult) {
				return $familyName;
			}
		}
		
		return self::$familyDefault;
	}
	
	/**
	* figures out which test style should be run
	* @param  {String}        the key that may be needed for the test
	* @param  {String}        the value that may be needed for the test
	* @param  {Object}        the set of features that have been already identified for the user agent
	* @param  {Boolean}       whether or not both key & value should be tested or just the value
	*
	* @return {Boolean}       the result of testing the value against the object
	*/
	private static function runTest($testKey,$testValue,$currentObj,$testKeyValue) {

		if ($testKeyValue) {
			$testResult = false;
			if ($values = explode("||",$testValue)) {
				$testOrFinal = false;
				foreach ($values as $value) {
					$testOrTest  = self::testKeyValue($testKey,$value,$currentObj);
					$testOrFinal = ($testOrFinal || $testOrTest) ? true : false;
				}
				$testResult = $testOrFinal;
			} else {
				$testResult = self::testKeyValue($testKey,$testValue,$currentObj);
			}
		} else {
			$testResult  = true;
			$testLiteral = explode("=",$testValue);
			$testOr      = explode("||",$testValue);
			if (count($testLiteral) > 1) {
				$testResult = ($currentObj->$testLiteral[0] == $testLiteral[1]) ? true : false;
				//print "(obj->".$value." = ".$literal.") ".$testResult." <br />";
			} else if (count($testOr) > 1) {
				$testOrFinal = false;
				foreach ($testOr as $value) {
					$testOrTest  = self::testValue($value,$currentObj);
					$testOrFinal = ($testOrFinal || $testOrTest) ? true : false;
				}
				$testResult = $testOrFinal;
			} else {
				$testResult = self::testValue($testValue,$currentObj);
			}
		}
		
		return $testResult;
	}
	
	/**
	* tests a key & value against the object to see if it's true or not
	* @param  {String}        the key that will be tested
	* @param  {String}        the value that will be tested
	* @param  {Object}        the set of features that have been already identified for the user agent
	*
	* @return {Boolean}       the result of testing the value against the object
	*/
	private static function testKeyValue($key,$value,$currentObj) {
		$pos = strpos($value,"!");
		if ($pos !== false) {
			$value = substr($value,1);
			$testResult = !($currentObj->$key == $value) ? true : false;
			//print "!(obj->".$key." = ".$value.") ".$testResult." <br />";
		} else {
			$testResult = ($currentObj->$key == $value) ? true : false;
			//print "(obj->".$key." = ".$value.") ".$testResult." obj result: ".$currentObj->$key."<br />";
		}
		return $testResult;
	}
	
	/**
	* tests a value against the object to see if it's true or not
	* @param  {String}        the value that will be tested
	* @param  {Object}        the set of features that have been already identified for the user agent
	*
	* @return {Boolean}       the result of testing the value against the object
	*/
	private static function testValue($value,$currentObj) {
		$pos = strpos($value,"!");
		if ($pos !== false) {
			$value = substr($value,1);
			$testResult = !($currentObj->$value) ? true : false;
			//print "!(obj->".$value.") ".$testResult." <br />";
		} else {
			$testResult = ($currentObj->$value) ? true : false;
			//print "(obj->".$value.") ".$testResult." <br />";
		}
		return $testResult;
	}

}

?>