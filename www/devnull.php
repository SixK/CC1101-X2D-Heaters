<?php

if(move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name'])){
    echo "Upload OK";
}
?>