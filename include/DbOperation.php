<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
class DbOperation
{
    //Database connection link
    private $con;

    //Class constructor
    function __construct()
    {
        //Getting the DbConnect.php file
        require_once dirname(__FILE__) . '/DbConnect.php';

        //Creating a DbConnect object to connect to the database
        $db = new DbConnect();

        //Initializing our connection link of this class
        //by calling the method connect of DbConnect class
        $this->con = $db->connect();
    }

    //Method will create a new user
    public function createUser($username,$pass){
        if (!$this->userExists($username)) {
            //Encrypting the password
            $password = md5($pass);

            //Generating an API Key
            $apiKey = $this->generateApiKey();

            //Creating a statement
            $stmt = $this->con->prepare("INSERT INTO `userCredentials` (`id`, `username`, `password`, `apiKey`) VALUES (?,?,?,?)");
            $empty = '';
            //Binding the parameters
            $stmt->bind_param("ssss", $empty, $username, $password, $apiKey);

            //Executing the statement
            $result = $stmt->execute();

            //Closing the statement
            $stmt->close();

            //If statement executed successfully
            if ($result) {
                //Returning 0 means user created successfully
                return 0;
            } else {
                //Returning 1 means failed to create user
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }

    }

    //Method will create a new avatar
    public function createAvatar($gender, $height, $skinColor, $hairColor, $hairLength, $apiKey){

        if (!$this->avatarExists($apiKey)) {

            //Creating a statement
            $stmt = $this->con->prepare("INSERT INTO avatar(gender, height, skinColor, hairColor, hairLength, api_key) values(?, ?, ?, ?, ?, ?)");

            //Binding the parameters
            $stmt->bind_param("ssssis", $gender, $height, $skinColor, $hairColor, $hairLength, $apiKey);

            //Executing the statement
            $result = $stmt->execute();

            //Closing the statement
            $stmt->close();

            //If statement executed successfully
            if ($result) {
                //Returning 0 means avatar created successfully
                return 0;
            } else {
                //Returning 1 means failed to create avatar
                return 1;
            }
        } else {
            //returning 2 means avatar already exist in the database
            return 2;
        }
    }

    //Method for user login
    public function userLogin($username,$pass){
        //Generating password hash
        $password = md5($pass);
        //Creating query
        $stmt = $this->con->prepare("SELECT * FROM userCredentials WHERE username=? and password=?");
        //binding the parameters
        $stmt->bind_param("ss",$username,$password);
        //executing the query
        $stmt->execute();
        //Storing result
        $stmt->store_result();
        //Getting the result
        $num_rows = $stmt->num_rows;
        //closing the statement
        $stmt->close();
        //If the result value is greater than 0 means user found in the database with given username and password
        //So returning true
        return $num_rows>0;
    }

    //This method will return user credential details
    public function getUserCredentials($username){
        $stmt = $this->con->prepare("SELECT * FROM userCredentials WHERE username=?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        //Getting the userCredentials result array
        $creds = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the user credentials
        return $creds;
    }

    //This method will return the user's avatar
    public function getAvatar($apiKey){
        $stmt = $this->con->prepare("SELECT * FROM avatar WHERE api_key=?");
        $stmt->bind_param("s",$apiKey);
        $stmt->execute();
        //Getting the avatar result array
        $avatar = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the avatar
        return $avatar;
    }

    //This method will update the user's avatar
    public function updateAvatar($gender, $height, $skinColor, $hairColor, $hairLength, $apiKey){
        $stmt = $this->con->prepare("UPDATE avatar SET gender = ?, height = ?, skinColor = ?, hairColor = ?, hairLength = ? WHERE api_key=?");
        $stmt->bind_param("ssssis", $gender, $height, $skinColor, $hairColor, $hairLength, $apiKey);
        $stmt->execute();
        $stmt->close();
        //returning the avatar
        return $this->getAvatar($apiKey);
    }

    //Checking whether a user already exist
    private function userExists($username) {
        $stmt = $this->con->prepare("SELECT id from userCredentials WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Checks if an avatar for the user exists using api key
    public function avatarExists($apiKey) {
        $stmt = $this->con->prepare("SELECT id from avatar WHERE api_key = ?");
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will generate a unique api key
    private function generateApiKey(){
        return md5(uniqid(rand(), true));
    }

    //  ****************************************
    //              Closet Functions
    //  ****************************************

    // Check for existing clothing item
    public function clothingItemExists($clothingID) {
        $stmt = $this->con->prepare("SELECT * from clothingItem WHERE clothingID = ?");
        $stmt->bind_param("i", $clothingID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    // Add clothing item
    public function addClothingItem($clothingID, $clothingType, $clothingMaterial, $apiKey){

        if (!$this->clothingItemExists($clothingID)) {
            //$clothingMaterial = addslashes($__FILES['image']['Texture.png']);
            // $clothingMaterial = addslashes($__FILES['image']['tmp_name']);
            // $clothingMaterial = file_get_contents($clothingMaterial);
            // $clothingMaterial = base64_encode($clothingMaterial);
            //$clothingMaterial = $clothingMaterial->getStream();
            //echo $clothingMaterial;
            //Creating a statement
            echo $clothingMaterial->getClientFilename();
            $stmt = $this->con->prepare("INSERT INTO clothingItem(clothingID, clothingType, clothingMaterial, apiKey) VALUES(?, ?, ?, ?)");

            //Binding the parameters
            $stmt->bind_param("isbs", $clothingID, $clothingType, $clothingMaterial, $apiKey);

            //Executing the statement
            $result = $stmt->execute();

            //Closing the statement
            $stmt->close();

            //If statement executed successfully
            if ($result) {
                //Returning 0 means avatar created successfully
                return 0;
            } else {
                //Returning 1 means failed to create clothingItem
                return 1;
            }
        } else {
            //returning 2 means avatar already exist in the database
            return 2;
        }
    }

    // Update clothing item
    public function updateClothingItem($clothingID, $clothingType, $clothingMaterial, $apiKey){

        if ($this->clothingItemExists($clothingID)) {

            //Creating a statement
            $stmt = $this->con->prepare("UPDATE clothingItem SET clothingType=?, clothingMaterial=? WHERE clothingID=?");

            //Binding the parameters
            $stmt->bind_param("sbi", $clothingType, $clothingMaterial, $clothingID);

            //Executing the statement
            $result = $stmt->execute();

            //Closing the statement
            $stmt->close();

            //If statement executed successfully
            if ($result) {
                //Returning 0 means clothing item updated successfully
                return 0;
            } else {
                //Returning 1 means failed to update clothingItem
                return 1;
            }
        } else {
            //returning 2 means clothing item does not exist in the database
            return 2;
        }
    }

    //This method will return the user's avatar
    public function getClothingItem($clothingID){
        $stmt = $this->con->prepare("SELECT * FROM clothingItem WHERE clothingID=?");
        $stmt->bind_param("i",$clothingID);
        $stmt->execute();
        //Getting the clothing item result array
        $clothingItem = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning clothing item
        return $clothingItem;
    }

    //This method will return the user's avatar
    public function deleteClothingItem($clothingID){
        
        if ($this->clothingItemExists($clothingID)) {

            //Creating SQL statement
            $stmt = $this->con->prepare("DELETE FROM clothingItem WHERE clothingID=?");
            $stmt->bind_param("i",$clothingID);
            $result = $stmt->execute();
            $stmt->close();
           
            //If statement executed successfully
            if ($result) {
                //Returning 0 means clothing item updated successfully
                return 0;
            } else {
                //Returning 1 means failed to update clothingItem
                return $result;
            }

        } else {
            //Item does not exist in the database
            return 2;
        }
    }
}