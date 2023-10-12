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

$categoryName = "";

if (isset($_GET['category'])) {
    $categoryName = $_GET['category'];
}


if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session
}
include "./common/backendConnector.php";

// db connection in (lms) db
$con = mysqli_connect($host, $dbUserName, $dbPassword, $database);
if (!$con) {
    die("DB connection failed");
}

// for get Book content from db
$sqlFetchAll = "SELECT * FROM `books` ";
$res = mysqli_query($con, $sqlFetchAll);

$sqlcategory = "SELECT * FROM `category`";
$rescategory = mysqli_query($con, $sqlcategory);

if (isset($_GET['search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    // Escape the search value to prevent SQL injection
    $search = mysqli_real_escape_string($con, $search);

    // Check if the search value is set
    if (!empty($search)) {
        // Query with the search value
        $sqlFetch = "SELECT * FROM books WHERE bname LIKE '%$search%' OR bauthor LIKE '%$search%'";
        $res = mysqli_query($con, $sqlFetch);
    }
}

// for searching
// Retrieve the search value from the GET request]
if (isset($_GET['search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Escape the search value to prevent SQL injection
    $search = mysqli_real_escape_string($con, $search);

    // Check if the search value is set
    if (!empty($search)) {
        // Query with the search value
        $sqlNote = "SELECT * FROM books WHERE bname LIKE '%$search%'";
        $res = mysqli_query($con, $sqlNote);
    }
}

if (isset($_GET['category'])) {
    $cat = $_GET['category'];
    if ($cat != 'all') {
        $sqlFetchAll = "SELECT * FROM `books` where categoryName = '$cat'";
        $res = mysqli_query($con, $sqlFetchAll);
    }
}
if (isset($_POST['preorder'])) {
    $userId = $_SESSION['id'];
    $OrderQueryPrev = "SELECT * FROM bookorder WHERE userid = $userId AND isreturn = 0";
    $resOrderQueryPrev = mysqli_query($con, $OrderQueryPrev);
    if (mysqli_num_rows($resOrderQueryPrev) < 6) {
        $bookid = $_POST['book_id'];
        $name = $_SESSION['name'];
        $userid = $_SESSION['id'];
        $isreturn = 0;
        $istaken = 0;

        $isExist = "SELECT * FROM bookorder WHERE userid = '$userid' AND bookid='$bookid' AND isreturn=0";
        $resisExist = mysqli_query($con, $isExist);

        if (mysqli_num_rows($resisExist) > 0) {
            // die("You already Request");
            header("Location: " . $_SERVER['PHP_SELF'] . '?error=You Have Already Request.');
            exit();
        }

        $bookQuery = "SELECT * FROM books WHERE id = $bookid";
        $resBook = mysqli_query($con, $bookQuery);

        // Check if the query executed successfully
        if ($resBook) {
            if (mysqli_num_rows($resBook) < 1) {
                // die("Book not found");
                header("Location: " . $_SERVER['PHP_SELF'] . '?error=Book not found.');
                exit();
            }

            $rowBook = mysqli_fetch_assoc($resBook);

            // Insert query
            $sqlOrder = "INSERT INTO bookorder (`username`, `userid`, `bookid`, `isreturn`, `istaken`) VALUES ('$name', '$userid', '$bookid', '$isreturn', '$istaken')";
            $resBook = mysqli_query($con, $sqlOrder);

            if ($resBook) {
                $newQuantity = intval($rowBook['bquantity']) - 1;
                $sqlBook = "UPDATE books SET bquantity = '$newQuantity' WHERE id = '$bookid'";
                $resBook = mysqli_query($con, $sqlBook);

                if ($resBook) {
                    header("Location: " . $_SERVER['PHP_SELF'] . '?success=Book Successfully Ordered.');
                    exit();
                }
            } else {
                // Handle insertion failure
                echo "Record not inserted: " . mysqli_error($con);
                exit();
            }
        } else {
            // Handle query execution failure
            echo "Query failed: " . mysqli_error($con);
            exit();
        }
    } else {
        // echo "You cannot order please wait.";
        header("Location: " . $_SERVER['PHP_SELF'] . '?error=You cannot order please wait.');
        exit();
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/home.css">
    <link rel="stylesheet" href="./CSS/allBook.css">
    <link rel="stylesheet" href="./CSS/globals.css">
    <title>LMS Home</title>
</head>
<style>
     body{
            max -width: 1600px;
            overflow-x: hidden;
        }
        #search-btn {
            background-color: transparent;
            border: none;
        }
</style>
<body>
    <?php include "./common/header.php"; ?>
    <!-- Main container  -->
    <div class="container">

    <div class="all-books-nav">
            <div class="all-books">
                <h3>All Books:</h3>
            </div>
            <form action="">
                <div class="search-box">
                    <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : '' ?>" placeholder="Search..." id="search-box" autocomplete="off">
                    <div>
                        <svg width="3" height="25" viewBox="0 0 1 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="0.5" y1="23.0217" x2="0.5" stroke="#757575" />
                        </svg>
                    </div>
                    <div>
                        <button id="search-btn">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.42857 0C9.39875 0 11.2882 0.782651 12.6814 2.17578C14.0745 3.56891 14.8571 5.45839 14.8571 7.42857C14.8571 9.26857 14.1829 10.96 13.0743 12.2629L13.3829 12.5714H14.2857L20 18.2857L18.2857 20L12.5714 14.2857V13.3829L12.2629 13.0743C10.96 14.1829 9.26857 14.8571 7.42857 14.8571C5.45839 14.8571 3.56891 14.0745 2.17578 12.6814C0.782651 11.2882 0 9.39875 0 7.42857C0 5.45839 0.782651 3.56891 2.17578 2.17578C3.56891 0.782651 5.45839 0 7.42857 0ZM7.42857 2.28571C4.57143 2.28571 2.28571 4.57143 2.28571 7.42857C2.28571 10.2857 4.57143 12.5714 7.42857 12.5714C10.2857 12.5714 12.5714 10.2857 12.5714 7.42857C12.5714 4.57143 10.2857 2.28571 7.42857 2.28571Z" fill="#757575" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>


        </div>
        <div>

        </div>

        <!-- Books and books Details -->
        <div class="books-details">
            <?php

            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "
            <div class='book-details-list'>
                <div class='book-img'>
                    <img src='" . $row["bimage"] . "' alt='Books'>
                </div>
                <div class='details'>
                    <div class='book-name'>
                    <h3>" . $row["bname"] . "</h3>
                    <h4>" . 'Author: ' . $row["bauthor"] . "</h4>
                    <h4>" . 'Faculty: ' . $row["categoryName"] . "</h4>
                    <h4>" . 'Sem/Year: ' . $row["subcategoryName"] . "</h4>
                    <p>" . 'Publication: ' . $row["pubName"] . "</p>
                    </div>
                    <div class='date'>
                    <h5>" . $row["bpublishdate"] . "</h5>
                    </div>
                    <p>" . 'Quantity: ' . $row["bquantity"] . "</p>
                    <div class='pre-order-btn'>
                        <form action='./allBook.php' method='post'>
                            <input type='hidden' name='book_id' value=" . $row['id'] . " />
                            <button name='preorder' class='preorderBtn_sty' onclick='preOrder()'>Pre-Order</button>
                        </form>
                    </div>
                </div>
            </div>  ";
                }
            } else {
                echo "<div class='pageNotFound'>Books Not Found</div>";
            }
            ?>
        </div>
    </div>


        <?php include "./common/footer.php"; ?>

    </div>

    <script>
        const fullcontainerToast = document.querySelectorAll(".fullcontainerToast");
        setTimeout(() => {
            for (let i = 0; i < fullcontainerToast.length; i++) {
                fullcontainerToast[i].style.right = "0px";
                document.body.style.overflow = "hidden";
            }
        }, 200);
        setInterval(() => {
            closeModaltoster(); // Call the closeModaltoster function
        }, 2000);
        const crossClk = () => {
            closeModaltoster(); // Call the closeModaltoster function
        };

        const closeModaltoster = () => {
            for (let i = 0; i < fullcontainerToast.length; i++) {
                fullcontainerToast[i].style.right = "-700px";
                window.location.reload();
            }
        };
        if (window.location.search.includes('error') || window.location.search.includes('success') || window.location.search.includes('warning')) {
            history.replaceState({}, document.title, window.location.pathname);
        }
        document.body.style.overflowX = "hidden";



    </script>

</body>

</html>