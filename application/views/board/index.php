<!DOCTYPE html>
<html>
<head>
<title>CI & VUE</title>
<script src="<?php echo base_url()?>assets/js/vue.min.js"></script>
<script src="<?php echo base_url()?>assets/js/axios.min.js"></script>
</head>
 
<body>
<div id="app">
	<!-- search 값 확인 위한 div -->
	<div style="width:400px; height:105px; padding:10px; border-radius:3px; background-color:#cef3d6">
		제목 검색어 : {{search.word1}}<br>
		내용 검색어 : {{search.word2}}<br>
		정렬 필드 방식  : {{search.sort_field}} {{search.sort_type}}<br>
		전체/행 개수 : {{search.num_rows}}/{{search.rows_page}}<br>
		현재/전체 페이지 : {{search.now_page}}/{{search.total_page}}
	</div><br>
	
	<!-- 검색 Input 박스-->
	<input placeholder="제목 검색" type="text" size="24" name="word1" v-model="search.word1" @keyup="get">
	<input placeholder="내용 검색" type="text" size="24" name="word2" v-model="search.word2" @keyup="get"><br>
	
	<div>
		전체 : {{search.num_rows}} &nbsp;&nbsp;
		<!-- 화면에 출력할 행 개수 -->
		행 개수 : 
		<select v-model="search.rows_page" @change="get">
			<option value="5">5</option>
			<option value="10">10</option>
			<option value="25">25</option>
			<option value="50">50</option>
		</select>&nbsp;&nbsp;
	</div>
	<div>
		<!-- 페이징 -->
		페이지 : 
		<span v-if="search.now_page!=1"><a @click="paging(search.now_page, 'first')">&#171;</a></span>
		<span v-if="search.now_page!=1"><a @click="paging(search.now_page, 'priv')">&#60;</a></span>
		<input type="number" size="4" v-model="search.now_page" @keyup="get" @change="get" min="1">/ {{search.total_page}} 
		<span v-if="search.now_page!=search.total_page"><a @click="paging(search.now_page, 'next')">&#62;</a></span>
		<span v-if="search.now_page!=search.total_page"><a @click="paging(search.now_page, 'last')">&#187;</a></span><br><br>
	</div>
	
	<!-- 게시글 출력 table -->
	<table style="width:420px; border-collapse:collapse;">
		<thead style="background-color: #4CAF50;color: white;">
			<th><input type="checkbox" @click='check_all()' v-model='is_checkall'></th>
			<th height="30px">
				<a @click="sort('idx')">IDX
				<span v-if="search.sort_field=='idx'&search.sort_type=='asc'">&#8593;</span>
				<span v-if="search.sort_field=='idx'&search.sort_type=='desc'">&#8595;</span>
				</a>
			</th>
			<th>
				<a @click="sort('subject')">제목
				<span v-if="search.sort_field=='subject'&search.sort_type=='asc'">&#8593;</span>
				<span v-if="search.sort_field=='subject'&search.sort_type=='desc'">&#8595;</span>
				</a>
			</th>
			<th>내용</th>
		</thead>
		<tbody>
		<tr style="height:27px; border-bottom:1px solid #ddd;" v-for="row in rows">
			<td>
                <input type="checkbox" :value="row.idx" v-model="selected" @change='update_checkall()'>
            </td>
			<td>{{row.idx}}</td>
			<td><a @click="clear_all(); update_form=true; select_post(row)">{{row.subject}}</a></td>
			<td>{{row.content}}</td>
		</tr>
		<tr v-if="!rows.length"><td colspan="3">글이 없습니다.</td></tr>
		</tbody>
	</table><br>
 
	<button @click="clear_all(); insert_form= true">글작성</button>
	<span v-if="selected.length"><button @click="del()">삭제</button></span><br>
	<div v-if="result_msg" @click="result_msg = false">{{result_msg}}</div>
	
	<!--글 작성 폼-->
	<div v-if="insert_form">
	<h4>글 작성</h4>
		<input type="text" size="55" name="subject" v-model="new_post.subject" placeholder="제목"><br>
		<textarea cols="57" rows="10" name="content"  v-model="new_post.content" placeholder="내용"></textarea><br>
		<button @click="clear_all()">취소</button>
		<button @click="insert">추가</button>
	</div>
	
	<!--글 수정 폼-->
	<div  v-if="update_form">
	<h4>글 수정</h4>
		<input type="text" size="5" name="idx" v-model="choose_post.idx" disabled><br>
		<input type="text" size="55" name="subject" v-model="choose_post.subject"><br>
		<textarea cols="57" rows="10" name="content" v-model="choose_post.content"></textarea><br>
		<button @click="clear_all()">취소</button>
		<button @click="update">수정</button>
	</div>
</div> 
</body>
 
<script language="JavaScript">
let app = new Vue({
	el: '#app',
	data: {
        url:"<?php echo site_url() ?>",
		insert_form: false,
		update_form: false,
		rows:[],
		is_checkall: false, //테스트 소스
		selected:[],
		search: {
			rows_page: '',
			now_page: '',
			word1: '',
			word2: '',
			sort_field: '',
			sort_type: '',
			num_rows: '',
			total_page: ''
		},
		new_post:{
            subject:'',
            content:''
		},
		choose_post:{},
		result_msg:''
	},
	created(){
		this.search.rows_page=5;
		this.search.now_page=1;
		this.search.sort_field='idx';
		this.search.sort_type='desc';
		this.get(); 
    },
	methods:{
		<!-- 정렬할 필드와 방식 지정 -->
		sort(field){
			if (app.search.sort_field == field) {
				if ( app.search.sort_type == 'asc'){
					app.search.sort_type = 'desc';
				} else {
					app.search.sort_type = 'asc';
				}
			} else {
				app.search.sort_field = field;
				app.search.sort_type = 'desc';
			}
			app.get();
		},
		
		<!-- 페이징 기능 -->
		paging(value, type){
			if (type == 'next'){
				app.search.now_page = Number(value) + 1;
			} else if (type == 'priv') {
				app.search.now_page = value - 1;
			} else if (type == 'last') {
				app.search.now_page = app.search.total_page;
			} else {
				app.search.now_page = 1;
			}
			app.get();
		},
		
		<!-- 체크박스 전체 선택 -->
		check_all(){
			app.is_checkall = !app.is_checkall;
			app.selected = [];
			if(app.is_checkall){ 
				for (let key in app.rows) {
					app.selected.push(app.rows[key].idx);
				}
			}
		},
		
		<!-- 체크박스 업데이트 -->
		update_checkall(){
			if(app.selected.length == app.rows.length){
				app.is_checkall = true;
			}else{
				app.is_checkall = false;
			}
		},
		
		
		<!-- search 조건에 따라 서버에서 글 가져오기 -->
		get(){
			let form_data = this.form_data(this.search);
			axios.post(this.url+"/board/get", form_data).then(function(response){
				app.get_data(response.data.rows);
				app.search.num_rows = response.data.num_rows;
				app.search.total_page = response.data.total_page;
				if ( app.search.now_page > app.search.total_page ){
					app.search.now_page=1;
					app.get();
				}
			})
		},
 
		<!-- 새로운 글 작성 (서버 전송) -->
		insert(){
			let form_data = app.form_data(app.new_post);
			
			axios.post(app.url+"/board/insert", form_data).then(function(response){
				if(response.data.msg == 'success'){
					app.result_msg = '새로운 글 : "' + app.new_post.subject + '" 작성!';
					app.clear_all();
					app.get();
				}
			})
        },
		
		<!-- 기존 글 수정 -->
		update(){
            let form_data = app.form_data(app.choose_post);
			
			axios.post(app.url+"/board/update", form_data).then(function(response){
				if(response.data.msg == 'success'){
					app.result_msg = '글 제목 : "' + app.choose_post.subject + '" 수정!';
					app.clear_all();
					app.get();
				}
            })
        },
		
		<!-- 선택 글 삭제 -->
		del(){
			let idxs = "idxs=" + app.selected;
			
			axios.post(app.url+"/board/del/", idxs).then(function(response){
				if(response.data.msg == 'success'){
					app.result_msg = idxs + ' 삭제!';
					app.clear_all();
					app.get();
				}
			})
		},
		
		get_data(rows){
            app.rows = rows;
        },
		
		select_post(post){
            app.choose_post = post; 
        },
		
		form_data(obj){
			let form_data = new FormData();
			
		    for ( let key in obj ) {
		        form_data.append(key, obj[key]);
		    } 
		    return form_data;
		},
		
		clear_all(){
            app.new_post = { 
            subject:'',
            content:'',};
			app.selected=[],
			app.is_checkall= false,
            app.insert_form= false;
			app.update_form= false;
			setTimeout(function(){
				app.result_msg=''
			},1500);
        }
    }
})
</script>
</html>
