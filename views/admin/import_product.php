<!DOCTYPE html>
<html>

<head>
    <title>CSV Upload</title>
</head>

<body>
    <h2>Upload CSV File</h2>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="csvfile" accept=".csv">
        <br><br>
        <input type="submit" name="submit" value="Upload">
    </form>

    <br>

    <?php

    if (isset($_POST['submit'])) {
        $file = $_FILES['csvfile']['tmp_name'];

        if (($handle = fopen($file, "r")) !== FALSE) {
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                echo "Name : ", $data[0],  ", Price : ", $data[1],  ", Category_ID : ", $data[2], ", Image : ",  $data[3], "<br>";
            }

            fclose($handle);
        }
    }

    ?>

</body>

</html>