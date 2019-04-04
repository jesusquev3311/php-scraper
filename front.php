<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Franchise App</title>
    <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
include 'db.php';

$categories = $conn->query('select id, category_name from category');
if(!$categories){
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
<section id="scrapper-form" class="section-wrapper">
    <div class="container">
        <h1>Franchise Data APP</h1>
        <div class="row">
            <form action="index.php" method="post">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Website URL *</label>
                            <input type="text" name="webite_url" id="website_url" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Franchise category *</label>
                            <select name="franchise_cat" id="franchise_cat" class="form-control">
                                <?php
                                if($categories->num_rows > 0){
                                    // output data of each row
                                    while($row = $categories->fetch_assoc()){
                                        echo "<option value='" . $row["id"] . "'>" . $row["category_name"] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Get Data</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

</body>
</html>


