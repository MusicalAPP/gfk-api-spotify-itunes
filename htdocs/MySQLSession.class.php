<?php
class MySQLSession {

    private $database;
    private $session_id;
	private $sess_expire;
	private $name;
	private $sess_tbl_name;

    function __construct(
		$sess_dbuser,
		$sess_dbpass,
        $sess_dbhost,
		$sess_dbname,
		$sess_expire 	= null,
        $sess_name 		= null,
		$sess_tbl_name	= 'sessions'
	) 
    {
	
		$this->sess_expire 	= is_null($sess_expire) ? ini_get('session.gc_maxlifetime') : $sess_expire;
		$this->name 		= is_null($sess_name) 	? ini_get('session.name') 			: $sess_name;
		$this->sess_tbl_name	= $sess_tbl_name;
		$this->database = new mysqli(
			$sess_dbhost,
			$sess_dbuser,
			$sess_dbpass,
			$sess_dbname
		);
		$this->database->autocommit(false);
		$this->database->set_charset('utf8');

        if (mysqli_connect_errno() !== 0) {
            throw new Exception("Could not connect to the database!");
            return;
        }

        session_set_save_handler(array($this, "open"),
                                 array($this, "close"),
                                 array($this, "read"),
                                 array($this, "write"),
                                 array($this, "destroy"),
                                 array($this, "gc"));   

        session_name($this->name);
        session_start();
		$this->session_id = session_id();
    }
	
	function __destruct() {
		session_write_close();
		$this->database->close();
	}

    public function open($path, $sess_name)
    {
        $this->name = $sess_name;
        return true;              
    }

    public function close()
    {
        return true;
    }

    public function read($sess_id)
    {
	
		$this->session_id = $this->database->real_escape_string($sess_id);
		
        $query = "SELECT data 
                    FROM `" . $this->sess_tbl_name . "`
                   WHERE id = ?
				   AND last_updated + ? > NOW()";
		$stmt = $this->database->prepare($query);

		$stmt->bind_param(
			"si", $this->session_id, $this->sess_expire
		);
		
        if($stmt->execute()) {
            $stmt->bind_result($retval);

            $stmt->fetch();
			
            if(!empty($retval)) {
                return stripslashes($retval);
            }

        }

        return "";
    }

    public function write($sess_id, $data)
    {
        $query = "INSERT INTO `" . $this->sess_tbl_name . "` (id, data)
                   VALUES (?, ?) 
                   ON DUPLICATE KEY 
                   UPDATE data = ?, last_updated = NOW()";

		$this->session_id 	= $this->database->real_escape_string($sess_id);
		$data 				= $this->database->real_escape_string($data);

        $stmt = $this->database->prepare($query);
        $stmt->bind_param(
			"sss",
			$this->session_id,
			$data,
			$data
		);

		if ($stmt->execute()) {
			$this->database->commit();
			return true;
		}
		else {
			return false;
		}
    }

    public function destroy($sess_id)
    {
		$query = "DELETE FROM `" . $this->sess_tbl_name . "` WHERE id = ?";

        $stmt = $this->database->prepare($query);
		$this->session_id = $this->database->real_escape_string($this->session_id);
        $stmt->bind_param(
			"s",
			$this->session_id
		);

        if ($stmt->execute()) {
			$this->database->commit();
			return true;
		}
		else {
			return false;
		}
    }

    public function gc($max_life)
    {
		$this->sess_expire = $this->database->real_escape_string($max_life);
        $query = "DELETE FROM `" . $this->sess_tbl_name . "` 
                        WHERE UNIX_TIMESTAMP(last_updated) + " .
                        $this->sess_expire . " <= UNIX_TIMESTAMP(NOW())";

        $this->database->query($query);

        return;
    }

}
?>
