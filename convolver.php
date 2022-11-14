<?php
$target_dir = "uploads/";
$ir_name = basename($_FILES["ir"]["name"]);
$ir_path = $target_dir . basename($_FILES["ir"]["name"]);
$sample_path = $target_dir . basename($_FILES["wav"]["name"]);
$sample_name = basename($_FILES["wav"]["name"]);
$uploadOk = 1;
// IF FILES EXIST BREAK
move_uploaded_file($_FILES["ir"]["tmp_name"], $ir_path);
move_uploaded_file($_FILES["wav"]["tmp_name"], $sample_path);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="css/uploaded.css">
    <!--    <script src="scripts/helper.js" type="text/javascript"></script>-->
    <title>Convolver</title>
</head>

<body>
<h1 class="welcome">FILES UPLOADED</h1>
<div class="audio_box">
    <h2 class="file_title"><?= $ir_name ?></h2>
    <audio src=<?= $ir_path ?> controls="true"></audio>
    <h2 class="file_title"><?= $sample_name ?></h2>
    <audio src=<?= $sample_path ?> controls="true"></audio>
</div>

</body>


</html>