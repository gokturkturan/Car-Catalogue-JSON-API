<?php 
    //http://localhost/JSON-API-Car-Catalogue/index.php?id=2
    include "functions.php";

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "api";
    $connect = mysqli_connect($host,$user,$pass);
    
    $json = array();
    $json["Error"] = FALSE;
    $code = 200;

    if($connect) {
        mysqli_select_db($connect, $db);
        if($_SERVER['REQUEST_METHOD'] == "POST") {
            if($_POST["action"] == "addCar") {
                if(!isset($_POST["make"]) || !isset($_POST["model"]) || !isset($_POST["trims"]) || !isset($_POST["year"])) {
                    $code = 400;
                    $json["Error"] = TRUE;
                    $json["Message"] = "Please POST every data.";
                } else {
                    $make = mysqli_real_escape_string($connect,$_POST["make"]);
                    $model = mysqli_real_escape_string($connect,$_POST["model"]);
                    $trims = mysqli_real_escape_string($connect,$_POST["trims"]);
                    $year = mysqli_real_escape_string($connect,$_POST["year"]);

                    if(empty($make) || empty($model) || empty($trims) || empty($year)) {
                        $code = 400;
                        $json["Error"] = TRUE;
                        $json["Message"] = "Please do not leave a blank space.";
                    } else {
                        $getTable = mysqli_query($connect,"SELECT * FROM cars WHERE make='$make' AND model='$model' AND trims='$trims' AND year='$year'");
                        if(mysqli_num_rows($getTable) == 0) {
                            $addTable = mysqli_query($connect,"INSERT INTO cars (make,model,trims,year) VALUES('$make','$model','$trims','$year')");
                            if($addTable) {
                                $code = 200;
                                $json["Error"] = FALSE;
                                $json["Message"] = "Your input added to the cars table.";
                            } else {
                                $code = 400;
                                $json["Error"] = TRUE;
                                $json["Message"] = "Your input did not add to the cars table.";
                            }
                        } else {
                            $code = 400;
                            $json["Error"] = TRUE;
                            $json["Message"] = "This car is already added to the table.";
                        }
                    }
                }
            } else if ($_POST["action"] == "addCarRating") {
                if(!isset($_POST["make"]) || !isset($_POST["model"]) || !isset($_POST["trims"]) || !isset($_POST["year"]) || !isset($_POST["agency"]) || !isset($_POST["rating"])) {
                    $code = 400;
                    $json["Error"] = TRUE;
                    $json["Message"] = "Please POST every data.";
                } else {
                    $make =mysqli_real_escape_string($connect,$_POST["make"]);
                    $model = mysqli_real_escape_string($connect,$_POST["model"]);
                    $trims = mysqli_real_escape_string($connect,$_POST["trims"]);
                    $year = mysqli_real_escape_string($connect,$_POST["year"]);
                    $agency = mysqli_real_escape_string($connect,$_POST["agency"]);
                    $rating = mysqli_real_escape_string($connect,$_POST["rating"]);
                    if(empty($make) || empty($model) || empty($trims) || empty($year) || empty($agency) || empty($rating)) {
                        $code = 400;
                        $json["Error"] = TRUE;
                        $json["Message"] = "Please do not leave a blank space.";
                    } else {
                        $getTable = mysqli_query($connect,"SELECT * FROM cars WHERE make='$make' AND model='$model' AND trims='$trims' AND year='$year'");
                        if(mysqli_num_rows($getTable) == 1) {
                            while($row = mysqli_fetch_assoc($getTable)) {
                                $id = $row['id'];
                                $getTable = mysqli_query($connect,"SELECT * FROM rating WHERE car_id='$id'");
                                if(mysqli_num_rows($getTable) >= 1) {
                                    $getTable = mysqli_query($connect,"SELECT * FROM rating WHERE agency = '$agency' AND car_id = '$id'");
                                    if(mysqli_num_rows($getTable) == 0) {
                                        $addTable2 = mysqli_query($connect,"INSERT INTO rating (car_id,agency,rating) VALUES ('$id','$agency','$rating')");
                                        if($addTable2) {
                                            $code = 200;
                                            $json["Error"] = FALSE;
                                            $json["Message"] = "Evaluation is added.";
                                        } else {
                                            $code = 400;
                                            $json["Error"] = TRUE;
                                            $json["Message"] = "Evaluation did not added.";
                                        }
                                    } else {
                                        $code = 400;
                                        $json["Error"] = TRUE;
                                        $json["Message"] = "This evaluation is already added.";
                                    }
                                    break;
                                } else {
                                    $addTable = mysqli_query($connect,"INSERT INTO rating (car_id,agency,rating) VALUES('$id','$agency','$rating')");
                                    if($addTable) {
                                        $code = 200;
                                        $json["Error"] = FALSE;
                                        $json["Message"] = "Evaluation is added.";
                                    } else {
                                        $code = 400;
                                        $json["Error"] = TRUE;
                                        $json["Message"] = "Evaluation did not added.";
                                    }
                                }
                            }
                        } else {
                            $code = 400;
                            $json["Error"] = TRUE;
                            $json["Message"] = "Please add the car first.";
                        }
                    }
                }
            } else {
                $code = 400;
                $json["Error"] = TRUE;
                $json["Message"] = "Please sent your action (addCar or addCarRating)";
            }
            
        } else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
            if(isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
                $CarTableID = intval($_GET["id"]);
                $getTable = mysqli_query($connect,"SELECT * FROM cars WHERE id='$CarTableID'");
                if(mysqli_num_rows($getTable) == 0) {
                    $code = 400;
                    $json["Error"] = TRUE;
                    $json["Message"] = "There is no such car.";
                } else {
                    $deleteCar = mysqli_query($connect,"DELETE FROM cars WHERE id='$CarTableID'");
                    $deleteRating = mysqli_query($connect,"DELETE FROM rating WHERE car_id='$CarTableID'");
    
                    if($deleteCar && $deleteRating) {
                        $code = 200;
                        $json["Error"] = FALSE;
                        $json["Message"] = "Car and its rating were deleted.";
                    } else {
                        $code = 400;
                        $json["Error"] = TRUE;
                        $json["Message"] = "Car and its rating are not deleted.";
                    }
                }  
            } else {
                $code = 400;
                $json["Error"] = TRUE;
                $json["Message"] = "Please send datas";
            }
        } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
            if(isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
                $id = intval($_GET["id"]);
                $getTable = mysqli_query($connect,"SELECT * FROM cars WHERE id='$id'");
                if(mysqli_num_rows($getTable) == 1) {
                    $carData = mysqli_query($connect,"SELECT * FROM cars WHERE id='$id'");
                    $carRatingData = mysqli_query($connect,"SELECT * FROM rating WHERE car_id='$id'");
                    if($row = mysqli_fetch_assoc($carData)) {
                        $json["carData"] = $row;
                    }
                    $carRatings = [];
                    while($row = mysqli_fetch_assoc($carRatingData)) {
                        $carRatings[] = $row;
                    }
                    $json["carRatingData"] = $carRatings;
                } else if (mysqli_num_rows($getTable) == 0) {
                    $code = 400;
                    $json["Error"] = TRUE;
                    $json["Message"] = "There is no such car";
                }
            } else {
                $code = 400;
                $json["Error"] = TRUE;
                $json["Message"] = "Please send id";
            }
        } else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
            $input = json_decode(file_get_contents("php://input"));
            if(isset($input->id) && isset($input->make) && isset($input->model) && isset($input->trims) && isset($input->year)) {
                $getTable = mysqli_query($connect,"SELECT * FROM cars WHERE id='$input->id'");
                $checkTable = mysqli_query($connect,"SELECT * FROM cars WHERE make='$input->make' AND model='$input->model' AND trims='$input->trims' AND year='$input->year'");

                if(mysqli_num_rows($getTable) == 0) {
                    $code = 400;
                    $json["Error"] = TRUE;
                    $json["Message"] = "There is no such car. You cannot update.";
                } else {
                    if(mysqli_num_rows($checkTable) == 1) {
                        $code = 400;
                        $json["Error"] = TRUE;
                        $json["Message"] = "This car already exists. You cannot update.";
                    } else {
                        $row = mysqli_fetch_assoc($getTable);
                        if($row['make'] != $input->make || $row['model'] != $input->model || $row['trims'] != $input->trims || $row['year'] != $input->year) {
                            $deleteRating = mysqli_query($connect,"DELETE FROM rating WHERE car_id='$input->id'"); 
                            $updateTable =  mysqli_query($connect,"UPDATE cars SET make='$input->make', model='$input->model', trims='$input->trims', year='$input->year' WHERE id='$input->id'");
                            if($updateTable && $deleteRating) {
                                $code = 200;
                                $json["Error"] = FALSE;
                                $json["Message"] = "Old car is deleted and Update successful.";
                            } else {
                                $code = 400;
                                $json["Error"] = TRUE;
                                $json["Message"] = "Update failed.";
                            }
                        } else {
                            $code = 400;
                            $json["Error"] = TRUE;
                            $json["Message"] = "The data you updated is the same.";
                        }
                    } 
                }
            } else {
			    $code = 400;
			    $jsonArray["Error"] = TRUE;
	 		    $jsonArray["Message"] = "Car information not sent.";
		    }
        } else {
            $code = 400;
            $json["Error"] = TRUE;
            $json["Message"] = "Request does not found.";
        }
    } else {
        $code = 500;
        $json["Error"] = TRUE;
        $json["Message"] = "Database connection failed";
    }
    

    SetHeader($code);
    $json[$code] = HttpStatus($code);
    echo json_encode($json);
