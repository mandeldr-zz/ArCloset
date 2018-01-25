<?php

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

        //First we will check whether the student is already registered or not
        if (!$this->isUserExists($username)) {
            //Encrypting the password
            $password = md5($pass);

            //Generating an API Key
            $apikey = $this->generateApiKey();

            //Crating an statement
            $stmt = $this->con->prepare("INSERT INTO userCredentials(username, password, api_key) values(?, ?, ?)");

            //Binding the parameters
            $stmt->bind_param("ssss", $username, $password, $apikey);

            //Executing the statment
            $result = $stmt->execute();

            //Closing the statment
            $stmt->close();

            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
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
        //closing the statment
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

    //Checking whether a user already exist
    private function isUserExists($username) {
        $stmt = $this->con->prepare("SELECT id from userCredentials WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //Methods to check a user is valid or not using api key
    public function isValidUser($api_key) {
        //Creating an statement
        $stmt = $this->con->prepare("SELECT id from students WHERE api_key = ?");

        //Binding parameters to statement with this
        //the question mark of queries will be replaced with the actual values
        $stmt->bind_param("s", $api_key);

        //Executing the statement
        $stmt->execute();

        //Storing the results
        $stmt->store_result();

        //Getting the rows from the database
        //As API Key is always unique so we will get either a row or no row
        $num_rows = $stmt->num_rows;

        //Closing the statment
        $stmt->close();

        //If the fetched row is greater than 0 returning  true means user is valid
        return $num_rows > 0;
    }

    //This method will generate a unique api key
    private function generateApiKey(){
        return md5(uniqid(rand(), true));
    }
}