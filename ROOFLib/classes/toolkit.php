<?php

/**
 * Ray's Forms Toolkit
 *
 * A collection of functions used by ROOFLib
 */

abstract class RFTK {

	/**
	 * Append a query string to a url, even if that url currently has one
	 *
	 * @param  String  $url     a url with or without a query string
	 * @param  Mixed   $params  a query string or an array to be turned into a query string
	 *
	 * @return String
	 */
	static function href( $url, $params = null ) {
		$sep  = ( strpos($url,'?') === false ) ? '?' : '&';
		$href = $url;

		if ( is_array($params) ) {
			$params = http_build_query( $params );
		}

		if ( is_string($params) && trim($params) != "" ) {
			$href .= $sep . $params;
		}

		return $href;
	}

}
