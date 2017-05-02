<?php /* $Id$ */

/*
What this file does:
	- Generates the download links found at qa.php.net
	- Determines which test results are emailed to news.php.net/php.qa.reports
	- Defines $QA_RELEASES for internal and external (api.php) use, contains all qa related information for future PHP releases

Documentation:
	$QA_RELEASES documentation:
		Configuration:
		- Key is future PHP version number
			- Example: If 5.3.6 is the latest stable release, then use 5.3.7 because 5.3.7-dev is our qa version
			- Typically, this is the only part needing changed
		- active (bool): 
			- It's active and being tested here 
			- Meaning, the version will be reported to the qa.reports list, and be linked at qa.php.net
			- File extensions .tar.gz and .tar.bz2 are assumed to be available
		- release (array):
			- type: RC, alpha, and beta are examples (case should match filename case)
			- version: 0 if no such release exists, otherwise an integer of the rc/alpha/beta number
			- md5_bz2: md5 checksum of this downloadable .tar.bz2 file
			- md5_gz:  md5 checksum of this downloadable .tar.gz file
			- md5_xz: md5 checksum of this downloadble .xz file
			- date: date of release e.g., 21 May 2011
			- baseurl: base url of where these downloads are located
			- Multiple checksums can be available, see the $QA_CHECKSUM_TYPES array below
		Other variables within $QA_RELEASES are later defined including:
			- reported: versions that make it to the qa.reports mailing list
			- release: all current qa releases, including paths to dl urls (w/ md5 info)
			- dev_version: dev version
			- $QA_RELEASES is made available at qa.php.net/api.php

TODO:
	- Save all reports (on qa server) for all tests, categorize by PHP version (see buildtest-process.php)
	- Consider storing rc downloads at one location, independent of release master
	- Determine best way to handle rc baseurl, currently assumes .tar.gz/tar.bz2 will exist
	- Determine if $QA_RELEASES is compatible with all current, and most future configurations
	- Determine if $QA_RELEASES can be simplified
	- Determine if alpha/beta options are desired
	- Unify then create defaults for most settings
	- Add option to allow current releases (e.g., retrieve current release info via daily cron, cache, check, configure ~ALLOW_CURRENT_RELEASES)
*/

$QA_RELEASES = array(
	'5.6.31' => array(
		'active'		=> true,
		'release'		=> array(
			'type'	    	=> 'RC',
			'number'    	=> 0,
			'md5_bz2'   	=> '',
			'md5_gz'    	=> '',
			'md5_xz'    	=> '',
			'sha256_bz2'	=> '',
			'sha256_gz'	=> '',
			'sha256_xz'	=> '',
			'date'      	=> '05 January 2017',
			'baseurl'   	=> 'http://downloads.php.net/tyrael/',
		),
	),

        '7.0.19' => array(
                'active'                => true,
                'release'               => array(
                        'type'      	=> 'RC',
                        'number'    	=> 1,
                        'md5_bz2'   	=> 'bf513b3e2cb84d2847ac2f8f5a408a2b',
                        'md5_gz'    	=> '666c41fe973a8f9558c562d7fef13e3d',
                        'md5_xz'    	=> '767f129cde60a0440fa78d2757cc9f21',
                        'sha256_bz2'	=> '390be1647b2923f0fddfec6c859197469b15e3a3e42e1392b31811b9b14dfcf6',
                        'sha256_gz'     => 'a7932ae328bf19e41e7eb784e78fb45bd67584a4ef3edeebca4dc055673fc4fb',
                        'sha256_xz'     => '919ad77822564e842eeec6b58aba9fbdf07dd774c20a897fd273ad639c809921',
                        'date'      	=> '27 April 2017',
                        'baseurl'   	=> 'http://downloads.php.net/ab/',
                ),
        ),

        '7.1.5' => array(
                'active'                => true,
                'release'		=> array(
                        'type'          => 'RC',
                        'number'        => 1,
                        'md5_bz2'       => 'ed1f9dc1c76acd8bce5d75fa6805f8c2',
                        'md5_gz'        => '3c166a32d3e9e99c9be8333ed047a7a4',
                        'md5_xz'        => '9526543d7fd881532b3574947c2a42ba',
                        'sha256_bz2'    => '38d3d9b85de9229cf7a7762abbe2e477eeea0a743226c5c8c28089002fa45c3a',
                        'sha256_gz'     => 'fb04768d931af9ebd01259d3cbc0af5f1565b7d3dfb2f423b96550ec8f348bed',
                        'sha256_xz'     => '93a2f08537a78b1109ac1e529da85eda2508e7a5f31415b8839260c5825a92e2',
                        'date'          => '27 April 2017',
                        'baseurl'       => 'http://downloads.php.net/~krakjoe/',
                ),
	)
);

// This is a list of the possible checksum values that can be supplied with a QA release. Any 
// new algorithm is read from the $QA_RELEASES array under the 'release' index for each version 
// in the form of "$algorithm_$filetype".
//
// For example, if SHA256 were to be supported, the following indices would have to be added:
//
// 'sha256_bz2' => 'xxx', 
// 'sha256_gz'	=> 'xxx', 
// 'sha256_xz'	=> 'xxx', 

$QA_CHECKSUM_TYPES = Array(
				'md5', 
				'sha256'
				);

/*** End Configuration *******************************************************************/

// $QA_RELEASES eventually contains just about everything, also for external use
// release  : These are encouraged for use (e.g., linked at qa.php.net)
// reported : These are allowed to report @ the php.qa.reports mailing list

foreach ($QA_RELEASES as $pversion => $info) {

	if (isset($info['active']) && $info['active']) {
	
		// Allow -dev versions of all active types
		// Example: 5.3.6-dev
		$QA_RELEASES['reported'][] = "{$pversion}-dev";
		$QA_RELEASES[$pversion]['dev_version'] = "{$pversion}-dev";
		
		// Allow -dev version of upcoming qa releases (rc/alpha/beta)
		// @todo confirm this php version format for all dev versions
		if ((int)$info['release']['number'] > 0) {
			$QA_RELEASES['reported'][] = "{$pversion}{$info['release']['type']}{$info['release']['number']}";
			if (!empty($info['release']['baseurl'])) {
				
				// php.net filename format for qa releases
				// example: php-5.3.0RC2
				$fn_base = 'php-' . $pversion . $info['release']['type'] . $info['release']['number'];

				$QA_RELEASES[$pversion]['release']['version'] = $pversion . $info['release']['type'] . $info['release']['number'];
				$QA_RELEASES[$pversion]['release']['files']['bz2']['path']= $info['release']['baseurl'] . $fn_base . '.tar.bz2'; 
				$QA_RELEASES[$pversion]['release']['files']['gz']['path'] = $info['release']['baseurl'] . $fn_base . '.tar.gz';

				foreach($QA_CHECKSUM_TYPES as $algo)
				{
					$QA_RELEASES[$pversion]['release']['files']['bz2'][$algo] = $info['release'][$algo . '_bz2'];
					$QA_RELEASES[$pversion]['release']['files']['gz'][$algo]  = $info['release'][$algo . '_gz'];

					if (!empty($info['release'][$algo . '_xz'])) {
						if(!isset($QA_RELEASES[$pversion]['release']['files']['xz']))
						{
							$QA_RELEASES[$pversion]['release']['files']['xz']['path'] = $info['release']['baseurl'] . $fn_base . '.tar.xz';
						}

						$QA_RELEASES[$pversion]['release']['files']['xz'][$algo]  = $info['release'][$algo . '_xz'];
					}
				}
			}
		} else {
			$QA_RELEASES[$pversion]['release']['enabled'] = false;
		}
	}
}

// Sorted information for later use
// @todo need these?
// $QA_RELEASES['releases']   : All current versions with active qa releases
foreach ($QA_RELEASES as $pversion => $info) {
	if (isset($info['active']) && $info['active'] && !empty($info['release']['number'])) {
		$QA_RELEASES['releases'][$pversion] = $info['release'];
	}
}

/* Content */
function show_release_qa($QA_RELEASES) {
	// The checksum configuration array
	global $QA_CHECKSUM_TYPES;

	echo "<!-- RELEASE QA -->\n";
	
	if (!empty($QA_RELEASES['releases'])) {
		
		$plural = count($QA_RELEASES['releases']) > 1 ? 's' : '';
		
		// QA Releases
		echo "<span class='lihack'>\n";
		echo "Providing QA for the following <a href='/rc.php'>test release{$plural}</a>:<br> <br>\n";
		echo "</span>\n";
		echo "<table>\n";

		// @todo check for vars, like if md5_* are set
		foreach ($QA_RELEASES['releases'] as $pversion => $info) {

			echo "<tr>\n";
			echo "<td colspan=\"" . (sizeof($QA_CHECKSUM_TYPES) + 1) . "\">\n";
			echo "<h3 style=\"margin: 0px;\">{$info['version']}</h3>\n";
			echo "</td>\n";
			echo "</tr>\n";

			foreach (Array('bz2', 'gz', 'xz') as $file_type) {
				if (!isset($info['files'][$file_type])) {
					continue;
				}

				echo "<tr>\n";
				echo "<td width=\"20%\"><a href=\"{$info['files'][$file_type]['path']}\">php-{$info['version']}.tar.{$file_type}</a></td>\n";

				foreach ($QA_CHECKSUM_TYPES as $algo) {
					echo '<td>';
					echo '<strong>' . strtoupper($algo) . ':</strong> ';

					if (isset($info['files'][$file_type][$algo]) && !empty($info['files'][$file_type][$algo])) {
						echo $info['files'][$file_type][$algo];
					} else {
						echo '(<em><small>No checksum value available</small></em>)&nbsp;';
					}

					echo "</td>\n";
				}

				echo "</tr>\n";
			}
		}

		echo "</table>\n";
	}

	echo "<!-- END -->\n";
}
