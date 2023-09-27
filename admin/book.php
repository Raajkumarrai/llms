<?php
if (isset($_GET['error'])) {
    echo '<div class="fullcontainerToast">
    <div class="toastifier">
        <div class="toastifierContent errorToast ">
        <div class="cross" onclick="crossClk()">X</div>

        <div class="innercontent">
            <!-- <svg
            width="16"
            height="16"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
            >
            <path
                d="M10 0C4.5 0 0 4.5 0 10C0 15.5 4.5 20 10 20C15.5 20 20 15.5 20 10C20 4.5 15.5 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z"
            />
            </svg> -->

            <svg
            width="16"
            height="16"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
            >
            <path
                d="M10 0C15.53 0 20 4.47 20 10C20 15.53 15.53 20 10 20C4.47 20 0 15.53 0 10C0 4.47 4.47 0 10 0ZM13.59 5L10 8.59L6.41 5L5 6.41L8.59 10L5 13.59L6.41 15L10 11.41L13.59 15L15 13.59L11.41 10L15 6.41L13.59 5Z"
            />
            </svg>

            <span> ' . $_GET['error'] . '</span>
        </div>
            </div>
        </div>
    </div>';
}
if (isset($_GET['success'])) {
    echo '<div class="fullcontainerToast">
    <div class="toastifier">
        <div class="toastifierContent successToast ">
        <div class="cross" onclick="crossClk()">X</div>

        <div class="innercontent">
            <svg
            width="16"
            height="16"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
            >
            <path
                d="M10 0C4.5 0 0 4.5 0 10C0 15.5 4.5 20 10 20C15.5 20 20 15.5 20 10C20 4.5 15.5 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z"
            />
            </svg>
            <span> ' . $_GET['success'] . '</span>
        </div>
            </div>
        </div>
    </div>';
}



include "../common/backendConnector.php";
// db connection in (lms) db
$con = mysqli_connect($host, $dbUserName, $dbPassword, $database);
if (!$con) {
    die("DB connection failed");
}

// for get category content from db
$sqlFetchCat = "SELECT * FROM `category`";
$resFetchsub = mysqli_query($con, $sqlFetchCat);


if (isset($_GET['search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    // Escape the search value to prevent SQL injection
    $search = mysqli_real_escape_string($con, $search);

    // Check if the search value is set
    if (!empty($search)) {
        // Query with the search value
        $sqlFetch = "SELECT * FROM books WHERE bname LIKE '%$search%'";
        $resFetch = mysqli_query($con, $sqlFetch);
    }
}

// for get subcategory content from db
$sqlFetchSubcat = "SELECT * FROM `subcategory`";
$resFetchSubcat = mysqli_query($con, $sqlFetchSubcat);



// ============= Insertion Start ============

if (isset($_POST['addContent'])) {
    // Define the directory to store uploaded images
    $targetDir = "uploads/";
    // Check if the target directory exists, if not, create it
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Check if a file was uploaded
    if (isset($_FILES["image"])) {
        $file = $_FILES["image"];

        // Get the file name and extension
        $fileName = basename($file["name"]);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the file extension is allowed
        $allowedExtensions = array("jpg", "jpeg", "png");
        if (in_array($fileExtension, $allowedExtensions)) {
            $targetPath = $targetDir . uniqid() . "." . $fileExtension;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($file["tmp_name"], $targetPath)) {
                // File uploaded successfully

                // Get other form field values
                $bname = $_POST['bname'];
                $bauthor = $_POST['bauthor'];
                $bquantity = $_POST['bquantity'];
                $cid = $_POST['category'];
                $subcatid = $_POST['subcategory'];
                $bpublishdate = $_POST['bpublishdate'];
                $pubName = $_POST['pubname'];


                // Fetch category name from category table
                $catName = "";
                $sqlFetch = "SELECT * FROM `category` WHERE `id` = '$cid'";
                $res = mysqli_query($con, $sqlFetch);
                if (mysqli_num_rows($res) > 0) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        if ($row['id'] == $cid) {
                            $catName = $row['cname'];
                        }
                    }
                }

                // Fetch subcategory name from subcategory table
                $subcatName = "";
                $sqlSubFetch = "SELECT * FROM `subcategory` WHERE `id` = '$subcatid'";
                $resSub = mysqli_query($con, $sqlSubFetch);
                if (mysqli_num_rows($resSub) > 0) {
                    while ($ressubcat = mysqli_fetch_assoc($resSub)) {
                        if ($ressubcat['id'] == $subcatid)
                            $subcatName = $ressubcat['subcatname'];
                    }
                }

                $final_image_path = $fileFront . $targetPath;
                $checkDuplicate = "SELECT * FROM `books` WHERE `categoryName` = '$catName' AND `subcategoryName` = '$subcatName' AND `bname` = '$bname' AND `pubName`='$pubName'";
                $resDub = mysqli_query($con, $checkDuplicate);

                if (mysqli_num_rows($resDub) > 0) {
                    // Duplicate record found, redirect to the desired page
                    // echo "Dublicate Value";
                    header("Location: book.php" . '?error=Book Already Exists.');
                    exit;
                }
                // Insert data into the books table
                $sql = "INSERT INTO `books` (`bname`, `bauthor`, `bquantity`, `categoryid`, `subcategoryid`, `bpublishdate`, `categoryName`, `subcategoryName`, `bimage`, `pubName`) VALUES ('$bname', '$bauthor', '$bquantity', '$cid', '$subcatid', '$bpublishdate', '$catName', '$subcatName', '$final_image_path', '$pubName')";

                if (mysqli_query($con, $sql)) {
                    // Data inserted successfully
                    header("Location: " . $_SERVER['PHP_SELF'] . '?success=Insertion of Data success');
                } else {
                    // Error inserting data
                    echo "Error: " . mysqli_error($con);
                    header("Location: " . $_SERVER['PHP_SELF'] . '?error=Data Insertion Error.');
                }

                // Close the database connection
                mysqli_close($con);
            } else {
                // Failed to move the uploaded file
                echo "Error uploading image.";
                header("Location: " . $_SERVER['PHP_SELF'] . '?error=Image Upload Error.');
            }
        } else {
            // Invalid file type
            echo "Only JPG, JPEG, and PNG files are allowed.";
            header("Location: " . $_SERVER['PHP_SELF'] . '?error=Only JPG, JPEG, and PNG files are allowed.');
        }
    } else {
        // No file uploaded
        echo "No image file provided.";
        header("Location: " . $_SERVER['PHP_SELF'] . '?error=No Image Uploaded.');
    }
}



// for delete content
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sqlDel = "DELETE FROM `books` WHERE `id`='$id'";

    if (mysqli_query($con, $sqlDel)) {
        // echo "del success";
        header("Location: " . $_SERVER['PHP_SELF'] . '?success=Delete Success.');
    } else {
        // echo "Cannot Delete";
        header("location: " . $_SERVER['PHP_SELF'] . '?error=Failed to delete.');
    }
}




// ======================================Update the data in the database===========================================
$editId = 0;
$bid = "";
$catName = "";
$subcatname = "";
$bname = "";
$bauthor = "";
$pubName = "";
$bquantity = "";
$bimage = "";
$bpublishdate = "";

if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $editsql = "SELECT * FROM `books` WHERE `id` = $editId";
    $editresult = mysqli_query($con, $editsql);

    if (!$editresult) {
        echo "Book not found";
    } else {
        $book = mysqli_fetch_array($editresult);
        $bid = $book['id'];
        $catName = $book['categoryName'];
        $subcatname = $book['subcategoryName'];
        $bname = $book['bname'];
        $bauthor = $book['bauthor'];
        $pubName = $book['pubName'];
        $bquantity = $book['bquantity'];
        $bimage = $book['bimage'];
        $bpublishdate = $book['bpublishdate'];
    }
}

// ====================================== Update the data in the database =======================================
// if (isset($_POST['updateContent'])) {
//     // Define the directory to store uploaded images
//     $targetDir = "uploads/";
//     // Check if the target directory exists, if not, create it
//     if (!file_exists($targetDir)) {
//         mkdir($targetDir, 0777, true);
//     }

//     // Check if a file was uploaded
//     if (isset($_FILES["image"]) && $_POST["image"] != "") {
//         $file = $_FILES["image"];

//         // Get the file name and extension
//         $fileName = basename($file["name"]);
//         $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

//         // Check if the file extension is allowed
//         $allowedExtensions = array("jpg", "jpeg", "png");
//         if (in_array($fileExtension, $allowedExtensions)) {
//             $targetPath = $targetDir . uniqid() . "." . $fileExtension;

//             // Move the uploaded file to the target directory
//             if (move_uploaded_file($file["tmp_name"], $targetPath)) {
//                 // File uploaded successfully

//                 // Get other form field values
//                 $newBname = $_POST['bname'];
//                 $newBauthor = $_POST['bauthor'];
//                 $newPubName = $_POST['pubname'];
//                 $newBquantity = $_POST['bquantity'];
//                 $newCategoryId = $_POST['category']; // Updated category ID
//                 $newSubcategoryId = $_POST['subcategory']; // Updated subcategory ID
//                 $newBpublishdate = $_POST['bpublishdate'];
//                 $editId = $_POST['editId'];

//                 // Fetch category name from category table
//                 $newCatName = "";
//                 $sqlFetchCat = "SELECT * FROM `category` WHERE `id` = '$newCategoryId'";
//                 $resCat = mysqli_query($con, $sqlFetchCat);
//                 if (mysqli_num_rows($resCat) > 0) {
//                     while ($rowCat = mysqli_fetch_assoc($resCat)) {
//                         if ($rowCat['id'] == $newCategoryId) {
//                             $newCatName = $rowCat['cname'];
//                         }
//                     }
//                 }

//                 // Fetch subcategory name from subcategory table
//                 $newSubcatName = "";
//                 $sqlFetchSubcat = "SELECT * FROM `subcategory` WHERE `id` = '$newSubcategoryId'";
//                 $resSubcat = mysqli_query($con, $sqlFetchSubcat);
//                 if (mysqli_num_rows($resSubcat) > 0) {
//                     while ($rowSubcat = mysqli_fetch_assoc($resSubcat)) {
//                         if ($rowSubcat['id'] == $newSubcategoryId) {
//                             $newSubcatName = $rowSubcat['subcatname'];
//                         }
//                     }
//                 }

//                 // Delete the old image file
//                 if (!empty($bimage) && file_exists($bimage)) {
//                     unlink($bimage);
//                 }

//                 $final_image_path = $targetPath;


//                 // Update data in the books table
//                 $updatesql = "UPDATE `books` SET `bname`='$newBname', `bauthor`='$newBauthor', `pubName`='$newPubName', `bquantity`='$newBquantity', `categoryid`='$newCategoryId', `subcategoryid`='$newSubcategoryId', `bpublishdate`='$newBpublishdate', `categoryName`='$newCatName', `subcategoryName`='$newSubcatName', `bimage`='$final_image_path' WHERE `id`='$editId'";
//                 $resUpdate = mysqli_query($con, $updatesql);

//                 if ($resUpdate) {
//                     // Data updated successfully
//                     header("Location: " . $_SERVER['PHP_SELF'].'?success:Data Update Success.');
//                     exit;
//                 } else {
//                     // Error updating data
//                     echo "Update failed: " . mysqli_error($con);
//                     header("Location: " . $_SERVER['PHP_SELF'].'?reeeor:Data Update Failed.');
//                 }
//             } else {
//                 // Failed to move the uploaded file
//                 echo "Error uploading image.";
//                 header("Location: " . $_SERVER['PHP_SELF'].'?error:Image Update Failed.');
//             }
//         } else {
//             // Invalid file type
//             echo "Only JPG, JPEG, and PNG files are allowed.";
//             header("Location: " . $_SERVER['PHP_SELF'].'?error:Only JPG, JPEG, and PNG files are allowed.');
//         }
//     } else {
//         // No file uploaded, update other fields without changing the image
//         $newBname = $_POST['bname'];
//         $newBauthor = $_POST['bauthor'];
//         $newPubName = $_POST['pubname'];
//         $newBquantity = $_POST['bquantity'];
//         $newCategoryId = $_POST['category']; // Updated category ID
//         $newSubcategoryId = $_POST['subcategory']; // Updated subcategory ID
//         $newBpublishdate = $_POST['bpublishdate'];

//         // Fetch category name from category table
//         $newCatName = "";
//         $sqlFetchCat = "SELECT * FROM `category` WHERE `id` = '$newCategoryId'";
//         $resCat = mysqli_query($con, $sqlFetchCat);
//         if (mysqli_num_rows($resCat) > 0) {
//             while ($rowCat = mysqli_fetch_assoc($resCat)) {
//                 if ($rowCat['id'] == $newCategoryId) {
//                     $newCatName = $rowCat['cname'];
//                 }
//             }
//         }

//         // Fetch subcategory name from subcategory table
//         $newSubcatName = "";
//         $sqlFetchSubcat = "SELECT * FROM `subcategory` WHERE `id` = '$newSubcategoryId'";
//         $resSubcat = mysqli_query($con, $sqlFetchSubcat);
//         if (mysqli_num_rows($resSubcat) > 0) {
//             while ($rowSubcat = mysqli_fetch_assoc($resSubcat)) {
//                 if ($rowSubcat['id'] == $newSubcategoryId) {
//                     $newSubcatName = $rowSubcat['subcatname'];
//                 }
//             }
//         }

//         $checkDuplicate = "SELECT * FROM `books` WHERE `categoryName` = '$newCatName' AND `subcategoryName` = '$newSubcatName' AND `bname` = '$newBname' AND `pubName`='$newPubName'";
//         $resDub = mysqli_query($con, $checkDuplicate);

//         if (mysqli_num_rows($resDub) > 0) {
//             // Duplicate record found, redirect to the desired page
//             header("Location: book.php");
//             exit;
//         }

//         // Update data in the books table without changing the image
//         $updatesql = "UPDATE `books` SET `bname`='$newBname',
//          `bauthor`='$newBauthor', `pubName`='$newPubName', `bquantity`='$newBquantity', `categoryid`='$newCategoryId', `subcategoryid`='$newSubcategoryId', `bpublishdate`='$newBpublishdate', `categoryName`='$newCatName', `subcategoryName`='$newSubcatName' 
//          WHERE `id`='$editId'";
//         $resUpdate = mysqli_query($con, $updatesql);

//         if ($resUpdate) {
//             echo "sussess";
//             // Data updated successfully
//             header("Location: " . $_SERVER['PHP_SELF'].'?success:Update Success.');
//             exit;
//         } else {
//             // Error updating data
//             echo "Update failed: " . mysqli_error($con);
//             header("Location: " . $_SERVER['PHP_SELF'].'?error:Data Update Failed.');
//         }
//     }
// }


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // Your code for handling the POST request goes here
//     if ($_POST['bquantity']) {
//         $editId = $_POST['editId'];
//         $bookqn = $_POST['bquantity'];
//         $sql = "UPDATE books SET bquantity='$bookqn' WHERE id='$editId'";
//         $res = mysqli_query($con, $sql);
//     }
// }
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     if ($_POST['bpublishdate']) {
//         $editId = $_POST['editId'];
//         $bpubdate = $_POST['bpublishdate'];
//         $sql = "UPDATE books SET bpublishdate='$bpubdate' WHERE id='$editId'";
//         $res = mysqli_query($con, $sql);
//     }
// }

function uploadImage($targetDir)
{
    if (isset($_FILES["image"]) && $_FILES["image"]["name"]) {
        $file = $_FILES["image"];
        $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowedExtensions = array("jpg", "jpeg", "png");

        if (in_array($fileExtension, $allowedExtensions)) {
            $targetPath = $targetDir . uniqid() . "." . $fileExtension;

            if (move_uploaded_file($file["tmp_name"], $targetPath)) {
                return $targetPath; // Return the uploaded file path
            } else {
                throw new Exception("Error uploading image.");
            }
        } else {
            throw new Exception("Only JPG, JPEG, and PNG files are allowed.");
        }
    }

    return null; // No file uploaded
}


function updateBook($con, $editId, $imagePath)
{
    $newBname = $_POST['bname'];
    $newBauthor = $_POST['bauthor'];
    $newPubName = $_POST['pubname'];
    $newBquantity = $_POST['bquantity'];
    $newCategoryId = $_POST['category']; // Updated category ID
    $newSubcategoryId = $_POST['subcategory']; // Updated subcategory ID
    $newBpublishdate = $_POST['bpublishdate'];

    // Fetch category name from category table
    $newCatName = getCategoryName($con, $newCategoryId);

    // Fetch subcategory name from subcategory table
    $newSubcatName = getSubcategoryName($con, $newSubcategoryId);

    // Check for duplicate records
    // if (!isDuplicateRecord($con, $newCatName, $newSubcatName, $newBname, $newPubName)) {
        // Check if a new image was uploaded
        if ($imagePath) {
            $newImgLink = 'http://localhost/lms/admin/'.$imagePath;
            $updatesql = "UPDATE `books` SET `bname`='$newBname',
                `bauthor`='$newBauthor', `pubName`='$newPubName', `bquantity`='$newBquantity',
                `categoryid`='$newCategoryId', `subcategoryid`='$newSubcategoryId',
                `bpublishdate`='$newBpublishdate', `categoryName`='$newCatName',
                `subcategoryName`='$newSubcatName', `bimage`='$newImgLink'
                WHERE `id`='$editId'";
        } else {
            // No new image uploaded, retain the old image URL
            $updatesql = "UPDATE `books` SET `bname`='$newBname',
                `bauthor`='$newBauthor', `pubName`='$newPubName', `bquantity`='$newBquantity',
                `categoryid`='$newCategoryId', `subcategoryid`='$newSubcategoryId',
                `bpublishdate`='$newBpublishdate', `categoryName`='$newCatName',
                `subcategoryName`='$newSubcatName'
                WHERE `id`='$editId'";
        }

        if (mysqli_query($con, $updatesql)) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?success=Update Success.');
            exit;
        } else {
            throw new Exception("Error updating data: " . mysqli_error($con));
        }
    // } else {
    //     header("Location: book.php"); // Duplicate record found, redirect
    //     exit;
    // }
}


function getCategoryName($con, $categoryId)
{
    $sqlFetchCat = "SELECT `cname` FROM `category` WHERE `id` = '$categoryId'";
    $resCat = mysqli_query($con, $sqlFetchCat);

    if ($rowCat = mysqli_fetch_assoc($resCat)) {
        return $rowCat['cname'];
    }

    return "";
}

function getSubcategoryName($con, $subcategoryId)
{
    $sqlFetchSubcat = "SELECT `subcatname` FROM `subcategory` WHERE `id` = '$subcategoryId'";
    $resSubcat = mysqli_query($con, $sqlFetchSubcat);

    if ($rowSubcat = mysqli_fetch_assoc($resSubcat)) {
        return $rowSubcat['subcatname'];
    }

    return "";
}

function isDuplicateRecord($con, $newCatName, $newSubcatName, $newBname, $newPubName)
{
    $checkDuplicate = "SELECT * FROM `books` WHERE `categoryName` = '$newCatName'
        AND `subcategoryName` = '$newSubcatName' AND `bname` = '$newBname'
        AND `pubName`='$newPubName'";
    $resDub = mysqli_query($con, $checkDuplicate);

    return mysqli_num_rows($resDub) > 0;
}

if (isset($_POST['updateContent'])) {
    $targetDir = "uploads/";

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $imagePath = uploadImage($targetDir);
    $editId = $_POST['editId'];
    updateBook($con, $editId, $imagePath);
}




// ================================ for pagination (start) ==========================================
$querytotalnumberROw = "SELECT COUNT(*) as total FROM books";
$resultRowNum = mysqli_query($con, $querytotalnumberROw);
$rowNumbers = mysqli_fetch_assoc($resultRowNum);
$totalRowNumber = $rowNumbers['total'];

// for total page 
$recordsPerPage = 10;
$totalPages = ceil($totalRowNumber / $recordsPerPage);

// my current page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

$offset = ($currentPage - 1) * $recordsPerPage;


// for get Book content from db
$sqlFetch = "SELECT * FROM books ORDER BY id DESC LIMIT $offset, $recordsPerPage";
$resFetch = mysqli_query($con, $sqlFetch);



// for searching
// Retrieve the search value from the GET request]
if (isset($_GET['search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Escape the search value to prevent SQL injection
    $search = mysqli_real_escape_string($con, $search);

    // Check if the search value is set
    if (!empty($search)) {
        // Query with the search value
        $sqlFetch = "SELECT * FROM books WHERE bname LIKE '%$search%'";
        $resFetch = mysqli_query($con, $sqlFetch);
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books</title>
    <link rel="stylesheet" href="./sidestyles.css">
    <link rel="stylesheet" href="../CSS/globalssss.css">
    <link rel="stylesheet" href="./CSS/model.css">
    <link rel="stylesheet" href="./CSS/books.css">
    <style>
        select {
            padding: 10px;
            border: 1px solid #555;
            border-radius: 4px;
            outline: none;
            cursor: pointer !important;
            font-size: 17px !important;
            gap: 10px;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        #contentModal {
            min-height: 400px;
            width: 900px;
            margin: auto;
            padding: 50px 0px;
        }

        #nameTab {
            width: 20%;
        }

        #pbdate input {
            width: 100%;
        }

        #form_main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        #form_left {
            width: 900px;
            margin-right: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            justify-content: center;
        }

        #form_right {
            height: 300px;
            width: 900px;
            margin-right: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            justify-content: center;
        }

        input {
            outline: none;
        }
    </style>
</head>

<body>
    <?php include "./sideNav.php"; ?>

    <div id="content">
        <div id="semicontent">
            <div id="maincontent">
                <div class="contentTable">
                    <button id="modalOpen">Add Books</button>
                    <table>
                        <tr>
                            <th id="snTab">S.N</th>
                            <th id="nameTab">Books Name</th>
                            <th>Category Name</th>
                            <th>Sub-Category Name</th>
                            <th>Author</th>
                            <th>Publication</th>
                            <th>Quantity</th>
                            <th>Image</th>
                            <th>Publish Date</th>
                            <th colspan="2">Action</th>
                        </tr>
                        <?php
                        if (isset($_GET['page'])) {
                            $index = ($_GET['page'] - 1) * ($recordsPerPage) + 1;
                        } else {
                            $index = 1;
                        }
                        if (mysqli_num_rows($resFetch) > 0) {
                            while ($row = mysqli_fetch_assoc($resFetch)) {
                                echo "
                        <tr style='text-transform:capitalize'>
                            <td>" . $index . "</td>
                            <td>" . $row["bname"] . "</td>
                            <td>" . $row["categoryName"] . "</td>
                            <td>" . $row["subcategoryName"] . "</td>
                            <td>" . $row["bauthor"] . "</td>
                            <td>" . $row["pubName"] . "</td>
                            <td>" . $row["bquantity"] . "</td>
                            <td><img src='" . $row["bimage"] . "' height='30' width='30' style='border-radius: 0% 30% 10% 10%'></td>
                            <td>" . $row["bpublishdate"] . "</td>
                            <td>
                                <a href=\"./book.php?edit=" . $row["id"] . "\">
                                    <svg width='16' height='16' viewBox='0 0 25 24' xmlns='http://www.w3.org/2000/svg'>
                                        <path d='M22.5 8.75V7.5L15 0H2.5C1.1125 0 0 1.1125 0 2.5V20C0 21.3875 1.125 22.5 2.5 22.5H10V20.1625L20.4875 9.675C21.0375 9.125 21.7375 8.825 22.5 8.75ZM13.75 1.875L20.625 8.75H13.75V1.875ZM24.8125 13.9875L23.5875 15.2125L21.0375 12.6625L22.2625 11.4375C22.5 11.1875 22.9125 11.1875 23.1625 11.4375L24.8125 13.0875C25.0625 13.3375 25.0625 13.75 24.8125 13.9875ZM20.1625 13.5375L22.7125 16.0875L15.05 23.75H12.5V21.2L20.1625 13.5375Z' />
                                    </svg>
                                </a>
                            </td>
                            <td>
                                <a onclick='deleteBtnClk(" . $row["id"] . ")'>
                                    <svg width='16' height='16' viewBox='0 0 20 23' xmlns='http://www.w3.org/2000/svg'>
                                        <path d='M6.25 0V1.25H0V3.75H1.25V20C1.25 20.663 1.51339 21.2989 1.98223 21.7678C2.45107 22.2366 3.08696 22.5 3.75 22.5H16.25C16.913 22.5 17.5489 22.2366 18.0178 21.7678C18.4866 21.2989 18.75 20.663 18.75 20V3.75H20V1.25H13.75V0H6.25ZM6.25 6.25H8.75V17.5H6.25V6.25ZM11.25 6.25H13.75V17.5H11.25V6.25Z' />
                                    </svg>
                                </a>
                            </td>
                        </tr> ";
                                $index++;
                            }
                        }
                        ?>
                    </table>
                </div>
                <div class="pagination">
                    <?php
                    if ($currentPage > 1) {
                        echo '<a href="?page=' . ($currentPage - 1) . '" class="leftArrow">&laquo;</a>';
                    } else {
                        echo '<a class="leftArrow">&laquo;</a>';
                    }

                    for ($i = 1; $i <= $totalPages; $i++) {
                        $activeClass = ($currentPage == $i) ? 'activePage' : '';
                        echo '<a href="?page=' . $i . '" class="' . $activeClass . '">' . $i . '</a>';
                    }

                    if ($currentPage < $totalPages) {
                        echo '<a href="?page=' . ($currentPage + 1) . '" class="rightArrow">&raquo;</a>';
                    } else {
                        echo '<a class="rightArrow">&raquo;</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>


    <!-- for modal  -->
    <div id="modal">
        <div id="background">
            <div id="contentModal">
                <!-- For close button -->
                <button id="crossModal">X</button>
                <div class="formContent">
                    <form action="./book.php" method="post" enctype="multipart/form-data">
                        <div id="form_main">
                            <div id="form_left">
                                <div>
                                    <label>Category</label>
                                    <select name="category" id="category">
                                        <?php
                                        if (mysqli_num_rows($resFetchsub) > 0) {
                                            while ($rowcat = mysqli_fetch_assoc($resFetchsub)) {
                                                $selected = ($catId == $rowcat['id']) ? 'selected' : '';
                                                echo "<option " . $selected . " value=" . $rowcat['id'] . " onclick='categoryClick(" . $rowcat['id'] . ")' >" . $rowcat['cname'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label>Sub Category</label>
                                    <select name="subcategory" id="subcategory">

                                        <option value="0">Select</option>

                                    </select>
                                </div>
                                <input type="hidden" name="editId" value="<?php echo $editId ?>" id="editId">
                                <label for="bname">Book Name</label>
                                <input type="text" name="bname" value="<?php echo $bname ?>" id=" bname"
                                    placeholder="Book Name" required>
                                <label for="bauthor">Author Name</label>
                                <input type="text" name="bauthor" value="<?php echo $bauthor ?>" id=" bauthor"
                                    placeholder="Author Name" required>
                            </div>
                            <div id="form_right">
                                <label for="pubname">Publication</label>
                                <input type="text" name="pubname" value="<?php echo $pubName ?>" id="pubname"
                                    placeholder="Publication Name" required>
                                <label for="bquantity">Quantity</label>
                                <input type="number" name="bquantity" value="<?php echo $bquantity ?>" id=" bquantity"
                                    placeholder="Quantity" required>
                                <input id="bimage" type="file" name="image" accept=".jpg, .png, .jpeg"
                                    value="<?php echo $bimage ?>" <?php echo isset($_GET['edit']) ? '' : 'required'; ?>>
                                <div id="pbdate">
                                    <input type="date" name="bpublishdate" value="<?php echo $bpublishdate ?>"
                                        id=" bpublishdate" required>
                                </div>
                                <div class="formButtons">
                                    <?php
                                    if (intval($editId) > 0) {
                                        echo "<button type='submit' name='updateContent'>Update</button>";
                                    } else {
                                        echo "<button type='submit' name='addContent'>Add</button>";
                                    }
                                    ?>
                                    <button type="reset">Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div id="confirmModal">
        <div id="confirmModalContent">
            <div class="actualCardConfirm">
                <div class="headermodalCon">
                    <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M8.9443 12.9809L10.944 13.0181L10.9067 15.0178L8.90705 14.9805M9.09332 4.98225L11.093 5.01951L10.9812 11.0185L8.98156 10.9812M9.81375 19.9983C11.1267 20.0227 12.4317 19.7883 13.6541 19.3085C14.8765 18.8286 15.9924 18.1127 16.9381 17.2016C18.8481 15.3615 19.9489 12.838 19.9983 10.1863C20.0477 7.53457 19.0417 4.97185 17.2016 3.06188C16.2904 2.11616 15.202 1.35916 13.9983 0.834098C12.7946 0.30904 11.4993 0.0262068 10.1863 0.00174628C7.53457 -0.0476539 4.97185 0.958355 3.06188 2.79846C1.15191 4.63857 0.0511458 7.16204 0.0017456 9.81375C-0.0227149 11.1267 0.211676 12.4317 0.691537 13.6541C1.1714 14.8765 1.88733 15.9924 2.79846 16.9381C3.70959 17.8839 4.79807 18.6409 6.00175 19.1659C7.20544 19.691 8.50076 19.9738 9.81375 19.9983Z" />
                    </svg>
                    <h3>Confirmation</h3>
                </div>
                <p>Are you sure you want to delete this content?</p>
                <div id="confirmModalButtons">
                    <button onclick="cancelDelete()">Cancel</button>
                    <a id="deleteLink" href="#">Delete</a>
                </div>
            </div>
        </div>
    </div>



    <script>
        const modal = document.getElementById("modal");
        const modalOpen = document.getElementById("modalOpen");
        const crossModal = document.getElementById("crossModal");
        const params = new URLSearchParams(window.location.search);
        let editParameter = Number(params.get('edit'));
        if (editParameter > 0) {
            modal.style.display = "block";
        }
        const urlWithoutParams = window.location.origin + window.location.pathname;

        modalOpen.addEventListener('click', () => {
            modal.style.display = "block";
        })
        crossModal.addEventListener('click', () => {
            modal.style.display = "none";
            window.location.href = urlWithoutParams;
        });

        const fetchSubData = async () => {
            try {
                const response = await fetch('http://localhost/lms/server/subcategorydata.php');
                if (response.ok) {
                    const data = await response.json();
                    // console.log(data);
                    return (data);
                } else {
                    console.error('Error:', response.status);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        };

        fetchSubData();
        const category = document.getElementById("category");
        let changedValue = category.value;
        category.addEventListener("change", async (e) => {
            changedValue = e.target.value;
            const subcatdata = await (fetchSubData());
            const filterData = subcatdata.filter((e) => Number(e.cId) === Number(changedValue))
            subcategoryHTML(filterData);
        });

        const subcategoryHTML = (data) => {
            const subcategoryId = document.getElementById("subcategory");
            let htmlContent = "";
            if (data.length > 0) {

                for (let i = 0; i < data.length; i++) {
                    htmlContent += `<option value="${data[i].id}">${data[i].subcatname}</option>`
                }
            }
            else {
                htmlContent = `<option value="0">No SubCategory</option>`
            }
            subcategoryId.innerHTML = htmlContent;
        }
        const initialFilter = async (id) => {
            const subcatdata = await (fetchSubData());
            const filterData = subcatdata.filter((e) => Number(e.cId) === Number(id))
            subcategoryHTML(filterData);
        }

        window.onload = () => initialFilter(category.value);


        // For Popup Notification

        const fullcontainerToast = document.querySelectorAll(".fullcontainerToast");
        setTimeout(() => {
            for (let i = 0; i < fullcontainerToast.length; i++) {
                fullcontainerToast[i].style.right = "0px";
                document.body.style.overflow = "hidden";
            }
        }, 200);
        setInterval(() => {
            closeModal(); // Call the closeModal function
        }, 2000);
        const crossClk = () => {
            closeModal(); // Call the closeModal function
        };

        const closeModal = () => {
            for (let i = 0; i < fullcontainerToast.length; i++) {
                fullcontainerToast[i].style.right = "-700px";
                window.location.reload();
            }
        };
        if (window.location.search.includes('error') || window.location.search.includes('success') || window.location.search.includes('warning')) {
            history.replaceState({}, document.title, window.location.pathname);
        }
        document.body.style.overflowX = "hidden";




        // Function to cancel the deletion and hide the confirmation modal
        function cancelDelete() {
            const confirmModal = document.getElementById('confirmModal');
            confirmModal.style.display = 'none';
            window.location.reload()
        }

        const deleteBtnClk = (id) => {
            // console.log(id)
            const confirmModal = document.getElementById("confirmModal")
            const deleteLink = document.getElementById("deleteLink")
            confirmModal.style.display = "block"
            deleteLink.setAttribute("href", `./book.php?delete=${id}`)
        }
        document.addEventListener("click", (elm) => {
            if (elm.target.id === "confirmModalContent") {
                cancelDelete();
            }
        });
    </script>
</body>

</html>