<?php
// Configuration
define('DB_HOST', 'localhost:3306');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '1234');
define('DB_NAME', 'image_crud');
define('IMAGE_DIR', 'images/');

// Connect to database
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS images (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255),
  description TEXT,
  filename VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

function outputInformation() {

}


// Function to display images
function display_images() {
    global $conn;
    $sql = "SELECT * FROM images";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            ?>
            <div class="glass-card col-md-6">
                <h2><?php echo $row['title']; ?></h2>
                <img src="<?php echo IMAGE_DIR . $row['filename']; ?>" alt="<?php echo $row['title']; ?>">
                <p><?php echo $row['description']; ?></p>
                <p>
                    <a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
                    <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                </p>
            </div>
            <?php
        }
    } else {
        echo "No images found!";
    }
}

// Function to handle form submissions
function handle_form() {
    global $conn;
    if (isset($_POST['submit'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $filename = basename($_FILES['image']['name']);
        $target_dir = IMAGE_DIR;
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $uploadOk = 1;
        $message = "";

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image"]["size"] > 500000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // If everything is ok, try to upload file and insert into database
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO images (title, description, filename) VALUES ('$title', '$description', '$filename')";
                if ($conn->query($sql) === TRUE) {
                    $message = "Image uploaded and created successfully!";
                    $status = "success";
                } else {
                    $message = "Error: " . $sql . "<br>" . $conn->error;
                    $status = "error";
                }
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $status = "error";
            }
        } else {
            $status = "error";
        }

        // Output the result message with styling
        ?>
        <style>
            .message-container {
                max-width: 500px;
                margin: 50px auto;
                padding: 20px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 16px;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                text-align: center;
            }
            .message {
                font-size: 18px;
                margin-bottom: 20px;
                padding: 10px;
                border-radius: 8px;
            }
            .success {
                background-color: rgba(76, 175, 80, 0.1);
                color: #4CAF50;
            }
            .error {
                background-color: rgba(244, 67, 54, 0.1);
                color: #F44336;
            }
            .back-btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #2196F3;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                transition: background-color 0.3s ease;
            }
            .back-btn:hover {
                background-color: #1976D2;
            }
        </style>
        <div class="message-container">
            <div class="message <?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
            <a href="index.php" class="back-btn">Back to Gallery</a>
        </div>
        <?php
        // Prevent form resubmission on page refresh
        echo "<script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>";
    }
}

// Function to edit image
function edit_image() {
    global $conn;
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM images WHERE id=$id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        ?>
        <style>
            .edit-container {
                max-width: 500px;
                margin: 50px auto;
                padding: 30px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 16px;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                border: 1px solid rgba(255, 255, 255, 0.3);
            }
            .edit-form {
                display: flex;
                flex-direction: column;
            }
            .edit-form label {
                margin-bottom: 5px;
                color: #333;
                font-weight: bold;
            }
            .edit-form input[type="text"],
            .edit-form textarea {
                width: 100%;
                padding: 10px;
                margin-bottom: 20px;
                border: none;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.2);
                color: #333;
            }
            .edit-form textarea {
                height: 100px;
                resize: vertical;
            }
            .edit-form input[type="submit"],
            .go-back-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }
            .edit-form input[type="submit"] {
                background-color: #4CAF50;
                color: white;
            }
            .edit-form input[type="submit"]:hover {
                background-color: #45a049;
            }
            .go-back-btn {
                background-color: #f44336;
                color: white;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
            }
            .go-back-btn:hover {
                background-color: #d32f2f;
            }
            .form-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
        </style>
        <div class="edit-container">
            <h2>Edit Image</h2>
            <form action="?action=update&id=<?php echo $row['id']; ?>" method="post" class="edit-form">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                
                <div class="form-actions">
                    <input type="submit" name="submit" value="Update">
                    <a href="index.php" class="go-back-btn">Go Back</a>
                </div>
            </form>
        </div>
        <?php
    }
}
// Function to update image
function update_image() {
    global $conn;
    if (isset($_POST['submit'])) {
        $id = $_GET['id'];
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $sql = "UPDATE images SET title='$title', description='$description' WHERE id=$id";
        $result = $conn->query($sql);

        if ($result === TRUE) {
            $message = "Image updated successfully!";
            $status = "success";
        } else {
            $message = "Error updating image: " . $conn->error;
            $status = "error";
        }

        // Output the result message with styling
        ?>
        <style>
            .message-container {
                max-width: 500px;
                margin: 50px auto;
                padding: 20px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 16px;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                text-align: center;
            }
            .message {
                font-size: 18px;
                margin-bottom: 20px;
                padding: 10px;
                border-radius: 8px;
            }
            .success {
                background-color: rgba(76, 175, 80, 0.1);
                color: #4CAF50;
            }
            .error {
                background-color: rgba(244, 67, 54, 0.1);
                color: #F44336;
            }
            .back-btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #2196F3;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                transition: background-color 0.3s ease;
            }
            .back-btn:hover {
                background-color: #1976D2;
            }
        </style>
        <div class="message-container">
            <div class="message <?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
            <a href="index.php" class="back-btn">Back to Gallery</a>
        </div>
        <?php
        // Prevent form resubmission on page refresh
        echo "<script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>";
    }
}
// Function to delete image
function delete_image() {
    global $conn;
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']); // Ensure id is an integer
        $sql = "SELECT filename FROM images WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filename = $row['filename'];
            $file_path = IMAGE_DIR . $filename;

            if (file_exists($file_path) && unlink($file_path)) {
                $delete_sql = "DELETE FROM images WHERE id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $id);
                
                if ($delete_stmt->execute()) {
                    $message = "Image deleted successfully!";
                    $status = "success";
                } else {
                    $message = "Error deleting image from database: " . $conn->error;
                    $status = "error";
                }
                $delete_stmt->close();
            } else {
                $message = "Error deleting image file or file not found!";
                $status = "error";
            }
        } else {
            $message = "Image not found in database!";
            $status = "error";
        }
        $stmt->close();
    } else {
        $message = "No image ID provided for deletion!";
        $status = "error";
    }

    // Output the result message with styling
    ?>
    <style>
        .message-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }
        .message {
            font-size: 18px;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
        }
        .success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        .error {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #1976D2;
        }
    </style>
    <div class="message-container">
        <div class="message <?php echo $status; ?>">
            <?php echo $message; ?>
        </div>
        <a href="index.php" class="back-btn">Back to Gallery</a>
    </div>
    <?php
}

// Main program
?>
<!DOCTYPE html>
<html>
<head>
    <title>Glassmorphic Image CRUD</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f0f0;
            padding: 20px;
        }
        .glass-card {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 300px;
            height: 400px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .glass-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        .glass-card h2 {
            font-weight: bold;
            margin-top: 10px;
            font-size: 1.2em;
        }
        .glass-card p {
            margin-bottom: 10px;
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .glass-card .btn-group {
            display: flex;
            justify-content: space-between;
        }

        .glass-card .btn {
            width: 48%;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        
    </style>
</head>
<body>
    <?php

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'create':
                ?>
                <a href="index.php" class="btn btn-primary">Go Back</a>
                <h1>Create Image</h1>
                <form action="?action=create" method="post" enctype="multipart/form-data">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" class="form-control"><br><br>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" class="form-control"></textarea><br><br>
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" class="form-control"><br><br>
                    <input type="submit" name="submit" value="Create" class="btn btn-primary">
                </form>
                
                <?php
                handle_form();
                break;
            case 'edit':
                ?>
                <h1>Edit Image</h1>
                <?php
                edit_image();
                break;
            case 'update':
                update_image();
                break;
            case 'delete':
                delete_image();
                break;
        }
    } else {
        ?>
        <h1>Gallery CRUD</h1>
        <p><a href="?action=create" class="btn btn-primary">Create new image</a></p>
        <div class="row">
            <?php
            display_images();
            ?>
        </div>
        <?php
    }
    ?>
</body>
</html>