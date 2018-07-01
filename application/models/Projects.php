<?php

class Projects extends CI_Model
{
    public function get_data($u_id){
        $query = $this->db->query("SELECT * FROM users WHERE uId='$u_id'");
        $link = $this->get_connection();
        $query2 = $link->query("SELECT * FROM projects, projectcollaboration WHERE projects.projectId=projectcollaboration.pId and projectcollaboration.uId='$u_id'");
        if($query2->num_rows <= 0){
            $query2 = $link->query("SELECT * FROM projects WHERE uId='$u_id'");
        }
        $query3 = $this->db->query("SELECT * FROM projects WHERE status='public' ORDER BY pId DESC");
        $data = array(
            'user_info' => $query,
            'my_projects' => $query2,
            'all_projects' => $query3
        );
        return $data;
    }
    public function get_connection(){
        return mysqli_connect('127.0.0.1', 'root', '', 'amader');
    }
    public function check_projectId($pId){
        $sql = "SELECT * FROM projects WHERE projectId='$pId'";
        $query= $this->db->query($sql);
        if($query->num_rows() == 0){
            return false;
        }else{
            return true;
        }
    }
    public function search_project($key){
        $query= $this->db->query("SELECT * FROM projects WHERE pName like '%$key%'");
        return $query;
    }
    public function restore_project(){
        $query= $this->db->query("SELECT * FROM projects");
        return $query;
    }
    public function update_name($data){
        $query = $this->db->query("UPDATE users SET fname='".$data['fname']."', lname='".$data['lname']."' WHERE uId='".$data['uId']."'");
        return $query;
    }
    public function update_password($password, $uId){
        $query = $this->db->query("UPDATE users SET password='$password' WHERE uId='$uId'");
        return $query;
    }
    public function check_password($password, $uId){
        $query = $this->db->query("SELECT * FROM users WHERE uId='$uId' and password='$password'");
        return $query;
    }
    public function get_user($uId){
        $query = $this->db->query("SELECT * FROM users WHERE uId='$uId'");
        return $query;
    }
    public function create_project($data){
        $sql = "INSERT INTO projects VALUES ('','".$data['uId']."','".$data['pName']."','".$data['projectId']."','".$data['description'].
            "','".$data['pCreation']."','".$data['status']."')";
        $res = $this->db->query($sql);
        return $res;
    }
    public function invited_users($email,$pId){
        $query = $this->db->query("SELECT * FROM users WHERE email='$email'");
        if($query->num_rows() > 0){
            foreach ($query->result() as $row) {
                $q = $this->db->query("SELECT * FROM projectcollaboration WHERE pId='$pId' AND uId='$row->uId'");
                if($q->num_rows() < 1) {
                    $query2 = $this->db->query("INSERT INTO projectcollaboration VALUES ('','$pId','$row->uId')");
                    //Sending Email
                    $subject = 'Translation Contribution Invitation';
                    $message = 'Existing User';
                    $this->send_email($email,$subject,$message);
                    return 'suc';
                }else{
                    return 'p_err';
                }
                break;
            }
        }else{
            $res = $this->db->query("SELECT * FROM inviteduser WHERE email='$email'");
            if($res->num_rows() <= 0) {
                $query = $this->db->query("INSERT INTO `inviteduser`(`iuId`, `pId`, `email`) VALUES ('','$pId','$email')");
                $subject = 'Translation Contribution Invitation';
                $message = 'New User';
                $this->send_email($email,$subject,$message);
                return 'suc';
            }else{
                return 'u_err';
            }
        }
    }
    public function check_project($uId, $pId){
        $query = $this->db->query("SELECT * FROM projects WHERE uId='$uId' and projectId='$pId'");
        if($query->num_rows() > 0){
            return $query;
        }else{
            return false;
        }
    }
    public function upload_sentences($data){
        $sql = "INSERT INTO sentences VALUES ('','".$data['uId']."','".$data['projectId']."','".$data['sentence']."','".$data['sCreation']."')";
        $this->db->query($sql);
    }
    public function upload_glossary($data){
        $data["bnBD"] = str_replace('"','',$data['bnBD']);
        $data["enUS"] = str_replace('"','',$data['enUS']);
        $sql="INSERT INTO glossary VALUES ('','".$data['uId']."',\"".$data['enUS']."\",\"".$data['bnBD']."\",'".$data['pos']."','".$data['description'].
            "','".$data['gCreation']."')";
        $this->db->query($sql);
    }
    public function get_glossary($page){
        $low_lim = 1000*($page-1);
        $h_lim = 1000*$page;
        $query = $this->db->query("SELECT * FROM glossary ORDER BY enUS ASC LIMIT $low_lim,$h_lim");
        return $query;
    }
    public function search_glossary($key,$low,$high){
        $offset = $low*$high;
        $query = $this->db->query("SELECT * FROM glossary WHERE enUS like '$key%%' OR bnBD like '$key%%' ORDER BY enUS ASC LIMIT 1000");
        return $query;
    }
    public function count_glossary(){
        $query = $this->db->query("SELECT * FROM glossary ORDER BY enUS ASC");
        return $query->num_rows();
    }

    public function send_email($to, $subject, $message){
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'amaderinfo7@gmail.com',
            'smtp_pass' => 'amaderinfo123',
            'mailtype'  => 'html',
            'charset'   => 'iso-8859-1'
        );
        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");
        $this->email->from('no_reply@amaderinfo.com','no_reply@amaderinfo.com');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);
        // Set to, from, message, etc.
        $this->email->send();
    }

}