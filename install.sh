#!/bin/bash
php -r '
echo "\n ..::Tainacan Theme::..\n";

echo "\nStarting installation with composer ... please wait!!\n";

echo "\n Checking if file bootstrap navwalker exist ... \n\n";

if (!file_exists("src/vendor/class-wp-bootstrap-navwalker.php")) {
    echo "\m Copy Boostrap Navwalker ... \n\n";
    mkdir("src/vendor/", 0777, true);
    copy("vendor/wp-bootstrap/wp-bootstrap-navwalker/class-wp-bootstrap-navwalker.php", "src/vendor/class-wp-bootstrap-navwalker.php");
}

echo "\nStarting Copying Files...\n\n";

echo "\nCopy Frameword CSS - Bootstrap ...\n\n";

    if (!file_exists("src/assets/vendor/bootstrap")) { 
        mkdir("src/assets/vendor/", 0777, true);
        mkdir("src/assets/vendor/bootstrap/", 0777, true); 
        mkdir("src/assets/vendor/bootstrap/scss", 0777, true); 
        mkdir("src/assets/vendor/bootstrap/js", 0777, true); 
    }
    recurse_copy("vendor/twbs/bootstrap/scss", "src/assets/vendor/bootstrap/scss");
    copy("vendor/twbs/bootstrap/dist/js/bootstrap.min.js", "src/assets/vendor/bootstrap/js/bootstrap.min.js");
    copy("vendor/twbs/bootstrap/site/docs/4.1/assets/js/vendor/popper.min.js", "src/assets/vendor/bootstrap/js/popper.min.js");

echo "Copy Slick for Slider...\n\n";
    if (!file_exists("src/assets/vendor/slick")) {
        mkdir("src/assets/vendor/slick/", 0777, true);
        mkdir("src/assets/vendor/slick/scss", 0777, true);
        mkdir("src/assets/vendor/slick/js", 0777, true);
        mkdir("src/assets/vendor/slick/fonts", 0777, true);
    }
    copy("vendor/fabianobn/slick/slick/slick-theme.scss", "src/assets/vendor/slick/scss/slick-theme.scss");
    copy("vendor/fabianobn/slick/slick/slick.scss", "src/assets/vendor/slick/scss/slick.scss");
    copy("vendor/fabianobn/slick/slick/slick.min.js", "src/assets/vendor/slick/js/slick.min.js");
    copy("vendor/fabianobn/slick/slick/ajax-loader.gif", "src/assets/vendor/slick/ajax-loader.gif");
    recurse_copy("vendor/fabianobn/slick/slick/fonts", "src/assets/vendor/slick/fonts");
    
echo "Finish Copy files! \n\n";

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