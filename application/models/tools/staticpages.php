<?php

class staticpages extends CI_Model {

	function __construct() {
		$this->main_table = "page_settings";
		$this->primary_key = "iPageId";
	}

	function insert($data) {
		$this->db->insert($this->main_table, $data);
        return $this->db->insert_id();
	}

	function update($data, $id) {
		$this->db->where($this->primary_key, $id);
		return $this->db->update($this->main_table, $data);
	}


	function checkRecordAlreadyExists($field_name, $field_value, $id, $mode) {
		$exists = false;
		if ($mode == 'Add') {
			$this->db->select($this->primary_key);
			$this->db->where($field_name, $field_value);
			$data = $this->db->get($this->main_table)->result_array();
			if ($data[0][$this->primary_key] > 0) {
				$exists = true;
			}
		} else if ($mode == 'Update') {
			$this->db->select($this->primary_key);
			$this->db->where($field_name, $field_value);
			$this->db->where($this->primary_key . " !=", $id);
			$data = $this->db->get($this->main_table)->result_array();
			if ($data[0][$this->primary_key] > 0) {
				$exists = true;
			}
		}
		return $exists;
	}

	function getStaticpages($extracond = "", $field = "", $orderby = "", $limit = "", $iLangugeId = "") {
        if ($field == "") {
            $field = "$this->main_table.*";
        }
        $this->db->select($field, false);
        $this->db->from($this->main_table);

        if ($extracond != "") {
            if (intval($extracond)) {
                $this->db->where($this->primary_key, $extracond);
            } else {
                $this->db->where($extracond);
            }
        }
        if ($orderby != "") {
            $this->db->order_by($orderby);
        }
        if ($limit != "") {
            list($offset, $limit) = @explode(",", $limit);
            $this->db->limit($offset, $limit);
        }

        $list_data = $this->db->get()->result_array();
        #echo $this->db->last_query();exit;
        return $list_data;
    }

	function getAllStaticpage() {
		$iAdminId = $this->session->userdata('iAdminId');
		$this->db->select("vPageCode, vUrl, vPageTitle");
		if ($iAdminId == '') {
			$this->db->where("eStatus", "Active");
		}
		$data = $this->db->get($this->main_table)->result_array();
		return $data;
	}

	function getStaticpage($pagecode) {
		$this->db->select("vPageTitle,tContent,tMetaTitle,tMetaKeyword,tMetaDesc");
		$this->db->where("vPageCode", $pagecode);
		$data = $this->db->get($this->main_table)->result_array();
		return $data;
	}

	function getMetaInformation($pagecode) {
		$this->db->select("tMetaTitle, tMetaKeyword, tMetaDesc");
		$this->db->where("vPageCode", $pagecode);
		$data = $this->db->get($this->main_table)->result_array();

		return $data;
	}

}
