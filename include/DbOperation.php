<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php';
    
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

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

    private function apiKeyExists($apiKey) {
        $stmt = $this->con->prepare("SELECT id from userCredentials WHERE apiKey = ?");
        $stmt->bind_param("s", $apiKey);
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
    public function addClothingItem($textureName, $textureFile, $previewName, $previewFile, $clothingName, $clothingType, $apiKey){
            // AWS Info
            $bucketName = 'arcloset';
            $IAM_KEY = 'AKIAJPTYKKKGOLHZIKAQ';
            $IAM_SECRET = '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';
            // Connect to AWS
            try {
                // You may need to change the region. It will say in the URL when the bucket is open
                // and on creation.
                $s3 = S3Client::factory(
                    array(
                        'credentials' => array(
                            'key' => $IAM_KEY,
                            'secret' => $IAM_SECRET
                        ),
                        'version' => 'latest',
                        'region'  => 'us-east-2'
                    )
                );
            } catch (Exception $e) {
                die("Error: " . $e->getMessage());
            }

            $keyName = ''.$apiKey.'/'.$clothingName.'/'.$textureName.'';
            $pathInS3 = 'https://s3.us-east-2.amazonaws.com/' . $bucketName . '/' . $keyName;
            // Add texture file to S3
            try {
                // Uploaded:
                //$file = $clothingMaterial;
                $s3->putObject(
                    array(
                        'Bucket'=>$bucketName,
                        'Key' =>  $keyName,
                        'SourceFile' => $textureFile,
                        'StorageClass' => 'REDUCED_REDUNDANCY'
                    )
                );
            } catch (S3Exception $e) {
                die('Error:' . $e->getMessage());
            } catch (Exception $e) {
                die('Error:' . $e->getMessage());
            }
            // Add preview file to S3
            $keyName = ''.$apiKey.'/'.$clothingName.'/'.$previewName.'';
            try {
                // Uploaded:
                //$file = $clothingMaterial;
                $s3->putObject(
                    array(
                        'Bucket'=>$bucketName,
                        'Key' =>  $keyName,
                        'SourceFile' => $previewFile,
                        'StorageClass' => 'REDUCED_REDUNDANCY'
                    )
                );
            } catch (S3Exception $e) {
                die('Error:' . $e->getMessage());
            } catch (Exception $e) {
                die('Error:' . $e->getMessage());
            }
            //Creating a statement
            $stmt = $this->con->prepare("INSERT INTO `clothingItem` (`clothingID`, `clothingType`, `clothingName`, `apiKey`) VALUES (?,?,?,?)");
            $empty = '';
            //Binding the parameters
            $stmt->bind_param("ssss", $empty, $clothingType, $clothingName, $apiKey);

            //Executing the statement
            $result = $stmt->execute();

            //Closing the statement
            $stmt->close();

            //If statement executed successfully
            if ($result) {
                //Returning 0 means item created successfully
                return 0;
            } else {
                //Returning 1 means failed to create item
                return 1;
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
    public function getClothingItems($apiKey){
        // AWS Info
        

        if($this->apiKeyExists($apiKey) == false){
            return null;
        }

        $stmt = $this->con->prepare("SELECT clothingType, clothingName FROM clothingItem WHERE apiKey = ?");
        $stmt->bind_param("s",$apiKey);
        $stmt->execute();
        $clothingItems = $stmt->get_result()->fetch_all();
        $stmt->close();

        // Connect to AWS
        try {
            // You may need to change the region. It will say in the URL when the bucket is open
            // and on creation.
            $s3 = S3Client::factory(
                array(
                    'credentials' => array(
                        'key' => $IAM_KEY,
                        'secret' => $IAM_SECRET
                    ),
                    'version' => 'latest',
                    'region'  => 'us-east-2'
                )
            );
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }

        $array = array();

        foreach ( $clothingItems as $clothingItem ) {

            $keyName = ''.$apiKey.'/'.$clothingItem[1].'/Texture.png';
            try {
                // Get the object
                $result = $s3->getObject(array(
                    'Bucket' => $bucketName,
                    'Key'    => $keyName
                ));

            } catch (S3Exception $e) {
                echo $e->getMessage() . "\n";
            }
            $bodyAsString = (string) $result['Body'];
            $bodyAsString = $result['Body']->__toString();
            $image = imagecreatefromstring($bodyAsString);
            ob_start();
            imagepng($image);
            $contents =  ob_get_contents();
            ob_end_clean();
            $base64String = base64_encode($contents);
            imagedestroy($image);

            $keyName = ''.$apiKey.'/'.$clothingItem[1].'/Preview.png';
            try {
                // Get the object
                $result = $s3->getObject(array(
                    'Bucket' => $bucketName,
                    'Key'    => $keyName
                ));

            } catch (S3Exception $e) {
                echo $e->getMessage() . "\n";
            }
            $bodyAsString = (string) $result['Body'];
            $bodyAsString = $result['Body']->__toString();
            $image = imagecreatefromstring($bodyAsString);
            ob_start();
            imagepng($image);
            $contents =  ob_get_contents();
            ob_end_clean();
            $base64StringPreview = base64_encode($contents);
            imagedestroy($image);

            $clothingItemArray = array('clothingType' => $clothingItem[0], 'clothingName' => $clothingItem[1],
                                                        'preview' => $base64StringPreview, 'texture' => $base64String);
            array_push($array, $clothingItemArray);
        }
        
        return $array;
        
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