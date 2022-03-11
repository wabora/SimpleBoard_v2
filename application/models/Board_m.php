<?php 
class Board_m extends CI_Model{
    public function get($kind='', $start_row='', $rows_page='', $word1='', $word2='', $sort_field='', $sort_type=''){
		$this->db->order_by($sort_field ." ". $sort_type);
		$this->db->like('subject', $word1);
		$this->db->like('content', $word2);
        
		if ($kind == 'num_rows'){
			$this->db->from('board');
			$result=$this->db->count_all_results();
		} else {
			$query=$this->db->get('board',$rows_page,$start_row);
			$result=$query->result();
		}
		return $result;
    }
	
	public function insert($data){
        return $this->db->insert('board', $data);
    }
	
	public function update($idx,$field){
        $this->db->where('idx', $idx);
        return $this->db->update('board', $field);
	}
	
	public function del($idxs){
		$this->db->where_in('idx', explode(",", $idxs));
		return $this->db->delete('board');
    }
}
