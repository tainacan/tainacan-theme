#!/bin/bash
php -r '
echo "\n ..::Tainacan Theme::..\n";

echo "\nStarting installation with composer ... please wait!!";

echo "\nChecking if project folders exist ...\n\n";

if (!file_exists("vendor/bootstrap")) { 
    mkdir("src/vendor/bootstrap/", 0777, true); 
    mkdir("src/vendor/bootstrap/scss", 0777, true); 
    mkdir("src/vendor/bootstrap/js", 0777, true); 
}

echo "\nVerification completed...\n";

echo "\nStarting Copying Files...\n";

echo "...Bootstrap\n";
if (!file_exists("src/vendor/bootstrap/scss/bootstrap.scss")) { 
    recurse_copy("vendor/twbs/bootstrap/scss", "src/vendor/bootstrap/scss");
    copy("vendor/twbs/bootstrap/dist/js/bootstrap.min.js", "src/vendor/bootstrap/js/bootstrap.min.js");
}

echo "Finish!\n\n";

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != "." ) && ( $file != ".." )) {
            if ( is_dir($src . "/" . $file) ) {
                recurse_copy($src . "/" . $file,$dst . "/" . $file);
            }
            else {
                copy($src . "/" . $file,$dst . "/" . $file);
            }
        }
    }
    closedir($dir);
}
'