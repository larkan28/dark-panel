<?php

class MySQL_DataBase
{
    protected $conn;

    // VARS: MySQL Connection
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "panel";

    // VARS: MySQL Tables
    private $table_hosts = "hosts";
    private $table_users = "users";
    private $table_queue = "queue";
    private $table_servers = "servers";
    private $table_subusers = "sub_users";

    // VARS: Host Table Index
    public const HOST_ID    = 0;
    public const HOST_NAME  = 1;
    public const HOST_IP    = 2;

    // VARS: Server Table Index
    public const SERVER_ID          = 0;
    public const SERVER_NAME        = 1;
    public const SERVER_PORT        = 2;
    public const SERVER_MAXSLOTS    = 3;
    public const SERVER_GAMEID      = 4;
    public const SERVER_HOSTID      = 5;
    public const SERVER_OWNERID     = 6;
    
    // VARS: User Table Index
    public const USER_ID        = 0;
    public const USER_PARENT    = 1;
    public const USER_NAME      = 2;
    public const USER_PASS      = 3;
    public const USER_MAIL      = 4;
    public const USER_FNAME     = 5;
    public const USER_LNAME     = 6;
    public const USER_ISADMIN   = 7;
    public const USER_REGDATE   = 8;
    public const USER_LOGDATE   = 9;

    // VARS: Subuser Table Index
    public const SUBUSER_ID         = 0;
    public const SUBUSER_USERID     = 1;
    public const SUBUSER_SERVERID   = 2;
    public const SUBUSER_PRIVILEGES = 3;

    // MYSQL FUNCTIONS
    function connectMySQL ()
    {
		if (!extension_loaded("mysqli"))
            return -1;

		$this->conn = mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);

		if (!$this->conn)
			return -2;

		return TRUE;
    }

    // HOST FUNCTIONS
    function host_Add ($host_name, $host_ip) 
    {
        if ($this->host_Exists($host_name, $host_ip) === TRUE)
            return -3;
            
        $sql = "INSERT INTO " . $this->table_hosts . " (Name, IP) VALUES (?, ?);";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "ss", $host_name, $host_ip);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function host_Exists ($host_name, $host_ip) 
    {
        $sql = "SELECT * FROM " . $this->table_hosts . " WHERE Name=? OR IP=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "ss", $host_name, $host_ip);
        mysqli_stmt_execute($stm);
        
        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return -2;
    }

    function host_Edit ($host_data) 
    {
        $sql = "UPDATE " . $this->table_hosts . " SET Name=?, IP=? WHERE ID=?;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

        mysqli_stmt_bind_param($stm, "sss", 
            $host_data[self::HOST_NAME], 
            $host_data[self::HOST_IP], 
            $host_data[self::HOST_ID]);

        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function host_Delete ($host_id) 
    {
        $sql = "DELETE FROM " . $this->table_hosts . " WHERE ID=?;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $host_id);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function host_GetData ($host_id) 
    {
        $sql = "SELECT * FROM " . $this->table_hosts . " WHERE ID=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return (array) null;

		mysqli_stmt_bind_param($stm, "s", $host_id);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return array($row['ID'], $row['Name'], $row['IP']);
        }

        mysqli_stmt_close($stm);
        return (array) null;
    }

    function host_GetAll () 
    {
        $hosts = [];

        $sql = "SELECT * FROM " . $this->table_hosts . ";";
        $res = mysqli_query($this->conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $i = 0;
            
            while ($row = mysqli_fetch_assoc($res)) {
                $hosts[$i][0] = $row['ID'];
                $hosts[$i][1] = $row['Name'];
                $hosts[$i][2] = $row['IP'];
                
                $i++;
            }
        }

        return $hosts;
    }

    // SERVER FUNCTIONS
    function server_Add ($server_name, $server_port, $server_slots, $server_game, $server_host, $server_owner)
    {
        if ($this->server_Exists($server_host, $server_port) === TRUE)
            return -3;
            
        $sql = "INSERT INTO " . $this->table_servers . " (Name, Port, MaxSlots, GameID, HostID, OwnerID) VALUES (?, ?, ?, ?, ?, ?);";
        $stm = mysqli_stmt_init($this->conn);

        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "ssssss", $server_name, $server_port, $server_slots, $server_game, $server_host, $server_owner);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function server_Edit ($server_data)
    {
        $sql = "UPDATE " . $this->table_servers . " SET Name=?, Port=?, HostID=?, OwnerID=?, MaxSlots=? WHERE ID=?;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

        mysqli_stmt_bind_param($stm, "ssssss", 
            $server_data[self::SERVER_NAME],
            $server_data[self::SERVER_PORT],
            $server_data[self::SERVER_HOSTID],
            $server_data[self::SERVER_OWNERID],
            $server_data[self::SERVER_MAXSLOTS],
            $server_data[self::SERVER_ID]);

        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function server_Exists ($server_host, $server_port)
    {
        $sql = "SELECT * FROM " . $this->table_servers . " WHERE HostID=? AND Port=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "ss", $server_host, $server_port);
        mysqli_stmt_execute($stm);
        
        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return -2;
    }

    function server_Delete ($server_id)
    {
        $sql = "DELETE FROM " . $this->table_servers . " WHERE ID=?;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $server_id);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function server_GetData ($server_id) 
    {
        $sql = "SELECT * FROM " . $this->table_servers . " WHERE ID=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return (array) null;

		mysqli_stmt_bind_param($stm, "s", $server_id);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            $output = (array) null;

            $output[0] = $row['ID'];
            $output[1] = $row['Name'];
            $output[2] = $row['Port'];
            $output[3] = $row['MaxSlots'];
            $output[4] = $row['GameID'];
            $output[5] = $row['HostID'];
            $output[6] = $row['OwnerID'];

            mysqli_stmt_close($stm);
            return $output;
        }

        mysqli_stmt_close($stm);
        return (array) null;
    }

    function server_GetAll ($server_owner = 0) 
    {
        $servers = [];

        $sql = "SELECT * FROM " . $this->table_servers . ($server_owner != 0 ? (" WHERE OwnerID=" . $server_owner) : "") . ";";
        $res = mysqli_query($this->conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $i = 0;
            
            while ($row = mysqli_fetch_assoc($res)) {
                $servers[$i][0] = $row['ID'];
                $servers[$i][1] = $row['Name'];
                $servers[$i][2] = $row['Port'];
                $servers[$i][3] = $row['MaxSlots'];
                $servers[$i][4] = $row['GameID'];
                $servers[$i][5] = $row['HostID'];
                $servers[$i][6] = $row['OwnerID'];
                
                $i++;
            }
        }

        return $servers;
    }

    // DAEMON FUNCS
    function query_Add ($query_action)
    {
        if ($this->query_Exists($query_action) === TRUE)
        {
            $this->query_Set($query_action, -11);
            return TRUE;
        }
            
        $sql = "INSERT INTO " . $this->table_queue . " (Query) VALUES (?);";
        $stm = mysqli_stmt_init($this->conn);
            
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $query_action);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function query_Set ($query_action, $query_status)
    {
        $sql = "UPDATE " . $this->table_queue . " SET Status=? WHERE Query=?;";
        $stm = mysqli_stmt_init($this->conn);
            
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "ss", $query_status, $query_action);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function query_Exists ($query_action)
    {
        $sql = "SELECT * FROM " . $this->table_queue . " WHERE Query=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $query_action);
        mysqli_stmt_execute($stm);
        
        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return -2;
    }

    function query_Remove ($query_action)
    {
        $sql = "DELETE FROM " . $this->table_queue . " WHERE Query=?;";
        $stm = mysqli_stmt_init($this->conn);
            
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $query_action);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function query_GetData ($query_action)
    {
        $sql = "SELECT * FROM " . $this->table_queue . " WHERE Query=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return (array) null;

		mysqli_stmt_bind_param($stm, "s", $query_action);
        mysqli_stmt_execute($stm);
        
        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return array($row['ID'], $row['Query'], $row['Status']);
        }

        mysqli_stmt_close($stm);
        return (array) null;
    }

    // USER FUNCTIONS
    function user_Add ($user_name, $user_pass, $user_admin = 0, $user_mail = "") 
    {
        if ($this->user_Exists($user_name) === TRUE)
            return FALSE;

        $query_names = "Username, Password, IsAdmin";
        $query_value = "?, ?, ?";

        if (!empty($user_mail))
        {
            $query_names .= ", Email";
            $query_value .= ", ?";
        }

        $sql = "INSERT INTO " . $this->table_users . " (" . $query_names . ") VALUES (" . $query_value . ");";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return FALSE;
           
        if (empty($user_mail))
            mysqli_stmt_bind_param($stm, "sss", $user_name, $user_pass, $user_admin);
        else
            mysqli_stmt_bind_param($stm, "ssss", $user_name, $user_pass, $user_admin, $user_mail);
            
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return FALSE;
    }

    function user_Edit ($user_data)
    {
        $sql = "UPDATE " . $this->table_users . " SET Username=?, Password=?, Email=?, IsAdmin=?, FName=?, LName=? WHERE ID=?;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

        mysqli_stmt_bind_param($stm, "sssssss", 
            $user_data[self::USER_NAME],
            $user_data[self::USER_PASS],
            $user_data[self::USER_MAIL],
            $user_data[self::USER_ISADMIN],
            $user_data[self::USER_FNAME],
            $user_data[self::USER_LNAME],
            $user_data[self::USER_ID]);

        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function user_Exists ($user_name) 
    {
        $sql = "SELECT * FROM " . $this->table_users . " WHERE Username=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $user_name);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return -2;
    }

    function user_Delete ($user_id)
    {
        $sql = "DELETE FROM " . $this->table_users . " WHERE ID=?;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

		mysqli_stmt_bind_param($stm, "s", $user_id);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }

    function user_GetData ($user_name) 
    {
        $sql = "SELECT * FROM " . $this->table_users . " WHERE Username=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return (array) null;

		mysqli_stmt_bind_param($stm, "s", $user_name);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            $output = (array) null;

            $output[0] = $row['ID'];
            $output[1] = $row['ParentID'];
            $output[2] = $row['Username'];
            $output[3] = $row['Password'];
            $output[4] = $row['Email'];
            $output[5] = $row['FName'];
            $output[6] = $row['LName'];
            $output[7] = $row['IsAdmin'];
            $output[8] = $row['RegDate'];
            $output[9] = $row['LogDate'];

            mysqli_stmt_close($stm);
            return $output;
        }

        mysqli_stmt_close($stm);
        return (array) null;
    }

    function user_GetDataID ($user_id) 
    {
        $sql = "SELECT * FROM " . $this->table_users . " WHERE ID=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return (array) null;

		mysqli_stmt_bind_param($stm, "s", $user_id);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            $output = (array) null;

            $output[0] = $row['ID'];
            $output[1] = $row['ParentID'];
            $output[2] = $row['Username'];
            $output[3] = $row['Password'];
            $output[4] = $row['Email'];
            $output[5] = $row['FName'];
            $output[6] = $row['LName'];
            $output[7] = $row['IsAdmin'];
            $output[8] = $row['RegDate'];
            $output[9] = $row['LogDate'];

            mysqli_stmt_close($stm);
            return $output;
        }

        mysqli_stmt_close($stm);
        return (array) null;
    }

    function user_GetAll () 
    {
        $users = [];

        $sql = "SELECT * FROM " . $this->table_users . ";";
        $res = mysqli_query($this->conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $i = 0;
            
            while ($row = mysqli_fetch_assoc($res)) {
                $users[$i][0] = $row['ID'];
                $users[$i][1] = $row['ParentID'];
                $users[$i][2] = $row['Username'];
                $users[$i][3] = $row['Password'];
                $users[$i][4] = $row['Email'];
                $users[$i][5] = $row['FName'];
                $users[$i][6] = $row['LName'];
                $users[$i][7] = $row['IsAdmin'];
                $users[$i][8] = $row['RegDate'];
                $users[$i][9] = $row['LogDate'];
                
                $i++;
            }
        }

        return $users;
    }

    function user_Login ($user_name, $user_password)
    {
        $sql = "SELECT * FROM " . $this->table_users . " WHERE Username=? LIMIT 1;";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return FALSE;

		mysqli_stmt_bind_param($stm, "s", $user_name);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return (password_verify($user_password, $row['Password']) ? $row['ID'] : FALSE);
        }

        mysqli_stmt_close($stm);
        return FALSE;
    }

    function user_SaveDate ($user_id)
    {
        $sql = "UPDATE " . $this->table_users . " SET LogDate=? WHERE ID=?;";
		$stm = mysqli_stmt_init($this->conn);
    
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;
        
		$currentDate = date('Y-m-d H:i:s');
		
		mysqli_stmt_bind_param($stm, "ss", $currentDate, $user_id);
		mysqli_stmt_execute($stm);
		
		if (mysqli_stmt_affected_rows($stm) > 0) {
            mysqli_stmt_close($stm);
            return TRUE;
        }
        
        mysqli_stmt_close($stm);
        return -2;
    }

    // SUBUSER FUNCTIONS
    function subuser_Add ($user_name, $user_pass, $user_mail, $user_parent)
    {
        if ($this->user_Exists($user_name) === TRUE)
            return FALSE;

        $sql = "INSERT INTO " . $this->table_users . " (ParentID, Username, Password, Email) VALUES (?, ?, ?, ?);";
        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return FALSE;

        mysqli_stmt_bind_param($stm, "ssss", $user_parent, $user_name, $user_pass, $user_mail);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return FALSE;
    }

    function subuser_Count ($parent_id)
    {
        $sql = "SELECT count(*) as Total FROM " . $this->table_users . " WHERE ParentID=" . $parent_id . ";";
        $res = mysqli_query($this->conn, $sql);

        $data = mysqli_fetch_assoc($res);
        return $data['Total'];
    }

    function subuser_GetAll ($parent_id)
    {
        $users = [];

        $sql = "SELECT * FROM " . $this->table_users . " WHERE ParentID=" . $parent_id . ";";
        $res = mysqli_query($this->conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $i = 0;
            
            while ($row = mysqli_fetch_assoc($res)) {
                $users[$i][0] = $row['ID'];
                $users[$i][1] = $row['ParentID'];
                $users[$i][2] = $row['Username'];
                $users[$i][3] = $row['Password'];
                $users[$i][4] = $row['Email'];
                $users[$i][5] = $row['FName'];
                $users[$i][6] = $row['LName'];
                $users[$i][7] = $row['IsAdmin'];
                $users[$i][8] = $row['RegDate'];
                $users[$i][9] = $row['LogDate'];
                
                $i++;
            }
        }

        return $users;
    }

    function subuser_AllAccess ($user_id)
    {
        $access = [];

        $sql = "SELECT * FROM " . $this->table_subusers . " WHERE UserID=" . $user_id . ";";
        $res = mysqli_query($this->conn, $sql);

        if (mysqli_num_rows($res) > 0) {
            $i = 0;
            
            while ($row = mysqli_fetch_assoc($res)) {
                $access[$i][0] = $row['ID'];
                $access[$i][1] = $row['UserID'];
                $access[$i][2] = $row['ServerID'];
                $access[$i][3] = $row['Privileges'];
                
                $i++;
            }
        }

        return $access;
    }

    function subuser_SetAccess ($user_id, $server_id, $privileges)
    {
        $sql = "";

        if ($this->subuser_ExistsAccess($user_id, $server_id) === TRUE)
            $sql = "UPDATE " . $this->table_subusers . " SET Privileges=? WHERE UserID=? AND ServerID=?;";
        else
            $sql = "INSERT INTO " . $this->table_subusers . " (Privileges, UserID, ServerID) VALUES (?, ?, ?);";

        $stm = mysqli_stmt_init($this->conn);
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return (array) null;

        mysqli_stmt_bind_param($stm, "sss", $privileges, $user_id, $server_id);
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return (array) null;
    }

    function subuser_ExistsAccess ($user_id, $server_id = 0)
    {
        $sql = "";
        $stm = mysqli_stmt_init($this->conn);

        if ($server_id == 0)
            $sql = "SELECT * FROM " . $this->table_subusers . " WHERE UserID=? LIMIT 1;";
        else
            $sql = "SELECT * FROM " . $this->table_subusers . " WHERE UserID=? AND ServerID=? LIMIT 1;";
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

        if ($server_id == 0)
            mysqli_stmt_bind_param($stm, "s", $user_id);
        else
            mysqli_stmt_bind_param($stm, "ss", $user_id, $server_id);
            
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
			
        if ($row = mysqli_fetch_assoc($result)) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }

        mysqli_stmt_close($stm);
        return -2;
    }

    function subuser_DeleteAccess ($user_id, $server_id = 0)
    {
        if ($this->subuser_ExistsAccess($user_id, $server_id) === FALSE)
            return TRUE;

        $sql = "";
        $stm = mysqli_stmt_init($this->conn);

        if ($server_id == 0)
            $sql = "DELETE FROM " . $this->table_subusers . " WHERE UserID=?;";
        else
            $sql = "DELETE FROM " . $this->table_subusers . " WHERE UserID=? AND ServerID=?;";
        
        if (!mysqli_stmt_prepare($stm, $sql))
            return -1;

        if ($server_id == 0)
            mysqli_stmt_bind_param($stm, "s", $user_id);
        else
            mysqli_stmt_bind_param($stm, "ss", $user_id, $server_id);
            
        mysqli_stmt_execute($stm);

        if (mysqli_stmt_affected_rows($stm) > 0) 
        {
            mysqli_stmt_close($stm);
            return TRUE;
        }
            
        mysqli_stmt_close($stm);
        return -2;
    }
}

?>