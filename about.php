<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/homes.css">
    <title>LMS Home</title>
    <style>
main {
    margin: 70px 25px;
    padding: 20px;
    box-shadow: 1px 0 5px rgba(0, 0, 0, 0.2);
    background-color: #f0f0f0;
}

.about-section {
    display: flex;
    justify-content: space-between;
    padding: 20px;
}

.image-side {
    flex-basis: 40%;
}

.image-side img {
    max-width: 100%;
    height: auto;
}

.details-side {
    flex-basis: 55%;
}

.about-section h2,
.founders-section h2,
.services-section h2 {
    font-size: 2rem;
    margin-bottom: 15px;
}

.about-section p,
.founders-section ul,
.services-section p,
.services-section ul {
    font-size: 1.1rem;
    line-height: 1.6;
}

    </style>
</head>

<body>
    <?php include "./common/header.php"; ?>
    <main>
        <section class="about-section">
            <div class="image-side">
                <img src="your-image.jpg" alt="Library Image">
            </div>
            <div class="details-side">
                <h2>Who We Are</h2>
                <p>Welcome to the Library Management System! We are a dedicated team of book enthusiasts who are passionate about connecting readers with their favorite books. Our platform offers a seamless experience for managing, borrowing, and discovering a wide range of books.</p>
            </div>
        </section>
        <section class="founders-section">
            <h2>Created By</h2>
            <ul>
                <li>Raj Kumar Rai</li>
                <li>Nirmal Kharel</li>
            </ul>
        </section>
        <section class="services-section">
            <h2>Our Services</h2>
            <p>We offer a range of services to make your reading experience enjoyable and convenient:</p>
            <ul>
                <li>Book Catalog and Management</li>
                <li>Borrowing and Returning Books</li>
                <li>Online Reservations</li>
                <li>Notifications and Reminders</li>
            </ul>
        </section>
    </main>

</body>

</html>