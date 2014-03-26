<?php
/**
 * saveCustomCss
 *
 * Copyright 2013 by YJ Tso <yj@modx.com>
 *
 * saveCustomCss and cssSweet is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * saveCustomCss and cssSweet is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * saveCustomCss and cssSweet; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package cssSweet
 *
 * TO DO: change file_exists check on line 60 to is_dir. Better check on last line OR more accurate return notice.
 */

// Optionally a chunk name can be specified in plugin properties
$chunk = $modx->getOption('csss.custom_css_chunk',$scriptProperties,$modx->getOption('csss.custom_css_chunk',null,'csss.custom.css'));

// Optionally a file name can be specified in plugin properties
$filename = $modx->getOption('csss.custom_css_filename',$scriptProperties,$modx->getOption('csss.custom_css_filename',null,'csss-custom.css'));

// Optionally minify the output, defaults to 'true'
$minify_custom_css = $modx->getOption('csss.minify_custom_css',$scriptProperties,$modx->getOption('csss.minify_custom_css',null,true));

// Construct path from system settings
$csssCustomCssPath = $modx->getOption('csss.custom_css_path');
if ( !$csssCustomCssPath ) {
    $assetsPath = $modx->getOption('assets_path');
    $csssCustomCssPath = $assetsPath . 'components/csssweet/';
    $modx->log(modX::LOG_LEVEL_INFO, 'csss.custom_css_path was not defined. Path set to ' . $csssCustomCssPath,'','saveCustomCss');
}

// Grab the ClientConfig class
$ccPath = $modx->getOption('clientconfig.core_path', null, $modx->getOption('core_path') . 'components/clientconfig/');
$ccPath .= 'model/clientconfig/';
$clientConfig = $modx->getService('clientconfig','ClientConfig', $ccPath);
$settings = array();

// If we got the class (which means it's installed properly), include the settings
if ($clientConfig instanceof ClientConfig) {
    $settings = $clientConfig->getSettings();

    /* Make settings available as [[++tags]] */
    $modx->setPlaceholders($settings, '+');

} else { 
    $modx->log(modX::LOG_LEVEL_WARN, 'Failed to load ClientConfig class. ClientConfig settings not included.','','saveCustomCssClientConfig'); 
}

// If directory exists but isn't writable we have a problem, Houston
if ( file_exists($csssCustomCssPath) && !is_writable($csssCustomCssPath) ) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'The directory at ' . $csssCustomCssPath . 'is not writable!','','saveCustomCss');
    return;
}
    
// Check if directory exists, if not, create it
if ( !file_exists($csssCustomCssPath) ) {
    mkdir($csssCustomCssPath,0755,true);
    $modx->log(modX::LOG_LEVEL_INFO, 'Directory created at ' . $csssCustomCssPath,'','saveCustomCss');
}

// Parse chunk with $settings array
$contents = '/* Contents generated by MODX - this file will be overwritten. */' . PHP_EOL;
if ($chunk) $chunked = $modx->getChunk($chunk,$settings);
if ($chunked) { 
    $contents .= $chunked;    
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Failed to get Chunk. Custom CSS not saved.','','saveCustomCss');
    return;
}

// Define target file
$file = $csssCustomCssPath . $filename;

// Output
if ($minify_custom_css) {
    $contents = preg_replace("/\s+/"," ",$contents);
    $expanded = array(' {', '{ ', ' }', '} ', ' :', ': ', ' ;', '; ', ', ');
    $contracted = array('{', '{', '}', '}', ':', ':', ';', ';', ',', ',');
    $contents = str_replace($expanded, $contracted, $contents);
} 
file_put_contents($file,$contents);
if (file_exists($file) && is_readable($file)) $modx->log(modX::LOG_LEVEL_INFO, 'Success! Custom CSS saved to file "' . $file . '"','','saveCustomCss');
