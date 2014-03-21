<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\out\templating\interfaces;

interface Theme {
	// THEME-RELATED METHODS
	function getName();
	function getDescription();

	// HTML-RELATED METHODS
	function buildMeta();
	function buildCSS();
	function buildJS();
	
	function getMeta();
	function getCSS();
	function getJS();

	function getFile($template);
	function getTemplate($template);
}