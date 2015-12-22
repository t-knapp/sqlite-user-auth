<?php
    session_start();
    class userauth {
    
        //Choose a unique salt. The longer and more complex it is, the better.
        //Since the database is being stored in an unencrypted file, we need
        //strong protection on the passwords.
        
        private $bcryptOptions = [ 'salt' => "@#6$%^&*THIS$%^&IS(&^A%^&LARGE^&**%^&SALT-8,/7%^&*" ];
        private $dbfile = "sqlite:userauth.db";
        private $db = null;
        
        public function dbinit() {
            if ($this->db == null){
                try {
                    $this->db = new PDO($this->dbfile);
                } catch (PDOException $e) {
                    echo 'Connection failed: ' . $e->getMessage();
                }
            }
        }
        
        public function dbclose() {
            if ($this->db != null) {
                $db = null;
            }
        }
        
        public function createTables() {
            $sql = "CREATE TABLE users (
                        uid INTEGER PRIMARY KEY,
                        username varchar(255),
                        password varchar(60),
                        lastlogin int
                    );";
            if(!$this->db->exec($sql)){
                print_r($this->db->errorInfo());
                die("The database table already exists. Please remove createTables() from your code<br />");
            }
            die("The database and tables were created. Please remove createTables() from userauth.php now.");
        }
        
        public function login($user, $password, $stayloggedin = false) {
            $pass = password_hash($password, PASSWORD_BCRYPT, $this->bcryptOptions);
            $sql = "SELECT * FROM users WHERE username = :user AND password = :pass LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user", $user, PDO::PARAM_STR);
            $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                if ($stayloggedin) {
                    setcookie("userid", $result['uid'], time() + 60 * 60 * 24 * 30);
                    setcookie("pass"  , $pass         , time() + 60 * 60 * 24 * 30);
                }
                $_SESSION['uid']   = $result['uid'];
                $_SESSION['uname'] = $result['username'];
                $time = time();
                $id = $result['uid'];
                $this->db->exec("UPDATE users SET lastlogin = '$time' WHERE uid = '$id'");
                return true;
            }
            return false;
        }
        
        //Returns false if username is taken
        public function newUser($username, $password) {
            $user = $username;
            $pass = password_hash($password, PASSWORD_BCRYPT, $this->bcryptOptions);
            $time = time();
            
            $sql = "SELECT uid FROM users WHERE username = :user";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user", $user, PDO::PARAM_STR);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($result){
                return false;
            }
            
            $sql = "INSERT INTO users VALUES (NULL, :user, :pass, :time)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":user", $user, PDO::PARAM_STR);
            $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
            $stmt->bindParam(":time", $time, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            if(!$result){
                print_r($this->db->errorInfo());
            }
            
            return $result;
        }
        
        /*
        public function updatePassword($username, $cpass, $newpass) {
            if (!$this->login($username, $cpass)) return false;
            
            // escaping session data is probably unnecessary, but better safe than sorry
            $id = sqlite_escape_string($_SESSION['uid']); 
            $q = "UPDATE users SET password = '$newpass' WHERE uid = '$id' AND password = '$cpass'";
            return sqlite_query($this->db, $q, $e);
        }*/
        
        public function cookielogin() {
            if (isset($_COOKIE['userid']) && isset($_COOKIE['pass'])) {
                $id = $_COOKIE['userid'];
                $pass = $_COOKIE['pass'];
                $sql = "SELECT * FROM users WHERE uid = :id AND password = :pass LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(":id",   $id,   PDO::PARAM_INT);
                $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if($result){
                    $_SESSION['uid']   = $result['uid'];
                    $_SESSION['uname'] = $result['username'];
                }
            }
        }
        
        public function isAuthenticated() {
            return isset($_SESSION['uid']);
        }
        
        public function logout() {
            unset($_SESSION['uid']);
            session_destroy();
            setcookie("userid", "", time() - 60 * 60 * 24 * 30);
            setcookie("pass", "", time() - 60 * 60 * 24 * 30);
        }
        
        public function uname() {
            if ($this->isAuthenticated()) 
                return $_SESSION['uname'];
            else 
                return "NO LOGIN.";
        }
    }
    $auth = new userauth();
    $auth->dbinit();
    
    //Initial
    //$u->createTables();
?>
