<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 */

function vendorTGW_ErrorHandler($code, $message, $file, $line)
{
    date_default_timezone_set("Asia/Kolkata");
    $timestamp = date("Y-m-d H:i:s");
    error_log("\r\n"."$ ".$timestamp." >> Error msg: ".$message.", in file ".$file.", at line number ".$line."\n",3,'logs/errors.txt');
}

class DbHandler
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // register another error handler
        set_error_handler(array($this, 'fatalErrorShutdownHandler'));
        date_default_timezone_set("Asia/Kolkata");
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function fatalErrorShutdownHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {

        // fatal error
        vendorTGW_ErrorHandler($errno, $errstr, $errfile, $errline);

    }

    //helper function that validates the api key
    public function apikeyValidity($api_key)
    {
        $stmt = $this->conn->prepare("
            CALL apikeyValidity(?, ?)
        ");
        $execution = $stmt->execute(array($api_key, BASE_URL));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);
        if($execution == false)
            return NULL;
        return $result;
    }

    //Function to get high precision timestamp
    public function millitime()
    {
        $microtime = microtime();
        $comps = explode(' ', $microtime);

        // Note: Using a string here to prevent loss of precision
        // in case of "overflow" (PHP converts it to a double)
        return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }

    /*
     * function that stores the error log
     */
    public function storeErrorDetails($api_name,$build,$parameters,$error_date,$error_time,$error_local,$exception)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO error_log(api_name,build,parameters,error_date,error_time,error_local,exception) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute(array($api_name,$build,$parameters,$error_date,$error_time,$error_local,$exception));

        if($result)
        {
            return true;
        }
        return null;
    }

    //function that auto rejects the bookings whose checkout date has lapsed
    public function autoRejection()
    {
        $current_date = date('Y-m-d');
        $stmt = $this->conn->prepare("
            CALL auto_rejection(?)
        ");

        $execution = $stmt->execute(array($current_date));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }




    //function that returns the list of loactions
    public function locations()
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT location_id location FROM homes
        ");
        //var_dump($stmt); die;
        $execution = $stmt->execute(array());
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //functions that returns the list of countries and their phonecodes
    public function phoneCodes()
    {
        $stmt = $this->conn->prepare("
            SELECT iso3 AS country_code, name AS country_name, CONCAT('+', phonecode) AS phonecode FROM country_master
              WHERE status = 1
        ");
        $execution = $stmt->execute(array());
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches all kinds of property and room types
    public function propertyRoomTypes()
    {
        $stmt = $this->conn->prepare("
            CALL property_room_location_types()
        ");
        $execution                = $stmt->execute(array());

        $result['locations']      = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['property_types']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['room_types']     = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['price_range']   = $stmt->fetch(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }


    //function that gives the min and the max price
    public function priceRange()
    {
        $stmt = $this->conn->prepare("
            CALL price_range()
        ");
        $execution = $stmt->execute(array());
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for filtering homes / rooms based on location, propert_type and room_type
    public function homeFiltering($location, $property_type, $room_type, $food, $price_low, $price_high, $people, $home_name, $offset)
    {
        $stmt = $this->conn->prepare("
            CALL home_filtering(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($location, $property_type, $room_type, $food, $price_low, $price_high, $people, $home_name, $offset, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for filtering homes / rooms based on location, propert_type and room_type
    public function homeFilteringTemp($location, $property_type, $room_type, $food, $price_low, $price_high, $people, $offset)
    {
        $stmt = $this->conn->prepare("
            CALL home_filtering(?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($location, $property_type, $room_type, $food, $price_low, $price_high, $people, $offset, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for image and thumb_image uploads against a particular home
    public function homeMultiImagesUploads($home_id, $final_db_path, $finalthumb_db_path)
    {
        $stmt = $this->conn->prepare("
            CALL multi_images_uploads(?, ?, ?)
        ");
        $execution = $stmt->execute(array($home_id, $final_db_path, $finalthumb_db_path));

        if($execution == false)
            return NULL;
        return $execution;
    }

    //helper function that fetches the profile image of a home
    public function home_images($home_id)
    {
        $stmt = $this->conn->prepare("
            SELECT home_image, home_thumb_image, gallary_images, gallary_thumb_images FROM homes WHERE home_id = ?
        ");
        $execution = $stmt->execute(array($home_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for home image upload(single)
    public function homeSingleImageUploads($home_id, $image_db_path, $thumbimage_db_path)
    {
        $stmt = $this->conn->prepare("
            CALL single_image_upload(?, ?, ?)
        ");
        $execution = $stmt->execute(array($home_id, $image_db_path, $thumbimage_db_path));

        if($execution == false)
            return NULL;
        return $execution;
    }

    //function for adding my place
    public function addMyPlace($singleimage_db_path, $singleimagethumb_db_image, $multi_path, $multi_thumb_path, $property_name, $property_address,
                               $property_type_id, $room_type_id, $no_of_rooms, $room_no, $total_capacity, $price, $property_description,
                               $attached_bathroom, $food, $cable_tv, $wifi, $security, $parking, $subheader, $content, $cancellation_policy, $checkin_time,
                               $checkout_time, $user_id, $latitude, $longitude, $location_id)
    {

        $stmt = $this->conn->prepare("
            CALL add_my_place(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($singleimage_db_path, $singleimagethumb_db_image, $multi_path, $multi_thumb_path, $property_name,
            $property_address, $property_type_id, $room_type_id, $no_of_rooms, $room_no, $total_capacity, $price, $property_description,
            $attached_bathroom, $food, $cable_tv, $wifi, $security, $parking, $subheader, $content, $cancellation_policy, $checkin_time, $checkout_time,
            $user_id, $latitude, $longitude, $location_id));


        if($execution == false)
            return NULL;
        return $execution;
    }

    //function for publishing a home
    public function publishHome($home_id)
    {
        $stmt = $this->conn->prepare("
            UPDATE homes SET home_status = 1 WHERE home_id = ?
        ");
        $execution = $stmt->execute(array($home_id));

        if($execution == false)
            return NULL;
        return $execution;
    }

    //function for unpublishing a home
    public function unpublishHome($home_id)
    {
        $current_time = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("
            CALL unpublish_home(?, ?)
        ");
        $execution = $stmt->execute(array($home_id, $current_time));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for inserting attraction and distance
    public function attraction_and_distance($header, $content)
    {
        $stmt = $this->conn->prepare("
            CALL attraction_and_distance(?, ?)
        ");
        $execution = $stmt->execute(array($header, $content));

        if($execution == false)
            return NULL;
        return $execution;
    }


    //function that fetches the list of homes registered against a particular vendor
    public function myPlaces($user_id)
    {
        $stmt = $this->conn->prepare("
            CALL my_places(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the muliple images path
    public function fetchMultiPath($home_id)
    {
        $stmt = $this->conn->prepare("
            SELECT SUBSTRING(homes.home_image, 9) AS home_image, SUBSTRING(homes.home_thumb_image, 9) AS home_thumb_image,
                SUBSTRING(homes.gallary_images, 9) AS gallary_images, SUBSTRING(homes.gallary_thumb_images, 9) AS gallary_thumb_images
            FROM homes WHERE home_id = ?
        ");
        $execution = $stmt->execute(array($home_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for updating my place
    public function updateMyPlace($singleimage_db_path, $singleimagethumb_db_image, $multi_path, $multi_thumb_path, $property_name, $property_address,
                                  $property_type_id, $room_type_id, $no_of_rooms, $total_capacity, $price, $property_description,
                                  $attached_bathroom, $food, $cable_tv, $wifi, $security, $parking, $subheader, $content, $cancellation_policy, $checkin_time,
                                  $checkout_time, $user_id, $latitude, $longitude, $location_id, $home_id, $current_date)
    {
        $stmt = $this->conn->prepare("
            CALL home_updation(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($singleimage_db_path, $singleimagethumb_db_image, $multi_path, $multi_thumb_path, $property_name,
            $property_address, $property_type_id, $room_type_id, $no_of_rooms, $total_capacity, $price, $property_description,
            $attached_bathroom, $food, $cable_tv, $wifi, $security, $parking, $subheader, $content, $cancellation_policy, $checkin_time, $checkout_time,
            $user_id, $latitude, $longitude, $location_id, $home_id, $current_date));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the home basic details
    public function homeBasicDetails($home_id)
    {
        $stmt = $this->conn->prepare("
            CALL home_basic_details(?)
        ");
        $execution = $stmt->execute(array($home_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the home/room details
    public function homeRoomDetails($room_id, $home_id, $location, $property_type, $room_type, $food, $price_low, $price_high, $people, $home_name, $user_id)
    {
        $stmt = $this->conn->prepare("
            CALL home_room_details(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($room_id, $home_id, $location, $property_type, $room_type, $food, $price_low, $price_high, $people,
            $home_name, BASE_URL, $user_id));

        $result['room_details']            = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['home_details']            = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['attraction_and_distance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['home_reviews']            = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['similar_listings']        = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the home/room details
    public function homeRoomDetailsTemp($room_id, $home_id, $location, $property_type, $room_type, $food, $price_low, $price_high, $people, $user_id)
    {
        $stmt = $this->conn->prepare("
            CALL home_room_details(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($room_id, $home_id, $location, $property_type, $room_type, $food, $price_low, $price_high, $people, BASE_URL, $user_id));

        print_r(array($room_id, $home_id, $location, $property_type, $room_type, $food, $price_low, $price_high, $people, BASE_URL, $user_id));
        die();

        $result['room_details']            = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['home_details']            = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['attraction_and_distance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['home_reviews']            = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['similar_listings']        = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the home details to populate the data during editing the home
    public function home_details($home_id, $user_id)
    {
        $stmt = $this->conn->prepare("
            CALL home_details(?, ?, ?)
        ");
        $execution                          = $stmt->execute(array($home_id, BASE_URL, $user_id));
        $result['home_details']             = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowSet();

        $result['attraction_and_distance']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //funcrtion for fetching the room details for a particular property type of a home
    public function roomdetails($home_id, $property_type_id)
    {
        $stmt = $this->conn->prepare("
            CALL room_details(?, ?, ?)
        ");
        $execution     = $stmt->execute(array($home_id, $property_type_id, BASE_URL));
        $result        = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //helper function that fetches rooms of particular property_type homes
    public function fetchrooms($property_type, $home_id)
    {
        $stmt = $this->conn->prepare("
            CALL fetch_rooms(?, ?)
        ");
        $execution = $stmt->execute(array($property_type, $home_id));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($execution == false)
            return NULL;
        return $result;

    }

    //function for marking home as favourite
    public function markHomeAsFavourite($user_id, $home_id)
    {
        $stmt = $this->conn->prepare("
            CALL marking_favourite(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $home_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for unmarking home as favourite
    public function unmarkHomeAsFavourite($user_id, $home_id)
    {
        $stmt = $this->conn->prepare("
            CALL unmarking_favourite(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $home_id));

        if($execution == false)
            return NULL;
        return $execution;
    }


    //function that fetches the list of favourite homes against a particular vendor
    public function userFavouriteHomes($user_id)
    {
        $stmt = $this->conn->prepare("
            CALL favourite_homes(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for casting reviews
    public function castReview($home_id, $user_id, $rating, $visit_time, $visit_type, $title, $review, $current_datetime)
    {
        $stmt = $this->conn->prepare("
            CALL cast_review(?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($home_id, $user_id, $rating, $visit_time, $visit_type, $title, $review, $current_datetime));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function taht fetches the list of reviews for a particular home
    public function homeReviews($home_id)
    {
        $stmt = $this->conn->prepare("
            CALL home_reviews(?, ?)
        ");
        $execution = $stmt->execute(array($home_id, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for registering a user
    public function userRegistration($name, $email, $password, $phone_no, $api_key, $registration_time, $last_login_time, $phone_code, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL user_registration(?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($name, $email, $password, $phone_no, $api_key, $registration_time, $last_login_time, $phone_code, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for contact number verification
    public function contactNoVerification($phone_no, $code, $email_code, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL contact_no_verification(?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($phone_no, $code, $email_code, $role_id, BASE_URL));

        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subscriptions']  = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['is_subscribed']  = $result['subscriptions'][0]['is_subscribed'];


        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the username from phone number
    public function fetchName($phone_no)
    {
        $stmt = $this->conn->prepare("
            SELECT name FROM users WHERE phone_no = ?
        ");
        $execution = $stmt->execute(array($phone_no));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);
        if($execution == false)
            return NULL;
        return $result['name'];
    }

    //function for resending the code
    public function resendCode($phone_no, $code, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL code_resending(?, ?, ?)
        ");
        $execution = $stmt->execute(array($phone_no, $code, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that checks whether image exists for a particular user
    public function checkImageExistence($email, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL check_image_existance(?, ?)
        ");
        $execution = $stmt->execute(array($email, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        elseif($result == false)
            return 0;
        return $result['profile_picture'];
    }

    //function for registration / login with facebook
    public function registrationLoginWithSocialPlatform($name, $email, $phone_no, $image_path, $social_id, $api_key, $phone_code, $current_time, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL loginwith_social_platform(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($name, $email, $phone_no, $image_path, $social_id, $api_key, $phone_code, BASE_URL, $current_time, $current_time, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subscriptions']    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['is_subscribed']    = $result['subscriptions'][0]['is_subscribed'];

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches name by email
    public function fetchNameByEmail($email, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL name_from_email(?, ?)
        ");
        $execution = $stmt->execute(array($email, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for registering the phone number
    public function phoneNoRegistration($email, $phone_no, $phone_code, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL phone_no_registration(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($email, $phone_no, $phone_code, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for registration / login with google plus
    public function registrationLoginWithGoogleplus($name, $email, $image_path, $google_id, $api_key, $current_time)
    {
        $stmt = $this->conn->prepare("
            CALL login_with_googleplus(?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($name, $email, $image_path, $google_id, $api_key, $current_time, BASE_URL));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the home timings
    public function homeTimes($home_id)
    {
        $stmt = $this->conn->prepare("
            SELECT checkin_time + INTERVAL 1 SECOND AS checkin_time, checkout_time FROM homes WHERE home_id = ?
        ");
        $execution = $stmt->execute(array($home_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for booking room
    public function booking($home_id, $no_of_rooms, $adults, $children, $checkin_date, $checkout_date, $room_type_id, $price,
                            $no_of_nights, $user_id)
    {
        $current_date = date("Y-m-d");

        $stmt = $this->conn->prepare("
            CALL book_temp_1(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

//        print_r(array($home_id, $no_of_rooms, $adults, $children, $checkin_date, $checkout_date, $room_type_id, $price,
//            $no_of_nights, $user_id, $current_date));
//        die();

        $execution = $stmt->execute(array($home_id, $no_of_rooms, $adults, $children, $checkin_date, $checkout_date, $room_type_id, $price,
            $no_of_nights, $user_id, $current_date));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for booking home
    public function bookHome($user_id, $room_id, $home_id, $no_of_rooms, $adults, $children, $checkin_date, $checkout_date, $room_type, $price)
    {
        $no_of_nights = strtotime($checkout_date) - strtotime( $checkin_date);
        $no_of_nights =  $no_of_nights/86400;
        $current_date = date("Y-m-d");

        $stmt = $this->conn->prepare("
            CALL book_temp(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($room_id, $home_id, $no_of_rooms, $adults, $children, $checkin_date, $checkout_date, $room_type, $price, $user_id, $no_of_nights, $current_date));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);
        if($execution == false)
            return NULL;
        return $result;
    }

    //list of bookings against a particular user
    public function bookingsList($user_id)
    {
        $stmt = $this->conn->prepare("
           CALL bookings_list(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }



    //function that fetches the booking details
    public function bookingDetails($booking_id)
    {
        $stmt = $this->conn->prepare("
            CALL booking_details(?, ?)
        ");
        $execution = $stmt->execute(array($booking_id, BASE_URL));
        $result['booking_details']    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['room_nos']    = $stmt->fetch(PDO::FETCH_ASSOC);

        $result['booking_details']['room_nos']    = $result['room_nos']['room_nos'];

        unset($result['room_nos']);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for user login
    public function userLogin($email, $password, $api_key, $current_time, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL user_login(?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($email, $password, $api_key, $current_time, $role_id, BASE_URL));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subscriptions']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result['is_subscribed']    = $result['subscriptions'][0]['is_subscribed'];

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for fetching the user profile details
    public function userProfile($user_id)
    {
        $stmt = $this->conn->prepare("
            CALL user_profile(?)
        ");
        $stmt->execute(array($user_id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result)
            return $result;
        return NULL;
    }

    //function for updating user profile
    public function profileUpdate($user_id, $name, $phone_no, $profile_picture, $gender, $birth_date, $subscriptions, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL profile_update(?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $name, $phone_no, $profile_picture, $gender, $birth_date, $subscriptions, $role_id, BASE_URL));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subscriptions']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result['is_subscribed']    = $result['subscriptions'][0]['is_subscribed'];

        if($execution === false)
            return NULL;
        return $result;
    }

    //function for updating services
    public function servicesUpdation($user_id, $services)
    {
        $stmt = $this->conn->prepare("
            CALL services_updation(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $services));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution === false)
            return NULL;
        return $result;
    }

    //function for updating user password
    public function passwordUpdation($user_id, $old_password, $new_password)
    {
        $stmt = $this->conn->prepare("
            CALL password_updation(?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $old_password, $new_password));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution === false)
            return NULL;
        return $result;
    }

    //function that fetches the faqs
    public function getFaqs()
    {
        $stmt = $this->conn->prepare("
            CALL faqs()
        ");
        $execution = $stmt->execute(array());
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for adding a feedback
    public function addFeedback($user_id, $feedback)
    {
        $stmt = $this->conn->prepare("
            CALL add_feedback(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $feedback));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the privacy policy
    public function fetchPrivacyPolicy()
    {
        $stmt = $this->conn->prepare("
            CALL privacy_policy()
        ");
        $execution = $stmt->execute(array());
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the terms of service
    public function fetchTermsOfService()
    {
        $stmt = $this->conn->prepare("
            CALL terms_of_service()
        ");
        $execution = $stmt->execute(array());
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for deleting a user's account
    public function deleteAccount($user_id, $current_datetime, $delete_account)
    {
        $stmt = $this->conn->prepare("
            CALL account_deletion(?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $current_datetime, $delete_account));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the event types
    public function eventTypes()
    {
        $stmt = $this->conn->prepare("
            CALL event_dropdowns()
        ");
        $execution = $stmt->execute(array());
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the event visit types
    public function eventVisitTypes()
    {
        $stmt = $this->conn->prepare("
            CALL event_dropdowns()
        ");
        $execution = $stmt->execute(array());

        $stmt->nextRowset();
        $stmt->nextRowset();

        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for populating master data for adding events
    public function eventDropdowns()
    {
        $stmt = $this->conn->prepare("
            CALL event_dropdowns()
        ");
        $execution = $stmt->execute(array());

        $result['event_types']       = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['ticket_categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for creating event
    public function eventCreation($user_id, $venue, $title, $description, $host, $sponsor, $event_type, $from_date, $to_date,
                                  $final_event_image_db_path, $final_thumbevent_image_db_path, $ticket_type, $capacity, $separator1, $current_datetime)
    {

        $stmt = $this->conn->prepare("
            CALL event_creation(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $venue, $title, $description, $host, $sponsor, $event_type, $from_date, $to_date,
            $final_event_image_db_path, $final_thumbevent_image_db_path, $ticket_type, $capacity, $separator1, $current_datetime, EVENT_BOOKING_DURATION_TIME,
            BASE_URL));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['gallery_images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['ticket_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['session']             = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['status_master_data']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for event session addition
    public function eventSessionCreation($event_id, $session_name, $start_date, $end_date)
    {
        $stmt = $this->conn->prepare("
            CALL event_session_creation(?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_id, $session_name, $start_date, $end_date, BASE_URL));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subsessions']    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches ticket type details for a particular event
    public function eventTicketTypes($event_id)
    {
        $stmt = $this->conn->prepare("
            CALL event_ticket_types(?)
        ");
        $execution = $stmt->execute(array($event_id));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for editing events
    public function eventEdition($event_id, $venue, $title, $description, $host, $sponsor, $event_type, $from_date, $to_date,
                                 $final_event_image_db_path, $final_thumbevent_image_db_path, $ticket_type, $capacity, $status_id,
                                 $separator1)
    {
        $datetime = date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("
            CALL event_edition(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_id, $venue, $title, $description, $host, $sponsor, $event_type, $from_date, $to_date,
            $final_event_image_db_path, $final_thumbevent_image_db_path, $ticket_type, $capacity, $status_id,
            $separator1, $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['gallery_images']       = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['ticket_types']         = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['session']              = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['status_master_data']   = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for editing event ticket type
    public function eventTicketTypeEdition($ticket_type_id,$ticket_type,$capacity)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_ticket_type_edition(?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($ticket_type_id, $ticket_type, $capacity, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for fetching catagory from event ticket type
    public function eventTicketTypeEditionFetchCategory($event_ticket_type_id)
    {
        $stmt = $this->conn->prepare("
            CALL event_ticket_type_edition_fetch_category(?)
        ");
        $execution = $stmt->execute(array($event_ticket_type_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for deleting event ticket type
    public function ticketTypeDeletion($ticket_type_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_ticket_type_deletion(?, ?, ?)
        ");
        $execution = $stmt->execute(array($ticket_type_id, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for deleting event subsession
    public function eventSubsessionDeletion($subsession_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_subsession_deletion(?, ?, ?)
        ");
        $execution = $stmt->execute(array($subsession_id, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for deleting event session
    public function eventSessionDeletion($session_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_session_deletion(?, ?, ?)
        ");
        $execution = $stmt->execute(array($session_id, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for deleting event
    public function eventDeletion($event_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_deletion(?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_id, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function event session details for the session edition page
    public function eventSessionDetailsEditionPage($session_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_session_details_edition_page(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($session_id, $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));

        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subsessions']    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for editing event session
    public function eventSessionEdition($session_id, $session_name, $start_date, $end_date)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_session_edition(?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($session_id, $session_name, $start_date, $end_date, $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subsessions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the events list of a particular vendor
    public function myEvents($user_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL my_events(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $timestamp, EVENT_BOOKING_DURATION_TIME, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the filtered my events for the vendor
    public function eventFilteringVendor($user_id, $from_date, $to_date, $event_title_venue, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_filtering_vendor(?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $from_date, $to_date, $event_title_venue, $timestamp, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for event filtering
    public function eventFiltering($event_type_id, $venue, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_filtering(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_type_id, $venue, $timestamp, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the event details for mobile(user app)
    public function eventDetails($event_id, $user_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_details(?, ?, ?, ?)
        ");
        $execution                   = $stmt->execute(array($event_id, $user_id, $timestamp, BASE_URL));

        $result     = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['gallary_images']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['sessions']        = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['reviews']        = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the event details for vendor
    public function eventDetailsVendor($event_id)
    {
        $datetime = date("Y-m-d H:i:s");


        $stmt = $this->conn->prepare("
            CALL event_basic_details(?, ?, ?, ?)
        ");
        $execution  = $stmt->execute(array($event_id, $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));

        $result     = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['gallery_images']      = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['ticket_types']        = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['session']             = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['status_master_data']  = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }

    //function that lets vendor add subsessions for a particular session
    public function eventSubsessionCreation($subsession_id, $from_time, $to_time, $ticket_type_id, $ticket_category_id, $price,
                                            $subsession_image_db_path, $thumbevent_image_db_path, $subsession_video, $dress_code, $separator1, $separator2, $user_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_subsession_creation(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($subsession_id, $from_time, $to_time, $ticket_type_id, $ticket_category_id, $price,
            $subsession_image_db_path, $thumbevent_image_db_path, $subsession_video, $dress_code, $separator1, $separator2, $user_id,
            $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));

        $result  = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subsession_tickets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for edition subsession details
    public function eventSubsessionEdition($subsession_id, $from_time, $to_time, $ticket_type_id, $ticket_category_id, $price,
                                           $subsession_image_db_path, $thumbevent_image_db_path, $subsession_video, $dress_code, $separator1, $separator2)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_subsession_edition(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($subsession_id, $from_time, $to_time, $ticket_type_id, $ticket_category_id, $price,
            $subsession_image_db_path, $thumbevent_image_db_path, $subsession_video, $dress_code, $separator1, $separator2,
            $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));

        $result  = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['subsession_tickets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for fetching the session details
    public function eventSessionDetails($session_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_session_details(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($session_id, $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for fetching event subsession wise ticket details
    public function eventSubsessionWiseTicketDetails($subsession_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_subsession_wise_ticket_details(?, ?, ?)
        ");
        $execution = $stmt->execute(array($subsession_id, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }


    //function for fetching the subsession details
    public function eventSubsessionDetails($subsession_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_subsession_details(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($subsession_id, $datetime, EVENT_BOOKING_DURATION_TIME, BASE_URL));
        $sub   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = $sub[0];

        $stmt->nextRowset();

        $result['ticket_details']  = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }


    //function for fetching the subsession details
    public function eventTicketTypes1($event_id, $subsession_id)
    {
        $stmt = $this->conn->prepare("
            CALL event_ticket_types(?, ?)
        ");
        $execution = $stmt->execute(array($event_id, $subsession_id));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }

    //function for fetching the subsession tickets
    public function subsessionTicketsTypes($subsession_id, $user_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
                CALL event_subsession_ticket_types(?, ?, ?, ?)
            ");

        $execution = $stmt->execute(array($subsession_id, $datetime, $user_id, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if($execution == false)
            return NULL;
        return $result;
    }

    public function ticketCategories($subsession_id, $ticket_type)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
                CALL event_ticket_type_categories(?, ?, ?, ?)
            ");

        $execution = $stmt->execute(array($subsession_id, $ticket_type, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;

    }

    //function for casting review against an event
    public function reviewEvent($event_id, $user_id, $rating, $visit_time, $visit_type, $title, $review, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL review_event(?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_id, $user_id, $rating, $visit_time, $visit_type, $title, $review, $timestamp));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function that fetches the event cart list
    public function event_addToCart($user_id, $subsession_id, $from_date, $to_date, $ticket_type_category_id, $people, $price, $timestamp, $separator1)
    {
        $stmt = $this->conn->prepare("
            CALL event_save_cart(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $subsession_id, $from_date, $to_date, $ticket_type_category_id, $people, $price, $timestamp, EVENT_BOOKING_DURATION_TIME, $separator1));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for display cart details list
    public function eventCartList($user_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_cart_list(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $timestamp, EVENT_BOOKING_DURATION_TIME, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for display cart details list
    public function eventCartListSecond($user_id, $cart_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_cart_details(?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $cart_id, $timestamp));
        $result['cart_basic_details']    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['session_attendee_list']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result['session_attendee_list'];
    }


    //function for editing event cart
    public function eventCartEdition($user_id, $event_cart_id, $ticket_type_category_id, $people, $price, $separator1)
    {
        $datetime = date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("
            CALL event_cart_edition(?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $event_cart_id, $ticket_type_category_id, $people, $price, $separator1, $datetime, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }



    //function for deleting cart items from event cart list
    public function eventCartRemove($user_id, $event_cart_id)
    {
        $stmt = $this->conn->prepare("
            CALL event_cart_remove(?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $event_cart_id));

        if($execution == false)
            return NULL;
        return $execution;
    }

    //function for display cart event details
    public function eventCartDetails($user_id, $event_cart_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_cart_details(?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $event_cart_id, $timestamp));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->nextRowset();

        $result['session_attendee_list']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for booking event
    public function eventCartCheckout($event_cart_id, $price, $event_order_id, $user_id, $timestamp, $separator1)
    {
        $stmt = $this->conn->prepare("
            CALL event_cart_checkout(?, ?, ?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_cart_id, $price, $event_order_id, $user_id, $timestamp, $separator1, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for event_booking
    public function eventBooking($event_order_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_booking(?, ?, ?)
        ");
        $execution = $stmt->execute(array($event_order_id, $timestamp, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for display order details list
    public function eventOrderList($user_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_order_list(?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $datetime, EVENT_BOOKING_DURATION_TIME_IOS, EVENT_ACCESS_CODE, EVENT_MERCHANT_ID));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution === false)
            $result = NULL;
        elseif($result['exception'])
            $result = NULL;
        elseif($result['warning'])
            $result = NULL;
        else
        {
            foreach($result as $key=>$value)
            {
                $result[$key]['event_access_code'] = EVENT_ACCESS_CODE;
                $result[$key]['event_merchant_id'] = EVENT_MERCHANT_ID;
            }
        }
        return $result;
    }

    //function for display ticket details list
    public function eventTicketList($user_id, $event_order_id)
    {
        $datetime = date("Y-m-d H:i:s");

        $stmt = $this->conn->prepare("
            CALL event_ticket_list(?, ?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $event_order_id, $datetime, EVENT_BOOKING_DURATION_TIME_IOS, BASE_URL));
        $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $key=>$value)
        {
            $result[$key]['event_access_code'] = EVENT_ACCESS_CODE;
            $result[$key]['event_merchant_id'] = EVENT_MERCHANT_ID;
        }

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for display event ticket details
    public function eventTicketDetails($user_id, $event_cart_id, $timestamp)
    {
        $stmt = $this->conn->prepare("
            CALL event_ticket_details(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($user_id, $event_cart_id, $timestamp, EVENT_BOOKING_DURATION_TIME));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        $result['event_access_code']   = EVENT_ACCESS_CODE;
        $result['event_merchant_id']   = EVENT_MERCHANT_ID;

        $stmt->nextRowset();

        $result['session_attendee_list']  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }






    //function that fetches the company details
    public function companyDetails()
    {
        $stmt = $this->conn->prepare("
            CALL company_details()
        ");
        $execution = $stmt->execute(array());
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }



    //function for sending code to the registered email
    public function forgotPassword($email, $code, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL forgot_password(?, ?, ?)
        ");
        $execution = $stmt->execute(array($email,$code, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    //function for resetting password
    public function passwordReset($email, $code, $new_password, $role_id)
    {
        $stmt = $this->conn->prepare("
            CALL password_reset(?, ?, ?, ?)
        ");
        $execution = $stmt->execute(array($email, $code, $new_password, $role_id));
        $result    = $stmt->fetch(PDO::FETCH_ASSOC);

        if($execution == false)
            return NULL;
        return $result;
    }

    /*
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey()
    {
        return md5(uniqid(rand(), true));
    }


}

set_error_handler('vendorTGW_ErrorHandler');

?>
