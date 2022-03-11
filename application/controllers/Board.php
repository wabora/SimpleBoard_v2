<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Board extends CI_Controller{
    
    function __construct(){
        parent::__construct();
        $this->load->model('board_m');
    }
	
    public function index(){
        $this->load->view('board/index');
    }
	
    public function get(){
		$word1 = $this->input->post('word1');
		$word2 = $this->input->post('word2');
		$sort_field = $this->input->post('sort_field');
		$sort_type = $this->input->post('sort_type');
		$rows_page = $this->input->post('rows_page');
		$rows_page = intval($rows_page);
		$now_page = $this->input->post('now_page');
		$now_page = intval($now_page);
		$num_rows = $this->board_m->get('num_rows', '', '', $word1, $word2, $sort_field, $sort_type);
		
		if ($now_page == null) 
		{
			$now_page = 1;
		}
		$total_page = ceil($num_rows/$rows_page);
		if ($total_page <= 0)
		{
			$total_page = 1;
		}
		if ($now_page == 1){ 
			$start_row = 0; 
		} else { 
			$start_row = ($now_page * $rows_page) - $rows_page; 
		}
		
		$result['num_rows'] = $num_rows;
		$result['total_page'] = $total_page;
		$result['rows'] = $this->board_m->get('', $start_row, $rows_page, $word1, $word2, $sort_field, $sort_type);
		
        echo json_encode($result);
    }
	
	public function insert(){
		$data = array(
            'subject'=> $this->input->post('subject'),
            'content'=> $this->input->post('content')    
        );
		if($this->board_m->insert($data) != false){
			$result['msg'] = 'success';
		}
		echo json_encode($result);
	}
	
	public function update(){
		$idx = $this->input->post('idx');
		$data = array(
            'subject'=> $this->input->post('subject'),
            'content'=> $this->input->post('content')
            );
			
        if($this->board_m->update($idx,$data) != false){
            $result['msg'] = 'success';
        }
		echo json_encode($result);
	}
	
	public function del(){
		$idxs = $this->input->post('idxs');
		
		if($this->board_m->del($idxs) != false){
			$result['msg'] = 'success';
		}		
		echo json_encode($result);
    }
}
