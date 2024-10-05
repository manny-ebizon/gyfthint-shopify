<?php 

class UserModel extends Model {
    function clean($string) {   
        // $string = str_replace('"', "&quot;", $string);
        // $string = str_replace("'", "&apos;", $string);
        // $string = str_replace("\u", "&bsol;u", $string);
        return trim($string);
    }

    function checkIfExistGlobal($table, $index, $value, $del=false) {
        if ($del==false) {
            $sql = "SELECT * FROM $table WHERE $index = '$value' AND is_deleted = '0'";
        } else {
            $sql = "SELECT * FROM $table WHERE $index = '$value'";
        }
        
        $result = $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return false;
        }
    }

    function addGlobal($table,$data) {  
        $arrVal = $arrKey = [];
        foreach ($data as $key => $value) {
            $arrKey[] = $key;
            $arrVal[] = "'".$this->clean($value)."'";    
        }
        $sql = "INSERT INTO ".$table." (".implode(",",$arrKey).") VALUES (".implode(",",$arrVal).")";
        $data = $this->db->query($sql);
        if ($data) {
            if($table=='roles' || $table=='users' || $table=='sites'){
                $sql = "SELECT max(id) as 'id' FROM " . $table;
                $result = $this->db->query($sql);
                $data = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {                
                        $data = $row;
                    } 
                }           
                return true;
            } else {
                return $data;
            }
        } else {
            return $sql;
        }
    }

    function getGlobalbyId($table,$id,$is_deleted=true) {
        if ($is_deleted===true) {
            $sql = "SELECT * FROM ".$table." WHERE id='".$id."' AND is_deleted='0'";
        } else {
            $sql = "SELECT * FROM ".$table." WHERE id='".$id."'";
        }
        
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data = $row;
            } 
        }           
        return $data; 
    }

    function getGlobalbySpecificField($table, $field, $value, $filter=null) {
        $filter_str = true;
        if ($filter!=null) {
            $filter_arr = [];
            foreach ($filter as $key => $value) {
                if($key=='site_id'){
                    $filter_arr[] = "su.site_id='".$value."'";
                }else {
                    $filter_arr[] = $key."='".$value."'";    
                }
            }
            $filter_str = implode(" AND ",$filter_arr);
        }

        if($table=='users'){
            $sql = "SELECT u.*, r.role_name FROM ".$table." as u INNER JOIN roles as r on r.id=u.role_id WHERE ".$field."='".$value."' AND ".$filter_str."";
        }if($table=='ticket_threads'){
            if($_SESSION['role_id']==4){
                $sql = "SELECT tt.*, u.first_name, u.last_name FROM ".$table." as tt INNER JOIN users as u ON u.id=tt.user_id WHERE ".$field."='".$value."' AND is_internal=0 AND ".$filter_str."";
            } else{
                $sql = "SELECT tt.*, u.first_name, u.last_name FROM ".$table." as tt INNER JOIN users as u ON u.id=tt.user_id WHERE ".$field."='".$value."' AND ".$filter_str."";
            }
        }  else {
            $sql = "SELECT * FROM ".$table." WHERE ".$field."='".$value."' AND ".$filter_str."";
        }

        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {      
                $data[] = $row;
            } 
        }     
        return $data; 
    }

    function getRowByField($table, $field, $value) {
        if($table=='users'){
            $sql = "SELECT u.*, r.role_name FROM ".$table." as u INNER JOIN roles as r on r.id=u.role_id WHERE ".$field."='".$value."'";
        } else {
            $sql = "SELECT * FROM ".$table." WHERE ".$field."='".$value."'";
        }

        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {      
                $data = $row;
            } 
        }     
        return $data; 
    }

    function getGlobal($table,$start=null,$end=null,$filter=null, $whereIn=null) {
        if ($start==null && $end==null) {
            $period = true;
        } else {
            $start_date = date("Y-m-d",strtotime($start))." 00:00:00";
            $end_date = date("Y-m-d",strtotime($end))." 23:59:59";
            $period = "p.created_on >= '".$start_date."' AND p.created_on <= '".$end_date."'";
        } 

        $filter_str = true;
        if ($filter!=null) {
            $filter_arr = [];
            foreach ($filter as $key => $value) {
                if ($key=="district") {
                    $filter_arr[] = "s.district='".$value."'";
                } else if($key=='site_id'){
                    $filter_arr[] = "su.site_id='".$value."'";
                }else {
                    $filter_arr[] = $key."='".$value."'";    
                }
            }
            $filter_str = implode(" AND ",$filter_arr);
        }

        $whereIn_str = true;
        if($whereIn!=null){
            $whereIn_arr = [];
            foreach ($whereIn as $key => $value) {
                $value_str = '("'.implode('","', $value).'")';
                $whereIn_arr[] = $key." IN ".$value_str." ";    
            }
            $whereIn_str = implode(" AND ", $whereIn_arr);
        }

        if($table=='users'){
            
            if($_SESSION['role_name'] == 'superadmin'){
                $sql = "SELECT u.*, r.role_name, r.role_description, su.site_id FROM ".$table." AS u inner join roles AS r ON r.id=u.role_id inner join site_users su ON su.user_id=u.id WHERE r.role_name='siteadmin' AND u.is_deleted='0' AND ".$period." AND ".$filter_str; //display siteadmin only
            } else if($_SESSION['role_name'] == 'siteadmin'){
                if($_SESSION['is_client']){
                    $sql = "SELECT u.*, r.role_name, r.role_description, su.site_id FROM ".$table." AS u inner join roles AS r ON r.id=u.role_id inner join site_users su ON su.user_id=u.id WHERE su.site_id=".$_SESSION['site_id']." AND u.is_deleted='0' AND ".$period." AND ".$filter_str;
                } else {
                    $sql = "SELECT u.*, r.role_name, r.role_description, su.site_id FROM ".$table." AS u inner join roles AS r ON r.id=u.role_id inner join site_users su ON su.user_id=u.id WHERE r.role_name!='client' AND u.id!=".(int)$_SESSION['login_id']." AND su.site_id=".$_SESSION['site_id']." AND u.is_deleted='0' AND ".$period." AND ".$filter_str;
                }
            } else if($_SESSION['role_name'] != 'superadmin' || $_SESSION['role_name'] != 'admin' || $_SESSION['role_name'] != 'siteadmin'){ //filter by site_id
                $sql = "SELECT u.*, r.role_name, r.role_description, su.site_id FROM ".$table." AS u inner join roles AS r ON r.id=u.role_id inner join site_users su ON su.user_id=u.id WHERE su.site_id=".$_SESSION['site_id']." AND u.is_deleted='0' AND ".$period." AND ".$filter_str;
            } else {
                $sql = "SELECT u.*, r.role_name, r.role_description, su.site_id FROM ".$table." AS u inner join roles AS r ON r.id=u.role_id inner join site_users su ON su.user_id=u.id WHERE AND u.is_deleted='0' AND ".$period." AND ".$filter_str; //default
            }
            
            //$sql = "SELECT u.*, r.role_name, r.role_description, su.site_id FROM ".$table." AS u inner join roles AS r ON r.id=u.role_id inner join site_users su ON su.user_id=u.id WHERE AND u.is_deleted='0' AND ".$period." AND ".$filter_str; //default
        } elseif ($table=='sites'){
            $sql = "SELECT s.*, u.first_name, u.last_name FROM ".$table." AS s inner join users AS u ON u.id=s.site_admin_id WHERE u.is_deleted='0' AND ".$period." AND ".$filter_str;
        } elseif($table=='tickets'){
            $sql = "SELECT t.*, o.uuid as 'o_uuid', o.order_id, u.first_name, u.last_name FROM tickets t JOIN orders o ON o.uuid=t.order_id JOIN users u ON u.uuid=o.user_id WHERE t.is_deleted='0' AND o.site_id=".$_SESSION['site_id']." AND ".$period." AND ".$filter_str. " AND " . $whereIn_str;
        } elseif($table=='ticketing_user'){
            $sql = "SELECT u.uuid, u.first_name, u.last_name, su.site_id, u.role_id, r.role_name, r.tier FROM users u INNER JOIN site_users su on su.user_id = u.id INNER JOIN roles r on r.id=u.role_id WHERE ". $filter_str;
        }
        else {
            $sql = "SELECT * FROM ".$table." WHERE is_deleted='0' AND ".$period." AND ".$filter_str;
        }

        $result = $this->db->query($sql);                        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($table=="coupons") {
                    if ($row['assigned_to']!="" && $row['assigned_to']!=null) {
                        $row['client_details'] = $this->getUserbyId($row['assigned_to']);
                    }
                }
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function deleteGlobalById($table, $id){
        $sql = "DELETE FROM $table WHERE id = '$id'";
        $result = $this->db->query($sql);
        return $result;
    }

    function updateGlobal($table,$id,$data) {
        $arrVal = [];
        foreach ($data as $key => $value) {
            if ($value=="NULL") {
                $arrVal[] = $key."=NULL";
            } else {
                $arrVal[] = $key."='".$this->clean($value)."'";
            }            
        }      
        $sql = "UPDATE ".$table." SET ".implode(",",$arrVal)." WHERE id='".$id."'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }    

    function getUser($email) {
        $sql = "SELECT * FROM users WHERE email='$email' AND is_deleted='0'";
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {           
                $data = $row;                
            }
        }
        return $data;         
    }

    function getUserbyRole($param) {
        $sql = "SELECT * FROM users WHERE role='".$param."' AND is_deleted='0' ORDER BY first_name ASC";
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {           
                $data[] = $row;                
            }
        }
        return $data;         
    }

    function fetchAllRoles() {
        $sql = "SELECT DISTINCT role FROM users ORDER BY role ASC";
        $result = $this->db->query($sql);                        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) { 
                if ($row['role']!="admin") {
                   $data[] = $row['role'];
                }                           
            }
        }        
        return $data;  
    }
    
    function getAllUsers() {
        $sql = "SELECT * FROM users WHERE is_deleted!='2' AND role!='admin' ORDER BY name ASC";
        $result = $this->db->query($sql);                        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {            
                $data[] = $row;                
            }
        }        
        return $data;  
    }
    
    function getUserbyId($id) {                      
        $sql = "SELECT * FROM users WHERE uuid LIKE '$id'";
        $result = $this->db->query($sql);        
        $data = [];
        if ($result) {
            $data = $result->fetch_assoc();
            unset($data['password']);
            unset($data['id']);
        }
        
        return $data;        
    }

    function getUserbyEmail($email) {
        $sql = "SELECT uuid, email, first_name, created_on, is_deleted FROM users WHERE email='$email'";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    function getEmailUnique($email) {
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $this->db->query($sql);                        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {            
                $data[] = $row;                
            }
        }
        if (count($data)>0) {
            return false;
        } else {
            return true;
        }
    }

    function changePassword($id,$data) {
        $sql = "SELECT uuid FROM users WHERE uuid = '".$id."' AND password = '".sha1($data['oldpass'])."' AND is_deleted = '0'";
        $result = $this->db->query($sql);    
        if($result->fetch_assoc()!=null)    
        {            
            $sql = "UPDATE users SET password='".sha1($data['newpass'])."' WHERE uuid='".$id."'";
            $result2 = $this->db->query($sql);            
            if ($this->db->query($sql) === TRUE) {
                return true;
            } else {
                return false;
            }
        }
        else {
            return false;;
        }
    }

    function delete_queue_email($id) {
        $sql = "DELETE FROM email_queue WHERE id='$id'";
        $result = $this->db->query($sql);
    }

    function fetch_queue_email() {        
        $sql = "SELECT * FROM email_queue WHERE send_to !='' ORDER BY id ASC LIMIT 1";
        $result = $this->db->query($sql);        
        return $result->fetch_assoc();
    }

    function queue_email($to,$subject,$body,$from=null) {
        $body = str_replace("'", "&apos;", $body);
        $sql = "INSERT INTO email_queue (send_to, subject, body, send_from) VALUES ('".$to."','".$subject."','".$body."','".$from."')";   
        return $this->db->query($sql);
    }        

    function deactivateUser($id) {
        $sql = "UPDATE users SET is_deleted='2' WHERE uuid='".$id."'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function activateUser($id) {
        $sql = "UPDATE users SET is_deleted='0' WHERE uuid='".$id."'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function updateSuggestedHints($id,$url) {
        $sql = "UPDATE merchant_suggestions SET hint_url='$url',updated_at='".date("Y-m-d H:i:s")."' WHERE id='".$id."'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function loginPhone($phone) {
        $sql = "
            SELECT * 
            FROM customers 
            WHERE REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '') 
            LIKE REPLACE(REPLACE(REPLACE('$phone', '+', ''), ' ', ''), '-', '')";
        $result = $this->db->query($sql);        
        return $result->fetch_assoc();
    }

    function loginUser($data) {
        if (ENV=="DEVELOPMENT" || true) {
            // $sql = "SELECT u.*, r.role_name, r.role_description, r.tier, su.site_id FROM users as u INNER JOIN roles as r on r.id=u.role_id INNER JOIN site_users as su on su.user_id=u.id WHERE u.email = '".$data['email']."' AND u.is_deleted = '0'";
            $sql = "SELECT u.*,r.role_name as role FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.email LIKE '".$data['email']."'";
        } else {
            $sql = "SELECT u.*, r.role_name, r.role_description, r.tier, su.site_id FROM users as u INNER JOIN roles as r on r.id=u.role_id INNER JOIN site_users as su on su.user_id=u.id WHERE u.email = '".$data['email']."' AND u.password = '".sha1($data['password'])."' AND u.is_deleted = '0'";    
        }
        $result = $this->db->query($sql);        
        return $result->fetch_assoc();       
    }

    function checkPassword($email,$password) {             
        $sql = "SELECT * FROM users WHERE email = '".$email."' AND password = '".sha1($password)."' AND is_deleted = '0'";
        $result = $this->db->query($sql);        
        return $result->fetch_assoc();       
    }
    
    function resetPassword($email,$password) {        
        $ue = $this->userExist($email);
        if($ue) {
            $sql = "UPDATE users SET password='".sha1($password)."' WHERE email='".$email."'";
            if ($this->db->query($sql) === TRUE) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "Email address doesn't exist.";
        }        
    }

    function fetchSalesmanData($user_id) {
        $sql = "SELECT * FROM salesman WHERE user_id='".$user_id."'";
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            } 
        }           
        return $data; 
    }

    function fetchClientByCode($code) {
        $sql = "SELECT * FROM clients WHERE client_code='".$code."'";
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            } 
        }           
        return $data; 
    }

    function fetchUserByCode($code) {
        $sql = "SELECT * FROM users WHERE code='".$code."'";
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            } 
        }           
        return $data; 
    }

    function fetchProductByCode($code) {
        $sql = "SELECT * FROM products WHERE product_code='".$code."'";
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            } 
        }           
        return $data;
    }

    function fetchClientsByUserId($user_id) {
        $sql = "SELECT clients.*
            FROM clients
            INNER JOIN assigned_clients ON clients.uuid = assigned_clients.client_id
            WHERE assigned_clients.user_id = '$user_id'";
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchProductsByUserId($user_id) {
        $sql = "SELECT DISTINCT p.*
            FROM products p
            INNER JOIN assigned_client_products acp ON p.uuid = acp.product_id
            INNER JOIN assigned_clients ac ON acp.client_id = ac.client_id
            WHERE ac.user_id = '$user_id'";
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchProductsByClientId($client_id) {
        $sql = "SELECT DISTINCT p.*
            FROM products p
            INNER JOIN assigned_client_products acp ON p.uuid = acp.product_id WHERE acp.client_id = '$client_id'";
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchLastOrder($user_id=null) {
        $sql = "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY id DESC Limit 1";
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            } 
        }           
        return $data;
    }

    function fetchOrdersByUserId($user_id,$status=null,$service=null) {
        $service_str = $status_str = true;
        if ($status!=null && $status=="current") {
            $status_str = "(orders.status='Awaiting Payment' || orders.status='Paid' || orders.status='Client Approval Required')";
        } else if ($status!=null && $status=="confirmed") {
            $status_str = "(orders.status='Confirmed Order' || orders.status='In Process')";
        } else {
            $status_str = "orders.status='".$status."'";
        }

        if ($service!=null) {
            if ($service=="blogger outreach") {
                $service_str = "service='".$service."'";
            } else if ($service=="all") {
                $service_str = true;
            } else {
                $service_str = "service!='blogger outreach'";
            }            
        }
        if ($user_id==null) {
            $sql = "SELECT * FROM orders WHERE ".$status_str." AND ".$service_str;
        } else {
            $sql = "SELECT * FROM orders WHERE user_id = '$user_id' AND ".$status_str." AND ".$service_str;
        }
        
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['coupon']!=null && $row['coupon']!='') {
                    $coupon = $this->checkCoupon($row['coupon']);
                    if ($coupon['coupon_type']=="percentage") {
                        $discount = $row['amount']*($coupon['coupon_value']/100);
                        $row['coupon_discount'] = $discount;
                    } else if ($coupon['coupon_type']=="value") {
                        $row['coupon_discount'] = $coupon['coupon_value'];
                    } else {
                        $row['coupon_discount'] = 0;
                    }
                } else {
                    $row['coupon_discount'] = 0;
                }
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchOrders($status=null) {
        $status_str = true;
        if ($status!=null && $status=="current") {
            $status_str = "orders.status!='cancelled' && orders.status!='completed'";
        } else {
            $status_str = "orders.status='".$status."'";
        }
        $sql = "SELECT orders.*, clients.client_code, clients.client_name, clients.client_address1
            FROM orders
            JOIN clients ON orders.client_id = clients.uuid
            WHERE ".$status_str;
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchOrderById($order_id,$user_id=null) {
        if ($user_id!=null) {
            $sql = "SELECT * FROM orders WHERE user_id='$user_id' AND uuid='$order_id'";
        } else {
            $sql = "SELECT * FROM orders WHERE uuid='$order_id'";
        }    
        
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['coupon']!=null && $row['coupon']!='') {
                    $coupon = $this->checkCoupon($row['coupon']);
                    if ($coupon['coupon_type']=="percentage") {
                        $discount = $row['amount']*($coupon['coupon_value']/100);
                        $row['coupon_discount'] = $discount;
                    } else if ($coupon['coupon_type']=="value") {
                        $row['coupon_discount'] = $coupon['coupon_value'];
                    } else {
                        $row['coupon_discount'] = 0;
                    }
                } else {
                    $row['coupon_discount'] = 0;
                }
                $data = $row;
            } 
        }           
        return $data;
    }

    function checkCoupon($coupon,$user_id=null) {
        if ($user_id==null) {
            $sql = "SELECT * FROM coupons WHERE coupon_code LIKE '".$coupon."'";
        } else {
            $sql = "SELECT * FROM coupons WHERE coupon_code LIKE '".$coupon."' AND assigned_to='$user_id'";
        }
        
        $result = $this->db->query($sql);
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data = $row;
            } 
        }           
        return $data;
    }

    function fetchInvoices($user_id=null) {
        if ($user_id==null) {
            $sql = "SELECT * FROM orders WHERE is_deleted='0'";
        } else {
            $sql = "SELECT * FROM orders WHERE is_deleted='0' AND user_id='$user_id'";
        }
        
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row['coupon']!=null && $row['coupon']!='') {
                    $coupon = $this->checkCoupon($row['coupon']);
                    if ($coupon['coupon_type']=="percentage") {
                        $discount = $row['amount']*($coupon['coupon_value']/100);
                        $row['coupon_discount'] = $discount;
                    } else if ($coupon['coupon_type']=="value") {
                        $row['coupon_discount'] = $coupon['coupon_value'];
                    } else {
                        $row['coupon_discount'] = 0;
                    }
                } else {
                    $row['coupon_discount'] = 0;
                }
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchUserSidebarMenu($role_id=NULL) {
        $sql = "SELECT rm.module_id, m.module_name, m.module_description, m.module_icon, m.module_nav_link FROM rolemodules rm INNER JOIN modules m ON rm.module_id = m.id WHERE role_id='".$role_id."' AND rm.is_deleted='0' AND rm.can_read='1' GROUP BY module_id ORDER BY m.module_order";
       
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data; 
    }

    function fetchUsersRoleAccess($role_id=NULL, $module_name=NULL) {
        $sql = "SELECT m.module_name, m.module_icon, rm.* FROM rolemodules rm INNER JOIN modules m ON rm.module_id = m.id WHERE rm.role_id='".$role_id."' AND m.module_name='".$module_name."' AND rm.is_deleted='0' AND m.is_deleted='0' AND rm.can_read='1' ORDER BY m.module_order";
       
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data = $row;
            } 
        }           
        return $data; 
    }

    function fetchModulesJoiningRoleModules($id=NULL){
        $sql = "SELECT m.id as module_id, m.module_name, rm.* FROM modules as m RIGHT JOIN rolemodules as rm ON m.id=rm.module_id JOIN roles as r ON r.id=rm.role_id  WHERE r.uuid='".$id."' ORDER BY m.module_order";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchDynamicRoles($site_id=0, $is_fixed_roles=0){
        $sql = "SELECT * FROM roles WHERE site_id='$site_id' AND is_fixed_role='$is_fixed_roles'";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function updateSiteUsersByUserId($site_id,$user_id) {  
        $sql = "UPDATE site_users SET site_id=".$site_id." WHERE user_id='".$user_id."'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function updateRoleModules($role_id, $module_id, $data) {
        $arrVal = [];
        foreach ($data as $key => $value) {
            if ($value=="NULL") {
                $arrVal[] = $key."=NULL";
            } else {
                $arrVal[] = $key."='".$this->clean($value)."'";
            }            
        }      
        $sql = "UPDATE rolemodules SET ".implode(",",$arrVal)." WHERE role_id='$role_id' AND module_id='$module_id'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return  false;
        }
    }   
    
    function fetch_data_by_id($role_id, $module_id, $data) {
        $arrVal = [];
        foreach ($data as $key => $value) {
            if ($value=="NULL") {
                $arrVal[] = $key."=NULL";
            } else {
                $arrVal[] = $key."='".$this->clean($value)."'";
            }            
        }      
        $sql = "UPDATE rolemodules SET ".implode(",",$arrVal)." WHERE role_id='$role_id' AND module_id='$module_id'";            
        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            return  false;
        }
    }   

    function fetchMerchants() {
        $sql = "SELECT s.*,l.label FROM stores s LEFT JOIN merchant_licenses m ON s.id = m.store_id LEFT JOIN licenses l ON l.id=m.license_id";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchLicenses($integration=false) {
        if ($integration===false) {
            $sql = "SELECT * FROM licenses WHERE is_deleted='0' ORDER BY integration, order_sort ASC";
        } else {
            $sql = "SELECT * FROM licenses WHERE integration LIKE '$integration' AND is_deleted='0' ORDER BY order_sort ASC";
        }
        
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function loginAccessToken($access_token) {
        $sql = "SELECT u.* FROM users u LEFT JOIN stores s ON s.user_id=u.id WHERE s.access_token='$access_token'";
        $result = $this->db->query($sql);
        
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data = $row;
            } 
        }           
        return $data;   
    }

    function fetchHints() {
        $sql = "SELECT * FROM hints";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchCustomers() {
        $sql = "SELECT * FROM customers";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchSuggestedHintsByStoreID($store_id) {
        $sql = "SELECT * FROM merchant_suggestions WHERE store_id='$store_id'";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }
    function fetchStoresByUserId($user_id) {
        $sql = "SELECT * FROM stores WHERE user_id='$user_id'";
        $result = $this->db->query($sql);
        
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data[] = $row;
            } 
        }           
        return $data;
    }

    function fetchStoresLicenseByStoreId($store_id) {
        $sql = "SELECT ml.*,l.label FROM merchant_licenses ml LEFT JOIN licenses l ON ml.license_id = l.id WHERE ml.store_id='$store_id'";
        $result = $this->db->query($sql);
        
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data = $row;
            } 
        }           
        return $data;
    }

    function fetchStoreByDomain($domain) {
        $sql = "SELECT * FROM stores WHERE myshopify_domain='$domain'";
        $result = $this->db->query($sql);        
        $data = false;
        if ($result) {
            while ($row = $result->fetch_assoc()) {                
                $data = $row;
            } 
        }           
        return $data;
    }
} 

?>