<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\out\templating\interfaces;

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