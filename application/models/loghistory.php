<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of systemsettings
 *
 * @author nilay
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Loghistory extends CI_Model {

    function insert($data = array()) {
        $insertId = $this->db->insert('log_history', $data);
        $insertId = $this->db->insert_id();
        return $insertId;
    }

    function update($data = array(), $where) {
        if ($where != '') {
            if (intval($where)) {
                $this->db->where('iLogId', $where);
            } else {
                $this->db->where($where);
            }
        }
        return $this->db->update('log_history', $data, false);
    }

    function getLogHistory($iAdminId) {
        $this->db->select('*');
        $this->db->from('log_history');
        $this->db->where('log_history.iUserId', $iAdminId);
        $this->db->order_by('dLoginDate DESC');
        $this->db->limit('1', '0');
        return $this->db->get()->result_array();
    }

    public function updateLogoutUser($data = '') {
        if ($data > 0) {
            $this->db->set('dLogoutDate', date('Y-m-d H:i:s'));
            $this->db->where('iLogId', $data);
            $returnData = $this->db->update('log_history');
            return $returnData;
        }
    }

}