<?php

class Model_log_history extends CI_Model {

    private $primary_key;
    private $main_table;
    public $errorCode;
    public $errorMessage;

    public function __construct() {
        parent::__construct();
        $this->main_table = "log_history";
        $this->primary_key = "iLogId";
    }
    
    function insert($data = array()) {
        
        $this->db->insert($this->main_table,$data);
//        echo $this->db->last_query();exit;    
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function update($data = array(), $where = '') {
//        pr($data);exit;
        $this->db->where($where);
        $res = $this->db->update($this->main_table, $data);
        //  echo $this->db->last_query();exit;   

        $rs = mysqli_affected_rows();
        return $rs;
    }
    
    function getData($fields = '', $join_ary = array(), $condition = '', $orderby = '', $groupby = '', $having = '', $climit = '', $paging_array = array(), $reply_msgs = '', $like = array()) {

        if ($fields == '') {
            $fields = "*";
        }

        $this->db->start_cache();

        if (trim($condition) != '') {
            $this->db->where($condition);
        }
        if (trim($groupby) != '') {
            $this->db->group_by($groupby);
        }
        if (trim($having) != '') {
            $this->db->having($having);
        }
        if ($orderby != '' && is_array($paging_array) && count($paging_array) == "0") {
            $this->db->order_by($orderby);
        }

        $this->db->from($this->main_table);
        $this->db->stop_cache();
        $list_data = $this->db->get()->result_array();
        //$this->session->set_userdata(array('query' => $this->db->last_query()));
        $this->db->flush_cache();
        //print_r($list_data);
        return $list_data;
    }

    function query($sql) {

        $data = $this->db->query($sql)->result_array();
        return $data;
    }
    
    function getResult(){
        $userid = $this->session->userdata('iUserId');
        
        $this->db->select('user.*,log_history.dLoginDate');
        $this->db->from('user');
        $this->db->join('log_history',"user.iUserId = log_history.iUserId");
        $this->db->where("user.iUserId",$userid);
        $this->db->order_by("log_history.dLoginDate DESC");
        $this->db->limit(2);
        $res = $this->db->get()->result_array();
//        echo $this->db->last_query();exit;
        return $res;

    }
    
}
