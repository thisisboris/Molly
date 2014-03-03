<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\http\routing\interfaces;


interface Controller {
    public function doGet();
    public function doPost();
} 