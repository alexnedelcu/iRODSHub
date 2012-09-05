<?php
if ($_GET['randomId'] != "tA0u29cFdcf8kkD3gLtrjcCuvVlkq2QpY7bvHJO2PyF9zpzv4VSJk5oNf2dy1GeB") {
    echo "Access Denied";
    exit();
}

// display the HTML code:
echo stripslashes($_POST['wproPreviewHTML']);

?>  
