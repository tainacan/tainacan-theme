#!/bin/bash
php -r '
echo "\n ..::Tainacan Theme::..\n";

echo "\nStarting installation with composer ... please wait!!";

echo "\nChecking if bootstrap folders exist ...\n\n";

if (!file_exists("src/assets/bootstrap")) { 
    mkdir("src/assets/vendor/bootstrap/", 0777, true); 
    mkdir("src/assets/vendor/bootstrap/scss", 0777, true); 
    mkdir("src/assets/vendor/bootstrap/js", 0777, true); 
}

echo "\m Checking if file bootstrap navwalker exist ... \n\n";

if (!file_exists("src/vendor/class-wp-bootstrap-navwalker.php")) {
    echo "\m Copy Boostrap Navwalker ... \n\n";
    copy("vendor/wp-bootstrap/wp-bootstrap-navwalker/class-wp-bootstrap-navwalker.php", "src/vendor/class-wp-bootstrap-navwalker.php");
}

echo "\nStarting Copying Files...\n";

echo "...Bootstrap\n";
    recurse_copy("vendor/twbs/bootstrap/scss", "src/assets/vendor/bootstrap/scss");
    copy("vendor/twbs/bootstrap/dist/js/bootstrap.min.js", "src/assets/vendor/bootstrap/js/bootstrap.min.js");
    copy("vendor/twbs/bootstrap/assets/js/vendor/popper.min.js", "src/assets/vendor/bootstrap/js/popper.min.js");


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