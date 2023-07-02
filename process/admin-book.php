<?php
include_once('../config.php');
require_once('../classes/masterclass.php');
session_start();
$url='./admin/book.php';
$book_obj = new MasterClass('books');
$book_genres = new MasterClass('book_genres');


// create book 
if(isset($_POST['book_title'])){
    $book_title = $_POST['book_title'];
    $genres = $_POST['genres'];
    $author_id = $_POST['author_id'];
    $publisher_id = $_POST['publisher_id'];
    $book_language = $_POST['book_language'];
    $publish_year = $_POST['publish_year'];
    if (isset($_POST['book_rating'])) {
        $book_rating = $_POST['book_rating'];
    } else {
        $book_rating = 0;
    }
    $book_summary = $_POST['book_summary'];
    $book_page = $_POST['book_page'];
    $book_type = $_POST['book_type'];
    $book_image = NAN;
    $book_pdf = NAN;
    
    
    $extist = $book_obj->exists("book_title", $book_title);
    print_r($genres);
    echo "extist " . $extist;
    
    
    if ($extist == 0) {
        if (isset($_FILES['book_image'])) {
            $errors = array();
            $file_image = $_FILES['book_image'];
            $target_dir = base_app . "/media/uploads/books_image/";
            $file_size = $_FILES['book_image']['size'];
            $file_ext = explode("/", strtolower($file_image['type']));
            $extensions = array("jpeg", "jpg", "png");
    
            if (in_array($file_ext[1], $extensions, true) != 1) {
                $errors[] = "extension not allowed, please choose a JPEG or PNG file.";
                
            }
    
    
            $target_path = $target_dir . basename($_FILES['book_image']['name']);
    
            if (empty($errors) == true) {
                if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_path)) {
                    // echo "File uploaded successfully!";
                    $_SESSION["message"] = "File uploaded successfully!";
                    
                } else {
                     $_SESSION["error"] ="Sorry, file not uploaded, please try again!";

                    // echo "Sorry, file not uploaded, please try again!";
                }
            } else {
                // print_r($errors);
            $_SESSION["error"] = $errors[0];


            }
            $book_image = "/media/uploads/books_image/" . basename($_FILES['book_image']['name']);
    
        }
        if (isset($_FILES['book_pdf'])) {
            $errors = array();
            $target_dir = base_app . "/media/uploads/books_pdf/";
            $file_size = $_FILES['book_pdf']['size'];
            $file_pdf = $_FILES['book_pdf'];
            $extensions = array("pdf");
            $file_ext = explode("/", strtolower($file_pdf['type']));
            $target_path = $target_dir . basename($_FILES['book_pdf']['name']);
    
            if (in_array($file_ext[1], $extensions, true) != 1) {
                $errors[] = "extension not allowed, please choose a PDF file.<br/>";
            }
            if (empty($errors) == true) {
                if (move_uploaded_file($_FILES['book_pdf']['tmp_name'], $target_path)) {
                    // echo "File uploaded successfully!</br>";
                    $_SESSION["message"] = "File uploaded successfully!";
                    
                } else {
                    $_SESSION["error"] ="Sorry, file not uploaded, please try again!";
                    // echo "Sorry, file not uploaded, please try again! <br/>";
                }
            } else {
                // print_r($errors);
                $_SESSION["error"] ="extension not allowed, please choose a PDF file.";

            }
    
            $book_pdf = "/media/uploads/books_pdf/" . basename($_FILES['book_pdf']['name']);
            ;
    
        }
        $params = [
            "book_title" => $book_title,
            "book_summary" => $book_summary,
            "author_id" => $author_id,
            "book_language" => $book_language,
            "total_pages" => $book_page,
            "rating" => $book_rating,
            "book_type" => $book_type,
            "publisher_id" => $publisher_id,
            "publish_year" => $publish_year,
            "book_image" => $book_image,
            "book_pdf" => $book_pdf
        ];
        $result = $book_obj->create($params);
        // print_r($result);
        if ($result['status'] == 1) {
            $_SESSION["message"] = "Book is created successfully";
    
            foreach ($genres as $value) {
                $genrecountparam = [
                    "genre_id" => $value
                ];
                $count_genres = $book_genres->count($genrecountparam) + 1;
                $genres_param = [
                    "book_id" => $result['last_id'],
                    "genre_id" => $value,
                    "counter" => $count_genres
                ];
    
                // echo "Genres count: " . $count_genres;
                $book_genres->create($genres_param);
            }
            redirect($url);
    
            
        } else {
            $_SESSION["error"] = "Something went wrong";
            redirect($url);
        }
    
    
    } else {
        $_SESSION["error"] = "Book is already Exist here !";
        redirect($url);
    
    }
    
}
//end create book

// delete book 
if(isset($_POST['delete_id'])){
    $book_id=$_POST['delete_id'];
    $extist = $book_obj->exists("book_title", $book_title);
    if ($extist==1){
        $get_book=$book_obj->get('book_id',$book_id);
        $book_image_url=base_app.$get_book['book_image'];
        $book_pdf_url=  base_app.$get_book['book_pdf'];
        try {
            unlink($book_image_url);
            unlink($book_pdf_url);
        } catch (\Throwable $th) {
            echo $th->getMessage(); 
        }
        echo "book".$book_id;
        $Bresult=$book_obj->delete('book_id',$book_id);
        // print_r($Bresult);
        if ($bresult['status'] == 1) {
            $_SESSION["message"] = "Book is deleted successfully";
    
        try {
         
            $GBresult=$book_genres->delete('book_id',$book_id);
            redirect($url);

            // print_r($GBresult);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        redirect($url);

        }
    }

    }
    else{
        $_SESSION["error"] = "Book is not available !";
        redirect($url);

    }
}

?>