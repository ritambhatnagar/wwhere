<?php

class Model_group extends CI_Model {

    private $primary_key;
    private $main_table;
    public $errorCode;
    public $errorMessage;

    public function __construct() {
        parent::__construct();
        $this->main_table = "group_master";
        $this->primary_key = "iGroupId";
    }

    function insert($data = array()) {
        $this->db->insert($this->main_table, $data);

        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function update($data = array(), $where = '') {
        $this->db->where($where);
        $res = $this->db->update($this->main_table, $data);
        //$rs = mysqli_affected_rows();
//        echo $this->db->last_query();exit;
        return $res;
    }

    function getData($fields = '', $join_ary = array(), $condition = '', $orderby = '', $groupby = '', $having = '', $climit = '', $paging_array = array(), $reply_msgs = '', $like = array()) {

        if ($fields == '') {
            $fields = "*";
        }

        if ($fields != '') {
            $this->db->select($fields, false);
        }

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
        $list_data = $this->db->get()->result_array();
//        echo $this->db->last_query();exit;
        return $list_data;
    }

    function delete($where = '') {
        $this->db->where($where);
        $this->db->delete($this->main_table);
        //  echo $this->db->last_query();exit;
        return 'deleted';
    }

    function query($sql) {

        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    public function getUserValidation($usertype = '', $value = '', $cond = '', $table = '') {
        if ($usertype != '' && $value != '') {
            $this->db->select($usertype);

            if ($table == '') {
                $table = $this->main_table;
            }
            if ($cond != '') {
                $this->db->where($cond);
            }

            if (is_array($usertype)) {
                foreach ($usertype as $key => $type_value) {
                    $this->db->where($type_value, $value[$key]);
                }
            } else {
                $this->db->where($usertype, $value);
            }
//echo $table;
            $user_data = $this->db->get($table)->result_array();
//            echo $this->db->last_query();exit;
            if (is_array($user_data) && count($user_data) > 0) {
                echo "false";
            } else {
                echo "true";
            }
        } else {
            echo "false";
        }
    }

    public function getGroupMasterData($fields = '', $cond = '') {
        if ($fields == '') {
            $fields = '*';
        }
        $this->db->select($fields);
        $this->db->from('group_master');
        if ($cond != '') {
            $this->db->where($cond);
        }

        $group_data = $this->db->get()->result_array();
        return $group_data;
    }

    function getTotalGroup() {
        $id = $this->session->userdata('iUserId');
        $query = "SELECT COUNT(*) as tot FROM " . $this->main_table . " WHERE iUserId = '$id'";
        $result = $this->db->query($query)->result_array();
        return $result;
    }

    function insertData($data = array(), $table = '') {
        if ($table != '') {
            $result = $this->db->insert($table, $data);
        }
        return $result;
    }

    function deleteData($where = '', $table = '') {
        $this->db->where($where);
        if ($table != '') {
            $this->db->delete($table);
        } else {
            $this->db->delete($this->main_table);
        }
        //  echo $this->db->last_query();exit;
        return 'deleted';
    }

    function updateData($data = array(), $where = '', $table = '') {
        $this->db->where($where);
        $res = $this->db->update($table, $data);
        //$rs = mysqli_affected_rows();
//        echo $this->db->last_query();exit;
        return $res;
    }

}
