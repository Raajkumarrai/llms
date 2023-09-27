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


// for delete content
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sqlDel = "DELETE FROM `users` WHERE `id`='$id'";

    if (mysqli_query($con, $sqlDel)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?success=Delete Success');
        exit();
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . '?error=Delete Failed');
    }
}

if (isset($_GET['block'])) {
    $id = $_GET['block'];
    $sqlDel = "UPDATE `users` SET `restriction`='1' WHERE `id`='$id'";
    $res = mysqli_query($con, $sqlDel);
    if ($res) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?success=Restriction Success');
    }
}


// ================================ for pagination (start) ==========================================
$querytotalnumberROw = "SELECT COUNT(*) as total FROM users";
$resultRowNum = mysqli_query($con, $querytotalnumberROw);
$rowNumbers = mysqli_fetch_assoc($resultRowNum);
$totalRowNumber = $rowNumbers['total'];

// for total page 
$recordsPerPage = 10;
$totalPages = ceil($totalRowNumber / $recordsPerPage);

// my current page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

$offset = ($currentPage - 1) * $recordsPerPage;


$sqlFetch = "SELECT * FROM users  WHERE `restriction` = '0' ORDER BY id DESC LIMIT $offset, $recordsPerPage";
$resFetch = mysqli_query($con, $sqlFetch);


// ================================ for Search ==========================================

if (isset($_GET['search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    // Escape the search value to prevent SQL injection
    $search = mysqli_real_escape_string($con, $search);

    // Check if the search value is set
    if (!empty($search)) {
        // Query with the search value
        $sqlFetch = "SELECT * FROM users WHERE name LIKE '%$search%'";
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
    <title>Members</title>
    <link rel="stylesheet" href="./sidestyles.css">
    <link rel="stylesheet" href="./CSS/messagemodel.css">
    <link rel="stylesheet" href="../CSS/globalssss.css">

    <style>
        #action,
        #snTab {
            max-width: 30px;
        }
    </style>
</head>

<body>
    <?php include "./sideNav.php"; ?>


    <div id="content">
        <div id="semicontent">
            <div id="maincontent">
            </div>
            <div class="contentTable">
                <h2> Members List of LMS:-</h2><br>
                <table>
                    <tr>
                        <th id='snTab'>S.N</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th colspan='2' id='action'>Action</th>
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
                        <tr>
                            <td>" . $index . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["email"] . "</td>
                            <td>" . $row["phnumber"] . "</td>
                            <td>
                            <a href='?block=" . $row["id"] . "'>
                            <svg width='17' height='17' viewBox='0 0 21 21' fill='none' xmlns='http://www.w3.org/2000/svg'>
                            <path d='M10.2195 0.71106C15.7495 0.71106 20.2195 5.18106 20.2195 10.7111C20.2195 16.2411 15.7495 20.7111 10.2195 20.7111C4.68945 20.7111 0.219452 16.2411 0.219452 10.7111C0.219452 5.18106 4.68945 0.71106 10.2195 0.71106ZM13.8095 5.71106L10.2195 9.30106L6.62945 5.71106L5.21945 7.12106L8.80945 10.7111L5.21945 14.3011L6.62945 15.7111L10.2195 12.1211L13.8095 15.7111L15.2195 14.3011L11.6295 10.7111L15.2195 7.12106L13.8095 5.71106Z' fill='black'/>
                            </svg>
                        </a>
                            </td>
                            <td>
                            <a onclick='deleteBtnClk(" . $row["id"] . ")'>
                                    <svg width='14' height='16' viewBox='0 0 20 23' xmlns='http://www.w3.org/2000/svg'>
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
            <!-- =======================pagination============================ -->
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
            const confirmModal =document.getElementById("confirmModal")
            const deleteLink =document.getElementById("deleteLink")
            confirmModal.style.display = "block"
            deleteLink.setAttribute("href", `./members.php?delete=${id}`)
        }
        document.addEventListener("click", (elm)=>{
            if(elm.target.id === "confirmModalContent"){
                cancelDelete();
            }
        });
    </script>
</body>

</html>