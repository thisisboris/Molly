<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

// Require our libary autoloader.
require_once("library/toolbelt/Classloader.php");

$testarr = array("Test", "test" , "testtest");
$array = new \Molly\library\utils\collection\MollyArray($testarr);

?>
<html>
    <head>
        <title>Molly</title>
    </head>
    <body>
        <div id="wrapper">
            <div class="container">
                <h2>Soon!</h2>
                <p>Molly isn't ready yet.</p>
                <div class="code">
                    One ... more ... line ...
                    <?php
                        echo "<pre>";


                        echo "</pre>";
                    ?>
                </div>
            </div>
        </div>
    </body>

</html>

<?php

die();